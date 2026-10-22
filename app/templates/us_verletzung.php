<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Ärztlicher Befundbericht über Verletzungen als US-Formular zur Verletzungsdokumentation (Forensic Medical Report)
 * mit Verletzungstabelle und Körperschema. Felder und gespeicherte Auswahlwerte wie verletzung.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-FOR 5410', 'sections' => 'letters', 'numbered' => true, 'seal' => 'round',
    'title' => dt('Ärztlicher Befundbericht'), 'subtitle' => dt('Dokumentation von Verletzungen (Forensic Medical Report)'),
    'blocks' => ['confidential', 'oath'], 'copies' => [dt('Weiß – Ermittlungsbehörde'), dt('Gelb – Patientenakte'), dt('Rosa – Untersuchte Person')],
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
$injuries = array_values(array_filter((array) ($d['injuries'] ?? []), fn($r) => is_array($r) && trim(implode('', array_map('strval', $r))) !== ''));
$sex = ['m' => dt('männlich'), 'w' => dt('weiblich'), 'd' => dt('divers')][$p['gender'] ?? ''] ?? '';
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-forensic');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Ersuchende Stelle (Requesting Agency)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Angefordert durch'), e($d['requester'] ?? ''), 'us-med-w8') ?>
        <?= $cell(dt('Aktenzeichen Behörde'), '<b>' . e($d['case_no'] ?? '') . '</b>', 'w4') ?>
    </div>

    <?= $sec(dt('Untersuchung')) ?>
    <div class="us-grid">
        <?= $cell(dt('Name, Vorname'), '<b>' . e($p ? patient_name($p, true) : '') . '</b>', 'w6') ?>
        <?= $cell(dt('geb. am'), e(fmt_date($p['dob'] ?? '')), 'w2') ?>
        <?= $cell(dt('Geschlecht'), e($sex), 'w2') ?>
        <?= $cell(dt('Bürger-ID'), '<span class="mono">' . e($p['citizen_id'] ?? '') . '</span>', 'w2') ?>
        <?= $cell(dt('Untersuchung am'), e(fmt_date($d['exam_date'] ?? '')), 'w3') ?>
        <?= $cell(dt('Uhrzeit'), e($d['exam_time'] ?? ''), 'w3') ?>
        <?= $cell(dt('Ort der Untersuchung'), e(org('org_sub')), 'w6') ?>
        <?= $cell(dt('Angaben der untersuchten Person zum Geschehen (History)'), nl($d['incident'] ?? ''), 'w12', 'tall') ?>
    </div>

    <?= $sec(dt('Festgestellte Verletzungen (Injuries)')) ?>
    <div class="us-med-inj">
        <table class="us-med-tbl">
            <thead><tr><th class="num"><?= e(dt('Nr.')) ?></th><th><?= e(dt('Körperregion')) ?></th><th><?= e(dt('Befund (Art, Größe, Farbe, Form)')) ?></th><th><?= e(dt('Geschätztes Alter')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($injuries as $i => $inj): ?>
                <tr><td class="num"><?= $i + 1 ?></td><td><?= e($inj['region'] ?? '') ?></td><td><?= e($inj['finding'] ?? '') ?></td><td><?= e($inj['age'] ?? '') ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$injuries): ?><tr><td class="num">–</td><td colspan="3"><?= e(dt('Keine äußerlich sichtbaren Verletzungen feststellbar.')) ?></td></tr><?php endif; ?>
            <?php for ($i = count($injuries); $i < 4; $i++): ?><tr class="blank"><td class="num"></td><td></td><td></td><td></td></tr><?php endfor; ?>
            </tbody>
        </table>
        <div class="us-med-bm"><span class="fl"><?= e(dt('Körperschema – Lage der Befunde')) ?></span><?= bodymap_svg((array) ($d['bodymap'] ?? [])) ?></div>
    </div>

    <?= $sec(dt('Ärztliche Beurteilung (Medical Opinion)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Ärztliche Beurteilung'), nl($d['assessment'] ?? ''), 'w12', 'us-med-xl') ?>
        <?= $cell(dt('Lebensgefahr'), isset($F['danger']) ? us_field_value($F['danger'], $d['danger'] ?? '') : '', 'w6') ?>
        <?= $cell(dt('Fotodokumentation angefertigt'), us_field_value(['type' => 'checkbox'], $d['photos'] ?? ''), 'w3') ?>
        <?= $cell(dt('Schweigepflichtentbindung liegt vor'), us_field_value(['type' => 'checkbox'], $d['release'] ?? ''), 'w3') ?>
    </div>

    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
