<?php
defined('SAMS_ROOT') || exit;
/** Gesetze lesen und durchsuchen; Pflege (laws.manage) und Übernahme der Strafmaße in den eigenen Katalog (prices.manage). */
$me = current_user();
$manage = can('laws.manage');
$codes = law_codes(!$manage);

if (is_post()) {
    csrf_check();
    $action = post('action');
    $code = fone_global_law((int) post('code_id'));
    if ($action === 'catalog' && $code && can('prices.manage')) {
        [$new, $upd] = law_to_catalog((int) $code['id']);
        audit('prices.import', 'law_code', (int) $code['id'], $code['name'] . ": $new neu, $upd aktualisiert");
        flash('success', "Strafmaße übernommen: $new neue und $upd aktualisierte Positionen im " . term('catalog') . '.');
        redirect(url('laws', ['code' => $code['id']]));
    }
    require_perm('laws.manage');
    if ($action === 'code' && $code) {
        $server = (int) post('server_id');
        update('law_codes', (int) $code['id'], ['name' => mb_substr(trim((string) post('name')) ?: $code['name'], 0, 190), 'server_id' => $server && game_server($server) ? $server : null,
            'active' => post('active') ? 1 : 0, 'updated_at' => now()]);
        audit('laws.update', 'law_code', (int) $code['id'], (string) post('name'));
        flash('success', 'Gespeichert.');
    } elseif ($action === 'delete' && $code) {
        q('DELETE FROM law_sections WHERE book_id IN (SELECT id FROM law_books WHERE code_id = ?)', [$code['id']]);
        q('DELETE FROM law_books WHERE code_id = ?', [$code['id']]);
        q('DELETE FROM law_codes WHERE id = ?', [$code['id']]);
        audit('laws.delete', 'law_code', (int) $code['id'], $code['name']);
        flash('success', 'Gesetzbuch gelöscht.');
        redirect(url('laws'));
    } elseif ($action === 'section' && ($s = one('SELECT s.*, b.code_id FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE s.id = ?', [(int) post('section_id')]))) {
        $penalty = mb_substr(trim((string) post('penalty')), 0, 190);
        [$jail, $fine, $mode] = law_parse_penalty($penalty);
        update('law_sections', (int) $s['id'], ['title' => mb_substr(trim((string) post('title')), 0, 190), 'body' => trim((string) post('body')), 'penalty' => $penalty,
            'jail_max' => $jail, 'fine_max' => $fine, 'penalty_mode' => $mode]);
        audit('laws.section', 'law_code', (int) $s['code_id'], '§ ' . $s['number'] . ' ' . post('title'));
        flash('success', '§ ' . $s['number'] . ' gespeichert.');
        redirect(url('laws', ['code' => $s['code_id']]) . '#s' . $s['id']);
    }
    redirect(url('laws', $code ? ['code' => $code['id']] : []));
}

/** Gesetzbuch nach ID (Gesetzbücher gehören keiner Fraktion). */
function fone_global_law(int $id): ?array
{
    return $id ? one('SELECT * FROM law_codes WHERE id = ?', [$id]) : null;
}

$current = isset($_GET['code']) ? fone_global_law((int) $_GET['code']) : law_default_code($codes, (int) $me['id']);
if ($current && !$current['active'] && !$manage) {
    $current = null;
}
$q = trim((string) ($_GET['q'] ?? ''));
$books = $current ? all('SELECT * FROM law_books WHERE code_id = ? ORDER BY sort', [$current['id']]) : [];
$sections = [];
if ($books) {
    $like = '%' . $q . '%';
    foreach (all('SELECT s.* FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ?' . ($q !== '' ? ' AND (s.title LIKE ? OR s.body LIKE ? OR s.penalty LIKE ?)' : '') . ' ORDER BY b.sort, s.sort',
        $q !== '' ? [$current['id'], $like, $like, $like] : [$current['id']]) as $s) {
        $sections[(int) $s['book_id']][] = $s;
    }
}
$hits = array_sum(array_map('count', $sections));
$hasPenalties = $current && (bool) scalar('SELECT 1 FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ? AND (s.jail_max > 0 OR s.fine_max > 0) LIMIT 1', [$current['id']]);

ob_start(); ?>
<div class="page-head"><div><h1>Gesetze</h1><p class="muted"><?= $current ? e($current['name']) . (multi_server() ? ' · ' . e(game_server_label((int) $current['server_id']) ?: 'alle Staaten') : '') : 'Noch kein Gesetzbuch vorhanden.' ?></p></div>
    <div class="actions">
        <?php if (count($codes) > 1): ?><form method="get" class="inline"><input type="hidden" name="p" value="laws"><select name="code" onchange="this.form.submit()"><?php foreach ($codes as $c): ?><option value="<?= (int) $c['id'] ?>"<?= selected($c['id'], $current['id'] ?? 0) ?>><?= e($c['name']) ?><?= multi_server() ? ' (' . e(game_server_label((int) $c['server_id']) ?: 'alle') . ')' : '' ?><?= $c['active'] ? '' : ' – inaktiv' ?></option><?php endforeach; ?></select></form><?php endif; ?>
        <?php if ($manage): ?><a class="btn primary" href="<?= e(url('law_import')) ?>"><?= icon('plus') ?>Gesetzbuch importieren</a><?php endif; ?>
    </div></div>

<?php if ($current): ?>
<div class="grid-side law-layout">
<aside class="card law-nav no-print">
    <form method="get" class="law-search"><input type="hidden" name="p" value="laws"><input type="hidden" name="code" value="<?= (int) $current['id'] ?>">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Suchen (z. B. Raub, HE, Waffe)"></form>
    <?php if ($q !== ''): ?><p class="small muted"><?= $hits ?> Treffer · <a href="<?= e(url('laws', ['code' => $current['id']])) ?>">Suche löschen</a></p><?php endif; ?>
    <?php foreach ($books as $b): if (empty($sections[(int) $b['id']])) continue; ?>
        <details open><summary><strong><?= e($b['short'] ?: $b['title']) ?></strong> <span class="muted small"><?= e($b['short'] ? $b['title'] : '') ?></span></summary>
            <ul><?php foreach ($sections[(int) $b['id']] as $s): ?><li><a href="#s<?= (int) $s['id'] ?>">§ <?= e($s['number']) ?> <?= e($s['title']) ?></a></li><?php endforeach; ?></ul></details>
    <?php endforeach; ?>
    <?php if ($hasPenalties && can('prices.manage')): ?>
        <form method="post" onsubmit="return confirm('Alle Paragraphen mit Strafmaß als Positionen (z. B. „§1 StGB“) in den <?= e(term('catalog')) ?> deiner Fraktion übernehmen? Vorhandene Positionen mit gleichem Kürzel werden aktualisiert.')">
            <?= csrf_field() ?><input type="hidden" name="action" value="catalog"><input type="hidden" name="code_id" value="<?= (int) $current['id'] ?>">
            <button class="btn small block">Strafmaße in den <?= e(term('catalog')) ?> übernehmen</button></form>
    <?php endif; ?>
</aside>
<section class="law-text">
    <?php if ($current['description'] && $q === ''): ?><p class="muted"><?= e($current['description']) ?></p><?php endif; ?>
    <?php foreach ($books as $b): if (empty($sections[(int) $b['id']])) continue; ?>
        <div class="card law-book"><h2><?= e($b['title']) ?><?= $b['short'] ? ' <small class="muted">(' . e($b['short']) . ')</small>' : '' ?></h2>
        <?php foreach ($sections[(int) $b['id']] as $s): ?>
            <article class="law-section" id="s<?= (int) $s['id'] ?>">
                <h3>§ <?= e($s['number']) ?> <?= e($s['title']) ?> <?= $b['short'] ? '<small class="muted">' . e($b['short']) . '</small>' : '' ?></h3>
                <?= law_body_html((string) $s['body'], $q) ?>
                <?php if ($s['penalty'] !== ''): ?><p class="law-penalty"><span class="badge red">Strafmaß</span> <?= e($s['penalty']) ?></p><?php endif; ?>
                <?php if ($manage): ?>
                <details class="no-print"><summary class="small muted">bearbeiten</summary>
                    <form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="action" value="section"><input type="hidden" name="section_id" value="<?= (int) $s['id'] ?>">
                        <div class="field w-full"><label>Überschrift</label><input name="title" value="<?= e($s['title']) ?>" maxlength="190"></div>
                        <div class="field w-full"><label>Text (Aufzählungen mit „- “ beginnen)</label><textarea name="body" rows="6"><?= e($s['body']) ?></textarea></div>
                        <div class="field w-full"><label>Strafmaß</label><input name="penalty" value="<?= e($s['penalty']) ?>" maxlength="190" placeholder="z. B. bis 20 HE und 10.000 $"></div>
                        <div class="form-actions"><button class="btn small primary">Speichern</button></div></form></details>
                <?php endif; ?>
            </article>
        <?php endforeach; ?></div>
    <?php endforeach; ?>
    <?php if (!$hits): ?><div class="card muted"><?= $q !== '' ? 'Keine Treffer.' : 'Dieses Gesetzbuch enthält noch keine Paragraphen.' ?></div><?php endif; ?>
    <?php if ($manage): ?>
    <form method="post" class="card form-grid no-print">
        <?= csrf_field() ?><input type="hidden" name="action" value="code"><input type="hidden" name="code_id" value="<?= (int) $current['id'] ?>">
        <h2 class="w-full">Gesetzbuch verwalten</h2>
        <div class="field w-third"><label>Name</label><input name="name" value="<?= e($current['name']) ?>" maxlength="190"></div>
        <?php if (count(game_servers()) > 1): ?><div class="field w-third"><label>Gilt in</label><select name="server_id"><option value="0">allen Staaten</option><?php foreach (game_servers() as $gs): ?><option value="<?= (int) $gs['id'] ?>"<?= selected($gs['id'], $current['server_id']) ?>><?= e($gs['name']) ?></option><?php endforeach; ?></select></div>
        <?php else: ?><input type="hidden" name="server_id" value="<?= (int) $current['server_id'] ?>"><?php endif; ?>
        <div class="field w-sixth"><label class="choice"><input type="checkbox" name="active" value="1"<?= checked((bool) $current['active']) ?>> aktiv</label></div>
        <p class="hint w-full">Quelle: <?= e($current['source'] ?: '–') ?> · zuletzt aktualisiert <?= e(fmt_datetime($current['updated_at'] ?: $current['created_at'])) ?>. Eine neue Fassung einfach erneut importieren – gleicher Name im selben Staat ersetzt das Gesetzbuch.</p>
        <div class="form-actions"><button class="btn primary">Speichern</button></div>
    </form>
    <form method="post" class="no-print" onsubmit="return confirm('Gesetzbuch endgültig löschen?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="code_id" value="<?= (int) $current['id'] ?>"><button class="btn small danger">Gesetzbuch löschen</button></form>
    <?php endif; ?>
</section>
</div>
<?php elseif ($manage): ?>
<div class="card"><p>Noch kein Gesetzbuch. <a href="<?= e(url('law_import')) ?>">Jetzt importieren</a> – z. B. die PDF eures Servers.</p></div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Gesetze';
require SAMS_ROOT . '/app/layout.php';
