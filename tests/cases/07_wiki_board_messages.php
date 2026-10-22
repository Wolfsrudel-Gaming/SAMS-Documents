<?php
// Wiki, Vorschlagslisten, Schwarzes Brett, Nachrichten
$arzt = (new Client(TEST_BASE))->login('arzt');
$nfs = (new Client(TEST_BASE))->login('nfs');
$chief = (new Client(TEST_BASE))->login('chief');
$arzt->get('/index.php?p=wiki_edit');
$arzt->post('/index.php?p=wiki_edit', ['title' => 'Testseite', 'category' => 'SOP', 'content' => "# Titel\n**fett** <script>x</script> [[Willkommen im SAMS-Wiki]]"]);
check($arzt->has('<strong>fett</strong>') && !$arzt->has('<script>x'), 'Wiki: Formatierung, HTML escaped');
$arzt->get('/index.php?p=wiki_lists');
$arzt->post('/index.php?p=wiki_lists', ['list_key' => 'meds', 'items' => "Testomycin\nTestomycin", 'action' => 'save']);
check($nfs->get('/index.php?p=dashboard')->has('<option value="Testomycin">'), 'Vorschlagsliste in Formularen');
$chief->get('/index.php?p=board&edit=new');
$chief->post('/index.php?p=board', ['id' => '0', 'action' => 'save', 'title' => 'Dienstanweisung', 'content' => 'Text', 'priority' => 'important', 'require_ack' => '1']);
check($nfs->get('/index.php?p=dashboard')->has('Gelesen und verstanden'), 'Aushang mit Bestätigung auf der Übersicht');
$annId = (int) tval('SELECT MAX(id) FROM announcements');
$nfs->post('/index.php?p=board', ['action' => 'ack', 'id' => $annId, 'return' => 'dashboard']);
check((bool) tval('SELECT acknowledged_at FROM announcement_reads WHERE announcement_id = ?', [$annId]), 'Bestätigung gespeichert');
$arztId = (int) tval("SELECT id FROM users WHERE username = 'arzt'");
$nfs->get('/index.php?p=messages&new=1');
$nfs->post('/index.php?p=messages', ['action' => 'send', 'to[]' => [(string) $arztId], 'subject' => 'Tausch', 'body' => 'Freitag?']);
check(preg_match('/Nachrichten<\/span><b class="nav-count">1<\/b>/', $arzt->get('/index.php?p=dashboard')->body) === 1, 'Empfänger sieht ungelesene Nachricht');
