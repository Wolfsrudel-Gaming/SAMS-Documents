<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Ausbildungs-/Qualifikationsnachweis als „Certificate of Completion“ mit Ausbildungsnachweis
 * (Training Record) im Formularraster. Gleiche Felder wie app/templates/ausbildungsnachweis.php.
 */
$emp = $d['_employee'] ?? [];
$result = (string) ($d['result'] ?? '');
$lines = [
    'mit_auszeichnung' => 'den folgenden Lehrgang mit Auszeichnung abgeschlossen hat:',
    'bestanden' => 'den folgenden Lehrgang erfolgreich abgeschlossen hat:',
    'teilgenommen' => 'an dem folgenden Lehrgang teilgenommen hat:',
];
$heading = $result === 'teilgenommen' ? 'Teilnahmebescheinigung' : 'Abschlusszertifikat';
$period = !empty($d['date_from']) && $d['date_from'] !== ($d['date_to'] ?? '') ? dt('vom {from} bis {to}', ['from' => fmt_date($d['date_from']), 'to' => fmt_date($d['date_to'] ?? '')]) : dt('am {date}', ['date' => fmt_date($d['date_to'] ?? '')]);
$contents = array_values(array_filter(array_map('trim', explode("\n", (string) ($d['contents'] ?? '')))));
$resOptions = [];
foreach ($type['fields'] as $f) {
    if (($f['name'] ?? '') === 'result') {
        $resOptions = (array) ($f['options'] ?? []);
    }
}
$cfg = us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ' TRN 40';
}
echo us_paper_open($doc, $draft, 'us-certificate us-gen-train');
?>
<div class="us-body">
    <div class="us-gen-train-cert">
        <div class="us-gen-train-head">
            <?= logo_html('us-gen-train-logo') ?>
            <div>
                <div class="us-gen-cert-state"><?= e(mb_strtoupper(dt('Staat San Andreas'))) ?></div>
                <div class="us-gen-train-org"><?= e(org('org_name')) ?></div>
                <div class="us-gen-train-sub"><?= e(dt('Aus- und Fortbildung')) ?><?= org('org_sub') !== '' ? ' · ' . e(org('org_sub')) : '' ?></div>
            </div>
        </div>
        <h1 class="us-gen-train-title"><?= e(dt($heading)) ?></h1>
        <p class="us-gen-cert-text"><?= e(dt('Hiermit wird bescheinigt, dass')) ?></p>
        <div class="us-gen-cert-name"><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?: '&nbsp;' ?></div>
        <div class="us-gen-cert-small"><?= e($emp['rank_name'] ?? '') ?><?= !empty($emp['badge_number']) ? ' · ' . e(dt('Dienstnummer {number}', ['number' => $emp['badge_number']])) : '' ?></div>
        <p class="us-gen-cert-text"><?= e(dt($lines[$result] ?? $lines['bestanden'])) ?></p>
        <div class="us-gen-cert-rank"><?= e($d['course'] ?? '') ?: '&nbsp;' ?></div>
        <p class="us-gen-cert-text"><?= e($period) ?><?= !empty($d['hours']) ? ' · ' . e(dt('Umfang: {hours} Unterrichtseinheiten', ['hours' => (int) $d['hours']])) : '' ?></p>
        <div class="us-gen-train-signs">
            <div class="us-gen-cert-sign">
                <div class="us-gen-cert-sigarea"><?php if (!empty($d['instructor'])): ?><span class="printed"><?= e($d['instructor']) ?></span><?php endif; ?></div>
                <span class="us-gen-cert-line"></span>
                <small><?= e(dt('Ausbilder/in')) ?></small>
            </div>
            <div class="us-gen-cert-seal small"><?= seal_svg($doc) ?></div>
            <div class="us-gen-cert-sign">
                <div class="us-gen-cert-sigarea"><?= signature_html($u, doc_rand($doc, 'tsig', -3, 2)) ?></div>
                <span class="us-gen-cert-line"></span>
                <b><?= e(user_display($u)) ?></b><small><?= e(dt('Leitung Aus- und Fortbildung')) ?> · <?= e(fmt_date($doc['issued_at'] ?? now())) ?></small>
            </div>
        </div>
    </div>

    <div class="us-sec"><?= e(mb_strtoupper(dt('Ausbildungsnachweis (Training Record)'))) ?></div>
    <div class="us-grid">
        <div class="us-cell w6"><span class="fl"><?= e(dt('Lehrgang / Qualifikation')) ?></span><span class="fv"><?= e($d['course'] ?? '') ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e(dt('Zeitraum')) ?></span><span class="fv"><?= e(trim(fmt_date($d['date_from'] ?? '') . ' – ' . fmt_date($d['date_to'] ?? ''), ' –')) ?: '—' ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e(dt('Unterrichtseinheiten')) ?></span><span class="fv"><?= !empty($d['hours']) ? (int) $d['hours'] : '—' ?></span></div>
        <div class="us-cell w6"><span class="fl"><?= e(dt('Ergebnis')) ?></span><span class="fv"><span class="us-opts"><?php foreach ($resOptions as $k => $l): ?><span class="us-opt"><?= box((string) $k === $result) ?> <?= e(dt((string) $l)) ?></span><?php endforeach; ?></span></span></div>
        <div class="us-cell w2"><span class="fl"><?= e(dt('Note / Punkte')) ?></span><span class="fv"><?= e($d['grade'] ?? '') ?: '—' ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e(dt('Ausbilder/in')) ?></span><span class="fv"><?= e($d['instructor'] ?? '') ?: '—' ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e(dt('Gültig bis')) ?></span><span class="fv"><?= e(fmt_date($d['valid_until'] ?? '')) ?: '—' ?></span></div>
        <?php if ($contents): ?>
        <div class="us-cell w12"><span class="fl"><?= e(dt('Lehrgangsinhalte')) ?></span><ul class="us-gen-checklist"><?php foreach ($contents as $c): ?><li><?= box(true) ?> <?= e($c) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
    </div>
    <?php if (!empty($d['valid_until'])): ?><p class="us-gen-small"><?= e(dt('Die Qualifikation ist gültig bis {date} und ist danach durch eine Rezertifizierung zu erneuern.', ['date' => fmt_date($d['valid_until'])])) ?></p><?php endif; ?>

</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
