<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Erklärung zur Behandlungs-/Transportverweigerung als US-Formular „Refusal of Care / Against Medical Advice (AMA)“:
 * abgelehnte Maßnahmen, aufgeklärte Risiken, Prüfliste Einwilligungsfähigkeit, Haftungsfreistellung, Zeuge.
 * Felder und gespeicherte Auswahlwerte wie revers.php; Einstellungen aus app/us_layouts haben Vorrang.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'ems', 'form' => 'EMS-AMA 110', 'sections' => 'letters', 'numbered' => true, 'seal' => '',
    'title' => dt('Ablehnung von Behandlung oder Transport'), 'subtitle' => dt('Erklärung gegen ärztlichen Rat (Refusal of Care / AMA)'),
    'copies' => [dt('Weiß – Einsatzakte'), dt('Gelb – Patient/in')],
    'sig_caption' => dt('Unterschrift Einsatzkraft {org}', ['org' => org('org_short')]),
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
$name = $p ? patient_name($p) : (string) ($d['person_name'] ?? '');
$refused = array_map('strval', (array) ($d['refused'] ?? []));
$refusedLabels = ['untersuchung' => dt('Untersuchung'), 'behandlung' => dt('Behandlung vor Ort'), 'transport' => dt('Transport in ein Krankenhaus'), 'medikamente' => dt('Gabe von Medikamenten')];
$capable = !empty($d['capable']);
$capacity = [
    dt('Wach und orientiert zu Person, Ort, Zeit und Situation'),
    dt('Keine erkennbare Beeinträchtigung durch Alkohol, Drogen oder Medikamente'),
    dt('Keine Hinweise auf Kopfverletzung, Unterzuckerung oder Sauerstoffmangel mit Bewusstseinsstörung'),
    dt('Versteht die Erklärungen sowie die Risiken und Folgen der Ablehnung'),
    dt('Kann die eigene Entscheidung nachvollziehbar mitteilen'),
];
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-ama');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>

    <?= $sec(dt('Angaben zur Person und zum Einsatz')) ?>
    <div class="us-grid">
        <?= $cell(dt('Name, Vorname'), '<b>' . e($p ? patient_name($p, true) : $name) . '</b>', 'w6') ?>
        <?= $cell(dt('geb. am'), e(fmt_date($p['dob'] ?? '')), 'w2') ?>
        <?= $cell(dt('Datum'), e(fmt_date($d['date'] ?? '')), 'w2') ?>
        <?= $cell(dt('Uhrzeit'), e($d['time'] ?? ''), 'w2') ?>
        <?= $cell(dt('Einsatzort'), e($d['location'] ?? ''), 'w12') ?>
        <?= $cell(dt('Verdachtsdiagnose / Befund'), nl($d['findings'] ?? ''), 'w12', 'tall') ?>
    </div>

    <?= $sec(dt('Abgelehnte Maßnahmen (Refused)')) ?>
    <div class="us-grid">
        <div class="us-cell w12"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Die Person lehnt gegen den Rat der Einsatzkräfte ab')) ?></span>
            <div class="us-med-checks c4"><?php foreach ($refusedLabels as $k => $l): ?><span><?= box(in_array($k, $refused, true)) ?> <?= e($l) ?></span><?php endforeach; ?></div></div>
    </div>

    <?= $sec(dt('Aufgeklärte Risiken (Risks Explained)')) ?>
    <div class="us-grid">
        <div class="us-cell w12 tall"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Mögliche Folgen der Ablehnung, über die aufgeklärt wurde – bis hin zu bleibenden Schäden oder Tod')) ?></span>
            <span class="fv"><?= nl(dt((string) ($d['risks'] ?? ''))) ?></span></div>
    </div>

    <?= $sec(dt('Einwilligungsfähigkeit (Decision-Making Capacity)')) ?>
    <div class="us-grid">
        <div class="us-cell us-med-w8"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Prüfliste der Einsatzkräfte')) ?></span>
            <div class="us-med-checks c1"><?php foreach ($capacity as $l): ?><span><?= box($capable) ?> <?= e($l) ?></span><?php endforeach; ?></div></div>
        <div class="us-cell w4"><span class="fl"><?= e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt('Ergebnis: einwilligungsfähig')) ?></span>
            <span class="us-opts us-med-yn"><span class="us-opt"><?= box($capable) ?> <?= e(dt('ja')) ?></span><span class="us-opt"><?= box(!$capable) ?> <?= e(dt('nein')) ?></span></span>
            <?php if (!$capable): ?><span class="us-med-warn"><?= e(dt('Ohne festgestellte Einwilligungsfähigkeit ist die Ablehnung nicht wirksam – ärztliche Rücksprache halten.')) ?></span><?php endif; ?></div>
    </div>

    <?= $sec(dt('Erklärung der Patientin / des Patienten (Release)')) ?>
    <div class="us-med-statement">
        <p><?= strtr(e(dt('Ich, {name}, wurde von den unterzeichnenden Einsatzkräften des {org} über meinen Gesundheitszustand, die empfohlene Behandlung und die Risiken einer Ablehnung in für mich verständlicher Form aufgeklärt. Ich hatte Gelegenheit, Fragen zu stellen.')), ['{name}' => '<b>' . e($name !== '' ? $name : '______________________') . '</b>', '{org}' => e(org('org_name'))]) ?></p>
        <p><?= e(dt('Trotzdem lehne ich die oben angekreuzten Maßnahmen ausdrücklich gegen ärztlichen bzw. rettungsdienstlichen Rat ab.')) ?></p>
        <p><?= strtr(e(dt('Ich weiß, dass ich meine Entscheidung jederzeit ändern und erneut den Notruf {911} wählen kann. Ich übernehme die volle Verantwortung für die Folgen meiner Entscheidung und stelle die Einsatzkräfte sowie den {org} von jeglicher Haftung frei.')), ['{911}' => '<b>911</b>', '{org}' => e(org('org_short'))]) ?></p>
    </div>

    <div class="us-sigs">
        <div class="us-sig">
            <div class="us-sig-area"><?php if (!empty($d['patient_signature'])): ?><img class="sig-img" src="<?= e($d['patient_signature']) ?>" alt=""><?php elseif (!empty($d['signature_refused'])): ?><span class="us-med-refused"><?= e(dt('Unterschrift verweigert')) ?></span><?php endif; ?></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt('Unterschrift Patient/in')) ?></span>
            <span class="us-sig-name"><?= e($name) ?></span>
        </div>
        <div class="us-sig">
            <div class="us-sig-area"><span class="us-med-hand"><?= e($d['witness'] ?? '') ?></span></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt('Zeuge (Name, Funktion)')) ?></span>
        </div>
    </div>
    <?php if (!empty($d['signature_refused'])): ?><p class="small"><?= e(dt('Die Person hat die Unterschrift verweigert. Aufklärung und Ablehnung wurden in Anwesenheit der Zeugin / des Zeugen dokumentiert.')) ?></p><?php endif; ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa'])), $doc, $cfg) ?>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
