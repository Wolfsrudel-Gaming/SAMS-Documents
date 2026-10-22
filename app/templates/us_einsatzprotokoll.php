<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Rettungsdienstliches Einsatzprotokoll als US-Patientenversorgungsbericht (EMS Patient Care Report / Run Sheet):
 * Einsatzleiste, Zeitenraster, Vitalzeichentabelle, Befund, Maßnahmen, Verlaufsbericht, Verbleib.
 * Felder und gespeicherte Auswahlwerte wie einsatzprotokoll.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'ems', 'form' => 'EMS-PCR 100', 'sections' => 'letters', 'numbered' => true, 'seal' => '',
    'title' => dt('Patientenversorgungsbericht'), 'subtitle' => dt('Rettungsdienst – Einsatz- und Transportprotokoll (Patient Care Report)'),
    'blocks' => ['hipaa'], 'copies' => [dt('Weiß – Zielklinik'), dt('Gelb – Rettungsdienst'), dt('Rosa – Abrechnung')],
    'sig_caption' => dt('Unterschrift Protokollführer/in'),
], us_layout_registry()[$type['key'] ?? ''] ?? []));
$F = [];
foreach ($type['fields'] as $f) {
    if (!empty($f['name'])) {
        $F[$f['name']] = $f;
    }
}
$n = 0;
$cell = function (string $label, string $html, string $w = 'w4', string $cls = '') use (&$n, $cfg): string {
    return '<div class="us-cell ' . $w . ($cls !== '' ? ' ' . $cls : '') . '"><span class="fl">' . e(($cfg['numbered'] ? (++$n) . '. ' : '') . $label) . '</span><span class="fv">' . $html . '</span></div>';
};
$letter = 0;
$sec = function (string $label) use (&$letter, $cfg): string {
    $prefix = $cfg['sections'] === 'letters' ? chr(65 + $letter++) . ' – ' : ($cfg['sections'] === 'numbers' ? (++$letter) . '. ' : '');
    return '<div class="us-sec">' . e($prefix . mb_strtoupper($label)) . '</div>';
};
$opts = fn (string $name) => isset($F[$name]) ? us_field_value($F[$name], $d[$name] ?? '') : '';
// Zeitintervalle in Minuten (über Mitternacht hinweg)
$mins = function (string $a, string $b) use ($d): string {
    $x = (string) ($d[$a] ?? '');
    $y = (string) ($d[$b] ?? '');
    if (!preg_match('/^\d{1,2}:\d{2}/', $x) || !preg_match('/^\d{1,2}:\d{2}/', $y)) {
        return '';
    }
    $m = (int) round((strtotime('2000-01-01 ' . $y) - strtotime('2000-01-01 ' . $x)) / 60);
    return (string) ($m < 0 ? $m + 1440 : $m);
};
$gcsParts = [$d['gcs_eye'] ?? '', $d['gcs_verbal'] ?? '', $d['gcs_motor'] ?? ''];
$gcs = array_sum(array_map('intval', $gcsParts));
$gcsText = implode('/', array_map(fn($x) => (string) $x !== '' ? (string) $x : '–', $gcsParts)) . ($gcs ? ' = ' . $gcs : '');
$firstVitals = trim(implode('', array_map(fn($k) => (string) ($d[$k] ?? ''), ['rr', 'hf', 'af', 'spo2', 'bz', 'temp']))) !== '' || $gcs;
$missionNo = ($d['mission_no'] ?? '') !== '' ? (string) $d['mission_no'] : date('ymd', strtotime((string) (($d['date'] ?? '') ?: 'now')) ?: time()) . '-' . str_pad((string) ($doc['id'] ?? 0), 4, '0', STR_PAD_LEFT);
$sex = ['m' => dt('männlich'), 'w' => dt('weiblich'), 'd' => dt('divers')][$p['gender'] ?? ''] ?? '';
$age = '';
if (($p['dob'] ?? '') !== '' && strtotime((string) $p['dob'])) {
    $age = (string) (new DateTime((string) $p['dob']))->diff(new DateTime(strtotime((string) ($d['date'] ?? '')) ? (string) $d['date'] : 'now'))->y;
}
$intervals = array_filter([
    dt('Eintreffzeit') => $mins('t_alarm', 't_arrival'),
    dt('Zeit vor Ort') => $mins('t_arrival', 't_transport'),
    dt('Transportdauer') => $mins('t_transport', 't_handover'),
], fn($v) => $v !== '');
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-pcr');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Einsatz')) ?>
    <div class="us-grid">
        <?= $cell(dt('Einsatz-Nr.'), '<b>' . e($missionNo) . '</b>', 'w3') ?>
        <?= $cell(dt('Datum'), e(fmt_date($d['date'] ?? '')), 'w2') ?>
        <?= $cell(dt('Einheit / Rufname'), e($d['unit'] ?? ''), 'w3') ?>
        <?= $cell(dt('Besatzung'), e($d['crew'] ?? ''), 'w4') ?>
        <?= $cell(dt('Einsatzort'), e($d['location'] ?? ''), 'w12') ?>
    </div>

    <?= $sec(dt('Einsatzzeiten')) ?>
    <table class="us-med-times">
        <tr>
            <th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Alarmierung (Dispatched)')) ?></th>
            <th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Ausgerückt (En Route)')) ?></th>
            <th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Am Patienten (On Scene)')) ?></th>
            <th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Transportbeginn (Transport)')) ?></th>
            <th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Ankunft Klinik / Übergabe (At Hospital)')) ?></th>
        </tr>
        <tr>
            <td><?= e($d['t_alarm'] ?? '') ?></td>
            <td></td>
            <td><?= e($d['t_arrival'] ?? '') ?></td>
            <td><?= e($d['t_transport'] ?? '') ?></td>
            <td><?= e($d['t_handover'] ?? '') ?></td>
        </tr>
    </table>
    <?php if ($intervals): ?><div class="us-med-ints"><?php foreach ($intervals as $label => $m): ?><span><?= e($label) ?>: <b><?= e(dt('{n} min', ['n' => $m])) ?></b></span><?php endforeach; ?></div><?php endif; ?>

    <?= $sec(dt('Patient/in')) ?>
    <div class="us-grid">
        <?php if ($p): ?>
            <?= $cell(dt('Name, Vorname'), '<b>' . e(patient_name($p, true)) . '</b>', 'us-med-w5') ?>
            <?= $cell(dt('geb. am'), e(fmt_date($p['dob'] ?? '')), 'w2') ?>
            <?= $cell(dt('Alter'), e($age), 'us-med-w1') ?>
            <?= $cell(dt('Geschlecht'), e($sex), 'w2') ?>
            <?= $cell(dt('Bürger-ID'), '<span class="mono">' . e($p['citizen_id'] ?? '') . '</span>', 'w2') ?>
            <?= $cell(dt('Blutgruppe'), e($p['blood_type'] ?? ''), 'w2') ?>
            <?= $cell(dt('Allergien'), e(($p['allergies'] ?? '') !== '' ? $p['allergies'] : dt('keine bekannt')), 'w4') ?>
            <?= $cell(dt('Kostenträger / Vers.-Nr.'), e(trim(($p['insurance'] ?? '') . ' ' . ($p['insurance_number'] ?? ''))), 'w6') ?>
        <?php else: ?>
            <?= $cell(dt('Name, Vorname'), !empty($d['unknown_person']) ? '<b>' . e(dt('UNBEKANNTE PERSON')) . '</b>' : '', 'w6') ?>
            <?= $cell(dt('Personalien'), '<span class="us-opts"><span class="us-opt">' . box(!empty($d['unknown_person'])) . ' ' . e(dt('nicht feststellbar (unbekannte Person)')) . '</span></span>', 'w6') ?>
        <?php endif; ?>
    </div>

    <?= $sec(dt('Meldebild und Arbeitsdiagnose (Chief Complaint / Impression)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Meldebild / Notfallgeschehen'), nl($d['call_reason'] ?? ''), 'us-med-w8', 'tall') ?>
        <?= $cell(dt('Erstdiagnose (Primary Impression)'), e($d['icd'] ?? ''), 'w4', 'tall') ?>
    </div>

    <?= $sec(dt('Vitalzeichen (Vital Signs)')) ?>
    <table class="us-med-vitals">
        <tr>
            <th><?= e(dt('Zeit')) ?></th><th><?= e(dt('RR (mmHg)')) ?></th><th><?= e(dt('HF (/min)')) ?></th><th><?= e(dt('AF (/min)')) ?></th>
            <th><?= e(dt('SpO₂ (%)')) ?></th><th><?= e(dt('BZ (mg/dl)')) ?></th><th><?= e(dt('Temp. (°C)')) ?></th><th><?= e(dt('GCS (A/V/M)')) ?></th>
        </tr>
        <tr>
            <td><?= e($firstVitals ? trim(($d['t_arrival'] ?? '') . ' ' . dt('Erstbefund')) : '') ?></td>
            <td><?= e($d['rr'] ?? '') ?></td><td><?= e($d['hf'] ?? '') ?></td><td><?= e($d['af'] ?? '') ?></td>
            <td><?= e($d['spo2'] ?? '') ?></td><td><?= e($d['bz'] ?? '') ?></td><td><?= e($d['temp'] ?? '') ?></td>
            <td><?= e($gcs ? $gcsText : '') ?></td>
        </tr>
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
        <tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>
    </table>

    <?= $sec(dt('Befund (Assessment)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Bewusstsein'), $opts('consciousness'), 'w12') ?>
        <?= $cell(dt('Atmung'), $opts('breathing'), 'w6') ?>
        <?= $cell(dt('Kreislauf'), $opts('circulation'), 'w6') ?>
        <?= $cell(dt('Verletzungen / Befund'), nl($d['injuries'] ?? ''), 'us-med-w8', 'us-med-xl') ?>
        <div class="us-cell w4 us-med-bm"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Verletzungsmuster (Körperschema)')) ?></span><?= bodymap_svg((array) ($d['bodymap'] ?? [])) ?></div>
    </div>

    <?= $sec(dt('Maßnahmen (Treatment)')) ?>
    <div class="us-grid">
        <div class="us-cell w12"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Durchgeführte Maßnahmen')) ?></span>
            <div class="us-med-checks"><?php foreach ($F['measures']['options'] ?? [] as $k => $l): ?><span><?= box(in_array((string) $k, array_map('strval', (array) ($d['measures'] ?? [])), true)) ?> <?= e((string) $l) ?></span><?php endforeach; ?></div></div>
        <?= $cell(dt('Medikamente (Präparat, Dosis, Uhrzeit)'), nl($d['meds'] ?? ''), 'w12', 'tall') ?>
    </div>

    <?= $sec(dt('Verlaufsbericht (Narrative)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Verlauf / Bemerkungen'), nl($d['course'] ?? ''), 'w12', 'us-med-xl') ?>
    </div>

    <?= $sec(dt('Verbleib (Disposition)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Einsatzergebnis'), $opts('outcome'), 'w12') ?>
        <?= $cell(dt('Transportziel'), e($d['destination'] ?? ''), 'w6') ?>
        <?= $cell(dt('NACA-Score'), e(opt_label($type, 'naca', $d['naca'] ?? '')), 'w4') ?>
        <?= $cell(dt('Übergabe'), e($d['t_handover'] ?? ''), 'w2') ?>
    </div>

    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg, [['name' => '_receiving_signature', 'label' => dt('Übernehmende/r Arzt/Ärztin (Klinik)')]]) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
