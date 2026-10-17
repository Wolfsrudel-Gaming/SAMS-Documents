<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Anmeldung, Sitzungen, Login-Schutz und Pflichtschritte
 * (Startpasswort ändern, 2FA einrichten).
 */

const LOGIN_MAX_PER_USER = 5;    // Fehlversuche pro Benutzername …
const LOGIN_MAX_PER_IP = 20;     // … bzw. pro IP-Adresse
const LOGIN_WINDOW_MIN = 15;     // innerhalb von Minuten

function current_user(bool $refresh = false): ?array
{
    static $user = false;
    if ($refresh) {
        $user = false;
    }
    if ($user !== false) {
        return $user;
    }
    $uid = $_SESSION['uid'] ?? null;
    $user = null;
    if ($uid) {
        $row = one('SELECT * FROM users WHERE id = ? AND active = 1', [$uid]);
        // "Alle Sitzungen beenden" und Passwortwechsel erhöhen session_version
        $factionOff = $row && empty($row['is_superadmin']) && empty(faction_by_id((int) ($row['faction_id'] ?? 1))['active']);
        if ($factionOff) {
            flash('error', 'Deine Fraktion ist derzeit deaktiviert.');
            unset($_SESSION['uid'], $_SESSION['sv']);
        } elseif ($row && (int) $row['session_version'] === (int) ($_SESSION['sv'] ?? 0)) {
            $user = $row;
        } else {
            unset($_SESSION['uid'], $_SESSION['sv']);
        }
    }
    // Optionale Abmeldung bei Inaktivität (Einstellung session_idle_minutes, 0 = aus)
    if ($user && PHP_SAPI !== 'cli') {
        $idle = idle_minutes();
        if ($idle > 0 && isset($_SESSION['last_activity']) && time() - (int) $_SESSION['last_activity'] > $idle * 60) {
            unset($_SESSION['uid'], $_SESSION['sv'], $_SESSION['last_activity']);
            flash('warn', "Du wurdest nach $idle Minuten ohne Aktivität automatisch abgemeldet.");
            $user = null;
        } else {
            $_SESSION['last_activity'] = time();
        }
    }
    faction_context_for_user($user);
    return $user;
}

/**
 * Einfache Ratenbegrenzung pro IP für öffentliche Seiten. Gibt false zurück,
 * wenn das Limit erreicht ist (dann 429 senden). Großzügig gewählt, damit
 * normale Nutzung im Browser nie betroffen ist.
 */
function rate_limit(string $name, int $max, int $seconds = 60): bool
{
    try {
        $bucket = $name . ':' . client_ip();
        $since = date('Y-m-d H:i:s', time() - $seconds);
        if ((int) scalar('SELECT COUNT(*) FROM rate_hits WHERE bucket = ? AND created_at >= ?', [$bucket, $since]) >= $max) {
            return false;
        }
        insert('rate_hits', ['bucket' => $bucket, 'created_at' => now()]);
        if (random_int(1, 50) === 1) {
            q('DELETE FROM rate_hits WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 3600)]);
        }
    } catch (PDOException $e) {
        // vor der Migration: nicht begrenzen
    }
    return true;
}

function rate_limited_exit(): void
{
    http_response_code(429);
    header('Retry-After: 60');
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;padding:2rem">Zu viele Anfragen in kurzer Zeit. Bitte eine Minute warten.</body>');
}

function idle_minutes(): int
{
    return max(0, min(1440, (int) setting('session_idle_minutes', '0')));
}

/** Seiten, die trotz offener Pflichtschritte erreichbar sind. */
const AUTH_STEP_PAGES = ['password_change', 'twofa', 'logout'];

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        redirect(url('login', ['next' => $_SERVER['REQUEST_URI'] ?? '']));
    }
    $page = $_GET['p'] ?? '';
    if (!in_array($page, AUTH_STEP_PAGES, true)) {
        if (!empty($u['must_change_password'])) {
            redirect(url('password_change'));
        }
        if (empty($u['totp_enabled']) && in_array('security.2fa_required', user_permissions($u), true)) {
            redirect(url('twofa', ['required' => 1]));
        }
    }
    return $u;
}

/**
 * IP-Adresse des Besuchers. Kommt die Anfrage von einem Proxy im lokalen/privaten
 * Netz (z. B. nginx vor Docker), wird die ursprüngliche Adresse aus X-Forwarded-For genommen.
 */
function client_ip(): string
{
    $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
    $isPrivate = $ip !== '' && !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    $forwarded = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
    if ($isPrivate && $forwarded !== '') {
        // Von rechts nach links: erste Adresse, die nicht selbst ein interner Proxy ist
        foreach (array_reverse(array_map('trim', explode(',', $forwarded))) as $hop) {
            if (filter_var($hop, FILTER_VALIDATE_IP)) {
                $ip = $hop;
                if (filter_var($hop, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    break;
                }
            }
        }
    }
    return substr($ip, 0, 64);
}

/** Wartezeit in Minuten, falls zu viele Fehlversuche – sonst 0. */
function login_blocked(string $username, ?string $ip = null): int
{
    $ip ??= client_ip();
    $since = date('Y-m-d H:i:s', time() - LOGIN_WINDOW_MIN * 60);
    $lastOk = scalar('SELECT MAX(created_at) FROM login_attempts WHERE username = ? AND success = 1', [$username]);
    $from = $lastOk && $lastOk > $since ? $lastOk : $since;
    $byUser = (int) scalar('SELECT COUNT(*) FROM login_attempts WHERE username = ? AND success = 0 AND created_at > ?', [$username, $from]);
    $byIp = (int) scalar('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND success = 0 AND created_at > ?', [$ip, $since]);
    return ($byUser >= LOGIN_MAX_PER_USER || $byIp >= LOGIN_MAX_PER_IP) ? LOGIN_WINDOW_MIN : 0;
}

function record_login_attempt(string $username, bool $success, ?string $ip = null): void
{
    insert('login_attempts', ['username' => mb_substr($username, 0, 190), 'ip' => substr($ip ?? client_ip(), 0, 64), 'success' => $success ? 1 : 0, 'created_at' => now()]);
    // Alte Einträge gelegentlich aufräumen
    if (random_int(1, 50) === 1) {
        q('DELETE FROM login_attempts WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 30 * 86400)]);
    }
}

/**
 * Erster Schritt: Benutzername + Passwort.
 * Rückgabe: 'ok', '2fa' (Code nötig), 'blocked', 'inactive' oder 'invalid'.
 */
function attempt_login(string $username, string $password): string
{
    if (login_blocked($username)) {
        return 'blocked';
    }
    $u = one('SELECT * FROM users WHERE username = ?', [$username]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        record_login_attempt($username, false);
        usleep(random_int(250000, 450000));
        return 'invalid';
    }
    if (!(int) $u['active']) {
        record_login_attempt($username, false);
        return 'inactive';
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        update('users', (int) $u['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }
    if (!empty($u['totp_enabled'])) {
        $_SESSION['pending_2fa'] = ['uid' => (int) $u['id'], 'at' => time()];
        return '2fa';
    }
    record_login_attempt($username, true);
    complete_login($u);
    return 'ok';
}

/**
 * Zweiter Schritt: Code aus der Authenticator-App oder Wiederherstellungscode.
 * Rückgabe: 'ok', 'expired' oder 'invalid'.
 */
function attempt_2fa(string $code): string
{
    $pending = $_SESSION['pending_2fa'] ?? null;
    if (!$pending || time() - $pending['at'] > 300) {
        unset($_SESSION['pending_2fa']);
        return 'expired';
    }
    $u = one('SELECT * FROM users WHERE id = ? AND active = 1', [$pending['uid']]);
    if (!$u || login_blocked($u['username'])) {
        return 'invalid';
    }
    $step = totp_verify((string) $u['totp_secret'], $code, (int) $u['totp_last_step']);
    if ($step !== null) {
        update('users', (int) $u['id'], ['totp_last_step' => $step]);
    } elseif (($rest = recovery_code_use((string) $u['recovery_codes'], $code)) !== null) {
        update('users', (int) $u['id'], ['recovery_codes' => $rest]);
        audit('user.2fa_recovery_used', 'user', (int) $u['id']);
    } else {
        record_login_attempt($u['username'], false);
        return 'invalid';
    }
    unset($_SESSION['pending_2fa']);
    record_login_attempt($u['username'], true);
    complete_login($u);
    return 'ok';
}

function complete_login(array $u): void
{
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['id'];
    $_SESSION['sv'] = (int) $u['session_version'];
    $_SESSION['last_activity'] = time();
    update('users', (int) $u['id'], ['last_login' => now()]);
    current_user(true);
}

/** Alle Sitzungen eines Kontos ungültig machen (die eigene optional behalten). */
function end_sessions(int $userId, bool $keepCurrent = false): void
{
    q('UPDATE users SET session_version = session_version + 1 WHERE id = ?', [$userId]);
    if ($keepCurrent && (int) ($_SESSION['uid'] ?? 0) === $userId) {
        $_SESSION['sv'] = (int) scalar('SELECT session_version FROM users WHERE id = ?', [$userId]);
        current_user(true);
    }
}

/** Gut lesbares Einmalpasswort, z. B. "Kx7m-Pq3a-Z9tw". */
function generate_password(): string
{
    $alphabet = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $parts = [];
    for ($p = 0; $p < 3; $p++) {
        $s = '';
        for ($i = 0; $i < 4; $i++) {
            $s .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $parts[] = $s;
    }
    return implode('-', $parts);
}

function password_problems(string $password, string $username = ''): array
{
    $errors = [];
    if (strlen($password) < 10) {
        $errors[] = 'Das Passwort muss mindestens 10 Zeichen lang sein.';
    }
    if ($username !== '' && stripos($password, $username) !== false) {
        $errors[] = 'Das Passwort darf den Benutzernamen nicht enthalten.';
    }
    if (preg_match('/^(.)\1+$/', $password)) {
        $errors[] = 'Das Passwort ist zu einfach.';
    }
    return $errors;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
