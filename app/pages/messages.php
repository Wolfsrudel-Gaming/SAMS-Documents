<?php
defined('SAMS_ROOT') || exit;
/** Interne Nachrichten: Posteingang, neue Nachricht, Unterhaltung. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
// Kolleg/innen der eigenen Fraktion mit Nachrichtenrecht und Angehörige anderer (aktiver) Behörden
$activeFactions = array_map(fn($f) => (int) $f['id'], factions());
$staff = array_values(array_filter(all('SELECT * FROM users WHERE active = 1 AND id <> ? ORDER BY name', [$me['id']]),
    fn($u) => (int) $u['faction_id'] === fid() ? in_array('messages.use', user_permissions($u), true) : in_array((int) $u['faction_id'], $activeFactions, true)));
$staffIds = array_map('intval', array_column($staff, 'id'));
$departments = all('SELECT * FROM departments WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name');

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'send') {
        $subject = mb_substr(trim((string) post('subject')), 0, 190);
        $body = mb_substr(trim((string) post('body')), 0, 10000);
        $to = array_values(array_intersect(array_map('intval', (array) ($_POST['to'] ?? [])), $staffIds));
        foreach (array_map('intval', (array) ($_POST['to_dep'] ?? [])) as $dep) {
            $members = array_map('intval', array_column(all('SELECT user_id FROM user_departments WHERE department_id = ?', [$dep]), 'user_id'));
            $to = array_merge($to, array_intersect($members, $staffIds));
        }
        $to = array_values(array_unique($to));
        if ($subject === '' || $body === '' || !$to) {
            flash('error', 'Bitte Empfänger, Betreff und Text angeben.');
            redirect(url('messages', ['new' => 1]));
        }
        $tid = start_thread($subject, $body, $to, (int) $me['id']);
        audit('messages.send', 'thread', $tid, $subject . ' an ' . count($to) . ' Empfänger');
        foreach ($to as $uid) {
            notify_user((int) $uid, 'message', 'Neue Nachricht von ' . user_display($me) . ': ' . $subject, url('messages', ['id' => $tid]));
        }
        redirect(url('messages', ['id' => $tid]));
    }
    $member = thread_member((int) post('thread_id'), (int) $me['id']);
    if (!$member) {
        forbidden();
    }
    if ($action === 'reply') {
        $body = mb_substr(trim((string) post('body')), 0, 10000);
        if ($body !== '') {
            $mid = insert('messages', ['thread_id' => $member['thread_id'], 'sender_id' => $me['id'], 'body' => $body, 'created_at' => now()]);
            q('UPDATE message_threads SET last_at = ? WHERE id = ?', [now(), $member['thread_id']]);
            q('UPDATE thread_members SET archived = 0 WHERE thread_id = ?', [$member['thread_id']]);
            update('thread_members', (int) $member['id'], ['last_read_id' => $mid]);
            $subject = (string) scalar('SELECT subject FROM message_threads WHERE id = ?', [$member['thread_id']]);
            foreach (all('SELECT user_id FROM thread_members WHERE thread_id = ? AND user_id <> ?', [$member['thread_id'], $me['id']]) as $m) {
                notify_user((int) $m['user_id'], 'message', 'Antwort von ' . user_display($me) . ': ' . $subject, url('messages', ['id' => $member['thread_id']]));
            }
        }
        redirect(url('messages', ['id' => $member['thread_id']]) . '#last');
    }
    if ($action === 'archive') {
        update('thread_members', (int) $member['id'], ['archived' => 1]);
        redirect(url('messages'));
    }
}

if ($id) {
    $member = thread_member($id, (int) $me['id']);
    $thread = $member ? one('SELECT * FROM message_threads WHERE id = ?', [$id]) : null;
    if (!$thread) {
        not_found('Unterhaltung nicht gefunden.');
    }
    $msgs = all('SELECT m.*, u.name, u.title FROM messages m LEFT JOIN users u ON u.id = m.sender_id WHERE m.thread_id = ? ORDER BY m.id', [$id]);
    $members = all('SELECT u.* FROM thread_members tm JOIN users u ON u.id = tm.user_id WHERE tm.thread_id = ? ORDER BY u.name', [$id]);
    if ($msgs) {
        update('thread_members', (int) $member['id'], ['last_read_id' => (int) end($msgs)['id']]);
    }
    ob_start(); ?>
    <div class="page-head"><div><h1><?= e($thread['subject']) ?></h1><p class="muted"><?= e(implode(', ', array_map('user_display', $members))) ?></p></div>
        <div class="btn-row"><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="archive"><input type="hidden" name="thread_id" value="<?= $id ?>"><button class="btn">Archivieren</button></form><a class="btn" href="<?= e(url('messages')) ?>">Posteingang</a></div></div>
    <div class="chat">
        <?php foreach ($msgs as $i => $m): $mine = (int) $m['sender_id'] === (int) $me['id']; ?>
            <div class="msg<?= $mine ? ' mine' : '' ?>"<?= $i === count($msgs) - 1 ? ' id="last"' : '' ?>>
                <div class="msg-head"><strong><?= e(trim(($m['title'] ?? '') . ' ' . ($m['name'] ?? 'System'))) ?></strong> <small><?= e(fmt_datetime($m['created_at'])) ?></small></div>
                <div class="msg-body"><?= nl($m['body']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="post" class="card form-grid">
        <?= csrf_field() ?><input type="hidden" name="action" value="reply"><input type="hidden" name="thread_id" value="<?= $id ?>">
        <div class="field w-full"><textarea name="body" rows="3" required placeholder="Antwort schreiben …"></textarea></div>
        <div class="form-actions"><button class="btn primary">Senden</button></div>
    </form>
    <?php
    $content = ob_get_clean();
    $title = $thread['subject'];
    require SAMS_ROOT . '/app/layout.php';
    return;
}

$threads = all('SELECT t.*, tm.archived,
        (SELECT COUNT(*) FROM messages m WHERE m.thread_id = t.id AND (m.sender_id IS NULL OR m.sender_id <> ?) AND m.id > tm.last_read_id) AS unread,
        (SELECT body FROM messages m WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS preview,
        (SELECT u.name FROM messages m LEFT JOIN users u ON u.id = m.sender_id WHERE m.thread_id = t.id ORDER BY m.id DESC LIMIT 1) AS last_sender
    FROM thread_members tm JOIN message_threads t ON t.id = tm.thread_id WHERE tm.user_id = ? AND tm.archived = ? ORDER BY t.last_at DESC LIMIT 200',
    [$me['id'], $me['id'], isset($_GET['archive']) ? 1 : 0]);
$compose = isset($_GET['new']);
$preTo = (int) ($_GET['to'] ?? 0);

ob_start(); ?>
<div class="page-head">
    <div><h1>Nachrichten</h1><p class="muted">Interne Nachrichten an Kolleg/innen oder ganze Abteilungen.</p></div>
    <div class="btn-row"><a class="btn" href="<?= e(url('messages', isset($_GET['archive']) ? [] : ['archive' => 1])) ?>"><?= isset($_GET['archive']) ? 'Posteingang' : 'Archiv' ?></a><a class="btn primary" href="<?= e(url('messages', ['new' => 1])) ?>">+ Neue Nachricht</a></div>
</div>
<?php if ($compose): ?>
<form method="post" class="card form-grid">
    <h2>Neue Nachricht</h2>
    <?= csrf_field() ?><input type="hidden" name="action" value="send">
    <div class="field w-half"><label>An (Personen)</label><select name="to[]" multiple size="6"><?php foreach ($staff as $u): ?><option value="<?= (int) $u['id'] ?>"<?= $preTo === (int) $u['id'] ? ' selected' : '' ?>><?= e(user_display($u)) ?> · <?= e(user_rank_name($u)) ?><?= (int) $u['faction_id'] !== fid() ? ' · ' . e(faction_by_id((int) $u['faction_id'])['short'] ?? '') : '' ?></option><?php endforeach; ?></select><div class="hint">Mehrfachauswahl mit Strg / ⌘</div></div>
    <div class="field w-half"><span class="label">… oder ganze Abteilungen</span><div class="choices vertical"><?php foreach ($departments as $d): ?><label class="choice"><input type="checkbox" name="to_dep[]" value="<?= (int) $d['id'] ?>"> <?= e($d['name']) ?></label><?php endforeach; ?></div></div>
    <div class="field w-full"><label>Betreff</label><input name="subject" required maxlength="190"></div>
    <div class="field w-full"><label>Nachricht</label><textarea name="body" rows="6" required></textarea></div>
    <div class="form-actions"><button class="btn primary">Senden</button><a class="btn" href="<?= e(url('messages')) ?>">Abbrechen</a></div>
</form>
<?php endif; ?>
<div class="card">
    <table class="list">
        <tbody>
        <?php foreach ($threads as $t): ?>
            <tr class="<?= $t['unread'] ? 'unread' : '' ?>" onclick="location.href='<?= e(url('messages', ['id' => $t['id']])) ?>'">
                <td><strong><?= e($t['subject']) ?></strong><?= $t['unread'] ? ' <b class="nav-count">' . (int) $t['unread'] . '</b>' : '' ?><br>
                    <small class="muted"><?= e($t['last_sender'] ?? '') ?>: <?= e(mb_strimwidth((string) $t['preview'], 0, 120, '…')) ?></small></td>
                <td class="right small"><?= e(fmt_datetime($t['last_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$threads): ?><tr><td class="muted">Keine Nachrichten.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Nachrichten';
require SAMS_ROOT . '/app/layout.php';
