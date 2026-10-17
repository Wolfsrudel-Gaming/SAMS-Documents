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
    'diagnoses' => 'Diagnosen', 'history' => 'Anamnese', 'findings' => 'Aufnahmebefund', 'diagnostics' => 'Diagnostik',
    'therapy' => 'Therapie und Verlauf', 'medication' => 'Medikation bei Entlassung', 'recommendation' => 'Procedere / Empfehlungen',
];
?>
<div class="doc-body">
    <h2 class="doc-subject"><?= e($d['kind'] ?? 'Entlassungsbericht') ?></h2>
    <p>Sehr geehrte Frau Kollegin, sehr geehrter Herr Kollege,</p>
    <p>wir berichten über <?= e(patient_salutation_name($p) ?: 'o.g. Patienten') ?>, geb. am <?= e(fmt_date($p['dob'] ?? '')) ?>,
        <?= e(($p['address'] ?? '') !== '' ? 'wohnhaft ' . str_replace("\n", ', ', $p['address']) . ',' : '') ?>
        der/die sich vom <?= e(fmt_date($d['admission'] ?? '')) ?> bis <?= e(fmt_date($d['discharge'] ?? '')) ?> in unserer
        <?= e(($d['station'] ?? '') !== '' ? 'Abteilung ' . $d['station'] : 'Behandlung') ?> befand.</p>
    <?php foreach ($sections as $k => $label): if (trim((string) ($d[$k] ?? '')) === '') continue; ?>
        <h3 class="doc-h"><?= e($label) ?></h3>
        <div class="prose <?= $k === 'diagnoses' ? 'strong' : '' ?>"><?= nl($d[$k]) ?></div>
    <?php endforeach; ?>
    <p>Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.</p>
    <p>Mit freundlichen kollegialen Grüßen</p>
    <?= doc_signature($doc, 'rect', 'Behandelnde/r Arzt/Ärztin', false) ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
