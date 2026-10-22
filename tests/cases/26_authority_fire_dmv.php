<?php
// Feuerwehr und Verwaltung/DMV: Lizenzen mit Ablauf, Objektpläne, gemeinsame Einsatzberichte
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=factions');
$admin->post('/index.php?p=factions', ['action' => 'create', 'name' => 'Los Santos Fire Department', 'short' => 'LSFD', 'kind' => 'fire', 'color' => '#a3161a', 'lead_username' => 'fd.chief', 'lead_name' => 'Fiona Feuer']);
$admin->get('/index.php?p=factions');
$admin->post('/index.php?p=factions', ['action' => 'create', 'name' => 'Department of Motor Vehicles', 'short' => 'DMV', 'kind' => 'government', 'color' => '#2f5d50', 'lead_username' => 'dmv.chief', 'lead_name' => 'Dora Dmv']);
$fd = (int) tval("SELECT id FROM factions WHERE short = 'LSFD'");
$dmv = (int) tval("SELECT id FROM factions WHERE short = 'DMV'");
check($fd && $dmv, 'Feuerwehr und DMV angelegt');
foreach (['fd.chief' => $fd, 'dmv.chief' => $dmv] as $u => $f) {
    texec('UPDATE users SET password_hash = ?, must_change_password = 0 WHERE username = ?', [password_hash('TestPasswort123', PASSWORD_DEFAULT), $u]);
    texec("DELETE FROM rank_permissions WHERE permission = 'security.2fa_required' AND rank_id IN (SELECT id FROM ranks WHERE faction_id = ?)", [$f]);
}
$fdc = (new Client(TEST_BASE))->login('fd.chief');
$dmvc = (new Client(TEST_BASE))->login('dmv.chief');
$nav = fn(Client $c) => preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $c->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav($fdc), 'Objektpläne') && str_contains($nav($fdc), 'Einsatzberichte') && !str_contains($nav($fdc), 'Lizenzen'), 'Feuerwehr: Objektpläne und Einsatzberichte');
check(str_contains($nav($dmvc), 'Lizenzen') && str_contains($nav($dmvc), 'Fahrzeugregister'), 'DMV: Lizenzen und Fahrzeugregister');

// Lizenzen
$dmvc->get('/index.php?p=licenses');
check($dmvc->has('Bootsführerschein'), 'Lizenzarten der Verwaltung vorbelegt');
$dmvc->post('/index.php?p=licenses', ['action' => 'issue', 'name' => 'Rico Raser', 'citizen_id' => 'TST777', 'type' => 'Führerschein', 'classes' => 'B', 'expires_on' => date('Y-m-d', strtotime('+5 days'))]);
$lic = trow("SELECT * FROM licenses WHERE citizen_id = 'TST777' AND faction_id = ?", [$dmv]);
check($lic && $lic['status'] === 'valid' && preg_match('/^LIZ-\d{4}-0001$/', $lic['number']), 'Führerschein eingetragen');
$dmvc->post('/index.php?p=licenses', ['action' => 'issue', 'name' => 'Rico Raser', 'citizen_id' => 'TST777', 'type' => 'Führerschein']);
check($dmvc->has('bereits eine Führerschein'), 'Keine doppelte Lizenz');
$dmvc->post('/index.php?p=licenses', ['action' => 'suspend', 'id' => $lic['id'], 'note' => '']);
check(tval('SELECT status FROM licenses WHERE id = ?', [$lic['id']]) === 'valid', 'Sperre nur mit Grund');
[, $r] = api('person_check', ['citizen_id' => 'TST777']);
check(($r['licenses'][0]['label'] ?? '') === 'gültig' && ($r['licenses'][0]['agency'] ?? '') === 'DMV', 'Personenabfrage im Spiel zeigt den Führerschein');
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1', $o);
check(str_contains(implode("\n", $o), 'Lizenzen: 1') && (bool) tval("SELECT 1 FROM notifications n JOIN users u ON u.id = n.user_id WHERE u.username = 'dmv.chief' AND n.kind = 'licenses'"), 'Hinweis auf bald ablaufende Lizenz');
$dmvc->post('/index.php?p=licenses', ['action' => 'suspend', 'id' => $lic['id'], 'note' => 'Trunkenheitsfahrt', 'until' => date('Y-m-d', strtotime('+30 days'))]);
[, $r] = api('person_check', ['citizen_id' => 'TST777']);
check(($r['licenses'][0]['state'] ?? '') === 'suspended', 'Gesperrt – auch in der Abfrage');
texec("UPDATE licenses SET status = 'valid', expires_on = ? WHERE id = ?", [date('Y-m-d', strtotime('-1 day')), $lic['id']]);
[, $r] = api('person_check', ['citizen_id' => 'TST777']);
check(($r['licenses'][0]['state'] ?? '') === 'expired', 'Abgelaufen wird erkannt');
$officer = (new Client(TEST_BASE))->login('officer');
$rid = (int) tval("SELECT id FROM patients WHERE citizen_id = 'TST777' AND faction_id = (SELECT id FROM factions WHERE short = 'LSPD')");
check($officer->get('/index.php?p=patient&id=' . $rid)->has('Führerschein') && $officer->has('abgelaufen'), 'Polizei sieht den Führerschein in der Personenakte');
check($fdc->get('/index.php?p=licenses')->status === 403, 'Feuerwehr hat kein Lizenzregister');

// Objektpläne
$fdc->get('/index.php?p=objects');
$fdc->post('/index.php?p=objects', ['name' => 'Lagerhalle Elysian Island', 'address' => 'Elysian Fields Fwy 3', 'kind' => 'Industrie / Lager', 'risk' => '2']);
$obj = (int) tval('SELECT id FROM objects WHERE faction_id = ?', [$fd]);
check($obj > 0, 'Objekt angelegt');
$fdc->post('/index.php?p=object&id=' . $obj, ['action' => 'save', 'name' => 'Lagerhalle Elysian Island', 'kind' => 'Industrie / Lager', 'risk' => '3', 'hazards' => 'Propangas 2 t, Gasflaschenlager Nordseite',
    'keybox' => 'FSD am Haupttor', 'next_inspection' => date('Y-m-d', strtotime('-1 day'))]);
check($fdc->get('/index.php?p=object&id=' . $obj)->has('Propangas 2 t') && $fdc->has('FSD am Haupttor'), 'Gefahren und Schlüsseldepot gespeichert');
check($fdc->get('/index.php?p=objects')->has('überfällig'), 'Überfällige Brandschau markiert');
$img = tempnam(sys_get_temp_dir(), 'plan') . '.png';
$im = imagecreatetruecolor(40, 30);
imagepng($im, $img);
$fdc->get('/index.php?p=object&id=' . $obj);
$fdc->upload('/index.php?p=object&id=' . $obj, ['action' => 'upload', 'title' => 'Grundriss EG'], ['file' => $img]);
$file = (int) tval('SELECT id FROM object_files WHERE object_id = ?', [$obj]);
check($file > 0 && $fdc->has('Grundriss EG'), 'Plan hochgeladen');
check($fdc->get('/index.php?p=object_file&id=' . $file)->status === 200, 'Plan abrufbar');
check($officer->get('/index.php?p=object_file&id=' . $file)->status === 403 && $officer->get('/index.php?p=object&id=' . $obj)->status === 403, 'Andere Fraktion ohne Objektpläne gesperrt');

// Gemeinsame Einsatzberichte
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
$fdc->get('/index.php?p=joint');
$fdc->post('/index.php?p=joint', ['title' => 'Großbrand Lagerhalle', 'location' => 'Elysian Island', 'summary' => 'Vollbrand, eine verletzte Person', 'factions[]' => ['1', (string) $pd]]);
$jr = (int) tval('SELECT id FROM joint_reports WHERE faction_id = ?', [$fd]);
check($jr > 0 && (int) tval('SELECT COUNT(*) FROM joint_participants WHERE report_id = ?', [$jr]) === 2, 'Bericht mit Rettungsdienst und Polizei angelegt');
$fdc->post('/index.php?p=joint_report&id=' . $jr, ['action' => 'section', 'body' => 'Brandbekämpfung mit 2 HLF, Riegelstellung zur Nachbarhalle']);
$nfs = (new Client(TEST_BASE))->login('nfs');
check($nfs->get('/index.php?p=joint')->has('eigener Teil fehlt'), 'Rettungsdienst sieht den Bericht mit Hinweis');
$nfs->get('/index.php?p=joint_report&id=' . $jr);
check($nfs->has('Riegelstellung'), 'Teil der Feuerwehr sichtbar');
$nfs->post('/index.php?p=joint_report&id=' . $jr, ['action' => 'section', 'body' => '1 Patient mit Rauchgasintoxikation ins Pillbox transportiert']);
$officer->get('/index.php?p=joint_report&id=' . $jr);
$officer->post('/index.php?p=joint_report&id=' . $jr, ['action' => 'section', 'body' => 'Absperrung, Brandursachenermittlung eingeleitet']);
check((int) tval('SELECT COUNT(*) FROM joint_sections WHERE report_id = ?', [$jr]) === 3, 'Jede Behörde schreibt ihren Teil');
$officer->post('/index.php?p=joint_report&id=' . $jr, ['action' => 'close']);
check(tval('SELECT status FROM joint_reports WHERE id = ?', [$jr]) === 'open', 'Nur die anlegende Behörde schließt ab');
$judge = (new Client(TEST_BASE))->login('judge');
check(in_array($judge->get('/index.php?p=joint_report&id=' . $jr)->status, [403, 404], true) && !$judge->has('Riegelstellung'), 'Nicht beteiligte Behörde sieht den Bericht nicht');
$fdc->post('/index.php?p=joint_report&id=' . $jr, ['action' => 'close']);
$nfs->post('/index.php?p=joint_report&id=' . $jr, ['action' => 'section', 'body' => 'geändert']);
check(tval('SELECT status FROM joint_reports WHERE id = ?', [$jr]) === 'closed' && $nfs->has('abgeschlossen') && !tval("SELECT 1 FROM joint_sections WHERE body = 'geändert'"), 'Abgeschlossen: keine Änderungen mehr');
