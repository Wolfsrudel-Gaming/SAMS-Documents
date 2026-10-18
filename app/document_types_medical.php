<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Weitere Dokumentarten für Rettungsdienst und Klinik (Vorlagen-Engine, beim
 * Ausstellen eingefroren). Ergänzen die fest gestalteten Formulare in documents.php.
 */
function document_types_medical(): array
{
    $t = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'text', 'width' => 'full'] + $o;
    $date = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'date', 'width' => 'quarter'] + $o;
    $time = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'time', 'width' => 'quarter'];
    $num = fn(string $name, string $label, string $w = 'quarter') => ['name' => $name, 'label' => $label, 'type' => 'number', 'width' => $w];
    $area = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'textarea', 'rows' => 4, 'width' => 'full'] + $o;
    $sel = fn(string $name, string $label, array $opts, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'select', 'width' => 'third', 'options' => array_combine($opts, $opts)] + $o;
    $radio = fn(string $name, string $label, array $opts, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'radio', 'width' => 'full', 'options' => array_combine($opts, $opts)] + $o;
    $multi = fn(string $name, string $label, array $opts) => ['name' => $name, 'label' => $label, 'type' => 'checkboxes', 'width' => 'full', 'options' => array_combine($opts, $opts)];
    $check = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'checkbox', 'width' => 'third'] + $o;
    $head = fn(string $label) => ['type' => 'heading', 'label' => $label];
    $sign = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'signature', 'width' => 'half'];
    $icd = fn(string $name = 'diagnose', string $label = 'Diagnose(n)') => ['name' => $name, 'label' => $label, 'type' => 'textarea', 'rows' => 2, 'width' => 'full', 'list' => 'icd'];
    $vitals = [
        $num('rr', 'RR (mmHg)'), $num('hf', 'Herzfrequenz /min'), $num('spo2', 'SpO₂ %'), $num('af', 'Atemfrequenz /min'),
        $num('temp', 'Temperatur °C'), $num('bz', 'Blutzucker mg/dl'), $num('gcs', 'GCS'), $num('schmerz', 'Schmerz (NRS 0–10)'),
    ];

    $def = function (string $title, string $short, string $code, string $role, string $category, string $layout, array $fields, string $body = '', string $patient = 'required', string $seal = 'rect', ?string $valid = null): array {
        return array_filter([
            'title' => $title, 'short' => $short, 'code' => $code, 'category' => $category, 'role' => $role, 'kinds' => ['medical'],
            'patient' => $patient, 'template' => 'custom', 'fields' => $fields, 'valid' => $valid,
            'custom' => ['layout' => $layout, 'body' => $body, 'seal' => $seal, 'title' => $title],
        ], fn($v) => $v !== null);
    };

    return [
        // ===================================================== Notaufnahme / Klinik
        'med_aufnahme' => $def('Aufnahmebogen Notaufnahme', 'Aufnahme', 'NA', 'medic', 'Klinik', 'protocol', array_merge([
            $head('Aufnahme'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'),
            $sel('zuweisung', 'Zuweisung', ['Rettungsdienst', 'Notarzt', 'Selbsteinweisung', 'Polizei', 'Hausarzt', 'Verlegung']),
            $sel('triage', 'Ersteinschätzung (MTS)', ['Rot – sofort', 'Orange – sehr dringend (10 min)', 'Gelb – dringend (30 min)', 'Grün – normal (90 min)', 'Blau – nicht dringend (120 min)'], ['required' => true]),
            $area('anlass', 'Vorstellungsgrund / Leitsymptom', ['required' => true, 'rows' => 2]), $area('anamnese', 'Anamnese (SAMPLER)', ['rows' => 4]),
            $head('Vitalparameter'),
        ], $vitals, [
            $head('Erstversorgung'), $area('befund', 'Befund', ['rows' => 3]), $area('massnahmen', 'Maßnahmen', ['rows' => 3]),
            $sel('weiter', 'Weiteres Vorgehen', ['Ambulant', 'Stationäre Aufnahme', 'Intensivstation', 'OP', 'Verlegung', 'Entlassung gegen ärztlichen Rat']),
        ])),
        'med_triage' => $def('Sichtungskarte (MANV)', 'Sichtungskarte', 'SK', 'medic', 'Rettungsdienst', 'protocol', [
            $t('nummer', 'Patientennummer / Anhänger-Nr.', ['width' => 'third', 'required' => true]), $date('datum', 'Datum', ['default' => 'today']), $time('uhrzeit', 'Sichtung um'),
            $radio('kategorie', 'Sichtungskategorie', ['T1 – rot – akute vitale Bedrohung', 'T2 – gelb – schwer verletzt', 'T3 – grün – leicht verletzt', 'T4 – blau – ohne Überlebenschance', 'Tot – schwarz'], ['required' => true]),
            $area('verletzungen', 'Verletzungen', ['rows' => 3]), $area('massnahmen', 'Durchgeführte Maßnahmen', ['rows' => 2]),
            $t('einsatzort', 'Einsatzort / Schadensereignis'), $sel('transport', 'Transport', ['RTW', 'RTH', 'KTW', 'Bus / Sammeltransport', 'kein Transport']), $t('ziel', 'Zielklinik', ['width' => 'third']),
        ], '', 'optional', 'none'),
        'med_reanimation' => $def('Reanimationsprotokoll', 'Reanimation', 'RP', 'medic', 'Rettungsdienst', 'protocol', [
            $head('Ereignis'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('kollaps', 'Kollaps'), $time('beginn_cpr', 'Beginn CPR'), $time('ende', 'Ende'),
            $radio('beobachtet', 'Kollaps', ['beobachtet', 'nicht beobachtet']), $check('laienreanimation', 'Laienreanimation'), $check('aed', 'AED durch Ersthelfer'),
            $sel('erster_rhythmus', 'Erster Rhythmus', ['Kammerflimmern', 'pulslose VT', 'Asystolie', 'PEA']),
            $head('Maßnahmen'), $num('schocks', 'Anzahl Schocks'), $num('adrenalin', 'Adrenalin gesamt (mg)'), $num('amiodaron', 'Amiodaron gesamt (mg)'),
            $sel('atemweg', 'Atemweg', ['Beutel-Maske', 'Larynxtubus', 'Endotracheale Intubation']), $area('verlauf', 'Verlauf / reversible Ursachen (4H/HITS)', ['rows' => 5]),
            $radio('ergebnis', 'Ergebnis', ['ROSC – Transport', 'Reanimation abgebrochen – Tod festgestellt', 'Unter laufender CPR transportiert'], ['required' => true]), $time('rosc', 'ROSC um'),
        ]),
        'med_narkose' => $def('Narkoseprotokoll', 'Narkose', 'NP', 'doctor', 'OP und Anästhesie', 'protocol', array_merge([
            $head('Narkose'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('einleitung', 'Einleitung'), $time('schnitt', 'Schnitt'), $time('ende', 'Narkoseende'),
            $sel('asa', 'ASA-Klasse', ['ASA I', 'ASA II', 'ASA III', 'ASA IV', 'ASA V']), $radio('art', 'Narkoseverfahren', ['Allgemeinanästhesie (ITN)', 'Larynxmaske', 'Regionalanästhesie', 'Analgosedierung']),
            $t('eingriff', 'Eingriff', ['required' => true]), $area('medikamente', 'Medikamente (Wirkstoff, Dosis, Uhrzeit)', ['required' => true, 'rows' => 5]),
            $area('infusionen', 'Infusionen / Blutprodukte', ['rows' => 2]), $num('blutverlust', 'Blutverlust (ml)'), $head('Vitalparameter bei Übergabe'),
        ], array_slice($vitals, 0, 4), [$area('besonderheiten', 'Besonderheiten / Komplikationen', ['rows' => 2]), $sel('uebergabe', 'Übergabe an', ['Aufwachraum', 'Intensivstation', 'Normalstation'])])),
        'med_anaesthesie_aufklaerung' => $def('Aufklärung und Einwilligung Anästhesie', 'Narkoseaufklärung', 'AA', 'doctor', 'OP und Anästhesie', 'letter', [
            $t('eingriff', 'Geplanter Eingriff', ['required' => true]), $radio('verfahren', 'Geplantes Verfahren', ['Allgemeinanästhesie', 'Regionalanästhesie', 'Analgosedierung'], ['required' => true]),
            $multi('risiken', 'Besprochene Risiken', ['Übelkeit und Erbrechen', 'Zahnschäden', 'Halsschmerzen / Heiserkeit', 'Allergische Reaktion', 'Aspiration', 'Kreislaufstörungen', 'Nervenschäden (Regional)', 'Wachheit während der Narkose (sehr selten)']),
            $t('nuechtern', 'Nüchternheit seit', ['width' => 'half']), $area('fragen', 'Fragen der Patientin / des Patienten', ['rows' => 2]), $sign('sig_patient', 'Unterschrift Patient/in'),
        ], "**{{patient.name}}** wurde am {{date}} über das geplante Anästhesieverfahren (**{{verfahren}}**) für den Eingriff „{{eingriff}}“, über Alternativen sowie typische und seltene Risiken mündlich aufgeklärt. Fragen wurden beantwortet.\n\nDie Patientin / der Patient willigt in das Verfahren ein und kann die Einwilligung jederzeit widerrufen."),
        'med_transfusion' => $def('Transfusionsprotokoll', 'Transfusion', 'TF', 'doctor', 'Klinik', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('beginn', 'Beginn'), $time('ende', 'Ende'),
            $sel('blutgruppe', 'Blutgruppe Empfänger', ['A Rh+', 'A Rh-', 'B Rh+', 'B Rh-', 'AB Rh+', 'AB Rh-', '0 Rh+', '0 Rh-'], ['required' => true]),
            $radio('produkt', 'Blutprodukt', ['Erythrozytenkonzentrat', 'Gefrorenes Frischplasma', 'Thrombozytenkonzentrat'], ['required' => true]), $num('einheiten', 'Einheiten'),
            $t('chargen', 'Konserven-Nr. / Chargen', ['required' => true]), $check('bedside', 'Bedside-Test durchgeführt', ['required' => true]), $check('aufklaerung', 'Einwilligung liegt vor'),
            $radio('reaktion', 'Transfusionsreaktion', ['keine', 'leicht (Fieber, Urtikaria)', 'schwer – Transfusion abgebrochen']), $area('bemerkung', 'Bemerkungen', ['rows' => 2]),
        ]),
        'med_labor' => $def('Laborbefund', 'Laborbefund', 'LB', 'doctor', 'Befunde', 'protocol', [
            $date('abnahme', 'Abnahme', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $sel('material', 'Material', ['Venöses Blut', 'Kapillarblut', 'Urin', 'Liquor', 'Abstrich']),
            $head('Blutbild'), $t('hb', 'Hämoglobin (g/dl)', ['width' => 'quarter']), $t('leuko', 'Leukozyten (/nl)', ['width' => 'quarter']), $t('thrombo', 'Thrombozyten (/nl)', ['width' => 'quarter']), $t('hk', 'Hämatokrit (%)', ['width' => 'quarter']),
            $head('Klinische Chemie'), $t('crp', 'CRP (mg/l)', ['width' => 'quarter']), $t('kreatinin', 'Kreatinin (mg/dl)', ['width' => 'quarter']), $t('kalium', 'Kalium (mmol/l)', ['width' => 'quarter']), $t('natrium', 'Natrium (mmol/l)', ['width' => 'quarter']),
            $t('troponin', 'Troponin (ng/l)', ['width' => 'quarter']), $t('laktat', 'Laktat (mmol/l)', ['width' => 'quarter']), $t('inr', 'INR', ['width' => 'quarter']), $t('glukose', 'Glukose (mg/dl)', ['width' => 'quarter']),
            $area('weitere', 'Weitere Werte', ['rows' => 2]), $area('beurteilung', 'Beurteilung', ['rows' => 2]),
        ]),
        'med_bildgebung' => $def('Radiologischer Befund', 'Röntgen/CT', 'RB', 'doctor', 'Befunde', 'letter', [
            $radio('modalitaet', 'Untersuchung', ['Röntgen', 'CT', 'MRT', 'Sonografie'], ['required' => true]), $t('region', 'Region / Fragestellung', ['required' => true]),
            $date('datum', 'Untersuchungsdatum', ['default' => 'today']), $area('befund', 'Befund', ['required' => true, 'rows' => 6]), $area('beurteilung', 'Beurteilung', ['required' => true, 'rows' => 3]),
        ], "**{{modalitaet}} – {{region}}** vom {{datum}}\n\n**Befund:**\n{{befund}}\n\n**Beurteilung:**\n{{beurteilung}}"),
        'med_ekg' => $def('EKG-Befund', 'EKG', 'EK', 'doctor', 'Befunde', 'protocol', [
            $date('datum', 'Datum', ['default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $sel('ableitung', 'Ableitung', ['12-Kanal', '3-Kanal (Monitor)', 'Langzeit']),
            $sel('rhythmus', 'Rhythmus', ['Sinusrhythmus', 'Vorhofflimmern', 'Vorhofflattern', 'AV-Block I°', 'AV-Block II°', 'AV-Block III°', 'Ventrikuläre Tachykardie', 'Schrittmacherrhythmus']),
            $num('frequenz', 'Frequenz /min'), $t('lagetyp', 'Lagetyp', ['width' => 'quarter']), $t('zeiten', 'PQ / QRS / QTc (ms)', ['width' => 'half']),
            $area('st', 'ST-Strecke / Erregungsrückbildung', ['rows' => 2]), $check('stemi', 'STEMI-Kriterien erfüllt'), $area('beurteilung', 'Beurteilung', ['required' => true, 'rows' => 2]),
        ]),
        'med_konsil' => $def('Konsilbericht', 'Konsil', 'KO', 'doctor', 'Befunde', 'letter', [
            $t('fachrichtung', 'Fachrichtung', ['width' => 'half', 'list' => 'departments', 'required' => true]), $t('anforderung', 'Angefordert von', ['width' => 'half']),
            $area('fragestellung', 'Fragestellung', ['required' => true, 'rows' => 2]), $area('befund', 'Befund', ['rows' => 5]), $area('empfehlung', 'Empfehlung', ['required' => true, 'rows' => 3]),
        ], "Konsiliarische Untersuchung von **{{patient.name}}** – Fachrichtung {{fachrichtung}}.\n\n**Fragestellung:** {{fragestellung}}"),
        'med_wunde' => $def('Wunddokumentation', 'Wunddoku', 'WD', 'medic', 'Klinik', 'protocol', [
            $date('datum', 'Datum', ['default' => 'today']), $t('lokalisation', 'Lokalisation', ['width' => 'half', 'required' => true]), $sel('art', 'Wundart', ['Schnittwunde', 'Platzwunde', 'Schürfwunde', 'Schusswunde', 'Bisswunde', 'Verbrennung', 'Operationswunde', 'Dekubitus']),
            $t('groesse', 'Größe (L × B × T cm)', ['width' => 'third']), $sel('zustand', 'Wundgrund', ['sauber', 'belegt', 'nekrotisch', 'granulierend', 'infiziert']), $check('naht', 'Naht / Klammern'),
            $area('versorgung', 'Versorgung', ['rows' => 3]), $date('kontrolle', 'Nächste Kontrolle'), $check('tetanus', 'Tetanusschutz geprüft'),
        ]),
        'med_pflege' => $def('Pflegebericht', 'Pflegebericht', 'PB', 'medic', 'Klinik', 'protocol', array_merge([
            $date('datum', 'Datum', ['default' => 'today']), $sel('schicht', 'Schicht', ['Frühdienst', 'Spätdienst', 'Nachtdienst']), $t('station', 'Station / Zimmer', ['width' => 'third']),
        ], array_slice($vitals, 0, 5), [
            $sel('bewusstsein', 'Bewusstsein', ['wach und orientiert', 'verlangsamt', 'desorientiert', 'somnolent', 'sediert']),
            $area('verlauf', 'Verlauf / Beobachtungen', ['required' => true, 'rows' => 5]), $area('massnahmen', 'Pflegemaßnahmen', ['rows' => 3]), $area('uebergabe', 'Für die Übergabe', ['rows' => 2]),
        ]), '', 'required', 'none'),
        'med_ueberleitung' => $def('Pflegeüberleitungsbogen', 'Überleitung', 'PU', 'medic', 'Klinik', 'protocol', [
            $date('datum', 'Verlegung am', ['default' => 'today']), $t('ziel', 'Verlegt nach', ['width' => 'half', 'required' => true]), $icd(),
            $sel('mobilitaet', 'Mobilität', ['selbstständig', 'mit Hilfsmittel', 'mit Unterstützung', 'bettlägerig']), $sel('ernaehrung', 'Ernährung', ['normal', 'Schonkost', 'Sonde', 'nüchtern']),
            $area('medikation', 'Aktuelle Medikation', ['rows' => 3, 'list' => 'meds']), $area('zugaenge', 'Zugänge / Katheter / Drainagen', ['rows' => 2]), $area('hinweise', 'Besondere Hinweise (Allergien, Isolation, Wunden)', ['rows' => 2]),
        ]),
        'med_medikationsplan' => $def('Medikationsplan', 'Medikationsplan', 'MP', 'doctor', 'Ärztliche Bescheinigungen', 'protocol', [
            $date('stand', 'Stand', ['default' => 'today']), $area('dauer', 'Dauermedikation (Wirkstoff, Stärke, morgens-mittags-abends-nachts, Grund)', ['required' => true, 'rows' => 6, 'list' => 'meds']),
            $area('bedarf', 'Bedarfsmedikation', ['rows' => 3]), $area('hinweise', 'Hinweise (Einnahme, Wechselwirkungen)', ['rows' => 2]),
        ]),
        'med_krankentransport' => $def('Verordnung einer Krankenbeförderung', 'Krankentransport', 'KT', 'doctor', 'Ärztliche Bescheinigungen', 'protocol', [
            $radio('art', 'Beförderungsmittel', ['Taxi / Mietwagen', 'Krankentransportwagen (KTW)', 'Rettungswagen (RTW)', 'Rettungshubschrauber'], ['required' => true]),
            $radio('richtung', 'Fahrt', ['Hinfahrt', 'Rückfahrt', 'Hin- und Rückfahrt']), $t('von', 'Von', ['width' => 'half']), $t('nach', 'Nach', ['width' => 'half', 'required' => true]),
            $date('datum', 'Datum', ['default' => 'today']), $area('grund', 'Medizinische Begründung', ['required' => true, 'rows' => 2]),
            $multi('ausstattung', 'Erforderlich', ['Tragestuhl', 'Liegend', 'Sauerstoff', 'Monitoring', 'Infektionsschutz', 'Begleitperson']),
        ]),
        'med_heilmittel' => $def('Heilmittelverordnung', 'Heilmittel', 'HM', 'doctor', 'Ärztliche Bescheinigungen', 'protocol', [
            $radio('heilmittel', 'Heilmittel', ['Physiotherapie', 'Ergotherapie', 'Logopädie', 'Manuelle Lymphdrainage'], ['required' => true]), $icd('indikation', 'Indikation / Diagnose'),
            $num('einheiten', 'Behandlungseinheiten'), $sel('frequenz', 'Frequenz', ['1× wöchentlich', '2× wöchentlich', '3× wöchentlich']), $check('hausbesuch', 'Hausbesuch'),
            $area('ziel', 'Therapieziel', ['rows' => 2]),
        ]),
        'med_reha' => $def('Empfehlung zur Rehabilitation', 'Reha-Empfehlung', 'RH', 'doctor', 'Ärztliche Bescheinigungen', 'letter', [
            $icd(), $radio('form', 'Form', ['Stationäre Rehabilitation', 'Ambulante Rehabilitation', 'Anschlussheilbehandlung (AHB)'], ['required' => true]),
            $area('begruendung', 'Begründung / Funktionseinschränkungen', ['required' => true]), $area('ziele', 'Rehabilitationsziele', ['rows' => 2]),
        ], "Für **{{patient.name}}** wird aus ärztlicher Sicht eine **{{form}}** empfohlen.\n\nDiagnose: {{diagnose}}\n\n{{begruendung}}"),
        'med_wiedereingliederung' => $def('Stufenweise Wiedereingliederung', 'Wiedereingliederung', 'WE', 'doctor', 'Ärztliche Bescheinigungen', 'letter', [
            $t('arbeitgeber', 'Arbeitgeber', ['width' => 'half']), $date('beginn', 'Beginn', ['required' => true]), $date('ende', 'Voraussichtliches Ende'),
            $area('stufen', 'Stufenplan (z. B. 2 Wochen 4 h/Tag, 2 Wochen 6 h/Tag)', ['required' => true, 'rows' => 4]), $area('einschraenkungen', 'Einschränkungen', ['rows' => 2]),
        ], "Für **{{patient.name}}** wird ab {{beginn}} eine stufenweise Wiedereingliederung bei {{arbeitgeber}} empfohlen:\n\n{{stufen}}\n\nWährend der Wiedereingliederung besteht weiterhin Arbeitsunfähigkeit."),

        // ====================================================== Bescheinigungen
        'med_haftfaehigkeit' => $def('Gewahrsams- und Haftfähigkeitsbescheinigung', 'Haftfähigkeit', 'HF', 'doctor', 'Behördliche Untersuchungen', 'letter', [
            $t('anforderung', 'Angefordert durch (Dienststelle, Beamte/r)', ['required' => true]), $date('datum', 'Untersuchung am', ['default' => 'today']), $time('uhrzeit', 'Uhrzeit'),
            $area('befund', 'Befund (Vitalwerte, Verletzungen, Intoxikation)', ['required' => true, 'rows' => 3]),
            $radio('ergebnis', 'Ergebnis', ['gewahrsams- und haftfähig', 'eingeschränkt haftfähig (Auflagen beachten)', 'nicht haftfähig – stationäre Behandlung erforderlich'], ['required' => true]),
            $area('auflagen', 'Auflagen (z. B. Kontrollen alle 30 min, Medikation)', ['rows' => 2]),
        ], "Auf Anforderung von {{anforderung}} wurde **{{patient.name}}** am {{datum}} um {{uhrzeit}} ärztlich untersucht.\n\nErgebnis: Die untersuchte Person ist **{{ergebnis}}**.\n\nDiese Bescheinigung enthält nur die für den Gewahrsam erforderlichen Angaben.", 'required', 'rect'),
        'med_sporttauglichkeit' => $def('Sporttauglichkeitsbescheinigung', 'Sporttauglichkeit', 'ST', 'doctor', 'Ärztliche Bescheinigungen', 'letter', [
            $t('sportart', 'Sportart / Verein', ['required' => true]), $radio('ergebnis', 'Ergebnis', ['uneingeschränkt sporttauglich', 'sporttauglich mit Einschränkungen', 'derzeit nicht sporttauglich'], ['required' => true]),
            $area('einschraenkungen', 'Einschränkungen', ['rows' => 2]), $date('gueltig_bis', 'Gültig bis'),
        ], "Nach ärztlicher Untersuchung wird bescheinigt, dass **{{patient.name}}**, geb. {{patient.dob}}, für {{sportart}} **{{ergebnis}}** ist.", 'required', 'rect', 'gueltig_bis'),
        'med_fahrtauglichkeit' => $def('Ärztliches Gutachten zur Fahreignung', 'Fahreignung', 'FE', 'doctor', 'Behördliche Untersuchungen', 'letter', [
            $multi('klassen', 'Fahrerlaubnisklassen', ['Pkw (B)', 'Motorrad (A)', 'Lkw (C)', 'Bus (D)', 'Personenbeförderung']), $area('befund', 'Untersuchungsbefund (Sehtest, Neurologie, Herz-Kreislauf)', ['required' => true, 'rows' => 4]),
            $radio('ergebnis', 'Ergebnis', ['geeignet', 'bedingt geeignet (Auflagen)', 'nicht geeignet'], ['required' => true]), $area('auflagen', 'Auflagen (z. B. Sehhilfe, Nachuntersuchung)', ['rows' => 2]), $date('gueltig_bis', 'Nachuntersuchung bis'),
        ], "**{{patient.name}}**, geb. {{patient.dob}}, ist zum Führen von Kraftfahrzeugen der Klassen {{klassen}} aus ärztlicher Sicht **{{ergebnis}}**.\n\nAuflagen: {{auflagen}}", 'required', 'rect', 'gueltig_bis'),
        'med_flugtauglichkeit' => $def('Fliegerärztliches Tauglichkeitszeugnis', 'Flugtauglichkeit', 'FT', 'doctor', 'Behördliche Untersuchungen', 'letter', [
            $radio('klasse', 'Tauglichkeitsklasse', ['Klasse 1 (Berufspiloten)', 'Klasse 2 (Privatpiloten)', 'Hubschrauber (Rettungsdienst)'], ['required' => true]),
            $area('befund', 'Befund (Visus, Hörtest, EKG, Lunge)', ['required' => true, 'rows' => 3]), $radio('ergebnis', 'Ergebnis', ['tauglich', 'tauglich mit Auflagen', 'nicht tauglich'], ['required' => true]),
            $date('gueltig_bis', 'Gültig bis', ['required' => true]),
        ], "Für **{{patient.name}}** wird die fliegerärztliche Tauglichkeit **{{klasse}}** als **{{ergebnis}}** festgestellt. Das Zeugnis gilt bis {{gueltig_bis}}.", 'required', 'rect', 'gueltig_bis'),
        'med_drogenscreening' => $def('Drogenscreening', 'Drogenscreening', 'DS', 'medic', 'Behördliche Untersuchungen', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $sel('material', 'Material', ['Urin', 'Speichel', 'Blut']),
            $t('anlass', 'Anlass / Auftraggeber', ['width' => 'half']), $head('Ergebnis (Schnelltest)'),
            $sel('thc', 'Cannabis (THC)', ['negativ', 'positiv']), $sel('kokain', 'Kokain', ['negativ', 'positiv']), $sel('amphetamin', 'Amphetamin', ['negativ', 'positiv']),
            $sel('methamphetamin', 'Methamphetamin', ['negativ', 'positiv']), $sel('opiate', 'Opiate', ['negativ', 'positiv']), $sel('benzodiazepine', 'Benzodiazepine', ['negativ', 'positiv']),
            $t('alkohol', 'Atemalkohol (‰)', ['width' => 'quarter']), $check('bestaetigung', 'Bestätigungsanalyse veranlasst'), $sign('sig_proband', 'Unterschrift untersuchte Person'),
        ]),
        'med_schwangerschaft' => $def('Schwangerschaftsbescheinigung', 'Schwangerschaft', 'SW', 'doctor', 'Ärztliche Bescheinigungen', 'letter', [
            $num('ssw', 'Schwangerschaftswoche', 'third'), $date('et', 'Errechneter Geburtstermin', ['required' => true]), $t('zweck', 'Zur Vorlage bei', ['width' => 'half', 'default' => 'Arbeitgeber']),
            $area('hinweise', 'Hinweise (z. B. Beschäftigungsverbot)', ['rows' => 2]),
        ], "Hiermit wird bescheinigt, dass sich **{{patient.name}}** derzeit in der {{ssw}}. Schwangerschaftswoche befindet. Der voraussichtliche Geburtstermin ist der **{{et}}**.\n\n{{hinweise}}"),
        'med_geburt' => $def('Geburtsbescheinigung', 'Geburt', 'GB', 'doctor', 'Ärztliche Bescheinigungen', 'letter', [
            $t('kind', 'Name des Kindes', ['required' => true]), $date('geburt', 'Geburtsdatum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'),
            $sel('geschlecht', 'Geschlecht', ['weiblich', 'männlich', 'divers']), $num('gewicht', 'Gewicht (g)', 'third'), $num('groesse', 'Länge (cm)', 'third'), $t('ort', 'Geburtsort', ['default' => 'Pillbox Hill Medical Center']),
            $t('apgar', 'APGAR (1/5/10 min)', ['width' => 'third']),
        ], "Hiermit wird bescheinigt, dass **{{patient.name}}** am **{{geburt}} um {{uhrzeit}}** in {{ort}} ein Kind entbunden hat:\n\n**{{kind}}**, {{geschlecht}}, {{gewicht}} g, {{groesse}} cm."),
        'med_quarantaene' => $def('Anordnung zur Absonderung (Infektionsschutz)', 'Absonderung', 'IS', 'doctor', 'Behördliche Untersuchungen', 'letter', [
            $t('erkrankung', 'Erkrankung / Verdacht', ['required' => true, 'list' => 'icd']), $date('von', 'Absonderung ab', ['required' => true, 'default' => 'today']), $date('bis', 'bis einschließlich', ['required' => true]),
            $t('ort', 'Ort der Absonderung', ['default' => 'häusliche Absonderung']), $area('auflagen', 'Auflagen', ['rows' => 3, 'default' => 'Keine Besuche, Kontakt zu Haushaltsangehörigen minimieren, bei Verschlechterung 911 anrufen.']),
        ], "Wegen **{{erkrankung}}** wird für **{{patient.name}}** zum Schutz der Bevölkerung die Absonderung vom {{von}} bis {{bis}} angeordnet (Ort: {{ort}}).\n\nAuflagen: {{auflagen}}\n\nVerstöße werden den zuständigen Behörden gemeldet.", 'required', 'round', 'bis'),
        'med_obduktion' => $def('Obduktionsbericht', 'Obduktion', 'OD', 'doctor', 'Rechtsmedizin', 'protocol', [
            $date('datum', 'Obduktion am', ['required' => true, 'default' => 'today']), $t('auftrag', 'Auftraggeber (z. B. DOJ, Aktenzeichen)', ['width' => 'half']), $t('anwesend', 'Anwesend', ['width' => 'half']),
            $area('aeussere', 'Äußere Besichtigung', ['required' => true, 'rows' => 5]), $area('innere', 'Innere Besichtigung', ['required' => true, 'rows' => 6]),
            $area('asservate', 'Asservate (Blut, Urin, Projektile …)', ['rows' => 2]), $sel('todesart', 'Todesart', ['natürlich', 'nicht natürlich – Unfall', 'nicht natürlich – Fremdeinwirkung', 'nicht natürlich – Suizid', 'ungeklärt']),
            $area('todesursache', 'Todesursache / Zusammenfassung', ['required' => true, 'rows' => 3]),
        ]),

        // ================================================= Einwilligungen / Recht
        'med_schweigepflicht' => $def('Entbindung von der Schweigepflicht', 'Schweigepflichtentbindung', 'SE', 'medic', 'Einwilligungen', 'letter', [
            $t('empfaenger', 'Empfänger der Auskunft (Behörde / Person)', ['required' => true]), $area('umfang', 'Umfang der Auskunft', ['required' => true, 'rows' => 2, 'default' => 'Befunde, Diagnosen und Behandlungsunterlagen zum Ereignis vom …']),
            $t('zweck', 'Zweck', ['width' => 'half']), $date('bis', 'Gültig bis'), $sign('sig_patient', 'Unterschrift Patient/in'),
        ], "Ich, **{{patient.name}}**, geb. {{patient.dob}}, entbinde die behandelnden Ärztinnen, Ärzte und Mitarbeitenden von {{org.name}} gegenüber **{{empfaenger}}** von der Schweigepflicht.\n\nUmfang: {{umfang}}\n\nZweck: {{zweck}}\n\nDiese Erklärung kann ich jederzeit für die Zukunft widerrufen.", 'required', 'none', 'bis'),
        'med_patientenverfuegung' => $def('Patientenverfügung', 'Patientenverfügung', 'PV', 'medic', 'Einwilligungen', 'letter', [
            $multi('situationen', 'Gilt für folgende Situationen', ['Endstadium einer unheilbaren Erkrankung', 'Dauerhafter Verlust der Einsichts- und Kommunikationsfähigkeit', 'Schwerste Hirnschädigung', 'Unabwendbarer Sterbeprozess']),
            $multi('ablehnung', 'Ich lehne ab', ['Wiederbelebung', 'Künstliche Beatmung', 'Künstliche Ernährung', 'Dialyse', 'Bluttransfusion']),
            $check('palliativ', 'Ich wünsche Schmerz- und Symptombehandlung, auch wenn sie mein Leben verkürzt'), $t('vertreter', 'Bevollmächtigte Vertrauensperson'), $sign('sig_patient', 'Unterschrift'),
        ], "Ich, **{{patient.name}}**, geb. {{patient.dob}}, bestimme für den Fall, dass ich meinen Willen nicht mehr bilden oder verständlich äußern kann:\n\nIn folgenden Situationen: {{situationen}}\n\nlehne ich folgende Maßnahmen ab: **{{ablehnung}}**\n\nVertrauensperson: {{vertreter}}", 'required', 'none'),
        'med_behandlungsvertrag' => $def('Behandlungsvertrag Selbstzahler', 'Behandlungsvertrag', 'BV', 'medic', 'Einwilligungen', 'letter', [
            $t('leistung', 'Leistung', ['required' => true, 'list' => 'services']), $t('kosten', 'Voraussichtliche Kosten', ['width' => 'half', 'required' => true]),
            $sign('sig_patient', 'Unterschrift Patient/in'),
        ], "**{{patient.name}}** wünscht die Behandlung „{{leistung}}“ als Selbstzahler. Die voraussichtlichen Kosten betragen **{{kosten}}** nach der Gebührenordnung von {{org.name}}.\n\nDie Rechnung ist innerhalb von 14 Tagen nach Erhalt zu begleichen."),
    ];
}
