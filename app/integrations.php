<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Andock-Schnittstelle für eigene Skripte und Tools.
 *
 * Zwei Wege mit denselben Aufrufen (integration_call):
 *  - Von außen: public/integration.php mit einem Integrationsschlüssel (Administration → Schnittstellen). Jeder Schlüssel hat
 *    Rechte (Scopes) und optional eine Liste von Fraktionen. Ereignisse per Abfrage (events) oder Webhook (signiert).
 *  - Im Spiel: api.php?action=ext mit dem Schlüssel des Spielservers; sams_tablet stellt das als Lua-Exports bereit
 *    (exports.sams_tablet:Call(…), AddRecordEntry(…), …) und gibt Ereignisse als Server-Events weiter (sams:event).
 *
 * Grundsätze: Medizinische Inhalte gehen nie hinaus – Akten und Dokumente des Rettungsdienstes sind für Abfragen
 * gesperrt, Ereignisse aus medizinischen Fraktionen und aus Kanzleien tragen keine Personendaten. Schreiben (Akteneintrag,
 * Benachrichtigung, Anfrage) ist erlaubt, wenn der Schlüssel das Recht dafür und die Fraktion freigegeben hat.
 */

const INTEGRATION_SCOPES = [
    'persons.read'   => ['Personen abfragen', 'Fahndungsstatus, Führer-/Waffenschein, Lizenzen, Erlaubnisse und vorhandene Akten (ohne Rettungsdienst)'],
    'wanted.read'    => ['Fahndung & Fahrzeuge abfragen', 'Offene Fahndungen/Haftbefehle zu Bürger-ID oder Kennzeichen, Fahrzeugregister'],
    'wanted.write'   => ['Fahndungen ausschreiben', 'Fahndung anlegen und erledigen (z. B. Blitzer, Kennzeichenerkennung)'],
    'documents.read' => ['Dokumente prüfen', 'Echtheitsprüfung und Dokumentliste einer Person (nur Eckdaten, ohne Rettungsdienst)'],
    'duty.read'      => ['Dienststatus lesen', 'Wer ist gerade im Dienst (je Fraktion)'],
    'duty.write'     => ['Dienst stempeln', 'Dienst für ein Konto beginnen/beenden'],
    'entries.write'  => ['Akteneinträge schreiben', 'Eintrag in eine Akte (Akte auf Wunsch anlegen)'],
    'alerts.write'   => ['Benachrichtigungen senden', 'Meldung an die Glocke der Mitglieder einer Fraktion'],
    'requests.write' => ['Behördenanfragen stellen', 'Anfrage von einer Fraktion an eine andere'],
    'laws.read'      => ['Gesetzbuch lesen', 'Paragraphen mit Strafmaß'],
    'events.read'    => ['Ereignisse empfangen', 'Ereignisse abrufen (events) bzw. per Webhook zugestellt bekommen'],
];

const INTEGRATION_EVENTS = [
    'document.issued'  => 'Dokument ausgestellt',
    'document.revoked' => 'Dokument storniert',
    'duty.changed'     => 'Dienstbeginn/-ende',
    'wanted.created'   => 'Fahndung ausgeschrieben',
    'wanted.closed'    => 'Fahndung erledigt',
    'request.created'  => 'Behördenanfrage gestellt',
    'request.answered' => 'Behördenanfrage beantwortet/abgelehnt',
    'bill.paid'        => 'Rechnung im Spiel bezahlt',
    'integration.test' => 'Testereignis',
];

/** Aufruf => benötigtes Recht ('' = jeder gültige Schlüssel). */
const INTEGRATION_ACTIONS = [
    'ping' => '', 'factions' => '',
    'person' => 'persons.read',
    'wanted' => 'wanted.read', 'wanted_create' => 'wanted.write', 'wanted_close' => 'wanted.write',
    'verify' => 'documents.read', 'documents' => 'documents.read',
    'duty' => 'duty.read', 'duty_set' => 'duty.write',
    'entry_create' => 'entries.write',
    'alert' => 'alerts.write',
    'request_create' => 'requests.write',
    'laws' => 'laws.read',
    'events' => 'events.read',
];

/** Fraktionsarten, deren Ereignisse ohne Personendaten hinausgehen (Schweigepflicht, Mandatsgeheimnis). */
const INTEGRATION_PRIVATE_KINDS = ['medical', 'law'];

// ------------------------------------------------------------- Schlüssel

function integrations(): array
{
    try {
        return all('SELECT * FROM integrations ORDER BY name, id');
    } catch (PDOException $e) {
        return [];
    }
}

function integration_new_token(): string
{
    return 'samsi_' . bin2hex(random_bytes(24));
}

function integration_by_token(string $token): ?array
{
    if (strlen($token) < 32) {
        return null;
    }
    try {
        $i = one('SELECT * FROM integrations WHERE token_hash = ? AND active = 1', [hash('sha256', $token)]);
    } catch (PDOException $e) {
        return null;
    }
    return $i ?: null;
}

function integration_scopes(?array $integ): array
{
    return $integ === null ? array_keys(INTEGRATION_SCOPES) : array_values(array_intersect(json_decode((string) $integ['scopes'], true) ?: [], array_keys(INTEGRATION_SCOPES)));
}

/** Freigegebene Fraktionen (IDs, nur aktive). Leere Liste am Schlüssel = alle. */
function integration_faction_ids(?array $integ): array
{
    $all = array_map(fn($f) => (int) $f['id'], factions());
    $own = $integ === null ? [] : array_map('intval', json_decode((string) $integ['factions'], true) ?: []);
    return $own ? array_values(array_intersect($all, $own)) : $all;
}

/** Fraktion aus dem Aufruf (ID, Code oder Kürzel); ohne Angabe die des Spielers bzw. die einzige freigegebene. */
function integration_faction(array $in, ?array $integ, ?array $actor = null, string $key = 'faction'): ?array
{
    $allowed = integration_faction_ids($integ);
    $want = trim((string) ($in[$key] ?? ($in[$key . '_id'] ?? '')));
    if ($want === '') {
        $id = $actor ? (int) $actor['faction_id'] : (count($allowed) === 1 ? $allowed[0] : 0);
        return $id && in_array($id, $allowed, true) ? faction_by_id($id) : null;
    }
    foreach (factions() as $f) {
        if (in_array((int) $f['id'], $allowed, true) && (ctype_digit($want) ? (int) $want === (int) $f['id'] : (strcasecmp($want, (string) $f['code']) === 0 || strcasecmp($want, (string) $f['short']) === 0))) {
            return $f;
        }
    }
    return null;
}

function integration_faction_info(array $f): array
{
    return ['id' => (int) $f['id'], 'code' => (string) $f['code'], 'short' => (string) ($f['short'] ?: $f['name']), 'name' => (string) $f['name'], 'kind' => (string) ($f['kind'] ?? 'medical')];
}

function int_in(array $in, string $key, int $max = 190): string
{
    return is_scalar($in[$key] ?? null) ? mb_substr(trim((string) $in[$key]), 0, $max) : '';
}

function int_flag(array $in, string $key): bool
{
    return in_array(strtolower((string) (is_scalar($in[$key] ?? null) ? $in[$key] : '')), ['1', 'true', 'on', 'yes', 'ja'], true);
}

/** Wer hat gehandelt? Für Protokoll und Akteneinträge. */
function integration_label(?array $integ, ?array $actor): string
{
    return $actor ? user_display($actor) . ' (Spiel)' : ($integ ? 'Schnittstelle „' . $integ['name'] . '“' : 'Spielserver' . (game_server_label(game_server_id() ?: null) !== '' ? ' ' . game_server_label(game_server_id()) : ''));
}

// ------------------------------------------------------------ Ereignisse

/** Ereignis protokollieren (für events-Abfrage, Webhooks und das Lua-Relay). Payload ohne medizinische Inhalte! */
function integration_event(string $event, array $payload, ?int $factionId = null, ?int $integrationId = null): void
{
    try {
        insert('integration_events', ['event' => $event, 'faction_id' => $factionId, 'server_id' => (function_exists('game_server_id') && game_server_id()) ? game_server_id() : null,
            'integration_id' => $integrationId, 'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'created_at' => now()]);
    } catch (PDOException $e) {
        return; // vor der Migration
    }
    static $registered = false;
    if (!$registered && PHP_SAPI !== 'cli' && integration_has_webhooks()) {
        $registered = true;
        register_shutdown_function(function () {
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            integration_deliver(3);
        });
    }
}

function integration_has_webhooks(): bool
{
    try {
        return (bool) scalar("SELECT COUNT(*) FROM integrations WHERE active = 1 AND webhook_url <> ''");
    } catch (PDOException $e) {
        return false;
    }
}

/** Fraktion und (bei Rettungsdienst/Kanzlei) ohne Personendaten. */
function integration_person_part(int $factionId, array $person): array
{
    if (in_array(faction_kind($factionId), INTEGRATION_PRIVATE_KINDS, true) || !$person) {
        return [];
    }
    return ['citizen_id' => (string) ($person['citizen_id'] ?? ''), 'person' => patient_name($person)];
}

function integration_faction_part(int $factionId): array
{
    $f = faction_by_id($factionId);
    return ['faction' => $f ? integration_faction_info($f) : null];
}

function integration_document_event(string $event, int $documentId, string $reason = ''): void
{
    $d = one('SELECT * FROM documents WHERE id = ?', [$documentId]);
    if (!$d) {
        return;
    }
    $fid = (int) $d['faction_id'];
    $t = doc_type($d['type']);
    $issuer = json_decode((string) $d['issuer_snapshot'], true) ?: [];
    integration_event($event, ['id' => (int) $d['id'], 'number' => $d['number'], 'type' => $d['type'], 'title' => $t['title'] ?? $d['type'], 'status' => $d['status'],
        'issued_at' => $d['issued_at'], 'valid_until' => $d['valid_until'], 'issuer' => user_display($issuer), 'verify_code' => $d['verify_code'], 'verify_url' => verify_url($d['verify_code'])]
        + ($reason !== '' ? ['reason' => mb_substr($reason, 0, 300)] : [])
        + integration_person_part($fid, json_decode((string) $d['patient_snapshot'], true) ?: []) + integration_faction_part($fid), $fid);
}

function integration_wanted_event(string $event, int $wantedId): void
{
    $w = one('SELECT * FROM wanted WHERE id = ?', [$wantedId]);
    if ($w) {
        integration_event($event, integration_wanted_info($w) + integration_faction_part((int) $w['faction_id']), (int) $w['faction_id']);
    }
}

function integration_wanted_info(array $w): array
{
    return ['id' => (int) $w['id'], 'kind' => $w['kind'], 'name' => $w['name'], 'citizen_id' => $w['citizen_id'], 'plate' => $w['plate'], 'vehicle' => $w['vehicle'],
        'reason' => mb_strimwidth((string) $w['reason'], 0, 240, '…'), 'danger' => (int) $w['danger'], 'danger_label' => WANTED_DANGER[(int) $w['danger']] ?? '',
        'warrant' => $w['source'] === 'warrant', 'status' => $w['status'], 'since' => $w['created_at'], 'expires_at' => $w['expires_at']];
}

function integration_request_event(string $event, int $requestId): void
{
    $r = one('SELECT * FROM agency_requests WHERE id = ?', [$requestId]);
    if (!$r) {
        return;
    }
    $from = faction_by_id((int) $r['faction_id']);
    $to = faction_by_id((int) $r['to_faction_id']);
    $payload = ['id' => (int) $r['id'], 'subject' => $r['subject'], 'status' => $r['status'], 'urgent' => (bool) $r['urgent'], 'reference' => $r['reference'],
        'from' => $from ? integration_faction_info($from) : null, 'to' => $to ? integration_faction_info($to) : null];
    // An beide Seiten: je ein Eintrag, damit Fraktions-Freigaben greifen
    integration_event($event, $payload, (int) $r['faction_id']);
    if ((int) $r['to_faction_id'] !== (int) $r['faction_id']) {
        integration_event($event, $payload, (int) $r['to_faction_id']);
    }
}

/** Ereignisse nach $since, die ein Schlüssel (bzw. der Spielserver bei $integ = null) sehen darf: [Ereignisse, zuletzt gelesene ID]. */
function integration_events_for(?array $integ, int $since, int $limit = 100): array
{
    $ids = integration_faction_ids($integ) ?: [0];
    $where = '(e.faction_id IS NULL OR e.faction_id IN (' . implode(',', $ids) . '))';
    $where .= $integ ? ' AND (e.integration_id IS NULL OR e.integration_id = ' . (int) $integ['id'] . ')' : ' AND e.integration_id IS NULL';
    if (!$integ && game_server_id()) {
        $where .= ' AND (e.server_id IS NULL OR e.server_id = ' . game_server_id() . " OR e.event NOT IN ('duty.changed'))";
    }
    $filter = $integ ? array_values(array_filter(array_map('trim', explode(',', (string) $integ['event_filter'])))) : [];
    $rows = all("SELECT e.* FROM integration_events e WHERE e.id > ? AND $where ORDER BY e.id LIMIT " . max(1, min(500, $limit)), [$since]);
    $out = [];
    $last = $since;
    foreach ($rows as $r) {
        $last = (int) $r['id'];
        if ($filter && !array_filter($filter, fn($p) => $r['event'] === $p || (str_ends_with($p, '*') && str_starts_with($r['event'], rtrim($p, '*'))))) {
            continue;
        }
        $out[] = ['id' => (int) $r['id'], 'event' => $r['event'], 'faction_id' => $r['faction_id'] !== null ? (int) $r['faction_id'] : null,
            'created_at' => $r['created_at'], 'data' => json_decode((string) $r['payload'], true) ?: new stdClass()];
    }
    if (count($rows) < $limit) {
        $last = max($last, (int) scalar('SELECT MAX(id) FROM integration_events WHERE id > ?', [$since])); // nicht sichtbare Ereignisse überspringen
    }
    return [$out, $last];
}

function integration_last_event_id(): int
{
    try {
        return (int) scalar('SELECT MAX(id) FROM integration_events');
    } catch (PDOException $e) {
        return 0;
    }
}

/** Signatur eines Webhook-Bodys: Header X-SAMS-Signature: sha256=<hex>. */
function integration_signature(string $body, string $secret, string $timestamp): string
{
    return 'sha256=' . hash_hmac('sha256', $timestamp . '.' . $body, $secret);
}

function integration_http_post(string $url, string $body, array $headers, int $timeout = 4): array
{
    $ctx = stream_context_create(['http' => ['method' => 'POST', 'timeout' => $timeout, 'ignore_errors' => true, 'content' => $body,
        'header' => implode("\r\n", array_merge(['User-Agent: SAMS-Dokumentensystem (' . SAMS_VERSION . ')', 'Content-Type: application/json'], $headers))]]);
    $res = @file_get_contents($url, false, $ctx);
    $status = 0;
    foreach ($http_response_header ?? [] as $h) {
        if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) {
            $status = (int) $m[1];
        }
    }
    return [$status, $res === false ? '' : (string) $res];
}

function valid_integration_webhook(string $url): bool
{
    return (bool) preg_match('#^https?://[^\s/$.?\#][^\s]*$#i', $url) && strlen($url) <= 500;
}

/**
 * Webhooks zustellen: je Schlüssel alle neuen Ereignisse als ein POST (bis 50). Bei Fehlern später erneut
 * (mit wachsender Pause bis 1 Stunde), die Reihenfolge bleibt erhalten. Gibt die Zahl zugestellter Ereignisse zurück.
 */
function integration_deliver(int $rounds = 5): int
{
    $sent = 0;
    try {
        $list = all("SELECT * FROM integrations WHERE active = 1 AND webhook_url <> ''");
    } catch (PDOException $e) {
        return 0;
    }
    foreach ($list as $integ) {
        if (!in_array('events.read', integration_scopes($integ), true) || !valid_integration_webhook((string) $integ['webhook_url'])) {
            continue;
        }
        $fails = (int) $integ['webhook_failures'];
        if ($fails && $integ['webhook_at'] && strtotime((string) $integ['webhook_at']) > time() - min(3600, 30 * 2 ** min($fails, 7))) {
            continue; // Pause nach Fehlern
        }
        for ($round = 0; $round < $rounds; $round++) {
            [$events, $upto] = integration_events_for($integ, (int) $integ['last_event_id'], 50);
            if (!$events) {
                if ($upto > (int) $integ['last_event_id']) {
                    q('UPDATE integrations SET last_event_id = ? WHERE id = ?', [$upto, $integ['id']]); // nur Ereignisse, die dieser Schlüssel nicht sieht
                }
                break;
            }
            $body = json_encode(['integration' => $integ['name'], 'sent_at' => date('c'), 'events' => $events], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $ts = (string) time();
            [$st] = integration_http_post((string) $integ['webhook_url'], $body, ['X-SAMS-Timestamp: ' . $ts, 'X-SAMS-Signature: ' . integration_signature($body, (string) $integ['webhook_secret'], $ts),
                'X-SAMS-Delivery: ' . $events[0]['id'] . '-' . end($events)['id']]);
            if ($st >= 200 && $st < 300) {
                $integ['last_event_id'] = $upto;
                q("UPDATE integrations SET last_event_id = ?, webhook_failures = 0, webhook_error = '', webhook_at = ? WHERE id = ?", [$integ['last_event_id'], now(), $integ['id']]);
                $sent += count($events);
                continue;
            }
            q('UPDATE integrations SET webhook_failures = webhook_failures + 1, webhook_error = ?, webhook_at = ? WHERE id = ?',
                [$st ? 'HTTP ' . $st : 'nicht erreichbar', now(), $integ['id']]);
            break;
        }
    }
    return $sent;
}

/** Aufräumen: Ereignisse älter als 14 Tage. */
function integration_cleanup(): int
{
    try {
        $n = (int) scalar('SELECT COUNT(*) FROM integration_events WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 14 * 86400)]);
        if ($n) {
            q('DELETE FROM integration_events WHERE created_at < ?', [date('Y-m-d H:i:s', time() - 14 * 86400)]);
        }
        return $n;
    } catch (PDOException $e) {
        return 0;
    }
}

// --------------------------------------------- gemeinsame Abfragen (auch api.php)

/** Echtheitsprüfung eines Prüfcodes (öffentlich wie verify.php). */
function document_verify_data(string $code): array
{
    $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    $doc = $code !== '' ? one('SELECT * FROM documents WHERE verify_code = ?', [$code]) : null;
    if (!$doc) {
        if ($code !== '') {
            forgery_note_check($code);
        }
        return ['found' => false];
    }
    $issuer = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
    $patient = json_decode((string) $doc['patient_snapshot'], true) ?: [];
    return [
        'found' => true, 'status' => $doc['status'],
        'expired' => $doc['valid_until'] !== '' && $doc['valid_until'] < date('Y-m-d'),
        'type' => doc_type($doc['type'])['title'] ?? $doc['type'], 'number' => $doc['number'],
        'issued_at' => $doc['issued_at'], 'valid_until' => $doc['valid_until'],
        'issuer' => user_display($issuer), 'patient' => $patient ? patient_name($patient) : null,
        'citizen_id' => $patient['citizen_id'] ?? null, 'url' => verify_url($doc['verify_code']),
    ];
}

/** Fahndungen und Fahrzeugregister zu Bürger-ID/Kennzeichen. */
function wanted_check_data(string $citizenId, string $plate): array
{
    $hits = wanted_lookup($citizenId, $plate);
    $registry = $plate !== '' ? array_map(fn($v) => ['plate' => $v['plate'], 'model' => $v['model'], 'color' => $v['color'], 'owner' => $v['owner_name'],
        'owner_citizen_id' => $v['owner_citizen_id'], 'status' => $v['status'], 'status_label' => VEHICLE_STATUS[$v['status']][1] ?? $v['status'],
        'valid_until' => fmt_date($v['valid_until']), 'expired' => $v['valid_until'] !== '' && $v['valid_until'] < date('Y-m-d'),
        'agency' => faction_by_id((int) $v['faction_id'])['short'] ?? ''], vehicle_lookup($plate)) : [];
    $flagged = array_values(array_filter($registry, fn($v) => in_array($v['status'], ['stolen', 'seized'], true)));
    return ['ok' => true, 'wanted' => $hits !== [] || $flagged !== [], 'registry' => $registry, 'flagged' => $flagged, 'entries' => array_map(fn($w) => [
        'kind' => $w['kind'], 'name' => $w['name'], 'citizen_id' => $w['citizen_id'], 'plate' => $w['plate'], 'vehicle' => $w['vehicle'],
        'reason' => mb_strimwidth((string) $w['reason'], 0, 240, '…'), 'danger' => (int) $w['danger'], 'danger_label' => WANTED_DANGER[(int) $w['danger']],
        'warrant' => $w['source'] === 'warrant', 'agency' => faction_by_id((int) $w['faction_id'])['short'] ?? '', 'since' => fmt_date($w['created_at']),
    ], $hits)];
}

/** Personenabfrage: Fahndung, Führer-/Waffenschein, Erlaubnisse, Lizenzregister (ohne medizinische Daten). */
function person_check_data(string $cid): array
{
    if ($cid === '') {
        return ['ok' => false, 'error' => 'citizen_id fehlt'];
    }
    $licenses = ['driver' => '', 'weapon' => ''];
    foreach (all("SELECT extra FROM patients WHERE citizen_id = ? AND merged_into IS NULL AND faction_id IN (" . implode(',', faction_ids_of_kind('police')) . ')', [$cid]) as $r) {
        $x = json_decode((string) $r['extra'], true) ?: [];
        $licenses['driver'] = $licenses['driver'] ?: (string) ($x['license_driver'] ?? '');
        $licenses['weapon'] = $licenses['weapon'] ?: (string) ($x['license_weapon'] ?? '');
    }
    $permits = all("SELECT d.type, d.number, d.valid_until, d.faction_id FROM documents d JOIN patients p ON p.id = d.patient_id
        WHERE p.citizen_id = ? AND d.status = 'valid' AND d.type IN ('pd_waffenschein', 'gov_genehmigung') AND (d.valid_until = '' OR d.valid_until >= ?)", [$cid, date('Y-m-d')]);
    $hits = wanted_lookup($cid);
    return ['ok' => true, 'wanted' => $hits !== [], 'warrant' => (bool) array_filter($hits, fn($w) => $w['source'] === 'warrant'),
        'danger' => $hits ? max(array_map(fn($w) => (int) $w['danger'], $hits)) : 0,
        'license_driver' => LICENSE_STATES[$licenses['driver']] ?? 'unbekannt', 'license_weapon' => LICENSE_STATES[$licenses['weapon']] ?? 'unbekannt',
        'permits' => array_map(fn($d) => ['title' => doc_type($d['type'])['title'] ?? $d['type'], 'number' => $d['number'], 'valid_until' => fmt_date($d['valid_until'])], $permits),
        'licenses' => array_map(fn($l) => ['type' => $l['type'], 'classes' => $l['classes'], 'number' => $l['number'], 'state' => $l['state'],
            'label' => LICENSE_STATUS[$l['state']][1] ?? $l['state'], 'expires' => fmt_date($l['expires_on']), 'agency' => faction_by_id((int) $l['faction_id'])['short'] ?? ''],
            licenses_for_citizen($cid))];
}

/** Fraktionen ohne Rettungsdienst (für lesende Abfragen auf Akten und Dokumente). */
function integration_readable_ids(?array $integ): array
{
    return array_values(array_filter(integration_faction_ids($integ), fn($id) => faction_kind($id) !== 'medical')) ?: [0];
}

// ------------------------------------------------------------------ Aufrufe

/**
 * Einen Aufruf ausführen. $integ = Integrationsschlüssel (null = Spielserver mit eigenem Schlüssel, darf alles),
 * $actor = angemeldetes Konto im Spiel (Tablet-Token), wird als Urheber eingetragen.
 */
function integration_call(string $action, array $in, ?array $integ, ?array $actor = null): array
{
    if (!array_key_exists($action, INTEGRATION_ACTIONS)) {
        return ['ok' => false, 'error' => 'Unbekannter Aufruf: ' . mb_substr($action, 0, 40), 'actions' => array_keys(INTEGRATION_ACTIONS)];
    }
    $scope = INTEGRATION_ACTIONS[$action];
    if ($scope !== '' && !in_array($scope, integration_scopes($integ), true)) {
        return ['ok' => false, 'error' => 'Dem Schlüssel fehlt das Recht ' . $scope . '.', 'scope' => $scope];
    }
    $prev = fid();
    try {
        return ('integration_do_' . $action)($in, $integ, $actor);
    } finally {
        set_faction_context($prev);
    }
}

function integration_do_ping(array $in, ?array $integ, ?array $actor): array
{
    return ['ok' => true, 'name' => $integ['name'] ?? 'Spielserver', 'version' => SAMS_VERSION, 'time' => date('c'), 'scopes' => integration_scopes($integ),
        'factions' => array_map(fn($id) => integration_faction_info(faction_by_id($id)), integration_faction_ids($integ))];
}

function integration_do_factions(array $in, ?array $integ, ?array $actor): array
{
    return ['ok' => true, 'factions' => array_map(fn($id) => integration_faction_info(faction_by_id($id)) + ['modules' => faction_modules($id)], integration_faction_ids($integ))];
}

function integration_do_person(array $in, ?array $integ, ?array $actor): array
{
    $cid = int_in($in, 'citizen_id', 64);
    $res = person_check_data($cid);
    if (!$res['ok']) {
        return $res;
    }
    $ids = integration_readable_ids($integ);
    $records = all('SELECT id, faction_id, first_name, last_name, dob FROM patients WHERE citizen_id = ? AND merged_into IS NULL AND archived_at IS NULL AND faction_id IN (' . implode(',', $ids) . ') ORDER BY id', [$cid]);
    $first = $records[0] ?? null;
    return $res + ['citizen_id' => $cid, 'name' => $first ? trim($first['first_name'] . ' ' . $first['last_name']) : null, 'dob' => $first['dob'] ?? null,
        'records' => array_map(fn($r) => ['faction' => integration_faction_info(faction_by_id((int) $r['faction_id'])), 'record_id' => (int) $r['id'],
            'url' => base_url() . '/index.php?p=patient&id=' . (int) $r['id']], $records)];
}

function integration_do_wanted(array $in, ?array $integ, ?array $actor): array
{
    $cid = int_in($in, 'citizen_id', 64);
    $plate = int_in($in, 'plate', 16);
    if ($cid === '' && $plate === '') {
        return ['ok' => false, 'error' => 'citizen_id oder plate angeben'];
    }
    return wanted_check_data($cid, $plate);
}

function integration_do_wanted_create(array $in, ?array $integ, ?array $actor): array
{
    $f = integration_faction($in, $integ, $actor);
    if (!$f || !module_enabled('wanted', (int) $f['id'])) {
        return ['ok' => false, 'error' => 'Fraktion fehlt, ist nicht freigegeben oder hat kein Fahndungsmodul.'];
    }
    set_faction_context((int) $f['id']);
    $kind = int_in($in, 'kind') === 'vehicle' || (int_in($in, 'plate') !== '' && int_in($in, 'citizen_id') === '' && int_in($in, 'name') === '') ? 'vehicle' : 'person';
    $row = ['kind' => $kind, 'citizen_id' => int_in($in, 'citizen_id', 64), 'name' => int_in($in, 'name'), 'plate' => normalize_plate(int_in($in, 'plate', 16)),
        'vehicle' => int_in($in, 'vehicle'), 'reason' => int_in($in, 'reason', 2000), 'danger' => max(0, min(3, (int) int_in($in, 'danger'))),
        'expires_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', int_in($in, 'expires')) ? int_in($in, 'expires') . ' 23:59:59' : null,
        'created_by' => $actor['id'] ?? null, 'created_at' => now()];
    if ($row['reason'] === '' || ($kind === 'person' && $row['name'] === '' && $row['citizen_id'] === '') || ($kind === 'vehicle' && $row['plate'] === '')) {
        return ['ok' => false, 'error' => $kind === 'vehicle' ? 'plate und reason angeben' : 'name oder citizen_id und reason angeben'];
    }
    if ($row['name'] === '' && $row['citizen_id'] !== '' && ($p = one('SELECT first_name, last_name FROM patients WHERE citizen_id = ? AND merged_into IS NULL AND faction_id = ?', [$row['citizen_id'], $f['id']]))) {
        $row['name'] = trim($p['first_name'] . ' ' . $p['last_name']);
    }
    $id = insert('wanted', $row);
    audit_as($actor['id'] ?? null, 'wanted.create', 'wanted', $id, ($kind === 'vehicle' ? $row['plate'] : ($row['name'] ?: $row['citizen_id'])) . ': ' . mb_substr($row['reason'], 0, 120) . ' · ' . integration_label($integ, $actor));
    notify_users_with('wanted.view', 'wanted', 'Neue Fahndung: ' . ($kind === 'vehicle' ? 'Fahrzeug ' . $row['plate'] : ($row['name'] ?: $row['citizen_id'])), url('wanted'),
        fn($u) => (int) $u['faction_id'] === (int) $f['id']);
    integration_wanted_event('wanted.created', $id);
    return ['ok' => true, 'id' => $id];
}

function integration_do_wanted_close(array $in, ?array $integ, ?array $actor): array
{
    $w = one("SELECT * FROM wanted WHERE id = ? AND status = 'open'", [(int) int_in($in, 'id')]);
    if (!$w || !in_array((int) $w['faction_id'], integration_faction_ids($integ), true)) {
        return ['ok' => false, 'error' => 'Offene Fahndung nicht gefunden.'];
    }
    set_faction_context((int) $w['faction_id']);
    $note = int_in($in, 'note') ?: 'erledigt';
    update('wanted', (int) $w['id'], ['status' => 'closed', 'closed_at' => now(), 'closed_by' => $actor['id'] ?? null, 'close_note' => $note]);
    audit_as($actor['id'] ?? null, 'wanted.close', 'wanted', (int) $w['id'], ($w['plate'] ?: $w['name']) . ': ' . $note . ' · ' . integration_label($integ, $actor));
    integration_wanted_event('wanted.closed', (int) $w['id']);
    return ['ok' => true];
}

function integration_do_verify(array $in, ?array $integ, ?array $actor): array
{
    return ['ok' => true] + document_verify_data(int_in($in, 'code', 32));
}

function integration_do_documents(array $in, ?array $integ, ?array $actor): array
{
    $cid = int_in($in, 'citizen_id', 64);
    if ($cid === '') {
        return ['ok' => false, 'error' => 'citizen_id fehlt'];
    }
    $ids = integration_readable_ids($integ);
    $rows = all('SELECT d.id, d.type, d.number, d.status, d.issued_at, d.valid_until, d.verify_code, d.faction_id FROM documents d JOIN patients p ON p.id = d.patient_id
        WHERE p.citizen_id = ? AND d.faction_id IN (' . implode(',', $ids) . ') ORDER BY d.issued_at DESC LIMIT ' . max(1, min(100, (int) (int_in($in, 'limit') ?: 50))), [$cid]);
    return ['ok' => true, 'documents' => array_map(fn($r) => ['id' => (int) $r['id'], 'type' => $r['type'], 'title' => doc_type($r['type'])['title'] ?? $r['type'], 'number' => $r['number'],
        'status' => $r['status'], 'issued_at' => $r['issued_at'], 'valid_until' => $r['valid_until'], 'verify_url' => verify_url($r['verify_code']),
        'faction' => faction_by_id((int) $r['faction_id'])['short'] ?? ''], $rows)];
}

function integration_do_duty(array $in, ?array $integ, ?array $actor): array
{
    $one = (int_in($in, 'faction') !== '' || int_in($in, 'faction_id') !== '') ? integration_faction($in, $integ, $actor) : null;
    if ((int_in($in, 'faction') !== '' || int_in($in, 'faction_id') !== '') && !$one) {
        return ['ok' => false, 'error' => 'Fraktion nicht gefunden oder nicht freigegeben.'];
    }
    $out = [];
    foreach ($one ? [(int) $one['id']] : integration_faction_ids($integ) as $id) {
        set_faction_context($id);
        $f = faction_by_id($id);
        foreach (duty_on_now() as $s) {
            $out[] = ['user_id' => (int) $s['user_id'], 'name' => user_display($s), 'badge' => (string) $s['badge_number'], 'rank' => user_rank_name($s),
                'faction' => (string) ($f['short'] ?: $f['name']), 'faction_id' => $id, 'since' => $s['started_at'], 'seconds' => duty_seconds($s),
                'fms' => $s['fms_status'] !== null && $s['fms_status'] !== '' ? (int) $s['fms_status'] : null];
        }
    }
    return ['ok' => true, 'count' => count($out), 'on_duty' => $out];
}

/** Konto über user_id, username oder discord_id (nur in freigegebenen Fraktionen). */
function integration_user(array $in, ?array $integ): ?array
{
    $u = match (true) {
        int_in($in, 'user_id') !== '' => one('SELECT * FROM users WHERE id = ? AND active = 1', [(int) int_in($in, 'user_id')]),
        int_in($in, 'username') !== '' => one('SELECT * FROM users WHERE username = ? AND active = 1', [int_in($in, 'username', 64)]),
        int_in($in, 'discord_id') !== '' => one("SELECT * FROM users WHERE discord_id = ? AND discord_id <> '' AND active = 1", [int_in($in, 'discord_id', 32)]),
        default => null,
    };
    return $u && in_array((int) $u['faction_id'], integration_faction_ids($integ), true) ? $u : null;
}

function integration_do_duty_set(array $in, ?array $integ, ?array $actor): array
{
    $u = integration_user($in, $integ) ?? ($integ === null && $actor && !isset($in['user_id']) && !isset($in['username']) && !isset($in['discord_id']) ? $actor : null);
    if (!$u) {
        return ['ok' => false, 'error' => 'Konto nicht gefunden (user_id, username oder discord_id) oder Fraktion nicht freigegeben.'];
    }
    set_faction_context((int) $u['faction_id']);
    if (!in_array('duty.clock', user_permissions($u), true)) {
        return ['ok' => false, 'error' => 'Dieses Konto darf keine Dienstzeiten stempeln.'];
    }
    duty_set((int) $u['id'], int_flag($in, 'on'), 'api', $actor['id'] ?? null);
    $open = duty_open_session((int) $u['id']);
    return ['ok' => true, 'user_id' => (int) $u['id'], 'on_duty' => (bool) $open, 'since' => $open['started_at'] ?? null];
}

function integration_do_entry_create(array $in, ?array $integ, ?array $actor): array
{
    $f = integration_faction($in, $integ, $actor);
    if (!$f || !module_enabled('records', (int) $f['id'])) {
        return ['ok' => false, 'error' => 'Fraktion fehlt, ist nicht freigegeben oder führt keine Akten.'];
    }
    set_faction_context((int) $f['id']);
    $fields = game_player_fields($in);
    $title = int_in($in, 'title');
    $content = int_in($in, 'content', 20000);
    if ($fields['citizen_id'] === '' || $title === '') {
        return ['ok' => false, 'error' => 'citizen_id und title angeben'];
    }
    $existed = (bool) game_record_find($fields['citizen_id']);
    $p = sky_record((int) $f['id'], $fields, int_flag($in, 'create'), $actor['id'] ?? null);
    if (!$p) {
        return ['ok' => false, 'error' => int_flag($in, 'create') ? 'Akte fehlt; zum Anlegen first_name und last_name mitsenden.' : 'Keine Akte zu dieser Bürger-ID (create: true legt sie an).'];
    }
    if ($p['archived_at']) {
        return ['ok' => false, 'error' => 'Die Akte ist archiviert.'];
    }
    $types = entry_types((string) ($f['kind'] ?? 'medical'));
    $type = int_in($in, 'type', 64);
    $type = in_array($type, $types, true) ? $type : (in_array('Sonstiges', $types, true) ? 'Sonstiges' : $types[0]);
    $id = sky_entry($p, $type, $title, rtrim($content) . "\n\n— " . integration_label($integ, $actor) . ', ' . fmt_datetime(now()), $actor['id'] ?? null);
    audit_as($actor['id'] ?? null, 'entry.create', 'patient', (int) $p['id'], $title . ' · ' . integration_label($integ, $actor));
    return ['ok' => true, 'entry_id' => $id, 'record_id' => (int) $p['id'], 'record_created' => !$existed, 'type' => $type];
}

function integration_do_alert(array $in, ?array $integ, ?array $actor): array
{
    $f = integration_faction($in, $integ, $actor);
    $title = int_in($in, 'title');
    if (!$f || $title === '') {
        return ['ok' => false, 'error' => $f ? 'title angeben' : 'Fraktion fehlt oder ist nicht freigegeben.'];
    }
    set_faction_context((int) $f['id']);
    $perm = int_in($in, 'permission', 64);
    $dutyOnly = int_flag($in, 'on_duty');
    $link = int_in($in, 'link', 300);
    $link = preg_match('#^(index\.php\?|https?://)#', $link) ? $link : '';
    $label = $integ ? $integ['name'] : ($actor ? user_display($actor) : 'Spielserver');
    $n = 0;
    foreach (all('SELECT * FROM users WHERE active = 1 AND faction_id = ?', [$f['id']]) as $u) {
        if (($perm !== '' && !in_array($perm, user_permissions($u), true)) || ($dutyOnly && !duty_open_session((int) $u['id'])) || ($actor && (int) $actor['id'] === (int) $u['id'])) {
            continue;
        }
        notify_user((int) $u['id'], 'integration', '📡 ' . $title, $link, int_in($in, 'body', 1000) . ($label !== '' ? "\n(" . $label . ')' : ''));
        $n++;
    }
    return ['ok' => true, 'notified' => $n];
}

function integration_do_request_create(array $in, ?array $integ, ?array $actor): array
{
    $from = integration_faction($in, $integ, $actor, 'from');
    $to = null;
    $want = int_in($in, 'to');
    foreach (factions() as $f) {
        if ($want !== '' && (ctype_digit($want) ? (int) $want === (int) $f['id'] : (strcasecmp($want, (string) $f['code']) === 0 || strcasecmp($want, (string) $f['short']) === 0))) {
            $to = $f;
        }
    }
    if (!$from || !module_enabled('requests', (int) $from['id'])) {
        return ['ok' => false, 'error' => 'Absender (from) fehlt, ist nicht freigegeben oder hat keine Behördenanfragen.'];
    }
    if (!$to || (int) $to['id'] === (int) $from['id'] || !module_enabled('requests', (int) $to['id'])) {
        return ['ok' => false, 'error' => 'Empfänger (to) nicht gefunden oder nimmt keine Anfragen an.'];
    }
    $row = ['faction_id' => (int) $from['id'], 'to_faction_id' => (int) $to['id'], 'subject' => int_in($in, 'subject'), 'body' => int_in($in, 'body', 10000),
        'citizen_id' => int_in($in, 'citizen_id', 64), 'person' => int_in($in, 'person'), 'reference' => int_in($in, 'reference'), 'urgent' => int_flag($in, 'urgent') ? 1 : 0,
        'status' => 'open', 'created_by' => $actor['id'] ?? null, 'created_at' => now()];
    if ($row['subject'] === '' || $row['body'] === '') {
        return ['ok' => false, 'error' => 'subject und body angeben'];
    }
    if (!$actor) {
        $row['body'] .= "\n\n— " . integration_label($integ, null);
    }
    set_faction_context((int) $from['id']);
    $rid = insert('agency_requests', $row);
    audit_as($actor['id'] ?? null, 'request.create', 'request', $rid, $row['subject'] . ' → ' . $to['name'] . ' · ' . integration_label($integ, $actor));
    set_faction_context((int) $to['id']);
    notify_users_with('requests.manage', 'request', ($row['urgent'] ? '❗ ' : '') . 'Anfrage von ' . ($from['short'] ?: $from['name']) . ': ' . $row['subject'], url('requests', ['id' => $rid]),
        fn($u) => (int) $u['faction_id'] === (int) $to['id']);
    integration_request_event('request.created', $rid);
    return ['ok' => true, 'id' => $rid];
}

function integration_do_laws(array $in, ?array $integ, ?array $actor): array
{
    $codes = law_codes();
    $want = int_in($in, 'code');
    $code = null;
    foreach ($codes as $c) {
        if ($want !== '' ? ((ctype_digit($want) && (int) $want === (int) $c['id']) || strcasecmp($want, (string) $c['name']) === 0) : ((int) $c['server_id'] === game_server_id())) {
            $code = $c;
            break;
        }
    }
    $code ??= $want === '' ? ($codes[0] ?? null) : null;
    $list = array_map(fn($c) => ['id' => (int) $c['id'], 'name' => $c['name'], 'server' => game_server_label((int) $c['server_id'] ?: null)], $codes);
    if (!$code) {
        return ['ok' => true, 'codes' => $list, 'code' => null, 'sections' => []];
    }
    $q = mb_strtolower(int_in($in, 'q', 100));
    $sections = array_values(array_filter(law_sections_flat($code), fn($s) => $q === '' || str_contains(mb_strtolower($s['ref'] . ' ' . $s['title']), $q)));
    $withBody = int_flag($in, 'body');
    return ['ok' => true, 'codes' => $list, 'code' => ['id' => (int) $code['id'], 'name' => $code['name']], 'sections' => array_map(fn($s) => ['ref' => $s['ref'], 'number' => $s['number'], 'book' => $s['short'],
        'title' => $s['title'], 'penalty' => $s['penalty'], 'jail_max' => $s['jail'], 'fine_max' => $s['fine'], 'mode' => $s['mode']] + ($withBody ? ['body' => $s['body']] : []),
        array_slice($sections, 0, max(1, min(2000, (int) (int_in($in, 'limit') ?: 500)))))];
}

function integration_do_events(array $in, ?array $integ, ?array $actor): array
{
    $since = (int) (int_in($in, 'since') ?: 0);
    if ($since < 0) {
        return ['ok' => true, 'last_id' => integration_last_event_id(), 'events' => []]; // Startwert holen
    }
    [$events, $last] = integration_events_for($integ, $since, max(1, min(500, (int) (int_in($in, 'limit') ?: 100))));
    return ['ok' => true, 'last_id' => $last, 'events' => $events];
}
