<?php
/**
 * Kleine JSON-API, z.B. für eine FiveM-Resource (Tablet, /checkdoc-Befehl …).
 * Authentifizierung per Header "X-API-Key" oder Parameter "key" (siehe .env, API_KEY).
 *
 *   GET api.php?action=verify&code=XXXX          Dokument prüfen
 *   GET api.php?action=patient&citizen_id=ABC123  Notfalldaten eines Patienten
 *   GET api.php?action=documents&citizen_id=ABC123 Dokumentliste eines Patienten
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function respond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$key = (string) cfg('api_key', '');
$sent = (string) ($_SERVER['HTTP_X_API_KEY'] ?? ($_GET['key'] ?? ''));
if ($key === '') {
    respond(['error' => 'API deaktiviert'], 503);
}
if (!hash_equals($key, $sent)) {
    respond(['error' => 'Ungültiger API-Schlüssel'], 401);
}

$action = (string) ($_GET['action'] ?? '');
switch ($action) {
    case 'verify':
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_GET['code'] ?? '')));
        $doc = one('SELECT * FROM documents WHERE verify_code = ?', [$code]);
        if (!$doc) {
            respond(['found' => false]);
        }
        $issuer = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
        $patient = json_decode((string) $doc['patient_snapshot'], true) ?: [];
        respond([
            'found'       => true,
            'status'      => $doc['status'],
            'expired'     => $doc['valid_until'] !== '' && $doc['valid_until'] < date('Y-m-d'),
            'type'        => doc_type($doc['type'])['title'] ?? $doc['type'],
            'number'      => $doc['number'],
            'issued_at'   => $doc['issued_at'],
            'valid_until' => $doc['valid_until'],
            'issuer'      => user_display($issuer),
            'patient'     => $patient ? patient_name($patient) : null,
            'citizen_id'  => $patient['citizen_id'] ?? null,
            'url'         => verify_url($doc['verify_code']),
        ]);
        // no break
    case 'patient':
        $p = one('SELECT * FROM patients WHERE citizen_id = ? AND citizen_id <> ?', [(string) ($_GET['citizen_id'] ?? ''), '']);
        if (!$p) {
            respond(['found' => false]);
        }
        respond([
            'found'       => true,
            'name'        => patient_name($p),
            'dob'         => $p['dob'],
            'blood_type'  => $p['blood_type'],
            'allergies'   => $p['allergies'],
            'conditions'  => $p['conditions'],
            'medication'  => $p['medication'],
            'organ_donor' => $p['organ_donor'],
            'living_will' => (bool) $p['living_will'],
            'emergency_contact' => $p['emergency_contact'],
            'deceased'    => $p['deceased_at'] !== '',
        ]);
        // no break
    case 'documents':
        $p = one('SELECT id FROM patients WHERE citizen_id = ? AND citizen_id <> ?', [(string) ($_GET['citizen_id'] ?? ''), '']);
        if (!$p) {
            respond(['found' => false]);
        }
        $rows = all('SELECT type, number, status, issued_at, valid_until, verify_code FROM documents WHERE patient_id = ? ORDER BY issued_at DESC LIMIT 50', [$p['id']]);
        respond(['found' => true, 'documents' => array_map(fn($r) => [
            'type' => doc_type($r['type'])['title'] ?? $r['type'],
            'number' => $r['number'],
            'status' => $r['status'],
            'issued_at' => $r['issued_at'],
            'valid_until' => $r['valid_until'],
            'url' => verify_url($r['verify_code']),
        ], $rows)]);
        // no break
    default:
        respond(['error' => 'Unbekannte Aktion', 'actions' => ['verify', 'patient', 'documents']], 400);
}
