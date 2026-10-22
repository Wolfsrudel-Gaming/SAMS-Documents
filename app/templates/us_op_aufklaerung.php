<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Aufklärung und Einwilligung als US-Einwilligungsformular (Informed Consent for Surgery or Procedure):
 * Eingriff, Aufklärungsinhalte, nummerierte Einwilligungserklärung, Entscheidung, Unterschriften mit Zeuge und ärztlicher Bestätigung.
 * Felder und gespeicherte Auswahlwerte wie op_aufklaerung.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-OR 2401', 'sections' => 'letters', 'numbered' => true, 'seal' => 'rect',
    'title' => dt('Einwilligung in einen Eingriff'), 'subtitle' => dt('Aufklärung und Einwilligung (Informed Consent for Surgery or Procedure)'),
    'banner' => dt('Erst unterschreiben, wenn alle Fragen beantwortet sind'),
    'copies' => [dt('Weiß – Patientenakte'), dt('Gelb – Patient/in')],
    'sig_caption' => dt('Aufklärende/r Arzt/Ärztin'),
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
$chosen = array_map('strval', (array) ($d['risks'] ?? []));
$decision = (string) ($d['decision'] ?? '');
$anesthesia = opt_label($type, 'anesthesia', $d['anesthesia'] ?? '');
$fill = fn (string $v): string => '<b class="us-med-fill">' . e($v !== '' ? $v : '______________') . '</b>';
$name = $p ? patient_name($p) : '';
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-consent');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Geplanter Eingriff')) ?>
    <div class="us-grid">
        <?= $cell(dt('Geplanter Eingriff'), '<b>' . e($d['procedure'] ?? '') . '</b>', 'w12') ?>
        <?= $cell(dt('Geplant am'), e(fmt_date($d['planned_date'] ?? '')), 'w3') ?>
        <?= $cell(dt('Betäubung'), e($anesthesia), 'w3') ?>
        <?= $cell(dt('Aufklärende/r Arzt/Ärztin'), e(user_display($u)), 'w6') ?>
    </div>

    <?= $sec(dt('Aufklärung (Disclosure)')) ?>
    <div class="us-grid">
        <?= $cell(dt('Art und Ablauf des Eingriffs'), nl($d['explanation'] ?? ''), 'w12', 'us-med-xl') ?>
        <div class="us-cell w12"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Besprochene Risiken und mögliche Komplikationen')) ?></span>
            <div class="us-med-checks"><?php foreach ($F['risks']['options'] ?? [] as $k => $l): ?><span><?= box(in_array((string) $k, $chosen, true)) ?> <?= e((string) $l) ?></span><?php endforeach; ?></div></div>
        <?= $cell(dt('Spezielle Risiken dieses Eingriffs'), trim((string) ($d['risks_specific'] ?? '')) !== '' ? nl($d['risks_specific']) : '—', 'w12') ?>
        <?= $cell(dt('Behandlungsalternativen'), trim((string) ($d['alternatives'] ?? '')) !== '' ? nl($d['alternatives']) : '—', 'w6') ?>
        <?= $cell(dt('Fragen der Patientin / des Patienten, Gesprächsnotizen'), trim((string) ($d['questions'] ?? '')) !== '' ? nl($d['questions']) : '—', 'w6') ?>
    </div>

    <?= $sec(dt('Einwilligungserklärung (Consent)')) ?>
    <ol class="us-med-statement us-med-ol">
        <li><?= strtr(e(dt('Ich ermächtige {doctor} und die von ihr bzw. ihm hinzugezogenen Fachkräfte, bei mir folgenden Eingriff vorzunehmen: {procedure}.')), ['{doctor}' => $fill(user_display($u)), '{procedure}' => $fill((string) ($d['procedure'] ?? ''))]) ?></li>
        <li><?= e(dt('Art, Zweck und Ablauf des Eingriffs, mögliche Behandlungsalternativen, die damit verbundenen Risiken und mögliche Komplikationen wurden mir verständlich erklärt. Eine Garantie für einen bestimmten Behandlungserfolg wurde mir nicht gegeben.')) ?></li>
        <li><?= strtr(e(dt('Ich willige in die vorgesehene Betäubung ({anesthesia}) ein; über deren Risiken wurde ich gesondert aufgeklärt.')), ['{anesthesia}' => $fill($anesthesia)]) ?></li>
        <li><?= e(dt('Ich willige in zusätzliche oder abweichende Maßnahmen ein, die sich während des Eingriffs als medizinisch notwendig erweisen.')) ?></li>
        <li><?= e(dt('Ich hatte Gelegenheit, alle mich interessierenden Fragen zu stellen; sie wurden vollständig beantwortet. Ich hatte ausreichend Bedenkzeit.')) ?></li>
    </ol>
    <div class="us-grid">
        <div class="us-cell w12 us-med-decision"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Entscheidung')) ?></span>
            <div class="us-med-checks c1">
                <span class="<?= $decision === 'ja' ? 'on' : '' ?>"><?= box($decision === 'ja') ?> <b><?= e(dt('Ich willige in den vorgeschlagenen Eingriff')) ?></b> <?= e(dt('sowie in medizinisch erforderliche Erweiterungen ein.')) ?></span>
                <span class="<?= $decision === 'nein' ? 'on' : '' ?>"><?= box($decision === 'nein') ?> <b><?= e(dt('Ich lehne den Eingriff ab.')) ?></b> <?= e(dt('Über die möglichen Nachteile und Folgen der Ablehnung wurde ich aufgeklärt.')) ?></span>
                <span><?= box(!empty($d['copy_received'])) ?> <?= e(dt('Eine Kopie dieses Aufklärungsbogens habe ich erhalten.')) ?></span>
            </div></div>
    </div>

    <div class="us-sigs">
        <div class="us-sig">
            <div class="us-sig-area"><?php if (!empty($d['patient_signature'])): ?><img class="sig-img" src="<?= e($d['patient_signature']) ?>" alt=""><?php endif; ?></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt('Patient/in bzw. gesetzl. Vertreter/in')) ?></span>
            <span class="us-sig-name"><?= e($name) ?></span>
        </div>
        <div class="us-sig">
            <div class="us-sig-area"><span class="us-med-hand"><?= e($d['witness'] ?? '') ?></span></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt('Zeuge/Zeugin')) ?></span>
        </div>
    </div>
    <p class="us-med-statement small"><b><?= e(dt('Ärztliche Bestätigung:')) ?></b> <?= e(dt('Ich habe der Patientin / dem Patienten bzw. der gesetzlichen Vertretung Art, Zweck, Nutzen, Risiken und Alternativen des Eingriffs erläutert und alle Fragen beantwortet.')) ?></p>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
