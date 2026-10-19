<?php
/**
 * Fälscherrechner (im Spiel an einer geheimen Werkstatt): Vorlage einer Behörde wählen, Felder ausfüllen,
 * Vorschau, „Drucken“. Zugang nur über den einmaligen Sitzungsschlüssel aus der Resource (20 Minuten gültig).
 * Es werden keine echten Daten angezeigt – nur leere Vorlagen der Behörden.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$session = (string) ($_REQUEST['s'] ?? '');
$f = forgery_by_session($session, false);
$open = $f && $f['status'] === 'session' && $f['expires_at'] >= now();
if (!$f || (!$open && $f['status'] !== 'done')) {
    http_response_code(404);
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:monospace;background:#0b0f0b;color:#7f7;padding:2rem">KEINE VERBINDUNG.</body>');
}
$allowed = json_decode((string) $f['allowed_types'], true) ?: [];
$factions = array_values(array_filter(factions(), function ($fx) use ($allowed) {
    return (bool) array_intersect(array_keys(document_types((int) $fx['id'])), $allowed);
}));
$fid = (int) ($_REQUEST['f'] ?? $f['faction_id'] ?? 0);
$fid = in_array($fid, array_map(fn($x) => (int) $x['id'], $factions), true) ? $fid : 0;
$types = $fid ? array_intersect_key(document_types($fid), array_flip($allowed)) : [];
$typeKey = (string) ($_REQUEST['type'] ?? $f['type'] ?? '');
$typeKey = isset($types[$typeKey]) ? $typeKey : '';
if ($fid) {
    set_faction_context($fid);
}
$type = $typeKey !== '' ? doc_type($typeKey) : null;
$errors = [];
$done = $f['status'] === 'done';

if ($open && is_post() && $type) {
    $action = (string) post('action');
    $txt = fn(array $src, string $k, int $max = 120) => mb_substr(trim((string) ($src[$k] ?? '')), 0, $max);
    $p = is_array($_POST['p'] ?? null) ? $_POST['p'] : [];
    $i = is_array($_POST['i'] ?? null) ? $_POST['i'] : [];
    $fields = array_values(array_filter($type['fields'], fn($fl) => ($fl['type'] ?? '') !== 'signature'));
    [$data, $errors] = collect_fields($fields, is_array($_POST['d'] ?? null) ? $_POST['d'] : []);
    $sig = (string) post('sig');
    $patient = ['first_name' => $txt($p, 'first_name'), 'last_name' => $txt($p, 'last_name'), 'dob' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($p['dob'] ?? '')) ? $p['dob'] : '',
        'citizen_id' => $txt($p, 'citizen_id', 64), 'gender' => in_array($p['gender'] ?? '', ['m', 'w', 'd'], true) ? $p['gender'] : ''];
    $issuer = ['name' => $txt($i, 'name'), 'title' => $txt($i, 'title', 40), 'rank_name' => $txt($i, 'rank_name'), 'department' => $txt($i, 'department'),
        'badge_number' => $txt($i, 'badge_number', 32), 'signature' => preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $sig) && strlen($sig) < 300000 ? $sig : '', 'signature_live' => true];
    if (($type['patient'] ?? '') === 'required' && ($patient['first_name'] === '' || $patient['last_name'] === '')) {
        $errors[] = 'Name der Person fehlt.';
    }
    if ($issuer['name'] === '') {
        $errors[] = 'Name der ausstellenden Person fehlt.';
    }
    update('forgeries', (int) $f['id'], ['type' => $typeKey, 'faction_id' => $fid, 'number' => $f['number'] !== '' && $f['type'] === $typeKey ? $f['number'] : forgery_number($type),
        'data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'patient_snapshot' => json_encode($patient, JSON_UNESCAPED_UNICODE), 'issuer_snapshot' => json_encode($issuer, JSON_UNESCAPED_UNICODE)]);
    $f = one('SELECT * FROM forgeries WHERE id = ?', [$f['id']]);
    if ($action === 'print' && !$errors) {
        update('forgeries', (int) $f['id'], ['status' => 'done', 'done_at' => now()]);
        $f = one('SELECT * FROM forgeries WHERE id = ?', [$f['id']]);
        $done = true;
    }
}
$saved = $f['type'] === $typeKey && $typeKey !== '' && $f['data'] !== null;
$d = $saved ? (json_decode((string) $f['data'], true) ?: []) : [];
$ps = $saved ? (json_decode((string) $f['patient_snapshot'], true) ?: []) : [];
$is = $saved ? (json_decode((string) $f['issuer_snapshot'], true) ?: []) : [];
$link = fn(array $q) => 'forge.php?' . http_build_query(['s' => $session] + $q);
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex">
<title>~/print</title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= e(SAMS_VERSION) ?>">
<link rel="stylesheet" href="assets/css/document.css?v=<?= e(SAMS_VERSION) ?>">
<style>
    body { background: #0b0f0b; color: #b8f5b8; font-family: "Courier New", monospace; margin: 0; }
    .forge-top { padding: .6rem 1rem; border-bottom: 1px solid #1f3a1f; display: flex; gap: 1rem; align-items: center; color: #6fdc6f; }
    .forge-top b { color: #b8f5b8; } .forge-top .q { margin-left: auto; }
    .forge-wrap { display: grid; grid-template-columns: minmax(320px, 440px) 1fr; gap: 1rem; padding: 1rem; }
    .forge-panel { background: #111a11; border: 1px solid #1f3a1f; border-radius: 6px; padding: .9rem; }
    .forge-panel h2 { color: #8ef08e; font-size: .95rem; text-transform: uppercase; letter-spacing: .08em; margin: 0 0 .6rem; }
    .forge-panel label, .forge-panel .form-heading { color: #8ef08e; } .forge-panel .form-heading { font-size: .8rem; margin: .8rem 0 .3rem; }
    .forge-panel input, .forge-panel select, .forge-panel textarea { background: #050805; color: #c9ffc9; border: 1px solid #2c4d2c; font-family: inherit; }
    .forge-panel .field { margin-bottom: .45rem; } .forge-panel .hint { color: #5a9a5a; }
    .forge-btn { background: #1d3b1d; color: #c9ffc9; border: 1px solid #3d7a3d; padding: .45rem .9rem; border-radius: 4px; cursor: pointer; font-family: inherit; }
    .forge-btn.go { background: #2f6b2f; } .forge-err { color: #ff8b8b; }
    .forge-preview { background: #2a2a2a; border-radius: 6px; padding: 12px; overflow: auto; max-height: calc(100vh - 90px); }
    .forge-preview .paper { transform-origin: top left; }
    #sig-pad { background: #fff; border-radius: 4px; width: 100%; height: 110px; touch-action: none; cursor: crosshair; }
    .forge-done { text-align: center; padding: 3rem 1rem; } .forge-done h1 { color: #8ef08e; }
    .typing::after { content: '█'; animation: blink 1s steps(1) infinite; } @keyframes blink { 50% { opacity: 0; } }
</style>
</head>
<body>
<div class="forge-top"><b>root@print-srv</b><span>~/vorlagen</span><span class="q">Toner <?= str_repeat('▮', max(1, (int) round($f['quality'] / 20))) . str_repeat('▯', 5 - max(1, (int) round($f['quality'] / 20))) ?></span>
    <?php if (!$done): ?><button class="forge-btn" type="button" data-cancel>Abbrechen</button><?php endif; ?></div>
<?php if ($done): ?>
    <div class="forge-done"><h1 class="typing">Druckauftrag gesendet</h1><p>Das Blatt kommt gleich aus dem Drucker.</p></div>
    <script>window.parent && window.parent.postMessage({ sams: 'forgeDone', session: <?= json_encode($session) ?> }, '*');</script>
<?php else: ?>
<div class="forge-wrap">
<section class="forge-panel">
    <h2>Vorlage</h2>
    <form method="get" action="forge.php"><input type="hidden" name="s" value="<?= e($session) ?>">
        <div class="field"><label>Behörde</label><select name="f" onchange="this.form.submit()"><option value="">– wählen –</option><?php foreach ($factions as $fx): ?><option value="<?= (int) $fx['id'] ?>"<?= selected($fx['id'], $fid) ?>><?= e($fx['name']) ?></option><?php endforeach; ?></select></div>
        <?php if ($fid): ?><div class="field"><label>Dokument</label><select name="type" onchange="this.form.submit()"><option value="">– wählen –</option><?php foreach ($types as $k => $t): ?><option value="<?= e($k) ?>"<?= selected($k, $typeKey) ?>><?= e($t['title']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    </form>
    <?php if (!$factions): ?><p class="forge-err">Keine passende Vorlage gefunden.</p><?php endif; ?>
    <?php if ($type): ?>
    <form method="post" action="forge.php" id="forge-form">
        <input type="hidden" name="s" value="<?= e($session) ?>"><input type="hidden" name="f" value="<?= (int) $fid ?>"><input type="hidden" name="type" value="<?= e($typeKey) ?>"><input type="hidden" name="sig" id="sig" value="<?= e($is['signature'] ?? '') ?>">
        <?php foreach ($errors as $er): ?><p class="forge-err">! <?= e($er) ?></p><?php endforeach; ?>
        <?php if (($type['patient'] ?? '') !== 'none'): ?>
        <h3 class="form-heading">Person</h3>
        <div class="field"><label>Vorname</label><input name="p[first_name]" value="<?= e($ps['first_name'] ?? '') ?>"></div>
        <div class="field"><label>Nachname</label><input name="p[last_name]" value="<?= e($ps['last_name'] ?? '') ?>"></div>
        <div class="field"><label>Geburtsdatum</label><input type="date" name="p[dob]" value="<?= e($ps['dob'] ?? '') ?>"></div>
        <div class="field"><label>Bürger-ID</label><input name="p[citizen_id]" value="<?= e($ps['citizen_id'] ?? '') ?>"></div>
        <?php endif; ?>
        <h3 class="form-heading">Ausgestellt von</h3>
        <div class="field"><label>Titel</label><input name="i[title]" value="<?= e($is['title'] ?? '') ?>"></div>
        <div class="field"><label>Name</label><input name="i[name]" value="<?= e($is['name'] ?? '') ?>"></div>
        <div class="field"><label>Rang / Funktion</label><input name="i[rank_name]" value="<?= e($is['rank_name'] ?? '') ?>"></div>
        <div class="field"><label>Abteilung</label><input name="i[department]" value="<?= e($is['department'] ?? '') ?>"></div>
        <div class="field"><label>Dienstnummer</label><input name="i[badge_number]" value="<?= e($is['badge_number'] ?? '') ?>"></div>
        <h3 class="form-heading">Inhalt</h3>
        <?php foreach ($type['fields'] as $fl): if (($fl['type'] ?? '') === 'signature') continue; ?>
            <?php $def = ($fl['default'] ?? '') === 'today' ? date('Y-m-d') : ($fl['default'] ?? ''); ?>
            <div class="field"><?= render_field($fl, $d[$fl['name'] ?? ''] ?? $def) ?></div>
        <?php endforeach; ?>
        <h3 class="form-heading">Unterschrift</h3>
        <canvas id="sig-pad" width="600" height="160"></canvas><button type="button" class="forge-btn" id="sig-clear">Löschen</button>
        <p style="margin-top:1rem"><button class="forge-btn" name="action" value="preview">Vorschau</button> <?php if ($saved): ?><button class="forge-btn go" name="action" value="print">Drucken</button><?php endif; ?></p>
        <p class="hint">Gedruckt wird erst nach der Vorschau. Der Rechner ist <?= e(max(0, (int) round((strtotime($f['expires_at']) - time()) / 60))) ?> Minuten erreichbar.</p>
    </form>
    <?php endif; ?>
</section>
<section class="forge-preview">
    <?php if ($saved): ?><div class="<?= e(forgery_flaw_class((int) $f['quality'])) ?>"><?= render_document(forgery_doc($f), ['no_addenda' => true]) ?></div>
    <?php else: ?><p class="typing">Vorlage wählen und ausfüllen</p><?php endif; ?>
</section>
</div>
<?php endif; ?>
<script>
(function () {
    document.querySelectorAll('[data-cancel]').forEach(function (b) { b.addEventListener('click', function () { window.parent.postMessage({ sams: 'forgeCancel' }, '*'); }); });
    var c = document.getElementById('sig-pad'); if (!c) { return; }
    var x = c.getContext('2d'), down = false, dirty = false, hidden = document.getElementById('sig');
    x.lineWidth = 2.4; x.lineCap = 'round'; x.strokeStyle = '#13235a';
    if (hidden.value) { var img = new Image(); img.onload = function () { x.drawImage(img, 0, 0, c.width, c.height); }; img.src = hidden.value; }
    var pos = function (e) { var r = c.getBoundingClientRect(); return [(e.clientX - r.left) * c.width / r.width, (e.clientY - r.top) * c.height / r.height]; };
    c.addEventListener('pointerdown', function (e) { down = true; var p = pos(e); x.beginPath(); x.moveTo(p[0], p[1]); });
    c.addEventListener('pointermove', function (e) { if (!down) { return; } var p = pos(e); x.lineTo(p[0], p[1]); x.stroke(); dirty = true; });
    window.addEventListener('pointerup', function () { if (down && dirty) { hidden.value = c.toDataURL('image/png'); } down = false; });
    document.getElementById('sig-clear').addEventListener('click', function () { x.clearRect(0, 0, c.width, c.height); hidden.value = ''; });
})();
</script>
</body>
</html>
