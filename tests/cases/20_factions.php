<?php
// Mehrere Fraktionen: Anlegen, strikte Datentrennung, eigene Einstellungen und Ränge
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=factions');
$admin->post('/index.php?p=factions', ['action' => 'create', 'name' => 'Los Santos Police Department', 'short' => 'LSPD', 'kind' => 'police', 'color' => '#1d3a6e',
    'lead_username' => 'chief.miller', 'lead_name' => 'John Miller']);
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
check($pd > 1 && $admin->has('Startpasswort'), 'Polizei angelegt mit Leitungskonto');
check((int) tval('SELECT COUNT(*) FROM ranks WHERE faction_id = ?', [$pd]) >= 8 && (int) tval('SELECT COUNT(*) FROM departments WHERE faction_id = ?', [$pd]) >= 4, 'Ränge und Abteilungen aus der Vorlage');
check((int) tval("SELECT COUNT(*) FROM wiki_pages WHERE faction_id = ? AND slug = 'handbuch_erste_schritte'", [$pd]) === 1
    && (int) tval("SELECT COUNT(*) FROM wiki_pages WHERE slug = 'handbuch_erste_schritte'") === 2, 'Eigenes Handbuch (gleiche Adresse je Fraktion möglich)');
texec("UPDATE users SET password_hash = ?, must_change_password = 0 WHERE username = 'chief.miller'", [password_hash('TestPasswort123', PASSWORD_DEFAULT)]);
texec("DELETE FROM rank_permissions WHERE permission = 'security.2fa_required' AND rank_id IN (SELECT id FROM ranks WHERE faction_id = ?)", [$pd]);
check(!tval("SELECT COUNT(*) FROM rank_permissions WHERE permission LIKE 'stock.%' AND rank_id IN (SELECT id FROM ranks WHERE faction_id = ?)", [$pd]), 'Keine Medizinrechte in den Polizeirängen');
$sgtRank = (int) tval("SELECT id FROM ranks WHERE faction_id = ? AND short = 'OFC'", [$pd]);
texec("INSERT INTO users (username, name, password_hash, rank_id, faction_id, active, must_change_password, created_at) VALUES ('officer', 'Olli Officer', ?, ?, ?, 1, 0, ?)",
    [password_hash('TestPasswort123', PASSWORD_DEFAULT), $sgtRank, $pd, date('Y-m-d H:i:s')]);
$chief = (new Client(TEST_BASE))->login('chief.miller');
$chief->get('/index.php?p=dashboard');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $chief->body, $mm) ? $mm[1] : '';
check(str_contains($nav, 'Personenakten') && str_contains($nav, 'Bußgelder') && !str_contains($nav, 'Medikamente') && !str_contains($nav, 'Patientenakten'), 'Menü passend zur Polizei');
check($chief->has('Los Santos Police Department') && $chief->has('assets/img/kinds/police.svg'), 'Eigener Name und Logo');
check($chief->get('/index.php?p=stock')->status === 403, 'Medikamentenbestand für die Polizei gesperrt');
// Keine Daten anderer Fraktionen
check(!$chief->get('/index.php?p=patients')->has('Tester'), 'Akten des Rettungsdienstes nicht in der Liste');
check($chief->get('/index.php?p=patient&id=1')->status === 404, 'Akte des Rettungsdienstes per ID nicht abrufbar');
$samsDoc = (int) tval('SELECT id FROM documents WHERE faction_id = 1 ORDER BY id LIMIT 1');
check($samsDoc && $chief->get('/index.php?p=document&id=' . $samsDoc)->status === 404, 'Dokument des Rettungsdienstes per ID nicht abrufbar');
$arztId = (int) tval("SELECT id FROM users WHERE username = 'arzt'");
check(!$chief->get('/index.php?p=users')->has('Arne Arzt') && $chief->get('/index.php?p=user_edit&id=' . $arztId)->status === 404, 'Personal anderer Fraktionen unsichtbar');
check(!$chief->get('/index.php?p=search&q=Tester')->has('TST001'), 'Suche findet nichts aus anderen Fraktionen');
check(!$chief->get('/index.php?p=ranks')->has('Oberarzt') && $chief->has('Sergeant'), 'Nur eigene Ränge');
check(!$chief->get('/index.php?p=wiki')->has('SOP Reanimation'), 'Nur eigenes Wiki');
check(!$chief->get('/index.php?p=audit')->has('Arne Arzt'), 'Nur eigenes Zugriffsprotokoll');
// Eigene Akte und Dokument anlegen
$chief->get('/index.php?p=patient_edit');
$chief->post('/index.php?p=patient_edit', ['first_name' => 'Vinnie', 'last_name' => 'Verdacht', 'dob' => '1985-05-05', 'gender' => 'm', 'citizen_id' => 'TST001']);
$pp = (int) tval("SELECT id FROM patients WHERE last_name = 'Verdacht'");
check($pp && (int) tval('SELECT faction_id FROM patients WHERE id = ?', [$pp]) === $pd, 'Neue Akte gehört der Polizei (gleiche Bürger-ID erlaubt)');
$arzt = (new Client(TEST_BASE))->login('arzt');
check($arzt->get('/index.php?p=patient&id=' . $pp)->status === 404 && !$arzt->get('/index.php?p=search&q=Verdacht')->has('Vinnie'), 'Rettungsdienst sieht Polizeiakten nicht');
// Einstellungen je Fraktion
$chief->get('/index.php?p=settings');
check(!$chief->has('session_idle_minutes') && $chief->has('org_name'), 'Nur fraktionseigene Einstellungen');
$chief->post('/index.php?p=settings', ['org_name' => 'LSPD Mission Row', 'org_short' => 'LSPD']);
check($arzt->get('/index.php?p=dashboard')->has('San Andreas Medical Services') && $chief->get('/index.php?p=dashboard')->has('LSPD Mission Row'), 'Briefkopf getrennt gespeichert');
$chief->get('/index.php?p=document_new&type=schreiben');
$chief->post('/index.php?p=document_new&type=schreiben', ['type' => 'schreiben', 'patient_id' => (string) $pp, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[recipient]' => 'DOJ', 'd[subject]' => 'Ermittlungsstand', 'd[salutation]' => 'Hallo', 'd[body]' => 'Text', 'd[closing]' => 'Gruß']);
$pdDoc = (int) tval('SELECT id FROM documents WHERE faction_id = ? ORDER BY id DESC LIMIT 1', [$pd]);
check($pdDoc && str_starts_with((string) tval('SELECT number FROM documents WHERE id = ?', [$pdDoc]), 'LSPD-'), 'Dokument mit eigenem Nummernkreis');
check($chief->get('/index.php?p=document&id=' . $pdDoc)->has('LSPD Mission Row') && !$chief->has('Elgin Avenue'), 'Briefkopf der Polizei im Dokument');
check($arzt->get('/index.php?p=document&id=' . $pdDoc)->status === 404, 'Rettungsdienst kann Polizeidokument nicht öffnen');
$code = (string) tval('SELECT verify_code FROM documents WHERE id = ?', [$pdDoc]);
texec('DELETE FROM rate_hits');
check((new Client(TEST_BASE))->get('/verify.php?c=' . $code)->has('LSPD Mission Row'), 'Echtheitsprüfung zeigt die ausstellende Behörde');
// Hauptadministrator wechselt die Ansicht
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => (string) $pd, 'return' => 'patients']);
check($admin->has('Verdacht') && !$admin->has('Tester'), 'Hauptadministrator sieht nach Wechsel die Polizei');
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => '1', 'return' => 'patients']);
check($admin->has('Tester'), 'Zurück zum Rettungsdienst');
// Deaktivierte Fraktion
$admin->get('/index.php?p=factions&edit=' . $pd);
$admin->post('/index.php?p=factions', ['action' => 'save', 'id' => (string) $pd, 'name' => 'Los Santos Police Department', 'short' => 'LSPD', 'color' => '#1d3a6e', 'modules' => ['records', 'billing', 'wanted', 'requests']]);
check($chief->get('/index.php?p=dashboard')->has('derzeit deaktiviert'), 'Deaktivierte Fraktion kann sich nicht anmelden');
texec('UPDATE factions SET active = 1 WHERE id = ?', [$pd]);
