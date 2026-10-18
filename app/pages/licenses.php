<?php
defined('SAMS_ROOT') || exit;
/** Lizenzregister: Führerscheine und andere Lizenzen mit Ablauf, Sperre und Entzug. Die Polizei fragt sie im Spiel ab. */
$me = current_user();
$manage = can('licenses.manage');
$types = license_types();

if (is_post()) {
    csrf_check();
    require_perm('licenses.manage');
    $action = post('action');
    if ($action === 'issue') {
        $person = person_from_post();
        $type = in_array(post('type'), $types, true) ? (string) post('type') : '';
        $row = $person + ['type' => $type, 'classes' => mb_substr(trim((string) post('classes')), 0, 64), 'number' => mb_substr(trim((string) post('number')), 0, 32),
            'issued_on' => post_date('issued_on') ?: date('Y-m-d'), 'expires_on' => post_date('expires_on'), 'status' => 'valid', 'note' => mb_substr(trim((string) post('note')), 0, 190),
            'created_by' => $me['id'], 'created_at' => now(), 'updated_by' => $me['id'], 'updated_at' => now()];
        if ($row['name'] === '' || $type === '') {
            flash('error', 'Bitte Person und Lizenzart angeben.');
        } elseif ($row['citizen_id'] !== '' && scalar("SELECT id FROM licenses WHERE citizen_id = ? AND type = ? AND status <> 'revoked' AND " . fw(), [$row['citizen_id'], $type])) {
            flash('error', 'Diese Person hat bereits eine ' . $type . '. Bitte die bestehende verlängern.');
        } else {
            $row['number'] = $row['number'] ?: authority_number('licenses', 'LIZ');
            $id = insert('licenses', $row);
            audit('license.issue', 'license', $id, $type . ' für ' . $row['name']);
            flash('success', $type . ' für ' . $row['name'] . ' eingetragen (' . $row['number'] . ').');
        }
    } elseif ($l = fone('licenses', (int) post('id'))) {
        $note = mb_substr(trim((string) post('note')), 0, 190);
        $row = match ($action) {
            'extend' => post_date('expires_on') ? ['expires_on' => post_date('expires_on'), 'status' => $l['status'] === 'revoked' ? 'revoked' : 'valid', 'suspended_until' => '', 'reminded' => 0] : null,
            'suspend' => ['status' => 'suspended', 'suspended_until' => post_date('until')],
            'revoke' => ['status' => 'revoked'],
            'reinstate' => ['status' => 'valid', 'suspended_until' => ''],
            default => null,
        };
        if ($row === null || ($action !== 'extend' && $note === '' && in_array($action, ['suspend', 'revoke'], true))) {
            flash('error', $action === 'extend' ? 'Bitte ein neues Ablaufdatum angeben.' : 'Bitte einen Grund angeben.');
        } else {
            update('licenses', (int) $l['id'], $row + ['note' => $note !== '' ? $note : $l['note'], 'updated_by' => $me['id'], 'updated_at' => now()]);
            audit('license.' . $action, 'license', (int) $l['id'], $l['type'] . ' ' . $l['name'] . ($note !== '' ? ': ' . $note : ''));
            flash('success', 'Gespeichert.');
        }
    }
    redirect(url('licenses', array_filter(['q' => (string) post('q')])));
}

$q = trim((string) ($_GET['q'] ?? ''));
$filter = in_array($_GET['filter'] ?? '', ['expiring', 'expired', 'suspended', 'revoked'], true) ? $_GET['filter'] : '';
$type = in_array($_GET['type'] ?? '', $types, true) ? $_GET['type'] : '';
$where = fw('l');
$params = [];
if ($q !== '') {
    $where .= ' AND (l.name LIKE ? OR l.citizen_id LIKE ? OR l.number LIKE ?)';
    array_push($params, like($q), like($q), like($q));
}
if ($type !== '') {
    $where .= ' AND l.type = ?';
    $params[] = $type;
}
$where .= match ($filter) {
    'expiring' => " AND l.status = 'valid' AND l.expires_on <> '' AND l.expires_on BETWEEN '" . date('Y-m-d') . "' AND '" . date('Y-m-d', time() + 30 * 86400) . "'",
    'expired' => " AND l.status = 'valid' AND l.expires_on <> '' AND l.expires_on < '" . date('Y-m-d') . "'",
    'suspended' => " AND l.status = 'suspended'",
    'revoked' => " AND l.status = 'revoked'",
    default => '',
};
$rows = all("SELECT l.* FROM licenses l WHERE $where ORDER BY l.updated_at DESC, l.id DESC LIMIT 300", $params);
$records = $manage ? record_options() : [];

ob_start(); ?>
<div class="page-head"><div><h1>Lizenzen</h1><p class="muted">Führerscheine und Lizenzen mit Ablaufdatum. Die Polizei sieht den Stand bei der Personenabfrage.</p></div></div>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="licenses">
    <input name="q" value="<?= e($q) ?>" placeholder="Name, Bürger-ID, Lizenznummer …">
    <select name="type" onchange="this.form.submit()"><option value="">Alle Arten</option><?php foreach ($types as $t): ?><option<?= selected($t, $type) ?>><?= e($t) ?></option><?php endforeach; ?></select>
    <select name="filter" onchange="this.form.submit()"><option value="">Alle</option><option value="expiring"<?= selected('expiring', $filter) ?>>Läuft in 30 Tagen ab</option><option value="expired"<?= selected('expired', $filter) ?>>Abgelaufen</option><option value="suspended"<?= selected('suspended', $filter) ?>>Gesperrt</option><option value="revoked"<?= selected('revoked', $filter) ?>>Entzogen</option></select>
    <button class="btn">Suchen</button>
</form>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Person</th><th>Lizenz</th><th>Gültig bis</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $l): $st = license_state($l); ?>
            <tr><td><?= $l['patient_id'] ? '<a href="' . e(url('patient', ['id' => $l['patient_id']])) . '">' . e($l['name']) . '</a>' : e($l['name']) ?><?= $l['citizen_id'] !== '' ? '<br><small class="mono muted">' . e($l['citizen_id']) . '</small>' : '' ?></td>
                <td><?= e($l['type']) ?><?= $l['classes'] !== '' ? ' <small class="muted">' . e($l['classes']) . '</small>' : '' ?><br><small class="mono muted"><?= e($l['number']) ?></small></td>
                <td class="small"><?= e(fmt_date($l['expires_on']) ?: 'unbefristet') ?><?= $l['status'] === 'suspended' && $l['suspended_until'] !== '' ? '<br>gesperrt bis ' . e(fmt_date($l['suspended_until'])) : '' ?></td>
                <td><?= status_badge(LICENSE_STATUS, $st) ?><?= $l['note'] !== '' ? '<br><small class="muted">' . e($l['note']) . '</small>' : '' ?>
                <?php if ($manage): ?>
                    <details class="inline-details"><summary class="small">Ändern</summary>
                    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $l['id'] ?>"><input type="hidden" name="q" value="<?= e($q) ?>">
                        <input type="date" name="expires_on" title="Neues Ablaufdatum"><button class="btn small" name="action" value="extend">Verlängern</button></form>
                    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $l['id'] ?>"><input type="hidden" name="q" value="<?= e($q) ?>">
                        <input name="note" placeholder="Grund" maxlength="190"><input type="date" name="until" title="Gesperrt bis (optional)">
                        <button class="btn small" name="action" value="suspend">Sperren</button><button class="btn small danger" name="action" value="revoke" onclick="return confirm('Lizenz entziehen?')">Entziehen</button>
                        <?php if ($l['status'] !== 'valid'): ?><button class="btn small" name="action" value="reinstate">Wieder gültig</button><?php endif; ?></form></details>
                <?php endif; ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="4" class="muted">Keine Lizenzen gefunden.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php if ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="issue">
    <h2 class="w-full">Lizenz eintragen</h2>
    <?php if ($records): ?><div class="field w-full"><label>Person (<?= e(term('record')) ?>)</label><select name="patient_id"><option value="">–</option><?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e(patient_name($r, true)) ?><?= $r['citizen_id'] ? ' · ' . e($r['citizen_id']) : '' ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field"><label>oder Name</label><input name="name" maxlength="190"></div>
    <div class="field"><label>Bürger-ID</label><input name="citizen_id" maxlength="64"></div>
    <div class="field"><label>Lizenzart</label><select name="type"><?php foreach ($types as $t): ?><option><?= e($t) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Klassen / Umfang</label><input name="classes" maxlength="64" placeholder="z. B. A, B, C"></div>
    <div class="field"><label>Ausgestellt am</label><input type="date" name="issued_on" value="<?= e(date('Y-m-d')) ?>"></div>
    <div class="field"><label>Gültig bis</label><input type="date" name="expires_on" value="<?= e(date('Y-m-d', strtotime('+1 year'))) ?>"></div>
    <div class="field"><label>Nummer (leer = automatisch)</label><input name="number" maxlength="32"></div>
    <div class="field"><label>Bemerkung</label><input name="note" maxlength="190" placeholder="z. B. Brillenträger/in"></div>
    <div class="form-actions"><button class="btn primary">Eintragen</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Lizenzen';
require SAMS_ROOT . '/app/layout.php';
