<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Kommunikation: Schwarzes Brett (Aushänge mit Zielgruppe und
 * Lesebestätigung) und interne Nachrichten (Unterhaltungen).
 */

/** Aushänge, die der Benutzer sehen soll (Zielgruppe, nicht abgelaufen). */
function visible_announcements(array $user, bool $includeArchived = false): array
{
    $rows = all('SELECT a.*, u.name AS author, d.name AS department, r.read_at, r.acknowledged_at FROM announcements a
        LEFT JOIN users u ON u.id = a.created_by LEFT JOIN departments d ON d.id = a.department_id
        LEFT JOIN announcement_reads r ON r.announcement_id = a.id AND r.user_id = ?
        WHERE ' . fw('a') . ' AND ' . ($includeArchived ? '1=1' : 'a.archived = 0 AND (a.expires_at IS NULL OR a.expires_at > ?)') . '
        ORDER BY a.pinned DESC, a.created_at DESC', $includeArchived ? [$user['id']] : [$user['id'], now()]);
    $deps = user_department_ids((int) $user['id']);
    $level = user_level($user);
    return array_values(array_filter($rows, fn($a) => announcement_for($a, $deps, $level) || (int) $a['created_by'] === (int) $user['id'] || can('board.manage')));
}

function announcement_for(array $a, array $deps, int $level): bool
{
    return (!$a['department_id'] || in_array((int) $a['department_id'], $deps, true)) && $level >= (int) $a['min_level'];
}

/** Ungelesene bzw. unbestätigte Aushänge (für Navigation und Übersicht). */
function board_unread_count(): int
{
    $me = current_user();
    if (!$me || !can('board.view')) {
        return 0;
    }
    $deps = user_department_ids((int) $me['id']);
    $level = user_level($me);
    return count(array_filter(visible_announcements($me), fn($a) => announcement_for($a, $deps, $level) && (!$a['read_at'] || ($a['require_ack'] && !$a['acknowledged_at']))));
}

function mark_announcement_read(int $annId, int $userId, bool $ack = false): void
{
    $row = one('SELECT * FROM announcement_reads WHERE announcement_id = ? AND user_id = ?', [$annId, $userId]);
    if (!$row) {
        insert('announcement_reads', ['announcement_id' => $annId, 'user_id' => $userId, 'read_at' => now(), 'acknowledged_at' => $ack ? now() : null]);
    } elseif ($ack && !$row['acknowledged_at']) {
        update('announcement_reads', (int) $row['id'], ['acknowledged_at' => now()]);
    }
}

/** Personen der Zielgruppe eines Aushangs (für die Übersicht der Bestätigungen). */
function announcement_audience(array $a): array
{
    $users = all('SELECT * FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name');
    return array_values(array_filter($users, fn($u) => in_array('board.view', user_permissions($u), true)
        && announcement_for($a, user_department_ids((int) $u['id']), user_level($u))));
}

// ------------------------------------------------------------- Nachrichten

function messages_unread_count(): int
{
    $me = current_user();
    if (!$me || !can('messages.use')) {
        return 0;
    }
    // Gelesen wird über die ID der letzten gelesenen Nachricht verfolgt (sekundengenaue Zeiten reichen nicht)
    return (int) scalar('SELECT COUNT(*) FROM thread_members tm WHERE tm.user_id = ? AND tm.archived = 0
        AND EXISTS (SELECT 1 FROM messages m WHERE m.thread_id = tm.thread_id AND m.id > tm.last_read_id AND (m.sender_id IS NULL OR m.sender_id <> ?))', [$me['id'], $me['id']]);
}

/** Neue Unterhaltung anlegen. $recipients: Benutzer-IDs (ohne Absender). */
function start_thread(string $subject, string $body, array $recipients, int $senderId): int
{
    $tid = insert('message_threads', ['subject' => $subject, 'created_by' => $senderId, 'created_at' => now(), 'last_at' => now()]);
    $mid = insert('messages', ['thread_id' => $tid, 'sender_id' => $senderId, 'body' => $body, 'created_at' => now()]);
    foreach (array_unique(array_merge([$senderId], $recipients)) as $uid) {
        insert('thread_members', ['thread_id' => $tid, 'user_id' => $uid, 'last_read_id' => $uid === $senderId ? $mid : 0]);
    }
    return $tid;
}

function thread_member(int $threadId, int $userId): ?array
{
    return one('SELECT * FROM thread_members WHERE thread_id = ? AND user_id = ?', [$threadId, $userId]);
}
