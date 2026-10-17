<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Zwei-Faktor-Anmeldung per Authenticator-App (TOTP nach RFC 6238,
 * kompatibel mit Google Authenticator, Microsoft Authenticator, Authy …).
 */

function base32_encode(string $data): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($data) as $c) {
        $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 5) as $chunk) {
        $out .= $alphabet[bindec(str_pad($chunk, 5, '0'))];
    }
    return $out;
}

function base32_decode(string $b32): string
{
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $b32 = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $b32));
    $bits = '';
    foreach (str_split($b32) as $c) {
        $bits .= str_pad(decbin(strpos($alphabet, $c)), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 8) as $byte) {
        if (strlen($byte) === 8) {
            $out .= chr(bindec($byte));
        }
    }
    return $out;
}

function totp_new_secret(): string
{
    return base32_encode(random_bytes(20));
}

function totp_code(string $secret, int $step): string
{
    $hash = hash_hmac('sha1', pack('J', $step), base32_decode($secret), true);
    $offset = ord($hash[19]) & 0x0f;
    $value = ((ord($hash[$offset]) & 0x7f) << 24) | (ord($hash[$offset + 1]) << 16) | (ord($hash[$offset + 2]) << 8) | ord($hash[$offset + 3]);
    return str_pad((string) ($value % 1000000), 6, '0', STR_PAD_LEFT);
}

/**
 * Code prüfen (±30 Sekunden Toleranz). Gibt den verwendeten Zeitschritt zurück
 * oder null. $lastStep verhindert, dass ein Code zweimal benutzt wird.
 */
function totp_verify(string $secret, string $code, int $lastStep = 0): ?int
{
    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6 || $secret === '') {
        return null;
    }
    $now = intdiv(time(), 30);
    foreach ([0, -1, 1] as $d) {
        $step = $now + $d;
        if ($step > $lastStep && hash_equals(totp_code($secret, $step), $code)) {
            return $step;
        }
    }
    return null;
}

function totp_uri(string $secret, string $account): string
{
    $issuer = setting('org_short', 'SAMS');
    return 'otpauth://totp/' . rawurlencode($issuer . ':' . $account) . '?secret=' . $secret
        . '&issuer=' . rawurlencode($issuer) . '&digits=6&period=30';
}

/** Wiederherstellungscodes: 8 Stück, einmal anzeigen, nur als Hash speichern. */
function recovery_codes_generate(): array
{
    $plain = [];
    for ($i = 0; $i < 8; $i++) {
        $plain[] = strtolower(random_code(4) . '-' . random_code(4));
    }
    return $plain;
}

function recovery_codes_hash(array $plain): string
{
    return json_encode(array_map(fn($c) => password_hash($c, PASSWORD_DEFAULT), $plain));
}

/** Prüft einen Wiederherstellungscode und entwertet ihn. Gibt die neue Liste oder null zurück. */
function recovery_code_use(string $stored, string $code): ?string
{
    $hashes = json_decode($stored ?: '[]', true) ?: [];
    $code = strtolower(trim($code));
    foreach ($hashes as $i => $hash) {
        if (password_verify($code, $hash)) {
            unset($hashes[$i]);
            return json_encode(array_values($hashes));
        }
    }
    return null;
}
