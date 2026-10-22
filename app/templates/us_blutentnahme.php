<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Blutentnahme auf behördliche Anordnung als US-Bericht zum chemischen Test bei Verdacht auf Fahren unter Einfluss (DUI)
 * mit klinischem Befund, Ergebnissen, Probenentnahme und Beweismittelkette (Chain of Custody).
 * Felder und gespeicherte Auswahlwerte wie blutentnahme.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-TOX 3560', 'sections' => 'letters', 'numbered' => true, 'seal' => 'rect',
    'title' => dt('Blutentnahme – chemischer Test'), 'subtitle' => dt('Bericht bei Verdacht auf Fahren unter Alkohol- oder Drogeneinfluss (DUI) mit Beweismittelkette'),
    'banner' => dt('Beweismittel – Probe nur versiegelt weitergeben'),
    'copies' => [dt('Weiß – Ermittlungsbehörde'), dt('Gelb – Labor'), dt('Rosa – Patientenakte')],
    'sig_caption' => dt('Unterschrift entnehmende/r Arzt/Ärztin'),
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
// Ankreuzreihe aus den Auswahlwerten der Dokumentart (gespeicherte Schlüssel, übersetzte Beschriftungen)
$row = function (string $name) use ($F, $d): string {
    $out = '';
    foreach ($F[$name]['options'] ?? [] as $k => $l) {
        if ((string) $k !== '') {
            $out .= '<span class="us-opt">' . box((string) ($d[$name] ?? '') === (string) $k) . ' ' . e((string) $l) . '</span>';
        }
    }
    return '<span class="us-opts">' . $out . '</span>';
};
$bacRaw = trim((string) ($d['bac'] ?? ''));
$bacNum = is_numeric(str_replace(',', '.', $bacRaw)) ? (float) str_replace(',', '.', $bacRaw) : null;
$tox = ['thc' => dt('THC'), 'coc' => dt('Kokain'), 'amp' => dt('Amphetamine / Meth.'), 'opi' => dt('Opiate'), 'benzo' => dt('Benzodiazepine')];
$specimen = doc_code_display((string) ($doc['number'] ?? '')) . '-B1';
$drawn = trim(fmt_date($d['blood_date'] ?? '') . ' ' . ($d['blood_time'] ?? ''));
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-bac');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Anordnung (Requesting Officer)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Anordnende/r Beamter/in'), e($d['officer'] ?? ''), 'w4') ?>
        <?= $cell(dt('Dienstnr. / Dienststelle'), e($d['officer_no'] ?? ''), 'w4') ?>
        <?= $cell(dt('Aktenzeichen'), '<b>' . e($d['case_no'] ?? '') . '</b>', 'w4') ?>
        <?= $cell(dt('Anlass'), e(opt_label($type, 'reason', $d['reason'] ?? '')), 'w6') ?>
        <?= $cell(dt('Vorfallzeit'), e($d['incident_time'] ?? ''), 'w2') ?>
        <?= $cell(dt('Blutentnahme am / um'), '<b>' . e($drawn) . '</b>', 'w4') ?>
    </div>

    <?= $sec(dt('Untersuchte Person')) ?>
    <div class="us-grid">
        <?= $cell(dt('Name, Vorname'), '<b>' . e($p ? patient_name($p, true) : '') . '</b>', 'w6') ?>
        <?= $cell(dt('geb. am'), e(fmt_date($p['dob'] ?? '')), 'w2') ?>
        <?= $cell(dt('Größe'), e(($p['height'] ?? '') !== '' ? $p['height'] . ' cm' : ''), 'w2') ?>
        <?= $cell(dt('Gewicht'), e(($p['weight'] ?? '') !== '' ? $p['weight'] . ' kg' : ''), 'w2') ?>
    </div>

    <?= $sec(dt('Klinischer Befund (Clinical Observations)')) ?>
    <table class="us-med-tbl us-med-obs">
        <?php foreach (['gait', 'turn', 'finger_nose', 'speech', 'pupils', 'mood', 'odor'] as $k): if (!isset($F[$k])) continue; ?>
            <tr><th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . (string) $F[$k]['label']) ?></th><td><?= $row($k) ?></td></tr>
        <?php endforeach; ?>
        <tr><th><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Gesamteindruck')) ?></th><td><?= e(dt('Der/die Untersuchte steht äußerlich')) ?> <?= $row('influence') ?> <?= e(dt('unter Alkohol- / Drogeneinfluss.')) ?></td></tr>
    </table>

    <?= $sec(dt('Ergebnisse (Test Results)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Blutalkoholkonzentration'), $bacRaw !== '' ? '<b class="us-med-big">' . e($bacRaw) . ' ‰</b>' : '—', 'w3') ?>
        <?= $cell(dt('BAC (g/100 ml)'), $bacNum !== null ? '<b class="us-med-big">' . e(number_format($bacNum / 10, 3)) . ' %</b>' . ($bacNum / 10 >= 0.08 ? '<span class="us-med-warn">' . e(dt('über dem Grenzwert von 0,08 %')) . '</span>' : '') : '—', 'w3') ?>
        <?php foreach ($tox as $k => $l): ?>
            <?= $cell($l, e(($d[$k] ?? '') !== '' ? opt_label($type, $k, $d[$k]) : '—'), 'w2', ($d[$k] ?? '') === 'positiv' ? 'us-med-pos' : '') ?>
        <?php endforeach; ?>
        <?= $cell(dt('Bemerkungen'), nl($d['remarks'] ?? ''), 'us-med-w8', 'tall') ?>
    </div>

    <?= $sec(dt('Probenentnahme (Specimen)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Probennummer'), '<b>' . e($specimen) . '</b>', 'w3') ?>
        <?= $cell(dt('Entnahme am / um'), e($drawn), 'w3') ?>
        <?= $cell(dt('Entnommen durch'), e(user_display($u)), 'w6') ?>
    </div>

    <?= $sec(dt('Beweismittelkette (Chain of Custody)')) ?>
    <table class="us-med-tbl us-med-coc">
        <thead><tr><th class="num"><?= e(dt('Nr.')) ?></th><th><?= e(dt('Übergeben von')) ?></th><th><?= e(dt('Übernommen von')) ?></th><th><?= e(dt('Datum / Uhrzeit')) ?></th><th><?= e(dt('Zweck')) ?></th><th><?= e(dt('Unterschrift Empfänger/in')) ?></th></tr></thead>
        <tbody>
            <tr>
                <td class="num">1</td>
                <td><?= e(user_display($u)) ?></td>
                <td><?= e(trim(($d['officer'] ?? '') . (($d['officer_no'] ?? '') !== '' ? ', ' . $d['officer_no'] : ''))) ?></td>
                <td><?= e($drawn) ?></td>
                <td><?= e(dt('Sicherstellung als Beweismittel')) ?></td>
                <td class="sig"><?php if (!empty($d['officer_signature'])): ?><img class="sig-img" src="<?= e($d['officer_signature']) ?>" alt=""><?php endif; ?></td>
            </tr>
            <tr><td class="num">2</td><td></td><td></td><td></td><td></td><td class="sig"></td></tr>
            <tr><td class="num">3</td><td></td><td></td><td></td><td></td><td class="sig"></td></tr>
        </tbody>
    </table>
    <p class="small"><?= e(dt('Die Blutprobe wurde mittels venöser Punktion (alkoholfreie Desinfektion) entnommen, in Anwesenheit der/des anordnenden Beamtin/Beamten versiegelt und übergeben.')) ?></p>

    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
