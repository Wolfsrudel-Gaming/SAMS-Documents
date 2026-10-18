<?php
/**
 * Anbindung an den Spielserver (ESX): Einstellungen für die Resource, Rechnungen ins Spiel, Aktenabruf am Spieler.
 * Alles Serverabhängige (Society-Konten, Jobs, Befehle, Funktionsschalter) wird im Websystem unter
 * „Einstellungen → Spielserver“ gepflegt; die Resource holt es über api.php?action=game_config.
 */

/** Vorgaben je Fraktionsart: ESX-Jobnamen (erster = Society-Konto). */
const GAME_DEFAULT_JOBS = [
    'medical' => ['ambulance'], 'police' => ['police', 'sheriff'], 'justice' => ['doj'], 'fire' => ['fire'], 'government' => ['gov'], 'rescue' => ['tas'],
];

const GAME_BILL_STATUS = [
    'pending' => ['grey', 'wird gesendet'], 'sent' => ['blue', 'im Spiel offen'], 'paid' => ['green', 'im Spiel bezahlt'],
    'cancel' => ['grey', 'wird zurückgezogen'], 'cancelled' => ['grey', 'zurückgezogen'], 'failed' => ['red', 'fehlgeschlagen'],
];

/** Systemweite Einstellungen der Resource (gelten für alle Fraktionen). */
function game_global(): array
{
    $cmd = fn(string $k, string $d) => preg_replace('/[^a-z0-9_]/i', '', (string) setting($k, $d));
    return [
        'framework' => setting('ingame_framework', 'esx') === 'none' ? 'none' : 'esx',
        'commands' => [
            'tablet' => $cmd('ingame_cmd_tablet', 'tablet'), 'duty' => $cmd('duty_command', 'dienst'),
            'akte' => $cmd('ingame_cmd_akte', 'akte'), 'plate' => $cmd('ingame_cmd_plate', 'kennzeichen'),
        ],
        'billing_table' => preg_replace('/[^a-z0-9_]/i', '', (string) setting('ingame_billing_table', 'billing')) ?: 'billing',
        'akte_distance' => max(1.0, min(10.0, (float) setting('ingame_akte_distance', '3'))),
        'bill_interval' => max(15, min(600, (int) setting('ingame_bill_interval', '30'))),
        // Sky Ambulance (sky_ambulancejob) und Rechnungssystem (esx_billing oder Sky Billing aus sky_jobs_base)
        'billing_provider' => setting('ingame_billing_provider', 'esx_billing') === 'sky' ? 'sky' : 'esx_billing',
        'sky' => setting('ingame_sky', '0') === '1',
        'sky_death' => setting('ingame_sky', '0') === '1' && setting('ingame_sky_death', '1') === '1',
        'sky_meds' => setting('ingame_sky', '0') === '1' && setting('ingame_sky_meds', '1') === '1',
        // Leitstelle tetrascript (tetra-core): FMS-Status und Einsätze über die Brücke in sams_tablet
        'tetra' => setting('ingame_tetra', '0') === '1',
        'tetra_poll' => max(10, min(300, (int) setting('ingame_tetra_poll', '20'))),
    ];
}

/** Einstellungen einer Fraktion für den Spielserver. */
function game_faction(?int $factionId = null): array
{
    $factionId ??= fid();
    $prev = fid();
    set_faction_context($factionId);
    $jobs = array_values(array_filter(array_map(fn($j) => preg_replace('/[^a-z0-9_]/i', '', trim($j)), explode(',', (string) setting('game_jobs', '')))));
    $jobs = $jobs ?: (GAME_DEFAULT_JOBS[faction_kind($factionId)] ?? []);
    $out = [
        'jobs' => $jobs,
        'society' => preg_replace('/[^a-z0-9_]/i', '', (string) setting('game_society', '')) ?: ($jobs ? 'society_' . $jobs[0] : ''),
        'billing' => setting('game_billing', '1') === '1' && module_enabled('billing', $factionId),
        'akte' => setting('game_akte', '1') === '1' && module_enabled('records', $factionId),
        'akte_duty' => setting('game_akte_duty', '1') === '1',
        'akte_job' => setting('game_akte_job', '0') === '1',
        'tetra_org' => function_exists('dispatch_org') ? dispatch_org($factionId) : '',
    ];
    set_faction_context($prev);
    return $out;
}

/** Konfiguration für die Resource (keine Geheimnisse – wird an die Spieler weitergegeben). */
function game_config(): array
{
    $factions = [];
    foreach (factions() as $f) {
        $factions[] = ['id' => (int) $f['id'], 'short' => (string) $f['short'], 'name' => (string) $f['name'], 'kind' => faction_kind((int) $f['id'])] + game_faction((int) $f['id']);
    }
    return ['ok' => true] + game_global() + ['factions' => $factions];
}

// ------------------------------------------------------------ Rechnungen

/** Bürger-ID (ESX identifier) des Rechnungsempfängers. */
function game_bill_citizen(array $doc): string
{
    $p = !empty($doc['patient_id']) ? one('SELECT citizen_id FROM patients WHERE id = ?', [$doc['patient_id']]) : null;
    $snap = is_array($doc['patient_snapshot']) ? $doc['patient_snapshot'] : (json_decode((string) $doc['patient_snapshot'], true) ?: []);
    return trim((string) (($p['citizen_id'] ?? '') ?: ($snap['citizen_id'] ?? '')));
}

/** Aktueller Spiel-Eintrag zu einer Rechnung (oder null). */
function game_bill_for(int $documentId): ?array
{
    return one('SELECT * FROM game_bills WHERE document_id = ? ORDER BY id DESC LIMIT 1', [$documentId]);
}

/** Dokumentarten, die als Forderung ins Spiel gehen: Art => Feld mit dem Betrag (null = Rechnungsbetrag). */
const GAME_BILL_TYPES = ['rechnung' => null, 'doj_strafbefehl' => 'betrag', 'doj_urteil' => 'geldstrafe'];

/** Betrag aus einem Freitextfeld („$ 5.000“, „2500,50“, „1,500.00“). */
function parse_money(string $v): float
{
    $v = preg_replace('/[^0-9.,]/', '', $v);
    if (preg_match('/^\d{1,3}(\.\d{3})+(,\d+)?$/', $v)) {
        $v = str_replace(['.', ','], ['', '.'], $v);
    } elseif (preg_match('/^\d{1,3}(,\d{3})+(\.\d+)?$/', $v)) {
        $v = str_replace(',', '', $v);
    } else {
        $v = str_replace(',', '.', $v);
    }
    return (float) $v;
}

/** Forderung in ganzen Dollar (aufgerundet). */
function game_bill_amount(array $doc): int
{
    $field = GAME_BILL_TYPES[$doc['type']] ?? null;
    if ($field === null) {
        return (int) ceil((int) $doc['amount_cents'] / 100);
    }
    $data = is_array($doc['data']) ? $doc['data'] : (json_decode((string) $doc['data'], true) ?: []);
    return (int) ceil(parse_money((string) ($data[$field] ?? '')));
}

/** Darf der Benutzer diese Forderung ins Spiel senden? Rechnungen: Rechnungsverwaltung; Geldstrafen: auch wer sie ausstellen darf. */
function game_bill_allowed(array $doc): bool
{
    return can('invoices.manage') || ($doc['type'] !== 'rechnung' && can('documents.issue.' . $doc['type']));
}

/** Warum die Rechnung (noch) nicht ins Spiel gesendet werden kann – oder '' wenn es geht. */
function game_bill_problem(array $doc): string
{
    $g = game_faction((int) $doc['faction_id']);
    $last = game_bill_for((int) $doc['id']);
    return match (true) {
        !array_key_exists($doc['type'], GAME_BILL_TYPES) || $doc['status'] === 'revoked' => 'Nur für gültige Rechnungen und Geldstrafen.',
        game_global()['framework'] !== 'esx' => 'Die Spielserver-Anbindung ist ausgeschaltet.',
        !$g['billing'] => 'Rechnungen ins Spiel sind für diese Fraktion ausgeschaltet.',
        $g['society'] === '' => 'Kein Society-Konto eingetragen (Einstellungen → Spielserver).',
        $doc['pay_status'] === 'paid' => 'Die Rechnung ist bereits bezahlt.',
        game_bill_amount($doc) <= 0 => $doc['type'] === 'rechnung' ? 'Die Rechnung hat keinen Betrag.' : 'Im Dokument ist keine Geldstrafe eingetragen.',
        game_bill_citizen($doc) === '' => 'Für die Person ist keine Bürger-ID hinterlegt (in der Akte eintragen oder Akte im Spiel mit /akte anlegen).',
        $last && in_array($last['status'], ['pending', 'sent', 'cancel'], true) => 'Die Rechnung liegt bereits im Spiel.',
        default => '',
    };
}

function game_bill_send(array $doc, int $userId): int
{
    $g = game_faction((int) $doc['faction_id']);
    $f = faction_by_id((int) $doc['faction_id']);
    $id = insert('game_bills', [
        'faction_id' => (int) $doc['faction_id'], 'document_id' => (int) $doc['id'], 'citizen_id' => mb_substr(game_bill_citizen($doc), 0, 64),
        'amount' => game_bill_amount($doc), 'society' => $g['society'],
        'label' => mb_substr(trim(($f['short'] ?? '') . ' ' . ($doc['type'] === 'rechnung' ? billing_labels((int) $doc['faction_id'])['title'] : 'Geldstrafe ' . (doc_type($doc['type'])['title'] ?? '')) . ' ' . $doc['number']), 0, 190),
        'status' => 'pending', 'created_by' => $userId, 'created_at' => now(),
    ]);
    audit('invoice.game_send', 'document', (int) $doc['id'], $doc['number'] . ' ins Spiel gesendet (' . $g['society'] . ')');
    return $id;
}

/** Spiel-Rechnung zurückziehen (Storno oder Zahlung außerhalb des Spiels). */
function game_bill_cancel_for(int $documentId): void
{
    q("UPDATE game_bills SET status = 'cancelled', updated_at = ? WHERE document_id = ? AND status = 'pending'", [now(), $documentId]);
    q("UPDATE game_bills SET status = 'cancel', updated_at = ? WHERE document_id = ? AND status = 'sent'", [now(), $documentId]);
}

function game_bill_badge(array $b): string
{
    [$c, $l] = GAME_BILL_STATUS[$b['status']] ?? ['grey', $b['status']];
    return '<span class="badge ' . $c . '">' . e($l) . '</span>';
}

/** Abgleich für die Resource: neue, zurückzuziehende und offene Spiel-Rechnungen. */
function game_bills_sync(): array
{
    $pending = all("SELECT g.id, g.citizen_id, g.amount, g.society, g.label, g.faction_id, d.patient_snapshot, d.number FROM game_bills g JOIN documents d ON d.id = g.document_id
        WHERE g.status = 'pending' ORDER BY g.id LIMIT 50");
    foreach ($pending as $i => $b) {
        // Für Sky Billing: Job (Society) der Fraktion und Name des Empfängers
        $snap = json_decode((string) $b['patient_snapshot'], true) ?: [];
        $pending[$i]['job'] = game_faction((int) $b['faction_id'])['jobs'][0] ?? '';
        $pending[$i]['name'] = $snap ? patient_name($snap) : '';
        $pending[$i]['reference'] = 'SAMS-' . $b['number'] . '-' . $b['id'];
        unset($pending[$i]['patient_snapshot'], $pending[$i]['faction_id'], $pending[$i]['number']);
    }
    $cancel = all("SELECT id, esx_id, ext_ref FROM game_bills WHERE status = 'cancel' ORDER BY id LIMIT 50");
    $open = all("SELECT id, esx_id, ext_ref FROM game_bills WHERE status = 'sent' ORDER BY id LIMIT 500");
    $int = fn(array $rows, array $keys) => array_map(function ($r) use ($keys) {
        foreach ($keys as $k) {
            $r[$k] = (int) $r[$k];
        }
        return $r;
    }, $rows);
    return ['ok' => true, 'table' => game_global()['billing_table'], 'provider' => game_global()['billing_provider'],
        'pending' => $int($pending, ['id', 'amount']), 'cancel' => $int($cancel, ['id', 'esx_id']), 'open' => $int($open, ['id', 'esx_id'])];
}

/** Ergebnis der Resource verarbeiten: sent, failed, paid, cancelled. */
function game_bill_result(int $id, string $result, int $esxId = 0, string $error = '', string $extRef = ''): array
{
    $b = one('SELECT * FROM game_bills WHERE id = ?', [$id]);
    if (!$b) {
        return ['ok' => false, 'error' => 'Spiel-Rechnung nicht gefunden.'];
    }
    $doc = one('SELECT * FROM documents WHERE id = ?', [$b['document_id']]);
    set_faction_context((int) $b['faction_id']);
    // cancelled aus sent: Rechnung wurde im Spiel (z. B. in Sky Billing) storniert
    $allowed = ['sent' => ['pending'], 'failed' => ['pending'], 'paid' => ['sent', 'cancel'], 'cancelled' => ['cancel', 'sent']];
    if (!isset($allowed[$result]) || !in_array($b['status'], $allowed[$result], true)) {
        return ['ok' => false, 'error' => 'Status passt nicht (' . $b['status'] . ' → ' . $result . ').'];
    }
    $row = ['status' => $result, 'updated_at' => now()];
    if ($result === 'sent') {
        $row += ['esx_id' => $esxId ?: null, 'ext_ref' => mb_substr($extRef, 0, 64), 'sent_at' => now()];
    } elseif ($result === 'failed') {
        $row['error'] = mb_substr($error ?: 'unbekannter Fehler', 0, 190);
    } elseif ($result === 'paid') {
        $row['paid_at'] = now();
    }
    update('game_bills', $id, $row);
    if ($result === 'paid' && $doc && $doc['pay_status'] !== 'paid' && $doc['status'] !== 'revoked') {
        update('documents', (int) $doc['id'], ['pay_status' => 'paid', 'paid_at' => now(), 'pay_note' => 'im Spiel bezahlt']);
        audit_as(null, 'invoice.status', 'document', (int) $doc['id'], $doc['number'] . ' → im Spiel bezahlt');
        notify_users_with('invoices.manage', 'billing', billing_labels()['title'] . ' ' . $doc['number'] . ' im Spiel bezahlt', url('document', ['id' => $doc['id']]));
    } elseif ($result === 'failed' && $doc) {
        audit_as(null, 'invoice.game_failed', 'document', (int) $doc['id'], $doc['number'] . ': ' . $row['error']);
    }
    return ['ok' => true];
}

// --------------------------------------------------------- Akte am Spieler

/** Spielerdaten aus ESX in Aktenfelder umwandeln. */
function game_player_fields(array $in): array
{
    $dob = trim((string) ($in['dob'] ?? ''));
    $date = '';
    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})/', $dob, $m)) {
        $date = sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
    } elseif (preg_match('#^(\d{1,2})[./-](\d{1,2})[./-](\d{4})$#', $dob, $m)) {
        // esx_identity speichert standardmäßig TT/MM/JJJJ
        $date = sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
    }
    if ($date !== '' && !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
        $date = '';
    }
    $sex = strtolower(trim((string) ($in['sex'] ?? '')));
    return [
        'citizen_id' => mb_substr(trim((string) ($in['citizen_id'] ?? '')), 0, 64),
        'first_name' => mb_substr(trim((string) ($in['first_name'] ?? '')), 0, 100),
        'last_name' => mb_substr(trim((string) ($in['last_name'] ?? '')), 0, 100),
        'dob' => $date,
        'gender' => in_array($sex, ['m', 'male', 'mann', 'männlich'], true) ? 'm' : (in_array($sex, ['f', 'w', 'female', 'frau', 'weiblich'], true) ? 'w' : ''),
    ];
}

/** Prüft, ob der Benutzer im Spiel Akten abrufen darf. Gibt eine Fehlermeldung oder '' zurück. */
function game_record_problem(array $u): string
{
    $g = game_faction((int) $u['faction_id']);
    $perms = user_permissions($u);
    return match (true) {
        !$g['akte'] => 'Der Aktenabruf im Spiel ist für deine Fraktion ausgeschaltet.',
        !in_array('patients.view', $perms, true) => 'Dein Rang darf keine Akten ansehen.',
        $g['akte_duty'] && !duty_open_session((int) $u['id']) => 'Aktenabruf nur im Dienst.',
        default => '',
    };
}

function game_record_find(string $citizenId): ?array
{
    return $citizenId === '' ? null : one('SELECT * FROM patients WHERE citizen_id = ? AND merged_into IS NULL AND ' . fw() . ' ORDER BY archived_at IS NOT NULL, id LIMIT 1', [$citizenId]);
}

/** Kurzfassung der Akte für das Kontextmenü im Spiel. */
function game_record_summary(array $p): array
{
    $lines = [];
    $add = function (string $label, $value) use (&$lines) {
        $value = trim((string) $value);
        if ($value !== '') {
            $lines[] = ['label' => $label, 'value' => mb_strimwidth($value, 0, 160, '…')];
        }
    };
    $add('Geburtsdatum', $p['dob'] ? fmt_date($p['dob']) : '');
    if (faction_kind() === 'medical') {
        $add('Blutgruppe', $p['blood_type']);
        $add('Allergien', $p['allergies']);
        $add('Vorerkrankungen', $p['conditions']);
        $add('Medikation', $p['medication']);
        $add('Patientenverfügung', $p['living_will'] ? 'liegt vor' : '');
        $add('Organspende', $p['organ_donor']);
        $add('Notfallkontakt', $p['emergency_contact']);
    }
    $add('Hinweise', $p['flags']);
    if ($p['deceased_at']) {
        $add('Verstorben', fmt_date($p['deceased_at']));
    }
    if ($p['archived_at']) {
        $add('Status', 'archiviert');
    }
    $open = (int) scalar("SELECT COUNT(*) FROM cases WHERE patient_id = ? AND status = 'open'", [$p['id']]);
    $add(term('cases'), $open ? $open . ' offen' : '');
    if (in_array(faction_kind(), ['police', 'justice'], true) && $p['citizen_id'] !== '') {
        foreach (licenses_for_citizen((string) $p['citizen_id']) as $l) {
            $add($l['type'], (LICENSE_STATUS[$l['state']][1] ?? $l['state']) . ($l['expires_on'] ? ' (bis ' . fmt_date($l['expires_on']) . ')' : ''));
        }
        $reg = criminal_record((string) $p['citizen_id']);
        $add('Strafregister', $reg ? count($reg) . ' Einträge, zuletzt ' . $reg[0]['kind'] . ' am ' . fmt_date($reg[0]['date']) : '');
    }
    if (module_enabled('wanted') && $p['citizen_id'] !== '' && ($hits = wanted_lookup((string) $p['citizen_id']))) {
        $add('Fahndung', count($hits) . ' offen' . (array_filter($hits, fn($w) => $w['source'] === 'warrant') ? ' (Haftbefehl)' : ''));
    }
    return $lines;
}

/** Leere Stammdaten der Akte mit den Spielerdaten ergänzen (nichts überschreiben). */
function game_record_fill(array $p, array $fields): void
{
    $row = [];
    foreach (['first_name', 'last_name', 'dob', 'gender'] as $k) {
        if (trim((string) $p[$k]) === '' && $fields[$k] !== '') {
            $row[$k] = $fields[$k];
        }
    }
    if ($row) {
        update('patients', (int) $p['id'], $row + ['updated_at' => now()]);
    }
}
