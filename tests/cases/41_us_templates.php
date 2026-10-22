<?php
// US-Vorlagenset: Umschalten, jede Dokumentart jeder Fraktionsart als Vorschau auf Englisch, US-Formate, alte Dokumente bleiben deutsch
$admin = (new Client(TEST_BASE))->login('admin');
$kinds = ['medical' => 'SAMS', 'police' => 'USPD', 'justice' => 'USDOJ', 'law' => 'USLAW', 'fire' => 'USFD', 'rescue' => 'USTAS', 'government' => 'USGOV'];
$fids = ['medical' => 1];
foreach ($kinds as $kind => $short) {
    if ($kind === 'medical') {
        continue;
    }
    $id = (int) tval('SELECT id FROM factions WHERE kind = ? ORDER BY id LIMIT 1', [$kind]);
    if (!$id) {
        $admin->get('/index.php?p=factions');
        $admin->post('/index.php?p=factions', ['action' => 'create', 'name' => "US Test $kind", 'short' => $short, 'kind' => $kind, 'color' => '#334455']);
        $id = (int) tval('SELECT id FROM factions WHERE short = ?', [$short]);
    }
    $fids[$kind] = $id;
}
$deDoc = (int) tval("SELECT id FROM documents WHERE type = 'attest' ORDER BY id LIMIT 1");

// Umschalten (nur Hauptadministrator, Einstellungen → Portal)
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => '1']);
$admin->get('/index.php?p=settings');
check($admin->has('Vorlagenset der Dokumente'), 'Schalter für das Vorlagenset in den Einstellungen');
$form = [];
if (preg_match_all('/<(?:input|select|textarea)[^>]*name="([a-z_0-9]+)"[^>]*>/', $admin->body, $m)) {
    foreach ($m[1] as $name) {
        if ($name[0] !== '_') {
            $form[$name] = '';
        }
    }
}
// aktuelle Werte übernehmen, damit nichts anderes verändert wird
foreach (array_keys($form) as $name) {
    $v = tval('SELECT value FROM settings WHERE name = ?', [$name]);
    $form[$name] = $v === null || $v === false ? '' : (string) $v;
}
$admin->post('/index.php?p=settings', ['doc_region' => 'us'] + $form);
check(tval("SELECT value FROM settings WHERE name = 'doc_region'") === 'us', 'US-Vorlagenset eingeschaltet');

$german = '/[äöüÄÖÜß]|\b(und|oder|der|die|das|nicht|Datum|Unterschrift|Uhrzeit|Anschrift|Geburtsdatum|Bescheinigung|Ausgestellt|Seite|Blatt|Arzt|Ärztin|Patient\/in|Behörde|Aktenzeichen|Bürger-ID|gültig|bis|vom|am|im|für|mit|von|zur|zum|ja|nein)\b/u';
$leftovers = [];
$checked = 0;
$sampleNames = [];
foreach (tdb()->query('SELECT first_name, last_name FROM patients UNION SELECT name, title FROM users')->fetchAll() as $r) {
    foreach ($r as $v) {
        if (mb_strlen((string) $v) > 2) {
            $sampleNames[] = (string) $v;
        }
    }
}
usort($sampleNames, fn($a, $b) => mb_strlen($b) <=> mb_strlen($a));
foreach ($fids as $kind => $fid) {
    $admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => (string) $fid]);
    $pid = (int) tval("SELECT id FROM patients WHERE faction_id = ? AND archived_at IS NULL AND merged_into IS NULL ORDER BY id LIMIT 1", [$fid]);
    if (!$pid) {
        texec("INSERT INTO patients (faction_id, first_name, last_name, dob, gender, citizen_id, created_at, updated_at) VALUES (?, 'John', 'Doe', '1990-04-05', 'm', ?, ?, ?)",
            [$fid, 'US' . $fid, date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
        $pid = (int) tval('SELECT id FROM patients WHERE faction_id = ? ORDER BY id LIMIT 1', [$fid]);
    }
    $admin->get('/index.php?p=document_new');
    preg_match_all('/p=document_new&(?:amp;)?type=([a-z0-9_]+)/', $admin->body, $tm);
    foreach (array_unique($tm[1]) as $type) {
        if (str_starts_with($type, 'c_')) {
            continue; // eigene Vorlagen aus dem Editor
        }
        $admin->get("/index.php?p=document_new&type=$type&patient=$pid");
        $admin->post("/index.php?p=document_new&type=$type", ['type' => $type, 'patient_id' => (string) $pid, 'action' => 'preview']);
        $checked++;
        if ($admin->status !== 200 || !preg_match('#<div class="doc-stage">(.*)</section>#s', $admin->body, $pm)) {
            $leftovers[] = "$kind/$type: keine Vorschau (HTTP {$admin->status})";
            continue;
        }
        $text = html_entity_decode(strip_tags(preg_replace('#<(script|style)\b.*?</\1>#s', ' ', $pm[1])), ENT_QUOTES, 'UTF-8');
        foreach (preg_split('/\s{2,}|\n/', $text) as $chunk) {
            $chunk = trim(str_replace($sampleNames, '', $chunk)); // Namen aus den Testakten sind Daten, keine Vorlagentexte
            if ($chunk !== '' && preg_match($german, $chunk) && !str_contains($chunk, 'San Andreas Medical') && !preg_match('/^(John Doe|Tom Tester)/', $chunk)) {
                $leftovers[] = "$kind/$type: " . mb_strimwidth($chunk, 0, 90, '…');
            }
        }
    }
}
$leftovers = array_values(array_unique($leftovers));
if ($leftovers && getenv('SAMS_US_REPORT')) {
    file_put_contents(getenv('SAMS_US_REPORT'), implode("\n", $leftovers) . "\n");
}
check($checked > 80, "Alle Dokumentarten als Vorschau gedruckt ($checked)");
check(!$leftovers, 'Keine deutschen Reste in US-Dokumenten' . ($leftovers ? ' (' . count($leftovers) . ', z. B. ' . implode(' | ', array_slice($leftovers, 0, 5)) . ')' : ''));

// US-Formate und US-eigene Arten
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => '1']);
$admin->get('/index.php?p=document_new&type=attest&patient=1');
$admin->post('/index.php?p=document_new&type=attest', ['type' => 'attest', 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE,
    'd[purpose]' => 'Arbeitgeber', 'd[exam_date]' => '2026-03-04', 'd[subject]' => 'Medical Certificate', 'd[body]' => 'Fit for duty.']);
$usDoc = (int) (preg_match('/p=document&id=(\d+)/', $admin->url, $mm) ? $mm[1] : 0);
check($usDoc && json_decode((string) tval('SELECT data FROM documents WHERE id = ?', [$usDoc]), true)['_region'] === 'us', 'Ausgestellt im US-Set (gespeichert am Dokument)');
check($admin->get("/index.php?p=document&id=$usDoc")->has('03/04/2026') && !$admin->has('04.03.2026'), 'US-Datumsformat MM/DD/YYYY');
$admin->get('/index.php?p=document_new');
check($admin->has('type=us_dnr'), 'US-eigene Dokumentart (DNR) für den Rettungsdienst');
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => (string) $fids['police']]);
check($admin->get('/index.php?p=document_new')->has('type=us_citation') && $admin->has('type=us_miranda'), 'Citation und Miranda Waiver für die Polizei');
check((bool) tval("SELECT 1 FROM rank_permissions rp JOIN ranks r ON r.id = rp.rank_id WHERE r.faction_id = ? AND rp.permission = 'documents.issue.us_citation'", [$fids['police']]),
    'Rechte für neue US-Arten an die Ränge vergeben');
$admin->post('/index.php?p=factions', ['action' => 'switch', 'faction_id' => '1']);

// Bildschirmfotos zur Sichtprüfung (nur wenn ein Ziel angegeben ist)
if ($shot = getenv('SAMS_SHOT_DIR')) {
    $issue = function (string $type, array $d) use ($admin) {
        $admin->get("/index.php?p=document_new&type=$type&patient=1");
        $admin->post("/index.php?p=document_new&type=$type", ['type' => $type, 'patient_id' => '1', 'action' => 'issue', 'sig_mode' => 'live', 'live_signature' => TEST_SIGNATURE] + $d);
        return (string) tval('SELECT view_token FROM documents WHERE type = ? ORDER BY id DESC LIMIT 1', [$type]);
    };
    $docs = [
        'us_rx' => $issue('rezept', ['d[form]' => 'btm', 'd[items][0][med]' => 'Oxycodone 5 mg tablets, #20', 'd[items][0][dose]' => '1 tab PO q6h PRN pain']),
        'us_death' => $issue('todesbescheinigung', ['d[death_date]' => date('Y-m-d'), 'd[place]' => 'krankenhaus', 'd[place_address]' => 'Pillbox Hill Medical Center', 'd[exam_date]' => date('Y-m-d'),
            'd[exam_time]' => '14:20', 'd[signs][]' => ['totenflecke'], 'd[manner]' => 'nichtnatuerlich', 'd[cause_a]' => 'Hemorrhagic shock', 'd[cause_b]' => 'Gunshot wound to the chest']),
        'us_excuse' => $issue('au', ['d[kind]' => 'erst', 'd[au_since]' => date('Y-m-d'), 'd[au_until]' => date('Y-m-d', strtotime('+3 days')), 'd[au_found]' => date('Y-m-d'), 'd[icd]' => 'S52.5']),
    ];
    $js = "const {chromium}=require('/opt/node22/lib/node_modules/playwright');(async()=>{const b=await chromium.launch();const p=await b.newPage({viewport:{width:1000,height:1300}});";
    foreach ($docs as $name => $tok) {
        $js .= "await p.goto('" . TEST_BASE . "/doc.php?flat=1&t=$tok');await p.screenshot({path:'$shot/$name.png',fullPage:true});";
    }
    file_put_contents("$shot/shot_us.js", $js . 'await b.close();})();');
    exec('node ' . escapeshellarg("$shot/shot_us.js") . ' 2>&1', $shotOut);
    echo implode("\n", $shotOut);
}

// Älteres deutsches Dokument bleibt deutsch
if ($deDoc) {
    check($admin->get("/index.php?p=document&id=$deDoc")->has('Ärztliches Attest'), 'Vorher ausgestellte deutsche Dokumente bleiben deutsch');
}

// zurück auf deutsch für die übrigen Tests
texec("UPDATE settings SET value = 'de' WHERE name = 'doc_region'");
check(!(new Client(TEST_BASE))->login('arzt')->get('/index.php?p=document_new&type=attest&patient=1')->has('Medical Certificate'), 'Zurückgeschaltet: wieder deutsche Vorlagen');
