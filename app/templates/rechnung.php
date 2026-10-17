<?php
defined('SAMS_ROOT') || exit;
/** Rechnung / Liquidation */
$recipient = ($d['recipient'] ?? '') !== '' ? $d['recipient'] : '';
$items = (array) ($d['items'] ?? []);
$net = 0.0;
foreach ($items as $it) {
    $net += (float) str_replace(',', '.', (string) ($it['qty'] ?? 1)) * (float) str_replace(',', '.', (string) ($it['price'] ?? 0));
}
$taxRate = (float) str_replace(',', '.', (string) ($d['tax'] ?? 0));
$tax = round($net * $taxRate / 100, 2);
$total = $net + $tax;
$due = date('Y-m-d', strtotime(($doc['issued_at'] ?? now()) . ' +' . max(0, (int) ($d['due_days'] ?? 14)) . ' days'));
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'no_patient_address' => $recipient !== '',
    'info' => ['Leistungsdatum' => fmt_date($d['service_date'] ?? '')],
]);
?>
<div class="doc-body">
    <h2 class="doc-subject">Rechnung</h2>
    <?php if ($p): ?><p class="small">Patient/in: <?= e(patient_name($p)) ?>, geb. <?= e(fmt_date($p['dob'] ?? '')) ?><?= ($p['insurance_number'] ?? '') ? ' · Vers.-Nr. ' . e($p['insurance_number']) : '' ?></p><?php endif; ?>
    <p>Für die erbrachten Leistungen erlauben wir uns, wie folgt zu berechnen:</p>
    <table class="tbl invoice">
        <thead><tr><th>Pos.</th><th>Leistung</th><th class="right">Anzahl</th><th class="right">Einzelpreis</th><th class="right">Betrag</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i => $it):
            $qty = (float) str_replace(',', '.', (string) ($it['qty'] ?: 1));
            $price = (float) str_replace(',', '.', (string) ($it['price'] ?: 0)); ?>
            <tr><td><?= $i + 1 ?></td><td><?= e($it['text'] ?? '') ?></td><td class="right"><?= e(rtrim(rtrim(number_format($qty, 2, ',', ''), '0'), ',')) ?></td>
                <td class="right"><?= e(money($price)) ?></td><td class="right"><?= e(money($qty * $price)) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="4" class="right">Nettobetrag</td><td class="right"><?= e(money($net)) ?></td></tr>
            <?php if ($taxRate > 0): ?><tr><td colspan="4" class="right">zzgl. <?= e(rtrim(rtrim(number_format($taxRate, 2, ',', ''), '0'), ',')) ?> % Steuer</td><td class="right"><?= e(money($tax)) ?></td></tr><?php endif; ?>
            <tr class="sum"><td colspan="4" class="right">Rechnungsbetrag</td><td class="right"><?= e(money($total)) ?></td></tr>
        </tfoot>
    </table>
    <?php if (!empty($d['paid'])): ?>
        <div class="paid-stamp">BEZAHLT<small><?= e(fmt_date($doc['issued_at'] ?? now())) ?></small></div>
        <p>Der Rechnungsbetrag wurde bar beglichen. Vielen Dank.</p>
    <?php else: ?>
        <p>Bitte überweisen Sie den Rechnungsbetrag bis zum <strong><?= e(fmt_date($due)) ?></strong> unter Angabe der Rechnungsnummer
            <strong class="mono"><?= e($doc['number'] ?? '') ?></strong> auf das unten genannte Konto.</p>
    <?php endif; ?>
    <?php if (!empty($d['note'])): ?><p class="small"><?= nl($d['note']) ?></p><?php endif; ?>
    <p>Mit freundlichen Grüßen<br><?= e(org('org_name')) ?> – Abrechnungsstelle</p>
    <?= doc_signature($doc, '', 'i. A.', false) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
