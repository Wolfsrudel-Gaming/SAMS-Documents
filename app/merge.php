<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Doppelte Patientenakten finden und zusammenführen. Die doppelte Akte wird
 * nicht gelöscht, sondern archiviert und verweist auf die verbleibende Akte
 * (alte Links und Dokumente bleiben gültig).
 */

/** Felder der Stammdaten, die beim Zusammenführen gewählt werden können. */
const MERGE_FIELDS = [
    'first_name' => 'Vorname', 'last_name' => 'Nachname', 'dob' => 'Geburtsdatum', 'gender' => 'Geschlecht', 'citizen_id' => 'Bürger-ID',
    'phone' => 'Telefon', 'address' => 'Anschrift', 'occupation' => 'Beruf', 'employer' => 'Arbeitgeber', 'insurance' => 'Versicherung',
    'insurance_number' => 'Versichertennummer', 'blood_type' => 'Blutgruppe', 'height' => 'Größe', 'weight' => 'Gewicht',
    'emergency_contact' => 'Notfallkontakt', 'organ_donor' => 'Organspende',
];
/** Freitextfelder werden zusammengefügt statt ersetzt. */
const MERGE_COMBINE = ['allergies' => 'Allergien', 'conditions' => 'Vorerkrankungen', 'medication' => 'Dauermedikation', 'flags' => 'Hinweise', 'notes' => 'Notizen'];
/** Tabellen mit Verweis auf die Patientenakte. */
const MERGE_TABLES = ['entries', 'documents', 'cases', 'attachments', 'document_drafts', 'citizens', 'appointments', 'portal_requests'];

/** Mögliche Doppelungen: gleiche Bürger-ID, gleicher Name + Geburtsdatum oder gleicher Name. */
function find_duplicate_patients(): array
{
    $rows = all('SELECT id, first_name, last_name, dob, citizen_id, phone, created_at FROM patients WHERE merged_into IS NULL ORDER BY id');
    $groups = [];
    $norm = fn(string $s) => mb_strtolower(trim(preg_replace('/\s+/', ' ', $s)));
    foreach ($rows as $r) {
        $keys = [];
        if (trim((string) $r['citizen_id']) !== '') {
            $keys[] = ['Gleiche Bürger-ID', 'cid:' . $norm($r['citizen_id'])];
        }
        $name = $norm($r['first_name'] . ' ' . $r['last_name']);
        if ($name !== '') {
            $keys[] = $r['dob'] ? ['Gleicher Name und Geburtsdatum', 'nd:' . $name . '|' . $r['dob']] : ['Gleicher Name', 'n:' . $name];
            $keys[] = ['Gleicher Name', 'n:' . $name];
        }
        foreach ($keys as [$reason, $k]) {
            $groups[$k]['reason'] = $reason;
            $groups[$k]['ids'][$r['id']] = $r;
        }
    }
    // Stärkste Übereinstimmung je Paar behalten
    $pairs = [];
    $rank = ['Gleiche Bürger-ID' => 3, 'Gleicher Name und Geburtsdatum' => 2, 'Gleicher Name' => 1];
    foreach ($groups as $g) {
        $list = array_values($g['ids']);
        for ($i = 0; $i < count($list); $i++) {
            for ($j = $i + 1; $j < count($list); $j++) {
                $k = $list[$i]['id'] . '-' . $list[$j]['id'];
                if (!isset($pairs[$k]) || $rank[$g['reason']] > $rank[$pairs[$k]['reason']]) {
                    $pairs[$k] = ['a' => $list[$i], 'b' => $list[$j], 'reason' => $g['reason']];
                }
            }
        }
    }
    usort($pairs, fn($x, $y) => $rank[$y['reason']] <=> $rank[$x['reason']]);
    return array_values($pairs);
}

/**
 * Akte $sourceId in $targetId zusammenführen. $choices: Feld → 'target'|'source'.
 * Gibt eine Zusammenfassung (Anzahl verschobener Datensätze) zurück.
 */
function merge_patients(int $targetId, int $sourceId, array $choices, int $userId): array
{
    $target = one('SELECT * FROM patients WHERE id = ?', [$targetId]);
    $source = one('SELECT * FROM patients WHERE id = ?', [$sourceId]);
    if (!$target || !$source || $targetId === $sourceId || $source['merged_into'] || $target['merged_into']) {
        throw new RuntimeException('Diese Akten können nicht zusammengeführt werden.');
    }
    $update = [];
    foreach (MERGE_FIELDS as $f => $_) {
        $pick = ($choices[$f] ?? '') === 'source' ? $source[$f] : $target[$f];
        if ((string) $pick !== (string) $target[$f]) {
            $update[$f] = $pick;
        }
    }
    foreach (MERGE_COMBINE as $f => $_) {
        $a = trim((string) $target[$f]);
        $b = trim((string) $source[$f]);
        if ($b !== '' && mb_stripos($a, $b) === false) {
            $update[$f] = $f === 'flags' ? implode(', ', array_unique(array_filter(array_map('trim', explode(',', $a . ',' . $b))))) : trim($a . ($a !== '' ? "\n" : '') . $b);
        }
    }
    if (!empty($source['living_will']) && empty($target['living_will'])) {
        $update['living_will'] = 1;
    }
    if (empty($target['deceased_at']) && !empty($source['deceased_at'])) {
        $update['deceased_at'] = $source['deceased_at'];
    }
    $moved = [];
    db()->beginTransaction();
    try {
        if ($update) {
            update('patients', $targetId, $update + ['updated_at' => now()]);
        }
        foreach (MERGE_TABLES as $table) {
            $moved[$table] = q("UPDATE $table SET patient_id = ? WHERE patient_id = ?", [$targetId, $sourceId])->rowCount();
        }
        // Ältere Verweise auf die doppelte Akte ebenfalls umleiten
        q('UPDATE patients SET merged_into = ? WHERE merged_into = ?', [$targetId, $sourceId]);
        update('patients', $sourceId, ['merged_into' => $targetId, 'archived_at' => now(), 'archived_by' => $userId,
            'archived_reason' => 'Zusammengeführt in Akte ' . str_pad((string) $targetId, 6, '0', STR_PAD_LEFT), 'citizen_id' => '', 'updated_at' => now()]);
        insert('entries', ['patient_id' => $targetId, 'type' => 'Sonstiges', 'title' => 'Akte zusammengeführt',
            'content' => 'Die doppelte Akte ' . str_pad((string) $sourceId, 6, '0', STR_PAD_LEFT) . ' (' . patient_name($source, true) . ', geb. ' . fmt_date($source['dob']) . ') wurde in diese Akte übernommen.',
            'confidential' => 0, 'occurred_at' => now(), 'created_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
    audit('patient.merge', 'patient', $targetId, patient_name($source) . ' #' . $sourceId . ' → #' . $targetId . ' (' . array_sum($moved) . ' Datensätze)');
    return $moved;
}
