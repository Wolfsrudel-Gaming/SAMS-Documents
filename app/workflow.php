<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Dokumenten-Workflow: Einstellungen je Dokumentart, Ausstellen, Entwürfe,
 * Freigaben (Gegenzeichnung) und Nachträge.
 */

const DRAFT_STATUS = [
    'draft'    => ['grey', 'Entwurf'],
    'pending'  => ['orange', 'wartet auf Freigabe'],
    'rejected' => ['red', 'zurückgewiesen'],
];

/**
 * Einstellungen je Dokumentart (Einstellung doc_type_config, JSON):
 *   approval_below  Ränge unterhalb dieser Stufe brauchen eine Freigabe (0 = nie)
 *   approval_by     Mindest-Rangstufe für die Freigabe
 *   mode            full | quick (Schnellmodus zeigt nur Pflichtfelder)
 */
function doc_type_config(string $type): array
{
    static $all = null;
    if ($all === null) {
        $all = json_decode((string) setting('doc_type_config', '{}'), true) ?: [];
    }
    $c = is_array($all[$type] ?? null) ? $all[$type] : [];
    return [
        'approval_below' => max(0, (int) ($c['approval_below'] ?? 0)),
        'approval_by' => max(0, (int) ($c['approval_by'] ?? 70)),
        'mode' => ($c['mode'] ?? 'full') === 'quick' ? 'quick' : 'full',
    ];
}

function needs_approval(string $type, array $user): bool
{
    $cfg = doc_type_config($type);
    return $cfg['approval_below'] > 0 && user_level($user) < $cfg['approval_below'];
}

/** Darf der angemeldete Benutzer diesen eingereichten Entwurf freigeben? */
function can_approve(array $draft): bool
{
    $me = current_user();
    return $draft['status'] === 'pending' && can('documents.approve')
        && (int) $draft['submitted_by'] !== (int) $me['id']
        && user_level($me) >= doc_type_config($draft['type'])['approval_by'];
}

function draft_editable(array $draft): bool
{
    $me = current_user();
    return $draft['status'] !== 'pending' && (int) $draft['owner_id'] === (int) $me['id'];
}

function draft_visible(array $draft): bool
{
    $me = current_user();
    return (int) $draft['owner_id'] === (int) $me['id'] || (int) $draft['created_by'] === (int) $me['id']
        || can('documents.drafts_all') || can_approve($draft);
}

function draft_status_badge(array $draft): string
{
    [$c, $l] = DRAFT_STATUS[$draft['status']] ?? ['grey', $draft['status']];
    return '<span class="badge ' . $c . '">' . e($l) . '</span>';
}

/** Anzahl offener Aufgaben für die Navigation (eigene Entwürfe + Freigaben). */
function draft_inbox_count(): int
{
    $me = current_user();
    if (!$me) {
        return 0;
    }
    $n = (int) scalar("SELECT COUNT(*) FROM document_drafts WHERE owner_id = ? AND status IN ('draft', 'rejected') AND " . fw(), [$me['id']]);
    if (can('documents.approve')) {
        foreach (all("SELECT * FROM document_drafts WHERE status = 'pending' AND submitted_by <> ? AND " . fw(), [$me['id']]) as $d) {
            $n += can_approve($d) ? 1 : 0;
        }
    }
    return $n;
}

/** Angaben eines Personalkontos für Personaldokumente (Dienstausweis, Urkunde …). */
function employee_snapshot(array $u): array
{
    $deps = all('SELECT d.name FROM departments d JOIN user_departments ud ON ud.department_id = d.id WHERE ud.user_id = ? ORDER BY d.sort, d.name', [$u['id']]);
    $quals = all('SELECT q.name FROM qualifications q JOIN user_qualifications uq ON uq.qualification_id = q.id WHERE uq.user_id = ? ORDER BY q.name', [$u['id']]);
    return [
        'id' => (int) $u['id'], 'name' => $u['name'], 'title' => $u['title'], 'badge_number' => $u['badge_number'],
        'doctor_number' => $u['doctor_number'], 'rank_name' => user_rank_name($u),
        'departments' => implode(', ', array_column($deps, 'name')), 'qualifications' => implode(', ', array_column($quals, 'name')),
        'since' => substr((string) $u['created_at'], 0, 10),
    ];
}

/** Personendaten für Listen, Item-Namen und Echtheitsprüfung (Patient oder Mitarbeiter/in). */
function document_person_snapshot(?array $patient, array $data): ?array
{
    if ($patient) {
        return patient_snapshot($patient);
    }
    if (!empty($data['_employee']['name'])) {
        $parts = preg_split('/\s+/', trim((string) $data['_employee']['name']));
        $last = count($parts) > 1 ? array_pop($parts) : '';
        return ['first_name' => implode(' ', $parts), 'last_name' => $last, 'employee' => true];
    }
    return null;
}

/** Live-Unterschrift aus dem Formular prüfen (PNG als Data-URL). */
function clean_signature($raw): string
{
    return is_string($raw) && preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $raw) && strlen($raw) < 400000 ? $raw : '';
}

/**
 * Dokument verbindlich ausstellen. Vergibt Nummer und Prüfcode.
 * $issuer: Benutzer, in dessen Namen ausgestellt wird; $cosigner: freigebende Person (optional).
 */
function issue_document(array $type, ?array $patient, ?array $case, array $data, array $issuer, ?array $cosigner = null, string $liveSignature = ''): int
{
    if (($type['template'] ?? '') === 'custom') {
        $data['_tpl'] = custom_template_snapshot($type); // Vorlage einfrieren
    }
    // Foto-URL (z. B. Dienstausweis): Bild herunterladen und fest im Dokument speichern
    $fieldNames = array_column($type['fields'] ?? [], 'name');
    if (in_array('photo_file', $fieldNames, true) && empty($data['photo_file']) && preg_match('#^https?://#i', (string) ($data['photo'] ?? ''))) {
        $data['photo_file'] = remote_image_data_uri((string) $data['photo']);
    }
    $issuerSnap = issuer_snapshot($issuer);
    if ($liveSignature !== '') {
        $issuerSnap['signature'] = $liveSignature;
        $issuerSnap['signature_live'] = true;
    }
    if (!empty($data['_location']) && ($loc = location_snapshot($data['_location']))) {
        $issuerSnap['location'] = $loc;
    }
    if ($cosigner) {
        $issuerSnap['cosigner'] = issuer_snapshot($cosigner) + ['at' => now()];
    }
    db()->beginTransaction();
    try {
        do {
            $code = random_code(8);
        } while (scalar('SELECT id FROM documents WHERE verify_code = ?', [$code]));
        $validUntil = isset($type['valid']) ? (string) ($data[$type['valid']] ?? '') : '';
        $id = insert('documents', [
            'number' => next_document_number($type),
            'type' => $type['key'],
            'patient_id' => $patient['id'] ?? null,
            'case_id' => $case['id'] ?? null,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'patient_snapshot' => json_encode(document_person_snapshot($patient, $data), JSON_UNESCAPED_UNICODE),
            'issuer_snapshot' => json_encode($issuerSnap, JSON_UNESCAPED_UNICODE),
            'verify_code' => $code,
            'view_token' => bin2hex(random_bytes(20)),
            'status' => 'valid',
            'issued_by' => $issuer['id'],
            'issued_at' => now(),
            'valid_until' => $validUntil,
            'employee_id' => !empty($data['_employee']['id']) ? (int) $data['_employee']['id'] : null,
            'location_id' => $issuerSnap['location']['id'] ?? null,
        ]);
        personnel_event_for_document($id, $type, $data);
        if ($type['key'] === 'rechnung') {
            $paid = !empty($data['paid']);
            update('documents', $id, ['pay_status' => $paid ? 'paid' : 'open', 'amount_cents' => (int) round(invoice_totals($data)[2] * 100), 'paid_at' => $paid ? now() : null]);
        }
        if ($patient) {
            q('UPDATE patients SET updated_at = ? WHERE id = ?', [now(), $patient['id']]);
            if ($type['key'] === 'todesbescheinigung' && !empty($data['mark_deceased'])) {
                q('UPDATE patients SET deceased_at = ? WHERE id = ?', [$data['death_date'], $patient['id']]);
            }
        }
        db()->commit();
    } catch (Throwable $ex) {
        db()->rollBack();
        throw $ex;
    }
    audit('document.issue', 'document', $id, $type['short'] . ' ' . patient_name($patient) . ($cosigner ? ' (freigegeben durch ' . user_display($cosigner) . ')' : ''));
    $person = document_person_snapshot($patient, $data);
    $number = (string) scalar('SELECT number FROM documents WHERE id = ?', [$id]);
    notify_webhook('documents', log_embed('📄 ' . $type['title'] . ' ausgestellt', '', 0x1e7a43, array_values(array_filter([
        ['name' => 'Nummer', 'value' => $number, 'inline' => true],
        ['name' => 'Ausgestellt von', 'value' => user_display($issuer), 'inline' => true],
        $person ? ['name' => 'Für', 'value' => initials_name($person), 'inline' => true] : null,
        $cosigner ? ['name' => 'Freigegeben von', 'value' => user_display($cosigner), 'inline' => true] : null,
    ]))));
    if ($type['key'] === 'doj_haftbefehl') {
        wanted_from_warrant($id); // Fahndung bei allen Polizeibehörden
    }
    fire_document_issued($id, $type['key'], $data); // Brandschau → Prüftermine im Objektplan
    rescue_document_issued($id, $type['key'], $data, (int) $issuer['id']); // Flugbericht → Flugbuch
    if (!empty($data['_dispatch'])) {
        q('UPDATE dispatch_incidents SET document_id = ? WHERE id = ? AND faction_id = ?', [$id, (int) $data['_dispatch'], fid()]);
    }
    notify_patient_dm($patient['id'] ?? null, 'Der ' . org('org_short') . ' hat für dich ein neues Dokument ausgestellt: ' . $type['title'] . ' (' . $number . '). Du findest es im Bürgerportal: ' . base_url() . '/portal.php?page=documents');
    return $id;
}

// ------------------------------------------------------------------ Nachträge

function document_addenda(int $documentId): array
{
    if ($documentId <= 0) {
        return [];
    }
    return array_map(function ($a) {
        $a['author'] = json_decode((string) $a['author_snapshot'], true) ?: [];
        return $a;
    }, all('SELECT * FROM document_addenda WHERE document_id = ? ORDER BY id', [$documentId]));
}

function can_add_addendum(array $doc): bool
{
    $me = current_user();
    return $doc['status'] !== 'revoked' && ((int) $doc['issued_by'] === (int) $me['id'] || can('documents.addendum'));
}
