<?php
/** Statusabfrage für Monitoring (z. B. Uptime Kuma): HTTP 200 = alles in Ordnung. */
declare(strict_types=1);
define('SAMS_IGNORE_MAINTENANCE', true);
require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
$db = true;
$pending = 0;
try {
    db()->query('SELECT 1');
    $pending = count(pending_migrations());
} catch (Throwable $e) {
    $db = false;
}
$maintenance = in_maintenance();
http_response_code($db && !$maintenance ? 200 : 503);
echo json_encode([
    'status' => !$db ? 'db_error' : ($maintenance ? 'maintenance' : 'ok'),
    'version' => SAMS_VERSION,
    'commit' => substr(git_version()['commit'], 0, 7),
    'pending_migrations' => $pending,
]);
