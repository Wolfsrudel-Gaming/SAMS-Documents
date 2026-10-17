<?php
defined('SAMS_ROOT') || exit;
/** Arbeitsunfähigkeitsbescheinigung – Formularvordruck A5 in drei Ausfertigungen */
$copies = [
    'kasse'       => 'Ausfertigung zur Vorlage bei der Krankenkasse',
    'arbeitgeber' => 'Ausfertigung zur Vorlage beim Arbeitgeber',
    'versicherter' => 'Ausfertigung für Versicherte',
];
$show = isset($copies[$copy]) ? [$copy => $copies[$copy]] : $copies;
$rehab = (array) ($d['rehab'] ?? []);
foreach ($show as $ck => $clabel):
    $hideDiag = $ck === 'arbeitgeber';
?>
<section class="paper a5 form form-red form-au">
    <?= doc_watermark($doc, $draft) ?>
    <div class="form-grid2">
        <div class="form-left">
            <?php require __DIR__ . '/_formhead.php'; ?>
        </div>
        <div class="form-right">
            <div class="form-title">Arbeitsunfähigkeits&shy;bescheinigung</div>
            <div class="au-kind">
                <div><?= box(($d['kind'] ?? '') === 'erst') ?> Erstbescheinigung</div>
                <div><?= box(($d['kind'] ?? '') === 'folge') ?> Folgebescheinigung</div>
            </div>
            <div class="au-flags">
                <div><?= box(!empty($d['accident'])) ?> Arbeitsunfall, Arbeitsunfallfolgen, Berufskrankheit</div>
                <div><?= box(!empty($d['d_doctor'])) ?> dem Durchgangsarzt zugewiesen</div>
            </div>
        </div>
    </div>

    <div class="au-dates">
        <div class="fcell"><span class="fl">arbeitsunfähig seit</span><?= date_boxes($d['au_since'] ?? '') ?></div>
        <div class="fcell"><span class="fl">voraussichtlich arbeitsunfähig bis einschließlich oder letzter Tag der Arbeitsunfähigkeit</span><?= date_boxes($d['au_until'] ?? '') ?></div>
        <div class="fcell"><span class="fl">festgestellt am</span><?= date_boxes($d['au_found'] ?? '') ?></div>
    </div>

    <div class="fcell au-diag<?= $hideDiag ? ' hatched' : '' ?>">
        <span class="fl">AU-begründende Diagnose(n) (ICD-10)</span>
        <?php if (!$hideDiag): ?>
            <div class="fv printed"><?= nl($d['icd'] ?? '') ?></div>
        <?php endif; ?>
    </div>

    <div class="au-bottom">
        <div class="au-misc">
            <div><?= box(!empty($d['other_accident'])) ?> sonstiger Unfall, Unfallfolgen</div>
            <div><?= box(in_array('reha', $rehab, true)) ?> Maßnahmen zur Rehabilitation angezeigt</div>
            <div><?= box(in_array('wiedereingliederung', $rehab, true)) ?> Stufenweise Wiedereingliederung</div>
            <div><?= box(in_array('krankengeld', $rehab, true)) ?> Ab 7. AU-Woche oder sonstiger Krankengeldfall</div>
            <?php if (!empty($d['employer']) && $ck !== 'kasse'): ?><div class="small">Arbeitgeber: <span class="printed"><?= e($d['employer']) ?></span></div><?php endif; ?>
        </div>
        <div class="au-sign">
            <div class="sig-area">
                <div class="sig-stamp rect"><?= doctor_stamp_svg($doc) ?></div>
                <?= signature_html($doc['issuer_snapshot'] ?? [], doc_rand($doc, 'sig' . $ck, -5, 3)) ?>
            </div>
            <span class="fl">Vertragsarztstempel / Unterschrift des Arztes</span>
        </div>
    </div>

    <div class="form-foot">
        <span class="form-copy"><?= e($clabel) ?></span>
        <span class="form-id"><?= e(org('org_short')) ?>-Vordruck 1<?= ['kasse' => 'a', 'arbeitgeber' => 'b', 'versicherter' => 'c'][$ck] ?> (<?= date('n.Y', strtotime($doc['issued_at'] ?? now())) ?>)</span>
        <span class="form-num mono"><?= e($doc['number'] ?? '') ?> · Prüfcode <?= e($doc['verify_code'] ?? '') ?></span>
        <?= qr_html($doc, 'qr qr-small') ?>
    </div>
    <?php if ($ck === 'arbeitgeber'): ?>
        <p class="form-note">Die Diagnose wird dem Arbeitgeber aus Datenschutzgründen nicht mitgeteilt.</p>
    <?php elseif ($ck === 'versicherter'): ?>
        <p class="form-note">Bitte bewahren Sie diese Ausfertigung für Ihre Unterlagen auf. Die Krankenkasse wird elektronisch informiert.</p>
    <?php endif; ?>
</section>
<?php endforeach; ?>
