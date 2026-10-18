<?php
defined('SAMS_ROOT') || exit;
/** Bescheinigung über die Einsatztauglichkeit – ohne Befunde (Schweigepflicht), nur Ergebnis für die Dienststelle */
$results = [
    'geeignet'  => ['TAUGLICH', 'Gegen die genannte Tätigkeit bestehen aus ärztlicher Sicht keine gesundheitlichen Bedenken.'],
    'bedingt'   => ['TAUGLICH MIT EINSCHRÄNKUNGEN', 'Keine gesundheitlichen Bedenken unter Beachtung der nachfolgend genannten Einschränkungen.'],
    'befristet' => ['VORÜBERGEHEND NICHT TAUGLICH', 'Befristete gesundheitliche Bedenken. Eine Nachuntersuchung wird empfohlen.'],
    'nicht'     => ['DAUERHAFT NICHT TAUGLICH', 'Dauerhafte gesundheitliche Bedenken gegen die genannte Tätigkeit.'],
];
[$resTitle, $resText] = $results[$d['result'] ?? ''] ?? ['', ''];
$art = FITNESS_KINDS[$d['art'] ?? ''][0] ?? ($d['art'] ?? '');
$umfang = array_values(array_filter((array) ($d['umfang'] ?? [])));
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['dienststelle'] ?? '',
    'no_patient_address' => ($d['dienststelle'] ?? '') !== '',
    'info' => ['Gültig bis' => fmt_date($d['valid_until'] ?? '')],
]);
?>
<div class="doc-body">
    <h1 class="doc-title">Bescheinigung über die Einsatztauglichkeit <small><?= e($art) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <p class="lead-in">Die/der Obengenannte wurde am <?= e(fmt_date($d['exam_date'] ?? '')) ?> arbeitsmedizinisch untersucht. Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.</p>
    <?php if ($umfang): ?><p><strong>Untersuchungsumfang:</strong> <?= e(implode(', ', $umfang)) ?></p><?php endif; ?>
    <h3 class="doc-h">Ergebnis</h3>
    <div class="result-box r-<?= e($d['result'] ?? '') ?>"><strong><?= e($resTitle) ?></strong><span><?= e($resText) ?></span></div>
    <?php if (!empty($d['conditions'])): ?><p><strong>Einschränkungen / Auflagen:</strong><br><?= nl($d['conditions']) ?></p><?php endif; ?>
    <?php if (!empty($d['valid_until']) && in_array($d['result'] ?? '', ['geeignet', 'bedingt'], true)): ?><p>Diese Bescheinigung gilt bis einschließlich <strong><?= e(fmt_date($d['valid_until'])) ?></strong>. Vor Ablauf ist eine Nachuntersuchung erforderlich.</p><?php endif; ?>
    <p class="small muted">Die Befunde unterliegen der ärztlichen Schweigepflicht und sind nicht Bestandteil dieser Bescheinigung. Der Dienststelle wird ausschließlich das Ergebnis mitgeteilt.</p>
    <?= doc_signature($doc, 'round') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
