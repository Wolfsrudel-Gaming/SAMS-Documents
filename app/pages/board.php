<?php
defined('SAMS_ROOT') || exit;
/** Schwarzes Brett: Aushänge lesen und bestätigen; Leitung veröffentlicht und sieht Bestätigungen. */
$me = current_user();
$manage = can('board.manage');
$id = (int) ($_GET['id'] ?? 0);
$editId = $manage ? (int) ($_GET['edit'] ?? 0) : 0;

if (is_post()) {
    csrf_check();
    $action = post('action');
    if (in_array($action, ['read', 'ack'], true)) {
        $ann = one('SELECT * FROM announcements WHERE id = ?', [(int) post('id')]);
        if ($ann) {
            mark_announcement_read((int) $ann['id'], (int) $me['id'], $action === 'ack');
            if ($action === 'ack') {
                audit('board.ack', 'announcement', (int) $ann['id'], $ann['title']);
                flash('success', 'Kenntnisnahme bestätigt.');
            }
        }
        redirect(post('return') === 'dashboard' ? url('dashboard') : url('board') . '#a' . (int) post('id'));
    }
    require_perm('board.manage');
    if ($action === 'archive') {
        q('UPDATE announcements SET archived = 1 - archived WHERE id = ?', [(int) post('id')]);
        redirect(url('board'));
    }
    $row = [
        'title' => mb_substr(trim((string) post('title')), 0, 190),
        'content' => mb_substr((string) post('content'), 0, 20000),
        'priority' => post('priority') === 'important' ? 'important' : 'normal',
        'department_id' => (int) post('department_id') ?: null,
        'min_level' => max(0, (int) post('min_level')),
        'require_ack' => post('require_ack') ? 1 : 0,
        'pinned' => post('pinned') ? 1 : 0,
        'expires_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) post('expires')) ? post('expires') . ' 23:59:59' : null,
        'updated_at' => now(),
    ];
    if ($row['title'] === '') {
        flash('error', 'Bitte einen Titel angeben.');
        redirect(url('board', ['edit' => (int) post('id') ?: 'new']));
    }
    if ((int) post('id')) {
        update('announcements', (int) post('id'), $row);
        audit('board.update', 'announcement', (int) post('id'), $row['title']);
    } else {
        $nid = insert('announcements', $row + ['created_by' => $me['id'], 'created_at' => now()]);
        mark_announcement_read($nid, (int) $me['id'], true);
        audit('board.create', 'announcement', $nid, $row['title']);
        foreach (announcement_audience($row + ['id' => $nid]) as $u) {
            notify_user((int) $u['id'], 'board', ($row['require_ack'] ? 'Bitte bestätigen: ' : 'Neuer Aushang: ') . $row['title'], url('board') . '#a' . $nid);
        }
    }
    flash('success', 'Aushang veröffentlicht.');
    redirect(url('board'));
}

// Einzelansicht mit Bestätigungen (Leitung)
if ($id && $manage) {
    $ann = one('SELECT * FROM announcements WHERE id = ?', [$id]);
    if (!$ann) {
        not_found();
    }
    $reads = array_column(all('SELECT * FROM announcement_reads WHERE announcement_id = ?', [$id]), null, 'user_id');
    $audience = announcement_audience($ann);
    $done = count(array_filter($audience, fn($u) => $ann['require_ack'] ? !empty($reads[$u['id']]['acknowledged_at']) : isset($reads[$u['id']])));
    ob_start(); ?>
    <div class="page-head"><div><h1><?= e($ann['title']) ?></h1><p class="muted"><?= $ann['require_ack'] ? 'Bestätigt' : 'Gelesen' ?>: <?= $done ?> von <?= count($audience) ?></p></div><a class="btn" href="<?= e(url('board')) ?>">Schwarzes Brett</a></div>
    <div class="card">
        <table class="list compact">
            <thead><tr><th>Mitarbeiter/in</th><th>Gelesen</th><?php if ($ann['require_ack']): ?><th>Bestätigt</th><?php endif; ?></tr></thead>
            <tbody>
            <?php foreach ($audience as $u): $r = $reads[$u['id']] ?? null; ?>
                <tr><td><?= e(user_display($u)) ?> <small class="muted"><?= e(user_rank_name($u)) ?></small></td>
                    <td class="small"><?= $r ? e(fmt_datetime($r['read_at'])) : '<span class="badge grey">offen</span>' ?></td>
                    <?php if ($ann['require_ack']): ?><td class="small"><?= $r && $r['acknowledged_at'] ? '<span class="badge green">' . e(fmt_datetime($r['acknowledged_at'])) . '</span>' : '<span class="badge orange">ausstehend</span>' ?></td><?php endif; ?></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
    $content = ob_get_clean();
    $title = 'Bestätigungen';
    require SAMS_ROOT . '/app/layout.php';
    return;
}

$list = visible_announcements($me, $manage && isset($_GET['archive']));
// Aufrufen der Seite zählt als „gelesen“ (Bestätigung bleibt ein eigener Schritt)
foreach ($list as $a) {
    if (!$a['read_at'] && announcement_for($a, user_department_ids((int) $me['id']), user_level($me))) {
        mark_announcement_read((int) $a['id'], (int) $me['id']);
    }
}
$edit = $editId ? one('SELECT * FROM announcements WHERE id = ?', [$editId]) : null;
$showForm = $manage && ($edit || ($_GET['edit'] ?? '') === 'new');
$form = $edit ?? ['id' => 0, 'title' => '', 'content' => '', 'priority' => 'normal', 'department_id' => null, 'min_level' => 0, 'require_ack' => 0, 'pinned' => 0, 'expires_at' => null];
$departments = all('SELECT * FROM departments WHERE active = 1 ORDER BY sort, name');
$ranks = all('SELECT * FROM ranks ORDER BY level');

ob_start(); ?>
<div class="page-head">
    <div><h1>Schwarzes Brett</h1><p class="muted">Dienstanweisungen, Mitteilungen und Termine.</p></div>
    <div class="btn-row"><?php if ($manage): ?>
        <a class="btn" href="<?= e(url('board', isset($_GET['archive']) ? [] : ['archive' => 1])) ?>"><?= isset($_GET['archive']) ? 'Aktuelle' : 'Mit Archiv' ?></a>
        <a class="btn primary" href="<?= e(url('board', ['edit' => 'new'])) ?>">+ Neuer Aushang</a><?php endif; ?></div>
</div>
<?php if ($showForm): ?>
<form method="post" class="card form-grid">
    <h2><?= $edit ? 'Aushang bearbeiten' : 'Neuer Aushang' ?></h2>
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $form['id'] ?>"><input type="hidden" name="action" value="save">
    <div class="field w-full"><label>Titel <span class="req">*</span></label><input name="title" value="<?= e($form['title']) ?>" required></div>
    <div class="field w-full"><label>Text</label><textarea name="content" rows="8"><?= e($form['content']) ?></textarea><div class="hint">Formatierung wie im Wiki: <code>**fett**</code>, <code>- Liste</code>, <code># Überschrift</code></div></div>
    <div class="field w-quarter"><label>Wichtigkeit</label><select name="priority"><option value="normal">normal</option><option value="important"<?= selected('important', $form['priority']) ?>>wichtig (rot, auf der Übersicht)</option></select></div>
    <div class="field w-quarter"><label>Nur für Abteilung</label><select name="department_id"><option value="">alle</option><?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>"<?= selected($d['id'], $form['department_id']) ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field w-quarter"><label>Ab Rang</label><select name="min_level"><option value="0">alle Ränge</option><?php foreach ($ranks as $r): ?><option value="<?= (int) $r['level'] ?>"<?= selected($r['level'], $form['min_level']) ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field w-quarter"><label>Sichtbar bis</label><input type="date" name="expires" value="<?= e($form['expires_at'] ? substr($form['expires_at'], 0, 10) : '') ?>"></div>
    <div class="field w-full">
        <label class="choice single"><input type="checkbox" name="require_ack" value="1"<?= checked($form['require_ack']) ?>> Lesebestätigung erforderlich (z. B. für Dienstanweisungen)</label>
        <label class="choice single"><input type="checkbox" name="pinned" value="1"<?= checked($form['pinned']) ?>> oben anheften</label>
    </div>
    <div class="form-actions"><button class="btn primary">Veröffentlichen</button><a class="btn" href="<?= e(url('board')) ?>">Abbrechen</a></div>
</form>
<?php endif; ?>
<?php foreach ($list as $a): $pendingAck = $a['require_ack'] && !$a['acknowledged_at'] && announcement_for($a, user_department_ids((int) $me['id']), user_level($me)); ?>
    <article class="card notice<?= $a['priority'] === 'important' ? ' important' : '' ?><?= $a['archived'] ? ' inactive' : '' ?>" id="a<?= (int) $a['id'] ?>">
        <div class="card-head"><h2><?= $a['pinned'] ? '📌 ' : '' ?><?= e($a['title']) ?></h2>
            <span><?= $a['department'] ? '<span class="badge">' . e($a['department']) . '</span> ' : '' ?><?= $a['require_ack'] ? ($a['acknowledged_at'] ? '<span class="badge green">bestätigt</span>' : '<span class="badge orange">Bestätigung erforderlich</span>') : '' ?></span></div>
        <div class="prose-ui"><?= light_markup((string) $a['content']) ?></div>
        <div class="notice-foot">
            <small class="muted"><?= e($a['author'] ?? '') ?> · <?= e(fmt_datetime($a['created_at'])) ?><?= $a['expires_at'] ? ' · sichtbar bis ' . e(fmt_date($a['expires_at'])) : '' ?></small>
            <span class="btn-row">
                <?php if ($pendingAck): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="ack"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button class="btn small primary">Gelesen und verstanden</button></form><?php endif; ?>
                <?php if ($manage): ?>
                    <a class="btn small" href="<?= e(url('board', ['id' => $a['id']])) ?>">Bestätigungen</a>
                    <a class="btn small" href="<?= e(url('board', ['edit' => $a['id']])) ?>">Bearbeiten</a>
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="archive"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button class="btn small"><?= $a['archived'] ? 'Wiederherstellen' : 'Archivieren' ?></button></form>
                <?php endif; ?>
            </span>
        </div>
    </article>
<?php endforeach; ?>
<?php if (!$list): ?><div class="card muted">Keine Aushänge.</div><?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Schwarzes Brett';
require SAMS_ROOT . '/app/layout.php';
