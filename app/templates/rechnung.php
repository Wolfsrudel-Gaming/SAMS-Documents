<?php
defined('SAMS_ROOT') || exit;
/** Rechnung / Liquidation */
$recipient = ($d['recipient'] ?? '') !== '' ? $d['recipient'] : '';
$items = (array) ($d['items'] ?? []);
[$net, $tax, $total] = invoice_totals($d);
$taxRate = to_float($d['tax'] ?? 0);
$due = invoice_due($doc, $d);
$paid = !empty($d['paid']) || ($doc['pay_status'] ?? '') === 'paid';
$dunning = ($doc['pay_status'] ?? '') === 'dunned' ? (int) ($doc['dunning_level'] ?? 0) : 0;
$bl = billing_labels();
$jail = array_sum(array_map(fn($it) => (int) ($it['jail'] ?? 0), $items));
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'no_patient_address' => $recipient !== '',
    'info' => [$bl['date'] => fmt_date($d['service_date'] ?? '')] + ($dunning ? ['Mahndatum' => fmt_date($doc['dunned_at'])] : []),
]);
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= $dunning ? $dunning . '. Mahnung' : e($bl['title']) ?></h2>
    <?php if ($dunning): ?><p>Sicher haben Sie übersehen, dass die nachstehende Rechnung vom <?= e(fmt_date($doc['issued_at'])) ?> noch offen ist. Bis heute konnten wir keinen Zahlungseingang feststellen.</p><?php endif; ?>
    <?php if ($p): ?><p class="small"><?= e(term('person')) ?>: <?= e(patient_name($p)) ?>, geb. <?= e(fmt_date($p['dob'] ?? '')) ?><?= ($p['insurance_number'] ?? '') ? ' · Vers.-Nr. ' . e($p['insurance_number']) : '' ?></p><?php endif; ?>
    <p><?= e($bl['intro']) ?></p>
    <table class="tbl invoice">
        <thead><tr><th>Pos.</th><th><?= e($bl['item']) ?></th><th class="right">Anzahl</th><?php if ($jail): ?><th class="right">Haft</th><?php endif; ?><th class="right">Einzelpreis</th><th class="right">Betrag</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i => $it):
            $qty = (float) str_replace(',', '.', (string) ($it['qty'] ?: 1));
            $price = (float) str_replace(',', '.', (string) ($it['price'] ?: 0)); ?>
            <tr><td><?= $i + 1 ?></td><td><?= e($it['text'] ?? '') ?><?php if (trim((string) ($it['reason'] ?? '')) !== ''): ?><br><small class="muted">Begründung: <?= e($it['reason']) ?></small><?php endif; ?></td><td class="right"><?= e(rtrim(rtrim(number_format($qty, 2, ',', ''), '0'), ',')) ?></td><?php if ($jail): ?><td class="right"><?= (int) ($it['jail'] ?? 0) ?: '' ?></td><?php endif; ?>
                <td class="right"><?= e(money($price)) ?></td><td class="right"><?= e(money($qty * $price)) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="<?= $jail ? 5 : 4 ?>" class="right">Nettobetrag</td><td class="right"><?= e(money($net)) ?></td></tr>
            <?php if ($taxRate > 0): ?><tr><td colspan="<?= $jail ? 5 : 4 ?>" class="right">zzgl. <?= e(rtrim(rtrim(number_format($taxRate, 2, ',', ''), '0'), ',')) ?> % Steuer</td><td class="right"><?= e(money($tax)) ?></td></tr><?php endif; ?>
            <?php if ($jail): ?><tr><td colspan="5" class="right">Haft-Einheiten gesamt</td><td class="right"><?= $jail ?></td></tr><?php endif; ?>
            <tr class="sum"><td colspan="<?= $jail ? 5 : 4 ?>" class="right"><?= e($bl['total']) ?></td><td class="right"><?= e(money($total)) ?></td></tr>
        </tfoot>
    </table>
    <?php if ($paid): ?>
        <div class="paid-stamp">BEZAHLT<small><?= e(fmt_date(($doc['paid_at'] ?? '') ?: ($doc['issued_at'] ?? now()))) ?></small></div>
        <p>Der Rechnungsbetrag wurde beglichen. Vielen Dank.</p>
    <?php elseif ($dunning): ?>
        <p>Wir bitten Sie, den offenen Betrag von <strong><?= e(money($total)) ?></strong> bis spätestens <strong><?= e(fmt_date($due)) ?></strong> unter Angabe der Rechnungsnummer
            <strong class="mono"><?= e($doc['number'] ?? '') ?></strong> zu überweisen.<?= $dunning >= 3 ? ' Nach Ablauf dieser Frist übergeben wir die Forderung ohne weitere Ankündigung an das Department of Justice.' : '' ?>
            Sollten Sie die Zahlung inzwischen geleistet haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.</p>
    <?php else: ?>
        <p>Bitte überweisen Sie den Rechnungsbetrag bis zum <strong><?= e(fmt_date($due)) ?></strong> unter Angabe der Rechnungsnummer
            <strong class="mono"><?= e($doc['number'] ?? '') ?></strong> auf das unten genannte Konto.</p>
    <?php endif; ?>
    <?php if (!empty($d['note'])): ?><p class="small"><?= nl($d['note']) ?></p><?php endif; ?>
    <p>Mit freundlichen Grüßen<br><?= e(org('org_name')) ?> – <?= e($bl['office']) ?></p>
    <?= doc_signature($doc, '', 'i. A.', false) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
