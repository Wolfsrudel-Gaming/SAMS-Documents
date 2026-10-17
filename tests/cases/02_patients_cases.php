<?php
// Akten, Fälle, Abteilungs-Sichtbarkeit, Archiv
$nfs = (new Client(TEST_BASE))->login('nfs');
$arzt = (new Client(TEST_BASE))->login('arzt');
$admin = (new Client(TEST_BASE))->login('admin');
$nfs->get('/index.php?p=patient_edit');
$nfs->post('/index.php?p=patient_edit', ['first_name' => 'Fall', 'last_name' => 'Patient', 'dob' => '1985-05-05', 'gender' => 'w']);
$pid = (int) $nfs->match('/[?&]id=(\d+)/') ?: (int) tval("SELECT id FROM patients WHERE last_name = 'Patient'");
check($pid > 0, 'Patientenakte angelegt');
$nfs->get("/index.php?p=case_edit&patient=$pid");
$nfs->post("/index.php?p=case_edit&patient=$pid", ['title' => 'VU Route 68', 'kind' => 'notfall', 'department_id' => '1', 'location' => 'Route 68', 'incident' => 'PKW gegen Baum', 'diagnosis' => 'S52.5 Radiusfraktur']);
$cid = (int) tval("SELECT id FROM cases WHERE patient_id = ?", [$pid]);
check($cid > 0 && $nfs->has('F-'), 'Fall mit Fallnummer angelegt');
$nfs->get("/index.php?p=entry_edit&patient=$pid&case=$cid");
$nfs->post("/index.php?p=entry_edit&patient=$pid", ['type' => 'Notfalleinsatz', 'title' => 'Erstversorgung', 'content' => 'RD-GEHEIM', 'case_id' => $cid, 'department_id' => '1', 'occurred_at' => '2026-10-17T14:40']);
check($nfs->has('RD-GEHEIM'), 'Eintrag sichtbar für Verfasser');
check(!$arzt->get("/index.php?p=patient&id=$pid")->has('RD-GEHEIM'), 'Andere Abteilung sieht Inhalt nicht');
check($admin->get("/index.php?p=patient&id=$pid")->has('RD-GEHEIM'), 'Leitung mit „alle Abteilungen“ sieht Inhalt');
$nfs->get("/index.php?p=document_new&type=einsatzprotokoll&case=$cid");
check($nfs->has('Route 68') && $nfs->has('PKW gegen Baum'), 'Dokument aus Fall: Felder vorbelegt');
$admin->get("/index.php?p=patient&id=$pid");
$admin->post("/index.php?p=patient&id=$pid", ['action' => 'archive', 'reason' => 'Test']);
check($admin->has('Archivierte Akte'), 'Akte archiviert');
check(str_contains($nfs->get("/index.php?p=entry_edit&patient=$pid")->url, "p=patient"), 'Keine Einträge in archivierter Akte');
$admin->post("/index.php?p=patient&id=$pid", ['action' => 'restore']);
check(!$admin->has('Archivierte Akte'), 'Akte reaktiviert');
