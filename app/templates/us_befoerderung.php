<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Beförderungs-/Ernennungsurkunde als „Certificate of Promotion“ – Zierrahmen mit Eckrosetten,
 * Staatszeile, Urkundentext im US-Stil, Unterschriften und goldenes Siegel. Gleiche Felder wie app/templates/befoerderung.php.
 */
$emp = $d['_employee'] ?? [];
$kinds = [
    'befoerderung' => ['Beförderungsurkunde', 'in Anerkennung erwiesener Leistung, Eignung und Befähigung befördert wird zum / zur', 'mit Wirkung vom {date}.'],
    'ernennung'    => ['Ernennungsurkunde', 'im Vertrauen auf Pflichtbewusstsein, Eignung und Befähigung ernannt wird zum / zur', 'mit Wirkung vom {date}.'],
    'auszeichnung' => ['Ehrenurkunde', 'für herausragende Verdienste ausgezeichnet wird für', 'Verliehen am {date}.'],
    'jubilaeum'    => ['Urkunde zum Dienstjubiläum', 'in Würdigung treuer Dienste geehrt wird für', 'Verliehen am {date}.'],
];
[$heading, $text, $from] = $kinds[$d['kind'] ?? 'befoerderung'] ?? $kinds['befoerderung'];
$signer2 = trim((string) ($d['signer2'] ?? ''));
$petals = '';
for ($i = 0; $i < 12; $i++) {
    $petals .= '<ellipse cx="0" cy="-10" rx="2.6" ry="6" transform="rotate(' . ($i * 30) . ')"/>';
}
$rosette = '<svg class="us-gen-rosette" viewBox="-20 -20 40 40" xmlns="http://www.w3.org/2000/svg"><g fill="none" stroke="currentColor" stroke-width=".9"><circle r="18.5"/><circle r="16.5" stroke-dasharray="1.2 1.4"/>' . $petals . '<circle r="3.2" fill="currentColor"/></g></svg>';
echo us_paper_open($doc, $draft, 'us-certificate us-gen-cert');
?>
<div class="us-gen-cert-frame">
    <span class="us-gen-corner tl"><?= $rosette ?></span><span class="us-gen-corner tr"><?= $rosette ?></span><span class="us-gen-corner bl"><?= $rosette ?></span><span class="us-gen-corner br"><?= $rosette ?></span>
    <div class="us-gen-cert-inner">
        <?= logo_html('us-gen-cert-logo') ?>
        <div class="us-gen-cert-state"><?= e(mb_strtoupper(dt('Staat San Andreas'))) ?></div>
        <div class="us-gen-cert-org"><?= e(mb_strtoupper(org('org_name'))) ?></div>
        <div class="us-gen-cert-orgsub"><?= e(org('org_sub')) ?><?= org('org_sub') !== '' ? ' · ' : '' ?><?= e(org('city')) ?></div>
        <h1 class="us-gen-cert-title"><?= e(dt($heading)) ?></h1>
        <div class="us-gen-cert-rule"><span>★</span></div>
        <p class="us-gen-cert-greet"><?= e(dt('An alle, die diese Urkunde sehen – Gruß zuvor.')) ?></p>
        <p class="us-gen-cert-text"><?= e(dt('Es wird hiermit bekundet, dass')) ?></p>
        <div class="us-gen-cert-name"><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?: '&nbsp;' ?></div>
        <?php if (!empty($emp['badge_number'])): ?><div class="us-gen-cert-small"><?= e(dt('Dienstnummer {number}', ['number' => $emp['badge_number']])) ?></div><?php endif; ?>
        <p class="us-gen-cert-text"><?= e(dt($text)) ?></p>
        <div class="us-gen-cert-rank"><?= e($d['new_rank'] ?? '') ?: '&nbsp;' ?></div>
        <p class="us-gen-cert-text"><?= e(dt($from, ['date' => fmt_date($d['effective'] ?? '')])) ?></p>
        <?php if (!empty($d['laudation'])): ?><p class="us-gen-cert-laud"><?= nl($d['laudation']) ?></p><?php endif; ?>
        <p class="us-gen-cert-witness"><?= e(dt('Zu Urkund dessen wurde diese Urkunde eigenhändig unterzeichnet und mit dem Dienstsiegel versehen.')) ?></p>
        <p class="us-gen-cert-given"><?= e(dt('Ausgefertigt in {city} am {date}.', ['city' => org('city'), 'date' => fmt_date($doc['issued_at'] ?? now())])) ?></p>
        <div class="us-gen-cert-signs<?= $signer2 !== '' ? ' two' : '' ?>">
            <div class="us-gen-cert-sign">
                <div class="us-gen-cert-sigarea"><?= signature_html($u, doc_rand($doc, 'csig', -3, 2)) ?></div>
                <span class="us-gen-cert-line"></span>
                <b><?= e(user_display($u)) ?></b><small><?= e($u['rank_name'] ?? '') ?></small>
            </div>
            <div class="us-gen-cert-seal"><?= seal_svg($doc, '#9a7b1c') ?><span class="us-gen-ribbon l"></span><span class="us-gen-ribbon r"></span></div>
            <?php if ($signer2 !== ''): ?>
            <div class="us-gen-cert-sign">
                <div class="us-gen-cert-sigarea"></div>
                <span class="us-gen-cert-line"></span>
                <b><?= e($signer2) ?></b>
            </div>
            <?php endif; ?>
        </div>
        <div class="us-gen-cert-verify"><?= qr_html($doc, 'qr us-gen-cert-qr') ?><span class="mono"><?= e(doc_code_display((string) ($doc['number'] ?? ''))) ?> · <?= e(dt('Prüfcode')) ?> <?= e(chunk_split(doc_code_display((string) ($doc['verify_code'] ?? '')), 4, ' ')) ?></span></div>
    </div>
</div>
<?= a4_close() ?>
