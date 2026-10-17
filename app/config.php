<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Konfiguration aus Umgebungsvariablen bzw. der Datei .env laden.
 * Echte Umgebungsvariablen (z. B. aus Docker) haben Vorrang vor .env.
 */
function load_env(string $file): array
{
    $vars = [];
    if (!is_file($file)) {
        return $vars;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[-1] === $value[0]) {
            $value = substr($value, 1, -1);
        }
        $vars[$key] = $value;
    }
    return $vars;
}

function env(string $key, $default = null)
{
    static $file = null;
    if ($file === null) {
        $file = load_env(SAMS_ROOT . '/.env');
    }
    $value = getenv($key);
    if ($value === false) {
        $value = $file[$key] ?? null;
    }
    if ($value === null || $value === '') {
        return $default;
    }
    return match (strtolower((string) $value)) {
        'true' => true,
        'false' => false,
        default => $value,
    };
}

function is_configured(): bool
{
    return is_file(SAMS_ROOT . '/.env') || getenv('DB_DRIVER') !== false || is_file(SAMS_ROOT . '/config.php');
}

function build_config(): array
{
    // Altinstallationen mit config.php weiterhin unterstützen
    if (is_file(SAMS_ROOT . '/config.php') && !is_file(SAMS_ROOT . '/.env')) {
        return require SAMS_ROOT . '/config.php';
    }
    $sqlite = (string) env('DB_SQLITE_PATH', 'storage/sams.sqlite');
    return [
        'base_url' => (string) env('APP_URL', ''),
        'timezone' => (string) env('APP_TIMEZONE', 'Europe/Berlin'),
        'debug'    => (bool) env('APP_DEBUG', false),
        'db' => [
            'driver'   => env('DB_DRIVER', 'mysql') === 'sqlite' ? 'sqlite' : 'mysql',
            'sqlite'   => str_starts_with($sqlite, '/') ? $sqlite : SAMS_ROOT . '/' . $sqlite,
            'host'     => (string) env('DB_HOST', '127.0.0.1'),
            'port'     => (int) env('DB_PORT', 3306),
            'database' => (string) env('DB_DATABASE', 'sams'),
            'username' => (string) env('DB_USERNAME', 'sams'),
            'password' => (string) env('DB_PASSWORD', ''),
            'charset'  => 'utf8mb4',
        ],
        'cookie_samesite' => (string) env('SESSION_SAMESITE', 'auto'),
        'api_key'  => (string) env('API_KEY', ''),
        'update' => [
            'branch'          => (string) env('UPDATE_BRANCH', 'main'),
            'remote'          => (string) env('UPDATE_REMOTE', 'origin'),
            'webhook_secret'  => (string) env('UPDATE_WEBHOOK_SECRET', ''),
        ],
        'backup_keep_days' => (int) env('BACKUP_KEEP_DAYS', 14),
        'discord' => [
            'client_id'     => (string) env('DISCORD_CLIENT_ID', ''),
            'client_secret' => (string) env('DISCORD_CLIENT_SECRET', ''),
            'bot_token'     => (string) env('DISCORD_BOT_TOKEN', ''),
            'guild_id'      => (string) env('DISCORD_GUILD_ID', ''),
        ],
        // Nur zum Testen ohne Discord-App: Anmeldung im Bürgerportal mit frei eingegebener ID
        'portal_dev_login' => (bool) env('PORTAL_DEV_LOGIN', false),
        'frame_ancestors' => (string) env('FRAME_ANCESTORS', ''),
    ];
}

function cfg(string $key, $default = null)
{
    $value = $GLOBALS['sams_config'];
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

/** Pfad im Speicherordner. Der übergeordnete Ordner (bzw. mit $isDir der Ordner selbst) wird bei Bedarf angelegt. */
function storage_path(string $sub = '', bool $isDir = false): string
{
    // STORAGE_PATH (optional, z. B. für die Tests) – sonst storage/ im Projektordner
    $base = rtrim((string) (getenv('STORAGE_PATH') ?: SAMS_ROOT . '/storage'), '/');
    $path = $base . ($sub !== '' ? '/' . ltrim($sub, '/') : '');
    $dir = $isDir || $sub === '' ? $path : dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    return $path;
}
