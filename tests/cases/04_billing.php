<?php
// Gebührenordnung und Rechnungsstatus
$nfs = (new Client(TEST_BASE))->login('nfs');
$base = ['type' => 'rechnung', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE, 'd[service_date]' => date('Y-m-d'), 'd[due_days]' => '14', 'd[tax]' => '0'];
$nfs->get('/index.php?p=document_new&type=rechnung');
$nfs->post('/index.php?p=document_new&type=rechnung', $base + ['d[items][0][text]' => 'R01 Notfalleinsatz Rettungswagen', 'd[items][0][qty]' => '1', 'd[items][0][price]' => '1.00']);
check($nfs->has('weicht von der Gebührenordnung ab'), 'Abweichender Preis ohne Begründung abgelehnt');
$nfs->post('/index.php?p=document_new&type=rechnung', $base + ['d[items][0][text]' => 'R01 Notfalleinsatz Rettungswagen', 'd[items][0][qty]' => '1', 'd[items][0][price]' => '500.00',
    'd[items][1][text]' => 'Verband', 'd[items][1][qty]' => '1', 'd[items][1][price]' => '10', 'd[items][1][reason]' => 'Material']);
$id = (int) (preg_match('/p=document&id=(\d+)/', $nfs->url, $m) ? $m[1] : 0);
check($id > 0 && (int) tval('SELECT amount_cents FROM documents WHERE id = ?', [$id]) === 51000, 'Rechnung über 510,00 ausgestellt');
$nfs->post("/index.php?p=document&id=$id", ['action' => 'pay_status', 'to' => 'dunned']);
check($nfs->has('1. Mahnung'), 'Mahnung');
$nfs->post("/index.php?p=document&id=$id", ['action' => 'pay_status', 'to' => 'paid']);
check(tval('SELECT pay_status FROM documents WHERE id = ?', [$id]) === 'paid', 'Als bezahlt markiert');
