<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Dienstausweis als US-Behördenausweis (Agency ID) im Scheckkartenformat (CR80, 85,6 × 54 mm) –
 * Vorder- und Rückseite. Gleiche Daten und Kartenmechanik wie app/templates/dienstausweis.php
 * (Klassen „paper idcard front/back“ für die drehbare Karte in public/doc.php).
 */
$emp = $d['_employee'] ?? [];
$function = trim((string) ($d['function'] ?? '')) ?: ($emp['rank_name'] ?? '');
$photo = preg_match('#^https?://#i', (string) ($d['photo'] ?? '')) ? (string) $d['photo'] : '';
// Foto über den eigenen Server einbetten, damit es auch beim „Als Bild speichern“ erscheint (sonst Original-URL)
$photo = $photo !== '' ? (remote_image_data_uri($photo) ?: $photo) : '';
// Hochgeladenes Foto hat Vorrang
if (preg_match('#^data:image/(png|jpeg|webp);base64,#', (string) ($d['photo_file'] ?? ''))) {
    $photo = (string) $d['photo_file'];
}
$initials = implode('', array_map(fn($w) => mb_substr($w, 0, 1), array_slice(preg_split('/\s+/', trim((string) ($emp['name'] ?? '?'))), 0, 2)));
$access = implode(', ', array_map(fn($x) => dt(opt_label($type, 'access', $x)), (array) ($d['access'] ?? [])));
$badge = (string) ($emp['badge_number'] ?? '');
?>
<section class="paper idcard front us us-gen-id">
    <?= doc_watermark($doc, $draft) ?>
    <div class="us-gen-id-top">
        <?= logo_html('us-gen-id-logo') ?>
        <div class="us-gen-id-agency">
            <small><?= e(mb_strtoupper(dt('Staat San Andreas'))) ?></small>
            <b><?= e(mb_strtoupper(org('org_name'))) ?></b>
        </div>
    </div>
    <div class="us-gen-id-main">
        <div class="us-gen-id-photo"><?php if ($photo !== ''): ?><img src="<?= e($photo) ?>" alt="" referrerpolicy="no-referrer"><?php else: ?><span><?= e(mb_strtoupper($initials)) ?></span><?php endif; ?></div>
        <div class="us-gen-id-data">
            <div class="us-gen-id-name"><?= e(mb_strtoupper(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? '')))) ?></div>
            <div class="us-gen-id-title"><?= e($function) ?></div>
            <?php if (!empty($emp['departments'])): ?><div class="us-gen-id-dep"><?= e($emp['departments']) ?></div><?php endif; ?>
            <dl>
                <dt><?= e(dt('Dienst-Nr.')) ?></dt><dd class="mono"><?= e($badge) ?: '—' ?></dd>
                <dt><?= e(dt('Ausgestellt')) ?></dt><dd><?= e(fmt_date($doc['issued_at'] ?? now())) ?></dd>
                <dt><?= e(dt('Gültig bis')) ?></dt><dd class="us-gen-id-exp"><?= e(fmt_date($d['valid_until'] ?? '')) ?></dd>
            </dl>
            <div class="us-gen-id-sig"><?= signature_html($u, doc_rand($doc, 'idsig', -4, 3)) ?><span><?= e(dt('Ausstellende Stelle')) ?></span></div>
        </div>
    </div>
    <div class="us-gen-id-strip"><?= e(mb_strtoupper(dt('Dienstausweis'))) ?></div>
    <div class="us-gen-id-holo"></div>
</section>
<section class="paper idcard back us us-gen-id">
    <?= doc_watermark($doc, $draft) ?>
    <div class="us-gen-id-mag"></div>
    <div class="us-gen-id-back">
        <div class="us-gen-id-bgrid">
            <div><span class="fl"><?= e(dt('Zugang / Access')) ?></span><b><?= e($access ?: dt('Klinik')) ?></b></div>
            <div><span class="fl"><?= e(dt('Blutgruppe')) ?></span><b><?= e($d['blood_type'] ?? '') ?: '—' ?></b></div>
            <div class="wide"><span class="fl"><?= e(dt('Im Notfall benachrichtigen')) ?></span><b><?= e($d['emergency'] ?? '') ?: '—' ?></b></div>
        </div>
        <p class="us-gen-id-note"><?= e(dt('Eigentum der {org}. Nur gültig in Verbindung mit einem amtlichen Lichtbildausweis, nicht übertragbar. Verlust bitte sofort melden. Missbrauch wird strafrechtlich verfolgt.', ['org' => org('org_name')])) ?></p>
        <div class="us-gen-id-return"><span class="fl"><?= e(dt('Falls gefunden, bitte zurücksenden an')) ?></span><?= e(org('org_name')) ?> · <?= e(org('org_street')) ?> · <?= e(org('org_city')) ?></div>
        <div class="us-gen-id-bfoot">
            <?= qr_html($doc, 'qr us-gen-id-qr') ?>
            <div class="us-gen-id-bc"><?= barcode_svg($badge !== '' ? $badge : (string) ($doc['number'] ?? 'SAMS'), 18) ?><span class="mono"><?= e($badge !== '' ? $badge : doc_code_display((string) ($doc['number'] ?? ''))) ?></span></div>
            <div class="us-gen-id-seal"><?= seal_svg($doc) ?></div>
        </div>
        <div class="us-gen-id-issuer"><?= e(dt('ausgestellt {date}', ['date' => fmt_date($doc['issued_at'] ?? now())])) ?> · <?= e(user_display($u)) ?><?= doc_admin_issued($doc) ? ' · ' . e(admin_stamp_setting('admin_org_name')) : '' ?></div>
        <div class="us-gen-id-code mono"><?= e(doc_code_display((string) ($doc['number'] ?? ''))) ?> · <?= e(chunk_split(doc_code_display((string) ($doc['verify_code'] ?? '')), 4, ' ')) ?></div>
    </div>
</section>
