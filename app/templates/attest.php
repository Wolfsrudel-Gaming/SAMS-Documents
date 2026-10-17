<?php
defined('SAMS_ROOT') || exit;
/** Ärztliches Attest – A4-Brief */
$recipient = $d['recipient'] ?? '';
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'info' => ['Zur Vorlage bei' => $d['purpose'] ?? ''],
]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e($d['subject'] ?: 'Ärztliches Attest') ?></h1>
    <?= doc_patient_box($doc, false) ?>
    <p class="lead-in">Untersuchung am <?= e(fmt_date($d['exam_date'] ?? '')) ?><?php if (!empty($d['valid_until'])): ?> · gültig bis einschließlich <strong><?= e(fmt_date($d['valid_until'])) ?></strong><?php endif; ?></p>
    <div class="prose"><?= nl($d['body'] ?? '') ?></div>
    <?php if (!empty($d['icd'])): ?>
        <p class="small"><strong>Diagnose (auf ausdrücklichen Wunsch des Patienten angegeben):</strong> <?= e($d['icd']) ?></p>
    <?php endif; ?>
    <p class="small muted-print">Dieses Attest wurde aufgrund einer persönlichen ärztlichen Untersuchung ausgestellt. Es ist nur mit Unterschrift und Stempel gültig.</p>
    <?= doc_signature($doc, 'rect') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
