<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Dokumentenfälschung im Spiel. Kriminelle basteln an geheimen Fälscherwerkstätten (Rechner + Drucker) Dokumente
 * zusammen, die echt aussehen. Ihr Prüfcode steht nicht in documents – die Echtheitsprüfung schlägt deshalb fehl.
 * Hürden (alle unter Administration → Fälschungen einstellbar): Verbrauchsmaterial, echtes Original als Vorlage,
 * Geschicklichkeitsprüfungen (bestimmen die Qualität), Arbeitszeit, Abklingzeit, Alarm-Risiko. Je schlechter die
 * Qualität, desto mehr sichtbare Mängel (fehlendes Sicherheitsmerkmal, unscharfer Stempel, falsche Farben/Schrift).
 */

const FORGERY_DEFAULTS = [
    'forgery_enabled' => '0', 'forgery_types' => 'attest, au, tauglichkeit, gov_genehmigung', 'forgery_materials' => 'forgery_paper:1, forgery_ink:1',
    'forgery_original' => '1', 'forgery_duration' => '60', 'forgery_cooldown' => '30', 'forgery_checks' => '4', 'forgery_difficulty' => 'medium', 'forgery_alert' => '20',
];
const FORGERY_DIFFICULTIES = ['easy' => 'leicht', 'medium' => 'mittel', 'hard' => 'schwer'];

function forgery_setting(string $key): string
{
    return trim((string) setting($key, FORGERY_DEFAULTS[$key] ?? ''));
}

function forgery_enabled(): bool
{
    return forgery_setting('forgery_enabled') === '1';
}

/** Dokumentarten, die gefälscht werden können (Schlüssel). */
function forgery_types(): array
{
    return array_values(array_filter(array_map('trim', explode(',', forgery_setting('forgery_types')))));
}

/** Verbrauchsmaterial: [['item' => …, 'count' => …], …]. */
function forgery_materials(): array
{
    $out = [];
    foreach (explode(',', forgery_setting('forgery_materials')) as $part) {
        if (preg_match('/^\s*([a-z0-9_]+)\s*(?::\s*(\d+))?\s*$/i', $part, $m)) {
            $out[] = ['item' => $m[1], 'count' => max(1, (int) ($m[2] ?? 1))];
        }
    }
    return $out;
}

/** Einstellungen für die Resource (nur Werkstätten des anfragenden Servers). */
function forgery_config(): array
{
    if (!forgery_enabled()) {
        return ['enabled' => false];
    }
    $srv = function_exists('game_server_id') ? game_server_id() : 0;
    try {
        $sites = all('SELECT id, name, x, y, z, heading, prop_model FROM forgery_sites WHERE active = 1' . ($srv ? ' AND (server_id = ' . $srv . ' OR server_id IS NULL)' : '') . ' ORDER BY id');
    } catch (PDOException $e) {
        $sites = [];
    }
    return [
        'enabled' => true,
        'sites' => array_map(fn($s) => ['id' => (int) $s['id'], 'x' => (float) $s['x'], 'y' => (float) $s['y'], 'z' => (float) $s['z'], 'heading' => (float) $s['heading'], 'prop' => (string) $s['prop_model']], $sites),
        'materials' => forgery_materials(),
        'original' => forgery_setting('forgery_original') === '1',
        'duration' => max(10, min(600, (int) forgery_setting('forgery_duration'))),
        'cooldown' => max(0, min(1440, (int) forgery_setting('forgery_cooldown'))),
        'checks' => max(1, min(10, (int) forgery_setting('forgery_checks'))),
        'difficulty' => array_key_exists(forgery_setting('forgery_difficulty'), FORGERY_DIFFICULTIES) ? forgery_setting('forgery_difficulty') : 'medium',
        'alert' => max(0, min(100, (int) forgery_setting('forgery_alert'))),
    ];
}

/** Sichtbare Mängel je Qualität (CSS-Klasse um das Blatt). */
function forgery_flaw_class(int $quality): string
{
    return $quality >= 85 ? 'forged' : ($quality >= 60 ? 'forged q-hi' : ($quality >= 35 ? 'forged q-mid' : 'forged q-low'));
}

/**
 * Sitzung an der Werkstatt anlegen (nach Material, Vorlage und Geschicklichkeitsprüfung in der Resource).
 * $originals: Ansichts-Schlüssel der Dokument-Items im Inventar – nur ECHTE Dokumente zählen als Vorlage.
 */
function forgery_start(int $siteId, int $quality, array $originals, string $license, string $player): array
{
    if (!forgery_enabled()) {
        return ['ok' => false, 'error' => 'Hier lässt sich nichts drucken.'];
    }
    $site = one('SELECT * FROM forgery_sites WHERE id = ? AND active = 1', [$siteId]);
    if (!$site) {
        return ['ok' => false, 'error' => 'Unbekannter Rechner.'];
    }
    $allowed = forgery_types();
    if (forgery_setting('forgery_original') === '1') {
        $tokens = array_slice(array_values(array_filter(array_map('strval', $originals), fn($t) => strlen($t) >= 32)), 0, 50);
        $genuine = $tokens ? array_column(all('SELECT DISTINCT type FROM documents WHERE view_token IN (' . implode(',', array_fill(0, count($tokens), '?')) . ')', $tokens), 'type') : [];
        $allowed = array_values(array_intersect($allowed, $genuine));
        if (!$allowed) {
            return ['ok' => false, 'error' => 'Ohne echtes Original als Vorlage lässt sich nichts nachmachen.'];
        }
    }
    do {
        $code = random_code(8);
    } while (scalar('SELECT id FROM documents WHERE verify_code = ?', [$code]) || scalar('SELECT id FROM forgeries WHERE verify_code = ?', [$code]));
    $session = bin2hex(random_bytes(24));
    insert('forgeries', ['session_hash' => hash('sha256', $session), 'view_token' => bin2hex(random_bytes(20)), 'verify_code' => $code, 'quality' => max(0, min(100, $quality)),
        'allowed_types' => json_encode($allowed), 'status' => 'session', 'site_id' => $siteId, 'server_id' => (function_exists('game_server_id') && game_server_id()) ? game_server_id() : null,
        'license' => mb_substr($license, 0, 190), 'player' => mb_substr($player, 0, 190), 'created_at' => now(), 'expires_at' => date('Y-m-d H:i:s', time() + 20 * 60)]);
    return ['ok' => true, 'session' => $session, 'url' => base_url() . '/forge.php?s=' . $session, 'origin' => preg_replace('#^(https?://[^/]+).*$#', '$1', base_url())];
}

function forgery_by_session(string $session, bool $open = true): ?array
{
    if (strlen($session) < 32) {
        return null;
    }
    $f = one('SELECT * FROM forgeries WHERE session_hash = ?', [hash('sha256', $session)]);
    if (!$f || ($open && ($f['status'] !== 'session' || $f['expires_at'] < now()))) {
        return null;
    }
    return $f;
}

/** Plausible Dokumentnummer im Stil der nachgemachten Behörde (gibt es so nicht). */
function forgery_number(array $type): string
{
    return doc_prefix() . '-' . ($type['code'] ?? 'DOK') . '-' . date('Y') . '-' . str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
}

/** Dokument-Array für render_document (nie mit echter ID, keine Nachträge). */
function forgery_doc(array $f): array
{
    return ['id' => 0, 'type' => $f['type'], 'faction_id' => (int) $f['faction_id'], 'number' => $f['number'], 'verify_code' => $f['verify_code'], 'view_token' => $f['view_token'],
        'data' => json_decode((string) $f['data'], true) ?: [], 'patient_snapshot' => json_decode((string) $f['patient_snapshot'], true) ?: [],
        'issuer_snapshot' => json_decode((string) $f['issuer_snapshot'], true) ?: [], 'issued_at' => $f['done_at'] ?: $f['created_at'], 'status' => 'valid', 'valid_until' => '',
        'finalized_at' => null, 'final_hash' => '', '_forged' => (int) $f['quality']];
}

/** Gefälschtes Dokument über den Ansichts-Schlüssel (für doc.php und das Item). */
function forgery_by_view_token(string $token): ?array
{
    try {
        return strlen($token) >= 32 ? one("SELECT * FROM forgeries WHERE view_token = ? AND status = 'done'", [$token]) : null;
    } catch (PDOException $e) {
        return null;
    }
}

/** Item-Metadaten wie bei einem echten Ausdruck. */
function forgery_item_metadata(array $f): array
{
    set_faction_context((int) $f['faction_id']);
    $t = doc_type($f['type']);
    $p = json_decode((string) $f['patient_snapshot'], true) ?: [];
    $who = $p ? trim(mb_substr((string) ($p['first_name'] ?? ''), 0, 1) . '. ' . ($p['last_name'] ?? '')) : '';
    return ['label' => ($t['short'] ?? 'Dokument') . ($who !== '' ? ' – ' . $who : ''), 'description' => $f['number'] . ' · ' . fmt_date($f['done_at']) . ' · ' . org('org_short'),
        'sams_token' => $f['view_token'], 'sams_variant' => '', 'sams_number' => $f['number'], 'sams_type' => $f['type']];
}

/** Echtheitsprüfung eines unbekannten Codes: still mitzählen, ob es eine bekannte Fälschung ist (nur für die Administration). */
function forgery_note_check(string $code): void
{
    try {
        q('UPDATE forgeries SET checked_count = checked_count + 1, last_checked_at = ? WHERE verify_code = ?', [now(), $code]);
    } catch (PDOException $e) {
        // vor der Migration
    }
}

/** Anonymer Hinweis an die Polizei (Alarm-Risiko an der Werkstatt). */
function forgery_alert(string $street): int
{
    $n = 0;
    foreach (factions() as $f) {
        if (!in_array($f['kind'] ?? '', ['police'], true)) {
            continue;
        }
        $prev = fid();
        set_faction_context((int) $f['id']);
        notify_users_with('wanted.view', 'wanted', 'Anonymer Hinweis: verdächtige Druckaktivität' . ($street !== '' ? ' in der Nähe von ' . mb_substr($street, 0, 80) : ''), url('wanted'),
            function ($u) use ($f, &$n) {
                $ok = (int) $u['faction_id'] === (int) $f['id'];
                $n += $ok ? 1 : 0;
                return $ok;
            });
        set_faction_context($prev);
    }
    return $n;
}
