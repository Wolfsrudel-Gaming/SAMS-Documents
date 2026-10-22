<?php
defined('SAMS_ROOT') || exit;
/** Erklärung zur Behandlungs-/Transportverweigerung (Revers) */
$protoTitle = dt('Erklärung über die Ablehnung');
$protoSub = dt('von Untersuchung, Behandlung und/oder Transport (Revers)');
$name = $p ? patient_name($p) : ($d['person_name'] ?? '');
$refused = (array) ($d['refused'] ?? []);
$refusedLabels = ['untersuchung' => dt('die Untersuchung'), 'behandlung' => dt('die Behandlung'), 'transport' => dt('den Transport in ein Krankenhaus'), 'medikamente' => dt('die Gabe von Medikamenten')];
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w6"><span class="fl"><?= e(dt('Name, Vorname')) ?></span><span class="fv"><strong><?= e($name) ?></strong></span></div>
    <div class="pc w3"><span class="fl"><?= e(dt('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl"><?= e(dt('Datum / Uhrzeit')) ?></span><span class="fv"><?= e(fmt_date($d['date'] ?? '')) ?>, <?= e($d['time'] ?? '') ?><?= e(dt(' Uhr')) ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('Einsatzort')) ?></span><span class="fv"><?= e($d['location'] ?? '') ?></span></div>
    <div class="pc w12 tall"><span class="fl"><?= e(dt('Verdachtsdiagnose / Befund')) ?></span><span class="fv"><?= nl($d['findings'] ?? '') ?></span></div>
</div>
<div class="doc-body revers-text">
    <p><?= strtr(e(dt('Ich, {name}, wurde von den unterzeichnenden Mitarbeitenden des
        {org} ausführlich und in für mich verständlicher Form über meinen Gesundheitszustand und die
        Notwendigkeit weiterer medizinischer Maßnahmen aufgeklärt.')), ['{name}' => '<strong>' . e($name ?: '______________________') . '</strong>', '{org}' => e(org('org_name'))]) ?></p>
    <p><?= e(dt('Trotz dieser Aufklärung lehne ich')) ?></p>
    <ul class="chklist">
        <?php foreach ($refusedLabels as $k => $l): ?><li><?= box(in_array($k, $refused, true)) ?> <?= e($l) ?></li><?php endforeach; ?>
    </ul>
    <p><?= e(dt('ausdrücklich ab. Über die möglichen Folgen meiner Entscheidung, insbesondere')) ?></p>
    <div class="pc w12 boxed"><span class="fv"><?= nl(dt((string) ($d['risks'] ?? ''))) ?></span></div>
    <p><?= strtr(e(dt('wurde ich informiert. Ich weiß, dass ich jederzeit erneut den Rettungsdienst unter der Notrufnummer {911} verständigen kann.
        Ich übernehme die volle Verantwortung für alle sich aus meiner Entscheidung ergebenden Folgen und entbinde die Mitarbeitenden
        des {org} von jeglicher Haftung.')), ['{911}' => '<strong>911</strong>', '{org}' => e(org('org_short'))]) ?></p>
    <p><?= box(!empty($d['capable'])) ?> <?= e(dt('Der/die Erklärende war zum Zeitpunkt der Aufklärung wach, orientiert und nach Einschätzung der Einsatzkräfte einwilligungsfähig.')) ?></p>
</div>
<div class="proto-sign three">
    <div class="sigblock"><div class="sig-sign"><div class="sig-area">
        <?php if (!empty($d['patient_signature'])): ?><img class="sig-img" src="<?= e($d['patient_signature']) ?>" alt=""><?php elseif (!empty($d['signature_refused'])): ?><span class="printed refused"><?= e(dt('Unterschrift verweigert')) ?></span><?php endif; ?>
    </div><span class="sig-cap"><?= e(dt('Unterschrift Patient/in')) ?></span></div></div>
    <?= doc_signature($doc, '', dt('Unterschrift Einsatzkraft {org}', ['org' => org('org_short')]), false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"><span class="printed"><?= e($d['witness'] ?? '') ?></span></div><span class="sig-cap"><?= e(dt('Zeuge (Name, Funktion)')) ?></span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
