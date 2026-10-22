<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Sterbeurkunde (angelehnt an das US Standard Certificate of Death). */
$protoTitle = dt('Sterbeurkunde (Certificate of Death)');
$protoSub = dt('Staat San Andreas · Gesundheitsbehörde · Personenstandswesen');
$signs = (array) ($d['signs'] ?? []);
$age = ($p['dob'] ?? '') && ($d['death_date'] ?? '') ? (new DateTime($p['dob']))->diff(new DateTime($d['death_date']))->y : '';
$sex = ['m' => dt('Männlich'), 'w' => dt('Weiblich'), 'd' => dt('Divers')][$p['gender'] ?? ''] ?? '';
$manner = ['natuerlich' => dt('Natürlich'), 'nichtnatuerlich' => dt('Unfall / Tötungsdelikt / Suizid (nicht natürlich)'), 'ungeklaert' => dt('Ermittlungen laufen / nicht feststellbar')];
echo a4_open($doc, $draft, 'proto death');
require __DIR__ . '/_protohead.php';
?>
<div class="death-part"><?= e(dt('Verstorbene Person')) ?></div>
<div class="pgrid">
    <div class="pc w6"><span class="fl"><?= e(dt('1. Name der verstorbenen Person (Nachname, Vorname)')) ?></span><span class="fv"><strong><?= e($p ? patient_name($p, true) : '') ?></strong></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('2. Geschlecht')) ?></span><span class="fv"><?= e($sex) ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('3. Geburtsdatum')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('4. Alter (Jahre)')) ?></span><span class="fv"><?= e((string) $age) ?></span></div>
    <div class="pc w8"><span class="fl"><?= e(dt('5. Wohnanschrift')) ?></span><span class="fv"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('6. Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
</div>
<div class="death-part"><?= e(dt('Angaben zum Tod')) ?></div>
<div class="pgrid">
    <div class="pc w3"><span class="fl"><?= e(dt('7. Sterbedatum')) ?></span><span class="fv"><?= e(fmt_date($d['death_date'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl"><?= e(dt('8. Todeszeit')) ?></span><span class="fv"><?= e($d['death_time'] ?? '') ?><?= !empty($d['time_estimated']) ? e(dt(' (geschätzt / aufgefunden)')) : '' ?></span></div>
    <div class="pc w6"><span class="fl"><?= e(dt('9. Sterbeort')) ?></span><span class="fv"><?= e(opt_label($type, 'place', $d['place'] ?? '')) ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('10. Ort (Anschrift / Einrichtung)')) ?></span><span class="fv"><?= e($d['place_address'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('11. Tod festgestellt am')) ?></span><span class="fv"><?= e(fmt_date($d['exam_date'] ?? '')) ?> <?= e($d['exam_time'] ?? '') ?></span></div>
    <div class="pc w8"><span class="fl"><?= e(dt('12. Todeszeichen')) ?></span><span class="fv chk"><?php foreach ($type['fields'] as $f): if (($f['name'] ?? '') === 'signs'): foreach ($f['options'] as $k => $l): ?><span><?= box(in_array($k, $signs, true)) ?> <?= e($l) ?></span><?php endforeach; endif; endforeach; ?></span></div>
    <div class="pc w6"><span class="fl"><?= e(dt('13. Identifiziert durch')) ?></span><span class="fv"><?= e(opt_label($type, 'identified_by', $d['identified_by'] ?? '')) ?></span></div>
    <div class="pc w6"><span class="fl"><?= e(dt('14. Todesart')) ?></span><span class="fv"><?= e($manner[$d['manner'] ?? ''] ?? '') ?></span></div>
</div>
<div class="death-part"><?= e(dt('Todesursache')) ?></div>
<div class="pgrid">
    <div class="pc w12"><span class="fl"><?= e(dt('Teil I a. Unmittelbare Todesursache (zum Tod führende Erkrankung oder Verletzung)')) ?></span><span class="fv"><?= e($d['cause_a'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('b. Infolge von (bzw. als Folge von)')) ?></span><span class="fv"><?= e($d['cause_b'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('c. Grundleiden (Erkrankung oder Verletzung, die den Verlauf ausgelöst hat)')) ?></span><span class="fv"><?= e($d['cause_c'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl"><?= e(dt('Teil II. Andere wesentliche, zum Tod beitragende Erkrankungen')) ?></span><span class="fv"><?= e($d['cause_2'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('15. An Rechtsmedizin / Polizei gemeldet')) ?></span><span class="fv"><?= e(!empty($d['police']) ? dt('Ja') : dt('Nein')) ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('16. Obduktion angefordert')) ?></span><span class="fv"><?= e(!empty($d['autopsy']) ? dt('Ja') : dt('Nein')) ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('17. Übertragbare Krankheit')) ?></span><span class="fv"><?= e(!empty($d['infection']) ? dt('Ja – Schutzmaßnahmen erforderlich') : dt('Nein')) ?></span></div>
    <?php if (!empty($d['remarks'])): ?><div class="pc w12"><span class="fl"><?= e(dt('Bemerkungen')) ?></span><span class="fv"><?= nl($d['remarks']) ?></span></div><?php endif; ?>
</div>
<p class="small"><?= e(dt('Bescheinigende Person: Nach bestem Wissen ist der Tod zur angegebenen Zeit, am angegebenen Datum und Ort sowie aufgrund der angegebenen Ursache(n) und Todesart eingetreten.')) ?></p>
<?= doc_signature($doc, 'round') ?>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
