<?php
// Spielserver (ESX): Einstellungen im Websystem, Rechnungen ins Spiel (esx_billing), Akte am Spieler (/akte)
$admin = (new Client(TEST_BASE))->login('admin');
$nfs = (new Client(TEST_BASE))->login('nfs');

// 1. Konfiguration für die Resource mit Vorgaben
[$st, $cfg] = api('game_config', []);
$sams = array_values(array_filter($cfg['factions'] ?? [], fn($f) => $f['id'] === 1))[0] ?? [];
check($st === 200 && $cfg['ok'] && $cfg['framework'] === 'esx' && $cfg['commands']['akte'] === 'akte' && $cfg['billing_table'] === 'billing', 'game_config: Vorgaben (ESX, /akte, Tabelle billing)');
check(($sams['society'] ?? '') === 'society_ambulance' && $sams['billing'] === true && $sams['jobs'] === ['ambulance'], 'game_config: Society-Konto und Job aus der Fraktionsart');

// 2. Im Websystem einstellen (Society, Befehl, Umfang)
$admin->get('/index.php?p=settings');
check($admin->has('Spielserver') && $admin->has('Society-Konto') && $admin->has('Umfang'), 'Einstellungen: Bereich Spielserver und Umfang sichtbar');
$admin->post('/index.php?p=settings', ['game_society' => 'society_ems!', 'game_jobs' => 'ambulance, ems', 'ingame_cmd_akte' => 'patient akte', 'ingame_akte_distance' => '50',
    'game_billing' => '1', 'game_akte' => '1', 'game_akte_duty' => '1', 'game_akte_job' => 'quatsch', 'ingame_framework' => 'esx',
    'modules_form' => '1', 'modules[]' => ['records', 'billing', 'stock', 'portal', 'requests']]);
[, $cfg] = api('game_config', []);
$sams = array_values(array_filter($cfg['factions'], fn($f) => $f['id'] === 1))[0];
check($sams['society'] === 'society_ems' && $sams['jobs'] === ['ambulance', 'ems'] && $cfg['commands']['akte'] === 'patientakte' && $cfg['akte_distance'] == 10, 'Einstellungen bereinigt übernommen');
check($sams['akte_job'] === false, 'Ungültige Auswahl fällt auf die Vorgabe zurück');

// 3. Rechnung ins Spiel senden
texec("UPDATE patients SET citizen_id = 'char1:esx001' WHERE id = 1");
$base = ['type' => 'rechnung', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[service_date]' => date('Y-m-d'), 'd[due_days]' => '14', 'd[tax]' => '0',
    'd[items][0][text]' => 'Verband', 'd[items][0][qty]' => '1', 'd[items][0][price]' => '120.50', 'd[items][0][reason]' => 'Material'];
$issue = function () use ($nfs, $base): int {
    $nfs->get('/index.php?p=document_new&type=rechnung');
    $nfs->post('/index.php?p=document_new&type=rechnung', $base);
    return (int) (preg_match('/p=document&id=(\d+)/', $nfs->url, $m) ? $m[1] : 0);
};
$id = $issue();
check($id > 0 && $nfs->has('Ins Spiel senden') && $nfs->has('noch nicht gesendet'), 'Rechnung zeigt „Ins Spiel senden“');
$nfs->post("/index.php?p=document&id=$id", ['action' => 'game_bill']);
$bill = trow('SELECT * FROM game_bills WHERE document_id = ?', [$id]);
check($bill && $bill['status'] === 'pending' && $bill['citizen_id'] === 'char1:esx001' && (int) $bill['amount'] === 121 && $bill['society'] === 'society_ems', 'Spiel-Rechnung angelegt (Betrag aufgerundet, Society aus den Einstellungen)');
$nfs->post("/index.php?p=document&id=$id", ['action' => 'game_bill']);
check($nfs->has('bereits im Spiel') && (int) tval('SELECT COUNT(*) FROM game_bills WHERE document_id = ?', [$id]) === 1, 'Kein doppelter Versand');
[$st, $sync] = api('bills_sync', []);
$p = array_values(array_filter($sync['pending'], fn($b) => $b['id'] === (int) $bill['id']))[0] ?? null;
check($st === 200 && $p && $p['amount'] === 121 && str_contains($p['label'], 'SAMS') && $sync['table'] === 'billing', 'bills_sync liefert die neue Rechnung');
[$st, $r] = api('bill_result', ['id' => $bill['id'], 'result' => 'sent', 'esx_id' => 77]);
check($r['ok'] && tval('SELECT status FROM game_bills WHERE id = ?', [$bill['id']]) === 'sent', 'Versand gemeldet');
[, $sync] = api('bills_sync', []);
check(in_array(['id' => (int) $bill['id'], 'esx_id' => 77], $sync['open'], true), 'Offene Rechnung wird auf Zahlung geprüft');
[, $r] = api('bill_result', ['id' => $bill['id'], 'result' => 'sent', 'esx_id' => 78]);
check($r['ok'] === false && $r['error'] !== '', 'Falscher Statuswechsel → ok:false mit Text');
[, $r] = api('bill_result', ['id' => $bill['id'], 'result' => 'paid']);
$doc = trow('SELECT * FROM documents WHERE id = ?', [$id]);
check($r['ok'] && $doc['pay_status'] === 'paid' && $doc['pay_note'] === 'im Spiel bezahlt', 'Zahlung im Spiel → Rechnung bezahlt');
check($nfs->get("/index.php?p=document&id=$id")->has('im Spiel bezahlt'), 'Status an der Rechnung sichtbar');

// 4. Außerhalb des Spiels bezahlt / storniert → im Spiel zurückziehen
$id2 = $issue();
$nfs->post("/index.php?p=document&id=$id2", ['action' => 'game_bill']);
$b2 = (int) tval('SELECT id FROM game_bills WHERE document_id = ?', [$id2]);
api('bill_result', ['id' => $b2, 'result' => 'sent', 'esx_id' => 90]);
$nfs->post("/index.php?p=document&id=$id2", ['action' => 'pay_status', 'to' => 'paid', 'pay_note' => 'bar']);
[, $sync] = api('bills_sync', []);
check(in_array(['id' => $b2, 'esx_id' => 90], $sync['cancel'], true), 'Bar bezahlt → Rechnung im Spiel wird zurückgezogen');
api('bill_result', ['id' => $b2, 'result' => 'cancelled']);
check(tval('SELECT status FROM game_bills WHERE id = ?', [$b2]) === 'cancelled', 'Zurückziehen bestätigt');
$id3 = $issue();
$nfs->post("/index.php?p=document&id=$id3", ['action' => 'game_bill']);
$nfs->post("/index.php?p=document&id=$id3", ['action' => 'revoke', 'reason' => 'Fehler']);
check(tval('SELECT status FROM game_bills WHERE document_id = ?', [$id3]) === 'cancelled', 'Storno vor dem Versand → gar nicht erst senden');
texec("UPDATE patients SET citizen_id = '' WHERE id = 1");
$id4 = $issue();
$nfs->post("/index.php?p=document&id=$id4", ['action' => 'game_bill']);
check($nfs->has('keine Bürger-ID') && !tval('SELECT id FROM game_bills WHERE document_id = ?', [$id4]), 'Ohne Bürger-ID kein Versand');
texec("UPDATE patients SET citizen_id = 'char1:esx001' WHERE id = 1");

// 5. Akte am Spieler (/akte)
[, $login] = api('tablet_login', ['username' => 'nfs', 'password' => 'TestPasswort123', 'license' => 'license:akte', 'player' => 'Nina']);
$tok = $login['token'] ?? '';
api('duty_set', ['token' => $tok, 'on' => false]);
$player = ['token' => $tok, 'citizen_id' => 'char1:neu42', 'first_name' => 'Erika', 'last_name' => 'Spielerin', 'dob' => '31/01/1990', 'sex' => 'f'];
[$st, $r] = api('record_lookup', $player);
check($st === 200 && $r['ok'] === false && str_contains($r['error'], 'Dienst'), 'Aktenabruf nur im Dienst');
api('duty_set', ['token' => $tok, 'on' => true]);
[$st, $r] = api('record_lookup', $player);
check($r['ok'] && $r['found'] === false && $r['can_create'] === true && $r['name'] === 'Erika Spielerin', 'Unbekannter Spieler: Akte kann angelegt werden');
[$st, $r] = api('record_open', $player);
check($r['ok'] === false, 'Ohne „anlegen“ wird keine Akte erzeugt');
[$st, $r] = api('record_open', $player + ['create' => true]);
$pat = trow("SELECT * FROM patients WHERE citizen_id = 'char1:neu42'");
check($r['ok'] && $r['created'] && $pat && $pat['dob'] === '1990-01-31' && $pat['gender'] === 'w' && (int) $pat['faction_id'] === 1, 'Akte aus den ESX-Daten angelegt (Datum TT/MM/JJJJ, Geschlecht)');
check(str_contains($r['url'], '&next=index.php%3Fp%3Dpatient%26id%3D' . $pat['id']), 'Tablet-Link führt direkt zur Akte');
$tab = new Client(TEST_BASE);
$u = parse_url($r['url']);
check($tab->get($u['path'] . '?' . $u['query'])->has('Spielerin') && str_contains($tab->url, 'p=patient'), 'Tablet öffnet die Akte');
texec("UPDATE patients SET allergies = 'Penicillin', blood_type = '0 neg' WHERE id = ?", [$pat['id']]);
[, $r] = api('record_lookup', $player);
$sum = array_column($r['summary'], 'value', 'label');
check($r['found'] && ($sum['Allergien'] ?? '') === 'Penicillin' && ($sum['Blutgruppe'] ?? '') === '0 neg' && ($sum['Geburtsdatum'] ?? '') === '31.01.1990', 'Kurzfassung mit Allergien und Blutgruppe');
check((int) tval("SELECT COUNT(*) FROM audit_log WHERE action = 'patient.game_lookup' AND object_id = ?", [$pat['id']]) >= 1, 'Abruf wird protokolliert');
[, $r] = api('record_open', $player + ['create' => true]);
check($r['ok'] && !$r['created'] && (int) tval("SELECT COUNT(*) FROM patients WHERE citizen_id = 'char1:neu42'") === 1, 'Vorhandene Akte wird geöffnet, nicht doppelt angelegt');
// Schalter im Websystem
texec("UPDATE settings SET value = '0' WHERE name = 'game_akte'");
texec("INSERT INTO settings (name, value) SELECT 'game_akte', '0' WHERE NOT EXISTS (SELECT 1 FROM settings WHERE name = 'game_akte')");
[, $r] = api('record_lookup', $player);
check($r['ok'] === false && str_contains($r['error'], 'ausgeschaltet'), 'Aktenabruf im Websystem abschaltbar');
texec("UPDATE settings SET value = '1' WHERE name = 'game_akte'");
[$st] = api('record_lookup', ['token' => 'ungueltig'] + $player);
check($st === 401, 'Ungültige Tablet-Anmeldung → 401');
api('duty_set', ['token' => $tok, 'on' => false]);
