<?php
// Mehrere FiveM-Server (Staaten) an einem Portal: eigene Schlüssel, Drucker je Staat, getrennte Anmeldungen und Rechnungen
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=factions');
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => '1']);
$admin->get('/index.php?p=servers');
check($admin->status === 200 && $admin->has('San Andreas') && $admin->has('sams_url') && $admin->has(TEST_BASE) && $admin->has('testkey') && $admin->has('aus der .env'), 'Adminbereich zeigt Portal-Adresse und Schlüssel des Hauptservers');
check((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=servers')->status === 403, 'Spielserver nur für den Hauptadministrator');

$admin->post('/index.php?p=servers', ['action' => 'create', 'name' => 'Liberty City', 'short' => 'LC']);
$lc = trow("SELECT * FROM game_servers WHERE name = 'Liberty City'");
check($lc && str_starts_with($lc['api_key'], 'sams_') && strlen($lc['api_key']) > 40, 'Neuer Server mit eigenem Schlüssel');
$sa = (int) tval('SELECT MIN(id) FROM game_servers');
$lcKey = $lc['api_key'];
$admin->get('/index.php?p=servers&open=' . $lc['id']);
check($admin->has($lcKey) && $admin->has('set sams_api_key'), 'Schlüssel und server.cfg-Zeilen abrufbar');

[$st, $cfg] = api('game_config', [], $lcKey);
check($st === 200 && ($cfg['server']['name'] ?? '') === 'Liberty City', 'Zweiter Server meldet sich mit eigenem Schlüssel an');
[, $cfgSa] = api('game_config', []);
check(($cfgSa['server']['name'] ?? '') === 'San Andreas', 'Hauptserver weiter mit dem Schlüssel aus der .env');
check(tval('SELECT last_seen_at FROM game_servers WHERE id = ?', [$lc['id']]) !== null, 'Letzte Verbindung wird angezeigt');

// Abweichende Einstellungen je Server
$admin->post('/index.php?p=servers', ['action' => 'save', 'id' => $lc['id'], 'name' => 'Liberty City', 'short' => 'LC', 'active' => '1', 'cfg_ingame_cmd_tablet' => 'lc-tab!', 'cfg_ingame_billing_provider' => 'sky']);
[, $cfg] = api('game_config', [], $lcKey);
[, $cfgSa] = api('game_config', []);
check($cfg['commands']['tablet'] === 'lctab' && $cfg['billing_provider'] === 'sky' && $cfgSa['commands']['tablet'] !== 'lctab' && $cfgSa['billing_provider'] === 'esx_billing', 'Einstellungen je Server überschreibbar');

// Drucker je Staat
$admin->get('/index.php?p=printers');
check($admin->has('Staat / Spielserver'), 'Drucker: Staat wählbar');
$admin->post('/index.php?p=printers', ['name' => 'Drucker LC Hospital', 'location' => 'Liberty General', 'x' => '100', 'y' => '200', 'z' => '30', 'heading' => '90', 'prop_model' => 'prop_printer_01', 'active' => '1', 'server_id' => $lc['id']]);
$lp = trow("SELECT * FROM printers WHERE name = 'Drucker LC Hospital'");
check($lp && (int) $lp['server_id'] === (int) $lc['id'], 'Drucker dem Staat zugeordnet');
texec("INSERT INTO printers (faction_id, name, location, x, y, z, heading, prop_model, show_blip, active, server_id, created_at, updated_at) VALUES (1, 'Drucker SA Pillbox', '', 1, 2, 3, 0, '', 0, 1, ?, ?, ?)", [$sa, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
[, $pl] = api('printers', [], $lcKey);
[, $ps] = api('printers', []);
$names = fn($r) => array_column($r['printers'] ?? [], 'name');
check($names($pl) === ['Drucker LC Hospital'] && !in_array('Drucker LC Hospital', $names($ps), true) && in_array('Drucker SA Pillbox', $names($ps), true), 'Jeder Server sieht nur seine Drucker');
$admin->get('/index.php?p=printers');
check($admin->has('Liberty City') && $admin->has('San Andreas'), 'Druckerliste zeigt den Staat');

// Druckauftrag am LC-Drucker
$doc = (int) tval("SELECT id FROM documents WHERE faction_id = 1 AND status = 'valid' ORDER BY id LIMIT 1");
$admin->get('/index.php?p=document&id=' . $doc);
check($admin->has('Drucker LC Hospital · Liberty City'), 'Druckerauswahl mit Staat');
[, $j0] = api('jobs_since', ['since_id' => -1]);
texec("INSERT INTO print_jobs (document_id, printer_id, copies, status, requested_by, requested_at) VALUES (?, ?, 1, 'queued', 1, ?)", [$doc, $lp['id'], date('Y-m-d H:i:s')]);
$job = (int) tval('SELECT MAX(id) FROM print_jobs');
[, $jsSa] = api('jobs_since', ['since_id' => $j0['last_id']]);
[, $jsLc] = api('jobs_since', ['since_id' => $j0['last_id']], $lcKey);
check(!in_array($job, array_column($jsSa['jobs'], 'id'), true) && in_array($job, array_column($jsLc['jobs'], 'id'), true), 'Neue Aufträge nur an den Server des Druckers');

// Tablet-Anmeldung gilt nur auf dem eigenen Server
[, $login] = api('tablet_login', ['username' => 'nfs', 'password' => 'TestPasswort123', 'license' => 'license:lc', 'player' => 'Nina'], $lcKey);
$tok = $login['token'] ?? '';
check($tok !== '' && (int) tval('SELECT server_id FROM tablet_tokens WHERE token_hash = ?', [hash('sha256', $tok)]) === (int) $lc['id'], 'Anmeldung dem Server zugeordnet');
[$st1] = api('tablet_session', ['token' => $tok], $lcKey);
[$st2] = api('tablet_session', ['token' => $tok]);
check($st1 === 200 && $st2 === 401, 'Anmeldung von Liberty City gilt nicht in San Andreas');
[, $pj] = api('printer_jobs', ['printer_id' => $lp['id'], 'token' => $tok]);
check(($pj['jobs'] ?? []) === [], 'Fremder Server kann am LC-Drucker nichts abholen');
[, $pj] = api('printer_jobs', ['printer_id' => $lp['id'], 'token' => $tok], $lcKey);
check(in_array($job, array_column($pj['jobs'] ?? [], 'id'), true), 'Abholung am eigenen Server');

// Spiel-Rechnungen je Server
$now = date('Y-m-d H:i:s');
texec("INSERT INTO game_bills (faction_id, document_id, citizen_id, amount, society, label, status, created_at, server_id) VALUES (1, ?, 'char1:lc', 50, 'society_ambulance', 'LC Rechnung', 'pending', ?, ?)", [$doc, $now, $lc['id']]);
$lcBill = (int) tval('SELECT MAX(id) FROM game_bills');
[, $bs] = api('bills_sync', []);
[, $bl] = api('bills_sync', [], $lcKey);
check(!in_array($lcBill, array_column($bs['pending'], 'id'), true) && in_array($lcBill, array_column($bl['pending'], 'id'), true) && $bl['provider'] === 'sky', 'Rechnungen nur an den passenden Server');
[, $r] = api('bill_result', ['id' => $lcBill, 'result' => 'sent', 'ext_ref' => 'X1']);
check(!$r['ok'] && tval('SELECT status FROM game_bills WHERE id = ?', [$lcBill]) === 'pending', 'Fremder Server kann die Rechnung nicht melden');

// Schlüssel erneuern, Server deaktivieren
$admin->post('/index.php?p=servers', ['action' => 'new_key', 'id' => $lc['id']]);
[$st] = api('game_config', [], $lcKey);
$newKey = (string) tval('SELECT api_key FROM game_servers WHERE id = ?', [$lc['id']]);
[$st2] = api('game_config', [], $newKey);
check($st === 401 && $st2 === 200 && $newKey !== $lcKey, 'Neuer Schlüssel ersetzt den alten');
$admin->post('/index.php?p=servers', ['action' => 'save', 'id' => $lc['id'], 'name' => 'Liberty City', 'short' => 'LC']);
[$st] = api('game_config', [], $newKey);
check($st === 401, 'Deaktivierter Server wird abgewiesen');
$admin->post('/index.php?p=servers', ['action' => 'save', 'id' => $sa, 'name' => 'San Andreas', 'short' => 'SA']);
check((int) tval('SELECT active FROM game_servers WHERE id = ?', [$sa]) === 1, 'Hauptserver bleibt aktiv');
// Aufräumen für spätere Tests
texec('DELETE FROM print_jobs WHERE printer_id = ?', [$lp['id']]);
texec("DELETE FROM printers WHERE id = ? OR name = 'Drucker SA Pillbox'", [$lp['id']]);
texec('DELETE FROM game_servers WHERE id = ?', [$lc['id']]);
