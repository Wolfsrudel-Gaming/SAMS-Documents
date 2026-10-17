<?php
declare(strict_types=1);

define('SAMS_ROOT', dirname(__DIR__));
define('SAMS_CLI', PHP_SAPI === 'cli');

require __DIR__ . '/config.php';

define('SAMS_VERSION', trim((string) @file_get_contents(SAMS_ROOT . '/VERSION')) ?: '0.0.0');

if (!is_configured() && !SAMS_CLI && basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'install.php') {
    header('Location: install.php');
    exit;
}

$GLOBALS['sams_config'] = build_config();

date_default_timezone_set(cfg('timezone', 'Europe/Berlin'));
mb_internal_encoding('UTF-8');

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/migrations.php';
require __DIR__ . '/system.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/documents.php';
require __DIR__ . '/render.php';

set_exception_handler('handle_exception');
set_error_handler(function (int $no, string $msg, string $file, int $line): bool {
    if (!(error_reporting() & $no)) {
        return false;
    }
    app_log('warning', "$msg in $file:$line");
    return !cfg('debug');
});

// Wartungsmodus während eines Updates
if (!SAMS_CLI && !defined('SAMS_IGNORE_MAINTENANCE') && in_maintenance()) {
    http_response_code(503);
    header('Retry-After: 30');
    require __DIR__ . '/maintenance.php';
    exit;
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $sameSite = cfg('cookie_samesite', 'Lax');
    $secure = $sameSite === 'None' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    session_name('SAMSSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => $sameSite,
    ]);
    session_start();
}

if (!SAMS_CLI) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
}
