<?php
// Leitstelle (tetrascript): Einsätze je Organisation, Bericht per Knopf, FMS-Status → Funkstatus/Dienst
$set = function (string $k, string $v): void {
    texec('DELETE FROM settings WHERE name = ?', [$k]);
    texec('INSERT INTO settings (name, value) VALUES (?, ?)', [$k, $v]);
};
$fd = (int) tval("SELECT id FROM factions WHERE short = 'LSFD'");
$now = time();
$incident = ['id' => 'inc-7', 'number' => 'ELS-2026-000007', 'org' => 'fire', 'category' => 'fire', 'keyword' => 'B3', 'title' => 'Brand Lagerhalle', 'address' => 'Elysian Fields Fwy 3',
    'caller' => 'Hafenmeister', 'phone' => '555-0101', 'description' => 'Starke Rauchentwicklung, Personen vermisst', 'priority' => 1, 'patients' => 2, 'phase' => 'onscene',
    'createdAt' => ($now - 1800) * 1000, 'firstAlarmAt' => ($now - 1750) * 1000, 'firstOnSceneAt' => ($now - 1200) * 1000, 'commander' => 'BC Feuer', 'risks' => 'Gasflaschenlager',
    'assignments' => [['issi' => '2000001', 'label' => 'HLF 1-1', 'assignedAt' => ($now - 1750) * 1000, 'onSceneAt' => ($now - 1200) * 1000], ['issi' => '2000002', 'label' => 'DLK 1-1']],
    'timeline' => [['at' => ($now - 1750) * 1000, 'text' => 'Alarmierung HLF 1-1, DLK 1-1', 'actor' => 'Disponent'], ['at' => ($now - 1100) * 1000, 'text' => 'Menschenrettung über Drehleiter']]];
[, $r] = api('dispatch_incidents', ['incidents' => [$incident]]);
check($r['ok'] === false, 'Ohne Freischaltung keine Übernahme');
$set('ingame_tetra', '1');
[, $cfg] = api('game_config', []);
$fdCfg = array_values(array_filter($cfg['factions'], fn($f) => $f['id'] === $fd))[0] ?? [];
check($cfg['tetra'] === true && ($fdCfg['tetra_org'] ?? '') === 'fire', 'game_config: Leitstelle an, Organisation je Fraktion');
$ems = ['id' => 'inc-8', 'number' => 'ELS-2026-000008', 'org' => 'ems', 'title' => 'Bewusstlose Person', 'address' => 'Pillbox Hill', 'phase' => 'enroute', 'createdAt' => $now];
[, $r] = api('dispatch_incidents', ['incidents' => [$incident, $ems, ['id' => 'x', 'org' => 'unbekannt']]]);
check($r['ok'] && $r['stored'] === 2, 'Einsätze den Fraktionen über die Organisation zugeordnet');
$row = trow("SELECT * FROM dispatch_incidents WHERE ext_id = 'inc-7'");
check($row && (int) $row['faction_id'] === $fd && $row['alarm_at'] === date('Y-m-d H:i:s', $now - 1750), 'Feuerwehr-Einsatz mit Zeiten (Millisekunden erkannt)');
check((int) tval("SELECT faction_id FROM dispatch_incidents WHERE ext_id = 'inc-8'") === 1, 'Rettungsdienst-Einsatz beim Rettungsdienst');

texec("UPDATE objects SET address = 'Elysian Fields Fwy 3' WHERE faction_id = ?", [$fd]);
$fdc = (new Client(TEST_BASE))->login('fd.chief');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $fdc->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav, 'Leitstelle'), 'Menüpunkt Leitstelle');
check($fdc->get('/index.php?p=dispatch')->has('Brand Lagerhalle') && !$fdc->has('Bewusstlose Person'), 'Nur eigene Einsätze');
$fdc->get('/index.php?p=dispatch&id=' . $row['id']);
check($fdc->has('HLF 1-1') && $fdc->has('Menschenrettung über Drehleiter') && $fdc->has('Gasflaschenlager') && $fdc->has('Objektplan: Lagerhalle Elysian Island'), 'Einsatzdetails mit Einheiten, Verlauf, Gefahren und Objektplan');
// Abschluss → Hinweis „Bericht schreiben“
$incident['phase'] = 'closed';
$incident['closedAt'] = $now * 1000;
$incident['closure'] = 'Feuer aus';
api('dispatch_incidents', ['incidents' => [$incident]]);
check((bool) tval("SELECT 1 FROM notifications n JOIN users u ON u.id = n.user_id WHERE u.username = 'fd.chief' AND n.kind = 'dispatch' AND n.title LIKE '%Bericht schreiben%'"), 'Abgeschlossen: Hinweis „Bericht schreiben“');
$fdc->get('/index.php?p=dispatch&id=' . $row['id']);
check($fdc->has('Einsatzbericht erstellen'), 'Knopf „Einsatzbericht erstellen“');
$fdc->get('/index.php?p=document_new&type=fd_einsatz&dispatch=' . $row['id']);
check($fdc->has('value="Elysian Fields Fwy 3"') && $fdc->has('value="' . date('H:i', $now - 1750) . '"') && $fdc->has('HLF 1-1, DLK 1-1') && $fdc->has('name="_dispatch"'), 'Einsatzbericht aus dem Einsatz vorbelegt');
$fdc->post('/index.php?p=document_new&type=fd_einsatz', ['type' => 'fd_einsatz', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, '_dispatch' => (string) $row['id'],
    'd[datum]' => date('Y-m-d'), 'd[art]' => 'Brand', 'd[ort]' => 'Elysian Fields Fwy 3', 'd[lage]' => 'Vollbrand', 'd[massnahmen]' => 'Innenangriff']);
check((int) tval('SELECT document_id FROM dispatch_incidents WHERE id = ?', [$row['id']]) > 0 && $fdc->get('/index.php?p=dispatch&id=' . $row['id'])->has('Bericht öffnen'), 'Bericht mit dem Einsatz verknüpft');

// FMS-Status
$set("f$fd:game_tetra_duty", '1');
[, $login] = api('tablet_login', ['username' => 'fd.chief', 'password' => 'TestPasswort123', 'license' => 'license:fdtetra', 'player' => 'Fiona']);
$tok = $login['token'];
api('duty_set', ['token' => $tok, 'on' => false]);
[, $r] = api('tetra_status', ['token' => $tok, 'status' => 2, 'issi' => '2000001', 'opta' => 'HLF 1-1']);
$u = trow("SELECT * FROM users WHERE username = 'fd.chief'");
check($r['ok'] && $r['duty'] === true && (int) $u['fms_status'] === 2 && $u['fms_opta'] === 'HLF 1-1', 'Status 2: Funkstatus gespeichert, Dienst automatisch begonnen');
check($fdc->get('/index.php?p=duty')->has('FMS 2') && $fdc->get('/index.php?p=dispatch')->has('FMS 2'), 'Funkstatus in Dienstzeiten und Leitstelle');
[, $r] = api('tetra_status', ['token' => $tok, 'status' => 6]);
check($r['duty'] === false, 'Status 6 beendet den Dienst');
[, $r] = api('tetra_status', ['token' => $tok, 'status' => 12]);
check($r['ok'] === false, 'Ungültiger Status abgelehnt');

// Objektpläne als POI: nur auf ausdrücklichen Wunsch, nur mit Position
check($cfg['tetra_pois'] === false && $cfg['tetra_status_on'] === '-' && $cfg['tetra_status_off'] === '-', 'Zusatzfunktionen der Leitstelle standardmäßig aus');
[, $r] = api('dispatch_pois', []);
check($r['ok'] === false, 'Ohne Einschalten keine POI-Übertragung');
$set('ingame_tetra_pois', '1');
$obj = (int) tval('SELECT id FROM objects WHERE faction_id = ? ORDER BY id LIMIT 1', [$fd]);
$fdc->post('/index.php?p=object&id=' . $obj, ['action' => 'save', 'name' => 'Lagerhalle Elysian', 'kind' => 'Industrie', 'risk' => '2', 'address' => 'Elysian Fields Fwy 3', 'hazards' => 'Gasflaschenlager',
    'x' => '512,5', 'y' => '-2210.25', 'z' => '6']);
[, $r] = api('dispatch_pois', []);
$poi = array_values(array_filter($r['pois'] ?? [], fn($p) => $p['id'] === 'sams-obj-' . $obj))[0] ?? null;
check($poi && $poi['org'] === 'fire' && $poi['x'] === 512.5 && $poi['y'] === -2210.25 && str_contains($poi['notes'], 'Gasflaschenlager'), 'Objektplan mit Position als POI für die Leitstelle');
$fdc->post('/index.php?p=object&id=' . $obj, ['action' => 'save', 'name' => 'Lagerhalle Elysian', 'kind' => 'Industrie', 'risk' => '2', 'x' => '', 'y' => '']);
[, $r] = api('dispatch_pois', []);
check(!array_filter($r['pois'] ?? [], fn($p) => $p['id'] === 'sams-obj-' . $obj), 'Ohne Position kein POI');
$set('ingame_tetra_pois', '0');
$set('ingame_tetra', '0');
