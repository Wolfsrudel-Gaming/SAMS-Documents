<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Arztbrief / Entlassungsbericht als US-Klinikbericht (Discharge Summary) mit Aufenthaltsdaten und Berichtsabschnitten.
 * Felder und gespeicherte Auswahlwerte wie arztbrief.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$kinds = [
    'Entlassungsbericht' => dt('Entlassungsbericht (Discharge Summary)'),
    'Vorläufiger Entlassungsbericht' => dt('Vorläufiger Entlassungsbericht (Interim Summary)'),
    'Ambulanzbrief' => dt('Ambulanzbrief (Clinic Visit Note)'),
    'Notaufnahmebericht' => dt('Notaufnahmebericht (ED Report)'),
    'OP-Bericht' => dt('OP-Bericht (Operative Note)'),
];
$kind = (string) ($d['kind'] ?? '');
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-HIM 1020', 'sections' => 'letters', 'numbered' => true, 'seal' => 'rect',
    'title' => $kinds[$kind] ?? ($kind !== '' ? opt_label($type, 'kind', $kind) : $kinds['Entlassungsbericht']),
    'subtitle' => trim((string) ($d['station'] ?? '')),
    'blocks' => ['hipaa'], 'copies' => [dt('Weiß – Patientenakte'), dt('Gelb – Weiterbehandelnde Stelle'), dt('Rosa – Patient/in')],
    'sig_caption' => dt('Entlassende Ärztin / entlassender Arzt (Attending)'),
], us_layout_registry()[$type['key'] ?? ''] ?? []));
$n = 0;
$cell = function (string $label, string $html, string $w = 'w4', string $cls = '') use (&$n, $cfg): string {
    return '<div class="us-cell ' . $w . ($cls !== '' ? ' ' . $cls : '') . '"><span class="fl">' . e(($cfg['numbered'] ? (++$n) . '. ' : '') . $label) . '</span><span class="fv">' . $html . '</span></div>';
};
$letter = 0;
$sec = function (string $label) use (&$letter, $cfg): string {
    $prefix = $cfg['sections'] === 'letters' ? chr(65 + $letter++) . ' – ' : ($cfg['sections'] === 'numbers' ? (++$letter) . '. ' : '');
    return '<div class="us-sec">' . e($prefix . mb_strtoupper($label)) . '</div>';
};
// Verweildauer (Length of Stay) in Tagen
$los = '';
$from = strtotime((string) ($d['admission'] ?? ''));
$to = strtotime((string) ($d['discharge'] ?? ''));
if ($from && $to && $to >= $from) {
    $days = (int) round(($to - $from) / 86400);
    $los = $days === 1 ? dt('1 Tag') : dt('{n} Tage', ['n' => $days]);
}
$sections = [
    'diagnoses' => [dt('Diagnosen (Discharge Diagnoses)'), true],
    'history' => [dt('Anamnese (History of Present Illness)'), false],
    'findings' => [dt('Aufnahmebefund (Physical Exam on Admission)'), false],
    'diagnostics' => [dt('Diagnostik – Labor, Bildgebung (Results)'), false],
    'therapy' => [dt('Therapie und Verlauf (Hospital Course)'), true],
    'medication' => [dt('Medikation bei Entlassung (Discharge Medications)'), false],
    'recommendation' => [dt('Procedere / Empfehlungen (Follow-up)'), false],
];
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-dc');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Aufenthalt (Encounter)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Aufnahme (Admission)'), e(fmt_date($d['admission'] ?? '')), 'w2') ?>
        <?= $cell(dt('Entlassung (Discharge)'), e(fmt_date($d['discharge'] ?? '')), 'w2') ?>
        <?= $cell(dt('Verweildauer (Length of Stay)'), e($los), 'w2') ?>
        <?= $cell(dt('Station / Abteilung'), e($d['station'] ?? ''), 'w6') ?>
        <?= $cell(dt('Behandelnde/r Arzt/Ärztin'), e(user_display($u)), 'w6') ?>
        <?= $cell(dt('Empfänger / Kopie an (cc)'), e(str_replace("\n", ', ', trim((string) ($d['recipient'] ?? '')))), 'w6') ?>
    </div>

    <?php foreach ($sections as $k => [$label, $required]):
        if (!$required && trim((string) ($d[$k] ?? '')) === '') {
            continue;
        } ?>
        <?= $sec($label) ?>
        <div class="us-med-narr<?= $k === 'diagnoses' ? ' strong' : '' ?><?= $k === 'therapy' ? ' tall' : '' ?>"><?= nl($d[$k] ?? '') ?></div>
    <?php endforeach; ?>

    <p class="us-med-statement"><?= e(dt('Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.')) ?></p>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
