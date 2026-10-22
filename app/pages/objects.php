<?php
defined('SAMS_ROOT') || exit;
/** Objektpläne: Gebäude mit Gefahren, Zufahrt, Schlüsseldepot, Ansprechpartner, Brandschau-Terminen und Plänen. */
$me = current_user();
if (is_post()) {
    csrf_check();
    require_perm('objects.manage');
    $name = mb_substr(trim((string) post('name')), 0, 190);
    if ($name === '') {
        flash('error', 'Bitte einen Namen angeben.');
        redirect(url('objects'));
    }
    $id = insert('objects', ['name' => $name, 'address' => mb_substr(trim((string) post('address')), 0, 190), 'kind' => in_array(post('kind'), OBJECT_KINDS, true) ? post('kind') : 'Sonstiges',
        'risk' => max(0, min(3, (int) post('risk'))), 'created_by' => $me['id'], 'created_at' => now(), 'updated_at' => now()]);
    audit('object.create', 'object', $id, $name);
    redirect(url('object', ['id' => $id]));
}
$q = trim((string) ($_GET['q'] ?? ''));
$rows = all('SELECT o.*, (SELECT COUNT(*) FROM object_files f WHERE f.object_id = o.id) AS files FROM objects o WHERE ' . fw('o')
    . ($q !== '' ? ' AND (o.name LIKE ? OR o.address LIKE ? OR o.hazards LIKE ? OR o.kind LIKE ?)' : '') . ' ORDER BY o.risk DESC, o.name LIMIT 500', $q !== '' ? [like($q), like($q), like($q), like($q)] : []);
ob_start(); ?>
<div class="page-head"><div><h1>Objektpläne</h1><p class="muted">Gebäude mit Gefahren, Zufahrten, Schlüsseldepots und Plänen – im Einsatz griffbereit im Tablet.</p></div></div>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="objects"><input name="q" value="<?= e($q) ?>" placeholder="Name, Adresse, Gefahrstoff …"><button class="btn">Suchen</button></form>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Objekt</th><th>Art</th><th>Risiko</th><th>Nächste Brandschau</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $o): $due = $o['next_inspection'] !== '' && $o['next_inspection'] < date('Y-m-d'); ?>
            <tr onclick="location.href='<?= e(url('object', ['id' => $o['id']])) ?>'"><td><strong><?= e($o['name']) ?></strong><br><small class="muted"><?= e($o['address']) ?><?= $o['files'] ? ' · ' . (int) $o['files'] . ' Plan/Pläne' : '' ?></small></td>
                <td class="small"><?= e($o['kind']) ?></td><td><span class="badge <?= (int) $o['risk'] >= 2 ? 'red' : ((int) $o['risk'] === 1 ? 'orange' : 'grey') ?>"><?= e(OBJECT_RISK[(int) $o['risk']]) ?></span></td>
                <td class="small<?= $due ? ' text-danger' : '' ?>"><?= e(fmt_date($o['next_inspection']) ?: '–') ?><?= $due ? ' · überfällig' : '' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="4" class="muted">Keine Objekte.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php if (can('objects.manage')): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <h2 class="w-full">Objekt anlegen</h2>
    <div class="field w-full"><label>Name</label><input name="name" required maxlength="190" placeholder="z. B. Pillbox Hill Medical Center"></div>
    <div class="field w-full"><label>Adresse</label><input name="address" maxlength="190"></div>
    <div class="field"><label>Art</label><select name="kind"><?php foreach (OBJECT_KINDS as $k): ?><option><?= e($k) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Risiko</label><select name="risk"><?php foreach (OBJECT_RISK as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="form-actions"><button class="btn primary">Anlegen</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Objektpläne';
require SAMS_ROOT . '/app/layout.php';
