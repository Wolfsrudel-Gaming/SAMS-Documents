<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Feuerwehr: Geräte & Prüffristen (auch für den Rettungsdienst nutzbar), Atemschutz-Nachweise nach FwDV 7,
 * Brandschau ↔ Objektplan (Prüftermine werden aus dem Brandschaubericht übernommen).
 */

const EQUIPMENT_STATUS = ['ok' => ['green', 'einsatzbereit'], 'defect' => ['red', 'defekt'], 'repair' => ['orange', 'in Reparatur'], 'out' => ['grey', 'ausgesondert']];
const EQUIPMENT_CATEGORIES = ['Atemschutz', 'Hydraulisches Rettungsgerät', 'Leitern', 'Schläuche & Armaturen', 'Pumpen & Aggregate', 'Messgeräte', 'Medizintechnik (Defibrillator, Beatmung)',
    'Sanitätsmaterial', 'Fahrzeug', 'Funk', 'Persönliche Schutzausrüstung', 'Sonstiges'];
const BREATHING_KINDS = ['Einsatz', 'Übung', 'Belastungsübung (Atemschutzstrecke)', 'Unterweisung', 'Einsatzkurzprüfung'];

function equipment_due_count(): int
{
    try {
        return module_enabled('equipment') ? (int) scalar("SELECT COUNT(*) FROM equipment WHERE active = 1 AND status <> 'out' AND next_check <> '' AND next_check <= ? AND " . fw(), [date('Y-m-d', time() + 7 * 86400)]) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}

/** Prüfung eintragen: setzt letzte/nächste Prüfung und Status. */
function equipment_check(array $eq, string $result, string $note, string $date, ?int $userId): void
{
    $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) ? $date : date('Y-m-d');
    insert('equipment_checks', ['equipment_id' => (int) $eq['id'], 'result' => $result, 'note' => mb_substr($note, 0, 190), 'user_id' => $userId, 'checked_on' => $date, 'created_at' => now()]);
    $row = ['last_check' => $date, 'reminded' => 0, 'updated_at' => now(), 'status' => $result === 'ok' ? 'ok' : 'defect'];
    if ($result === 'ok') {
        $row['next_check'] = date('Y-m-d', strtotime($date . ' +' . max(1, (int) $eq['interval_days']) . ' days'));
    }
    update('equipment', (int) $eq['id'], $row);
}

/** Atemschutz-Tauglichkeit: Eignungsuntersuchung (G26.3), Belastungsübung und Unterweisung je höchstens 12 Monate alt. */
function breathing_state(?array $st): array
{
    $today = date('Y-m-d');
    $yearAgo = date('Y-m-d', strtotime('-12 months'));
    $issues = [];
    if (!$st || ($st['g26_until'] ?? '') === '' || $st['g26_until'] < $today) {
        $issues[] = 'Eignungsuntersuchung';
    }
    if (!$st || ($st['last_exercise'] ?? '') === '' || $st['last_exercise'] < $yearAgo) {
        $issues[] = 'Belastungsübung';
    }
    if (!$st || ($st['last_instruction'] ?? '') === '' || $st['last_instruction'] < $yearAgo) {
        $issues[] = 'Unterweisung';
    }
    return ['ok' => !$issues, 'issues' => $issues];
}

/** Hinweise: fällige Geräteprüfungen (7 Tage vorher), ablaufende Atemschutz-Eignung (30 Tage vorher). Läuft im Cron je Fraktion. */
function fire_cron(): int
{
    $n = 0;
    if (module_enabled('equipment')) {
        foreach (all("SELECT * FROM equipment WHERE active = 1 AND status <> 'out' AND reminded = 0 AND next_check <> '' AND next_check <= ? AND " . fw(), [date('Y-m-d', time() + 7 * 86400)]) as $eq) {
            q('UPDATE equipment SET reminded = 1 WHERE id = ?', [$eq['id']]);
            notify_users_with('equipment.manage', 'equipment', 'Prüfung fällig: ' . $eq['name'] . ($eq['inventory_no'] ? ' (' . $eq['inventory_no'] . ')' : '') . ' bis ' . fmt_date($eq['next_check']), url('equipment', ['filter' => 'due']));
            $n++;
        }
    }
    if (module_enabled('breathing')) {
        foreach (all("SELECT * FROM breathing_status WHERE reminded = 0 AND g26_until <> '' AND g26_until <= ? AND " . fw(), [date('Y-m-d', time() + 30 * 86400)]) as $st) {
            q('UPDATE breathing_status SET reminded = 1 WHERE user_id = ?', [$st['user_id']]);
            $msg = 'Atemschutz: Eignungsuntersuchung läuft am ' . fmt_date($st['g26_until']) . ' ab';
            notify_user((int) $st['user_id'], 'breathing', $msg, url('breathing'));
            notify_users_with('breathing.manage', 'breathing', $msg . ' – ' . (string) scalar('SELECT name FROM users WHERE id = ?', [$st['user_id']]), url('breathing'));
            $n++;
        }
    }
    return $n;
}

/** Objektplan zu einem Freitext (Objektname oder Anschrift) der eigenen Fraktion. */
function object_by_text(string $text): ?array
{
    $text = trim($text);
    if ($text === '' || !module_enabled('objects')) {
        return null;
    }
    return one('SELECT * FROM objects WHERE (LOWER(name) = LOWER(?) OR LOWER(address) = LOWER(?) OR LOWER(?) LIKE ' . (db_driver() === 'sqlite' ? "'%' || LOWER(name) || '%'" : "CONCAT('%', LOWER(name), '%')") . ') AND ' . fw() . ' ORDER BY id LIMIT 1', [$text, $text, $text]);
}

/** Nach dem Ausstellen von Brandschau/Nachschau: Prüftermine im Objektplan übernehmen. */
function fire_document_issued(int $docId, string $type, array $data): void
{
    if (!in_array($type, ['fd_brandschau', 'fd_nachschau'], true) || !($obj = object_by_text((string) ($data['objekt'] ?? '')))) {
        return;
    }
    $date = (string) ($data['datum'] ?? date('Y-m-d'));
    $frist = (string) ($data['frist'] ?? '');
    $clean = in_array($data['ergebnis'] ?? '', ['Keine Mängel', 'Alle Mängel beseitigt'], true);
    $next = $frist !== '' && !$clean ? $frist : date('Y-m-d', strtotime($date . ' +1 year'));
    update('objects', (int) $obj['id'], ['last_inspection' => $date, 'next_inspection' => $next, 'updated_at' => now()]);
    audit('object.inspection', 'object', (int) $obj['id'], $obj['name'] . ': ' . ($data['ergebnis'] ?? '') . ' – nächste Prüfung ' . fmt_date($next));
}

/** Brandschau-Dokumente zu einem Objekt (über den Objektnamen im Dokument). */
function object_documents(array $obj): array
{
    $rows = all("SELECT id, type, number, issued_at, status, data FROM documents WHERE type IN ('fd_brandschau', 'fd_nachschau', 'fd_bescheinigung', 'fd_brandursache', 'fd_einsatz') AND " . fw() . ' ORDER BY issued_at DESC LIMIT 300');
    $name = mb_strtolower(trim($obj['name']));
    $addr = mb_strtolower(trim((string) $obj['address']));
    return array_values(array_filter($rows, function ($r) use ($name, $addr) {
        $d = json_decode((string) $r['data'], true) ?: [];
        $text = mb_strtolower((string) ($d['objekt'] ?? $d['ort'] ?? ''));
        return $text !== '' && (str_contains($text, $name) || ($addr !== '' && str_contains($text, $addr)));
    }));
}
