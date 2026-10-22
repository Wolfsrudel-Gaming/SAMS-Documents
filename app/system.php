<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Systemfunktionen: Protokolldateien, Fehlerbehandlung, Wartungsmodus,
 * Versionsinfo und Auto-Update-Status.
 */

function app_log(string $level, string $message): void
{
    $line = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
    @file_put_contents(storage_path('logs/app-' . date('Y-m-d') . '.log'), $line, FILE_APPEND | LOCK_EX);
}

function handle_exception(Throwable $e): void
{
    $id = substr(bin2hex(random_bytes(4)), 0, 8);
    app_log('error', "[$id] " . get_class($e) . ': ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
    if (SAMS_CLI) {
        fwrite(STDERR, 'Fehler: ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
    if (!headers_sent()) {
        http_response_code(500);
    }
    $debug = cfg('debug') ? '<pre style="white-space:pre-wrap">' . htmlspecialchars((string) $e) . '</pre>' : '';
    echo '<!doctype html><meta charset="utf-8"><title>Fehler</title>'
        . '<body style="font-family:system-ui,sans-serif;max-width:640px;margin:4rem auto;padding:0 1rem">'
        . '<h1>Es ist ein Fehler aufgetreten</h1><p>Bitte versuche es erneut. Bleibt der Fehler bestehen, melde der Leitung den Fehlercode '
        . '<code>' . $id . '</code>.</p>' . $debug . '</body>';
}

function in_maintenance(): bool
{
    return is_file(storage_path('maintenance'));
}

function set_maintenance(bool $on, string $reason = ''): void
{
    $file = storage_path('maintenance');
    if ($on) {
        file_put_contents($file, $reason !== '' ? $reason : date('c'));
    } elseif (is_file($file)) {
        unlink($file);
    }
}

/** Aktueller Git-Stand ohne Aufruf von git (liest .git direkt). */
function git_version(): array
{
    $git = SAMS_ROOT . '/.git';
    $info = ['commit' => '', 'branch' => '', 'date' => ''];
    $head = @file_get_contents($git . '/HEAD');
    if ($head === false) {
        return $info;
    }
    $head = trim($head);
    if (str_starts_with($head, 'ref: ')) {
        $ref = substr($head, 5);
        $info['branch'] = preg_replace('#^refs/heads/#', '', $ref);
        $sha = @file_get_contents($git . '/' . $ref);
        if ($sha === false && is_file($git . '/packed-refs')) {
            foreach (file($git . '/packed-refs') as $line) {
                if (str_ends_with(trim($line), ' ' . $ref)) {
                    $sha = strtok($line, ' ');
                }
            }
        }
        $info['commit'] = trim((string) $sha);
    } else {
        $info['commit'] = $head;
    }
    $refFile = $info['branch'] !== '' ? $git . '/refs/heads/' . $info['branch'] : $git . '/HEAD';
    $mtime = @filemtime(is_file($refFile) ? $refFile : $git . '/HEAD');
    $info['date'] = $mtime ? date('Y-m-d H:i:s', $mtime) : '';
    return $info;
}

function version_label(): string
{
    $g = git_version();
    return PRODUCT_NAME . ' v' . SAMS_VERSION . ($g['commit'] !== '' ? ' (' . substr($g['commit'], 0, 7) . ')' : '');
}

/** Ergebnis des letzten Auto-Updates (geschrieben von bin/update.sh). */
function update_status(): array
{
    $file = storage_path('update/last.json');
    $data = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
    return is_array($data) ? $data : [];
}

function update_last_check(): ?string
{
    $file = storage_path('update/last-check');
    return is_file($file) ? date('Y-m-d H:i:s', (int) filemtime($file)) : null;
}

/** Sofortige Update-Prüfung anfordern (wird vom Update-Timer beim nächsten Lauf abgearbeitet). */
function request_update(string $source): void
{
    file_put_contents(storage_path('update/request'), $source . ' ' . date('c'));
}

function update_log_tail(int $lines = 60): string
{
    $file = storage_path('logs/update.log');
    if (!is_file($file)) {
        return '';
    }
    $all = file($file, FILE_IGNORE_NEW_LINES);
    return implode("\n", array_slice($all, -$lines));
}
