<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Work / School Excuse (statt AU-Bescheinigung). Die Diagnose steht nicht auf dem Schreiben (HIPAA). */
$rehab = (array) ($d['rehab'] ?? []);
$since = (string) ($d['au_since'] ?? '');
$until = (string) ($d['au_until'] ?? '');
$back = $until !== '' ? date('Y-m-d', strtotime($until . ' +1 day')) : '';
$name = $p ? patient_name($p) : '';
echo a4_open($doc, $draft);
echo doc_letterhead($doc, ['recipient' => (string) ($d['employer'] ?? ''), 'no_patient_address' => true]);
?>
<div class="doc-body">
    <h1 class="doc-title">Work / School Excuse<small><?= ($d['kind'] ?? '') === 'folge' ? 'Continuation of previous excuse' : 'Initial excuse' ?></small></h1>
    <?= doc_patient_box($doc, false) ?>
    <p class="prose">To whom it may concern:</p>
    <p class="prose">This is to certify that <strong><?= e($name) ?></strong> was seen and evaluated at our facility on <strong><?= e(fmt_date($d['au_found'] ?? '')) ?></strong>.
        For medical reasons, the patient is unable to work or attend school from <strong><?= e(fmt_date($since)) ?></strong> through <strong><?= e(fmt_date($until)) ?></strong> (inclusive)<?php if ($back !== ''): ?> and may return on <strong><?= e(fmt_date($back)) ?></strong> unless otherwise advised<?php endif; ?>.</p>
    <table class="tbl two">
        <tr><th>Work-related injury or illness</th><td><?= !empty($d['accident']) ? 'Yes – workers\' compensation case' : 'No' ?></td></tr>
        <?php if (!empty($d['d_doctor'])): ?><tr><th>Occupational medicine</th><td>Referred to occupational health physician</td></tr><?php endif; ?>
        <?php if (!empty($d['other_accident'])): ?><tr><th>Accident</th><td>Non-occupational accident / injury</td></tr><?php endif; ?>
        <?php if (in_array('reha', $rehab, true)): ?><tr><th>Recommendation</th><td>Rehabilitation / physical therapy recommended</td></tr><?php endif; ?>
        <?php if (in_array('wiedereingliederung', $rehab, true)): ?><tr><th>Return to work</th><td>Graduated return to work (modified duty) recommended</td></tr><?php endif; ?>
    </table>
    <p class="small muted-print">The diagnosis is confidential and is not disclosed to employers or schools (HIPAA). Questions regarding this excuse may be directed to the issuing provider. Verify authenticity using the code below.</p>
    <?= doc_signature($doc, 'rect') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
