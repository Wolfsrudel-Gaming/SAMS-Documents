<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Sky Ambulance (sky_ambulancejob) – nur über die dokumentierten Schnittstellen:
 * - Vitalwerte: Client-Export GetLocalInjuryProfile() (vitalSchemaVersion 2) des Patienten → Vitalverlauf der Akte,
 *   Vorbelegung von Einsatzprotokoll & Co. (RR, HF, SpO2, BZ)
 * - Tod/Wiederbelebung: Client-Ereignis sky_ambulancejob:deathStateChanged (state.dead), serverseitig geprüft mit isDead()
 * - Medikamentengabe: Config.CustomMedications → onFinishServer ruft exports.sams_tablet:SkyMedication(...)
 * - Rechnungen: Sky Billing (sky_jobs_base CreateInvoice/GetInvoice/CancelInvoice) – in der Resource
 * Die Werte stammen aus dem Spiel des Patienten; sie dienen der Dokumentation, nicht dem Spielablauf.
 */

const SKY_VITAL_LIMITS = ['heart_rate' => [0, 220], 'systolic' => [0, 260], 'diastolic' => [0, 160], 'spo2' => [0, 100], 'blood_sugar' => [20, 600]];

/** Vitalwerte aus der API prüfen; ungültige Werte werden verworfen (nicht durch „normale“ ersetzt). */
function sky_clean_vitals(array $v): ?array
{
    $out = [];
    foreach (SKY_VITAL_LIMITS as $k => [$min, $max]) {
        $n = $v[$k] ?? null;
        $out[$k] = is_numeric($n) && (float) $n >= $min && (float) $n <= $max ? (int) round((float) $n) : null;
    }
    // Blutdruck: 0/0 (Herzstillstand) oder systolisch > diastolisch
    if ($out['systolic'] !== null && $out['diastolic'] !== null && !($out['systolic'] === 0 && $out['diastolic'] === 0) && $out['systolic'] <= $out['diastolic']) {
        $out['systolic'] = $out['diastolic'] = null;
    }
    $out['cardiac_arrest'] = !empty($v['cardiac_arrest']) && $v['cardiac_arrest'] !== 'false' ? 1 : 0;
    $act = (string) ($v['activity'] ?? '');
    $out['activity'] = in_array($act, ['rest', 'walk', 'run', 'sprint', 'swim', 'dive', 'climb', 'combat'], true) ? $act : '';
    return array_filter($out, fn($x) => $x !== null) === ['cardiac_arrest' => 0, 'activity' => ''] ? null : $out;
}

function sky_vitals_text(array $v): string
{
    $parts = [];
    if ($v['cardiac_arrest']) {
        $parts[] = 'HERZ-KREISLAUF-STILLSTAND';
    }
    if ($v['systolic'] !== null && $v['diastolic'] !== null) {
        $parts[] = 'RR ' . $v['systolic'] . '/' . $v['diastolic'] . ' mmHg';
    }
    foreach (['heart_rate' => ['HF', '/min'], 'spo2' => ['SpO₂', ' %'], 'blood_sugar' => ['BZ', ' mg/dl']] as $k => [$l, $u]) {
        if ($v[$k] !== null) {
            $parts[] = $l . ' ' . $v[$k] . $u;
        }
    }
    return implode(' · ', $parts);
}

function sky_store_vitals(array $patient, array $v, ?int $userId): int
{
    return insert('patient_vitals', ['faction_id' => (int) $patient['faction_id'], 'patient_id' => (int) $patient['id'], 'heart_rate' => $v['heart_rate'], 'systolic' => $v['systolic'],
        'diastolic' => $v['diastolic'], 'spo2' => $v['spo2'], 'blood_sugar' => $v['blood_sugar'], 'cardiac_arrest' => $v['cardiac_arrest'], 'activity' => $v['activity'],
        'source' => 'sky', 'recorded_by' => $userId, 'recorded_at' => now()]);
}

/** Letzte Vitalwerte einer Akte (jünger als $minutes) für die Vorbelegung von Formularen. */
function latest_vitals(int $patientId, int $minutes = 60): ?array
{
    return one('SELECT * FROM patient_vitals WHERE patient_id = ? AND recorded_at >= ? ORDER BY id DESC LIMIT 1', [$patientId, date('Y-m-d H:i:s', time() - $minutes * 60)]);
}

/** Formularfelder RR/HF/SpO2/BZ mit frischen Vitalwerten vorbelegen (nur leere Felder). */
function prefill_vitals(array $type, array $data, array $patient): array
{
    $v = latest_vitals((int) $patient['id']);
    if (!$v) {
        return $data;
    }
    $names = array_column($type['fields'], 'name');
    $map = ['rr' => $v['systolic'] !== null && $v['diastolic'] !== null ? $v['systolic'] . '/' . $v['diastolic'] : '', 'hf' => (string) ($v['heart_rate'] ?? ''),
        'spo2' => (string) ($v['spo2'] ?? ''), 'bz' => (string) ($v['blood_sugar'] ?? '')];
    foreach ($map as $k => $val) {
        if ($val !== '' && in_array($k, $names, true) && (string) ($data[$k] ?? '') === '') {
            $data[$k] = $val;
        }
    }
    return $data;
}

/** Akteneintrag ohne angemeldeten Benutzer anlegen (aus dem Spiel). */
function sky_entry(array $patient, string $type, string $title, string $content, ?int $userId): int
{
    return insert('entries', ['faction_id' => (int) $patient['faction_id'], 'patient_id' => (int) $patient['id'], 'type' => $type, 'title' => mb_substr($title, 0, 190),
        'content' => $content, 'confidential' => 0, 'occurred_at' => now(), 'created_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
}

/** Akte zur Bürger-ID in einer Fraktion; auf Wunsch aus den ESX-Daten anlegen. */
function sky_record(int $factionId, array $fields, bool $create, ?int $userId): ?array
{
    $p = $fields['citizen_id'] === '' ? null : one('SELECT * FROM patients WHERE citizen_id = ? AND merged_into IS NULL AND faction_id = ? ORDER BY archived_at IS NOT NULL, id LIMIT 1', [$fields['citizen_id'], $factionId]);
    if ($p || !$create || $fields['first_name'] === '' || $fields['last_name'] === '') {
        return $p;
    }
    $id = insert('patients', $fields + ['faction_id' => $factionId, 'created_by' => $userId, 'created_at' => now(), 'updated_at' => now()]);
    audit_as($userId, 'patient.create', 'patient', $id, trim($fields['first_name'] . ' ' . $fields['last_name']) . ' (Sky Ambulance, aus den Spielerdaten)');
    return one('SELECT * FROM patients WHERE id = ?', [$id]);
}

/**
 * Tod bzw. Wiederbelebung eines Spielers. Mit Medic im Dienst in der Nähe: Eintrag in dessen Fraktion (Akte wird bei Bedarf angelegt).
 * Ohne Medic: nur in vorhandene Akten der Rettungsdienste.
 */
function sky_death_event(array $fields, bool $dead, ?array $medic): int
{
    $title = $dead ? 'Bewusstlos / Todeszustand (Sky Ambulance)' : 'Wieder bei Bewusstsein / wiederbelebt (Sky Ambulance)';
    $content = ($dead ? 'Der Spieler ist im Spiel zusammengebrochen (Todeszustand).' : 'Der Spieler ist wieder bei Bewusstsein.')
        . ($medic ? "\nVor Ort im Dienst: " . user_display($medic) : '') . "\nAutomatisch erfasst " . fmt_datetime(now()) . '.';
    $n = 0;
    $factions = $medic ? [(int) $medic['faction_id']] : medical_faction_ids();
    foreach ($factions as $fid) {
        set_faction_context($fid);
        if (!module_enabled('records', $fid)) {
            continue;
        }
        $p = sky_record($fid, $fields, $medic !== null && (int) $medic['faction_id'] === $fid, $medic ? (int) $medic['id'] : null);
        if (!$p || $p['archived_at']) {
            continue;
        }
        sky_entry($p, 'Notfalleinsatz', $title, $content, $medic ? (int) $medic['id'] : null);
        if ($dead && !$medic) {
            notify_users_with('patients.edit', 'patients', patient_name($p) . ': im Spiel zusammengebrochen', url('patient', ['id' => $p['id']]));
        }
        $n++;
    }
    return $n;
}

/** Medikamentengabe aus Sky: Bestand/BtM-Buch buchen und in die Akte eintragen. Gibt [ok, Text] zurück. */
function sky_medication(array $medic, string $item, array $fields): array
{
    $fid = (int) $medic['faction_id'];
    set_faction_context($fid);
    $med = $item !== '' && module_enabled('stock', $fid) ? one('SELECT * FROM medications WHERE item_name = ? AND active = 1 AND faction_id = ?', [$item, $fid]) : null;
    $p = sky_record($fid, $fields, in_array('patients.edit', user_permissions($medic), true), (int) $medic['id']);
    $label = $med['name'] ?? $item;
    $notes = [];
    if ($med) {
        $move = medication_move((int) $med['id'], -1, 'out', ['patient_id' => $p['id'] ?? null, 'user_id' => (int) $medic['id'], 'source' => 'game',
            'party' => $p ? patient_name($p) : trim($fields['first_name'] . ' ' . $fields['last_name']), 'note' => 'Gabe im Spiel (Sky Ambulance)']);
        $notes[] = $move ? 'Bestand gebucht (' . $move['balance'] . ' ' . $med['unit'] . ' übrig)' : 'Bestand reicht nicht – bitte im System prüfen';
        if (!$move) {
            notify_users_with('stock.manage', 'stock', 'Gabe ohne Bestand: ' . $med['name'] . ' durch ' . user_display($medic), url('stock'));
        }
    } else {
        $notes[] = 'kein Artikel mit diesem Item im Bestand';
    }
    if ($p && module_enabled('records', $fid)) {
        sky_entry($p, 'Medikation', 'Medikamentengabe: ' . $label, 'Gegeben im Spiel durch ' . user_display($medic) . ' (Sky Ambulance, Item ' . $item . ').' . ($med && $med['is_btm'] ? "\nBetäubungsmittel – im BtM-Buch gebucht." : ''), (int) $medic['id']);
    }
    audit_as((int) $medic['id'], 'stock.sky_given', 'patient', $p ? (int) $p['id'] : null, $label . ': ' . implode(', ', $notes));
    return [true, $label . ': ' . implode(', ', $notes)];
}
