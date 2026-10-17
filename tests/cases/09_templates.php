<?php
// Vorlagen-Editor
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=templates&new=1');
$admin->post('/index.php?p=templates&new=1', ['id' => '0', 'title' => 'Hausverbot', 'code' => 'HV', 'layout' => 'letter', 'subject' => 'patient', 'patient_mode' => 'required',
    'seal' => 'round', 'active' => '1', 'f[0][label]' => 'Grund', 'f[0][type]' => 'textarea', 'f[0][width]' => 'full', 'f[0][required]' => '1',
    'body' => 'Hausverbot für {{patient.name}}: {{grund}}', 'ranks[]' => [(string) tval("SELECT id FROM ranks WHERE name LIKE 'Arzt%'")], 'action' => 'save']);
check((int) tval("SELECT COUNT(*) FROM custom_doc_types WHERE type_key = 'c_hausverbot'") === 1, 'Vorlage angelegt');
$arzt = (new Client(TEST_BASE))->login('arzt');
$arzt->get('/index.php?p=document_new&type=c_hausverbot&patient=1');
$arzt->post('/index.php?p=document_new&type=c_hausverbot', ['type' => 'c_hausverbot', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[grund]' => 'Randale']);
check($arzt->has('Hausverbot für') && $arzt->has('Randale'), 'Dokument aus Vorlage mit Platzhaltern');
texec("UPDATE custom_doc_types SET body = 'GEÄNDERT'");
$id = (int) tval("SELECT MAX(id) FROM documents WHERE type = 'c_hausverbot'");
check(!$arzt->get("/index.php?p=document&id=$id")->has('GEÄNDERT'), 'Vorlagenänderung verändert altes Dokument nicht');
