<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Personalschreiben (Abmahnung bzw. Kündigung) als US-Personalmemo: „MEMORANDUM“ mit
 * An/Von/Datum/Betreff, Art der Maßnahme zum Ankreuzen, nummerierte Abschnitte und Empfangsbestätigung
 * (Employee Acknowledgment). Gleiche Felder und Auswahlwerte wie app/templates/personalbrief.php.
 */
$emp = $d['_employee'] ?? [];
$empName = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''));
$isWarning = $doc['type'] === 'abmahnung';
$level = (string) ($d['level'] ?? 'abmahnung');
$kind = (string) ($d['kind'] ?? 'ordentlich');
if ($isWarning) {
    $subjects = ['ermahnung' => 'Dokumentierte Ermahnung (Verbal Warning)', 'abmahnung' => 'Schriftliche Abmahnung (Written Warning)', 'letzte' => 'Letzte schriftliche Abmahnung (Final Written Warning)'];
    $subject = dt($subjects[$level] ?? $subjects['abmahnung']);
    $optField = 'level';
    $selected = $level;
} else {
    $subjects = ['ordentlich' => 'Kündigung des Arbeitsverhältnisses (Notice of Termination)', 'fristlos' => 'Fristlose Kündigung aus wichtigem Grund (Termination for Cause)', 'aufhebung' => 'Bestätigung der Beendigung des Arbeitsverhältnisses (Separation Notice)'];
    $subject = dt($subjects[$kind] ?? $subjects['ordentlich']);
    $optField = 'kind';
    $selected = $kind;
}
$fieldOpts = fn(string $name) => (array) (array_values(array_filter($type['fields'], fn($f) => ($f['name'] ?? '') === $name))[0]['options'] ?? []);
$admin = doc_admin_issued($doc);
$fromTitle = $admin ? (($u['admin_title'] ?? '') ?: admin_stamp_setting('admin_title')) : (string) ($u['rank_name'] ?? '');
$cfg = us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ($isWarning ? ' HR 21' : ' HR 30');
}
if (!$cfg['copies']) {
    $cfg['copies'] = ['Original – Personalakte', 'Kopie – Mitarbeiter/in'];
}
$sec = 0;
echo us_paper_open($doc, $draft, 'us-hr us-gen-memo');
?>
<div class="us-body">
    <header class="us-gen-memo-head">
        <?= logo_html('us-gen-memo-logo') ?>
        <div class="us-gen-memo-org"><b><?= e(org('org_name')) ?></b><br><?= e(dt('Personalabteilung')) ?><?= org('org_sub') !== '' ? ' · ' . e(org('org_sub')) : '' ?><br><small><?= e(org('org_street')) ?> · <?= e(org('org_city')) ?></small></div>
        <div class="us-gen-memo-conf"><?= e(mb_strtoupper(dt('Persönlich / vertraulich'))) ?></div>
    </header>
    <h1 class="us-gen-memo-title"><?= e(mb_strtoupper(dt('Memorandum'))) ?></h1>
    <table class="us-gen-memo-to">
        <tr><th><?= e(dt('An:')) ?></th><td><b><?= e($empName) ?: '—' ?></b><?= ($emp['rank_name'] ?? '') !== '' ? ', ' . e($emp['rank_name']) : '' ?><?= ($emp['badge_number'] ?? '') !== '' ? ' · ' . e(dt('Personal-Nr.')) . ' <span class="mono">' . e($emp['badge_number']) . '</span>' : '' ?></td></tr>
        <tr><th><?= e(dt('Von:')) ?></th><td><?= e(user_display($u)) ?><?= $fromTitle !== '' ? ', ' . e($fromTitle) : '' ?> · <?= e($admin ? admin_stamp_setting('admin_org_name') : dt('Personalabteilung')) ?></td></tr>
        <tr><th><?= e(dt('Datum:')) ?></th><td><?= e(fmt_date($doc['issued_at'] ?? now())) ?></td></tr>
        <tr><th><?= e(dt('Betreff:')) ?></th><td><b><?= e($subject) ?></b></td></tr>
    </table>
    <div class="us-gen-memo-kind">
        <span class="fl"><?= e(dt('Art der Maßnahme')) ?></span>
        <span class="us-opts"><?php foreach ($fieldOpts($optField) as $k => $l): ?><span class="us-opt"><?= box((string) $k === $selected) ?> <?= e(dt((string) $l)) ?></span><?php endforeach; ?></span>
    </div>
    <div class="us-gen-memo-body">
    <?php if ($isWarning): ?>
        <p><?= e(dt('Dieses Memorandum dokumentiert ein Fehlverhalten, das nicht hingenommen werden kann.')) ?></p>
        <h3 class="us-gen-h"><?= ++$sec ?>. <?= e(dt('Beschreibung des Vorfalls (Vorfall am {date})', ['date' => fmt_date($d['incident_date'] ?? '')])) ?></h3>
        <div class="us-gen-prose"><?= nl($d['facts'] ?? '') ?></div>
        <h3 class="us-gen-h"><?= ++$sec ?>. <?= e(dt('Verletzte Pflicht / Dienstanweisung')) ?></h3>
        <div class="us-gen-prose"><b><?= nl($d['duty'] ?? '') ?></b></div>
        <h3 class="us-gen-h"><?= ++$sec ?>. <?= e(dt('Erwartetes Verhalten')) ?></h3>
        <div class="us-gen-prose">
            <p><?= e(dt(['ermahnung' => 'Mit diesem Schreiben werden Sie förmlich ermahnt.', 'letzte' => 'Mit diesem Schreiben erhalten Sie eine letzte schriftliche Abmahnung.'][$level] ?? 'Mit diesem Schreiben erhalten Sie eine schriftliche Abmahnung.')) ?></p>
            <?php if (trim((string) ($d['expectation'] ?? '')) !== ''): ?><p><?= nl(dt((string) $d['expectation'])) ?></p><?php endif; ?>
        </div>
        <?php if ($level !== 'ermahnung' && !empty($d['consequence'])): ?>
        <h3 class="us-gen-h"><?= ++$sec ?>. <?= e(dt('Folgen bei Wiederholung')) ?></h3>
        <div class="us-gen-prose"><b><?= nl(dt((string) $d['consequence'])) ?></b></div>
        <?php endif; ?>
        <p class="us-gen-small"><?= e(dt('Eine Kopie dieses Schreibens wird zu Ihrer Personalakte genommen. Sie haben das Recht, eine Gegendarstellung abzugeben, die ebenfalls zur Personalakte genommen wird.')) ?></p>
    <?php else: ?>
        <?php if ($kind === 'aufhebung'): ?>
            <p><?= e(dt('Hiermit bestätigen wir die Beendigung Ihres Arbeitsverhältnisses mit der {org} zum {date}.', ['org' => org('org_name'), 'date' => fmt_date($d['end_date'] ?? '')])) ?></p>
        <?php elseif ($kind === 'fristlos'): ?>
            <p><?= e(dt('Hiermit wird Ihr Arbeitsverhältnis mit der {org} aus wichtigem Grund fristlos und mit sofortiger Wirkung beendet, hilfsweise ordentlich zum nächstmöglichen Termin, nach unserer Berechnung zum {date}.', ['org' => org('org_name'), 'date' => fmt_date($d['end_date'] ?? '')])) ?></p>
        <?php else: ?>
            <p><?= e(dt('Hiermit kündigen wir Ihr Arbeitsverhältnis mit der {org} fristgerecht zum {date}, hilfsweise zum nächstmöglichen Termin.', ['org' => org('org_name'), 'date' => fmt_date($d['end_date'] ?? '')])) ?></p>
        <?php endif; ?>
        <div class="us-grid us-gen-memo-grid">
            <div class="us-cell w4"><span class="fl"><?= e(dt('Letzter Arbeitstag')) ?></span><span class="fv"><b><?= e(fmt_date($d['end_date'] ?? '')) ?: '—' ?></b></span></div>
            <div class="us-cell w4"><span class="fl"><?= e(dt('Rückgabe bis')) ?></span><span class="fv"><?= e(fmt_date($d['return_items'] ?? '')) ?: '—' ?></span></div>
            <div class="us-cell w4"><span class="fl"><?= e(dt('Systemzugang deaktiviert ab')) ?></span><span class="fv"><?= e(fmt_date($d['end_date'] ?? '')) ?: '—' ?></span></div>
        </div>
        <?php if (!empty($d['reason'])): ?>
            <h3 class="us-gen-h"><?= ++$sec ?>. <?= e(dt('Begründung')) ?></h3>
            <div class="us-gen-prose"><?= nl($d['reason']) ?></div>
        <?php endif; ?>
        <?php $returnItems = array_map('strval', (array) ($d['items'] ?? [])); if ($returnItems): ?>
            <h3 class="us-gen-h"><?= ++$sec ?>. <?= e(dt('Rückgabe von Dienstgegenständen (Return of Property)')) ?></h3>
            <p><?= e(dt('Bitte geben Sie die angekreuzten Gegenstände bei der Personalabteilung zurück.')) ?></p>
            <div class="us-opts us-gen-memo-items"><?php foreach ($fieldOpts('items') as $k => $l): ?><span class="us-opt"><?= box(in_array((string) $k, $returnItems, true)) ?> <?= e(dt((string) $l)) ?></span><?php endforeach; ?></div>
        <?php endif; ?>
        <p><?= e(dt('Ihr Zugang zu den Systemen der {org} wird zum Beendigungszeitpunkt deaktiviert. Ein Arbeitszeugnis erhalten Sie gesondert.', ['org' => org('org_short')])) ?></p>
        <?php if ($kind !== 'aufhebung'): ?><p><?= e(dt('Wir danken Ihnen für Ihre bisherige Tätigkeit und wünschen Ihnen für Ihre Zukunft alles Gute.')) ?></p><?php endif; ?>
    <?php endif; ?>
    </div>
    <?= us_signatures($doc, ['seal' => 'round', 'sig_caption' => 'Leitung / Personalabteilung'] + $cfg) ?>
    <?php if (!empty($d['ack'])): ?>
    <div class="us-gen-ack">
        <div class="us-gen-ack-h"><?= e(mb_strtoupper(dt('Empfangsbestätigung (Employee Acknowledgment)'))) ?></div>
        <p><?= e(dt('Ich bestätige, dieses Schreiben erhalten zu haben.')) ?><?= $isWarning ? ' ' . e(dt('Mit der Unterschrift bestätige ich lediglich den Erhalt, nicht die Richtigkeit des Inhalts.')) : '' ?></p>
        <?php if ($isWarning): ?><div class="us-gen-ack-comment"><span class="fl"><?= e(dt('Stellungnahme der Mitarbeiterin / des Mitarbeiters')) ?></span><span class="us-line"></span><span class="us-line"></span></div><?php endif; ?>
        <div class="us-review">
            <div><span class="fl"><?= e(dt('Unterschrift {name}', ['name' => $empName])) ?></span><span class="us-line"></span></div>
            <div class="us-gen-ack-date"><span class="fl"><?= e(dt('Datum')) ?></span><span class="us-line"></span></div>
        </div>
        <div class="us-gen-ack-refused"><?= box(false) ?> <?= e(dt('Unterschrift verweigert – Zeuge/Zeugin (Name, Unterschrift):')) ?> <span class="us-gen-fillline"></span></div>
    </div>
    <?php endif; ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
