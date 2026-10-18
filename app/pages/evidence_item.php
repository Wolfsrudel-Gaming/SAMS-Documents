<?php
defined('SAMS_ROOT') || exit;
/** Ein Asservat mit Beweismittelkette. */
$id = (int) ($_GET['id'] ?? 0);
$ev = $id ? one('SELECT e.*, c.number AS case_number, u.name AS author FROM evidence e LEFT JOIN cases c ON c.id = e.case_id LEFT JOIN users u ON u.id = e.created_by WHERE e.id = ? AND ' . fw('e'), [$id]) : null;
if (!$ev) {
    not_found('Asservat nicht gefunden.');
}
if (is_post()) {
    csrf_check();
    require_perm('evidence.manage');
    $action = (string) post('action');
    $def = EVIDENCE_ACTIONS[$action] ?? null;
    $note = mb_substr(trim((string) post('note')), 0, 190);
    $counterpart = mb_substr(trim((string) post('counterpart')), 0, 190);
    if (!$def || !in_array($ev['status'], $def[1], true)) {
        flash('error', 'Diese Aktion ist im aktuellen Status nicht möglich.');
    } elseif (in_array($action, ['handed_over', 'returned'], true) && $counterpart === '') {
        flash('error', 'Bitte angeben, an wen übergeben wird.');
    } elseif (in_array($action, ['destroyed', 'note'], true) && $note === '') {
        flash('error', $action === 'note' ? 'Bitte einen Vermerk eingeben.' : 'Bitte Grund bzw. Anordnung der Vernichtung angeben.');
    } else {
        $row = ['updated_at' => now()];
        if ($def[2]) {
            $row['status'] = $def[2];
        }
        if ($action === 'stored' && $counterpart !== '') {
            $row['storage'] = $counterpart;
        }
        update('evidence', $id, $row);
        evidence_move($id, $action, $note, $counterpart);
        audit('evidence.' . $action, 'evidence', $id, $ev['number'] . ': ' . trim($counterpart . ' ' . $note));
        flash('success', 'Eingetragen.');
    }
    redirect(url('evidence_item', ['id' => $id]));
}
$moves = all('SELECT m.*, u.name AS user_name FROM evidence_moves m LEFT JOIN users u ON u.id = m.user_id WHERE m.evidence_id = ? ORDER BY m.id', [$id]);
$labels = ['stored' => 'Eingelagert', 'out' => 'Entnommen', 'handed_over' => 'Übergeben', 'returned' => 'Herausgegeben', 'destroyed' => 'Vernichtet', 'note' => 'Vermerk'];
$storages = setting_list('evidence_storage', 'Asservatenkammer | Waffenschrank | Kühlraum (biologische Spuren) | Fahrzeughalle');

ob_start(); ?>
<div class="page-head">
    <div><h1><span class="mono"><?= e($ev['number']) ?></span> <?= e($ev['title']) ?></h1>
        <p class="muted"><?= e($ev['category']) ?><?= $ev['quantity'] !== '' ? ' · ' . e($ev['quantity']) : '' ?> · <?= status_badge(EVIDENCE_STATUS, $ev['status']) ?></p></div>
    <a class="btn" href="<?= e(url('evidence')) ?>">Zur Übersicht</a>
</div>
<div class="grid-side">
<section>
    <div class="card">
        <dl class="kv">
            <dt>Lagerort</dt><dd><?= e($ev['storage'] ?: '–') ?></dd>
            <dt>Aufgefunden</dt><dd><?= e(fmt_datetime($ev['found_at'])) ?><?= $ev['found_location'] !== '' ? ', ' . e($ev['found_location']) : '' ?></dd>
            <dt>Person</dt><dd><?= $ev['patient_id'] ? '<a href="' . e(url('patient', ['id' => $ev['patient_id']])) . '">' . e($ev['owner']) . '</a>' : e($ev['owner'] ?: '–') ?></dd>
            <dt><?= e(term('case')) ?></dt><dd><?= $ev['case_id'] ? '<a class="mono" href="' . e(url('case', ['id' => $ev['case_id']])) . '">' . e($ev['case_number']) . '</a>' : '–' ?></dd>
            <dt>Erfasst</dt><dd><?= e(fmt_datetime($ev['created_at'])) ?> von <?= e($ev['author'] ?? '–') ?></dd>
        </dl>
        <?php if (trim((string) $ev['description']) !== ''): ?><p><?= nl($ev['description']) ?></p><?php endif; ?>
    </div>
    <div class="card">
        <h2>Beweismittelkette</h2>
        <table class="list compact">
            <thead><tr><th>Zeitpunkt</th><th>Vorgang</th><th>Ort / an</th><th>Vermerk</th><th>Durch</th></tr></thead>
            <tbody>
            <?php foreach ($moves as $m): ?>
                <tr><td class="small"><?= e(fmt_datetime($m['created_at'])) ?></td><td><?= e($labels[$m['action']] ?? $m['action']) ?></td>
                    <td class="small"><?= e($m['counterpart'] ?: '–') ?></td><td class="small"><?= e($m['note']) ?></td><td class="small"><?= e($m['user_name'] ?? '–') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php if (can('evidence.manage')): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <h2 class="w-full">Bewegung eintragen</h2>
    <div class="field w-full"><label>Vorgang</label><select name="action"><?php foreach (EVIDENCE_ACTIONS as $k => [$l, $from]): if (in_array($ev['status'], $from, true)): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endif; endforeach; ?></select></div>
    <div class="field w-full"><label>Lagerort bzw. Empfänger/in</label><input name="counterpart" list="storages" maxlength="190" placeholder="z. B. DOJ Asservatenstelle, Labor, Eigentümer/in"><datalist id="storages"><?php foreach ($storages as $st): ?><option value="<?= e($st) ?>"><?php endforeach; ?></datalist></div>
    <div class="field w-full"><label>Vermerk / Grund</label><input name="note" maxlength="190" placeholder="z. B. Anordnung StA, Verhandlung 2 Js 12/26"></div>
    <div class="form-actions"><button class="btn primary">Eintragen</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = $ev['number'];
require SAMS_ROOT . '/app/layout.php';
