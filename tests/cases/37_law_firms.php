<?php
// Anwaltskanzleien: eigene Fraktionsart, beliebig viele Kanzleien, getrennte Mandantenakten, Einreichen bei Behörden,
// Gerichtstermine, Anwaltsverzeichnis – und das Gesetzbuch in Formularen, Dokumenten, Strafrechner, Suche und Bürgerportal
$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=factions');
check($admin->has('Anwaltskanzlei'), 'Fraktionsart Anwaltskanzlei wählbar');
foreach ([['Kanzlei Saul & Partner', 'KSP', 'ra.saul', 'Sabine Saul'], ['Kanzlei Sterling', 'KST', 'ra.sterling', 'Sam Sterling']] as [$name, $short, $user, $lead]) {
    $admin->post('/index.php?p=factions', ['action' => 'create', 'name' => $name, 'short' => $short, 'kind' => 'law', 'color' => '#1f3d36', 'lead_username' => $user, 'lead_name' => $lead]);
    texec('UPDATE users SET password_hash = ?, must_change_password = 0, phone = ? WHERE username = ?', [password_hash('TestPasswort123', PASSWORD_DEFAULT), '555-0' . strlen($user), $user]);
    texec("DELETE FROM rank_permissions WHERE permission = 'security.2fa_required' AND rank_id IN (SELECT id FROM ranks WHERE faction_id = (SELECT faction_id FROM users WHERE username = ?))", [$user]);
}
$ksp = (int) tval("SELECT id FROM factions WHERE short = 'KSP'");
$kst = (int) tval("SELECT id FROM factions WHERE short = 'KST'");
check($ksp && $kst && (int) tval('SELECT COUNT(*) FROM ranks WHERE faction_id = ?', [$ksp]) === 6 && (int) tval("SELECT COUNT(*) FROM price_list WHERE faction_id = ? AND code LIKE 'H%'", [$ksp]) === 11, 'Zwei Kanzleien mit Rängen und Honorarordnung');
$saul = (new Client(TEST_BASE))->login('ra.saul');
$nav = preg_match('/<nav class="sidenav">(.*?)<\/nav>/s', $saul->get('/index.php?p=dashboard')->body, $m) ? $m[1] : '';
check(str_contains($nav, 'Mandantenakten') && str_contains($nav, 'Honorarrechnungen') && str_contains($nav, 'Gerichtskalender') && str_contains($nav, 'Anwaltsverzeichnis') && str_contains($nav, 'Gesetze') && !str_contains($nav, 'Medikamente'), 'Menü der Kanzlei');
check($saul->has('assets/img/kinds/law.svg'), 'Eigenes Logo');

// Mandantenakten sind je Kanzlei getrennt
$now = date('Y-m-d H:i:s');
texec("INSERT INTO patients (faction_id, first_name, last_name, dob, citizen_id, created_at, updated_at) VALUES (?, 'Max', 'Mandant', '1988-08-08', 'char1:mm', ?, ?)", [$ksp, $now, $now]);
$mm = (int) tval('SELECT id FROM patients WHERE faction_id = ? AND last_name = ?', [$ksp, 'Mandant']);
check($saul->get('/index.php?p=patient&id=' . $mm)->status === 200 && (new Client(TEST_BASE))->login('ra.sterling')->get('/index.php?p=patient&id=' . $mm)->status === 404, 'Andere Kanzlei sieht die Mandantenakte nicht');

// Vollmacht beim Gericht einreichen
$justice = (int) tval("SELECT faction_id FROM users WHERE username = 'judge'");
$police = (int) tval("SELECT faction_id FROM users WHERE username = 'chief.miller'");
$issue = function (string $type, array $d) use ($saul, $mm) {
    $saul->get('/index.php?p=document_new&type=' . $type . '&patient=' . $mm);
    $saul->post('/index.php?p=document_new&type=' . $type, ['type' => $type, 'patient_id' => (string) $mm, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE]
        + array_combine(array_map(fn($k) => "d[$k]", array_keys($d)), array_values($d)));
    return trow('SELECT * FROM documents WHERE type = ? AND patient_id = ? ORDER BY id DESC LIMIT 1', [$type, $mm]);
};
$saul->get('/index.php?p=document_new&type=ra_vollmacht&patient=' . $mm);
check($saul->has('Einreichen bei') && $saul->has('nicht einreichen (nur ausstellen)'), 'Kanzleidokument mit „Einreichen bei“');
$vm = $issue('ra_vollmacht', ['sache' => 'Ermittlungsverfahren wegen Raub', 'aktenzeichen' => '2 Js 77/26', 'umfang' => 'Vertretung im Strafverfahren', 'einreichen' => (string) $justice]);
check($vm && (bool) tval("SELECT 1 FROM document_shares WHERE document_id = ? AND kind = 'faction' AND target_faction_id = ?", [$vm['id'], $justice]), 'Vollmacht beim Gericht eingereicht');
$judge = (new Client(TEST_BASE))->login('judge');
$judge->get('/index.php?p=shared&id=' . $vm['id']);
check($judge->status === 200 && $judge->has('Max Mandant') && $judge->has('Kanzlei Saul &amp; Partner'), 'Gericht sieht die eingereichte Vollmacht mit Kanzleistempel');

// Akteneinsicht bei der Polizei → Behördenanfrage
$ae = $issue('ra_akteneinsicht', ['an' => 'LSPD', 'aktenzeichen' => 'LSPD-77', 'einreichen' => (string) $police]);
$req = trow('SELECT * FROM agency_requests WHERE reference = ?', ['akteneinsicht:' . $ae['id']]);
check($req && (int) $req['faction_id'] === $ksp && (int) $req['to_faction_id'] === $police && str_contains($req['subject'], 'Akteneinsicht Az. LSPD-77'), 'Akteneinsicht erzeugt Anfrage an die Polizei');
check((new Client(TEST_BASE))->login('chief.miller')->get('/index.php?p=requests&tab=in')->has('Akteneinsicht Az. LSPD-77'), 'Polizei sieht die Anfrage der Kanzlei');

// Gesetze in Formularen und Dokumenten
$saul->get('/index.php?p=document_new&type=ra_schriftsatz&patient=' . $mm);
check($saul->has('§2 StGB – Raub (bis 35 HE oder 15.000 $)'), 'Gesetzbuch als Vorschlag im Schriftsatz');
check((new Client(TEST_BASE))->login('chief.miller')->get('/index.php?p=document_new&type=pd_anzeige')->has('§1 StGB – Körperverletzung'), 'Gesetzbuch als Vorschlag in der Strafanzeige');
$ss = $issue('ra_schriftsatz', ['art' => 'Verteidigungsschrift', 'an' => 'Superior Court', 'aktenzeichen' => '2 Js 77/26', 'antrag' => 'Freispruch', 'begruendung' => 'Ein Raub nach §2 StGB setzt Gewalt voraus; diese lag nicht vor (vgl. auch § 1 StGB).']);
$saul->get('/index.php?p=document&id=' . $ss['id']);
check($saul->has('Zitierte Gesetze (2)') && $saul->has('Wegnahme fremden Eigentums'), 'Zitierte Paragraphen mit Gesetzestext am Dokument');

// Gerichtstermin mit Anwalt einer Kanzlei
$saulId = (int) tval("SELECT id FROM users WHERE username = 'ra.saul'");
$judge->get('/index.php?p=court');
check($judge->has('Kanzlei Saul &amp; Partner') && $judge->has('Sabine Saul'), 'Gericht kann Anwälte aller Kanzleien als Verteidigung wählen');
$judge->post('/index.php?p=court', ['action' => 'hearing', 'title' => 'Strafsache Mandant', 'kind' => 'Hauptverhandlung', 'case_ref' => '2 Js 77/26', 'starts_at' => date('Y-m-d\TH:i', strtotime('+3 days')),
    'room' => 'Saal 2', 'judge_id' => (string) tval("SELECT id FROM users WHERE username = 'judge'"), 'lawyer_id' => (string) $saulId]);
check((int) tval("SELECT lawyer_id FROM hearings WHERE title = 'Strafsache Mandant'") === $saulId && (bool) tval("SELECT 1 FROM notifications WHERE user_id = ? AND kind = 'court'", [$saulId]), 'Termin mit Kanzlei-Anwältin, Benachrichtigung');
$saul->get('/index.php?p=court');
check($saul->has('Strafsache Mandant') && $saul->has('Gerichtstermine setzt das Gericht'), 'Kanzlei sieht den Termin ihrer Anwältin');
check(!(new Client(TEST_BASE))->login('ra.sterling')->get('/index.php?p=court')->has('Strafsache Mandant'), 'Andere Kanzlei sieht den Termin nicht');
$saul->post('/index.php?p=court', ['action' => 'hearing', 'title' => 'Selbst angesetzt', 'starts_at' => date('Y-m-d\TH:i', strtotime('+4 days'))]);
check($saul->status === 403 && !tval("SELECT id FROM hearings WHERE title = 'Selbst angesetzt'"), 'Kanzlei setzt keine Gerichtstermine');

// Anwaltsverzeichnis, Strafrechner, Suche, Bürgerportal
$arzt = (new Client(TEST_BASE))->login('arzt');
$arzt->get('/index.php?p=lawyers');
check($arzt->has('Kanzlei Saul &amp; Partner') && $arzt->has('Kanzlei Sterling') && $arzt->has('Sam Sterling') && $arzt->has('Kanzleiinhaber/in'), 'Anwaltsverzeichnis für alle Fraktionen');
$code = (int) tval("SELECT id FROM law_codes WHERE name = 'Gesetzbuch des Teststaats'");
$saul->get('/index.php?p=laws&tab=rechner&code=' . $code);
check($saul->has('Strafrechner') && $saul->has('class="calc-n"') && $saul->has('data-max-jail="100"') && $saul->has('data-max-fine="50000"'), 'Strafrechner mit Höchstgrenzen aus dem Gesetzbuch');
$saul->get('/index.php?p=search&q=Raub');
check($saul->has('§2 StGB – Raub'), 'Gesetze in der Suche');
$b = new Client(TEST_BASE);
$b->get('/portal.php');
$b->post('/portal.php?action=devlogin', ['discord_id' => '123456789099', 'name' => 'Bürgerin Recht']);
check($b->get('/portal.php?page=laws')->has('§2 StGB') && $b->get('/portal.php?page=lawyers')->has('Kanzlei Sterling'), 'Gesetze und Anwälte im Bürgerportal');
if ($shot = getenv('SAMS_SHOT_DIR')) {
    $host = parse_url(TEST_BASE, PHP_URL_HOST);
    $sid = $saul->sessionId();
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const c=await b.newContext({viewport:{width:1300,height:1000}});"
        . "await c.addCookies([{name:'SAMSSESSID',value:'$sid',domain:'$host',path:'/'}]);const p=await c.newPage();"
        . "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=" . $vm['view_token'] . "');await p.waitForTimeout(300);await p.screenshot({path:'$shot/law_vollmacht.png',fullPage:true});"
        . "await p.goto('" . TEST_BASE . "/index.php?p=document&id=" . $ss['id'] . "');await p.click('.law-cited summary');await p.screenshot({path:'$shot/law_schriftsatz.png',fullPage:true});"
        . "await p.goto('" . TEST_BASE . "/index.php?p=lawyers');await p.screenshot({path:'$shot/law_lawyers.png'});"
        . "await p.goto('" . TEST_BASE . "/index.php?p=laws&tab=rechner&code=$code');await p.fill('.calc-n >> nth=0','2');await p.fill('.calc-n >> nth=1','3');await p.screenshot({path:'$shot/law_rechner.png'});"
        . "await b.close();})();";
    file_put_contents("$shot/law.js", $js);
    exec('node ' . escapeshellarg("$shot/law.js") . ' 2>&1', $lo);
    echo implode("\n", $lo);
}
check((int) tval("SELECT COUNT(*) FROM wiki_pages WHERE faction_id = ? AND category <> 'Handbuch'", [$ksp]) >= 30 && (bool) tval("SELECT id FROM wiki_pages WHERE faction_id = ? AND title = 'Handbuch: Kanzlei'", [$ksp]), 'Wiki und Handbuch der Kanzlei');
check((int) tval('SELECT COUNT(*) FROM training_courses WHERE faction_id = ?', [$ksp]) === 3 && (bool) tval("SELECT id FROM qualifications WHERE faction_id = ? AND name = 'Strafverteidiger/in'", [$ksp]), 'Lehrgänge und Qualifikationen der Kanzlei');
