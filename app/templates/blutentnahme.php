<?php
defined('SAMS_ROOT') || exit;
/** Protokoll und ärztlicher Bericht zur Blutentnahme auf behördliche Anordnung */
$protoTitle = dt('Protokoll Blutentnahme');
$protoSub = dt('und ärztlicher Untersuchungsbericht (§ 81a StPO analog)');
$influence = ['nicht' => dt('nicht merkbar'), 'leicht' => dt('leicht'), 'deutlich' => dt('deutlich'), 'stark' => dt('stark')];
$rows = [
    'gait' => [dt('Gang (geradeaus)'), ['sicher', 'schwankend', 'torkelnd', 'nicht möglich']],
    'turn' => [dt('Plötzliche Kehrtwendung'), ['sicher', 'unsicher', 'nicht möglich']],
    'finger_nose' => [dt('Finger-Nasen-Prüfung'), ['sicher', 'unsicher', 'daneben']],
    'speech' => [dt('Sprache'), ['deutlich', 'verwaschen', 'lallend']],
    'pupils' => [dt('Pupillen'), ['unauffällig', 'eng', 'weit', 'lichtstarr']],
    'mood' => [dt('Bewusstsein / Verhalten'), ['klar', 'benommen', 'distanzlos', 'aggressiv', 'apathisch']],
    'odor' => [dt('Alkoholgeruch'), ['nein', 'leicht', 'deutlich']],
];
$tox = ['thc' => 'THC', 'coc' => dt('Kokain'), 'amp' => dt('Amphetamine / Meth.'), 'opi' => dt('Opiate'), 'benzo' => dt('Benzodiazepine')];
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<h3 class="psec"><?= e(dt('I. Anordnung')) ?></h3>
<div class="pgrid">
    <div class="pc w4"><span class="fl"><?= e(dt('Anordnende/r Beamter/in')) ?></span><span class="fv"><?= e($d['officer'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Dienstnr. / Dienststelle')) ?></span><span class="fv"><?= e($d['officer_no'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Aktenzeichen')) ?></span><span class="fv mono"><?= e($d['case_no'] ?? '') ?></span></div>
    <div class="pc w6"><span class="fl"><?= e(dt('Anlass')) ?></span><span class="fv"><?= e(opt_label($type, 'reason', $d['reason'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('Vorfallzeit')) ?></span><span class="fv"><?= e($d['incident_time'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl"><?= e(dt('Blutentnahme am / um')) ?></span><span class="fv"><strong><?= e(fmt_date($d['blood_date'] ?? '')) ?>, <?= e($d['blood_time'] ?? '') ?><?= e(dt(' Uhr')) ?></strong></span></div>
</div>
<h3 class="psec"><?= e(dt('II. Personalien')) ?></h3>
<div class="pgrid">
    <div class="pc w6"><span class="fl"><?= e(dt('Name, Vorname')) ?></span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('Größe')) ?></span><span class="fv"><?= e(($p['height'] ?? '') ? $p['height'] . ' cm' : '') ?></span></div>
    <div class="pc w2"><span class="fl"><?= e(dt('Gewicht')) ?></span><span class="fv"><?= e(($p['weight'] ?? '') ? $p['weight'] . ' kg' : '') ?></span></div>
</div>
<h3 class="psec"><?= e(dt('III. Ärztlicher Untersuchungsbefund')) ?></h3>
<table class="tbl be-tbl">
    <?php foreach ($rows as $k => [$label, $opts]): ?>
        <tr><th><?= e($label) ?></th><td><?php foreach ($opts as $o): ?><span class="nowrap"><?= box(($d[$k] ?? '') === $o) ?> <?= e(dt($o)) ?></span> &nbsp; <?php endforeach; ?></td></tr>
    <?php endforeach; ?>
    <tr><th><?= e(dt('Gesamteindruck')) ?></th><td><?= e(dt('Der/die Untersuchte steht äußerlich')) . "\n" ?>
        <?php foreach ($influence as $k => $l): ?><span class="nowrap"><?= box(($d['influence'] ?? '') === $k) ?> <?= e($l) ?></span> &nbsp; <?php endforeach; ?>
        <?= e(dt('unter Alkohol- / Drogeneinfluss.')) ?></td></tr>
</table>
<h3 class="psec"><?= e(dt('IV. Ergebnisse')) ?></h3>
<div class="pgrid">
    <div class="pc w45"><span class="fl"><?= e(dt('Blutalkoholkonzentration')) ?></span><span class="fv big"><?= e(($d['bac'] ?? '') !== '' ? $d['bac'] . ' ‰' . (doc_us() && is_numeric(str_replace(',', '.', (string) $d['bac'])) ? ' (' . number_format((float) str_replace(',', '.', (string) $d['bac']) / 10, 3) . ' % BAC)' : '') : '—') ?></span></div>
    <?php foreach ($tox as $k => $l): ?>
        <div class="pc w15 tox <?= ($d[$k] ?? '') === 'positiv' ? 'pos' : '' ?>"><span class="fl"><?= e($l) ?></span><span class="fv"><?= e(($d[$k] ?? '') !== '' ? dt((string) $d[$k]) : '—') ?></span></div>
    <?php endforeach; ?>
    <div class="pc w12 tall"><span class="fl"><?= e(dt('Bemerkungen')) ?></span><span class="fv"><?= nl($d['remarks'] ?? '') ?></span></div>
</div>
<p class="small"><?= e(dt('Die Blutprobe wurde mittels venöser Punktion (alkoholfreie Desinfektion) entnommen, in Anwesenheit der/des anordnenden Beamtin/Beamten versiegelt und übergeben.')) ?> <?= e(dt('Probennummer:')) ?> <span class="mono"><?= e($doc['number'] ?? '') ?>-B1</span></p>
<div class="proto-sign">
    <?= doc_signature($doc, 'rect', dt('Unterschrift entnehmende/r Arzt/Ärztin'), false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area">
        <?php if (!empty($d['officer_signature'])): ?><img class="sig-img" src="<?= e($d['officer_signature']) ?>" alt=""><?php endif; ?>
    </div><span class="sig-cap"><?= e(dt('Probe übernommen – Unterschrift Beamter/Beamtin')) ?></span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
