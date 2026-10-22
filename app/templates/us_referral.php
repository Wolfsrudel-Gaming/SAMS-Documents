<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Überweisung (Referral / Consultation Request, statt Überweisungsschein). */
echo a4_open($doc, $draft);
echo doc_letterhead($doc, ['recipient' => trim((string) ($d['to_facility'] ?? '') . "\n" . dt('z. Hd. {to}', ['to' => (string) ($d['to'] ?? '')])), 'no_patient_address' => true]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e(dt('Überweisung')) ?><?= !empty($d['urgent']) ? e(dt(' – DRINGEND')) : '' ?><small><?= e(opt_label($type, 'kind', $d['kind'] ?? '')) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <table class="tbl two">
        <tr><th><?= e(dt('Überwiesen an (Fachrichtung)')) ?></th><td><?= e($d['to'] ?? '') ?></td></tr>
        <?php if (!empty($d['to_facility'])): ?><tr><th><?= e(dt('Zieleinrichtung')) ?></th><td><?= e($d['to_facility']) ?></td></tr><?php endif; ?>
        <tr><th><?= e(dt('Priorität')) ?></th><td><?= !empty($d['urgent']) ? '<strong>' . e(dt('Dringend – bitte Termin innerhalb von 24–48 Stunden')) . '</strong>' : e(dt('Regulär')) ?></td></tr>
        <tr><th><?= e(dt('Diagnose bzw. Verdachtsdiagnose')) ?></th><td><?= nl($d['icd'] ?? '') ?></td></tr>
        <?php if (!empty($d['findings'])): ?><tr><th><?= e(dt('Befunde / aktuelle Medikation')) ?></th><td><?= nl($d['findings']) ?></td></tr><?php endif; ?>
        <tr><th><?= e(dt('Überweisungsgrund / Fragestellung')) ?></th><td><?= nl($d['order'] ?? '') ?></td></tr>
    </table>
    <p class="small muted-print"><?= e(dt('Bitte senden Sie Ihren Befundbericht an die überweisende Stelle. Diese Überweisung enthält geschützte Gesundheitsdaten.')) ?></p>
    <?= doc_signature($doc, 'rect') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
