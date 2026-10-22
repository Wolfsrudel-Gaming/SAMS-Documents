<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Allgemeines Schreiben als US-Geschäftsbrief im Blockformat (block format): Briefkopf, Datum,
 * Empfängeranschrift (inside address), „Betreff:“, Anrede, Text, Grußformel, Unterschriftsblock.
 * Gleiche Felder wie app/templates/schreiben.php.
 */
$cfg = us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ' COR 1';
}
$recipient = trim((string) ($d['recipient'] ?? ''));
if ($recipient === '' && $p) {
    $recipient = trim(patient_salutation_name($p) . "\n" . ($p['address'] ?? ''));
}
$paras = array_values(array_filter(array_map('trim', preg_split('/\R\s*\R/', trim((string) ($d['body'] ?? ''))) ?: []), fn($s) => $s !== ''));
$admin = doc_admin_issued($doc);
$sigTitle = $admin ? (($u['admin_title'] ?? '') ?: admin_stamp_setting('admin_title')) : (string) ($u['rank_name'] ?? '');
$sigOrg = $admin ? admin_stamp_setting('admin_org_name') : org('org_name');
$refs = array_filter([dt('Ihr Zeichen') => (string) ($d['your_ref'] ?? ''), dt('Unser Zeichen') => (string) ($d['our_ref'] ?? '')], fn($v) => trim($v) !== '');
$accent = ['medical' => 'us-medical', 'police' => 'us-police', 'justice' => 'us-court', 'law' => 'us-pleading', 'fire' => 'us-fire', 'rescue' => 'us-rescue', 'government' => 'us-gov'][faction_kind()] ?? 'us-gov';
echo us_paper_open($doc, $draft, $accent . ' us-gen-letter');
?>
<div class="us-body">
    <header class="us-gen-lh">
        <?= logo_html('us-gen-lh-logo') ?>
        <div class="us-gen-lh-c">
            <div class="us-gen-lh-state"><?= e(mb_strtoupper(dt('Staat San Andreas'))) ?></div>
            <div class="us-gen-lh-org"><?= e(org('org_name')) ?></div>
            <div class="us-gen-lh-sub"><?= e(org('org_sub')) ?></div>
        </div>
        <div class="us-gen-lh-addr"><?= e(org('org_street')) ?><br><?= e(org('org_city')) ?><br><?= e(dt('Tel.')) ?> <?= e(org('phone')) ?><?= org('email') !== '' ? '<br>' . e(org('email')) : '' ?><?= org('web') !== '' ? '<br>' . e(org('web')) : '' ?></div>
    </header>
    <div class="us-gen-letter-body">
        <div class="us-gen-date"><?= e(fmt_date($doc['issued_at'] ?? now())) ?></div>
        <?php if ($refs): ?><div class="us-gen-refs"><?php foreach ($refs as $k => $v): ?><span><?= e($k) ?>: <?= e($v) ?></span><?php endforeach; ?></div><?php endif; ?>
        <div class="us-gen-inside"><?= $recipient !== '' ? nl($recipient) : '&nbsp;' ?></div>
        <div class="us-gen-subject"><b><?= e(dt('Betreff:')) ?></b> <?= e($d['subject'] ?? '') ?></div>
        <?php if ($p): ?><div class="us-gen-subject"><b><?= e(dt('Betrifft:')) ?></b> <?= e(patient_name($p)) ?>, <?= e(dt('geb. {date}', ['date' => fmt_date($p['dob'] ?? '')])) ?></div><?php endif; ?>
        <p class="us-gen-salute"><?= e(dt((string) ($d['salutation'] ?? ''))) ?></p>
        <div class="us-gen-prose">
            <?php foreach ($paras as $para): ?><p><?= nl($para) ?></p><?php endforeach; ?>
        </div>
        <p class="us-gen-close"><?= e(dt((string) ($d['closing'] ?? 'Mit freundlichen Grüßen'))) ?></p>
        <div class="us-gen-sigblock">
            <div class="us-gen-sigarea">
                <?php if (!empty($d['seal'])): ?><div class="sig-stamp round us-gen-seal"><?= seal_svg($doc) ?></div><?php endif; ?>
                <?= signature_html($u, doc_rand($doc, 'ussig', -4, 3)) ?>
            </div>
            <div class="us-gen-signame">
                <b><?= e(user_display($u)) ?></b>
                <?php if ($sigTitle !== ''): ?><br><?= e($sigTitle) ?><?php endif; ?>
                <?php if (($u['department'] ?? '') !== '' && !$admin): ?><br><?= e($u['department']) ?><?php endif; ?>
                <br><?= e($sigOrg) ?>
            </div>
            <?php if (!empty($u['cosigner'])): $c = $u['cosigner']; ?>
            <div class="us-gen-cosign">
                <div class="us-gen-sigarea"><?= signature_html($c, doc_rand($doc, 'uscosig', -3, 3)) ?></div>
                <div class="us-gen-signame"><b><?= e(user_display($c)) ?></b><?= !empty($c['rank_name']) ? '<br>' . e($c['rank_name']) : '' ?><br><span class="us-gen-small"><?= e(dt('Gegengezeichnet / freigegeben am {date}', ['date' => fmt_date($c['at'] ?? '')])) ?></span></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
