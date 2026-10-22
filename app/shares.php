<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Freigaben für Behörden: einzelne Dokumente per geheimem Link (mit Ablauf)
 * oder an ein Benutzerkonto (z. B. Rang „LSPD (extern)“ mit nur dem Recht
 * „Freigegebene Dokumente ansehen“). Jeder Abruf wird protokolliert.
 */

const SHARE_AGENCIES = [
    'lspd'  => 'LSPD – Los Santos Police Department',
    'bcso'  => 'BCSO – Blaine County Sheriff\'s Office',
    'doj'   => 'DOJ – Department of Justice / Gericht',
    'gov'   => 'Government / Behörde',
    'other' => 'Sonstige Stelle',
];
const SHARE_DURATIONS = ['24' => '24 Stunden', '168' => '7 Tage', '720' => '30 Tage', '0' => 'unbegrenzt (bis Widerruf)'];

function share_url(string $token): string
{
    return base_url() . '/share.php?t=' . rawurlencode($token);
}

function share_active(array $s): bool
{
    return !(int) $s['revoked'] && (empty($s['expires_at']) || $s['expires_at'] > now());
}

/** Freigabe anlegen. Gibt bei Links den Klartext-Schlüssel zurück (wird nur einmal angezeigt). */
function create_share(array $doc, string $kind, ?int $userId, string $agency, string $recipient, string $purpose, int $hours): ?string
{
    $token = $kind === 'link' ? bin2hex(random_bytes(24)) : null;
    $id = insert('document_shares', [
        'document_id' => $doc['id'], 'kind' => $kind, 'token_hash' => $token ? hash('sha256', $token) : null,
        'user_id' => $kind === 'user' ? $userId : null, 'agency' => $agency,
        'recipient' => mb_substr($recipient, 0, 190), 'purpose' => mb_substr($purpose, 0, 190),
        'expires_at' => $hours > 0 ? date('Y-m-d H:i:s', time() + $hours * 3600) : null,
        'created_by' => current_user()['id'], 'created_at' => now(),
    ]);
    audit('document.share', 'document', (int) $doc['id'], $doc['number'] . ' → ' . (SHARE_AGENCIES[$agency] ?? $agency) . ($recipient !== '' ? ', ' . $recipient : '') . " (Freigabe #$id)");
    return $token;
}

/** Abruf zählen und protokollieren. */
function share_touch(array $share, string $via): void
{
    q('UPDATE document_shares SET views = views + 1, last_viewed_at = ? WHERE id = ?', [now(), $share['id']]);
    try {
        insert('audit_log', [
            'user_id' => current_user()['id'] ?? null, 'action' => 'document.share_view', 'object_type' => 'document',
            'object_id' => $share['document_id'], 'details' => mb_substr($via . ' · Freigabe #' . $share['id'] . ' · ' . ($share['recipient'] ?: $share['agency']), 0, 1000),
            'ip' => client_ip(), 'created_at' => now(),
        ]);
    } catch (PDOException $e) {
        // Protokollierung darf die Ansicht nicht verhindern
    }
}

/** Für das angemeldete Konto freigegebene, aktive Freigabe eines Dokuments. */
function user_share_for(int $documentId): ?array
{
    $me = current_user();
    if (!$me) {
        return null;
    }
    foreach (all("SELECT * FROM document_shares WHERE document_id = ? AND kind = 'user' AND user_id = ?", [$documentId, $me['id']]) as $s) {
        if (share_active($s)) {
            return $s;
        }
    }
    return null;
}
