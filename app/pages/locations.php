<?php
defined('SAMS_ROOT') || exit;
/** Standorte mit eigenem Briefkopf verwalten. */
if (is_post()) {
    csrf_check();
    $old = one('SELECT * FROM locations WHERE id = ?', [(int) post('id')]);
    $row = [
        'name' => mb_substr(trim((string) post('name')), 0, 190),
        'short' => mb_substr(trim((string) post('short')), 0, 32),
        'sort' => (int) post('sort'),
        'active' => post('active') ? 1 : 0,
        'updated_at' => now(),
    ];
    foreach (array_keys(LOCATION_FIELDS) as $k) {
        $row[$k] = mb_substr(trim((string) post($k)), 0, 190);
    }
    if ($row['name'] === '') {
        flash('error', 'Bitte einen Namen angeben.');
        redirect(url('locations', ['edit' => $old['id'] ?? 0]));
    }
    if ($old) {
        update('locations', (int) $old['id'], $row);
        audit('location.update', 'location', (int) $old['id'], $row['name']);
    } else {
        $id = insert('locations', $row + ['created_at' => now()]);
        audit('location.create', 'location', (int) $id, $row['name']);
    }
    flash('success', 'Standort gespeichert. Bereits ausgestellte Dokumente behalten ihren Briefkopf.');
    redirect(url('locations'));
}

$rows = locations(false);
$edit = isset($_GET['edit']) ? (one('SELECT * FROM locations WHERE id = ?', [(int) $_GET['edit']]) ?: []) : null;
$counts = [];
foreach (all('SELECT location_id, COUNT(*) AS n FROM users WHERE location_id IS NOT NULL GROUP BY location_id') as $r) {
    $counts[(int) $r['location_id']] = (int) $r['n'];
}

ob_start(); ?>
<div class="page-head">
    <div><h1>Standorte</h1><p class="muted">Jeder Standort hat einen eigenen Briefkopf. Leere Felder übernehmen die Angaben aus den <a href="<?= e(url('settings')) ?>">Einstellungen</a>.</p></div>
    <div class="btn-row"><a class="btn primary" href="<?= e(url('locations', ['edit' => 0])) ?>#loc-form"><?= icon('plus') ?>Standort anlegen</a></div>
</div>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Standort</th><th>Anschrift</th><th class="right">Personal</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $l): ?>
            <tr><td><strong><?= e($l['name']) ?></strong><?= $l['short'] ? ' <span class="badge">' . e($l['short']) . '</span>' : '' ?><?= $l['active'] ? '' : ' <span class="badge grey">inaktiv</span>' ?><br><small class="muted"><?= e($l['org_sub']) ?></small></td>
                <td class="small"><?= e(implode(', ', array_filter([$l['org_street'], $l['org_city']]))) ?: '<span class="muted">wie Einstellungen</span>' ?></td>
                <td class="right"><?= $counts[(int) $l['id']] ?? 0 ?></td>
                <td class="right"><a class="btn small" href="<?= e(url('locations', ['edit' => $l['id']])) ?>#loc-form">Bearbeiten</a></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="4" class="muted">Noch keine Standorte – alle Dokumente nutzen den Briefkopf aus den Einstellungen.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <p class="hint">Gibt es mehr als einen Standort, wählt man beim Ausstellen den Briefkopf (vorausgewählt ist der Standort der Person, einstellbar im Konto). Der Standort wird ins Dokument eingefroren.</p>
</section>
<?php if ($edit !== null): $l = $edit + ['id' => 0, 'name' => '', 'short' => '', 'sort' => 0, 'active' => 1] + array_fill_keys(array_keys(LOCATION_FIELDS), ''); ?>
<form method="post" class="card form-grid" id="loc-form">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
    <h2 class="w-full"><?= $l['id'] ? 'Standort bearbeiten' : 'Standort anlegen' ?></h2>
    <div class="field w-full"><label>Name <span class="req">*</span></label><input name="name" required maxlength="190" value="<?= e($l['name']) ?>" placeholder="z. B. Sandy Shores Medical Center"></div>
    <div class="field"><label>Kürzel</label><input name="short" maxlength="32" value="<?= e($l['short']) ?>" placeholder="SSMC"></div>
    <div class="field"><label>Reihenfolge</label><input type="number" name="sort" value="<?= (int) $l['sort'] ?>"></div>
    <?php foreach (LOCATION_FIELDS as $k => $label): ?>
        <div class="field<?= in_array($k, ['org_sub', 'org_street', 'org_city'], true) ? ' w-full' : '' ?>"><label><?= e($label) ?></label><input name="<?= e($k) ?>" maxlength="190" value="<?= e($l[$k]) ?>" placeholder="<?= e(org($k)) ?>"></div>
    <?php endforeach; ?>
    <div class="field w-full"><label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($l['active']) ?>> aktiv</label></div>
    <div class="form-actions"><a class="btn" href="<?= e(url('locations')) ?>">Abbrechen</a><button class="btn primary">Speichern</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Standorte';
require SAMS_ROOT . '/app/layout.php';
