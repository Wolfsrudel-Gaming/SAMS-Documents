<?php
defined('SAMS_ROOT') || exit;
/** Discord: Webhooks, Berichte, Direktnachrichten, Rollen-Sync. */
$me = current_user();
$ranks = all('SELECT * FROM ranks ORDER BY level DESC');

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'save') {
        $errors = [];
        foreach (array_keys(WEBHOOK_CHANNELS) as $ch) {
            $url = trim((string) post('webhook_' . $ch));
            if ($url !== '' && !valid_webhook_url($url)) {
                $errors[] = WEBHOOK_CHANNELS[$ch] . ': keine gültige Discord-Webhook-Adresse.';
                continue;
            }
            save_setting('webhook_' . $ch, $url);
        }
        foreach (['report_weekly', 'report_monthly', 'discord_dm_enabled', 'discord_duty_log'] as $k) {
            save_setting($k, post($k) ? '1' : '0');
        }
        $map = [];
        foreach ((array) ($_POST['map_role'] ?? []) as $i => $role) {
            $role = preg_replace('/\D/', '', (string) $role);
            $rank = (int) ($_POST['map_rank'][$i] ?? 0);
            if ($role !== '' && $rank && rank_by_id($rank)) {
                $map[$role] = $rank;
            }
        }
        save_setting('discord_role_map', json_encode($map));
        audit('discord.settings', '', null, count($map) . ' Rollen-Zuordnungen');
        foreach ($errors as $err) {
            flash('error', $err);
        }
        if (!$errors) {
            flash('success', 'Discord-Einstellungen gespeichert.');
        }
    } elseif ($action === 'test' && isset(WEBHOOK_CHANNELS[(string) post('channel')])) {
        notify_webhook((string) post('channel'), log_embed('🔔 Testnachricht', 'Der Kanal „' . WEBHOOK_CHANNELS[(string) post('channel')] . '“ ist korrekt eingerichtet. Gesendet von ' . user_display($me) . '.'));
        $n = discord_flush(5);
        flash($n ? 'success' : 'error', $n ? 'Testnachricht gesendet.' : 'Senden fehlgeschlagen – Adresse prüfen (siehe Warteschlange unten).');
    } elseif ($action === 'report') {
        [$f, $t, $label] = stats_period(post('period') === 'lastmonth' ? 'lastmonth' : 'lastweek');
        notify_webhook('reports', stats_discord_embed(stats_collect($f, $t), $label));
        flash('success', 'Bericht ' . $label . ' eingereiht (' . discord_flush(5) . ' gesendet).');
    } elseif ($action === 'sync') {
        save_setting('discord_last_sync', (string) time());
        $changes = discord_sync_all();
        flash('success', $changes ? 'Rollen-Sync: ' . implode('; ', $changes) : 'Rollen-Sync: keine Änderungen.');
    } elseif ($action === 'retry') {
        q('UPDATE discord_queue SET attempts = 0 WHERE sent_at IS NULL');
        flash('success', discord_flush(50) . ' Nachricht(en) gesendet.');
    }
    redirect(url('discord'));
}

$map = discord_role_map();
$pending = all('SELECT * FROM discord_queue WHERE sent_at IS NULL ORDER BY id DESC LIMIT 20');
$sentToday = (int) scalar('SELECT COUNT(*) FROM discord_queue WHERE sent_at >= ?', [date('Y-m-d 00:00:00')]);
$botReady = cfg('discord.bot_token', '') !== '' && cfg('discord.guild_id', '') !== '';

ob_start(); ?>
<div class="page-head">
    <div><h1>Discord</h1><p class="muted">Benachrichtigungen, Berichte, Direktnachrichten und Rollen-Sync. Medizinische Inhalte werden nie übertragen.</p></div>
</div>
<div class="grid-side wide-side reverse">
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="save">
    <h2>Webhooks</h2>
    <p class="hint w-full">In Discord: Kanal → Einstellungen → Integrationen → Webhooks → „Webhook-URL kopieren“.</p>
    <?php foreach (WEBHOOK_CHANNELS as $ch => $label): ?>
        <div class="field w-full"><label><?= e($label) ?></label>
            <div class="inline-form"><input name="webhook_<?= e($ch) ?>" value="<?= e(setting('webhook_' . $ch, '')) ?>" placeholder="https://discord.com/api/webhooks/…" class="mono small">
                <?php if (valid_webhook_url((string) setting('webhook_' . $ch, ''))): ?><button class="btn small" name="action" value="test" formnovalidate onclick="this.form.channel.value='<?= e($ch) ?>'">Test</button><?php endif; ?></div></div>
    <?php endforeach; ?>
    <input type="hidden" name="channel" value="">
    <div class="field w-full">
        <label class="choice single"><input type="checkbox" name="report_weekly" value="1"<?= checked(setting('report_weekly', '0') === '1') ?>> Wochenbericht montags ab 8 Uhr senden</label>
        <label class="choice single"><input type="checkbox" name="report_monthly" value="1"<?= checked(setting('report_monthly', '0') === '1') ?>> Monatsbericht am 1. ab 8 Uhr senden</label>
        <label class="choice single"><input type="checkbox" name="discord_duty_log" value="1"<?= checked(setting('discord_duty_log', '0') === '1') ?>> Dienstbeginn/-ende im Personal-Log melden</label>
        <label class="choice single"><input type="checkbox" name="discord_dm_enabled" value="1"<?= checked(setting('discord_dm_enabled', '1') === '1') ?>> Bürger per Direktnachricht informieren (neue Dokumente, Termine, Anträge)<?= cfg('discord.bot_token', '') === '' ? ' – benötigt DISCORD_BOT_TOKEN' : '' ?></label>
    </div>

    <h2 class="w-full">Rollen-Sync (Discord-Rolle → Rang)</h2>
    <p class="hint w-full">Es gilt der höchste zugeordnete Rang. Wer keine zugeordnete Rolle hat, behält seinen Rang. Abgleich stündlich und bei jeder Discord-Anmeldung.
        <?= $botReady ? '' : '<strong>Benötigt DISCORD_BOT_TOKEN und DISCORD_GUILD_ID in der .env.</strong>' ?> Rollen-ID: in Discord Entwicklermodus an, Rechtsklick auf die Rolle → „ID kopieren“.</p>
    <table class="list compact w-full" id="rolemap">
        <thead><tr><th>Discord-Rollen-ID</th><th>Rang</th></tr></thead>
        <tbody>
        <?php foreach ($map + ['' => 0] as $role => $rank): ?>
            <tr><td><input name="map_role[]" value="<?= e((string) $role) ?>" class="mono" placeholder="123456789012345678"></td>
                <td><select name="map_rank[]"><option value="">–</option><?php foreach ($ranks as $r): ?><option value="<?= (int) $r['id'] ?>"<?= selected($r['id'], $rank) ?>><?= e($r['name']) ?></option><?php endforeach; ?></select></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div class="w-full"><button type="button" class="btn small" onclick="var t=document.querySelector('#rolemap tbody'),r=t.lastElementChild.cloneNode(true);r.querySelectorAll('input').forEach(function(i){i.value=''});r.querySelector('select').value='';t.appendChild(r)">+ Zuordnung</button></div>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<section>
    <div class="card">
        <h2>Aktionen</h2>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="report">
            <select name="period"><option value="lastweek">Letzte Woche</option><option value="lastmonth">Letzter Monat</option></select><button class="btn small">Bericht jetzt senden</button></form>
        <?php if ($map): ?><form method="post" style="margin-top:.5rem"><?= csrf_field() ?><input type="hidden" name="action" value="sync"><button class="btn small"<?= $botReady ? '' : ' disabled' ?>>Rollen jetzt abgleichen</button></form><?php endif; ?>
        <p class="hint">Discord-Login: <?= discord_configured() ? '<span class="badge green">eingerichtet</span>' : '<span class="badge grey">nicht eingerichtet</span> (DISCORD_CLIENT_ID/SECRET)' ?></p>
        <p class="hint">Redirect-URIs für die Discord-App:<br><code><?= e(discord_staff_redirect()) ?></code><br><code><?= e(base_url() . '/portal.php?action=callback') ?></code></p>
    </div>
    <div class="card">
        <h2>Warteschlange</h2>
        <p class="small muted">Heute gesendet: <?= $sentToday ?> · ausstehend: <?= count($pending) ?></p>
        <?php if ($pending): ?>
            <table class="list compact"><tbody>
            <?php foreach ($pending as $m): ?><tr><td class="small"><?= e($m['kind']) ?> · <?= e(fmt_datetime($m['created_at'])) ?></td><td class="small right"><?= (int) $m['attempts'] ?>× <?= e($m['last_error']) ?></td></tr><?php endforeach; ?>
            </tbody></table>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="retry"><button class="btn small">Erneut senden</button></form>
        <?php endif; ?>
    </div>
</section>
</div>
<?php
$content = ob_get_clean();
$title = 'Discord';
require SAMS_ROOT . '/app/layout.php';
