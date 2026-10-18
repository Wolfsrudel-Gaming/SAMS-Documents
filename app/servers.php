<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Mehrere FiveM-Server (Staaten) an einem Portal. Jeder Server meldet sich mit seinem eigenen API-Schlüssel;
 * Drucker, Tablet-Anmeldungen und Spiel-Rechnungen gehören zu genau einem Server. Spieleinstellungen
 * (Framework, Rechnungssystem, Befehle, Sky, Leitstelle) gelten global und lassen sich je Server überschreiben.
 * Der erste Server (Hauptserver) nimmt API_KEY aus der .env, solange im Portal kein eigener Schlüssel erzeugt wurde.
 */

/** Je Server überschreibbare Spieleinstellungen: Schlüssel => [Bezeichnung, Optionen oder null für Freitext]. */
const GAME_SERVER_OVERRIDES = [
    'ingame_framework'        => ['Framework', ['esx' => 'ESX', 'none' => 'ohne Framework']],
    'ingame_billing_provider' => ['Rechnungssystem', ['esx_billing' => 'esx_billing', 'sky' => 'Sky Billing']],
    'ingame_billing_table'    => ['Tabelle von esx_billing', null],
    'ingame_bill_interval'    => ['Rechnungen abgleichen alle … Sekunden', null],
    'ingame_cmd_tablet'       => ['Befehl Tablet', null],
    'duty_command'            => ['Befehl Dienst', null],
    'ingame_cmd_akte'         => ['Befehl Akte', null],
    'ingame_cmd_plate'        => ['Befehl Kennzeichenabfrage', null],
    'ingame_akte_distance'    => ['Reichweite Aktenabruf (Meter)', null],
    'ingame_sky'              => ['Sky Ambulance', ['1' => 'an', '0' => 'aus']],
    'ingame_sky_death'        => ['Sky: Tod/Wiederbelebung', ['1' => 'an', '0' => 'aus']],
    'ingame_sky_meds'         => ['Sky: Medikamentengabe', ['1' => 'an', '0' => 'aus']],
    'ingame_tetra'            => ['Leitstelle tetrascript', ['1' => 'an', '0' => 'aus']],
    'ingame_tetra_poll'       => ['Leitstelle abgleichen alle … Sekunden', null],
];

function game_servers(bool $activeOnly = false): array
{
    static $cache = [];
    try {
        return $cache[(int) $activeOnly] ??= all('SELECT * FROM game_servers' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY id');
    } catch (PDOException $e) {
        return []; // vor der Migration
    }
}

function game_server(int $id): ?array
{
    foreach (game_servers() as $s) {
        if ((int) $s['id'] === $id) {
            return $s;
        }
    }
    return null;
}

/** Hauptserver (ältester Eintrag): Ziel für alles ohne Zuordnung. */
function game_server_default_id(): int
{
    return (int) (game_servers()[0]['id'] ?? 0);
}

/** Mehr als ein aktiver Server? Dann zeigt die Oberfläche den Staat an. */
function multi_server(): bool
{
    return count(game_servers(true)) > 1;
}

/** Wirksamer Schlüssel eines Servers (Hauptserver ohne eigenen Schlüssel: API_KEY aus der .env). */
function game_server_key(array $srv): string
{
    if ((string) $srv['api_key'] !== '') {
        return (string) $srv['api_key'];
    }
    return (int) $srv['id'] === game_server_default_id() ? (string) cfg('api_key', '') : '';
}

/** Server zum gesendeten API-Schlüssel (nur aktive). */
function game_server_by_key(string $sent): ?array
{
    if ($sent === '') {
        return null;
    }
    foreach (game_servers(true) as $srv) {
        $key = game_server_key($srv);
        if ($key !== '' && hash_equals($key, $sent)) {
            return $srv;
        }
    }
    return null;
}

/** Gibt es überhaupt einen gültigen Schlüssel? (sonst ist die API aus) */
function game_api_enabled(): bool
{
    foreach (game_servers(true) as $srv) {
        if (game_server_key($srv) !== '') {
            return true;
        }
    }
    return !game_servers() && (string) cfg('api_key', '') !== '';
}

function game_server_new_key(): string
{
    return 'sams_' . bin2hex(random_bytes(24));
}

/** Server der aktuellen API-Anfrage (oder null im Browser). */
function current_game_server(): ?array
{
    return $GLOBALS['sams_game_server'] ?? null;
}

function set_current_game_server(?array $srv): void
{
    $GLOBALS['sams_game_server'] = $srv;
    if ($srv && (!$srv['last_seen_at'] || strtotime((string) $srv['last_seen_at']) < time() - 60)) {
        q('UPDATE game_servers SET last_seen_at = ?, last_ip = ? WHERE id = ?', [now(), mb_substr(client_ip(), 0, 64), $srv['id']]);
    }
}

function game_server_id(): int
{
    return (int) (current_game_server()['id'] ?? 0);
}

/** Spieleinstellung: Wert des anfragenden Servers, sonst global. */
function game_setting(string $key, string $default = ''): string
{
    $cfg = json_decode((string) (current_game_server()['config'] ?? ''), true);
    return is_array($cfg) && isset($cfg[$key]) && $cfg[$key] !== '' ? (string) $cfg[$key] : (string) setting($key, $default);
}

/** Server, auf dem eine Person zuletzt mit dem Tablet angemeldet war (sonst Hauptserver). */
function game_server_for_user(int $userId): int
{
    try {
        $id = (int) scalar('SELECT server_id FROM tablet_tokens WHERE user_id = ? AND revoked = 0 AND server_id IS NOT NULL AND expires_at > ? ORDER BY last_used_at DESC, id DESC LIMIT 1', [$userId, now()]);
    } catch (PDOException $e) {
        $id = 0;
    }
    return $id && game_server($id) ? $id : game_server_default_id();
}

/** Name (Staat) eines Servers. */
function game_server_label(?int $id): string
{
    $s = $id ? game_server($id) : null;
    return $s ? (string) $s['name'] : '';
}

/** Zeile für die server.cfg des Servers. */
function game_server_cfg(array $srv): string
{
    return "set sams_url \"" . base_url() . "\"\nset sams_api_key \"" . game_server_key($srv) . "\"";
}
