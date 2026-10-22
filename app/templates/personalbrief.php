<?php
defined('SAMS_ROOT') || exit;
/** Personalschreiben: Abmahnung bzw. Kündigung – A4-Brief „persönlich / vertraulich“ */
$emp = $d['_employee'] ?? [];
$empName = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''));
$recipient = dt('Persönlich / vertraulich') . "\n" . $empName . "\n" . dt('– im Hause –');
$isWarning = $doc['type'] === 'abmahnung';
if ($isWarning) {
    $levels = ['ermahnung' => 'Ermahnung', 'abmahnung' => 'Abmahnung', 'letzte' => 'Letztmalige Abmahnung'];
    $subject = dt($levels[$d['level'] ?? 'abmahnung'] ?? 'Abmahnung');
} else {
    $kinds = ['ordentlich' => 'Ordentliche Kündigung Ihres Arbeitsverhältnisses', 'fristlos' => 'Außerordentliche fristlose Kündigung Ihres Arbeitsverhältnisses', 'aufhebung' => 'Bestätigung der Beendigung Ihres Arbeitsverhältnisses'];
    $subject = dt($kinds[$d['kind'] ?? 'ordentlich'] ?? 'Kündigung');
}
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'no_patient_address' => true,
    'department' => dt('Personalabteilung'),
    'info' => [dt('Personal-Nr.') => $emp['badge_number'] ?? '', dt('Funktion') => $emp['rank_name'] ?? ''],
]);
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= e($subject) ?></h2>
    <p><?= e(dt('Sehr geehrte/r {name},', ['name' => $empName])) ?></p>
    <?php if ($isWarning): ?>
        <p><?= e(dt('wir müssen Sie auf folgendes Fehlverhalten hinweisen, das wir nicht hinnehmen können:')) ?></p>
        <h3 class="doc-h"><?= e(dt('Sachverhalt (Vorfall am {date})', ['date' => fmt_date($d['incident_date'] ?? '')])) ?></h3>
        <div class="prose"><?= nl($d['facts'] ?? '') ?></div>
        <p><?= e(dt('Mit diesem Verhalten haben Sie gegen Ihre Pflichten aus dem Arbeitsverhältnis verstoßen, insbesondere gegen:')) ?></p>
        <div class="prose strong"><?= nl($d['duty'] ?? '') ?></div>
        <p><?= doc_en() ? e(['ermahnung' => 'You are hereby formally counseled regarding this conduct.', 'letzte' => 'You are hereby issued a final written warning.'][$d['level'] ?? ''] ?? 'You are hereby issued a written warning.')
            : 'Wir ' . (($d['level'] ?? '') === 'ermahnung' ? 'ermahnen' : 'mahnen') . ' Sie hiermit' . (($d['level'] ?? '') === 'letzte' ? ' letztmalig' : '') . ' ' . (($d['level'] ?? '') === 'ermahnung' ? '' : 'ab') . '.' ?> <?= nl($d['expectation'] ?? '') ?></p>
        <?php if (($d['level'] ?? '') !== 'ermahnung' && !empty($d['consequence'])): ?><p><strong><?= nl($d['consequence']) ?></strong></p><?php endif; ?>
        <p><?= e(dt('Eine Kopie dieses Schreibens wird zu Ihrer Personalakte genommen. Sie haben das Recht, eine Gegendarstellung abzugeben, die ebenfalls zur Personalakte genommen wird.')) ?></p>
    <?php else: ?>
        <?php if (($d['kind'] ?? '') === 'aufhebung'): ?>
            <p><?= e(dt('hiermit bestätigen wir die Beendigung Ihres Arbeitsverhältnisses mit der {org} zum', ['org' => org('org_name')])) ?> <strong><?= e(fmt_date($d['end_date'] ?? '')) ?></strong>.</p>
        <?php elseif (($d['kind'] ?? '') === 'fristlos'): ?>
            <p><?= e(dt('hiermit kündigen wir das mit Ihnen bestehende Arbeitsverhältnis außerordentlich und fristlos mit sofortiger Wirkung, hilfsweise ordentlich zum nächstmöglichen Termin, das ist nach unserer Berechnung der')) ?> <strong><?= e(fmt_date($d['end_date'] ?? '')) ?></strong>.</p>
        <?php else: ?>
            <p><?= e(dt('hiermit kündigen wir das mit Ihnen bestehende Arbeitsverhältnis fristgerecht zum')) ?> <strong><?= e(fmt_date($d['end_date'] ?? '')) ?></strong><?= e(dt(', hilfsweise zum nächstmöglichen Termin.')) ?></p>
        <?php endif; ?>
        <?php if (!empty($d['reason'])): ?><h3 class="doc-h"><?= e(dt('Begründung')) ?></h3><div class="prose"><?= nl($d['reason']) ?></div><?php endif; ?>
        <?php $items = implode(', ', array_map(fn($x) => dt(opt_label($type, 'items', $x)), (array) ($d['items'] ?? []))); if ($items !== ''): ?>
            <p><?= e(dt('Wir bitten Sie, folgende dienstliche Gegenstände')) ?><?= !empty($d['return_items']) ? ' ' . e(dt('bis zum')) . ' <strong>' . e(fmt_date($d['return_items'])) . '</strong>' : '' ?> <?= e(dt('bei der Personalabteilung zurückzugeben:')) ?> <?= e($items) ?>.</p>
        <?php endif; ?>
        <p><?= e(dt('Ihr Zugang zu den Systemen der {org} wird zum Beendigungszeitpunkt deaktiviert. Ein Arbeitszeugnis erhalten Sie gesondert.', ['org' => org('org_short')])) ?></p>
        <?php if (($d['kind'] ?? '') !== 'aufhebung'): ?><p><?= e(dt('Wir danken Ihnen für Ihre bisherige Tätigkeit und wünschen Ihnen für Ihre Zukunft alles Gute.')) ?></p><?php endif; ?>
    <?php endif; ?>
    <p><?= e(dt('Mit freundlichen Grüßen')) ?></p>
    <?= doc_signature($doc, 'round', dt('Leitung / Personalabteilung'), false) ?>
    <?php if (!empty($d['ack'])): ?>
        <div class="ack-box">
            <strong><?= e(dt('Empfangsbestätigung')) ?></strong>
            <p><?= e(dt('Ich habe das vorstehende Schreiben am __________________ erhalten.')) ?><?= $isWarning ? ' ' . e(dt('Mit der Unterschrift bestätige ich lediglich den Erhalt, nicht die Richtigkeit des Inhalts.')) : '' ?></p>
            <div class="ack-sign"><span></span><small><?= e(dt('Unterschrift {name}', ['name' => $empName])) ?></small></div>
        </div>
    <?php endif; ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
