<?php
// Feuerwehr-Ausbau: Geräte & Prüffristen, Atemschutz-Nachweise, neue Dokumentarten, Brandschau ↔ Objektplan, Fachinhalte
$fd = (int) tval("SELECT id FROM factions WHERE short = 'LSFD'");
$fdc = (new Client(TEST_BASE))->login('fd.chief');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $fdc->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav, 'Geräte &amp; Prüfungen') && str_contains($nav, 'Atemschutz'), 'Feuerwehr-Menü mit Geräten und Atemschutz');
$m1 = json_decode((string) tval('SELECT modules FROM factions WHERE id = 1'), true);
if (is_array($m1)) { // Test 23 hat die Module ausdrücklich gesetzt
    texec('UPDATE factions SET modules = ? WHERE id = 1', [json_encode(array_values(array_unique(array_merge($m1, ['equipment']))))]);
}
check(str_contains(preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', (new Client(TEST_BASE))->login('arzt')->get('/index.php?p=dashboard')->body, $m2) ? $m2[1] : '', 'Geräte &amp; Prüfungen'), 'Rettungsdienst hat ebenfalls Geräte & Prüfungen');
check((new Client(TEST_BASE))->login('officer')->get('/index.php?p=breathing')->status === 403, 'Polizei ohne Atemschutz-Modul');

// Geräte
$fdc->get('/index.php?p=equipment');
$fdc->post('/index.php?p=equipment', ['action' => 'save', 'name' => 'Pressluftatmer PA 12', 'category' => 'Atemschutz', 'inventory_no' => 'PA-12', 'location' => 'HLF Station 1',
    'interval_days' => '180', 'next_check' => date('Y-m-d', strtotime('+3 days')), 'status' => 'ok']);
$eq = trow('SELECT * FROM equipment WHERE faction_id = ? AND inventory_no = ?', [$fd, 'PA-12']);
check($eq && $eq['status'] === 'ok', 'Gerät angelegt');
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1', $cronOut);
check(str_contains(implode("\n", $cronOut), 'Geräte/Atemschutz: 1') && (bool) tval("SELECT 1 FROM notifications n JOIN users u ON u.id = n.user_id WHERE u.username = 'fd.chief' AND n.kind = 'equipment'"), 'Hinweis vor fälliger Prüfung');
$fdc->post('/index.php?p=equipment', ['action' => 'check', 'id' => $eq['id'], 'result' => 'ok', 'note' => 'Dichtprüfung ok']);
$eq = trow('SELECT * FROM equipment WHERE id = ?', [$eq['id']]);
check($eq['last_check'] === date('Y-m-d') && $eq['next_check'] === date('Y-m-d', strtotime('+180 days')) && (int) $eq['reminded'] === 0, 'Prüfung: nächster Termin nach Intervall');
$fdc->post('/index.php?p=equipment', ['action' => 'check', 'id' => $eq['id'], 'result' => 'defect', 'note' => 'Lungenautomat undicht']);
check(tval('SELECT status FROM equipment WHERE id = ?', [$eq['id']]) === 'defect' && $fdc->get('/index.php?p=equipment&filter=defect')->has('Lungenautomat undicht'), 'Mangel markiert das Gerät als defekt');
check((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=equipment')->has('PA-12') === false, 'Geräte nur in der eigenen Fraktion');

// Atemschutz
$chiefId = (int) tval("SELECT id FROM users WHERE username = 'fd.chief'");
$fdc->get('/index.php?p=breathing&user=' . $chiefId);
$fdc->post('/index.php?p=breathing', ['action' => 'log', 'user_id' => $chiefId, 'kind' => 'Belastungsübung (Atemschutzstrecke)', 'minutes' => '25', 'log_date' => date('Y-m-d')]);
check(tval('SELECT last_exercise FROM breathing_status WHERE user_id = ?', [$chiefId]) === date('Y-m-d'), 'Belastungsübung aktualisiert den Nachweis');
check($fdc->get('/index.php?p=breathing')->has('gesperrt') && $fdc->has('Eignungsuntersuchung'), 'Ohne Eignung: gesperrt mit Grund');
$fdc->post('/index.php?p=breathing', ['action' => 'status', 'user_id' => $chiefId, 'g26_until' => date('Y-m-d', strtotime('+20 days')), 'last_exercise' => date('Y-m-d'), 'last_instruction' => date('Y-m-d')]);
check($fdc->get('/index.php?p=breathing')->has('einsatzbereit'), 'Mit allen Nachweisen einsatzbereit');
$fdc->post('/index.php?p=breathing', ['action' => 'log', 'user_id' => $chiefId, 'kind' => 'Einsatz', 'minutes' => '35', 'note' => 'Innenangriff']);
check($fdc->get('/index.php?p=breathing&user=' . $chiefId)->has('Innenangriff') && $fdc->has('60 min'), 'Einsatz im Verlauf, Minuten summiert');
$cronOut = [];
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1', $cronOut);
check((bool) tval("SELECT 1 FROM notifications WHERE user_id = ? AND kind = 'breathing'", [$chiefId]), 'Hinweis vor Ablauf der Eignungsuntersuchung');

// Neue Dokumentarten
foreach (['fd_atemschutz' => ['einsatzort' => 'Lagerhalle', 't1_namen' => 'Feuer / Wasser', 't1_druck' => '300'], 'fd_brandursache' => ['objekt' => 'Lagerhalle', 'ausbruch' => 'Elektroverteilung', 'ursache' => 'Elektrischer Defekt'],
    'fd_tueroeffnung' => ['anschrift' => 'Grove Street 12', 'auftraggeber' => 'Rettungsdienst (SAMS)'], 'fd_uebung' => ['thema' => 'Menschenrettung', 'teilnehmer' => 'Wache 1']] as $type => $d) {
    $fdc->get('/index.php?p=document_new&type=' . $type);
    $fdc->post('/index.php?p=document_new&type=' . $type, ['type' => $type, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[datum]' => date('Y-m-d')]
        + array_combine(array_map(fn($k) => "d[$k]", array_keys($d)), array_values($d)));
    check((bool) tval('SELECT id FROM documents WHERE faction_id = ? AND type = ?', [$fd, $type]), "Dokumentart $type ausstellbar");
}

// Brandschau ↔ Objektplan
$obj = trow('SELECT * FROM objects WHERE faction_id = ?', [$fd]);
$fdc->get('/index.php?p=object&id=' . $obj['id']);
check($fdc->has('Brandschau dokumentieren'), 'Objektplan bietet „Brandschau dokumentieren“');
$fdc->get('/index.php?p=document_new&type=fd_brandschau&prefill[objekt]=' . rawurlencode($obj['name']));
check($fdc->has('value="' . htmlspecialchars($obj['name']) . '"'), 'Objektname vorbelegt');
$frist = date('Y-m-d', strtotime('+21 days'));
$fdc->post('/index.php?p=document_new&type=fd_brandschau', ['type' => 'fd_brandschau', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[objekt]' => $obj['name'], 'd[datum]' => date('Y-m-d'), 'd[maengel]' => 'Notausgang verstellt', 'd[frist]' => $frist, 'd[ergebnis]' => 'Mängel – Nachschau erforderlich']);
$obj2 = trow('SELECT * FROM objects WHERE id = ?', [$obj['id']]);
check($obj2['last_inspection'] === date('Y-m-d') && $obj2['next_inspection'] === $frist, 'Brandschau mit Mängeln: Nachschau-Termin im Objektplan');
$fdc->get('/index.php?p=document_new&type=fd_nachschau');
$fdc->post('/index.php?p=document_new&type=fd_nachschau', ['type' => 'fd_nachschau', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[objekt]' => $obj['name'], 'd[datum]' => date('Y-m-d'), 'd[ergebnis]' => 'Alle Mängel beseitigt']);
check(tval('SELECT next_inspection FROM objects WHERE id = ?', [$obj['id']]) === date('Y-m-d', strtotime('+1 year')), 'Nachschau ohne Mängel: nächste Brandschau in einem Jahr');
check($fdc->get('/index.php?p=object&id=' . $obj['id'])->has('Brandschaubericht') && $fdc->has('Nachschau'), 'Objektplan listet die Berichte');

// Fachinhalte
if (is_file(dirname(__DIR__, 2) . '/database/content/fire_wiki.php')) {
    check((int) tval("SELECT COUNT(*) FROM wiki_pages WHERE faction_id = ? AND category <> 'Handbuch'", [$fd]) >= 40, 'Wiki der Feuerwehr mit Fachinhalten');
}
if (is_file(dirname(__DIR__, 2) . '/database/content/fire_training.php')) {
    check((int) tval('SELECT COUNT(*) FROM training_courses WHERE faction_id = ?', [$fd]) >= 7 && (bool) tval("SELECT id FROM qualifications WHERE faction_id = ? AND name = 'Atemschutzgeräteträger'", [$fd]), 'Lehrgänge und Qualifikationen der Feuerwehr');
    check(!tval("SELECT COUNT(*) FROM training_courses WHERE faction_id = 1 AND title LIKE 'Atemschutzgeräteträger%'"), 'Feuerwehr-Lehrgänge nicht beim Rettungsdienst');
}
