<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Zusammenarbeit der Fraktionen:
 * - Fahndung: Personen und Fahrzeuge; Haftbefehle der Justiz werden automatisch
 *   bei allen Fraktionen mit Fahndungsmodul ausgeschrieben und bei Stornierung beendet.
 * - Behördenanfragen: Anfrage an eine andere Fraktion, Antwort mit freigegebenen Dokumenten.
 * - Freigabe an eine ganze Behörde (alle mit Dokumentrecht in dieser Fraktion).
 */

const WANTED_DANGER = [0 => 'unbekannt', 1 => 'gering', 2 => 'erhöht', 3 => 'hoch – bewaffnet'];
const REQUEST_STATUS = ['open' => 'offen', 'in_progress' => 'in Bearbeitung', 'answered' => 'beantwortet', 'declined' => 'abgelehnt'];

function normalize_plate(string $plate): string
{
    return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $plate));
}

/** Offene Fahndungen nach Bürger-ID oder Kennzeichen über alle Fraktionen mit Fahndungsmodul. */
function wanted_lookup(string $citizenId = '', string $plate = ''): array
{
    $ids = array_values(array_filter(array_map(fn($f) => (int) $f['id'], factions()), fn($id) => module_enabled('wanted', $id)));
    if (!$ids || ($citizenId === '' && $plate === '')) {
        return [];
    }
    $where = [];
    $params = [];
    if ($citizenId !== '') {
        $where[] = 'citizen_id = ?';
        $params[] = $citizenId;
    }
    if ($plate !== '') {
        $where[] = 'plate = ?';
        $params[] = normalize_plate($plate);
    }
    return all("SELECT * FROM wanted WHERE status = 'open' AND (expires_at IS NULL OR expires_at > ?) AND faction_id IN (" . implode(',', $ids) . ') AND (' . implode(' OR ', $where) . ') ORDER BY danger DESC, id DESC',
        array_merge([now()], $params));
}

/** Haftbefehl der Justiz bei allen Fraktionen mit Fahndungsmodul (außer der Justiz selbst) ausschreiben. */
function wanted_from_warrant(int $documentId): int
{
    $doc = one('SELECT * FROM documents WHERE id = ?', [$documentId]);
    if (!$doc || $doc['type'] !== 'doj_haftbefehl') {
        return 0;
    }
    $d = json_decode((string) $doc['data'], true) ?: [];
    $p = json_decode((string) $doc['patient_snapshot'], true) ?: [];
    $n = 0;
    foreach (factions() as $f) {
        if ((int) $f['id'] === (int) $doc['faction_id'] || !module_enabled('wanted', (int) $f['id'])) {
            continue;
        }
        insert('wanted', ['faction_id' => $f['id'], 'kind' => 'person', 'citizen_id' => (string) ($p['citizen_id'] ?? ''), 'name' => $p ? patient_name($p) : '',
            'reason' => 'Haftbefehl ' . $doc['number'] . ': ' . ($d['tatvorwurf'] ?? '') . (($d['haftgrund'] ?? '') !== '' ? ' (' . $d['haftgrund'] . ')' : ''),
            'danger' => !empty($d['gefaehrlich']) ? 3 : 1, 'source' => 'warrant', 'document_id' => $documentId, 'source_faction_id' => $doc['faction_id'],
            'expires_at' => !empty($d['gueltig_bis']) ? $d['gueltig_bis'] . ' 23:59:59' : null, 'created_by' => $doc['issued_by'], 'created_at' => now()]);
        // Die Polizei darf den Haftbefehl selbst einsehen (Freigabe bis Ablauf)
        insert('document_shares', ['document_id' => $documentId, 'kind' => 'faction', 'target_faction_id' => $f['id'], 'agency' => (string) $f['code'],
            'recipient' => (string) $f['name'], 'purpose' => 'Haftbefehl zur Vollstreckung', 'expires_at' => !empty($d['gueltig_bis']) ? $d['gueltig_bis'] . ' 23:59:59' : null,
            'created_by' => $doc['issued_by'], 'created_at' => now()]);
        $n++;
        $prev = fid();
        set_faction_context((int) $f['id']);
        notify_users_with('wanted.view', 'wanted', 'Neuer Haftbefehl: ' . ($p ? patient_name($p) : 'unbekannt'), url('wanted'));
        set_faction_context($prev);
    }
    return $n;
}

/** Fahndungen zu einem Dokument beenden (z. B. Haftbefehl storniert). */
function wanted_close_for_document(int $documentId, string $note): void
{
    q("UPDATE wanted SET status = 'closed', closed_at = ?, close_note = ? WHERE document_id = ? AND status = 'open'", [now(), mb_substr($note, 0, 190), $documentId]);
}

/** Offene Fahndungen zu einer Akte der eigenen Fraktion (für den Hinweis in der Akte). */
function wanted_for_record(array $p): array
{
    if (!module_enabled('wanted')) {
        return [];
    }
    return all("SELECT * FROM wanted WHERE status = 'open' AND (expires_at IS NULL OR expires_at > ?) AND " . fw() . " AND (patient_id = ? OR (citizen_id <> '' AND citizen_id = ?))",
        [now(), $p['id'], (string) $p['citizen_id']]);
}

function wanted_open_count(): int
{
    try {
        return module_enabled('wanted') ? (int) scalar("SELECT COUNT(*) FROM wanted WHERE status = 'open' AND (expires_at IS NULL OR expires_at > ?) AND " . fw(), [now()]) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

// --------------------------------------------------------- Behördenanfragen

/** Andere aktive Fraktionen (für Anfragen und Freigaben). */
function other_factions(): array
{
    return array_values(array_filter(factions(), fn($f) => (int) $f['id'] !== fid()));
}

function request_open_count(): int
{
    try {
        return module_enabled('requests') ? (int) scalar("SELECT COUNT(*) FROM agency_requests WHERE to_faction_id = ? AND status IN ('open', 'in_progress')", [fid()]) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/** Anfrage, die die aktuelle Fraktion sehen darf (gestellt oder erhalten). */
function agency_request(int $id): ?array
{
    return one('SELECT * FROM agency_requests WHERE id = ? AND (faction_id = ? OR to_faction_id = ?)', [$id, fid(), fid()]);
}

// --------------------------------------------- Freigabe an eine ganze Behörde

/** Aktive Freigabe eines Dokuments für die aktuelle Fraktion (an die Behörde oder das eigene Konto). */
function faction_share_for(int $documentId): ?array
{
    $me = current_user();
    foreach (all("SELECT * FROM document_shares WHERE document_id = ? AND ((kind = 'faction' AND target_faction_id = ?) OR (kind = 'user' AND user_id = ?))",
        [$documentId, fid(), $me['id'] ?? 0]) as $s) {
        if (share_active($s) && ($s['kind'] === 'user' || can('documents.view|requests.view'))) {
            return $s;
        }
    }
    return null;
}
