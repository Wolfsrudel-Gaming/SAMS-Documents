<?php
defined('SAMS_ROOT') || exit;
/** Überweisungsschein – Formularvordruck A5 */
$kinds = ['auftrag' => 'Auftragsleistung', 'konsil' => 'Konsiliaruntersuchung', 'mitbehandlung' => 'Mit-/Weiterbehandlung', 'stationaer' => 'Stationäre Einweisung'];
?>
<section class="paper a5 form form-green form-uw">
    <?= doc_watermark($doc, $draft) ?>
    <div class="form-grid2">
        <div class="form-left"><?php require __DIR__ . '/_formhead.php'; ?></div>
        <div class="form-right">
            <div class="form-title"><?= ($d['kind'] ?? '') === 'stationaer' ? 'Verordnung von Krankenhausbehandlung' : 'Überweisungsschein' ?></div>
            <div class="fcell"><span class="fl">Überweisung an</span><span class="fv printed big"><?= e($d['to'] ?? '') ?></span>
                <?php if (!empty($d['to_facility'])): ?><span class="fv printed"><?= e($d['to_facility']) ?></span><?php endif; ?></div>
            <div class="uw-kinds">
                <?php foreach ($kinds as $k => $l): ?><div><?= box(($d['kind'] ?? '') === $k) ?> <?= e($l) ?></div><?php endforeach; ?>
                <div><?= box(!empty($d['urgent'])) ?> <strong>dringend</strong></div>
            </div>
        </div>
    </div>
    <div class="fcell"><span class="fl">Diagnose / Verdachtsdiagnose (ICD-10)</span><div class="fv printed"><?= nl($d['icd'] ?? '') ?></div></div>
    <div class="fcell"><span class="fl">Befund / Medikation</span><div class="fv printed"><?= nl($d['findings'] ?? '') ?></div></div>
    <div class="fcell grow"><span class="fl">Auftrag</span><div class="fv printed"><?= nl($d['order'] ?? '') ?></div></div>
    <div class="au-bottom">
        <div class="au-misc small">Bitte bringen Sie diesen Schein sowie vorhandene Vorbefunde zum Termin mit.</div>
        <div class="au-sign">
            <div class="sig-area">
                <div class="sig-stamp rect"><?= doctor_stamp_svg($doc) ?></div>
                <?= signature_html($doc['issuer_snapshot'] ?? [], doc_rand($doc, 'siguw', -5, 3)) ?>
            </div>
            <span class="fl">Vertragsarztstempel / Unterschrift des Arztes</span>
        </div>
    </div>
    <div class="form-foot">
        <span class="form-id"><?= e(org('org_short')) ?>-Vordruck <?= ($d['kind'] ?? '') === 'stationaer' ? '2' : '6' ?></span>
        <span class="form-num mono"><?= e($doc['number'] ?? '') ?> · Prüfcode <?= e($doc['verify_code'] ?? '') ?></span>
        <?= qr_html($doc, 'qr qr-small') ?>
    </div>
</section>
