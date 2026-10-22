<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Bescheinigung über die Einsatztauglichkeit als US-Formular „Fitness-for-Duty Certificate“ –
 * nur Ergebnis für die Dienststelle, keine Befunde (ärztliche Schweigepflicht). Gleiche Felder und Logik wie
 * app/templates/einsatztauglichkeit.php (auch die aus dem SV-Untersuchungsbogen erzeugte Bescheinigung).
 */
$sv = !empty($d['verwendungen']);
$results = [
    'geeignet'  => [$sv ? SV_OVERALL['geeignet'] : 'TAUGLICH', 'Gegen die genannten Verwendungen bestehen aus ärztlicher Sicht keine gesundheitlichen Bedenken.'],
    'bedingt'   => [$sv ? SV_OVERALL['bedingt'] : 'TAUGLICH MIT EINSCHRÄNKUNGEN', 'Keine gesundheitlichen Bedenken unter Beachtung der nachfolgend genannten Einschränkungen.'],
    'befristet' => [$sv ? SV_OVERALL['befristet'] : 'VORÜBERGEHEND NICHT TAUGLICH', 'Befristete gesundheitliche Bedenken. Eine Nachuntersuchung ist erforderlich.'],
    'nicht'     => [$sv ? SV_OVERALL['nicht'] : 'DAUERHAFT NICHT TAUGLICH', 'Dauerhafte gesundheitliche Bedenken gegen die genannte Verwendung.'],
];
$resLabel = ['geeignet' => 'tauglich', 'bedingt' => 'tauglich mit Einschränkungen', 'befristet' => 'vorübergehend nicht tauglich', 'nicht' => 'nicht tauglich'];
$result = (string) ($d['result'] ?? '');
[$resTitle, $resText] = $results[$result] ?? ['', ''];
$art = FITNESS_KINDS[$d['art'] ?? ''][0] ?? ($d['art'] ?? '');
$umfang = array_values(array_filter((array) ($d['umfang'] ?? [])));
$standard = ($d['standard'] ?? '') ?: SV_STANDARD_DEFAULT;
// Untersuchungsumfang: Auswahl der Art (alle Bereiche zum Ankreuzen), bei SV-Bescheinigungen die erhobenen Bereiche
$scopeOptions = [];
foreach ($type['fields'] as $f) {
    if (($f['name'] ?? '') === 'umfang') {
        $scopeOptions = array_keys((array) ($f['options'] ?? []));
    }
}
$scopeAll = $sv ? array_map('strval', $umfang) : array_values(array_unique(array_merge(array_map('strval', $scopeOptions), array_map('strval', $umfang))));
$cfg = ['style' => 'medical', 'title' => $sv ? 'Bescheinigung über die Sonderverwendungsfähigkeit' : 'Bescheinigung über die Einsatztauglichkeit',
    'subtitle' => 'Ärztliche Bescheinigung zur Vorlage bei der Dienststelle (Fitness-for-Duty Certificate)', 'banner' => '', 'seal' => 'round', 'sig_caption' => 'Untersuchende/r Arzt/Ärztin'] + us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ' MED 2813';
}
if (!$cfg['copies']) {
    $cfg['copies'] = ['Original – Dienststelle', 'Kopie – Untersuchte Person', 'Kopie – Akte'];
}
$n = 0;
$L = function (string $label) use (&$n): string {
    return (++$n) . '. ' . dt($label);
};
echo us_paper_open($doc, $draft, 'us-medical us-gen-fit');
?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <div class="us-gen-fit-to">
        <div><span class="fl"><?= e(dt('An (Dienststelle / Arbeitgeber)')) ?></span><div class="us-gen-party-v"><?= ($d['dienststelle'] ?? '') !== '' ? nl($d['dienststelle']) : '—' ?></div></div>
        <div><span class="fl"><?= e(dt('Von (untersuchender Dienst)')) ?></span><div class="us-gen-party-v"><?= e(org('org_name')) ?><br><?= e(org('org_street')) ?> · <?= e(org('org_city')) ?><br><?= e(dt('Tel.')) ?> <?= e(org('phone')) ?></div></div>
    </div>
    <div class="us-sec"><?= e(mb_strtoupper(dt('Untersuchte Person'))) ?></div>
    <div class="us-grid">
        <div class="us-cell w6"><span class="fl"><?= e($L('Name, Vorname')) ?></span><span class="fv"><b><?= e($p ? patient_name($p, true) : '') ?></b></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($L('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    </div>
    <div class="us-sec"><?= e(mb_strtoupper(dt('Angaben zur Untersuchung'))) ?></div>
    <div class="us-grid">
        <div class="us-cell w6"><span class="fl"><?= e($L($sv ? 'Untersuchungsstandard' : 'Untersuchung')) ?></span><span class="fv"><?= e(dt($sv ? $standard : $art)) ?: '—' ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($L('Untersucht am')) ?></span><span class="fv"><?= e(fmt_date($d['exam_date'] ?? '')) ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Anlass')) ?></span><span class="fv"><?= ($d['anlass'] ?? '') !== '' ? e(dt((string) $d['anlass'])) : '—' ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Gültig bis')) ?></span><span class="fv"><b><?= e(fmt_date($d['valid_until'] ?? '')) ?: '—' ?></b></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Nachuntersuchung spätestens')) ?></span><span class="fv"><?= e(fmt_date($d['nachuntersuchung'] ?? '')) ?: '—' ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e($L('Untersuchungsbogen')) ?></span><span class="fv mono"><?= e($d['protokoll'] ?? '') ?: '—' ?></span></div>
        <div class="us-cell w12"><span class="fl"><?= e($L('Untersuchungsumfang')) ?></span><span class="fv"><span class="us-opts"><?php foreach ($scopeAll as $s): ?><span class="us-opt"><?= box(in_array($s, $umfang, true)) ?> <?= e(dt($s)) ?></span><?php endforeach; ?></span></span></div>
    </div>
    <p class="us-gen-lead"><?= e(dt('Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.')) ?></p>
    <?php if ($sv): ?>
        <div class="us-sec"><?= e(mb_strtoupper(dt('Beurteilung je Verwendung'))) ?></div>
        <table class="us-gen-table us-gen-fit-table">
            <tr><th><?= e(dt('Verwendung')) ?></th><?php foreach ($resLabel as $rk => $rl): ?><th class="c"><?= e(dt($rl)) ?></th><?php endforeach; ?></tr>
            <?php foreach ((array) $d['verwendungen'] as $k => $r): ?>
                <tr><td><?= e(dt(FITNESS_KINDS[$k][0] ?? $k)) ?></td><?php foreach ($resLabel as $rk => $rl): ?><td class="c r-<?= e($rk) ?>"><?= box((string) $r === $rk) ?></td><?php endforeach; ?></tr>
            <?php endforeach; ?>
        </table>
        <?php if (!empty($d['profil'])): ?><div class="us-gen-fit-profile"><span class="fl"><?= e(dt('Gesundheitsprofil (PULHES)')) ?></span><b class="mono"><?= e($d['profil']) ?></b></div><?php endif; ?>
    <?php endif; ?>
    <div class="us-sec"><?= e(mb_strtoupper($sv ? dt('Gesamturteil') : dt('Ergebnis'))) ?></div>
    <div class="us-gen-fit-result r-<?= e($result) ?>">
        <div class="us-gen-fit-opts">
            <?php foreach ($results as $rk => [$rt]): ?><div class="us-gen-fit-opt<?= $rk === $result ? ' on' : '' ?>"><?= box($rk === $result) ?> <?= e(dt($rt)) ?></div><?php endforeach; ?>
        </div>
        <?php if ($resText !== ''): ?><p><?= e(dt($resText)) ?></p><?php endif; ?>
    </div>
    <div class="us-grid">
        <div class="us-cell w12 tall"><span class="fl"><?= e($L('Einschränkungen / Auflagen (Work Restrictions)')) ?></span><span class="fv"><?= !empty($d['conditions']) ? nl($d['conditions']) : e(dt('keine')) ?></span></div>
    </div>
    <?php if (!empty($d['valid_until']) && in_array($result, ['geeignet', 'bedingt'], true)): ?><p class="us-gen-lead"><?= e(dt('Diese Bescheinigung gilt bis einschließlich')) ?> <b><?= e(fmt_date($d['valid_until'])) ?></b>.<?= !empty($d['nachuntersuchung']) ? ' ' . e(dt('Nachuntersuchung spätestens am')) . ' <b>' . e(fmt_date($d['nachuntersuchung'])) . '</b>.' : ' ' . e(dt('Vor Ablauf ist eine Nachuntersuchung erforderlich.')) ?></p><?php endif; ?>
    <div class="us-box us-notice us-gen-small"><?= e(dt('Die Befunde unterliegen der ärztlichen Schweigepflicht und sind nicht Bestandteil dieser Bescheinigung')) ?><?= !empty($d['protokoll']) ? e(dt('; sie sind im Untersuchungsbogen {protocol} beim untersuchenden Dienst dokumentiert', ['protocol' => $d['protokoll']])) : '' ?>. <?= e(dt('Der Dienststelle wird ausschließlich das Ergebnis mitgeteilt.')) ?><?= $sv ? ' ' . e(dt('Profil: 1 = uneingeschränkt, 2 = geringe, 3 = deutliche Einschränkung, 4 = nicht verwendungsfähig (P Belastbarkeit, U obere, L untere Extremitäten/Wirbelsäule, H Gehör, E Augen, S psychische Stabilität).')) : '' ?></div>
    <?= us_signatures($doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
