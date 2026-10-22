<?php
defined('SAMS_ROOT') || exit;
/** Asservaten: Beweismittel erfassen, Lagerort, Status und lückenlose Kette aller Bewegungen. */
$me = current_user();
$storages = setting_list('evidence_storage', 'Asservatenkammer | Waffenschrank | Kühlraum (biologische Spuren) | Fahrzeughalle');

if (is_post()) {
    csrf_check();
    require_perm('evidence.manage');
    $person = person_from_post();
    $case = (int) post('case_id') ? fone('cases', (int) post('case_id')) : null;
    $row = [
        'title' => mb_substr(trim((string) post('title')), 0, 190), 'description' => mb_substr(trim((string) post('description')), 0, 4000),
        'category' => in_array(post('category'), EVIDENCE_CATEGORIES, true) ? post('category') : 'Sonstiges',
        'quantity' => mb_substr(trim((string) post('quantity')), 0, 190), 'storage' => mb_substr(trim((string) post('storage')), 0, 190),
        'patient_id' => $person['patient_id'], 'owner' => $person['name'], 'case_id' => $case['id'] ?? null,
        'found_at' => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) post('found_at')) ? str_replace('T', ' ', post('found_at')) . ':00' : now(),
        'found_location' => mb_substr(trim((string) post('found_location')), 0, 190),
    ];
    if ($row['title'] === '') {
        flash('error', 'Bitte eine Bezeichnung angeben.');
        redirect(url('evidence'));
    }
    $row += ['number' => authority_number('evidence', 'AS'), 'status' => 'stored', 'created_by' => $me['id'], 'created_at' => now(), 'updated_at' => now()];
    $id = insert('evidence', $row);
    evidence_move($id, 'stored', 'Erfasst' . ($row['found_location'] !== '' ? ', aufgefunden: ' . $row['found_location'] : ''), $row['storage']);
    audit('evidence.create', 'evidence', $id, $row['number'] . ' ' . $row['title']);
    flash('success', 'Asservat ' . $row['number'] . ' erfasst.');
    redirect(url('evidence_item', ['id' => $id]));
}

$status = array_key_exists($_GET['status'] ?? '', EVIDENCE_STATUS) || ($_GET['status'] ?? '') === 'all' ? $_GET['status'] : 'active';
$q = trim((string) ($_GET['q'] ?? ''));
$where = fw('e');
$params = [];
if ($status === 'active') {
    $where .= " AND e.status IN ('stored', 'out')";
} elseif ($status !== 'all') {
    $where .= ' AND e.status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where .= ' AND (e.number LIKE ? OR e.title LIKE ? OR e.owner LIKE ? OR e.storage LIKE ? OR e.description LIKE ?)';
    array_push($params, like($q), like($q), like($q), like($q), like($q));
}
$rows = all("SELECT e.*, c.number AS case_number FROM evidence e LEFT JOIN cases c ON c.id = e.case_id WHERE $where ORDER BY e.id DESC LIMIT 300", $params);
$manage = can('evidence.manage');
$records = $manage ? record_options() : [];
$cases = $manage && module_enabled('records') ? all("SELECT id, number, title FROM cases WHERE status = 'open' AND " . fw() . ' ORDER BY id DESC LIMIT 200') : [];

ob_start(); ?>
<div class="page-head"><div><h1>Asservaten</h1><p class="muted">Beweismittel mit Lagerort und lückenloser Kette aller Bewegungen.</p></div></div>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="evidence">
    <input name="q" value="<?= e($q) ?>" placeholder="Nummer, Bezeichnung, Person, Lagerort …">
    <select name="status" onchange="this.form.submit()"><option value="active">Im Bestand</option><?php foreach (EVIDENCE_STATUS as $k => [, $l]): ?><option value="<?= $k ?>"<?= selected($k, $status) ?>><?= e($l) ?></option><?php endforeach; ?><option value="all"<?= selected('all', $status) ?>>Alle</option></select>
    <button class="btn">Suchen</button>
</form>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Nummer</th><th>Bezeichnung</th><th>Person / Vorgang</th><th>Lagerort</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr onclick="location.href='<?= e(url('evidence_item', ['id' => $r['id']])) ?>'"><td class="mono"><?= e($r['number']) ?></td>
                <td><?= e($r['title']) ?><br><small class="muted"><?= e($r['category']) ?><?= $r['quantity'] !== '' ? ' · ' . e($r['quantity']) : '' ?></small></td>
                <td class="small"><?= e($r['owner'] ?: '–') ?><?= $r['case_number'] ? '<br><span class="mono">' . e($r['case_number']) . '</span>' : '' ?></td>
                <td class="small"><?= e($r['storage'] ?: '–') ?></td><td><?= status_badge(EVIDENCE_STATUS, $r['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted">Keine Asservaten gefunden.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php if ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <h2 class="w-full">Asservat erfassen</h2>
    <div class="field w-full"><label>Bezeichnung</label><input name="title" required maxlength="190" placeholder="z. B. Pistole Kaliber 9 mm, Seriennr. …"></div>
    <div class="field"><label>Kategorie</label><select name="category"><?php foreach (EVIDENCE_CATEGORIES as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Menge</label><input name="quantity" maxlength="190" placeholder="z. B. 3 Stück, 25 g"></div>
    <div class="field w-full"><label>Beschreibung / Spurenlage</label><textarea name="description" rows="3"></textarea></div>
    <div class="field"><label>Aufgefunden am</label><input type="datetime-local" name="found_at" value="<?= e(date('Y-m-d\TH:i')) ?>"></div>
    <div class="field"><label>Fundort</label><input name="found_location" maxlength="190"></div>
    <div class="field w-full"><label>Lagerort</label><input name="storage" list="storages" maxlength="190" value="<?= e($storages[0] ?? '') ?>"><datalist id="storages"><?php foreach ($storages as $st): ?><option value="<?= e($st) ?>"><?php endforeach; ?></datalist></div>
    <?php if ($records): ?><div class="field w-full"><label>Betroffene Person (<?= e(term('record')) ?>)</label><select name="patient_id"><option value="">–</option><?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e(patient_name($r, true)) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field w-full"><label>oder Name</label><input name="name" maxlength="190"></div>
    <?php if ($cases): ?><div class="field w-full"><label><?= e(term('case')) ?></label><select name="case_id"><option value="">–</option><?php foreach ($cases as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['number'] . ' ' . $c['title']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="form-actions"><button class="btn primary">Erfassen</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Asservaten';
require SAMS_ROOT . '/app/layout.php';
