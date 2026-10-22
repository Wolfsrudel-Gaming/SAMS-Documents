<?php
defined('SAMS_ROOT') || exit;
/**
 * Gemeinsamer Kopf der Formular-Vordrucke (AU, Rezept, Überweisung):
 * Kostenträger, Versicherter, Kennungen – wie auf echten Praxisvordrucken.
 */
$fu = $doc['issuer_snapshot'] ?? [];
?>
<div class="fh">
    <div class="fh-cell fh-kasse"><span class="fl"><?= e(dt('Krankenkasse bzw. Kostenträger')) ?></span><span class="fv"><?= e($p['insurance'] ?? '') ?></span></div>
    <div class="fh-cell fh-name"><span class="fl"><?= e(dt('Name, Vorname des Versicherten')) ?></span>
        <span class="fv"><?= e(patient_name($p, true)) ?></span>
        <span class="fv small"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span>
        <span class="fl fh-dob"><?= e(dt('geb. am')) ?></span><span class="fv fh-dobv"><?= e(fmt_date($p['dob'] ?? '')) ?></span>
    </div>
    <div class="fh-row">
        <div class="fh-cell"><span class="fl"><?= e(dt('Kostenträgerkennung')) ?></span><span class="fv"><?= e(substr(preg_replace('/\D/', '', md5((string) ($p['insurance'] ?? ''))), 0, 9) ?: '') ?></span></div>
        <div class="fh-cell"><span class="fl"><?= e(dt('Versicherten-Nr.')) ?></span><span class="fv"><?= e($p['insurance_number'] ?? '') ?></span></div>
        <div class="fh-cell"><span class="fl"><?= e(dt('Status')) ?></span><span class="fv">1000000</span></div>
    </div>
    <div class="fh-row">
        <div class="fh-cell"><span class="fl"><?= e(dt('Betriebsstätten-Nr.')) ?></span><span class="fv"><?= e(str_replace(' ', '', org('bsnr'))) ?></span></div>
        <div class="fh-cell"><span class="fl"><?= e(dt('Arzt-Nr.')) ?></span><span class="fv"><?= e($fu['doctor_number'] ?? '') ?></span></div>
        <div class="fh-cell"><span class="fl"><?= e(dt('Datum')) ?></span><span class="fv"><?= e(fmt_date($doc['issued_at'] ?? now(), 'd.m.y')) ?></span></div>
    </div>
</div>
