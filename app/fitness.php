<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Einsatztauglichkeit der Mitarbeitenden (TAS, Feuerwehr): Der Rettungsdienst untersucht und stellt eine
 * Bescheinigung aus. Die Dienststelle erfährt nur das Ergebnis (Art, tauglich/Einschränkungen, gültig bis) –
 * die Befunde bleiben vertraulich in der Patientenakte. Zuordnung über die Bürger-ID am Konto, sonst über den Namen.
 */

/** Untersuchungsarten: [Bezeichnung, Kurzname, Gültigkeit in Monaten]. */
const FITNESS_KINDS = [
    'g263'   => ['Atemschutz (G 26.3)', 'Atemschutz', 36],
    'g31'    => ['Tauchtauglichkeit (G 31, Rettungstaucher)', 'Tauchen', 12],
    'g41'    => ['Absturzgefahr (G 41, Höhen- und Bergrettung)', 'Höhe', 36],
    'hems'   => ['Luftrettung – Besatzung (HEMS, Windenoperator)', 'Luftrettung', 12],
    'pilot'  => ['Luftrettung – Pilot/in (fliegerärztlich, Klasse 2)', 'Pilot/in', 24],
    'wasser' => ['Rettungsschwimmen / Wasserrettung', 'Wasser', 24],
    'allg'   => ['Allgemeine Einsatztauglichkeit (Gelände, Belastung)', 'Allgemein', 24],
];
const FITNESS_RESULTS = ['geeignet' => ['green', 'tauglich'], 'bedingt' => ['orange', 'tauglich mit Einschränkungen'],
    'befristet' => ['red', 'vorübergehend nicht tauglich'], 'nicht' => ['red', 'nicht tauglich']];
/** Spalten der Übersicht je Fraktionsart (weitere erscheinen, sobald es Nachweise gibt). */
const FITNESS_DEFAULT_KINDS = ['rescue' => ['g263', 'g31', 'g41', 'hems', 'pilot', 'wasser', 'allg'], 'fire' => ['g263', 'g41', 'allg']];

function fitness_kind_options(): array
{
    return array_map(fn($k) => $k[0], FITNESS_KINDS);
}

/** Gültigkeit aus dem Untersuchungsdatum und der Art. */
function fitness_default_until(string $kind, string $examined): string
{
    $months = FITNESS_KINDS[$kind][2] ?? 24;
    return date('Y-m-d', strtotime(($examined ?: date('Y-m-d')) . " +$months months"));
}

/** Konten, denen eine Untersuchung der Person gehört: gleiche Bürger-ID, sonst gleicher Name – nur in Fraktionen mit Modul. */
function fitness_employees(?array $patient, array $data): array
{
    $factions = factions_with_module('fitness');
    if (!$factions || !$patient) {
        return [];
    }
    $in = implode(',', array_map('intval', $factions));
    $cid = trim((string) ($patient['citizen_id'] ?? ''));
    $rows = $cid !== '' ? all("SELECT * FROM users WHERE active = 1 AND citizen_id = ? AND faction_id IN ($in)", [$cid]) : [];
    if (!$rows) {
        $name = trim(patient_name($patient));
        $rows = $name !== '' ? all("SELECT * FROM users WHERE active = 1 AND (citizen_id = '' OR citizen_id IS NULL) AND LOWER(name) = LOWER(?) AND faction_id IN ($in)", [$name]) : [];
    }
    return $rows;
}

/** Ergebnis für ein Konto eintragen und Dienststelle benachrichtigen (läuft im Kontext der Dienststelle). */
function fitness_record(array $user, array $r): int
{
    $prev = fid();
    set_faction_context((int) $user['faction_id']);
    try {
        // ältere Nachweise derselben Art erinnern nicht mehr
        q('UPDATE fitness_records SET reminded = 1 WHERE user_id = ? AND kind = ? AND faction_id = ?', [$user['id'], $r['kind'], fid()]);
        $id = insert('fitness_records', $r + ['faction_id' => fid(), 'user_id' => (int) $user['id'], 'created_at' => now()]);
        if ($r['kind'] === 'g263' && in_array($r['result'], ['geeignet', 'bedingt'], true) && module_enabled('breathing')) {
            if (scalar('SELECT user_id FROM breathing_status WHERE user_id = ?', [$user['id']])) {
                q('UPDATE breathing_status SET g26_until = ?, reminded = 0, updated_at = ? WHERE user_id = ?', [$r['valid_until'], now(), $user['id']]);
            } else {
                insert('breathing_status', ['user_id' => (int) $user['id'], 'faction_id' => fid(), 'g26_until' => $r['valid_until'], 'updated_at' => now()]);
            }
        }
        $label = (FITNESS_KINDS[$r['kind']][1] ?? $r['kind']) . ': ' . (FITNESS_RESULTS[$r['result']][1] ?? $r['result']) . ($r['valid_until'] ? ' bis ' . fmt_date($r['valid_until']) : '');
        notify_user((int) $user['id'], 'fitness', 'Tauglichkeit – ' . $label, url('fitness'));
        notify_users_with('fitness.manage', 'fitness', 'Tauglichkeit ' . $user['name'] . ' – ' . $label, url('fitness'),
            fn($u) => (int) $u['faction_id'] === fid() && (int) $u['id'] !== (int) $user['id']);
    } finally {
        set_faction_context($prev);
    }
    return $id;
}

/**
 * Nach dem Ausstellen: Untersuchungsbogen → Bescheinigung für die Dienststelle. Bescheinigung → Befunde (Kurzform) in einen
 * vertraulichen Akteneintrag, Ergebnis je Verwendung an die Dienststelle der untersuchten Person, offene Anforderungen erledigen.
 */
function fitness_document_issued(int $docId, string $type, array $data, ?array $patient, array $issuer, string $liveSignature = ''): void
{
    if ($type === 'sv_untersuchung') {
        sv_issue_certificate($docId, $data, $patient, $issuer, $liveSignature);
        return;
    }
    if ($type !== 'einsatztauglichkeit') {
        return;
    }
    $pairs = [];
    foreach ((array) ($data['verwendungen'] ?? []) as $k => $r) {
        if (isset(FITNESS_KINDS[$k], FITNESS_RESULTS[$r])) {
            $pairs[$k] = $r;
        }
    }
    if (!$pairs) {
        $kind = array_key_exists((string) ($data['art'] ?? ''), FITNESS_KINDS) ? (string) $data['art'] : 'allg';
        $pairs[$kind] = array_key_exists((string) ($data['result'] ?? ''), FITNESS_RESULTS) ? (string) $data['result'] : 'nicht';
    }
    $examined = (string) ($data['exam_date'] ?? date('Y-m-d'));
    if (empty($data['valid_until']) && in_array($data['result'] ?? '', ['geeignet', 'bedingt'], true)) {
        $data['valid_until'] = fitness_default_until(array_key_first($pairs), $examined);
        q('UPDATE documents SET valid_until = ? WHERE id = ?', [$data['valid_until'], $docId]);
    }
    if (trim((string) ($data['befund'] ?? '')) !== '' && $patient) {
        insert('entries', ['faction_id' => (int) $patient['faction_id'], 'patient_id' => (int) $patient['id'], 'type' => 'Untersuchung',
            'title' => 'Befunde zur Einsatztauglichkeit (' . FITNESS_KINDS[array_key_first($pairs)][1] . ')', 'content' => (string) $data['befund'], 'confidential' => 1,
            'occurred_at' => now(), 'created_by' => (int) $issuer['id'], 'created_at' => now(), 'updated_at' => now()]);
    }
    unset($data['befund']);
    $employees = fitness_employees($patient, $data);
    if ($employees) {
        $f = faction_by_id((int) $employees[0]['faction_id']);
        if (trim((string) ($data['dienststelle'] ?? '')) === '') {
            $data['dienststelle'] = 'Leitung ' . ($f['name'] ?? '');
        }
        if (trim((string) ($data['standard'] ?? '')) === '' && !empty($data['verwendungen'])) {
            $prev = fid();
            set_faction_context((int) $employees[0]['faction_id']);
            $data['standard'] = fitness_standard_name();
            set_faction_context($prev);
        }
    }
    q('UPDATE documents SET data = ? WHERE id = ?', [json_encode($data, JSON_UNESCAPED_UNICODE), $docId]);
    $doctor = user_display($issuer);
    $number = (string) scalar('SELECT number FROM documents WHERE id = ?', [$docId]);
    $shared = [];
    foreach ($employees as $u) {
        $fid = (int) $u['faction_id'];
        foreach ($pairs as $kind => $result) {
            $valid = in_array($result, ['geeignet', 'bedingt'], true) ? (string) ($data['valid_until'] ?? '') : '';
            fitness_record($u, ['kind' => $kind, 'result' => $result, 'restrictions' => mb_substr((string) ($data['conditions'] ?? ''), 0, 2000),
                'examined_on' => $examined, 'valid_until' => $valid, 'examiner' => mb_substr($doctor . ' (' . org('org_short') . ')', 0, 190),
                'document_id' => $docId, 'source_faction_id' => fid(), 'recorded_by' => (int) $issuer['id']]);
            q("UPDATE agency_requests SET status = 'done', answer = ?, answered_by = ?, answered_at = ? WHERE faction_id = ? AND to_faction_id = ? AND status IN ('open', 'in_progress')
                AND reference = ?", ['Bescheinigung ausgestellt: ' . $number, $issuer['id'], now(), $fid, fid(), 'fitness:' . $u['id'] . ':' . $kind]);
        }
        if (!isset($shared[$fid])) {
            // Die Dienststelle darf die Bescheinigung (ohne Befunde) einsehen
            $shared[$fid] = true;
            $f = faction_by_id($fid);
            $until = (string) ($data['valid_until'] ?? '');
            insert('document_shares', ['document_id' => $docId, 'kind' => 'faction', 'target_faction_id' => $fid, 'agency' => (string) ($f['code'] ?? ''),
                'recipient' => (string) ($f['name'] ?? ''), 'purpose' => 'Einsatztauglichkeit (Dienststelle)', 'expires_at' => $until !== '' ? $until . ' 23:59:59' : date('Y-m-d H:i:s', time() + 90 * 86400),
                'created_by' => (int) $issuer['id'], 'created_at' => now()]);
        }
    }
}

function fitness_revoke_for(int $docId): void
{
    try {
        q('UPDATE fitness_records SET revoked = 1 WHERE document_id = ?', [$docId]);
    } catch (PDOException $e) {
        // vor der Migration
    }
}

/** Aktueller (neuester gültiger) Nachweis je Konto und Art der aktuellen Fraktion: [user_id][kind] => Zeile. */
function fitness_matrix(?int $userId = null): array
{
    $out = [];
    $rows = all('SELECT * FROM fitness_records WHERE revoked = 0 AND ' . fw() . ($userId ? ' AND user_id = ' . (int) $userId : '') . ' ORDER BY examined_on, id');
    foreach ($rows as $r) {
        $out[(int) $r['user_id']][$r['kind']] = $r;
    }
    return $out;
}

/** Zustand eines Nachweises: [Farbe, Text]. */
function fitness_state(?array $r): array
{
    if (!$r) {
        return ['grey', 'kein Nachweis'];
    }
    if (!in_array($r['result'], ['geeignet', 'bedingt'], true)) {
        return ['red', FITNESS_RESULTS[$r['result']][1] ?? $r['result']];
    }
    if ($r['valid_until'] !== '' && $r['valid_until'] < date('Y-m-d')) {
        return ['red', 'abgelaufen ' . fmt_date($r['valid_until'])];
    }
    $soon = $r['valid_until'] !== '' && $r['valid_until'] <= date('Y-m-d', time() + 30 * 86400);
    return [$soon || $r['result'] === 'bedingt' ? 'orange' : 'green', ($r['result'] === 'bedingt' ? 'mit Einschr. · ' : '') . ($r['valid_until'] !== '' ? 'bis ' . fmt_date($r['valid_until']) : 'tauglich')];
}

function fitness_due_count(): int
{
    try {
        if (!module_enabled('fitness') || !can('fitness.manage')) {
            return 0;
        }
        $n = 0;
        $active = array_map('intval', array_column(all('SELECT id FROM users WHERE active = 1 AND ' . fw()), 'id'));
        foreach (fitness_matrix() as $uid => $kinds) {
            foreach ($kinds as $r) {
                $n += in_array($uid, $active, true) && fitness_state($r)[0] === 'red' && $r['valid_until'] !== '' ? 1 : 0;
            }
        }
        return $n;
    } catch (PDOException $e) {
        return 0;
    }
}

/** Hinweis 30 Tage vor Ablauf an die Person und die Leitung. */
function fitness_cron(): int
{
    if (!module_enabled('fitness')) {
        return 0;
    }
    $n = 0;
    foreach (all("SELECT r.*, u.name FROM fitness_records r JOIN users u ON u.id = r.user_id WHERE r.revoked = 0 AND r.reminded = 0 AND r.valid_until <> '' AND r.valid_until <= ? AND u.active = 1 AND " . fw('r'),
        [date('Y-m-d', time() + 30 * 86400)]) as $r) {
        q('UPDATE fitness_records SET reminded = 1 WHERE id = ?', [$r['id']]);
        $msg = 'Tauglichkeit ' . (FITNESS_KINDS[$r['kind']][1] ?? $r['kind']) . ' läuft am ' . fmt_date($r['valid_until']) . ' ab';
        notify_user((int) $r['user_id'], 'fitness', $msg, url('fitness'));
        notify_users_with('fitness.manage', 'fitness', $msg . ' – ' . $r['name'], url('fitness'), fn($u) => (int) $u['faction_id'] === fid());
        $n++;
    }
    return $n;
}

// ------------------------------------------------- Sonderverwendungsfähigkeit

/**
 * Untersuchungsstandard für Spezialkräfte (angelehnt an militärische Musterung/Sonderverwendung, PULHES-Profil):
 * ausführlicher Untersuchungsbogen beim Rettungsdienst, daraus automatisch die Bescheinigung für die Leitung der Dienststelle.
 */
const SV_STANDARD_DEFAULT = 'Sonderverwendungsfähigkeit Spezialkräfte (SV-SK)';
/** PULHES-Profil: Buchstabe => [Bereich, Mindestgrad für Sonderverwendung]. Grad 1 uneingeschränkt … 4 nicht verwendungsfähig. */
const SV_PULHES = [
    'p' => ['P – Allgemeine körperliche Belastbarkeit', 1], 'u' => ['U – Obere Extremitäten', 1], 'l' => ['L – Untere Extremitäten, Wirbelsäule', 1],
    'h' => ['H – Gehör, Ohren', 2], 'e' => ['E – Augen, Sehvermögen', 2], 's' => ['S – Psychische Stabilität', 1],
];
const SV_GRADES = ['1' => '1 – uneingeschränkt', '2' => '2 – geringe Einschränkung', '3' => '3 – deutliche Einschränkung (Auflagen)', '4' => '4 – nicht verwendungsfähig'];
/** Untersuchungsbereiche für die Bescheinigung (ohne Messwerte). */
const SV_SCOPE = ['bmi' => 'Körperliche Untersuchung', 'rr' => 'Körperliche Untersuchung', 'puls' => 'Körperliche Untersuchung', 'zahn' => 'Körperliche Untersuchung',
    'romberg' => 'Körperliche Untersuchung', 'visus_cc' => 'Sehtest', 'visus_air' => 'Sehtest', 'visus_sc' => 'Sehtest', 'farbsinn' => 'Sehtest', 'stereo' => 'Sehtest',
    'hoer_low' => 'Tonaudiometrie', 'hoer_high' => 'Tonaudiometrie', 'druck' => 'HNO-Untersuchung', 'ekg' => 'Ruhe-EKG', 'ergo' => 'Ergometrie', 'spiro' => 'Spirometrie',
    'roentgen' => 'Röntgen Thorax', 'labor' => 'Labor', 'drogen' => 'Drogen- und Alkoholscreening', 'lauf' => 'Sportleistungstest', 'liegest' => 'Sportleistungstest',
    'situps' => 'Sportleistungstest', 'klimmz' => 'Sportleistungstest', 'marsch' => 'Gepäckmarsch', 'schwimm' => 'Schwimm- und Tauchtest', 'strecke' => 'Schwimm- und Tauchtest',
    'treten' => 'Schwimm- und Tauchtest', 'kammer' => 'Druckkammertest', 'asstrecke' => 'Atemschutz-Übungsstrecke', 'steig' => 'Höhen- und Steigtest', 'huet' => 'HUET',
    'psych' => 'Psychologische Eignung'];
const SV_OVERALL = ['geeignet' => 'SONDERVERWENDUNGSFÄHIG', 'bedingt' => 'EINGESCHRÄNKT VERWENDUNGSFÄHIG', 'befristet' => 'VORÜBERGEHEND NICHT VERWENDUNGSFÄHIG', 'nicht' => 'NICHT VERWENDUNGSFÄHIG'];

function sv_num(array $d, string $k): ?float
{
    $v = str_replace(',', '.', trim((string) ($d[$k] ?? '')));
    return is_numeric($v) ? (float) $v : null;
}

/** „mm:ss“ oder „h:mm:ss“ (auch „h:mm“ bei Stundenangaben über $hours) in Sekunden. */
function sv_secs(array $d, string $k, bool $hours = false): ?int
{
    $v = trim((string) ($d[$k] ?? ''));
    if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $v, $m)) {
        return is_numeric($v) ? (int) round((float) $v * ($hours ? 3600 : 60)) : null;
    }
    return isset($m[3]) && $m[3] !== '' ? (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3] : ($hours ? (int) $m[1] * 3600 + (int) $m[2] * 60 : (int) $m[1] * 60 + (int) $m[2]);
}

/** Mindestanforderungen: key => [Bezeichnung, Norm, Verwendungen, Prüfung (null = nicht erhoben), Wert für die Anzeige]. */
function sv_requirements(): array
{
    $all = array_keys(FITNESS_KINDS);
    $sel = fn(string $k, string $ok) => fn(array $d) => in_array($d[$k] ?? '', ['', 'nicht durchgeführt'], true) ? null : ($d[$k] === $ok);
    $min = fn(array $d, array $keys) => ($vals = array_filter(array_map(fn($k) => sv_num($d, $k), $keys), fn($v) => $v !== null)) ? min($vals) : null;
    $max = fn(array $d, array $keys) => ($vals = array_filter(array_map(fn($k) => sv_num($d, $k), $keys), fn($v) => $v !== null)) ? max($vals) : null;
    $bmi = fn(array $d) => ($h = sv_num($d, 'groesse')) && ($w = sv_num($d, 'gewicht')) ? round($w / (($h / 100) ** 2), 1) : null;
    $wkg = fn(array $d) => ($w = sv_num($d, 'ergo_watt')) && ($kg = sv_num($d, 'gewicht')) ? round($w / $kg, 2) : null;
    $fmt = fn($v) => $v === null ? '' : str_replace('.', ',', (string) $v);
    return [
        'bmi'      => ['Body-Mass-Index', '18,5 – 30 kg/m²', $all, fn($d) => ($b = $bmi($d)) === null ? null : $b >= 18.5 && $b <= 30, fn($d) => $fmt($bmi($d))],
        'rr'       => ['Blutdruck in Ruhe', '≤ 140/90 mmHg', $all, fn($d) => sv_num($d, 'rr_sys') === null ? null : sv_num($d, 'rr_sys') <= 140 && (sv_num($d, 'rr_dia') ?? 0) <= 90, fn($d) => trim(($d['rr_sys'] ?? '') . '/' . ($d['rr_dia'] ?? ''), '/')],
        'puls'     => ['Ruhepuls', '45 – 100 /min', $all, fn($d) => ($p = sv_num($d, 'puls')) === null ? null : $p >= 45 && $p <= 100, fn($d) => $d['puls'] ?? ''],
        'visus_cc' => ['Visus mit Korrektur, je Auge', '≥ 0,8', ['g263', 'g41', 'wasser', 'allg'], fn($d) => ($v = $min($d, ['visus_cc_r', 'visus_cc_l'])) === null ? null : $v >= 0.8, fn($d) => trim(($d['visus_cc_r'] ?? '') . ' / ' . ($d['visus_cc_l'] ?? ''), ' /')],
        'visus_air' => ['Visus mit Korrektur, je Auge (Luftrettung)', '≥ 1,0', ['hems', 'pilot'], fn($d) => ($v = $min($d, ['visus_cc_r', 'visus_cc_l'])) === null ? null : $v >= 1.0, fn($d) => trim(($d['visus_cc_r'] ?? '') . ' / ' . ($d['visus_cc_l'] ?? ''), ' /')],
        'visus_sc' => ['Visus ohne Korrektur, je Auge', '≥ 0,5', ['g31', 'hems', 'pilot', 'wasser'], fn($d) => ($v = $min($d, ['visus_sc_r', 'visus_sc_l'])) === null ? null : $v >= 0.5, fn($d) => trim(($d['visus_sc_r'] ?? '') . ' / ' . ($d['visus_sc_l'] ?? ''), ' /')],
        'farbsinn' => ['Farbsinn (Ishihara)', 'normal', ['hems', 'pilot', 'g263'], $sel('farbsinn', 'normal'), fn($d) => $d['farbsinn'] ?? ''],
        'stereo'   => ['Räumliches Sehen, Gesichtsfeld', 'normal', ['hems', 'pilot', 'g41'], fn($d) => ($d['stereo'] ?? '') === '' ? null : $d['stereo'] === 'normal' && ($d['gesichtsfeld'] ?? 'normal') === 'normal', fn($d) => trim(($d['stereo'] ?? '') . ' / ' . ($d['gesichtsfeld'] ?? ''), ' /')],
        'hoer_low' => ['Hörschwelle 500 – 2000 Hz, je Ohr', '≤ 25 dB', $all, fn($d) => ($v = $max($d, ['hr_500', 'hr_1000', 'hr_2000', 'hl_500', 'hl_1000', 'hl_2000'])) === null ? null : $v <= 25, fn($d) => $fmt($max($d, ['hr_500', 'hr_1000', 'hr_2000', 'hl_500', 'hl_1000', 'hl_2000'])) . ' dB (max.)'],
        'hoer_high' => ['Hörschwelle 3 / 4 / 6 kHz, je Ohr', '≤ 35 / 45 / 55 dB', ['g263', 'g31', 'hems', 'pilot'], function ($d) use ($max) {
            $r = [[['hr_3000', 'hl_3000'], 35], [['hr_4000', 'hl_4000'], 45], [['hr_6000', 'hl_6000'], 55]];
            $any = false;
            foreach ($r as [$keys, $lim]) {
                if (($v = $max($d, $keys)) !== null) {
                    $any = true;
                    if ($v > $lim) {
                        return false;
                    }
                }
            }
            return $any ? true : null;
        }, fn($d) => implode(' / ', array_map(fn($f) => $fmt($max($d, ["hr_$f", "hl_$f"])) ?: '–', [3000, 4000, 6000])) . ' dB'],
        'druck'    => ['Trommelfell, Druckausgleich (Valsalva)', 'beidseits frei', ['g31', 'hems', 'pilot'], $sel('druckausgleich', 'beidseits frei'), fn($d) => $d['druckausgleich'] ?? ''],
        'ekg'      => ['Ruhe-EKG', 'unauffällig', $all, $sel('ekg', 'unauffällig'), fn($d) => $d['ekg'] ?? ''],
        'ergo'     => ['Ergometrie (Belastungs-EKG)', '≥ 3,0 W/kg, ohne Ischämiezeichen', ['g263', 'g31', 'g41', 'wasser', 'allg'], fn($d) => ($w = $wkg($d)) === null ? null : $w >= 3.0 && ($d['ergo_befund'] ?? 'unauffällig') !== 'auffällig', fn($d) => ($w = $wkg($d)) === null ? '' : $fmt($w) . ' W/kg (' . ($d['ergo_watt'] ?? '') . ' W)'],
        'spiro'    => ['Lungenfunktion (Spirometrie)', 'FVC, FEV1 ≥ 80 % Soll · FEV1/FVC ≥ 70 %', ['g263', 'g31'], fn($d) => sv_num($d, 'fvc') === null ? null : sv_num($d, 'fvc') >= 80 && (sv_num($d, 'fev1') ?? 100) >= 80 && (sv_num($d, 'tiffeneau') ?? 100) >= 70, fn($d) => trim(($d['fvc'] ?? '') . ' / ' . ($d['fev1'] ?? '') . ' / ' . ($d['tiffeneau'] ?? ''), ' /') . ' %'],
        'roentgen' => ['Röntgen Thorax', 'ohne Befund', ['g31'], $sel('roentgen', 'ohne Befund'), fn($d) => $d['roentgen'] ?? ''],
        'labor'    => ['Labor (Blutbild, Leber, Niere, Glukose, Urin)', 'ohne Befund', $all, $sel('labor', 'ohne Befund'), fn($d) => $d['labor'] ?? ''],
        'drogen'   => ['Drogen- und Alkoholscreening', 'negativ', $all, $sel('drogen', 'negativ'), fn($d) => $d['drogen'] ?? ''],
        'romberg'  => ['Gleichgewicht (Romberg, Unterberger)', 'unauffällig', ['g41', 'hems'], $sel('romberg', 'unauffällig'), fn($d) => $d['romberg'] ?? ''],
        'zahn'     => ['Zahnstatus', 'Klasse 1 oder 2', $all, fn($d) => ($d['zahn'] ?? '') === '' ? null : in_array($d['zahn'], ['1', '2'], true), fn($d) => ($d['zahn'] ?? '') !== '' ? 'Klasse ' . $d['zahn'] : ''],
        'lauf'     => ['3000-m-Lauf', '≤ 13:30 min', ['allg', 'g41'], fn($d) => ($s = sv_secs($d, 'lauf')) === null ? null : $s <= 810, fn($d) => $d['lauf'] ?? ''],
        'liegest'  => ['Liegestütze in 2 min', '≥ 50', ['allg'], fn($d) => ($v = sv_num($d, 'liegestuetz')) === null ? null : $v >= 50, fn($d) => $d['liegestuetz'] ?? ''],
        'situps'   => ['Sit-ups in 2 min', '≥ 60', ['allg'], fn($d) => ($v = sv_num($d, 'situps')) === null ? null : $v >= 60, fn($d) => $d['situps'] ?? ''],
        'klimmz'   => ['Klimmzüge', '≥ 8', ['allg', 'g41', 'hems'], fn($d) => ($v = sv_num($d, 'klimmzug')) === null ? null : $v >= 8, fn($d) => $d['klimmzug'] ?? ''],
        'marsch'   => ['Gepäckmarsch 12 km mit 20 kg', '≤ 2:00 h', ['allg', 'g41'], fn($d) => ($s = sv_secs($d, 'marsch', true)) === null ? null : $s <= 7200, fn($d) => $d['marsch'] ?? ''],
        'schwimm'  => ['Schwimmen 500 m', '≤ 12:30 min', ['wasser', 'g31', 'hems'], fn($d) => ($s = sv_secs($d, 'schwimmen')) === null ? null : $s <= 750, fn($d) => $d['schwimmen'] ?? ''],
        'strecke'  => ['Streckentauchen', '≥ 25 m', ['wasser', 'g31'], fn($d) => ($v = sv_num($d, 'streckentauchen')) === null ? null : $v >= 25, fn($d) => ($d['streckentauchen'] ?? '') !== '' ? $d['streckentauchen'] . ' m' : ''],
        'treten'   => ['15 min Wassertreten in Einsatzkleidung', 'bestanden', ['wasser', 'g31'], $sel('wassertreten', 'bestanden'), fn($d) => $d['wassertreten'] ?? ''],
        'kammer'   => ['Druckkammertest bis 5 bar (40 m)', 'bestanden', ['g31'], $sel('druckkammer', 'bestanden'), fn($d) => $d['druckkammer'] ?? ''],
        'asstrecke' => ['Belastungsübung Atemschutz (Übungsstrecke)', 'bestanden', ['g263'], $sel('atemschutzstrecke', 'bestanden'), fn($d) => $d['atemschutzstrecke'] ?? ''],
        'steig'    => ['Höhen- und Steigtest (Drehleiter, Seil, Winde)', 'bestanden', ['g41', 'hems'], $sel('steigtest', 'bestanden'), fn($d) => $d['steigtest'] ?? ''],
        'huet'     => ['HUET – Unterwasser-Notausstieg Hubschrauber', 'bestanden', ['hems', 'pilot'], $sel('huet', 'bestanden'), fn($d) => $d['huet'] ?? ''],
        'psych'    => ['Psychologische Eignung (Stress, Enge, Höhe)', 'unauffällig', $all, $sel('psych', 'unauffällig'), fn($d) => $d['psych'] ?? ''],
    ];
}

/** Abgleich mit dem Standard für die gewählten Verwendungen: Zeilen [Bezeichnung, Norm, Wert, true/false/null]. */
function sv_check(array $d, array $kinds): array
{
    $out = [];
    foreach (sv_requirements() as $key => [$label, $norm, $for, $check, $value]) {
        if (!$kinds || array_intersect($kinds, $for)) {
            $out[$key] = [$label, $norm, (string) $value($d), $check($d), array_values(array_intersect($for, $kinds ?: $for))];
        }
    }
    return $out;
}

/** Formular des Untersuchungsbogens (beim Rettungsdienst). */
function sv_document_type(): array
{
    $t = fn(string $n, string $l, string $w = 'third', array $o = []) => ['name' => $n, 'label' => $l, 'type' => 'text', 'width' => $w] + $o;
    $n = fn(string $n, string $l, string $w = 'sixth') => ['name' => $n, 'label' => $l, 'type' => 'number', 'width' => $w];
    $s = fn(string $n, string $l, array $opts, string $w = 'third') => ['name' => $n, 'label' => $l, 'type' => 'select', 'width' => $w, 'options' => ['' => '–'] + array_combine($opts, $opts)];
    $h = fn(string $l) => ['type' => 'heading', 'label' => $l];
    $norm = ['unauffällig', 'auffällig', 'nicht durchgeführt'];
    $pass = ['bestanden', 'nicht bestanden', 'nicht durchgeführt'];
    $kindSel = fn(string $k) => ['name' => 'v_' . $k, 'label' => FITNESS_KINDS[$k][0], 'type' => 'select', 'width' => 'half',
        'options' => ['' => 'nicht beurteilt', 'geeignet' => 'tauglich', 'bedingt' => 'tauglich mit Einschränkungen', 'befristet' => 'vorübergehend nicht tauglich', 'nicht' => 'nicht tauglich']];
    $fields = [
        $h('Anlass'),
        $s('anlass', 'Anlass', ['Einstellungsuntersuchung', 'Wiederholungsuntersuchung', 'Nachuntersuchung nach Erkrankung / Verletzung', 'Anlassbezogen (z. B. nach Tauch- oder Absturzunfall)']),
        ['name' => 'exam_date', 'label' => 'Untersucht am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'third'],
        $t('dienststelle', 'Dienststelle (leer = automatisch)', 'third', ['placeholder' => 'z. B. Leitung TAS']),
        ['name' => 'verwendungen', 'label' => 'Beantragte Verwendungen', 'type' => 'checkboxes', 'options' => fitness_kind_options()],
        $t('standard', 'Untersuchungsstandard (leer = Standard der Dienststelle)', 'full', ['placeholder' => SV_STANDARD_DEFAULT]),
        $h('Anamnese (Selbstauskunft, ärztlich erhoben)'),
        ['name' => 'anamnese', 'label' => 'Vorerkrankungen', 'type' => 'checkboxes', 'options' => array_combine($a = ['Herz-Kreislauf-Erkrankung', 'Asthma / Lungenerkrankung',
            'Krampfanfälle / Bewusstseinsstörungen', 'Diabetes mellitus', 'Psychische Erkrankung', 'Schädel-Hirn-Trauma', 'Ohren / Trommelfell / Nasennebenhöhlen',
            'Dekompressionsunfall', 'Wirbelsäule / Bandscheibe', 'Gelenkverletzungen / Operationen', 'Höhenangst / Platzangst', 'Dauermedikation', 'Nikotin', 'Alkohol / Drogen'], $a)],
        ['name' => 'anamnese_text', 'label' => 'Erläuterungen, Medikamente, frühere Untersuchungen', 'type' => 'textarea', 'rows' => 2],
        $h('Körperliche Untersuchung'),
        $n('groesse', 'Größe (cm)'), $n('gewicht', 'Gewicht (kg)'), $n('bauch', 'Bauchumfang (cm)'), $n('koerperfett', 'Körperfett (%)'),
        $n('rr_sys', 'RR systolisch'), $n('rr_dia', 'RR diastolisch'), $n('puls', 'Ruhepuls /min'), $n('spo2', 'SpO₂ %'),
        ['name' => 'organe_auffaellig', 'label' => 'Auffällige Organsysteme (nicht angekreuzt = ohne Befund)', 'type' => 'checkboxes', 'options' => array_combine($o = ['Kopf / Hals',
            'HNO', 'Herz', 'Lunge', 'Abdomen', 'Haut', 'Wirbelsäule', 'Obere Extremitäten', 'Untere Extremitäten', 'Neurologie (Reflexe, Koordination)'], $o)],
        $s('romberg', 'Gleichgewicht (Romberg, Unterberger)', $norm), $s('zahn', 'Zahnstatus (Klasse)', ['1', '2', '3', '4']),
        $h('Augen'),
        $n('visus_sc_r', 'Visus ohne Korr. R'), $n('visus_sc_l', 'Visus ohne Korr. L'), $n('visus_cc_r', 'Visus mit Korr. R'), $n('visus_cc_l', 'Visus mit Korr. L'),
        $s('farbsinn', 'Farbsinn (Ishihara)', ['normal', 'gestört', 'nicht durchgeführt'], 'sixth'), $s('stereo', 'Räumliches Sehen', ['normal', 'gestört'], 'sixth'),
        $s('gesichtsfeld', 'Gesichtsfeld', ['normal', 'eingeschränkt'], 'sixth'),
        $h('Ohren – Tonaudiogramm (Hörverlust in dB)'),
    ];
    foreach (['r' => 'R', 'l' => 'L'] as $side => $lbl) {
        foreach ([500, 1000, 2000, 3000, 4000, 6000] as $f) {
            $fields[] = $n("h{$side}_$f", "$lbl " . ($f >= 1000 ? ($f / 1000) . ' kHz' : "$f Hz"));
        }
    }
    return [
        'title'    => 'Untersuchungsbogen Sonderverwendungsfähigkeit',
        'short'    => 'SV-Untersuchung',
        'code'     => 'SV',
        'category' => 'Behörden & Gutachten',
        'role'     => 'doctor',
        'patient'  => 'required',
        'template' => 'sv_untersuchung',
        'valid'    => 'valid_until',
        'fields'   => array_merge($fields, [
            $s('druckausgleich', 'Trommelfell / Druckausgleich (Valsalva)', ['beidseits frei', 'eingeschränkt', 'nicht möglich'], 'half'),
            $h('Apparative Diagnostik'),
            $s('ekg', 'Ruhe-EKG', $norm), $n('ergo_watt', 'Ergometrie max. (Watt)', 'third'), $s('ergo_befund', 'Ergometrie Befund', $norm),
            $n('fvc', 'FVC (% Soll)', 'third'), $n('fev1', 'FEV1 (% Soll)', 'third'), $n('tiffeneau', 'FEV1/FVC (%)', 'third'),
            $s('roentgen', 'Röntgen Thorax', ['ohne Befund', 'auffällig', 'nicht durchgeführt']), $s('labor', 'Labor', ['ohne Befund', 'auffällig', 'nicht durchgeführt']),
            $s('drogen', 'Drogen- und Alkoholscreening', ['negativ', 'positiv', 'nicht durchgeführt']),
            $h('Leistungstests (Zeiten als mm:ss, Marsch als h:mm)'),
            $t('lauf', '3000-m-Lauf', 'sixth', ['placeholder' => '12:45']), $n('liegestuetz', 'Liegestütze 2 min'), $n('situps', 'Sit-ups 2 min'), $n('klimmzug', 'Klimmzüge'),
            $t('marsch', 'Gepäckmarsch 12 km/20 kg', 'sixth', ['placeholder' => '1:52']), $t('schwimmen', 'Schwimmen 500 m', 'sixth', ['placeholder' => '11:20']),
            $n('streckentauchen', 'Streckentauchen (m)'), $s('wassertreten', '15 min Wassertreten', $pass, 'sixth'), $s('druckkammer', 'Druckkammer 5 bar', $pass, 'sixth'),
            $s('atemschutzstrecke', 'Atemschutz-Übungsstrecke', $pass, 'sixth'), $s('steigtest', 'Höhen-/Steigtest', $pass, 'sixth'), $s('huet', 'HUET (Notausstieg)', $pass, 'sixth'),
            $h('Psychologische Eignung'),
            $s('psych', 'Ergebnis', $norm), ['name' => 'psych_text', 'label' => 'Bemerkung (vertraulich)', 'type' => 'text', 'width' => 'two-thirds'],
            $h('Beurteilung – Profil (PULHES)'),
        ], array_map(fn($k) => ['name' => 'pulhes_' . $k, 'label' => SV_PULHES[$k][0], 'type' => 'select', 'width' => 'third', 'options' => ['' => '–'] + SV_GRADES], array_keys(SV_PULHES)), [
            $h('Beurteilung je Verwendung (geht an die Dienststelle)'),
        ], array_map($kindSel, array_keys(FITNESS_KINDS)), [
            ['name' => 'gesamt', 'label' => 'Gesamturteil', 'type' => 'radio', 'required' => true, 'options' => [
                'geeignet' => 'Sonderverwendungsfähig', 'bedingt' => 'Eingeschränkt verwendungsfähig', 'befristet' => 'Vorübergehend nicht verwendungsfähig', 'nicht' => 'Nicht verwendungsfähig']],
            ['name' => 'einschraenkungen', 'label' => 'Einschränkungen / Auflagen (funktional, ohne Diagnosen – geht an die Dienststelle)', 'type' => 'textarea', 'rows' => 2,
                'placeholder' => 'z. B. Tauchtiefe max. 20 m; nur mit Korrekturbrille; kein Windeneinsatz bis Nachuntersuchung'],
            ['name' => 'bemerkung', 'label' => 'Ärztliche Beurteilung, Diagnosen (vertraulich – verbleibt beim untersuchenden Dienst)', 'type' => 'textarea', 'rows' => 3],
            ['name' => 'valid_until', 'label' => 'Gültig bis (leer = kürzeste Regelfrist)', 'type' => 'date', 'width' => 'half'],
            ['name' => 'nachuntersuchung', 'label' => 'Nachuntersuchung spätestens', 'type' => 'date', 'width' => 'half'],
        ]),
    ];
}

/** Standardbezeichnung der Dienststelle (Einstellung der Fraktion im aktuellen Kontext). */
function fitness_standard_name(): string
{
    return trim((string) setting('fitness_standard', '')) ?: SV_STANDARD_DEFAULT;
}

/** Aus dem Untersuchungsbogen die Bescheinigung (ohne Befunde) für die Dienststelle ausstellen. */
function sv_issue_certificate(int $docId, array $data, ?array $patient, array $issuer, string $liveSignature): ?int
{
    $ver = [];
    foreach (array_keys(FITNESS_KINDS) as $k) {
        if (array_key_exists((string) ($data['v_' . $k] ?? ''), FITNESS_RESULTS)) {
            $ver[$k] = (string) $data['v_' . $k];
        }
    }
    if (!$ver || !($type = doc_type('einsatztauglichkeit'))) {
        return null;
    }
    $order = array_keys(FITNESS_RESULTS);
    $gesamt = array_key_exists((string) ($data['gesamt'] ?? ''), FITNESS_RESULTS) ? (string) $data['gesamt']
        : $order[max(array_map(fn($r) => array_search($r, $order, true), $ver))];
    $exam = (string) ($data['exam_date'] ?? date('Y-m-d'));
    $fit = array_keys(array_filter($ver, fn($r) => in_array($r, ['geeignet', 'bedingt'], true)));
    $valid = (string) ($data['valid_until'] ?? '') ?: ($fit ? min(array_map(fn($k) => fitness_default_until($k, $exam), $fit)) : '');
    $checks = sv_check($data, array_keys($ver));
    $cert = [
        'art' => array_key_first($ver), 'verwendungen' => $ver, 'result' => $gesamt, 'exam_date' => $exam, 'valid_until' => $valid,
        'nachuntersuchung' => (string) ($data['nachuntersuchung'] ?? ''), 'conditions' => (string) ($data['einschraenkungen'] ?? ''),
        'profil' => implode(' ', array_map(fn($k) => strtoupper($k) . (($data['pulhes_' . $k] ?? '') !== '' ? $data['pulhes_' . $k] : '–'), array_keys(SV_PULHES))),
        'dienststelle' => (string) ($data['dienststelle'] ?? ''), 'standard' => (string) ($data['standard'] ?? ''), 'anlass' => (string) ($data['anlass'] ?? ''),
        'umfang' => array_values(array_unique(array_map(fn($k) => SV_SCOPE[$k] ?? $k, array_keys(array_filter($checks, fn($c) => $c[3] !== null))))),
        'protokoll' => (string) scalar('SELECT number FROM documents WHERE id = ?', [$docId]),
    ];
    $certId = issue_document($type, $patient, null, $cert, $issuer, null, $liveSignature);
    $data['_certificate'] = $certId;
    q('UPDATE documents SET data = ? WHERE id = ?', [json_encode($data, JSON_UNESCAPED_UNICODE), $docId]);
    return $certId;
}
