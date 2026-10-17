<?php
defined('SAMS_ROOT') || exit;
/** Erklärung zur Behandlungs-/Transportverweigerung (Revers) */
$protoTitle = 'Erklärung über die Ablehnung';
$protoSub = 'von Untersuchung, Behandlung und/oder Transport (Revers)';
$name = $p ? patient_name($p) : ($d['person_name'] ?? '');
$refused = (array) ($d['refused'] ?? []);
$refusedLabels = ['untersuchung' => 'die Untersuchung', 'behandlung' => 'die Behandlung', 'transport' => 'den Transport in ein Krankenhaus', 'medikamente' => 'die Gabe von Medikamenten'];
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w6"><span class="fl">Name, Vorname</span><span class="fv"><strong><?= e($name) ?></strong></span></div>
    <div class="pc w3"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl">Datum / Uhrzeit</span><span class="fv"><?= e(fmt_date($d['date'] ?? '')) ?>, <?= e($d['time'] ?? '') ?> Uhr</span></div>
    <div class="pc w12"><span class="fl">Einsatzort</span><span class="fv"><?= e($d['location'] ?? '') ?></span></div>
    <div class="pc w12 tall"><span class="fl">Verdachtsdiagnose / Befund</span><span class="fv"><?= nl($d['findings'] ?? '') ?></span></div>
</div>
<div class="doc-body revers-text">
    <p>Ich, <strong><?= e($name ?: '______________________') ?></strong>, wurde von den unterzeichnenden Mitarbeitenden des
        <?= e(org('org_name')) ?> ausführlich und in für mich verständlicher Form über meinen Gesundheitszustand und die
        Notwendigkeit weiterer medizinischer Maßnahmen aufgeklärt.</p>
    <p>Trotz dieser Aufklärung lehne ich</p>
    <ul class="chklist">
        <?php foreach ($refusedLabels as $k => $l): ?><li><?= box(in_array($k, $refused, true)) ?> <?= e($l) ?></li><?php endforeach; ?>
    </ul>
    <p>ausdrücklich ab. Über die möglichen Folgen meiner Entscheidung, insbesondere</p>
    <div class="pc w12 boxed"><span class="fv"><?= nl($d['risks'] ?? '') ?></span></div>
    <p>wurde ich informiert. Ich weiß, dass ich jederzeit erneut den Rettungsdienst unter der Notrufnummer <strong>911</strong> verständigen kann.
        Ich übernehme die volle Verantwortung für alle sich aus meiner Entscheidung ergebenden Folgen und entbinde die Mitarbeitenden
        des <?= e(org('org_short')) ?> von jeglicher Haftung.</p>
    <p><?= box(!empty($d['capable'])) ?> Der/die Erklärende war zum Zeitpunkt der Aufklärung wach, orientiert und nach Einschätzung der Einsatzkräfte einwilligungsfähig.</p>
</div>
<div class="proto-sign three">
    <div class="sigblock"><div class="sig-sign"><div class="sig-area">
        <?php if (!empty($d['patient_signature'])): ?><img class="sig-img" src="<?= e($d['patient_signature']) ?>" alt=""><?php elseif (!empty($d['signature_refused'])): ?><span class="printed refused">Unterschrift verweigert</span><?php endif; ?>
    </div><span class="sig-cap">Unterschrift Patient/in</span></div></div>
    <?= doc_signature($doc, '', 'Unterschrift Einsatzkraft ' . org('org_short'), false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"><span class="printed"><?= e($d['witness'] ?? '') ?></span></div><span class="sig-cap">Zeuge (Name, Funktion)</span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
