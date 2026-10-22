<?php
defined('SAMS_ROOT') || exit;
/** Aufklärungsbogen und Einwilligungserklärung zu einem Eingriff */
$risks = [];
foreach ($type['fields'] as $f) {
    if (($f['name'] ?? '') === 'risks') {
        $risks = $f['options'];
    }
}
$chosen = (array) ($d['risks'] ?? []);
$protoTitle = dt('Aufklärung und Einwilligung');
$protoSub = dt('Patienteninformation zum geplanten Eingriff');
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w6"><span class="fl"><?= e(dt('Patient/in')) ?></span><span class="fv"><strong><?= e($p ? patient_name($p, true) : '') ?></strong></span></div>
    <div class="pc w3"><span class="fl"><?= e(dt('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl"><?= e(dt('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('Geplanter Eingriff')) ?></span><span class="fv"><strong><?= e($d['procedure'] ?? '') ?></strong></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Geplant am')) ?></span><span class="fv"><?= e(fmt_date($d['planned_date'] ?? '')) ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Betäubung')) ?></span><span class="fv"><?= e(opt_label($type, 'anesthesia', $d['anesthesia'] ?? '')) ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Aufklärende/r Arzt/Ärztin')) ?></span><span class="fv"><?= e(user_display($u)) ?></span></div>
</div>
<h3 class="psec"><?= e(dt('Art und Ablauf des Eingriffs')) ?></h3>
<div class="pgrid"><div class="pc w12 tall" style="min-height:24mm"><span class="fv"><?= nl($d['explanation'] ?? '') ?></span></div></div>
<h3 class="psec"><?= e(dt('Mögliche Risiken und Komplikationen')) ?></h3>
<div class="pgrid">
    <div class="pc w12"><div class="chkgrid" style="grid-template-columns:repeat(3,1fr)">
        <?php foreach ($risks as $k => $l): ?><span><?= box(in_array($k, $chosen, true)) ?> <?= e($l) ?></span><?php endforeach; ?>
    </div></div>
    <div class="pc w12 tall"><span class="fl"><?= e(dt('Spezielle Risiken dieses Eingriffs')) ?></span><span class="fv"><?= nl($d['risks_specific'] ?? '') ?: '—' ?></span></div>
    <div class="pc w12 tall"><span class="fl"><?= e(dt('Behandlungsalternativen')) ?></span><span class="fv"><?= nl($d['alternatives'] ?? '') ?: '—' ?></span></div>
    <div class="pc w12 tall"><span class="fl"><?= e(dt('Fragen der Patientin / des Patienten, Gesprächsnotizen')) ?></span><span class="fv"><?= nl($d['questions'] ?? '') ?: '—' ?></span></div>
</div>
<h3 class="psec"><?= e(dt('Erklärung')) ?></h3>
<div class="revers-text">
    <p><?= e(dt('Über den geplanten Eingriff, seine Art, Bedeutung, Tragweite, Dringlichkeit und Risiken sowie über Behandlungsalternativen wurde ich in einem
        persönlichen Gespräch mit der/dem unterzeichnenden Ärztin/Arzt ausführlich und für mich verständlich aufgeklärt. Ich konnte alle mich
        interessierenden Fragen stellen; diese wurden vollständig beantwortet. Ich hatte ausreichend Bedenkzeit.')) ?></p>
    <ul class="chklist">
        <li><?= box(($d['decision'] ?? '') === 'ja') ?> <strong><?= e(dt('Ich willige in den vorgeschlagenen Eingriff')) ?></strong> <?= e(dt('sowie in medizinisch erforderliche Erweiterungen ein.')) ?></li>
        <li><?= box(($d['decision'] ?? '') === 'nein') ?> <strong><?= e(dt('Ich lehne den Eingriff ab.')) ?></strong> <?= e(dt('Über die möglichen Nachteile und Folgen der Ablehnung wurde ich aufgeklärt.')) ?></li>
        <li><?= box(!empty($d['copy_received'])) ?> <?= e(dt('Eine Kopie dieses Aufklärungsbogens habe ich erhalten.')) ?></li>
    </ul>
</div>
<div class="proto-sign three">
    <div class="sigblock"><div class="sig-sign"><div class="sig-area">
        <?php if (!empty($d['patient_signature'])): ?><img class="sig-img" src="<?= e($d['patient_signature']) ?>" alt=""><?php endif; ?>
    </div><span class="sig-cap"><?= e(dt('Patient/in bzw. gesetzl. Vertreter/in')) ?></span></div></div>
    <?= doc_signature($doc, 'rect', dt('Aufklärende/r Arzt/Ärztin'), false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"><span class="printed"><?= e($d['witness'] ?? '') ?></span></div><span class="sig-cap"><?= e(dt('Zeuge/Zeugin')) ?></span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
