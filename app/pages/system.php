<?php
defined('SAMS_ROOT') || exit;
require_once SAMS_ROOT . '/app/backup.php';

if (is_post()) {
    csrf_check();
    switch (post('action')) {
        case 'check_update':
            request_update('admin ' . current_user()['username']);
            audit('system.update_request');
            flash('success', 'Update-Prüfung angefordert. Ist der Webhook-/Path-Trigger eingerichtet, läuft sie sofort, sonst beim nächsten Timer-Lauf (max. 2 Minuten).');
            break;
        case 'migrate':
            $done = run_migrations();
            audit('system.migrate', '', null, implode(', ', $done));
            flash('success', $done ? count($done) . ' Migration(en) ausgeführt.' : 'Datenbank war bereits aktuell.');
            break;
        case 'backup':
            $file = create_backup('manuell');
            audit('system.backup', '', null, basename($file));
            flash('success', 'Backup erstellt: ' . basename($file));
            break;
    }
    redirect(url('system'));
}

if (isset($_GET['download'])) {
    $name = basename((string) $_GET['download']);
    $file = backup_dir() . '/' . $name;
    if (!preg_match('/^sams-[\w-]+\.(sql|sqlite)\.gz$/', $name) || !is_file($file)) {
        not_found('Backup nicht gefunden.');
    }
    audit('system.backup_download', '', null, $name);
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . $name . '"');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

$git = git_version();
$last = update_status();
$lastCheck = update_last_check();
$pending = pending_migrations();
$backups = array_slice(list_backups(), 0, 15);
$updateLog = update_log_tail(40);
$statusLabels = ['ok' => ['green', 'Erfolgreich'], 'failed' => ['red', 'Fehlgeschlagen'], 'rolled_back' => ['orange', 'Zurückgesetzt']];
$timerActive = $lastCheck && strtotime($lastCheck) > time() - 600;

ob_start(); ?>
<div class="page-head">
    <div><h1>System</h1><p class="muted">Version, automatische Updates und Datensicherung</p></div>
</div>

<?php if ($pending): ?>
<div class="flash warn">Es gibt <?= count($pending) ?> ausstehende Datenbank-Migration(en): <?= e(implode(', ', $pending)) ?>.
    <form method="post" style="display:inline"><?= csrf_field() ?><button class="btn small" name="action" value="migrate">Jetzt ausführen</button></form></div>
<?php endif; ?>

<div class="grid-2">
    <section class="card">
        <h2>Version</h2>
        <dl class="kv">
            <dt>Version</dt><dd><?= e(SAMS_VERSION) ?></dd>
            <dt>Commit</dt><dd class="mono"><?= e($git['commit'] !== '' ? substr($git['commit'], 0, 12) : '–') ?></dd>
            <dt>Branch (lokal)</dt><dd class="mono"><?= e($git['branch'] ?: '–') ?></dd>
            <dt>Folgt Branch</dt><dd class="mono"><?= e(cfg('update.remote', 'origin') . '/' . cfg('update.branch', 'main')) ?></dd>
            <dt>Datenbank</dt><dd><?= e(db_driver() === 'mysql' ? 'MySQL / MariaDB' : 'SQLite') ?> · <?= count(ran_migrations()) ?> Migrationen</dd>
            <dt>PHP</dt><dd><?= e(PHP_VERSION) ?></dd>
        </dl>
    </section>

    <section class="card">
        <div class="card-head"><h2>Auto-Update</h2>
            <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="check_update">Jetzt auf Updates prüfen</button></form>
        </div>
        <dl class="kv">
            <dt>Update-Timer</dt><dd><?= $timerActive ? '<span class="badge green">aktiv</span>' : '<span class="badge red">nicht aktiv</span>' ?>
                <?= $lastCheck ? '<small class="muted">letzte Prüfung ' . e(fmt_datetime($lastCheck)) . '</small>' : '' ?></dd>
            <dt>Letztes Update</dt><dd>
                <?php if ($last): [$c, $l] = $statusLabels[$last['status']] ?? ['grey', $last['status']]; ?>
                    <span class="badge <?= $c ?>"><?= e($l) ?></span> <?= e(fmt_datetime($last['finished_at'] ?? '')) ?><br>
                    <span class="mono small"><?= e(substr($last['from'] ?? '', 0, 7)) ?> → <?= e(substr($last['to'] ?? '', 0, 7)) ?></span>
                    <small class="muted"><?= e($last['message'] ?? '') ?></small>
                <?php else: ?>–<?php endif; ?></dd>
            <dt>GitHub-Webhook</dt><dd><?= cfg('update.webhook_secret') ? '<span class="badge green">konfiguriert</span> <code>' . e(base_url()) . '/hooks/github.php</code>' : '<span class="badge grey">nicht konfiguriert</span>' ?></dd>
        </dl>
        <?php if (!$timerActive): ?>
            <p class="hint">Der Update-Timer hat in den letzten 10 Minuten nicht geprüft. Einrichtung siehe README, Abschnitt „Auto-Update“.</p>
        <?php endif; ?>
    </section>
</div>

<section class="card">
    <div class="card-head"><h2>Datensicherung</h2>
        <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="backup">Backup jetzt erstellen</button></form>
    </div>
    <p class="muted small">Backups liegen in <code>storage/backups/</code> und werden nach <?= (int) cfg('backup_keep_days', 14) ?> Tagen gelöscht. Vor jedem Update wird automatisch gesichert.</p>
    <table class="list compact">
        <thead><tr><th>Zeitpunkt</th><th>Datei</th><th class="right">Größe</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($backups as $b): ?>
            <tr><td><?= e(date('d.m.Y H:i', $b['time'])) ?></td><td class="mono"><?= e($b['file']) ?></td>
                <td class="right"><?= e(number_format($b['size'] / 1024, 1, ',', '.')) ?> KB</td>
                <td class="right"><a href="<?= e(url('system', ['download' => $b['file']])) ?>">Herunterladen</a></td></tr>
        <?php endforeach; ?>
        <?php if (!$backups): ?><tr><td colspan="4" class="muted">Noch keine Backups.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<section class="card">
    <h2>Update-Protokoll</h2>
    <pre class="logbox"><?= e($updateLog !== '' ? $updateLog : 'Noch keine Einträge.') ?></pre>
</section>
<?php
$content = ob_get_clean();
$title = 'System';
require SAMS_ROOT . '/app/layout.php';
