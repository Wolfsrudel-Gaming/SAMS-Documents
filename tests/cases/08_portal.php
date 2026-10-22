<?php
// Bürgerportal
$b = new Client(TEST_BASE);
$b->get('/portal.php');
$b->post('/portal.php?action=devlogin', ['discord_id' => '123456789012', 'name' => 'Bürger Test']);
$code = (string) $b->match('/<div class="link-code">([A-Z0-9-]+)<\/div>/');
check($code !== '', 'Bürger angemeldet, Verknüpfungscode');
$nfs = (new Client(TEST_BASE))->login('nfs');
$nfs->get('/index.php?p=patient&id=1');
$nfs->post('/index.php?p=patient&id=1', ['action' => 'portal_link', 'code' => $code]);
check($nfs->has('verknüpft'), 'Medic verknüpft Akte');
check($b->get('/portal.php?page=documents')->has('Meine Dokumente'), 'Bürger sieht Dokumente');
$b->get('/portal.php?page=appointments');
$b->post('/portal.php', ['do' => 'appointment', 'kind' => 'Allgemeine Sprechstunde', 'preferred' => 'Freitag']);
check($b->has('Terminanfrage gesendet'), 'Terminanfrage');
check($b->get('/index.php?p=patient&id=1')->status !== 200 || str_contains($b->url, 'login'), 'Bürger-Sitzung ohne Zugriff aufs Websystem');
