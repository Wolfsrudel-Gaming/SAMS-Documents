<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Referral / Consultation Request (statt Überweisungsschein). */
echo a4_open($doc, $draft);
echo doc_letterhead($doc, ['recipient' => trim((string) ($d['to_facility'] ?? '') . "\n" . 'Attn: ' . (string) ($d['to'] ?? '')), 'no_patient_address' => true]);
?>
<div class="doc-body">
    <h1 class="doc-title">Referral<?= !empty($d['urgent']) ? ' – URGENT' : '' ?><small><?= e(opt_label($type, 'kind', $d['kind'] ?? '')) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <table class="tbl two">
        <tr><th>Referred to (specialty)</th><td><?= e($d['to'] ?? '') ?></td></tr>
        <?php if (!empty($d['to_facility'])): ?><tr><th>Facility</th><td><?= e($d['to_facility']) ?></td></tr><?php endif; ?>
        <tr><th>Priority</th><td><?= !empty($d['urgent']) ? '<strong>Urgent – please schedule within 24–48 hours</strong>' : 'Routine' ?></td></tr>
        <tr><th>Diagnosis / suspected diagnosis</th><td><?= nl($d['icd'] ?? '') ?></td></tr>
        <?php if (!empty($d['findings'])): ?><tr><th>Findings / current medications</th><td><?= nl($d['findings']) ?></td></tr><?php endif; ?>
        <tr><th>Reason for referral / question</th><td><?= nl($d['order'] ?? '') ?></td></tr>
    </table>
    <p class="small muted-print">Please send your consultation report to the referring provider. This referral contains protected health information.</p>
    <?= doc_signature($doc, 'rect') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
