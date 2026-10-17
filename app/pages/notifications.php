<?php
defined('SAMS_ROOT') || exit;
/** Benachrichtigungen: öffnen (als gelesen markieren), Liste, Zähler für die Kopfleiste. */
$me = current_user();
if (isset($_GET['count'])) {
    header('Content-Type: application/json');
    header('Cache-Control: no-store');
    $latest = notifications_recent(1)[0] ?? null;
    exit(json_encode(['unread' => notifications_unread(), 'latest' => $latest && !$latest['read_at'] ? ['id' => (int) $latest['id'], 'title' => $latest['title']] : null]));
}
if (isset($_GET['open'])) {
    $n = one('SELECT * FROM notifications WHERE id = ? AND user_id = ?', [(int) $_GET['open'], $me['id']]);
    if ($n) {
        q('UPDATE notifications SET read_at = ? WHERE id = ? AND read_at IS NULL', [now(), $n['id']]);
        // Nur interne Ziele zulassen
        redirect($n['link'] !== '' && str_starts_with($n['link'], 'index.php?') ? $n['link'] : url('notifications'));
    }
    redirect(url('notifications'));
}
if (is_post()) {
    csrf_check();
    if (post('action') === 'read_all') {
        q('UPDATE notifications SET read_at = ? WHERE user_id = ? AND read_at IS NULL', [now(), $me['id']]);
    } elseif (post('action') === 'discord') {
        update('users', (int) $me['id'], ['notify_discord' => post('notify_discord') ? 1 : 0]);
        flash('success', 'Einstellung gespeichert.');
    }
    redirect(url('notifications'));
}
$rows = all('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT 200', [$me['id']]);

ob_start(); ?>
<div class="page-head">
    <div><h1>Benachrichtigungen</h1><p class="muted"><?= notifications_unread() ?> ungelesen</p></div>
    <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read_all"><button class="btn">Alle als gelesen markieren</button></form>
</div>
<div class="grid-side wide-side reverse">
    <div class="card">
        <ul class="notif-list">
            <?php foreach ($rows as $n): ?>
                <li class="<?= $n['read_at'] ? '' : 'unread' ?>"><a href="<?= e(url('notifications', ['open' => $n['id']])) ?>">
                    <span class="badge"><?= e(NOTIFY_KINDS[$n['kind']] ?? $n['kind']) ?></span> <strong><?= e($n['title']) ?></strong>
                    <?php if ($n['body'] !== ''): ?><span class="small"><?= e($n['body']) ?></span><?php endif; ?>
                    <small class="muted"><?= e(time_ago($n['created_at'])) ?></small></a></li>
            <?php endforeach; ?>
            <?php if (!$rows): ?><li class="muted">Keine Benachrichtigungen.</li><?php endif; ?>
        </ul>
    </div>
    <form method="post" class="card">
        <h2>Discord</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="discord">
        <label class="choice single"><input type="checkbox" name="notify_discord" value="1"<?= checked(!empty($me['notify_discord'])) ?><?= $me['discord_id'] && cfg('discord.bot_token', '') !== '' ? '' : ' disabled' ?>> Benachrichtigungen zusätzlich als Discord-Direktnachricht</label>
        <p class="hint"><?= $me['discord_id'] ? (cfg('discord.bot_token', '') !== '' ? '' : 'Der Discord-Bot ist noch nicht eingerichtet.') : 'Dafür zuerst im Profil dein Discord-Konto verknüpfen.' ?></p>
        <div class="form-actions"><button class="btn small">Speichern</button></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = 'Benachrichtigungen';
require SAMS_ROOT . '/app/layout.php';
