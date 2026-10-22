<?php
defined('SAMS_ROOT') || exit;
/** Impfbescheinigung – A5, gelb, zweisprachig (angelehnt an die Internationale Bescheinigung über Impfungen) */
$items = array_values(array_filter((array) ($d['items'] ?? []), fn($it) => trim(($it['vaccine'] ?? '') . ($it['date'] ?? '')) !== ''));
$rows = max(8, count($items));
?>
<section class="paper a5 vax">
    <?= doc_watermark($doc, $draft) ?>
    <header class="vax-head">
        <?= logo_html('vax-logo') ?>
        <div>
            <div class="vax-title">Impfbescheinigung</div>
            <div class="vax-sub">Certificate of Vaccination · State of San Andreas</div>
        </div>
        <div class="vax-num"><span>Nr. / No.</span><b class="mono"><?= e($doc['number'] ?? '') ?></b></div>
    </header>
    <table class="vax-person">
        <tr><th>Name, Vorname<small>Surname, given name</small></th><td colspan="3"><b><?= e($p ? patient_name($p, true) : '') ?></b></td></tr>
        <tr><th>Geburtsdatum<small>Date of birth</small></th><td><?= e(fmt_date($p['dob'] ?? '')) ?></td>
            <th>Bürger-ID<small>Citizen ID</small></th><td class="mono"><?= e($p['citizen_id'] ?? '') ?></td></tr>
    </table>
    <table class="vax-tbl">
        <thead><tr>
            <th style="width:17mm">Datum<small>Date</small></th>
            <th>Impfstoff, Schutz gegen<small>Vaccine, disease</small></th>
            <th style="width:19mm">Ch.-B.<small>Batch no.</small></th>
            <th style="width:10mm">Dosis<small>Dose</small></th>
            <th style="width:33mm">Stempel, Unterschrift<small>Official stamp, signature</small></th>
        </tr></thead>
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
        <?php if (!empty($d['next'])): ?><div><b>Nächste Impfung / Next vaccination:</b> <span class="printed"><?= e($d['next']) ?></span></div><?php endif; ?>
        <?php if (!empty($d['remarks'])): ?><div><b>Bemerkungen / Remarks:</b> <span class="printed"><?= e($d['remarks']) ?></span></div><?php endif; ?>
        <div class="vax-verify"><?= qr_html($doc, 'qr vax-qr') ?><span>Ausgestellt von <?= e(org('org_name')) ?>, <?= e(org('org_city')) ?><br>Prüfcode: <b class="mono"><?= e(chunk_split($doc['verify_code'] ?? '', 4, ' ')) ?></b> · <?= e(preg_replace('#^https?://#', '', base_url())) ?>/verify.php</span></div>
    </div>
</section>
