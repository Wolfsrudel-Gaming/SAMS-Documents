<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Behörden-Werkzeuge: Asservaten, Fahrzeugregister, Haftbuch, Strafregister (Polizei/Justiz),
 * Gerichtskalender und Fristen mit Anwaltszugang (Justiz), Lizenzen mit Ablauf (Verwaltung/DMV, Polizei),
 * Objektpläne (Feuerwehr) und gemeinsame Einsatzberichte mehrerer Behörden.
 */

const EVIDENCE_STATUS = [
    'stored' => ['green', 'eingelagert'], 'out' => ['orange', 'entnommen'], 'handed_over' => ['blue', 'übergeben'],
    'returned' => ['grey', 'herausgegeben'], 'destroyed' => ['grey', 'vernichtet'],
];
/** Aktion => [Bezeichnung, erlaubt ab Status, neuer Status (null = bleibt)] */
const EVIDENCE_ACTIONS = [
    'out' => ['Entnehmen (z. B. Untersuchung, Verhandlung)', ['stored'], 'out'],
    'stored' => ['Wieder einlagern / zurückerhalten', ['out', 'handed_over'], 'stored'],
    'handed_over' => ['Übergeben (z. B. an DOJ, Labor)', ['stored', 'out'], 'handed_over'],
    'returned' => ['An Berechtigte herausgeben', ['stored', 'out'], 'returned'],
    'destroyed' => ['Vernichten', ['stored', 'out'], 'destroyed'],
    'note' => ['Vermerk', ['stored', 'out', 'handed_over', 'returned', 'destroyed'], null],
];
const EVIDENCE_CATEGORIES = ['Waffe', 'Munition', 'Betäubungsmittel', 'Bargeld', 'Elektronik', 'Dokument', 'Fahrzeug', 'Kleidung', 'Biologische Spur', 'Werkzeug', 'Sonstiges'];

const VEHICLE_STATUS = [
    'registered' => ['green', 'zugelassen'], 'deregistered' => ['grey', 'abgemeldet'], 'stolen' => ['red', 'gestohlen gemeldet'], 'seized' => ['orange', 'beschlagnahmt'],
];

const CUSTODY_FIT = ['' => 'nicht geprüft', 'fit' => 'haftfähig', 'limited' => 'eingeschränkt haftfähig', 'unfit' => 'nicht haftfähig (Klinik)'];

const LICENSE_STATUS = ['valid' => ['green', 'gültig'], 'suspended' => ['orange', 'gesperrt'], 'revoked' => ['red', 'entzogen'], 'expired' => ['grey', 'abgelaufen']];
const LICENSE_DEFAULT_TYPES = [
    'government' => "Führerschein | Motorradführerschein | Lkw-Führerschein | Bootsführerschein | Flugschein (PPL) | Hubschrauberlizenz | Taxischein | Jagdschein | Angelschein",
    'police' => "Waffenbesitzkarte | Kleiner Waffenschein | Waffenschein (Führen) | Sicherheitsdienst-Erlaubnis",
    'default' => "Führerschein | Waffenschein",
];

const HEARING_KINDS = ['Hauptverhandlung', 'Haftprüfung', 'Vorführung', 'Anhörung', 'Zivilverhandlung', 'Verkündungstermin', 'Güteverhandlung'];
const HEARING_STATUS = ['planned' => ['blue', 'geplant'], 'done' => ['green', 'durchgeführt'], 'postponed' => ['orange', 'vertagt'], 'cancelled' => ['grey', 'aufgehoben']];
const DEADLINE_KINDS = ['Einspruchsfrist', 'Berufungsfrist', 'Stellungnahme', 'Haftprüfung', 'Zahlungsfrist', 'Wiedervorlage', 'Sonstige'];

const OBJECT_KINDS = ['Wohngebäude', 'Gewerbe / Büro', 'Industrie / Lager', 'Tankstelle', 'Krankenhaus / Pflege', 'Schule / Kita', 'Behörde', 'Versammlungsstätte', 'Hochhaus', 'Sonstiges'];
const OBJECT_RISK = [0 => 'normal', 1 => 'erhöht', 2 => 'hoch', 3 => 'sehr hoch (Sonderobjekt)'];

function status_badge(array $map, string $key): string
{
    [$c, $l] = $map[$key] ?? ['grey', $key];
    return '<span class="badge ' . $c . '">' . e($l) . '</span>';
}

/** Laufende Nummer je Fraktion und Jahr, z. B. AS-2026-0007. */
function authority_number(string $table, string $prefix): string
{
    $base = $prefix . '-' . date('Y') . '-';
    $last = (string) scalar("SELECT number FROM $table WHERE number LIKE ? AND " . fw() . ' ORDER BY id DESC LIMIT 1', [$base . '%']);
    return $base . str_pad((string) ((int) substr($last, strlen($base)) + 1), 4, '0', STR_PAD_LEFT);
}

/** Liste aus einer Einstellung (durch | oder Zeilen getrennt). */
function setting_list(string $key, string $default): array
{
    return array_values(array_filter(array_map('trim', preg_split('/[|\n]/', (string) setting($key, $default)))));
}

function factions_with_module(string $module): array
{
    return array_values(array_filter(array_map(fn($f) => (int) $f['id'], factions()), fn($id) => module_enabled($module, $id)));
}

/** Datum (YYYY-MM-DD) aus einem Formularfeld oder ''. */
function post_date(string $key): string
{
    $v = (string) post($key);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
}

/** Person zu einer Akte der eigenen Fraktion (oder freie Angaben). */
function person_from_post(): array
{
    $p = (int) post('patient_id') ? fone('patients', (int) post('patient_id')) : null;
    return [
        'patient_id' => $p['id'] ?? null,
        'citizen_id' => mb_substr(trim((string) ($p['citizen_id'] ?? '') ?: (string) post('citizen_id')), 0, 64),
        'name' => mb_substr(trim($p ? patient_name($p) : (string) post('name')), 0, 190),
    ];
}

function record_options(): array
{
    return module_enabled('records') ? all('SELECT id, first_name, last_name, citizen_id FROM patients WHERE archived_at IS NULL AND merged_into IS NULL AND ' . fw() . ' ORDER BY last_name, first_name LIMIT 1000') : [];
}

// ------------------------------------------------------------- Asservaten

function evidence_move(int $evidenceId, string $action, string $note = '', string $counterpart = '', ?int $userId = null): void
{
    insert('evidence_moves', ['evidence_id' => $evidenceId, 'action' => $action, 'note' => mb_substr($note, 0, 190), 'counterpart' => mb_substr($counterpart, 0, 190),
        'user_id' => $userId ?? (current_user()['id'] ?? null), 'created_at' => now()]);
}

// --------------------------------------------------------- Fahrzeugregister

/** Registrierte Fahrzeuge zu einem Kennzeichen (alle Fraktionen mit Fahrzeugregister). */
function vehicle_lookup(string $plate): array
{
    $ids = factions_with_module('vehicles');
    $plate = normalize_plate($plate);
    if (!$ids || $plate === '') {
        return [];
    }
    return all('SELECT * FROM vehicles WHERE plate = ? AND faction_id IN (' . implode(',', $ids) . ') ORDER BY updated_at DESC, id DESC', [$plate]);
}

// ---------------------------------------------------------------- Haftbuch

function custody_open_count(): int
{
    try {
        return module_enabled('custody') ? (int) scalar("SELECT COUNT(*) FROM custody WHERE status = 'in' AND " . fw()) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/** Geplante Entlassung nach Haft-Einheiten (1 Einheit = einstellbare Minuten, Standard 1). */
function custody_release_due(array $c): ?string
{
    $minutes = (int) $c['jail_units'] * max(1, (int) setting('custody_unit_minutes', '1'));
    return $minutes > 0 ? date('Y-m-d H:i:s', strtotime($c['arrested_at']) + $minutes * 60) : null;
}

// ------------------------------------------------------------- Strafregister

/** Dokumentarten, die ins Strafregister gehören. */
const CRIMINAL_RECORD_TYPES = ['doj_urteil' => 'Urteil', 'doj_strafbefehl' => 'Strafbefehl', 'doj_haftbefehl' => 'Haftbefehl', 'doj_anklage' => 'Anklage',
    'pd_festnahme' => 'Festnahme', 'pd_anzeige' => 'Strafanzeige', 'rechnung' => 'Bußgeld'];

/**
 * Strafregister einer Person über Polizei und Justiz hinweg (gültige Dokumente und Haftbuch).
 * Nur für Polizei und Justiz; medizinische Akten fließen nie ein.
 */
function criminal_record(string $citizenId): array
{
    $ids = array_merge(faction_ids_of_kind('police'), faction_ids_of_kind('justice'));
    if ($citizenId === '' || !$ids) {
        return [];
    }
    $in = implode(',', $ids);
    $police = implode(',', faction_ids_of_kind('police') ?: [0]);
    $docs = all("SELECT d.id, d.type, d.number, d.issued_at, d.status, d.faction_id, d.data, d.amount_cents FROM documents d JOIN patients p ON p.id = d.patient_id
        WHERE p.citizen_id = ? AND d.faction_id IN ($in) AND d.status <> 'revoked'
        AND d.type IN ('" . implode("','", array_keys(CRIMINAL_RECORD_TYPES)) . "') AND (d.type <> 'rechnung' OR d.faction_id IN ($police))
        ORDER BY d.issued_at DESC LIMIT 200", [$citizenId]);
    $out = [];
    foreach ($docs as $d) {
        $data = json_decode((string) $d['data'], true) ?: [];
        $what = (string) ($data['tat'] ?? $data['tatvorwurf'] ?? $data['tenor'] ?? $data['delikte'] ?? $data['vorwurf'] ?? '');
        if ($d['type'] === 'rechnung') {
            $what = implode(', ', array_filter(array_map(fn($i) => (string) ($i['text'] ?? ''), (array) ($data['items'] ?? [])))) . ' · ' . money($d['amount_cents'] / 100);
        }
        $out[] = ['date' => $d['issued_at'], 'kind' => CRIMINAL_RECORD_TYPES[$d['type']], 'number' => $d['number'], 'text' => mb_strimwidth(trim($what), 0, 200, '…'),
            'agency' => faction_by_id((int) $d['faction_id'])['short'] ?? '', 'document_id' => (int) $d['id'], 'faction_id' => (int) $d['faction_id']];
    }
    foreach (all("SELECT * FROM custody WHERE citizen_id = ? AND faction_id IN ($in) ORDER BY arrested_at DESC LIMIT 100", [$citizenId]) as $c) {
        $out[] = ['date' => $c['arrested_at'], 'kind' => 'Gewahrsam', 'number' => '', 'text' => mb_strimwidth(trim((string) $c['reason']), 0, 200, '…') . ($c['jail_units'] ? ' · ' . $c['jail_units'] . ' Haft-Einheiten' : ''),
            'agency' => faction_by_id((int) $c['faction_id'])['short'] ?? '', 'document_id' => 0, 'faction_id' => (int) $c['faction_id']];
    }
    usort($out, fn($a, $b) => strcmp($b['date'], $a['date']));
    return $out;
}

function criminal_record_visible(): bool
{
    return in_array(faction_kind(), ['police', 'justice'], true) && can('patients.view');
}

// ---------------------------------------------------------------- Lizenzen

function license_types(): array
{
    return setting_list('license_types', LICENSE_DEFAULT_TYPES[faction_kind()] ?? LICENSE_DEFAULT_TYPES['default']);
}

/** Tatsächlicher Status (abgelaufen, Sperre vorbei → gültig). */
function license_state(array $l): string
{
    if ($l['status'] === 'suspended' && $l['suspended_until'] !== '' && $l['suspended_until'] < date('Y-m-d')) {
        return $l['expires_on'] !== '' && $l['expires_on'] < date('Y-m-d') ? 'expired' : 'valid';
    }
    if ($l['status'] === 'valid' && $l['expires_on'] !== '' && $l['expires_on'] < date('Y-m-d')) {
        return 'expired';
    }
    return $l['status'];
}

/** Lizenzen einer Person über alle Fraktionen mit Lizenzregister (für Polizei-Abfragen). */
function licenses_for_citizen(string $citizenId): array
{
    $ids = factions_with_module('licenses');
    if ($citizenId === '' || !$ids) {
        return [];
    }
    return array_map(fn($l) => $l + ['state' => license_state($l)],
        all('SELECT * FROM licenses WHERE citizen_id = ? AND faction_id IN (' . implode(',', $ids) . ') ORDER BY type, id DESC', [$citizenId]));
}

// ------------------------------------------------------------------- Gericht

/** SQL-Bedingung für Termine, die der Benutzer sehen darf (Anwaltszugang: nur eigene). */
function hearing_scope(string $alias = 'h'): string
{
    $u = current_user();
    if (!can('court.own')) {
        return fw($alias);
    }
    $id = (int) $u['id'];
    return fw($alias) . " AND ($alias.judge_id = $id OR $alias.prosecutor_id = $id OR $alias.lawyer_id = $id OR $alias.created_by = $id)";
}

function deadline_scope(string $alias = 'd'): string
{
    return can('court.own') ? fw($alias) . " AND ($alias.assigned_to = " . (int) current_user()['id'] . " OR $alias.created_by = " . (int) current_user()['id'] . ')' : fw($alias);
}

/** Erinnerungen: Termine 24 Stunden vorher an die Beteiligten, Fristen 2 Tage vorher (läuft im Cron je Fraktion). */
function court_cron(): int
{
    if (!module_enabled('court')) {
        return 0;
    }
    $n = 0;
    foreach (all("SELECT * FROM hearings WHERE status = 'planned' AND reminded = 0 AND starts_at > ? AND starts_at <= ? AND " . fw(), [now(), date('Y-m-d H:i:s', time() + 86400)]) as $h) {
        q('UPDATE hearings SET reminded = 1 WHERE id = ?', [$h['id']]);
        foreach (array_unique(array_filter([(int) $h['judge_id'], (int) $h['prosecutor_id'], (int) $h['lawyer_id']])) as $uid) {
            notify_user($uid, 'court', 'Termin morgen: ' . $h['title'] . ' (' . fmt_datetime($h['starts_at']) . ', ' . $h['room'] . ')', url('court'));
            $n++;
        }
    }
    foreach (all('SELECT * FROM deadlines WHERE done_at IS NULL AND reminded = 0 AND due_date <= ? AND ' . fw(), [date('Y-m-d', time() + 2 * 86400)]) as $d) {
        q('UPDATE deadlines SET reminded = 1 WHERE id = ?', [$d['id']]);
        $title = 'Frist ' . ($d['due_date'] < date('Y-m-d') ? 'abgelaufen' : 'läuft ab') . ': ' . $d['title'] . ' (' . fmt_date($d['due_date']) . ')';
        if ($d['assigned_to']) {
            notify_user((int) $d['assigned_to'], 'court', $title, url('court', ['view' => 'deadlines']));
        } else {
            notify_users_with('court.manage', 'court', $title, url('court', ['view' => 'deadlines']));
        }
        $n++;
    }
    return $n;
}

function court_open_count(): int
{
    try {
        return module_enabled('court') ? (int) scalar('SELECT COUNT(*) FROM deadlines d WHERE d.done_at IS NULL AND d.due_date <= ? AND ' . deadline_scope(), [date('Y-m-d', time() + 2 * 86400)]) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/** Konten der eigenen Fraktion mit einer Rolle für Auswahllisten (Richter/in, Staatsanwaltschaft, Anwaltschaft). */
function court_people(): array
{
    return all('SELECT u.id, u.name, r.short AS rank_short FROM users u LEFT JOIN ranks r ON r.id = u.rank_id WHERE u.active = 1 AND ' . fw('u') . ' ORDER BY u.name');
}

// ------------------------------------------------------------ Objektpläne

/** Plan/Foto zu einem Objekt speichern (Bild wird neu kodiert, PDF geprüft). Gibt [id, null] oder [null, Fehler]. */
function store_object_file(array $file, int $objectId, string $title, int $userId): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || $file['size'] > attachment_max_bytes()) {
        return [null, 'Bitte eine Datei bis ' . round(attachment_max_bytes() / 1048576) . ' MB auswählen.'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    if (!isset(ATTACHMENT_TYPES[$mime])) {
        return [null, 'Erlaubt sind Bilder (JPG, PNG, WebP, GIF) und PDF-Dateien.'];
    }
    $stored = 'obj_' . bin2hex(random_bytes(16)) . '.' . ATTACHMENT_TYPES[$mime];
    $target = attachment_dir() . '/' . $stored;
    $thumb = false;
    if ($mime === 'application/pdf') {
        if (strncmp((string) file_get_contents($file['tmp_name'], false, null, 0, 5), '%PDF-', 5) !== 0 || !move_uploaded_file_safe($file['tmp_name'], $target)) {
            return [null, 'Die PDF-Datei ist beschädigt.'];
        }
    } else {
        [$ok, , , $thumb] = reencode_image($file['tmp_name'], $mime, $target);
        if (!$ok) {
            return [null, 'Das Bild konnte nicht gelesen werden.'];
        }
    }
    $id = insert('object_files', ['object_id' => $objectId, 'title' => mb_substr($title !== '' ? $title : pathinfo((string) $file['name'], PATHINFO_FILENAME), 0, 190),
        'stored_name' => $stored, 'mime' => $mime, 'size' => (int) filesize($target), 'has_thumb' => $thumb ? 1 : 0, 'created_by' => $userId, 'created_at' => now()]);
    return [$id, null];
}

// ------------------------------------------------- Gemeinsame Einsatzberichte

/** Bericht, wenn die aktuelle Fraktion beteiligt ist (oder null). */
function joint_report(int $id): ?array
{
    return one('SELECT r.* FROM joint_reports r WHERE r.id = ? AND (r.faction_id = ? OR EXISTS (SELECT 1 FROM joint_participants p WHERE p.report_id = r.id AND p.faction_id = ?))', [$id, fid(), fid()]);
}

function joint_participants(int $reportId): array
{
    return all('SELECT f.* FROM factions f WHERE f.id IN (SELECT faction_id FROM joint_participants WHERE report_id = ?) OR f.id = (SELECT faction_id FROM joint_reports WHERE id = ?) ORDER BY f.sort, f.id', [$reportId, $reportId]);
}

function joint_open_count(): int
{
    try {
        // offene Berichte, an denen die eigene Fraktion beteiligt ist, aber noch keinen Teil geschrieben hat
        return module_enabled('joint') ? (int) scalar("SELECT COUNT(*) FROM joint_reports r WHERE r.status = 'open' AND EXISTS (SELECT 1 FROM joint_participants p WHERE p.report_id = r.id AND p.faction_id = ?)
            AND NOT EXISTS (SELECT 1 FROM joint_sections s WHERE s.report_id = r.id AND s.faction_id = ?)", [fid(), fid()]) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/** Hinweis an die Lizenzstelle, wenn eine Lizenz in 14 Tagen abläuft (läuft im Cron je Fraktion). */
function license_cron(): int
{
    if (!module_enabled('licenses')) {
        return 0;
    }
    $rows = all("SELECT * FROM licenses WHERE status = 'valid' AND reminded = 0 AND expires_on <> '' AND expires_on <= ? AND " . fw(), [date('Y-m-d', time() + 14 * 86400)]);
    foreach ($rows as $l) {
        q('UPDATE licenses SET reminded = 1 WHERE id = ?', [$l['id']]);
        notify_users_with('licenses.manage', 'licenses', $l['type'] . ' von ' . $l['name'] . ' läuft am ' . fmt_date($l['expires_on']) . ' ab', url('licenses', ['filter' => 'expiring']));
    }
    return count($rows);
}
