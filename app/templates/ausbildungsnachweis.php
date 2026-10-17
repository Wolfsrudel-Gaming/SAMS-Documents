<?php
defined('SAMS_ROOT') || exit;
/** Zertifikat über einen Lehrgang / eine Qualifikation – A4 hoch */
$emp = $d['_employee'] ?? [];
$results = ['mit_auszeichnung' => 'mit Auszeichnung bestanden', 'bestanden' => 'erfolgreich bestanden', 'teilgenommen' => 'teilgenommen'];
$period = !empty($d['date_from']) && $d['date_from'] !== ($d['date_to'] ?? '') ? 'vom ' . fmt_date($d['date_from']) . ' bis ' . fmt_date($d['date_to'] ?? '') : 'am ' . fmt_date($d['date_to'] ?? '');
$contents = array_filter(array_map('trim', explode("\n", (string) ($d['contents'] ?? ''))));
?>
<section class="paper a4 certificate training">
    <?= doc_watermark($doc, $draft) ?>
    <div class="cert-band"></div>
    <div class="cert-inner left">
        <div class="cert-head"><?= logo_html('cert-logo small') ?><div><div class="cert-org"><?= e(org('org_name')) ?></div><div class="cert-orgsub">Aus- und Fortbildung · <?= e(org('org_sub')) ?></div></div></div>
        <h1 class="cert-title">Zertifikat</h1>
        <p class="cert-text">Hiermit wird bescheinigt, dass</p>
        <div class="cert-name"><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?></div>
        <p class="cert-small"><?= e($emp['rank_name'] ?? '') ?><?= !empty($emp['badge_number']) ? ' · Dienstnummer ' . e($emp['badge_number']) : '' ?></p>
        <p class="cert-text"><?= e($period) ?> an dem Lehrgang</p>
        <div class="cert-rank"><?= e($d['course'] ?? '') ?></div>
        <p class="cert-text"><?= !empty($d['hours']) ? 'im Umfang von ' . (int) $d['hours'] . ' Unterrichtseinheiten ' : '' ?><strong><?= e($results[$d['result'] ?? ''] ?? '') ?></strong> hat.<?= !empty($d['grade']) ? ' Ergebnis: <strong>' . e($d['grade']) . '</strong>.' : '' ?></p>
        <?php if ($contents): ?>
            <h3 class="doc-h">Lehrgangsinhalte</h3>
            <ul class="cert-list"><?php foreach ($contents as $c): ?><li><?= e($c) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if (!empty($d['valid_until'])): ?><p class="cert-text">Die Qualifikation ist gültig bis <strong><?= e(fmt_date($d['valid_until'])) ?></strong> und ist danach durch eine Rezertifizierung zu erneuern.</p><?php endif; ?>
        <div class="cert-place"><?= e(org('city')) ?>, den <?= e(fmt_date($doc['issued_at'] ?? now())) ?></div>
        <div class="cert-signs two">
            <div class="cert-sign"><div class="sig-area"><?php if (!empty($d['instructor'])): ?><span class="printed"><?= e($d['instructor']) ?></span><?php endif; ?></div><span>Ausbilder/in</span></div>
            <div class="cert-seal"><?= seal_svg($doc) ?></div>
            <div class="cert-sign"><div class="sig-area"><?= signature_html($u, doc_rand($doc, 'tsig', -3, 2)) ?></div><span><?= e(user_display($u)) ?></span><small>Leitung Aus- und Fortbildung</small></div>
        </div>
    </div>
    <?= doc_footer($doc) ?>
</section>
