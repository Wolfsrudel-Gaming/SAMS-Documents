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
 * Nach dem Ausstellen einer Einsatztauglichkeits-Bescheinigung: Befunde in einen vertraulichen Akteneintrag
 * verschieben (nicht im Dokument), Ergebnis an die Dienststelle der untersuchten Person, offene Anforderung erledigen.
 */
function fitness_document_issued(int $docId, string $type, array $data, ?array $patient, array $issuer): void
{
    if ($type !== 'einsatztauglichkeit') {
        return;
    }
    $kind = array_key_exists((string) ($data['art'] ?? ''), FITNESS_KINDS) ? (string) $data['art'] : 'allg';
    $examined = (string) ($data['exam_date'] ?? date('Y-m-d'));
    $changed = false;
    if (empty($data['valid_until']) && in_array($data['result'] ?? '', ['geeignet', 'bedingt'], true)) {
        $data['valid_until'] = fitness_default_until($kind, $examined);
        q('UPDATE documents SET valid_until = ? WHERE id = ?', [$data['valid_until'], $docId]);
        $changed = true;
    }
    if (trim((string) ($data['befund'] ?? '')) !== '' && $patient) {
        insert('entries', ['faction_id' => (int) $patient['faction_id'], 'patient_id' => (int) $patient['id'], 'type' => 'Untersuchung',
            'title' => 'Befunde zur Einsatztauglichkeit (' . FITNESS_KINDS[$kind][1] . ')', 'content' => (string) $data['befund'], 'confidential' => 1,
            'occurred_at' => now(), 'created_by' => (int) $issuer['id'], 'created_at' => now(), 'updated_at' => now()]);
        $changed = true;
    }
    unset($data['befund']);
    if ($changed || array_key_exists('befund', json_decode((string) scalar('SELECT data FROM documents WHERE id = ?', [$docId]), true) ?: [])) {
        q('UPDATE documents SET data = ? WHERE id = ?', [json_encode($data, JSON_UNESCAPED_UNICODE), $docId]);
    }
    $result = array_key_exists((string) ($data['result'] ?? ''), FITNESS_RESULTS) ? (string) $data['result'] : 'nicht';
    $valid = in_array($result, ['geeignet', 'bedingt'], true) ? (string) ($data['valid_until'] ?? '') : '';
    $doctor = user_display($issuer);
    foreach (fitness_employees($patient, $data) as $u) {
        $fid = (int) $u['faction_id'];
        fitness_record($u, ['kind' => $kind, 'result' => $result, 'restrictions' => mb_substr((string) ($data['conditions'] ?? ''), 0, 2000),
            'examined_on' => $examined, 'valid_until' => $valid, 'examiner' => mb_substr($doctor . ' (' . org('org_short') . ')', 0, 190),
            'document_id' => $docId, 'source_faction_id' => fid(), 'recorded_by' => (int) $issuer['id']]);
        // Die Dienststelle darf die Bescheinigung (ohne Befunde) einsehen
        $f = faction_by_id($fid);
        insert('document_shares', ['document_id' => $docId, 'kind' => 'faction', 'target_faction_id' => $fid, 'agency' => (string) ($f['code'] ?? ''),
            'recipient' => (string) ($f['name'] ?? ''), 'purpose' => 'Einsatztauglichkeit (Arbeitgeber)', 'expires_at' => $valid !== '' ? $valid . ' 23:59:59' : date('Y-m-d H:i:s', time() + 90 * 86400),
            'created_by' => (int) $issuer['id'], 'created_at' => now()]);
        q("UPDATE agency_requests SET status = 'done', answer = ?, answered_by = ?, answered_at = ? WHERE faction_id = ? AND to_faction_id = ? AND status IN ('open', 'in_progress')
            AND reference = ?", ['Bescheinigung ausgestellt: ' . scalar('SELECT number FROM documents WHERE id = ?', [$docId]), $issuer['id'], now(), $fid, fid(), 'fitness:' . $u['id'] . ':' . $kind]);
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
