<?php
defined('SAMS_ROOT') || exit;
/** Ärztliches Zeugnis über die (Dienst-/Waffen-/Fahr-)Tauglichkeit */
$results = [
    'geeignet'  => [dt('GEEIGNET'), dt('Gegen die beantragte Tätigkeit / Erlaubnis bestehen aus ärztlicher Sicht keine Bedenken.')],
    'bedingt'   => [dt('BEDINGT GEEIGNET'), dt('Die Eignung ist unter Beachtung der nachfolgend genannten Auflagen gegeben.')],
    'befristet' => [dt('VORÜBERGEHEND NICHT GEEIGNET'), dt('Eine erneute Untersuchung nach Ablauf der genannten Frist wird empfohlen.')],
    'nicht'     => [dt('NICHT GEEIGNET'), dt('Aus ärztlicher Sicht bestehen erhebliche Bedenken gegen die beantragte Tätigkeit / Erlaubnis.')],
];
[$resTitle, $resText] = $results[$d['result'] ?? ''] ?? ['', ''];
$checks = ['vision' => dt('Sehtest'), 'hearing' => dt('Hörtest'), 'reaction' => dt('Reaktionstest'), 'physical' => dt('Körperliche Untersuchung'), 'psych' => dt('Psychologisches Gespräch'), 'drugs' => dt('Drogenscreening')];
echo a4_open($doc, $draft);
echo doc_letterhead($doc, [
    'recipient' => $d['requester'] ?? '',
    'no_patient_address' => ($d['requester'] ?? '') !== '',
    'info' => [dt('Gültig bis') => fmt_date($d['valid_until'] ?? '')],
]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e(dt('Ärztliches Zeugnis')) ?> <small><?= e(opt_label($type, 'purpose', $d['purpose'] ?? '')) ?></small></h1>
    <?= doc_patient_box($doc) ?>
    <p class="lead-in"><?= e(dt('Die/der Obengenannte wurde am {date} von mir persönlich untersucht.', ['date' => fmt_date($d['exam_date'] ?? '')])) ?> <?= e(dt('Die Identität wurde anhand eines amtlichen Lichtbildausweises festgestellt.')) ?></p>
    <h3 class="doc-h"><?= e(dt('Untersuchungsbefunde')) ?></h3>
    <table class="tbl two">
        <?php foreach (array_chunk($checks, 2, true) as $pair): ?>
            <tr><?php foreach ($pair as $k => $l): ?><th><?= e($l) ?></th><td><?= e(opt_label($type, $k, $d[$k] ?? '') ?: '—') ?></td><?php endforeach; ?></tr>
        <?php endforeach; ?>
        <tr><th><?= e(dt('Blutdruck')) ?></th><td><?= e($d['rr'] ?? '') ?: '—' ?></td><th><?= e(dt('Puls')) ?></th><td><?= e($d['hf'] ?? '') ?: '—' ?></td></tr>
        <tr><th><?= e(dt('Größe / Gewicht')) ?></th><td colspan="3"><?= e($d['bmi'] ?? '') ?: '—' ?></td></tr>
    </table>
    <?php if (!empty($d['findings'])): ?><div class="prose"><?= nl($d['findings']) ?></div><?php endif; ?>
    <h3 class="doc-h"><?= e(dt('Ergebnis')) ?></h3>
    <div class="result-box r-<?= e($d['result'] ?? '') ?>"><strong><?= e($resTitle) ?></strong><span><?= e($resText) ?></span></div>
    <?php if (!empty($d['conditions'])): ?><p><strong><?= e(dt('Auflagen / Einschränkungen:')) ?></strong><br><?= nl($d['conditions']) ?></p><?php endif; ?>
    <?php if (!empty($d['valid_until'])): ?><p><?= e(dt('Dieses Zeugnis ist gültig bis einschließlich')) ?> <strong><?= e(fmt_date($d['valid_until'])) ?></strong>.</p><?php endif; ?>
    <?= doc_signature($doc, 'round') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
