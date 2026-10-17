<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Dienstplan: Schichten mit Mindest-/Höchstbesetzung. Personal trägt sich ein
 * oder wird eingeteilt; kurz vor Beginn kann man nur noch zum Tausch anbieten.
 * Die Anwesenheit wird mit der gestempelten Dienstzeit abgeglichen.
 */

const ROSTER_STATUS = ['assigned' => 'eingeteilt', 'offered' => 'zum Tausch angeboten'];

/** Stunden vor Schichtbeginn, ab denen Austragen nur noch per Tausch geht. */
function roster_lock_hours(): int
{
    return max(0, min(168, (int) setting('roster_lock_hours', '12')));
}

/** Schichten eines Zeitraums inkl. eingeteilter Personen. */
function roster_shifts(string $from, string $to): array
{
    $shifts = all('SELECT s.*, d.name AS department FROM roster_shifts s LEFT JOIN departments d ON d.id = s.department_id
        WHERE s.starts_at < ? AND s.ends_at > ? ORDER BY s.starts_at, s.title', [$to, $from]);
    if (!$shifts) {
        return [];
    }
    $ids = implode(',', array_map(fn($s) => (int) $s['id'], $shifts));
    $slots = [];
    foreach (all("SELECT r.*, u.name, u.title AS user_title, u.badge_number, u.rank_id FROM roster_slots r JOIN users u ON u.id = r.user_id
        WHERE r.shift_id IN ($ids) ORDER BY r.created_at") as $r) {
        $slots[(int) $r['shift_id']][] = $r;
    }
    foreach ($shifts as &$s) {
        $s['slots'] = $slots[(int) $s['id']] ?? [];
    }
    return $shifts;
}

function roster_shift(int $id): ?array
{
    $s = one('SELECT * FROM roster_shifts WHERE id = ?', [$id]);
    if ($s) {
        $s['slots'] = all('SELECT * FROM roster_slots WHERE shift_id = ?', [$id]);
    }
    return $s;
}

function roster_label(array $s): string
{
    return trim(($s['title'] ?: 'Schicht') . ' ' . fmt_date($s['starts_at'], 'd.m. H:i') . '–' . fmt_date($s['ends_at'], 'H:i'));
}

/** Freie Plätze (null = unbegrenzt). */
function roster_free(array $s): ?int
{
    return (int) $s['max_staff'] > 0 ? max(0, (int) $s['max_staff'] - count($s['slots'])) : null;
}

function roster_locked(array $s): bool
{
    return strtotime($s['starts_at']) - time() < roster_lock_hours() * 3600;
}

/** Gestempelte Sekunden einer Person innerhalb der Schicht. */
function roster_attended(array $s, int $userId): int
{
    $sum = 0;
    foreach (all('SELECT * FROM duty_sessions WHERE user_id = ? AND started_at < ? AND (ended_at IS NULL OR ended_at > ?)', [$userId, $s['ends_at'], $s['starts_at']]) as $d) {
        $sum += duty_seconds($d, $s['starts_at'], $s['ends_at']);
    }
    return $sum;
}

/** Ob die Person im Zeitraum schon in einer anderen Schicht steht. */
function roster_overlap(int $userId, array $s): ?array
{
    return one('SELECT s.* FROM roster_slots r JOIN roster_shifts s ON s.id = r.shift_id WHERE r.user_id = ? AND s.id <> ? AND s.starts_at < ? AND s.ends_at > ?',
        [$userId, $s['id'], $s['ends_at'], $s['starts_at']]);
}

/** Eintragen (selbst oder durch die Leitung). Gibt eine Fehlermeldung oder null zurück. */
function roster_assign(array $s, int $userId, bool $byManager = false): ?string
{
    if (strtotime($s['ends_at']) < time()) {
        return 'Die Schicht ist bereits vorbei.';
    }
    if (in_array($userId, array_map('intval', array_column($s['slots'], 'user_id')), true)) {
        return 'Bereits eingeteilt.';
    }
    if (!$byManager && roster_free($s) === 0) {
        return 'Die Schicht ist voll besetzt.';
    }
    if ($o = roster_overlap($userId, $s)) {
        return 'Überschneidung mit ' . roster_label($o) . '.';
    }
    insert('roster_slots', ['shift_id' => $s['id'], 'user_id' => $userId, 'status' => 'assigned', 'created_at' => now()]);
    return null;
}

/** Erinnerungen und Unterbesetzungs-Warnungen (läuft im Cron). */
function roster_cron(): int
{
    $n = 0;
    $soon = date('Y-m-d H:i:s', time() + 3600);
    foreach (all("SELECT r.user_id, s.* FROM roster_slots r JOIN roster_shifts s ON s.id = r.shift_id
        WHERE r.reminded = 0 AND s.starts_at > ? AND s.starts_at <= ?", [now(), $soon]) as $r) {
        q('UPDATE roster_slots SET reminded = 1 WHERE shift_id = ? AND user_id = ?', [$r['id'], $r['user_id']]);
        notify_user((int) $r['user_id'], 'roster', 'Deine Schicht beginnt um ' . fmt_date($r['starts_at'], 'H:i') . ' Uhr', url('roster', ['week' => substr(week_start($r['starts_at']), 0, 10)]), roster_label($r));
        $n++;
    }
    // 24 Stunden vorher: Leitung über Unterbesetzung informieren (einmal pro Schicht)
    $day = date('Y-m-d H:i:s', time() + 86400);
    foreach (all('SELECT s.*, (SELECT COUNT(*) FROM roster_slots r WHERE r.shift_id = s.id) AS staff FROM roster_shifts s
        WHERE s.staff_warned = 0 AND s.starts_at > ? AND s.starts_at <= ?', [now(), $day]) as $s) {
        q('UPDATE roster_shifts SET staff_warned = 1 WHERE id = ?', [$s['id']]);
        if ((int) $s['staff'] < (int) $s['min_staff']) {
            notify_users_with('roster.manage', 'roster', 'Unterbesetzt: ' . roster_label($s) . ' (' . $s['staff'] . '/' . $s['min_staff'] . ')',
                url('roster', ['week' => substr(week_start($s['starts_at']), 0, 10)]));
            $n++;
        }
    }
    return $n;
}

/** Die nächsten Schichten einer Person (für die Übersicht). */
function roster_upcoming(int $userId, int $limit = 3): array
{
    return all('SELECT s.*, r.status FROM roster_slots r JOIN roster_shifts s ON s.id = r.shift_id WHERE r.user_id = ? AND s.ends_at > ? ORDER BY s.starts_at LIMIT ' . $limit, [$userId, now()]);
}
