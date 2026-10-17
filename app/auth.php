<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Rollen in aufsteigender Berechtigung.
 *  trainee – Auszubildende: Akten lesen, Einträge schreiben, keine Dokumente
 *  medic   – Rettungsdienst: Einsatzdokumente (Protokoll, Revers, Rechnung …)
 *  doctor  – Ärztlicher Dienst: alle ärztlichen Bescheinigungen
 *  admin   – Leitung: zusätzlich Benutzer, Einstellungen, Protokoll
 */
const ROLES = [
    'trainee' => 'Auszubildende/r',
    'medic'   => 'Rettungsdienst',
    'doctor'  => 'Ärztlicher Dienst',
    'admin'   => 'Leitung / Admin',
];

function role_level(string $role): int
{
    $idx = array_search($role, array_keys(ROLES), true);
    return $idx === false ? -1 : $idx;
}

function current_user(): ?array
{
    static $user = false;
    if ($user !== false && ($user === null || ($user['id'] ?? null) === ($_SESSION['uid'] ?? null))) {
        return $user;
    }
    $uid = $_SESSION['uid'] ?? null;
    $user = $uid ? one('SELECT * FROM users WHERE id = ? AND active = 1', [$uid]) : null;
    return $user;
}

function can(string $minRole): bool
{
    $u = current_user();
    return $u !== null && role_level($u['role']) >= role_level($minRole);
}

function require_login(): array
{
    $u = current_user();
    if (!$u) {
        redirect(url('login', ['next' => $_SERVER['REQUEST_URI'] ?? '']));
    }
    return $u;
}

function require_role(string $minRole): void
{
    require_login();
    if (!can($minRole)) {
        forbidden('Für diese Funktion ist mindestens die Rolle „' . ROLES[$minRole] . '“ erforderlich.');
    }
}

function attempt_login(string $username, string $password): bool
{
    $u = one('SELECT * FROM users WHERE username = ? AND active = 1', [$username]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        usleep(300000);
        return false;
    }
    if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
        update('users', (int) $u['id'], ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    }
    session_regenerate_id(true);
    $_SESSION['uid'] = (int) $u['id'];
    update('users', (int) $u['id'], ['last_login' => now()]);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
