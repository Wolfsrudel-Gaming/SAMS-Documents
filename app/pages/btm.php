<?php
defined('SAMS_ROOT') || exit;
/** BtM-Buch: Karteikarte je Betäubungsmittel mit laufendem Bestand, Prüfung durch die verantwortliche Person, Druckansicht. */
$me = current_user();
$btmMeds = all('SELECT * FROM medications WHERE is_btm = 1 ORDER BY active DESC, name');
$med = null;
foreach ($btmMeds as $m) {
    if ((int) $m['id'] === (int) ($_GET['med'] ?? $_POST['medication_id'] ?? 0) || !$med) {
        $med = $m;
    }
}
$month = preg_match('/^\d{4}-\d{2}$/', (string) ($_GET['month'] ?? '')) ? $_GET['month'] : date('Y-m');

if (is_post()) {
    csrf_check();
    require_perm('btm.manage');
    if (post('action') === 'check' && $med) {
        $counted = (int) post('counted');
        if ($counted !== (int) $med['stock']) {
            flash('error', 'Abweichung: gezählt ' . $counted . ', Buchbestand ' . $med['stock'] . '. Bitte im Medikamentenbestand eine Korrektur mit Begründung buchen und danach erneut prüfen.');
        } else {
            medication_move((int) $med['id'], 0, 'check', ['note' => 'Bestand geprüft und für richtig befunden' . (trim((string) post('note')) !== '' ? ': ' . trim((string) post('note')) : '')]);
            audit('btm.check', 'medication', (int) $med['id'], $med['name'] . ': ' . $counted . ' ' . $med['unit']);
            flash('success', 'Prüfung eingetragen.');
        }
    }
    redirect(url('btm', ['med' => $med['id'] ?? 0, 'month' => $month]));
}

$from = "$month-01 00:00:00";
$to = date('Y-m-d H:i:s', strtotime("$from +1 month"));
$moves = $med ? all('SELECT m.*, u.name AS user_name, u.title AS user_title, w.name AS witness_name, p.first_name, p.last_name, d.number AS doc_number FROM medication_moves m
    LEFT JOIN users u ON u.id = m.user_id LEFT JOIN users w ON w.id = m.witness_id LEFT JOIN patients p ON p.id = m.patient_id LEFT JOIN documents d ON d.id = m.document_id
    WHERE m.medication_id = ? AND m.created_at >= ? AND m.created_at < ? ORDER BY m.id', [$med['id'], $from, $to]) : [];
$opening = $med ? (int) (scalar('SELECT balance FROM medication_moves WHERE medication_id = ? AND created_at < ? ORDER BY id DESC LIMIT 1', [$med['id'], $from]) ?? 0) : 0;
$lastCheck = $med ? one("SELECT m.*, u.name AS user_name FROM medication_moves m LEFT JOIN users u ON u.id = m.user_id WHERE m.medication_id = ? AND m.kind = 'check' ORDER BY m.id DESC LIMIT 1", [$med['id']]) : null;
$in = array_sum(array_map(fn($m) => max(0, (int) $m['delta']), $moves));
$out = array_sum(array_map(fn($m) => min(0, (int) $m['delta']), $moves));

ob_start(); ?>
<div class="page-head no-print">
    <div><h1>BtM-Buch</h1><p class="muted">Nachweis über Zugang, Abgang und Bestand der Betäubungsmittel · Buchungen im <a href="<?= e(url('stock', ['f' => 'btm'])) ?>">Medikamentenbestand</a></p></div>
    <div class="btn-row"><button class="btn" onclick="window.print()"><?= icon('print') ?>Drucken</button></div>
</div>
<?php if (!$btmMeds): ?>
    <div class="card muted">Noch keine Betäubungsmittel angelegt. Im Medikamentenbestand einen Artikel als „Betäubungsmittel“ kennzeichnen.</div>
<?php else: ?>
<div class="tabs page-tabs no-print"><?php foreach ($btmMeds as $m): ?><a href="<?= e(url('btm', ['med' => $m['id'], 'month' => $month])) ?>" class="<?= (int) $m['id'] === (int) $med['id'] ? 'active' : '' ?>"><?= e($m['name']) ?></a><?php endforeach; ?></div>
<form class="card filter no-print" method="get"><input type="hidden" name="p" value="btm"><input type="hidden" name="med" value="<?= (int) $med['id'] ?>">
    <label>Monat <input type="month" name="month" value="<?= e($month) ?>"></label><button class="btn">Anzeigen</button></form>

<section class="card btm-card">
    <div class="btm-head">
        <div><small class="muted">Betäubungsmittel</small><h2><?= e($med['name']) ?></h2></div>
        <div><small class="muted">Einrichtung</small><strong><?= e(setting('org_name', 'San Andreas Medical Services')) ?></strong></div>
        <div><small class="muted">Zeitraum</small><strong><?= e(fmt_date($from, 'F Y')) ?></strong></div>
        <div><small class="muted">Bestand aktuell</small><strong><?= (int) $med['stock'] ?> <?= e($med['unit']) ?></strong></div>
    </div>
    <table class="list compact btm-table">
        <thead><tr><th>Datum</th><th>Vorgang</th><th>Patient / Lieferant / Empfänger</th><th class="right">Zugang</th><th class="right">Abgang</th><th class="right">Bestand</th><th>Verantwortlich</th><th>Zeuge</th></tr></thead>
        <tbody>
            <tr class="muted"><td><?= e(fmt_date($from)) ?></td><td colspan="4">Übertrag</td><td class="right"><?= $opening ?></td><td colspan="2"></td></tr>
            <?php foreach ($moves as $mv): ?>
            <tr>
                <td class="small"><?= e(fmt_datetime($mv['created_at'])) ?></td>
                <td class="small"><?= e(MED_MOVE_KINDS[$mv['kind']] ?? $mv['kind']) ?><?= $mv['doc_number'] ? '<br><span class="mono">' . e($mv['doc_number']) . '</span>' : '' ?><?= $mv['note'] && $mv['note'] !== $mv['doc_number'] ? '<br><span class="muted">' . e($mv['note']) . '</span>' : '' ?></td>
                <td class="small"><?= e(implode(' · ', array_filter([$mv['first_name'] ? patient_name($mv, true) : '', $mv['party']]))) ?: '–' ?></td>
                <td class="right"><?= $mv['delta'] > 0 ? (int) $mv['delta'] : '' ?></td>
                <td class="right"><?= $mv['delta'] < 0 ? -(int) $mv['delta'] : '' ?></td>
                <td class="right"><strong><?= (int) $mv['balance'] ?></strong></td>
                <td class="small"><?= e(trim($mv['user_title'] . ' ' . $mv['user_name'])) ?: ($mv['source'] === 'game' ? 'Apotheke' : '–') ?></td>
                <td class="small"><?= e((string) $mv['witness_name']) ?: '–' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$moves): ?><tr><td colspan="8" class="muted">Keine Bewegungen in diesem Monat.</td></tr><?php endif; ?>
        </tbody>
        <tfoot><tr><th colspan="3">Summe <?= e(fmt_date($from, 'F Y')) ?></th><th class="right"><?= $in ?></th><th class="right"><?= -$out ?></th><th class="right"><?= $moves ? (int) end($moves)['balance'] : $opening ?></th><th colspan="2"></th></tr></tfoot>
    </table>
    <p class="small muted">Letzte Prüfung: <?= $lastCheck ? e(fmt_datetime($lastCheck['created_at']) . ' durch ' . $lastCheck['user_name']) : 'noch keine' ?></p>
    <div class="btm-sign print-only"><div>Datum, Unterschrift der verantwortlichen Person</div></div>
</section>
<?php if (can('btm.manage')): ?>
<form method="post" class="card inline-form no-print">
    <?= csrf_field() ?><input type="hidden" name="action" value="check"><input type="hidden" name="medication_id" value="<?= (int) $med['id'] ?>">
    <strong>Bestand prüfen:</strong>
    <label>gezählt <input type="number" name="counted" min="0" required style="width:6rem"></label> <?= e($med['unit']) ?>
    <input name="note" placeholder="Bemerkung (optional)" maxlength="120">
    <button class="btn primary">Prüfung eintragen</button>
</form>
<?php endif; ?>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'BtM-Buch';
require SAMS_ROOT . '/app/layout.php';
