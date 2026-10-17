<?php
declare(strict_types=1);

define('SAMS_ROOT', dirname(__DIR__));
define('SAMS_VERSION', '1.0.0');

$configFile = SAMS_ROOT . '/config.php';
if (!is_file($configFile)) {
    if (basename($_SERVER['SCRIPT_NAME'] ?? '') !== 'install.php') {
        header('Location: install.php');
        exit;
    }
    $GLOBALS['sams_config'] = require SAMS_ROOT . '/config.sample.php';
} else {
    $GLOBALS['sams_config'] = require $configFile;
}

date_default_timezone_set(cfg('timezone', 'Europe/Berlin'));
mb_internal_encoding('UTF-8');

require __DIR__ . '/helpers.php';
require __DIR__ . '/db.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/documents.php';
require __DIR__ . '/render.php';

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

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $sameSite = cfg('cookie_samesite', 'Lax');
    $secure = $sameSite === 'None' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
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

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
