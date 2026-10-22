<?php
// Unterschriften im Spiel: Ausdruck unterschreiben, Abschluss sperrt das Dokument, Empfangsbestätigung, Prüfsumme
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
$officer = (new Client(TEST_BASE))->login('officer');
$rid = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND citizen_id = 'TST777'", [$pd]);
$png = function (): string {
    $im = imagecreatetruecolor(300, 90);
    for ($i = 0; $i < 400; $i++) {
        imagesetpixel($im, random_int(0, 299), random_int(0, 89), random_int(0, 0xffffff));
    }
    ob_start();
    imagepng($im);
    return 'data:image/png;base64,' . base64_encode((string) ob_get_clean());
};
$issue = function () use ($officer, $rid): int {
    $officer->get('/index.php?p=document_new&type=pd_sicherstellung&patient=' . $rid);
    $officer->post('/index.php?p=document_new&type=pd_sicherstellung', ['type' => 'pd_sicherstellung', 'patient_id' => (string) $rid, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
        'd[datum]' => date('Y-m-d'), 'd[gegenstaende]' => 'Pistole 9 mm, AS-2026-0001', 'd[grund]' => 'Beweismittel']);
    return (int) (preg_match('/p=document&id=(\d+)/', $officer->url, $m) ? $m[1] : 0);
};

// 1. Ausgestellt mit leerem Unterschriftsfeld der betroffenen Person
$id = $issue();
$doc = trow('SELECT * FROM documents WHERE id = ?', [$id]);
check($id > 0 && $officer->has('Unterschriften') && $officer->has('1 offen') && $officer->has('Unterschrift betroffene Person'), 'Offenes Unterschriftsfeld am Dokument');
[$st, $r] = api('document_item', ['token' => $doc['view_token']]);
check($st === 200 && $r['signing']['final'] === false && $r['signing']['open'] === 1 && $r['signing']['fields'][0]['name'] === 'sig_betroffen', 'Item kennt das offene Feld');

// 2. Im Spiel unterschreiben
[, $r] = api('doc_sign', ['token' => $doc['view_token'], 'field' => 'sig_betroffen', 'signature' => 'data:image/png;base64,AAAA', 'signer_name' => 'Rico Raser']);
check($r['ok'] === false && str_contains($r['error'], 'ungültig'), 'Ungültige Unterschrift abgelehnt');
[, $r] = api('doc_sign', ['token' => $doc['view_token'], 'field' => 'gibtsnicht', 'signature' => $png(), 'signer_name' => 'Rico Raser']);
check($r['ok'] === false, 'Unbekanntes Feld abgelehnt');
[$st, $r] = api('doc_sign', ['token' => $doc['view_token'], 'field' => 'sig_betroffen', 'signature' => $png(), 'signer_name' => 'Rico Raser', 'signer_citizen_id' => 'TST777', 'player' => 'RicoRP', 'license' => 'license:rico']);
check($st === 200 && $r['ok'] && $r['signing']['final'] === true && $r['signing']['fields'][0]['by'] === 'Rico Raser', 'Unterschrieben – alle Felder voll, automatisch abgeschlossen');
$doc = trow('SELECT * FROM documents WHERE id = ?', [$id]);
check(str_starts_with((string) (json_decode($doc['data'], true)['sig_betroffen'] ?? ''), 'data:image/png') && $doc['finalized_at'] && strlen($doc['final_hash']) === 64, 'Unterschrift im Dokument gespeichert, Prüfsumme gesetzt');
$sig = trow('SELECT * FROM document_signatures WHERE document_id = ?', [$id]);
check($sig && $sig['source'] === 'game' && $sig['player'] === 'RicoRP' && $sig['signer_citizen_id'] === 'TST777', 'Unterschrift protokolliert (wer, wann, Spieler)');
[, $r] = api('doc_sign', ['token' => $doc['view_token'], 'field' => 'sig_betroffen', 'signature' => $png(), 'signer_name' => 'Fälscher']);
check($r['ok'] === false && str_contains($r['error'], 'abgeschlossen'), 'Nach dem Abschluss keine Änderung mehr');
check($officer->get('/index.php?p=document&id=' . $id)->has('abgeschlossen') && $officer->has('Rico Raser') && !$officer->has('Hier unterschreiben lassen'), 'Dokumentseite zeigt Abschluss und Unterzeichner');
$verify = (new Client(TEST_BASE))->get('/verify.php?c=' . $doc['verify_code']);
check($verify->has('Abgeschlossen') && $verify->has('unverändert') && !$verify->has('Rico Raser'), 'Echtheitsprüfung: abgeschlossen und unverändert (ohne Namen)');
$view = (new Client(TEST_BASE))->get('/doc.php?t=' . $doc['view_token']);
check($view->has('data:image/png'), 'Ausdruck im Spiel zeigt die Unterschrift');
// Manipulation wird erkannt
$data = json_decode($doc['data'], true);
$data['gegenstaende'] = 'nichts';
texec('UPDATE documents SET data = ? WHERE id = ?', [json_encode($data, JSON_UNESCAPED_UNICODE), $id]);
check((new Client(TEST_BASE))->get('/verify.php?c=' . $doc['verify_code'])->has('weicht vom Abschluss ab'), 'Nachträgliche Änderung in der Datenbank fällt auf');

// 3. Im Tablet unterschreiben lassen, manuell abschließen
$id2 = $issue();
$officer->get('/index.php?p=document&id=' . $id2);
$officer->post('/index.php?p=document&id=' . $id2, ['action' => 'finalize', 'note' => 'Unterschrift verweigert']);
$d2 = trow('SELECT * FROM documents WHERE id = ?', [$id2]);
check($d2['finalized_at'] && str_contains($d2['finalized_note'], 'Unterschrift verweigert'), 'Manuell abgeschlossen (Unterschrift verweigert)');
[, $r] = api('doc_sign', ['token' => $d2['view_token'], 'field' => 'sig_betroffen', 'signature' => $png(), 'signer_name' => 'Rico Raser']);
check($r['ok'] === false, 'Abgeschlossenes Dokument kann nicht mehr unterschrieben werden');
$id3 = $issue();
$officer->get('/index.php?p=document&id=' . $id3);
$officer->post('/index.php?p=document&id=' . $id3, ['action' => 'ic_sign', 'field' => 'sig_betroffen', 'signature' => $png(), 'signer_name' => 'Rico Raser']);
check(tval("SELECT source FROM document_signatures WHERE document_id = ?", [$id3]) === 'web' && (bool) tval('SELECT finalized_at FROM documents WHERE id = ?', [$id3]), 'Im Tablet unterschrieben und abgeschlossen');
$arzt = (new Client(TEST_BASE))->login('arzt');
$arzt->get('/index.php?p=dashboard');
$arzt->post('/index.php?p=document&id=' . $id3, ['action' => 'finalize']);
check($arzt->status === 404, 'Andere Fraktion kann nichts abschließen');

// 4. Dokument ohne Unterschriftsfeld: Empfangsbestätigung
$nfs = (new Client(TEST_BASE))->login('nfs');
$nfs->get('/index.php?p=document_new&type=rechnung');
$nfs->post('/index.php?p=document_new&type=rechnung', ['type' => 'rechnung', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[service_date]' => date('Y-m-d'), 'd[due_days]' => '14', 'd[tax]' => '0',
    'd[items][0][text]' => 'Verband', 'd[items][0][qty]' => '1', 'd[items][0][price]' => '10', 'd[items][0][reason]' => 'Material']);
$rid4 = (int) (preg_match('/p=document&id=(\d+)/', $nfs->url, $m) ? $m[1] : 0);
$d4 = trow('SELECT * FROM documents WHERE id = ?', [$rid4]);
[, $r] = api('document_item', ['token' => $d4['view_token']]);
check(($r['signing']['fields'][0]['name'] ?? '') === '_ic_receipt', 'Rechnung: Empfangsbestätigung als Unterschriftsfeld');
check(!(new Client(TEST_BASE))->get('/doc.php?t=' . $d4['view_token'])->has('Empfangsbestätigung'), 'Zusatzblatt erst nach der Unterschrift');
api('doc_sign', ['token' => $d4['view_token'], 'field' => '_ic_receipt', 'signature' => $png(), 'signer_name' => 'Tom Tester']);
$v4 = (new Client(TEST_BASE))->get('/doc.php?t=' . $d4['view_token']);
check($v4->has('Empfangsbestätigung') && $v4->has('Tom Tester'), 'Empfangsbestätigung auf dem Ausdruck');

// 5. Storniertes Dokument
$id5 = $issue();
$officer->get('/index.php?p=document&id=' . $id5);
$officer->post('/index.php?p=document&id=' . $id5, ['action' => 'revoke', 'reason' => 'Fehler']);
[, $r] = api('doc_sign', ['token' => tval('SELECT view_token FROM documents WHERE id = ?', [$id5]), 'field' => 'sig_betroffen', 'signature' => $png(), 'signer_name' => 'X']);
check($r['ok'] === false && str_contains($r['error'], 'storniert'), 'Storniertes Dokument nicht unterschreibbar');
