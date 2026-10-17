<?php
/**
 * Automatische Tests: startet das System mit einer frischen SQLite-Datenbank
 * auf einem eigenen Port und spielt die Abläufe aus tests/cases/*.php durch.
 *
 *   php tests/run.php            alle Tests
 *   php tests/run.php documents  nur Tests, deren Dateiname „documents“ enthält
 *
 * Die echte .env und Datenbank werden nicht angefasst.
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$tmp = sys_get_temp_dir() . '/sams-test-' . getmypid();
@mkdir($tmp, 0777, true);
$port = 18000 + getmypid() % 1000;
define('TEST_BASE', "http://127.0.0.1:$port");
define('TEST_DB', "$tmp/test.sqlite");
define('TEST_ROOT_STORAGE', "$tmp/storage");
$env = [
    'DB_DRIVER' => 'sqlite', 'DB_SQLITE_PATH' => TEST_DB, 'APP_URL' => TEST_BASE, 'APP_DEBUG' => 'true', 'APP_TIMEZONE' => 'Europe/Berlin',
    'API_KEY' => 'testkey', 'SESSION_SAMESITE' => 'Lax', 'PORTAL_DEV_LOGIN' => 'true', 'DISCORD_CLIENT_ID' => '', 'DISCORD_CLIENT_SECRET' => '',
    'DISCORD_BOT_TOKEN' => '', 'DISCORD_GUILD_ID' => '', 'UPDATE_WEBHOOK_SECRET' => 'testsecret', 'STORAGE_PATH' => "$tmp/storage",
];
foreach ($env as $k => $v) {
    putenv("$k=$v");
}
date_default_timezone_set('Europe/Berlin');
require __DIR__ . '/lib.php';
$GLOBALS['test_count'] = 0;
$GLOBALS['test_failed'] = [];

$php = escapeshellarg(PHP_BINARY);
passthru("$php " . escapeshellarg("$root/bin/sams") . ' migrate > ' . escapeshellarg("$tmp/migrate.log") . ' 2>&1', $rc);
if ($rc !== 0) {
    fwrite(STDERR, "Migration fehlgeschlagen:\n" . file_get_contents("$tmp/migrate.log"));
    exit(1);
}
passthru("$php " . escapeshellarg(__DIR__ . '/seed.php'), $rc);
if ($rc !== 0) {
    exit(1);
}

$server = proc_open([PHP_BINARY, '-S', "127.0.0.1:$port", '-t', "$root/public"], [1 => ['file', "$tmp/server.log", 'a'], 2 => ['file', "$tmp/server.log", 'a']], $pipes);
for ($i = 0; $i < 50 && !@file_get_contents(TEST_BASE . '/health.php'); $i++) {
    usleep(100000);
}

$filter = $argv[1] ?? '';
$started = microtime(true);
foreach (glob(__DIR__ . '/cases/*.php') as $file) {
    $name = basename($file, '.php');
    if ($filter !== '' && !str_contains($name, $filter)) {
        continue;
    }
    $GLOBALS['test_current'] = $name;
    echo "\n\033[1m$name\033[0m\n";
    try {
        require $file;
    } catch (Throwable $e) {
        check(false, 'Abbruch: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')');
    }
}

proc_terminate($server);
$log = (string) @file_get_contents("$tmp/server.log");
// PHP-Warnungen und Fehler im Serverlog gelten als Fehlschlag
if (preg_match_all('/PHP (Fatal error|Warning|Notice|Deprecated|Parse error):[^\n]+/', $log, $m)) {
    foreach (array_unique($m[0]) as $line) {
        check(false, 'Serverlog: ' . trim($line));
    }
}
$failed = $GLOBALS['test_failed'];
printf("\n%d Prüfungen, %d fehlgeschlagen (%.1f s)\n", $GLOBALS['test_count'], count($failed), microtime(true) - $started);
foreach ($failed as $f) {
    echo "  ✗ $f\n";
}
if (!$failed) {
    exec('rm -rf ' . escapeshellarg($tmp));
}
exit($failed ? 1 : 0);
