<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Bürgerportal: Bürger melden sich mit Discord an, sehen nach der
 * Verknüpfung mit ihrer Patientenakte ihre Dokumente und können Termine,
 * Anträge und Bewerbungen einreichen. Bearbeitet wird im Websystem.
 */

const REQUEST_KINDS = [
    'aktenkopie'     => 'Kopie meiner Patientenakte',
    'schweigepflicht' => 'Entbindung von der Schweigepflicht',
    'beschwerde'     => 'Beschwerde / Rückmeldung',
    'sonstiges'      => 'Sonstiges Anliegen',
];
const PORTAL_STATUS = [
    'requested' => ['orange', 'angefragt'], 'confirmed' => ['green', 'bestätigt'], 'declined' => ['red', 'abgelehnt'], 'done' => ['grey', 'erledigt'], 'cancelled' => ['grey', 'storniert'],
    'open' => ['orange', 'offen'], 'in_progress' => ['blue', 'in Bearbeitung'], 'rejected' => ['red', 'abgelehnt'],
    'new' => ['orange', 'neu'], 'invited' => ['blue', 'zum Gespräch eingeladen'], 'accepted' => ['green', 'angenommen'],
];

function portal_status_badge(string $status): string
{
    [$c, $l] = PORTAL_STATUS[$status] ?? ['grey', $status];
    return '<span class="badge ' . $c . '">' . e($l) . '</span>';
}

function portal_enabled(): bool
{
    return setting('portal_enabled', '1') === '1';
}

function appointment_kinds(): array
{
    $kinds = array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) setting('portal_appointment_kinds',
        "Allgemeine Sprechstunde\nUntersuchung Waffenschein\nUntersuchung Führerschein / Fluglizenz\nEinstellungsuntersuchung\nPsychologisches Gespräch\nNachsorge / Fäden ziehen\nImpfung"))), 'strlen'));
    return $kinds ?: ['Allgemeine Sprechstunde'];
}

/** Dokumentarten, die im Portal nicht angezeigt werden (vertraulich). */
function portal_hidden_types(): array
{
    return array_values(array_filter(array_map('trim', explode(',', (string) setting('portal_hidden_types', 'psych_gutachten,unterbringung,blutentnahme,verletzung,todesbescheinigung')))));
}

function current_citizen(): ?array
{
    $id = (int) ($_SESSION['citizen_id'] ?? 0);
    if (!$id) {
        return null;
    }
    $c = one('SELECT * FROM citizens WHERE id = ? AND blocked = 0', [$id]);
    if (!$c) {
        unset($_SESSION['citizen_id']);
    }
    return $c;
}

/** Anmeldung bzw. Registrierung über das Discord-Profil. */
function citizen_login(array $profile): array
{
    $discordId = preg_replace('/\D/', '', (string) $profile['id']);
    $name = mb_substr((string) ($profile['global_name'] ?? $profile['username'] ?? ''), 0, 120);
    $c = one('SELECT * FROM citizens WHERE discord_id = ?', [$discordId]);
    if ($c) {
        update('citizens', (int) $c['id'], ['discord_name' => $name, 'avatar' => discord_avatar_url($profile), 'last_login' => now()]);
    } else {
        $id = insert('citizens', ['discord_id' => $discordId, 'discord_name' => $name, 'avatar' => discord_avatar_url($profile), 'created_at' => now(), 'last_login' => now()]);
        $c = one('SELECT * FROM citizens WHERE id = ?', [$id]);
    }
    session_regenerate_id(true);
    $_SESSION['citizen_id'] = (int) $c['id'];
    return $c;
}

/** Verknüpfungscode (24 h gültig), den der Bürger dem SAMS nennt. */
function citizen_link_code(array $c): string
{
    if ($c['link_code'] && $c['link_expires'] > now()) {
        return $c['link_code'];
    }
    $code = substr(random_code(8), 0, 4) . '-' . substr(random_code(8), 0, 4);
    update('citizens', (int) $c['id'], ['link_code' => $code, 'link_expires' => date('Y-m-d H:i:s', time() + 86400)]);
    return $code;
}

/** Bürgerkonto per Code mit einer Patientenakte verknüpfen. Gibt das Konto zurück oder null. */
function link_citizen(string $code, int $patientId, int $staffId): ?array
{
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    $code = substr($code, 0, 4) . '-' . substr($code, 4, 4);
    $c = one('SELECT * FROM citizens WHERE link_code = ? AND link_expires > ? AND blocked = 0', [$code, now()]);
    if (!$c) {
        return null;
    }
    update('citizens', (int) $c['id'], ['patient_id' => $patientId, 'link_code' => null, 'link_expires' => null, 'linked_by' => $staffId, 'linked_at' => now()]);
    return $c;
}

function portal_documents(array $c): array
{
    if (!$c['patient_id']) {
        return [];
    }
    $hidden = portal_hidden_types();
    $own = array_filter(all('SELECT * FROM documents WHERE patient_id = ? ORDER BY issued_at DESC', [$c['patient_id']]), fn($d) => !in_array($d['type'], $hidden, true));
    // Bescheide anderer Behörden zur selben Bürger-ID (Bußgeld, Waffenschein, Ladung, Urteil, Genehmigung …)
    $cid = (string) scalar('SELECT citizen_id FROM patients WHERE id = ?', [$c['patient_id']]);
    $other = $cid === '' ? [] : all('SELECT d.* FROM documents d JOIN patients p ON p.id = d.patient_id WHERE p.citizen_id = ? AND d.patient_id <> ? AND d.faction_id <> 1
        AND d.type IN (' . implode(',', array_map(fn($t) => "'$t'", PORTAL_CROSS_TYPES)) . ') ORDER BY d.issued_at DESC LIMIT 50', [$cid, $c['patient_id']]);
    $all = array_merge($own, $other);
    usort($all, fn($a, $b) => strcmp($b['issued_at'], $a['issued_at']));
    return array_values($all);
}

/** Dokumentarten anderer Behörden, die Bürger im Portal sehen (an sie gerichtete Bescheide und Erlaubnisse). */
const PORTAL_CROSS_TYPES = ['rechnung', 'pd_waffenschein', 'pd_platzverweis', 'doj_ladung', 'doj_strafbefehl', 'doj_urteil', 'fd_bescheinigung', 'gov_bescheid', 'gov_genehmigung', 'gov_bescheinigung', 'gov_gewerbe'];

/** Fraktionen, bei denen man sich über das Portal bewerben kann. */
function portal_factions(): array
{
    return array_values(array_filter(factions(), fn($f) => module_enabled('portal', (int) $f['id'])));
}

/** Offene Vorgänge für das Personal (Navigation). */
function portal_open_count(): int
{
    if (!can('portal.manage|portal.applications')) {
        return 0;
    }
    $n = 0;
    if (can('portal.manage')) {
        $n += (int) scalar("SELECT COUNT(*) FROM appointments WHERE status = 'requested' AND " . fw()) + (int) scalar("SELECT COUNT(*) FROM portal_requests WHERE status = 'open' AND " . fw());
    }
    if (can('portal.applications')) {
        $n += (int) scalar("SELECT COUNT(*) FROM applications WHERE status = 'new' AND " . fw());
    }
    return $n;
}
