<?php
/**
 * GitHub-Webhook für sofortige Updates.
 *
 * In GitHub: Settings → Webhooks → Add webhook
 *   Payload URL:  https://<eure-domain>/hooks/github.php
 *   Content type: application/json
 *   Secret:       Wert von UPDATE_WEBHOOK_SECRET aus der .env
 *   Events:       Just the push event
 *
 * Der Webhook führt selbst keinen Code aus. Er hinterlegt nur eine Anforderung,
 * die der Update-Timer (bin/update.sh) sofort bzw. beim nächsten Lauf abarbeitet.
 */
declare(strict_types=1);
define('SAMS_IGNORE_MAINTENANCE', true);
require dirname(__DIR__, 2) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$secret = (string) cfg('update.webhook_secret', '');
if ($secret === '') {
    http_response_code(503);
    exit('{"error":"Webhook deaktiviert"}');
}
$payload = (string) file_get_contents('php://input');
$sig = (string) ($_SERVER['HTTP_X_HUB_SIGNATURE_256'] ?? '');
if (!hash_equals('sha256=' . hash_hmac('sha256', $payload, $secret), $sig)) {
    http_response_code(401);
    exit('{"error":"Ungültige Signatur"}');
}

$event = (string) ($_SERVER['HTTP_X_GITHUB_EVENT'] ?? '');
if ($event === 'ping') {
    exit('{"ok":true,"message":"pong"}');
}
$data = json_decode($payload, true);
$branch = (string) cfg('update.branch', 'main');
if ($event !== 'push' || ($data['ref'] ?? '') !== 'refs/heads/' . $branch) {
    exit('{"ok":true,"message":"ignoriert"}');
}

request_update('github ' . substr((string) ($data['after'] ?? ''), 0, 7));
echo '{"ok":true,"message":"Update angefordert"}';
