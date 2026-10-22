<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Psychologisches Gutachten als US-Bericht zur psychologischen Begutachtung (Psychological Evaluation Report):
 * Angaben zur Begutachtung, Fragestellung, Erkenntnisquellen, Befundabschnitte, Beantwortung zum Ankreuzen, Empfehlung.
 * Felder und gespeicherte Auswahlwerte wie psych_gutachten.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-BH 6810', 'sections' => 'numbers', 'numbered' => true, 'seal' => !empty($d['seal']) ? 'round' : 'rect',
    'title' => dt('Psychologisches Gutachten'), 'subtitle' => dt('Psychologische Begutachtung (Psychological Evaluation Report)'),
    'banner' => dt('Vertraulich – nur für den Auftraggeber bestimmt'),
    'copies' => [dt('Weiß – Auftraggeber'), dt('Gelb – Patientenakte')],
    'sig_caption' => dt('Gutachter/in'),
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
$results = [
    'positiv'  => ['r-geeignet', dt('Die Fragestellung wird positiv beantwortet.')],
    'auflagen' => ['r-bedingt', dt('Die Fragestellung wird mit Einschränkungen positiv beantwortet.')],
    'negativ'  => ['r-nicht', dt('Die Fragestellung wird negativ beantwortet.')],
    'offen'    => ['r-offen', dt('Die Fragestellung kann derzeit nicht abschließend beantwortet werden.')],
];
$result = (string) ($d['result'] ?? '');
$parts = [
    'history' => [dt('Anamnese (Background and History)'), false],
    'mental_status' => [dt('Psychopathologischer Befund (Mental Status Examination)'), true],
    'tests' => [dt('Testpsychologische Untersuchung (Psychological Testing)'), false],
    'diagnosis' => [dt('Diagnose(n) (Diagnostic Impression)'), false],
    'assessment' => [dt('Zusammenfassende Beurteilung (Summary and Conclusions)'), true],
];
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-psych');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Angaben zur Begutachtung (Identifying Information)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Auftraggeber'), nl($d['client'] ?? ''), 'w6', 'tall') ?>
        <?= $cell(dt('Aktenzeichen des Auftraggebers'), '<b>' . e($d['reference'] ?? '') . '</b>', 'w3') ?>
        <?= $cell(dt('Auftrag vom'), e(fmt_date($d['order_date'] ?? '')), 'w3') ?>
        <?= $cell(dt('Untersuchungstermine'), e($d['sessions'] ?? ''), 'w6') ?>
        <?= $cell(dt('Begutachtete Person'), '<b>' . e($p ? patient_name($p, true) : '') . '</b>' . ($p ? ' · ' . e(dt('geb. am')) . ' ' . e(fmt_date($p['dob'] ?? '')) : ''), 'w6') ?>
        <?= $cell(dt('Gutachter/in'), e(user_display($u)), 'w6') ?>
        <?= $cell(dt('Erkenntnisquellen (Sources of Information)'), isset($F['sources']) ? us_field_value($F['sources'], $d['sources'] ?? []) : '', 'w12') ?>
    </div>

    <?= $sec(dt('Fragestellung (Reason for Referral)')) ?>
    <div class="us-med-narr"><?= nl($d['question'] ?? '') ?></div>

    <?php foreach ($parts as $k => [$label, $required]):
        if (!$required && trim((string) ($d[$k] ?? '')) === '') {
            continue;
        } ?>
        <?= $sec($label) ?>
        <div class="us-med-narr<?= $k === 'diagnosis' ? ' strong' : '' ?>"><?= nl($d[$k] ?? '') ?></div>
    <?php endforeach; ?>

    <?= $sec(dt('Beantwortung der Fragestellung (Opinion)')) ?>
    <div class="us-med-result">
        <?php foreach ($results as $k => [$cls, $text]): ?>
            <div class="<?= $result === $k ? 'on ' . e($cls) : '' ?>"><?= box($result === $k) ?><span><b><?= e(opt_label($type, 'result', $k)) ?></b><small><?= e($text) ?></small></span></div>
        <?php endforeach; ?>
    </div>

    <?php if (trim((string) ($d['recommendation'] ?? '')) !== ''): ?>
        <?= $sec(dt('Empfehlung (Recommendations)')) ?>
        <div class="us-med-narr"><?= nl($d['recommendation']) ?></div>
    <?php endif; ?>

    <p class="us-med-statement small"><?= e(dt('Das Gutachten wurde nach bestem Wissen und Gewissen unparteiisch erstattet. Es unterliegt der Schweigepflicht und ist ausschließlich für den Auftraggeber bestimmt; eine Weitergabe an Dritte bedarf der Zustimmung der/des Begutachteten.')) ?></p>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
