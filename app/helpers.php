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

/** Hex-Farbe prüfen, sonst Standard verwenden. */
function hex_color(string $value, string $default): string
{
    return preg_match('/^#[0-9a-f]{6}$/i', $value) ? strtolower($value) : $default;
}

/** Farbe aufhellen (> 0) oder abdunkeln (< 0), $amount von -1 bis 1. */
function shade(string $hex, float $amount): string
{
    $rgb = array_map('hexdec', str_split(ltrim($hex, '#'), 2));
    foreach ($rgb as &$c) {
        $c = (int) round($amount < 0 ? $c * (1 + $amount) : $c + (255 - $c) * $amount);
    }
    return sprintf('#%02x%02x%02x', ...$rgb);
}

/** Farbschema aus den Einstellungen (Admin → Einstellungen → Design). */
function theme_style(): string
{
    $primary = hex_color((string) setting('theme_primary', ''), '#0b4f9c');
    $accent = hex_color((string) setting('theme_accent', ''), '#c8102e');
    $nav = hex_color((string) setting('theme_nav', ''), '#1c2b3b');
    return '<style>:root{'
        . '--primary:' . $primary . ';--primary-dark:' . shade($primary, -0.25) . ';--primary-soft:' . shade($primary, 0.9) . ';'
        . '--accent:' . $accent . ';--accent-soft:' . shade($accent, 0.88) . ';'
        . '--nav:' . $nav . ';--nav-dark:' . shade($nav, -0.3) . ';--nav-hover:' . shade($nav, 0.08) . ';'
        . '}</style>';
}

/**
 * Einfache Textauszeichnung für Lerninhalte und Wiki (sicher escaped):
 * # Überschrift, ## Unterüberschrift, - Aufzählung, 1. Nummerierung,
 * **fett**, *kursiv*, [Text](https://…) und Leerzeile = neuer Absatz.
 */
function light_markup(string $text): string
{
    $inline = function (string $s): string {
        $s = e($s);
        $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/', '<em>$1</em>', $s);
        $s = preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+|index\.php\?[^\s)]+)\)/', fn($m) => '<a href="' . $m[2] . '"' . (str_starts_with($m[2], 'http') ? ' target="_blank" rel="noopener"' : '') . '>' . $m[1] . '</a>', $s);
        return $s;
    };
    $out = '';
    $list = null;
    $para = [];
    $flush = function () use (&$para, &$out, $inline) {
        if ($para) {
            $out .= '<p>' . implode('<br>', array_map($inline, $para)) . '</p>';
            $para = [];
        }
    };
    $closeList = function () use (&$list, &$out) {
        if ($list) {
            $out .= "</$list>";
            $list = null;
        }
    };
    foreach (preg_split('/\r?\n/', $text) as $line) {
        $t = rtrim($line);
        if (preg_match('/^(#{1,3})\s+(.+)$/', $t, $m)) {
            $flush();
            $closeList();
            $lvl = strlen($m[1]) + 1;
            $out .= "<h$lvl>" . $inline($m[2]) . "</h$lvl>";
        } elseif (preg_match('/^\s*[-•]\s+(.+)$/', $t, $m) || preg_match('/^\s*\d+[.)]\s+(.+)$/', $t, $m)) {
            $flush();
            $type = preg_match('/^\s*\d/', $t) ? 'ol' : 'ul';
            if ($list !== $type) {
                $closeList();
                $out .= "<$type>";
                $list = $type;
            }
            $out .= '<li>' . $inline($m[1]) . '</li>';
        } elseif (trim($t) === '') {
            $flush();
            $closeList();
        } else {
            $closeList();
            $para[] = $t;
        }
    }
    $flush();
    $closeList();
    return $out;
}
