<?php
defined('SAMS_ROOT') || exit;
/** Bürgerportal bearbeiten: Terminanfragen, Anträge, Bewerbungen, Bürgerkonten. */
$me = current_user();
$tabs = [];
if (can('portal.manage')) {
    $tabs += ['appointments' => 'Termine', 'requests' => 'Anträge', 'citizens' => 'Bürgerkonten'];
}
if (can('portal.applications')) {
    $tabs['applications'] = 'Bewerbungen';
}
$tab = array_key_exists($_GET['tab'] ?? '', $tabs) ? $_GET['tab'] : array_key_first($tabs);
$showAll = isset($_GET['all']);

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    $note = mb_substr(trim((string) post('note')), 0, 2000);
    $do = (string) post('do');
    if ($tab === 'appointments' && can('portal.manage') && ($a = one('SELECT * FROM appointments WHERE id = ?', [$id]))) {
        $when = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) post('scheduled_at')) ? str_replace('T', ' ', (string) post('scheduled_at')) . ':00' : null;
        if ($do === 'confirm' && !$when) {
            flash('error', 'Bitte Datum und Uhrzeit für den Termin angeben.');
        } elseif (in_array($do, ['confirm', 'decline', 'done'], true)) {
            update('appointments', $id, ['status' => ['confirm' => 'confirmed', 'decline' => 'declined', 'done' => 'done'][$do], 'scheduled_at' => $when ?? $a['scheduled_at'],
                'location' => mb_substr(trim((string) post('location')), 0, 190) ?: $a['location'], 'staff_note' => $note ?: $a['staff_note'], 'handled_by' => $me['id'], 'handled_at' => now()]);
            audit('portal.appointment', 'appointment', $id, $a['kind'] . ' → ' . $do);
            flash('success', 'Termin aktualisiert.');
        }
    } elseif ($tab === 'requests' && can('portal.manage') && ($r = one('SELECT * FROM portal_requests WHERE id = ?', [$id]))) {
        $status = in_array(post('status'), ['open', 'in_progress', 'done', 'rejected'], true) ? (string) post('status') : $r['status'];
        update('portal_requests', $id, ['status' => $status, 'reply' => $note !== '' ? $note : $r['reply'], 'handled_by' => $me['id'], 'handled_at' => now()]);
        audit('portal.request', 'request', $id, (REQUEST_KINDS[$r['kind']] ?? $r['kind']) . ' → ' . $status);
        flash('success', 'Antrag aktualisiert.');
    } elseif ($tab === 'applications' && can('portal.applications') && ($ap = one('SELECT * FROM applications WHERE id = ?', [$id]))) {
        $status = in_array(post('status'), ['new', 'invited', 'accepted', 'rejected'], true) ? (string) post('status') : $ap['status'];
        update('applications', $id, ['status' => $status, 'staff_note' => $note !== '' ? $note : $ap['staff_note'], 'handled_by' => $me['id'], 'handled_at' => now()]);
        audit('portal.application', 'application', $id, $ap['ic_name'] . ' → ' . $status);
        flash('success', 'Bewerbung aktualisiert.' . ($status === 'accepted' && can('users.create') ? ' Lege jetzt unter Personal ein Konto an.' : ''));
    } elseif ($tab === 'citizens' && can('portal.manage') && ($ci = one('SELECT * FROM citizens WHERE id = ?', [$id]))) {
        if ($do === 'block') {
            update('citizens', $id, ['blocked' => $ci['blocked'] ? 0 : 1]);
            audit('portal.citizen', 'citizen', $id, $ci['discord_name'] . ($ci['blocked'] ? ' entsperrt' : ' gesperrt'));
        } elseif ($do === 'unlink') {
            update('citizens', $id, ['patient_id' => null]);
            audit('portal.citizen', 'citizen', $id, $ci['discord_name'] . ' Verknüpfung gelöst');
        }
    }
    redirect(url('portal_admin', array_filter(['tab' => $tab, 'all' => $showAll ? 1 : null])));
}

$openFilter = fn(string $col, array $open) => $showAll ? '' : " WHERE $col IN ('" . implode("','", $open) . "')";
$dt = fn($v) => $v ? str_replace(' ', 'T', substr((string) $v, 0, 16)) : '';

ob_start(); ?>
<div class="page-head">
    <div><h1>Bürgerportal</h1><p class="muted">Anfragen aus dem Bürgerportal bearbeiten · <a href="portal.php" target="_blank">Portal öffnen</a></p></div>
    <a class="btn" href="<?= e(url('portal_admin', array_filter(['tab' => $tab, 'all' => $showAll ? null : 1]))) ?>"><?= $showAll ? 'Nur offene' : 'Alle anzeigen' ?></a>
</div>
<div class="tabs page-tabs"><?php foreach ($tabs as $k => $l): ?><a class="<?= $tab === $k ? 'active' : '' ?>" href="<?= e(url('portal_admin', ['tab' => $k])) ?>"><?= e($l) ?></a><?php endforeach; ?></div>

<?php if ($tab === 'appointments'):
    $rows = all('SELECT a.*, c.ic_name, c.discord_name, c.phone, p.first_name, p.last_name, p.id AS pid FROM appointments a JOIN citizens c ON c.id = a.citizen_id
        LEFT JOIN patients p ON p.id = a.patient_id' . str_replace('WHERE status', 'WHERE a.status', $openFilter('status', ['requested', 'confirmed'])) . ' ORDER BY a.status = \'requested\' DESC, COALESCE(a.scheduled_at, a.created_at) LIMIT 200'); ?>
    <?php foreach ($rows as $a): ?>
        <div class="card portal-item">
            <div class="card-head"><h2><?= e($a['kind']) ?></h2><?= portal_status_badge($a['status']) ?></div>
            <p><strong><?= e($a['pid'] ? patient_name($a, true) : ($a['ic_name'] ?: $a['discord_name'])) ?></strong>
                <?= $a['pid'] ? ' · <a href="' . e(url('patient', ['id' => $a['pid']])) . '">Akte</a>' : ' <span class="badge grey">nicht verknüpft</span>' ?>
                <?= $a['phone'] ? ' · Tel. ' . e($a['phone']) : '' ?> · Discord: <?= e($a['discord_name']) ?><br>
                <small class="muted">Anfrage vom <?= e(fmt_datetime($a['created_at'])) ?><?= $a['preferred'] ? ' · Wunsch: ' . e($a['preferred']) : '' ?></small></p>
            <?php if ($a['note']): ?><p class="small"><?= nl($a['note']) ?></p><?php endif; ?>
            <?php if (in_array($a['status'], ['requested', 'confirmed'], true)): ?>
            <form method="post" class="form-grid">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <div class="field w-quarter"><label>Termin</label><input type="datetime-local" name="scheduled_at" value="<?= e($dt($a['scheduled_at'])) ?>"></div>
                <div class="field w-quarter"><label>Ort</label><input name="location" value="<?= e($a['location'] ?: org('org_sub')) ?>"></div>
                <div class="field w-half"><label>Hinweis an den Bürger</label><input name="note" value="<?= e($a['staff_note']) ?>" placeholder="z. B. Bitte Ausweis mitbringen"></div>
                <div class="form-actions"><button class="btn small primary" name="do" value="confirm"><?= $a['status'] === 'confirmed' ? 'Termin ändern' : 'Bestätigen' ?></button>
                    <button class="btn small" name="do" value="done">Erledigt</button><button class="btn small danger" name="do" value="decline">Ablehnen</button></div>
            </form>
            <?php else: ?><p class="small muted"><?= $a['scheduled_at'] ? 'Termin war ' . e(fmt_datetime($a['scheduled_at'])) : '' ?><?= $a['staff_note'] ? ' · ' . e($a['staff_note']) : '' ?></p><?php endif; ?>
        </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="card muted">Keine offenen Terminanfragen.</div><?php endif; ?>

<?php elseif ($tab === 'requests'):
    $rows = all('SELECT r.*, c.ic_name, c.discord_name, p.first_name, p.last_name, p.id AS pid FROM portal_requests r JOIN citizens c ON c.id = r.citizen_id
        LEFT JOIN patients p ON p.id = r.patient_id' . str_replace('WHERE status', 'WHERE r.status', $openFilter('status', ['open', 'in_progress'])) . ' ORDER BY r.id DESC LIMIT 200'); ?>
    <?php foreach ($rows as $r): ?>
        <div class="card portal-item">
            <div class="card-head"><h2><?= e(REQUEST_KINDS[$r['kind']] ?? $r['kind']) ?><?= $r['subject'] ? ' – ' . e($r['subject']) : '' ?></h2><?= portal_status_badge($r['status']) ?></div>
            <p><strong><?= e($r['pid'] ? patient_name($r, true) : ($r['ic_name'] ?: $r['discord_name'])) ?></strong><?= $r['pid'] ? ' · <a href="' . e(url('patient', ['id' => $r['pid']])) . '">Akte</a>' . ($r['kind'] === 'aktenkopie' ? ' · <a href="' . e(url('patient_print', ['id' => $r['pid']])) . '" target="_blank">Akte drucken</a>' : '') : '' ?>
                <small class="muted"> · <?= e(fmt_datetime($r['created_at'])) ?></small></p>
            <?php if ($r['text']): ?><div class="small"><?= nl($r['text']) ?></div><?php endif; ?>
            <form method="post" class="form-grid">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <div class="field w-quarter"><label>Status</label><select name="status"><?php foreach (['open', 'in_progress', 'done', 'rejected'] as $st): ?><option value="<?= $st ?>"<?= selected($st, $r['status']) ?>><?= e(PORTAL_STATUS[$st][1]) ?></option><?php endforeach; ?></select></div>
                <div class="field w-three-quarters"><label>Antwort an den Bürger</label><input name="note" value="<?= e($r['reply']) ?>"></div>
                <div class="form-actions"><button class="btn small primary">Speichern</button></div>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="card muted">Keine offenen Anträge.</div><?php endif; ?>

<?php elseif ($tab === 'applications'):
    $rows = all('SELECT a.*, c.discord_name, c.discord_id FROM applications a JOIN citizens c ON c.id = a.citizen_id' . str_replace('WHERE status', 'WHERE a.status', $openFilter('status', ['new', 'invited'])) . ' ORDER BY a.id DESC LIMIT 200'); ?>
    <?php foreach ($rows as $a): ?>
        <div class="card portal-item">
            <div class="card-head"><h2><?= e($a['ic_name']) ?> <small class="muted"><?= (int) $a['age'] ?> Jahre<?= $a['department'] ? ' · ' . e($a['department']) : '' ?></small></h2><?= portal_status_badge($a['status']) ?></div>
            <p class="small">Discord: <strong><?= e($a['discord_name']) ?></strong> <span class="mono muted">(<?= e($a['discord_id']) ?>)</span><?= $a['phone'] ? ' · Tel. ' . e($a['phone']) : '' ?><?= $a['availability'] ? ' · Verfügbar: ' . e($a['availability']) : '' ?> · eingegangen <?= e(fmt_datetime($a['created_at'])) ?></p>
            <?php if ($a['experience']): ?><h3 class="form-heading">Erfahrung</h3><p><?= nl($a['experience']) ?></p><?php endif; ?>
            <h3 class="form-heading">Motivation</h3><p><?= nl($a['motivation']) ?></p>
            <form method="post" class="form-grid">
                <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                <div class="field w-quarter"><label>Status</label><select name="status"><?php foreach (['new', 'invited', 'accepted', 'rejected'] as $st): ?><option value="<?= $st ?>"<?= selected($st, $a['status']) ?>><?= e(PORTAL_STATUS[$st][1]) ?></option><?php endforeach; ?></select></div>
                <div class="field w-three-quarters"><label>Interne Notiz</label><input name="note" value="<?= e($a['staff_note']) ?>"></div>
                <div class="form-actions"><button class="btn small primary">Speichern</button></div>
            </form>
        </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="card muted">Keine offenen Bewerbungen.</div><?php endif; ?>

<?php elseif ($tab === 'citizens'):
    $rows = all('SELECT c.*, p.first_name, p.last_name, p.id AS pid FROM citizens c LEFT JOIN patients p ON p.id = c.patient_id ORDER BY c.last_login DESC LIMIT 300'); ?>
    <div class="card">
        <table class="list compact">
            <thead><tr><th>Discord</th><th>Name im Spiel</th><th>Patientenakte</th><th>Letzte Anmeldung</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $ci): ?>
                <tr class="<?= $ci['blocked'] ? 'inactive' : '' ?>"><td><?= e($ci['discord_name']) ?><br><small class="mono muted"><?= e($ci['discord_id']) ?></small></td>
                    <td><?= e($ci['ic_name']) ?></td>
                    <td><?= $ci['pid'] ? '<a href="' . e(url('patient', ['id' => $ci['pid']])) . '">' . e(patient_name($ci, true)) . '</a>' : '<span class="muted">–</span>' ?></td>
                    <td class="small"><?= e(fmt_datetime($ci['last_login'])) ?></td>
                    <td class="right"><form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $ci['id'] ?>">
                        <?php if ($ci['pid']): ?><button class="btn small" name="do" value="unlink" onclick="return confirm('Verknüpfung lösen?')">Lösen</button><?php endif; ?>
                        <button class="btn small<?= $ci['blocked'] ? '' : ' danger' ?>" name="do" value="block"><?= $ci['blocked'] ? 'Entsperren' : 'Sperren' ?></button></form></td></tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">Noch keine Bürgerkonten.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Bürgerportal';
require SAMS_ROOT . '/app/layout.php';
