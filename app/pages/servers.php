<?php
defined('SAMS_ROOT') || exit;
/** Spielserver (Staaten) verwalten – nur Hauptadministrator: Schlüssel, Portal-Adresse, Einstellungen je Server. */
$me = current_user();
if (empty($me['is_superadmin'])) {
    forbidden('Spielserver verwaltet nur der Hauptadministrator.');
}

if (is_post()) {
    csrf_check();
    $action = post('action');
    $srv = (int) post('id') ? game_server((int) post('id')) : null;
    $base = ['name' => mb_substr(trim((string) post('name')), 0, 190), 'short' => mb_substr(trim((string) post('short')), 0, 16), 'notes' => mb_substr(trim((string) post('notes')), 0, 190)];
    if ($action === 'create') {
        if ($base['name'] === '') {
            flash('error', 'Bitte einen Namen (Staat) angeben.');
        } else {
            $id = insert('game_servers', $base + ['api_key' => game_server_new_key(), 'config' => '{}', 'active' => 1, 'created_at' => now()]);
            audit('server.create', 'game_server', $id, $base['name']);
            flash('success', 'Spielserver „' . $base['name'] . '“ angelegt. Schlüssel und Portal-Adresse in die server.cfg dieses Servers eintragen.');
            redirect(url('servers', ['open' => $id]));
        }
    } elseif ($action === 'save' && $srv) {
        $cfg = [];
        foreach (GAME_SERVER_OVERRIDES as $k => [, $opts]) {
            $v = trim((string) post('cfg_' . $k));
            if ($v !== '' && ($opts === null || array_key_exists($v, $opts))) {
                $cfg[$k] = mb_substr(preg_match('/^(ingame_cmd_|duty_command|ingame_billing_table)/', $k) ? preg_replace('/[^a-z0-9_]/i', '', $v) : $v, 0, 64);
            }
        }
        $active = post('active') ? 1 : 0;
        if (!$active && (int) $srv['id'] === game_server_default_id()) {
            $active = 1; // der Hauptserver nimmt alles ohne Zuordnung auf
        }
        update('game_servers', (int) $srv['id'], ($base['name'] !== '' ? $base : []) + ['config' => json_encode($cfg, JSON_UNESCAPED_UNICODE), 'active' => $active, 'updated_at' => now()]);
        audit('server.update', 'game_server', (int) $srv['id'], $base['name'] ?: $srv['name']);
        flash('success', 'Gespeichert. Der Server übernimmt Änderungen innerhalb weniger Minuten (Befehle nach restart sams_tablet).');
    } elseif ($action === 'new_key' && $srv) {
        update('game_servers', (int) $srv['id'], ['api_key' => game_server_new_key(), 'updated_at' => now()]);
        audit('server.key', 'game_server', (int) $srv['id'], $srv['name'] . ': neuer API-Schlüssel');
        flash('success', 'Neuer Schlüssel erzeugt. Der alte gilt ab sofort nicht mehr – server.cfg von „' . $srv['name'] . '“ anpassen und sams_tablet neu starten.');
    } elseif ($action === 'delete' && $srv && (int) $srv['id'] !== game_server_default_id()) {
        if ((int) scalar('SELECT COUNT(*) FROM printers WHERE server_id = ?', [$srv['id']])) {
            flash('error', 'Dem Server sind noch Drucker zugeordnet. Bitte erst umstellen oder deaktivieren.');
        } else {
            q('DELETE FROM game_servers WHERE id = ?', [$srv['id']]);
            q("UPDATE tablet_tokens SET revoked = 1 WHERE server_id = ?", [$srv['id']]);
            audit('server.delete', 'game_server', (int) $srv['id'], $srv['name']);
            flash('success', 'Spielserver entfernt.');
        }
    }
    redirect(url('servers', $srv ? ['open' => $srv['id']] : []));
}

$servers = game_servers();
$open = (int) ($_GET['open'] ?? 0);
$counts = [];
foreach (all('SELECT server_id, COUNT(*) AS n FROM printers GROUP BY server_id') as $r) {
    $counts[(int) $r['server_id']] = (int) $r['n'];
}
$online = fn(array $s) => $s['last_seen_at'] && strtotime((string) $s['last_seen_at']) > time() - 900;

ob_start(); ?>
<div class="page-head"><div><h1>Spielserver</h1><p class="muted">Mehrere FiveM-Server (z. B. je Staat) an diesem Portal. Jeder Server bekommt einen eigenen API-Schlüssel; Drucker, Tablet-Anmeldungen und Rechnungen gehören zu genau einem Server. Akten, Dokumente und Fraktionen sind gemeinsam.</p></div></div>

<?php foreach ($servers as $s): $cfg = json_decode((string) $s['config'], true) ?: []; $key = game_server_key($s); $isMain = (int) $s['id'] === game_server_default_id(); ?>
<details class="card" id="srv-<?= (int) $s['id'] ?>"<?= $open === (int) $s['id'] || count($servers) === 1 ? ' open' : '' ?>>
    <summary class="card-head"><h2><?= e($s['name']) ?><?= $s['short'] ? ' <small class="muted">' . e($s['short']) . '</small>' : '' ?></h2>
        <span><?= $isMain ? '<span class="badge blue">Hauptserver</span> ' : '' ?><?= !$s['active'] ? '<span class="badge grey">deaktiviert</span>' : ($online($s) ? '<span class="badge green">verbunden</span>' : '<span class="badge orange">' . ($s['last_seen_at'] ? 'zuletzt ' . e(fmt_datetime($s['last_seen_at'])) : 'noch nie verbunden') . '</span>') ?>
        <span class="muted small"><?= (int) ($counts[(int) $s['id']] ?? 0) ?> Drucker</span></span></summary>

    <h3 class="form-heading">Verbindung (server.cfg dieses Servers)</h3>
    <?php if ($key === ''): ?>
        <div class="flash warn">Noch kein Schlüssel. Bitte „Neuen Schlüssel erzeugen“.</div>
    <?php else: ?>
        <dl class="kv">
            <dt>sams_url</dt><dd><code><?= e(base_url()) ?></code> <button type="button" class="btn small" data-copy="<?= e(base_url()) ?>">Kopieren</button></dd>
            <dt>sams_api_key</dt><dd><details class="inline-reveal"><summary class="btn small">Anzeigen</summary><code class="mono"><?= e($key) ?></code></details>
                <button type="button" class="btn small" data-copy="<?= e($key) ?>">Kopieren</button><?= $isMain && (string) $s['api_key'] === '' ? ' <span class="muted small">(aus der .env)</span>' : '' ?></dd>
        </dl>
        <p class="small">Für die <code>server.cfg</code> (mit <code>set</code>, nie <code>setr</code> – der Schlüssel darf nicht zu den Spielern):</p>
        <pre class="code-block"><?= e("set sams_url \"" . base_url() . "\"\nset sams_api_key \"••••••••\"") ?></pre>
        <button type="button" class="btn small" data-copy="<?= e(game_server_cfg($s)) ?>"><?= icon('copy') ?> Beide Zeilen kopieren</button>
    <?php endif; ?>
    <form method="post" class="inline" onsubmit="return confirm('Neuen Schlüssel erzeugen? Der bisherige gilt dann sofort nicht mehr.')"><?= csrf_field() ?><input type="hidden" name="action" value="new_key"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="btn small">Neuen Schlüssel erzeugen</button></form>

    <form method="post" class="form-grid" style="margin-top:1rem">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
        <div class="field w-third"><label>Name / Staat</label><input name="name" value="<?= e($s['name']) ?>" required maxlength="190"></div>
        <div class="field w-sixth"><label>Kürzel</label><input name="short" value="<?= e($s['short']) ?>" maxlength="16"></div>
        <div class="field w-third"><label>Notiz</label><input name="notes" value="<?= e($s['notes']) ?>" maxlength="190" placeholder="z. B. IP, Hoster, Ansprechpartner"></div>
        <div class="field w-sixth"><label class="choice"><input type="checkbox" name="active" value="1"<?= checked((bool) $s['active']) ?><?= $isMain ? ' disabled' : '' ?>> aktiv</label></div>
        <h3 class="form-heading w-full">Abweichende Spieleinstellungen <small class="muted">(leer = wie unter Einstellungen → Spielserver)</small></h3>
        <?php foreach (GAME_SERVER_OVERRIDES as $k => [$label, $opts]): ?>
            <div class="field w-third"><label><?= e($label) ?></label>
            <?php if ($opts): ?><select name="cfg_<?= e($k) ?>"><option value="">wie global (<?= e($opts[(string) setting($k, '')] ?? (string) setting($k, '–')) ?>)</option><?php foreach ($opts as $v => $l): ?><option value="<?= e($v) ?>"<?= selected($v, $cfg[$k] ?? null) ?>><?= e($l) ?></option><?php endforeach; ?></select>
            <?php else: ?><input name="cfg_<?= e($k) ?>" value="<?= e($cfg[$k] ?? '') ?>" placeholder="wie global: <?= e((string) setting($k, '')) ?>" maxlength="64"><?php endif; ?></div>
        <?php endforeach; ?>
        <div class="form-actions"><button class="btn primary">Speichern</button></div>
    </form>
    <?php if (!$isMain): ?><form method="post" class="inline" onsubmit="return confirm('Spielserver entfernen? Tablet-Anmeldungen dieses Servers enden.')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>"><button class="btn small danger">Entfernen</button></form><?php endif; ?>
</details>
<?php endforeach; ?>

<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <h2 class="w-full">Weiteren Spielserver verbinden</h2>
    <div class="field w-third"><label>Name / Staat</label><input name="name" required maxlength="190" placeholder="z. B. Liberty City"></div>
    <div class="field w-sixth"><label>Kürzel</label><input name="short" maxlength="16" placeholder="LC"></div>
    <div class="field w-third"><label>Notiz</label><input name="notes" maxlength="190"></div>
    <div class="form-actions"><button class="btn primary">Anlegen und Schlüssel erzeugen</button></div>
    <p class="hint w-full">Danach auf dem neuen FiveM-Server die Resource <code>sams_tablet</code> installieren, beide Zeilen in die <code>server.cfg</code> eintragen und unter <a href="<?= e(url('printers')) ?>">Drucker</a> die Drucker diesem Staat zuordnen.</p>
</form>
<?php
$content = ob_get_clean();
$title = 'Spielserver';
require SAMS_ROOT . '/app/layout.php';
