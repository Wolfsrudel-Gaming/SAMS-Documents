<?php
// Dienstausweis im Spiel als drehbare Karte (Vorder- und Rückseite), ohne Empfangsbestätigung
$admin = (new Client(TEST_BASE))->login('admin');
$nfsId = (int) tval("SELECT id FROM users WHERE username = 'nfs'");
$admin->get('/index.php?p=document_new&type=dienstausweis&employee=' . $nfsId);
$admin->post('/index.php?p=document_new&type=dienstausweis', ['type' => 'dienstausweis', 'employee_id' => (string) $nfsId, 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[valid_until]' => date('Y-m-d', strtotime('+1 year')), 'd[blood_type]' => 'A+']);
$id = (int) (preg_match('/p=document&id=(\d+)/', $admin->url, $m) ? $m[1] : 0);
$doc = trow('SELECT * FROM documents WHERE id = ?', [$id]);
check($id > 0 && !$admin->has('Empfangsbestätigung') && !$admin->has('id="signatures"'), 'Dienstausweis ohne Unterschriften-Abschnitt');
[, $r] = api('document_item', ['token' => $doc['view_token']]);
check($r['ok'] && $r['card'] === true && $r['signing']['fields'] === [], 'Item meldet Kartenansicht, keine Unterschriftsfelder');
$v = (new Client(TEST_BASE))->get('/doc.php?t=' . $doc['view_token']);
check($v->has('class="card-mode"') && $v->has('card3d') && $v->has('idcard front') && $v->has('idcard back'), 'Ansicht im Spiel: drehbare Karte mit Vorder- und Rückseite');
check(!(new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $doc['view_token'])->has('card3d'), 'Flache Ansicht weiterhin möglich');
[, $r] = api('document_item', ['token' => tval("SELECT view_token FROM documents WHERE type = 'rechnung' ORDER BY id DESC LIMIT 1")]);
check($r['card'] === false, 'Andere Dokumente bleiben Blätter');
// Bildschirmfoto zur Sichtprüfung (nur wenn ein Ziel angegeben ist)
if ($shot = getenv('SAMS_SHOT_DIR')) {
    $url = TEST_BASE . '/doc.php?t=' . $doc['view_token'];
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1100,height:700}});"
        . "await p.goto('$url');await p.screenshot({path:'$shot/card_front.png'});await p.mouse.click(550,330);await p.waitForTimeout(900);await p.screenshot({path:'$shot/card_back.png'});"
        . "await p.mouse.move(550,330);await p.mouse.down();await p.mouse.move(640,300,{steps:8});await p.screenshot({path:'$shot/card_tilt.png'});await b.close();})();";
    file_put_contents("$shot/shot.js", $js);
    exec("node " . escapeshellarg("$shot/shot.js") . " 2>&1", $shotOut);
    echo implode("\n", $shotOut);
}
