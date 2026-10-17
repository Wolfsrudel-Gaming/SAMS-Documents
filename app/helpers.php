<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

function e($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Mehrzeiligen Text sicher mit Zeilenumbrüchen ausgeben. */
function nl($value): string
{
    return nl2br(e($value), false);
}

function url(string $page, array $params = []): string
{
    $params = ['p' => $page] + $params;
    return 'index.php?' . http_build_query($params);
}

function redirect(string $location): void
{
    header('Location: ' . $location);
    exit;
}

function base_url(): string
{
    $configured = rtrim((string) cfg('base_url', ''), '/');
    if ($configured !== '' && $configured !== 'https://sams.example.com') {
        return $configured;
    }
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $dir;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Ungültiges Formular-Token. Bitte Seite neu laden.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, $default = '')
{
    $v = $_POST[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

function flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

function now(): string
{
    return date('Y-m-d H:i:s');
}

function fmt_date($value, string $format = 'd.m.Y'): string
{
    if ($value === null || $value === '') {
        return '';
    }
    $ts = strtotime((string) $value);
    if ($ts === false) {
        return (string) $value;
    }
    return strtr(date($format, $ts), [
        'Monday' => 'Montag', 'Tuesday' => 'Dienstag', 'Wednesday' => 'Mittwoch', 'Thursday' => 'Donnerstag',
        'Friday' => 'Freitag', 'Saturday' => 'Samstag', 'Sunday' => 'Sonntag',
        'January' => 'Januar', 'February' => 'Februar', 'March' => 'März', 'May' => 'Mai', 'June' => 'Juni',
        'July' => 'Juli', 'October' => 'Oktober', 'December' => 'Dezember',
    ]);
}

function fmt_datetime($value): string
{
    return fmt_date($value, 'd.m.Y H:i');
}

function age($dob): string
{
    if (!$dob) {
        return '';
    }
    try {
        return (string) (new DateTime((string) $dob))->diff(new DateTime())->y;
    } catch (Exception $e) {
        return '';
    }
}

function money($value): string
{
    $symbol = setting('currency', '$');
    return $symbol . ' ' . number_format((float) $value, 2, ',', '.');
}

function random_code(int $length = 8): string
{
    // Ohne leicht verwechselbare Zeichen (0/O, 1/I/L)
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $out = '';
    for ($i = 0; $i < $length; $i++) {
        $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $out;
}

function patient_name(?array $p, bool $lastFirst = false): string
{
    if (!$p) {
        return '';
    }
    return $lastFirst
        ? trim(($p['last_name'] ?? '') . ', ' . ($p['first_name'] ?? ''), ', ')
        : trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
}

function user_display(?array $u): string
{
    if (!$u) {
        return '';
    }
    return trim(($u['title'] ?? '') . ' ' . ($u['name'] ?? ''));
}

function initials_name(?array $p): string
{
    if (!$p) {
        return '–';
    }
    $f = mb_substr((string) ($p['first_name'] ?? ''), 0, 1);
    $l = mb_substr((string) ($p['last_name'] ?? ''), 0, 1);
    return $f . '. ' . $l . '.';
}

function checked($cond): string
{
    return $cond ? ' checked' : '';
}

function selected($a, $b): string
{
    return (string) $a === (string) $b ? ' selected' : '';
}

/** Ankreuzfeld wie auf einem gedruckten Formular. */
function box($cond): string
{
    return '<span class="xbox">' . ($cond ? '✕' : '&nbsp;') . '</span>';
}

function render(string $page, array $vars = []): void
{
    extract($vars, EXTR_SKIP);
    $pageFile = SAMS_ROOT . '/app/pages/' . $page . '.php';
    ob_start();
    require $pageFile;
    $content = ob_get_clean();
    require SAMS_ROOT . '/app/layout.php';
}

function not_found(string $msg = 'Nicht gefunden.'): void
{
    http_response_code(404);
    render('error', ['message' => $msg, 'title' => 'Fehler']);
    exit;
}

function forbidden(string $msg = 'Keine Berechtigung.'): void
{
    http_response_code(403);
    render('error', ['message' => $msg, 'title' => 'Zugriff verweigert']);
    exit;
}
