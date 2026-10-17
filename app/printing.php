<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Ingame-Anbindung: Tablet-Anmeldungen, Drucker, Druckaufträge und die
 * Daten für Dokument-Items (ox_inventory).
 */

const TABLET_TOKEN_DAYS = 30;
const PRINT_PROPS = [
    'prop_printer_01' => 'Bürodrucker (klein)',
    'prop_printer_02' => 'Bürodrucker (groß)',
    'v_res_printer'   => 'Tintenstrahldrucker',
    'prop_copier_01'  => 'Kopierer',
    ''                => 'Kein Prop (vorhandenes Objekt in der Map nutzen)',
];

// ------------------------------------------------------------------ Tablet

/** Anmeldung aus dem Spiel. Rückgabe: ['status' => ok|2fa|invalid|blocked|inactive, …] */
function tablet_login(string $username, string $password, string $code, string $license, string $playerName): array
{
    // Für Login-Schutz zählt der Spieler (Lizenz), nicht die IP des Spielservers
    $ip = 'fivem:' . substr($license !== '' ? $license : $playerName, 0, 56);
    if (login_blocked($username, $ip)) {
        return ['status' => 'blocked', 'message' => 'Zu viele Fehlversuche. Bitte in ' . LOGIN_WINDOW_MIN . ' Minuten erneut versuchen.'];
    }
    $u = one('SELECT * FROM users WHERE username = ?', [$username]);
    if (!$u || !password_verify($password, $u['password_hash'])) {
        record_login_attempt($username, false, $ip);
        return ['status' => 'invalid', 'message' => 'Benutzername oder Passwort falsch.'];
    }
    if (!(int) $u['active']) {
        return ['status' => 'inactive', 'message' => 'Dieses Konto ist gesperrt.'];
    }
    if (!empty($u['totp_enabled'])) {
        if ($code === '') {
            return ['status' => '2fa', 'message' => 'Bitte den Code aus deiner Authenticator-App eingeben.'];
        }
        $step = totp_verify((string) $u['totp_secret'], $code, (int) $u['totp_last_step']);
        if ($step !== null) {
            update('users', (int) $u['id'], ['totp_last_step' => $step]);
        } elseif (($rest = recovery_code_use((string) $u['recovery_codes'], $code)) !== null) {
            update('users', (int) $u['id'], ['recovery_codes' => $rest]);
        } else {
            record_login_attempt($username, false, $ip);
            return ['status' => '2fa', 'message' => 'Der Code ist ungültig.'];
        }
    }
    record_login_attempt($username, true, $ip);
    $token = bin2hex(random_bytes(32));
    insert('tablet_tokens', [
        'user_id' => $u['id'], 'token_hash' => hash('sha256', $token), 'session_version' => (int) $u['session_version'],
        'player_name' => mb_substr($playerName, 0, 190), 'license' => mb_substr($license, 0, 190),
        'created_at' => now(), 'last_used_at' => now(), 'expires_at' => date('Y-m-d H:i:s', time() + TABLET_TOKEN_DAYS * 86400),
    ]);
    update('users', (int) $u['id'], ['last_login' => now()]);
    audit_as((int) $u['id'], 'login', 'user', (int) $u['id'], 'Tablet im Spiel (' . $playerName . ')');
    return ['status' => 'ok', 'token' => $token, 'user' => tablet_user_info($u)];
}

/** Tablet-Token prüfen. Gibt [token-Zeile, Benutzer] zurück oder null. */
function tablet_token_user(string $token): ?array
{
    if (strlen($token) < 32) {
        return null;
    }
    $row = one('SELECT * FROM tablet_tokens WHERE token_hash = ? AND revoked = 0', [hash('sha256', $token)]);
    if (!$row || $row['expires_at'] < now()) {
        return null;
    }
    $u = one('SELECT * FROM users WHERE id = ? AND active = 1', [$row['user_id']]);
    // "Alle Sitzungen beenden" und Passwortwechsel machen auch Tablet-Anmeldungen ungültig
    if (!$u || (int) $u['session_version'] !== (int) $row['session_version']) {
        return null;
    }
    if (!$row['last_used_at'] || strtotime($row['last_used_at']) < time() - 300) {
        q('UPDATE tablet_tokens SET last_used_at = ? WHERE id = ?', [now(), $row['id']]);
    }
    return [$row, $u];
}

function tablet_user_info(array $u): array
{
    $perms = user_permissions($u);
    return [
        'id' => (int) $u['id'],
        'name' => user_display($u),
        'rank' => user_rank_name($u),
        'badge' => (string) $u['badge_number'],
        'can_print' => in_array('documents.print', $perms, true),
        'can_manage_printers' => in_array('printers.manage', $perms, true),
        'can_duty' => in_array('duty.clock', $perms, true),
        'on_duty' => in_array('duty.clock', $perms, true) && duty_open_session((int) $u['id']) !== null,
    ];
}

/** Einmal-Link, mit dem das Tablet eine Browsersitzung im Websystem öffnet (60 Sekunden gültig). */
function tablet_launch_url(array $tokenRow): string
{
    $code = bin2hex(random_bytes(24));
    update('tablet_tokens', (int) $tokenRow['id'], ['launch_hash' => hash('sha256', $code), 'launch_expires' => date('Y-m-d H:i:s', time() + 60)]);
    return base_url() . '/tablet.php?c=' . $code;
}

// ---------------------------------------------------------------- Drucker

/** Aktive Drucker; $allFactions für die Spielwelt (alle Drucker stehen dort), sonst nur die der eigenen Fraktion. */
function active_printers(bool $allFactions = false): array
{
    return all('SELECT * FROM printers WHERE active = 1' . ($allFactions ? '' : ' AND ' . fw()) . ' ORDER BY name');
}

function printer_payload(array $p): array
{
    return [
        'id' => (int) $p['id'], 'name' => $p['name'], 'location' => $p['location'],
        'x' => (float) $p['x'], 'y' => (float) $p['y'], 'z' => (float) $p['z'], 'heading' => (float) $p['heading'],
        'prop' => (string) $p['prop_model'], 'blip' => (bool) $p['show_blip'],
    ];
}

/** Ausfertigungen, die eine Dokumentart beim Druck anbietet (AU: drei Exemplare). */
function print_variants(string $type): array
{
    return $type === 'au'
        ? ['kasse' => 'Ausfertigung Krankenkasse', 'arbeitgeber' => 'Ausfertigung Arbeitgeber (ohne Diagnose)', 'versicherter' => 'Ausfertigung Versicherte/r']
        : ['' => 'Dokument'];
}

/** Wer darf an einem Drucker abholen? (Einstellung print_pickup) */
function pickup_rule(): string
{
    $rule = (string) setting('print_pickup', 'staff');
    return in_array($rule, ['own', 'staff', 'anyone'], true) ? $rule : 'staff';
}

/** Offene Aufträge an einem Drucker, die der Spieler abholen darf. */
function printer_jobs_for(int $printerId, ?array $user): array
{
    $rule = pickup_rule();
    if (!$user && $rule !== 'anyone') {
        return [];
    }
    $where = 'j.printer_id = ? AND j.status = ?';
    $params = [$printerId, 'queued'];
    if ($rule === 'own') {
        $where .= ' AND j.requested_by = ?';
        $params[] = (int) $user['id'];
    }
    return all("SELECT j.*, d.type, d.number, d.patient_snapshot, d.status AS doc_status, u.name AS requester
        FROM print_jobs j JOIN documents d ON d.id = j.document_id LEFT JOIN users u ON u.id = j.requested_by
        WHERE $where ORDER BY j.id", $params);
}

function job_label(array $job): string
{
    $t = doc_type($job['type']);
    $p = json_decode((string) $job['patient_snapshot'], true) ?: [];
    $variant = print_variants($job['type'])[$job['copy_variant']] ?? '';
    $who = $p ? trim(mb_substr((string) ($p['first_name'] ?? ''), 0, 1) . '. ' . ($p['last_name'] ?? '')) : '';
    return trim(($t['short'] ?? 'Dokument') . ($who !== '' ? ' – ' . $who : '')
        . ($job['type'] === 'au' && $variant ? ' (' . preg_replace('/^Ausfertigung /', '', $variant) . ')' : ''));
}

/** Metadaten für das ox_inventory-Item eines gedruckten Dokuments. */
function document_item_metadata(array $doc, string $variant = ''): array
{
    $t = doc_type($doc['type']);
    $p = json_decode((string) $doc['patient_snapshot'], true) ?: [];
    $who = $p ? trim(mb_substr((string) ($p['first_name'] ?? ''), 0, 1) . '. ' . ($p['last_name'] ?? '')) : '';
    $label = ($t['short'] ?? 'Dokument') . ($who !== '' ? ' – ' . $who : '');
    if ($doc['type'] === 'au' && $variant !== '') {
        $label .= ' (' . preg_replace('/^Ausfertigung /', '', print_variants('au')[$variant] ?? $variant) . ')';
    }
    return [
        'label' => $label,
        'description' => $doc['number'] . ' · ' . fmt_date($doc['issued_at']) . ' · ' . org('org_short'),
        'sams_token' => $doc['view_token'],
        'sams_variant' => $variant,
        'sams_number' => $doc['number'],
        'sams_type' => $doc['type'],
    ];
}

function document_view_url(string $token, string $variant = ''): string
{
    return base_url() . '/doc.php?t=' . rawurlencode($token) . ($variant !== '' ? '&copy=' . rawurlencode($variant) : '');
}

/** Protokolleintrag im Namen eines bestimmten Kontos (z. B. aus der API heraus). */
function audit_as(?int $userId, string $action, string $objectType = '', ?int $objectId = null, string $details = ''): void
{
    try {
        insert('audit_log', [
            'faction_id' => (int) ($GLOBALS['sams_fid'] ?? 1),
            'user_id' => $userId, 'action' => $action, 'object_type' => $objectType, 'object_id' => $objectId,
            'details' => mb_substr($details, 0, 1000), 'ip' => 'fivem', 'created_at' => now(),
        ]);
    } catch (PDOException $e) {
        // Protokollierung darf den Betrieb nicht verhindern
    }
}
