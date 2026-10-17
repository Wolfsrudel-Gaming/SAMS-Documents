<?php
// Personalakte, Ausbildung
$chief = (new Client(TEST_BASE))->login('chief');
$nfs = (new Client(TEST_BASE))->login('nfs');
$nfsId = (int) tval("SELECT id FROM users WHERE username = 'nfs'");
$chief->get("/index.php?p=personnel&id=$nfsId");
$chief->post("/index.php?p=personnel&id=$nfsId", ['kind' => 'praise', 'title' => 'Lob Pier', 'event_date' => date('Y-m-d')]);
$chief->post("/index.php?p=personnel&id=$nfsId", ['kind' => 'note', 'title' => 'GEHEIME-NOTIZ', 'confidential' => '1']);
$nfs->get('/index.php?p=personnel');
check($nfs->has('Lob Pier') && !$nfs->has('GEHEIME-NOTIZ'), 'Eigene Akte ohne vertrauliche Notiz');
// Lehrgang mit Prüfung
$chief->get('/index.php?p=course_edit');
$chief->post('/index.php?p=course_edit', ['action' => 'course', 'title' => 'Testkurs', 'pass_percent' => '100', 'question_count' => '0', 'time_limit' => '0',
    'attempts_per_day' => '0', 'require_modules' => '1', 'certificate' => '1', 'active' => '1', 'signer_id' => (string) tval("SELECT id FROM users WHERE username = 'chief'")]);
$cid = (int) tval("SELECT id FROM training_courses WHERE title = 'Testkurs'");
$chief->post("/index.php?p=course_edit&id=$cid", ['action' => 'module', 'module_id' => '0', 'm_title' => 'Modul 1', 'm_content' => '**Wissen**', 'm_sort' => '1']);
$chief->post("/index.php?p=course_edit&id=$cid", ['action' => 'question', 'question_id' => '0', 'q_text' => 'Richtig?', 'q_options' => "nein\n* ja", 'q_sort' => '1']);
$mid = (int) tval('SELECT id FROM training_modules WHERE course_id = ?', [$cid]);
$nfs->get("/index.php?p=course&id=$cid");
$nfs->post("/index.php?p=course&id=$cid", ['action' => 'read', 'module_id' => $mid]);
$nfs->post("/index.php?p=course&id=$cid", ['action' => 'start']);
$aid = (int) $nfs->match('/p=exam&(?:amp;)?id=(\d+)/') ?: (int) tval('SELECT MAX(id) FROM training_attempts');
$qid = (int) tval('SELECT id FROM training_questions WHERE course_id = ?', [$cid]);
check(!$nfs->has('answer correct'), 'Lösung vor Abgabe nicht im HTML');
$nfs->post("/index.php?p=exam&id=$aid", ["a[$qid][]" => '1']);
check($nfs->has('Bestanden'), 'Prüfung bestanden');
check((int) tval("SELECT COUNT(*) FROM documents WHERE type = 'ausbildungsnachweis' AND employee_id = ?", [$nfsId]) === 1, 'Zertifikat automatisch ausgestellt');
