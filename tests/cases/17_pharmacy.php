<?php
// Medikamentenbestand, BtM-Buch, Rezept-Einlösung (Klinik und Apotheke im Spiel)
$oa = (new Client(TEST_BASE))->login('oa');
$nfs = (new Client(TEST_BASE))->login('nfs');
$arzt = (new Client(TEST_BASE))->login('arzt');
$ibu = (int) tval("SELECT id FROM medications WHERE name LIKE 'Ibuprofen 600%'");
$mor = (int) tval("SELECT id FROM medications WHERE name LIKE 'Morphin%'");
$nfsId = (int) tval("SELECT id FROM users WHERE username = 'nfs'");
check($ibu && $mor && $oa->get('/index.php?p=stock')->has('Ibuprofen 600'), 'Startsortiment vorhanden');
$oa->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $ibu, 'kind' => 'in', 'quantity' => '10', 'party' => 'Großhandel']);
$oa->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $mor, 'kind' => 'in', 'quantity' => '5', 'party' => 'Großhandel']);
check((int) tval('SELECT stock FROM medications WHERE id = ?', [$ibu]) === 10 && (int) tval('SELECT stock FROM medications WHERE id = ?', [$mor]) === 5, 'Zugänge gebucht');
$nfs->get('/index.php?p=stock');
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $ibu, 'kind' => 'in', 'quantity' => '99']);
check((int) tval('SELECT stock FROM medications WHERE id = ?', [$ibu]) === 10, 'Zugang ohne Recht abgelehnt');
$nfs->get('/index.php?p=stock');
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $ibu, 'kind' => 'out', 'quantity' => '2', 'patient' => 'TST001']);
check((int) tval('SELECT stock FROM medications WHERE id = ?', [$ibu]) === 8 && (bool) tval("SELECT id FROM entries WHERE patient_id = 1 AND type = 'Medikation'"), 'Entnahme mit Akteneintrag');
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $ibu, 'kind' => 'out', 'quantity' => '50']);
check($nfs->has('Nicht genug vorrätig') && (int) tval('SELECT stock FROM medications WHERE id = ?', [$ibu]) === 8, 'Bestand kann nicht negativ werden');
// BtM-Regeln
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $mor, 'kind' => 'out', 'quantity' => '1']);
check($nfs->has('muss der Patient angegeben') && (int) tval('SELECT stock FROM medications WHERE id = ?', [$mor]) === 5, 'BtM-Entnahme nur mit Patient');
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $mor, 'kind' => 'loss', 'quantity' => '1', 'note' => 'Ampulle zerbrochen']);
check($nfs->has('zweiten Person als Zeuge'), 'BtM-Verwurf nur mit Zeuge');
$oaId = (int) tval("SELECT id FROM users WHERE username = 'oa'");
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $mor, 'kind' => 'loss', 'quantity' => '1', 'note' => 'Ampulle zerbrochen', 'witness_id' => $oaId]);
check((int) tval("SELECT witness_id FROM medication_moves WHERE medication_id = ? AND kind = 'loss'", [$mor]) === $oaId, 'BtM-Verwurf mit Zeuge gebucht');
$oa->get('/index.php?p=btm&med=' . $mor);
check($oa->has('Großhandel') && $oa->has('Ampulle zerbrochen') && $oa->has('Übertrag'), 'BtM-Buch zeigt Karteikarte');
$oa->post('/index.php?p=btm&med=' . $mor, ['action' => 'check', 'medication_id' => $mor, 'counted' => '5']);
check($oa->has('Abweichung') && !tval("SELECT id FROM medication_moves WHERE kind = 'check'"), 'Prüfung mit Abweichung abgelehnt');
$oa->post('/index.php?p=btm&med=' . $mor, ['action' => 'check', 'medication_id' => $mor, 'counted' => '4']);
check((bool) tval("SELECT id FROM medication_moves WHERE kind = 'check' AND medication_id = ?", [$mor]), 'Prüfung eingetragen');
// Mindestbestand
texec('UPDATE medications SET min_stock = 8, low_warned = 0 WHERE id = ?', [$ibu]);
$nfs->get('/index.php?p=stock');
$nfs->post('/index.php?p=stock', ['action' => 'move', 'medication_id' => $ibu, 'kind' => 'out', 'quantity' => '1']);
check((bool) tval("SELECT id FROM notifications WHERE user_id = ? AND kind = 'stock'", [$oaId]), 'Warnung bei Mindestbestand');
texec("UPDATE medications SET item_name = 'ibuprofen', item_count = 2, min_stock = 0 WHERE id = ?", [$ibu]);
// Rezept ausstellen
$rx = function (string $form, array $meds) use ($arzt): int {
    $arzt->get('/index.php?p=document_new&type=rezept&patient=1');
    $data = ['type' => 'rezept', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[form]' => $form];
    foreach ($meds as $i => $m) {
        $data["d[items][$i][med]"] = $m;
        $data["d[items][$i][dose]"] = '1-0-1';
    }
    $arzt->post('/index.php?p=document_new&type=rezept', $data);
    return preg_match('/[?&]id=(\d+)/', $arzt->url, $mm) ? (int) $mm[1] : 0;
};
$doc = $rx('kassen', ['2x Ibuprofen 600 mg Filmtbl. N2', 'Morphin 10 mg Retardtbl. N1 (BtM)', 'Zauberpille']);
check($doc > 0, 'Rezept ausgestellt');
$token = (string) tval('SELECT view_token FROM documents WHERE id = ?', [$doc]);
[$st, $res] = api('rx_check', ['tokens' => [$token]]);
$lines = array_column($res['prescriptions'][0]['lines'] ?? [], 'status');
check($lines === ['ok', 'btm_form', 'nomatch'] && ($res['prescriptions'][0]['lines'][0]['count'] ?? 0) === 4, 'Apotheke prüft Positionen (Menge, BtM-Formular, Sortiment)');
[$st, $res] = api('rx_redeem', ['token' => $token, 'player' => 'Tom', 'license' => 'license:abc']);
check($st === 200 && ($res['items'][0] ?? []) === ['item' => 'ibuprofen', 'count' => 4, 'name' => 'Ibuprofen 600 mg Filmtbl. N2'], 'Einlösen gibt Items aus');
check((int) tval('SELECT stock FROM medications WHERE id = ?', [$ibu]) === 5, 'Bestand ausgebucht (2 Packungen)');
[$st] = api('rx_redeem', ['token' => $token, 'player' => 'Tom']);
check($st === 409, 'Doppeltes Einlösen verhindert');
$arzt->get('/index.php?p=document&id=' . $doc);
check($arzt->has('Apotheke (Tom)') && $arzt->has('nur auf BtM-Rezept'), 'Einlösung auf dem Rezept sichtbar');
// BtM-Rezept, in der Klinik ausgeben
$doc2 = $rx('btm', ['Morphin 10 mg Retardtbl. N1 (BtM)']);
$arzt->get('/index.php?p=document&id=' . $doc2);
$arzt->post('/index.php?p=document&id=' . $doc2, ['action' => 'rx_redeem']);
check((int) tval('SELECT stock FROM medications WHERE id = ?', [$mor]) === 3 && (bool) tval("SELECT id FROM medication_moves WHERE kind = 'dispense' AND document_id = ?", [$doc2]), 'BtM-Rezept in der Klinik ausgegeben');
[$st, $res] = api('rx_check', ['tokens' => [(string) tval('SELECT view_token FROM documents WHERE id = ?', [$doc2])]]);
check(($res['prescriptions'][0]['lines'][0]['status'] ?? '') === 'redeemed', 'Eingelöst-Status für die Apotheke');
// Abgelaufen
$doc3 = $rx('kassen', ['Ibuprofen 600 mg Filmtbl. N2']);
texec('UPDATE documents SET issued_at = ? WHERE id = ?', [date('Y-m-d H:i:s', strtotime('-40 days')), $doc3]);
[$st, $res] = api('rx_redeem', ['token' => (string) tval('SELECT view_token FROM documents WHERE id = ?', [$doc3])]);
check($st === 409 && str_contains($res['error'] ?? '', 'abgelaufen'), 'Abgelaufenes Rezept abgelehnt');
check($arzt->get('/index.php?p=document_new&type=rezept&patient=1')->has('<option value="Ibuprofen 600 mg Filmtbl. N2">'), 'Sortiment als Vorschlag im Rezept');
