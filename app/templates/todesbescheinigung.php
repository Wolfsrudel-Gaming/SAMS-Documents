<?php
defined('SAMS_ROOT') || exit;
/** Todesbescheinigung – vertraulicher und nichtvertraulicher Teil */
$protoTitle = 'Todesbescheinigung';
$protoSub = 'Leichenschauschein · State of San Andreas';
$places = ['krankenhaus' => 'Krankenhaus', 'wohnung' => 'Wohnung', 'oeffentlich' => 'Öffentlicher Ort', 'rettungsmittel' => 'Rettungsmittel', 'sonstig' => 'Sonstiger Ort'];
$signs = (array) ($d['signs'] ?? []);
$signLabels = ['totenflecke' => 'Totenflecke', 'totenstarre' => 'Totenstarre', 'faeulnis' => 'Fäulnis', 'verletzung' => 'Mit dem Leben nicht vereinbare Verletzungen', 'reanimation' => 'Erfolglose Reanimation > 20 min'];
$manners = ['natuerlich' => 'Natürlicher Tod', 'nichtnatuerlich' => 'Nicht natürlicher Tod', 'ungeklaert' => 'Ungeklärt, ob natürlicher oder nicht natürlicher Tod'];
echo a4_open($doc, $draft, 'proto death');
require __DIR__ . '/_protohead.php';
?>
<div class="death-part">Nichtvertraulicher Teil</div>
<div class="pgrid">
    <div class="pc w6"><span class="fl">Familienname, Vorname(n)</span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
    <div class="pc w2"><span class="fl">Geschlecht</span><span class="fv"><?= e(['m' => 'männlich', 'w' => 'weiblich', 'd' => 'divers'][$p['gender'] ?? ''] ?? '') ?></span></div>
    <div class="pc w2"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
    <div class="pc w2"><span class="fl">Alter</span><span class="fv"><?= e(($p['dob'] ?? '') && ($d['death_date'] ?? '') ? (new DateTime($p['dob']))->diff(new DateTime($d['death_date']))->y . ' J.' : '') ?></span></div>
    <div class="pc w8"><span class="fl">Letzte Anschrift</span><span class="fv"><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></span></div>
    <div class="pc w4"><span class="fl">Bürger-ID</span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
    <div class="pc w3"><span class="fl">Sterbedatum</span><span class="fv"><strong><?= e(fmt_date($d['death_date'] ?? '')) ?></strong></span></div>
    <div class="pc w2"><span class="fl">Uhrzeit</span><span class="fv"><?= e($d['death_time'] ?? '') ?></span></div>
    <div class="pc w7"><span class="fl">&nbsp;</span><span class="fv chk"><?= box(!empty($d['time_estimated'])) ?> Zeitpunkt geschätzt / tot aufgefunden</span></div>
    <div class="pc w12"><span class="fl">Sterbeort</span><span class="fv chk"><?php foreach ($places as $k => $l): ?><span class="nowrap"><?= box(($d['place'] ?? '') === $k) ?> <?= e($l) ?></span> <?php endforeach; ?></span>
        <span class="fv"><?= e($d['place_address'] ?? '') ?></span></div>
    <div class="pc w6"><span class="fl">Leichenschau am / um</span><span class="fv"><?= e(fmt_date($d['exam_date'] ?? '')) ?>, <?= e($d['exam_time'] ?? '') ?> Uhr</span></div>
    <div class="pc w6"><span class="fl">Identität festgestellt durch</span><span class="fv"><?= e($d['identified_by'] ?? '') ?></span></div>
    <div class="pc w12"><span class="fl">Sichere Todeszeichen</span><span class="fv chk"><?php foreach ($signLabels as $k => $l): ?><span class="nowrap"><?= box(in_array($k, $signs, true)) ?> <?= e($l) ?></span> <?php endforeach; ?></span></div>
    <div class="pc w12 manner"><span class="fl">Todesart</span><span class="fv chk"><?php foreach ($manners as $k => $l): ?><span class="nowrap"><?= box(($d['manner'] ?? '') === $k) ?> <strong><?= e($l) ?></strong></span> <?php endforeach; ?></span></div>
    <div class="pc w4"><span class="fv chk"><?= box(!empty($d['police'])) ?> Polizei benachrichtigt</span></div>
    <div class="pc w4"><span class="fv chk"><?= box(!empty($d['autopsy'])) ?> Obduktion angeregt</span></div>
    <div class="pc w4"><span class="fv chk"><?= box(!empty($d['infection'])) ?> Infektionsgefahr</span></div>
</div>

<div class="death-part conf">Vertraulicher Teil – Angaben zur Todesursache</div>
<table class="tbl cause">
    <tr><th>I a</th><td>Unmittelbar zum Tode führende Krankheit / Verletzung</td><td class="printed"><?= e($d['cause_a'] ?? '') ?></td></tr>
    <tr><th>I b</th><td>als Folge von</td><td class="printed"><?= e($d['cause_b'] ?? '') ?></td></tr>
    <tr><th>I c</th><td>als Folge von (Grundleiden)</td><td class="printed"><?= e($d['cause_c'] ?? '') ?></td></tr>
    <tr><th>II</th><td>Andere wesentliche Krankheiten</td><td class="printed"><?= e($d['cause_2'] ?? '') ?></td></tr>
</table>
<?php if (!empty($d['remarks'])): ?><div class="pgrid"><div class="pc w12"><span class="fl">Bemerkungen</span><span class="fv"><?= nl($d['remarks']) ?></span></div></div><?php endif; ?>
<p class="small">Ich habe die Leichenschau persönlich vorgenommen und die Leiche vollständig entkleidet untersucht.</p>
<?= doc_signature($doc, 'round', 'Unterschrift und Stempel der leichenschauenden Ärztin / des leichenschauenden Arztes') ?>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
