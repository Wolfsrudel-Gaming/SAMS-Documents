<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Weitere Dokumentarten (Etappe 3): Gutachten, Unterbringung, Impfnachweis,
 * OP-Bericht/-Aufklärung sowie Personaldokumente.
 *
 * 'subject' => 'employee': Das Dokument betrifft ein Personalkonto statt
 * einer Patientenakte. Im Formular erscheint dann eine Personalauswahl.
 */
function document_types_extra(): array
{
    $yesNo = ['ja' => 'ja', 'nein' => 'nein'];
    return [

        // ------------------------------------------------------------------
        'psych_gutachten' => [
            'title'    => 'Psychologisches Gutachten',
            'short'    => 'Psych. Gutachten',
            'code'     => 'PG',
            'category' => 'Behörden & Gutachten',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'psych_gutachten',
            'fields'   => [
                ['name' => 'client', 'label' => 'Auftraggeber', 'type' => 'textarea', 'rows' => 3, 'required' => true,
                    'placeholder' => "Department of Justice\nSuperior Court of San Andreas\nRichter/in …"],
                ['name' => 'reference', 'label' => 'Aktenzeichen des Auftraggebers', 'type' => 'text', 'width' => 'half'],
                ['name' => 'order_date', 'label' => 'Auftrag vom', 'type' => 'date', 'width' => 'half'],
                ['name' => 'question', 'label' => 'Fragestellung', 'type' => 'textarea', 'rows' => 3, 'required' => true,
                    'placeholder' => 'z. B. Ist der Proband in der Lage, eine Waffe verantwortungsvoll zu führen? Liegt eine die Schuldfähigkeit beeinträchtigende Störung vor?'],
                ['name' => 'sessions', 'label' => 'Untersuchungstermine', 'type' => 'text', 'placeholder' => 'z. B. 12.10.2026 (90 min), 15.10.2026 (60 min)'],
                ['name' => 'sources', 'label' => 'Grundlagen', 'type' => 'checkboxes', 'options' => [
                    'exploration' => 'Exploration / klinisches Interview', 'akte' => 'Aktenstudium', 'tests' => 'Testpsychologische Untersuchung',
                    'fremd' => 'Fremdanamnese', 'koerper' => 'Körperliche Untersuchung', 'labor' => 'Laborbefunde / Drogenscreening']],
                ['type' => 'heading', 'label' => 'Befunde'],
                ['name' => 'history', 'label' => 'Anamnese (Vorgeschichte, Biografie, Sucht, Vorerkrankungen)', 'type' => 'textarea', 'rows' => 5],
                ['name' => 'mental_status', 'label' => 'Psychopathologischer Befund', 'type' => 'textarea', 'rows' => 5, 'required' => true,
                    'placeholder' => 'Bewusstsein klar, allseits orientiert. Auffassung, Konzentration und Gedächtnis unauffällig. Formales Denken geordnet, keine Wahninhalte. Stimmung ausgeglichen, Antrieb regelrecht. Keine Suizidalität, keine Fremdgefährdung.'],
                ['name' => 'tests', 'label' => 'Testdiagnostik (Verfahren und Ergebnisse)', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'z. B. MMPI-2: unauffälliges Profil; d2-R: durchschnittliche Konzentrationsleistung'],
                ['name' => 'diagnosis', 'label' => 'Diagnose(n) nach ICD-10', 'type' => 'textarea', 'rows' => 2, 'list' => 'icd', 'placeholder' => 'keine psychische Störung von Krankheitswert'],
                ['type' => 'heading', 'label' => 'Beurteilung'],
                ['name' => 'assessment', 'label' => 'Zusammenfassende Beurteilung', 'type' => 'textarea', 'rows' => 6, 'required' => true],
                ['name' => 'result', 'label' => 'Beantwortung der Fragestellung', 'type' => 'radio', 'required' => true, 'options' => [
                    'positiv' => 'Positiv / Eignung gegeben', 'auflagen' => 'Mit Einschränkungen / Auflagen', 'negativ' => 'Negativ / Eignung nicht gegeben', 'offen' => 'Nicht abschließend beantwortbar']],
                ['name' => 'recommendation', 'label' => 'Empfehlung', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'z. B. Wiedervorstellung in 6 Monaten, ambulante Psychotherapie, Abstinenznachweis'],
                ['name' => 'seal', 'label' => 'Dienstsiegel aufdrucken', 'type' => 'checkbox', 'default' => '1'],
            ],
        ],

        // ------------------------------------------------------------------
        'unterbringung' => [
            'title'    => 'Antrag auf Unterbringung (5150-Hold)',
            'short'    => 'Unterbringung',
            'code'     => 'UB',
            'category' => 'Behörden & Gutachten',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'unterbringung',
            'fields'   => [
                ['name' => 'kind', 'label' => 'Art der Unterbringung', 'type' => 'radio', 'required' => true, 'default' => '5150', 'options' => [
                    '5150' => 'Sofortige Unterbringung bis 72 Std. (Section 5150 W&I Code)', '5250' => 'Verlängerung bis 14 Tage (Section 5250)', 'gericht' => 'Antrag auf gerichtliche Unterbringung']],
                ['name' => 'grounds', 'label' => 'Gründe', 'type' => 'checkboxes', 'required' => true, 'options' => [
                    'selbst' => 'Gefahr für sich selbst (Eigengefährdung)', 'fremd' => 'Gefahr für andere (Fremdgefährdung)', 'hilflos' => 'Schwere Beeinträchtigung – kann sich nicht selbst versorgen (gravely disabled)']],
                ['name' => 'begin_date', 'label' => 'Beginn am', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'third'],
                ['name' => 'begin_time', 'label' => 'um', 'type' => 'time', 'default' => 'now', 'required' => true, 'width' => 'third'],
                ['name' => 'facility', 'label' => 'Einrichtung', 'type' => 'text', 'width' => 'third', 'default' => 'Pillbox Hill Medical Center – Psychiatrie'],
                ['name' => 'location', 'label' => 'Ort des Aufgreifens', 'type' => 'text', 'width' => 'half'],
                ['name' => 'brought_by', 'label' => 'Zugeführt durch', 'type' => 'select', 'width' => 'half', 'options' => [
                    'Rettungsdienst' => 'Rettungsdienst', 'LSPD' => 'LSPD', 'BCSO' => 'BCSO', 'Angehörige' => 'Angehörige', 'selbst' => 'selbst vorstellig']],
                ['name' => 'officer', 'label' => 'Beteiligte/r Beamter/Beamtin (Name, Dienstnummer)', 'type' => 'text'],
                ['name' => 'facts', 'label' => 'Tatsachen, die die Gefahr begründen', 'type' => 'textarea', 'rows' => 6, 'required' => true,
                    'placeholder' => 'Konkrete Beobachtungen und Äußerungen mit Datum/Uhrzeit, z. B. „Patient äußerte wiederholt, sich von der Brücke stürzen zu wollen …“'],
                ['name' => 'mental_status', 'label' => 'Psychischer Befund bei Aufnahme', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'diagnosis', 'label' => 'Verdachtsdiagnose', 'type' => 'text', 'list' => 'icd'],
                ['name' => 'voluntary_refused', 'label' => 'Freiwillige Behandlung wurde angeboten und abgelehnt', 'type' => 'checkbox'],
                ['name' => 'informed', 'label' => 'Patient/in wurde über Rechte und Grund der Unterbringung belehrt', 'type' => 'checkbox', 'default' => '1'],
                ['name' => 'relatives', 'label' => 'Benachrichtigte Angehörige / Betreuer', 'type' => 'text'],
            ],
        ],

        // ------------------------------------------------------------------
        'impfnachweis' => [
            'title'    => 'Impfbescheinigung',
            'short'    => 'Impfnachweis',
            'code'     => 'IM',
            'category' => 'Ärztliche Bescheinigungen',
            'role'     => 'medic',
            'patient'  => 'required',
            'template' => 'impfnachweis',
            'fields'   => [
                ['name' => 'items', 'label' => 'Impfungen', 'type' => 'items', 'required' => true, 'max' => 12, 'columns' => [
                    'date'    => ['label' => 'Datum', 'type' => 'date'],
                    'vaccine' => ['label' => 'Impfstoff / Handelsname', 'list' => 'vaccines'],
                    'against' => ['label' => 'Schutz gegen', 'placeholder' => 'Tetanus, Diphtherie'],
                    'batch'   => ['label' => 'Ch.-B. (Charge)', 'placeholder' => 'AB1234'],
                    'dose'    => ['label' => 'Dosis', 'placeholder' => '1/3'],
                ]],
                ['name' => 'next', 'label' => 'Nächste Impfung empfohlen', 'type' => 'text', 'placeholder' => 'z. B. Tetanus-Auffrischung 10/2036'],
                ['name' => 'remarks', 'label' => 'Bemerkungen', 'type' => 'text'],
            ],
        ],

        // ------------------------------------------------------------------
        'op_bericht' => [
            'title'    => 'Operationsbericht',
            'short'    => 'OP-Bericht',
            'code'     => 'OB',
            'category' => 'Klinik',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'op_bericht',
            'fields'   => [
                ['name' => 'op_date', 'label' => 'OP-Datum', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'quarter'],
                ['name' => 't_cut', 'label' => 'Schnitt', 'type' => 'time', 'width' => 'quarter'],
                ['name' => 't_suture', 'label' => 'Naht', 'type' => 'time', 'width' => 'quarter'],
                ['name' => 'room', 'label' => 'OP-Saal', 'type' => 'text', 'width' => 'quarter', 'placeholder' => 'OP 2'],
                ['name' => 'urgency', 'label' => 'Dringlichkeit', 'type' => 'radio', 'default' => 'elektiv', 'options' => [
                    'elektiv' => 'Elektiv', 'dringlich' => 'Dringlich', 'notfall' => 'Notfall', 'vital' => 'Vitale Indikation']],
                ['name' => 'surgeon', 'label' => 'Operateur/in', 'type' => 'text', 'width' => 'third'],
                ['name' => 'assistant', 'label' => 'Assistenz', 'type' => 'text', 'width' => 'third'],
                ['name' => 'anesthesia_doc', 'label' => 'Anästhesie', 'type' => 'text', 'width' => 'third'],
                ['name' => 'anesthesia', 'label' => 'Narkoseart', 'type' => 'select', 'width' => 'half', 'options' => [
                    'ITN' => 'Intubationsnarkose (ITN)', 'LAMA' => 'Larynxmaske', 'Spinal' => 'Spinalanästhesie', 'Regional' => 'Regionalanästhesie', 'Lokal' => 'Lokalanästhesie', 'Analgosedierung' => 'Analgosedierung']],
                ['name' => 'position', 'label' => 'Lagerung', 'type' => 'text', 'width' => 'half', 'placeholder' => 'Rückenlage'],
                ['name' => 'diagnosis', 'label' => 'Diagnose (ICD-10)', 'type' => 'textarea', 'rows' => 2, 'required' => true, 'list' => 'icd'],
                ['name' => 'procedure', 'label' => 'Eingriff (OPS)', 'type' => 'textarea', 'rows' => 2, 'required' => true, 'placeholder' => '5-470.11 Appendektomie, laparoskopisch'],
                ['name' => 'indication', 'label' => 'Indikation', 'type' => 'textarea', 'rows' => 2],
                ['name' => 'report', 'label' => 'Operationsverlauf', 'type' => 'textarea', 'rows' => 10, 'required' => true,
                    'placeholder' => "Team-Time-Out. Steriles Abwaschen und Abdecken. …\nHautschnitt … Präparation … \nKontrolle auf Bluttrockenheit, Zählkontrolle korrekt. Schichtweiser Wundverschluss, steriler Verband."],
                ['name' => 'implants', 'label' => 'Implantate / Material', 'type' => 'text'],
                ['name' => 'blood_loss', 'label' => 'Blutverlust (ml)', 'type' => 'text', 'width' => 'third'],
                ['name' => 'drains', 'label' => 'Drainagen', 'type' => 'text', 'width' => 'third'],
                ['name' => 'histology', 'label' => 'Histologie eingesandt', 'type' => 'checkbox', 'width' => 'third'],
                ['name' => 'complications', 'label' => 'Komplikationen', 'type' => 'text', 'default' => 'keine'],
                ['name' => 'aftercare', 'label' => 'Procedere / Nachbehandlung', 'type' => 'textarea', 'rows' => 3],
            ],
        ],

        // ------------------------------------------------------------------
        'op_aufklaerung' => [
            'title'    => 'Aufklärung und Einwilligung',
            'short'    => 'OP-Aufklärung',
            'code'     => 'EW',
            'category' => 'Klinik',
            'role'     => 'doctor',
            'patient'  => 'required',
            'template' => 'op_aufklaerung',
            'fields'   => [
                ['name' => 'procedure', 'label' => 'Geplanter Eingriff', 'type' => 'text', 'required' => true, 'placeholder' => 'z. B. Offene Reposition und Plattenosteosynthese distaler Radius links'],
                ['name' => 'planned_date', 'label' => 'Geplant am', 'type' => 'date', 'width' => 'half'],
                ['name' => 'anesthesia', 'label' => 'Betäubung', 'type' => 'select', 'width' => 'half', 'options' => [
                    'Vollnarkose' => 'Vollnarkose', 'Regionalanästhesie' => 'Regionalanästhesie', 'Lokalanästhesie' => 'Lokalanästhesie', 'Analgosedierung' => 'Analgosedierung']],
                ['name' => 'explanation', 'label' => 'Art und Ablauf des Eingriffs (für den Patienten verständlich)', 'type' => 'textarea', 'rows' => 4, 'required' => true],
                ['name' => 'risks', 'label' => 'Besprochene Risiken', 'type' => 'checkboxes', 'required' => true, 'options' => [
                    'blutung' => 'Blutung / Nachblutung, ggf. Transfusion', 'infektion' => 'Wundinfektion, Wundheilungsstörung', 'thrombose' => 'Thrombose / Embolie',
                    'nerven' => 'Verletzung von Nerven und Gefäßen', 'narbe' => 'Narbenbildung', 'allergie' => 'Allergische Reaktion',
                    'erweiterung' => 'Notwendige Erweiterung des Eingriffs', 'revision' => 'Erneute Operation', 'narkose' => 'Narkoserisiken (gesondert aufgeklärt)']],
                ['name' => 'risks_specific', 'label' => 'Spezielle Risiken', 'type' => 'textarea', 'rows' => 2],
                ['name' => 'alternatives', 'label' => 'Alternativen', 'type' => 'textarea', 'rows' => 2, 'placeholder' => 'z. B. konservative Behandlung im Gips mit höherem Risiko einer Fehlstellung'],
                ['name' => 'questions', 'label' => 'Fragen des Patienten / Gesprächsnotizen', 'type' => 'textarea', 'rows' => 2],
                ['name' => 'decision', 'label' => 'Entscheidung', 'type' => 'radio', 'required' => true, 'default' => 'ja', 'options' => [
                    'ja' => 'Ich willige in den Eingriff ein', 'nein' => 'Ich lehne den Eingriff ab (über die Folgen wurde ich aufgeklärt)']],
                ['name' => 'copy_received', 'label' => 'Patient/in hat eine Kopie erhalten', 'type' => 'checkbox', 'default' => '1'],
                ['name' => 'patient_signature', 'label' => 'Unterschrift Patient/in bzw. gesetzl. Vertreter', 'type' => 'signature'],
                ['name' => 'witness', 'label' => 'Zeuge/Zeugin (Name)', 'type' => 'text'],
            ],
        ],

        // ------------------------------------------------------------------ Personal
        'dienstausweis' => [
            'title'    => 'Dienstausweis',
            'short'    => 'Dienstausweis',
            'code'     => 'DA',
            'category' => 'Personal',
            'role'     => 'admin',
            'patient'  => 'none',
            'subject'  => 'employee',
            'template' => 'dienstausweis',
            'card'     => true, // im Spiel als drehbare Karte statt Blatt auf dem Klemmbrett
            'kinds'    => '*',
            'valid'    => 'valid_until',
            'fields'   => [
                ['name' => 'function', 'label' => 'Funktion auf dem Ausweis', 'type' => 'text', 'placeholder' => 'leer = Rang des Mitarbeiters', 'width' => 'half'],
                ['name' => 'valid_until', 'label' => 'Gültig bis', 'type' => 'date', 'required' => true, 'width' => 'half'],
                ['name' => 'photo', 'label' => 'Foto-URL (optional, sonst Initialen)', 'type' => 'text', 'placeholder' => 'https://…/foto.png'],
                ['name' => 'access', 'label' => 'Zugangsberechtigungen', 'type' => 'checkboxes', 'options' => [
                    'klinik' => 'Klinik', 'op' => 'OP-Bereich', 'intensiv' => 'Intensivstation', 'btm' => 'BtM-Schrank', 'heli' => 'Hubschrauberlandeplatz', 'leitung' => 'Verwaltung / Leitung']],
                ['name' => 'blood_type', 'label' => 'Blutgruppe (Rückseite)', 'type' => 'text', 'width' => 'half'],
                ['name' => 'emergency', 'label' => 'Notfallkontakt (Rückseite)', 'type' => 'text', 'width' => 'half'],
            ],
        ],

        'befoerderung' => [
            'title'    => 'Beförderungsurkunde',
            'short'    => 'Beförderungsurkunde',
            'code'     => 'BU',
            'category' => 'Personal',
            'role'     => 'admin',
            'patient'  => 'none',
            'subject'  => 'employee',
            'template' => 'befoerderung',
            'kinds'    => '*',
            'fields'   => [
                ['name' => 'new_rank', 'label' => 'Neuer Rang / neue Funktion', 'type' => 'text', 'required' => true, 'list' => 'ranks'],
                ['name' => 'effective', 'label' => 'Mit Wirkung vom', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'half'],
                ['name' => 'kind', 'label' => 'Anlass', 'type' => 'select', 'width' => 'half', 'default' => 'befoerderung', 'options' => [
                    'befoerderung' => 'Beförderung', 'ernennung' => 'Ernennung', 'auszeichnung' => 'Auszeichnung / Ehrung', 'jubilaeum' => 'Dienstjubiläum']],
                ['name' => 'laudation', 'label' => 'Würdigung (optional)', 'type' => 'textarea', 'rows' => 3, 'placeholder' => 'In Anerkennung der hervorragenden Leistungen bei …'],
                ['name' => 'signer2', 'label' => 'Zweite Unterschrift (Name, Funktion)', 'type' => 'text', 'placeholder' => 'z. B. Dr. A. Reyes, Ärztliche Direktorin'],
            ],
        ],

        'ausbildungsnachweis' => [
            'title'    => 'Ausbildungs- / Qualifikationsnachweis',
            'short'    => 'Ausbildungsnachweis',
            'code'     => 'AN',
            'category' => 'Personal',
            'role'     => 'admin',
            'patient'  => 'none',
            'subject'  => 'employee',
            'template' => 'ausbildungsnachweis',
            'kinds'    => '*',
            'valid'    => 'valid_until',
            'fields'   => [
                ['name' => 'course', 'label' => 'Lehrgang / Qualifikation', 'type' => 'text', 'required' => true, 'list' => 'qualifications'],
                ['name' => 'date_from', 'label' => 'von', 'type' => 'date', 'width' => 'quarter'],
                ['name' => 'date_to', 'label' => 'bis', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'quarter'],
                ['name' => 'hours', 'label' => 'Unterrichtseinheiten', 'type' => 'number', 'width' => 'quarter'],
                ['name' => 'valid_until', 'label' => 'Gültig bis (optional)', 'type' => 'date', 'width' => 'quarter'],
                ['name' => 'contents', 'label' => 'Inhalte', 'type' => 'textarea', 'rows' => 4, 'placeholder' => "Atemwegsmanagement\nReanimation nach ERC-Leitlinien\nTraumaversorgung (PHTLS)"],
                ['name' => 'result', 'label' => 'Ergebnis', 'type' => 'radio', 'required' => true, 'default' => 'bestanden', 'options' => [
                    'mit_auszeichnung' => 'mit Auszeichnung bestanden', 'bestanden' => 'bestanden', 'teilgenommen' => 'teilgenommen']],
                ['name' => 'grade', 'label' => 'Note / Punkte (optional)', 'type' => 'text', 'width' => 'half'],
                ['name' => 'instructor', 'label' => 'Ausbilder/in', 'type' => 'text', 'width' => 'half'],
            ],
        ],

        'abmahnung' => [
            'title'    => 'Abmahnung',
            'short'    => 'Abmahnung',
            'code'     => 'AM',
            'category' => 'Personal',
            'role'     => 'admin',
            'patient'  => 'none',
            'subject'  => 'employee',
            'template' => 'personalbrief',
            'kinds'    => '*',
            'fields'   => [
                ['name' => 'incident_date', 'label' => 'Vorfall am', 'type' => 'date', 'required' => true, 'width' => 'half'],
                ['name' => 'level', 'label' => 'Art', 'type' => 'select', 'width' => 'half', 'default' => 'abmahnung', 'options' => [
                    'ermahnung' => 'Ermahnung', 'abmahnung' => 'Abmahnung', 'letzte' => 'Letztmalige Abmahnung']],
                ['name' => 'facts', 'label' => 'Sachverhalt (konkret: was, wann, wo)', 'type' => 'textarea', 'rows' => 5, 'required' => true],
                ['name' => 'duty', 'label' => 'Verletzte Pflicht / Dienstanweisung', 'type' => 'textarea', 'rows' => 2, 'required' => true],
                ['name' => 'expectation', 'label' => 'Erwartetes Verhalten', 'type' => 'textarea', 'rows' => 2, 'default' => 'Wir fordern Sie auf, Ihre dienstlichen Pflichten künftig ordnungsgemäß zu erfüllen.'],
                ['name' => 'consequence', 'label' => 'Androhung', 'type' => 'textarea', 'rows' => 2, 'default' => 'Im Wiederholungsfall müssen Sie mit weiteren arbeitsrechtlichen Konsequenzen bis hin zur Kündigung rechnen.'],
                ['name' => 'ack', 'label' => 'Empfangsbestätigung durch Mitarbeiter/in vorsehen', 'type' => 'checkbox', 'default' => '1'],
            ],
        ],

        'kuendigung' => [
            'title'    => 'Kündigung',
            'short'    => 'Kündigung',
            'code'     => 'KD',
            'category' => 'Personal',
            'role'     => 'admin',
            'patient'  => 'none',
            'subject'  => 'employee',
            'template' => 'personalbrief',
            'kinds'    => '*',
            'fields'   => [
                ['name' => 'kind', 'label' => 'Art', 'type' => 'radio', 'required' => true, 'default' => 'ordentlich', 'options' => [
                    'ordentlich' => 'Ordentliche Kündigung', 'fristlos' => 'Außerordentliche (fristlose) Kündigung', 'aufhebung' => 'Bestätigung Aufhebung / Eigenkündigung']],
                ['name' => 'end_date', 'label' => 'Beendigung zum', 'type' => 'date', 'required' => true, 'width' => 'half'],
                ['name' => 'return_items', 'label' => 'Rückgabe bis', 'type' => 'date', 'width' => 'half'],
                ['name' => 'reason', 'label' => 'Begründung (optional)', 'type' => 'textarea', 'rows' => 4],
                ['name' => 'items', 'label' => 'Zurückzugebende Gegenstände', 'type' => 'checkboxes', 'options' => [
                    'ausweis' => 'Dienstausweis', 'kleidung' => 'Dienstkleidung', 'funk' => 'Funkgerät', 'schluessel' => 'Schlüssel / Zugangskarte', 'fahrzeug' => 'Dienstfahrzeug', 'tablet' => 'Dienst-Tablet']],
                ['name' => 'ack', 'label' => 'Empfangsbestätigung durch Mitarbeiter/in vorsehen', 'type' => 'checkbox', 'default' => '1'],
            ],
        ],

        'arbeitsvertrag' => [
            'title'    => 'Arbeitsvertrag',
            'short'    => 'Arbeitsvertrag',
            'code'     => 'AV',
            'category' => 'Personal',
            'role'     => 'admin',
            'patient'  => 'none',
            'subject'  => 'employee',
            'template' => 'arbeitsvertrag',
            'kinds'    => '*',
            'fields'   => [
                ['name' => 'position', 'label' => 'Tätigkeit / Rang', 'type' => 'text', 'required' => true, 'list' => 'ranks', 'width' => 'half'],
                ['name' => 'department', 'label' => 'Abteilung', 'type' => 'text', 'list' => 'staff_departments', 'width' => 'half'],
                ['name' => 'start', 'label' => 'Beginn', 'type' => 'date', 'default' => 'today', 'required' => true, 'width' => 'third'],
                ['name' => 'probation', 'label' => 'Probezeit (Monate)', 'type' => 'number', 'default' => '3', 'width' => 'third'],
                ['name' => 'fixed_until', 'label' => 'Befristet bis (leer = unbefristet)', 'type' => 'date', 'width' => 'third'],
                ['name' => 'hours', 'label' => 'Wochenstunden', 'type' => 'number', 'default' => '40', 'width' => 'third'],
                ['name' => 'salary', 'label' => 'Vergütung', 'type' => 'text', 'width' => 'third', 'placeholder' => '$ 2.500 / Woche'],
                ['name' => 'vacation', 'label' => 'Urlaubstage / Jahr', 'type' => 'number', 'default' => '30', 'width' => 'third'],
                ['name' => 'notice', 'label' => 'Kündigungsfrist', 'type' => 'text', 'default' => '4 Wochen zum Monatsende'],
                ['name' => 'extra', 'label' => 'Besondere Vereinbarungen', 'type' => 'textarea', 'rows' => 3],
                ['name' => 'employee_signature', 'label' => 'Unterschrift Arbeitnehmer/in', 'type' => 'signature'],
            ],
        ],
    ];
}
