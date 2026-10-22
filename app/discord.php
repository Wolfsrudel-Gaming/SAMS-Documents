<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Discord: OAuth2-Anmeldung (Bürgerportal, Mitarbeitende), Bot-API für den
 * Rollen-Sync und Webhooks für Benachrichtigungen.
 */

function discord_configured(): bool
{
    return cfg('discord.client_id', '') !== '' && cfg('discord.client_secret', '') !== '';
}

/** HTTP-Anfrage ohne Zusatzbibliotheken. Gibt [Status, dekodierte Antwort] zurück. */
function discord_http(string $method, string $url, array $headers = [], ?string $body = null, int $timeout = 8): array
{
    $ctx = stream_context_create(['http' => [
        'method' => $method, 'header' => implode("\r\n", array_merge(['User-Agent: Wolfpack-Documents (' . SAMS_VERSION . ')'], $headers)),
        'content' => $body ?? '', 'timeout' => $timeout, 'ignore_errors' => true,
    ]]);
    $res = @file_get_contents($url, false, $ctx);
    $status = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) {
            $status = (int) $m[1];
        }
    }
    return [$status, $res === false ? null : json_decode($res, true)];
}

/** Adresse für den Discord-Login. $redirect muss in der Discord-App eingetragen sein. */
function discord_authorize_url(string $redirect, string $scope = 'identify'): string
{
    $state = bin2hex(random_bytes(16));
    $_SESSION['discord_state'] = $state;
    return 'https://discord.com/oauth2/authorize?' . http_build_query([
        'client_id' => cfg('discord.client_id'), 'redirect_uri' => $redirect, 'response_type' => 'code',
        'scope' => $scope, 'state' => $state, 'prompt' => 'none',
    ]);
}

/** Rückkehr von Discord: Code gegen Benutzerdaten tauschen. Gibt das Discord-Profil oder null zurück. */
function discord_callback(string $redirect): ?array
{
    $state = (string) ($_GET['state'] ?? '');
    if ($state === '' || !hash_equals((string) ($_SESSION['discord_state'] ?? ''), $state)) {
        return null;
    }
    unset($_SESSION['discord_state']);
    $code = (string) ($_GET['code'] ?? '');
    if ($code === '') {
        return null;
    }
    [$st, $token] = discord_http('POST', 'https://discord.com/api/oauth2/token', ['Content-Type: application/x-www-form-urlencoded'], http_build_query([
        'client_id' => cfg('discord.client_id'), 'client_secret' => cfg('discord.client_secret'),
        'grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => $redirect,
    ]));
    if ($st !== 200 || empty($token['access_token'])) {
        app_log('discord', 'Token-Abruf fehlgeschlagen: HTTP ' . $st);
        return null;
    }
    [$st, $me] = discord_http('GET', 'https://discord.com/api/users/@me', ['Authorization: Bearer ' . $token['access_token']]);
    return $st === 200 && !empty($me['id']) ? $me : null;
}

function discord_avatar_url(array $profile): string
{
    return !empty($profile['avatar']) ? 'https://cdn.discordapp.com/avatars/' . rawurlencode((string) $profile['id']) . '/' . rawurlencode((string) $profile['avatar']) . '.png?size=64' : '';
}

// ------------------------------------------------- Mitarbeitende & Rollen

function discord_staff_redirect(): string
{
    return base_url() . '/index.php?p=discord_login';
}

/** Rollen eines Mitglieds auf dem Discord-Server (null = nicht Mitglied bzw. nicht eingerichtet). */
function discord_member_roles(string $discordId): ?array
{
    $token = (string) cfg('discord.bot_token', '');
    $guild = (string) cfg('discord.guild_id', '');
    if ($token === '' || $guild === '' || !preg_match('/^\d{5,25}$/', $discordId)) {
        return null;
    }
    [$st, $m] = discord_http('GET', 'https://discord.com/api/v10/guilds/' . rawurlencode($guild) . '/members/' . $discordId, ['Authorization: Bot ' . $token], null, 5);
    return $st === 200 && isset($m['roles']) ? array_map('strval', $m['roles']) : null;
}

/** Zuordnung Discord-Rolle → Rang (Einstellung discord_role_map, JSON {rollenId: rangId}). */
function discord_role_map(): array
{
    $map = json_decode((string) setting('discord_role_map', '{}'), true);
    return is_array($map) ? array_filter(array_map('intval', $map)) : [];
}

/**
 * Rang eines Kontos an die Discord-Rollen anpassen: Es gilt der höchste
 * zugeordnete Rang. Ohne zugeordnete Rolle bleibt der Rang unverändert.
 * Gibt eine Beschreibung der Änderung oder '' zurück.
 */
function discord_sync_rank(array $u, ?array $roles = null): string
{
    if (!empty($u['is_superadmin']) || empty($u['discord_id']) || !$map = discord_role_map()) {
        return '';
    }
    $roles = $roles ?? discord_member_roles((string) $u['discord_id']);
    if ($roles === null) {
        return '';
    }
    $best = null;
    foreach ($roles as $role) {
        $rank = isset($map[$role]) ? rank_by_id($map[$role]) : null;
        if ($rank && (!$best || (int) $rank['level'] > (int) $best['level'])) {
            $best = $rank;
        }
    }
    if (!$best || (int) $best['id'] === (int) $u['rank_id']) {
        return '';
    }
    $old = rank_by_id((int) $u['rank_id']);
    update('users', (int) $u['id'], ['rank_id' => $best['id']]);
    $up = (int) $best['level'] > (int) ($old['level'] ?? 0);
    personnel_event((int) $u['id'], 'rank', ($up ? 'Befördert zu ' : 'Rangänderung: ') . $best['name'] . ' (Discord-Rolle)');
    audit_as(null, 'discord.rank_sync', 'user', (int) $u['id'], ($old['name'] ?? '–') . ' → ' . $best['name']);
    return user_display($u) . ': ' . ($old['name'] ?? '–') . ' → ' . $best['name'];
}

/** Alle verknüpften Konten abgleichen (vom Cron-Lauf stündlich aufgerufen). */
function discord_sync_all(): array
{
    $changes = [];
    foreach (all("SELECT * FROM users WHERE active = 1 AND discord_id <> '' AND is_superadmin = 0 AND " . fw()) as $u) {
        if (($c = discord_sync_rank($u)) !== '') {
            $changes[] = $c;
        }
    }
    return $changes;
}
