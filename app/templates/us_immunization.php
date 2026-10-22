<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Impfausweis (Immunization Record, Impfkarte). */
$items = array_values(array_filter((array) ($d['items'] ?? []), fn($it) => trim(($it['vaccine'] ?? '') . ($it['date'] ?? '')) !== ''));
$rows = max(8, count($items));
?>
<section class="paper a5 vax us-vax">
    <?= doc_watermark($doc, $draft) ?>
    <header class="vax-head">
        <?= logo_html('vax-logo') ?>
        <div>
            <div class="vax-title"><?= e(dt('Impfausweis (Immunization Record)')) ?></div>
            <div class="vax-sub"><?= e(dt('Staat San Andreas · Für Schule, Arbeit und Reisen aufbewahren')) ?></div>
        </div>
        <div class="vax-num"><span><?= e(dt('Nr.')) ?></span><b class="mono"><?= e(doc_code_display($doc['number'] ?? '')) ?></b></div>
    </header>
    <table class="vax-person">
        <tr><th><?= e(dt('Name')) ?></th><td colspan="3"><b><?= e($p ? patient_name($p, true) : '') ?></b></td></tr>
        <tr><th><?= e(dt('Geburtsdatum')) ?></th><td><?= e(fmt_date($p['dob'] ?? '')) ?></td><th><?= e(dt('Bürger-ID')) ?></th><td class="mono"><?= e($p['citizen_id'] ?? '') ?></td></tr>
    </table>
    <table class="vax-tbl">
        <thead><tr><th style="width:18mm"><?= e(dt('Impfdatum')) ?></th><th><?= e(dt('Impfstoff (Präparat) / Krankheit')) ?></th><th style="width:19mm"><?= e(dt('Charge')) ?></th><th style="width:10mm"><?= e(dt('Dosis')) ?></th><th style="width:33mm"><?= e(dt('Praxis / impfende Person')) ?></th></tr></thead>
        <tbody>
        <?php for ($i = 0; $i < $rows; $i++): $it = $items[$i] ?? null; ?>
            <tr>
                <td class="printed"><?= $it ? e(fmt_date($it['date'] ?? '')) : '' ?></td>
                <td class="printed"><?php if ($it): ?><b><?= e($it['vaccine'] ?? '') ?></b><?= ($it['against'] ?? '') !== '' ? '<br><small>' . e($it['against']) . '</small>' : '' ?><?php endif; ?></td>
                <td class="printed mono"><?php if ($it && ($it['batch'] ?? '') !== ''): ?><span class="vax-label"><?= e($it['batch']) ?></span><?php endif; ?></td>
                <td class="printed center"><?= $it ? e($it['dose'] ?? '') : '' ?></td>
                <td class="vax-sig"><?php if ($it): ?><span class="vax-stamp"><?= e(org('org_short')) ?><br><?= e(user_display($u)) ?></span><?= signature_html($u, doc_rand($doc, 'vx' . $i, -6, 4)) ?><?php endif; ?></td>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
    <div class="vax-foot">
        <?php if (!empty($d['next'])): ?><div><b><?= e(dt('Nächste Impfung fällig:')) ?></b> <span class="printed"><?= e($d['next']) ?></span></div><?php endif; ?>
        <?php if (!empty($d['remarks'])): ?><div><b><?= e(dt('Bemerkungen:')) ?></b> <span class="printed"><?= e($d['remarks']) ?></span></div><?php endif; ?>
        <div class="vax-verify"><?= qr_html($doc, 'qr vax-qr') ?><span><?= e(dt('Ausgestellt von {org}, {city}', ['org' => org('org_name'), 'city' => org('org_city')])) ?><br><?= e(dt('Prüfcode:')) ?> <b class="mono"><?= e(chunk_split($doc['verify_code'] ?? '', 4, ' ')) ?></b> · <?= e(preg_replace('#^https?://#', '', base_url())) ?>/verify.php</span></div>
    </div>
</section>
