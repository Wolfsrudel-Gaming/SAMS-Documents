<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Gesetzbücher: Import aus PDF (Text wird im Browser mit pdf.js gelesen) oder eingefügtem Text.
 * Der Parser erkennt Bücher („Strafgesetzbuch (StGB)“), Paragraphen („§1 Titel“), Absätze, Aufzählungen
 * und Strafmaße („Strafmaß: bis 20 HE und 10.000 $“). Gesetzbücher gelten für einen Staat (Spielserver) oder für alle.
 */

const LAW_PENALTY_PREFIX = '/^(Strafmaß|Strafrahmen|Strafe|Bußgeld|Geldbuße|Sanktion)\s*:\s*(.+)$/iu';
const LAW_SECTION_RE = '/^(?:§+|Art(?:ikel|\.)?)\s*(\d+[a-zA-Z]?)\.?(?:\s+|$)(.*)$/u';

/** Strafmaß-Text auswerten: [Haft-Einheiten max., Geldstrafe max. (ganze $), 'and'|'or'|'']. */
function law_parse_penalty(string $text): array
{
    $jail = 0;
    if (preg_match_all('/(\d+)\s*(?:HE|Hafteinheiten|Haft-?Einheiten|Monate|Jahre)\b/iu', $text, $m)) {
        $jail = max(array_map('intval', $m[1]));
    }
    $fine = 0;
    if (preg_match_all('/(\d{1,3}(?:[.\s]\d{3})+|\d+)(?:,\d+)?\s*(?:\$|€|USD|Dollar|EUR)/iu', $text, $m)) {
        $fine = max(array_map(fn($v) => (int) preg_replace('/\D/', '', $v), $m[1]));
    } elseif (preg_match_all('/(?:\$|€)\s*(\d{1,3}(?:[.,]\d{3})+|\d+)/u', $text, $m)) {
        $fine = max(array_map(fn($v) => (int) preg_replace('/\D/', '', $v), $m[1]));
    }
    $mode = ($jail && $fine) ? (preg_match('/\boder\b/iu', $text) ? 'or' : 'and') : '';
    return [$jail, $fine, $mode];
}

/** Lesbares Strafmaß aus den Zahlen (für Anzeige und Katalog). */
function law_penalty_label(array $s): string
{
    $jail = (int) $s['jail_max'];
    $parts = array_filter([$jail ? 'bis ' . $jail . ' HE' : '', (int) $s['fine_max'] ? ($jail ? '' : 'bis ') . number_format((int) $s['fine_max'], 0, ',', '.') . ' $' : '']);
    return implode($s['penalty_mode'] === 'or' ? ' oder ' : ' und ', $parts);
}

/** Kürzel aus einer Überschrift: „Strafgesetzbuch (StGB)“ → StGB, sonst Anfangsbuchstaben. */
function law_book_short(string $title): array
{
    if (preg_match('/^(.*?)\s*\(([^()]{1,16})\)\s*$/u', $title, $m)) {
        return [trim($m[1]), trim($m[2])];
    }
    $letters = '';
    foreach (preg_split('/[\s\-]+/u', $title) as $w) {
        if ($w !== '' && mb_strtoupper(mb_substr($w, 0, 1)) === mb_substr($w, 0, 1) && preg_match('/\p{L}/u', $w)) {
            $letters .= mb_substr($w, 0, 1);
        }
    }
    return [$title, mb_substr($letters ?: mb_substr($title, 0, 4), 0, 6)];
}

/** Ist die Zeile eine Aufzählung? Gibt den Text ohne Aufzählungszeichen zurück oder null. */
function law_bullet(string $line, bool $indented): ?string
{
    if (preg_match('/^(?:[-–•▪◦*·]|[a-z]\)|\d+\.)\s+(.+)$/u', $line, $m)) {
        return preg_match('/^[a-z]\)/u', $line) ? $line : $m[1];
    }
    return $indented ? $line : null;
}

/**
 * Text eines Gesetzbuchs zerlegen.
 * Ergebnis: ['name' => Titel, 'description' => Vorspann, 'books' => [['title','short','sections' => [['number','title','body','penalty','jail_max','fine_max','penalty_mode']]]], 'warnings' => [...]]
 */
function law_parse(string $text): array
{
    $text = str_replace(["\r\n", "\r", "\f", "\t", "\u{00A0}", "\u{200B}", "\u{FEFF}", "\u{00AD}"], ["\n", "\n", "\n", "    ", " ", "", "", ""], $text);
    $raw = explode("\n", $text);
    // Seitenzahlen und Kopf-/Fußzeilen (mehrfach wiederholte Zeilen) entfernen; die erste ist der Titel
    $count = [];
    foreach ($raw as $l) {
        $t = trim($l);
        if ($t !== '') {
            $count[$t] = ($count[$t] ?? 0) + 1;
        }
    }
    $name = '';
    $lines = [];
    foreach ($raw as $l) {
        $t = trim($l);
        if (preg_match('/^(?:(?:Page|Seite)\s+\d+\s*(?:of|von|\/)\s*\d+|-?\s*\d{1,3}\s*-?|\d+\s*\/\s*\d+)$/iu', $t)) {
            continue;
        }
        if ($t !== '' && $count[$t] >= 3 && !preg_match(LAW_SECTION_RE, $t) && !preg_match(LAW_PENALTY_PREFIX, $t) && !preg_match('/^\(\d+\)/', $t) && mb_strlen($t) > 8) {
            $name = $name ?: $t;
            continue;
        }
        $lines[] = rtrim($l);
    }
    // Markdown-Überschriften (# Buch) und Fettdruck entfernen, Einrückung merken
    $items = [];
    foreach ($lines as $l) {
        if (trim($l) === '') {
            $items[] = null;
            continue;
        }
        $indent = strlen($l) - strlen(ltrim($l, ' '));
        $t = trim(preg_replace('/\*\*(.+?)\*\*/u', '$1', trim($l)));
        $md = preg_match('/^#{1,6}\s+(.+)$/u', $t, $mm) ? trim($mm[1]) : null;
        $items[] = ['t' => $md ?? $t, 'indent' => $indent >= 3, 'md' => $md !== null];
    }
    $next = function (int $i) use ($items): ?array {
        for ($j = $i + 1, $n = count($items); $j < $n; $j++) {
            if ($items[$j] !== null) {
                return $items[$j];
            }
        }
        return null;
    };
    $books = [];
    $book = null;
    $sec = null;
    $pre = [];
    $warnings = [];
    $flushSec = function () use (&$sec, &$book) {
        if ($sec !== null) {
            $sec['body'] = trim(implode("\n", array_map('trim', $sec['body'])));
            $book['sections'][] = $sec;
            $sec = null;
        }
    };
    $flushBook = function () use (&$book, &$books, $flushSec) {
        $flushSec();
        if ($book !== null && ($book['sections'] || $book['title'] !== '')) {
            $books[] = $book;
        }
        $book = null;
    };
    $paraBreak = true;
    foreach ($items as $i => $it) {
        if ($it === null) {
            $paraBreak = true;
            continue;
        }
        $t = $it['t'];
        $isSec = preg_match(LAW_SECTION_RE, $t, $sm);
        // Überschrift eines Buchs: nicht eingerückt, kurz, ohne Satzende, danach folgt §1 (oder sie trägt ein Kürzel in Klammern)
        if (!$isSec && !$it['indent'] && ($it['md'] || (mb_strlen($t) <= 90 && !preg_match('/[.,;:]$/u', $t) && !preg_match('/^\(/', $t)))) {
            $n = $next($i);
            $nIsSec = $n && preg_match(LAW_SECTION_RE, $n['t'], $nm);
            if ($it['md'] || ($nIsSec && ((int) $nm[1] === 1 || preg_match('/\([^()]{1,16}\)\s*$/u', $t)))) {
                $flushBook();
                [$title, $short] = law_book_short($t);
                $book = ['title' => $title, 'short' => $short, 'sections' => []];
                $paraBreak = true;
                continue;
            }
        }
        if ($isSec) {
            $flushSec();
            if ($book === null) {
                $book = ['title' => $name ?: 'Gesetz', 'short' => law_book_short($name ?: 'Gesetz')[1], 'sections' => []];
            }
            $title = trim($sm[2]);
            $sec = ['number' => $sm[1], 'title' => $title, 'body' => [], 'penalty' => '', 'jail_max' => 0, 'fine_max' => 0, 'penalty_mode' => ''];
            if ($title === '' && ($n = $next($i)) && !preg_match(LAW_SECTION_RE, $n['t']) && !preg_match('/^\(\d+\)/', $n['t']) && mb_strlen($n['t']) <= 90 && !preg_match('/[.;:]$/u', $n['t'])) {
                $sec['title_from_next'] = true;
            }
            $paraBreak = true;
            continue;
        }
        if ($sec === null) {
            $pre[] = $t;
            continue;
        }
        if (!empty($sec['title_from_next'])) {
            $sec['title'] = $t;
            unset($sec['title_from_next']);
            continue;
        }
        if (preg_match(LAW_PENALTY_PREFIX, $t, $pm)) {
            $sec['penalty'] = trim($pm[2]);
            [$sec['jail_max'], $sec['fine_max'], $sec['penalty_mode']] = law_parse_penalty($sec['penalty']);
            $paraBreak = true;
            continue;
        }
        $bullet = law_bullet($t, $it['indent']);
        $last = count($sec['body']) - 1;
        if ($bullet !== null) {
            $sec['body'][] = '- ' . $bullet;
            $paraBreak = false;
        } elseif (preg_match('/^\(\d+[a-z]?\)/u', $t) || $paraBreak || $last < 0) {
            $sec['body'][] = ($last >= 0 ? "\n" : '') . $t;
            $paraBreak = false;
        } else {
            // Zeilenumbruch innerhalb eines Absatzes (bei Aufzählungen: Fortsetzung des Punkts)
            $prev = $sec['body'][$last];
            $sec['body'][$last] = preg_match('/\p{Ll}-$/u', $prev) && preg_match('/^\p{Ll}/u', $t) ? mb_substr($prev, 0, -1) . $t : $prev . ' ' . $t;
        }
    }
    $flushBook();
    // Vorspann: alles vor dem ersten Buch, ohne Datum/Autor-Zeilen
    $desc = trim(implode(' ', array_filter($pre, fn($l) => !preg_match('/@|^\w{3} \d{1,2}, \d{4}/u', $l) && $l !== $name)));
    if ($name === '' && $pre) {
        $name = $pre[0];
        $desc = trim(preg_replace('/^' . preg_quote($name, '/') . '\s*/u', '', $desc));
    }
    foreach ($books as $b) {
        $seen = [];
        foreach ($b['sections'] as $s) {
            if (isset($seen[$s['number']])) {
                $warnings[] = $b['title'] . ': § ' . $s['number'] . ' kommt mehrfach vor.';
            }
            $seen[$s['number']] = true;
            if ($s['title'] === '') {
                $warnings[] = $b['title'] . ': § ' . $s['number'] . ' hat keine Überschrift.';
            }
            if ($s['penalty'] !== '' && !$s['jail_max'] && !$s['fine_max']) {
                $warnings[] = $b['title'] . ': Strafmaß von § ' . $s['number'] . ' nicht auswertbar („' . $s['penalty'] . '“).';
            }
        }
    }
    if (!$books) {
        $warnings[] = 'Keine Paragraphen erkannt. Paragraphen müssen mit „§1“, „§ 1“ oder „Art. 1“ beginnen.';
    }
    return ['name' => mb_substr($name, 0, 190), 'description' => mb_substr($desc, 0, 2000), 'books' => $books, 'warnings' => array_slice($warnings, 0, 50)];
}

/** Gesetzbuch speichern; vorhandenes mit gleichem Namen im selben Staat wird ersetzt. */
function law_import(array $parsed, ?int $serverId, string $source, int $userId): int
{
    $existing = one('SELECT id FROM law_codes WHERE name = ? AND ' . ($serverId ? 'server_id = ' . (int) $serverId : 'server_id IS NULL'), [$parsed['name']]);
    db()->beginTransaction();
    try {
        if ($existing) {
            $codeId = (int) $existing['id'];
            q('DELETE FROM law_sections WHERE book_id IN (SELECT id FROM law_books WHERE code_id = ?)', [$codeId]);
            q('DELETE FROM law_books WHERE code_id = ?', [$codeId]);
            update('law_codes', $codeId, ['description' => $parsed['description'], 'source' => mb_substr($source, 0, 190), 'active' => 1, 'updated_at' => now()]);
        } else {
            $codeId = insert('law_codes', ['server_id' => $serverId, 'name' => $parsed['name'], 'description' => $parsed['description'], 'source' => mb_substr($source, 0, 190),
                'active' => 1, 'created_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        }
        // Höchstgrenzen der Gesamtstrafe aus dem Text (für den Strafrechner)
        [$maxJail, $maxFine] = law_detect_limits(implode("\n", array_merge(...array_map(fn($b) => array_column($b['sections'], 'body'), $parsed['books'] ?: [['sections' => []]]))));
        try {
            update('law_codes', $codeId, ['max_jail' => $maxJail, 'max_fine' => $maxFine]);
        } catch (PDOException $e) {
            // vor Migration 0045
        }
        foreach (array_values($parsed['books']) as $bi => $b) {
            $bookId = insert('law_books', ['code_id' => $codeId, 'title' => mb_substr((string) $b['title'], 0, 190), 'short' => mb_substr((string) $b['short'], 0, 32), 'sort' => $bi]);
            foreach (array_values($b['sections']) as $si => $s) {
                insert('law_sections', ['book_id' => $bookId, 'number' => mb_substr((string) $s['number'], 0, 16), 'title' => mb_substr((string) $s['title'], 0, 190),
                    'body' => (string) $s['body'], 'penalty' => mb_substr((string) $s['penalty'], 0, 190), 'jail_max' => (int) $s['jail_max'], 'fine_max' => (int) $s['fine_max'],
                    'penalty_mode' => (string) $s['penalty_mode'], 'sort' => $si]);
            }
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
    return $codeId;
}

function law_codes(bool $activeOnly = true): array
{
    try {
        return all('SELECT * FROM law_codes' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY name');
    } catch (PDOException $e) {
        return [];
    }
}

/** Gesetzbuch, das für eine Person zuerst gilt: das ihres Staats (Spielserver), sonst das erste. */
function law_default_code(array $codes, int $userId): ?array
{
    $srv = game_server_for_user($userId);
    foreach ($codes as $c) {
        if ((int) $c['server_id'] === $srv) {
            return $c;
        }
    }
    foreach ($codes as $c) {
        if (!$c['server_id']) {
            return $c;
        }
    }
    return $codes[0] ?? null;
}

/** Strafmaße eines Gesetzbuchs in den Katalog der aktuellen Fraktion übernehmen (nach Kürzel, z. B. „§1 StGB“). */
function law_to_catalog(int $codeId): array
{
    $new = $upd = 0;
    $rows = all('SELECT s.*, b.short, b.title AS book FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ? AND (s.jail_max > 0 OR s.fine_max > 0) ORDER BY b.sort, s.sort', [$codeId]);
    $sort = (int) scalar('SELECT COALESCE(MAX(sort), 0) FROM price_list WHERE ' . fw());
    foreach ($rows as $s) {
        $code = mb_substr('§' . $s['number'] . ' ' . $s['short'], 0, 32);
        $row = ['name' => mb_substr($s['title'] ?: $code, 0, 190), 'category' => mb_substr((string) $s['book'], 0, 190), 'price_cents' => (int) $s['fine_max'] * 100,
            'jail' => (int) $s['jail_max'], 'active' => 1, 'updated_at' => now()];
        if ($ex = one('SELECT id FROM price_list WHERE code = ? AND ' . fw(), [$code])) {
            update('price_list', (int) $ex['id'], $row);
            $upd++;
        } else {
            insert('price_list', $row + ['code' => $code, 'sort' => $sort += 10, 'created_at' => now()]);
            $new++;
        }
    }
    return [$new, $upd];
}

/** Text eines Paragraphen als HTML (Absätze, Aufzählungen). */
function law_body_html(string $body, string $highlight = ''): string
{
    $out = '';
    $list = false;
    $hl = fn(string $s) => $highlight !== '' ? preg_replace('/(' . preg_quote(e($highlight), '/') . ')/iu', '<mark>$1</mark>', e($s)) : e($s);
    foreach (explode("\n", $body) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (str_starts_with($line, '- ')) {
            $out .= ($list ? '' : '<ul>') . '<li>' . $hl(substr($line, 2)) . '</li>';
            $list = true;
            continue;
        }
        $out .= ($list ? '</ul>' : '') . '<p>' . $hl($line) . '</p>';
        $list = false;
    }
    return $out . ($list ? '</ul>' : '');
}

// ----------------------------------------------------- Nutzung in anderen Teilen

/** Höchstgrenzen aus dem Gesetzestext („maximale Freiheitsstrafe beträgt 100 Hafteinheiten“, „maximale Geldstrafe beträgt 50.000 $“). */
function law_detect_limits(string $text): array
{
    $jail = preg_match('/(?:maximale|höchste|Höchst)\s*(?:Freiheitsstrafe|Haftstrafe)\s+(?:beträgt|von)\s+(\d+)/iu', $text, $m) ? (int) $m[1] : 0;
    $fine = preg_match('/(?:maximale|höchste|Höchst)\s*Geldstrafe\s+(?:beträgt|von)\s+(\d{1,3}(?:[.\s]\d{3})+|\d+)/iu', $text, $m) ? (int) preg_replace('/\D/', '', $m[1]) : 0;
    return [$jail, $fine];
}

/** Gesetzbuch, das für die angemeldete Person gilt (Staat ihres Spielservers), oder null. */
function law_current_code(): ?array
{
    static $code = false;
    if ($code === false) {
        $me = current_user();
        $code = $me ? law_default_code(law_codes(), (int) $me['id']) : null;
    }
    return $code;
}

/** Paragraphen des geltenden Gesetzbuchs: ref („§2 StGB“), title, penalty, jail, fine, mode, book. */
function law_sections_flat(?array $code = null): array
{
    static $cache = [];
    $code ??= law_current_code();
    if (!$code) {
        return [];
    }
    try {
        return $cache[(int) $code['id']] ??= array_map(fn($s) => ['id' => (int) $s['id'], 'ref' => '§' . $s['number'] . ' ' . $s['short'], 'number' => $s['number'], 'short' => $s['short'],
            'title' => $s['title'], 'book' => $s['book'], 'penalty' => $s['penalty'], 'jail' => (int) $s['jail_max'], 'fine' => (int) $s['fine_max'], 'mode' => $s['penalty_mode'], 'body' => $s['body']],
            all('SELECT s.*, b.short, b.title AS book FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ? ORDER BY b.sort, s.sort', [$code['id']]));
    } catch (PDOException $e) {
        return [];
    }
}

/** Vorschläge für Formularfelder (Tatvorwurf, Delikte, Begründung): „§2 StGB – Raub (bis 35 HE oder 15.000 $)“. */
function law_datalist(): array
{
    return array_map(fn($s) => $s['ref'] . ' – ' . $s['title'] . ($s['jail'] || $s['fine'] ? ' (' . law_penalty_label(['jail_max' => $s['jail'], 'fine_max' => $s['fine'], 'penalty_mode' => $s['mode']]) . ')' : ''),
        array_filter(law_sections_flat(), fn($s) => $s['short'] !== ''));
}

/** In einem Text zitierte Paragraphen („§ 2 StGB“, „§2 Abs. 1 StVO“, „§§ 1, 5 StGB“) des geltenden Gesetzbuchs. */
function law_refs_in(string $text): array
{
    $flat = law_sections_flat();
    if (!$flat || !str_contains($text, '§')) {
        return [];
    }
    $by = [];
    foreach ($flat as $s) {
        $by[mb_strtolower($s['short']) . '|' . mb_strtolower($s['number'])] = $s;
    }
    $found = [];
    if (preg_match_all('/§§?\s*((?:\d+[a-z]?(?:\s*(?:Abs\.|Absatz)\s*\d+)?(?:\s*(?:,|und|u\.)\s*)?)+)\s*([A-ZÄÖÜ][A-Za-zÄÖÜäöüß]{1,11})/u', $text, $mm, PREG_SET_ORDER)) {
        foreach ($mm as $m) {
            preg_match_all('/(\d+[a-z]?)(?:\s*(?:Abs\.|Absatz)\s*\d+)?/u', $m[1], $nums);
            foreach ($nums[1] as $n) {
                $key = mb_strtolower($m[2]) . '|' . mb_strtolower($n);
                if (isset($by[$key])) {
                    $found[$by[$key]['id']] = $by[$key];
                }
            }
        }
    }
    return array_values($found);
}

/** Behörden, bei denen eine Kanzlei Schriftsätze einreichen kann (Gericht/Staatsanwaltschaft, Polizei, Verwaltung). */
function law_submit_targets(): array
{
    try {
        $out = [];
        foreach (factions() as $f) {
            if (in_array($f['kind'] ?? '', ['justice', 'police', 'government'], true)) {
                $out[(int) $f['id']] = $f['name'];
            }
        }
        return $out;
    } catch (Throwable $e) {
        return [];
    }
}

/** Nach dem Ausstellen eines Kanzlei-Schriftsatzes: bei der gewählten Behörde einreichen (Freigabe), Akteneinsicht als Anfrage. */
function law_document_issued(int $docId, array $type, array $data, ?array $patient, array $issuer): void
{
    if (!str_starts_with($type['key'], 'ra_') || !($target = (int) ($data['einreichen'] ?? 0)) || !isset(law_submit_targets()[$target])) {
        return;
    }
    $f = faction_by_id($target);
    $firm = org('org_name');
    $number = (string) scalar('SELECT number FROM documents WHERE id = ?', [$docId]);
    $person = $patient ? patient_name($patient) : '';
    insert('document_shares', ['document_id' => $docId, 'kind' => 'faction', 'target_faction_id' => $target, 'agency' => (string) ($f['code'] ?? ''),
        'recipient' => (string) $f['name'], 'purpose' => 'Eingereicht von ' . $firm . ': ' . $type['title'], 'expires_at' => null, 'created_by' => (int) $issuer['id'], 'created_at' => now()]);
    if ($type['key'] === 'ra_akteneinsicht') {
        // Die Behörde beantwortet die Anfrage und gibt dabei die Akte frei (Behördenanfragen)
        insert('agency_requests', ['to_faction_id' => $target, 'subject' => 'Akteneinsicht' . (($data['aktenzeichen'] ?? '') !== '' ? ' Az. ' . $data['aktenzeichen'] : '') . ($person !== '' ? ' – ' . $person : ''),
            'body' => 'Antrag auf Akteneinsicht von ' . $firm . ' (' . user_display($issuer) . '), Schriftsatz ' . $number . '. ' . trim((string) ($data['zweck'] ?? '')),
            'citizen_id' => (string) ($patient['citizen_id'] ?? ''), 'person' => $person, 'reference' => 'akteneinsicht:' . $docId, 'status' => 'open',
            'created_by' => (int) $issuer['id'], 'created_at' => now()]);
    }
    audit('document.submit', 'document', $docId, $number . ' eingereicht bei ' . $f['name']);
    $prev = fid();
    set_faction_context($target);
    notify_users_with($type['key'] === 'ra_akteneinsicht' ? 'requests.manage' : 'requests.view', $type['key'] === 'ra_akteneinsicht' ? 'request' : 'share',
        'Eingang von ' . $firm . ': ' . $type['title'] . ($person !== '' ? ' (' . $person . ')' : ''), $type['key'] === 'ra_akteneinsicht' ? url('requests') : url('shared', ['id' => $docId]),
        fn($u) => (int) $u['faction_id'] === $target);
    set_faction_context($prev);
}

/** Kanzleien mit Anschrift/Telefon (aus den Einstellungen der jeweiligen Kanzlei) und ihren Anwältinnen und Anwälten. */
function law_firms(): array
{
    $firms = [];
    foreach (lawyers_directory() as $l) {
        $fid = (int) $l['faction_id'];
        if (!isset($firms[$fid])) {
            $prev = fid();
            set_faction_context($fid);
            $firms[$fid] = ['id' => $fid, 'name' => $l['firm'], 'street' => org('org_street'), 'city' => org('city'), 'phone' => org('phone'), 'logo' => faction_logo(), 'lawyers' => []];
            set_faction_context($prev);
        }
        $firms[$fid]['lawyers'][] = $l;
    }
    return array_values($firms);
}
