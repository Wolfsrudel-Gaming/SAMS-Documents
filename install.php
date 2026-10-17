<?php
/**
 * Einmalige Installation: Konfiguration schreiben, Tabellen anlegen,
 * erstes Leitungs-Konto erstellen. Nach der Installation gesperrt.
 */
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
start_session();

$installed = false;
if (is_file(__DIR__ . '/config.php')) {
    try {
        $installed = (int) scalar('SELECT COUNT(*) FROM users') > 0;
    } catch (Throwable $e) {
        $installed = false;
    }
}

$errors = [];
$done = false;
$configText = '';
$in = [
    'base_url' => rtrim(base_url(), '/'),
    'driver' => 'sqlite',
    'host' => '127.0.0.1', 'port' => '3306', 'database' => 'sams', 'username' => 'sams', 'password' => '',
    'admin_user' => 'leitung', 'admin_name' => '', 'admin_title' => 'Dr. med.', 'org_name' => 'San Andreas Medical Services',
];

if (!$installed && is_post()) {
    csrf_check();
    foreach ($in as $k => $v) {
        $in[$k] = trim((string) ($_POST[$k] ?? $v));
    }
    $pw = (string) ($_POST['admin_pass'] ?? '');
    if (!preg_match('/^[A-Za-z0-9._\-]{3,40}$/', $in['admin_user'])) {
        $errors[] = 'Benutzername ungültig.';
    }
    if (strlen($pw) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen haben.';
    }
    if ($in['admin_name'] === '') {
        $errors[] = 'Bitte Ihren Namen angeben.';
    }
    $dbToken = bin2hex(random_bytes(8));
    $config = [
        'base_url' => $in['base_url'],
        'db' => [
            'driver' => $in['driver'] === 'mysql' ? 'mysql' : 'sqlite',
            // Zufälliger Dateiname: schützt die Datenbank auch auf Servern ohne .htaccess (z.B. nginx)
            'sqlite' => '__DIR__/data/sams-' . $dbToken . '.sqlite',
            'host' => $in['host'], 'port' => (int) $in['port'], 'database' => $in['database'],
            'username' => $in['username'], 'password' => $in['password'], 'charset' => 'utf8mb4',
        ],
        'cookie_samesite' => 'Lax',
        'api_key' => bin2hex(random_bytes(16)),
        'timezone' => 'Europe/Berlin',
    ];
    if (!$errors) {
        $GLOBALS['sams_config'] = $config;
        $GLOBALS['sams_config']['db']['sqlite'] = __DIR__ . '/data/sams-' . $dbToken . '.sqlite';
        try {
            if (!is_dir(__DIR__ . '/data')) {
                mkdir(__DIR__ . '/data', 0750, true);
            }
            install_schema();
            if ((int) scalar('SELECT COUNT(*) FROM users') === 0) {
                insert('users', [
                    'username' => $in['admin_user'],
                    'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
                    'name' => $in['admin_name'],
                    'title' => $in['admin_title'],
                    'role' => 'admin',
                    'rank_name' => 'Ärztliche Direktion',
                    'department' => 'Ärztliche Direktion',
                    'created_at' => now(),
                ]);
            }
            save_setting('org_name', $in['org_name']);
        } catch (Throwable $e) {
            $errors[] = 'Datenbankfehler: ' . $e->getMessage();
        }
    }
    if (!$errors) {
        $export = var_export($config, true);
        $export = str_replace("'__DIR__/data/", "__DIR__ . '/data/", $export);
        $configText = "<?php\n// Erzeugt durch install.php am " . date('d.m.Y H:i') . "\nreturn " . $export . ";\n";
        if (!is_file(__DIR__ . '/config.php') && @file_put_contents(__DIR__ . '/config.php', $configText) !== false) {
            $configText = '';
        }
        $done = true;
    }
}
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Installation · SAMS Dokumentensystem</title>
<link rel="stylesheet" href="assets/css/app.css?v=<?= SAMS_VERSION ?>">
</head>
<body class="no-nav">
<main class="main">
<div class="card narrow">
    <div class="verify-head"><img src="assets/img/logo.svg" alt=""><div><h1>Installation</h1><p class="muted">SAMS Dokumenten- &amp; Aktensystem <?= SAMS_VERSION ?></p></div></div>

<?php if ($installed): ?>
    <div class="flash success">Das System ist bereits installiert.</div>
    <p><a class="btn primary" href="index.php">Zur Anmeldung</a></p>
<?php elseif ($done): ?>
    <div class="flash success">Installation abgeschlossen.</div>
    <?php if ($configText !== ''): ?>
        <div class="flash warn">config.php konnte nicht automatisch geschrieben werden. Bitte legen Sie die Datei <code>config.php</code> mit folgendem Inhalt im Hauptverzeichnis an:</div>
        <textarea rows="16" readonly class="mono" style="width:100%"><?= e($configText) ?></textarea>
    <?php endif; ?>
    <p>Löschen Sie aus Sicherheitsgründen anschließend die Datei <code>install.php</code> vom Server.</p>
    <p><a class="btn primary" href="index.php">Zur Anmeldung</a></p>
<?php else: ?>
    <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
    <?php if (!is_writable(__DIR__ . '/data') && !is_writable(__DIR__)): ?><div class="flash warn">Das Verzeichnis <code>data/</code> ist nicht beschreibbar (für SQLite erforderlich).</div><?php endif; ?>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <h3 class="form-heading">Allgemein</h3>
        <div class="field w-full"><label>Öffentliche Adresse (für QR-Codes)</label><input name="base_url" value="<?= e($in['base_url']) ?>" required></div>
        <div class="field w-full"><label>Name der Einrichtung</label><input name="org_name" value="<?= e($in['org_name']) ?>" required></div>
        <h3 class="form-heading">Datenbank</h3>
        <div class="field w-full"><div class="choices">
            <label class="choice"><input type="radio" name="driver" value="sqlite"<?= checked($in['driver'] !== 'mysql') ?>> SQLite (empfohlen, keine Einrichtung nötig)</label>
            <label class="choice"><input type="radio" name="driver" value="mysql"<?= checked($in['driver'] === 'mysql') ?>> MySQL / MariaDB</label>
        </div></div>
        <div class="field w-half"><label>MySQL-Host</label><input name="host" value="<?= e($in['host']) ?>"></div>
        <div class="field w-half"><label>Port</label><input name="port" value="<?= e($in['port']) ?>"></div>
        <div class="field w-third"><label>Datenbank</label><input name="database" value="<?= e($in['database']) ?>"></div>
        <div class="field w-third"><label>Benutzer</label><input name="username" value="<?= e($in['username']) ?>"></div>
        <div class="field w-third"><label>Passwort</label><input type="password" name="password" value=""></div>
        <h3 class="form-heading">Erstes Leitungs-Konto</h3>
        <div class="field w-third"><label>Titel</label><input name="admin_title" value="<?= e($in['admin_title']) ?>"></div>
        <div class="field w-third"><label>Name</label><input name="admin_name" value="<?= e($in['admin_name']) ?>" required placeholder="Vorname Nachname"></div>
        <div class="field w-third"><label>Benutzername</label><input name="admin_user" value="<?= e($in['admin_user']) ?>" required></div>
        <div class="field w-half"><label>Passwort (min. 8 Zeichen)</label><input type="password" name="admin_pass" minlength="8" required></div>
        <div class="form-actions"><button class="btn primary">Installieren</button></div>
    </form>
<?php endif; ?>
</div>
</main>
</body>
</html>
