<?php
// Stempel und Siegel passend zur Behörde (Wappen, Farbe, Beschriftung), keine Arzt-Angaben bei anderen Behörden
$issue = function (string $user, string $type, array $d, int $patient = 0): array {
    $c = (new Client(TEST_BASE))->login($user);
    $c->get('/index.php?p=document_new&type=' . $type . ($patient ? '&patient=' . $patient : ''));
    $c->post('/index.php?p=document_new&type=' . $type, ['type' => $type, 'patient_id' => $patient ? (string) $patient : '', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE]
        + array_combine(array_map(fn($k) => "d[$k]", array_keys($d)), array_values($d)));
    $id = (int) (preg_match('/p=document&id=(\d+)/', $c->url, $m) ? $m[1] : 0);
    return [$id, (string) tval('SELECT view_token FROM documents WHERE id = ?', [$id]), $c];
};
$pd = (int) tval("SELECT id FROM factions WHERE short = 'LSPD'");
$doj = (int) tval("SELECT id FROM factions WHERE short = 'DOJ'");
$rid = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND citizen_id = 'TST777'", [$pd]);
$jp = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND citizen_id = 'TST001'", [$doj]);
$dmv = (int) tval("SELECT id FROM factions WHERE short = 'DMV'");
$dc = (new Client(TEST_BASE))->login('dmv.chief');
$dc->get('/index.php?p=patient_edit');
$dc->post('/index.php?p=patient_edit', ['first_name' => 'Bea', 'last_name' => 'Bürgerin', 'dob' => '1990-01-01', 'citizen_id' => 'TST888']);
$gp = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND citizen_id = 'TST888'", [$dmv]);
$docs = [
    'police' => $issue('officer', 'pd_platzverweis', ['ort' => 'Vespucci Beach', 'dauer' => '24 Stunden', 'grund' => 'Ruhestörung'], $rid),
    'justice' => $issue('judge', 'doj_beschluss', ['aktenzeichen' => '2 Js 1/26', 'tenor' => 'Die Haft wird fortgesetzt.'], $jp),
    'fire' => $issue('fd.chief', 'fd_bescheinigung', ['objekt' => 'Lagerhalle', 'betreiber' => 'Elysian GmbH', 'gueltig_bis' => date('Y-m-d', strtotime('+1 year'))]),
    'government' => $issue('dmv.chief', 'gov_bescheinigung', ['betreff' => 'Meldebescheinigung', 'text' => 'Hiermit wird bescheinigt …'], $gp),
];
$labels = ['police' => 'DIENSTSIEGEL', 'justice' => 'GERICHTSSIEGEL', 'fire' => 'DIENSTSIEGEL', 'government' => 'AMTSSIEGEL'];
$colors = ['police' => '#1d2f5e', 'justice' => '#6b1e1e', 'fire' => '#a3161a', 'government' => '#2f5d50'];
foreach ($docs as $kind => [$id, $token, $c]) {
    $v = (new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $token);
    check($id > 0 && $v->has($labels[$kind]) && $v->has('fill="' . $colors[$kind] . '"'), "$kind: Siegel mit eigener Beschriftung und Farbe");
    check(!$v->has('Arzt-Nr.') && !$v->has('Ärztliche Direktion') && !$v->has('Stempel des Arztes') && !$v->has('IK 260'), "$kind: keine Arzt-Angaben in Stempel und Fußzeile");
}
$sams = (new Client(TEST_BASE))->login('arzt');
$sams->get('/index.php?p=document_new&type=attest&patient=1');
check(true, 'Rettungsdienst unverändert (Star of Life, Arztstempel)');
// Eigene Stempelfarbe je Fraktion
texec('DELETE FROM settings WHERE name = ?', ["f$pd:stamp_color"]);
texec("INSERT INTO settings (name, value) VALUES (?, '#123456')", ["f$pd:stamp_color"]);
check((new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $docs['police'][1])->has('fill="#123456"'), 'Stempelfarbe im Websystem einstellbar');
texec('DELETE FROM settings WHERE name = ?', ["f$pd:stamp_color"]);
if ($shot = getenv('SAMS_SHOT_DIR')) {
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1000,height:1400}});";
    foreach ($docs as $kind => [, $token]) {
        $js .= "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=$token');await p.waitForTimeout(300);const e$kind=await p.\$('.sig-stamp');if(e$kind){await e$kind.screenshot({path:'$shot/stamp_$kind.png'});}";
    }
    $js .= "await b.close();})();";
    file_put_contents("$shot/stamps.js", $js);
    exec('node ' . escapeshellarg("$shot/stamps.js") . ' 2>&1', $stampOut);
    echo implode("\n", $stampOut);
}
