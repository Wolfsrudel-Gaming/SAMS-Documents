<?php
defined('SAMS_ROOT') || exit;
/**
 * Gerichtskalender und Fristen. Anwältinnen und Anwälte (Recht „nur eigene“) sehen nur Termine und Fristen,
 * an denen sie beteiligt sind. Beteiligte werden bei neuen Terminen und 24 Stunden vorher benachrichtigt.
 */
$me = current_user();
$manage = can('court.manage') && !can('court.own');
$view = ($_GET['view'] ?? '') === 'deadlines' ? 'deadlines' : (($_GET['view'] ?? '') === 'past' ? 'past' : 'upcoming');
$rooms = setting_list('court_rooms', 'Saal 1 | Saal 2 | Besprechungsraum');
$people = court_people();
$peopleById = array_column($people, null, 'id');
$userField = fn(string $k) => isset($peopleById[(int) post($k)]) ? (int) post($k) : null;
$dt = fn(string $k) => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) post($k)) ? str_replace('T', ' ', (string) post($k)) . ':00' : '';

if (is_post()) {
    csrf_check();
    require_perm('court.manage');
    if (can('court.own')) {
        forbidden('Mit Anwaltszugang können keine Termine angelegt werden.');
    }
    $action = post('action');
    if ($action === 'hearing') {
        $h = (int) post('id') ? fone('hearings', (int) post('id')) : null;
        $p = (int) post('patient_id') ? fone('patients', (int) post('patient_id')) : null;
        $row = ['title' => mb_substr(trim((string) post('title')), 0, 190), 'kind' => in_array(post('kind'), HEARING_KINDS, true) ? post('kind') : HEARING_KINDS[0],
            'case_ref' => mb_substr(trim((string) post('case_ref')), 0, 64), 'patient_id' => $p['id'] ?? null, 'starts_at' => $dt('starts_at'),
            'room' => mb_substr(trim((string) post('room')), 0, 64), 'judge_id' => $userField('judge_id'), 'prosecutor_id' => $userField('prosecutor_id'),
            'lawyer_id' => $userField('lawyer_id'), 'participants' => mb_substr(trim((string) post('participants')), 0, 2000), 'updated_at' => now()];
        if ($row['title'] === '' || $row['starts_at'] === '') {
            flash('error', 'Bitte Bezeichnung und Termin angeben.');
            redirect(url('court'));
        }
        // Doppelbelegung des Saals im Abstand von unter 1 Stunde verhindern
        $clash = $row['room'] !== '' ? one("SELECT title FROM hearings WHERE room = ? AND status = 'planned' AND id <> ? AND ABS(" . (db_driver() === 'sqlite' ? "strftime('%s', starts_at) - strftime('%s', ?)" : 'TIMESTAMPDIFF(SECOND, ?, starts_at)') . ') < 3600 AND ' . fw(),
            [$row['room'], $h['id'] ?? 0, $row['starts_at']]) : null;
        if ($clash) {
            flash('error', $row['room'] . ' ist zu dieser Zeit bereits belegt (' . $clash['title'] . ').');
            redirect(url('court'));
        }
        if ($h) {
            update('hearings', (int) $h['id'], $row + ($h['starts_at'] !== $row['starts_at'] ? ['reminded' => 0] : []));
            $id = (int) $h['id'];
            audit('hearing.update', 'hearing', $id, $row['title'] . ' ' . $row['starts_at']);
        } else {
            $id = insert('hearings', $row + ['status' => 'planned', 'created_by' => $me['id'], 'created_at' => now()]);
            audit('hearing.create', 'hearing', $id, $row['title'] . ' ' . $row['starts_at']);
        }
        foreach (array_unique(array_filter([$row['judge_id'], $row['prosecutor_id'], $row['lawyer_id']])) as $uid) {
            notify_user((int) $uid, 'court', ($h ? 'Termin geändert: ' : 'Neuer Termin: ') . $row['title'] . ' (' . fmt_datetime($row['starts_at']) . ($row['room'] !== '' ? ', ' . $row['room'] : '') . ')', url('court'));
        }
        flash('success', 'Termin gespeichert.');
    } elseif ($action === 'hearing_status' && ($h = fone('hearings', (int) post('id')))) {
        $to = array_key_exists((string) post('status'), HEARING_STATUS) ? (string) post('status') : 'planned';
        $row = ['status' => $to, 'result' => mb_substr(trim((string) post('result')), 0, 4000), 'updated_at' => now()];
        if ($to === 'postponed' && $dt('new_date') !== '') {
            // Vertagt: neuer Termin mit denselben Beteiligten
            $copy = array_diff_key($h, array_flip(['id', 'status', 'result', 'reminded', 'created_at', 'updated_at']));
            insert('hearings', ['starts_at' => $dt('new_date'), 'status' => 'planned', 'created_by' => $me['id'], 'created_at' => now(), 'updated_at' => now()] + $copy);
        }
        update('hearings', (int) $h['id'], $row);
        audit('hearing.status', 'hearing', (int) $h['id'], $h['title'] . ' → ' . HEARING_STATUS[$to][1]);
        flash('success', 'Termin: ' . HEARING_STATUS[$to][1] . '.');
    } elseif ($action === 'deadline') {
        $p = (int) post('patient_id') ? fone('patients', (int) post('patient_id')) : null;
        $row = ['title' => mb_substr(trim((string) post('title')), 0, 190), 'kind' => in_array(post('kind'), DEADLINE_KINDS, true) ? post('kind') : 'Sonstige',
            'due_date' => post_date('due_date'), 'case_ref' => mb_substr(trim((string) post('case_ref')), 0, 64), 'patient_id' => $p['id'] ?? null,
            'assigned_to' => $userField('assigned_to'), 'note' => mb_substr(trim((string) post('note')), 0, 2000), 'created_by' => $me['id'], 'created_at' => now()];
        if ($row['title'] === '' || $row['due_date'] === '') {
            flash('error', 'Bitte Bezeichnung und Datum angeben.');
        } else {
            $id = insert('deadlines', $row);
            audit('deadline.create', 'deadline', $id, $row['title'] . ' bis ' . $row['due_date']);
            if ($row['assigned_to']) {
                notify_user((int) $row['assigned_to'], 'court', 'Neue Frist: ' . $row['title'] . ' bis ' . fmt_date($row['due_date']), url('court', ['view' => 'deadlines']));
            }
            flash('success', 'Frist eingetragen.');
        }
        redirect(url('court', ['view' => 'deadlines']));
    } elseif ($action === 'deadline_done' && ($d = fone('deadlines', (int) post('id'))) && !$d['done_at']) {
        update('deadlines', (int) $d['id'], ['done_at' => now(), 'done_by' => $me['id']]);
        audit('deadline.done', 'deadline', (int) $d['id'], $d['title']);
        flash('success', 'Frist erledigt.');
        redirect(url('court', ['view' => 'deadlines']));
    }
    redirect(url('court'));
}

$edit = $manage && (int) ($_GET['edit'] ?? 0) ? fone('hearings', (int) $_GET['edit']) : null;
$sel = 'SELECT h.*, j.name AS judge, s.name AS prosecutor, l.name AS lawyer FROM hearings h LEFT JOIN users j ON j.id = h.judge_id LEFT JOIN users s ON s.id = h.prosecutor_id LEFT JOIN users l ON l.id = h.lawyer_id WHERE ' . hearing_scope();
$hearings = $view === 'past'
    ? all($sel . " AND (h.starts_at < ? OR h.status <> 'planned') ORDER BY h.starts_at DESC LIMIT 100", [date('Y-m-d 00:00:00')])
    : all($sel . " AND h.starts_at >= ? AND h.status = 'planned' ORDER BY h.starts_at LIMIT 200", [date('Y-m-d 00:00:00')]);
$deadlines = $view === 'deadlines' ? all('SELECT d.*, u.name AS assignee FROM deadlines d LEFT JOIN users u ON u.id = d.assigned_to WHERE ' . deadline_scope() . ' ORDER BY d.done_at IS NOT NULL, d.due_date LIMIT 300') : [];
$records = $manage ? record_options() : [];
$userSelect = function (string $name, $value) use ($people) {
    $h = '<select name="' . $name . '"><option value="">–</option>';
    foreach ($people as $u) {
        $h .= '<option value="' . (int) $u['id'] . '"' . selected($u['id'], $value) . '>' . e($u['name']) . ($u['rank_short'] ? ' (' . e($u['rank_short']) . ')' : '') . '</option>';
    }
    return $h . '</select>';
};
$val = fn($k, $d = '') => e((string) ($edit[$k] ?? $d));

ob_start(); ?>
<div class="page-head"><div><h1>Gerichtskalender</h1><p class="muted"><?= can('court.own') ? 'Ihre Termine und Fristen als Rechtsbeistand.' : 'Verhandlungen, Säle, Beteiligte und Fristen.' ?></p></div></div>
<div class="tabs"><a href="<?= e(url('court')) ?>" class="<?= $view === 'upcoming' ? 'active' : '' ?>">Anstehend</a><a href="<?= e(url('court', ['view' => 'past'])) ?>" class="<?= $view === 'past' ? 'active' : '' ?>">Vergangen</a><a href="<?= e(url('court', ['view' => 'deadlines'])) ?>" class="<?= $view === 'deadlines' ? 'active' : '' ?>">Fristen</a></div>
<div class="grid-side wide-side reverse">
<section>
<?php if ($view === 'deadlines'): ?>
    <div class="card">
        <table class="list">
            <thead><tr><th>Frist</th><th>Bezeichnung</th><th>Aktenzeichen</th><th>Zuständig</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($deadlines as $d): $over = !$d['done_at'] && $d['due_date'] < date('Y-m-d'); ?>
                <tr><td class="<?= $over ? 'text-danger' : '' ?>"><?= e(fmt_date($d['due_date'])) ?><?= $over ? '<br><small>abgelaufen</small>' : '' ?></td>
                    <td><?= e($d['title']) ?><br><small class="muted"><?= e($d['kind']) ?><?= $d['note'] ? ' · ' . e(mb_strimwidth($d['note'], 0, 80, '…')) : '' ?></small></td>
                    <td class="mono small"><?= e($d['case_ref'] ?: '–') ?></td><td class="small"><?= e($d['assignee'] ?? '–') ?></td>
                    <td class="right"><?php if ($d['done_at']): ?><span class="badge green">erledigt</span><?php elseif ($manage): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="deadline_done"><input type="hidden" name="id" value="<?= (int) $d['id'] ?>"><button class="btn small">Erledigt</button></form><?php endif; ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$deadlines): ?><tr><td colspan="5" class="muted">Keine Fristen.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php else: $day = ''; ?>
    <?php foreach ($hearings as $h): $d = substr($h['starts_at'], 0, 10); if ($d !== $day): $day = $d; ?><h3 class="day-head"><?= e(['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'][(int) date('w', strtotime($d))] . ', ' . fmt_date($d)) ?></h3><?php endif; ?>
    <div class="card">
        <div class="card-head"><h2><?= e(substr($h['starts_at'], 11, 5)) ?> · <?= e($h['title']) ?> <small class="muted"><?= e($h['kind']) ?></small></h2><?= status_badge(HEARING_STATUS, $h['status']) ?></div>
        <p class="small"><?= $h['room'] !== '' ? '<strong>' . e($h['room']) . '</strong> · ' : '' ?><?= $h['case_ref'] !== '' ? 'Az. <span class="mono">' . e($h['case_ref']) . '</span> · ' : '' ?>
            Gericht: <?= e($h['judge'] ?? '–') ?> · Staatsanwaltschaft: <?= e($h['prosecutor'] ?? '–') ?> · Verteidigung: <?= e($h['lawyer'] ?? '–') ?>
            <?= $h['patient_id'] && can('patients.view') && !can('court.own') ? ' · <a href="' . e(url('patient', ['id' => $h['patient_id']])) . '">Akte</a>' : '' ?></p>
        <?php if (trim((string) $h['participants']) !== ''): ?><p class="small muted"><?= nl($h['participants']) ?></p><?php endif; ?>
        <?php if (trim((string) $h['result']) !== ''): ?><p><strong>Ergebnis:</strong> <?= nl($h['result']) ?></p><?php endif; ?>
        <?php if ($manage && $h['status'] === 'planned'): ?>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="hearing_status"><input type="hidden" name="id" value="<?= (int) $h['id'] ?>">
            <input name="result" placeholder="Ergebnis / Grund" maxlength="4000"><input type="datetime-local" name="new_date" title="Neuer Termin (bei Vertagung)">
            <button class="btn small primary" name="status" value="done">Durchgeführt</button><button class="btn small" name="status" value="postponed">Vertagen</button><button class="btn small" name="status" value="cancelled">Aufheben</button>
            <a class="btn small" href="<?= e(url('court', ['edit' => $h['id']])) ?>">Bearbeiten</a></form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$hearings): ?><div class="card muted"><?= $view === 'past' ? 'Keine vergangenen Termine.' : 'Keine anstehenden Termine.' ?></div><?php endif; ?>
<?php endif; ?>
</section>
<?php if ($manage && $view === 'deadlines'): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="deadline">
    <h2 class="w-full">Frist eintragen</h2>
    <div class="field w-full"><label>Bezeichnung</label><input name="title" required maxlength="190" placeholder="z. B. Einspruchsfrist Strafbefehl"></div>
    <div class="field"><label>Art</label><select name="kind"><?php foreach (DEADLINE_KINDS as $k): ?><option><?= e($k) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Fällig am</label><input type="date" name="due_date" required value="<?= e(date('Y-m-d', strtotime('+14 days'))) ?>"></div>
    <div class="field"><label>Aktenzeichen</label><input name="case_ref" maxlength="64"></div>
    <div class="field"><label>Zuständig</label><?= $userSelect('assigned_to', '') ?></div>
    <?php if ($records): ?><div class="field w-full"><label>Beteiligte/r</label><select name="patient_id"><option value="">–</option><?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e(patient_name($r, true)) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field w-full"><label>Notiz</label><textarea name="note" rows="2"></textarea></div>
    <div class="form-actions"><button class="btn primary">Eintragen</button></div>
</form>
<?php elseif ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="hearing"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <h2 class="w-full"><?= $edit ? 'Termin bearbeiten' : 'Termin anlegen' ?></h2>
    <div class="field w-full"><label>Bezeichnung</label><input name="title" required maxlength="190" value="<?= $val('title') ?>" placeholder="z. B. Strafsache gegen R. Raser"></div>
    <div class="field"><label>Art</label><select name="kind"><?php foreach (HEARING_KINDS as $k): ?><option<?= selected($k, $edit['kind'] ?? '') ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Aktenzeichen</label><input name="case_ref" maxlength="64" value="<?= $val('case_ref') ?>"></div>
    <div class="field"><label>Beginn</label><input type="datetime-local" name="starts_at" required value="<?= e($edit ? str_replace(' ', 'T', substr($edit['starts_at'], 0, 16)) : date('Y-m-d\T10:00', strtotime('+1 day'))) ?>"></div>
    <div class="field"><label>Saal</label><input name="room" list="rooms" value="<?= $val('room', $rooms[0] ?? '') ?>"><datalist id="rooms"><?php foreach ($rooms as $r): ?><option value="<?= e($r) ?>"><?php endforeach; ?></datalist></div>
    <div class="field"><label>Richter/in</label><?= $userSelect('judge_id', $edit['judge_id'] ?? $me['id']) ?></div>
    <div class="field"><label>Staatsanwaltschaft</label><?= $userSelect('prosecutor_id', $edit['prosecutor_id'] ?? '') ?></div>
    <div class="field"><label>Verteidigung / Rechtsbeistand</label><?= $userSelect('lawyer_id', $edit['lawyer_id'] ?? '') ?></div>
    <?php if ($records): ?><div class="field"><label>Beteiligte/r (Akte)</label><select name="patient_id"><option value="">–</option><?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"<?= selected($r['id'], $edit['patient_id'] ?? '') ?>><?= e(patient_name($r, true)) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field w-full"><label>Weitere Beteiligte (Zeugen, Sachverständige, Polizei …)</label><textarea name="participants" rows="2"><?= $val('participants') ?></textarea></div>
    <div class="form-actions"><?php if ($edit): ?><a class="btn" href="<?= e(url('court')) ?>">Abbrechen</a><?php endif; ?><button class="btn primary">Speichern</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Gerichtskalender';
require SAMS_ROOT . '/app/layout.php';
