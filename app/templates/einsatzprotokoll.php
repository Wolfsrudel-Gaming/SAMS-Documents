<?php
defined('SAMS_ROOT') || exit;
/** Rettungsdienstliches Einsatzprotokoll – A4, angelehnt an gängige Notfallprotokolle */
$protoTitle = 'Einsatzprotokoll Rettungsdienst';
$protoSub = 'Notfall- und Transportdokumentation';
$gcs = array_sum(array_map('intval', [$d['gcs_eye'] ?? 0, $d['gcs_verbal'] ?? 0, $d['gcs_motor'] ?? 0]));
$measures = (array) ($d['measures'] ?? []);
$mOpts = [];
foreach ($type['fields'] as $f) {
    if (($f['name'] ?? '') === 'measures') {
        $mOpts = $f['options'];
    }
}
$outcomes = ['transport' => 'Transport in Klinik', 'ambulant' => 'Ambulante Versorgung', 'verweigerung' => 'Transportverweigerung', 'uebergabe' => 'Übergabe and. Rettungsmittel', 'tod' => 'Tod am Einsatzort', 'fehl' => 'Fehleinsatz'];
$missionNo = ($d['mission_no'] ?? '') !== '' ? $d['mission_no'] : date('ymd', strtotime($d['date'] ?? 'now')) . '-' . str_pad((string) ($doc['id'] ?? 0), 4, '0', STR_PAD_LEFT);
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w3"><span class="fl">Einsatz-Nr.</span><span class="fv mono"><?= e($missionNo) ?></span></div>
    <div class="pc w2"><span class="fl">Datum</span><span class="fv"><?= e(fmt_date($d['date'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl">Rufname / Fahrzeug</span><span class="fv"><?= e($d['unit'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">Besatzung</span><span class="fv"><?= e($d['crew'] ?? '') ?></span></div>

    <div class="pc w6"><span class="fl">Einsatzort</span><span class="fv"><?= e($d['location'] ?? '') ?></span></div>
    <div class="pc w15"><span class="fl">Alarm</span><span class="fv"><?= e($d['t_alarm'] ?? '') ?></span></div>
    <div class="pc w15"><span class="fl">An Pat.</span><span class="fv"><?= e($d['t_arrival'] ?? '') ?></span></div>
    <div class="pc w15"><span class="fl">Transport</span><span class="fv"><?= e($d['t_transport'] ?? '') ?></span></div>
    <div class="pc w15"><span class="fl">Übergabe</span><span class="fv"><?= e($d['t_handover'] ?? '') ?></span></div>
</div>

<h3 class="psec">Patient</h3>
<div class="pgrid">
    <?php if ($p): ?>
        <div class="pc w5"><span class="fl">Name, Vorname</span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
        <div class="pc w2"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
        <div class="pc w1"><span class="fl">Geschl.</span><span class="fv"><?= e(strtoupper($p['gender'] ?? '')) ?></span></div>
        <div class="pc w2"><span class="fl">Bürger-ID</span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
        <div class="pc w2"><span class="fl">Blutgruppe</span><span class="fv"><?= e($p['blood_type'] ?? '') ?></span></div>
        <div class="pc w6"><span class="fl">Kostenträger / Vers.-Nr.</span><span class="fv"><?= e(trim(($p['insurance'] ?? '') . ' ' . ($p['insurance_number'] ?? ''))) ?></span></div>
        <div class="pc w6"><span class="fl">Allergien</span><span class="fv"><?= e($p['allergies'] ?? '') ?: 'keine bekannt' ?></span></div>
    <?php else: ?>
        <div class="pc w12"><span class="fl">Name, Vorname</span><span class="fv"><?= !empty($d['unknown_person']) ? 'UNBEKANNTE PERSON' : '' ?></span></div>
    <?php endif; ?>
</div>

<h3 class="psec">Notfallgeschehen</h3>
<div class="pgrid"><div class="pc w12 tall"><span class="fv"><?= nl($d['call_reason'] ?? '') ?></span></div></div>

<h3 class="psec">Erstbefund</h3>
<div class="pgrid">
    <div class="pc w2"><span class="fl">RR</span><span class="fv"><?= e($d['rr'] ?? '') ?></span><span class="unit">mmHg</span></div>
    <div class="pc w2"><span class="fl">HF</span><span class="fv"><?= e($d['hf'] ?? '') ?></span><span class="unit">/min</span></div>
    <div class="pc w2"><span class="fl">SpO₂</span><span class="fv"><?= e($d['spo2'] ?? '') ?></span><span class="unit">%</span></div>
    <div class="pc w2"><span class="fl">AF</span><span class="fv"><?= e($d['af'] ?? '') ?></span><span class="unit">/min</span></div>
    <div class="pc w2"><span class="fl">BZ</span><span class="fv"><?= e($d['bz'] ?? '') ?></span><span class="unit">mg/dl</span></div>
    <div class="pc w2"><span class="fl">Temp.</span><span class="fv"><?= e($d['temp'] ?? '') ?></span><span class="unit">°C</span></div>

    <div class="pc w4"><span class="fl">GCS (A/V/M)</span><span class="fv"><?= e(($d['gcs_eye'] ?? '-') . ' / ' . ($d['gcs_verbal'] ?? '-') . ' / ' . ($d['gcs_motor'] ?? '-')) ?> <?= $gcs ? '= <strong>' . $gcs . '</strong>' : '' ?></span></div>
    <div class="pc w8"><span class="fl">Bewusstsein</span><span class="fv chk">
        <?php foreach (['wach' => 'wach', 'getrübt' => 'getrübt', 'bewusstlos' => 'bewusstlos', 'sediert' => 'analgosediert'] as $k => $l): ?><span class="nowrap"><?= box(($d['consciousness'] ?? '') === $k) ?> <?= e($l) ?></span> <?php endforeach; ?></span></div>
    <div class="pc w6"><span class="fl">Atmung</span><span class="fv chk">
        <?php foreach (['unauffällig', 'Dyspnoe', 'Zyanose', 'Apnoe', 'Stridor', 'Spastik'] as $k): ?><span class="nowrap"><?= box(in_array($k, (array) ($d['breathing'] ?? []), true)) ?> <?= e($k) ?></span> <?php endforeach; ?></span></div>
    <div class="pc w6"><span class="fl">Kreislauf</span><span class="fv chk">
        <?php foreach (['stabil', 'Schock', 'Kreislaufstillstand', 'Rhythmusstörung'] as $k): ?><span class="nowrap"><?= box(in_array($k, (array) ($d['circulation'] ?? []), true)) ?> <?= e($k) ?></span> <?php endforeach; ?></span></div>
    <div class="pc w8 tall"><span class="fl">Verletzungen / Befund</span><span class="fv"><?= nl($d['injuries'] ?? '') ?></span>
        <?php if (!empty($d['icd'])): ?><span class="fv"><strong>Erstdiagnose:</strong> <?= e($d['icd']) ?></span><?php endif; ?></div>
    <div class="pc w4 bm"><?= bodymap_svg((array) ($d['bodymap'] ?? [])) ?></div>
</div>

<h3 class="psec">Maßnahmen</h3>
<div class="pgrid">
    <div class="pc w12"><div class="chkgrid">
        <?php foreach ($mOpts as $k => $l): ?><span><?= box(in_array($k, $measures, true)) ?> <?= e($l) ?></span><?php endforeach; ?>
    </div></div>
    <div class="pc w12"><span class="fl">Medikamente</span><span class="fv"><?= nl($d['meds'] ?? '') ?: '—' ?></span></div>
    <div class="pc w12 tall"><span class="fl">Verlauf / Bemerkungen</span><span class="fv"><?= nl($d['course'] ?? '') ?></span></div>
</div>

<h3 class="psec">Ergebnis</h3>
<div class="pgrid">
    <div class="pc w8"><span class="fv chk"><?php foreach ($outcomes as $k => $l): ?><span class="nowrap"><?= box(($d['outcome'] ?? '') === $k) ?> <?= e($l) ?></span> <?php endforeach; ?></span></div>
    <div class="pc w2"><span class="fl">NACA</span><span class="fv big"><?= e($d['naca'] ?? '') ?></span></div>
    <div class="pc w2"><span class="fl">Übergabe</span><span class="fv"><?= e($d['t_handover'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl">Transportziel</span><span class="fv"><?= e($d['destination'] ?? '') ?></span></div>
</div>

<div class="proto-sign">
    <?= doc_signature($doc, '', 'Unterschrift Protokollführer/in', false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"></div><span class="sig-cap">Übernehmende/r Arzt/Ärztin (Klinik)</span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
