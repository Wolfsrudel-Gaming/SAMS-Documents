<?php
defined('SAMS_ROOT') || exit;
$q = trim((string) ($_GET['q'] ?? ''));
$typeFilter = (string) ($_GET['type'] ?? '');
$status = (string) ($_GET['status'] ?? '');
$mine = !empty($_GET['mine']);

$where = [fw('d')];
$params = [];
if ($q !== '') {
    $esc = db_driver() === 'mysql' ? '' : " ESCAPE '\\'";
    $name = db_driver() === 'mysql' ? "CONCAT(p.first_name, ' ', p.last_name)" : "(p.first_name || ' ' || p.last_name)";
    $where[] = "(d.number LIKE :q$esc OR d.verify_code LIKE :q$esc OR LOWER($name) LIKE LOWER(:q)$esc)";
    $params['q'] = like($q);
}
if ($typeFilter !== '' && doc_type($typeFilter)) {
    $where[] = 'd.type = :type';
    $params['type'] = $typeFilter;
}
if ($status === 'valid' || $status === 'revoked') {
    $where[] = 'd.status = :status';
    $params['status'] = $status;
}
if ($mine) {
    $where[] = 'd.issued_by = :me';
    $params['me'] = current_user()['id'];
}
$w = implode(' AND ', $where);
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 30;
$total = (int) scalar("SELECT COUNT(*) FROM documents d LEFT JOIN patients p ON p.id = d.patient_id WHERE $w", $params);
$docs = all("SELECT d.*, p.first_name, p.last_name, p.dob, u.name AS issuer_name FROM documents d
    LEFT JOIN patients p ON p.id = d.patient_id LEFT JOIN users u ON u.id = d.issued_by
    WHERE $w ORDER BY d.id DESC LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$pages = max(1, (int) ceil($total / $per));

ob_start(); ?>
<div class="page-head">
    <div><h1>Dokumente</h1><p class="muted"><?= $total ?> Dokument(e)</p></div>
    <?php if (can_issue_any()): ?><a class="btn primary" href="<?= e(url('document_new')) ?>">+ Neues Dokument</a><?php endif; ?>
</div>
<form class="card filter" action="index.php">
    <input type="hidden" name="p" value="documents">
    <input name="q" value="<?= e($q) ?>" placeholder="Dokument-Nr., Prüfcode oder Patient …">
    <select name="type"><option value="">Alle Arten</option>
        <?php foreach (document_types() as $k => $t): ?><option value="<?= e($k) ?>"<?= selected($k, $typeFilter) ?>><?= e($t['short']) ?></option><?php endforeach; ?></select>
    <select name="status"><option value="">Alle Status</option><option value="valid"<?= selected('valid', $status) ?>>Gültig</option><option value="revoked"<?= selected('revoked', $status) ?>>Storniert</option></select>
    <label class="choice single"><input type="checkbox" name="mine" value="1"<?= checked($mine) ?>> nur meine</label>
    <button class="btn">Filtern</button>
</form>
<div class="card">
<table class="list">
    <thead><tr><th>Nummer</th><th>Art</th><th>Patient/in</th><th>Ausgestellt</th><th>Von</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($docs as $d): $t = doc_type($d['type']); ?>
        <tr onclick="location.href='<?= e(url('document', ['id' => $d['id']])) ?>'">
            <td class="mono"><?= e($d['number']) ?></td>
            <td><?= e($t['short'] ?? $d['type']) ?></td>
            <td><?= e(patient_name($d, true) ?: '—') ?></td>
            <td><?= e(fmt_datetime($d['issued_at'])) ?></td>
            <td><?= e($d['issuer_name']) ?></td>
            <td><?= doc_status_badge($d) ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$docs): ?><tr><td colspan="6" class="muted">Keine Dokumente gefunden.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($pages > 1): ?>
<div class="pager">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(url('documents', ['q' => $q, 'type' => $typeFilter, 'status' => $status, 'mine' => $mine ?: null, 'page' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Dokumente';
require SAMS_ROOT . '/app/layout.php';
