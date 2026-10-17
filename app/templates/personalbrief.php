<?php
defined('SAMS_ROOT') || exit;
/** Personalschreiben: Abmahnung bzw. Kündigung – A4-Brief „persönlich / vertraulich“ */
$emp = $d['_employee'] ?? [];
$empName = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''));
$recipient = "Persönlich / vertraulich\n" . $empName . "\n– im Hause –";
$isWarning = $doc['type'] === 'abmahnung';
if ($isWarning) {
    $levels = ['ermahnung' => 'Ermahnung', 'abmahnung' => 'Abmahnung', 'letzte' => 'Letztmalige Abmahnung'];
    $subject = $levels[$d['level'] ?? 'abmahnung'] ?? 'Abmahnung';
} else {
    $kinds = ['ordentlich' => 'Ordentliche Kündigung Ihres Arbeitsverhältnisses', 'fristlos' => 'Außerordentliche fristlose Kündigung Ihres Arbeitsverhältnisses', 'aufhebung' => 'Bestätigung der Beendigung Ihres Arbeitsverhältnisses'];
    $subject = $kinds[$d['kind'] ?? 'ordentlich'] ?? 'Kündigung';
}
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'no_patient_address' => true,
    'department' => 'Personalabteilung',
    'info' => ['Personal-Nr.' => $emp['badge_number'] ?? '', 'Funktion' => $emp['rank_name'] ?? ''],
]);
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= e($subject) ?></h2>
    <p>Sehr geehrte/r <?= e($empName) ?>,</p>
    <?php if ($isWarning): ?>
        <p>wir müssen Sie auf folgendes Fehlverhalten hinweisen, das wir nicht hinnehmen können:</p>
        <h3 class="doc-h">Sachverhalt (Vorfall am <?= e(fmt_date($d['incident_date'] ?? '')) ?>)</h3>
        <div class="prose"><?= nl($d['facts'] ?? '') ?></div>
        <p>Mit diesem Verhalten haben Sie gegen Ihre Pflichten aus dem Arbeitsverhältnis verstoßen, insbesondere gegen:</p>
        <div class="prose strong"><?= nl($d['duty'] ?? '') ?></div>
        <p>Wir <?= ($d['level'] ?? '') === 'ermahnung' ? 'ermahnen' : 'mahnen' ?> Sie hiermit<?= ($d['level'] ?? '') === 'letzte' ? ' letztmalig' : '' ?> <?= ($d['level'] ?? '') === 'ermahnung' ? '' : 'ab' ?>. <?= nl($d['expectation'] ?? '') ?></p>
        <?php if (($d['level'] ?? '') !== 'ermahnung' && !empty($d['consequence'])): ?><p><strong><?= nl($d['consequence']) ?></strong></p><?php endif; ?>
        <p>Eine Kopie dieses Schreibens wird zu Ihrer Personalakte genommen. Sie haben das Recht, eine Gegendarstellung abzugeben, die ebenfalls zur Personalakte genommen wird.</p>
    <?php else: ?>
        <?php if (($d['kind'] ?? '') === 'aufhebung'): ?>
            <p>hiermit bestätigen wir die Beendigung Ihres Arbeitsverhältnisses mit der <?= e(org('org_name')) ?> zum <strong><?= e(fmt_date($d['end_date'] ?? '')) ?></strong>.</p>
        <?php elseif (($d['kind'] ?? '') === 'fristlos'): ?>
            <p>hiermit kündigen wir das mit Ihnen bestehende Arbeitsverhältnis außerordentlich und fristlos mit sofortiger Wirkung, hilfsweise ordentlich zum nächstmöglichen Termin, das ist nach unserer Berechnung der <strong><?= e(fmt_date($d['end_date'] ?? '')) ?></strong>.</p>
        <?php else: ?>
            <p>hiermit kündigen wir das mit Ihnen bestehende Arbeitsverhältnis fristgerecht zum <strong><?= e(fmt_date($d['end_date'] ?? '')) ?></strong>, hilfsweise zum nächstmöglichen Termin.</p>
        <?php endif; ?>
        <?php if (!empty($d['reason'])): ?><h3 class="doc-h">Begründung</h3><div class="prose"><?= nl($d['reason']) ?></div><?php endif; ?>
        <?php $items = opt_label($type, 'items', $d['items'] ?? []); if ($items !== ''): ?>
            <p>Wir bitten Sie, folgende dienstliche Gegenstände<?= !empty($d['return_items']) ? ' bis zum <strong>' . e(fmt_date($d['return_items'])) . '</strong>' : '' ?> bei der Personalabteilung zurückzugeben: <?= e($items) ?>.</p>
        <?php endif; ?>
        <p>Ihr Zugang zu den Systemen der <?= e(org('org_short')) ?> wird zum Beendigungszeitpunkt deaktiviert. Ein Arbeitszeugnis erhalten Sie gesondert.</p>
        <?php if (($d['kind'] ?? '') !== 'aufhebung'): ?><p>Wir danken Ihnen für Ihre bisherige Tätigkeit und wünschen Ihnen für Ihre Zukunft alles Gute.</p><?php endif; ?>
    <?php endif; ?>
    <p>Mit freundlichen Grüßen</p>
    <?= doc_signature($doc, 'round', 'Leitung / Personalabteilung', false) ?>
    <?php if (!empty($d['ack'])): ?>
        <div class="ack-box">
            <strong>Empfangsbestätigung</strong>
            <p>Ich habe das vorstehende Schreiben am __________________ erhalten.<?= $isWarning ? ' Mit der Unterschrift bestätige ich lediglich den Erhalt, nicht die Richtigkeit des Inhalts.' : '' ?></p>
            <div class="ack-sign"><span></span><small>Unterschrift <?= e($empName) ?></small></div>
        </div>
    <?php endif; ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
