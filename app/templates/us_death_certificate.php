<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Certificate of Death (angelehnt an das US Standard Certificate of Death). */
$protoTitle = 'Certificate of Death';
$protoSub = 'State of San Andreas · Department of Public Health · Vital Records';
$signs = (array) ($d['signs'] ?? []);
$age = ($p['dob'] ?? '') && ($d['death_date'] ?? '') ? (new DateTime($p['dob']))->diff(new DateTime($d['death_date']))->y : '';
$sex = ['m' => 'Male', 'w' => 'Female', 'd' => 'Other'][$p['gender'] ?? ''] ?? '';
$manner = ['natuerlich' => 'Natural', 'nichtnatuerlich' => 'Accident / Homicide / Suicide (non-natural)', 'ungeklaert' => 'Pending investigation / could not be determined'];
echo a4_open($doc, $draft, 'proto death');
require __DIR__ . '/_protohead.php';
?>
<div class="death-part">Decedent</div>
<div class="pgrid">
    <div class="pc w6"><span class="fl">1. Decedent's legal name (last, first)</span><span class="fv"><strong><?= e($p ? patient_name($p, true) : '') ?></strong></span></div>
    <div class="pc w2"><span class="fl">2. Sex</span><span class="fv"><?= e($sex) ?></span></div>
    <div class="pc w2"><span class="fl">3. Date of birth</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">4. Age (years)</span><span class="fv"><?= e((string) $age) ?></span></div>
    <div class="pc w8"><span class="fl">5. Residence</span><span class="fv"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span></div>
    <div class="pc w4"><span class="fl">6. Citizen ID</span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
</div>
<div class="death-part">Death</div>
<div class="pgrid">
    <div class="pc w3"><span class="fl">7. Date of death</span><span class="fv"><?= e(fmt_date($d['death_date'] ?? '')) ?></span></div>
    <div class="pc w3"><span class="fl">8. Time of death</span><span class="fv"><?= e($d['death_time'] ?? '') ?><?= !empty($d['time_estimated']) ? ' (approx. / found)' : '' ?></span></div>
    <div class="pc w6"><span class="fl">9. Place of death</span><span class="fv"><?= e(opt_label($type, 'place', $d['place'] ?? '')) ?></span></div>
    <div class="pc w12"><span class="fl">10. Location (address / facility)</span><span class="fv"><?= e($d['place_address'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">11. Pronounced dead on</span><span class="fv"><?= e(fmt_date($d['exam_date'] ?? '')) ?> <?= e($d['exam_time'] ?? '') ?></span></div>
    <div class="pc w8"><span class="fl">12. Signs of death</span><span class="fv chk"><?php foreach ($type['fields'] as $f): if (($f['name'] ?? '') === 'signs'): foreach ($f['options'] as $k => $l): ?><span><?= box(in_array($k, $signs, true)) ?> <?= e($l) ?></span><?php endforeach; endif; endforeach; ?></span></div>
    <div class="pc w6"><span class="fl">13. Identified by</span><span class="fv"><?= e(opt_label($type, 'identified_by', $d['identified_by'] ?? '')) ?></span></div>
    <div class="pc w6"><span class="fl">14. Manner of death</span><span class="fv"><?= e($manner[$d['manner'] ?? ''] ?? '') ?></span></div>
</div>
<div class="death-part">Cause of death</div>
<div class="pgrid">
    <div class="pc w12"><span class="fl">Part I a. Immediate cause (final disease or condition resulting in death)</span><span class="fv"><?= e($d['cause_a'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl">b. Due to (or as a consequence of)</span><span class="fv"><?= e($d['cause_b'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl">c. Underlying cause (disease or injury that initiated the events)</span><span class="fv"><?= e($d['cause_c'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl">Part II. Other significant conditions contributing to death</span><span class="fv"><?= e($d['cause_2'] ?? '') ?></span></div>
    <div class="pc w4"><span class="fl">15. Referred to coroner / law enforcement</span><span class="fv"><?= !empty($d['police']) ? 'Yes' : 'No' ?></span></div>
    <div class="pc w4"><span class="fl">16. Autopsy requested</span><span class="fv"><?= !empty($d['autopsy']) ? 'Yes' : 'No' ?></span></div>
    <div class="pc w4"><span class="fl">17. Communicable disease</span><span class="fv"><?= !empty($d['infection']) ? 'Yes – precautions required' : 'No' ?></span></div>
    <?php if (!empty($d['remarks'])): ?><div class="pc w12"><span class="fl">Remarks</span><span class="fv"><?= nl($d['remarks']) ?></span></div><?php endif; ?>
</div>
<p class="small">Certifier: to the best of my knowledge, death occurred at the time, date and place, and due to the cause(s) and manner stated.</p>
<?= doc_signature($doc, 'round') ?>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
