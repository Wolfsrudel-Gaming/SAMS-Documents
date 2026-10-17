<?php
// Zusammenarbeit: Haftbefehl → Fahndung, Kennzeichenabfrage, Behördenanfragen, Freigabe an Behörde
$admin = (new Client(TEST_BASE))->login('admin');
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
$doj = (int) tval("SELECT id FROM factions WHERE short = 'DOJ'");
check($pd && $doj, 'Polizei und Justiz aus dem vorherigen Test vorhanden');
texec("DELETE FROM rank_permissions WHERE permission = 'security.2fa_required' AND rank_id IN (SELECT id FROM ranks WHERE faction_id = ?)", [$doj]);
$mk = function (string $user, string $name, int $faction, string $rankShort) {
    texec("INSERT INTO users (username, name, password_hash, rank_id, faction_id, active, must_change_password, created_at) VALUES (?, ?, ?, (SELECT id FROM ranks WHERE faction_id = ? AND short = ?), ?, 1, 0, ?)",
        [$user, $name, password_hash('TestPasswort123', PASSWORD_DEFAULT), $faction, $rankShort, $faction, date('Y-m-d H:i:s')]);
    return (new Client(TEST_BASE))->login($user);
};
$judge = $mk('judge', 'Judith Judge', $doj, 'RI');
$officer = (new Client(TEST_BASE))->login('officer');
// Richterin legt Akte an und erlässt einen Haftbefehl
$judge->get('/index.php?p=patient_edit');
$judge->post('/index.php?p=patient_edit', ['first_name' => 'Vinnie', 'last_name' => 'Verdacht', 'dob' => '1985-05-05', 'gender' => 'm', 'citizen_id' => 'TST001']);
$jp = (int) tval('SELECT id FROM patients WHERE faction_id = ? AND citizen_id = ?', [$doj, 'TST001']);
$judge->get('/index.php?p=document_new&type=doj_haftbefehl&patient=' . $jp);
$judge->post('/index.php?p=document_new&type=doj_haftbefehl', ['type' => 'doj_haftbefehl', 'patient_id' => (string) $jp, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[aktenzeichen]' => '2 Js 12/26', 'd[tatvorwurf]' => 'Bewaffneter Raubüberfall', 'd[haftgrund]' => 'Fluchtgefahr', 'd[gueltig_bis]' => date('Y-m-d', strtotime('+30 days')), 'd[gefaehrlich]' => '1']);
$warrant = (int) tval("SELECT id FROM documents WHERE type = 'doj_haftbefehl' ORDER BY id DESC LIMIT 1");
check($warrant && $judge->has('Untersuchungshaft'), 'Haftbefehl erlassen');
$w = trow('SELECT * FROM wanted WHERE document_id = ? AND faction_id = ?', [$warrant, $pd]);
check($w && $w['status'] === 'open' && (int) $w['danger'] === 3 && $w['citizen_id'] === 'TST001', 'Haftbefehl erscheint in der Fahndung der Polizei');
check(!tval('SELECT id FROM wanted WHERE document_id = ? AND faction_id IN (1, ?)', [$warrant, $doj]), 'Nicht beim Rettungsdienst und nicht bei der Justiz selbst');
$officer->get('/index.php?p=wanted');
check($officer->has('Bewaffneter Raubüberfall') && $officer->has('Haftbefehl DOJ'), 'Polizei sieht den Haftbefehl in der Fahndung');
check($officer->get('/index.php?p=shared&id=' . $warrant)->has('Untersuchungshaft') && $officer->has('Department of Justice'), 'Polizei kann den Haftbefehl selbst einsehen');
$pp = (int) tval('SELECT id FROM patients WHERE faction_id = ? AND citizen_id = ?', [$pd, 'TST001']);
check($officer->get('/index.php?p=patient&id=' . $pp)->has('Offener Haftbefehl'), 'Hinweis in der Personenakte der Polizei');
check((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=wanted')->status === 403, 'Rettungsdienst hat keine Fahndung');
// Abfragen über die API (Spiel)
[$st, $res] = api('wanted_check', ['citizen_id' => 'TST001']);
check($st === 200 && $res['wanted'] === true && ($res['entries'][0]['warrant'] ?? false) === true && ($res['entries'][0]['agency'] ?? '') === 'LSPD', 'API: Personenabfrage findet den Haftbefehl');
$officer->get('/index.php?p=wanted');
$officer->post('/index.php?p=wanted', ['action' => 'create', 'kind' => 'vehicle', 'plate' => '12 abc 345', 'vehicle' => 'Schwarzer Sultan', 'reason' => 'Fluchtfahrzeug Raub', 'danger' => '2']);
[$st, $res] = api('wanted_check', ['plate' => '12ABC345']);
check($res['wanted'] === true && ($res['entries'][0]['vehicle'] ?? '') === 'Schwarzer Sultan', 'API: Kennzeichenabfrage (Schreibweise egal)');
[$st, $res] = api('person_check', ['citizen_id' => 'TST001']);
check($res['warrant'] === true && $res['danger'] === 3 && $res['license_driver'] === 'entzogen', 'API: Personenprüfung mit Führerscheinstatus');
$offId = (int) tval("SELECT id FROM users WHERE username = 'officer'");
// Festnahme: Fahndung erledigen → Justiz sieht „vollstreckt“
$wid = (int) tval('SELECT id FROM wanted WHERE document_id = ? AND faction_id = ?', [$warrant, $pd]);
$officer->post('/index.php?p=wanted', ['action' => 'close', 'id' => (string) $wid, 'note' => 'Festgenommen Grove Street']);
check(tval('SELECT status FROM wanted WHERE id = ?', [$wid]) === 'closed', 'Fahndung erledigt');
check($judge->get('/index.php?p=wanted')->has('vollstreckt') && $judge->has('Festgenommen Grove Street'), 'Justiz sieht den Haftbefehl als vollstreckt');
// Stornierter Haftbefehl beendet offene Fahndungen
$judge->post('/index.php?p=document_new&type=doj_haftbefehl', ['type' => 'doj_haftbefehl', 'patient_id' => (string) $jp, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[aktenzeichen]' => '2 Js 13/26', 'd[tatvorwurf]' => 'Betrug', 'd[haftgrund]' => 'Verdunkelungsgefahr', 'd[gueltig_bis]' => date('Y-m-d', strtotime('+10 days'))]);
$w2 = (int) tval("SELECT id FROM documents WHERE type = 'doj_haftbefehl' ORDER BY id DESC LIMIT 1");
$judge->get('/index.php?p=document&id=' . $w2);
$judge->post('/index.php?p=document&id=' . $w2, ['action' => 'revoke', 'reason' => 'Aufgehoben']);
check(!tval("SELECT COUNT(*) FROM wanted WHERE document_id = ? AND status = 'open'", [$w2]), 'Aufgehobener Haftbefehl beendet die Fahndung');
// Behördenanfrage Polizei → Rettungsdienst mit Dokumentfreigabe
$officer->get('/index.php?p=requests&new=1');
check($officer->has('San Andreas Medical Services'), 'Rettungsdienst als Empfänger wählbar');
$officer->post('/index.php?p=requests', ['action' => 'create', 'to_faction_id' => '1', 'subject' => 'Befund nach Festnahme', 'person' => 'Tom Tester', 'citizen_id' => 'TST001', 'body' => 'Bitte Befund übermitteln.', 'urgent' => '1']);
$rq = (int) tval("SELECT id FROM agency_requests WHERE subject = 'Befund nach Festnahme'");
$chiefId = (int) tval("SELECT id FROM users WHERE username = 'chief'");
check($rq && (bool) tval("SELECT id FROM notifications WHERE user_id = ? AND kind = 'request'", [$chiefId]), 'Leitung des Rettungsdienstes wird benachrichtigt');
$chief = (new Client(TEST_BASE))->login('chief');
$samsDoc = (int) tval("SELECT d.id FROM documents d JOIN patients p ON p.id = d.patient_id WHERE d.faction_id = 1 AND p.citizen_id = 'TST001' AND d.status <> 'revoked' ORDER BY d.id LIMIT 1");
$chief->get('/index.php?p=requests&id=' . $rq);
check($chief->has('Befund nach Festnahme') && $chief->match('/name="docs\[\]" value="' . $samsDoc . '"/'), 'Eingehende Anfrage mit passenden Dokumenten');
$chief->post('/index.php?p=requests', ['id' => (string) $rq, 'action' => 'answer', 'answer' => 'Befund anbei.', 'docs[]' => [(string) $samsDoc]]);
check(tval('SELECT status FROM agency_requests WHERE id = ?', [$rq]) === 'answered' && (bool) tval("SELECT id FROM notifications WHERE user_id = ? AND kind = 'request'", [$offId]), 'Beantwortet, Polizei benachrichtigt');
$officer->get('/index.php?p=requests&id=' . $rq);
check($officer->has('Befund anbei.') && $officer->get('/index.php?p=shared&id=' . $samsDoc)->status === 200, 'Polizei kann das freigegebene Dokument öffnen');
$otherSams = (int) tval("SELECT id FROM documents WHERE faction_id = 1 AND id <> ? ORDER BY id DESC LIMIT 1", [$samsDoc]);
check($officer->get('/index.php?p=shared&id=' . $otherSams)->status === 404, 'Andere Dokumente bleiben gesperrt');
// Direkte Freigabe an eine Behörde
$chief->get('/index.php?p=document&id=' . $otherSams);
$chief->post('/index.php?p=document&id=' . $otherSams, ['action' => 'share', 'kind' => 'faction', 'target_faction_id' => (string) $doj, 'hours' => '24', 'purpose' => 'Gutachten']);
check($judge->get('/index.php?p=shared')->has('Gutachten') && $judge->get('/index.php?p=shared&id=' . $otherSams)->status === 200, 'Freigabe an die Justiz');
check($officer->get('/index.php?p=shared&id=' . $otherSams)->status === 404, 'Polizei sieht die Freigabe an die Justiz nicht');
