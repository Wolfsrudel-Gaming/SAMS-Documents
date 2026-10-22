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

// Untersuchungsbogen Sonderverwendungsfähigkeit → Bescheinigung an die Leitung der TAS
$c->post('/index.php?p=fitness', ['action' => 'standard', 'standard' => 'TAS-Standard SR-1 (Spezialkräfte)']);
$c->get('/index.php?p=fitness');
check($c->has('TAS-Standard SR-1') && $c->has('Druckkammertest') && $c->has('Mindestanforderung'), 'TAS legt ihren Untersuchungsstandard fest, Anforderungen je Verwendung sichtbar');
$sams->get('/index.php?p=document_new&type=sv_untersuchung&patient=' . $pid);
check($sams->has('Untersuchungsbogen Sonderverwendungsfähigkeit') && $sams->has('Tonaudiogramm') && $sams->has('PULHES'), 'Untersuchungsbogen beim SAMS');
$sv = ['d[anlass]' => 'Einstellungsuntersuchung', 'd[exam_date]' => date('Y-m-d'), 'd[groesse]' => '182', 'd[gewicht]' => '80', 'd[rr_sys]' => '125', 'd[rr_dia]' => '80', 'd[puls]' => '58',
    'd[visus_sc_r]' => '1,0', 'd[visus_sc_l]' => '0,9', 'd[visus_cc_r]' => '1,0', 'd[visus_cc_l]' => '1,0', 'd[farbsinn]' => 'normal', 'd[stereo]' => 'normal', 'd[gesichtsfeld]' => 'normal',
    'd[hr_500]' => '10', 'd[hr_1000]' => '10', 'd[hr_2000]' => '15', 'd[hr_4000]' => '30', 'd[hl_500]' => '10', 'd[hl_1000]' => '15', 'd[hl_2000]' => '20', 'd[hl_4000]' => '35',
    'd[druckausgleich]' => 'beidseits frei', 'd[ekg]' => 'unauffällig', 'd[ergo_watt]' => '280', 'd[ergo_befund]' => 'unauffällig', 'd[fvc]' => '98', 'd[fev1]' => '95', 'd[tiffeneau]' => '82',
    'd[roentgen]' => 'ohne Befund', 'd[labor]' => 'ohne Befund', 'd[drogen]' => 'negativ', 'd[lauf]' => '12:40', 'd[liegestuetz]' => '40', 'd[situps]' => '70', 'd[klimmzug]' => '12',
    'd[schwimmen]' => '10:50', 'd[streckentauchen]' => '30', 'd[druckkammer]' => 'bestanden', 'd[huet]' => 'bestanden', 'd[psych]' => 'unauffällig',
    'd[pulhes_p]' => '1', 'd[pulhes_u]' => '1', 'd[pulhes_l]' => '1', 'd[pulhes_h]' => '2', 'd[pulhes_e]' => '1', 'd[pulhes_s]' => '1',
    'd[v_g31]' => 'geeignet', 'd[v_hems]' => 'bedingt', 'd[v_allg]' => 'geeignet', 'd[gesamt]' => 'bedingt', 'd[einschraenkungen]' => 'Windeneinsatz erst nach Nachschulung HUET in 3 Monaten',
    'd[bemerkung]' => 'Z. n. Trommelfellperforation rechts 2019, folgenlos verheilt'];
$sams->post('/index.php?p=document_new&type=sv_untersuchung', ['type' => 'sv_untersuchung', 'patient_id' => (string) $pid, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE] + $sv);
$svDoc = trow("SELECT * FROM documents WHERE type = 'sv_untersuchung' AND patient_id = ?", [$pid]);
$certId = (int) (json_decode((string) ($svDoc['data'] ?? '{}'), true)['_certificate'] ?? 0);
$cert = trow('SELECT * FROM documents WHERE id = ?', [$certId]);
check($svDoc && $cert && $cert['type'] === 'einsatztauglichkeit' && $cert['valid_until'] === date('Y-m-d', strtotime('+12 months')), 'Bescheinigung automatisch erzeugt, kürzeste Regelfrist');
check(!str_contains((string) $cert['data'], 'Trommelfellperforation') && !str_contains((string) $cert['data'], '"liegestuetz"') && str_contains((string) $cert['data'], 'Tonaudiometrie') && str_contains((string) $cert['data'], 'Leitung Technische Abteilung Spezialrettung'), 'Bescheinigung ohne Befunde, an die Leitung der TAS');
check((int) tval('SELECT COUNT(*) FROM fitness_records WHERE document_id = ? AND faction_id = ?', [$certId, $tas]) === 3
    && tval("SELECT result FROM fitness_records WHERE document_id = ? AND kind = 'hems'", [$certId]) === 'bedingt', 'Ergebnis je Verwendung an die TAS');
$v = (new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $svDoc['view_token']);
check($v->has('Abgleich mit dem Standard') && $v->has('✗ nicht erfüllt') && $v->has('Liegestütze') && $v->has('VERTRAULICH'), 'Bogen gleicht Messwerte mit dem Standard ab');
$c->get('/index.php?p=shared&id=' . $certId);
check($c->status === 200 && $c->has('EINGESCHRÄNKT VERWENDUNGSFÄHIG') && $c->has('P1 U1 L1 H2 E1 S1') && $c->has('TAS-Standard SR-1') && !$c->has('Trommelfellperforation') && !$c->has('12:40'), 'TAS-Leitung erhält Bescheinigung mit Profil und Standard');
check((new Client(TEST_BASE))->login('tas.chief')->get('/index.php?p=shared&id=' . $svDoc['id'])->status !== 200, 'Untersuchungsbogen bleibt beim SAMS');
$sams->get('/index.php?p=document&id=' . $svDoc['id']);
check($sams->has('Vertrauliche ärztliche Unterlage') && $sams->has($cert['number']), 'SAMS sieht Bogen mit Verweis auf die Bescheinigung');
if ($shot = getenv('SAMS_SHOT_DIR')) {
    $ct = (string) tval('SELECT view_token FROM documents WHERE id = ?', [$certId]);
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1000,height:1400}});"
        . "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=" . $svDoc['view_token'] . "');await p.waitForTimeout(300);await p.screenshot({path:'$shot/sv_bogen.png',fullPage:true});"
        . "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=$ct');await p.waitForTimeout(300);await p.screenshot({path:'$shot/sv_cert.png',fullPage:true});"
        . "await b.close();})();";
    file_put_contents("$shot/sv.js", $js);
    exec('node ' . escapeshellarg("$shot/sv.js") . ' 2>&1', $svOut);
    echo implode("\n", $svOut);
}
