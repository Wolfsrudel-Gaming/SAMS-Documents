<?php
// Andock-Schnittstelle: Schlüssel mit Rechten/Fraktionen, Aufrufe, keine medizinischen Daten, Ereignisse, Webhook mit Signatur, Lua-Weg über api.php
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
$int = function (string $action, array $body = [], string $token = '') {
    $ch = curl_init(TEST_BASE . '/integration.php?action=' . $action);
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $token !== '' ? ['Authorization: Bearer ' . $token] : [])]);
    $res = (string) curl_exec($ch);
    $st = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    return [$st, json_decode($res, true) ?: []];
};

// Empfänger für den Webhook (eigener kleiner Server, schreibt jede Zustellung in eine Datei)
$hookDir = dirname(TEST_DB) . '/hook';
@mkdir($hookDir, 0777, true);
file_put_contents("$hookDir/index.php", '<?php file_put_contents(__DIR__ . "/got.jsonl", json_encode(["sig" => $_SERVER["HTTP_X_SAMS_SIGNATURE"] ?? "", "ts" => $_SERVER["HTTP_X_SAMS_TIMESTAMP"] ?? "", "body" => file_get_contents("php://input")]) . "\n", FILE_APPEND); http_response_code(204);');
$hookPort = (int) parse_url(TEST_BASE, PHP_URL_PORT) + 1500;
$hook = proc_open([PHP_BINARY, '-S', "127.0.0.1:$hookPort", '-t', $hookDir], [1 => ['file', '/dev/null', 'a'], 2 => ['file', '/dev/null', 'a']], $pipes);
usleep(400000);

$admin = (new Client(TEST_BASE))->login('admin');
check($admin->get('/index.php?p=integrations')->status === 200 && $admin->has('Neue Schnittstelle'), 'Adminseite Schnittstellen');
check((new Client(TEST_BASE))->login('officer')->get('/index.php?p=integrations')->status === 403, 'Nur für den Hauptadministrator');
$admin->post('/index.php?p=integrations', ['action' => 'create', 'name' => 'Blitzer-Bot', 'notes' => 'Test',
    'scopes[]' => ['persons.read', 'wanted.read', 'wanted.write', 'documents.read', 'duty.read', 'entries.write', 'alerts.write', 'laws.read', 'events.read'],
    'factions[]' => [(string) $pd], 'webhook_url' => "http://127.0.0.1:$hookPort/", 'event_filter' => '']);
$token = (string) $admin->match('/(samsi_[0-9a-f]{48})/');
$row = trow("SELECT * FROM integrations WHERE name = 'Blitzer-Bot'");
check($token !== '' && $row && $row['token_hash'] === hash('sha256', $token) && !str_contains(json_encode($row), $token), 'Schlüssel einmal angezeigt, nur als Hash gespeichert');
check(!$admin->get('/index.php?p=integrations')->has($token), 'Beim nächsten Aufruf nicht mehr sichtbar');

[$st] = $int('ping', [], 'samsi_falsch' . str_repeat('0', 40));
check($st === 401, 'Falscher Schlüssel: 401');
[$st, $r] = $int('ping', [], $token);
check($st === 200 && $r['ok'] && $r['name'] === 'Blitzer-Bot' && count($r['factions']) === 1 && $r['factions'][0]['short'] === 'LSPD', 'ping: Name, Rechte, nur freigegebene Fraktion');
[$st, $r] = $int('request_create', ['from' => 'LSPD', 'to' => '1', 'subject' => 'x', 'body' => 'y'], $token);
check($st === 200 && $r['ok'] === false && ($r['scope'] ?? '') === 'requests.write', 'Ohne Recht kein Aufruf (HTTP 200 mit Fehler)');
[, $r] = $int('gibtsnicht', [], $token);
check($r['ok'] === false && in_array('person', $r['actions'] ?? [], true), 'Unbekannter Aufruf listet die möglichen');

// Akteneintrag: fremde Fraktion gesperrt, eigene mit Anlage der Akte
[, $r] = $int('entry_create', ['faction' => '1', 'citizen_id' => 'TST001', 'title' => 'Hack'], $token);
check($r['ok'] === false && !tval("SELECT id FROM entries WHERE title = 'Hack'"), 'Keine Einträge in nicht freigegebene Fraktionen');
[, $r] = $int('entry_create', ['citizen_id' => 'INT555', 'title' => 'Geschwindigkeitsverstoß', 'content' => '142 km/h auf der A1', 'type' => 'Verkehrskontrolle',
    'create' => true, 'first_name' => 'Rasant', 'last_name' => 'Raser', 'dob' => '01/02/1991', 'sex' => 'm'], $token);
$e = $r['ok'] ? trow('SELECT * FROM entries WHERE id = ?', [$r['entry_id']]) : null;
check($r['ok'] && $r['record_created'] === true && $e && (int) $e['faction_id'] === $pd && $e['type'] === 'Verkehrskontrolle' && str_contains($e['content'], 'Schnittstelle „Blitzer-Bot“'),
    'Akteneintrag in der einzigen freigegebenen Fraktion, Akte angelegt');
check(tval("SELECT dob FROM patients WHERE citizen_id = 'INT555'") === '1991-02-01', 'Spielerdaten (TT/MM/JJJJ) übernommen');

// Personenabfrage: nie medizinische Daten
[, $r] = $int('person', ['citizen_id' => 'TST001'], $token);
$json = json_encode($r);
check($r['ok'] && $r['records'] && !array_filter($r['records'], fn($x) => $x['faction']['kind'] === 'medical'), 'Personenabfrage nur mit Akten der freigegebenen Behörde');
check(!str_contains($json, 'allergies') && !str_contains($json, 'blood_type') && !str_contains($json, 'conditions'), 'Keine Notfall-/Medizindaten');
[, $r] = $int('documents', ['citizen_id' => 'TST001'], $token);
check($r['ok'] && !array_filter($r['documents'], fn($d) => (int) tval('SELECT faction_id FROM documents WHERE id = ?', [$d['id']]) === 1), 'Dokumentliste ohne Rettungsdienst');
$code = (string) tval("SELECT verify_code FROM documents WHERE status = 'valid' ORDER BY id LIMIT 1");
[, $r] = $int('verify', ['code' => $code], $token);
check($r['ok'] && $r['found'] === true, 'Echtheitsprüfung');

// Fahndung anlegen, abfragen, erledigen
[, $r] = $int('wanted_create', ['kind' => 'vehicle', 'plate' => 'int 42', 'vehicle' => 'Roter Banshee', 'reason' => 'Blitzer A1: 142 km/h', 'danger' => 1], $token);
$wid = (int) ($r['id'] ?? 0);
check($r['ok'] && (int) tval('SELECT faction_id FROM wanted WHERE id = ?', [$wid]) === $pd, 'Fahndung über die Schnittstelle');
[, $r] = $int('wanted', ['plate' => 'INT42'], $token);
check($r['ok'] && $r['wanted'] === true && $r['entries'][0]['vehicle'] === 'Roter Banshee', 'Fahndungsabfrage nach Kennzeichen');
[, $r] = $int('wanted_close', ['id' => $wid, 'note' => 'angehalten'], $token);
check($r['ok'] && tval('SELECT status FROM wanted WHERE id = ?', [$wid]) === 'closed' && str_contains((string) tval("SELECT details FROM audit_log WHERE action = 'wanted.close' ORDER BY id DESC LIMIT 1"), 'Blitzer-Bot'),
    'Fahndung erledigt, im Protokoll mit Schnittstelle');

// Benachrichtigung, Dienst, Gesetze
[, $r] = $int('alert', ['title' => 'Blitzer ausgelöst', 'body' => 'INT42', 'permission' => 'wanted.view'], $token);
check($r['ok'] && $r['notified'] >= 1 && tval("SELECT COUNT(*) FROM notifications n JOIN users u ON u.id = n.user_id WHERE n.kind = 'integration' AND u.faction_id <> ?", [$pd]) == 0
    && tval("SELECT COUNT(*) FROM notifications n JOIN users u ON u.id = n.user_id WHERE n.kind = 'integration' AND u.username = 'officer'") >= 1, 'Meldung nur an die Polizei');
[, $r] = $int('duty', [], $token);
check($r['ok'] && is_array($r['on_duty']) && !array_filter($r['on_duty'], fn($u) => $u['faction_id'] !== $pd), 'Dienstliste nur der freigegebenen Fraktion');
[, $r] = $int('duty_set', ['username' => 'officer', 'on' => true], $token);
check($r['ok'] === false && str_contains($r['scope'] ?? '', 'duty.write'), 'Dienst stempeln braucht eigenes Recht');
[, $r] = $int('laws', [], $token);
check($r['ok'] && is_array($r['codes']) && isset($r['sections']), 'Gesetzbuch lesbar');

// Ereignisse: nur sichtbare Fraktionen, keine Personendaten bei Rettungsdienst
[, $r] = $int('events', ['since' => 0, 'limit' => 500], $token);
$names = array_column($r['events'], 'event');
check($r['ok'] && in_array('wanted.created', $names, true) && in_array('wanted.closed', $names, true)
    && !array_filter($r['events'], fn($ev) => $ev['faction_id'] !== null && $ev['faction_id'] !== $pd), 'Ereignisse nur der freigegebenen Fraktion');
[, $all] = api('ext', ['call' => 'events', 'params' => ['since' => 0, 'limit' => 500]]);
$med = array_filter($all['events'] ?? [], fn($ev) => $ev['event'] === 'document.issued' && $ev['faction_id'] === 1);
check($all['ok'] && $med && !array_filter($med, fn($ev) => isset($ev['data']['citizen_id']) || isset($ev['data']['person'])), 'Spielserver sieht alles, Rettungsdienst-Dokumente ohne Personendaten');
[, $s] = api('ext', ['call' => 'events', 'params' => ['since' => -1]]);
check($s['ok'] && $s['last_id'] >= max(array_column($all['events'], 'id')) && $s['events'] === [], 'Startposition für das Relay im Spiel');

// Lua-Weg: api.php?action=ext mit Spieler als Urheber
[, $login] = api('tablet_login', ['username' => 'officer', 'password' => 'TestPasswort123', 'license' => 'license:off1', 'player' => 'Olli']);
[, $r] = api('ext', ['call' => 'entry_create', 'token' => $login['token'] ?? '', 'params' => ['citizen_id' => 'INT555', 'title' => 'Kontrolle vor Ort', 'content' => 'Papiere ok']]);
$e = !empty($r['entry_id']) ? trow('SELECT * FROM entries WHERE id = ?', [$r['entry_id']]) : null;
check($r['ok'] && $e && (int) $e['created_by'] === (int) tval("SELECT id FROM users WHERE username = 'officer'") && (int) $e['faction_id'] === $pd, 'Im Spiel: Eintrag mit Spieler als Urheber in seiner Fraktion');
[, $r] = api('ext', ['call' => 'duty_set', 'token' => $login['token'] ?? '', 'params' => ['on' => true]]);
check($r['ok'] && $r['on_duty'] === true, 'Im Spiel: Dienst über die Schnittstelle');
[, $r] = $int('events', ['since' => (int) $s['last_id']], $token);
check(in_array('duty.changed', array_column($r['events'], 'event'), true), 'Dienstbeginn als Ereignis');
api('ext', ['call' => 'duty_set', 'token' => $login['token'] ?? '', 'params' => ['on' => false]]);
[$st, $r] = api('ext', ['call' => 'person', 'params' => ['citizen_id' => 'TST001']], 'falsch');
check($st === 401, 'Ohne Server-Schlüssel kein Zugriff');

// Webhook: Zustellung mit gültiger Signatur
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1');
$secret = (string) tval("SELECT webhook_secret FROM integrations WHERE name = 'Blitzer-Bot'");
$got = array_values(array_filter(array_map(fn($l) => json_decode($l, true), file(is_file("$hookDir/got.jsonl") ? "$hookDir/got.jsonl" : '/dev/null') ?: [])));
$events = array_merge(...array_map(fn($g) => json_decode($g['body'], true)['events'] ?? [], $got ?: [['body' => '{}']]));
check($got && !array_filter($got, fn($g) => !hash_equals('sha256=' . hash_hmac('sha256', $g['ts'] . '.' . $g['body'], $secret), $g['sig'])), 'Webhook signiert (HMAC-SHA256)');
check(in_array('wanted.created', array_column($events, 'event'), true) && !array_filter($events, fn($ev) => $ev['faction_id'] !== null && $ev['faction_id'] !== $pd), 'Webhook mit den Ereignissen der Fraktion');
check((int) tval("SELECT last_event_id FROM integrations WHERE name = 'Blitzer-Bot'") >= (int) max(array_column($events, 'id') ?: [0]) && tval("SELECT webhook_error FROM integrations WHERE name = 'Blitzer-Bot'") === '', 'Zustellstand gespeichert');
$admin->post('/index.php?p=integrations', ['action' => 'test', 'id' => (string) $row['id']]);
check($admin->has('Testereignis zugestellt'), 'Testereignis aus der Adminseite');
proc_terminate($hook);

// Sperren
$admin->post('/index.php?p=integrations', ['action' => 'save', 'id' => (string) $row['id'], 'name' => 'Blitzer-Bot', 'scopes[]' => ['persons.read'], 'factions[]' => [(string) $pd], 'webhook_url' => '']);
[$st] = $int('ping', [], $token);
check($st === 401, 'Gesperrter Schlüssel: 401');
