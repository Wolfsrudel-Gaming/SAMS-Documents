<?php
// Ausstellen, Echtheitsprüfung, Nachtrag, Storno, Entwurf/Freigabe, Behördenfreigabe
$arzt = (new Client(TEST_BASE))->login('arzt');
$oa = (new Client(TEST_BASE))->login('oa');
$nfs = (new Client(TEST_BASE))->login('nfs');
$lspd = (new Client(TEST_BASE))->login('lspd');
$attest = ['type' => 'attest', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[purpose]' => 'Arbeitgeber', 'd[exam_date]' => date('Y-m-d'), 'd[subject]' => 'Ärztliches Attest', 'd[body]' => 'Arbeitsunfähig.'];
$arzt->get('/index.php?p=document_new&type=attest&patient=1');
$arzt->post('/index.php?p=document_new&type=attest', $attest);
$docId = (int) (preg_match('/p=document&id=(\d+)/', $arzt->url, $m) ? $m[1] : 0);
check(str_contains($arzt->url, 'p=document&id='), 'Attest ausgestellt');
$doc = trow('SELECT * FROM documents WHERE id = ?', [$docId]);
check($doc && preg_match('/^SAMS-AT-\d{4}-\d{5}$/', $doc['number']) === 1, 'Fortlaufende Nummer ' . ($doc['number'] ?? ''));
$v = new Client(TEST_BASE);
check($v->get('/verify.php?c=' . $doc['verify_code'])->has('echt und gültig'), 'Echtheitsprüfung: gültig');
check(!$v->has('Arbeitsunfähig.'), 'Echtheitsprüfung zeigt keinen Inhalt');
$arzt->post("/index.php?p=document&id=$docId", ['action' => 'addendum', 'text' => 'Berichtigung Datum']);
check($arzt->has('Nachtrag Nr. 1'), 'Nachtrag als Blatt am Dokument');
// Freigabe (Gegenzeichnung)
texec("INSERT INTO settings (name, value) VALUES ('doc_type_config', ?)", [json_encode(['einsatzprotokoll' => ['approval_below' => 50, 'approval_by' => 70, 'mode' => 'full']])]);
$ep = ['type' => 'einsatzprotokoll', 'patient_id' => '1', 'action' => 'submit', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[date]' => date('Y-m-d'), 'd[location]' => 'Pier', 'd[call_reason]' => 'Sturz', 'd[outcome]' => 'transport'];
$nfs->get('/index.php?p=document_new&type=einsatzprotokoll&patient=1');
check($nfs->has('Zur Freigabe einreichen') && !$nfs->has('Verbindlich ausstellen'), 'NFS braucht Freigabe');
$nfs->post('/index.php?p=document_new&type=einsatzprotokoll', $ep);
$draftId = (int) tval("SELECT id FROM document_drafts WHERE status = 'pending' ORDER BY id DESC");
check($draftId > 0, 'Zur Freigabe eingereicht');
$nfs->post('/index.php?p=document_new&type=einsatzprotokoll', ['action' => 'issue'] + $ep);
check((int) tval("SELECT COUNT(*) FROM documents WHERE type = 'einsatzprotokoll'") === 0, 'Freigabe nicht per direktem POST umgehbar');
$oa->get("/index.php?p=draft&id=$draftId");
$oa->post("/index.php?p=draft&id=$draftId", ['action' => 'approve']);
check($oa->has('Gegengezeichnet'), 'Freigegeben mit Gegenzeichnung');
texec("DELETE FROM settings WHERE name = 'doc_type_config'");
// Behördenfreigabe
$arzt->get("/index.php?p=document&id=$docId");
$arzt->post("/index.php?p=document&id=$docId", ['action' => 'share', 'kind' => 'link', 'agency' => 'lspd', 'hours' => '24', 'purpose' => 'Verfahren 1']);
$link = html_entity_decode((string) $arzt->match('/value="(http[^"]+share\.php\?t=[a-f0-9]+)"/'));
check($link !== '' && (new Client(TEST_BASE))->get($link)->has('Vertrauliches Dokument'), 'Freigabe-Link zeigt Dokument');
check($lspd->get("/index.php?p=document&id=$docId")->status === 403, 'LSPD-Konto ohne Freigabe: kein Zugriff');
$arzt->post("/index.php?p=document&id=$docId", ['action' => 'revoke', 'reason' => 'Fehler']);
check($v->get('/verify.php?c=' . $doc['verify_code'])->has('storniert'), 'Storno sichtbar in der Echtheitsprüfung');
