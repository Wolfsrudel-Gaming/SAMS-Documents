<?php
defined('SAMS_ROOT') || exit;
/** Bescheinigung über die Einsatztauglichkeit – ohne Befunde (Schweigepflicht), nur Ergebnis für die Dienststelle */
$sv = !empty($d['verwendungen']);
$results = [
    'geeignet'  => [$sv ? SV_OVERALL['geeignet'] : 'TAUGLICH', 'Gegen die genannten Verwendungen bestehen aus ärztlicher Sicht keine gesundheitlichen Bedenken.'],
    'bedingt'   => [$sv ? SV_OVERALL['bedingt'] : 'TAUGLICH MIT EINSCHRÄNKUNGEN', 'Keine gesundheitlichen Bedenken unter Beachtung der nachfolgend genannten Einschränkungen.'],
    'befristet' => [$sv ? SV_OVERALL['befristet'] : 'VORÜBERGEHEND NICHT TAUGLICH', 'Befristete gesundheitliche Bedenken. Eine Nachuntersuchung ist erforderlich.'],
    'nicht'     => [$sv ? SV_OVERALL['nicht'] : 'DAUERHAFT NICHT TAUGLICH', 'Dauerhafte gesundheitliche Bedenken gegen die genannte Verwendung.'],
];
$resLabel = ['geeignet' => 'tauglich', 'bedingt' => 'tauglich mit Einschränkungen', 'befristet' => 'vorübergehend nicht tauglich', 'nicht' => 'nicht tauglich'];
[$resTitle, $resText] = $results[$d['result'] ?? ''] ?? ['', ''];
$art = FITNESS_KINDS[$d['art'] ?? ''][0] ?? ($d['art'] ?? '');
$umfang = array_values(array_filter((array) ($d['umfang'] ?? [])));
$standard = ($d['standard'] ?? '') ?: SV_STANDARD_DEFAULT;
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['dienststelle'] ?? '',
    'no_patient_address' => ($d['dienststelle'] ?? '') !== '',
    'info' => array_filter([dt('Gültig bis') => fmt_date($d['valid_until'] ?? ''), dt('Untersuchungsbogen') => $d['protokoll'] ?? '']),
]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e($sv ? dt('Bescheinigung über die Sonderverwendungsfähigkeit') : dt('Bescheinigung über die Einsatztauglichkeit')) ?> <small><?= e(dt($sv ? $standard : $art)) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <p class="lead-in"><?= e(dt('Die/der Obengenannte wurde am {date}{occasion} arbeitsmedizinisch untersucht{standard}. Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.', [
        'date' => fmt_date($d['exam_date'] ?? ''),
        'occasion' => ($d['anlass'] ?? '') !== '' ? dt(' im Rahmen einer {occasion}', ['occasion' => dt($d['anlass'])]) : '',
        'standard' => $sv ? dt(' – nach dem Untersuchungsstandard „{standard}“', ['standard' => dt($standard)]) : '',
    ])) ?></p>
    <?php if ($umfang): ?><p class="small"><strong><?= e(dt('Untersuchungsumfang:')) ?></strong> <?= e(implode(', ', array_map('dt', $umfang))) ?></p><?php endif; ?>
    <?php if ($sv): ?>
        <h3 class="doc-h"><?= e(dt('Beurteilung je Verwendung')) ?></h3>
        <table class="sv-table">
            <tr><th><?= e(dt('Verwendung')) ?></th><th><?= e(dt('Beurteilung')) ?></th></tr>
            <?php foreach ((array) $d['verwendungen'] as $k => $r): ?><tr><td><?= e(dt(FITNESS_KINDS[$k][0] ?? $k)) ?></td><td class="r-<?= e($r) ?>"><?= e(dt($resLabel[$r] ?? $r)) ?></td></tr><?php endforeach; ?>
            <?php if (!empty($d['profil'])): ?><tr><td><?= e(dt('Gesundheitsprofil (PULHES)')) ?></td><td class="sv-profile"><?= e($d['profil']) ?></td></tr><?php endif; ?>
        </table>
    <?php endif; ?>
    <h3 class="doc-h"><?= e($sv ? dt('Gesamturteil') : dt('Ergebnis')) ?></h3>
    <div class="result-box r-<?= e($d['result'] ?? '') ?>"><strong><?= e(dt($resTitle)) ?></strong><span><?= e(dt($resText)) ?></span></div>
    <?php if (!empty($d['conditions'])): ?><p><strong><?= e(dt('Einschränkungen / Auflagen:')) ?></strong><br><?= nl($d['conditions']) ?></p><?php endif; ?>
    <?php if (!empty($d['valid_until']) && in_array($d['result'] ?? '', ['geeignet', 'bedingt'], true)): ?><p><?= e(dt('Diese Bescheinigung gilt bis einschließlich')) ?> <strong><?= e(fmt_date($d['valid_until'])) ?></strong>.<?= !empty($d['nachuntersuchung']) ? ' ' . e(dt('Nachuntersuchung spätestens am')) . ' <strong>' . e(fmt_date($d['nachuntersuchung'])) . '</strong>.' : ' ' . e(dt('Vor Ablauf ist eine Nachuntersuchung erforderlich.')) ?></p><?php endif; ?>
    <p class="small muted"><?= e(dt('Die Befunde unterliegen der ärztlichen Schweigepflicht und sind nicht Bestandteil dieser Bescheinigung')) ?><?= !empty($d['protokoll']) ? e(dt('; sie sind im Untersuchungsbogen {protocol} beim untersuchenden Dienst dokumentiert', ['protocol' => $d['protokoll']])) : '' ?>. <?= e(dt('Der Dienststelle wird ausschließlich das Ergebnis mitgeteilt.')) ?><?= $sv ? ' ' . e(dt('Profil: 1 = uneingeschränkt, 2 = geringe, 3 = deutliche Einschränkung, 4 = nicht verwendungsfähig (P Belastbarkeit, U obere, L untere Extremitäten/Wirbelsäule, H Gehör, E Augen, S psychische Stabilität).')) : '' ?></p>
    <?= doc_signature($doc, 'round') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
