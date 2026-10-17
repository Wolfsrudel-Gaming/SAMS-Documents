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
        'method' => $method, 'header' => implode("\r\n", array_merge(['User-Agent: SAMS-Dokumentensystem (' . SAMS_VERSION . ')'], $headers)),
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
