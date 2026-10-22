<?php
defined('SAMS_ROOT') || exit;
/** Psychologisches / psychiatrisches Gutachten – A4-Brief an den Auftraggeber */
$results = [
    'positiv'  => ['r-geeignet', dt('Die Fragestellung wird positiv beantwortet.')],
    'auflagen' => ['r-bedingt', dt('Die Fragestellung wird mit Einschränkungen positiv beantwortet.')],
    'negativ'  => ['r-nicht', dt('Die Fragestellung wird negativ beantwortet.')],
    'offen'    => ['', dt('Die Fragestellung kann derzeit nicht abschließend beantwortet werden.')],
];
[$resClass, $resText] = $results[$d['result'] ?? ''] ?? ['', ''];
$sources = opt_label($type, 'sources', $d['sources'] ?? []);
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['client'] ?? '',
    'no_patient_address' => true,
    'department' => dt('Psychologischer Dienst'),
    'info' => [dt('Ihr Zeichen') => $d['reference'] ?? '', dt('Auftrag vom') => fmt_date($d['order_date'] ?? '')],
]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e(dt('Psychologisches Gutachten')) ?> <small><?= e(dt('– vertraulich –')) ?></small></h1>
    <?= doc_patient_box($doc, false) ?>
    <p><?= e(dt('In o. g. Angelegenheit erstatte ich auf Grundlage {basis}{sessions} das nachstehende Gutachten.', [
        'basis' => $sources !== '' ? dt('folgender Erkenntnisquellen: {sources}', ['sources' => $sources]) : dt('der persönlichen Untersuchung'),
        'sessions' => !empty($d['sessions']) ? ' (' . dt('Untersuchungstermine: {sessions}', ['sessions' => $d['sessions']]) . ')' : '',
    ])) ?></p>
    <h3 class="doc-h"><?= e(dt('1. Fragestellung')) ?></h3>
    <div class="prose"><?= nl($d['question'] ?? '') ?></div>
    <?php $n = 2; foreach (['history' => dt('Anamnese'), 'mental_status' => dt('Psychopathologischer Befund'), 'tests' => dt('Testpsychologische Untersuchung'), 'diagnosis' => dt('Diagnose(n)'), 'assessment' => dt('Zusammenfassende Beurteilung')] as $k => $label):
        if (trim((string) ($d[$k] ?? '')) === '') continue; ?>
        <h3 class="doc-h"><?= $n++ ?>. <?= e($label) ?></h3>
        <div class="prose<?= $k === 'diagnosis' ? ' strong' : '' ?>"><?= nl($d[$k]) ?></div>
    <?php endforeach; ?>
    <h3 class="doc-h"><?= $n++ ?>. <?= e(dt('Beantwortung der Fragestellung')) ?></h3>
    <div class="result-box <?= e($resClass) ?>"><strong><?= e(strtoupper(opt_label($type, 'result', $d['result'] ?? ''))) ?></strong><span><?= e($resText) ?></span></div>
    <?php if (!empty($d['recommendation'])): ?><h3 class="doc-h"><?= $n++ ?>. <?= e(dt('Empfehlung')) ?></h3><div class="prose"><?= nl($d['recommendation']) ?></div><?php endif; ?>
    <p class="small"><?= e(dt('Das Gutachten wurde nach bestem Wissen und Gewissen unparteiisch erstattet. Es unterliegt der Schweigepflicht und ist ausschließlich für den Auftraggeber bestimmt; eine Weitergabe an Dritte bedarf der Zustimmung der/des Begutachteten.')) ?></p>
    <?= doc_signature($doc, !empty($d['seal']) ? 'round' : 'rect', dt('Gutachter/in')) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
