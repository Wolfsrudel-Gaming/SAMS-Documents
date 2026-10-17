<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Register aller Dokumentarten.
 *
 * Jede Dokumentart besteht aus Metadaten, einer Feldliste (für das Formular
 * und die Validierung) und einer Druckvorlage in app/templates/<template>.php.
 * Neue Dokumentarten lassen sich hier ergänzen, ohne den restlichen Code
 * anzufassen.
 *
 * Feldtypen: heading, text, textarea, date, time, number, select, radio,
 * checkbox, checkboxes, items (Tabellenzeilen), bodymap (Körperschema),
 * signature (Unterschrift auf dem Gerät, z.B. vom Patienten).
 */
function document_types(): array
{
    static $types = null;
    if ($types !== null) {
        return $types;
    }

    $yesNo = ['unauffällig' => 'unauffällig', 'auffällig' => 'auffällig', 'n.d.' => 'nicht durchgeführt'];
    $posNeg = ['negativ' => 'negativ', 'positiv' => 'positiv', 'n.d.' => 'nicht durchgeführt'];

    $types = [

        // ------------------------------------------------------------------
        'au' => [
            'title'    => 'Arbeitsunfähigkeitsbescheinigung',
            'short'    => 'AU-Bescheinigung',
            'code'     => 'AU',
            'category' => 'Ärztliche Bescheinigungen',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'au',
            'valid'    => 'au_until',
            'fields'   => [
                ['type' => 'heading', 'label' => 'Bescheinigung'],
                ['name' => 'kind', 'label' => 'Art', 'type' => 'radio', 'required' => true, 'default' => 'erst',
                    'options' => ['erst' => 'Erstbescheinigung', 'folge' => 'Folgebescheinigung']],
                ['name' => 'accident', 'label' => 'Arbeitsunfall, Arbeitsunfallfolgen, Berufskrankheit', 'type' => 'checkbox'],
                ['name' => 'd_doctor', 'label' => 'dem Durchgangsarzt zugewiesen', 'type' => 'checkbox'],
                ['name' => 'other_accident', 'label' => 'Sonstiger Unfall, Unfallfolgen', 'type' => 'checkbox'],
                ['name' => 'au_since', 'label' => 'Arbeitsunfähig seit', 'type' => 'date', 'required' => true, 'default' => 'today', 'width' => 'third'],
                ['name' => 'au_until', 'label' => 'Voraussichtlich arbeitsunfähig bis einschließlich', 'type' => 'date', 'required' => true, 'width' => 'third'],
                ['name' => 'au_found', 'label' => 'Festgestellt am', 'type' => 'date', 'required' => true, 'default' => 'today', 'width' => 'third'],
                ['type' => 'heading', 'label' => 'Diagnose (erscheint nicht auf der Ausfertigung für den Arbeitgeber)'],
                ['name' => 'icd', 'label' => 'AU-begründende Diagnose(n) – ICD-10', 'type' => 'textarea', 'rows' => 2, 'required' => true,
                    'list' => 'icd', 'placeholder' => "S52.5 G R Distale Radiusfraktur rechts\nS06.0 G Gehirnerschütterung"],
                ['name' => 'rehab', 'label' => 'Sonstiges', 'type' => 'checkboxes',
                    'options' => ['reha' => 'Maßnahmen zur Rehabilitation angezeigt', 'wiedereingliederung' => 'Stufenweise Wiedereingliederung', 'krankengeld' => 'Ab 7. AU-Woche oder sonstiger Krankengeldfall']],
                ['name' => 'employer', 'label' => 'Arbeitgeber (optional)', 'type' => 'text', 'placeholder' => 'z.B. Los Santos Customs'],
            ],
        ],

        // ------------------------------------------------------------------
        'attest' => [
            'title'    => 'Ärztliches Attest',
            'short'    => 'Attest',
            'code'     => 'AT',
            'category' => 'Ärztliche Bescheinigungen',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'attest',
            'valid'    => 'valid_until',
            'fields'   => [
                ['name' => 'purpose', 'label' => 'Zur Vorlage bei', 'type' => 'select', 'required' => true, 'options' => [
                    'Arbeitgeber' => 'Arbeitgeber', 'Behörde' => 'Behörde', 'Schule / Universität' => 'Schule / Universität',
                    'Gericht' => 'Gericht', 'Polizei (LSPD / BCSO)' => 'Polizei (LSPD / BCSO)', 'Sportverein' => 'Sportverein',
                    'Versicherung' => 'Versicherung', 'Vorlage nach Bedarf' => 'Vorlage nach Bedarf']],
                ['name' => 'recipient', 'label' => 'Empfänger (optional)', 'type' => 'text', 'placeholder' => 'z.B. Los Santos Police Department, Mission Row'],
                ['name' => 'exam_date', 'label' => 'Untersuchung am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'valid_until', 'label' => 'Gültig bis (optional)', 'type' => 'date', 'width' => 'half'],
                ['name' => 'subject', 'label' => 'Betreff', 'type' => 'text', 'default' => 'Ärztliches Attest', 'required' => true],
                ['name' => 'body', 'label' => 'Bescheinigungstext', 'type' => 'textarea', 'rows' => 8, 'required' => true,
                    'placeholder' => 'Hiermit wird bescheinigt, dass sich o.g. Patient/in am … in unserer ärztlichen Behandlung befand. Aus medizinischen Gründen ist er/sie bis einschließlich … nicht in der Lage, …'],
                ['name' => 'icd', 'label' => 'Diagnose (nur auf Wunsch des Patienten)', 'type' => 'text', 'list' => 'icd'],
            ],
        ],

        // ------------------------------------------------------------------
        'rezept' => [
            'title'    => 'Rezept / Verordnung',
            'short'    => 'Rezept',
            'code'     => 'RX',
            'category' => 'Ärztliche Bescheinigungen',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'rezept',
            'fields'   => [
                ['name' => 'form', 'label' => 'Formular', 'type' => 'radio', 'default' => 'kassen', 'required' => true, 'options' => [
                    'kassen' => 'Kassenrezept (rosa)', 'privat' => 'Privatrezept (blau)', 'btm' => 'Betäubungsmittelrezept (gelb)']],
                ['name' => 'flags', 'label' => 'Kennzeichnung', 'type' => 'checkboxes', 'options' => [
                    'gebfrei' => 'Gebührenfrei', 'noctu' => 'noctu (Notdienst)', 'unfall' => 'Unfall', 'arbeitsunfall' => 'Arbeitsunfall']],
                ['name' => 'items', 'label' => 'Verordnung (max. 3 Positionen)', 'type' => 'items', 'max' => 3, 'columns' => [
                    'med'  => ['label' => 'Präparat, Stärke, Menge (N1/N2/N3)', 'list' => 'meds'],
                    'dose' => ['label' => 'Dosierung', 'placeholder' => '1-0-1'],
                    'autidem' => ['label' => 'aut idem ✕', 'type' => 'checkbox'],
                ]],
                ['name' => 'note', 'label' => 'Hinweis für Apotheke / Patient', 'type' => 'text'],
            ],
        ],

        // ------------------------------------------------------------------
        'ueberweisung' => [
            'title'    => 'Überweisungsschein',
            'short'    => 'Überweisung',
            'code'     => 'UW',
            'category' => 'Ärztliche Bescheinigungen',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'ueberweisung',
            'fields'   => [
                ['name' => 'to', 'label' => 'Überweisung an (Fachrichtung)', 'type' => 'text', 'required' => true, 'list' => 'departments', 'width' => 'half'],
                ['name' => 'to_facility', 'label' => 'Einrichtung (optional)', 'type' => 'text', 'width' => 'half', 'placeholder' => 'z.B. Mount Zonah Medical Center'],
                ['name' => 'kind', 'label' => 'Art', 'type' => 'radio', 'default' => 'mitbehandlung', 'options' => [
                    'auftrag' => 'Auftragsleistung', 'konsil' => 'Konsiliaruntersuchung', 'mitbehandlung' => 'Mit-/Weiterbehandlung', 'stationaer' => 'Stationäre Einweisung']],
                ['name' => 'urgent', 'label' => 'Dringend', 'type' => 'checkbox'],
                ['name' => 'icd', 'label' => 'Diagnose / Verdachtsdiagnose', 'type' => 'textarea', 'rows' => 2, 'required' => true, 'list' => 'icd'],
                ['name' => 'findings', 'label' => 'Befund / Medikation', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'order', 'label' => 'Auftrag / Fragestellung', 'type' => 'textarea', 'rows' => 3, 'required' => true],
            ],
        ],

        // ------------------------------------------------------------------
        'einsatzprotokoll' => [
            'title'    => 'Rettungsdienstliches Einsatzprotokoll',
            'short'    => 'Einsatzprotokoll',
            'code'     => 'EP',
            'category' => 'Rettungsdienst',
            'role'     => 'medic',
            'patient'  => 'optional',
            'template' => 'einsatzprotokoll',
            'fields'   => [
                ['type' => 'heading', 'label' => 'Einsatzdaten'],
                ['name' => 'mission_no', 'label' => 'Einsatz-Nr.', 'type' => 'text', 'width' => 'third', 'placeholder' => 'automatisch, falls leer'],
                ['name' => 'date', 'label' => 'Datum', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'third'],
                ['name' => 'unit', 'label' => 'Rufname / Fahrzeug', 'type' => 'text', 'width' => 'third', 'placeholder' => 'z.B. RTW 1-83-1'],
                ['name' => 'location', 'label' => 'Einsatzort', 'type' => 'text', 'required' => true, 'width' => 'half', 'placeholder' => 'z.B. Legion Square, Höhe Bank'],
                ['name' => 'crew', 'label' => 'Besatzung', 'type' => 'text', 'width' => 'half', 'placeholder' => 'Namen / Dienstnummern'],
                ['name' => 't_alarm', 'label' => 'Alarm', 'type' => 'time', 'width' => 'quarter'],
                ['name' => 't_arrival', 'label' => 'Ankunft Pat.', 'type' => 'time', 'width' => 'quarter'],
                ['name' => 't_transport', 'label' => 'Transportbeginn', 'type' => 'time', 'width' => 'quarter'],
                ['name' => 't_handover', 'label' => 'Übergabe', 'type' => 'time', 'width' => 'quarter'],
                ['name' => 'unknown_person', 'label' => 'Patient unbekannt (Personalien nicht feststellbar)', 'type' => 'checkbox'],
                ['name' => 'call_reason', 'label' => 'Meldebild / Notfallgeschehen', 'type' => 'textarea', 'rows' => 2, 'required' => true],
                ['type' => 'heading', 'label' => 'Erstbefund'],
                ['name' => 'gcs_eye', 'label' => 'GCS Augen', 'type' => 'select', 'width' => 'third', 'options' => ['' => '–', 4 => '4 spontan', 3 => '3 auf Ansprache', 2 => '2 auf Schmerz', 1 => '1 keine']],
                ['name' => 'gcs_verbal', 'label' => 'GCS Verbal', 'type' => 'select', 'width' => 'third', 'options' => ['' => '–', 5 => '5 orientiert', 4 => '4 desorientiert', 3 => '3 inadäquat', 2 => '2 unverständlich', 1 => '1 keine']],
                ['name' => 'gcs_motor', 'label' => 'GCS Motorik', 'type' => 'select', 'width' => 'third', 'options' => ['' => '–', 6 => '6 befolgt', 5 => '5 gezielt', 4 => '4 ungezielt', 3 => '3 Beugung', 2 => '2 Streckung', 1 => '1 keine']],
                ['name' => 'rr', 'label' => 'RR (mmHg)', 'type' => 'text', 'width' => 'sixth', 'placeholder' => '120/80'],
                ['name' => 'hf', 'label' => 'HF (/min)', 'type' => 'number', 'width' => 'sixth'],
                ['name' => 'spo2', 'label' => 'SpO₂ (%)', 'type' => 'number', 'width' => 'sixth'],
                ['name' => 'af', 'label' => 'AF (/min)', 'type' => 'number', 'width' => 'sixth'],
                ['name' => 'bz', 'label' => 'BZ (mg/dl)', 'type' => 'number', 'width' => 'sixth'],
                ['name' => 'temp', 'label' => 'Temp (°C)', 'type' => 'text', 'width' => 'sixth'],
                ['name' => 'consciousness', 'label' => 'Bewusstsein', 'type' => 'radio', 'options' => ['wach' => 'wach', 'getrübt' => 'getrübt', 'bewusstlos' => 'bewusstlos', 'sediert' => 'analgosediert']],
                ['name' => 'breathing', 'label' => 'Atmung', 'type' => 'checkboxes', 'options' => ['unauffällig' => 'unauffällig', 'Dyspnoe' => 'Dyspnoe', 'Zyanose' => 'Zyanose', 'Apnoe' => 'Apnoe', 'Stridor' => 'Stridor', 'Spastik' => 'Spastik']],
                ['name' => 'circulation', 'label' => 'Kreislauf', 'type' => 'checkboxes', 'options' => ['stabil' => 'stabil', 'Schock' => 'Schock', 'Kreislaufstillstand' => 'Kreislaufstillstand', 'Rhythmusstörung' => 'Rhythmusstörung']],
                ['name' => 'injuries', 'label' => 'Verletzungen / Befund', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'bodymap', 'label' => 'Verletzungsmuster (Körperschema)', 'type' => 'bodymap'],
                ['name' => 'icd', 'label' => 'Erstdiagnose', 'type' => 'text', 'list' => 'icd'],
                ['type' => 'heading', 'label' => 'Maßnahmen'],
                ['name' => 'measures', 'label' => 'Durchgeführte Maßnahmen', 'type' => 'checkboxes', 'columns' => 3, 'options' => [
                    'Basismonitoring' => 'Basismonitoring', '12-Kanal-EKG' => '12-Kanal-EKG', 'Sauerstoffgabe' => 'Sauerstoffgabe',
                    'Venöser Zugang' => 'Venöser Zugang', 'Intraossärer Zugang' => 'Intraossärer Zugang', 'Infusion' => 'Infusion',
                    'Wundversorgung' => 'Wundversorgung', 'Blutstillung / Tourniquet' => 'Blutstillung / Tourniquet', 'Beckenschlinge' => 'Beckenschlinge',
                    'HWS-Immobilisation' => 'HWS-Immobilisation', 'Vakuummatratze' => 'Vakuummatratze', 'Schienung' => 'Schienung',
                    'Thoraxentlastung' => 'Thoraxentlastung', 'Intubation' => 'Intubation', 'Supraglottischer Atemweg' => 'Supraglottischer Atemweg',
                    'CPR' => 'Kardiopulmonale Reanimation', 'Defibrillation' => 'Defibrillation', 'Narkose' => 'Narkose']],
                ['name' => 'meds', 'label' => 'Medikamente (Präparat, Dosis, Uhrzeit)', 'type' => 'textarea', 'rows' => 2, 'list' => 'meds'],
                ['name' => 'course', 'label' => 'Verlauf / Bemerkungen', 'type' => 'textarea', 'rows' => 3],
                ['type' => 'heading', 'label' => 'Ergebnis'],
                ['name' => 'outcome', 'label' => 'Einsatzergebnis', 'type' => 'radio', 'required' => true, 'options' => [
                    'transport' => 'Transport in Klinik', 'ambulant' => 'Ambulante Versorgung vor Ort', 'verweigerung' => 'Transportverweigerung',
                    'uebergabe' => 'Übergabe an anderes Rettungsmittel', 'tod' => 'Tod am Einsatzort', 'fehl' => 'Fehleinsatz']],
                ['name' => 'destination', 'label' => 'Transportziel', 'type' => 'text', 'width' => 'half', 'placeholder' => 'z.B. Pillbox Hill Medical Center, Schockraum'],
                ['name' => 'naca', 'label' => 'NACA-Score', 'type' => 'select', 'width' => 'half', 'options' => [
                    '' => '–', 'I' => 'I – geringfügig', 'II' => 'II – ambulant abklärungsbedürftig', 'III' => 'III – stationär, nicht lebensbedrohlich',
                    'IV' => 'IV – Entwicklung Lebensgefahr möglich', 'V' => 'V – akute Lebensgefahr', 'VI' => 'VI – Reanimation', 'VII' => 'VII – Tod']],
            ],
        ],

        // ------------------------------------------------------------------
        'revers' => [
            'title'    => 'Erklärung zur Behandlungs- / Transportverweigerung',
            'short'    => 'Revers',
            'code'     => 'RV',
            'category' => 'Rettungsdienst',
            'role'     => 'medic',
            'patient'  => 'optional',
            'template' => 'revers',
            'fields'   => [
                ['name' => 'date', 'label' => 'Datum', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'time', 'label' => 'Uhrzeit', 'type' => 'time', 'default' => 'now', 'required' => true, 'width' => 'half'],
                ['name' => 'person_name', 'label' => 'Name (falls kein Patient ausgewählt)', 'type' => 'text'],
                ['name' => 'location', 'label' => 'Einsatzort', 'type' => 'text', 'required' => true],
                ['name' => 'refused', 'label' => 'Abgelehnt wird', 'type' => 'checkboxes', 'required' => true, 'options' => [
                    'untersuchung' => 'die Untersuchung', 'behandlung' => 'die Behandlung', 'transport' => 'der Transport in ein Krankenhaus', 'medikamente' => 'die Gabe von Medikamenten']],
                ['name' => 'findings', 'label' => 'Verdachtsdiagnose / Befund', 'type' => 'textarea', 'rows' => 2, 'required' => true],
                ['name' => 'risks', 'label' => 'Aufgeklärt über folgende Risiken', 'type' => 'textarea', 'rows' => 3, 'required' => true,
                    'default' => 'Verschlechterung des Gesundheitszustandes, bleibende gesundheitliche Schäden bis hin zum Tod.'],
                ['name' => 'capable', 'label' => 'Patient ist wach, orientiert und einwilligungsfähig', 'type' => 'checkbox', 'default' => '1'],
                ['name' => 'witness', 'label' => 'Zeuge (Name, Funktion)', 'type' => 'text', 'placeholder' => 'z.B. Officer J. Miller, LSPD #412'],
                ['name' => 'patient_signature', 'label' => 'Unterschrift Patient/in', 'type' => 'signature'],
                ['name' => 'signature_refused', 'label' => 'Unterschrift wurde verweigert', 'type' => 'checkbox'],
            ],
        ],

        // ------------------------------------------------------------------
        'arztbrief' => [
            'title'    => 'Arztbrief / Entlassungsbericht',
            'short'    => 'Arztbrief',
            'code'     => 'AB',
            'category' => 'Klinik',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'arztbrief',
            'fields'   => [
                ['name' => 'kind', 'label' => 'Art', 'type' => 'select', 'options' => [
                    'Entlassungsbericht' => 'Entlassungsbericht', 'Vorläufiger Entlassungsbericht' => 'Vorläufiger Entlassungsbericht',
                    'Ambulanzbrief' => 'Ambulanzbrief', 'Notaufnahmebericht' => 'Notaufnahmebericht', 'OP-Bericht' => 'OP-Bericht']],
                ['name' => 'recipient', 'label' => 'Empfänger', 'type' => 'textarea', 'rows' => 3, 'placeholder' => "Hausarzt / weiterbehandelnde Einrichtung\nStraße Nr.\nLos Santos"],
                ['name' => 'station', 'label' => 'Station / Abteilung', 'type' => 'text', 'list' => 'departments', 'width' => 'third'],
                ['name' => 'admission', 'label' => 'Aufnahme', 'type' => 'date', 'default' => 'today', 'width' => 'third'],
                ['name' => 'discharge', 'label' => 'Entlassung', 'type' => 'date', 'default' => 'today', 'width' => 'third'],
                ['name' => 'diagnoses', 'label' => 'Diagnosen', 'type' => 'textarea', 'rows' => 3, 'required' => true, 'list' => 'icd'],
                ['name' => 'history', 'label' => 'Anamnese', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'findings', 'label' => 'Aufnahmebefund', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'diagnostics', 'label' => 'Diagnostik (Labor, Bildgebung)', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'therapy', 'label' => 'Therapie und Verlauf', 'type' => 'textarea', 'rows' => 4, 'required' => true],
                ['name' => 'medication', 'label' => 'Medikation bei Entlassung', 'type' => 'textarea', 'rows' => 3, 'list' => 'meds'],
                ['name' => 'recommendation', 'label' => 'Procedere / Empfehlungen', 'type' => 'textarea', 'rows' => 3],
            ],
        ],

        // ------------------------------------------------------------------
        'verletzung' => [
            'title'    => 'Ärztlicher Befundbericht über Verletzungen',
            'short'    => 'Verletzungsbefund',
            'code'     => 'VB',
            'category' => 'Behörden & Gutachten',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'verletzung',
            'fields'   => [
                ['name' => 'requester', 'label' => 'Angefordert durch', 'type' => 'text', 'width' => 'half', 'placeholder' => 'z.B. LSPD, Det. A. Ramirez #221'],
                ['name' => 'case_no', 'label' => 'Aktenzeichen Behörde', 'type' => 'text', 'width' => 'half'],
                ['name' => 'exam_date', 'label' => 'Untersuchung am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'exam_time', 'label' => 'um', 'type' => 'time', 'default' => 'now', 'width' => 'half'],
                ['name' => 'incident', 'label' => 'Angaben des/der Untersuchten zum Geschehen', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'injuries', 'label' => 'Festgestellte Verletzungen', 'type' => 'items', 'columns' => [
                    'region' => ['label' => 'Körperregion'],
                    'finding' => ['label' => 'Befund (Art, Größe, Farbe, Form)'],
                    'age' => ['label' => 'Geschätztes Alter'],
                ]],
                ['name' => 'bodymap', 'label' => 'Körperschema', 'type' => 'bodymap'],
                ['name' => 'assessment', 'label' => 'Ärztliche Beurteilung', 'type' => 'textarea', 'rows' => 4, 'required' => true,
                    'placeholder' => 'Die festgestellten Verletzungen sind mit dem geschilderten Hergang vereinbar / nicht vereinbar …'],
                ['name' => 'danger', 'label' => 'Lebensgefahr', 'type' => 'radio', 'options' => ['nein' => 'bestand nicht', 'abstrakt' => 'abstrakt', 'konkret' => 'konkret']],
                ['name' => 'photos', 'label' => 'Fotodokumentation angefertigt', 'type' => 'checkbox'],
                ['name' => 'release', 'label' => 'Entbindung von der Schweigepflicht liegt vor', 'type' => 'checkbox', 'default' => '1'],
            ],
        ],

        // ------------------------------------------------------------------
        'blutentnahme' => [
            'title'    => 'Protokoll und ärztlicher Untersuchungsbericht zur Blutentnahme',
            'short'    => 'Blutentnahmeprotokoll',
            'code'     => 'BE',
            'category' => 'Behörden & Gutachten',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'blutentnahme',
            'fields'   => [
                ['type' => 'heading', 'label' => 'Anordnung'],
                ['name' => 'officer', 'label' => 'Anordnende/r Beamter/Beamtin', 'type' => 'text', 'required' => true, 'width' => 'half', 'placeholder' => 'Ofc. J. Miller'],
                ['name' => 'officer_no', 'label' => 'Dienstnummer / Dienststelle', 'type' => 'text', 'width' => 'half', 'placeholder' => '#412, LSPD Mission Row'],
                ['name' => 'case_no', 'label' => 'Aktenzeichen / Tagebuch-Nr.', 'type' => 'text', 'width' => 'half'],
                ['name' => 'reason', 'label' => 'Anlass', 'type' => 'select', 'width' => 'half', 'options' => [
                    'Verkehrsdelikt' => 'Verkehrsdelikt (Trunkenheit im Verkehr)', 'Verkehrsunfall' => 'Verkehrsunfall',
                    'Straftat' => 'Sonstige Straftat', 'Gewahrsam' => 'Gewahrsamsfähigkeit']],
                ['name' => 'incident_time', 'label' => 'Vorfallzeit', 'type' => 'time', 'width' => 'third'],
                ['name' => 'blood_date', 'label' => 'Entnahme am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'third'],
                ['name' => 'blood_time', 'label' => 'Entnahme um', 'type' => 'time', 'default' => 'now', 'required' => true, 'width' => 'third'],
                ['type' => 'heading', 'label' => 'Ärztlicher Befund'],
                ['name' => 'gait', 'label' => 'Gang (geradeaus)', 'type' => 'select', 'width' => 'third', 'options' => ['sicher' => 'sicher', 'schwankend' => 'schwankend', 'torkelnd' => 'torkelnd', 'nicht möglich' => 'nicht möglich']],
                ['name' => 'turn', 'label' => 'Plötzliche Kehrtwendung', 'type' => 'select', 'width' => 'third', 'options' => ['sicher' => 'sicher', 'unsicher' => 'unsicher', 'nicht möglich' => 'nicht möglich']],
                ['name' => 'finger_nose', 'label' => 'Finger-Finger-/Nasen-Prüfung', 'type' => 'select', 'width' => 'third', 'options' => ['sicher' => 'sicher', 'unsicher' => 'unsicher', 'daneben' => 'daneben']],
                ['name' => 'speech', 'label' => 'Sprache', 'type' => 'select', 'width' => 'third', 'options' => ['deutlich' => 'deutlich', 'verwaschen' => 'verwaschen', 'lallend' => 'lallend']],
                ['name' => 'pupils', 'label' => 'Pupillen', 'type' => 'select', 'width' => 'third', 'options' => ['unauffällig' => 'unauffällig', 'eng' => 'eng (Miosis)', 'weit' => 'weit (Mydriasis)', 'lichtstarr' => 'lichtstarr']],
                ['name' => 'mood', 'label' => 'Bewusstsein / Stimmung', 'type' => 'select', 'width' => 'third', 'options' => ['klar' => 'klar', 'benommen' => 'benommen', 'distanzlos' => 'distanzlos', 'aggressiv' => 'aggressiv', 'apathisch' => 'apathisch']],
                ['name' => 'odor', 'label' => 'Alkoholgeruch', 'type' => 'radio', 'options' => ['nein' => 'nein', 'leicht' => 'leicht', 'deutlich' => 'deutlich']],
                ['name' => 'influence', 'label' => 'Gesamteindruck: Der/die Untersuchte steht', 'type' => 'radio', 'required' => true, 'options' => [
                    'nicht' => 'nicht merkbar', 'leicht' => 'leicht', 'deutlich' => 'deutlich', 'stark' => 'stark']],
                ['type' => 'heading', 'label' => 'Ergebnisse'],
                ['name' => 'bac', 'label' => 'Blutalkohol (‰)', 'type' => 'text', 'width' => 'third', 'placeholder' => '0,00'],
                ['name' => 'thc', 'label' => 'THC', 'type' => 'select', 'width' => 'third', 'options' => $posNeg],
                ['name' => 'coc', 'label' => 'Kokain', 'type' => 'select', 'width' => 'third', 'options' => $posNeg],
                ['name' => 'amp', 'label' => 'Amphetamine / Meth', 'type' => 'select', 'width' => 'third', 'options' => $posNeg],
                ['name' => 'opi', 'label' => 'Opiate', 'type' => 'select', 'width' => 'third', 'options' => $posNeg],
                ['name' => 'benzo', 'label' => 'Benzodiazepine', 'type' => 'select', 'width' => 'third', 'options' => $posNeg],
                ['name' => 'remarks', 'label' => 'Bemerkungen', 'type' => 'textarea', 'rows' => 2],
                ['name' => 'officer_signature', 'label' => 'Unterschrift Beamter/Beamtin (optional)', 'type' => 'signature'],
            ],
        ],

        // ------------------------------------------------------------------
        'tauglichkeit' => [
            'title'    => 'Ärztliches Zeugnis über die Tauglichkeit',
            'short'    => 'Tauglichkeitszeugnis',
            'code'     => 'TZ',
            'category' => 'Behörden & Gutachten',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'tauglichkeit',
            'valid'    => 'valid_until',
            'fields'   => [
                ['name' => 'purpose', 'label' => 'Art der Untersuchung', 'type' => 'select', 'required' => true, 'options' => [
                    'Waffenbesitzkarte / Waffenschein (persönliche Eignung)' => 'Waffenbesitzkarte / Waffenschein (persönliche Eignung)',
                    'Fahrerlaubnis' => 'Fahrerlaubnis',
                    'Fahrerlaubnis zur Fahrgastbeförderung / LKW' => 'Fahrerlaubnis zur Fahrgastbeförderung / LKW',
                    'Fluglizenz (Pilot / Helikopter)' => 'Fluglizenz (Pilot / Helikopter)',
                    'Polizeidiensttauglichkeit' => 'Polizeidiensttauglichkeit',
                    'Feuerwehr- / Rettungsdiensttauglichkeit' => 'Feuerwehr- / Rettungsdiensttauglichkeit',
                    'Einstellungsuntersuchung' => 'Einstellungsuntersuchung',
                    'Tauchtauglichkeit' => 'Tauchtauglichkeit',
                    'Sporttauglichkeit' => 'Sporttauglichkeit',
                    'Haftfähigkeit' => 'Haftfähigkeit']],
                ['name' => 'requester', 'label' => 'Zur Vorlage bei', 'type' => 'text', 'placeholder' => 'z.B. Ammu-Nation / Department of Justice'],
                ['name' => 'exam_date', 'label' => 'Untersucht am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'valid_until', 'label' => 'Gültig bis', 'type' => 'date', 'width' => 'half'],
                ['type' => 'heading', 'label' => 'Untersuchungsbefunde'],
                ['name' => 'vision', 'label' => 'Sehtest', 'type' => 'select', 'width' => 'third', 'options' => $yesNo],
                ['name' => 'hearing', 'label' => 'Hörtest', 'type' => 'select', 'width' => 'third', 'options' => $yesNo],
                ['name' => 'reaction', 'label' => 'Reaktionstest', 'type' => 'select', 'width' => 'third', 'options' => $yesNo],
                ['name' => 'physical', 'label' => 'Körperliche Untersuchung', 'type' => 'select', 'width' => 'third', 'options' => $yesNo],
                ['name' => 'psych', 'label' => 'Psychologisches Gespräch', 'type' => 'select', 'width' => 'third', 'options' => $yesNo],
                ['name' => 'drugs', 'label' => 'Drogenscreening', 'type' => 'select', 'width' => 'third', 'options' => $posNeg],
                ['name' => 'rr', 'label' => 'Blutdruck', 'type' => 'text', 'width' => 'third', 'placeholder' => '125/80 mmHg'],
                ['name' => 'hf', 'label' => 'Puls', 'type' => 'text', 'width' => 'third', 'placeholder' => '72/min'],
                ['name' => 'bmi', 'label' => 'Größe / Gewicht', 'type' => 'text', 'width' => 'third', 'placeholder' => '182 cm / 80 kg'],
                ['name' => 'findings', 'label' => 'Weitere Befunde', 'type' => 'textarea', 'rows' => 3],
                ['type' => 'heading', 'label' => 'Ergebnis'],
                ['name' => 'result', 'label' => 'Ergebnis', 'type' => 'radio', 'required' => true, 'options' => [
                    'geeignet' => 'Geeignet / tauglich', 'bedingt' => 'Bedingt geeignet (mit Auflagen)', 'nicht' => 'Nicht geeignet / untauglich', 'befristet' => 'Vorübergehend nicht geeignet']],
                ['name' => 'conditions', 'label' => 'Auflagen / Einschränkungen', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'z.B. Tragen einer Sehhilfe, Nachuntersuchung in 6 Monaten'],
            ],
        ],

        // ------------------------------------------------------------------
        'todesbescheinigung' => [
            'title'    => 'Todesbescheinigung',
            'short'    => 'Todesbescheinigung',
            'code'     => 'TB',
            'category' => 'Behörden & Gutachten',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'todesbescheinigung',
            'fields'   => [
                ['type' => 'heading', 'label' => 'Sterbefall'],
                ['name' => 'death_date', 'label' => 'Sterbedatum', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'third'],
                ['name' => 'death_time', 'label' => 'Uhrzeit', 'type' => 'time', 'width' => 'third'],
                ['name' => 'time_estimated', 'label' => 'Zeitpunkt geschätzt / aufgefunden', 'type' => 'checkbox', 'width' => 'third'],
                ['name' => 'place', 'label' => 'Sterbeort', 'type' => 'radio', 'options' => ['krankenhaus' => 'Krankenhaus', 'wohnung' => 'Wohnung', 'oeffentlich' => 'Öffentlicher Ort', 'rettungsmittel' => 'Rettungsmittel', 'sonstig' => 'Sonstiger Ort']],
                ['name' => 'place_address', 'label' => 'Genaue Ortsangabe', 'type' => 'text', 'required' => true],
                ['type' => 'heading', 'label' => 'Leichenschau'],
                ['name' => 'exam_date', 'label' => 'Leichenschau am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'exam_time', 'label' => 'um', 'type' => 'time', 'default' => 'now', 'required' => true, 'width' => 'half'],
                ['name' => 'signs', 'label' => 'Sichere Todeszeichen', 'type' => 'checkboxes', 'required' => true, 'options' => [
                    'totenflecke' => 'Totenflecke', 'totenstarre' => 'Totenstarre', 'faeulnis' => 'Fäulnis',
                    'verletzung' => 'Mit dem Leben nicht vereinbare Verletzungen', 'reanimation' => 'Erfolglose Reanimation (> 20 min)']],
                ['name' => 'identified_by', 'label' => 'Identität festgestellt durch', 'type' => 'select', 'options' => [
                    'Ausweisdokument' => 'Ausweisdokument', 'Angehörige' => 'Angehörige', 'Polizei' => 'Polizei', 'Persönliche Kenntnis' => 'Persönliche Kenntnis', 'nicht festgestellt' => 'nicht festgestellt']],
                ['type' => 'heading', 'label' => 'Todesart und -ursache'],
                ['name' => 'manner', 'label' => 'Todesart', 'type' => 'radio', 'required' => true, 'options' => [
                    'natuerlich' => 'Natürlicher Tod', 'nichtnatuerlich' => 'Nicht natürlicher Tod', 'ungeklaert' => 'Ungeklärt, ob natürlicher oder nicht natürlicher Tod']],
                ['name' => 'cause_a', 'label' => 'Ia – Unmittelbar zum Tode führende Krankheit', 'type' => 'text', 'required' => true, 'list' => 'icd', 'placeholder' => 'z.B. Hämorrhagischer Schock'],
                ['name' => 'cause_b', 'label' => 'Ib – als Folge von', 'type' => 'text', 'list' => 'icd', 'placeholder' => 'z.B. Schussverletzung Thorax'],
                ['name' => 'cause_c', 'label' => 'Ic – Grundleiden', 'type' => 'text', 'list' => 'icd'],
                ['name' => 'cause_2', 'label' => 'II – Andere wesentliche Krankheiten', 'type' => 'text'],
                ['name' => 'police', 'label' => 'Polizei benachrichtigt', 'type' => 'checkbox'],
                ['name' => 'autopsy', 'label' => 'Obduktion angeregt', 'type' => 'checkbox'],
                ['name' => 'infection', 'label' => 'Übertragbare Krankheit / Infektionsgefahr', 'type' => 'checkbox'],
                ['name' => 'remarks', 'label' => 'Bemerkungen', 'type' => 'textarea', 'rows' => 2],
                ['name' => 'mark_deceased', 'label' => 'Patient/in in der Akte als verstorben markieren', 'type' => 'checkbox', 'default' => '1'],
            ],
        ],

        // ------------------------------------------------------------------
        'rechnung' => [
            'title'    => 'Rechnung',
            'short'    => 'Rechnung',
            'code'     => 'RE',
            'category' => 'Verwaltung',
            'role'     => 'medic',
            'patient'  => 'optional',
            'template' => 'rechnung',
            'fields'   => [
                ['name' => 'recipient', 'label' => 'Rechnungsempfänger (falls abweichend vom Patienten)', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'service_date', 'label' => 'Leistungsdatum', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'due_days', 'label' => 'Zahlungsziel (Tage)', 'type' => 'number', 'default' => '14', 'width' => 'half'],
                ['name' => 'items', 'label' => 'Leistungen', 'type' => 'items', 'required' => true, 'columns' => [
                    'text'  => ['label' => 'Leistung', 'list' => 'services'],
                    'qty'   => ['label' => 'Anzahl', 'type' => 'number', 'default' => '1'],
                    'price' => ['label' => 'Einzelpreis', 'type' => 'number', 'step' => '0.01'],
                ]],
                ['name' => 'tax', 'label' => 'Steuersatz (%)', 'type' => 'number', 'default' => '0', 'width' => 'half'],
                ['name' => 'paid', 'label' => 'Bereits bar bezahlt', 'type' => 'checkbox', 'width' => 'half'],
                ['name' => 'note', 'label' => 'Hinweis', 'type' => 'textarea', 'rows' => 2],
            ],
        ],

        // ------------------------------------------------------------------
        'schreiben' => [
            'title'    => 'Allgemeines Schreiben',
            'short'    => 'Schreiben',
            'code'     => 'SC',
            'category' => 'Verwaltung',
            'role'     => 'medic',
            'patient'  => 'optional',
            'template' => 'schreiben',
            'fields'   => [
                ['name' => 'recipient', 'label' => 'Empfänger', 'type' => 'textarea', 'rows' => 4, 'placeholder' => "Los Santos Police Department\nz. Hd. Captain …\nMission Row\nLos Santos"],
                ['name' => 'your_ref', 'label' => 'Ihr Zeichen', 'type' => 'text', 'width' => 'half'],
                ['name' => 'our_ref', 'label' => 'Unser Zeichen', 'type' => 'text', 'width' => 'half'],
                ['name' => 'subject', 'label' => 'Betreff', 'type' => 'text', 'required' => true],
                ['name' => 'salutation', 'label' => 'Anrede', 'type' => 'text', 'default' => 'Sehr geehrte Damen und Herren,'],
                ['name' => 'body', 'label' => 'Text', 'type' => 'textarea', 'rows' => 12, 'required' => true],
                ['name' => 'closing', 'label' => 'Grußformel', 'type' => 'text', 'default' => 'Mit freundlichen Grüßen'],
                ['name' => 'seal', 'label' => 'Dienstsiegel aufdrucken', 'type' => 'checkbox'],
            ],
        ],
    ];

    return $types;
}

function doc_type(string $key): ?array
{
    $types = document_types();
    return isset($types[$key]) ? ['key' => $key] + $types[$key] : null;
}

/** Vorschlagslisten für Eingabefelder. */
function datalists(): array
{
    return [
        'icd' => [
            'S00.9 Oberflächliche Verletzung des Kopfes', 'S01.9 Offene Wunde des Kopfes', 'S02.2 Nasenbeinfraktur',
            'S06.0 Gehirnerschütterung', 'S06.5 Traumatische subdurale Blutung', 'S13.4 Verstauchung und Zerrung der HWS (Schleudertrauma)',
            'S20.2 Prellung des Thorax', 'S22.3 Rippenfraktur', 'S27.0 Traumatischer Pneumothorax', 'S30.0 Prellung der Lumbosakralgegend',
            'S36.0 Verletzung der Milz', 'S36.1 Verletzung der Leber', 'S42.0 Fraktur der Klavikula', 'S43.0 Luxation des Schultergelenkes',
            'S52.5 Distale Radiusfraktur', 'S62.6 Fraktur eines Fingers', 'S72.0 Schenkelhalsfraktur', 'S82.2 Fraktur des Tibiaschaftes',
            'S83.6 Verstauchung und Zerrung des Kniegelenkes', 'S93.4 Verstauchung und Zerrung des oberen Sprunggelenkes',
            'T14.0 Oberflächliche Verletzung, Körperregion n.n.bez.', 'T14.1 Offene Wunde – Schussverletzung', 'T14.1 Offene Wunde – Stichverletzung',
            'T14.2 Fraktur, Körperregion n.n.bez.', 'T30.0 Verbrennung, Grad n.n.bez.', 'T40.5 Vergiftung durch Kokain',
            'T42.4 Vergiftung durch Benzodiazepine', 'T51.0 Toxische Wirkung von Ethanol', 'T67.0 Hitzschlag und Sonnenstich',
            'T68 Hypothermie', 'T75.1 Ertrinken und nichttödliches Untertauchen', 'T79.4 Traumatischer Schock',
            'F10.0 Akute Alkoholintoxikation', 'F12.0 Akute Intoxikation durch Cannabinoide', 'F14.0 Akute Intoxikation durch Kokain',
            'F15.0 Akute Intoxikation durch Stimulanzien (Methamphetamin)', 'F32.9 Depressive Episode', 'F41.0 Panikstörung', 'F43.0 Akute Belastungsreaktion',
            'F43.1 Posttraumatische Belastungsstörung', 'I10.90 Essentielle Hypertonie', 'I21.9 Akuter Myokardinfarkt', 'I46.9 Herzstillstand',
            'I63.9 Hirninfarkt', 'J06.9 Akute Infektion der oberen Atemwege', 'J11.1 Grippe', 'J45.9 Asthma bronchiale', 'A09.9 Gastroenteritis',
            'K35.8 Akute Appendizitis', 'M54.2 Zervikalneuralgie', 'M54.5 Kreuzschmerz', 'R10.4 Bauchschmerzen', 'R51 Kopfschmerz',
            'R55 Synkope und Kollaps', 'E11.9 Diabetes mellitus Typ 2', 'E16.2 Hypoglykämie', 'G40.9 Epilepsie', 'N39.0 Harnwegsinfektion',
            'L03.1 Phlegmone', 'Z00.0 Allgemeine ärztliche Untersuchung', 'Z02.1 Einstellungsuntersuchung', 'Z02.4 Untersuchung zur Erlangung der Fahrerlaubnis',
            'Z04.1 Untersuchung nach Verkehrsunfall', 'Z04.5 Untersuchung nach Verletzung durch Gewalt',
        ],
        'meds' => [
            'Ibuprofen 600 mg Filmtbl. N2', 'Ibuprofen 400 mg Filmtbl. N1', 'Paracetamol 500 mg Tbl. N2', 'Metamizol 500 mg Tbl. N2',
            'Novaminsulfon Tropfen 500 mg/ml 20 ml', 'Diclofenac 75 mg Retardtbl. N2', 'Tramadol 50 mg Kps. N1', 'Tilidin/Naloxon 50/4 mg Retardtbl. N1',
            'Morphin 10 mg Retardtbl. N1 (BtM)', 'Oxycodon 10 mg Retardtbl. N1 (BtM)', 'Amoxicillin 1000 mg Filmtbl. N2', 'Cefuroxim 500 mg Filmtbl. N1',
            'Ciprofloxacin 500 mg Filmtbl. N1', 'Pantoprazol 40 mg Tbl. N2', 'Ondansetron 4 mg Schmelztbl. N1', 'MCP 10 mg Tbl. N1',
            'Salbutamol Dosieraerosol 100 µg', 'Prednisolon 20 mg Tbl. N1', 'Cetirizin 10 mg Filmtbl. N1', 'Lorazepam 1 mg Tbl. N1',
            'Enoxaparin 40 mg Fertigspritze N2', 'Ramipril 5 mg Tbl. N3', 'Metoprolol 47,5 mg Retardtbl. N3', 'Bepanthen Wund- und Heilsalbe 20 g',
            'Octenisept Wunddesinfektion 50 ml', 'Mullbinde / Verbandsmaterial',
        ],
        'departments' => [
            'Allgemeinmedizin', 'Innere Medizin', 'Chirurgie', 'Unfallchirurgie / Orthopädie', 'Neurochirurgie', 'Neurologie', 'Kardiologie',
            'Anästhesie / Intensivmedizin', 'Notaufnahme', 'Radiologie', 'Psychiatrie / Psychotherapie', 'Psychologie', 'Gynäkologie',
            'Pädiatrie', 'HNO', 'Augenheilkunde', 'Dermatologie', 'Urologie', 'Rechtsmedizin', 'Physiotherapie', 'Zahnmedizin',
        ],
        'services' => [
            'Notfalleinsatz Rettungswagen', 'Notarzteinsatz', 'Luftrettung (Helikopter)', 'Ambulante Erstversorgung', 'Ärztliche Untersuchung',
            'Wundversorgung / Naht', 'Gipsverband / Schienung', 'Röntgen', 'CT', 'Stationäre Behandlung (pro Tag)', 'Operation',
            'Ausstellung Attest', 'Tauglichkeitsuntersuchung', 'Blutentnahme auf behördliche Anordnung', 'Psychologisches Gutachten',
        ],
    ];
}

function datalist_html(): string
{
    $out = '';
    foreach (datalists() as $id => $items) {
        $out .= '<datalist id="dl-' . e($id) . '">';
        foreach ($items as $item) {
            $out .= '<option value="' . e($item) . '">';
        }
        $out .= '</datalist>';
    }
    return $out;
}

function field_default(array $f)
{
    $d = $f['default'] ?? '';
    if ($d === 'today') {
        return date('Y-m-d');
    }
    if ($d === 'now') {
        return date('H:i');
    }
    return $d;
}

/** Formularfeld für den Dokument-Editor ausgeben. */
function render_field(array $f, $value): string
{
    $type = $f['type'];
    if ($type === 'heading') {
        return '<h3 class="form-heading">' . e($f['label']) . '</h3>';
    }
    $name = $f['name'];
    $id = 'f_' . $name;
    $req = !empty($f['required']);
    $label = '<label for="' . e($id) . '">' . e($f['label']) . ($req ? ' <span class="req">*</span>' : '') . '</label>';
    $width = $f['width'] ?? 'full';
    $list = isset($f['list']) ? ' list="dl-' . e($f['list']) . '"' : '';
    $ph = isset($f['placeholder']) ? ' placeholder="' . e($f['placeholder']) . '"' : '';
    $reqAttr = $req && !in_array($type, ['checkboxes', 'radio', 'items'], true) ? ' required' : '';
    $html = '';

    switch ($type) {
        case 'textarea':
            $html = $label . '<textarea id="' . e($id) . '" name="d[' . e($name) . ']" rows="' . (int) ($f['rows'] ?? 3) . '"' . $ph . $reqAttr . '>' . e($value) . '</textarea>';
            if ($list) {
                $html .= '<div class="hint">Vorschläge: <input class="suggest" data-target="' . e($id) . '"' . $list . ' placeholder="tippen & mit Enter einfügen …"></div>';
            }
            break;

        case 'select':
            $html = $label . '<select id="' . e($id) . '" name="d[' . e($name) . ']"' . $reqAttr . '>';
            foreach ($f['options'] as $k => $v) {
                $html .= '<option value="' . e($k) . '"' . selected($k, $value) . '>' . e($v) . '</option>';
            }
            $html .= '</select>';
            break;

        case 'radio':
            $html = '<span class="label">' . e($f['label']) . ($req ? ' <span class="req">*</span>' : '') . '</span><div class="choices">';
            foreach ($f['options'] as $k => $v) {
                $html .= '<label class="choice"><input type="radio" name="d[' . e($name) . ']" value="' . e($k) . '"' . checked((string) $k === (string) $value) . ($req ? ' required' : '') . '> ' . e($v) . '</label>';
            }
            $html .= '</div>';
            break;

        case 'checkbox':
            $html = '<label class="choice single"><input type="checkbox" name="d[' . e($name) . ']" value="1"' . checked(!empty($value)) . '> ' . e($f['label']) . '</label>';
            break;

        case 'checkboxes':
            $value = is_array($value) ? $value : [];
            $cols = (int) ($f['columns'] ?? 2);
            $html = '<span class="label">' . e($f['label']) . ($req ? ' <span class="req">*</span>' : '') . '</span><div class="choices cols-' . $cols . '">';
            foreach ($f['options'] as $k => $v) {
                $html .= '<label class="choice"><input type="checkbox" name="d[' . e($name) . '][]" value="' . e($k) . '"' . checked(in_array((string) $k, array_map('strval', $value), true)) . '> ' . e($v) . '</label>';
            }
            $html .= '</div>';
            break;

        case 'items':
            $rows = is_array($value) && $value ? $value : [[]];
            $html = '<span class="label">' . e($f['label']) . ($req ? ' <span class="req">*</span>' : '') . '</span>';
            $html .= '<table class="items" data-name="' . e($name) . '" data-max="' . (int) ($f['max'] ?? 50) . '"><thead><tr>';
            foreach ($f['columns'] as $c) {
                $html .= '<th>' . e($c['label']) . '</th>';
            }
            $html .= '<th></th></tr></thead><tbody>';
            foreach ($rows as $i => $row) {
                $html .= '<tr>';
                foreach ($f['columns'] as $ck => $c) {
                    $cv = $row[$ck] ?? ($c['default'] ?? '');
                    $n = 'd[' . e($name) . '][' . (int) $i . '][' . e($ck) . ']';
                    $ctype = $c['type'] ?? 'text';
                    if ($ctype === 'checkbox') {
                        $html .= '<td class="center"><input type="checkbox" name="' . $n . '" value="1"' . checked(!empty($row[$ck])) . '></td>';
                    } else {
                        $html .= '<td><input type="' . e($ctype) . '" name="' . $n . '" value="' . e($cv) . '"'
                            . (isset($c['step']) ? ' step="' . e($c['step']) . '"' : '')
                            . (isset($c['list']) ? ' list="dl-' . e($c['list']) . '"' : '')
                            . (isset($c['placeholder']) ? ' placeholder="' . e($c['placeholder']) . '"' : '') . '></td>';
                    }
                }
                $html .= '<td><button type="button" class="btn-icon row-del" title="Zeile entfernen">✕</button></td></tr>';
            }
            $html .= '</tbody></table><button type="button" class="btn small row-add">+ Zeile</button>';
            break;

        case 'bodymap':
            $html = '<span class="label">' . e($f['label']) . '</span>'
                . '<div class="bodymap-editor" data-input="' . e($id) . '">' . bodymap_svg(is_array($value) ? $value : [], true) . '</div>'
                . '<input type="hidden" id="' . e($id) . '" name="d[' . e($name) . ']" value="' . e(json_encode(is_array($value) ? $value : [])) . '">'
                . '<div class="hint">In die Silhouette klicken, um eine nummerierte Markierung zu setzen. Markierung anklicken, um sie zu entfernen. Nummern im Befundtext referenzieren.</div>';
            break;

        case 'signature':
            $html = '<span class="label">' . e($f['label']) . '</span>'
                . '<div class="sigpad" data-input="' . e($id) . '"><canvas width="600" height="180"></canvas>'
                . '<button type="button" class="btn small sig-clear">Löschen</button></div>'
                . '<input type="hidden" id="' . e($id) . '" name="d[' . e($name) . ']" value="' . e($value) . '">'
                . '<div class="hint">Mit Maus, Stift oder Finger unterschreiben lassen.</div>';
            break;

        default:
            $inputType = in_array($type, ['date', 'time', 'number'], true) ? $type : 'text';
            $step = $type === 'number' ? ' step="any"' : '';
            $html = $label . '<input type="' . $inputType . '" id="' . e($id) . '" name="d[' . e($name) . ']" value="' . e($value) . '"' . $step . $list . $ph . $reqAttr . '>';
    }

    return '<div class="field w-' . e($width) . ' t-' . e($type) . ($req ? ' is-req' : '') . '">' . $html . '</div>';
}

/**
 * Formulardaten anhand der Felddefinition übernehmen und prüfen.
 * Gibt [daten, fehler] zurück.
 */
function collect_fields(array $fields, array $input): array
{
    $data = [];
    $errors = [];
    foreach ($fields as $f) {
        if ($f['type'] === 'heading') {
            continue;
        }
        $name = $f['name'];
        $raw = $input[$name] ?? null;
        switch ($f['type']) {
            case 'checkbox':
                $v = !empty($raw) ? '1' : '';
                break;
            case 'checkboxes':
                $v = array_values(array_filter(
                    is_array($raw) ? $raw : [],
                    fn($x) => is_string($x) && array_key_exists($x, $f['options'])
                ));
                break;
            case 'radio':
            case 'select':
                $v = is_string($raw) && array_key_exists($raw, $f['options']) ? $raw : '';
                break;
            case 'items':
                $v = [];
                foreach (is_array($raw) ? $raw : [] as $row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $clean = [];
                    $filled = false;
                    foreach ($f['columns'] as $ck => $c) {
                        $cv = $row[$ck] ?? '';
                        $cv = is_string($cv) ? trim(mb_substr($cv, 0, 500)) : '';
                        $clean[$ck] = $cv;
                        if ($cv !== '' && ($c['type'] ?? 'text') === 'text') {
                            $filled = true;
                        }
                    }
                    if ($filled) {
                        $v[] = $clean;
                    }
                }
                $v = array_slice($v, 0, (int) ($f['max'] ?? 50));
                break;
            case 'bodymap':
                $points = json_decode(is_string($raw) ? $raw : '[]', true);
                $v = [];
                foreach (is_array($points) ? $points : [] as $pt) {
                    if (isset($pt['x'], $pt['y']) && is_numeric($pt['x']) && is_numeric($pt['y'])) {
                        $v[] = ['x' => round((float) $pt['x'], 1), 'y' => round((float) $pt['y'], 1)];
                    }
                }
                $v = array_slice($v, 0, 40);
                break;
            case 'signature':
                $v = is_string($raw) && preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $raw) && strlen($raw) < 400000 ? $raw : '';
                break;
            default:
                $v = is_string($raw) ? trim($raw) : '';
                $v = mb_substr($v, 0, $f['type'] === 'textarea' ? 10000 : 500);
                if ($v !== '' && $f['type'] === 'date' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
                    $errors[] = $f['label'] . ': ungültiges Datum.';
                }
                if ($v !== '' && $f['type'] === 'time' && !preg_match('/^\d{2}:\d{2}$/', $v)) {
                    $errors[] = $f['label'] . ': ungültige Uhrzeit.';
                }
        }
        if (!empty($f['required']) && ($v === '' || $v === [])) {
            $errors[] = 'Pflichtfeld fehlt: ' . $f['label'];
        }
        $data[$name] = $v;
    }
    return [$data, $errors];
}

/**
 * Körperschema (Vorder- und Rückansicht) als SVG.
 * Koordinaten der Markierungen in Prozent des Zeichenbereichs.
 */
function bodymap_svg(array $points, bool $editable = false): string
{
    $figure = function (float $ox): string {
        // Einfache, ruhige Silhouette im Stil medizinischer Formulare
        return '<g transform="translate(' . $ox . ' 0)" class="figure">'
            . '<circle cx="50" cy="16" r="10"/>'
            . '<path d="M45 26 h10 v5 c10 1 17 4 19 12 l7 38 c1 5 -5 6 -6 1 l-8 -32 l-1 30 l3 62 c0 5 -7 6 -8 1 l-8 -55 h-2 l-8 55 c-1 5 -8 4 -8 -1 l3 -62 l-1 -30 l-8 32 c-1 5 -7 4 -6 -1 l7 -38 c2 -8 9 -11 19 -12 z"/>'
            . '</g>';
    };
    $svg = '<svg class="bodymap' . ($editable ? ' editable' : '') . '" viewBox="0 0 220 175" xmlns="http://www.w3.org/2000/svg">'
        . $figure(5) . $figure(115)
        . '<text x="55" y="173" text-anchor="middle" class="bm-label">ventral</text>'
        . '<text x="165" y="173" text-anchor="middle" class="bm-label">dorsal</text>'
        . '<line x1="110" y1="5" x2="110" y2="165" class="bm-sep"/>'
        . '<g class="marks">';
    foreach ($points as $i => $pt) {
        $x = (float) $pt['x'] * 2.2;
        $y = (float) $pt['y'] * 1.75;
        $svg .= '<g class="mark" data-i="' . $i . '"><circle cx="' . $x . '" cy="' . $y . '" r="5"/>'
            . '<text x="' . $x . '" y="' . ($y + 2.1) . '" text-anchor="middle">' . ($i + 1) . '</text></g>';
    }
    return $svg . '</g></svg>';
}

/** Nächste fortlaufende Dokumentnummer, z.B. SAMS-AU-2026-00042 */
function next_document_number(array $type): string
{
    $prefix = setting('doc_prefix', 'SAMS');
    $year = date('Y');
    $base = $prefix . '-' . $type['code'] . '-' . $year . '-';
    $last = scalar('SELECT number FROM documents WHERE number LIKE ? ORDER BY id DESC LIMIT 1', [$base . '%']);
    $n = $last ? ((int) substr((string) $last, strlen($base))) + 1 : 1;
    return $base . str_pad((string) $n, 5, '0', STR_PAD_LEFT);
}

function issuer_snapshot(array $u): array
{
    return [
        'id'            => (int) $u['id'],
        'name'          => $u['name'],
        'title'         => $u['title'],
        'role'          => $u['role'] ?? '',
        'rank_name'     => user_rank_name($u),
        'department'    => $u['department'],
        'badge_number'  => $u['badge_number'],
        'doctor_number' => $u['doctor_number'],
        'signature'     => $u['signature'],
    ];
}

function patient_snapshot(?array $p): ?array
{
    if (!$p) {
        return null;
    }
    $keys = ['id', 'citizen_id', 'first_name', 'last_name', 'dob', 'gender', 'phone', 'address', 'occupation', 'employer',
        'insurance', 'insurance_number', 'blood_type', 'height', 'weight', 'allergies'];
    return array_intersect_key($p, array_flip($keys));
}

function verify_url(string $code): string
{
    return base_url() . '/verify.php?c=' . rawurlencode($code);
}

/** Statusbadge für Dokumente */
function doc_status_badge(array $doc): string
{
    if ($doc['status'] === 'revoked') {
        return '<span class="badge red">Storniert</span>';
    }
    if (!empty($doc['valid_until']) && $doc['valid_until'] < date('Y-m-d')) {
        return '<span class="badge grey">Abgelaufen</span>';
    }
    return '<span class="badge green">Gültig</span>';
}
