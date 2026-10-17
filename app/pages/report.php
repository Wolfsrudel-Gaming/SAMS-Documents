<?php
defined('SAMS_ROOT') || exit;
/** Wochen-/Monatsbericht als druckbares A4-Dokument (Drucken / Als PDF speichern). */
$me = current_user();
$preset = in_array($_GET['period'] ?? '', ['week', 'lastweek', 'month', 'lastmonth', 'custom'], true) ? $_GET['period'] : 'lastweek';
[$from, $to, $label] = stats_period($preset, (string) ($_GET['from'] ?? ''), (string) ($_GET['to'] ?? ''));
$s = stats_collect($from, $to);
$doc = ['number' => 'BERICHT-' . preg_replace('/[^0-9]/', '', $label), 'verify_code' => '', 'issuer_snapshot' => issuer_snapshot($me), 'issued_at' => now()];
$protoTitle = 'Tätigkeitsbericht';
$protoSub = $label . ' · ' . fmt_date($from) . ' – ' . fmt_date(date('Y-m-d', strtotime($to . ' -1 day'))) . ' · intern';
ob_start(); ?>
<div class="page-head no-print"><h1>Bericht <?= e($label) ?></h1><button class="btn primary" onclick="window.print()">Drucken / PDF</button></div>
<div class="doc-stage"><div class="doc-wrap">
<section class="paper a4 proto">
    <?php require SAMS_ROOT . '/app/templates/_protohead.php'; ?>
    <h3 class="psec">Kennzahlen</h3>
    <div class="pgrid">
        <div class="pc w3"><span class="fl">Dokumente</span><span class="fv big"><?= (int) $s['documents'] ?></span></div>
        <div class="pc w3"><span class="fl">Neue Fälle / abgeschlossen</span><span class="fv big"><?= (int) $s['cases_new'] ?> / <?= (int) $s['cases_closed'] ?></span></div>
        <div class="pc w3"><span class="fl">Neue Patientenakten</span><span class="fv big"><?= (int) $s['patients_new'] ?></span></div>
        <div class="pc w3"><span class="fl">Dienstzeit gesamt</span><span class="fv big"><?= e(duty_format($s['duty_total'])) ?></span></div>
    </div>
    <h3 class="psec">Dokumente nach Art</h3>
    <table class="tbl"><thead><tr><th>Dokumentart</th><th style="width:25mm" class="right">Anzahl</th></tr></thead><tbody>
        <?php foreach ($s['by_type'] as $k => $n): ?><tr><td><?= e(doc_type($k)['title'] ?? $k) ?></td><td class="right"><?= (int) $n ?></td></tr><?php endforeach; ?>
        <?php if (!$s['by_type']): ?><tr><td colspan="2">keine</td></tr><?php endif; ?>
    </tbody></table>
    <h3 class="psec">Personal</h3>
    <table class="tbl"><thead><tr><th>Mitarbeiter/in</th><th>Rang</th><th class="right">Dienstzeit</th><th class="right">Dok.</th><th class="right">Einträge</th><th class="right">Fälle</th></tr></thead><tbody>
        <?php foreach ($s['staff'] as $r): ?><tr><td><?= e($r['name']) ?></td><td><?= e($r['rank']) ?></td><td class="right"><?= e(duty_format($r['duty'])) ?></td><td class="right"><?= (int) $r['docs'] ?></td><td class="right"><?= (int) $r['entries'] ?></td><td class="right"><?= (int) $r['cases'] ?></td></tr><?php endforeach; ?>
        <?php if (!$s['staff']): ?><tr><td colspan="6">keine Aktivität</td></tr><?php endif; ?>
    </tbody></table>
    <h3 class="psec">Finanzen und Qualität</h3>
    <div class="pgrid">
        <div class="pc w3"><span class="fl">Abgerechnet</span><span class="fv"><?= e(money($s['finance']['invoiced'])) ?></span></div>
        <div class="pc w3"><span class="fl">Zahlungseingang</span><span class="fv"><?= e(money($s['finance']['paid_in_period'])) ?></span></div>
        <div class="pc w3"><span class="fl">Offene Forderungen</span><span class="fv"><?= e(money($s['finance']['open_total'])) ?></span></div>
        <div class="pc w3"><span class="fl">Stornoquote</span><span class="fv"><?= e((string) $s['quality']['revoked_rate']) ?> %</span></div>
        <div class="pc w3"><span class="fl">Nachträge</span><span class="fv"><?= (int) $s['quality']['addenda'] ?></span></div>
        <div class="pc w3"><span class="fl">Freigaben / Zurückweisungen</span><span class="fv"><?= (int) $s['quality']['approved'] ?> / <?= (int) $s['quality']['rejected'] ?></span></div>
        <div class="pc w3"><span class="fl">Prüfungen bestanden</span><span class="fv"><?= (int) $s['quality']['exams_passed'] ?> / <?= (int) $s['quality']['exams'] ?></span></div>
        <div class="pc w3"><span class="fl">Akteneinträge</span><span class="fv"><?= (int) $s['entries'] ?></span></div>
    </div>
    <p class="small" style="margin-top:4mm">Erstellt am <?= e(fmt_datetime(now())) ?> durch <?= e(user_display($me)) ?>. Interner Bericht – enthält keine Patientendaten.</p>
</section>
</div></div>
<?php
$content = ob_get_clean();
$title = 'Bericht ' . $label;
$withDocumentCss = true;
require SAMS_ROOT . '/app/layout.php';
