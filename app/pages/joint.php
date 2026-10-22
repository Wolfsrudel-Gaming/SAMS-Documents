<?php
defined('SAMS_ROOT') || exit;
/**
 * Gemeinsame Einsatzberichte: eine Behörde legt den Einsatz an und lädt weitere ein; jede beteiligte Behörde
 * schreibt ihren eigenen Teil. Sichtbar nur für die beteiligten Behörden.
 */
$me = current_user();
$partners = array_values(array_filter(other_factions(), fn($f) => module_enabled('joint', (int) $f['id'])));
if (is_post()) {
    csrf_check();
    require_perm('joint.write');
    $row = ['title' => mb_substr(trim((string) post('title')), 0, 190), 'location' => mb_substr(trim((string) post('location')), 0, 190),
        'occurred_at' => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) post('occurred_at')) ? str_replace('T', ' ', post('occurred_at')) . ':00' : now(),
        'summary' => mb_substr(trim((string) post('summary')), 0, 4000), 'status' => 'open', 'created_by' => $me['id'], 'created_at' => now()];
    if ($row['title'] === '') {
        flash('error', 'Bitte eine Bezeichnung angeben.');
        redirect(url('joint'));
    }
    $row['number'] = authority_number('joint_reports', 'GE');
    $id = insert('joint_reports', $row);
    $invited = array_values(array_intersect(array_map('intval', (array) ($_POST['factions'] ?? [])), array_map(fn($f) => (int) $f['id'], $partners)));
    foreach ($invited as $fidTo) {
        insert('joint_participants', ['report_id' => $id, 'faction_id' => $fidTo, 'added_at' => now()]);
        $prev = fid();
        set_faction_context($fidTo);
        notify_users_with('joint.write', 'joint', 'Gemeinsamer Einsatzbericht: ' . $row['title'] . ' – bitte eigenen Teil ergänzen', url('joint_report', ['id' => $id]));
        set_faction_context($prev);
    }
    audit('joint.create', 'joint', $id, $row['number'] . ' ' . $row['title']);
    redirect(url('joint_report', ['id' => $id]));
}
$rows = all("SELECT r.*, f.short AS owner_short, (SELECT COUNT(*) FROM joint_sections s WHERE s.report_id = r.id) AS sections,
    (SELECT COUNT(*) FROM joint_sections s WHERE s.report_id = r.id AND s.faction_id = ?) AS mine
    FROM joint_reports r JOIN factions f ON f.id = r.faction_id
    WHERE r.faction_id = ? OR EXISTS (SELECT 1 FROM joint_participants p WHERE p.report_id = r.id AND p.faction_id = ?) ORDER BY r.status = 'closed', r.occurred_at DESC LIMIT 200", [fid(), fid(), fid()]);
ob_start(); ?>
<div class="page-head"><div><h1>Gemeinsame Einsatzberichte</h1><p class="muted">Ein Bericht, jede beteiligte Behörde schreibt ihren Teil (z. B. Feuerwehr, Rettungsdienst und Polizei beim Großbrand).</p></div></div>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Nummer</th><th>Einsatz</th><th>Zeit</th><th>Beiträge</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr onclick="location.href='<?= e(url('joint_report', ['id' => $r['id']])) ?>'"><td class="mono"><?= e($r['number']) ?></td>
                <td><?= e($r['title']) ?><br><small class="muted"><?= e($r['location']) ?> · angelegt von <?= e($r['owner_short']) ?></small></td>
                <td class="small"><?= e(fmt_datetime($r['occurred_at'])) ?></td>
                <td class="small"><?= (int) $r['sections'] ?><?= $r['status'] === 'open' && !(int) $r['mine'] ? ' <span class="badge orange">eigener Teil fehlt</span>' : '' ?></td>
                <td><?= $r['status'] === 'open' ? '<span class="badge blue">offen</span>' : '<span class="badge green">abgeschlossen</span>' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted">Noch keine gemeinsamen Berichte.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php if (can('joint.write')): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <h2 class="w-full">Bericht anlegen</h2>
    <div class="field w-full"><label>Einsatz</label><input name="title" required maxlength="190" placeholder="z. B. Großbrand Lagerhalle Elysian Island"></div>
    <div class="field"><label>Zeitpunkt</label><input type="datetime-local" name="occurred_at" value="<?= e(date('Y-m-d\TH:i')) ?>"></div>
    <div class="field"><label>Ort</label><input name="location" maxlength="190"></div>
    <div class="field w-full"><label>Lage / Zusammenfassung</label><textarea name="summary" rows="3"></textarea></div>
    <div class="field w-full"><label>Beteiligte Behörden</label><div class="choices vertical">
        <?php foreach ($partners as $f): ?><label class="choice"><input type="checkbox" name="factions[]" value="<?= (int) $f['id'] ?>"> <?= e($f['name']) ?></label><?php endforeach; ?>
        <?php if (!$partners): ?><span class="muted small">Keine anderen Behörden mit diesem Modul.</span><?php endif; ?></div></div>
    <div class="form-actions"><button class="btn primary">Anlegen</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Einsatzberichte';
require SAMS_ROOT . '/app/layout.php';
