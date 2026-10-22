<?php
// Anmeldung, Rechte, Sitzungen
$c = new Client(TEST_BASE);
$c->login('arzt', 'falsch');
check($c->has('falsch') || $c->has('ungültig') || str_contains($c->url, 'login'), 'Falsches Passwort wird abgelehnt');
$c->login('arzt');
check(str_contains($c->url, 'dashboard') || $c->has('Übersicht'), 'Anmeldung mit richtigem Passwort');
check($c->get('/index.php?p=users')->status === 403, 'Ohne Recht: Personalverwaltung gesperrt (403)');
check($c->get('/index.php?p=patients')->status === 200, 'Mit Recht: Patientenakten erreichbar');
$anon = new Client(TEST_BASE);
check(str_contains($anon->get('/index.php?p=patients')->url, 'p=login'), 'Ohne Anmeldung → Login');
$c->post('/index.php?p=patient_edit', ['_csrf' => 'falsch', 'first_name' => 'X', 'last_name' => 'Y']);
check($c->status === 400 && !tval("SELECT id FROM patients WHERE last_name = 'Y'"), 'POST mit falschem CSRF-Token abgelehnt');
check(@file_get_contents(TEST_BASE . '/health.php') !== false, 'health.php antwortet');
