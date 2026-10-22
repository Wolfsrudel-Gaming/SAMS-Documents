<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Benachrichtigungen nach Discord: Webhooks je Kanal (Dokument-Log,
 * Freigaben, Personal-Log, Bürgerportal, Berichte) und Direktnachrichten an
 * verknüpfte Bürger über den Bot. Nachrichten landen in einer Warteschlange
 * und werden nach der Antwort an den Browser bzw. vom Cron-Lauf verschickt –
 * so bremst Discord das Websystem nie aus. Medizinische Inhalte werden nie
 * übertragen.
 */

const WEBHOOK_CHANNELS = [
    'documents' => 'Dokument-Log (ausgestellt, storniert)',
    'approvals' => 'Freigaben (eingereicht, freigegeben, abgelehnt)',
    'personnel' => 'Personal-Log (Eintritt, Beförderung, Qualifikationen, Dienst)',
    'portal'    => 'Bürgerportal (Terminanfragen, Anträge, Bewerbungen)',
    'reports'   => 'Wochen- und Monatsberichte',
];

function valid_webhook_url(string $url): bool
{
    return (bool) preg_match('#^https://(?:ptb\.|canary\.)?(?:discord|discordapp)\.com/api/webhooks/\d+/[\w-]+$#', $url);
}

/** Nachricht an einen Webhook-Kanal (falls eingerichtet). $embed: Discord-Embed oder Text. */
function notify_webhook(string $channel, $embed): void
{
    $url = (string) setting('webhook_' . $channel, '');
    if (!valid_webhook_url($url)) {
        return;
    }
    $payload = is_array($embed) ? ['embeds' => [$embed + ['timestamp' => date('c')]]] : ['content' => mb_substr((string) $embed, 0, 1900)];
    $payload['username'] = org('org_short');
    discord_enqueue('webhook', $url, $payload);
}

/** Direktnachricht an eine Discord-ID (benötigt Bot-Token). */
function notify_dm(string $discordId, string $text): void
{
    if (cfg('discord.bot_token', '') === '' || setting('discord_dm_enabled', '1') !== '1' || !preg_match('/^\d{5,25}$/', $discordId)) {
        return;
    }
    discord_enqueue('dm', $discordId, ['content' => mb_substr($text, 0, 1900)]);
}

/** Bürger mit verknüpfter Akte per DM informieren. */
function notify_patient_dm(?int $patientId, string $text): void
{
    if (!$patientId) {
        return;
    }
    foreach (all('SELECT discord_id FROM citizens WHERE patient_id = ? AND blocked = 0', [$patientId]) as $c) {
        notify_dm((string) $c['discord_id'], $text);
    }
}

function discord_enqueue(string $kind, string $target, array $payload): void
{
    try {
        insert('discord_queue', ['kind' => $kind, 'target' => $target, 'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE), 'created_at' => now()]);
    } catch (PDOException $e) {
        return; // vor der Migration
    }
    static $registered = false;
    if (!$registered && PHP_SAPI !== 'cli') {
        $registered = true;
        register_shutdown_function(function () {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request(); // Seite ist beim Browser, jetzt in Ruhe senden
            }
            discord_flush(10);
        });
    }
}

/** Ausstehende Nachrichten senden. Gibt die Anzahl gesendeter Nachrichten zurück. */
function discord_flush(int $limit = 50): int
{
    $sent = 0;
    foreach (all('SELECT * FROM discord_queue WHERE sent_at IS NULL AND attempts < 5 ORDER BY id LIMIT ' . max(1, $limit)) as $m) {
        $payload = json_decode((string) $m['payload'], true) ?: [];
        $error = '';
        if ($m['kind'] === 'webhook') {
            [$st] = discord_http('POST', $m['target'], ['Content-Type: application/json'], json_encode($payload), 4);
            $error = $st >= 200 && $st < 300 ? '' : 'HTTP ' . $st;
        } elseif ($m['kind'] === 'dm') {
            $auth = ['Authorization: Bot ' . cfg('discord.bot_token', ''), 'Content-Type: application/json'];
            [$st, $ch] = discord_http('POST', 'https://discord.com/api/v10/users/@me/channels', $auth, json_encode(['recipient_id' => $m['target']]), 4);
            if ($st === 200 && !empty($ch['id'])) {
                [$st] = discord_http('POST', 'https://discord.com/api/v10/channels/' . $ch['id'] . '/messages', $auth, json_encode($payload), 4);
            }
            $error = $st >= 200 && $st < 300 ? '' : 'HTTP ' . $st;
        }
        if ($error === '') {
            q('UPDATE discord_queue SET sent_at = ?, attempts = attempts + 1 WHERE id = ?', [now(), $m['id']]);
            $sent++;
        } else {
            q('UPDATE discord_queue SET attempts = attempts + 1, last_error = ? WHERE id = ?', [$error, $m['id']]);
        }
    }
    // Aufräumen: gesendete Nachrichten nach 14 Tagen löschen
    q('DELETE FROM discord_queue WHERE sent_at IS NOT NULL AND sent_at < ?', [date('Y-m-d H:i:s', time() - 14 * 86400)]);
    return $sent;
}

/** Einheitliches Embed für Log-Kanäle. */
function log_embed(string $title, string $text = '', int $color = 0x0b4f9c, array $fields = []): array
{
    $e = ['title' => mb_substr($title, 0, 250), 'color' => $color];
    if ($text !== '') {
        $e['description'] = mb_substr($text, 0, 2000);
    }
    if ($fields) {
        $e['fields'] = $fields;
    }
    return $e;
}
