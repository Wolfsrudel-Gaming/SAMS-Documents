<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Dienstzeiten: eine offene Schicht pro Person. Gestempelt wird im Spiel
 * (On-/Off-Duty über die Resource) oder im Browser. Vergessene Schichten
 * werden nach der eingestellten Höchstdauer automatisch beendet.
 */

const DUTY_SOURCES = ['web' => 'Browser', 'game' => 'Spiel', 'manual' => 'nachgetragen', 'auto' => 'automatisch', 'api' => 'Schnittstelle'];

function duty_max_hours(): int
{
    return max(1, min(48, (int) setting('duty_max_hours', '12')));
}

/** Vergessene Schichten schließen (Ende = Beginn + Höchstdauer). */
function duty_autoclose(): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $limit = date('Y-m-d H:i:s', time() - duty_max_hours() * 3600);
    foreach (all('SELECT * FROM duty_sessions WHERE ended_at IS NULL AND started_at < ?', [$limit]) as $s) {
        update('duty_sessions', (int) $s['id'], ['ended_at' => date('Y-m-d H:i:s', strtotime($s['started_at']) + duty_max_hours() * 3600), 'end_source' => 'auto']);
    }
}

function duty_open_session(int $userId): ?array
{
    duty_autoclose();
    return one('SELECT * FROM duty_sessions WHERE user_id = ? AND ended_at IS NULL ORDER BY id DESC LIMIT 1', [$userId]);
}

/** Dienst beginnen/beenden. Gibt den neuen Status zurück (true = im Dienst). */
function duty_set(int $userId, bool $on, string $source, ?int $actingUserId = null): bool
{
    $open = duty_open_session($userId);
    if ($on && !$open) {
        insert('duty_sessions', ['user_id' => $userId, 'faction_id' => (int) (scalar('SELECT faction_id FROM users WHERE id = ?', [$userId]) ?: 1),
            'started_at' => now(), 'source' => $source, 'created_at' => now()]);
        if (setting('discord_duty_log', '0') === '1') {
            notify_webhook('personnel', '🟢 **' . user_display(one('SELECT name, title FROM users WHERE id = ?', [$userId])) . '** ist im Dienst.');
        }
        audit_as($actingUserId ?? $userId, 'duty.on', 'user', $userId, DUTY_SOURCES[$source] ?? $source);
    } elseif (!$on && $open) {
        update('duty_sessions', (int) $open['id'], ['ended_at' => now(), 'end_source' => $source]);
        if (setting('discord_duty_log', '0') === '1') {
            notify_webhook('personnel', '🔴 **' . user_display(one('SELECT name, title FROM users WHERE id = ?', [$userId])) . '** ist außer Dienst (' . duty_format(duty_seconds($open)) . ').');
        }
        audit_as($actingUserId ?? $userId, 'duty.off', 'user', $userId, (DUTY_SOURCES[$source] ?? $source) . ' · ' . duty_format(duty_seconds($open)));
    }
    if (($on && !$open) || (!$on && $open)) {
        $u = one('SELECT id, name, title, badge_number, faction_id FROM users WHERE id = ?', [$userId]);
        integration_event('duty.changed', ['user_id' => $userId, 'name' => user_display($u), 'badge' => (string) ($u['badge_number'] ?? ''), 'on_duty' => $on, 'source' => $source,
            'seconds' => $open ? duty_seconds($open) : 0] + integration_faction_part((int) ($u['faction_id'] ?? 1)), (int) ($u['faction_id'] ?? 1));
    }
    return $on;
}

function duty_seconds(array $s, ?string $from = null, ?string $to = null): int
{
    $start = strtotime($s['started_at']);
    $end = $s['ended_at'] ? strtotime($s['ended_at']) : time();
    if ($from) {
        $start = max($start, strtotime($from));
    }
    if ($to) {
        $end = min($end, strtotime($to));
    }
    return max(0, $end - $start);
}

function duty_format(int $seconds): string
{
    return sprintf('%d:%02d h', intdiv($seconds, 3600), intdiv($seconds % 3600, 60));
}

/** Summe der Dienstzeit pro Person in einem Zeitraum: [user_id => Sekunden]. */
function duty_totals(string $from, string $to, ?int $userId = null): array
{
    duty_autoclose();
    $sql = 'SELECT * FROM duty_sessions WHERE started_at < ? AND (ended_at IS NULL OR ended_at > ?) AND ' . fw();
    $params = [$to, $from];
    if ($userId) {
        $sql .= ' AND user_id = ?';
        $params[] = $userId;
    }
    $totals = [];
    foreach (all($sql, $params) as $s) {
        $totals[(int) $s['user_id']] = ($totals[(int) $s['user_id']] ?? 0) + duty_seconds($s, $from, $to);
    }
    return $totals;
}

/** Wer ist gerade im Dienst? */
function duty_on_now(): array
{
    duty_autoclose();
    return all('SELECT s.*, u.name, u.title, u.badge_number, u.rank_id, u.fms_status, u.fms_at, u.fms_opta FROM duty_sessions s JOIN users u ON u.id = s.user_id
        WHERE s.ended_at IS NULL AND ' . fw('s') . ' ORDER BY s.started_at');
}

/** Montag 00:00 der Woche, in der $date liegt. */
function week_start(string $date = 'now'): string
{
    $t = strtotime($date);
    return date('Y-m-d 00:00:00', strtotime('-' . ((int) date('N', $t) - 1) . ' days', $t));
}
