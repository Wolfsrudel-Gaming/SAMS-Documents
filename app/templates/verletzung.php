<?php
defined('SAMS_ROOT') || exit;
/** Ärztlicher Befundbericht über Verletzungen (z.B. für LSPD / Gericht) */
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['requester'] ?? '',
    'no_patient_address' => true,
    'info' => [dt('Aktenzeichen') => $d['case_no'] ?? ''],
]);
$dangerLabels = ['nein' => dt('Lebensgefahr bestand nicht.'), 'abstrakt' => dt('Es bestand eine abstrakte Lebensgefahr.'), 'konkret' => dt('Es bestand konkrete Lebensgefahr.')];
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e(dt('Ärztlicher Befundbericht')) ?> <small><?= e(dt('über die Untersuchung auf Verletzungen')) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <p class="lead-in"><?php if (doc_en()): ?><?= e(dt('Untersuchung am {date}{time} in den Räumen des {org}.', [
        'date' => fmt_date($d['exam_date'] ?? ''),
        'time' => !empty($d['exam_time']) ? ' ' . dt('um {time} Uhr', ['time' => $d['exam_time']]) : '',
        'org' => org('org_sub'),
    ])) ?><?php else: ?>Untersuchung am <?= e(fmt_date($d['exam_date'] ?? '')) ?><?= !empty($d['exam_time']) ? ' um ' . e($d['exam_time']) . ' Uhr' : '' ?>
        in den Räumen des <?= e(org('org_sub')) ?>.<?php endif; ?></p>
    <?php if (!empty($d['incident'])): ?>
        <h3 class="doc-h"><?= e(dt('Angaben des/der Untersuchten')) ?></h3>
        <div class="prose"><?= nl($d['incident']) ?></div>
    <?php endif; ?>
    <h3 class="doc-h"><?= e(dt('Befund')) ?></h3>
    <div class="vb-grid">
        <table class="tbl">
            <thead><tr><th><?= e(dt('Nr.')) ?></th><th><?= e(dt('Körperregion')) ?></th><th><?= e(dt('Befund')) ?></th><th><?= e(dt('Alter (geschätzt)')) ?></th></tr></thead>
            <tbody>
            <?php foreach ((array) ($d['injuries'] ?? []) as $i => $inj): ?>
                <tr><td><?= $i + 1 ?></td><td><?= e($inj['region'] ?? '') ?></td><td><?= e($inj['finding'] ?? '') ?></td><td><?= e($inj['age'] ?? '') ?></td></tr>
            <?php endforeach; ?>
            <?php if (empty($d['injuries'])): ?><tr><td colspan="4"><?= e(dt('Keine äußerlich sichtbaren Verletzungen feststellbar.')) ?></td></tr><?php endif; ?>
            </tbody>
        </table>
        <?php if (!empty($d['bodymap'])): ?><div class="bm-box"><?= bodymap_svg((array) $d['bodymap']) ?></div><?php endif; ?>
    </div>
    <h3 class="doc-h"><?= e(dt('Ärztliche Beurteilung')) ?></h3>
    <div class="prose"><?= nl($d['assessment'] ?? '') ?></div>
    <?php if (!empty($d['danger'])): ?><p><strong><?= e($dangerLabels[$d['danger']] ?? '') ?></strong></p><?php endif; ?>
    <p class="small">
        <?= box(!empty($d['photos'])) ?> <?= e(dt('Fotodokumentation wurde angefertigt und liegt der Akte bei.')) ?> &nbsp;
        <?= box(!empty($d['release'])) ?> <?= e(dt('Eine Entbindung von der ärztlichen Schweigepflicht liegt vor.')) . "\n" ?>
    </p>
    <?= doc_signature($doc, 'round', dt('Untersuchende/r Arzt/Ärztin')) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
