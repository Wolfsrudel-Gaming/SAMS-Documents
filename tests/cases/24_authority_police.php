<?php
// Polizei-Werkzeuge: Asservaten, Fahrzeugregister, Haftbuch, Strafregister
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
// Test 20 hat die Module der Polizei ausdrücklich gesetzt: neue Werkzeuge wie bei einer frischen Polizei einschalten
texec('UPDATE factions SET modules = ? WHERE id = ?', [json_encode(['records', 'billing', 'wanted', 'requests', 'evidence', 'vehicles', 'custody', 'licenses', 'joint']), $pd]);
$officer = (new Client(TEST_BASE))->login('officer');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $officer->get('/index.php?p=dashboard')->body, $mm) ? $mm[1] : '';
check(str_contains($nav, 'Asservaten') && str_contains($nav, 'Fahrzeugregister') && str_contains($nav, 'Haftbuch'), 'Polizei-Menü mit Asservaten, Fahrzeugregister, Haftbuch');
check((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=evidence')->status === 403, 'Rettungsdienst hat keine Asservaten');

// Person mit Akte
$officer->get('/index.php?p=patient_edit');
$officer->post('/index.php?p=patient_edit', ['first_name' => 'Rico', 'last_name' => 'Raser', 'dob' => '1992-02-02', 'gender' => 'm', 'citizen_id' => 'TST777']);
$rid = (int) tval('SELECT id FROM patients WHERE faction_id = ? AND citizen_id = ?', [$pd, 'TST777']);
check($rid > 0, 'Personenakte angelegt');

// Asservaten
$officer->get('/index.php?p=evidence');
$officer->post('/index.php?p=evidence', ['title' => 'Pistole 9 mm', 'category' => 'Waffe', 'quantity' => '1 Stück', 'storage' => 'Waffenschrank', 'patient_id' => (string) $rid, 'found_location' => 'Grove Street']);
$ev = trow('SELECT * FROM evidence WHERE faction_id = ? ORDER BY id DESC LIMIT 1', [$pd]);
check($ev && preg_match('/^AS-\d{4}-0001$/', $ev['number']) && $ev['status'] === 'stored' && $ev['owner'] === 'Rico Raser', 'Asservat mit Nummer erfasst');
$officer->post('/index.php?p=evidence_item&id=' . $ev['id'], ['action' => 'returned', 'counterpart' => '']);
check($officer->has('an wen übergeben'), 'Herausgabe nur mit Empfänger');
$officer->post('/index.php?p=evidence_item&id=' . $ev['id'], ['action' => 'handed_over', 'counterpart' => 'DOJ Asservatenstelle', 'note' => 'Anordnung StA']);
$officer->post('/index.php?p=evidence_item&id=' . $ev['id'], ['action' => 'destroyed', 'note' => 'x']);
check(tval('SELECT status FROM evidence WHERE id = ?', [$ev['id']]) === 'handed_over', 'Vernichten nach Übergabe nicht möglich');
check((int) tval('SELECT COUNT(*) FROM evidence_moves WHERE evidence_id = ?', [$ev['id']]) === 2 && $officer->get('/index.php?p=evidence_item&id=' . $ev['id'])->has('DOJ Asservatenstelle'), 'Beweismittelkette lückenlos');
check((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=evidence_item&id=' . $ev['id'])->status === 403, 'Asservat für andere Fraktionen gesperrt');

// Fahrzeugregister + Kennzeichenabfrage
$officer->get('/index.php?p=vehicles');
$officer->post('/index.php?p=vehicles', ['plate' => '77 rr 777', 'model' => 'Pegassi Zentorno', 'color' => 'gelb', 'patient_id' => (string) $rid, 'status' => 'stolen', 'valid_until' => date('Y-m-d', strtotime('-1 day'))]);
$v = trow("SELECT * FROM vehicles WHERE plate = '77RR777'");
check($v && $v['owner_citizen_id'] === 'TST777' && (int) $v['faction_id'] === $pd, 'Fahrzeug mit Halter aus der Akte registriert');
$officer->post('/index.php?p=vehicles', ['plate' => '77RR777', 'model' => 'Doppelt']);
check($officer->has('bereits registriert'), 'Kennzeichen nicht doppelt');
[$st, $r] = api('wanted_check', ['plate' => '77RR777']);
check($st === 200 && $r['wanted'] === true && ($r['registry'][0]['owner'] ?? '') === 'Rico Raser' && $r['registry'][0]['expired'] === true && count($r['flagged']) === 1, 'Kennzeichenabfrage: Halter, abgelaufen, gestohlen');
check($officer->get('/index.php?p=patient&id=' . $rid)->has('77RR777'), 'Fahrzeug in der Personenakte');

// Haftbuch: Aufnahme schließt eigene Fahndung, Anfrage an den Rettungsdienst
$officer->get('/index.php?p=wanted');
$officer->post('/index.php?p=wanted', ['action' => 'create', 'kind' => 'person', 'patient_id' => (string) $rid, 'reason' => 'Raserei', 'danger' => '1']);
$officer->get('/index.php?p=custody');
$officer->post('/index.php?p=custody', ['action' => 'intake', 'patient_id' => (string) $rid, 'reason' => 'Gefährlicher Eingriff in den Straßenverkehr', 'jail_units' => '20',
    'cell' => 'Zelle 2', 'belongings' => 'Handy', 'close_wanted' => '1', 'request_medical' => '1']);
$c = trow('SELECT * FROM custody WHERE faction_id = ? ORDER BY id DESC LIMIT 1', [$pd]);
check($c && $c['status'] === 'in' && $c['citizen_id'] === 'TST777' && (int) $c['jail_units'] === 20, 'In Gewahrsam genommen');
check(!tval("SELECT COUNT(*) FROM wanted WHERE faction_id = ? AND citizen_id = 'TST777' AND status = 'open'", [$pd]), 'Fahndung als vollstreckt geschlossen');
check((int) tval("SELECT COUNT(*) FROM agency_requests WHERE faction_id = ? AND to_faction_id = 1 AND subject LIKE 'Haftfähigkeit%'", [$pd]) === 1, 'Haftfähigkeit beim Rettungsdienst angefragt');
check($officer->has('Rico Raser') && $officer->has('nicht geprüft'), 'Haftbuch zeigt die Person');
$officer->post('/index.php?p=custody', ['action' => 'update', 'id' => $c['id'], 'fit_check' => 'fit', 'lawyer' => '1', 'phone_call' => '1', 'cell' => 'Zelle 3', 'jail_units' => '25']);
check(tval('SELECT fit_check FROM custody WHERE id = ?', [$c['id']]) === 'fit', 'Haftfähigkeit eingetragen');
$officer->post('/index.php?p=custody', ['action' => 'release', 'id' => $c['id'], 'to' => 'released', 'note' => 'Haft verbüßt']);
check(tval('SELECT status FROM custody WHERE id = ?', [$c['id']]) === 'released', 'Entlassen');

// Strafregister in der Personenakte (Polizei und Justiz), nicht beim Rettungsdienst
$officer->get('/index.php?p=patient&id=' . $rid);
check($officer->has('Strafregister') && $officer->has('Gewahrsam') && $officer->has('Gefährlicher Eingriff'), 'Strafregister zeigt Gewahrsam');
$tst = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND citizen_id = 'TST001'", [$pd]);
check($officer->get('/index.php?p=patient&id=' . $tst)->has('Haftbefehl') && $officer->has('DOJ'), 'Haftbefehl der Justiz im Strafregister der Polizei');
$arzt = (new Client(TEST_BASE))->login('arzt');
check(!$arzt->get('/index.php?p=patient&id=1')->has('Strafregister'), 'Kein Strafregister beim Rettungsdienst');
