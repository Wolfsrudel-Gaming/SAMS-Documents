<?php
defined('SAMS_ROOT') || exit;
/** Arztbrief / Entlassungsbericht */
$recipient = $d['recipient'] ?? '';
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $recipient,
    'no_patient_address' => $recipient === '',
    'department' => $d['station'] ?? '',
]);
$sections = [
    'diagnoses' => dt('Diagnosen'), 'history' => dt('Anamnese'), 'findings' => dt('Aufnahmebefund'), 'diagnostics' => dt('Diagnostik'),
    'therapy' => dt('Therapie und Verlauf'), 'medication' => dt('Medikation bei Entlassung'), 'recommendation' => dt('Procedere / Empfehlungen'),
];
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= e(isset($d['kind']) ? opt_label($type, 'kind', $d['kind']) : dt('Entlassungsbericht')) ?></h2>
    <p><?= e(dt('Sehr geehrte Frau Kollegin, sehr geehrter Herr Kollege,')) ?></p>
    <p><?php if (doc_us()): ?><?= e(dt('wir berichten über {name}, geb. am {dob}, {address}der/die sich vom {from} bis {to} {unit} befand.', [
        'name' => patient_salutation_name($p) ?: dt('o.g. Patienten'),
        'dob' => fmt_date($p['dob'] ?? ''),
        'address' => ($p['address'] ?? '') !== '' ? dt('wohnhaft {address},', ['address' => str_replace("\n", ', ', $p['address'])]) . ' ' : '',
        'from' => fmt_date($d['admission'] ?? ''),
        'to' => fmt_date($d['discharge'] ?? ''),
        'unit' => ($d['station'] ?? '') !== '' ? dt('in unserer Abteilung {station}', ['station' => $d['station']]) : dt('in unserer Behandlung'),
    ])) ?><?php else: ?>wir berichten über <?= e(patient_salutation_name($p) ?: 'o.g. Patienten') ?>, geb. am <?= e(fmt_date($p['dob'] ?? '')) ?>,
        <?= e(($p['address'] ?? '') !== '' ? 'wohnhaft ' . str_replace("\n", ', ', $p['address']) . ',' : '') ?>
        der/die sich vom <?= e(fmt_date($d['admission'] ?? '')) ?> bis <?= e(fmt_date($d['discharge'] ?? '')) ?> in unserer
        <?= e(($d['station'] ?? '') !== '' ? 'Abteilung ' . $d['station'] : 'Behandlung') ?> befand.<?php endif; ?></p>
    <?php foreach ($sections as $k => $label): if (trim((string) ($d[$k] ?? '')) === '') continue; ?>
        <h3 class="doc-h"><?= e($label) ?></h3>
        <div class="prose <?= $k === 'diagnoses' ? 'strong' : '' ?>"><?= nl($d[$k]) ?></div>
    <?php endforeach; ?>
    <p><?= e(dt('Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.')) ?></p>
    <p><?= e(dt('Mit freundlichen kollegialen Grüßen')) ?></p>
    <?= doc_signature($doc, 'rect', dt('Behandelnde/r Arzt/Ärztin'), false) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
