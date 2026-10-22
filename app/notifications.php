<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Persönliche Benachrichtigungen (Glocke in der Kopfleiste). Auf Wunsch
 * zusätzlich als Discord-Direktnachricht (Profil → Benachrichtigungen).
 */

const NOTIFY_KINDS = [
    'draft'     => 'Entwürfe und Übergaben',
    'approval'  => 'Freigaben',
    'message'   => 'Nachrichten',
    'board'     => 'Schwarzes Brett',
    'share'     => 'Freigegebene Dokumente',
    'personnel' => 'Personalakte',
    'roster'    => 'Dienstplan',
    'stock'     => 'Medikamentenbestand',
    'wanted'    => 'Fahndung',
    'request'   => 'Behördenanfragen',
    'integration' => 'Meldungen aus angebundenen Skripten',
];

/** Benachrichtigung an ein Konto (nicht an sich selbst). */
function notify_user(int $userId, string $kind, string $title, string $link = '', string $body = ''): void
{
    $me = current_user();
    if ($me && (int) $me['id'] === $userId) {
        return;
    }
    try {
        insert('notifications', ['user_id' => $userId, 'kind' => $kind, 'title' => mb_substr($title, 0, 190), 'body' => mb_substr($body, 0, 1000),
            'link' => $link, 'created_at' => now()]);
        $u = one('SELECT discord_id, notify_discord FROM users WHERE id = ? AND active = 1', [$userId]);
        if ($u && (int) $u['notify_discord'] && $u['discord_id'] !== '') {
            notify_dm((string) $u['discord_id'], '🔔 ' . $title . ($link !== '' ? "\n" . base_url() . '/' . ltrim($link, '/') : ''));
        }
    } catch (PDOException $e) {
        // vor der Migration
    }
}

/** An alle aktiven Konten mit einem Recht (optional zusätzliche Bedingung). */
function notify_users_with(string $permission, string $kind, string $title, string $link = '', ?callable $filter = null): void
{
    foreach (all('SELECT * FROM users WHERE active = 1') as $u) {
        if (in_array($permission, user_permissions($u), true) && (!$filter || $filter($u))) {
            notify_user((int) $u['id'], $kind, $title, $link);
        }
    }
}

function notifications_unread(): int
{
    $me = current_user();
    if (!$me) {
        return 0;
    }
    try {
        return (int) scalar('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL', [$me['id']]);
    } catch (PDOException $e) {
        return 0;
    }
}

function notifications_recent(int $limit = 8): array
{
    $me = current_user();
    try {
        return $me ? all('SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ' . $limit, [$me['id']]) : [];
    } catch (PDOException $e) {
        return [];
    }
}

function time_ago(string $dt): string
{
    $s = time() - strtotime($dt);
    return match (true) {
        $s < 60 => 'gerade eben',
        $s < 3600 => 'vor ' . intdiv($s, 60) . ' min',
        $s < 86400 => 'vor ' . intdiv($s, 3600) . ' h',
        default => fmt_datetime($dt),
    };
}
