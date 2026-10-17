<?php
defined('SAMS_ROOT') || exit;
/** Beförderungs-/Ernennungsurkunde – A4 hoch mit Zierrahmen und Siegel */
$emp = $d['_employee'] ?? [];
$kinds = [
    'befoerderung' => ['Beförderungsurkunde', 'befördere ich', 'mit Wirkung vom'],
    'ernennung'    => ['Ernennungsurkunde', 'ernenne ich', 'mit Wirkung vom'],
    'auszeichnung' => ['Ehrenurkunde', 'zeichne ich', 'am'],
    'jubilaeum'    => ['Urkunde', 'ehre ich', 'am'],
];
[$heading, $verb, $from] = $kinds[$d['kind'] ?? 'befoerderung'] ?? $kinds['befoerderung'];
$signer2 = trim((string) ($d['signer2'] ?? ''));
?>
<section class="paper a4 certificate">
    <?= doc_watermark($doc, $draft) ?>
    <div class="cert-frame">
        <span class="cert-corner tl"></span><span class="cert-corner tr"></span><span class="cert-corner bl"></span><span class="cert-corner br"></span>
        <div class="cert-inner">
            <?= logo_html('cert-logo') ?>
            <div class="cert-org"><?= e(org('org_name')) ?></div>
            <div class="cert-orgsub"><?= e(org('org_sub')) ?> · <?= e(org('city')) ?></div>
            <h1 class="cert-title"><?= e($heading) ?></h1>
            <p class="cert-text">Im Namen der <?= e(org('org_name')) ?><br><?= e($verb) ?></p>
            <div class="cert-name"><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?></div>
            <?php if (!empty($emp['badge_number'])): ?><div class="cert-small">Dienstnummer <?= e($emp['badge_number']) ?></div><?php endif; ?>
            <?php if (in_array($d['kind'] ?? '', ['befoerderung', 'ernennung'], true)): ?>
                <p class="cert-text">zur / zum</p>
                <div class="cert-rank"><?= e($d['new_rank'] ?? '') ?></div>
            <?php else: ?>
                <p class="cert-text">für</p>
                <div class="cert-rank"><?= e($d['new_rank'] ?? '') ?></div>
            <?php endif; ?>
            <p class="cert-text"><?= e($from) ?> <strong><?= e(fmt_date($d['effective'] ?? '')) ?></strong>.</p>
            <?php if (!empty($d['laudation'])): ?><p class="cert-laud"><?= nl($d['laudation']) ?></p><?php endif; ?>
            <p class="cert-wish">Ich spreche Ihnen hierzu meine Anerkennung und meinen Dank aus.</p>
            <div class="cert-place"><?= e(org('city')) ?>, den <?= e(fmt_date($doc['issued_at'] ?? now())) ?></div>
            <div class="cert-signs<?= $signer2 !== '' ? ' two' : '' ?>">
                <div class="cert-sign"><div class="sig-area"><?= signature_html($u, doc_rand($doc, 'csig', -3, 2)) ?></div><span><?= e(user_display($u)) ?></span><small><?= e($u['rank_name'] ?? '') ?></small></div>
                <div class="cert-seal"><?= seal_svg($doc, '#9a7b1c') ?></div>
                <?php if ($signer2 !== ''): ?><div class="cert-sign"><div class="sig-area"></div><span><?= e($signer2) ?></span></div><?php endif; ?>
            </div>
            <div class="cert-verify"><?= qr_html($doc, 'qr cert-qr') ?><span class="mono"><?= e($doc['number'] ?? '') ?> · Prüfcode <?= e(chunk_split($doc['verify_code'] ?? '', 4, ' ')) ?></span></div>
        </div>
    </div>
</section>
