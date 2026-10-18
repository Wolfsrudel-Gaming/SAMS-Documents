<?php
// Sky Ambulance: Vitalwerte, Tod/Wiederbelebung, Medikamentengabe, Sky Billing
$set = function (string $k, string $v): void {
    texec('DELETE FROM settings WHERE name = ?', [$k]);
    texec('INSERT INTO settings (name, value) VALUES (?, ?)', [$k, $v]);
};
[, $r] = api('sky_death', ['citizen_id' => 'char1:sky01', 'dead' => true]);
check($r['ok'] === false && str_contains($r['error'], 'ausgeschaltet'), 'Ohne Freischaltung im Websystem keine Sky-Erfassung');
$set('ingame_sky', '1');
[, $cfg] = api('game_config', []);
check($cfg['sky'] === true && $cfg['sky_death'] === true && $cfg['sky_meds'] === true && $cfg['billing_provider'] === 'esx_billing', 'game_config meldet die Sky-Anbindung');

[, $login] = api('tablet_login', ['username' => 'nfs', 'password' => 'TestPasswort123', 'license' => 'license:sky', 'player' => 'Nina']);
$tok = $login['token'];
api('duty_set', ['token' => $tok, 'on' => true]);
$player = ['citizen_id' => 'char1:sky01', 'first_name' => 'Paula', 'last_name' => 'Patient', 'dob' => '1995-03-03', 'sex' => 'f'];

// Vitalwerte
[, $r] = api('record_vitals', ['token' => $tok] + $player + ['vitals' => ['heart_rate' => 999, 'systolic' => 80, 'diastolic' => 90]]);
check($r['ok'] === false, 'Ungültige Vitalwerte werden verworfen, nicht durch Normalwerte ersetzt');
[, $r] = api('record_vitals', ['token' => $tok] + $player + ['vitals' => ['heart_rate' => 128, 'systolic' => 92, 'diastolic' => 58, 'spo2' => 88, 'blood_sugar' => 64, 'cardiac_arrest' => false, 'activity' => 'rest']]);
$pid = (int) tval("SELECT id FROM patients WHERE citizen_id = 'char1:sky01' AND faction_id = 1");
check($r['ok'] && $pid && str_contains($r['text'], 'RR 92/58') && str_contains($r['text'], 'SpO₂ 88 %'), 'Vitalwerte übernommen (Akte aus den Spielerdaten angelegt)');
$nfs = (new Client(TEST_BASE))->login('nfs');
check($nfs->get('/index.php?p=patient&id=' . $pid)->has('Vitalverlauf') && $nfs->has('92/58'), 'Vitalverlauf in der Akte');
$nfs->get('/index.php?p=document_new&type=einsatzprotokoll&patient=' . $pid);
check($nfs->has('value="92/58"') && $nfs->has('value="128"') && $nfs->has('value="88"'), 'Einsatzprotokoll mit frischen Vitalwerten vorbelegt');
[, $r] = api('record_vitals', ['token' => $tok] + $player + ['vitals' => ['systolic' => 0, 'diastolic' => 0, 'heart_rate' => 0, 'cardiac_arrest' => true]]);
check($r['ok'] && $r['cardiac_arrest'] === true && str_contains($r['text'], 'STILLSTAND'), 'Herzstillstand (0/0) erfasst');

// Tod und Wiederbelebung
[, $r] = api('sky_death', $player + ['dead' => true]);
check($r['ok'] && $r['entries'] === 1 && (bool) tval("SELECT 1 FROM entries WHERE patient_id = ? AND title LIKE 'Bewusstlos%'", [$pid]), 'Zusammenbruch in vorhandener Akte erfasst');
[, $r] = api('sky_death', ['citizen_id' => 'char1:unbekannt', 'first_name' => 'Ohne', 'last_name' => 'Akte', 'dead' => true]);
check($r['ok'] && $r['entries'] === 0 && !tval("SELECT 1 FROM patients WHERE citizen_id = 'char1:unbekannt'"), 'Ohne Medic und ohne Akte: keine neue Akte');
[, $r] = api('sky_death', ['citizen_id' => 'char1:sky02', 'first_name' => 'Rudi', 'last_name' => 'Reanimiert', 'dead' => false, 'medic_token' => $tok]);
$p2 = (int) tval("SELECT id FROM patients WHERE citizen_id = 'char1:sky02' AND faction_id = 1");
check($r['ok'] && $p2 && (bool) tval("SELECT 1 FROM entries WHERE patient_id = ? AND title LIKE 'Wieder bei Bewusstsein%' AND created_by = (SELECT id FROM users WHERE username = 'nfs')", [$p2]), 'Wiederbelebung mit Medic: Akte angelegt, Eintrag im Namen des Medics');

// Medikamentengabe → Bestand/BtM-Buch
$mor = (int) tval("SELECT id FROM medications WHERE name LIKE 'Morphin%' AND faction_id = 1");
texec("UPDATE medications SET item_name = 'sky_morphine', stock = 4 WHERE id = ?", [$mor]);
[, $r] = api('sky_medication', ['token' => $tok, 'item' => 'sky_morphine'] + $player);
check($r['ok'] && (int) tval('SELECT stock FROM medications WHERE id = ?', [$mor]) === 3, 'Gabe aus Sky bucht den Bestand');
$mv = trow("SELECT * FROM medication_moves WHERE medication_id = ? ORDER BY id DESC LIMIT 1", [$mor]);
check($mv && (int) $mv['patient_id'] === $pid && $mv['source'] === 'game' && (int) $mv['delta'] === -1, 'BtM-Buch: Abgang mit Patient und Medic');
check((bool) tval("SELECT 1 FROM entries WHERE patient_id = ? AND type = 'Medikation' AND title LIKE 'Medikamentengabe: Morphin%'", [$pid]), 'Gabe in der Akte dokumentiert');
[, $r] = api('sky_medication', ['token' => $tok, 'item' => 'gibtsnicht'] + $player);
check($r['ok'] && str_contains($r['text'], 'kein Artikel'), 'Unbekanntes Item: nur Akteneintrag, Hinweis');

// Sky Billing
$set('ingame_billing_provider', 'sky');
texec("UPDATE patients SET citizen_id = 'char1:esx001' WHERE id = 1");
$nfs->get('/index.php?p=document_new&type=rechnung');
$nfs->post('/index.php?p=document_new&type=rechnung', ['type' => 'rechnung', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[service_date]' => date('Y-m-d'), 'd[due_days]' => '14', 'd[tax]' => '0',
    'd[items][0][text]' => 'Verband', 'd[items][0][qty]' => '1', 'd[items][0][price]' => '75', 'd[items][0][reason]' => 'Material']);
$doc = (int) (preg_match('/p=document&id=(\d+)/', $nfs->url, $m) ? $m[1] : 0);
$nfs->post('/index.php?p=document&id=' . $doc, ['action' => 'game_bill']);
[, $sync] = api('bills_sync', []);
$b = array_values(array_filter($sync['pending'], fn($x) => str_contains($x['reference'], (string) tval('SELECT number FROM documents WHERE id = ?', [$doc]))))[0] ?? null;
check($sync['provider'] === 'sky' && $b && $b['job'] === 'ambulance' && $b['name'] !== '' && $b['amount'] === 75, 'Sky Billing: Rechnung mit Job, Empfängername und eindeutiger Referenz');
api('bill_result', ['id' => $b['id'], 'result' => 'sent', 'ext_ref' => 'INV-4711']);
[, $sync] = api('bills_sync', []);
check((array_column($sync['open'], 'ext_ref', 'id')[$b['id']] ?? '') === 'INV-4711', 'Sky-Rechnungsnummer gespeichert und abgeglichen');
[, $r] = api('bill_result', ['id' => $b['id'], 'result' => 'cancelled']);
check($r['ok'] && tval('SELECT status FROM game_bills WHERE id = ?', [$b['id']]) === 'cancelled', 'In Sky storniert → zurückgezogen');
$set('ingame_billing_provider', 'esx_billing');
$set('ingame_sky', '0');
api('duty_set', ['token' => $tok, 'on' => false]);
