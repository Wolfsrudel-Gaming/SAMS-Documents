<?php
defined('SAMS_ROOT') || exit;
/** Protokoll und ärztlicher Bericht zur Blutentnahme auf behördliche Anordnung */
$protoTitle = 'Protokoll Blutentnahme';
$protoSub = 'und ärztlicher Untersuchungsbericht (§ 81a StPO analog)';
$influence = ['nicht' => 'nicht merkbar', 'leicht' => 'leicht', 'deutlich' => 'deutlich', 'stark' => 'stark'];
$rows = [
    'gait' => ['Gang (geradeaus)', ['sicher', 'schwankend', 'torkelnd', 'nicht möglich']],
    'turn' => ['Plötzliche Kehrtwendung', ['sicher', 'unsicher', 'nicht möglich']],
    'finger_nose' => ['Finger-Nasen-Prüfung', ['sicher', 'unsicher', 'daneben']],
    'speech' => ['Sprache', ['deutlich', 'verwaschen', 'lallend']],
    'pupils' => ['Pupillen', ['unauffällig', 'eng', 'weit', 'lichtstarr']],
    'mood' => ['Bewusstsein / Verhalten', ['klar', 'benommen', 'distanzlos', 'aggressiv', 'apathisch']],
    'odor' => ['Alkoholgeruch', ['nein', 'leicht', 'deutlich']],
];
$tox = ['thc' => 'THC', 'coc' => 'Kokain', 'amp' => 'Amphetamine / Meth.', 'opi' => 'Opiate', 'benzo' => 'Benzodiazepine'];
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<h3 class="psec">I. Anordnung</h3>
<div class="pgrid">
    <div class="pc w4"><span class="fl">Anordnende/r Beamter/in</span><span class="fv"><?= e($d['officer'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">Dienstnr. / Dienststelle</span><span class="fv"><?= e($d['officer_no'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">Aktenzeichen</span><span class="fv mono"><?= e($d['case_no'] ?? '') ?></span></div>
    <div class="pc w6"><span class="fl">Anlass</span><span class="fv"><?= e(opt_label($type, 'reason', $d['reason'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Vorfallzeit</span><span class="fv"><?= e($d['incident_time'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">Blutentnahme am / um</span><span class="fv"><strong><?= e(fmt_date($d['blood_date'] ?? '')) ?>, <?= e($d['blood_time'] ?? '') ?> Uhr</strong></span></div>
</div>
<h3 class="psec">II. Personalien</h3>
<div class="pgrid">
    <div class="pc w6"><span class="fl">Name, Vorname</span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
    <div class="pc w2"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Größe</span><span class="fv"><?= e(($p['height'] ?? '') ? $p['height'] . ' cm' : '') ?></span></div>
    <div class="pc w2"><span class="fl">Gewicht</span><span class="fv"><?= e(($p['weight'] ?? '') ? $p['weight'] . ' kg' : '') ?></span></div>
</div>
<h3 class="psec">III. Ärztlicher Untersuchungsbefund</h3>
<table class="tbl be-tbl">
    <?php foreach ($rows as $k => [$label, $opts]): ?>
        <tr><th><?= e($label) ?></th><td><?php foreach ($opts as $o): ?><span class="nowrap"><?= box(($d[$k] ?? '') === $o) ?> <?= e($o) ?></span> &nbsp; <?php endforeach; ?></td></tr>
    <?php endforeach; ?>
    <tr><th>Gesamteindruck</th><td>Der/die Untersuchte steht äußerlich
        <?php foreach ($influence as $k => $l): ?><span class="nowrap"><?= box(($d['influence'] ?? '') === $k) ?> <?= e($l) ?></span> &nbsp; <?php endforeach; ?>
        unter Alkohol- / Drogeneinfluss.</td></tr>
</table>
<h3 class="psec">IV. Ergebnisse</h3>
<div class="pgrid">
    <div class="pc w45"><span class="fl">Blutalkoholkonzentration</span><span class="fv big"><?= e(($d['bac'] ?? '') !== '' ? $d['bac'] . ' ‰' : '—') ?></span></div>
    <?php foreach ($tox as $k => $l): ?>
        <div class="pc w15 tox <?= ($d[$k] ?? '') === 'positiv' ? 'pos' : '' ?>"><span class="fl"><?= e($l) ?></span><span class="fv"><?= e(($d[$k] ?? '') !== '' ? $d[$k] : '—') ?></span></div>
    <?php endforeach; ?>
    <div class="pc w12 tall"><span class="fl">Bemerkungen</span><span class="fv"><?= nl($d['remarks'] ?? '') ?></span></div>
</div>
<p class="small">Die Blutprobe wurde mittels venöser Punktion (alkoholfreie Desinfektion) entnommen, in Anwesenheit der/des anordnenden Beamtin/Beamten versiegelt und übergeben. Probennummer: <span class="mono"><?= e($doc['number'] ?? '') ?>-B1</span></p>
<div class="proto-sign">
    <?= doc_signature($doc, 'rect', 'Unterschrift entnehmende/r Arzt/Ärztin', false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area">
        <?php if (!empty($d['officer_signature'])): ?><img class="sig-img" src="<?= e($d['officer_signature']) ?>" alt=""><?php endif; ?>
    </div><span class="sig-cap">Probe übernommen – Unterschrift Beamter/Beamtin</span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
