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
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['dienststelle'] ?? '',
    'no_patient_address' => ($d['dienststelle'] ?? '') !== '',
    'info' => array_filter(['Gültig bis' => fmt_date($d['valid_until'] ?? ''), 'Untersuchungsbogen' => $d['protokoll'] ?? '']),
]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= $sv ? 'Bescheinigung über die Sonderverwendungsfähigkeit' : 'Bescheinigung über die Einsatztauglichkeit' ?> <small><?= e($sv ? (($d['standard'] ?? '') ?: SV_STANDARD_DEFAULT) : $art) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <p class="lead-in">Die/der Obengenannte wurde am <?= e(fmt_date($d['exam_date'] ?? '')) ?><?= ($d['anlass'] ?? '') !== '' ? ' im Rahmen einer ' . e($d['anlass']) : '' ?> arbeitsmedizinisch untersucht<?= $sv ? ' – nach dem Untersuchungsstandard „' . e(($d['standard'] ?? '') ?: SV_STANDARD_DEFAULT) . '“' : '' ?>. Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.</p>
    <?php if ($umfang): ?><p class="small"><strong>Untersuchungsumfang:</strong> <?= e(implode(', ', $umfang)) ?></p><?php endif; ?>
    <?php if ($sv): ?>
        <h3 class="doc-h">Beurteilung je Verwendung</h3>
        <table class="sv-table">
            <tr><th>Verwendung</th><th>Beurteilung</th></tr>
            <?php foreach ((array) $d['verwendungen'] as $k => $r): ?><tr><td><?= e(FITNESS_KINDS[$k][0] ?? $k) ?></td><td class="r-<?= e($r) ?>"><?= e($resLabel[$r] ?? $r) ?></td></tr><?php endforeach; ?>
            <?php if (!empty($d['profil'])): ?><tr><td>Gesundheitsprofil (PULHES)</td><td class="sv-profile"><?= e($d['profil']) ?></td></tr><?php endif; ?>
        </table>
    <?php endif; ?>
    <h3 class="doc-h"><?= $sv ? 'Gesamturteil' : 'Ergebnis' ?></h3>
    <div class="result-box r-<?= e($d['result'] ?? '') ?>"><strong><?= e($resTitle) ?></strong><span><?= e($resText) ?></span></div>
    <?php if (!empty($d['conditions'])): ?><p><strong>Einschränkungen / Auflagen:</strong><br><?= nl($d['conditions']) ?></p><?php endif; ?>
    <?php if (!empty($d['valid_until']) && in_array($d['result'] ?? '', ['geeignet', 'bedingt'], true)): ?><p>Diese Bescheinigung gilt bis einschließlich <strong><?= e(fmt_date($d['valid_until'])) ?></strong>.<?= !empty($d['nachuntersuchung']) ? ' Nachuntersuchung spätestens am <strong>' . e(fmt_date($d['nachuntersuchung'])) . '</strong>.' : ' Vor Ablauf ist eine Nachuntersuchung erforderlich.' ?></p><?php endif; ?>
    <p class="small muted">Die Befunde unterliegen der ärztlichen Schweigepflicht und sind nicht Bestandteil dieser Bescheinigung<?= !empty($d['protokoll']) ? '; sie sind im Untersuchungsbogen ' . e($d['protokoll']) . ' beim untersuchenden Dienst dokumentiert' : '' ?>. Der Dienststelle wird ausschließlich das Ergebnis mitgeteilt.<?= $sv ? ' Profil: 1 = uneingeschränkt, 2 = geringe, 3 = deutliche Einschränkung, 4 = nicht verwendungsfähig (P Belastbarkeit, U obere, L untere Extremitäten/Wirbelsäule, H Gehör, E Augen, S psychische Stabilität).' : '' ?></p>
    <?= doc_signature($doc, 'round') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
