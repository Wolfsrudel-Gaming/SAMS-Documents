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
    if (doc_us()) {
        // US-Gestaltung: eigene Druckvorlage bzw. US-Familie der Vorlagen-Engine
        // eigene Datei app/templates/us_<vorlage>.php wird automatisch verwendet
        $tpl = (string) ($t['template'] ?? '');
        $t['template'] = DOC_US_TEMPLATES[$key] ?? ($tpl === 'custom' ? 'us_custom' : (is_file(SAMS_ROOT . '/app/templates/us_' . $tpl . '.php') ? 'us_' . $tpl : $tpl));
    }
    $us = doc_us();
    $t['fields'] = array_map(fn($f) => doc_field_us($f, $us ? (DOC_US_DROP_OPTIONS[$key][$f['name'] ?? ''] ?? []) : []), $t['fields'] ?? []);
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

/**
 * Eingebaute Arten für Gestaltung und Sprache: Arten nur eines Sets ausblenden, Texte über dt() (Dokumentsprache),
 * bei US-Gestaltung US-Druckvorlagen.
 */
function doc_types_us_apply(array $types, string $region): array
{
    $out = [];
    foreach ($types as $key => $t) {
        if (($t['region'] ?? $region) !== $region) {
            continue;
        }
        $out[$key] = $region === 'de' && !doc_en() ? $t : doc_type_us((string) $key, $t);
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
    $all = document_types_all();
    foreach (document_types_us_only() as $key => $t) {
        $like = (string) ($t['like'] ?? '');
        if ($like === '' || (($all[$like]['role'] ?? null) !== ($t['role'] ?? ''))) {
            continue; // nur Ränge, die eine Art derselben Rolle ausstellen dürfen
        }
        $given = 0;
        foreach (all('SELECT rank_id FROM rank_permissions WHERE permission = ?', ['documents.issue.' . $like]) as $r) {
            if (!scalar('SELECT 1 FROM rank_permissions WHERE rank_id = ? AND permission = ?', [$r['rank_id'], 'documents.issue.' . $key])) {
                insert('rank_permissions', ['rank_id' => $r['rank_id'], 'permission' => 'documents.issue.' . $key]);
                $given++;
            }
        }
        if ($given) {
            audit('settings.doc_region_grant', 'setting', null, $key . ': Recht an ' . $given . ' Rang/Ränge mit „' . $like . '“ vergeben');
        }
        $n += $given;
    }
    return $n;
}

/** Dokumentarten, die es nur im US-Set gibt (deutsche Texte, bei Dokumentsprache Englisch über dt() übersetzt; Vorlagen-Engine). */
function document_types_us_only(): array
{
    $t = fn(string $name, string $label, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'text', 'width' => 'full'];
    $date = fn(string $name, string $label, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'date', 'width' => 'quarter'];
    $time = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'time', 'width' => 'quarter'];
    $num = fn(string $name, string $label, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'number', 'width' => 'quarter'];
    $area = fn(string $name, string $label, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'textarea', 'rows' => 4, 'width' => 'full'];
    $sel = fn(string $name, string $label, array $opts, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'select', 'width' => 'third', 'options' => array_combine($opts, $opts)];
    $radio = fn(string $name, string $label, array $opts, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'radio', 'width' => 'full', 'options' => array_combine($opts, $opts)];
    $check = fn(string $name, string $label, array $o = []) => $o + ['name' => $name, 'label' => $label, 'type' => 'checkbox', 'width' => 'third'];
    $head = fn(string $label) => ['type' => 'heading', 'label' => $label];
    $sign = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'signature', 'width' => 'half'];
    $case = $t('aktenzeichen', 'Fallnummer', ['width' => 'third', 'placeholder' => 'z. B. LS-CR-2026-00123']);

    $def = function (string $title, string $short, string $code, string $kind, string $role, string $category, string $layout, array $fields, string $like, string $body = '', string $patient = 'required', string $seal = 'round', ?string $valid = null): array {
        return array_filter([
            'title' => $title, 'short' => $short, 'code' => $code, 'category' => $category, 'role' => $role, 'kinds' => [$kind], 'region' => 'us', 'like' => $like,
            'patient' => $patient, 'template' => 'custom', 'fields' => $fields, 'valid' => $valid,
            'custom' => ['layout' => $layout, 'body' => $body, 'seal' => $seal, 'title' => $title],
        ], fn($v) => $v !== null);
    };

    return [
        // ================================================================ Polizei
        'us_citation' => $def('Strafzettel / Vorladung (Citation)', 'Strafzettel', 'CIT', 'police', 'officer', 'Verkehr', 'protocol', [
            $head('Verstoß'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'),
            $t('ort', 'Ort (Straße / Querstraße)', ['width' => 'half', 'required' => true]),
            $radio('art', 'Art', ['Verkehrsordnungswidrigkeit (Infraction)', 'Vergehen (Misdemeanor)', 'Parkverstoß', 'Mangel am Fahrzeug (Fix-it-Ticket)'], ['required' => true, 'default' => 'Verkehrsordnungswidrigkeit (Infraction)']),
            $t('delikte', 'Verstoß / Verstöße – Paragraf und Beschreibung', ['required' => true, 'list' => 'laws']),
            $t('fahrzeug', 'Fahrzeug (Marke, Modell, Farbe)', ['width' => 'half']), $t('kennzeichen', 'Kennzeichen', ['width' => 'quarter']),
            $num('speed', 'Gefahrene Geschwindigkeit (mph)', ['width' => 'quarter']), $num('limit', 'Erlaubte Höchstgeschwindigkeit (mph)', ['width' => 'quarter']),
            $head('Strafe'), $num('fine', 'Bußgeld ($)', ['width' => 'quarter']), $date('due', 'Zahlen bzw. erscheinen bis'),
            $t('court', 'Gericht', ['width' => 'half', 'default' => 'Superior Court von San Andreas']),
            $check('appearance', 'Persönliches Erscheinen vor Gericht erforderlich'),
            $area('notes', 'Anmerkungen des Beamten / der Beamtin', ['rows' => 2]),
            $sign('sig_violator', 'Unterschrift (kein Schuldeingeständnis – Zusage des Erscheinens)'),
        ], 'pd_platzverweis', "Ohne damit eine Schuld einzugestehen, verpflichte ich mich, zur angegebenen Zeit am angegebenen Ort zu erscheinen oder das Bußgeld bis zum Fälligkeitsdatum zu bezahlen."),
        'us_miranda' => $def('Miranda-Belehrung und Verzichtserklärung', 'Miranda-Verzicht', 'MIR', 'police', 'officer', 'Ermittlungen', 'letter', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit der Belehrung'), $t('ort', 'Ort', ['width' => 'half']),
            $case,
            $radio('waiver', 'Entscheidung', ['Ich habe meine Rechte verstanden und möchte ohne Anwalt aussagen', 'Ich mache von meinem Recht zu schweigen Gebrauch', 'Ich verlange einen Anwalt'], ['required' => true]),
            $t('zeuge', 'Anwesende/r Beamte/r als Zeuge/Zeugin', ['width' => 'half']),
            $sign('sig_suspect', 'Unterschrift der belehrten Person'),
        ], 'pd_vernehmung', "**Sie haben das Recht zu schweigen.** Alles, was Sie sagen, kann und wird vor Gericht gegen Sie verwendet werden.\n**Sie haben das Recht auf einen Anwalt** und darauf, dass er während der Vernehmung anwesend ist. Wenn Sie sich keinen Anwalt leisten können, wird Ihnen auf Wunsch vor jeder Vernehmung einer gestellt.\nSie können jederzeit entscheiden, von diesen Rechten Gebrauch zu machen und keine Fragen zu beantworten oder keine Aussagen zu machen.\n\nIch habe diese Belehrung über meine Rechte gelesen und verstehe, welche Rechte ich habe."),
        'us_use_of_force' => $def('Bericht über Gewaltanwendung (Use of Force)', 'Gewaltanwendung', 'UOF', 'police', 'officer', 'Streifendienst', 'protocol', [
            $head('Vorfall'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Ort', ['width' => 'half', 'required' => true]),
            $case, $t('anlass', 'Anlass des Einschreitens', ['width' => 'half']),
            $head('Angewandte Gewalt'), ['name' => 'force', 'label' => 'Eingesetzte Zwangsmittel', 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine(
                ['Verbale Anweisungen', 'Haltegriff / zu Boden gebracht', 'Handschellen / Fesselung', 'Pfefferspray (OC)', 'Schlagstock / Schlagwaffe', 'Taser / Elektroimpulswaffe', 'Diensthund (K9)', 'Weniger letale Munition (Less-lethal)', 'Schusswaffe auf Person gerichtet', 'Schusswaffe abgefeuert', 'Eingreifen mit Fahrzeug (PIT-Manöver)'],
                ['Verbale Anweisungen', 'Haltegriff / zu Boden gebracht', 'Handschellen / Fesselung', 'Pfefferspray (OC)', 'Schlagstock / Schlagwaffe', 'Taser / Elektroimpulswaffe', 'Diensthund (K9)', 'Weniger letale Munition (Less-lethal)', 'Schusswaffe auf Person gerichtet', 'Schusswaffe abgefeuert', 'Eingreifen mit Fahrzeug (PIT-Manöver)'])],
            $sel('resistance', 'Widerstand der Person', ['Passiv', 'Aktiv', 'Tätlicher Angriff', 'Lebensbedrohlich']),
            $area('hergang', 'Sachverhalt – warum die Gewaltanwendung notwendig und verhältnismäßig war', ['required' => true, 'rows' => 6]),
            $head('Verletzungen und medizinische Versorgung'), $area('verletzungen', 'Verletzungen (Person / Beamte / Unbeteiligte)', ['rows' => 2]),
            $check('ems', 'Rettungsdienst angefordert'), $check('hospital', 'Ins Krankenhaus transportiert'), $check('photos', 'Verletzungen fotografiert'),
            $t('supervisor', 'Informierte/r Vorgesetzte/r', ['width' => 'half']), $t('bodycam', 'Verweis auf Bodycam- / Dashcam-Aufnahme', ['width' => 'half']),
        ], 'pd_festnahme', '', 'optional'),
        'us_incident' => $def('Ereignisbericht (Incident Report)', 'Ereignisbericht', 'IR', 'police', 'officer', 'Ermittlungen', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Ort', ['width' => 'half', 'required' => true]), $case,
            $t('art', 'Art des Ereignisses', ['width' => 'half', 'required' => true, 'placeholder' => 'z. B. Einbruch, Körperverletzung, Ruhestörung']),
            $t('delikte', 'Delikt(e)', ['list' => 'laws']),
            $area('beteiligte', 'Beteiligte Personen (Geschädigte/r / Tatverdächtige/r / Zeuge/Zeugin / Anzeigeerstatter/in)', ['rows' => 3]),
            $area('sachverhalt', 'Sachverhalt', ['required' => true, 'rows' => 8]),
            $area('beweise', 'Sichergestellte Beweismittel / Gegenstände', ['rows' => 2]),
            $sel('status', 'Bearbeitungsstand', ['Offen', 'Abgeschlossen – Festnahme', 'Abgeschlossen – aufgeklärt', 'An die Kriminalpolizei (Detectives) abgegeben', 'Unbegründet']),
        ], 'pd_anzeige', '', 'optional'),
        'us_dui' => $def('DUI-Festnahmebericht und Standardisierte Nüchternheitstests', 'DUI-Bericht', 'DUI', 'police', 'officer', 'Verkehr', 'protocol', [
            $head('Anhaltung'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Ort', ['width' => 'half', 'required' => true]),
            $t('fahrzeug', 'Fahrzeug', ['width' => 'half']), $t('kennzeichen', 'Kennzeichen', ['width' => 'quarter']), $t('grund', 'Grund der Anhaltung', ['width' => 'full']),
            $head('Beobachtungen'), ['name' => 'signs', 'label' => 'Anzeichen einer Beeinträchtigung', 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine(
                ['Alkoholgeruch', 'Verwaschene Aussprache', 'Gerötete / wässrige Augen', 'Unsicherer Stand / Gang', 'Geöffnetes Alkoholbehältnis (Open Container)', 'Alkoholkonsum eingeräumt', 'Marihuanageruch'],
                ['Alkoholgeruch', 'Verwaschene Aussprache', 'Gerötete / wässrige Augen', 'Unsicherer Stand / Gang', 'Geöffnetes Alkoholbehältnis (Open Container)', 'Alkoholkonsum eingeräumt', 'Marihuanageruch'])],
            $head('Standardisierte Nüchternheitstests (SFST)'),
            $sel('hgn', 'Horizontaler Blickrichtungsnystagmus (Auffälligkeiten von 6)', ['0', '1', '2', '3', '4', '5', '6', 'Verweigert', 'Nicht durchgeführt']),
            $sel('walk', 'Gehen und Drehen (Auffälligkeiten von 8)', ['0', '1', '2', '3', '4', '5', '6', '7', '8', 'Verweigert', 'Nicht durchgeführt']),
            $sel('stand', 'Einbeinstand (Auffälligkeiten von 4)', ['0', '1', '2', '3', '4', 'Verweigert', 'Nicht durchgeführt']),
            $t('pas', 'Ergebnis Atemalkohol-Vortest (PAS)', ['width' => 'third', 'placeholder' => 'z. B. 0.12 % BAK']),
            $radio('chemical', 'Beweissicherer Test', ['Atemalkohol', 'Blutprobe', 'Verweigert (über Implied Consent belehrt)'], ['required' => true]),
            $t('bac', 'Ergebnis (BAK)', ['width' => 'third']),
            $area('notes', 'Sachverhalt', ['rows' => 4]),
        ], 'pd_unfall'),
        'us_impound' => $def('Abschlepp- / Verwahrungsbericht', 'Verwahrung', 'TOW', 'police', 'officer', 'Verkehr', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Ort', ['width' => 'half', 'required' => true]),
            $t('fahrzeug', 'Fahrzeug (Marke, Modell, Farbe)', ['width' => 'half', 'required' => true]), $t('kennzeichen', 'Kennzeichen', ['width' => 'quarter', 'required' => true]),
            $t('vin', 'Fahrgestellnummer (VIN)', ['width' => 'quarter']),
            $radio('grund', 'Grund', ['Sicherstellung als Beweismittel', 'Festnahme des Fahrers / der Fahrerin', 'Fahren ohne bzw. trotz entzogener Fahrerlaubnis', 'Zurückgelassenes Fahrzeug', 'Falschparken', 'Gestohlenes Fahrzeug aufgefunden', 'Gefahr / Verkehrsbehinderung'], ['required' => true]),
            $t('tow', 'Abschleppunternehmen / Verwahrort', ['width' => 'half']), $num('days', 'Verwahrdauer (Tage)', ['width' => 'quarter']),
            $area('inventar', 'Inventar des Fahrzeuginhalts und Schäden', ['rows' => 4]),
        ], 'pd_sicherstellung', '', 'optional'),

        // ================================================================ Justiz
        'us_complaint' => $def('Strafanzeige der Staatsanwaltschaft (Criminal Complaint)', 'Anklage (Complaint)', 'CC', 'justice', 'prosecutor', 'Strafverfahren', 'letter', [
            $case, $t('court', 'Gericht', ['width' => 'half', 'default' => 'Superior Court des Staates San Andreas']),
            $area('counts', 'Anklagepunkte (Paragraf, Beschreibung, Datum)', ['required' => true, 'rows' => 6, 'list' => 'laws']),
            $area('sachverhalt', 'Darlegung des hinreichenden Tatverdachts (Probable Cause)', ['required' => true, 'rows' => 6]),
            $sel('felony', 'Einstufung', ['Verbrechen (Felony)', 'Vergehen (Misdemeanor)', 'Verbrechen oder Vergehen (Wobbler)']),
        ], 'doj_anklage', "Das Volk des Staates San Andreas (The People), Anklägerin, gegen die unten genannte angeklagte Person. Die unterzeichnende Person erhebt aufgrund der vorliegenden Erkenntnisse den Vorwurf, dass die angeklagte Person folgende Straftaten begangen hat:"),
        'us_subpoena' => $def('Zeugenladung (Subpoena)', 'Zeugenladung', 'SUB', 'justice', 'clerk', 'Verfahren', 'letter', [
            $case, $t('empfaenger', 'An (Name und Anschrift)', ['required' => true]),
            $radio('art', 'Art', ['Ladung zum Erscheinen und zur Aussage', 'Ladung zur Vorlage von Unterlagen (Subpoena duces tecum)'], ['required' => true]),
            $date('termin', 'Termin', ['required' => true]), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Gerichtssaal / Ort', ['width' => 'half']),
            $area('documents', 'Vorzulegende Unterlagen oder Gegenstände', ['rows' => 3]),
        ], 'doj_ladung', "SIE WERDEN HIERMIT GELADEN, zu dem unten angegebenen Termin, zur angegebenen Uhrzeit und am angegebenen Ort vor diesem Gericht zu erscheinen und als Zeuge/Zeugin auszusagen. Wer dieser Ladung nicht Folge leistet, kann wegen Missachtung des Gerichts (Contempt of Court) bestraft werden.", 'optional'),
        'us_plea' => $def('Strafabsprache (Plea Agreement)', 'Strafabsprache', 'PLEA', 'justice', 'prosecutor', 'Strafverfahren', 'letter', [
            $case, $area('counts', 'Anklagepunkte, auf die sich die Erklärung bezieht', ['required' => true, 'rows' => 3, 'list' => 'laws']),
            $radio('plea', 'Erklärung zur Schuldfrage (Plea)', ['Schuldig', 'Keine Anfechtung der Anklage (nolo contendere)'], ['required' => true]),
            $area('dismissed', 'Fallengelassene Anklagepunkte', ['rows' => 2]),
            $area('sentence', 'Vereinbarte Strafe (Haft, Geldstrafe, Bewährung, Auflagen)', ['required' => true, 'rows' => 4]),
            $t('verteidiger', 'Verteidigung (Rechtsanwalt/Rechtsanwältin)', ['width' => 'half']),
            $sign('sig_defendant', 'Unterschrift der angeklagten Person'), $sign('sig_counsel', 'Unterschrift der Verteidigung'),
        ], 'doj_anklage', "Die angeklagte Person verzichtet nach Beratung durch ihre Verteidigung wissentlich und freiwillig auf das Recht auf ein Geschworenenverfahren, auf die Befragung der Zeugen und auf das Recht, sich nicht selbst belasten zu müssen, und gibt im Gegenzug für die Bedingungen dieser Vereinbarung die unten genannte Erklärung ab."),
        'us_bail' => $def('Kautionsbeschluss', 'Kautionsbeschluss', 'BAIL', 'justice', 'judge', 'Beschlüsse und Anordnungen', 'letter', [
            $case, $num('amount', 'Kautionshöhe ($)', ['width' => 'third', 'required' => true]),
            $radio('art', 'Art', ['Barkaution', 'Kautionsbürgschaft (Bail Bond)', 'Freilassung ohne Kaution (Own Recognizance)', 'Kaution abgelehnt'], ['required' => true]),
            $area('conditions', 'Auflagen für die Freilassung', ['rows' => 4, 'placeholder' => 'z. B. kein Kontakt zum Opfer, Abgabe des Reisepasses, Ausgangssperre 22:00–06:00']),
            $date('next', 'Nächster Gerichtstermin'),
        ], 'doj_haftbefehl', "Nach Prüfung der Anklagepunkte, der Vorstrafen der angeklagten Person und der Fluchtgefahr ordnet das Gericht Folgendes an:"),
        'us_restraining' => $def('Kontaktverbot / Schutzanordnung (Restraining Order)', 'Kontaktverbot', 'RO', 'justice', 'judge', 'Beschlüsse und Anordnungen', 'letter', [
            $case, $t('geschuetzt', 'Geschützte Person(en)', ['required' => true]),
            $radio('art', 'Art', ['Notfall-Schutzanordnung (Emergency Protective Order)', 'Vorläufiges Kontaktverbot (Temporary Restraining Order)', 'Kontaktverbot nach Anhörung'], ['required' => true]),
            $num('distance', 'Mindestabstand (yards)', ['width' => 'quarter', 'default' => '100']),
            $area('orders', 'Anordnungen', ['required' => true, 'rows' => 4, 'default' => "Die geschützte(n) Person(en) weder belästigen, bedrohen, kontaktieren noch verfolgen.\nWohnung, Arbeitsplatz und Fahrzeug der geschützten Person(en) fernbleiben."]),
            $date('gueltig_bis', 'Läuft ab am', ['required' => true]),
        ], 'doj_beschluss', "Ein Verstoß gegen diese Anordnung ist eine Straftat und kann Festnahme, Geldstrafe und/oder Freiheitsstrafe nach sich ziehen.", 'required', 'round', 'gueltig_bis'),
        'us_probation' => $def('Bewährungsbeschluss (Probation)', 'Bewährung (Probation)', 'PROB', 'justice', 'judge', 'Beschlüsse und Anordnungen', 'letter', [
            $case, $num('months', 'Bewährungszeit (Monate)', ['width' => 'quarter', 'required' => true]),
            $sel('art', 'Art', ['Einfache Bewährung (ohne Aufsicht)', 'Förmliche Bewährung (unter Aufsicht)']),
            $area('conditions', 'Auflagen', ['required' => true, 'rows' => 5, 'default' => "Alle Gesetze befolgen.\nSich nach Anweisung bei der Bewährungshilfe melden.\nDurchsuchungen und Beschlagnahmen jederzeit dulden.\nKeine Schusswaffen oder Munition besitzen."]),
            $t('officer', 'Bewährungshelfer/in', ['width' => 'half']),
        ], 'doj_urteil'),

        // ================================================================ Medizin
        'us_dnr' => $def('Patientenverfügung zur Wiederbelebung (DNR / POLST)', 'DNR / POLST', 'DNR', 'medical', 'doctor', 'Einwilligungen und Verfügungen', 'protocol', [
            $head('A – Herz-Lungen-Wiederbelebung (kein Puls und keine Atmung)'),
            $radio('cpr', 'Wiederbelebung (CPR)', ['Wiederbelebung versuchen / CPR', 'Keine Wiederbelebung versuchen (DNR)'], ['required' => true]),
            $head('B – Medizinische Maßnahmen (Puls und/oder Atmung vorhanden)'),
            $radio('level', 'Behandlungsumfang', ['Volle Behandlung (einschließlich Intubation)', 'Eingeschränkte Behandlung (keine Intubation)', 'Ausschließlich lindernde Behandlung (Comfort Care)'], ['required' => true]),
            $radio('nutrition', 'Künstlich zugeführte Ernährung', ['Langfristig', 'Befristeter Versuch', 'Keine künstliche Ernährung']),
            $area('notes', 'Weitere Anordnungen', ['rows' => 2]),
            $t('decision_maker', 'Besprochen mit (Patient/in / rechtliche Vertretung)', ['width' => 'half']),
            $sign('sig_patient', 'Unterschrift Patient/in oder rechtliche Vertretung'),
        ], 'attest', '', 'required', 'rect'),
        'us_hipaa' => $def('Schweigepflichtentbindung (HIPAA)', 'HIPAA-Entbindung', 'HIPAA', 'medical', 'doctor', 'Einwilligungen und Verfügungen', 'letter', [
            $t('empfaenger', 'Weitergabe an (Name / Behörde)', ['required' => true]),
            ['name' => 'scope', 'label' => 'Freigegebene Informationen', 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine(
                ['Gesamte Patientenakte', 'Notfall- / Notaufnahmeunterlagen', 'Labor- und Toxikologiebefunde', 'Befunde der Bildgebung', 'Unterlagen zur psychischen Gesundheit', 'Nur Ergebnis der Diensttauglichkeitsprüfung'],
                ['Gesamte Patientenakte', 'Notfall- / Notaufnahmeunterlagen', 'Labor- und Toxikologiebefunde', 'Befunde der Bildgebung', 'Unterlagen zur psychischen Gesundheit', 'Nur Ergebnis der Diensttauglichkeitsprüfung'])],
            $t('zweck', 'Zweck', ['width' => 'half', 'default' => 'Auf Wunsch des Patienten / der Patientin']),
            $date('gueltig_bis', 'Diese Entbindung gilt bis', ['required' => true]),
            $sign('sig_patient', 'Unterschrift des Patienten / der Patientin'),
        ], 'attest', "Ich willige in die Weitergabe meiner geschützten Gesundheitsdaten wie unten beschrieben ein. Mir ist bekannt, dass ich diese Einwilligung jederzeit schriftlich widerrufen kann und dass meine Behandlung nicht von meiner Unterschrift abhängt.", 'required', 'rect', 'gueltig_bis'),

        // ================================================================ Feuerwehr
        'us_burn_permit' => $def('Feuererlaubnis (Burn Permit)', 'Feuererlaubnis', 'BP', 'fire', 'fire', 'Vorbeugender Brandschutz', 'letter', [
            $t('ort', 'Ort des Feuers (Anschrift / Grundstück)', ['required' => true]),
            $radio('art', 'Art des Feuers', ['Gartenabfälle (privat)', 'Landwirtschaftlich', 'Lagerfeuer / Freizeit', 'Kontrolliertes Abbrennen (Prescribed Burn)', 'Veranstaltung (Freudenfeuer, Pyrotechnik)'], ['required' => true]),
            $date('von', 'Gültig ab', ['required' => true, 'default' => 'today']), $date('gueltig_bis', 'Gültig bis', ['required' => true]),
            $area('conditions', 'Auflagen', ['rows' => 4, 'default' => "Nur an freigegebenen Brenntagen verbrennen.\nFeuer ständig beaufsichtigen; Wasser und Werkzeug vor Ort bereithalten.\nHaufen höchstens 4 ft × 4 ft; 25 ft Abstand zu Gebäuden."]),
        ], 'fd_einsatz', "Diese Erlaubnis kann jederzeit widerrufen werden, wenn die Lage unsicher wird oder die unten genannten Auflagen nicht eingehalten werden.", 'required', 'round', 'gueltig_bis'),

        // ================================================================ Verwaltung
        'us_marriage' => $def('Heiratsurkunde', 'Eheurkunde', 'MC', 'government', 'gov', 'Personenstandswesen', 'letter', [
            $t('spouse2', 'Ehepartner/in (zweite Person) – vollständiger Name', ['required' => true]), $t('spouse2_id', 'Bürger-ID (zweite Person)', ['width' => 'third']),
            $date('datum', 'Datum der Eheschließung', ['required' => true, 'default' => 'today']), $t('ort', 'Ort der Trauung', ['width' => 'half']),
            $t('officiant', 'Trauung vollzogen durch', ['width' => 'half']), $t('zeuge1', 'Trauzeuge/Trauzeugin', ['width' => 'half']), $t('zeuge2', 'Trauzeuge/Trauzeugin', ['width' => 'half']),
            $sign('sig_spouse1', 'Unterschrift Ehepartner/in'), $sign('sig_spouse2', 'Unterschrift Ehepartner/in'),
        ], 'gov_bescheinigung', "Hiermit wird bescheinigt, dass die unten genannten Personen nach den Gesetzen des Staates San Andreas die Ehe miteinander geschlossen haben."),
    ];
}
