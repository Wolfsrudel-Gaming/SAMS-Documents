<?php
// Handbuch im Wiki, Sprache der Oberfläche
$nfs = (new Client(TEST_BASE))->login('nfs');
$nfs->get('/index.php?p=wiki_page&slug=handbuch_erste_schritte');
check($nfs->has('Handbuch: Erste Schritte') && $nfs->has('slug=handbuch_dienstzeit_und_dienstplan'), 'Handbuch im Wiki mit Verweisen');
check((int) tval("SELECT COUNT(*) FROM wiki_pages WHERE category = 'Handbuch'") >= 7, 'Alle Handbuch-Seiten angelegt');
texec("INSERT INTO settings (name, value) VALUES ('language', 'en')");
$nfs->get('/index.php?p=dashboard');
check($nfs->has('<html lang="en">') && $nfs->has('Patient records') && $nfs->has('Off duty'), 'Oberfläche auf Englisch');
check((new Client(TEST_BASE))->get('/index.php?p=login')->has('Log in'), 'Anmeldeseite auf Englisch');
texec("DELETE FROM settings WHERE name = 'language'");
check($nfs->get('/index.php?p=dashboard')->has('Patientenakten'), 'Standard bleibt Deutsch');
