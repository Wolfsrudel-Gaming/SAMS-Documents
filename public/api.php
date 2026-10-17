<?php
/**
 * JSON-API für die FiveM-Resource (sams_tablet) und andere Server-Skripte.
 * Authentifizierung: Header "X-API-Key" (Wert API_KEY aus der .env).
 * Der Schlüssel gehört ausschließlich auf den Spielserver, nie in den Client.
 *
 * Abfragen (GET):
 *   ?action=verify&code=XXXX                 Dokument prüfen
 *   ?action=patient&citizen_id=ABC123         Notfalldaten eines Patienten
 *   ?action=documents&citizen_id=ABC123       Dokumentliste eines Patienten
 *   ?action=printers                          Aktive Drucker (Positionen, Props)
 *
 * Aktionen (POST, JSON-Body):
 *   tablet_login   {username, password, code?, license, player}   → token
 *   tablet_session {token}                                         → Benutzer
 *   tablet_launch  {token}                                         → Einmal-URL für das Tablet
 *   tablet_logout  {token}
 *   printer_jobs   {printer_id, token?}                            → abholbereite Aufträge
 *   job_pickup     {job_id, printer_id, token?, license, player}   → Items für ox_inventory
 *   jobs_since     {since_id}  (-1 = Startwert holen)              → neue Aufträge (für Benachrichtigungen)
 *   document_item  {token}                                         → Ansichts-URL eines Dokument-Items
 *   duty_set       {token, on}                                     → Dienst beginnen/beenden
 *   duty_status    {token}                                         → im Dienst? seit wann?
 *   rx_check       {tokens: [sams_token, …]}                       → Rezepte mit Positionen und Einlösbarkeit
 *   rx_redeem      {token, license, player}                        → Rezept einlösen, Items für ox_inventory
 */
declare(strict_types=1);
define('SAMS_IGNORE_MAINTENANCE', true);
require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (in_maintenance()) {
    respond(['error' => 'maintenance', 'message' => 'Das System wird gerade aktualisiert.'], 503);
}
$key = (string) cfg('api_key', '');
$sent = (string) ($_SERVER['HTTP_X_API_KEY'] ?? ($_GET['key'] ?? ''));
if ($key === '') {
    respond(['error' => 'API deaktiviert (API_KEY in der .env setzen)'], 503);
}
if (!hash_equals($key, $sent)) {
    respond(['error' => 'Ungültiger API-Schlüssel'], 401);
}

$body = [];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $body = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($body)) {
        $body = $_POST;
    }
}
$in = fn(string $k, $d = '') => is_scalar($body[$k] ?? null) ? trim((string) $body[$k]) : (is_scalar($_GET[$k] ?? null) ? trim((string) $_GET[$k]) : $d);
$action = (string) ($_GET['action'] ?? ($body['action'] ?? ''));

/** Benutzer zum Tablet-Token (oder null). */
$tokenUser = function () use ($in): ?array {
    $t = $in('token');
    return $t !== '' ? tablet_token_user($t) : null;
};

switch ($action) {
    // ------------------------------------------------------------ Bestand
    case 'verify':
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $in('code')));
        $doc = one('SELECT * FROM documents WHERE verify_code = ?', [$code]);
        if (!$doc) {
            respond(['found' => false]);
        }
        $issuer = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
        $patient = json_decode((string) $doc['patient_snapshot'], true) ?: [];
        respond([
            'found' => true, 'status' => $doc['status'],
            'expired' => $doc['valid_until'] !== '' && $doc['valid_until'] < date('Y-m-d'),
            'type' => doc_type($doc['type'])['title'] ?? $doc['type'], 'number' => $doc['number'],
            'issued_at' => $doc['issued_at'], 'valid_until' => $doc['valid_until'],
            'issuer' => user_display($issuer), 'patient' => $patient ? patient_name($patient) : null,
            'citizen_id' => $patient['citizen_id'] ?? null, 'url' => verify_url($doc['verify_code']),
        ]);
        // no break
    case 'patient':
        $p = one("SELECT * FROM patients WHERE citizen_id = ? AND citizen_id <> ''", [$in('citizen_id')]);
        if (!$p) {
            respond(['found' => false]);
        }
        respond([
            'found' => true, 'name' => patient_name($p), 'dob' => $p['dob'], 'blood_type' => $p['blood_type'],
            'allergies' => $p['allergies'], 'conditions' => $p['conditions'], 'medication' => $p['medication'],
            'organ_donor' => $p['organ_donor'], 'living_will' => (bool) $p['living_will'],
            'emergency_contact' => $p['emergency_contact'], 'deceased' => $p['deceased_at'] !== '',
        ]);
        // no break
    case 'documents':
        $p = one("SELECT id FROM patients WHERE citizen_id = ? AND citizen_id <> ''", [$in('citizen_id')]);
        if (!$p) {
            respond(['found' => false]);
        }
        $rows = all('SELECT type, number, status, issued_at, valid_until, verify_code FROM documents WHERE patient_id = ? ORDER BY issued_at DESC LIMIT 50', [$p['id']]);
        respond(['found' => true, 'documents' => array_map(fn($r) => [
            'type' => doc_type($r['type'])['title'] ?? $r['type'], 'number' => $r['number'], 'status' => $r['status'],
            'issued_at' => $r['issued_at'], 'valid_until' => $r['valid_until'], 'url' => verify_url($r['verify_code']),
        ], $rows)]);
        // no break

    // ------------------------------------------------------------- Tablet
    case 'tablet_login':
        $res = tablet_login($in('username'), (string) ($body['password'] ?? ''), $in('code'), $in('license'), $in('player'));
        respond($res, $res['status'] === 'ok' ? 200 : 403);
        // no break
    case 'tablet_session':
        $tu = $tokenUser();
        respond($tu ? ['ok' => true, 'user' => tablet_user_info($tu[1])] : ['ok' => false], $tu ? 200 : 401);
        // no break
    case 'tablet_launch':
        $tu = $tokenUser();
        if (!$tu) {
            respond(['ok' => false, 'error' => 'Anmeldung abgelaufen'], 401);
        }
        respond(['ok' => true, 'url' => tablet_launch_url($tu[0]), 'origin' => preg_replace('#^(https?://[^/]+).*$#', '$1', base_url())]);
        // no break
    case 'tablet_logout':
        $t = $in('token');
        if ($t !== '') {
            q('UPDATE tablet_tokens SET revoked = 1 WHERE token_hash = ?', [hash('sha256', $t)]);
        }
        respond(['ok' => true]);
        // no break

    // ------------------------------------------------------------ Drucker
    case 'printers':
        respond(['printers' => array_map('printer_payload', active_printers()), 'pickup' => pickup_rule()]);
        // no break
    case 'printer_jobs':
        $tu = $tokenUser();
        $jobs = printer_jobs_for((int) $in('printer_id'), $tu[1] ?? null);
        respond([
            'ok' => true, 'logged_in' => (bool) $tu, 'pickup' => pickup_rule(),
            'jobs' => array_map(fn($j) => [
                'id' => (int) $j['id'], 'label' => job_label($j), 'number' => $j['number'], 'copies' => (int) $j['copies'],
                'requester' => (string) $j['requester'], 'requested_at' => fmt_datetime($j['requested_at']),
                'revoked' => $j['doc_status'] === 'revoked',
            ], $jobs),
        ]);
        // no break
    case 'job_pickup':
        $tu = $tokenUser();
        $jobId = (int) $in('job_id');
        $allowed = array_column(printer_jobs_for((int) $in('printer_id'), $tu[1] ?? null), null, 'id');
        if (!isset($allowed[$jobId])) {
            respond(['ok' => false, 'error' => 'Dieser Auftrag ist nicht (mehr) abholbereit.'], 404);
        }
        // Gleichzeitiges Abholen verhindern: nur wer den Status ändert, bekommt das Dokument
        $st = q("UPDATE print_jobs SET status = 'printed', picked_up_at = ?, picked_up_by = ?, picked_up_license = ? WHERE id = ? AND status = 'queued'",
            [now(), mb_substr($in('player'), 0, 190), mb_substr($in('license'), 0, 190), $jobId]);
        if ($st->rowCount() !== 1) {
            respond(['ok' => false, 'error' => 'Der Auftrag wurde bereits abgeholt.'], 409);
        }
        $job = $allowed[$jobId];
        $doc = one('SELECT * FROM documents WHERE id = ?', [$job['document_id']]);
        audit_as($tu ? (int) $tu[1]['id'] : null, 'document.printed', 'document', (int) $doc['id'],
            $doc['number'] . ' am Drucker abgeholt von ' . $in('player') . ' (' . $job['copies'] . ' Expl.)');
        respond(['ok' => true, 'count' => (int) $job['copies'], 'metadata' => document_item_metadata($doc, (string) $job['copy_variant'])]);
        // no break
    case 'jobs_since':
        $since = (int) $in('since_id', '-1');
        if ($since < 0) {
            // Start: aktuellen Stand liefern, alte Aufträge nicht melden
            respond(['ok' => true, 'last_id' => (int) scalar('SELECT COALESCE(MAX(id), 0) FROM print_jobs'), 'jobs' => []]);
        }
        $rows = all("SELECT j.id, j.requested_by, j.printer_id, p.name AS printer, p.x, p.y, p.z, d.type, d.number, d.patient_snapshot, j.copy_variant
            FROM print_jobs j JOIN printers p ON p.id = j.printer_id JOIN documents d ON d.id = j.document_id
            WHERE j.id > ? AND j.status = 'queued' ORDER BY j.id LIMIT 50", [$since]);
        respond(['ok' => true, 'last_id' => $rows ? (int) end($rows)['id'] : $since, 'jobs' => array_map(fn($r) => [
            'id' => (int) $r['id'], 'user_id' => (int) $r['requested_by'], 'printer_id' => (int) $r['printer_id'],
            'printer' => $r['printer'], 'x' => (float) $r['x'], 'y' => (float) $r['y'], 'z' => (float) $r['z'], 'label' => job_label($r),
        ], $rows)]);
        // no break
    case 'document_item':
        $doc = one('SELECT number, status FROM documents WHERE view_token = ?', [$in('token')]);
        if (!$doc || $in('token') === '') {
            respond(['ok' => false], 404);
        }
        respond(['ok' => true, 'number' => $doc['number'], 'revoked' => $doc['status'] === 'revoked', 'url' => document_view_url($in('token'), $in('variant'))]);
        // no break

    // -------------------------------------------------------- Dienstzeit
    case 'duty_set':
    case 'duty_status':
        $tu = $tokenUser();
        if (!$tu) {
            respond(['ok' => false, 'error' => 'Anmeldung abgelaufen'], 401);
        }
        if (!in_array('duty.clock', user_permissions($tu[1]), true)) {
            respond(['ok' => false, 'error' => 'Dein Rang darf keine Dienstzeiten stempeln.'], 403);
        }
        if ($action === 'duty_set') {
            duty_set((int) $tu[1]['id'], in_array(strtolower($in('on')), ['1', 'true', 'on', 'yes'], true), 'game');
        }
        $open = duty_open_session((int) $tu[1]['id']);
        respond(['ok' => true, 'on_duty' => (bool) $open, 'since' => $open['started_at'] ?? null, 'seconds' => $open ? duty_seconds($open) : 0]);
        // no break

    // --------------------------------------------------------- Apotheke
    case 'rx_check':
        $tokens = array_slice(array_values(array_filter((array) ($body['tokens'] ?? []), fn($t) => is_string($t) && strlen($t) >= 32)), 0, 20);
        $out = [];
        foreach ($tokens as $t) {
            $doc = one("SELECT * FROM documents WHERE view_token = ? AND type = 'rezept'", [$t]);
            if (!$doc) {
                continue;
            }
            $p = json_decode((string) $doc['patient_snapshot'], true) ?: [];
            $out[] = [
                'token' => $t, 'number' => $doc['number'], 'issued_at' => fmt_date($doc['issued_at']),
                'patient' => trim(mb_substr((string) ($p['first_name'] ?? ''), 0, 1) . '. ' . ($p['last_name'] ?? '')),
                'problem' => rx_problem($doc),
                'lines' => array_map(fn($l) => ['text' => $l['text'], 'status' => $l['status'], 'label' => RX_STATUS[$l['status']],
                    'item' => $l['med']['item_name'] ?? '', 'count' => $l['qty'] * max(1, (int) ($l['med']['item_count'] ?? 1))], rx_lines($doc, true)),
            ];
        }
        respond(['ok' => true, 'prescriptions' => $out]);
        // no break
    case 'rx_redeem':
        $doc = $in('token') !== '' ? one("SELECT * FROM documents WHERE view_token = ? AND type = 'rezept'", [$in('token')]) : null;
        if (!$doc) {
            respond(['ok' => false, 'error' => 'Rezept nicht gefunden.'], 404);
        }
        if ($problem = rx_problem($doc)) {
            respond(['ok' => false, 'error' => $problem], 409);
        }
        [$given, $notes] = rx_redeem($doc, 'game', null, $in('player'), $in('license'));
        if (!$given) {
            respond(['ok' => false, 'error' => $notes ? implode(' · ', $notes) : 'Alle Positionen sind bereits eingelöst.', 'notes' => $notes], 409);
        }
        respond(['ok' => true, 'number' => $doc['number'], 'notes' => $notes,
            'items' => array_map(fn($g) => ['item' => $g['item'], 'count' => $g['count'], 'name' => $g['name']], $given)]);
        // no break

    default:
        respond(['error' => 'Unbekannte Aktion'], 400);
}
