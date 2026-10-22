<?php
defined('SAMS_ROOT') || exit;
/** Andock-Schnittstelle verwalten – nur Hauptadministrator: Schlüssel für eigene Skripte, Rechte, Fraktionen, Webhooks. */
$me = current_user();
if (empty($me['is_superadmin'])) {
    forbidden('Schnittstellen verwaltet nur der Hauptadministrator.');
}

$form = function (): array {
    $scopes = array_values(array_intersect(array_map('strval', (array) ($_POST['scopes'] ?? [])), array_keys(INTEGRATION_SCOPES)));
    $valid = array_map(fn($f) => (int) $f['id'], factions(false));
    $facs = array_values(array_intersect(array_map('intval', (array) ($_POST['factions'] ?? [])), $valid));
    return ['name' => mb_substr(trim((string) post('name')), 0, 190), 'notes' => mb_substr(trim((string) post('notes')), 0, 190),
        'scopes' => json_encode($scopes), 'factions' => json_encode($facs), 'webhook_url' => mb_substr(trim((string) post('webhook_url')), 0, 500),
        'event_filter' => mb_substr(preg_replace('/[^a-z0-9_.*,\s]/i', '', (string) post('event_filter')), 0, 190)];
};

if (is_post()) {
    csrf_check();
    $action = post('action');
    $integ = (int) post('id') ? one('SELECT * FROM integrations WHERE id = ?', [(int) post('id')]) : null;
    if (in_array($action, ['create', 'save'], true)) {
        $row = $form();
        if ($row['name'] === '') {
            flash('error', 'Bitte einen Namen angeben.');
            redirect(url('integrations'));
        }
        if ($row['webhook_url'] !== '' && !valid_integration_webhook($row['webhook_url'])) {
            flash('error', 'Die Webhook-Adresse muss mit http:// oder https:// beginnen.');
            redirect(url('integrations', $integ ? ['open' => $integ['id']] : []));
        }
    }
    if ($action === 'create') {
        $token = integration_new_token();
        $id = insert('integrations', $row + ['token_hash' => hash('sha256', $token), 'token_prefix' => substr($token, 0, 12), 'webhook_secret' => bin2hex(random_bytes(24)),
            'active' => 1, 'last_event_id' => integration_last_event_id(), 'created_by' => $me['id'], 'created_at' => now()]);
        audit('integration.create', 'integration', $id, $row['name']);
        $_SESSION['integration_token'] = [$id, $token];
        redirect(url('integrations', ['open' => $id]));
    } elseif ($action === 'save' && $integ) {
        if ($row['webhook_url'] !== $integ['webhook_url']) {
            $row += ['webhook_failures' => 0, 'webhook_error' => '', 'last_event_id' => max((int) $integ['last_event_id'], $integ['webhook_url'] === '' ? integration_last_event_id() : 0)];
        }
        update('integrations', (int) $integ['id'], $row + ['active' => post('active') ? 1 : 0, 'updated_at' => now()]);
        audit('integration.update', 'integration', (int) $integ['id'], $row['name']);
        flash('success', 'Gespeichert.');
    } elseif ($action === 'new_token' && $integ) {
        $token = integration_new_token();
        update('integrations', (int) $integ['id'], ['token_hash' => hash('sha256', $token), 'token_prefix' => substr($token, 0, 12), 'updated_at' => now()]);
        audit('integration.token', 'integration', (int) $integ['id'], $integ['name'] . ': neuer Schlüssel');
        $_SESSION['integration_token'] = [(int) $integ['id'], $token];
    } elseif ($action === 'new_secret' && $integ) {
        update('integrations', (int) $integ['id'], ['webhook_secret' => bin2hex(random_bytes(24)), 'updated_at' => now()]);
        audit('integration.secret', 'integration', (int) $integ['id'], $integ['name'] . ': neues Webhook-Geheimnis');
        flash('success', 'Neues Webhook-Geheimnis erzeugt – im empfangenden Skript eintragen.');
    } elseif ($action === 'test' && $integ) {
        integration_event('integration.test', ['message' => 'Testereignis aus SAMS', 'by' => user_display($me)], null, (int) $integ['id']);
        $n = $integ['webhook_url'] !== '' ? integration_deliver(1) : 0;
        $fresh = one('SELECT webhook_error FROM integrations WHERE id = ?', [$integ['id']]);
        flash($integ['webhook_url'] === '' || $n ? 'success' : 'error', $integ['webhook_url'] === '' ? 'Testereignis angelegt – mit action=events abrufbar.'
            : ($n ? 'Testereignis zugestellt.' : 'Zustellung fehlgeschlagen: ' . ($fresh['webhook_error'] ?: 'kein Recht events.read') . '. Neuer Versuch beim nächsten Cron-Lauf.'));
    } elseif ($action === 'delete' && $integ) {
        q('DELETE FROM integrations WHERE id = ?', [$integ['id']]);
        audit('integration.delete', 'integration', (int) $integ['id'], $integ['name']);
        flash('success', 'Schnittstelle „' . $integ['name'] . '“ entfernt. Der Schlüssel gilt nicht mehr.');
        redirect(url('integrations'));
    } elseif ($action === 'rate') {
        save_setting('integration_rate', (string) max(10, min(5000, (int) post('rate'))));
        flash('success', 'Gespeichert.');
    }
    redirect(url('integrations', $integ ? ['open' => $integ['id']] : []));
}

$list = integrations();
$open = (int) ($_GET['open'] ?? 0);
$shown = $_SESSION['integration_token'] ?? null;
unset($_SESSION['integration_token']);
$eventsToday = (int) scalar('SELECT COUNT(*) FROM integration_events WHERE created_at >= ?', [date('Y-m-d 00:00:00')]);

$fields = function (?array $i) {
    $scopes = $i ? integration_scopes($i) : ['persons.read', 'wanted.read', 'documents.read', 'duty.read', 'laws.read', 'events.read'];
    $facs = $i ? array_map('intval', json_decode((string) $i['factions'], true) ?: []) : [];
    ob_start(); ?>
    <div class="field w-third"><label>Name</label><input name="name" value="<?= e($i['name'] ?? '') ?>" required maxlength="190" placeholder="z. B. Discord-Bot Leitstelle"></div>
    <div class="field w-two-thirds"><label>Notiz</label><input name="notes" value="<?= e($i['notes'] ?? '') ?>" maxlength="190" placeholder="Wer betreibt das Skript, wo läuft es?"></div>
    <div class="field w-full"><label>Rechte</label><div class="choices">
        <?php foreach (INTEGRATION_SCOPES as $k => [$label, $hint]): ?>
            <label class="choice" title="<?= e($hint) ?>"><input type="checkbox" name="scopes[]" value="<?= e($k) ?>"<?= checked(in_array($k, $scopes, true)) ?>> <?= e($label) ?> <code class="small"><?= e($k) ?></code></label>
        <?php endforeach; ?></div>
        <p class="hint">Medizinische Akten und Dokumente des Rettungsdienstes gibt die Schnittstelle nie heraus – unabhängig von den Rechten.</p></div>
    <div class="field w-full"><label>Fraktionen <small class="muted">(nichts gewählt = alle)</small></label><div class="choices">
        <?php foreach (factions(false) as $f): ?>
            <label class="choice"><input type="checkbox" name="factions[]" value="<?= (int) $f['id'] ?>"<?= checked(in_array((int) $f['id'], $facs, true)) ?>> <?= e($f['short'] ?: $f['name']) ?> <span class="muted small"><?= e($f['code']) ?></span></label>
        <?php endforeach; ?></div></div>
    <div class="field w-two-thirds"><label>Webhook-Adresse <small class="muted">(optional, braucht „Ereignisse empfangen“)</small></label><input name="webhook_url" value="<?= e($i['webhook_url'] ?? '') ?>" maxlength="500" placeholder="https://mein-bot.example/sams"></div>
    <div class="field w-third"><label>Nur diese Ereignisse <small class="muted">(leer = alle)</small></label><input name="event_filter" value="<?= e($i['event_filter'] ?? '') ?>" maxlength="190" placeholder="document.*, duty.changed"></div>
    <?php return ob_get_clean();
};

ob_start(); ?>
<div class="page-head"><div><h1>Schnittstellen</h1><p class="muted">Schlüssel für eigene Skripte und Tools (Discord-Bots, Webseiten, weitere FiveM-Resourcen), die an SAMS andocken. Jeder Schlüssel hat eigene Rechte und Fraktionen und lässt sich jederzeit sperren. FiveM-Resourcen auf einem verbundenen Spielserver brauchen keinen eigenen Schlüssel – sie nutzen die Exports von <code>sams_tablet</code>.</p></div></div>

<?php if ($shown): ?>
<div class="card"><h2>Neuer Schlüssel</h2>
    <p><strong>Jetzt kopieren – er wird nur dieses eine Mal angezeigt.</strong> Im Skript als Header <code>Authorization: Bearer …</code> senden.</p>
    <pre class="code-block mono"><?= e($shown[1]) ?></pre>
    <button type="button" class="btn small" data-copy="<?= e($shown[1]) ?>"><?= icon('copy') ?> Kopieren</button>
</div>
<?php endif; ?>

<div class="card"><h2>So docken Skripte an</h2>
    <dl class="kv">
        <dt>Adresse</dt><dd><code><?= e(base_url()) ?>/integration.php?action=ping</code> <button type="button" class="btn small" data-copy="<?= e(base_url()) ?>/integration.php">Kopieren</button></dd>
        <dt>Im Spiel (Lua)</dt><dd><code>exports.sams_tablet:Call('person', { citizen_id = 'ABC123' })</code>, Ereignisse mit <code>AddEventHandler('sams:event', function(name, data) … end)</code></dd>
        <dt>Ereignisse heute</dt><dd><?= $eventsToday ?></dd>
    </dl>
    <p class="hint">Alle Aufrufe, Rechte, Ereignisse und Beispiele stehen in <code>docs/API.md</code> im Projekt.</p>
    <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="rate">
        <label class="small">Höchstens <input type="number" name="rate" value="<?= e((string) setting('integration_rate', '240')) ?>" min="10" max="5000" style="width:6rem"> Anfragen je Minute und Schlüssel</label> <button class="btn small">Speichern</button></form>
</div>

<?php foreach ($list as $i): $scopes = integration_scopes($i); ?>
<details class="card" id="int-<?= (int) $i['id'] ?>"<?= $open === (int) $i['id'] ? ' open' : '' ?>>
    <summary class="card-head"><h2><?= e($i['name']) ?> <small class="muted mono"><?= e($i['token_prefix']) ?>…</small></h2>
        <span><?= !$i['active'] ? '<span class="badge grey">gesperrt</span>' : ($i['last_used_at'] ? '<span class="badge green">zuletzt ' . e(fmt_datetime($i['last_used_at'])) . '</span>' : '<span class="badge orange">noch nie benutzt</span>') ?>
        <?= $i['webhook_url'] !== '' ? ($i['webhook_error'] !== '' ? '<span class="badge red">Webhook: ' . e($i['webhook_error']) . '</span>' : '<span class="badge blue">Webhook</span>') : '' ?>
        <span class="muted small"><?= count($scopes) ?> Recht(e)</span></span></summary>
    <?php if ($i['last_ip']): ?><p class="muted small">Letzte Adresse: <?= e($i['last_ip']) ?><?= $i['webhook_at'] ? ' · Webhook zuletzt ' . e(fmt_datetime($i['webhook_at'])) : '' ?></p><?php endif; ?>
    <form method="post" class="form-grid">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>">
        <?= $fields($i) ?>
        <div class="field w-sixth"><label class="choice"><input type="checkbox" name="active" value="1"<?= checked((bool) $i['active']) ?>> aktiv</label></div>
        <div class="form-actions"><button class="btn primary">Speichern</button></div>
    </form>
    <?php if ($i['webhook_url'] !== ''): ?>
        <h3 class="form-heading">Webhook-Signatur</h3>
        <p class="small">Jede Zustellung trägt <code>X-SAMS-Timestamp</code> und <code>X-SAMS-Signature: sha256=HMAC(Zeitstempel + "." + Body, Geheimnis)</code>.</p>
        <dl class="kv"><dt>Geheimnis</dt><dd><details class="inline-reveal"><summary class="btn small">Anzeigen</summary><code class="mono"><?= e($i['webhook_secret']) ?></code></details>
            <button type="button" class="btn small" data-copy="<?= e($i['webhook_secret']) ?>">Kopieren</button></dd></dl>
    <?php endif; ?>
    <div class="btn-row">
        <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="test"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>"><button class="btn small">Testereignis senden</button></form>
        <form method="post" class="inline" onsubmit="return confirm('Neuen Schlüssel erzeugen? Der bisherige gilt sofort nicht mehr.')"><?= csrf_field() ?><input type="hidden" name="action" value="new_token"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>"><button class="btn small">Neuer Schlüssel</button></form>
        <form method="post" class="inline" onsubmit="return confirm('Neues Webhook-Geheimnis erzeugen?')"><?= csrf_field() ?><input type="hidden" name="action" value="new_secret"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>"><button class="btn small">Neues Webhook-Geheimnis</button></form>
        <form method="post" class="inline" onsubmit="return confirm('Schnittstelle entfernen? Das Skript verliert sofort den Zugriff.')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $i['id'] ?>"><button class="btn small danger">Entfernen</button></form>
    </div>
</details>
<?php endforeach; ?>

<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <h2 class="w-full">Neue Schnittstelle</h2>
    <?= $fields(null) ?>
    <div class="form-actions"><button class="btn primary">Anlegen und Schlüssel erzeugen</button></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Schnittstellen';
require SAMS_ROOT . '/app/layout.php';
