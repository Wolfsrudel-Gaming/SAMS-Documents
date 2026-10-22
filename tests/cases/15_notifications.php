<?php
// Benachrichtigungen (Glocke)
$nfs = (new Client(TEST_BASE))->login('nfs');
$arzt = (new Client(TEST_BASE))->login('arzt');
$oa = (new Client(TEST_BASE))->login('oa');
$arztId = (int) tval("SELECT id FROM users WHERE username = 'arzt'");
$nfsId = (int) tval("SELECT id FROM users WHERE username = 'nfs'");
texec('DELETE FROM notifications');
// Nachricht
$nfs->get('/index.php?p=messages&new=1');
$nfs->post('/index.php?p=messages', ['action' => 'send', 'to[]' => [(string) $arztId], 'subject' => 'Schichttausch', 'body' => 'Freitag?']);
$arzt->get('/index.php?p=dashboard');
check($arzt->has('class="bell-count">1<') && $arzt->has('Neue Nachricht von Nina Notfall'), 'Glocke zeigt neue Nachricht');
$json = json_decode($arzt->get('/index.php?p=notifications&count=1')->body, true);
check(($json['unread'] ?? 0) === 1, 'Zähler-Abfrage für die Kopfleiste');
$nid = (int) tval('SELECT id FROM notifications WHERE user_id = ?', [$arztId]);
$arzt->get("/index.php?p=notifications&open=$nid");
check(str_contains($arzt->url, 'p=messages&id=') && (bool) tval('SELECT read_at FROM notifications WHERE id = ?', [$nid]), 'Öffnen führt zum Ziel und markiert gelesen');
check($nfs->get("/index.php?p=notifications&open=$nid")->url !== '' && !tval('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND id = ?', [$nfsId, $nid]), 'Fremde Benachrichtigung nicht nutzbar');
// Freigabe angefragt → Freigebende
texec("INSERT INTO settings (name, value) VALUES ('doc_type_config', ?)", [json_encode(['einsatzprotokoll' => ['approval_below' => 50, 'approval_by' => 70, 'mode' => 'full']])]);
$nfs->get('/index.php?p=document_new&type=einsatzprotokoll&patient=1');
$nfs->post('/index.php?p=document_new&type=einsatzprotokoll', ['type' => 'einsatzprotokoll', 'patient_id' => '1', 'action' => 'submit', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[date]' => date('Y-m-d'), 'd[location]' => 'Pier', 'd[call_reason]' => 'Sturz', 'd[outcome]' => 'transport']);
$oaId = (int) tval("SELECT id FROM users WHERE username = 'oa'");
check((bool) tval("SELECT id FROM notifications WHERE user_id = ? AND kind = 'approval'", [$oaId]), 'Oberarzt erhält „Freigabe angefragt“');
check(!tval("SELECT id FROM notifications WHERE user_id = ? AND kind = 'approval'", [$arztId]), 'Arzt (unter Freigabestufe) nicht');
$draft = (int) tval("SELECT MAX(id) FROM document_drafts WHERE status = 'pending'");
$oa->get("/index.php?p=draft&id=$draft");
$oa->post("/index.php?p=draft&id=$draft", ['action' => 'reject', 'note' => 'Uhrzeit fehlt']);
check((bool) tval("SELECT id FROM notifications WHERE user_id = ? AND title LIKE 'Zurückgewiesen%' AND body = 'Uhrzeit fehlt'", [$nfsId]), 'Einreicher erhält Zurückweisung mit Begründung');
texec("DELETE FROM settings WHERE name = 'doc_type_config'");
$nfs->get('/index.php?p=notifications');
$nfs->post('/index.php?p=notifications', ['action' => 'read_all']);
check((int) tval('SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL', [$nfsId]) === 0, 'Alle als gelesen markieren');
