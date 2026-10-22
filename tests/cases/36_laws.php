<?php
// Gesetzbücher: Import (Text/PDF-Text) mit Vorschau, Lesen und Suchen, Übernahme der Strafmaße in den Katalog
$text = file_get_contents(dirname(__DIR__) . '/fixtures/gesetzbuch_beispiel.txt');
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=law_import');
check($admin->status === 200 && $admin->has('pdf.min.js') && $admin->has('Gesetzbuch importieren'), 'Importseite mit PDF-Leser');
$admin->post('/index.php?p=law_import', ['action' => 'preview', 'text' => $text, 'source_name' => 'beispiel.txt']);
$admin->get('/index.php?p=law_import');
check($admin->has('Vorschau: 2 Bücher, 5 Paragraphen, 3 mit Strafmaß') && $admin->has('Gesetzbuch des Teststaats') && $admin->has('bis 35 HE oder 15.000 $'), 'Vorschau erkennt Bücher, Paragraphen und Strafmaße');
check(!$admin->has('Page 1 of 2'), 'Seitenzahlen und Kopfzeilen entfernt');
$admin->post('/index.php?p=law_import', ['action' => 'import', 'name' => 'Gesetzbuch des Teststaats', 'description' => 'Test', 'server_id' => '0',
    'use_0' => '1', 'title_0' => 'Strafgesetzbuch', 'short_0' => 'StGB', 'use_1' => '1', 'title_1' => 'Straßenverkehrsordnung', 'short_1' => 'StVO']);
$code = trow("SELECT * FROM law_codes WHERE name = 'Gesetzbuch des Teststaats'");
check($code && (int) tval('SELECT COUNT(*) FROM law_books WHERE code_id = ?', [$code['id']]) === 2, 'Gesetzbuch gespeichert');
$s1 = trow("SELECT s.* FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ? AND b.short = 'StGB' AND s.number = '1'", [$code['id']]);
check($s1 && $s1['title'] === 'Körperverletzung' && (int) $s1['jail_max'] === 20 && (int) $s1['fine_max'] === 10000 && $s1['penalty_mode'] === 'and'
    && str_contains($s1['body'], 'misshandelt oder an der Gesundheit schädigt'), 'Paragraph mit zusammengefügten Zeilen und Strafmaß');
$s3 = (string) tval("SELECT s.body FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ? AND b.short = 'StGB' AND s.number = '3'", [$code['id']]);
check(str_contains($s3, "- Geständnis\n- Kooperation mit den Behörden"), 'Aufzählungen erkannt');

// Lesen und Suchen (alle Fraktionen)
$arzt = (new Client(TEST_BASE))->login('arzt');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $arzt->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav, 'Gesetze'), 'Menüpunkt Gesetze für alle');
$arzt->get('/index.php?p=laws');
check($arzt->has('§ 2 Fahrerflucht') && $arzt->has('Freiheitsstrafe bis 15 HE oder Geldstrafe bis 10.000 $') && !$arzt->has('Gesetzbuch verwalten'), 'Gesetze lesen');
$arzt->get('/index.php?p=laws&code=' . $code['id'] . '&q=Raub');
check($arzt->has('<mark>Raub</mark>') && !$arzt->has('§ 2 Fahrerflucht'), 'Volltextsuche');
check($arzt->get('/index.php?p=law_import')->status === 403, 'Import nur mit Recht');

// Strafmaße in den Katalog der Polizei
$pd = (new Client(TEST_BASE))->login('chief.miller');
$pd->get('/index.php?p=laws&code=' . $code['id']);
check($pd->has('Strafmaße in den'), 'Polizei kann Strafmaße übernehmen');
$pd->post('/index.php?p=laws', ['action' => 'catalog', 'code_id' => $code['id']]);
$pid = (int) tval("SELECT faction_id FROM users WHERE username = 'chief.miller'");
$row = trow("SELECT * FROM price_list WHERE faction_id = ? AND code = '§2 StGB'", [$pid]);
check($row && $row['name'] === 'Raub' && (int) $row['price_cents'] === 1500000 && (int) $row['jail'] === 35 && $row['category'] === 'Strafgesetzbuch', 'Katalogposition aus dem Gesetz');
$pd->post('/index.php?p=laws', ['action' => 'catalog', 'code_id' => $code['id']]);
check((int) tval("SELECT COUNT(*) FROM price_list WHERE faction_id = ? AND code LIKE '§%'", [$pid]) === 3, 'Erneute Übernahme aktualisiert statt zu verdoppeln');

// Bearbeiten und neue Fassung
$admin->post('/index.php?p=laws', ['action' => 'section', 'section_id' => $s1['id'], 'title' => 'Körperverletzung', 'body' => $s1['body'], 'penalty' => 'bis 25 HE und 12.500 $']);
check((int) tval('SELECT jail_max FROM law_sections WHERE id = ?', [$s1['id']]) === 25 && (int) tval('SELECT fine_max FROM law_sections WHERE id = ?', [$s1['id']]) === 12500, 'Paragraph bearbeiten, Strafmaß neu ausgewertet');
$admin->post('/index.php?p=law_import', ['action' => 'preview', 'text' => $text]);
$admin->post('/index.php?p=law_import', ['action' => 'import', 'name' => 'Gesetzbuch des Teststaats', 'server_id' => '0', 'use_0' => '1', 'title_0' => 'Strafgesetzbuch', 'short_0' => 'StGB']);
check((int) tval("SELECT COUNT(*) FROM law_codes WHERE name = 'Gesetzbuch des Teststaats'") === 1 && (int) tval('SELECT COUNT(*) FROM law_books WHERE code_id = ?', [$code['id']]) === 1, 'Neue Fassung ersetzt das Gesetzbuch (abgewählte Bücher entfallen)');
if ($shot = getenv('SAMS_SHOT_DIR')) {
    $sid = $arzt->sessionId();
    $host = parse_url(TEST_BASE, PHP_URL_HOST);
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const c=await b.newContext({viewport:{width:1300,height:1000}});"
        . "await c.addCookies([{name:'SAMSSESSID',value:'$sid',domain:'$host',path:'/'}]);const p=await c.newPage();"
        . "await p.goto('" . TEST_BASE . "/index.php?p=laws&code=" . $code['id'] . "');await p.screenshot({path:'$shot/laws.png'});await b.close();})();";
    file_put_contents("$shot/laws.js", $js);
    exec('node ' . escapeshellarg("$shot/laws.js") . ' 2>&1', $lo);
    echo implode("\n", $lo);
}
