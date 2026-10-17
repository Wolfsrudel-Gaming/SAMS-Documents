<?php
/**
 * Einstieg für das Ingame-Tablet: tauscht einen Einmal-Code (von api.php → tablet_launch)
 * gegen eine Browsersitzung im Websystem.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
start_session();

$code = (string) ($_GET['c'] ?? '');
$row = strlen($code) >= 32 ? one('SELECT * FROM tablet_tokens WHERE launch_hash = ? AND revoked = 0', [hash('sha256', $code)]) : null;
if (!$row || !$row['launch_expires'] || $row['launch_expires'] < now() || $row['expires_at'] < now()) {
    http_response_code(403);
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;padding:2rem">Der Tablet-Link ist abgelaufen. Bitte das Tablet schließen und erneut öffnen.</body>');
}
update('tablet_tokens', (int) $row['id'], ['launch_hash' => '', 'launch_expires' => null]);
$u = one('SELECT * FROM users WHERE id = ? AND active = 1', [$row['user_id']]);
if (!$u || (int) $u['session_version'] !== (int) $row['session_version']) {
    http_response_code(403);
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;padding:2rem">Die Anmeldung ist nicht mehr gültig. Bitte im Tablet erneut anmelden.</body>');
}
complete_login($u);
$_SESSION['tablet'] = (int) $row['id'];
$next = (string) ($_GET['next'] ?? '');
redirect(preg_match('#^index\.php\?p=[a-z_]+#', $next) ? $next : 'index.php?p=dashboard');
