<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Operationsbericht als US-Klinikformular (Operative Report): Eingriffsdaten, OP-Team, Diagnose und Eingriff,
 * Operationsverlauf, Blutverlust (EBL), Präparate, Komplikationen, Nachbehandlung.
 * Felder und gespeicherte Auswahlwerte wie op_bericht.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-OR 2410', 'sections' => 'letters', 'numbered' => true, 'seal' => 'rect',
    'title' => dt('Operationsbericht'), 'subtitle' => dt('Klinik für Chirurgie') . ' · ' . org('org_sub'),
    'blocks' => ['hipaa'], 'copies' => [dt('Weiß – Patientenakte'), dt('Gelb – Station'), dt('Rosa – Weiterbehandelnde Stelle')],
    'sig_caption' => dt('Operateur/in'),
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
$duration = '';
if (preg_match('/^\d{1,2}:\d{2}/', (string) ($d['t_cut'] ?? '')) && preg_match('/^\d{1,2}:\d{2}/', (string) ($d['t_suture'] ?? ''))) {
    $min = (int) round((strtotime('2000-01-01 ' . $d['t_suture']) - strtotime('2000-01-01 ' . $d['t_cut'])) / 60);
    $duration = dt('{n} min', ['n' => $min < 0 ? $min + 1440 : $min]);
}
$loss = trim((string) ($d['blood_loss'] ?? ''));
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-op');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Eingriffsdaten')) ?>
    <div class="us-grid">
        <?= $cell(dt('OP-Datum'), e(fmt_date($d['op_date'] ?? '')), 'w2') ?>
        <?= $cell(dt('Schnitt (Incision)'), e($d['t_cut'] ?? ''), 'w2') ?>
        <?= $cell(dt('Naht (Closure)'), e($d['t_suture'] ?? ''), 'w2') ?>
        <?= $cell(dt('Schnitt-Naht-Zeit'), e($duration), 'w2') ?>
        <?= $cell(dt('OP-Saal'), e($d['room'] ?? ''), 'w2') ?>
        <?= $cell(dt('Allergien'), e(($p['allergies'] ?? '') !== '' ? $p['allergies'] : dt('keine bekannt')), 'w2') ?>
        <?= $cell(dt('Dringlichkeit'), isset($F['urgency']) ? us_field_value($F['urgency'], $d['urgency'] ?? '') : '', 'w6') ?>
        <?= $cell(dt('Narkoseart'), e(opt_label($type, 'anesthesia', $d['anesthesia'] ?? '')), 'w3') ?>
        <?= $cell(dt('Lagerung'), e($d['position'] ?? ''), 'w3') ?>
    </div>

    <?= $sec(dt('OP-Team')) ?>
    <div class="us-grid">
        <?= $cell(dt('Operateur/in (Surgeon)'), '<b>' . e(($d['surgeon'] ?? '') !== '' ? $d['surgeon'] : user_display($u)) . '</b>', 'w4') ?>
        <?= $cell(dt('Assistenz'), e($d['assistant'] ?? ''), 'w4') ?>
        <?= $cell(dt('Anästhesie'), e($d['anesthesia_doc'] ?? ''), 'w4') ?>
    </div>

    <?= $sec(dt('Diagnose und Eingriff')) ?>
    <div class="us-grid">
        <?= $cell(dt('Diagnose (ICD-10) – prä- und postoperativ'), '<b>' . nl($d['diagnosis'] ?? '') . '</b>', 'w6', 'tall') ?>
        <?= $cell(dt('Durchgeführter Eingriff (OPS)'), '<b>' . nl($d['procedure'] ?? '') . '</b>', 'w6', 'tall') ?>
        <?= $cell(dt('Indikation'), nl($d['indication'] ?? ''), 'w12') ?>
    </div>

    <?= $sec(dt('Operationsverlauf (Description of Procedure)')) ?>
    <div class="us-med-narr xl"><?= nl($d['report'] ?? '') ?></div>

    <?= $sec(dt('Abschluss')) ?>
    <div class="us-grid">
        <?= $cell(dt('Implantate / Material'), e(($d['implants'] ?? '') !== '' ? $d['implants'] : '—'), 'w6') ?>
        <?= $cell(dt('Geschätzter Blutverlust (EBL)'), e($loss !== '' ? $loss . (is_numeric($loss) ? ' ml' : '') : ''), 'w2') ?>
        <?= $cell(dt('Drainagen'), e(($d['drains'] ?? '') !== '' ? $d['drains'] : '—'), 'w2') ?>
        <?= $cell(dt('Histologie (Specimens)'), '<span class="us-opts"><span class="us-opt">' . box(!empty($d['histology'])) . ' ' . e(dt('eingesandt')) . '</span></span>', 'w2') ?>
        <?= $cell(dt('Komplikationen'), e(trim((string) ($d['complications'] ?? '')) !== '' ? dt((string) $d['complications']) : dt('keine')), 'w12') ?>
        <?= $cell(dt('Procedere / Nachbehandlung'), nl($d['aftercare'] ?? ''), 'w12', 'tall') ?>
    </div>

    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
