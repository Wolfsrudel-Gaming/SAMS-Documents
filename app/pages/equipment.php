<?php
defined('SAMS_ROOT') || exit;
/** Geräte & Prüffristen: Inventar mit Prüfintervall, Prüfprotokoll, Hinweis vor Ablauf. */
$me = current_user();
$manage = can('equipment.manage');
$edit = $manage && (int) ($_GET['edit'] ?? 0) ? fone('equipment', (int) $_GET['edit']) : null;

if (is_post()) {
    csrf_check();
    require_perm('equipment.manage');
    $action = post('action');
    if ($action === 'save') {
        $eq = (int) post('id') ? fone('equipment', (int) post('id')) : null;
        $row = ['name' => mb_substr(trim((string) post('name')), 0, 190), 'category' => in_array(post('category'), EQUIPMENT_CATEGORIES, true) ? post('category') : 'Sonstiges',
            'inventory_no' => mb_substr(trim((string) post('inventory_no')), 0, 64), 'location' => mb_substr(trim((string) post('location')), 0, 190),
            'interval_days' => max(1, min(3650, (int) post('interval_days') ?: 365)), 'next_check' => post_date('next_check'),
            'status' => array_key_exists((string) post('status'), EQUIPMENT_STATUS) ? post('status') : 'ok', 'notes' => mb_substr(trim((string) post('notes')), 0, 2000), 'updated_at' => now()];
        if ($row['name'] === '') {
            flash('error', 'Bitte eine Bezeichnung angeben.');
        } elseif ($eq) {
            update('equipment', (int) $eq['id'], $row + ($row['next_check'] !== $eq['next_check'] ? ['reminded' => 0] : []));
            audit('equipment.update', 'equipment', (int) $eq['id'], $row['name']);
            flash('success', 'Gespeichert.');
        } else {
            $id = insert('equipment', $row + ['active' => 1, 'created_by' => $me['id'], 'created_at' => now()]);
            audit('equipment.create', 'equipment', $id, $row['name']);
            flash('success', 'Gerät angelegt.');
        }
    } elseif ($action === 'check' && ($eq = fone('equipment', (int) post('id')))) {
        $result = post('result') === 'defect' ? 'defect' : 'ok';
        equipment_check($eq, $result, trim((string) post('note')), (string) post('date'), (int) $me['id']);
        audit('equipment.check', 'equipment', (int) $eq['id'], $eq['name'] . ': ' . ($result === 'ok' ? 'ohne Mängel' : 'Mangel') . ' ' . post('note'));
        flash('success', $result === 'ok' ? 'Prüfung eingetragen.' : 'Mangel eingetragen – Gerät als defekt markiert.');
    }
    redirect(url('equipment', array_filter(['filter' => (string) post('filter')])));
}

$filter = in_array($_GET['filter'] ?? '', ['due', 'defect', 'out'], true) ? $_GET['filter'] : '';
$q = trim((string) ($_GET['q'] ?? ''));
$where = fw('e') . ' AND e.active = 1';
$params = [];
$where .= match ($filter) {
    'due' => " AND e.status <> 'out' AND e.next_check <> '' AND e.next_check <= '" . date('Y-m-d', time() + 30 * 86400) . "'",
    'defect' => " AND e.status IN ('defect', 'repair')",
    'out' => " AND e.status = 'out'",
    default => " AND e.status <> 'out'",
};
if ($q !== '') {
    $where .= ' AND (e.name LIKE ? OR e.inventory_no LIKE ? OR e.location LIKE ? OR e.category LIKE ?)';
    array_push($params, like($q), like($q), like($q), like($q));
}
$rows = all("SELECT e.*, (SELECT c.note FROM equipment_checks c WHERE c.equipment_id = e.id ORDER BY c.id DESC LIMIT 1) AS last_note FROM equipment e WHERE $where ORDER BY e.next_check = '', e.next_check, e.name LIMIT 500", $params);
$history = $edit ? all('SELECT c.*, u.name AS user_name FROM equipment_checks c LEFT JOIN users u ON u.id = c.user_id WHERE c.equipment_id = ? ORDER BY c.id DESC LIMIT 30', [$edit['id']]) : [];
$val = fn($k, $d = '') => e((string) ($edit[$k] ?? $d));

ob_start(); ?>
<div class="page-head"><div><h1>Geräte & Prüfungen</h1><p class="muted">Inventar mit Prüffristen (z. B. Atemschutzgeräte, Leitern, Rettungsgerät, Defibrillatoren). Eine Woche vor Ablauf kommt ein Hinweis.</p></div></div>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="equipment">
    <input name="q" value="<?= e($q) ?>" placeholder="Bezeichnung, Inventar-Nr., Standort …">
    <select name="filter" onchange="this.form.submit()"><option value="">Alle im Dienst</option><option value="due"<?= selected('due', $filter) ?>>Prüfung in 30 Tagen fällig</option><option value="defect"<?= selected('defect', $filter) ?>>Defekt / Reparatur</option><option value="out"<?= selected('out', $filter) ?>>Ausgesondert</option></select>
    <button class="btn">Suchen</button>
</form>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Gerät</th><th>Standort</th><th>Nächste Prüfung</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $over = $r['next_check'] !== '' && $r['next_check'] < date('Y-m-d'); ?>
            <tr><td><strong><?= e($r['name']) ?></strong><br><small class="muted"><?= e($r['category']) ?><?= $r['inventory_no'] !== '' ? ' · <span class="mono">' . e($r['inventory_no']) . '</span>' : '' ?></small></td>
                <td class="small"><?= e($r['location'] ?: '–') ?></td>
                <td class="small<?= $over ? ' text-danger' : '' ?>"><?= e(fmt_date($r['next_check']) ?: '–') ?><?= $over ? '<br>überfällig' : '' ?><br><span class="muted">alle <?= (int) $r['interval_days'] ?> Tage</span></td>
                <td><?= status_badge(EQUIPMENT_STATUS, $r['status']) ?><?= $r['last_note'] ? '<br><small class="muted">' . e($r['last_note']) . '</small>' : '' ?></td>
                <td class="right"><?php if ($manage): ?>
                    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="check"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="filter" value="<?= e($filter) ?>">
                        <input name="note" placeholder="Bemerkung" maxlength="190" style="max-width:9rem"><button class="btn small" name="result" value="ok">Geprüft ✓</button><button class="btn small danger" name="result" value="defect">Mangel</button></form>
                    <a class="small" href="<?= e(url('equipment', ['edit' => $r['id']])) ?>">bearbeiten</a>
                <?php endif; ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted">Keine Geräte gefunden.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php if ($manage): ?>
<section>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <h2 class="w-full"><?= $edit ? 'Gerät bearbeiten' : 'Gerät anlegen' ?></h2>
    <div class="field w-full"><label>Bezeichnung</label><input name="name" required maxlength="190" value="<?= $val('name') ?>" placeholder="z. B. Pressluftatmer PA 12"></div>
    <div class="field"><label>Kategorie</label><select name="category"><?php foreach (EQUIPMENT_CATEGORIES as $c): ?><option<?= selected($c, $edit['category'] ?? '') ?>><?= e($c) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Inventar-Nr.</label><input name="inventory_no" maxlength="64" value="<?= $val('inventory_no') ?>"></div>
    <div class="field w-full"><label>Standort / Fahrzeug</label><input name="location" maxlength="190" value="<?= $val('location') ?>" placeholder="z. B. HLF Station 1, Atemschutzwerkstatt"></div>
    <div class="field"><label>Prüfintervall (Tage)</label><input type="number" name="interval_days" min="1" value="<?= $val('interval_days', '365') ?>"></div>
    <div class="field"><label>Nächste Prüfung</label><input type="date" name="next_check" value="<?= $val('next_check') ?>"></div>
    <div class="field"><label>Status</label><select name="status"><?php foreach (EQUIPMENT_STATUS as $k => [, $l]): ?><option value="<?= $k ?>"<?= selected($k, $edit['status'] ?? 'ok') ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field w-full"><label>Notizen</label><textarea name="notes" rows="2"><?= $val('notes') ?></textarea></div>
    <div class="form-actions"><?php if ($edit): ?><a class="btn" href="<?= e(url('equipment')) ?>">Abbrechen</a><?php endif; ?><button class="btn primary">Speichern</button></div>
</form>
<?php if ($history): ?>
<div class="card"><h2>Prüfprotokoll</h2><table class="list compact"><tbody>
    <?php foreach ($history as $h): ?><tr><td class="small"><?= e(fmt_date($h['checked_on'])) ?></td><td><?= $h['result'] === 'ok' ? '<span class="badge green">ohne Mängel</span>' : '<span class="badge red">Mangel</span>' ?></td><td class="small"><?= e($h['note']) ?></td><td class="small muted"><?= e($h['user_name'] ?? '') ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Geräte & Prüfungen';
require SAMS_ROOT . '/app/layout.php';
