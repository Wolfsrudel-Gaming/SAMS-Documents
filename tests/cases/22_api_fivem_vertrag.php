<?php
// Vertrag der FiveM-API: FXServer (PerformHttpRequest) reicht bei Nicht-2xx-Antworten KEINEN Antworttext
// an die Resource weiter. Fachliche Ergebnisse und Fehler müssen deshalb mit HTTP 200 und Text im JSON kommen
// (ok:false + error bzw. status). Nur 401 (Schlüssel/Anmeldung ungültig) und 503 (Wartung/API aus) sind erlaubt;
// die Resource wertet 401 über den Statuscode aus. Hintergrund: Tablet zeigte sonst „nicht erreichbar“ statt 2FA-Abfrage.

// 1. Anmeldung: auch Fehlschläge antworten mit 200 und lesbarem Status
[$st, $r] = api('tablet_login', ['username' => 'gibt_es_nicht', 'password' => 'x', 'license' => 'license:vertrag', 'player' => 'Test']);
check($st === 200 && ($r['status'] ?? '') === 'invalid' && ($r['message'] ?? '') !== '', 'tablet_login: falsche Zugangsdaten → HTTP 200, status "invalid" mit Text');

// 2. Fachliche Fehler aller Aktionen: 200 mit ok:false und Text
$bad = 'ungueltig-' . bin2hex(random_bytes(4));
$cases = [
    'job_pickup' => ['job_id' => 999999, 'printer_id' => 999999, 'license' => 'license:vertrag', 'player' => 'Test'],
    'document_item' => ['token' => $bad],
    'rx_redeem' => ['token' => $bad, 'license' => 'license:vertrag', 'player' => 'Test'],
    'wanted_check' => ['token' => $bad, 'plate' => 'XX'],
    'person_check' => ['citizen_id' => ''],
    'bill_result' => ['id' => 999999, 'result' => 'paid'],
    'doc_sign' => ['token' => $bad, 'field' => 'x', 'signature' => ''],
    'doc_finalize' => ['token' => $bad],
];
foreach ($cases as $action => $body) {
    [$st, $r] = api($action, $body);
    check($st === 200 && ($r['ok'] ?? null) === false, "$action: fachlicher Fehler → HTTP 200 mit ok:false (war HTTP $st)");
}
foreach (['job_pickup', 'rx_redeem', 'wanted_check', 'person_check', 'bill_result', 'doc_sign', 'doc_finalize', 'document_item'] as $action) {
    [, $r] = api($action, $cases[$action]);
    check(is_string($r['error'] ?? null) && $r['error'] !== '', "$action: Fehlertext im JSON vorhanden");
}

// 3. Ungültige Anmeldung (Token) liefert bei jeder Aktion nur 200 oder 401 – niemals 403/404/409
foreach (['tablet_session', 'tablet_launch', 'tablet_logout', 'printer_jobs', 'duty_set', 'duty_status', 'rx_check', 'wanted_check', 'person_check', 'jobs_since', 'record_lookup', 'record_open', 'bills_sync', 'game_config', 'record_vitals', 'sky_death', 'sky_medication', 'tetra_status', 'dispatch_incidents'] as $action) {
    [$st] = api($action, ['token' => $bad, 'tokens' => [$bad], 'citizen_id' => 'GIBTSNICHT', 'since_id' => 0, 'printer_id' => 0]);
    check(in_array($st, [200, 401], true), "$action: nur HTTP 200 oder 401 erlaubt (war HTTP $st)");
}

// 4. Vorbeugung: im Quelltext keine neuen Fehlercodes einbauen
$src = (string) file_get_contents(dirname(__DIR__, 2) . '/public/api.php');
$bad_codes = [];
foreach (preg_split('/\R/', $src) as $n => $line) {
    if (preg_match('/\brespond\(.*,\s*(\d{3})\s*\)\s*;/', $line, $m) && !in_array((int) $m[1], [200, 401, 503], true) && !str_contains($line, 'Unbekannte Aktion')) {
        $bad_codes[] = 'Zeile ' . ($n + 1) . ': HTTP ' . $m[1];
    }
}
check($bad_codes === [], 'api.php: keine Fehlercodes außer 401/503 (FXServer verwirft sonst den Antworttext) ' . implode('; ', $bad_codes));
