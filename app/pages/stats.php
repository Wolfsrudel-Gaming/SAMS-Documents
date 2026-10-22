<?php
defined('SAMS_ROOT') || exit;
/** Statistik: Aktivität, Fallzahlen, Finanzen, Qualität für einen Zeitraum. */
$preset = in_array($_GET['period'] ?? '', ['week', 'lastweek', 'month', 'lastmonth', 'custom'], true) ? $_GET['period'] : 'week';
[$from, $to, $label] = stats_period($preset, (string) ($_GET['from'] ?? ''), (string) ($_GET['to'] ?? ''));
$s = stats_collect($from, $to);
$maxDay = max(1, max($s['daily'] ?: [0]));
$maxType = max(1, max($s['by_type'] ?: [0]));
$q = array_filter(['period' => $preset, 'from' => $_GET['from'] ?? null, 'to' => $_GET['to'] ?? null]);

ob_start(); ?>
<div class="page-head">
    <div><h1>Statistik</h1><p class="muted"><?= e($label) ?> · <?= e(fmt_date($from)) ?> – <?= e(fmt_date(date('Y-m-d', strtotime($to . ' -1 day')))) ?></p></div>
    <div class="btn-row"><a class="btn" href="<?= e(url('report', $q)) ?>" target="_blank"><?= icon('print') ?>Bericht drucken / PDF</a></div>
</div>
<form class="card filter" action="index.php">
    <input type="hidden" name="p" value="stats">
    <select name="period" onchange="this.form.querySelectorAll('.custom').forEach(function(e){e.hidden=this.value!=='custom'}.bind(this))">
        <?php foreach (['week' => 'Diese Woche', 'lastweek' => 'Letzte Woche', 'month' => 'Dieser Monat', 'lastmonth' => 'Letzter Monat', 'custom' => 'Zeitraum …'] as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $preset) ?>><?= $l ?></option><?php endforeach; ?>
    </select>
    <input type="date" name="from" value="<?= e($_GET['from'] ?? '') ?>" class="custom"<?= $preset === 'custom' ? '' : ' hidden' ?>>
    <input type="date" name="to" value="<?= e($_GET['to'] ?? '') ?>" class="custom"<?= $preset === 'custom' ? '' : ' hidden' ?>>
    <button class="btn">Anzeigen</button>
</form>
<div class="stats">
    <div class="stat"><strong><?= (int) $s['documents'] ?></strong><span>Dokumente</span></div>
    <div class="stat"><strong><?= (int) $s['cases_new'] ?></strong><span>neue Fälle (<?= (int) $s['cases_closed'] ?> abgeschlossen)</span></div>
    <div class="stat"><strong><?= (int) $s['patients_new'] ?></strong><span>neue Patientenakten</span></div>
    <div class="stat"><strong><?= e(duty_format($s['duty_total'])) ?></strong><span>Dienstzeit gesamt</span></div>
    <div class="stat"><strong><?= e(money($s['finance']['invoiced'])) ?></strong><span>abgerechnet (<?= (int) $s['finance']['count'] ?> Rechnungen)</span></div>
    <div class="stat"><strong><?= e(money($s['finance']['open_total'])) ?></strong><span>offene Forderungen gesamt</span></div>
</div>
<div class="grid-2">
    <section class="card">
        <h2>Dokumente pro Tag</h2>
        <div class="daybars">
            <?php foreach ($s['daily'] as $day => $n): ?><div title="<?= e(fmt_date($day)) ?>: <?= $n ?>"><span style="height:<?= round($n / $maxDay * 100) ?>%"></span><small><?= e(date('d.', strtotime($day))) ?></small></div><?php endforeach; ?>
        </div>
    </section>
    <section class="card">
        <h2>Nach Dokumentart</h2>
        <table class="list compact hbars"><tbody>
            <?php foreach ($s['by_type'] as $k => $n): ?><tr><td><?= e(doc_type($k)['title'] ?? $k) ?></td><td class="bar"><span style="width:<?= round($n / $maxType * 100) ?>%"></span></td><td class="right"><?= (int) $n ?></td></tr><?php endforeach; ?>
            <?php if (!$s['by_type']): ?><tr><td class="muted">Keine Dokumente im Zeitraum.</td></tr><?php endif; ?>
        </tbody></table>
    </section>
</div>
<div class="grid-side wide-side reverse">
    <section class="card">
        <h2>Aktivität pro Mitarbeiter/in</h2>
        <table class="list compact">
            <thead><tr><th>Mitarbeiter/in</th><th class="right">Dienstzeit</th><th class="right">Dokumente</th><th class="right">Einträge</th><th class="right">Fälle</th></tr></thead>
            <tbody>
            <?php foreach ($s['staff'] as $r): ?>
                <tr><td><?= e($r['name']) ?> <small class="muted"><?= e($r['rank']) ?></small></td><td class="right"><?= e(duty_format($r['duty'])) ?></td><td class="right"><?= (int) $r['docs'] ?></td><td class="right"><?= (int) $r['entries'] ?></td><td class="right"><?= (int) $r['cases'] ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$s['staff']): ?><tr><td colspan="5" class="muted">Keine Aktivität im Zeitraum.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
    <section>
        <div class="card facts"><h2>Finanzen</h2><dl>
            <dt>Abgerechnet</dt><dd><?= e(money($s['finance']['invoiced'])) ?></dd>
            <dt>davon bezahlt</dt><dd><?= e(money($s['finance']['paid'])) ?></dd>
            <dt>Zahlungseingang im Zeitraum</dt><dd><?= e(money($s['finance']['paid_in_period'])) ?></dd>
            <dt>Offen (gesamt)</dt><dd><?= e(money($s['finance']['open_total'])) ?></dd></dl></div>
        <div class="card facts"><h2>Qualität</h2><dl>
            <dt>Stornierte Dokumente</dt><dd><?= (int) $s['quality']['revoked'] ?> (<?= e((string) $s['quality']['revoked_rate']) ?> %)</dd>
            <dt>Nachträge</dt><dd><?= (int) $s['quality']['addenda'] ?></dd>
            <dt>Freigaben / Zurückweisungen</dt><dd><?= (int) $s['quality']['approved'] ?> / <?= (int) $s['quality']['rejected'] ?></dd>
            <dt>Prüfungen bestanden</dt><dd><?= (int) $s['quality']['exams_passed'] ?> von <?= (int) $s['quality']['exams'] ?></dd>
            <dt>Akteneinträge</dt><dd><?= (int) $s['entries'] ?></dd></dl></div>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = 'Statistik';
require SAMS_ROOT . '/app/layout.php';
