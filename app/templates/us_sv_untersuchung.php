<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Untersuchungsbogen Sonderverwendungsfähigkeit als US-Formular (Special Operations Medical Evaluation,
 * angelehnt an Musterungsbögen wie DD 2807/2808): nummerierte Felder, Abschnitte A–I, Anamnese mit Ja/Nein-Spalten,
 * Organsysteme Normal/Auffällig, Abgleich mit dem Standard, PULHES-Profil. Vertrauliche ärztliche Unterlage.
 * Gleiche Felder und Prüflogik (app/fitness.php) wie app/templates/sv_untersuchung.php.
 */
// US-Formate: Messwerte mit Dezimalpunkt, feste Auswahlwerte (z. B. „unauffällig“) über dt()
$dec = fn(string $s) => (string) preg_replace('/(\d),(\d)/', '$1.$2', $s);
$usv = fn(string $s) => $dec((string) preg_replace_callback('/^Klasse (\d)$/', fn($m) => dt('Klasse {n}', ['n' => $m[1]]), implode(' / ', array_map('dt', explode(' / ', $s)))));
$v = fn(string $k, string $unit = '') => ($d[$k] ?? '') !== '' && $d[$k] !== [] ? e($usv((string) $d[$k])) . ($unit !== '' ? ' ' . e($unit) : '') : '—';
$kinds = array_values(array_filter((array) ($d['verwendungen'] ?? []), fn($k) => isset(FITNESS_KINDS[$k])));
foreach (array_keys(FITNESS_KINDS) as $k) {
    if (($d['v_' . $k] ?? '') !== '' && !in_array($k, $kinds, true)) {
        $kinds[] = $k;
    }
}
$checks = sv_check($d, $kinds);
$mark = fn($ok) => $ok === null ? '<span class="us-gen-na">' . e(dt('nicht erhoben')) . '</span>' : ($ok ? '<span class="us-gen-ok">' . e(dt('✓ erfüllt')) . '</span>' : '<span class="us-gen-bad">' . e(dt('✗ nicht erfüllt')) . '</span>');
$bmi = ($h = sv_num($d, 'groesse')) && ($w = sv_num($d, 'gewicht')) ? (string) round($w / (($h / 100) ** 2), 1) : '';
$organs = (array) ($d['organe_auffaellig'] ?? []);
$anam = (array) ($d['anamnese'] ?? []);
$anamList = ['Herz-Kreislauf-Erkrankung', 'Asthma / Lungenerkrankung', 'Krampfanfälle / Bewusstseinsstörungen', 'Diabetes mellitus', 'Psychische Erkrankung', 'Schädel-Hirn-Trauma', 'Ohren / Trommelfell / Nasennebenhöhlen', 'Dekompressionsunfall', 'Wirbelsäule / Bandscheibe', 'Gelenkverletzungen / Operationen', 'Höhenangst / Platzangst', 'Dauermedikation', 'Nikotin', 'Alkohol / Drogen'];
$organList = ['Kopf / Hals', 'HNO', 'Herz', 'Lunge', 'Abdomen', 'Haut', 'Wirbelsäule', 'Obere Extremitäten', 'Untere Extremitäten', 'Neurologie (Reflexe, Koordination)'];
$resLabel = ['geeignet' => 'tauglich', 'bedingt' => 'tauglich mit Einschränkungen', 'befristet' => 'vorübergehend nicht tauglich', 'nicht' => 'nicht tauglich'];
$n = 0;
$L = function (string $label) use (&$n): string {
    return (++$n) . '. ' . dt($label);
};
$sec = 0;
$S = function (string $label) use (&$sec): string {
    return '<div class="us-sec">' . e(chr(65 + $sec++) . ' – ' . mb_strtoupper(dt($label))) . '</div>';
};
$cfg = ['style' => 'medical', 'title' => 'Untersuchungsbogen Sonderverwendungsfähigkeit', 'subtitle' => 'Ärztliche Untersuchung für Spezialkräfte (Special Operations Medical Evaluation)',
    'banner' => 'Vertraulich – ärztliche Schweigepflicht', 'seal' => 'rect', 'sig_caption' => 'Untersuchende/r Arzt/Ärztin'] + us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ' MED 2808';
}
$half = (int) ceil(count($anamList) / 2);
$ohalf = (int) ceil(count($organList) / 2);
echo us_paper_open($doc, $draft, 'us-medical us-gen-sv');
?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>

    <?= $S('Angaben zur Person und Anlass') ?>
    <div class="us-grid">
        <div class="us-cell w6"><span class="fl"><?= e($L('Name, Vorname')) ?></span><span class="fv"><b><?= e(patient_name($p, true)) ?></b></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Untersucht am')) ?></span><span class="fv"><?= e(fmt_date($d['exam_date'] ?? '')) ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Anlass')) ?></span><span class="fv"><?= $v('anlass') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Dienststelle')) ?></span><span class="fv"><?= $v('dienststelle') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Untersuchungsstandard')) ?></span><span class="fv"><?= e(dt(($d['standard'] ?? '') ?: SV_STANDARD_DEFAULT)) ?></span></div>
        <div class="us-cell w12"><span class="fl"><?= e($L('Beantragte Verwendungen')) ?></span><span class="fv"><span class="us-opts"><?php foreach (FITNESS_KINDS as $k => [$l]): ?><span class="us-opt"><?= box(in_array($k, $kinds, true)) ?> <?= e(dt($l)) ?></span><?php endforeach; ?></span></span></div>
    </div>

    <?= $S('Anamnese (Selbstauskunft, ärztlich erhoben)') ?>
    <div class="us-gen-yn">
        <?php foreach ([array_slice($anamList, 0, $half), array_slice($anamList, $half)] as $col): ?>
        <table class="us-gen-table">
            <tr><th><?= e(dt('Hat oder hatte die untersuchte Person …')) ?></th><th class="c"><?= e(dt('Ja')) ?></th><th class="c"><?= e(dt('Nein')) ?></th></tr>
            <?php foreach ($col as $a): ?><tr><td><?= e(dt($a)) ?></td><td class="c"><?= box(in_array($a, $anam, true)) ?></td><td class="c"><?= box(!in_array($a, $anam, true)) ?></td></tr><?php endforeach; ?>
        </table>
        <?php endforeach; ?>
    </div>
    <div class="us-grid">
        <div class="us-cell w12"><span class="fl"><?= e($L('Erläuterungen')) ?></span><span class="fv"><?= nl($d['anamnese_text'] ?? '') ?: '—' ?></span></div>
    </div>

    <?= $S('Körperliche Untersuchung') ?>
    <div class="us-grid">
        <div class="us-cell w2"><span class="fl"><?= e($L('Größe')) ?> (cm)</span><span class="fv"><?= $v('groesse') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Gewicht')) ?> (kg)</span><span class="fv"><?= $v('gewicht') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e((++$n) . '. BMI') ?></span><span class="fv"><?= e($bmi) ?: '—' ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Bauchumfang')) ?> (cm)</span><span class="fv"><?= $v('bauch') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Körperfett')) ?> (%)</span><span class="fv"><?= $v('koerperfett') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e((++$n) . '. SpO₂') ?> (%)</span><span class="fv"><?= $v('spo2') ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('Blutdruck')) ?> (mmHg)</span><span class="fv"><?= e(trim(($d['rr_sys'] ?? '') . '/' . ($d['rr_dia'] ?? ''), '/')) ?: '—' ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('Ruhepuls')) ?> (/min)</span><span class="fv"><?= $v('puls') ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('Gleichgewicht')) ?></span><span class="fv"><?= $v('romberg') ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('Zahnstatus')) ?></span><span class="fv"><?= ($d['zahn'] ?? '') !== '' ? e(dt('Klasse {n}', ['n' => $d['zahn']])) : '—' ?></span></div>
    </div>
    <div class="us-gen-yn">
        <?php foreach ([array_slice($organList, 0, $ohalf), array_slice($organList, $ohalf)] as $col): ?>
        <table class="us-gen-table">
            <tr><th><?= e(dt('Organsystem')) ?></th><th class="c"><?= e(dt('Normal')) ?></th><th class="c"><?= e(dt('Auffällig')) ?></th></tr>
            <?php foreach ($col as $o): ?><tr><td><?= e(dt($o)) ?></td><td class="c"><?= box(!in_array($o, $organs, true)) ?></td><td class="c"><?= box(in_array($o, $organs, true)) ?></td></tr><?php endforeach; ?>
        </table>
        <?php endforeach; ?>
    </div>

    <?= $S('Augen und Ohren') ?>
    <div class="us-grid">
        <div class="us-cell w3"><span class="fl"><?= e($L('Visus ohne Korrektur R / L')) ?></span><span class="fv"><?= $v('visus_sc_r') ?> / <?= $v('visus_sc_l') ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('Visus mit Korrektur R / L')) ?></span><span class="fv"><?= $v('visus_cc_r') ?> / <?= $v('visus_cc_l') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Farbsinn')) ?></span><span class="fv"><?= $v('farbsinn') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Räuml. Sehen')) ?></span><span class="fv"><?= $v('stereo') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Gesichtsfeld')) ?></span><span class="fv"><?= $v('gesichtsfeld') ?></span></div>
    </div>
    <table class="us-gen-table us-gen-audio">
        <tr><th><?= e(dt('Tonaudiogramm (dB)')) ?></th><?php foreach ([500, 1000, 2000, 3000, 4000, 6000] as $f): ?><th class="c"><?= $f >= 1000 ? ($f / 1000) . ' kHz' : "$f Hz" ?></th><?php endforeach; ?><th class="c"><?= e(dt('Druckausgleich')) ?></th></tr>
        <?php foreach (['r' => 'rechts', 'l' => 'links'] as $s => $l): ?><tr><th><?= e(dt($l)) ?></th><?php foreach ([500, 1000, 2000, 3000, 4000, 6000] as $f): ?><td class="c"><?= $v("h{$s}_$f") ?></td><?php endforeach; ?><?php if ($s === 'r'): ?><td class="c" rowspan="2"><?= $v('druckausgleich') ?></td><?php endif; ?></tr><?php endforeach; ?>
    </table>

    <?= $S('Apparative Diagnostik') ?>
    <div class="us-grid">
        <div class="us-cell w4"><span class="fl"><?= e($L('Ruhe-EKG')) ?></span><span class="fv"><?= $v('ekg') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Ergometrie')) ?></span><span class="fv"><?= $v('ergo_watt', 'W') ?> · <?= $v('ergo_befund') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Spirometrie FVC / FEV1 / Tiffeneau')) ?></span><span class="fv"><?= $v('fvc') ?> / <?= $v('fev1') ?> / <?= $v('tiffeneau') ?> %</span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Röntgen Thorax')) ?></span><span class="fv"><?= $v('roentgen') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Labor')) ?></span><span class="fv"><?= $v('labor') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Drogen-/Alkoholscreening')) ?></span><span class="fv"><?= $v('drogen') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Psychologische Eignung')) ?></span><span class="fv"><?= $v('psych') ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Psych. Bemerkung')) ?></span><span class="fv"><?= $v('psych_text') ?></span></div>
    </div>

    <?= $S('Leistungstests') ?>
    <div class="us-grid">
        <div class="us-cell w2"><span class="fl"><?= e((++$n) . '. 3000 m') ?></span><span class="fv"><?= $v('lauf') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Liegestütze')) ?></span><span class="fv"><?= $v('liegestuetz') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e((++$n) . '. Sit-ups') ?></span><span class="fv"><?= $v('situps') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Klimmzüge')) ?></span><span class="fv"><?= $v('klimmzug') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Marsch 12 km')) ?></span><span class="fv"><?= $v('marsch') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Schwimmen 500 m')) ?></span><span class="fv"><?= $v('schwimmen') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Streckentauchen')) ?></span><span class="fv"><?= $v('streckentauchen', 'm') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Wassertreten')) ?></span><span class="fv"><?= $v('wassertreten') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Druckkammer')) ?></span><span class="fv"><?= $v('druckkammer') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('AS-Übungsstrecke')) ?></span><span class="fv"><?= $v('atemschutzstrecke') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Steigtest')) ?></span><span class="fv"><?= $v('steigtest') ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e((++$n) . '. HUET') ?></span><span class="fv"><?= $v('huet') ?></span></div>
    </div>

    <?= $S('Abgleich mit dem Standard') ?>
    <table class="us-gen-table us-gen-check">
        <tr><th><?= e(dt('Anforderung')) ?></th><th><?= e(dt('Mindestanforderung')) ?></th><th><?= e(dt('Messwert')) ?></th><th><?= e(dt('für')) ?></th><th><?= e(dt('Bewertung')) ?></th></tr>
        <?php foreach ($checks as [$label, $norm, $value, $ok, $for]): ?>
            <tr class="<?= $ok === false ? 'us-gen-row-bad' : '' ?>"><td><?= e(dt($label)) ?></td><td><?= e($dec(dt($norm))) ?></td><td class="mono"><?= e($usv($value)) ?: '—' ?></td><td class="us-gen-small"><?= e(implode(', ', array_map(fn($k) => dt(FITNESS_KINDS[$k][1]), $for))) ?></td><td><?= $mark($ok) ?></td></tr>
        <?php endforeach; ?>
    </table>

    <?= $S('Beurteilung') ?>
    <div class="us-gen-pulhes">
        <span class="fl"><?= e($L('Gesundheitsprofil (PULHES)')) ?></span>
        <div class="us-gen-pulhes-row">
            <?php foreach (SV_PULHES as $k => [$l, $req]): $g = (string) ($d['pulhes_' . $k] ?? ''); ?>
                <div class="us-gen-pulhes-box<?= $g !== '' && (int) $g > $req ? ' bad' : '' ?>"><b><?= e(strtoupper($k)) ?></b><span class="us-gen-grade"><?= e($g) ?: '–' ?></span><small><?= e(dt($l)) ?></small><small><?= e(dt('mind. {n}', ['n' => $req])) ?></small></div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="us-grid">
        <?php foreach (FITNESS_KINDS as $k => [$l]): if (($d['v_' . $k] ?? '') === '') continue; ?>
            <div class="us-cell w6"><span class="fl"><?= e($L($l)) ?></span><span class="fv"><b><?= e(dt($resLabel[$d['v_' . $k]] ?? $d['v_' . $k])) ?></b></span></div>
        <?php endforeach; ?>
        <div class="us-cell w12"><span class="fl"><?= e($L('Gesamturteil')) ?></span><span class="fv"><span class="us-opts us-gen-overall"><?php foreach (SV_OVERALL as $k => $l): ?><span class="us-opt"><?= box(($d['gesamt'] ?? '') === $k) ?> <?= e(dt($l)) ?></span><?php endforeach; ?></span></span></div>
        <div class="us-cell w12"><span class="fl"><?= e($L('Einschränkungen / Auflagen (werden der Dienststelle mitgeteilt)')) ?></span><span class="fv"><?= nl($d['einschraenkungen'] ?? '') ?: e(dt('keine')) ?></span></div>
        <div class="us-cell w12 tall"><span class="fl"><?= e($L('Ärztliche Beurteilung, Diagnosen (vertraulich)')) ?></span><span class="fv"><?= nl($d['bemerkung'] ?? '') ?: '—' ?></span></div>
        <div class="us-cell w6"><span class="fl"><?= e($L('Gültig bis')) ?></span><span class="fv"><?= e(fmt_date($d['valid_until'] ?? '')) ?: e(dt('Regelfrist')) ?></span></div>
        <div class="us-cell w6"><span class="fl"><?= e($L('Nachuntersuchung spätestens')) ?></span><span class="fv"><?= e(fmt_date($d['nachuntersuchung'] ?? '')) ?: '—' ?></span></div>
    </div>
    <div class="us-box us-notice us-gen-small"><?= e(dt('Vertrauliche ärztliche Unterlage. Verbleibt beim untersuchenden Dienst und wird nicht an die Dienststelle weitergegeben. Die Dienststelle erhält eine gesonderte Bescheinigung ohne Befunde (nur Profil, Beurteilung je Verwendung, Einschränkungen, Gültigkeit).')) ?></div>
    <?= us_signatures($doc, $cfg, [['name' => '_sv_examinee', 'label' => 'Untersuchte Person (Angaben zur Anamnese vollständig und wahr)']]) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
