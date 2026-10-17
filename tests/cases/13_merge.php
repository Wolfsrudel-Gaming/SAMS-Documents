<?php
// Doppelte Akten zusammenführen
$now = date('Y-m-d H:i:s');
texec("INSERT INTO patients (first_name, last_name, dob, citizen_id, phone, allergies, created_at, updated_at) VALUES ('Doris', 'Doppelt', '1980-01-01', 'DUP1', '', 'Penicillin', ?, ?)", [$now, $now]);
$a = (int) tdb()->lastInsertId();
texec("INSERT INTO patients (first_name, last_name, dob, citizen_id, phone, allergies, created_at, updated_at) VALUES ('Doris', 'Doppelt', '1980-01-01', '', '555-1234', 'Latex', ?, ?)", [$now, $now]);
$b = (int) tdb()->lastInsertId();
texec("INSERT INTO entries (patient_id, type, title, content, occurred_at, created_at, updated_at) VALUES (?, 'Behandlung', 'Eintrag B', 'x', ?, ?, ?)", [$b, $now, $now, $now]);
$nfs = (new Client(TEST_BASE))->login('nfs');
check($nfs->get('/index.php?p=patient_merge')->status === 403, 'NFS darf nicht zusammenführen');
$chief = (new Client(TEST_BASE))->login('chief');
$chief->get('/index.php?p=patient_merge');
check($chief->has('Doppelt') && $chief->has('Gleicher Name und Geburtsdatum'), 'Doppelung wird vorgeschlagen');
$chief->get("/index.php?p=patient_merge&target=$a&source=$b");
$chief->post('/index.php?p=patient_merge', ['target' => $a, 'source' => $b, 'pick[phone]' => 'source']);
check(str_contains($chief->url, "p=patient&id=$a") && $chief->has('zusammengeführt'), 'Zusammengeführt');
$pa = trow('SELECT * FROM patients WHERE id = ?', [$a]);
check($pa['phone'] === '555-1234' && $pa['citizen_id'] === 'DUP1', 'Gewählte Felder übernommen (Telefon aus B, Bürger-ID aus A)');
check(str_contains($pa['allergies'], 'Penicillin') && str_contains($pa['allergies'], 'Latex'), 'Allergien zusammengefügt');
check((int) tval('SELECT patient_id FROM entries WHERE title = ?', ['Eintrag B']) === $a, 'Einträge verschoben');
check((int) tval('SELECT merged_into FROM patients WHERE id = ?', [$b]) === $a, 'Doppelte Akte archiviert mit Verweis');
check(str_contains($chief->get("/index.php?p=patient&id=$b")->url, "id=$a"), 'Alte Akte leitet auf die verbleibende um');
$chief->post('/index.php?p=patient_merge', ['target' => $a, 'source' => $b]);
check($chief->has('können nicht zusammengeführt werden'), 'Bereits zusammengeführte Akte nicht erneut verwendbar');
