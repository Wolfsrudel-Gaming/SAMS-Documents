<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Prescription (Rx-Block). Betäubungsmittel als Schedule II mit eigenem Hinweis. */
$form = $d['form'] ?? 'kassen';
$flags = (array) ($d['flags'] ?? []);
$items = array_values(array_filter((array) ($d['items'] ?? []), fn($it) => trim((string) ($it['med'] ?? '')) !== ''));
$age = age($p['dob'] ?? '');
?>
<section class="paper a6l us-rx<?= $form === 'btm' ? ' us-rx-cii' : '' ?>">
    <?= doc_watermark($doc, $draft) ?>
    <header class="us-rx-head">
        <?= logo_html('us-rx-logo') ?>
        <div>
            <div class="us-rx-org"><?= e(org('org_name')) ?></div>
            <div class="us-rx-addr"><?= e(org('org_street')) ?> · <?= e(org('org_city')) ?> · Tel. <?= e(org('phone')) ?></div>
            <div class="us-rx-dr"><?= e(user_display($u)) ?><?= ($u['doctor_number'] ?? '') !== '' ? ' · NPI ' . e($u['doctor_number']) : '' ?></div>
        </div>
        <?php if ($form === 'btm'): ?><div class="us-rx-badge">C-II<small>Controlled substance</small></div><?php elseif ($form === 'privat'): ?><div class="us-rx-badge light">Self-pay</div><?php endif; ?>
    </header>
    <div class="us-rx-pt">
        <div><span class="fl">Patient</span><span class="fv"><?= e($p ? patient_name($p) : '') ?></span></div>
        <div><span class="fl">DOB</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?><?= $age !== '' ? ' (' . e($age) . ' y)' : '' ?></span></div>
        <div><span class="fl">Date</span><span class="fv"><?= e(fmt_date($doc['issued_at'] ?? now())) ?></span></div>
        <div class="wide"><span class="fl">Address</span><span class="fv"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span></div>
    </div>
    <div class="us-rx-body">
        <div class="us-rx-sym">℞</div>
        <div class="us-rx-items">
            <?php foreach ($items ?: [null] as $i => $it): ?>
                <div class="us-rx-line printed">
                    <?php if ($it): ?>
                        <b><?= e($it['med']) ?></b>
                        <?php if (!empty($it['dose'])): ?><div>Sig: <?= e($it['dose']) ?></div><?php endif; ?>
                        <div class="us-rx-daw"><?= box(empty($it['autidem'])) ?> Substitution permitted &nbsp; <?= box(!empty($it['autidem'])) ?> Dispense as written</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <?php if (!empty($d['note'])): ?><div class="us-rx-note printed"><?= e($d['note']) ?></div><?php endif; ?>
            <div class="us-rx-flags">
                <?php if (in_array('noctu', $flags, true)): ?><span>After-hours / emergency</span><?php endif; ?>
                <?php if (in_array('unfall', $flags, true)): ?><span>Accident-related</span><?php endif; ?>
                <?php if (in_array('arbeitsunfall', $flags, true)): ?><span>Workers' compensation</span><?php endif; ?>
                <span>Refills: 0</span>
            </div>
        </div>
    </div>
    <div class="us-rx-sign">
        <div class="sig-area">
            <?= signature_html($u, doc_rand($doc, 'sigrx', -6, 2)) ?>
        </div>
        <span class="fl">Prescriber signature</span>
    </div>
    <div class="form-foot">
        <span class="form-id"><?= $form === 'btm' ? 'Valid 7 days · no refills · original only' : 'Valid 28 days from date written' ?></span>
        <span class="form-num mono"><?= e(doc_code_display($doc['number'] ?? '')) ?> · Code <?= e(doc_code_display($doc['verify_code'] ?? '')) ?></span>
        <?= qr_html($doc, 'qr qr-small') ?>
    </div>
</section>
