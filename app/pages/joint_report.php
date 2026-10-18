<?php
defined('SAMS_ROOT') || exit;
/** Gemeinsamer Einsatzbericht: Teile der beteiligten Behörden, eigener Teil bearbeitbar. */
$me = current_user();
$r = joint_report((int) ($_GET['id'] ?? 0));
if (!$r) {
    not_found('Bericht nicht gefunden.');
}
$id = (int) $r['id'];
$owner = (int) $r['faction_id'] === fid();
if (is_post()) {
    csrf_check();
    require_perm('joint.write');
    $action = post('action');
    if ($r['status'] !== 'open' && $action !== 'reopen') {
        flash('error', 'Der Bericht ist abgeschlossen.');
    } elseif ($action === 'section') {
        $body = mb_substr(trim((string) post('body')), 0, 20000);
        $sec = one('SELECT * FROM joint_sections WHERE report_id = ? AND faction_id = ?', [$id, fid()]);
        if ($sec) {
            update('joint_sections', (int) $sec['id'], ['body' => $body, 'user_id' => $me['id'], 'updated_at' => now()]);
        } elseif ($body !== '') {
            insert('joint_sections', ['report_id' => $id, 'faction_id' => fid(), 'user_id' => $me['id'], 'body' => $body, 'created_at' => now(), 'updated_at' => now()]);
        }
        audit('joint.section', 'joint', $id, $r['number']);
        flash('success', 'Eigener Teil gespeichert.');
    } elseif ($action === 'invite' && $owner && ($f = faction_by_id((int) post('faction_id'))) && module_enabled('joint', (int) $f['id']) && (int) $f['id'] !== fid()
        && !scalar('SELECT id FROM joint_participants WHERE report_id = ? AND faction_id = ?', [$id, $f['id']])) {
        insert('joint_participants', ['report_id' => $id, 'faction_id' => (int) $f['id'], 'added_at' => now()]);
        $prev = fid();
        set_faction_context((int) $f['id']);
        notify_users_with('joint.write', 'joint', 'Gemeinsamer Einsatzbericht: ' . $r['title'] . ' – bitte eigenen Teil ergänzen', url('joint_report', ['id' => $id]));
        set_faction_context($prev);
        flash('success', $f['name'] . ' eingeladen.');
    } elseif ($action === 'close' && $owner) {
        update('joint_reports', $id, ['status' => 'closed', 'closed_at' => now(), 'closed_by' => $me['id']]);
        audit('joint.close', 'joint', $id, $r['number']);
        flash('success', 'Bericht abgeschlossen.');
    } elseif ($action === 'reopen' && $owner) {
        update('joint_reports', $id, ['status' => 'open', 'closed_at' => null, 'closed_by' => null]);
        flash('success', 'Bericht wieder geöffnet.');
    }
    redirect(url('joint_report', ['id' => $id]));
}
$parts = joint_participants($id);
$sections = array_column(all('SELECT s.*, u.name AS author FROM joint_sections s LEFT JOIN users u ON u.id = s.user_id WHERE s.report_id = ?', [$id]), null, 'faction_id');
$mine = $sections[fid()] ?? null;
$canWrite = can('joint.write') && $r['status'] === 'open';
$invitable = $owner ? array_values(array_filter(other_factions(), fn($f) => module_enabled('joint', (int) $f['id']) && !in_array((int) $f['id'], array_map(fn($p) => (int) $p['id'], $parts), true))) : [];

ob_start(); ?>
<div class="page-head">
    <div><h1><span class="mono"><?= e($r['number']) ?></span> <?= e($r['title']) ?></h1>
        <p class="muted"><?= e(fmt_datetime($r['occurred_at'])) ?><?= $r['location'] !== '' ? ' · ' . e($r['location']) : '' ?> · <?= $r['status'] === 'open' ? 'offen' : 'abgeschlossen am ' . e(fmt_datetime($r['closed_at'])) ?></p></div>
    <div class="btn-row"><a class="btn" href="<?= e(url('joint')) ?>">Zur Übersicht</a><button class="btn" onclick="window.print()"><?= icon('print') ?>Drucken</button>
        <?php if ($owner && can('joint.write')): ?><form method="post" class="inline"><?= csrf_field() ?><button class="btn" name="action" value="<?= $r['status'] === 'open' ? 'close' : 'reopen' ?>"><?= $r['status'] === 'open' ? 'Abschließen' : 'Wieder öffnen' ?></button></form><?php endif; ?></div>
</div>
<?php if (trim((string) $r['summary']) !== ''): ?><div class="card"><h2>Lage</h2><p><?= nl($r['summary']) ?></p></div><?php endif; ?>
<?php foreach ($parts as $f): $sec = $sections[(int) $f['id']] ?? null; $own = (int) $f['id'] === fid(); ?>
<div class="card joint-part" style="border-left:4px solid <?= e($f['color'] ?: '#64717f') ?>">
    <div class="card-head"><h2><?= e($f['name']) ?></h2><span class="muted small"><?= $sec ? e($sec['author'] ?? '') . ' · ' . e(fmt_datetime($sec['updated_at'] ?: $sec['created_at'])) : 'noch kein Beitrag' ?></span></div>
    <?php if ($own && $canWrite): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="section">
            <textarea name="body" rows="8" placeholder="Eigene Maßnahmen, eingesetzte Kräfte, Feststellungen …"><?= e($mine['body'] ?? '') ?></textarea>
            <div class="form-actions"><button class="btn primary">Eigenen Teil speichern</button></div></form>
    <?php else: ?>
        <?= $sec ? '<p>' . nl($sec['body']) . '</p>' : '<p class="muted">–</p>' ?>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php if ($invitable && $r['status'] === 'open' && can('joint.write')): ?>
<form method="post" class="card inline-form no-print"><?= csrf_field() ?><input type="hidden" name="action" value="invite">
    <strong>Weitere Behörde einladen:</strong> <select name="faction_id"><?php foreach ($invitable as $f): ?><option value="<?= (int) $f['id'] ?>"><?= e($f['name']) ?></option><?php endforeach; ?></select><button class="btn small">Einladen</button></form>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = $r['number'];
require SAMS_ROOT . '/app/layout.php';
