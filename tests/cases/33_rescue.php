<?php
// Spezialrettung (TAS): eigene Fraktionsart mit Rängen, Dokumenten, Flugbuch, Siegel und Fachinhalten
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=factions');
check($admin->has('Spezialrettung (Gelände, Wasser, Luft)'), 'Fraktionsart Spezialrettung wählbar');
$admin->post('/index.php?p=factions', ['action' => 'create', 'name' => 'Technische Abteilung Spezialrettung', 'short' => 'TAS', 'kind' => 'rescue', 'color' => '#d35400', 'lead_username' => 'tas.chief', 'lead_name' => 'Toni Taucher']);
$tas = (int) tval("SELECT id FROM factions WHERE short = 'TAS'");
check($tas > 0 && (int) tval('SELECT COUNT(*) FROM ranks WHERE faction_id = ?', [$tas]) === 8 && (int) tval("SELECT COUNT(*) FROM departments WHERE faction_id = ? AND name = 'Luftrettung'", [$tas]) === 1, 'TAS mit Rängen und Abteilungen');
texec('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE username = ?', [password_hash('TestPasswort123', PASSWORD_DEFAULT), 'tas.chief']);
texec("DELETE FROM rank_permissions WHERE permission = 'security.2fa_required' AND rank_id IN (SELECT id FROM ranks WHERE faction_id = ?)", [$tas]);
$c = (new Client(TEST_BASE))->login('tas.chief');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $c->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav, 'Flugbuch') && str_contains($nav, 'Einsatzakten') && str_contains($nav, 'Kostenbescheide') && !str_contains($nav, 'Medikamente'), 'Menü passend zur Spezialrettung');
check($c->has('assets/img/kinds/rescue.svg'), 'Eigenes Logo');
check((int) tval("SELECT COUNT(*) FROM price_list WHERE faction_id = ? AND code LIKE 'R%'", [$tas]) === 9, 'Kostenordnung vorbelegt');

// Flugbuch
$c->get('/index.php?p=flightlog');
$c->post('/index.php?p=flightlog', ['action' => 'aircraft', 'callsign' => 'Air Rescue 1', 'registration' => 'N-TAS1', 'kind' => 'Hubschrauber', 'model' => 'Maverick', 'hours_total' => '49', 'interval_h' => '1']);
$ac = trow('SELECT * FROM aircraft WHERE faction_id = ?', [$tas]);
check($ac && (int) $ac['minutes_total'] === 49 * 60 && (int) $ac['maintenance_at'] === 50 * 60, 'Luftfahrzeug mit Betriebszeit und Wartungsintervall');
// Flugbericht → Flugbuch
$c->get('/index.php?p=document_new&type=tas_flug');
$c->post('/index.php?p=document_new&type=tas_flug', ['type' => 'tas_flug', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[kennung]' => 'N-TAS1', 'd[datum]' => date('Y-m-d'), 'd[auftrag]' => 'Windenbergung Mount Chiliad', 'd[flugzeit]' => '75', 'd[winden]' => '2', 'd[landungen]' => '1', 'd[crew]' => 'LR Wind']);
$fl = trow('SELECT * FROM flights WHERE faction_id = ?', [$tas]);
check($fl && (int) $fl['minutes'] === 75 && (int) $fl['winch_ops'] === 2 && (int) $fl['pilot_id'] === (int) tval("SELECT id FROM users WHERE username = 'tas.chief'") && $fl['document_id'], 'Flugbericht trägt den Flug ins Flugbuch ein');
check((int) tval('SELECT minutes_total FROM aircraft WHERE id = ?', [$ac['id']]) === 49 * 60 + 75 && (bool) tval("SELECT 1 FROM notifications WHERE kind = 'flightlog' AND title LIKE 'Wartung fällig%'"), 'Betriebszeit erhöht, Wartung fällig gemeldet');
$c->get('/index.php?p=flightlog');
check($c->has('Windenbergung Mount Chiliad') && $c->has('1:15 h') && $c->has('fällig'), 'Flugbuch zeigt Flug, Flugzeit je Pilot/in und fällige Wartung');
$c->post('/index.php?p=flightlog', ['action' => 'maintenance', 'id' => $ac['id']]);
check((int) tval('SELECT maintenance_at FROM aircraft WHERE id = ?', [$ac['id']]) === 49 * 60 + 75 + 60, 'Wartung bestätigt: nächster Termin nach Intervall');

// Dokumentarten und Siegel
foreach (['tas_einsatz' => ['art' => 'Wasserrettung', 'ort' => 'Alamo Sea', 'lage' => 'Person in Strömung', 'massnahmen' => 'Rettung mit Boot'],
    'tas_suche' => ['vermisst' => 'Wanderer', 'zuletzt_ort' => 'Raton Canyon', 'sektoren' => 'Sektor A: negativ', 'ergebnis' => 'lebend aufgefunden'],
    'tas_tauch' => ['gewaesser' => 'Hafenbecken', 'taucher' => 'T. Taucher', 'signalmann' => 'S. Mann'],
    'tas_wache' => ['abschnitt' => 'Vespucci Beach Turm 2']] as $type => $d) {
    $c->get('/index.php?p=document_new&type=' . $type);
    $c->post('/index.php?p=document_new&type=' . $type, ['type' => $type, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[datum]' => date('Y-m-d')]
        + array_combine(array_map(fn($k) => "d[$k]", array_keys($d)), array_values($d)));
    check((bool) tval('SELECT id FROM documents WHERE faction_id = ? AND type = ?', [$tas, $type]), "Dokumentart $type ausstellbar");
}
$tok = (string) tval("SELECT view_token FROM documents WHERE faction_id = ? AND type = 'tas_einsatz'", [$tas]);
$v = (new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $tok);
check($v->has('fill="#b34700"') && $v->has('DIENSTSIEGEL') && !$v->has('Arzt-Nr.'), 'Eigenes Siegel der Spezialrettung');
check((new Client(TEST_BASE))->login('fd.chief')->get('/index.php?p=document_new&type=tas_einsatz')->status === 403, 'TAS-Dokumente nur für die TAS');

// Zusammenarbeit und Leitstelle
[, $cfg] = api('game_config', []);
$t = array_values(array_filter($cfg['factions'], fn($f) => $f['id'] === $tas))[0] ?? [];
check(($t['tetra_org'] ?? '') === 'tas' && $t['jobs'] === ['tas'], 'Leitstellen-Organisation und ESX-Job vorbelegt');
check((new Client(TEST_BASE))->login('fd.chief')->get('/index.php?p=joint')->has('Technische Abteilung Spezialrettung'), 'Feuerwehr kann die TAS zu gemeinsamen Berichten einladen');

// Fachinhalte
if (is_file(dirname(__DIR__, 2) . '/database/content/rescue_wiki.php')) {
    check((int) tval("SELECT COUNT(*) FROM wiki_pages WHERE faction_id = ? AND category <> 'Handbuch'", [$tas]) >= 40, 'Wiki der Spezialrettung');
}
if (is_file(dirname(__DIR__, 2) . '/database/content/rescue_training.php')) {
    check((int) tval('SELECT COUNT(*) FROM training_courses WHERE faction_id = ?', [$tas]) >= 7 && (bool) tval("SELECT id FROM qualifications WHERE faction_id = ? AND name = 'Rettungstaucher/in'", [$tas]), 'Lehrgänge und Qualifikationen der Spezialrettung');
}
