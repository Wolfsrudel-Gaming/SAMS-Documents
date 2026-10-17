<?php
defined('SAMS_ROOT') || exit;
/** Allgemeines Schreiben – A4-Brief */
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['recipient'] ?? '',
    'no_patient_address' => ($d['recipient'] ?? '') !== '',
    'info' => ['Ihr Zeichen' => $d['your_ref'] ?? '', 'Unser Zeichen' => $d['our_ref'] ?? ''],
]);
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= e($d['subject'] ?? '') ?></h2>
    <?php if ($p): ?><p class="small">Betrifft: <?= e(patient_name($p)) ?>, geb. <?= e(fmt_date($p['dob'] ?? '')) ?></p><?php endif; ?>
    <p><?= e($d['salutation'] ?? '') ?></p>
    <div class="prose"><?= nl($d['body'] ?? '') ?></div>
    <p><?= e($d['closing'] ?? 'Mit freundlichen Grüßen') ?></p>
    <?= doc_signature($doc, !empty($d['seal']) ? 'round' : '', 'i. A.', false) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
