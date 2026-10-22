<?php
defined('SAMS_ROOT') || exit;
/** Antrag auf Unterbringung (angelehnt an das kalifornische 5150-Formular, „Application for Assessment, Evaluation and Crisis Intervention“) */
$kinds = ['5150' => dt('Sofortige Unterbringung bis 72 Stunden'), '5250' => dt('Verlängerung (Intensive Treatment) bis 14 Tage'), 'gericht' => dt('Antrag auf gerichtliche Unterbringung')];
$grounds = (array) ($d['grounds'] ?? []);
$protoTitle = dt('Antrag auf Unterbringung');
$protoSub = 'Section ' . (($d['kind'] ?? '5150') === '5250' ? '5250' : '5150') . ' Welfare & Institutions Code · State of San Andreas';
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w12"><span class="fv chk"><?php foreach ($kinds as $k => $l): ?><span class="nowrap"><?= box((string) ($d['kind'] ?? '') === (string) $k) ?> <?= e($l) ?></span> <?php endforeach; ?></span></div>
</div>
<h3 class="psec"><?= e(dt('Betroffene Person')) ?></h3>
<div class="pgrid">
    <div class="pc w5"><span class="fl"><?= e(dt('Name, Vorname')) ?></span><span class="fv"><strong><?= e($p ? patient_name($p, true) : '') ?></strong></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('Geschl.')) ?></span><span class="fv"><?= e(strtoupper(doc_en() ? strtr((string) ($p['gender'] ?? ''), ['w' => 'f', 'd' => 'x']) : ($p['gender'] ?? ''))) ?></span></div>
    <div class="pc w3"><span class="fl"><?= e(dt('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('Anschrift')) ?></span><span class="fv"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span></div>
</div>
<h3 class="psec"><?= e(dt('Aufgreifen und Zuführung')) ?></h3>
<div class="pgrid">
    <div class="pc w3"><span class="fl"><?= e(dt('Beginn (Datum)')) ?></span><span class="fv"><?= e(fmt_date($d['begin_date'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('Uhrzeit')) ?></span><span class="fv"><?= e($d['begin_time'] ?? '') ?></span></div>
    <div class="pc w7"><span class="fl"><?= e(dt('Einrichtung')) ?></span><span class="fv"><?= e($d['facility'] ?? '') ?></span></div>
    <div class="pc w6"><span class="fl"><?= e(dt('Ort des Aufgreifens')) ?></span><span class="fv"><?= e($d['location'] ?? '') ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('Zugeführt durch')) ?></span><span class="fv"><?= e(dt((string) ($d['brought_by'] ?? ''))) ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Beamter/Beamtin, Dienstnr.')) ?></span><span class="fv"><?= e($d['officer'] ?? '') ?></span></div>
</div>
<h3 class="psec"><?= e(dt('Gründe der Unterbringung')) ?></h3>
<div class="pgrid">
    <div class="pc w12"><span class="fv chk" style="flex-direction:column;gap:1mm">
        <?php foreach (['selbst' => dt('Die Person stellt aufgrund einer psychischen Störung eine Gefahr für sich selbst dar.'), 'fremd' => dt('Die Person stellt aufgrund einer psychischen Störung eine Gefahr für andere dar.'), 'hilflos' => dt('Die Person ist aufgrund einer psychischen Störung schwer beeinträchtigt und kann für Nahrung, Kleidung oder Unterkunft nicht selbst sorgen (gravely disabled).')] as $k => $l): ?>
            <span><?= box(in_array($k, $grounds, true)) ?> <?= e($l) ?></span>
        <?php endforeach; ?>
    </span></div>
    <div class="pc w12 tall" style="min-height:42mm"><span class="fl"><?= e(dt('Tatsachen und Beobachtungen, die die Gefahr begründen (mit Datum, Uhrzeit, Äußerungen)')) ?></span><span class="fv"><?= nl($d['facts'] ?? '') ?></span></div>
    <div class="pc w8 tall"><span class="fl"><?= e(dt('Psychischer Befund bei Aufnahme')) ?></span><span class="fv"><?= nl($d['mental_status'] ?? '') ?></span></div>
    <div class="pc w4 tall"><span class="fl"><?= e(dt('Verdachtsdiagnose')) ?></span><span class="fv"><?= e($d['diagnosis'] ?? '') ?></span></div>
</div>
<h3 class="psec"><?= e(dt('Belehrung')) ?></h3>
<div class="pgrid">
    <div class="pc w12"><span class="fv chk" style="flex-direction:column;gap:1mm">
        <span><?= box(!empty($d['voluntary_refused'])) ?> <?= e(dt('Eine freiwillige Behandlung wurde angeboten und abgelehnt bzw. ist nicht ausreichend.')) ?></span>
        <span><?= box(!empty($d['informed'])) ?> <?= e(dt('Die Person wurde über den Grund der Unterbringung, deren Höchstdauer (72 Stunden) und ihr Recht auf anwaltlichen Beistand und gerichtliche Überprüfung belehrt.')) ?></span>
    </span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('Benachrichtigte Angehörige / Betreuer')) ?></span><span class="fv"><?= e($d['relatives'] ?? '') ?: '—' ?></span></div>
</div>
<p class="small" style="margin-top:2mm"><?= e(dt('Ich bestätige, dass aufgrund der oben genannten Tatsachen hinreichender Grund zu der Annahme besteht, dass die Voraussetzungen der Unterbringung vorliegen. Falsche Angaben in diesem Antrag sind strafbar.')) ?></p>
<div class="proto-sign">
    <?= doc_signature($doc, 'rect', dt('Antragstellende/r Arzt/Ärztin'), false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"></div><span class="sig-cap"><?= e(dt('Aufnehmende Einrichtung (Datum, Uhrzeit, Unterschrift)')) ?></span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
