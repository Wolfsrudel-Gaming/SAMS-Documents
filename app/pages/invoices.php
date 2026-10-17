<?php
defined('SAMS_ROOT') || exit;
/** Rechnungsübersicht mit Status, Fälligkeit und Summen. */
$status = in_array($_GET['status'] ?? '', ['open', 'paid', 'dunned', 'overdue', 'all'], true) ? $_GET['status'] : 'unpaid';
$q = trim((string) ($_GET['q'] ?? ''));
$where = "d.type = 'rechnung' AND d.status <> 'revoked' AND " . fw('d');
$params = [];
if ($status === 'unpaid' || $status === 'overdue') {
    $where .= " AND d.pay_status IN ('open', 'dunned')";
} elseif ($status !== 'all') {
    $where .= ' AND d.pay_status = ?';
    $params[] = $status;
}
if ($q !== '') {
    $where .= " AND (d.number LIKE ? OR d.patient_snapshot LIKE ?)";
    $params[] = like($q);
    $params[] = like($q);
}
$rows = all("SELECT d.* FROM documents d WHERE $where ORDER BY d.issued_at DESC LIMIT 500", $params);
foreach ($rows as $i => $r) {
    $rows[$i]['due'] = invoice_due($r, json_decode((string) $r['data'], true) ?: []);
    $rows[$i]['p'] = json_decode((string) $r['patient_snapshot'], true) ?: [];
}
if ($status === 'overdue') {
    $rows = array_values(array_filter($rows, fn($r) => $r['due'] < date('Y-m-d')));
}
$sum = fn(string $st) => (int) scalar("SELECT COALESCE(SUM(amount_cents), 0) FROM documents WHERE type = 'rechnung' AND status <> 'revoked' AND pay_status = ? AND " . fw(), [$st]) / 100;
$filters = ['unpaid' => 'Unbezahlt', 'overdue' => 'Überfällig', 'open' => 'Offen', 'dunned' => 'Gemahnt', 'paid' => 'Bezahlt', 'all' => 'Alle'];

ob_start(); ?>
<div class="page-head">
    <div><h1><?= e(term('billing')) ?></h1><p class="muted">Offene Forderungen, Mahnungen und Zahlungseingänge.</p></div>
    <?php if (can('documents.issue.rechnung')): ?><a class="btn primary" href="<?= e(url('document_new', ['type' => 'rechnung'])) ?>"><?= icon('doc-plus') ?><?= e(billing_labels()['title']) ?></a><?php endif; ?>
</div>
<div class="stats">
    <div class="stat"><strong><?= e(money($sum('open'))) ?></strong><span>offen</span></div>
    <div class="stat"><strong><?= e(money($sum('dunned'))) ?></strong><span>gemahnt</span></div>
    <div class="stat"><strong><?= e(money($sum('paid'))) ?></strong><span>bezahlt (gesamt)</span></div>
</div>
<form class="card filter" action="index.php">
    <input type="hidden" name="p" value="invoices">
    <input name="q" value="<?= e($q) ?>" placeholder="Nummer oder Name …">
    <select name="status" onchange="this.form.submit()"><?php foreach ($filters as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $status) ?>><?= $l ?></option><?php endforeach; ?></select>
    <button class="btn">Suchen</button>
</form>
<div class="card">
    <table class="list">
        <thead><tr><th>Nummer</th><th>Empfänger/in</th><th>Datum</th><th>Fällig</th><th class="right">Betrag</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $overdue = $r['pay_status'] !== 'paid' && $r['due'] < date('Y-m-d'); ?>
            <tr onclick="location.href='<?= e(url('document', ['id' => $r['id']])) ?>'">
                <td class="mono"><?= e($r['number']) ?></td>
                <td><?= e($r['p'] ? patient_name($r['p'], true) : '–') ?></td>
                <td class="small"><?= e(fmt_date($r['issued_at'])) ?></td>
                <td class="small<?= $overdue ? ' text-danger' : '' ?>"><?= $r['pay_status'] === 'paid' ? '–' : e(fmt_date($r['due'])) . ($overdue ? ' · überfällig' : '') ?></td>
                <td class="right"><?= e(money($r['amount_cents'] / 100)) ?></td>
                <td><?= pay_status_badge($r) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="muted">Keine Einträge gefunden.</td></tr><?php endif; ?>
        </tbody>
        <?php if ($rows): ?><tfoot><tr><td colspan="4" class="right"><strong>Summe (<?= count($rows) ?>)</strong></td><td class="right"><strong><?= e(money(array_sum(array_column($rows, 'amount_cents')) / 100)) ?></strong></td><td></td></tr></tfoot><?php endif; ?>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = term('billing');
require SAMS_ROOT . '/app/layout.php';
