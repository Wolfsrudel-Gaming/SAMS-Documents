<?php
defined('SAMS_ROOT') || exit;
/** Untersuchungsbogen Sonderverwendungsfähigkeit – vertrauliche ärztliche Unterlage, verbleibt beim untersuchenden Dienst */
$protoTitle = 'Untersuchungsbogen Sonderverwendungsfähigkeit';
$protoSub = 'Arbeitsmedizinische Untersuchung Spezialkräfte · VERTRAULICH – ärztliche Schweigepflicht';
$v = fn(string $k, string $unit = '') => ($d[$k] ?? '') !== '' && $d[$k] !== [] ? e((string) $d[$k]) . ($unit !== '' ? ' ' . e($unit) : '') : '—';
$kinds = array_values(array_filter((array) ($d['verwendungen'] ?? []), fn($k) => isset(FITNESS_KINDS[$k])));
foreach (array_keys(FITNESS_KINDS) as $k) {
    if (($d['v_' . $k] ?? '') !== '' && !in_array($k, $kinds, true)) {
        $kinds[] = $k;
    }
}
$checks = sv_check($d, $kinds);
$mark = fn($ok) => $ok === null ? '<span class="sv-na">nicht erhoben</span>' : ($ok ? '<span class="sv-ok">✓ erfüllt</span>' : '<span class="sv-bad">✗ nicht erfüllt</span>');
$bmi = ($h = sv_num($d, 'groesse')) && ($w = sv_num($d, 'gewicht')) ? str_replace('.', ',', (string) round($w / (($h / 100) ** 2), 1)) : '';
$organs = (array) ($d['organe_auffaellig'] ?? []);
$anam = (array) ($d['anamnese'] ?? []);
$resLabel = ['geeignet' => 'tauglich', 'bedingt' => 'tauglich mit Einschränkungen', 'befristet' => 'vorübergehend nicht tauglich', 'nicht' => 'nicht tauglich'];
echo a4_open($doc, $draft, 'proto sv');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w5"><span class="fl">Name, Vorname</span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
    <div class="pc w2"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Bürger-ID</span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    <div class="pc w3"><span class="fl">Untersucht am</span><span class="fv"><?= e(fmt_date($d['exam_date'] ?? '')) ?></span></div>
    <div class="pc w4"><span class="fl">Anlass</span><span class="fv small"><?= $v('anlass') ?></span></div>
    <div class="pc w4"><span class="fl">Dienststelle</span><span class="fv small"><?= $v('dienststelle') ?></span></div>
    <div class="pc w4"><span class="fl">Untersuchungsstandard</span><span class="fv small"><?= e(($d['standard'] ?? '') ?: SV_STANDARD_DEFAULT) ?></span></div>
    <div class="pc w12"><span class="fl">Beantragte Verwendungen</span><span class="fv chk"><?php foreach (FITNESS_KINDS as $k => [$l]): ?><span class="nowrap"><?= box(in_array($k, $kinds, true)) ?> <?= e($l) ?></span> <?php endforeach; ?></span></div>
</div>

<h3 class="psec">Anamnese</h3>
<div class="pgrid">
    <div class="pc w12"><div class="chkgrid"><?php foreach (['Herz-Kreislauf-Erkrankung', 'Asthma / Lungenerkrankung', 'Krampfanfälle / Bewusstseinsstörungen', 'Diabetes mellitus', 'Psychische Erkrankung', 'Schädel-Hirn-Trauma', 'Ohren / Trommelfell / Nasennebenhöhlen', 'Dekompressionsunfall', 'Wirbelsäule / Bandscheibe', 'Gelenkverletzungen / Operationen', 'Höhenangst / Platzangst', 'Dauermedikation', 'Nikotin', 'Alkohol / Drogen'] as $a): ?><span><?= box(in_array($a, $anam, true)) ?> <?= e($a) ?></span><?php endforeach; ?></div></div>
    <div class="pc w12"><span class="fl">Erläuterungen</span><span class="fv small"><?= nl($d['anamnese_text'] ?? '') ?: '—' ?></span></div>
</div>

<h3 class="psec">Körperliche Untersuchung</h3>
<div class="pgrid">
    <div class="pc w2"><span class="fl">Größe</span><span class="fv"><?= $v('groesse') ?></span><span class="unit">cm</span></div>
    <div class="pc w2"><span class="fl">Gewicht</span><span class="fv"><?= $v('gewicht') ?></span><span class="unit">kg</span></div>
    <div class="pc w2"><span class="fl">BMI</span><span class="fv"><?= e($bmi) ?: '—' ?></span></div>
    <div class="pc w2"><span class="fl">Bauchumfang</span><span class="fv"><?= $v('bauch') ?></span><span class="unit">cm</span></div>
    <div class="pc w2"><span class="fl">Körperfett</span><span class="fv"><?= $v('koerperfett') ?></span><span class="unit">%</span></div>
    <div class="pc w2"><span class="fl">SpO₂</span><span class="fv"><?= $v('spo2') ?></span><span class="unit">%</span></div>
    <div class="pc w3"><span class="fl">Blutdruck</span><span class="fv"><?= e(trim(($d['rr_sys'] ?? '') . '/' . ($d['rr_dia'] ?? ''), '/')) ?: '—' ?></span><span class="unit">mmHg</span></div>
    <div class="pc w3"><span class="fl">Ruhepuls</span><span class="fv"><?= $v('puls') ?></span><span class="unit">/min</span></div>
    <div class="pc w3"><span class="fl">Gleichgewicht</span><span class="fv small"><?= $v('romberg') ?></span></div>
    <div class="pc w3"><span class="fl">Zahnstatus</span><span class="fv"><?= ($d['zahn'] ?? '') !== '' ? 'Klasse ' . e($d['zahn']) : '—' ?></span></div>
    <div class="pc w12"><span class="fl">Organsysteme (angekreuzt = auffällig, sonst ohne Befund)</span><div class="chkgrid"><?php foreach (['Kopf / Hals', 'HNO', 'Herz', 'Lunge', 'Abdomen', 'Haut', 'Wirbelsäule', 'Obere Extremitäten', 'Untere Extremitäten', 'Neurologie (Reflexe, Koordination)'] as $o): ?><span><?= box(in_array($o, $organs, true)) ?> <?= e($o) ?></span><?php endforeach; ?></div></div>
</div>

<h3 class="psec">Augen und Ohren</h3>
<div class="pgrid">
    <div class="pc w3"><span class="fl">Visus ohne Korrektur R / L</span><span class="fv"><?= $v('visus_sc_r') ?> / <?= $v('visus_sc_l') ?></span></div>
    <div class="pc w3"><span class="fl">Visus mit Korrektur R / L</span><span class="fv"><?= $v('visus_cc_r') ?> / <?= $v('visus_cc_l') ?></span></div>
    <div class="pc w2"><span class="fl">Farbsinn</span><span class="fv small"><?= $v('farbsinn') ?></span></div>
    <div class="pc w2"><span class="fl">Räuml. Sehen</span><span class="fv small"><?= $v('stereo') ?></span></div>
    <div class="pc w2"><span class="fl">Gesichtsfeld</span><span class="fv small"><?= $v('gesichtsfeld') ?></span></div>
</div>
<table class="tbl sv-audio">
    <tr><th>Tonaudiogramm (dB)</th><?php foreach ([500, 1000, 2000, 3000, 4000, 6000] as $f): ?><th><?= $f >= 1000 ? ($f / 1000) . ' kHz' : "$f Hz" ?></th><?php endforeach; ?><th>Druckausgleich</th></tr>
    <?php foreach (['r' => 'rechts', 'l' => 'links'] as $s => $l): ?><tr><th><?= $l ?></th><?php foreach ([500, 1000, 2000, 3000, 4000, 6000] as $f): ?><td><?= $v("h{$s}_$f") ?></td><?php endforeach; ?><?php if ($s === 'r'): ?><td rowspan="2"><?= $v('druckausgleich') ?></td><?php endif; ?></tr><?php endforeach; ?>
</table>

<h3 class="psec">Apparative Diagnostik und Leistungstests</h3>
<div class="pgrid">
    <div class="pc w3"><span class="fl">Ruhe-EKG</span><span class="fv small"><?= $v('ekg') ?></span></div>
    <div class="pc w3"><span class="fl">Ergometrie</span><span class="fv small"><?= $v('ergo_watt', 'W') ?> · <?= $v('ergo_befund') ?></span></div>
    <div class="pc w3"><span class="fl">Spirometrie FVC / FEV1 / Tiffeneau</span><span class="fv small"><?= $v('fvc') ?> / <?= $v('fev1') ?> / <?= $v('tiffeneau') ?> %</span></div>
    <div class="pc w3"><span class="fl">Röntgen Thorax</span><span class="fv small"><?= $v('roentgen') ?></span></div>
    <div class="pc w3"><span class="fl">Labor</span><span class="fv small"><?= $v('labor') ?></span></div>
    <div class="pc w3"><span class="fl">Drogen-/Alkoholscreening</span><span class="fv small"><?= $v('drogen') ?></span></div>
    <div class="pc w3"><span class="fl">Psychologische Eignung</span><span class="fv small"><?= $v('psych') ?></span></div>
    <div class="pc w3"><span class="fl">Psych. Bemerkung</span><span class="fv small"><?= $v('psych_text') ?></span></div>
    <div class="pc w2"><span class="fl">3000 m</span><span class="fv"><?= $v('lauf') ?></span></div>
    <div class="pc w2"><span class="fl">Liegestütze</span><span class="fv"><?= $v('liegestuetz') ?></span></div>
    <div class="pc w2"><span class="fl">Sit-ups</span><span class="fv"><?= $v('situps') ?></span></div>
    <div class="pc w2"><span class="fl">Klimmzüge</span><span class="fv"><?= $v('klimmzug') ?></span></div>
    <div class="pc w2"><span class="fl">Marsch 12 km</span><span class="fv"><?= $v('marsch') ?></span></div>
    <div class="pc w2"><span class="fl">Schwimmen 500 m</span><span class="fv"><?= $v('schwimmen') ?></span></div>
    <div class="pc w2"><span class="fl">Streckentauchen</span><span class="fv"><?= $v('streckentauchen', 'm') ?></span></div>
    <div class="pc w2"><span class="fl">Wassertreten</span><span class="fv small"><?= $v('wassertreten') ?></span></div>
    <div class="pc w2"><span class="fl">Druckkammer</span><span class="fv small"><?= $v('druckkammer') ?></span></div>
    <div class="pc w2"><span class="fl">AS-Übungsstrecke</span><span class="fv small"><?= $v('atemschutzstrecke') ?></span></div>
    <div class="pc w2"><span class="fl">Steigtest</span><span class="fv small"><?= $v('steigtest') ?></span></div>
    <div class="pc w2"><span class="fl">HUET</span><span class="fv small"><?= $v('huet') ?></span></div>
</div>

<h3 class="psec">Abgleich mit dem Standard</h3>
<table class="tbl sv-check">
    <tr><th>Anforderung</th><th>Mindestanforderung</th><th>Messwert</th><th>für</th><th>Bewertung</th></tr>
    <?php foreach ($checks as [$label, $norm, $value, $ok, $for]): ?>
        <tr class="<?= $ok === false ? 'sv-row-bad' : '' ?>"><td><?= e($label) ?></td><td><?= e($norm) ?></td><td><?= e($value) ?: '—' ?></td><td class="small"><?= e(implode(', ', array_map(fn($k) => FITNESS_KINDS[$k][1], $for))) ?></td><td><?= $mark($ok) ?></td></tr>
    <?php endforeach; ?>
</table>

<h3 class="psec">Beurteilung</h3>
<div class="pgrid">
    <?php foreach (SV_PULHES as $k => [$l, $req]): $g = (string) ($d['pulhes_' . $k] ?? ''); ?>
        <div class="pc w2"><span class="fl"><?= e($l) ?></span><span class="fv big<?= $g !== '' && (int) $g > $req ? ' sv-bad' : '' ?>"><?= e($g) ?: '–' ?></span><span class="unit">mind. <?= $req ?></span></div>
    <?php endforeach; ?>
    <?php foreach (FITNESS_KINDS as $k => [$l]): if (($d['v_' . $k] ?? '') === '') continue; ?>
        <div class="pc w6"><span class="fl"><?= e($l) ?></span><span class="fv small"><strong><?= e($resLabel[$d['v_' . $k]] ?? $d['v_' . $k]) ?></strong></span></div>
    <?php endforeach; ?>
    <div class="pc w12"><span class="fl">Gesamturteil</span><span class="fv big"><?= e(SV_OVERALL[$d['gesamt'] ?? ''] ?? '—') ?></span></div>
    <div class="pc w12"><span class="fl">Einschränkungen / Auflagen (werden der Dienststelle mitgeteilt)</span><span class="fv small"><?= nl($d['einschraenkungen'] ?? '') ?: 'keine' ?></span></div>
    <div class="pc w12 tall"><span class="fl">Ärztliche Beurteilung, Diagnosen (vertraulich)</span><span class="fv small"><?= nl($d['bemerkung'] ?? '') ?: '—' ?></span></div>
    <div class="pc w6"><span class="fl">Gültig bis</span><span class="fv"><?= e(fmt_date($d['valid_until'] ?? '')) ?: 'Regelfrist' ?></span></div>
    <div class="pc w6"><span class="fl">Nachuntersuchung spätestens</span><span class="fv"><?= e(fmt_date($d['nachuntersuchung'] ?? '')) ?: '—' ?></span></div>
</div>
<p class="small sv-note">Vertrauliche ärztliche Unterlage. Verbleibt beim untersuchenden Dienst und wird nicht an die Dienststelle weitergegeben. Die Dienststelle erhält eine gesonderte Bescheinigung ohne Befunde (nur Profil, Beurteilung je Verwendung, Einschränkungen, Gültigkeit).</p>
<div class="proto-sign">
    <?= doc_signature($doc, 'rect', 'Untersuchende/r Arzt/Ärztin', false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"></div><span class="sig-cap">Untersuchte Person (Angaben zur Anamnese vollständig und wahr)</span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
