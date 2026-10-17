<?php
// Statistik, Discord-Warteschlange (ohne echte Discord-Verbindung)
$admin = (new Client(TEST_BASE))->login('admin');
check($admin->get('/index.php?p=stats')->has('Aktivität pro Mitarbeiter/in'), 'Statistikseite');
check($admin->get('/index.php?p=report&period=week')->has('Tätigkeitsbericht'), 'Druckbarer Bericht');
$admin->get('/index.php?p=discord');
$admin->post('/index.php?p=discord', ['action' => 'save', 'webhook_documents' => 'https://evil.example/x']);
check($admin->has('keine gültige Discord-Webhook-Adresse'), 'Fremde Webhook-Adresse abgelehnt');
texec("INSERT INTO settings (name, value) VALUES ('webhook_documents', 'https://discord.com/api/webhooks/1/test')");
texec('DELETE FROM discord_queue');
$arzt = (new Client(TEST_BASE))->login('arzt');
$arzt->get('/index.php?p=document_new&type=attest&patient=1');
$arzt->post('/index.php?p=document_new&type=attest', ['type' => 'attest', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[purpose]' => 'Arbeitgeber', 'd[exam_date]' => date('Y-m-d'), 'd[subject]' => 'Attest', 'd[body]' => 'INHALT-GEHEIM']);
$payload = (string) tval('SELECT payload FROM discord_queue ORDER BY id DESC');
check($payload !== '' && !str_contains($payload, 'Tester') && !str_contains($payload, 'INHALT-GEHEIM'), 'Dokument-Log ohne Namen/Inhalt eingereiht');
texec("DELETE FROM settings WHERE name = 'webhook_documents'");
texec('DELETE FROM discord_queue');
