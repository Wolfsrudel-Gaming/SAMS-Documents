<?php
/**
 * Andock-Schnittstelle für eigene Skripte und Tools (Discord-Bots, Webseiten, weitere FiveM-Resourcen …).
 * Schlüssel, Rechte und Webhooks: Administration → Schnittstellen. Ausführliche Beschreibung: docs/API.md
 *
 *   GET  integration.php?action=ping
 *   POST integration.php?action=entry_create   {"faction": "sams", "citizen_id": "…", "title": "…", "content": "…"}
 *   Header: Authorization: Bearer samsi_…   (alternativ X-SAMS-Token)
 *
 * Antworten immer JSON mit HTTP 200 und "ok" (true/false + "error"). Ausnahmen: 401 (Schlüssel ungültig) und 503 (Wartung).
 * So funktionieren auch Aufrufer wie FXServer (PerformHttpRequest), die bei Fehlercodes den Antworttext verwerfen.
 */
declare(strict_types=1);
define('SAMS_IGNORE_MAINTENANCE', true);
require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$respond = function (array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
};

if (in_maintenance()) {
    $respond(['ok' => false, 'error' => 'maintenance', 'message' => 'Das System wird gerade aktualisiert.'], 503);
}
$auth = (string) ($_SERVER['HTTP_AUTHORIZATION'] ?? ($_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''));
$token = preg_match('/^Bearer\s+(\S+)$/i', $auth, $m) ? $m[1] : (string) ($_SERVER['HTTP_X_SAMS_TOKEN'] ?? '');
$integ = integration_by_token($token);
if (!$integ) {
    $respond(['ok' => false, 'error' => 'Ungültiger oder deaktivierter Schlüssel'], 401);
}
if (!rate_limit('integration' . $integ['id'], max(10, (int) setting('integration_rate', '240')), 60)) {
    $respond(['ok' => false, 'error' => 'rate_limited', 'message' => 'Zu viele Anfragen – bitte kurz warten.', 'retry_after' => 60]);
}
if (!$integ['last_used_at'] || strtotime((string) $integ['last_used_at']) < time() - 60) {
    q('UPDATE integrations SET last_used_at = ?, last_ip = ? WHERE id = ?', [now(), mb_substr(client_ip(), 0, 64), $integ['id']]);
}

$body = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $body = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = $_POST;
    }
}
$in = $body + array_diff_key($_GET, ['action' => 1]);
$action = (string) ($_GET['action'] ?? ($body['action'] ?? ''));
try {
    $respond(integration_call($action, $in, $integ));
} catch (Throwable $e) {
    error_log('[integration] ' . $action . ': ' . $e->getMessage());
    $respond(['ok' => false, 'error' => 'Interner Fehler bei ' . mb_substr($action, 0, 40)]);
}
