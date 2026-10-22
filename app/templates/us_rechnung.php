<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Rechnung (Invoice) – bei Polizei, Justiz und Behörde als Zahlungsaufforderung im Stil einer
 * „Notice of Fine / Citation payment notice“. Rechnungsempfänger, Positionen, Zwischensumme/Steuer/Gesamt,
 * Zahlungsbedingungen und Zahlungsabschnitt zum Abtrennen. Gleiche Felder und Logik wie app/templates/rechnung.php.
 */
$recipient = trim((string) ($d['recipient'] ?? ''));
$items = array_values(array_filter((array) ($d['items'] ?? []), fn($it) => is_array($it) && (trim((string) ($it['text'] ?? '')) !== '' || to_float($it['price'] ?? 0) != 0)));
[$net, $tax, $total] = invoice_totals($d);
$taxRate = to_float($d['tax'] ?? 0);
$due = invoice_due($doc, $d);
$days = max(0, (int) ($d['due_days'] ?? 14));
$paid = !empty($d['paid']) || ($doc['pay_status'] ?? '') === 'paid';
$dunning = ($doc['pay_status'] ?? '') === 'dunned' ? (int) ($doc['dunning_level'] ?? 0) : 0;
$bl = billing_labels();
$kind = faction_kind();
$notice = in_array($kind, ['police', 'justice', 'government'], true); // Bescheid statt Rechnung
$num = fn(float $n) => rtrim(rtrim(number_format($n, 2, '.', ','), '0'), '.');
$jail = array_sum(array_map(fn($it) => (int) ($it['jail'] ?? 0), $items));
$number = doc_code_display((string) ($doc['number'] ?? ''));
$balance = $paid ? 0.0 : $total;
$billTo = $recipient !== '' ? $recipient : ($p ? trim(patient_name($p) . "\n" . ($p['address'] ?? '')) : '');
$payer = $billTo !== '' ? (string) preg_split('/\R/', $billTo)[0] : '';
$noLabel = $notice ? dt('Bescheid-Nr.') : dt('Rechnungs-Nr.');
$cfg = us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ($notice ? ' FIN 215' : ' FIN 100');
}
$cols = 4 + ($jail ? 1 : 0);
echo us_paper_open($doc, $draft, 'us-gen-inv' . ($notice ? ' us-gen-notice us-' . (['police' => 'police', 'justice' => 'court'][$kind] ?? 'gov') : ' us-hr'));
?>
<div class="us-body">
<?php if ($notice): ?>
    <?= us_header($doc, ['style' => $kind === 'police' ? 'police' : 'gov', 'title' => $bl['title'], 'banner' => $dunning ? dt('{n}. Mahnung – Zahlung überfällig', ['n' => $dunning]) : '',
        'subtitle' => ['police' => 'Zahlungsaufforderung (Notice of Fine / Citation)', 'justice' => 'Zahlungsaufforderung (Statement of Court Costs)', 'government' => 'Zahlungsaufforderung (Notice of Fees Due)'][$kind]] + $cfg, $type) ?>
    <div class="us-sec"><?= e(mb_strtoupper(dt('Zahlungspflichtige Person'))) ?></div>
    <div class="us-grid">
        <?php if ($p): ?>
            <div class="us-cell w6"><span class="fl"><?= e(dt('Name, Vorname')) ?></span><span class="fv"><b><?= e(patient_name($p, true)) ?></b></span></div>
            <div class="us-cell w3"><span class="fl"><?= e(dt('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
            <div class="us-cell w3"><span class="fl"><?= e(dt('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
            <?php if (($p['address'] ?? '') !== ''): ?><div class="us-cell w12"><span class="fl"><?= e(dt('Anschrift')) ?></span><span class="fv"><?= e(str_replace("\n", ', ', (string) $p['address'])) ?></span></div><?php endif; ?>
        <?php endif; ?>
        <?php if ($recipient !== '' || !$p): ?>
            <div class="us-cell w12"><span class="fl"><?= e(dt('Bescheidempfänger/in')) ?></span><span class="fv"><?= $recipient !== '' ? nl($recipient) : '—' ?></span></div>
        <?php endif; ?>
    </div>
    <div class="us-sec"><?= e(mb_strtoupper(dt('Angaben zum Bescheid'))) ?></div>
    <div class="us-grid">
        <div class="us-cell w3"><span class="fl"><?= e($noLabel) ?></span><span class="fv mono"><?= e($number) ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($bl['date']) ?></span><span class="fv"><?= e(fmt_date($d['service_date'] ?? '')) ?: '—' ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e(dt('Ausgestellt am')) ?></span><span class="fv"><?= e(fmt_date($doc['issued_at'] ?? now())) ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e(dt('Zahlbar bis')) ?></span><span class="fv"><b><?= e(fmt_date($due)) ?></b></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($bl['total']) ?></span><span class="fv"><b><?= e(money($total)) ?></b></span></div>
    </div>
    <?php if ($dunning): ?><p class="us-gen-lead"><?= e(dt('Sicher haben Sie übersehen, dass die nachstehende Rechnung vom {date} noch offen ist. Bis heute konnten wir keinen Zahlungseingang feststellen.', ['date' => fmt_date($doc['issued_at'] ?? '')])) ?></p><?php endif; ?>
    <p class="us-gen-lead"><?= e($bl['intro']) ?></p>
<?php else: ?>
    <header class="us-gen-inv-head">
        <div class="us-gen-inv-org">
            <?= logo_html('us-gen-inv-logo') ?>
            <div><b><?= e(org('org_name')) ?></b><br><?= e(org('org_sub')) ?><br><?= e(org('org_street')) ?> · <?= e(org('org_city')) ?><br><?= e(dt('Tel.')) ?> <?= e(org('phone')) ?><?= org('email') !== '' ? ' · ' . e(org('email')) : '' ?></div>
        </div>
        <div class="us-gen-inv-box">
            <h1><?= e(mb_strtoupper($bl['title'])) ?></h1>
            <table class="us-gen-kv">
                <tr><th><?= e($noLabel) ?></th><td class="mono"><?= e($number) ?></td></tr>
                <tr><th><?= e(dt('Rechnungsdatum')) ?></th><td><?= e(fmt_date($doc['issued_at'] ?? now())) ?></td></tr>
                <tr><th><?= e($bl['date']) ?></th><td><?= e(fmt_date($d['service_date'] ?? '')) ?: '—' ?></td></tr>
                <?php if ($dunning): ?><tr><th><?= e(dt('Mahndatum')) ?></th><td><?= e(fmt_date($doc['dunned_at'] ?? '')) ?></td></tr><?php endif; ?>
                <tr><th><?= e(dt('Fällig am')) ?></th><td><b><?= e(fmt_date($due)) ?></b></td></tr>
            </table>
        </div>
    </header>
    <?php if ($dunning): ?><div class="us-banner"><?= e(mb_strtoupper(dt('{n}. Mahnung – Zahlung überfällig', ['n' => $dunning]))) ?></div><?php endif; ?>
    <div class="us-gen-parties">
        <div class="us-gen-party"><span class="fl"><?= e(dt('Rechnung an (Bill to)')) ?></span><div class="us-gen-party-v"><?= $billTo !== '' ? nl($billTo) : '—' ?></div></div>
        <?php if ($p): ?>
            <div class="us-gen-party"><span class="fl"><?= e(dt(term('person'))) ?></span><div class="us-gen-party-v"><b><?= e(patient_name($p)) ?></b><br><?= e(dt('geb. {date}', ['date' => fmt_date($p['dob'] ?? '')])) ?><?= ($p['citizen_id'] ?? '') !== '' ? '<br>' . e(dt('Bürger-ID')) . ' <span class="mono">' . e($p['citizen_id']) . '</span>' : '' ?><?= ($p['insurance_number'] ?? '') !== '' ? '<br>' . e(dt('Vers.-Nr. {number}', ['number' => $p['insurance_number']])) : '' ?></div></div>
        <?php endif; ?>
        <div class="us-gen-party us-gen-amount"><span class="fl"><?= e(dt('Fälliger Betrag')) ?></span><b><?= e(money($balance)) ?></b><small><?= $paid ? e(dt('BEZAHLT')) : e(dt('Zahlbar bis {date}', ['date' => fmt_date($due)])) ?></small></div>
    </div>
    <?php if ($dunning): ?><p class="us-gen-lead"><?= e(dt('Sicher haben Sie übersehen, dass die nachstehende Rechnung vom {date} noch offen ist. Bis heute konnten wir keinen Zahlungseingang feststellen.', ['date' => fmt_date($doc['issued_at'] ?? '')])) ?></p><?php endif; ?>
    <p class="us-gen-lead"><?= e($bl['intro']) ?></p>
<?php endif; ?>
    <table class="us-gen-items">
        <thead><tr><th class="c"><?= e(dt('Pos.')) ?></th><th><?= e($bl['item']) ?></th><th class="r"><?= e(dt('Anzahl')) ?></th><?php if ($jail): ?><th class="r"><?= e(dt('Haft-Einheiten')) ?></th><?php endif; ?><th class="r"><?= e(dt('Einzelpreis')) ?></th><th class="r"><?= e(dt('Betrag')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($items as $i => $it):
            $qty = to_float(($it['qty'] ?? '') !== '' ? $it['qty'] : 1);
            $price = to_float($it['price'] ?? 0); ?>
            <tr>
                <td class="c"><?= $i + 1 ?></td>
                <td><?= e($it['text'] ?? '') ?><?php if (trim((string) ($it['reason'] ?? '')) !== ''): ?><br><small class="us-gen-muted"><?= e(dt('Begründung:')) ?> <?= e($it['reason']) ?></small><?php endif; ?></td>
                <td class="r"><?= e($num($qty)) ?></td>
                <?php if ($jail): ?><td class="r"><?= (int) ($it['jail'] ?? 0) ?: '' ?></td><?php endif; ?>
                <td class="r"><?= e(money($price)) ?></td>
                <td class="r"><?= e(money($qty * $price)) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php for ($i = count($items); $i < 4; $i++): ?><tr class="us-gen-empty"><td class="c">&nbsp;</td><td></td><td></td><?php if ($jail): ?><td></td><?php endif; ?><td></td><td></td></tr><?php endfor; ?>
        </tbody>
    </table>
    <div class="us-gen-sumrow">
        <div class="us-gen-terms">
            <b><?= e($notice ? dt('Hinweise zur Zahlung') : dt('Zahlungsbedingungen')) ?></b>
            <?php if ($paid): ?>
                <div class="us-gen-paid"><?= e(dt('BEZAHLT')) ?><small><?= e(fmt_date(($doc['paid_at'] ?? '') ?: ($doc['issued_at'] ?? now()))) ?></small></div>
                <p><?= e(dt('Der Rechnungsbetrag wurde beglichen. Vielen Dank.')) ?></p>
            <?php elseif ($dunning): ?>
                <p><?= e(dt('Bitte begleichen Sie den offenen Betrag von {amount} bis spätestens {date} unter Angabe der Nummer {number}.', ['amount' => money($total), 'date' => fmt_date($due), 'number' => $number])) ?><?= $dunning >= 3 ? ' ' . e(dt('Nach Ablauf dieser Frist übergeben wir die Forderung ohne weitere Ankündigung an das Department of Justice.')) : '' ?></p>
                <p><?= e(dt('Sollten Sie die Zahlung inzwischen geleistet haben, betrachten Sie dieses Schreiben bitte als gegenstandslos.')) ?></p>
            <?php elseif ($notice): ?>
                <p><?= e(dt('Zahlen Sie den festgesetzten Betrag bis zum {date} unter Angabe der Bescheid-Nr. {number}.', ['date' => fmt_date($due), 'number' => $number])) ?></p>
                <?php if ($kind === 'police'): ?><p><?= e(dt('Wenn Sie den Bescheid nicht anerkennen, können Sie innerhalb der Zahlungsfrist schriftlich Einspruch einlegen und eine gerichtliche Anhörung (Court Hearing) beantragen.')) ?></p><?php endif; ?>
                <?php if ($kind === 'government'): ?><p><?= e(dt('Gegen diesen Bescheid kann innerhalb der Zahlungsfrist schriftlich Widerspruch eingelegt werden.')) ?></p><?php endif; ?>
                <p><?= e(dt('Bei nicht fristgerechter Zahlung können Mahngebühren und weitere Vollstreckungsmaßnahmen folgen.')) ?></p>
            <?php else: ?>
                <p><?= e(dt('Zahlbar innerhalb von {days} Tagen netto (Net {days}) bis zum {date}.', ['days' => $days, 'date' => fmt_date($due)])) ?> <?= e(dt('Bitte geben Sie bei der Zahlung die Nummer {number} an.', ['number' => $number])) ?></p>
            <?php endif; ?>
            <?php if (!$paid && org('bank') !== ''): ?><p class="us-gen-small"><?= e(dt('Bankverbindung')) ?>: <?= e(org('bank')) ?><?= org('iban') !== '' ? ' · ' . e(dt('Konto-Nr.')) . ' ' . e(org('iban')) : '' ?></p><?php endif; ?>
            <?php if (!empty($d['note'])): ?><p class="us-gen-small"><?= nl($d['note']) ?></p><?php endif; ?>
        </div>
        <table class="us-gen-totals">
            <tr><th><?= e(dt('Zwischensumme')) ?></th><td><?= e(money($net)) ?></td></tr>
            <?php if ($taxRate > 0): ?><tr><th><?= e(dt('Steuer ({rate} %)', ['rate' => $num($taxRate)])) ?></th><td><?= e(money($tax)) ?></td></tr><?php endif; ?>
            <?php if ($jail): ?><tr><th><?= e(dt('Haft-Einheiten gesamt')) ?></th><td><?= $jail ?></td></tr><?php endif; ?>
            <tr class="us-gen-total"><th><?= e($bl['total']) ?></th><td><?= e(money($total)) ?></td></tr>
            <?php if ($paid): ?><tr><th><?= e(dt('Bereits bezahlt')) ?></th><td>−<?= e(money($total)) ?></td></tr><?php endif; ?>
            <tr class="us-gen-balance"><th><?= e(dt('Offener Betrag')) ?></th><td><?= e(money($balance)) ?></td></tr>
        </table>
    </div>
    <?php if (!$notice && in_array($kind, ['medical', 'law'], true) && !$dunning): ?><p class="us-gen-thanks"><?= e(dt('Vielen Dank für Ihr Vertrauen.')) ?></p><?php endif; ?>
    <?= us_signatures($doc, ['seal' => $notice ? 'round' : '', 'sig_caption' => $bl['office']] + $cfg) ?>
    <div class="us-gen-stub">
        <div class="us-gen-cut"><span>✂</span> <?= e(dt('Bitte hier abtrennen und zusammen mit Ihrer Zahlung einsenden')) ?></div>
        <div class="us-gen-stub-row">
            <div class="us-gen-stub-to">
                <span class="fl"><?= e(dt('Zahlungsabschnitt – Zahlung an')) ?></span>
                <b><?= e(org('org_name')) ?></b><br><?= e($bl['office']) ?><br><?= e(org('org_street')) ?><br><?= e(org('org_city')) ?>
            </div>
            <table class="us-gen-kv us-gen-stub-kv">
                <tr><th><?= e($noLabel) ?></th><td class="mono"><?= e($number) ?></td></tr>
                <tr><th><?= e(dt('Zahlungspflichtig')) ?></th><td><?= e($payer) ?: '—' ?></td></tr>
                <tr><th><?= e(dt('Fällig am')) ?></th><td><?= e(fmt_date($due)) ?></td></tr>
                <tr><th><?= e(dt('Fälliger Betrag')) ?></th><td><b><?= e(money($balance)) ?></b></td></tr>
                <tr><th><?= e(dt('Gezahlter Betrag')) ?></th><td class="us-gen-fill"><?= e(setting('currency', '$')) ?></td></tr>
            </table>
            <div class="us-gen-stub-bc"><?= barcode_svg($number !== '' ? $number : 'ENTWURF', 26) ?><span class="mono"><?= e($number) ?></span></div>
        </div>
    </div>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
