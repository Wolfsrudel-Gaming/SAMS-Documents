<?php
defined('SAMS_ROOT') || exit;
/** Ärztliches Zeugnis über die (Dienst-/Waffen-/Fahr-)Tauglichkeit */
$results = [
    'geeignet'  => ['GEEIGNET', 'Gegen die beantragte Tätigkeit / Erlaubnis bestehen aus ärztlicher Sicht keine Bedenken.'],
    'bedingt'   => ['BEDINGT GEEIGNET', 'Die Eignung ist unter Beachtung der nachfolgend genannten Auflagen gegeben.'],
    'befristet' => ['VORÜBERGEHEND NICHT GEEIGNET', 'Eine erneute Untersuchung nach Ablauf der genannten Frist wird empfohlen.'],
    'nicht'     => ['NICHT GEEIGNET', 'Aus ärztlicher Sicht bestehen erhebliche Bedenken gegen die beantragte Tätigkeit / Erlaubnis.'],
];
[$resTitle, $resText] = $results[$d['result'] ?? ''] ?? ['', ''];
$checks = ['vision' => 'Sehtest', 'hearing' => 'Hörtest', 'reaction' => 'Reaktionstest', 'physical' => 'Körperliche Untersuchung', 'psych' => 'Psychologisches Gespräch', 'drugs' => 'Drogenscreening'];
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['requester'] ?? '',
    'no_patient_address' => ($d['requester'] ?? '') !== '',
    'info' => ['Gültig bis' => fmt_date($d['valid_until'] ?? '')],
]);
?>
<div class="doc-body">
    <h1 class="doc-title">Ärztliches Zeugnis <small><?= e($d['purpose'] ?? '') ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <p class="lead-in">Die/der Obengenannte wurde am <?= e(fmt_date($d['exam_date'] ?? '')) ?> von mir persönlich untersucht. Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.</p>
    <h3 class="doc-h">Untersuchungsbefunde</h3>
    <table class="tbl two">
        <?php foreach (array_chunk($checks, 2, true) as $pair): ?>
            <tr><?php foreach ($pair as $k => $l): ?><th><?= e($l) ?></th><td><?= e(opt_label($type, $k, $d[$k] ?? '') ?: '—') ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        <tr><th>Blutdruck</th><td><?= e($d['rr'] ?? '') ?: '—' ?></td><th>Puls</th><td><?= e($d['hf'] ?? '') ?: '—' ?></td></tr>
        <tr><th>Größe / Gewicht</th><td colspan="3"><?= e($d['bmi'] ?? '') ?: '—' ?></td></tr>
    </table>
    <?php if (!empty($d['findings'])): ?><div class="prose"><?= nl($d['findings']) ?></div><?php endif; ?>
    <h3 class="doc-h">Ergebnis</h3>
    <div class="result-box r-<?= e($d['result'] ?? '') ?>"><strong><?= e($resTitle) ?></strong><span><?= e($resText) ?></span></div>
    <?php if (!empty($d['conditions'])): ?><p><strong>Auflagen / Einschränkungen:</strong><br><?= nl($d['conditions']) ?></p><?php endif; ?>
    <?php if (!empty($d['valid_until'])): ?><p>Dieses Zeugnis ist gültig bis einschließlich <strong><?= e(fmt_date($d['valid_until'])) ?></strong>.</p><?php endif; ?>
    <?= doc_signature($doc, 'round') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
