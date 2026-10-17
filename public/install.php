<?php
/**
 * Ersteinrichtung im Browser: .env schreiben (falls nötig), Datenbank
 * migrieren, erstes Admin-Konto anlegen. Danach gesperrt.
 * Alternative auf der Kommandozeile: php bin/sams create-admin
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
start_session();

$hasEnv = is_configured();
$installed = false;
if ($hasEnv) {
    try {
        $installed = (int) scalar('SELECT COUNT(*) FROM users') > 0;
    } catch (Throwable $e) {
        $installed = false;
    }
}

$errors = [];
$done = false;
$envText = '';
$in = [
    'app_url' => rtrim(base_url(), '/'),
    'driver' => 'mysql',
    'host' => '127.0.0.1', 'port' => '3306', 'database' => 'sams', 'username' => 'sams', 'password' => '',
    'admin_user' => 'admin', 'admin_name' => '', 'org_name' => 'San Andreas Medical Services',
];

if (!$installed && is_post()) {
    csrf_check();
    foreach ($in as $k => $v) {
        $in[$k] = trim((string) ($_POST[$k] ?? $v));
    }
    $pw = (string) ($_POST['admin_pass'] ?? '');
    if (!preg_match('/^[A-Za-z0-9._\-]{3,40}$/', $in['admin_user'])) {
        $errors[] = 'Benutzername ungültig (3–40 Zeichen: Buchstaben, Ziffern, . _ -).';
    }
    $errors = array_merge($errors, password_problems($pw, $in['admin_user']));
    if ($in['admin_name'] === '') {
        $errors[] = 'Bitte deinen Namen angeben.';
    }

    if (!$errors && !$hasEnv) {
        // .env aus der Vorlage erzeugen
        $values = [
            'APP_URL' => $in['app_url'],
            'DB_DRIVER' => $in['driver'] === 'sqlite' ? 'sqlite' : 'mysql',
            'DB_HOST' => $in['host'], 'DB_PORT' => $in['port'], 'DB_DATABASE' => $in['database'],
            'DB_USERNAME' => $in['username'], 'DB_PASSWORD' => $in['password'],
            'API_KEY' => bin2hex(random_bytes(24)),
            'UPDATE_WEBHOOK_SECRET' => bin2hex(random_bytes(20)),
        ];
        $envText = (string) file_get_contents(SAMS_ROOT . '/.env.example');
        foreach ($values as $k => $v) {
            $envText = preg_replace('/^' . $k . '=.*$/m', $k . '=' . str_replace('$', '\$', $v), $envText);
        }
        if (@file_put_contents(SAMS_ROOT . '/.env', $envText) !== false) {
            $envText = '';
            @chmod(SAMS_ROOT . '/.env', 0640);
        }
        // Für diesen Request direkt verwenden
        $GLOBALS['sams_config'] = array_replace_recursive(build_config(), [
            'base_url' => $values['APP_URL'],
            'db' => [
                'driver' => $values['DB_DRIVER'], 'host' => $in['host'], 'port' => (int) $in['port'],
                'database' => $in['database'], 'username' => $in['username'], 'password' => $in['password'],
                'sqlite' => SAMS_ROOT . '/storage/sams.sqlite',
            ],
        ]);
    }

    if (!$errors) {
        try {
            run_migrations();
            if ((int) scalar('SELECT COUNT(*) FROM users') === 0) {
                insert('users', [
                    'username' => $in['admin_user'],
                    'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
                    'name' => $in['admin_name'],
                    'title' => '',
                    'role' => 'admin',
                    'rank_id' => scalar('SELECT id FROM ranks ORDER BY level DESC LIMIT 1'),
                    'is_superadmin' => 1,
                    'created_at' => now(),
                    'password_changed_at' => now(),
                ]);
            }
            save_setting('org_name', $in['org_name']);
            $done = true;
        } catch (Throwable $e) {
            $errors[] = 'Datenbankfehler: ' . $e->getMessage();
        }
    }
}
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Installation · SAMS</title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= e(SAMS_VERSION) ?>">
</head>
<body class="no-nav page-install">
<main class="main">
<div class="card narrow">
    <div class="verify-head"><img src="assets/img/logo.svg" alt=""><div><h1>Installation</h1><p class="muted">SAMS Dokumenten- &amp; Aktensystem <?= e(version_label()) ?></p></div></div>

<?php if ($installed): ?>
    <div class="flash success">Das System ist bereits eingerichtet.</div>
    <p><a class="btn primary" href="index.php">Zur Anmeldung</a></p>
<?php elseif ($done): ?>
    <div class="flash success">Einrichtung abgeschlossen.</div>
    <?php if ($envText !== ''): ?>
        <div class="flash warn">Die Datei <code>.env</code> konnte nicht geschrieben werden. Lege sie im Projektordner (eine Ebene über <code>public/</code>) mit diesem Inhalt an:</div>
        <textarea rows="16" readonly class="mono" style="width:100%"><?= e($envText) ?></textarea>
    <?php endif; ?>
    <p><a class="btn primary" href="index.php">Zur Anmeldung</a></p>
<?php else: ?>
    <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <?php if (!$hasEnv): ?>
            <h3 class="form-heading">Allgemein</h3>
            <div class="field w-full"><label>Öffentliche Adresse (für QR-Codes)</label><input name="app_url" value="<?= e($in['app_url']) ?>" required></div>
            <h3 class="form-heading">Datenbank</h3>
            <div class="field w-full"><div class="choices">
                <label class="choice"><input type="radio" name="driver" value="mysql"<?= checked($in['driver'] !== 'sqlite') ?>> MySQL / MariaDB (empfohlen)</label>
                <label class="choice"><input type="radio" name="driver" value="sqlite"<?= checked($in['driver'] === 'sqlite') ?>> SQLite (nur zum Testen)</label>
            </div></div>
            <div class="field w-half"><label>Host</label><input name="host" value="<?= e($in['host']) ?>"></div>
            <div class="field w-half"><label>Port</label><input name="port" value="<?= e($in['port']) ?>"></div>
            <div class="field w-third"><label>Datenbank</label><input name="database" value="<?= e($in['database']) ?>"></div>
            <div class="field w-third"><label>Benutzer</label><input name="username" value="<?= e($in['username']) ?>"></div>
            <div class="field w-third"><label>Passwort</label><input type="password" name="password" value=""></div>
        <?php else: ?>
            <div class="flash success w-full">Konfiguration (<code>.env</code>) gefunden. Es fehlt nur noch das Admin-Konto.</div>
        <?php endif; ?>
        <h3 class="form-heading">Einrichtung</h3>
        <div class="field w-full"><label>Name der Einrichtung</label><input name="org_name" value="<?= e($in['org_name']) ?>" required></div>
        <h3 class="form-heading">Admin-Konto</h3>
        <div class="field w-half"><label>Dein Name</label><input name="admin_name" value="<?= e($in['admin_name']) ?>" required placeholder="Vorname Nachname"></div>
        <div class="field w-half"><label>Benutzername</label><input name="admin_user" value="<?= e($in['admin_user']) ?>" required></div>
        <div class="field w-half"><label>Passwort (min. 10 Zeichen)</label><input type="password" name="admin_pass" minlength="10" required></div>
        <div class="form-actions"><button class="btn primary">Einrichten</button></div>
    </form>
<?php endif; ?>
</div>
</main>
</body>
</html>
