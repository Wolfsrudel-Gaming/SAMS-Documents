<?php
defined('SAMS_ROOT') || exit;
/** Operationsbericht */
$protoTitle = 'Operationsbericht';
$protoSub = 'Klinik für Chirurgie · ' . org('org_sub');
$duration = '';
if (!empty($d['t_cut']) && !empty($d['t_suture'])) {
    $min = (strtotime('2000-01-01 ' . $d['t_suture']) - strtotime('2000-01-01 ' . $d['t_cut'])) / 60;
    $duration = ($min < 0 ? $min + 1440 : $min) . ' min';
}
echo a4_open($doc, $draft, 'proto');
require __DIR__ . '/_protohead.php';
?>
<div class="pgrid">
    <div class="pc w5"><span class="fl">Patient/in</span><span class="fv"><strong><?= e($p ? patient_name($p, true) : '') ?></strong></span></div>
    <div class="pc w2"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Bürger-ID</span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    <div class="pc w3"><span class="fl">Allergien</span><span class="fv"><?= e($p['allergies'] ?? '') ?: 'keine bekannt' ?></span></div>
    <div class="pc w2"><span class="fl">OP-Datum</span><span class="fv"><?= e(fmt_date($d['op_date'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Schnitt</span><span class="fv"><?= e($d['t_cut'] ?? '') ?></span></div>
    <div class="pc w2"><span class="fl">Naht</span><span class="fv"><?= e($d['t_suture'] ?? '') ?></span></div>
    <div class="pc w2"><span class="fl">Schnitt-Naht-Zeit</span><span class="fv"><?= e($duration) ?></span></div>
    <div class="pc w1"><span class="fl">Saal</span><span class="fv"><?= e($d['room'] ?? '') ?></span></div>
    <div class="pc w3"><span class="fl">Dringlichkeit</span><span class="fv"><?= e(opt_label($type, 'urgency', $d['urgency'] ?? '')) ?></span></div>
    <div class="pc w4"><span class="fl">Operateur/in</span><span class="fv"><?= e($d['surgeon'] ?: user_display($u)) ?></span></div>
    <div class="pc w4"><span class="fl">Assistenz</span><span class="fv"><?= e($d['assistant'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">Anästhesie</span><span class="fv"><?= e($d['anesthesia_doc'] ?? '') ?></span></div>
    <div class="pc w6"><span class="fl">Narkoseart</span><span class="fv"><?= e(opt_label($type, 'anesthesia', $d['anesthesia'] ?? '')) ?></span></div>
    <div class="pc w6"><span class="fl">Lagerung</span><span class="fv"><?= e($d['position'] ?? '') ?></span></div>
</div>
<h3 class="psec">Diagnose und Eingriff</h3>
<div class="pgrid">
    <div class="pc w6 tall"><span class="fl">Diagnose (ICD-10)</span><span class="fv"><strong><?= nl($d['diagnosis'] ?? '') ?></strong></span></div>
    <div class="pc w6 tall"><span class="fl">Eingriff (OPS)</span><span class="fv"><strong><?= nl($d['procedure'] ?? '') ?></strong></span></div>
    <?php if (!empty($d['indication'])): ?><div class="pc w12"><span class="fl">Indikation</span><span class="fv"><?= nl($d['indication']) ?></span></div><?php endif; ?>
</div>
<h3 class="psec">Operationsverlauf</h3>
<div class="pgrid"><div class="pc w12" style="min-height:80mm"><span class="fv op-report"><?= nl($d['report'] ?? '') ?></span></div></div>
<div class="pgrid">
    <div class="pc w6"><span class="fl">Implantate / Material</span><span class="fv"><?= e($d['implants'] ?? '') ?: '—' ?></span></div>
    <div class="pc w2"><span class="fl">Blutverlust</span><span class="fv"><?= e($d['blood_loss'] ?? '') ?><?= ($d['blood_loss'] ?? '') !== '' && is_numeric($d['blood_loss']) ? ' ml' : '' ?></span></div>
    <div class="pc w2"><span class="fl">Drainagen</span><span class="fv"><?= e($d['drains'] ?? '') ?: '—' ?></span></div>
    <div class="pc w2"><span class="fl">Histologie</span><span class="fv chk"><?= box(!empty($d['histology'])) ?> eingesandt</span></div>
    <div class="pc w12"><span class="fl">Komplikationen</span><span class="fv"><?= e($d['complications'] ?? '') ?: 'keine' ?></span></div>
    <div class="pc w12 tall"><span class="fl">Procedere / Nachbehandlung</span><span class="fv"><?= nl($d['aftercare'] ?? '') ?></span></div>
</div>
<div class="proto-sign">
    <?= doc_signature($doc, 'rect', 'Operateur/in', false) ?>
    <div class="sigblock"><div class="sig-sign"><div class="sig-area"><span class="printed"><?= e(fmt_date($d['op_date'] ?? '')) ?></span></div><span class="sig-cap">Diktiert / geschrieben am</span></div></div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
