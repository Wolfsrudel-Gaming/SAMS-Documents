<?php
defined('SAMS_ROOT') || exit;
/** Kopfzeile für formularartige A4-Protokolle. Erwartet $protoTitle, $protoSub */
?>
<header class="ph">
    <div class="ph-brand"><?= logo_html() ?><div><div class="lh-name"><?= e(org('org_name')) ?></div><div class="lh-sub"><?= e(org('org_sub')) ?></div></div></div>
    <div class="ph-title"><?= e($protoTitle) ?><?php if (!empty($protoSub)): ?><small><?= e($protoSub) ?></small><?php endif; ?></div>
    <div class="ph-num"><span class="fl">Dokument-Nr.</span><span class="mono"><?= e($doc['number'] ?? '') ?></span><?= barcode_svg($doc['number'] ?? 'ENTWURF', 22) ?></div>
</header>
