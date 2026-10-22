<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * US-Vorlagenset (Einstellungen → Portal → Vorlagenset „USA“).
 *
 * Alle Dokumentarten behalten ihre Schlüssel und Feldnamen (die Programmlogik hängt daran: Haftbefehl → Fahndung,
 * Rezept → Apotheke, Rechnung → Spiel …). Im US-Set werden Bezeichnungen, Feldbeschriftungen, Auswahltexte,
 * Platzhalter und Vorlagentexte über dt() ins Englische übersetzt (lang/doc_en/*.php); gespeichert bleiben die
 * Auswahlwerte wie bisher. Typisch deutsche Formulare bekommen eine eigene US-Druckvorlage (us_*), dazu kommen
 * Dokumentarten, die es nur im US-Set gibt (region 'us').
 */

/** US-Druckvorlagen statt der deutschen Formulare (Muster 1, Kassenrezept, Überweisungsschein …). */
const DOC_US_TEMPLATES = [
    'au' => 'us_work_excuse', 'rezept' => 'us_prescription', 'ueberweisung' => 'us_referral',
    'todesbescheinigung' => 'us_death_certificate', 'impfnachweis' => 'us_immunization',
];

/** Auswahlwerte, die es im US-Set nicht gibt (Art => Feld => Werte). */
const DOC_US_DROP_OPTIONS = [
    'au' => ['rehab' => ['krankengeld']],
    'rezept' => ['flags' => ['gebfrei']],
];

/** Dokumentart ins US-Set übersetzen (Schlüssel und Feldnamen bleiben). */
function doc_type_us(string $key, array $t): array
{
    foreach (['title', 'short', 'category'] as $k) {
        if (isset($t[$k]) && is_string($t[$k])) {
            $t[$k] = dt($t[$k]);
        }
    }
    if (isset(DOC_US_TEMPLATES[$key])) {
        $t['template'] = DOC_US_TEMPLATES[$key];
    }
    $t['fields'] = array_map(fn($f) => doc_field_us($f, DOC_US_DROP_OPTIONS[$key][$f['name'] ?? ''] ?? []), $t['fields'] ?? []);
    if (isset($t['custom'])) {
        foreach (['title', 'body'] as $k) {
            if (isset($t['custom'][$k]) && is_string($t['custom'][$k])) {
                $t['custom'][$k] = dt($t['custom'][$k]);
            }
        }
    }
    return $t;
}

function doc_field_us(array $f, array $drop = []): array
{
    foreach (['label', 'placeholder', 'hint', 'help'] as $k) {
        if (isset($f[$k]) && is_string($f[$k])) {
            $f[$k] = dt($f[$k]);
        }
    }
    // Vorgaben von Auswahlfeldern sind gespeicherte Schlüssel (bleiben deutsch), nur Freitext-Vorgaben übersetzen
    if (isset($f['default']) && is_string($f['default']) && !isset($f['options']) && !in_array($f['default'], ['today', 'now'], true)) {
        $f['default'] = dt($f['default']);
    }
    if (isset($f['options']) && is_array($f['options'])) {
        $opts = [];
        foreach ($f['options'] as $value => $label) {
            if (!in_array((string) $value, $drop, true)) {
                $opts[$value] = is_string($label) ? dt($label) : $label;
            }
        }
        $f['options'] = $opts;
    }
    if (isset($f['columns']) && is_array($f['columns'])) {
        foreach ($f['columns'] as $c => $col) {
            foreach (['label', 'placeholder'] as $k) {
                if (isset($col[$k]) && is_string($col[$k])) {
                    $f['columns'][$c][$k] = dt($col[$k]);
                }
            }
        }
    }
    return $f;
}

/** Alle eingebauten Arten fürs US-Set: deutsche Sonderarten raus, Rest übersetzen. */
function doc_types_us_apply(array $types): array
{
    $out = [];
    foreach ($types as $key => $t) {
        if (($t['region'] ?? 'us') === 'de') {
            continue;
        }
        $out[$key] = ($t['region'] ?? '') === 'us' ? $t : doc_type_us((string) $key, $t);
    }
    return $out;
}

/**
 * Nach dem Umschalten auf das US-Set: Rechte für die neuen US-Arten an die Ränge vergeben,
 * die die verwandte Art ('like') schon ausstellen dürfen. Gibt die Zahl neuer Rechte zurück.
 */
function doc_region_grant_permissions(): int
{
    $n = 0;
    foreach (document_types_us_only() as $key => $t) {
        $like = (string) ($t['like'] ?? '');
        if ($like === '') {
            continue;
        }
        foreach (all('SELECT rank_id FROM rank_permissions WHERE permission = ?', ['documents.issue.' . $like]) as $r) {
            if (!scalar('SELECT 1 FROM rank_permissions WHERE rank_id = ? AND permission = ?', [$r['rank_id'], 'documents.issue.' . $key])) {
                insert('rank_permissions', ['rank_id' => $r['rank_id'], 'permission' => 'documents.issue.' . $key]);
                $n++;
            }
        }
    }
    return $n;
}

/** Dokumentarten, die es nur im US-Set gibt (englisch, Vorlagen-Engine). */
function document_types_us_only(): array
{
    $t = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'text', 'width' => 'full'] + $o;
    $date = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'date', 'width' => 'quarter'] + $o;
    $time = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'time', 'width' => 'quarter'];
    $num = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'number', 'width' => 'quarter'] + $o;
    $area = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'textarea', 'rows' => 4, 'width' => 'full'] + $o;
    $sel = fn(string $name, string $label, array $opts, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'select', 'width' => 'third', 'options' => array_combine($opts, $opts)] + $o;
    $radio = fn(string $name, string $label, array $opts, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'radio', 'width' => 'full', 'options' => array_combine($opts, $opts)] + $o;
    $check = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'checkbox', 'width' => 'third'] + $o;
    $head = fn(string $label) => ['type' => 'heading', 'label' => $label];
    $sign = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'signature', 'width' => 'half'];
    $case = $t('aktenzeichen', 'Case number', ['width' => 'third', 'placeholder' => 'e.g. LS-CR-2026-00123']);

    $def = function (string $title, string $short, string $code, string $kind, string $role, string $category, string $layout, array $fields, string $like, string $body = '', string $patient = 'required', string $seal = 'round', ?string $valid = null): array {
        return array_filter([
            'title' => $title, 'short' => $short, 'code' => $code, 'category' => $category, 'role' => $role, 'kinds' => [$kind], 'region' => 'us', 'like' => $like,
            'patient' => $patient, 'template' => 'custom', 'fields' => $fields, 'valid' => $valid,
            'custom' => ['layout' => $layout, 'body' => $body, 'seal' => $seal, 'title' => $title],
        ], fn($v) => $v !== null);
    };

    return [
        // ================================================================ Police
        'us_citation' => $def('Notice to Appear / Citation', 'Citation', 'CIT', 'police', 'officer', 'Traffic', 'protocol', [
            $head('Violation'), $date('datum', 'Date', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Time'),
            $t('ort', 'Location (street / cross street)', ['width' => 'half', 'required' => true]),
            $radio('art', 'Type', ['Traffic infraction', 'Misdemeanor', 'Parking', 'Equipment violation (fix-it ticket)'], ['required' => true, 'default' => 'Traffic infraction']),
            $t('delikte', 'Violation(s) – code section and description', ['required' => true, 'list' => 'laws']),
            $t('fahrzeug', 'Vehicle (make, model, color)', ['width' => 'half']), $t('kennzeichen', 'License plate', ['width' => 'quarter']),
            $num('speed', 'Speed (mph)', ['width' => 'quarter']), $num('limit', 'Posted limit (mph)', ['width' => 'quarter']),
            $head('Penalty'), $num('fine', 'Fine ($)', ['width' => 'quarter']), $date('due', 'Pay by / appear by'),
            $t('court', 'Court', ['width' => 'half', 'default' => 'San Andreas Superior Court']),
            $check('appearance', 'Mandatory court appearance'),
            $area('notes', 'Officer notes', ['rows' => 2]),
            $sign('sig_violator', 'Signature (not an admission of guilt – promise to appear)'),
        ], 'pd_platzverweis', "Without admitting guilt, I promise to appear at the time and place indicated or to pay the fine by the due date."),
        'us_miranda' => $def('Miranda Rights Advisement and Waiver', 'Miranda Waiver', 'MIR', 'police', 'officer', 'Investigations', 'letter', [
            $date('datum', 'Date', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Time advised'), $t('ort', 'Location', ['width' => 'half']),
            $case,
            $radio('waiver', 'Decision', ['I understand my rights and wish to speak without an attorney', 'I invoke my right to remain silent', 'I request an attorney'], ['required' => true]),
            $t('zeuge', 'Witnessing officer', ['width' => 'half']),
            $sign('sig_suspect', 'Signature of person advised'),
        ], 'pd_vernehmung', "**You have the right to remain silent.** Anything you say can and will be used against you in a court of law.\n**You have the right to an attorney** and to have one present during questioning. If you cannot afford an attorney, one will be appointed for you before any questioning if you wish.\nYou can decide at any time to exercise these rights and not answer any questions or make any statements.\n\nI have read this statement of my rights and I understand what my rights are."),
        'us_use_of_force' => $def('Use of Force Report', 'Use of Force', 'UOF', 'police', 'officer', 'Field Operations', 'protocol', [
            $head('Incident'), $date('datum', 'Date', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Time'), $t('ort', 'Location', ['width' => 'half', 'required' => true]),
            $case, $t('anlass', 'Reason for contact', ['width' => 'half']),
            $head('Force used'), ['name' => 'force', 'label' => 'Force options', 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine(
                ['Verbal commands', 'Control hold / takedown', 'Handcuffs / restraints', 'OC spray', 'Baton / impact weapon', 'Taser / CEW', 'K9', 'Less-lethal munition', 'Firearm pointed', 'Firearm discharged', 'Vehicle intervention (PIT)'],
                ['Verbal commands', 'Control hold / takedown', 'Handcuffs / restraints', 'OC spray', 'Baton / impact weapon', 'Taser / CEW', 'K9', 'Less-lethal munition', 'Firearm pointed', 'Firearm discharged', 'Vehicle intervention (PIT)'])],
            $sel('resistance', 'Subject resistance', ['Passive', 'Active', 'Assaultive', 'Life-threatening']),
            $area('hergang', 'Narrative – why force was necessary and proportional', ['required' => true, 'rows' => 6]),
            $head('Injuries and medical'), $area('verletzungen', 'Injuries (subject / officers / bystanders)', ['rows' => 2]),
            $check('ems', 'EMS requested'), $check('hospital', 'Transported to hospital'), $check('photos', 'Injuries photographed'),
            $t('supervisor', 'Supervisor notified', ['width' => 'half']), $t('bodycam', 'Body camera / dashcam reference', ['width' => 'half']),
        ], 'pd_festnahme', '', 'optional'),
        'us_incident' => $def('Incident Report', 'Incident Report', 'IR', 'police', 'officer', 'Investigations', 'protocol', [
            $date('datum', 'Date', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Time'), $t('ort', 'Location', ['width' => 'half', 'required' => true]), $case,
            $t('art', 'Incident type', ['width' => 'half', 'required' => true, 'placeholder' => 'e.g. Burglary, Assault, Disturbance']),
            $t('delikte', 'Offense(s)', ['list' => 'laws']),
            $area('beteiligte', 'Involved persons (victim / suspect / witness / reporting party)', ['rows' => 3]),
            $area('sachverhalt', 'Narrative', ['required' => true, 'rows' => 8]),
            $area('beweise', 'Evidence collected / property', ['rows' => 2]),
            $sel('status', 'Case status', ['Open', 'Closed – arrest', 'Closed – cleared', 'Referred to detectives', 'Unfounded']),
        ], 'pd_anzeige', '', 'optional'),
        'us_dui' => $def('DUI Arrest Report and Field Sobriety Tests', 'DUI Report', 'DUI', 'police', 'officer', 'Traffic', 'protocol', [
            $head('Stop'), $date('datum', 'Date', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Time'), $t('ort', 'Location', ['width' => 'half', 'required' => true]),
            $t('fahrzeug', 'Vehicle', ['width' => 'half']), $t('kennzeichen', 'License plate', ['width' => 'quarter']), $t('grund', 'Reason for stop', ['width' => 'full']),
            $head('Observations'), ['name' => 'signs', 'label' => 'Signs of impairment', 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine(
                ['Odor of alcohol', 'Slurred speech', 'Bloodshot / watery eyes', 'Unsteady on feet', 'Open container', 'Admitted drinking', 'Odor of marijuana'],
                ['Odor of alcohol', 'Slurred speech', 'Bloodshot / watery eyes', 'Unsteady on feet', 'Open container', 'Admitted drinking', 'Odor of marijuana'])],
            $head('Standardized field sobriety tests'),
            $sel('hgn', 'Horizontal gaze nystagmus (clues of 6)', ['0', '1', '2', '3', '4', '5', '6', 'Refused', 'Not performed']),
            $sel('walk', 'Walk and turn (clues of 8)', ['0', '1', '2', '3', '4', '5', '6', '7', '8', 'Refused', 'Not performed']),
            $sel('stand', 'One-leg stand (clues of 4)', ['0', '1', '2', '3', '4', 'Refused', 'Not performed']),
            $t('pas', 'Preliminary breath test (PAS) result', ['width' => 'third', 'placeholder' => 'e.g. 0.12 % BAC']),
            $radio('chemical', 'Chemical test', ['Breath', 'Blood', 'Refused (implied consent advised)'], ['required' => true]),
            $t('bac', 'Result (BAC)', ['width' => 'third']),
            $area('notes', 'Narrative', ['rows' => 4]),
        ], 'pd_unfall'),
        'us_impound' => $def('Vehicle Impound / Tow Report', 'Impound', 'TOW', 'police', 'officer', 'Traffic', 'protocol', [
            $date('datum', 'Date', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Time'), $t('ort', 'Location', ['width' => 'half', 'required' => true]),
            $t('fahrzeug', 'Vehicle (make, model, color)', ['width' => 'half', 'required' => true]), $t('kennzeichen', 'License plate', ['width' => 'quarter', 'required' => true]),
            $t('vin', 'VIN', ['width' => 'quarter']),
            $radio('grund', 'Reason', ['Evidence hold', 'Arrest of driver', 'Unlicensed / suspended driver', 'Abandoned', 'Parking violation', 'Stolen vehicle recovered', 'Hazard / blocking traffic'], ['required' => true]),
            $t('tow', 'Tow company / storage', ['width' => 'half']), $num('days', 'Hold (days)', ['width' => 'quarter']),
            $area('inventar', 'Inventory of vehicle contents and damage', ['rows' => 4]),
        ], 'pd_sicherstellung', '', 'optional'),

        // ================================================================ Justice
        'us_complaint' => $def('Criminal Complaint', 'Complaint', 'CC', 'justice', 'prosecutor', 'Criminal Proceedings', 'letter', [
            $case, $t('court', 'Court', ['width' => 'half', 'default' => 'Superior Court of the State of San Andreas']),
            $area('counts', 'Counts (code section, description, date)', ['required' => true, 'rows' => 6, 'list' => 'laws']),
            $area('sachverhalt', 'Statement of probable cause', ['required' => true, 'rows' => 6]),
            $sel('felony', 'Classification', ['Felony', 'Misdemeanor', 'Wobbler']),
        ], 'doj_anklage', "The People of the State of San Andreas, plaintiff, against the defendant named below. The undersigned, on information and belief, complains that the defendant committed the following offenses:"),
        'us_subpoena' => $def('Subpoena', 'Subpoena', 'SUB', 'justice', 'clerk', 'Proceedings', 'letter', [
            $case, $t('empfaenger', 'To (name and address)', ['required' => true]),
            $radio('art', 'Type', ['Subpoena to appear and testify', 'Subpoena duces tecum (produce documents)'], ['required' => true]),
            $date('termin', 'Date', ['required' => true]), $time('uhrzeit', 'Time'), $t('ort', 'Courtroom / location', ['width' => 'half']),
            $area('documents', 'Documents or items to produce', ['rows' => 3]),
        ], 'doj_ladung', "YOU ARE ORDERED TO APPEAR in this court at the date, time and place shown below to testify as a witness. Failure to obey this subpoena may be punished as contempt of court.", 'optional'),
        'us_plea' => $def('Plea Agreement', 'Plea Agreement', 'PLEA', 'justice', 'prosecutor', 'Criminal Proceedings', 'letter', [
            $case, $area('counts', 'Charges pleaded to', ['required' => true, 'rows' => 3, 'list' => 'laws']),
            $radio('plea', 'Plea', ['Guilty', 'No contest (nolo contendere)'], ['required' => true]),
            $area('dismissed', 'Charges to be dismissed', ['rows' => 2]),
            $area('sentence', 'Agreed sentence (custody, fine, probation, conditions)', ['required' => true, 'rows' => 4]),
            $t('verteidiger', 'Defense counsel', ['width' => 'half']),
            $sign('sig_defendant', 'Signature of defendant'), $sign('sig_counsel', 'Signature of defense counsel'),
        ], 'doj_strafbefehl', "The defendant, with the advice of counsel, knowingly and voluntarily waives the right to a jury trial, to confront witnesses and against self-incrimination, and enters the plea stated below in exchange for the terms of this agreement."),
        'us_bail' => $def('Bail Order', 'Bail Order', 'BAIL', 'justice', 'judge', 'Orders', 'letter', [
            $case, $num('amount', 'Bail amount ($)', ['width' => 'third', 'required' => true]),
            $radio('art', 'Type', ['Cash bail', 'Bail bond', 'Own recognizance (OR)', 'Bail denied'], ['required' => true]),
            $area('conditions', 'Conditions of release', ['rows' => 4, 'placeholder' => 'e.g. no contact with victim, surrender passport, curfew 22:00–06:00']),
            $date('next', 'Next court date'),
        ], 'doj_haftbefehl', "Upon review of the charges, the defendant's criminal history and risk of flight, the Court orders as follows:"),
        'us_restraining' => $def('Restraining Order', 'Restraining Order', 'RO', 'justice', 'judge', 'Orders', 'letter', [
            $case, $t('geschuetzt', 'Protected person(s)', ['required' => true]),
            $radio('art', 'Type', ['Emergency protective order', 'Temporary restraining order', 'Restraining order after hearing'], ['required' => true]),
            $num('distance', 'Stay-away distance (yards)', ['width' => 'quarter', 'default' => '100']),
            $area('orders', 'Orders', ['required' => true, 'rows' => 4, 'default' => "Do not harass, threaten, contact or follow the protected person(s).\nStay away from their home, workplace and vehicle."]),
            $date('gueltig_bis', 'Expires on', ['required' => true]),
        ], 'doj_beschluss', "Violation of this order is a crime and may result in arrest, a fine and/or imprisonment.", 'required', 'round', 'gueltig_bis'),
        'us_probation' => $def('Order of Probation', 'Probation', 'PROB', 'justice', 'judge', 'Orders', 'letter', [
            $case, $num('months', 'Probation term (months)', ['width' => 'quarter', 'required' => true]),
            $sel('art', 'Type', ['Summary (informal)', 'Formal (supervised)']),
            $area('conditions', 'Conditions', ['required' => true, 'rows' => 5, 'default' => "Obey all laws.\nReport to the probation officer as directed.\nSubmit to search and seizure at any time.\nDo not possess firearms or ammunition."]),
            $t('officer', 'Probation officer', ['width' => 'half']),
        ], 'doj_urteil'),

        // ================================================================ Medical
        'us_dnr' => $def('Do Not Resuscitate Order (DNR / POLST)', 'DNR / POLST', 'DNR', 'medical', 'doctor', 'Consent Forms', 'protocol', [
            $head('A – Cardiopulmonary resuscitation (no pulse and not breathing)'),
            $radio('cpr', 'CPR', ['Attempt resuscitation / CPR', 'Do not attempt resuscitation (DNR)'], ['required' => true]),
            $head('B – Medical interventions (pulse and/or breathing)'),
            $radio('level', 'Treatment level', ['Full treatment (including intubation)', 'Selective treatment (no intubation)', 'Comfort-focused treatment only'], ['required' => true]),
            $radio('nutrition', 'Artificially administered nutrition', ['Long-term', 'Trial period', 'None']),
            $area('notes', 'Additional orders', ['rows' => 2]),
            $t('decision_maker', 'Discussed with (patient / legal decision maker)', ['width' => 'half']),
            $sign('sig_patient', 'Signature of patient or decision maker'),
        ], 'med_patientenverfuegung', '', 'required', 'rect'),
        'us_hipaa' => $def('Authorization for Release of Protected Health Information (HIPAA)', 'HIPAA Release', 'HIPAA', 'medical', 'doctor', 'Consent Forms', 'letter', [
            $t('empfaenger', 'Release to (name / agency)', ['required' => true]),
            ['name' => 'scope', 'label' => 'Information to be released', 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine(
                ['Entire medical record', 'Emergency / ER records', 'Lab and toxicology results', 'Imaging reports', 'Mental health records', 'Fitness-for-duty results only'],
                ['Entire medical record', 'Emergency / ER records', 'Lab and toxicology results', 'Imaging reports', 'Mental health records', 'Fitness-for-duty results only'])],
            $t('zweck', 'Purpose', ['width' => 'half', 'default' => 'At the request of the patient']),
            $date('gueltig_bis', 'This authorization expires on', ['required' => true]),
            $sign('sig_patient', 'Signature of patient'),
        ], 'med_schweigepflicht', "I authorize the release of my protected health information as described below. I understand that I may revoke this authorization in writing at any time and that treatment does not depend on my signing it.", 'required', 'rect', 'gueltig_bis'),

        // ================================================================ Fire
        'us_burn_permit' => $def('Burn Permit', 'Burn Permit', 'BP', 'fire', 'fire', 'Fire Prevention', 'letter', [
            $t('ort', 'Burn location (address / parcel)', ['required' => true]),
            $radio('art', 'Type of burn', ['Residential debris', 'Agricultural', 'Campfire / recreational', 'Controlled / prescribed burn', 'Special event (bonfire, pyrotechnics)'], ['required' => true]),
            $date('von', 'Valid from', ['required' => true, 'default' => 'today']), $date('gueltig_bis', 'Valid until', ['required' => true]),
            $area('conditions', 'Conditions', ['rows' => 4, 'default' => "Burn only on permissive burn days.\nAttended at all times with water source and tools on site.\nPiles no larger than 4 ft × 4 ft; 25 ft clearance from structures."]),
        ], 'fd_bescheinigung', "This permit may be revoked at any time if conditions become unsafe or the conditions below are not met.", 'required', 'round', 'gueltig_bis'),

        // ================================================================ Government
        'us_marriage' => $def('Certificate of Marriage', 'Marriage Certificate', 'MC', 'government', 'gov', 'Vital Records', 'letter', [
            $t('spouse2', 'Spouse (second party) – full name', ['required' => true]), $t('spouse2_id', 'Citizen ID (second party)', ['width' => 'third']),
            $date('datum', 'Date of marriage', ['required' => true, 'default' => 'today']), $t('ort', 'Place of ceremony', ['width' => 'half']),
            $t('officiant', 'Officiant', ['width' => 'half']), $t('zeuge1', 'Witness', ['width' => 'half']), $t('zeuge2', 'Witness', ['width' => 'half']),
            $sign('sig_spouse1', 'Signature of spouse'), $sign('sig_spouse2', 'Signature of spouse'),
        ], 'gov_bescheinigung', "This is to certify that the persons named below were united in marriage in accordance with the laws of the State of San Andreas."),
    ];
}
