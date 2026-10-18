<?php
defined('SAMS_ROOT') || exit;
/** Fahrzeugregister: Kennzeichen, Halter, Zulassung, gestohlen/beschlagnahmt. Abfrage auch im Spiel (/kennzeichen). */
$me = current_user();
$manage = can('vehicles.manage');
$edit = (int) ($_GET['edit'] ?? 0) ? fone('vehicles', (int) $_GET['edit']) : null;

if (is_post()) {
    csrf_check();
    require_perm('vehicles.manage');
    $v = (int) post('id') ? fone('vehicles', (int) post('id')) : null;
    $person = person_from_post();
    $row = [
        'plate' => normalize_plate((string) post('plate')), 'model' => mb_substr(trim((string) post('model')), 0, 190), 'color' => mb_substr(trim((string) post('color')), 0, 64),
        'vin' => mb_substr(trim((string) post('vin')), 0, 64), 'patient_id' => $person['patient_id'], 'owner_name' => $person['name'], 'owner_citizen_id' => $person['citizen_id'],
        'status' => array_key_exists((string) post('status'), VEHICLE_STATUS) ? post('status') : 'registered', 'valid_until' => post_date('valid_until'),
        'notes' => mb_substr(trim((string) post('notes')), 0, 2000), 'updated_at' => now(),
    ];
    if ($row['plate'] === '') {
        flash('error', 'Bitte ein Kennzeichen angeben.');
    } elseif (scalar('SELECT id FROM vehicles WHERE plate = ? AND id <> ? AND ' . fw(), [$row['plate'], $v['id'] ?? 0])) {
        flash('error', 'Das Kennzeichen ist bereits registriert.');
    } elseif ($v) {
        update('vehicles', (int) $v['id'], $row);
        audit('vehicle.update', 'vehicle', (int) $v['id'], $row['plate'] . ' · ' . VEHICLE_STATUS[$row['status']][1]);
        flash('success', 'Fahrzeug gespeichert.');
    } else {
        $id = insert('vehicles', $row + ['created_by' => $me['id'], 'created_at' => now()]);
        audit('vehicle.create', 'vehicle', $id, $row['plate'] . ' ' . $row['model']);
        flash('success', 'Fahrzeug registriert.');
    }
    redirect(url('vehicles', ['q' => $row['plate']]));
}

$q = trim((string) ($_GET['q'] ?? ''));
$status = array_key_exists($_GET['status'] ?? '', VEHICLE_STATUS) ? $_GET['status'] : '';
$where = fw('v');
$params = [];
if ($q !== '') {
    $where .= ' AND (v.plate LIKE ? OR v.owner_name LIKE ? OR v.owner_citizen_id LIKE ? OR v.model LIKE ? OR v.vin LIKE ?)';
    array_push($params, like(normalize_plate($q) ?: $q), like($q), like($q), like($q), like($q));
}
if ($status !== '') {
    $where .= ' AND v.status = ?';
    $params[] = $status;
}
$rows = all("SELECT v.* FROM vehicles v WHERE $where ORDER BY v.updated_at DESC, v.id DESC LIMIT 300", $params);
// Treffer anderer Behörden zum gesuchten Kennzeichen (z. B. Zulassung beim DMV)
$foreign = $q !== '' && normalize_plate($q) !== '' ? array_values(array_filter(vehicle_lookup($q), fn($v) => (int) $v['faction_id'] !== fid())) : [];
$wantedHits = $q !== '' && module_enabled('wanted') ? wanted_lookup('', $q) : [];
$records = $manage ? record_options() : [];
$val = fn($k, $d = '') => e((string) ($edit[$k] ?? $d));

ob_start(); ?>
<div class="page-head"><div><h1>Fahrzeugregister</h1><p class="muted">Kennzeichen, Halter/in und Zulassung. Gestohlene und beschlagnahmte Fahrzeuge erscheinen bei der Kennzeichenabfrage im Spiel.</p></div></div>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="vehicles">
    <input name="q" value="<?= e($q) ?>" placeholder="Kennzeichen, Halter/in, Bürger-ID, Modell, FIN …">
    <select name="status" onchange="this.form.submit()"><option value="">Alle</option><?php foreach (VEHICLE_STATUS as $k => [, $l]): ?><option value="<?= $k ?>"<?= selected($k, $status) ?>><?= e($l) ?></option><?php endforeach; ?></select>
    <button class="btn">Suchen</button>
</form>
<?php foreach ($wantedHits as $w): ?><div class="flash error"><strong>🚨 Fahndung:</strong> <?= e($w['plate']) ?> – <?= e(mb_strimwidth((string) $w['reason'], 0, 160, '…')) ?></div><?php endforeach; ?>
<div class="grid-side wide-side reverse">
<section>
    <div class="card">
        <table class="list">
            <thead><tr><th>Kennzeichen</th><th>Fahrzeug</th><th>Halter/in</th><th>Zulassung bis</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr<?= $manage ? ' onclick="location.href=\'' . e(url('vehicles', ['edit' => $r['id'], 'q' => $q])) . '\'"' : '' ?>><td class="mono"><strong><?= e($r['plate']) ?></strong></td>
                    <td><?= e(trim($r['model'] . ' · ' . $r['color'], ' ·')) ?: '–' ?><?= $r['vin'] !== '' ? '<br><small class="muted mono">' . e($r['vin']) . '</small>' : '' ?></td>
                    <td class="small"><?= $r['patient_id'] ? '<a href="' . e(url('patient', ['id' => $r['patient_id']])) . '">' . e($r['owner_name']) . '</a>' : e($r['owner_name'] ?: '–') ?><?= $r['owner_citizen_id'] !== '' ? '<br><span class="mono muted">' . e($r['owner_citizen_id']) . '</span>' : '' ?></td>
                    <td class="small<?= $r['valid_until'] !== '' && $r['valid_until'] < date('Y-m-d') ? ' text-danger' : '' ?>"><?= e(fmt_date($r['valid_until']) ?: '–') ?></td>
                    <td><?= status_badge(VEHICLE_STATUS, $r['status']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">Keine Fahrzeuge gefunden.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($foreign): ?>
    <div class="card"><h2>Bei anderen Behörden registriert</h2>
        <table class="list compact"><tbody>
        <?php foreach ($foreign as $r): ?><tr><td class="mono"><?= e($r['plate']) ?></td><td><?= e(trim($r['model'] . ' ' . $r['color'])) ?></td><td class="small"><?= e($r['owner_name']) ?> <span class="mono muted"><?= e($r['owner_citizen_id']) ?></span></td><td><?= status_badge(VEHICLE_STATUS, $r['status']) ?></td><td class="small muted"><?= e(faction_by_id((int) $r['faction_id'])['short'] ?? '') ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    <?php endif; ?>
</section>
<?php if ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <h2 class="w-full"><?= $edit ? 'Fahrzeug bearbeiten' : 'Fahrzeug registrieren' ?></h2>
    <div class="field"><label>Kennzeichen</label><input name="plate" required maxlength="16" class="mono" value="<?= $val('plate', normalize_plate($q)) ?>"></div>
    <div class="field"><label>Status</label><select name="status"><?php foreach (VEHICLE_STATUS as $k => [, $l]): ?><option value="<?= $k ?>"<?= selected($k, $edit['status'] ?? 'registered') ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Modell</label><input name="model" maxlength="190" value="<?= $val('model') ?>" placeholder="z. B. Bravado Buffalo"></div>
    <div class="field"><label>Farbe</label><input name="color" maxlength="64" value="<?= $val('color') ?>"></div>
    <div class="field"><label>FIN</label><input name="vin" maxlength="64" class="mono" value="<?= $val('vin') ?>"></div>
    <div class="field"><label>Zulassung / HU bis</label><input type="date" name="valid_until" value="<?= $val('valid_until') ?>"></div>
    <?php if ($records): ?><div class="field w-full"><label>Halter/in (<?= e(term('record')) ?>)</label><select name="patient_id"><option value="">–</option><?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"<?= selected($r['id'], $edit['patient_id'] ?? '') ?>><?= e(patient_name($r, true)) ?><?= $r['citizen_id'] ? ' · ' . e($r['citizen_id']) : '' ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field"><label>oder Name</label><input name="name" maxlength="190" value="<?= $edit && !$edit['patient_id'] ? $val('owner_name') : '' ?>"></div>
    <div class="field"><label>Bürger-ID</label><input name="citizen_id" maxlength="64" value="<?= $edit && !$edit['patient_id'] ? $val('owner_citizen_id') : '' ?>"></div>
    <div class="field w-full"><label>Notizen</label><textarea name="notes" rows="2"><?= $val('notes') ?></textarea></div>
    <div class="form-actions"><?php if ($edit): ?><a class="btn" href="<?= e(url('vehicles')) ?>">Abbrechen</a><?php endif; ?><button class="btn primary">Speichern</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Fahrzeugregister';
require SAMS_ROOT . '/app/layout.php';
