<?php
defined('SAMS_ROOT') || exit;
/** Psychologisches / psychiatrisches Gutachten – A4-Brief an den Auftraggeber */
$results = [
    'positiv'  => ['r-geeignet', 'Die Fragestellung wird positiv beantwortet.'],
    'auflagen' => ['r-bedingt', 'Die Fragestellung wird mit Einschränkungen positiv beantwortet.'],
    'negativ'  => ['r-nicht', 'Die Fragestellung wird negativ beantwortet.'],
    'offen'    => ['', 'Die Fragestellung kann derzeit nicht abschließend beantwortet werden.'],
];
[$resClass, $resText] = $results[$d['result'] ?? ''] ?? ['', ''];
$sources = opt_label($type, 'sources', $d['sources'] ?? []);
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['client'] ?? '',
    'no_patient_address' => true,
    'department' => 'Psychologischer Dienst',
    'info' => ['Ihr Zeichen' => $d['reference'] ?? '', 'Auftrag vom' => fmt_date($d['order_date'] ?? '')],
]);
?>
<div class="doc-body">
    <h1 class="doc-title">Psychologisches Gutachten <small>– vertraulich –</small></h1>
    <?= doc_patient_box($doc, false) ?>
    <p>In o. g. Angelegenheit erstatte ich auf Grundlage <?= $sources !== '' ? 'folgender Erkenntnisquellen: ' . e($sources) : 'der persönlichen Untersuchung' ?><?= !empty($d['sessions']) ? ' (Untersuchungstermine: ' . e($d['sessions']) . ')' : '' ?> das nachstehende Gutachten.</p>
    <h3 class="doc-h">1. Fragestellung</h3>
    <div class="prose"><?= nl($d['question'] ?? '') ?></div>
    <?php $n = 2; foreach (['history' => 'Anamnese', 'mental_status' => 'Psychopathologischer Befund', 'tests' => 'Testpsychologische Untersuchung', 'diagnosis' => 'Diagnose(n)', 'assessment' => 'Zusammenfassende Beurteilung'] as $k => $label):
        if (trim((string) ($d[$k] ?? '')) === '') continue; ?>
        <h3 class="doc-h"><?= $n++ ?>. <?= e($label) ?></h3>
        <div class="prose<?= $k === 'diagnosis' ? ' strong' : '' ?>"><?= nl($d[$k]) ?></div>
    <?php endforeach; ?>
    <h3 class="doc-h"><?= $n++ ?>. Beantwortung der Fragestellung</h3>
    <div class="result-box <?= e($resClass) ?>"><strong><?= e(strtoupper(opt_label($type, 'result', $d['result'] ?? ''))) ?></strong><span><?= e($resText) ?></span></div>
    <?php if (!empty($d['recommendation'])): ?><h3 class="doc-h"><?= $n++ ?>. Empfehlung</h3><div class="prose"><?= nl($d['recommendation']) ?></div><?php endif; ?>
    <p class="small">Das Gutachten wurde nach bestem Wissen und Gewissen unparteiisch erstattet. Es unterliegt der Schweigepflicht und ist ausschließlich für den Auftraggeber bestimmt; eine Weitergabe an Dritte bedarf der Zustimmung der/des Begutachteten.</p>
    <?= doc_signature($doc, !empty($d['seal']) ? 'round' : 'rect', 'Gutachter/in') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
