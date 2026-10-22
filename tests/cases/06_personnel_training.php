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
check(!tval("SELECT COUNT(*) FROM documents WHERE type = 'ausbildungsnachweis' AND employee_id = ?", [$nfsId]), 'Theorie allein ergibt kein Zertifikat');
$chiefId = (int) tval("SELECT id FROM users WHERE username = 'chief'");
check((bool) tval("SELECT id FROM notifications WHERE user_id = ? AND title LIKE 'Theorie bestanden%'", [$chiefId]), 'Ausbildung wird über die ausstehende Praxis informiert');
$chief->get("/index.php?p=course_edit&id=$cid");
check($chief->has('ausstehend') && $chief->has('Praxis bestanden'), 'Praxis in den Ergebnissen bestätigbar');
$chief->post("/index.php?p=course_edit&id=$cid", ['action' => 'practical_fail', 'attempt_id' => (string) $aid, 'note' => 'Reanimation zu langsam']);
check(tval('SELECT practical FROM training_attempts WHERE id = ?', [$aid]) === 'failed' && !tval("SELECT COUNT(*) FROM documents WHERE type = 'ausbildungsnachweis' AND employee_id = ?", [$nfsId]), 'Praxis nicht bestanden vermerkt');
$qual = (int) tval("SELECT id FROM qualifications WHERE faction_id = 1 AND id NOT IN (SELECT qualification_id FROM user_qualifications WHERE user_id = ?) ORDER BY id LIMIT 1", [$nfsId]);
texec('UPDATE training_courses SET qualification_id = ? WHERE id = ?', [$qual, $cid]);
check(!tval('SELECT COUNT(*) FROM user_qualifications WHERE user_id = ? AND qualification_id = ?', [$nfsId, $qual]), 'Keine Qualifikation allein durch die Theorie');
$chief->post("/index.php?p=course_edit&id=$cid", ['action' => 'practical_pass', 'attempt_id' => (string) $aid, 'note' => 'Fallbeispiel Polytrauma']);
check((int) tval("SELECT COUNT(*) FROM documents WHERE type = 'ausbildungsnachweis' AND employee_id = ?", [$nfsId]) === 1, 'Zertifikat erst nach bestandener Praxis');
check((bool) tval('SELECT COUNT(*) FROM user_qualifications WHERE user_id = ? AND qualification_id = ?', [$nfsId, $qual]), 'Qualifikation nach bestätigter Praxis');
