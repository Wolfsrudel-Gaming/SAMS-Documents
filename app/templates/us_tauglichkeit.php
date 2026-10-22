<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Ärztliches Zeugnis über die Tauglichkeit als US-Bescheinigung der gesundheitlichen Eignung
 * (Medical Fitness Certificate) mit Befundraster, Ergebnis zum Ankreuzen und Kurzbescheinigung zum Ausschneiden (Wallet Card).
 * Felder und gespeicherte Auswahlwerte wie tauglichkeit.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-OCC 6620', 'sections' => 'letters', 'numbered' => true, 'seal' => 'round',
    'title' => dt('Ärztliches Tauglichkeitszeugnis'), 'subtitle' => dt('Bescheinigung der gesundheitlichen Eignung (Medical Fitness Certificate)'),
    'copies' => [dt('Weiß – Untersuchte Person'), dt('Gelb – Vorlagestelle'), dt('Rosa – Patientenakte')],
    'sig_caption' => dt('Untersuchende/r Arzt/Ärztin'),
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
// Reihenfolge wie auf US-Bescheinigungen: geeignet, mit Auflagen, vorübergehend nicht, nicht geeignet
$results = [
    'geeignet'  => [dt('GEEIGNET'), dt('Gegen die beantragte Tätigkeit / Erlaubnis bestehen aus ärztlicher Sicht keine Bedenken.')],
    'bedingt'   => [dt('BEDINGT GEEIGNET'), dt('Die Eignung ist unter Beachtung der nachfolgend genannten Auflagen gegeben.')],
    'befristet' => [dt('VORÜBERGEHEND NICHT GEEIGNET'), dt('Eine erneute Untersuchung nach Ablauf der genannten Frist wird empfohlen.')],
    'nicht'     => [dt('NICHT GEEIGNET'), dt('Aus ärztlicher Sicht bestehen erhebliche Bedenken gegen die beantragte Tätigkeit / Erlaubnis.')],
];
$result = (string) ($d['result'] ?? '');
$checks = ['vision' => dt('Sehtest'), 'hearing' => dt('Hörtest'), 'reaction' => dt('Reaktionstest'), 'physical' => dt('Körperliche Untersuchung'), 'psych' => dt('Psychologisches Gespräch'), 'drugs' => dt('Drogenscreening')];
$purpose = opt_label($type, 'purpose', $d['purpose'] ?? '');
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-fit');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Angaben zur Untersuchung')) ?>
    <div class="us-grid">
        <?= $cell(dt('Art der Untersuchung'), '<b>' . e($purpose) . '</b>', 'w6') ?>
        <?= $cell(dt('Zur Vorlage bei'), e($d['requester'] ?? ''), 'w6') ?>
        <?= $cell(dt('Untersucht am'), e(fmt_date($d['exam_date'] ?? '')), 'w3') ?>
        <?= $cell(dt('Gültig bis'), '<b>' . e(fmt_date($d['valid_until'] ?? '')) . '</b>', 'w3') ?>
        <?= $cell(dt('Identitätsnachweis'), e(dt('Amtlicher Lichtbildausweis')), 'w6') ?>
    </div>

    <?= $sec(dt('Untersuchungsbefunde (Examination)')) ?>
    <div class="us-grid">
        <?php foreach ($checks as $k => $l): ?>
            <?= $cell($l, isset($F[$k]) ? us_field_value($F[$k], $d[$k] ?? '') : '', 'w4') ?>
        <?php endforeach; ?>
        <?= $cell(dt('Blutdruck'), e($d['rr'] ?? ''), 'w4') ?>
        <?= $cell(dt('Puls'), e($d['hf'] ?? ''), 'w4') ?>
        <?= $cell(dt('Größe / Gewicht'), e($d['bmi'] ?? ''), 'w4') ?>
        <?= $cell(dt('Weitere Befunde'), nl($d['findings'] ?? ''), 'w12', 'tall') ?>
    </div>

    <?= $sec(dt('Ergebnis (Determination)')) ?>
    <div class="us-med-result">
        <?php foreach ($results as $k => [$title, $text]): ?>
            <div class="<?= $result === $k ? 'on r-' . e($k) : '' ?>"><?= box($result === $k) ?><span><b><?= e($title) ?></b><small><?= e($text) ?></small></span></div>
        <?php endforeach; ?>
    </div>
    <div class="us-grid">
        <?= $cell(dt('Auflagen / Einschränkungen'), nl($d['conditions'] ?? ''), 'w12', 'tall') ?>
    </div>

    <p class="us-med-statement"><?= e(dt('Die/der Obengenannte wurde am {date} von mir persönlich untersucht.', ['date' => fmt_date($d['exam_date'] ?? '')])) ?> <?= e(dt('Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.')) ?><?php if (!empty($d['valid_until'])): ?> <?= e(dt('Dieses Zeugnis ist gültig bis einschließlich')) ?> <b><?= e(fmt_date($d['valid_until'])) ?></b>.<?php endif; ?></p>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <div class="us-med-sigrow">
        <div class="us-med-sigcol"><?= us_signatures($doc, $cfg) ?></div>
        <div class="us-med-card">
            <div class="us-med-card-h"><?= logo_html('us-med-card-logo') ?><span><b><?= e(org('org_name')) ?></b><br><?= e(dt('Kurzbescheinigung (Wallet Card)')) ?></span></div>
            <div><span class="fl"><?= e(dt('Name, Vorname')) ?></span><b><?= e($p ? patient_name($p, true) : '') ?></b> · <?= e(fmt_date($p['dob'] ?? '')) ?></div>
            <div><span class="fl"><?= e(dt('Art der Untersuchung')) ?></span><?= e($purpose) ?></div>
            <div class="us-med-card-r <?= e($result) ?>"><span class="fl"><?= e(dt('Ergebnis')) ?></span><b><?= e($results[$result][0] ?? '') ?></b></div>
            <div class="us-med-card-f"><span><span class="fl"><?= e(dt('Gültig bis')) ?></span><?= e(fmt_date($d['valid_until'] ?? '')) ?></span><span><span class="fl"><?= e(dt('Dokument-Nr.')) ?></span><span class="mono"><?= e(doc_code_display((string) ($doc['number'] ?? ''))) ?></span></span></div>
        </div>
    </div>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
