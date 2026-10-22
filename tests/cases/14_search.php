<?php
// Volltextsuche mit Rechtefilter
$now = date('Y-m-d H:i:s');
texec("INSERT INTO entries (patient_id, type, title, content, confidential, occurred_at, created_at, updated_at) VALUES (1, 'Behandlung', 'Fraktur', 'Diagnose Zygomaticusfraktur rechts', 0, ?, ?, ?)", [$now, $now, $now]);
texec("INSERT INTO entries (patient_id, type, title, content, confidential, occurred_at, created_at, updated_at) VALUES (1, 'Psychologisches Gespräch', 'Gespräch', 'Zygomaticus-GEHEIM', 1, ?, ?, ?)", [$now, $now, $now]);
$nfs = (new Client(TEST_BASE))->login('nfs');
$arzt = (new Client(TEST_BASE))->login('arzt');
$nfs->get('/index.php?p=search&q=Zygomaticus');
check($nfs->has('Zygomaticus</mark>fraktur') || $nfs->has('<mark>Zygomaticus</mark>'), 'Treffer im Eintragstext mit Hervorhebung');
check(!$nfs->has('GEHEIM'), 'Vertraulicher Eintrag für NFS nicht in der Suche');
check($arzt->get('/index.php?p=search&q=Zygomaticus')->has('GEHEIM'), 'Arzt (vertraulich-Recht) findet ihn');
check($nfs->get('/index.php?p=search&q=Tester')->has('Patientenakten'), 'Patient per Name');
check(str_contains($nfs->get('/index.php?p=search&q=TST001')->url, 'p=patient&id=1'), 'Exakte Bürger-ID öffnet direkt');
check(!$nfs->get('/index.php?p=search&q=Nina')->has('<h2>Personal</h2>'), 'Personal nur mit Recht durchsuchbar');
check($nfs->get('/index.php?p=search&q=Willkommen')->has('<h2>Wiki</h2>'), 'Wiki wird durchsucht');
$lspd = (new Client(TEST_BASE))->login('lspd');
check(!$lspd->get('/index.php?p=search&q=Tester')->has('Patientenakten'), 'Behördenkonto findet keine Akten');
