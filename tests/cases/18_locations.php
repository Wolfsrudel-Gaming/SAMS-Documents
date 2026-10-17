<?php
// Standorte: eigener Briefkopf, Auswahl beim Ausstellen, eingefroren im Dokument
$admin = (new Client(TEST_BASE))->login('admin');
$arzt = (new Client(TEST_BASE))->login('arzt');
$admin->get('/index.php?p=locations&edit=0');
$admin->post('/index.php?p=locations', ['name' => 'Sandy Shores Medical Center', 'short' => 'SSMC', 'org_sub' => 'Sandy Shores Medical Center', 'org_street' => 'Alhambra Drive 12', 'org_city' => '92001 Sandy Shores', 'city' => 'Sandy Shores', 'active' => '1']);
$admin->get('/index.php?p=locations&edit=0');
$admin->post('/index.php?p=locations', ['name' => 'Paleto Bay Clinic', 'org_street' => 'Paleto Boulevard 3', 'active' => '1']);
$ss = (int) tval("SELECT id FROM locations WHERE short = 'SSMC'");
$pb = (int) tval("SELECT id FROM locations WHERE name = 'Paleto Bay Clinic'");
check($ss && $pb, 'Standorte angelegt');
texec("UPDATE users SET location_id = ? WHERE username = 'arzt'", [$ss]);
$arzt->get('/index.php?p=document_new&type=attest&patient=1');
check($arzt->has('Standort (Briefkopf)') && $arzt->match('/<option value="' . $ss . '" selected/'), 'Eigener Standort beim Ausstellen vorausgewählt');
$arzt->post('/index.php?p=document_new&type=attest', ['type' => 'attest', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    '_location' => (string) $pb, 'd[purpose]' => 'Arbeitgeber', 'd[exam_date]' => date('Y-m-d'), 'd[subject]' => 'Ärztliches Attest', 'd[body]' => 'Arbeitsfähig']);
$doc = preg_match('/[?&]id=(\d+)/', $arzt->url, $m) ? (int) $m[1] : 0;
check($doc && (int) tval('SELECT location_id FROM documents WHERE id = ?', [$doc]) === $pb, 'Gewählter Standort am Dokument');
check($arzt->has('Paleto Boulevard 3') && !$arzt->has('Alhambra Drive 12'), 'Briefkopf des Standorts im Dokument');
$admin->get('/index.php?p=locations&edit=' . $pb);
$admin->post('/index.php?p=locations', ['id' => $pb, 'name' => 'Paleto Bay Clinic', 'org_street' => 'Neue Straße 9', 'active' => '1']);
check($arzt->get('/index.php?p=document&id=' . $doc)->has('Paleto Boulevard 3'), 'Spätere Adressänderung ändert alte Dokumente nicht');
check(!$arzt->get('/index.php?p=locations')->has('Standort anlegen'), 'Standortverwaltung nur mit Recht');
texec('DELETE FROM locations');
texec('UPDATE users SET location_id = NULL');
