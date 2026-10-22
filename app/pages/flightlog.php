<?php
defined('SAMS_ROOT') || exit;
/** Flugbuch: Luftfahrzeuge mit Betriebszeit und Wartung, Flüge, Flugzeiten je Pilot/in. */
$me = current_user();
$manage = can('flightlog.manage');
$staff = all('SELECT id, name FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name');
$staffIds = array_map('intval', array_column($staff, 'id'));

if (is_post()) {
    csrf_check();
    require_perm('flightlog.manage');
    $action = post('action');
    if ($action === 'aircraft') {
        $ac = (int) post('id') ? fone('aircraft', (int) post('id')) : null;
        $row = ['callsign' => mb_substr(trim((string) post('callsign')), 0, 32), 'registration' => mb_substr(trim((string) post('registration')), 0, 32),
            'kind' => in_array(post('kind'), AIRCRAFT_KINDS, true) ? post('kind') : 'Hubschrauber', 'model' => mb_substr(trim((string) post('model')), 0, 190),
            'status' => array_key_exists((string) post('status'), AIRCRAFT_STATUS) ? post('status') : 'ok', 'maintenance_interval' => max(0, (int) post('interval_h')) * 60,
            'notes' => mb_substr(trim((string) post('notes')), 0, 2000), 'updated_at' => now()];
        if ($row['callsign'] === '') {
            flash('error', 'Bitte einen Rufnamen angeben.');
        } elseif ($ac) {
            update('aircraft', (int) $ac['id'], $row);
            flash('success', 'Gespeichert.');
        } else {
            insert('aircraft', $row + ['minutes_total' => max(0, (int) post('hours_total')) * 60, 'maintenance_at' => $row['maintenance_interval'] > 0 ? max(0, (int) post('hours_total')) * 60 + $row['maintenance_interval'] : 0,
                'active' => 1, 'created_at' => now()]);
            flash('success', 'Luftfahrzeug angelegt.');
        }
        audit('flightlog.aircraft', 'aircraft', (int) ($ac['id'] ?? 0), $row['callsign']);
    } elseif ($action === 'maintenance' && ($ac = fone('aircraft', (int) post('id')))) {
        update('aircraft', (int) $ac['id'], ['status' => 'ok', 'maintenance_at' => (int) $ac['maintenance_interval'] > 0 ? (int) $ac['minutes_total'] + (int) $ac['maintenance_interval'] : 0, 'updated_at' => now()]);
        audit('flightlog.maintenance', 'aircraft', (int) $ac['id'], $ac['callsign'] . ' Wartung durchgeführt bei ' . minutes_hm((int) $ac['minutes_total']));
        flash('success', 'Wartung eingetragen.');
    } elseif ($action === 'flight' && ($ac = fone('aircraft', (int) post('aircraft_id')))) {
        $pilot = in_array((int) post('pilot_id'), $staffIds, true) ? (int) post('pilot_id') : null;
        flight_log($ac, ['flown_on' => post_date('flown_on') ?: date('Y-m-d'), 'pilot_id' => $pilot, 'crew' => mb_substr(trim((string) post('crew')), 0, 190),
            'mission' => mb_substr(trim((string) post('mission')), 0, 190), 'start_place' => mb_substr(trim((string) post('start_place')), 0, 190),
            'end_place' => mb_substr(trim((string) post('end_place')), 0, 190), 'minutes' => max(1, min(1440, (int) post('minutes'))), 'winch_ops' => max(0, (int) post('winch_ops')),
            'landings' => max(0, (int) post('landings')), 'recorded_by' => $me['id']]);
        audit('flightlog.flight', 'aircraft', (int) $ac['id'], $ac['callsign'] . ': ' . post('mission'));
        flash('success', 'Flug eingetragen.');
    }
    redirect(url('flightlog'));
}

$aircraft = all('SELECT * FROM aircraft WHERE active = 1 AND ' . fw() . ' ORDER BY callsign');
$flights = all('SELECT f.*, a.callsign, u.name AS pilot FROM flights f JOIN aircraft a ON a.id = f.aircraft_id LEFT JOIN users u ON u.id = f.pilot_id WHERE ' . fw('f') . ' ORDER BY f.flown_on DESC, f.id DESC LIMIT 100');
$pilots = all('SELECT u.name, COUNT(*) AS n, SUM(f.minutes) AS minutes, SUM(f.winch_ops) AS winch, SUM(CASE WHEN f.flown_on >= ? THEN f.minutes ELSE 0 END) AS recent
    FROM flights f JOIN users u ON u.id = f.pilot_id WHERE ' . fw('f') . ' GROUP BY u.id, u.name ORDER BY minutes DESC', [date('Y-m-d', strtotime('-90 days'))]);

ob_start(); ?>
<div class="page-head"><div><h1>Flugbuch</h1><p class="muted">Luftfahrzeuge mit Betriebszeit und Wartung, Flüge und Flugzeiten. Flugberichte mit bekannter Kennung werden automatisch eingetragen.</p></div></div>
<div class="card"><table class="list">
    <thead><tr><th>Luftfahrzeug</th><th>Typ</th><th>Betriebszeit</th><th>Wartung bei</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($aircraft as $a): $due = (int) $a['maintenance_at'] > 0 && (int) $a['minutes_total'] >= (int) $a['maintenance_at']; ?>
        <tr><td><strong><?= e($a['callsign']) ?></strong> <small class="muted mono"><?= e($a['registration']) ?></small></td><td class="small"><?= e($a['kind']) ?> <?= e($a['model']) ?></td>
            <td><?= e(minutes_hm((int) $a['minutes_total'])) ?></td><td class="small<?= $due ? ' text-danger' : '' ?>"><?= (int) $a['maintenance_at'] > 0 ? e(minutes_hm((int) $a['maintenance_at'])) . ($due ? ' · fällig' : '') : '–' ?></td>
            <td><?= status_badge(AIRCRAFT_STATUS, $a['status']) ?></td>
            <td class="right"><?php if ($manage && (int) $a['maintenance_interval'] > 0): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="maintenance"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button class="btn small">Wartung durchgeführt</button></form><?php endif; ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$aircraft): ?><tr><td colspan="6" class="muted">Noch keine Luftfahrzeuge.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="grid-side wide-side reverse">
<section>
    <div class="card"><h2>Flüge</h2><table class="list compact"><thead><tr><th>Datum</th><th>Luftfahrzeug</th><th>Pilot/in</th><th>Auftrag</th><th>Zeit</th><th>Winde</th></tr></thead><tbody>
        <?php foreach ($flights as $f): ?><tr><td class="small"><?= e(fmt_date($f['flown_on'])) ?></td><td><?= e($f['callsign']) ?></td><td class="small"><?= e($f['pilot'] ?? '–') ?><?= $f['crew'] ? '<br><span class="muted">' . e($f['crew']) . '</span>' : '' ?></td>
            <td class="small"><?= e($f['mission']) ?><?= $f['start_place'] || $f['end_place'] ? '<br><span class="muted">' . e(trim($f['start_place'] . ' → ' . $f['end_place'], ' →')) . '</span>' : '' ?><?= $f['document_id'] ? ' <a href="' . e(url('document', ['id' => $f['document_id']])) . '">Bericht</a>' : '' ?></td>
            <td class="small"><?= (int) $f['minutes'] ?> min</td><td class="small"><?= (int) $f['winch_ops'] ?: '' ?></td></tr><?php endforeach; ?>
        <?php if (!$flights): ?><tr><td colspan="6" class="muted">Noch keine Flüge.</td></tr><?php endif; ?>
    </tbody></table></div>
    <div class="card"><h2>Flugzeiten je Pilot/in</h2><table class="list compact"><thead><tr><th>Pilot/in</th><th>Flüge</th><th>Gesamt</th><th>letzte 90 Tage</th><th>Windeneinsätze</th></tr></thead><tbody>
        <?php foreach ($pilots as $p): ?><tr><td><?= e($p['name']) ?></td><td><?= (int) $p['n'] ?></td><td><?= e(minutes_hm((int) $p['minutes'])) ?></td><td><?= e(minutes_hm((int) $p['recent'])) ?></td><td><?= (int) $p['winch'] ?></td></tr><?php endforeach; ?>
        <?php if (!$pilots): ?><tr><td colspan="5" class="muted">–</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php if ($manage): ?>
<section>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="flight">
    <h2 class="w-full">Flug eintragen</h2>
    <div class="field"><label>Luftfahrzeug</label><select name="aircraft_id" required><?php foreach ($aircraft as $a): ?><option value="<?= (int) $a['id'] ?>"><?= e($a['callsign']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Datum</label><input type="date" name="flown_on" value="<?= e(date('Y-m-d')) ?>"></div>
    <div class="field"><label>Pilot/in</label><select name="pilot_id"><?php foreach ($staff as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected($u['id'], $me['id']) ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Besatzung</label><input name="crew" maxlength="190"></div>
    <div class="field w-full"><label>Auftrag</label><input name="mission" maxlength="190" placeholder="z. B. Windenbergung Mount Chiliad"></div>
    <div class="field"><label>Start</label><input name="start_place" maxlength="190"></div><div class="field"><label>Landung</label><input name="end_place" maxlength="190"></div>
    <div class="field"><label>Flugzeit (min)</label><input type="number" name="minutes" min="1" value="30"></div><div class="field"><label>Windeneinsätze</label><input type="number" name="winch_ops" min="0" value="0"></div>
    <div class="field"><label>Landungen</label><input type="number" name="landings" min="0" value="1"></div>
    <div class="form-actions"><button class="btn primary"<?= $aircraft ? '' : ' disabled' ?>>Eintragen</button></div>
</form>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="aircraft">
    <h2 class="w-full">Luftfahrzeug anlegen</h2>
    <div class="field"><label>Rufname</label><input name="callsign" required maxlength="32" placeholder="z. B. Air Rescue 1"></div><div class="field"><label>Kennung</label><input name="registration" maxlength="32" class="mono"></div>
    <div class="field"><label>Art</label><select name="kind"><?php foreach (AIRCRAFT_KINDS as $k): ?><option><?= e($k) ?></option><?php endforeach; ?></select></div><div class="field"><label>Muster</label><input name="model" maxlength="190" placeholder="z. B. Maverick"></div>
    <div class="field"><label>Betriebszeit bisher (Std.)</label><input type="number" name="hours_total" min="0" value="0"></div><div class="field"><label>Wartung alle … Std. (0 = keine)</label><input type="number" name="interval_h" min="0" value="50"></div>
    <div class="form-actions"><button class="btn primary">Anlegen</button></div>
</form>
</section>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Flugbuch';
require SAMS_ROOT . '/app/layout.php';
