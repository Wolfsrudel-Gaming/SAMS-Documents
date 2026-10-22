<?php
defined('SAMS_ROOT') || exit;
/** Rezept – Kassen- (rosa), Privat- (blau) oder BtM-Rezept (gelb), A6 quer */
$form = $d['form'] ?? 'kassen';
$flags = (array) ($d['flags'] ?? []);
$items = (array) ($d['items'] ?? []);
$colors = ['kassen' => 'form-red', 'privat' => 'form-blue', 'btm' => 'form-yellow'];
$titles = ['kassen' => '', 'privat' => 'Privatrezept', 'btm' => 'Betäubungsmittelrezept'];
?>
<section class="paper a6l form <?= $colors[$form] ?? 'form-red' ?> form-rx">
    <?= doc_watermark($doc, $draft) ?>
    <div class="rx-grid">
        <div class="rx-flags">
            <div class="rx-flag"><?= box(in_array('gebfrei', $flags, true)) ?><span>Gebühr frei</span></div>
            <div class="rx-flag"><?= box(!in_array('gebfrei', $flags, true) && $form === 'kassen') ?><span>Geb.-pfl.</span></div>
            <div class="rx-flag"><?= box(in_array('noctu', $flags, true)) ?><span>noctu</span></div>
            <div class="rx-flag"><?= box(false) ?><span>Sonstige</span></div>
            <div class="rx-flag"><?= box(in_array('unfall', $flags, true)) ?><span>Unfall</span></div>
            <div class="rx-flag"><?= box(in_array('arbeitsunfall', $flags, true)) ?><span>Arbeits-<br>unfall</span></div>
        </div>
        <div class="rx-head">
            <?php require __DIR__ . '/_formhead.php'; ?>
        </div>
        <div class="rx-side">
            <?php if (!empty($titles[$form])): ?><div class="rx-title"><?= e($titles[$form]) ?></div><?php endif; ?>
            <div class="rx-box"><span class="fl">Zuzahlung</span><span class="fv"></span></div>
            <div class="rx-box"><span class="fl">Gesamt-Brutto</span><span class="fv"></span></div>
            <div class="rx-box"><span class="fl">Apotheken-Nummer / IK</span><span class="fv"></span></div>
        </div>
    </div>
    <div class="rx-body">
        <div class="rx-autidem">
            <span class="fl">aut idem</span>
            <?php for ($i = 0; $i < 3; $i++): ?><?= box(!empty($items[$i]['autidem'])) ?><?php endfor; ?>
        </div>
        <div class="rx-items">
            <span class="rx-rp">Rp. <small>(Bitte Leerräume durchstreichen)</small></span>
            <?php for ($i = 0; $i < 3; $i++): $it = $items[$i] ?? null; ?>
                <div class="rx-line printed">
                    <?php if ($it): ?>
                        <?= e($it['med'] ?? '') ?><?php if (!empty($it['dose'])): ?> <span class="rx-dose">» <?= e($it['dose']) ?> «</span><?php endif; ?>
                    <?php else: ?>
                        <span class="rx-strike"></span>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
            <?php if (!empty($d['note'])): ?><div class="rx-note printed"><?= e($d['note']) ?></div><?php endif; ?>
        </div>
        <div class="rx-sign">
            <div class="sig-area">
                <div class="sig-stamp rect"><?= doctor_stamp_svg($doc) ?></div>
                <?= signature_html($doc['issuer_snapshot'] ?? [], doc_rand($doc, 'sigrx', -6, 2)) ?>
            </div>
            <span class="fl">Unterschrift des Arztes</span>
        </div>
    </div>
    <div class="form-foot">
        <span class="form-id"><?= e(org('org_short')) ?>-Vordruck <?= $form === 'btm' ? 'BtM' : ($form === 'privat' ? 'P' : '16') ?></span>
        <span class="form-num mono"><?= e($doc['number'] ?? '') ?> · Prüfcode <?= e($doc['verify_code'] ?? '') ?></span>
        <?= qr_html($doc, 'qr qr-small') ?>
    </div>
</section>
