<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Fallakten, Archiv und Sichtbarkeit von Akteneinträgen.
 */

const CASE_KINDS = [
    'notfall'    => 'Notfall / Einsatz',
    'ambulant'   => 'Ambulante Behandlung',
    'stationaer' => 'Stationärer Aufenthalt',
    'gutachten'  => 'Gutachten / Untersuchung',
    'psych'      => 'Psychologische Betreuung',
    'sonstiges'  => 'Sonstiges',
];

function next_case_number(): string
{
    $base = doc_prefix() . '-F-' . date('Y') . '-';
    $last = scalar('SELECT number FROM cases WHERE number LIKE ? AND ' . fw() . ' ORDER BY id DESC LIMIT 1', [$base . '%']);
    $n = $last ? ((int) substr((string) $last, strlen($base))) + 1 : 1;
    return $base . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
}

/** Darf der angemeldete Benutzer diesen Eintrag lesen? (vertraulich + Abteilung) */
function entry_visible(array $entry): bool
{
    $me = current_user();
    if (!$me) {
        return false;
    }
    if ((int) $entry['created_by'] === (int) $me['id']) {
        return true;
    }
    if (!empty($entry['confidential']) && !can('entries.confidential')) {
        return false;
    }
    if (!empty($entry['department_id']) && !can('entries.all_departments')) {
        return in_array((int) $entry['department_id'], user_department_ids((int) $me['id']), true);
    }
    return true;
}

function patient_archived(array $p): bool
{
    return !empty($p['archived_at']);
}

/** Bricht ab, wenn die Akte archiviert ist (keine neuen Einträge, Fälle, Dokumente). */
function require_active_patient(?array $p): void
{
    if ($p && patient_archived($p)) {
        flash('error', 'Die Akte ist archiviert. Für neue Einträge muss sie zuerst reaktiviert werden.');
        redirect(url('patient', ['id' => $p['id']]));
    }
}

function case_status_badge(array $c): string
{
    return $c['status'] === 'closed' ? '<span class="badge grey">abgeschlossen</span>' : '<span class="badge green">offen</span>';
}

/**
 * Felder eines neuen Dokuments aus der Fallakte vorbelegen.
 * Arbeitet mit den Feldnamen der Dokumentarten (Diagnose, Ort, Hergang, Datum).
 */
function prefill_from_case(array $type, array $case, array $data): array
{
    $incidentDate = $case['incident_at'] ? substr((string) $case['incident_at'], 0, 10) : '';
    $incidentTime = $case['incident_at'] ? substr((string) $case['incident_at'], 11, 5) : '';
    $map = [
        'icd' => $case['diagnosis'], 'diagnoses' => $case['diagnosis'], 'cause_a' => '',
        'location' => $case['location'], 'place_address' => $case['location'],
        'incident' => $case['incident'], 'history' => $case['incident'], 'call_reason' => $case['incident'],
        'findings' => '', 'date' => $incidentDate, 'exam_date' => '', 't_alarm' => $incidentTime,
        'admission' => $incidentDate, 'service_date' => $incidentDate,
    ];
    foreach ($type['fields'] as $f) {
        $name = $f['name'] ?? null;
        if (!$name || !isset($map[$name]) || (string) $map[$name] === '') {
            continue;
        }
        // Mehrzeilige Diagnosen in einzeilige Felder: nur erste Zeile
        $value = (string) $map[$name];
        if (($f['type'] ?? '') === 'text') {
            $value = strtok($value, "\n") ?: $value;
        }
        if (in_array($f['type'] ?? '', ['text', 'textarea', 'date', 'time'], true) && (empty($data[$name]) || $data[$name] === field_default($f))) {
            $data[$name] = $value;
        }
    }
    return $data;
}
