<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Vorlagen-Editor: eigene Dokumentarten, die die Leitung im Browser anlegt.
 * Felder, Grundlayout (Brief / Formular / Protokoll) und ein Text mit
 * Platzhaltern werden in custom_doc_types gespeichert. Beim Ausstellen wird
 * die Vorlage in die Dokumentdaten kopiert (_tpl), damit spätere Änderungen
 * an der Vorlage bereits ausgestellte Dokumente nicht verändern.
 */

const CUSTOM_LAYOUTS = [
    'letter'   => 'Brief (A4 mit Briefkopf, Text im Mittelpunkt)',
    'form'     => 'Formular (A4 mit Kästchenraster)',
    'protocol' => 'Protokoll (Formular mit roten Abschnittsbalken)',
];
const CUSTOM_FIELD_TYPES = [
    'text' => 'Text (einzeilig)', 'textarea' => 'Text (mehrzeilig)', 'date' => 'Datum', 'time' => 'Uhrzeit', 'number' => 'Zahl',
    'select' => 'Auswahlliste', 'radio' => 'Auswahl (Optionen)', 'checkbox' => 'Ankreuzfeld', 'checkboxes' => 'Mehrfachauswahl',
    'heading' => 'Zwischenüberschrift', 'signature' => 'Unterschrift (auf dem Gerät)',
];
const CUSTOM_WIDTHS = ['full' => 'ganze Breite', 'half' => '1/2', 'third' => '1/3', 'quarter' => '1/4'];
const CUSTOM_SEALS = ['rect' => 'Arztstempel (rechteckig)', 'round' => 'Dienstsiegel (rund)', 'none' => 'ohne Stempel'];

function custom_doc_types(bool $activeOnly = false): array
{
    try {
        return all('SELECT * FROM custom_doc_types' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY category, title');
    } catch (PDOException $e) {
        return []; // vor der Migration
    }
}

/** Datenbankzeile → Dokumentart im Format von document_types(). */
function custom_type_definition(array $row): array
{
    $employee = $row['subject'] === 'employee';
    return [
        'title'    => $row['title'],
        'short'    => $row['short'] !== '' ? $row['short'] : $row['title'],
        'code'     => $row['code'],
        'category' => $row['category'] !== '' ? $row['category'] : 'Eigene Vorlagen',
        'role'     => 'custom',
        'patient'  => $employee ? 'none' : (in_array($row['patient_mode'], ['required', 'optional', 'none'], true) ? $row['patient_mode'] : 'optional'),
        'subject'  => $employee ? 'employee' : 'patient',
        'template' => 'custom',
        'faction_id' => (int) ($row['faction_id'] ?? 1),
        'hidden'   => !(int) $row['active'],
        'fields'   => json_decode((string) $row['fields'], true) ?: [],
        'custom'   => ['layout' => $row['layout'], 'body' => (string) $row['body'], 'seal' => $row['seal'], 'title' => $row['title']],
    ];
}

/** Ersatz, falls eine Vorlage nicht mehr existiert: Definition aus der Kopie im Dokument. */
function custom_type_from_snapshot(array $doc): ?array
{
    $tpl = $doc['data']['_tpl'] ?? null;
    if (!is_array($tpl)) {
        return null;
    }
    return ['key' => $doc['type'], 'title' => $tpl['title'] ?? 'Dokument', 'short' => $tpl['title'] ?? 'Dokument', 'code' => '??',
        'category' => 'Eigene Vorlagen', 'patient' => 'optional', 'template' => 'custom', 'hidden' => true,
        'fields' => $tpl['fields'] ?? [], 'custom' => $tpl];
}

/** Kopie der Vorlage, die beim Ausstellen in die Dokumentdaten geschrieben wird. */
function custom_template_snapshot(array $type): array
{
    return ($type['custom'] ?? []) + ['fields' => $type['fields']];
}

function custom_slug(string $s): string
{
    $s = strtr(mb_strtolower(trim($s)), ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue', 'ß' => 'ss']);
    return trim(preg_replace('/[^a-z0-9]+/', '_', $s), '_');
}

/**
 * Felder aus dem Editor prüfen. Gibt [felder, fehler] zurück.
 * Erwartet $_POST['f'] als Liste mit label, name, type, required, width, options, placeholder.
 */
function clean_custom_fields(array $rows): array
{
    $fields = [];
    $errors = [];
    $names = [];
    foreach ($rows as $i => $r) {
        if (!is_array($r)) {
            continue;
        }
        $label = mb_substr(trim((string) ($r['label'] ?? '')), 0, 120);
        $type = array_key_exists($r['type'] ?? '', CUSTOM_FIELD_TYPES) ? $r['type'] : 'text';
        if ($label === '') {
            continue;
        }
        if ($type === 'heading') {
            $fields[] = ['type' => 'heading', 'label' => $label];
            continue;
        }
        $name = custom_slug((string) ($r['name'] ?? '')) ?: custom_slug($label);
        $name = substr($name, 0, 40);
        if ($name === '' || in_array($name, $names, true)) {
            $name = ($name ?: 'feld') . '_' . ($i + 1);
        }
        $names[] = $name;
        $f = ['name' => $name, 'label' => $label, 'type' => $type, 'width' => array_key_exists($r['width'] ?? '', CUSTOM_WIDTHS) ? $r['width'] : 'full'];
        if (!empty($r['required']) && $type !== 'signature') {
            $f['required'] = true;
        }
        if (($ph = trim((string) ($r['placeholder'] ?? ''))) !== '' && in_array($type, ['text', 'textarea', 'number'], true)) {
            $f['placeholder'] = mb_substr($ph, 0, 300);
        }
        if ($type === 'textarea') {
            $f['rows'] = 4;
        }
        if ($type === 'date' && !empty($r['today'])) {
            $f['default'] = 'today';
        }
        if (in_array($type, ['select', 'radio', 'checkboxes'], true)) {
            $opts = array_values(array_unique(array_filter(array_map(fn($o) => mb_substr(trim($o), 0, 120), preg_split('/\r?\n/', (string) ($r['options'] ?? ''))), 'strlen')));
            if (!$opts) {
                $errors[] = 'Feld „' . $label . '“: bitte Optionen angeben (eine pro Zeile).';
            }
            $f['options'] = array_combine($opts, $opts) ?: [];
        }
        $fields[] = $f;
    }
    if (!array_filter($fields, fn($f) => $f['type'] !== 'heading')) {
        $errors[] = 'Bitte mindestens ein Eingabefeld anlegen.';
    }
    return [$fields, $errors];
}

/** Platzhalter, die im Vorlagentext verwendet werden können. */
function custom_placeholder_help(): array
{
    return [
        '{{patient.name}}' => 'Name der Patientin / des Patienten', '{{patient.dob}}' => 'Geburtsdatum', '{{patient.address}}' => 'Anschrift',
        '{{patient.citizen_id}}' => 'Bürger-ID', '{{employee.name}}' => 'Name Mitarbeiter/in', '{{employee.rank}}' => 'Rang Mitarbeiter/in',
        '{{employee.badge}}' => 'Dienstnummer Mitarbeiter/in', '{{issuer.name}}' => 'Aussteller/in', '{{issuer.rank}}' => 'Rang Aussteller/in',
        '{{date}}' => 'Ausstellungsdatum', '{{number}}' => 'Dokument-Nr.', '{{org.name}}' => 'Name der Organisation', '{{org.city}}' => 'Stadt',
    ];
}

/** Wert eines Feldes für die Ausgabe (Datum formatiert, Optionen als Text). */
function custom_field_value(array $f, $v): string
{
    if (is_array($v)) {
        return implode(', ', $v);
    }
    $v = (string) $v;
    if ($f['type'] === 'date') {
        return fmt_date($v);
    }
    if ($f['type'] === 'checkbox') {
        return $v !== '' ? 'ja' : 'nein';
    }
    return $v;
}

/** Vorlagentext mit Platzhaltern füllen (sicher escaped, Zeilenumbrüche, **fett**). */
function custom_render_body(string $body, array $doc, array $fields): string
{
    $d = $doc['data'] ?? [];
    $p = $doc['patient_snapshot'] ?? [];
    $u = $doc['issuer_snapshot'] ?? [];
    $emp = $d['_employee'] ?? [];
    $map = [
        'patient.name' => $p && empty($p['employee']) ? patient_name($p) : '', 'patient.dob' => fmt_date($p['dob'] ?? ''),
        'patient.address' => str_replace("\n", ', ', (string) ($p['address'] ?? '')), 'patient.citizen_id' => (string) ($p['citizen_id'] ?? ''),
        'employee.name' => trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? '')), 'employee.rank' => (string) ($emp['rank_name'] ?? ''),
        'employee.badge' => (string) ($emp['badge_number'] ?? ''), 'issuer.name' => user_display($u), 'issuer.rank' => (string) ($u['rank_name'] ?? ''),
        'date' => fmt_date($doc['issued_at'] ?? now()), 'number' => (string) ($doc['number'] ?? ''), 'org.name' => org('org_name'), 'org.city' => org('city'),
    ];
    foreach ($fields as $f) {
        if (isset($f['name'])) {
            $map[$f['name']] = custom_field_value($f, $d[$f['name']] ?? '');
        }
    }
    $html = e($body);
    $html = preg_replace_callback('/\{\{\s*([a-z0-9_.]+)\s*\}\}/', fn($m) => array_key_exists($m[1], $map) ? '<span class="printed">' . e($map[$m[1]]) . '</span>' : $m[0], $html);
    $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
    return nl2br($html, false);
}

/** Im Text verwendete Feldnamen (die nicht noch einmal als Tabelle erscheinen). */
function custom_used_fields(string $body): array
{
    preg_match_all('/\{\{\s*([a-z0-9_]+)\s*\}\}/', $body, $m);
    return $m[1];
}
