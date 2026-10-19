<?php
// Dokumentenfälschung: aus bis zur Freischaltung, Hürden (Original), Fälscherrechner, Echtheitsprüfung schlägt fehl, Mängel je Qualität
$set = function (string $k, string $v): void {
    texec('DELETE FROM settings WHERE name = ?', [$k]);
    texec('INSERT INTO settings (name, value) VALUES (?, ?)', [$k, $v]);
};
[, $cfg] = api('game_config', []);
check(($cfg['forgery']['enabled'] ?? null) === false, 'Fälschungen standardmäßig aus');
[, $r] = api('forge_start', ['site_id' => 1, 'quality' => 90, 'originals' => [], 'license' => 'license:crook', 'player' => 'Carl']);
check($r['ok'] === false, 'Ohne Freischaltung kein Fälscherrechner');

$admin = (new Client(TEST_BASE))->login('admin');
$admin->get('/index.php?p=factions');
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => '1']);
$admin->get('/index.php?p=forgery');
check($admin->status === 200 && $admin->has('Schalter und Hürden'), 'Adminseite Fälschungen');
check((new Client(TEST_BASE))->login('arzt')->get('/index.php?p=forgery')->status === 403, 'Nur für den Hauptadministrator');
$admin->post('/index.php?p=forgery', ['action' => 'settings', 'enabled' => '1', 'materials' => 'forgery_paper:2, forgery_ink', 'original' => '1', 'duration' => '60', 'cooldown' => '30',
    'checks' => '4', 'difficulty' => 'hard', 'alert' => '100', 'types[]' => ['attest', 'au']]);
$admin->post('/index.php?p=forgery', ['action' => 'site', 'name' => 'Keller Grove Street', 'x' => '105.2', 'y' => '-1940.5', 'z' => '20.8', 'heading' => '45', 'prop_model' => 'prop_laptop_01a', 'active' => '1']);
$site = trow("SELECT * FROM forgery_sites WHERE name = 'Keller Grove Street'");
[, $cfg] = api('game_config', []);
$fc = $cfg['forgery'];
check($fc['enabled'] === true && $fc['sites'][0]['id'] === (int) $site['id'] && $fc['materials'] === [['item' => 'forgery_paper', 'count' => 2], ['item' => 'forgery_ink', 'count' => 1]]
    && $fc['original'] === true && $fc['difficulty'] === 'hard' && $fc['checks'] === 4, 'Werkstatt und Hürden gehen an den Spielserver');

// Ohne echtes Original keine Vorlage
[, $r] = api('forge_start', ['site_id' => $site['id'], 'quality' => 92, 'originals' => [str_repeat('a', 40)], 'license' => 'license:crook', 'player' => 'Carl']);
check($r['ok'] === false && str_contains($r['error'], 'Original'), 'Ohne echtes Original keine Fälschung');
$orig = trow("SELECT * FROM documents WHERE type = 'attest' AND status = 'valid' ORDER BY id LIMIT 1");
[, $r] = api('forge_start', ['site_id' => $site['id'], 'quality' => 92, 'originals' => [$orig['view_token']], 'license' => 'license:crook', 'player' => 'Carl']);
check($r['ok'] === true && str_contains($r['url'], '/forge.php?s='), 'Mit echtem Attest als Vorlage: Fälscherrechner');
$s = $r['session'];

$b = new Client(TEST_BASE);
$b->get('/forge.php?s=' . $s);
check($b->status === 200 && $b->has('Behörde') && !$b->has('Max Mandant'), 'Fälscherrechner ohne echte Daten');
$b->get('/forge.php?s=' . $s . '&f=1&type=attest');
check($b->has('Zur Vorlage bei') && !$b->has('value="au"'), 'Nur Dokumentarten mit Original als Vorlage');
$post = ['s' => $s, 'f' => '1', 'type' => 'attest', 'p[first_name]' => 'Carl', 'p[last_name]' => 'Crook', 'p[dob]' => '1990-01-01', 'p[citizen_id]' => 'char1:crook',
    'i[title]' => 'Dr.', 'i[name]' => 'Frank Fake', 'i[rank_name]' => 'Oberarzt', 'd[purpose]' => 'Arbeitgeber', 'd[exam_date]' => date('Y-m-d'), 'd[subject]' => 'Ärztliches Attest',
    'd[body]' => 'Herr Crook ist arbeitsunfähig.'];
$b->post('/forge.php', $post + ['action' => 'preview']);
check($b->has('Frank Fake') && $b->has('Herr Crook ist arbeitsunfähig') && $b->has('class="forged"'), 'Vorschau sieht aus wie ein echtes Attest');
[, $r] = api('forge_result', ['session' => $s, 'license' => 'license:crook']);
check($r['ok'] === false, 'Vor dem Drucken kein Item');
$b->post('/forge.php', $post + ['action' => 'print']);
check($b->has('forgeDone') && $b->has('Druckauftrag gesendet'), 'Druck abgeschickt');
[, $r] = api('forge_result', ['session' => $s, 'license' => 'license:other']);
check($r['ok'] === false, 'Nur der Fälscher selbst bekommt das Blatt');
[, $r] = api('forge_result', ['session' => $s, 'license' => 'license:crook']);
$meta = $r['metadata'] ?? [];
check($r['ok'] === true && str_starts_with($meta['label'], 'Attest') && strlen($meta['sams_token']) === 40, 'Dokument-Item wie ein echter Ausdruck');
$fg = trow('SELECT * FROM forgeries WHERE view_token = ?', [$meta['sams_token']]);
check($fg && !tval('SELECT id FROM documents WHERE verify_code = ? OR view_token = ?', [$fg['verify_code'], $fg['view_token']]), 'Fälschung steht nicht in den Dokumenten');

// Ansehen und prüfen
$v = (new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $meta['sams_token']);
check($v->status === 200 && $v->has('Frank Fake') && $v->has(chunk_split($fg['verify_code'], 4, ' ')) && $v->has('df-micro'), 'Gute Fälschung: sieht echt aus, mit Mikroschrift');
[, $di] = api('document_item', ['token' => $meta['sams_token']]);
check($di['ok'] === true && $di['revoked'] === false && $di['signing'] === null, 'Im Spiel wie ein echtes Blatt ansehbar');
$chk = (new Client(TEST_BASE))->get('/verify.php?c=' . $fg['verify_code']);
check($chk->has('Kein Dokument gefunden') && $chk->has('Fälschung'), 'Echtheitsprüfung schlägt fehl');
[, $r] = api('verify', ['code' => $fg['verify_code']]);
check($r['found'] === false && (int) tval('SELECT checked_count FROM forgeries WHERE id = ?', [$fg['id']]) === 2, 'Prüfungen werden still mitgezählt');
check((new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $orig['view_token'])->has('df-micro'), 'Echte Dokumente tragen das Sicherheitsmerkmal');
check((new Client(TEST_BASE))->get('/forge.php?s=' . $s)->has('Druckauftrag gesendet'), 'Sitzung nach dem Druck verbraucht');

// Schlechte Fälschung: sichtbare Mängel
[, $r] = api('forge_start', ['site_id' => $site['id'], 'quality' => 20, 'originals' => [$orig['view_token']], 'license' => 'license:crook', 'player' => 'Carl']);
$b->post('/forge.php', ['s' => $r['session']] + array_diff_key($post, ['s' => 1]) + ['action' => 'preview']);
$b->post('/forge.php', ['s' => $r['session']] + array_diff_key($post, ['s' => 1]) + ['action' => 'print']);
[, $r2] = api('forge_result', ['session' => $r['session'], 'license' => 'license:crook']);
$v = (new Client(TEST_BASE))->get('/doc.php?flat=1&t=' . $r2['metadata']['sams_token']);
check($v->has('forged q-low') && !$v->has('df-micro'), 'Schlechte Fälschung: ohne Mikroschrift, mit Mängeln');

// Alarm und Übersicht
texec("DELETE FROM notifications WHERE kind = 'wanted'");
[, $r] = api('forge_alert', ['street' => 'Grove Street']);
check($r['notified'] >= 1 && (bool) tval("SELECT 1 FROM notifications WHERE kind = 'wanted' AND title LIKE '%Grove Street%'"), 'Anonymer Hinweis an die Polizei');
$admin->get('/index.php?p=forgery');
check($admin->has($fg['verify_code']) && $admin->has('Keller Grove Street') && $admin->has('Carl'), 'Übersicht der Fälschungen für die Administration');
if ($shot = getenv('SAMS_SHOT_DIR')) {
    [, $r3] = api('forge_start', ['site_id' => $site['id'], 'quality' => 95, 'originals' => [$orig['view_token']], 'license' => 'license:crook', 'player' => 'Carl']);
    $b->post('/forge.php', ['s' => $r3['session']] + array_diff_key($post, ['s' => 1]) + ['action' => 'preview']);
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1280,height:800}});"
        . "await p.goto('" . TEST_BASE . "/forge.php?s=" . $r3['session'] . "');await p.screenshot({path:'$shot/forge_ui.png'});"
        . "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=" . $meta['sams_token'] . "');await p.waitForTimeout(300);await p.screenshot({path:'$shot/forge_good.png',fullPage:true});"
        . "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=" . $r2['metadata']['sams_token'] . "');await p.waitForTimeout(300);await p.screenshot({path:'$shot/forge_bad.png',fullPage:true});"
        . "await p.goto('" . TEST_BASE . "/verify.php?c=" . $fg['verify_code'] . "');await p.screenshot({path:'$shot/forge_verify.png'});await b.close();})();";
    file_put_contents("$shot/forge.js", $js);
    exec('node ' . escapeshellarg("$shot/forge.js") . ' 2>&1', $fo);
    echo implode("\n", $fo);
}
$set('forgery_enabled', '0');
