<?php
// Justiz: Gerichtskalender, Fristen, Anwaltszugang, Geldstrafe ins Spiel
$doj = (int) tval("SELECT id FROM factions WHERE short = 'DOJ'");
texec('UPDATE factions SET modules = NULL WHERE id = ?', [$doj]); // Vorgabe der Art (inkl. Gericht)
texec("INSERT INTO users (username, name, password_hash, rank_id, faction_id, active, must_change_password, created_at) VALUES ('anwalt', 'Anna Anwalt', ?, (SELECT id FROM ranks WHERE faction_id = ? AND short = 'RA'), ?, 1, 0, ?)",
    [password_hash('TestPasswort123', PASSWORD_DEFAULT), $doj, $doj, date('Y-m-d H:i:s')]);
texec("INSERT INTO users (username, name, password_hash, rank_id, faction_id, active, must_change_password, created_at) VALUES ('anwalt2', 'Bert Beistand', ?, (SELECT id FROM ranks WHERE faction_id = ? AND short = 'RA'), ?, 1, 0, ?)",
    [password_hash('TestPasswort123', PASSWORD_DEFAULT), $doj, $doj, date('Y-m-d H:i:s')]);
$raRank = (int) tval("SELECT id FROM ranks WHERE faction_id = ? AND short = 'RA'", [$doj]);
check((bool) tval("SELECT 1 FROM rank_permissions WHERE rank_id = ? AND permission = 'court.own'", [$raRank]) && !tval("SELECT 1 FROM rank_permissions WHERE rank_id = ? AND permission = 'court.manage'", [$raRank]),
    'Anwaltsrang: nur eigene Termine, keine Verwaltung');
$judge = (new Client(TEST_BASE))->login('judge');
$anwalt = (int) tval("SELECT id FROM users WHERE username = 'anwalt'");
$jp = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND citizen_id = 'TST001'", [$doj]);

$when = date('Y-m-d\TH:i', strtotime('+20 hours'));
$judge->get('/index.php?p=court');
$judge->post('/index.php?p=court', ['action' => 'hearing', 'title' => 'Strafsache Verdacht', 'kind' => 'Hauptverhandlung', 'case_ref' => '2 Js 12/26', 'starts_at' => $when,
    'room' => 'Saal 1', 'judge_id' => (string) tval("SELECT id FROM users WHERE username = 'judge'"), 'lawyer_id' => (string) $anwalt, 'patient_id' => (string) $jp, 'participants' => 'Zeuge: Officer Olli']);
$h = trow('SELECT * FROM hearings WHERE faction_id = ? ORDER BY id DESC LIMIT 1', [$doj]);
check($h && $h['title'] === 'Strafsache Verdacht' && (int) $h['lawyer_id'] === $anwalt, 'Termin angelegt');
check((bool) tval("SELECT 1 FROM notifications WHERE user_id = ? AND title LIKE 'Neuer Termin%'", [$anwalt]), 'Rechtsbeistand benachrichtigt');
$judge->post('/index.php?p=court', ['action' => 'hearing', 'title' => 'Andere Sache', 'starts_at' => date('Y-m-d\TH:i', strtotime('+20 hours 30 minutes')), 'room' => 'Saal 1']);
check($judge->has('bereits belegt'), 'Saal nicht doppelt belegt');
$judge->post('/index.php?p=court', ['action' => 'hearing', 'title' => 'Haftprüfung Fremd', 'kind' => 'Haftprüfung', 'starts_at' => date('Y-m-d\TH:i', strtotime('+2 days')), 'room' => 'Saal 2']);
check($judge->get('/index.php?p=court')->has('Strafsache Verdacht') && $judge->has('Haftprüfung Fremd'), 'Gericht sieht alle Termine');

// Anwaltszugang
$a = (new Client(TEST_BASE))->login('anwalt');
$a->get('/index.php?p=court');
check($a->has('Strafsache Verdacht') && !$a->has('Haftprüfung Fremd') && !$a->has('Termin anlegen'), 'Anwalt sieht nur eigene Termine, ohne Verwaltung');
$a->post('/index.php?p=court', ['action' => 'hearing', 'title' => 'Selbst angelegt', 'starts_at' => $when]);
check($a->status === 403 && !tval("SELECT 1 FROM hearings WHERE title = 'Selbst angelegt'"), 'Anwalt kann keine Termine anlegen');
check(!(new Client(TEST_BASE))->login('anwalt2')->get('/index.php?p=court')->has('Strafsache Verdacht'), 'Anderer Anwalt sieht den Termin nicht');

// Fristen und Erinnerungen
$judge->post('/index.php?p=court', ['action' => 'deadline', 'title' => 'Einspruchsfrist Strafbefehl', 'kind' => 'Einspruchsfrist', 'due_date' => date('Y-m-d', strtotime('+1 day')), 'assigned_to' => (string) $anwalt, 'case_ref' => '2 Js 12/26']);
check((bool) tval("SELECT 1 FROM deadlines WHERE title = 'Einspruchsfrist Strafbefehl' AND faction_id = ?", [$doj]), 'Frist eingetragen');
check($a->get('/index.php?p=court&view=deadlines')->has('Einspruchsfrist Strafbefehl'), 'Anwalt sieht seine Frist');
texec("DELETE FROM notifications WHERE user_id = ?", [$anwalt]);
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1', $o);
check(str_contains(implode("\n", $o), 'Gericht:') && (int) tval("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND kind = 'court'", [$anwalt]) === 2, 'Cron erinnert an Termin (morgen) und Frist');
$judge->get('/index.php?p=court&view=deadlines');
$judge->post('/index.php?p=court', ['action' => 'deadline_done', 'id' => tval("SELECT id FROM deadlines WHERE title = 'Einspruchsfrist Strafbefehl'")]);
check((bool) tval("SELECT done_at FROM deadlines WHERE title = 'Einspruchsfrist Strafbefehl'"), 'Frist erledigt');

// Vertagen
$judge->get('/index.php?p=court');
$judge->post('/index.php?p=court', ['action' => 'hearing_status', 'id' => $h['id'], 'status' => 'postponed', 'result' => 'Zeuge erkrankt', 'new_date' => date('Y-m-d\T10:00', strtotime('+7 days'))]);
check(tval('SELECT status FROM hearings WHERE id = ?', [$h['id']]) === 'postponed' && (int) tval("SELECT COUNT(*) FROM hearings WHERE title = 'Strafsache Verdacht' AND status = 'planned'") === 1, 'Vertagt mit neuem Termin');

// Geldstrafe per Strafbefehl ins Spiel
texec("UPDATE patients SET citizen_id = 'TST001' WHERE id = ?", [$jp]);
$judge->get('/index.php?p=document_new&type=doj_strafbefehl&patient=' . $jp);
$judge->post('/index.php?p=document_new&type=doj_strafbefehl', ['type' => 'doj_strafbefehl', 'patient_id' => (string) $jp, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[aktenzeichen]' => '2 Js 12/26', 'd[tat]' => 'Diebstahl', 'd[strafe]' => 'Geldstrafe', 'd[betrag]' => '$ 2.500']);
$sb = (int) tval("SELECT id FROM documents WHERE type = 'doj_strafbefehl' ORDER BY id DESC LIMIT 1");
check($sb && $judge->has('Geldstrafe im Spiel') && $judge->has('Ins Spiel senden'), 'Strafbefehl bietet „Ins Spiel senden“');
$judge->post('/index.php?p=document&id=' . $sb, ['action' => 'game_bill']);
$gb = trow('SELECT * FROM game_bills WHERE document_id = ?', [$sb]);
check($gb && (int) $gb['amount'] === 2500 && $gb['society'] === 'society_doj' && str_contains($gb['label'], 'Geldstrafe'), 'Geldstrafe über $2500 an society_doj');
api('bill_result', ['id' => $gb['id'], 'result' => 'sent', 'esx_id' => 501]);
api('bill_result', ['id' => $gb['id'], 'result' => 'paid']);
check(tval('SELECT pay_status FROM documents WHERE id = ?', [$sb]) === 'paid', 'Geldstrafe im Spiel bezahlt');
