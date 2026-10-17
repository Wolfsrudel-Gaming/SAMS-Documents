<?php
defined('SAMS_ROOT') || exit;
/** Antrag auf Unterbringung (angelehnt an das kalifornische 5150-Formular, „Application for Assessment, Evaluation and Crisis Intervention“) */
$kinds = ['5150' => 'Sofortige Unterbringung bis 72 Stunden', '5250' => 'Verlängerung (Intensive Treatment) bis 14 Tage', 'gericht' => 'Antrag auf gerichtliche Unterbringung'];
$grounds = (array) ($d['grounds'] ?? []);
$protoTitle = 'Antrag auf Unterbringung';
$protoSub = 'Section ' . (($d['kind'] ?? '5150') === '5250' ? '5250' : '5150') . ' Welfare & Institutions Code · State of San Andreas';
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w12"><span class="fv chk"><?php foreach ($kinds as $k => $l): ?><span class="nowrap"><?= box((string) ($d['kind'] ?? '') === (string) $k) ?> <?= e($l) ?></span> <?php endforeach; ?></span></div>
</div>
<h3 class="psec">Betroffene Person</h3>
<div class="pgrid">
    <div class="pc w5"><span class="fl">Name, Vorname</span><span class="fv"><strong><?= e($p ? patient_name($p, true) : '') ?></strong></span></div>
    <div class="pc w2"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Geschl.</span><span class="fv"><?= e(strtoupper($p['gender'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl">Bürger-ID</span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl">Anschrift</span><span class="fv"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span></div>
</div>
<h3 class="psec">Aufgreifen und Zuführung</h3>
<div class="pgrid">
    <div class="pc w3"><span class="fl">Beginn (Datum)</span><span class="fv"><?= e(fmt_date($d['begin_date'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Uhrzeit</span><span class="fv"><?= e($d['begin_time'] ?? '') ?></span></div>
    <div class="pc w7"><span class="fl">Einrichtung</span><span class="fv"><?= e($d['facility'] ?? '') ?></span></div>
    <div class="pc w6"><span class="fl">Ort des Aufgreifens</span><span class="fv"><?= e($d['location'] ?? '') ?></span></div>
    <div class="pc w2"><span class="fl">Zugeführt durch</span><span class="fv"><?= e($d['brought_by'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">Beamter/Beamtin, Dienstnr.</span><span class="fv"><?= e($d['officer'] ?? '') ?></span></div>
</div>
<h3 class="psec">Gründe der Unterbringung</h3>
<div class="pgrid">
    <div class="pc w12"><span class="fv chk" style="flex-direction:column;gap:1mm">
        <?php foreach (['selbst' => 'Die Person stellt aufgrund einer psychischen Störung eine Gefahr für sich selbst dar.', 'fremd' => 'Die Person stellt aufgrund einer psychischen Störung eine Gefahr für andere dar.', 'hilflos' => 'Die Person ist aufgrund einer psychischen Störung schwer beeinträchtigt und kann für Nahrung, Kleidung oder Unterkunft nicht selbst sorgen (gravely disabled).'] as $k => $l): ?>
            <span><?= box(in_array($k, $grounds, true)) ?> <?= e($l) ?></span>
        <?php endforeach; ?>
    </span></div>
    <div class="pc w12 tall" style="min-height:42mm"><span class="fl">Tatsachen und Beobachtungen, die die Gefahr begründen (mit Datum, Uhrzeit, Äußerungen)</span><span class="fv"><?= nl($d['facts'] ?? '') ?></span></div>
    <div class="pc w8 tall"><span class="fl">Psychischer Befund bei Aufnahme</span><span class="fv"><?= nl($d['mental_status'] ?? '') ?></span></div>
    <div class="pc w4 tall"><span class="fl">Verdachtsdiagnose</span><span class="fv"><?= e($d['diagnosis'] ?? '') ?></span></div>
</div>
<h3 class="psec">Belehrung</h3>
<div class="pgrid">
    <div class="pc w12"><span class="fv chk" style="flex-direction:column;gap:1mm">
        <span><?= box(!empty($d['voluntary_refused'])) ?> Eine freiwillige Behandlung wurde angeboten und abgelehnt bzw. ist nicht ausreichend.</span>
        <span><?= box(!empty($d['informed'])) ?> Die Person wurde über den Grund der Unterbringung, deren Höchstdauer (72 Stunden) und ihr Recht auf anwaltlichen Beistand und gerichtliche Überprüfung belehrt.</span>
    </span></div>
    <div class="pc w12"><span class="fl">Benachrichtigte Angehörige / Betreuer</span><span class="fv"><?= e($d['relatives'] ?? '') ?: '—' ?></span></div>
</div>
<p class="small" style="margin-top:2mm">Ich bestätige, dass aufgrund der oben genannten Tatsachen hinreichender Grund zu der Annahme besteht, dass die Voraussetzungen der Unterbringung vorliegen. Falsche Angaben in diesem Antrag sind strafbar.</p>
<div class="proto-sign">
    <?= doc_signature($doc, 'rect', 'Antragstellende/r Arzt/Ärztin', false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"></div><span class="sig-cap">Aufnehmende Einrichtung (Datum, Uhrzeit, Unterschrift)</span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
