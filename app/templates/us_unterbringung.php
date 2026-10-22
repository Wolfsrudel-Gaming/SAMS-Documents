<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Antrag auf Unterbringung nach dem Vorbild des kalifornischen Formulars „Application for up to 72-Hour Detention
 * for Evaluation and Treatment“ (Section 5150 W&I Code) mit Belehrung bei der Ingewahrsamnahme (Detainment Advisement).
 * Felder und gespeicherte Auswahlwerte wie unterbringung.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$kind = (string) ($d['kind'] ?? '');
[$title, $subtitle] = match ($kind) {
    '5250' => [dt('Bescheinigung zur Verlängerung der Unterbringung'), dt('Intensivbehandlung bis zu 14 Tage · Section 5250 W&I Code')],
    'gericht' => [dt('Antrag auf gerichtliche Unterbringung'), dt('Unterbringung nach dem Welfare & Institutions Code des Staates San Andreas')],
    default => [dt('Antrag auf Unterbringung bis zu 72 Stunden'), dt('Beurteilung, Behandlung und Krisenintervention · Section 5150 W&I Code')],
};
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-BH 5150', 'sections' => 'letters', 'numbered' => true, 'seal' => 'rect',
    'title' => $title, 'subtitle' => $subtitle,
    'banner' => dt('Vertrauliche Patienteninformation – W&I Code § 5328'),
    'copies' => [dt('Weiß – Aufnehmende Einrichtung'), dt('Gelb – Betroffene Person'), dt('Rosa – Antragsteller/in')],
    'sig_caption' => dt('Antragstellende/r Arzt/Ärztin'),
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
$grounds = array_map('strval', (array) ($d['grounds'] ?? []));
$groundLabels = [
    'selbst' => dt('Die Person stellt aufgrund einer psychischen Störung eine Gefahr für sich selbst dar.'),
    'fremd' => dt('Die Person stellt aufgrund einer psychischen Störung eine Gefahr für andere dar.'),
    'hilflos' => dt('Die Person ist aufgrund einer psychischen Störung schwer beeinträchtigt und kann für Nahrung, Kleidung oder Unterkunft nicht selbst sorgen (gravely disabled).'),
];
$informed = !empty($d['informed']);
$sex = ['m' => dt('männlich'), 'w' => dt('weiblich'), 'd' => dt('divers')][$p['gender'] ?? ''] ?? '';
$fill = fn (string $v): string => '<b class="us-med-fill">' . e($v !== '' ? $v : '______________') . '</b>';
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-hold');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>
    <div class="us-grid">
        <?= $cell(dt('Art der Unterbringung'), isset($F['kind']) ? us_field_value($F['kind'], $kind) : '', 'w12') ?>
    </div>

    <?= $sec(dt('Betroffene Person')) ?>
    <div class="us-grid">
        <?= $cell(dt('Name, Vorname'), '<b>' . e($p ? patient_name($p, true) : '') . '</b>', 'us-med-w5') ?>
        <?= $cell(dt('geb. am'), e(fmt_date($p['dob'] ?? '')), 'w2') ?>
        <?= $cell(dt('Geschlecht'), e($sex), 'w2') ?>
        <?= $cell(dt('Bürger-ID'), '<span class="mono">' . e($p['citizen_id'] ?? '') . '</span>', 'w3') ?>
        <?= $cell(dt('Anschrift'), e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))), 'w12') ?>
    </div>

    <?= $sec(dt('Belehrung bei der Ingewahrsamnahme (Detainment Advisement)')) ?>
    <div class="us-med-box">
        <p><?= strtr(e(dt('Mein Name ist {name}. Ich bin {role} bei {org}. Sie sind nicht festgenommen. Ich bringe Sie zu einer Untersuchung durch Fachkräfte für psychische Gesundheit in {facility}. Über Ihre Rechte werden Sie dort vom Fachpersonal belehrt.')), [
            '{name}' => $fill(user_display($u)), '{role}' => $fill((string) ($u['rank_name'] ?? '')), '{org}' => $fill(org('org_name')), '{facility}' => $fill((string) ($d['facility'] ?? '')),
        ]) ?></p>
        <p><b><?= e(dt('Bei Ingewahrsamnahme in der Wohnung zusätzlich:')) ?></b> <?= e(dt('Sie dürfen einige persönliche Gegenstände mitnehmen, die ich vorher ansehen muss. Sagen Sie mir bitte, wenn Sie Hilfe brauchen, um Geräte oder das Wasser abzustellen. Sie dürfen telefonieren und eine Nachricht hinterlassen, damit Angehörige oder Freunde wissen, wohin Sie gebracht werden.')) ?></p>
        <div class="us-opts"><span class="us-opt"><?= box($informed) ?> <?= e(dt('Belehrung vollständig erteilt')) ?></span><span class="us-opt"><?= box(!$informed) ?> <?= e(dt('Belehrung nicht oder nur unvollständig erteilt (Grund bei den Tatsachen angeben)')) ?></span></div>
    </div>

    <?= $sec(dt('Aufgreifen und Zuführung')) ?>
    <div class="us-grid">
        <?= $cell(dt('Beginn (Datum)'), e(fmt_date($d['begin_date'] ?? '')), 'w3') ?>
        <?= $cell(dt('Uhrzeit'), e($d['begin_time'] ?? ''), 'w2') ?>
        <?= $cell(dt('Einrichtung'), e($d['facility'] ?? ''), 'us-med-w7') ?>
        <?= $cell(dt('Ort des Aufgreifens'), e($d['location'] ?? ''), 'w6') ?>
        <?= $cell(dt('Zugeführt durch'), e(opt_label($type, 'brought_by', $d['brought_by'] ?? '')), 'w2') ?>
        <?= $cell(dt('Beamter/Beamtin, Dienstnr.'), e($d['officer'] ?? ''), 'w4') ?>
    </div>

    <?= $sec(dt('Hinreichender Grund (Probable Cause)')) ?>
    <div class="us-grid">
        <div class="us-cell w12"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Aufgrund der vorliegenden Tatsachen besteht hinreichender Grund zu der Annahme:')) ?></span>
            <div class="us-med-checks c1"><?php foreach ($groundLabels as $k => $l): ?><span><?= box(in_array($k, $grounds, true)) ?> <?= e($l) ?></span><?php endforeach; ?></div></div>
    </div>

    <?= $sec(dt('Tatsachen und Beobachtungen')) ?>
    <div class="us-grid">
        <?= $cell(dt('Tatsachen und Beobachtungen, die die Gefahr begründen (mit Datum, Uhrzeit, Äußerungen)'), nl($d['facts'] ?? ''), 'w12', 'us-med-xl') ?>
        <?= $cell(dt('Psychischer Befund bei Aufnahme'), nl($d['mental_status'] ?? ''), 'us-med-w8', 'tall') ?>
        <?= $cell(dt('Verdachtsdiagnose'), e($d['diagnosis'] ?? ''), 'w4', 'tall') ?>
    </div>

    <?= $sec(dt('Freiwillige Behandlung und Benachrichtigung')) ?>
    <div class="us-grid">
        <?= $cell(dt('Freiwillige Behandlung'), '<span class="us-opts"><span class="us-opt">' . box(!empty($d['voluntary_refused'])) . ' ' . e(dt('Eine freiwillige Behandlung wurde angeboten und abgelehnt bzw. ist nicht ausreichend.')) . '</span></span>', 'w6') ?>
        <?= $cell(dt('Benachrichtigte Angehörige / Betreuer'), e(($d['relatives'] ?? '') !== '' ? $d['relatives'] : '—'), 'w6') ?>
    </div>

    <p class="us-med-statement"><?= e(dt('Ich bestätige, dass aufgrund der oben genannten Tatsachen hinreichender Grund zu der Annahme besteht, dass die Voraussetzungen der Unterbringung vorliegen. Falsche Angaben in diesem Antrag sind strafbar.')) ?></p>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa', 'oath'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg, [['name' => '_facility_signature', 'label' => dt('Aufnehmende Einrichtung (Datum, Uhrzeit, Unterschrift)')]]) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
