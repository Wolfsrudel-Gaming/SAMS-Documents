<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Spezialrettung (TAS): Flugbuch mit Luftfahrzeugen, Flugzeiten je Pilot/in, Wartung nach Betriebsstunden.
 * Ein Flugbericht mit bekannter Kennung und Flugzeit trägt den Flug automatisch ein.
 */

const AIRCRAFT_STATUS = ['ok' => ['green', 'einsatzbereit'], 'maintenance' => ['orange', 'in Wartung'], 'defect' => ['red', 'außer Dienst']];
const AIRCRAFT_KINDS = ['Hubschrauber', 'Flugzeug', 'Drohne'];

function minutes_hm(int $min): string
{
    return sprintf('%d:%02d h', intdiv($min, 60), $min % 60);
}

function aircraft_by_text(string $text): ?array
{
    $text = trim($text);
    return $text === '' ? null : one('SELECT * FROM aircraft WHERE active = 1 AND (LOWER(callsign) = LOWER(?) OR LOWER(registration) = LOWER(?)) AND ' . fw() . ' LIMIT 1', [$text, $text]);
}

/** Flug eintragen und Betriebszeit des Luftfahrzeugs erhöhen; Hinweis, wenn die Wartung fällig wird. */
function flight_log(array $ac, array $f): int
{
    $id = insert('flights', $f + ['aircraft_id' => (int) $ac['id'], 'created_at' => now()]);
    $total = (int) $ac['minutes_total'] + (int) $f['minutes'];
    update('aircraft', (int) $ac['id'], ['minutes_total' => $total, 'updated_at' => now()]);
    if ((int) $ac['maintenance_at'] > 0 && $total >= (int) $ac['maintenance_at'] && (int) $ac['minutes_total'] < (int) $ac['maintenance_at']) {
        notify_users_with('flightlog.manage', 'flightlog', 'Wartung fällig: ' . $ac['callsign'] . ' (' . minutes_hm($total) . ' Betriebszeit)', url('flightlog'));
    }
    return $id;
}

/** Nach dem Ausstellen eines Flugberichts: Flug ins Flugbuch (Pilot/in = ausstellende Person). */
function rescue_document_issued(int $docId, string $type, array $data, int $issuerId): void
{
    if ($type !== 'tas_flug' || !module_enabled('flightlog') || !($ac = aircraft_by_text((string) ($data['kennung'] ?? ''))) || (int) ($data['flugzeit'] ?? 0) <= 0) {
        return;
    }
    flight_log($ac, ['flown_on' => (string) ($data['datum'] ?? date('Y-m-d')), 'pilot_id' => $issuerId, 'crew' => mb_substr((string) ($data['crew'] ?? ''), 0, 190),
        'mission' => mb_substr((string) ($data['auftrag'] ?? ''), 0, 190), 'start_place' => mb_substr((string) ($data['start_ort'] ?? ''), 0, 190),
        'end_place' => mb_substr((string) ($data['lande_ort'] ?? ''), 0, 190), 'minutes' => min(1440, (int) $data['flugzeit']), 'winch_ops' => (int) ($data['winden'] ?? 0),
        'landings' => (int) ($data['landungen'] ?? 0), 'document_id' => $docId, 'recorded_by' => $issuerId]);
}

function flightlog_due_count(): int
{
    try {
        return module_enabled('flightlog') ? (int) scalar('SELECT COUNT(*) FROM aircraft WHERE active = 1 AND maintenance_at > 0 AND minutes_total >= maintenance_at AND ' . fw()) : 0;
    } catch (PDOException $e) {
        return 0;
    }
}
