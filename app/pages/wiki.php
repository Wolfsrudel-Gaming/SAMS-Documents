<?php
defined('SAMS_ROOT') || exit;
/** Wiki-Übersicht mit Suche. */
$q = trim((string) ($_GET['q'] ?? ''));
$pages = $q !== ''
    ? all('SELECT * FROM wiki_pages WHERE title LIKE ? OR content LIKE ? ORDER BY title', [like($q), like($q)])
    : all('SELECT * FROM wiki_pages ORDER BY pinned DESC, category, title');
$byCat = [];
foreach ($pages as $pg) {
    $byCat[$pg['pinned'] && $q === '' ? 'Angeheftet' : $pg['category']][] = $pg;
}
$recent = $q === '' ? all('SELECT p.*, u.name AS editor FROM wiki_pages p LEFT JOIN users u ON u.id = p.updated_by ORDER BY p.updated_at DESC LIMIT 6') : [];

ob_start(); ?>
<div class="page-head">
    <div><h1>Wiki</h1><p class="muted">SOPs, Medikamente, Behandlungsabläufe und Wissen für den Dienst.</p></div>
    <div class="btn-row">
        <?php if (can('lists.manage')): ?><a class="btn" href="<?= e(url('wiki_lists')) ?>">Vorschlagslisten</a><?php endif; ?>
        <?php if (can('wiki.edit')): ?><a class="btn primary" href="<?= e(url('wiki_edit')) ?>">+ Neue Seite</a><?php endif; ?>
    </div>
</div>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="wiki"><input name="q" value="<?= e($q) ?>" placeholder="Wiki durchsuchen …" autofocus><button class="btn">Suchen</button></form>
<div class="grid-side wide-side reverse">
    <section>
        <?php foreach ($byCat as $cat => $list): ?>
            <div class="card"><h2><?= e($cat) ?></h2>
                <ul class="wiki-list"><?php foreach ($list as $pg): ?>
                    <li><a href="<?= e(url('wiki_page', ['slug' => $pg['slug']])) ?>"><?= e($pg['title']) ?></a>
                        <small class="muted"><?= e(mb_strimwidth(trim(preg_replace('/[#*\[\]`]+/', '', strip_tags((string) $pg['content']))), 0, 120, '…')) ?></small></li>
                <?php endforeach; ?></ul>
            </div>
        <?php endforeach; ?>
        <?php if (!$pages): ?><div class="card muted"><?= $q !== '' ? 'Keine Treffer.' : 'Noch keine Seiten.' ?></div><?php endif; ?>
    </section>
    <section>
        <?php if ($recent): ?>
        <div class="card"><h2>Zuletzt geändert</h2>
            <ul class="wiki-list"><?php foreach ($recent as $pg): ?><li><a href="<?= e(url('wiki_page', ['slug' => $pg['slug']])) ?>"><?= e($pg['title']) ?></a><small class="muted"><?= e(fmt_datetime($pg['updated_at'])) ?><?= $pg['editor'] ? ' · ' . e($pg['editor']) : '' ?></small></li><?php endforeach; ?></ul>
        </div>
        <?php endif; ?>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = 'Wiki';
require SAMS_ROOT . '/app/layout.php';
