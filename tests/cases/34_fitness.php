<?php
// Einsatztauglichkeit: Anforderung durch die TAS, Untersuchung beim SAMS, nur das Ergebnis geht an die Dienststelle
$tas = (int) tval("SELECT id FROM factions WHERE short = 'TAS'");
$fd = (int) tval("SELECT faction_id FROM users WHERE username = 'fd.chief'");
foreach ([$tas, $fd] as $f) {
    $mods = json_decode((string) tval('SELECT modules FROM factions WHERE id = ?', [$f]), true);
    if (is_array($mods) && !in_array('fitness', $mods, true)) {
        texec('UPDATE factions SET modules = ? WHERE id = ?', [json_encode(array_merge($mods, ['fitness'])), $f]);
    }
}
$tasUser = (int) tval("SELECT id FROM users WHERE username = 'tas.chief'");
$c = (new Client(TEST_BASE))->login('tas.chief');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $c->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav, 'Tauglichkeit'), 'Menüpunkt Tauglichkeit bei der TAS');
check(str_contains((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=dashboard')->body, 'p=fitness') === false, 'Kein Tauglichkeitsmodul beim SAMS selbst');

// Bürger-ID am Konto (Leitung)
$c->get('/index.php?p=user_edit&id=' . $tasUser);
check($c->has('Bürger-ID im Spiel'), 'Bürger-ID am Konto pflegbar');
texec("UPDATE users SET citizen_id = 'char1:tas01' WHERE id = ?", [$tasUser]);
$c->get('/index.php?p=fitness');
check($c->has('Tauchen') && $c->has('Pilot/in') && $c->has('kein Nachweis'), 'Übersicht mit Untersuchungsarten der Spezialrettung');

// Anforderung beim Rettungsdienst
$c->post('/index.php?p=fitness', ['action' => 'request', 'user_id' => $tasUser, 'kind' => 'g31', 'to_faction_id' => 1, 'note' => 'Einstellung']);
$req = trow("SELECT * FROM agency_requests WHERE faction_id = ? AND reference = ?", [$tas, "fitness:$tasUser:g31"]);
check($req && (int) $req['to_faction_id'] === 1 && $req['citizen_id'] === 'char1:tas01' && str_contains($req['subject'], 'Tauchtauglichkeit'), 'Untersuchung als Behördenanfrage an den SAMS');
$c->post('/index.php?p=fitness', ['action' => 'request', 'user_id' => $tasUser, 'kind' => 'g31', 'to_faction_id' => 1]);
check((int) tval("SELECT COUNT(*) FROM agency_requests WHERE reference = ?", ["fitness:$tasUser:g31"]) === 1, 'Keine doppelte Anforderung');

// Untersuchung beim SAMS
$now = date('Y-m-d H:i:s');
texec("INSERT INTO patients (faction_id, first_name, last_name, dob, citizen_id, created_at, updated_at) VALUES (1, 'Toni', 'Taucher', '1990-04-04', 'char1:tas01', ?, ?)", [$now, $now]);
$pid = (int) tval("SELECT id FROM patients WHERE citizen_id = 'char1:tas01' AND faction_id = 1");
$sams = (new Client(TEST_BASE))->login('arzt');
$sams->get('/index.php?p=document_new&type=einsatztauglichkeit&patient=' . $pid);
check($sams->has('Tauchtauglichkeit (G 31') && $sams->has('vertraulich'), 'Dokumentart Einsatztauglichkeit beim SAMS');
$sams->post('/index.php?p=document_new&type=einsatztauglichkeit', ['type' => 'einsatztauglichkeit', 'patient_id' => (string) $pid, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[art]' => 'g31', 'd[exam_date]' => date('Y-m-d'), 'd[umfang][]' => 'HNO / Druckausgleich', 'd[befund]' => 'Trommelfellnarbe links, Tubenfunktion gut',
    'd[result]' => 'bedingt', 'd[conditions]' => 'Tauchtiefe max. 20 m']);
$doc = trow("SELECT * FROM documents WHERE type = 'einsatztauglichkeit' AND patient_id = ?", [$pid]);
check($doc && $doc['valid_until'] === date('Y-m-d', strtotime('+12 months')) && !str_contains((string) $doc['data'], 'Trommelfell'), 'Regelfrist gesetzt, Befunde nicht im Dokument');
check((bool) tval("SELECT 1 FROM entries WHERE patient_id = ? AND confidential = 1 AND content LIKE '%Trommelfell%'", [$pid]), 'Befunde als vertraulicher Akteneintrag');
$rec = trow('SELECT * FROM fitness_records WHERE document_id = ?', [$doc['id']]);
check($rec && (int) $rec['faction_id'] === $tas && (int) $rec['user_id'] === $tasUser && $rec['result'] === 'bedingt' && $rec['restrictions'] === 'Tauchtiefe max. 20 m', 'Ergebnis an die Dienststelle übermittelt');
check(tval('SELECT status FROM agency_requests WHERE id = ?', [$req['id']]) === 'done' && (bool) tval("SELECT 1 FROM notifications WHERE user_id = ? AND kind = 'fitness'", [$tasUser]), 'Anforderung erledigt, Person benachrichtigt');
$sams->get('/index.php?p=document&id=' . $doc['id']);
check($sams->has('übermittelt an') && $sams->has('Technische Abteilung Spezialrettung'), 'SAMS sieht, an wen übermittelt wurde');
$c->get('/index.php?p=fitness');
check($c->has('mit Einschr.') && $c->has('Tauchtiefe max. 20 m') && $c->has('Bescheinigung'), 'TAS sieht Ergebnis und Einschränkung');
$c->get('/index.php?p=shared&id=' . $doc['id']);
check($c->status === 200 && $c->has('TAUGLICH MIT EINSCHRÄNKUNGEN') && !$c->has('Trommelfell'), 'Bescheinigung für die Dienststelle einsehbar, ohne Befunde');
check((new Client(TEST_BASE))->login('fd.chief')->get('/index.php?p=fitness')->has('Toni Taucher') === false, 'Andere Dienststellen sehen den Nachweis nicht');

// Atemschutz-Eignung bei der Feuerwehr: Nachweis aktualisiert die Atemschutz-Übersicht
$fdRank = (int) tval("SELECT rank_id FROM users WHERE username = 'fd.chief'");
if (!tval("SELECT 1 FROM rank_permissions WHERE rank_id = ? AND permission = 'fitness.manage'", [$fdRank])) {
    texec("INSERT INTO rank_permissions (rank_id, permission) VALUES (?, 'fitness.manage')", [$fdRank]);
}
$fdc = (new Client(TEST_BASE))->login('fd.chief');
$fdUser = (int) tval("SELECT id FROM users WHERE username = 'fd.chief'");
$fdc->get('/index.php?p=fitness');
$fdc->post('/index.php?p=fitness', ['action' => 'manual', 'user_id' => $fdUser, 'kind' => 'g263', 'result' => 'geeignet', 'examined_on' => date('Y-m-d'), 'valid_until' => date('Y-m-d', strtotime('+10 days')), 'examiner' => 'Betriebsarzt']);
check(tval('SELECT g26_until FROM breathing_status WHERE user_id = ?', [$fdUser]) === date('Y-m-d', strtotime('+10 days')), 'G 26.3 übernimmt die Atemschutz-Eignung');
texec("DELETE FROM notifications WHERE kind = 'fitness'");
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1', $o);
check(str_contains(implode("\n", $o), 'Tauglichkeit: 1') && (bool) tval("SELECT 1 FROM notifications WHERE user_id = ? AND kind = 'fitness'", [$fdUser]), 'Hinweis vor Ablauf');

// Storno beim SAMS zieht das Ergebnis zurück
$sams->get('/index.php?p=document&id=' . $doc['id']);
$sams->post('/index.php?p=document&id=' . $doc['id'], ['action' => 'revoke', 'reason' => 'Falsche Untersuchungsart']);
check((int) tval('SELECT revoked FROM fitness_records WHERE id = ?', [$rec['id']]) === 1, 'Storno zieht den Nachweis zurück');
$c->get('/index.php?p=fitness');
check($c->has('zurückgezogen'), 'TAS sieht den zurückgezogenen Nachweis');
