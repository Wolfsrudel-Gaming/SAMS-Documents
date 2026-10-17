<?php
defined('SAMS_ROOT') || exit;
/** Dienstausweis im Scheckkartenformat (ID-1, 85,6 × 54 mm) – Vorder- und Rückseite */
$emp = $d['_employee'] ?? [];
$function = trim((string) ($d['function'] ?? '')) ?: ($emp['rank_name'] ?? '');
$photo = preg_match('#^https?://#i', (string) ($d['photo'] ?? '')) ? (string) $d['photo'] : '';
$initials = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim((string) ($emp['name'] ?? '?'))), 0, 2)));
$access = opt_label($type, 'access', $d['access'] ?? []);
?>
<section class="paper idcard front">
    <?= doc_watermark($doc, $draft) ?>
    <div class="id-band"><?= logo_html('id-logo') ?><div><b><?= e(org('org_name')) ?></b><span>Dienstausweis · Employee ID</span></div></div>
    <div class="id-body">
        <div class="id-photo"><?php if ($photo !== ''): ?><img src="<?= e($photo) ?>" alt="" referrerpolicy="no-referrer"><?php else: ?><span><?= e(mb_strtoupper($initials)) ?></span><?php endif; ?></div>
        <div class="id-data">
            <div class="id-name"><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?></div>
            <div class="id-func"><?= e($function) ?></div>
            <?php if (!empty($emp['departments'])): ?><div class="id-dep"><?= e($emp['departments']) ?></div><?php endif; ?>
            <dl>
                <dt>Dienst-Nr.</dt><dd class="mono"><?= e($emp['badge_number'] ?? '') ?: '—' ?></dd>
                <dt>Gültig bis</dt><dd><?= e(fmt_date($d['valid_until'] ?? '')) ?></dd>
            </dl>
        </div>
    </div>
    <div class="id-bar"><?= barcode_svg(($emp['badge_number'] ?? '') !== '' ? (string) $emp['badge_number'] : (string) ($doc['number'] ?? 'SAMS'), 18) ?></div>
    <div class="id-holo"></div>
</section>
<section class="paper idcard back">
    <?= doc_watermark($doc, $draft) ?>
    <div class="id-strip"></div>
    <div class="id-back">
        <div class="id-back-grid">
            <div><span class="fl">Zugang / Access</span><b><?= e($access ?: 'Klinik') ?></b></div>
            <div><span class="fl">Blutgruppe</span><b><?= e($d['blood_type'] ?? '') ?: '—' ?></b></div>
            <div class="wide"><span class="fl">Im Notfall benachrichtigen</span><b><?= e($d['emergency'] ?? '') ?: '—' ?></b></div>
        </div>
        <p class="id-note">Dieser Ausweis ist Eigentum der <?= e(org('org_name')) ?> und nur in Verbindung mit einem amtlichen Lichtbildausweis gültig. Bei Verlust bitte sofort melden. Finder werden gebeten, ihn an <?= e(org('org_street')) ?>, <?= e(org('org_city')) ?> zurückzugeben.</p>
        <div class="id-back-foot">
            <?= qr_html($doc, 'qr id-qr') ?>
            <div class="id-issuer"><?= signature_html($u, doc_rand($doc, 'idsig', -4, 3)) ?><span><?= e(user_display($u)) ?> · ausgestellt <?= e(fmt_date($doc['issued_at'] ?? now())) ?></span></div>
            <div class="id-seal"><?= seal_svg($doc, '#1c3c8c') ?></div>
        </div>
        <div class="id-code mono"><?= e($doc['number'] ?? '') ?> · <?= e(chunk_split($doc['verify_code'] ?? '', 4, ' ')) ?></div>
    </div>
</section>
