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
$num = fn(float $n) => doc_us() ? rtrim(rtrim(number_format($n, 2, '.', ','), '0'), '.') : rtrim(rtrim(number_format($n, 2, ',', ''), '0'), ',');
$jail = array_sum(array_map(fn($it) => (int) ($it['jail'] ?? 0), $items));
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'no_patient_address' => $recipient !== '',
    'info' => [$bl['date'] => fmt_date($d['service_date'] ?? '')] + ($dunning ? [dt('Mahndatum') => fmt_date($doc['dunned_at'])] : []),
]);
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= $dunning ? e(dt('{n}. Mahnung', ['n' => $dunning])) : e($bl['title']) ?></h2>
    <?php if ($dunning): ?><p><?= e(dt('Sicher haben Sie übersehen, dass die nachstehende Rechnung vom {date} noch offen ist. Bis heute konnten wir keinen Zahlungseingang feststellen.', ['date' => fmt_date($doc['issued_at'])])) ?></p><?php endif; ?>
    <?php if ($p): ?><p class="small"><?= e(dt(term('person'))) ?>: <?= e(patient_name($p)) ?>, <?= e(dt('geb. {date}', ['date' => fmt_date($p['dob'] ?? '')])) ?><?= ($p['insurance_number'] ?? '') ? ' · ' . e(dt('Vers.-Nr. {number}', ['number' => $p['insurance_number']])) : '' ?></p><?php endif; ?>
    <p><?= e($bl['intro']) ?></p>
    <table class="tbl invoice">
        <thead><tr><th><?= e(dt('Pos.')) ?></th><th><?= e($bl['item']) ?></th><th class="right"><?= e(dt('Anzahl')) ?></th><?php if ($jail): ?><th class="right"><?= e(dt('Haft')) ?></th><?php endif; ?><th class="right"><?= e(dt('Einzelpreis')) ?></th><th class="right"><?= e(dt('Betrag')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($items as $i => $it):
            $qty = (float) str_replace(',', '.', (string) ($it['qty'] ?: 1));
            $price = (float) str_replace(',', '.', (string) ($it['price'] ?: 0)); ?>
            <tr><td><?= $i + 1 ?></td><td><?= e($it['text'] ?? '') ?><?php if (trim((string) ($it['reason'] ?? '')) !== ''): ?><br><small class="muted"><?= e(dt('Begründung:')) ?> <?= e($it['reason']) ?></small><?php endif; ?></td><td class="right"><?= e($num($qty)) ?></td><?php if ($jail): ?><td class="right"><?= (int) ($it['jail'] ?? 0) ?: '' ?></td><?php endif; ?>
                <td class="right"><?= e(money($price)) ?></td><td class="right"><?= e(money($qty * $price)) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="<?= $jail ? 5 : 4 ?>" class="right"><?= e(dt('Nettobetrag')) ?></td><td class="right"><?= e(money($net)) ?></td></tr>
            <?php if ($taxRate > 0): ?><tr><td colspan="<?= $jail ? 5 : 4 ?>" class="right"><?= e(dt('zzgl. {rate} % Steuer', ['rate' => $num($taxRate)])) ?></td><td class="right"><?= e(money($tax)) ?></td></tr><?php endif; ?>
            <?php if ($jail): ?><tr><td colspan="5" class="right"><?= e(dt('Haft-Einheiten gesamt')) ?></td><td class="right"><?= $jail ?></td></tr><?php endif; ?>
            <tr class="sum"><td colspan="<?= $jail ? 5 : 4 ?>" class="right"><?= e($bl['total']) ?></td><td class="right"><?= e(money($total)) ?></td></tr>
        </tfoot>
    </table>
    <?php if ($paid): ?>
        <div class="paid-stamp"><?= e(dt('BEZAHLT')) ?><small><?= e(fmt_date(($doc['paid_at'] ?? '') ?: ($doc['issued_at'] ?? now()))) ?></small></div>
        <p><?= e(dt('Der Rechnungsbetrag wurde beglichen. Vielen Dank.')) ?></p>
    <?php elseif ($dunning): ?>
        <p><?= e(dt('Wir bitten Sie, den offenen Betrag von')) ?> <strong><?= e(money($total)) ?></strong> <?= e(dt('bis spätestens')) ?> <strong><?= e(fmt_date($due)) ?></strong> <?= e(dt('unter Angabe der Rechnungsnummer')) ?>
            <strong class="mono"><?= e($doc['number'] ?? '') ?></strong> <?= e(dt('zu überweisen.')) ?><?= $dunning >= 3 ? ' ' . e(dt('Nach Ablauf dieser Frist übergeben wir die Forderung ohne weitere Ankündigung an das Department of Justice.')) : '' ?>
            <?= e(dt('Sollten Sie die Zahlung inzwischen geleistet haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.')) ?></p>
    <?php else: ?>
        <p><?= e(dt('Bitte überweisen Sie den Rechnungsbetrag bis zum')) ?> <strong><?= e(fmt_date($due)) ?></strong> <?= e(dt('unter Angabe der Rechnungsnummer')) ?>
            <strong class="mono"><?= e($doc['number'] ?? '') ?></strong> <?= e(dt('auf das unten genannte Konto.')) ?></p>
    <?php endif; ?>
    <?php if (!empty($d['note'])): ?><p class="small"><?= nl($d['note']) ?></p><?php endif; ?>
    <p><?= e(dt('Mit freundlichen Grüßen')) ?><br><?= e(org('org_name')) ?> – <?= e($bl['office']) ?></p>
    <?= doc_signature($doc, '', dt('i. A.'), false) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
