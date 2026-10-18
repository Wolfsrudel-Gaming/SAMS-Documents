<?php
// Weitere Vorlagen, Wiki-Seiten und Lehrgänge für den Rettungsdienst
$arzt = (new Client(TEST_BASE))->login('arzt');
$nfs = (new Client(TEST_BASE))->login('nfs');
$arzt->get('/index.php?p=document_new');
check($arzt->has('type=med_haftfaehigkeit') && $arzt->has('Entbindung von der Schweigepflicht') && $arzt->has('Narkoseprotokoll'), 'Neue Vorlagen für Ärzt/innen');
check(!$nfs->get('/index.php?p=document_new')->has('type=med_haftfaehigkeit') && $nfs->has('type=med_triage'), 'Notfallsanitäter dürfen nur nicht-ärztliche Vorlagen ausstellen');
$arzt->get('/index.php?p=document_new&type=med_haftfaehigkeit&patient=1');
$arzt->post('/index.php?p=document_new&type=med_haftfaehigkeit', ['type' => 'med_haftfaehigkeit', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[anforderung]' => 'LSPD Mission Row, Officer Smith', 'd[datum]' => date('Y-m-d'), 'd[uhrzeit]' => '22:10', 'd[befund]' => 'Vitalwerte stabil, AAK 0,8 ‰', 'd[ergebnis]' => 'gewahrsams- und haftfähig']);
check(str_contains($arzt->url, 'p=document&id=') && $arzt->has('gewahrsams- und haftfähig') && $arzt->has('LSPD Mission Row'), 'Haftfähigkeitsbescheinigung ausgestellt');
if (!is_file(dirname(__DIR__, 2) . '/database/content/medical_training.php')) {
    return; // Inhaltsdateien noch nicht vorhanden
}
$wiki = (int) tval('SELECT COUNT(*) FROM wiki_pages WHERE faction_id = 1');
$modules = (int) tval('SELECT COUNT(*) FROM training_modules m JOIN training_courses c ON c.id = m.course_id WHERE c.faction_id = 1');
$questions = (int) tval('SELECT COUNT(*) FROM training_questions q JOIN training_courses c ON c.id = q.course_id WHERE c.faction_id = 1');
check($wiki >= 100, "Mindestens 100 Wiki-Seiten ($wiki)");
check($modules >= 50 && $questions >= 100, "Mindestens 50 Lernmodule ($modules) und 100 Prüfungsfragen ($questions)");
check($nfs->get('/index.php?p=wiki_page&slug=medikament_adrenalin')->has('Adrenalin') && $nfs->get('/index.php?p=training')->has('Advanced Life Support'), 'Inhalte für das Personal sichtbar');
