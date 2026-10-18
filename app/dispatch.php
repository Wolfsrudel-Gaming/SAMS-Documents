<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Leitstelle (optional): Einsätze und FMS-Status aus einer Leitstellen-Software, z. B. tetrascript (tetra-core).
 * Die Brücke in sams_tablet (server/tetra.lua) liefert die Daten über api.php; ohne Leitstelle bleibt alles leer.
 * Aus einem Einsatz entsteht per Knopf ein vorbelegter Einsatzbericht bzw. ein Einsatzprotokoll.
 */

const FMS_LABELS = [0 => 'Notruf / dringender Sprechwunsch', 1 => 'Einsatzbereit über Funk', 2 => 'Einsatzbereit auf Wache', 3 => 'Einsatz übernommen', 4 => 'Am Einsatzort',
    5 => 'Sprechwunsch', 6 => 'Nicht einsatzbereit', 7 => 'Patient aufgenommen', 8 => 'Am Zielort', 9 => 'Fremdanmeldung'];
const FMS_COLORS = [0 => 'red', 1 => 'green', 2 => 'green', 3 => 'orange', 4 => 'red', 5 => 'blue', 6 => 'grey', 7 => 'orange', 8 => 'blue', 9 => 'grey'];
const DISPATCH_PHASES = ['intake' => ['grey', 'Annahme'], 'dispatch' => ['orange', 'Disposition'], 'enroute' => ['orange', 'Anfahrt'], 'onscene' => ['red', 'vor Ort'],
    'closed' => ['green', 'abgeschlossen'], 'cancelled' => ['grey', 'abgebrochen']];
/** Organisation in der Leitstelle je Fraktionsart (änderbar je Fraktion: Einstellungen → Spielserver). */
const DISPATCH_DEFAULT_ORG = ['medical' => 'ems', 'police' => 'police', 'fire' => 'fire', 'justice' => '', 'government' => '', 'rescue' => 'tas'];
/** Dokumentart für den Bericht aus einem Einsatz je Fraktionsart. */
const DISPATCH_REPORT_TYPE = ['fire' => 'fd_einsatz', 'medical' => 'einsatzprotokoll', 'police' => 'pd_anzeige', 'rescue' => 'tas_einsatz'];

function dispatch_org(?int $factionId = null): string
{
    $factionId ??= fid();
    $prev = fid();
    set_faction_context($factionId);
    $org = preg_replace('/[^a-z0-9_]/', '', strtolower((string) setting('game_tetra_org', ''))) ?: (DISPATCH_DEFAULT_ORG[faction_kind($factionId)] ?? '');
    set_faction_context($prev);
    return $org;
}

function fms_badge(?int $status): string
{
    if ($status === null) {
        return '';
    }
    return '<span class="badge ' . (FMS_COLORS[$status] ?? 'grey') . '" title="' . e(FMS_LABELS[$status] ?? '') . '">FMS ' . $status . '</span>';
}

/** Unix- oder ISO-Zeit aus der Leitstelle → Datenbank-Zeit (Millisekunden werden erkannt). */
function dispatch_time($v): ?string
{
    if (is_numeric($v) && (float) $v > 0) {
        $t = (float) $v > 1e11 ? (int) ((float) $v / 1000) : (int) $v;
        return date('Y-m-d H:i:s', $t);
    }
    return is_string($v) && ($t = strtotime($v)) ? date('Y-m-d H:i:s', $t) : null;
}

/** Einsatz anlegen/aktualisieren (Fraktion über die Organisation des Einsatzes). Gibt die Anzahl betroffener Fraktionen zurück. */
function dispatch_upsert(array $inc, string $source = 'tetra'): int
{
    $extId = mb_substr((string) ($inc['id'] ?? ''), 0, 64);
    $org = strtolower((string) ($inc['org'] ?? ''));
    if ($extId === '' || $org === '') {
        return 0;
    }
    $n = 0;
    foreach (factions() as $f) {
        if (dispatch_org((int) $f['id']) !== $org) {
            continue;
        }
        set_faction_context((int) $f['id']);
        $times = ['started_at' => dispatch_time($inc['createdAt'] ?? null), 'alarm_at' => dispatch_time($inc['firstAlarmAt'] ?? null),
            'onscene_at' => dispatch_time($inc['firstOnSceneAt'] ?? null), 'closed_at' => dispatch_time($inc['closedAt'] ?? null)];
        $row = $times + [
            'number' => mb_substr((string) ($inc['number'] ?? ''), 0, 64), 'org' => mb_substr($org, 0, 32), 'category' => mb_substr((string) ($inc['category'] ?? ''), 0, 32),
            'keyword' => mb_substr((string) ($inc['keyword'] ?? ''), 0, 64), 'title' => mb_substr((string) ($inc['title'] ?? ''), 0, 190),
            'address' => mb_substr((string) ($inc['address'] ?? ''), 0, 190), 'caller' => mb_substr(trim(($inc['caller'] ?? '') . ' ' . ($inc['phone'] ?? '')), 0, 190),
            'description' => mb_substr((string) ($inc['description'] ?? ''), 0, 5000), 'priority' => (int) ($inc['priority'] ?? 0), 'patients' => (int) ($inc['patients'] ?? 0),
            'phase' => mb_substr((string) ($inc['phase'] ?? ''), 0, 16), 'closure' => mb_substr((string) ($inc['closure'] ?? ''), 0, 190),
            'payload' => json_encode(['assignments' => array_slice((array) ($inc['assignments'] ?? []), 0, 50), 'timeline' => array_slice((array) ($inc['timeline'] ?? []), -200),
                'tasks' => array_slice((array) ($inc['tasks'] ?? []), 0, 50), 'commander' => (string) ($inc['commander'] ?? ''), 'risks' => (string) ($inc['risks'] ?? '')], JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ];
        $existing = one('SELECT * FROM dispatch_incidents WHERE source = ? AND ext_id = ? AND faction_id = ?', [$source, $extId, $f['id']]);
        if ($existing) {
            update('dispatch_incidents', (int) $existing['id'], $row);
            if ($row['phase'] === 'closed' && $existing['phase'] !== 'closed' && !$existing['document_id']) {
                notify_users_with('documents.issue.' . (DISPATCH_REPORT_TYPE[faction_kind()] ?? 'fd_einsatz'), 'dispatch',
                    'Einsatz ' . ($row['number'] ?: $extId) . ' abgeschlossen – Bericht schreiben', url('dispatch', ['id' => $existing['id']]));
            }
        } else {
            insert('dispatch_incidents', $row + ['faction_id' => (int) $f['id'], 'source' => $source, 'ext_id' => $extId, 'received_at' => now()]);
        }
        $n++;
    }
    return $n;
}

/** Vorbelegung für den Bericht aus einem Einsatz (Feldname => Wert). */
function dispatch_prefill(array $inc, string $type): array
{
    $p = json_decode((string) $inc['payload'], true) ?: [];
    $units = implode(', ', array_unique(array_filter(array_map(fn($a) => (string) ($a['label'] ?? $a['issi'] ?? ''), (array) ($p['assignments'] ?? [])))));
    $timeline = implode("\n", array_map(fn($t) => (dispatch_time($t['at'] ?? null) ? date('H:i', strtotime(dispatch_time($t['at']))) . ' ' : '') . trim((string) ($t['text'] ?? '')),
        array_filter((array) ($p['timeline'] ?? []), fn($t) => trim((string) ($t['text'] ?? '')) !== '')));
    $hm = fn($v) => $v ? date('H:i', strtotime($v)) : '';
    $what = trim(($inc['keyword'] ? $inc['keyword'] . ' – ' : '') . $inc['title']);
    return match ($type) {
        'fd_einsatz' => ['datum' => substr((string) ($inc['started_at'] ?: now()), 0, 10), 'alarm' => $hm($inc['alarm_at']), 'ankunft' => $hm($inc['onscene_at']), 'ende' => $hm($inc['closed_at']),
            'art' => match ($inc['category']) { 'fire' => 'Brand', 'technical' => 'Technische Hilfe', default => '' }, 'ort' => $inc['address'],
            'lage' => trim($what . "\n" . $inc['description']), 'massnahmen' => $timeline, 'kraefte' => $units],
        'einsatzprotokoll' => ['mission_no' => $inc['number'], 'date' => substr((string) ($inc['started_at'] ?: now()), 0, 10), 'location' => $inc['address'], 'unit' => $units,
            't_alarm' => $hm($inc['alarm_at']), 't_arrival' => $hm($inc['onscene_at']), 'call_reason' => trim($what . "\n" . $inc['description'])],
        default => ['tatort' => $inc['address'], 'sachverhalt' => trim($what . "\n" . $inc['description'] . "\n\n" . $timeline), 'datum' => substr((string) ($inc['started_at'] ?: now()), 0, 10)],
    };
}

function dispatch_open_count(): int
{
    try {
        return (int) scalar("SELECT COUNT(*) FROM dispatch_incidents WHERE phase NOT IN ('closed', 'cancelled') AND " . fw());
    } catch (PDOException $e) {
        return 0;
    }
}

/** FMS-Status eines Mitarbeitenden (von der Brücke), optional Dienst automatisch beginnen/beenden. */
function dispatch_fms(array $u, int $status, string $issi, string $opta): array
{
    q('UPDATE users SET fms_status = ?, fms_at = ?, fms_issi = ?, fms_opta = ? WHERE id = ?', [$status, now(), mb_substr($issi, 0, 16), mb_substr($opta, 0, 32), $u['id']]);
    $duty = null;
    set_faction_context((int) $u['faction_id']);
    if (setting('game_tetra_duty', '0') === '1' && in_array('duty.clock', user_permissions($u), true)) {
        $open = duty_open_session((int) $u['id']);
        if (in_array($status, [1, 2, 3, 4], true) && !$open) {
            duty_set((int) $u['id'], true, 'game');
            $duty = true;
        } elseif ($status === 6 && $open) {
            duty_set((int) $u['id'], false, 'game');
            $duty = false;
        }
    }
    return ['ok' => true, 'duty' => $duty];
}
