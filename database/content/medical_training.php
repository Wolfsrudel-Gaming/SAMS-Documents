<?php
/** Ausbildungsinhalte Rettungsdienst: Lehrgänge mit Lernmodulen und Prüfungsfragen. */
return [
    [
        'title' => 'Einführung in den Rettungsdienst',
        'description' => 'Grundlagenlehrgang für neue Mitarbeitende der San Andreas Medical Services. Vermittelt Aufgaben, Rettungskette, Fahrzeuge, Funkverkehr und das richtige Verhalten im Dienst.',
        'hours' => 4, 'pass_percent' => 70, 'question_count' => 8, 'time_limit' => 15,
        'qualification' => null,
        'modules' => [
            ['Aufgaben des Rettungsdienstes', <<<'MD'
# Aufgaben des Rettungsdienstes

Der Rettungsdienst der **San Andreas Medical Services (SAMS)** stellt die präklinische Notfallversorgung im gesamten Staat sicher – von Los Santos über Sandy Shores bis Paleto Bay. Zentrale Klinik ist das **Pillbox Hill Medical Center**.

## Kernaufgaben

- **Notfallrettung:** Lebensrettende Maßnahmen am Einsatzort, Herstellen der Transportfähigkeit und Transport in eine geeignete Klinik.
- **Krankentransport:** Planbare Fahrten von Patienten, die keine Notfallpatienten sind, aber fachliche Betreuung benötigen.
- **Notarztdienst:** Bei lebensbedrohlichen Zuständen wird ein Notarzt nachgefordert oder direkt mitalarmiert.
- **Sanitätsdienst:** Absicherung von Veranstaltungen, Rennen oder Großlagen.
- **Klinische Versorgung:** Behandlung in der Notaufnahme, ambulante Versorgung, Ausstellung von Attesten, AU und Rezepten.

## Hilfsfrist

Ziel ist, dass ein Rettungsmittel innerhalb von **10 bis 15 Minuten** nach Notrufeingang am Einsatzort ist. Jede Minute ohne Reanimation senkt die Überlebenschance beim Herzstillstand um etwa **10 Prozent**.

## Grundsätze

1. **Eigenschutz vor Fremdschutz:** Kein Retter betritt eine unsichere Einsatzstelle. Bei Gewalt oder Schusswaffen wird zuerst das LSPD oder BCSO zur Absicherung angefordert.
2. **Patientenwohl:** Jeder Patient wird unabhängig von Herkunft, Fraktion oder Vorgeschichte gleich behandelt.
3. **Neutralität:** Der Rettungsdienst ermittelt nicht und ergreift keine Partei.
4. **Dokumentation:** Jeder Einsatz wird im System mit einem Einsatzprotokoll dokumentiert.

*Merke:* Der Rettungsdienst ist eine Gesundheitseinrichtung, keine Strafverfolgungsbehörde. Vertrauen der Bevölkerung ist unser wichtigstes Gut.
MD],
            ['Die Rettungskette', <<<'MD'
# Die Rettungskette

Die Rettungskette beschreibt die Abfolge aller Maßnahmen vom Notfallereignis bis zur klinischen Versorgung. Sie ist nur so stark wie ihr schwächstes Glied.

## Die fünf Glieder

1. **Sofortmaßnahmen:** Absichern der Unfallstelle, Retten aus dem Gefahrenbereich, Herz-Lungen-Wiederbelebung, Blutstillung, stabile Seitenlage.
2. **Notruf:** Über die **911** wird die Leitstelle alarmiert.
3. **Erste Hilfe:** Laienhelfer betreuen den Patienten bis zum Eintreffen des Rettungsdienstes, wärmen, beruhigen und beobachten.
4. **Rettungsdienst:** Professionelle Versorgung vor Ort und Transport.
5. **Krankenhaus:** Weiterbehandlung in Notaufnahme, OP oder Intensivstation.

## Die W-Fragen des Notrufs

- **Wo** ist der Notfall? (Straße, Postleitzahl, markante Punkte)
- **Was** ist passiert?
- **Wie viele** Verletzte gibt es?
- **Welche** Verletzungen oder Symptome liegen vor?
- **Warten** auf Rückfragen der Leitstelle.

## Bedeutung für den Rettungsdienst

Schon bei der Anfahrt wertet das Team die Alarmierungsinformationen aus: Wie viele Patienten? Brauchen wir Polizei, Feuerwehr oder einen Notarzt? Gibt es Gefahren wie Schusswaffen, Feuer oder Gas?

Ein gut funktionierendes Glied ist auch die **Klinikvoranmeldung**: Kritische Patienten werden per Funk im Pillbox Hill Medical Center angekündigt, damit Schockraum oder Herzkatheter vorbereitet werden können.

*Merkhilfe:* „Sichern, Melden, Helfen, Retten, Heilen“ – so lässt sich die Rettungskette kurz zusammenfassen.
MD],
            ['Fahrzeuge und Ausrüstung', <<<'MD'
# Fahrzeuge und Ausrüstung

Die SAMS setzen verschiedene Rettungsmittel ein, die je nach Lage disponiert werden.

## Rettungsmittel

- **RTW (Rettungswagen):** Standardfahrzeug für Notfälle. Besatzung mindestens zwei Personen, Ausstattung für Diagnostik, Reanimation und Transport.
- **KTW (Krankentransportwagen):** Für nicht lebensbedrohlich erkrankte Patienten, einfachere Ausstattung.
- **NEF (Notarzteinsatzfahrzeug):** Bringt den Notarzt zum Einsatzort, arbeitet im Rendezvous-System mit dem RTW.
- **RTH (Rettungshubschrauber):** Für lange Anfahrtswege, etwa ins Blaine County oder in die Berge, sowie für schnelle Transporte schwerverletzter Patienten.
- **Sonderfahrzeuge:** Geländefahrzeuge, Boote, MANV-Abrollbehälter.

## Ausrüstung des RTW

- **Notfallrucksack** mit Atemweg (Beutel, Masken, Guedeltuben), Kreislauf (Zugänge, Infusionen) und Verbandmaterial
- **EKG-Defibrillator** mit 12-Kanal-EKG, Pulsoxymetrie, Blutdruck, Kapnografie
- **Absaugpumpe** und **Sauerstoff**
- **Medikamentenkoffer**, BtM gesichert und dokumentiert
- **Immobilisation:** Vakuummatratze, Schaufeltrage, Spineboard, HWS-Orthese, Schienen
- **Trage** und **Tragestuhl**

## Fahrzeugcheck

Zu Dienstbeginn wird das Fahrzeug nach Checkliste geprüft:
1. Sauerstoffvorrat (mindestens halbvoll)
2. Defibrillator-Selbsttest und Akkus
3. Medikamente auf Vollständigkeit und Verfallsdatum
4. Sondersignalanlage und Funk
5. Sauberkeit und Desinfektion

*Merke:* Fehlendes Material im Einsatz ist fast immer die Folge eines ausgelassenen Fahrzeugchecks.
MD],
            ['Funk und Statusmeldungen', <<<'MD'
# Funk und Statusmeldungen

Der Funkverkehr ist das Nervensystem des Rettungsdienstes. Er muss **kurz, klar und eindeutig** sein.

## Grundregeln

- Erst **denken**, dann Taste drücken, dann sprechen.
- Gesprächsaufbau: *„Leitstelle von RTW 2-1, kommen.“* – erst Gerufener, dann Rufender.
- Keine Klarnamen von Patienten über Funk; nur Alter, Geschlecht und Verdachtsdiagnose.
- Funkdisziplin: Notrufe und Prioritätsmeldungen haben immer Vorrang.

## Statusmeldungen (FMS)

- **Status 1:** Einsatzbereit über Funk (unterwegs)
- **Status 2:** Einsatzbereit auf Wache
- **Status 3:** Einsatz übernommen, Anfahrt
- **Status 4:** Am Einsatzort eingetroffen
- **Status 5:** Sprechwunsch
- **Status 6:** Nicht einsatzbereit (z. B. Desinfektion, Pause)
- **Status 7:** Patient aufgenommen, Transport
- **Status 8:** Am Zielort (Klinik) angekommen
- **Status 0:** Priorisierter Sprechwunsch, Notfall (z. B. Bedrohung der Besatzung)

## Rückmeldung von der Einsatzstelle

Nach Eintreffen erfolgt eine kurze **Lagemeldung**: Anzahl Patienten, Schweregrad, Nachforderungen (Notarzt, Polizei, Feuerwehr). Beispiel: *„Leitstelle von RTW 2-1, ein Patient, männlich, etwa 30, Schussverletzung Oberschenkel, kreislaufinstabil, erbitten NEF und LSPD.“*

## Status 0

Status 0 wird nur bei akuter Gefahr für die Besatzung gedrückt. Die Leitstelle schaltet sofort durch und alarmiert Polizei.

*Merke:* Was nicht gefunkt wurde, weiß die Leitstelle nicht. Status immer zeitnah setzen.
MD],
            ['Verhalten im Dienst', <<<'MD'
# Verhalten im Dienst

Mitarbeitende der SAMS repräsentieren die Organisation bei jedem Einsatz. Professionelles Auftreten schafft Vertrauen bei Patienten, Angehörigen und Behörden.

## Dienstbeginn und Dienstende

- Eintragung im **Dienstplan** im System vor Schichtbeginn.
- Vollständige **Dienstkleidung** und Dienstausweis.
- Fahrzeugcheck und Übergabe von der Vorschicht.
- Zum Dienstende Fahrzeug aufgerüstet, desinfiziert und alle Einsatzprotokolle abgeschlossen übergeben.

## Auftreten am Einsatzort

- Vorstellen mit Namen und Funktion: *„Ich bin Alex vom Rettungsdienst, ich helfe Ihnen jetzt.“*
- Ruhig und respektvoll sprechen, auch bei aggressiven Patienten.
- Keine Bewertung von Lebensstil, Drogenkonsum oder Straftaten.
- Angehörige einbeziehen, ohne die Schweigepflicht zu verletzen.

## Umgang mit Gefahr

- Bei Schusswechseln oder Bedrohung: Rückzug, Bereitstellungsraum, Polizei abwarten.
- Waffen von Patienten werden **nicht** angefasst, sondern der Polizei übergeben.
- Die SAMS tragen keine Waffen und beteiligen sich nicht an Festnahmen.

## Verbote

1. Kein Alkohol oder Drogen vor und im Dienst.
2. Keine Weitergabe von Patientendaten an Unbefugte, auch nicht an Freunde in Polizei oder Justiz.
3. Keine Entnahme von Medikamenten zum privaten Gebrauch, insbesondere keine BtM.
4. Kein Missbrauch der Sondersignale.

## Teamarbeit

Der **Teamführer** (meist der erfahrenere Notfallsanitäter) koordiniert, der Teampartner arbeitet zu. Kritik wird nach dem Einsatz sachlich besprochen, nicht vor dem Patienten.

*Merke:* Höflichkeit kostet nichts, ein schlechter Ruf kostet die ganze Organisation.
MD],
        ],
        'questions' => [
            ['Welche Notrufnummer wird in San Andreas für den Rettungsdienst gewählt?', ['110', '112', '911', '119'], [2], 'In San Andreas ist die 911 die einheitliche Notrufnummer.'],
            ['Was ist der wichtigste Grundsatz beim Eintreffen an einer unsicheren Einsatzstelle?', ['Sofort zum Patienten laufen', 'Eigenschutz vor Fremdschutz', 'Zuerst Personalien aufnehmen', 'Den Patienten sofort einladen'], [1], 'Ein verletzter Retter kann niemandem mehr helfen. Bei Gefahr wird die Polizei zur Absicherung angefordert.'],
            ['Welche Statusmeldung bedeutet „Am Einsatzort eingetroffen“?', ['Status 2', 'Status 3', 'Status 4', 'Status 7'], [2], 'Status 4 wird bei Ankunft am Einsatzort gesetzt.'],
            ['Wann wird Status 0 gedrückt?', ['Bei jeder Rückfrage', 'Bei akuter Gefahr für die Besatzung', 'Zum Schichtende', 'Wenn der Patient abgelehnt hat'], [1], 'Status 0 ist der priorisierte Sprechwunsch für Notfälle der Besatzung.'],
            ['Welche Fahrzeuge gehören zu den Rettungsmitteln der SAMS? (Mehrere Antworten möglich)', ['RTW', 'NEF', 'RTH', 'Streifenwagen'], [0, 1, 2], 'RTW, NEF und RTH sind Rettungsmittel. Streifenwagen gehören zur Polizei.'],
            ['Welche Informationen gehören NICHT in einen Funkspruch?', ['Alter des Patienten', 'Verdachtsdiagnose', 'Vollständiger Name des Patienten', 'Nachforderung Notarzt'], [2], 'Klarnamen werden aus Datenschutzgründen nicht über Funk genannt.'],
            ['Was tun Sie, wenn ein Patient eine Schusswaffe bei sich trägt?', ['Die Waffe entladen und einstecken', 'Die Waffe liegen lassen und der Polizei übergeben bzw. die Polizei informieren', 'Die Waffe dem Angehörigen geben', 'Die Waffe im RTW verstauen'], [1], 'Waffen fasst der Rettungsdienst nicht an, sie werden der Polizei überlassen.'],
            ['Um wie viel Prozent sinkt die Überlebenschance etwa pro Minute ohne Reanimation beim Herzstillstand?', ['1 Prozent', '5 Prozent', '10 Prozent', '25 Prozent'], [2], 'Pro Minute ohne Maßnahmen sinkt die Überlebenschance um etwa 10 Prozent.'],
            ['Was gehört zum Fahrzeugcheck zu Dienstbeginn? (Mehrere Antworten möglich)', ['Sauerstoffvorrat prüfen', 'Defibrillator-Selbsttest', 'Verfallsdaten der Medikamente', 'Tankquittung der Vorschicht unterschreiben'], [0, 1, 2], 'Sauerstoff, Defibrillator und Medikamente sind Kernbestandteile des Checks.'],
            ['Was ist das zweite Glied der Rettungskette?', ['Krankenhaus', 'Notruf', 'Rettungsdienst', 'Erste Hilfe'], [1], 'Nach den Sofortmaßnahmen folgt der Notruf über die 911.'],
        ],
    ],
    [
        'title' => 'Patientenuntersuchung nach ABCDE',
        'description' => 'Strukturierte Untersuchung von Notfallpatienten nach dem xABCDE-Schema. Umfasst Vitalparameter, Anamnese mit SAMPLER und OPQRST, Bodycheck und strukturierte Übergabe.',
        'hours' => 5, 'pass_percent' => 75, 'question_count' => 8, 'time_limit' => 15,
        'qualification' => null,
        'modules' => [
            ['Ersteindruck und xABCDE', <<<'MD'
# Ersteindruck und xABCDE

Die strukturierte Untersuchung nach **xABCDE** stellt sicher, dass lebensbedrohliche Probleme in der richtigen Reihenfolge erkannt und sofort behandelt werden: *„Treat first what kills first.“*

## Ersteindruck (erste 10 Sekunden)

Noch bevor man den Patienten berührt:
- Ist die Einsatzstelle sicher?
- Wirkt der Patient **kritisch** oder **nicht kritisch**?
- Hautfarbe, Atemgeräusche, Bewusstsein, sichtbare Blutungen
- Ansprechen: *„Hallo, können Sie mich hören?“*

## Das Schema

- **x – eXsanguination:** Kritische äußere Blutungen sofort stoppen (Druck, Druckverband, Tourniquet).
- **A – Airway:** Atemweg frei? Fremdkörper, Erbrochenes, Schwellung? Bei Trauma HWS mitdenken.
- **B – Breathing:** Atemfrequenz, Atemtiefe, Seitengleichheit, SpO2, Atemgeräusche.
- **C – Circulation:** Puls (Frequenz, Qualität), Rekapillarisierungszeit, Blutdruck, Hautkolorit, Blutungsquellen.
- **D – Disability:** Bewusstsein (AVPU oder GCS), Pupillen, Blutzucker, grobe Neurologie.
- **E – Exposure/Environment:** Entkleiden, Ganzkörperuntersuchung, Wärmeerhalt.

## Grundregel

Wird in einem Schritt ein Problem gefunden, wird es **sofort behandelt**, bevor man weitergeht. Nach jeder Maßnahme und bei jeder Verschlechterung beginnt man wieder bei x bzw. A.

## AVPU

- **A**lert – wach
- **V**oice – reagiert auf Ansprache
- **P**ain – reagiert nur auf Schmerz
- **U**nresponsive – reagiert nicht

*Merke:* Ein kritischer Patient bekommt maximal 10 Minuten am Einsatzort („Platinum Ten Minutes“), danach Transport.
MD],
            ['Vitalparameter', <<<'MD'
# Vitalparameter

Vitalparameter sind objektive Messwerte, die den Zustand des Patienten beschreiben. Sie werden zu Beginn und im Verlauf wiederholt gemessen und dokumentiert.

## Normwerte Erwachsene

- **Atemfrequenz:** 12–20 pro Minute
- **Sauerstoffsättigung (SpO2):** 94–99 Prozent (bei COPD 88–92 Prozent akzeptabel)
- **Herzfrequenz:** 60–100 pro Minute
- **Blutdruck:** etwa 120/80 mmHg, kritisch bei systolisch unter 90 mmHg
- **Rekapillarisierungszeit:** unter 2 Sekunden
- **Blutzucker:** 70–120 mg/dl nüchtern, Hypoglykämie unter 60 mg/dl
- **Temperatur:** 36,5–37,5 °C
- **Glasgow Coma Scale (GCS):** 15 Punkte normal, 8 oder weniger bedeutet Atemwegsgefährdung

## Glasgow Coma Scale

1. **Augen öffnen** (1–4): spontan 4, auf Ansprache 3, auf Schmerz 2, nicht 1
2. **Verbale Antwort** (1–5): orientiert 5, verwirrt 4, unangemessene Worte 3, unverständliche Laute 2, keine 1
3. **Motorische Antwort** (1–6): befolgt Aufforderungen 6, gezielte Abwehr 5, ungezielte Abwehr 4, Beugen 3, Strecken 2, keine 1

## Schockindex

Der **Schockindex** ist Herzfrequenz geteilt durch systolischen Blutdruck. Ein Wert über **1,0** weist auf einen drohenden oder manifesten Schock hin. Beispiel: HF 120, RR 100 systolisch ergibt 1,2.

## Messfehler vermeiden

- Kalte Finger verfälschen die SpO2.
- Falsche Manschettengröße verfälscht den Blutdruck.
- Blutzucker nicht am Finger mit Glukoseresten messen.

*Merke:* Ein einzelner Wert sagt wenig – entscheidend ist der **Trend**. Vitalwerte mindestens alle 5 Minuten bei kritischen Patienten wiederholen.
MD],
            ['SAMPLER-Anamnese und OPQRST', <<<'MD'
# SAMPLER-Anamnese und OPQRST

Nach der Erstuntersuchung folgt die strukturierte Befragung des Patienten oder seiner Angehörigen. Die Anamnese liefert oft die entscheidenden Hinweise auf die Diagnose.

## SAMPLER

- **S – Symptome:** Was führt Sie zu uns? Was ist das Hauptproblem?
- **A – Allergien:** Gegen Medikamente, Lebensmittel, Latex?
- **M – Medikamente:** Dauermedikation, Blutverdünner, Insulin, Drogen?
- **P – Patientengeschichte:** Vorerkrankungen, Operationen, Schwangerschaft?
- **L – Letzte Mahlzeit:** Wann zuletzt gegessen und getrunken? Wichtig für Narkose. Bei Frauen auch letzte Periode.
- **E – Ereignis:** Was ist genau passiert? Was haben Sie gemacht, als es begann?
- **R – Risikofaktoren:** Rauchen, Alkohol, Übergewicht, familiäre Belastung.

## OPQRST für Schmerzen

- **O – Onset:** Wann und wie hat der Schmerz begonnen? Plötzlich oder schleichend?
- **P – Provocation/Palliation:** Was verschlimmert oder lindert ihn?
- **Q – Quality:** Wie fühlt er sich an? Stechend, drückend, brennend, kolikartig?
- **R – Radiation:** Strahlt er aus? Arm, Kiefer, Rücken?
- **S – Severity:** Stärke auf der **Numerischen Rating-Skala** von 0 bis 10.
- **T – Time:** Wie lange schon? Dauerhaft oder wellenförmig?

## Praxistipps

1. Offene Fragen stellen: *„Erzählen Sie mal, was passiert ist.“*
2. Bei Bewusstlosen Angehörige, Zeugen und Medikamentenpläne nutzen.
3. Im System die **Patientenakte** prüfen: Vorerkrankungen und Allergien sind dort oft hinterlegt.
4. Drogen- oder Alkoholkonsum wertfrei erfragen – die Information bleibt unter Schweigepflicht.

*Merke:* Drückender Brustschmerz mit Ausstrahlung in den linken Arm und Kiefer ist bis zum Beweis des Gegenteils ein **akutes Koronarsyndrom**.
MD],
            ['Bodycheck', <<<'MD'
# Bodycheck

Der Bodycheck ist die systematische Ganzkörperuntersuchung von **Kopf bis Fuß**. Er ist bei Traumapatienten Pflicht und erfolgt im Rahmen von „E“ oder nach Stabilisierung der Vitalfunktionen.

## Ablauf

1. **Kopf:** Kopfhaut abtasten, Blutungen, Hämatome, Brillenhämatom, Blut oder Liquor aus Nase und Ohren.
2. **Gesicht:** Stabilität des Mittelgesichts und Unterkiefers, Zahnverletzungen.
3. **Hals:** Halsvenenstauung, Trachealverschiebung, Druckschmerz der Halswirbelsäule.
4. **Thorax:** Inspektion, Palpation (Stabilität, Krepitationen), Auskultation beidseits.
5. **Abdomen:** Prellmarken, Abwehrspannung, Druckschmerz in allen vier Quadranten.
6. **Becken:** *Nur einmal* vorsichtig auf Stabilität prüfen – bei Instabilität sofort Beckenschlinge.
7. **Extremitäten:** Fehlstellungen, Durchblutung, Motorik, Sensibilität (**DMS**) an allen vier Gliedmaßen.
8. **Rücken:** Beim Umlagern mit Achsengerechtem Drehen (Log Roll) oder Schaufeltrage begutachten.

## Hinweise

- Kleidung bei Verdacht großzügig aufschneiden.
- Wärmeerhalt: Nach der Untersuchung sofort zudecken, Rettungsdecke nutzen.
- Prellmarken vom Gurt oder Lenkrad deuten auf innere Verletzungen hin.
- Bei Schuss- und Stichverletzungen immer nach **Austrittswunden** suchen, besonders am Rücken und in Achseln.

## Dokumentation

Alle Befunde werden im Einsatzprotokoll vermerkt. Verletzungen, die später für Polizei oder Gericht relevant sein könnten, werden zusätzlich in der **Verletzungsdokumentation** genau beschrieben (Lage, Größe, Form, Farbe).

*Merke:* Was man nicht sucht, findet man nicht. Den Rücken nie vergessen!
MD],
            ['Übergabe nach SINNHAFT', <<<'MD'
# Übergabe nach SINNHAFT

Bei der Übergabe des Patienten in der Notaufnahme gehen besonders viele Informationen verloren. Das Schema **SINNHAFT** strukturiert die Übergabe und sorgt dafür, dass nichts vergessen wird.

## Das Schema

- **S – Start:** Ruhe herstellen, „Hands off“ – das Klinikteam hört zu, bevor umgelagert wird (Ausnahme: Reanimation).
- **I – Identifikation:** Name, Alter, Geschlecht des Patienten.
- **N – Notfallereignis:** Was ist passiert? Unfallmechanismus, Auffindesituation.
- **N – Notfallpriorität:** Welche Probleme nach xABCDE liegen vor? Wie stabil ist der Patient?
- **H – Handlung:** Welche Maßnahmen wurden durchgeführt? Zugänge, Medikamente mit Dosis und Uhrzeit, Immobilisation.
- **A – Anamnese:** Relevante SAMPLER-Informationen, Allergien, Vorerkrankungen.
- **F – Fazit:** Verdachtsdiagnose und aktueller Zustand, Trend.
- **T – Teamfragen:** Gibt es Rückfragen? Erst dann wird umgelagert.

## Beispiel

*„Das ist Herr Miller, 45 Jahre. Motorradunfall mit etwa 60 km/h. Kritische Blutung am rechten Oberschenkel, Tourniquet seit 14:32. Atemweg frei, SpO2 95 Prozent unter 10 Liter Sauerstoff. Puls 118, RR 95/60. GCS 14. Zwei Zugänge, 500 ml Vollelektrolyt, 0,1 mg Fentanyl um 14:40. Keine Allergien bekannt. Verdacht auf Oberschenkelfraktur mit Blutverlust. Fragen?“*

## Tipps

1. Übergabe maximal **60 bis 90 Sekunden**.
2. Ein Sprecher, alle anderen hören zu.
3. Einsatzprotokoll ausgedruckt oder digital im System übergeben.
4. Wertsachen und Medikamente des Patienten dokumentiert übergeben.

*Merke:* Erst reden, dann heben.
MD],
        ],
        'questions' => [
            ['Wofür steht das „x“ im xABCDE-Schema?', ['Extremitäten', 'Exsanguination – kritische Blutung', 'Exposure', 'Extrication'], [1], 'Das x steht für kritische äußere Blutungen, die vor dem Atemweg behandelt werden.'],
            ['Was ist der Normbereich der Atemfrequenz beim Erwachsenen?', ['6–10 pro Minute', '12–20 pro Minute', '20–30 pro Minute', '30–40 pro Minute'], [1], 'Beim Erwachsenen liegt die normale Atemfrequenz bei 12 bis 20 pro Minute.'],
            ['Ab welchem GCS-Wert gilt der Atemweg als gefährdet?', ['15', '13', '10', '8 oder weniger'], [3], 'Ab einem GCS von 8 oder weniger sind die Schutzreflexe meist nicht mehr ausreichend.'],
            ['Ein Patient hat eine Herzfrequenz von 130 und einen systolischen Blutdruck von 100. Wie hoch ist der Schockindex?', ['0,77', '1,0', '1,3', '2,3'], [2], '130 geteilt durch 100 ergibt 1,3 – ein Hinweis auf einen Schock.'],
            ['Wofür steht das „L“ in SAMPLER?', ['Lagerung', 'Letzte Mahlzeit', 'Labor', 'Lungenbefund'], [1], 'L steht für letzte Mahlzeit, bei Frauen auch letzte Periode.'],
            ['Welche Fragen gehören zum OPQRST-Schema? (Mehrere Antworten möglich)', ['Strahlt der Schmerz aus?', 'Wie stark ist der Schmerz von 0 bis 10?', 'Wann hat der Schmerz begonnen?', 'Wie ist Ihre Versicherungsnummer?'], [0, 1, 2], 'Radiation, Severity und Onset sind Bestandteile von OPQRST.'],
            ['Wie oft sollte das Becken auf Stabilität geprüft werden?', ['Gar nicht', 'Nur einmal und vorsichtig', 'Bei jeder Umlagerung', 'Alle 5 Minuten'], [1], 'Mehrfaches Prüfen kann Blutgerinnsel lösen und die Blutung verstärken.'],
            ['Was bedeutet „P“ im AVPU-Schema?', ['Patient wach', 'Reagiert nur auf Schmerzreiz', 'Pupillen auffällig', 'Puls tastbar'], [1], 'P steht für Pain – der Patient reagiert nur auf Schmerzreize.'],
            ['Was passiert beim „S“ (Start) der SINNHAFT-Übergabe?', ['Patient wird sofort umgelagert', 'Ruhe herstellen, Hands off, das Team hört zu', 'Der Notarzt unterschreibt', 'Die Rechnung wird erstellt'], [1], 'Zuerst wird zugehört, dann umgelagert – außer bei Reanimation.'],
            ['Was ist bei einem Problem im Schritt „B“ zu tun?', ['Notieren und mit C weitermachen', 'Das Problem sofort behandeln, bevor man weitergeht', 'Den Transport abwarten', 'Nur die Klinik informieren'], [1], 'Gefundene Probleme werden sofort behandelt, erst dann geht es weiter.'],
        ],
    ],
    [
        'title' => 'Advanced Life Support (ALS)',
        'description' => 'Erweiterte Reanimationsmaßnahmen nach den Leitlinien des European Resuscitation Council. Behandelt Algorithmus, Defibrillation, Medikamente, reversible Ursachen und Postreanimationsbehandlung.',
        'hours' => 6, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['ALS-Algorithmus', <<<'MD'
# ALS-Algorithmus

Der Algorithmus des **European Resuscitation Council (ERC)** gibt einen klaren Ablauf für die erweiterte Reanimation Erwachsener vor.

## Erkennen des Kreislaufstillstands

- Keine Reaktion auf Ansprache und Schmerz
- Keine normale Atmung (Schnappatmung zählt als **keine** Atmung)
- Prüfung maximal **10 Sekunden**

## Ablauf

1. Hilfe rufen, Notarzt nachfordern, Defibrillator holen.
2. Sofort **Thoraxkompressionen** beginnen: Druckpunkt Mitte der Brust, Tiefe **5–6 cm**, Frequenz **100–120 pro Minute**, vollständige Entlastung.
3. Verhältnis **30:2** bis zur Atemwegssicherung, danach kontinuierliche Kompressionen mit 10 Beatmungen pro Minute.
4. Defibrillator anschließen, Rhythmus analysieren.
5. **Schockbarer Rhythmus** (VF, pulslose VT): Schock, sofort 2 Minuten CPR.
6. **Nicht schockbarer Rhythmus** (Asystolie, PEA): sofort 2 Minuten CPR, Adrenalin so früh wie möglich.
7. Alle **2 Minuten** Rhythmuskontrolle und Wechsel des Drückers.

## Während der CPR

- Hochwertige Kompressionen mit minimalen Unterbrechungen (unter 5 Sekunden für Schockabgabe)
- Sauerstoff 100 Prozent
- Kapnografie zur Qualitätskontrolle und Erkennung eines ROSC
- i.v. oder i.o. Zugang
- Atemwegssicherung (Larynxtubus oder Intubation)
- Reversible Ursachen suchen und behandeln

## Rollen im Team

Ein **Teamleiter** koordiniert, hält die Zeit im Blick und spricht laut an. Weitere Rollen: Drücker, Atemweg, Zugang und Medikamente, Dokumentation.

*Merke:* Die Qualität der Thoraxkompressionen entscheidet über das Überleben. „Push hard, push fast.“
MD],
            ['Rhythmusanalyse und Defibrillation', <<<'MD'
# Rhythmusanalyse und Defibrillation

Bei der Reanimation unterscheidet man zwischen **schockbaren** und **nicht schockbaren** Rhythmen.

## Schockbare Rhythmen

- **Kammerflimmern (VF):** Chaotische, unregelmäßige Wellen ohne erkennbare QRS-Komplexe.
- **Pulslose ventrikuläre Tachykardie (pVT):** Breite, regelmäßige Komplexe mit hoher Frequenz, aber kein tastbarer Puls.

## Nicht schockbare Rhythmen

- **Asystolie:** Nulllinie. Vor der Diagnose Elektroden, Ableitung und Verstärkung prüfen.
- **Pulslose elektrische Aktivität (PEA):** Organisierte elektrische Aktivität im EKG, aber kein Puls.

## Durchführung der Defibrillation

1. Klebeelektroden: eine **unterhalb des rechten Schlüsselbeins**, eine **seitlich links** in Höhe V6 (anterolateral).
2. Während der Ladung des Geräts weiterdrücken.
3. Sauerstoffquelle mindestens 1 Meter entfernen.
4. Laut warnen: *„Achtung, Schock! Alle weg!“* und Sichtkontrolle.
5. Schock auslösen, **sofort** mit Kompressionen weitermachen – keine Pulskontrolle direkt nach dem Schock.

## Energie

- Biphasisch: erster Schock **150–200 Joule**, danach gleich oder steigernd bis 360 Joule je nach Gerät.
- Bei Kindern: **4 Joule pro kg** Körpergewicht.

## Besonderheiten

- Beobachteter Kollaps am Monitor mit VF: bis zu **drei Schocks** direkt hintereinander möglich.
- Nasse Brust abtrocknen, starke Behaarung ggf. rasieren.
- Bei Herzschrittmacher Elektroden mindestens 8 cm entfernt kleben.

*Merke:* Jede Pause vor dem Schock verringert den Erfolg. Kompressionspausen unter 5 Sekunden halten.
MD],
            ['Medikamente bei Reanimation', <<<'MD'
# Medikamente bei Reanimation

Medikamente sind in der Reanimation nachrangig gegenüber hochwertiger CPR und früher Defibrillation, können aber die Chancen auf einen Spontankreislauf erhöhen.

## Zugangsweg

- Erste Wahl: **intravenös (i.v.)**
- Wenn nicht schnell möglich: **intraossär (i.o.)**, z. B. am proximalen Schienbein oder Oberarmkopf
- Endotracheale Gabe wird **nicht mehr** empfohlen

## Adrenalin (Epinephrin)

- Dosis: **1 mg** i.v./i.o. beim Erwachsenen
- **Nicht schockbar:** so früh wie möglich
- **Schockbar:** nach dem **dritten** Schock
- Danach alle **3–5 Minuten** (praktisch jeder zweite Zyklus)
- Wirkung: Gefäßverengung, erhöhter Perfusionsdruck in Herz und Gehirn

## Amiodaron

- Bei therapierefraktärem VF/pVT
- **300 mg** nach dem **dritten** Schock
- **150 mg** nach dem **fünften** Schock
- Alternative bei Nichtverfügbarkeit: Lidocain 1 mg/kg

## Weitere Medikamente

- **Natriumbicarbonat:** nur bei Hyperkaliämie oder Vergiftung mit trizyklischen Antidepressiva.
- **Calcium:** bei Hyperkaliämie, Hypokalzämie oder Überdosis Calciumantagonisten.
- **Thrombolyse:** bei vermuteter Lungenembolie, danach CPR für 60–90 Minuten fortsetzen.
- **Infusion:** Vollelektrolytlösung zum Offenhalten, bei Volumenmangel als Bolus.

## Dokumentation

Jede Medikamentengabe wird mit **Uhrzeit, Dosis und Applikationsweg** laut angesagt und im Einsatzprotokoll festgehalten.

*Merkhilfe:* „3-3-5“ – nach dem 3. Schock Adrenalin und 300 mg Amiodaron, nach dem 5. Schock 150 mg Amiodaron.
MD],
            ['Reversible Ursachen: 4H und HITS', <<<'MD'
# Reversible Ursachen: 4H und HITS

Ein Kreislaufstillstand hat oft eine behandelbare Ursache. Solange sie nicht behoben ist, bleibt die Reanimation häufig erfolglos. Die Merkhilfe **4H und HITS** hilft bei der systematischen Suche.

## Die 4 H

1. **Hypoxie:** Atemweg sichern, 100 Prozent Sauerstoff, korrekte Tubuslage prüfen.
2. **Hypovolämie:** Blutungen stoppen, Volumen geben, Beckenschlinge, schneller Transport zur OP.
3. **Hypo-/Hyperkaliämie und metabolische Störungen:** Anamnese mit Dialyse, Blutgasanalyse, Calcium und Bicarbonat bei Hyperkaliämie, Glukose bei Hypoglykämie.
4. **Hypothermie und Hyperthermie:** Temperatur messen. Bei Hypothermie unter 30 °C maximal drei Schocks, Adrenalin zurückhalten, Transport in eine Klinik mit extrakorporaler Erwärmung. „Niemand ist tot, bis er warm und tot ist.“

## HITS

1. **Herzbeuteltamponade:** Nach Stichverletzung oder Herz-OP, Sonografie, Perikardpunktion.
2. **Intoxikation:** Opioide (Naloxon), Antidepressiva, Betablocker – Giftnotruf, Antidote.
3. **Thromboembolie:** Lungenembolie oder Herzinfarkt, Thrombolyse erwägen.
4. **Spannungspneumothorax:** Fehlendes Atemgeräusch, gestaute Halsvenen, Trauma – Nadeldekompression im 2. ICR medioklavikular oder 4./5. ICR vordere Axillarlinie.

## Praktisches Vorgehen

- Während jedes 2-Minuten-Zyklus geht der Teamleiter die Liste laut durch.
- **Sonografie** am Einsatzort hilft bei Tamponade, Pneumothorax und Hypovolämie, darf aber die Kompressionen nicht unterbrechen.
- Bei traumatischem Kreislaufstillstand haben Blutungskontrolle, Beatmung und Entlastung des Thorax Vorrang vor Thoraxkompressionen.

*Merke:* Reanimation ohne Ursachensuche ist nur halbe Arbeit.
MD],
            ['Postreanimationsbehandlung', <<<'MD'
# Postreanimationsbehandlung

Nach Wiederkehr des Spontankreislaufs (**ROSC – Return of Spontaneous Circulation**) beginnt eine kritische Phase. Viele Patienten versterben in den ersten Stunden am Postreanimationssyndrom.

## Zeichen eines ROSC

- Plötzlicher Anstieg des **etCO2** in der Kapnografie (oft über 40 mmHg)
- Tastbarer Puls bei organisiertem Rhythmus
- Atembemühungen, Bewegungen, Augenöffnen

## Ziele nach ABCDE

- **A/B:** Atemweg sichern, Beatmung mit Ziel-SpO2 **94–98 Prozent**, etCO2 **35–45 mmHg**. Hyperoxie und Hyperventilation vermeiden.
- **C:** 12-Kanal-EKG schreiben. Systolischer Blutdruck über **100 mmHg**, mittlerer arterieller Druck über **65 mmHg**. Bei Bedarf Volumen und Katecholamine (z. B. Noradrenalin).
- **D:** Blutzucker kontrollieren, Krampfanfälle behandeln, Sedierung bei beatmeten Patienten.
- **E:** Temperatur messen, **Fieber vermeiden** (Ziel unter 37,7 °C für mindestens 72 Stunden).

## Transportziel

- Bei **ST-Hebungen** im EKG oder hochgradigem Verdacht auf Herzinfarkt: Klinik mit **Herzkatheterlabor**, im SAMS-Bereich das Pillbox Hill Medical Center.
- Voranmeldung per Funk: Alter, Dauer des Stillstands, Erstrhythmus, Anzahl der Schocks, aktuelle Werte.

## Transport

1. Monitoring vollständig, Defibrillator angeschlossen lassen.
2. Erneuter Stillstand jederzeit möglich – Personal und Medikamente griffbereit.
3. Notarztbegleitung obligat.

## Dokumentation

Im Einsatzprotokoll: Beginn des Stillstands, Laienreanimation ja/nein, Erstrhythmus, Zeitpunkte von Schocks und Medikamenten, Zeitpunkt des ROSC.

*Merke:* ROSC ist nicht das Ende, sondern der Beginn der Intensivtherapie.
MD],
        ],
        'questions' => [
            ['Mit welcher Frequenz werden Thoraxkompressionen beim Erwachsenen durchgeführt?', ['60–80 pro Minute', '80–100 pro Minute', '100–120 pro Minute', '120–140 pro Minute'], [2], 'Die ERC-Leitlinien empfehlen 100 bis 120 Kompressionen pro Minute.'],
            ['Wie tief soll beim Erwachsenen komprimiert werden?', ['2–3 cm', '3–4 cm', '5–6 cm', '8–10 cm'], [2], 'Die empfohlene Kompressionstiefe beträgt 5 bis 6 cm.'],
            ['Welche Rhythmen sind schockbar? (Mehrere Antworten möglich)', ['Kammerflimmern', 'Pulslose ventrikuläre Tachykardie', 'Asystolie', 'Pulslose elektrische Aktivität'], [0, 1], 'Nur VF und pulslose VT werden defibrilliert.'],
            ['Wann wird Adrenalin bei einem schockbaren Rhythmus erstmals gegeben?', ['Sofort vor dem ersten Schock', 'Nach dem ersten Schock', 'Nach dem dritten Schock', 'Gar nicht'], [2], 'Bei schockbaren Rhythmen wird 1 mg Adrenalin nach dem dritten Schock gegeben.'],
            ['Welche Dosis Amiodaron wird nach dem dritten Schock gegeben?', ['150 mg', '300 mg', '450 mg', '1 mg'], [1], 'Initial werden 300 mg gegeben, nach dem fünften Schock weitere 150 mg.'],
            ['Was ist direkt nach Abgabe eines Schocks zu tun?', ['Puls tasten', 'Rhythmus erneut analysieren', 'Sofort mit Thoraxkompressionen weitermachen', 'Zwei Beatmungen geben'], [2], 'Nach dem Schock wird ohne Pulskontrolle sofort für 2 Minuten weiter reanimiert.'],
            ['Welche Ursachen gehören zu den HITS? (Mehrere Antworten möglich)', ['Herzbeuteltamponade', 'Intoxikation', 'Hypothermie', 'Spannungspneumothorax'], [0, 1, 3], 'Hypothermie gehört zu den 4H, nicht zu HITS.'],
            ['Welches Zeichen weist während der CPR am ehesten auf einen ROSC hin?', ['Abfall des etCO2', 'Plötzlicher deutlicher Anstieg des etCO2', 'Erweiterte Pupillen', 'Zunehmende Zyanose'], [1], 'Ein plötzlicher Anstieg des etCO2 zeigt einen wiederkehrenden Kreislauf an.'],
            ['Welcher Zugangsweg ist zweite Wahl, wenn kein i.v.-Zugang möglich ist?', ['Endotracheal', 'Intramuskulär', 'Intraossär', 'Rektal'], [2], 'Der intraossäre Zugang ist die Alternative zum i.v.-Zugang.'],
            ['Welche Ziel-Sauerstoffsättigung gilt nach ROSC?', ['80–85 Prozent', '88–92 Prozent', '94–98 Prozent', 'Immer 100 Prozent'], [2], 'Nach ROSC soll Hyperoxie vermieden werden, Ziel ist 94 bis 98 Prozent.'],
        ],
    ],
    [
        'title' => 'Traumaversorgung',
        'description' => 'Präklinische Versorgung schwerverletzter Patienten nach PHTLS- und ATLS-Prinzipien. Schwerpunkte sind Blutungskontrolle, Immobilisation, Thorax- und Abdominaltrauma sowie Schockbehandlung.',
        'hours' => 6, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Unfallmechanismus und Triage-Kriterien', <<<'MD'
# Unfallmechanismus und Triage-Kriterien

Der **Unfallmechanismus** (Kinematik) liefert bereits vor der Untersuchung wichtige Hinweise auf mögliche Verletzungen. Hohe Energie bedeutet hohes Risiko für innere Verletzungen.

## Typische Hochrasanzmechanismen

- Verkehrsunfall mit über **50 km/h** oder Fahrzeugdeformierung über 50 cm
- Herausschleudern aus dem Fahrzeug, Tod eines Mitfahrers
- Motorrad- oder Fahrradunfall, Fußgänger angefahren
- Sturz aus über **3 Metern** Höhe
- Explosionen, Verschüttung
- Penetrierende Verletzungen an Kopf, Hals, Rumpf (Schuss, Stich)

## Schockraumkriterien

Ein Patient wird im Pillbox Hill Medical Center im **Schockraum** angemeldet, wenn eines der folgenden Kriterien vorliegt:

1. **Vitalparameter:** systolischer RR unter 90 mmHg, Atemfrequenz unter 10 oder über 29, SpO2 unter 90 Prozent, GCS unter 9
2. **Verletzungsmuster:** instabiler Thorax, instabile Beckenfraktur, Amputation proximal von Hand oder Fuß, offene Schädelverletzung, Querschnitt, Verbrennung über 20 Prozent Körperoberfläche
3. **Mechanismus:** siehe oben

## Load and Go oder Stay and Play

- **Load and Go:** Kritische Patienten mit inneren Blutungen werden nach Basismaßnahmen sofort transportiert. Ziel: Einsatzzeit unter **10 Minuten**.
- **Stay and Play:** Bei stabilen Patienten ist eine umfassendere Versorgung vor Ort möglich.

## Besondere Lagen

Bei Schussverletzungen oder Gewalttaten wird immer zuerst die Sicherheit durch LSPD oder BCSO hergestellt. Spuren werden nach Möglichkeit geschont (Kleidung nicht durch Einschusslöcher schneiden), das Patientenwohl hat aber immer Vorrang.

*Merke:* Der Mechanismus lügt nicht – auch ein „unverletzt“ wirkender Patient nach Hochrasanztrauma gehört in den Schockraum.
MD],
            ['Blutungskontrolle und Tourniquet', <<<'MD'
# Blutungskontrolle und Tourniquet

Unkontrollierte Blutungen sind die häufigste **vermeidbare** Todesursache beim Trauma. Deshalb steht im xABCDE-Schema die Blutungskontrolle an erster Stelle.

## Stufenschema bei Extremitätenblutungen

1. **Direkter Druck** mit Kompresse oder behandschuhter Hand
2. **Druckverband**, ggf. mit Israeli-Bandage
3. **Wound Packing:** Tiefe Wunden mit (hämostyptischer) Gaze austamponieren und mindestens **3 Minuten** fest abdrücken
4. **Tourniquet** bei lebensbedrohlicher Blutung oder wenn andere Maßnahmen versagen

Bei lebensbedrohlicher Blutung, mehreren Verletzten oder unsicherer Lage wird **sofort** ein Tourniquet angelegt.

## Anlage des Tourniquets

- Möglichst **5–7 cm oberhalb** der Wunde, nicht über Gelenken; in der Akutsituation „high and tight“ (so weit körpernah wie möglich)
- Festziehen, bis die **Blutung steht** und kein peripherer Puls mehr tastbar ist
- **Uhrzeit** notieren, auf dem Tourniquet und im Protokoll
- Reicht ein Tourniquet nicht, wird ein zweites direkt oberhalb angelegt
- Ein Tourniquet ist **schmerzhaft** – Analgesie einplanen
- Kein Lockern im Rettungsdienst

## Junktionale Blutungen

Blutungen in Leiste, Achsel oder Hals lassen sich nicht mit einem Tourniquet stoppen. Hier helfen Wound Packing, starker Druck und spezielle Junktional-Tourniquets.

## Medikamentöse Unterstützung

- **Tranexamsäure:** 1 g i.v. über 10 Minuten innerhalb von **3 Stunden** nach Trauma, idealerweise so früh wie möglich.
- Wärmeerhalt, denn Kälte verschlechtert die Gerinnung.

## Tödliche Trias

**Hypothermie, Azidose und Koagulopathie** verstärken sich gegenseitig. Jede Komponente muss aktiv verhindert werden.

*Merke:* Ein Tourniquet rettet Leben – im Zweifel lieber früh anlegen.
MD],
            ['Wirbelsäule und Immobilisation', <<<'MD'
# Wirbelsäule und Immobilisation

Eine Wirbelsäulenverletzung kann zu bleibenden Lähmungen führen. Gleichzeitig verursacht eine unnötige Immobilisation Schmerzen, Druckstellen und erschwert die Atmung. Daher gilt: **gezielt** immobilisieren.

## Hinweise auf eine Wirbelsäulenverletzung

- Entsprechender Unfallmechanismus (Sturz, Verkehrsunfall, Sprung ins flache Wasser)
- Schmerzen oder Druckschmerz an der Wirbelsäule
- Neurologische Ausfälle: Kribbeln, Taubheit, Lähmung
- Bewusstseinsstörung, Intoxikation oder ablenkende Verletzungen, die eine Beurteilung unmöglich machen

## Klinische Entscheidungshilfe

Eine Immobilisation der HWS ist bei wachem, kooperativem Patienten **nicht erforderlich**, wenn alle Kriterien erfüllt sind:
1. Wach und orientiert (GCS 15), nicht intoxikiert
2. Keine Schmerzen oder Druckschmerzen an der Wirbelsäule
3. Keine neurologischen Ausfälle
4. Keine ablenkende Verletzung
5. Aktive Rotation des Kopfes um 45 Grad zu beiden Seiten schmerzfrei

## Hilfsmittel

- **Manuelle Inline-Stabilisierung** des Kopfes als erste Maßnahme
- **HWS-Orthese** (Stifneck) – nur in Kombination mit weiterer Immobilisation sinnvoll
- **Schaufeltrage** zum schonenden Aufnehmen
- **Vakuummatratze** für den Transport – Goldstandard
- **Spineboard** nur zur Rettung, nicht für längeren Transport
- **KED-System** zur Rettung aus Fahrzeugen bei stabilen Patienten

## Crash-Rettung

Bei akuter Lebensgefahr (Feuer, Reanimation, kritischer Schock) wird der Patient ohne aufwendige Immobilisation sofort aus dem Fahrzeug gerettet. Das Leben geht vor der Wirbelsäule.

*Merke:* Eine HWS-Orthese allein ist keine Immobilisation. Vakuummatratze ist das Mittel der Wahl für den Transport.
MD],
            ['Thorax- und Abdominaltrauma', <<<'MD'
# Thorax- und Abdominaltrauma

Verletzungen von Brustkorb und Bauch sind oft lebensbedrohlich und von außen schwer zu erkennen.

## Thoraxtrauma

### Spannungspneumothorax
- Luft gelangt in den Pleuraspalt und kann nicht entweichen, Druck steigt, Herz und Gefäße werden komprimiert.
- Zeichen: zunehmende **Atemnot**, einseitig fehlendes Atemgeräusch, gestaute Halsvenen, Schock, ggf. Trachealverschiebung (Spätzeichen).
- Therapie: **Nadeldekompression** im 2. ICR medioklavikular oder 4./5. ICR vordere bis mittlere Axillarlinie, anschließend Thoraxdrainage durch den Notarzt.

### Offener Pneumothorax
- Saugende Wunde am Thorax, z. B. nach Stich oder Schuss.
- Therapie: **Chest Seal** (Ventilpflaster), auf Entwicklung eines Spannungspneumothorax achten.

### Instabiler Thorax und Hämatothorax
- Paradoxe Atmung bei Rippenserienfrakturen, Schmerztherapie, Sauerstoff, ggf. Beatmung.
- Hämatothorax: gedämpfter Klopfschall, Schockzeichen, schneller Transport.

## Abdominaltrauma

- Hinweise: Prellmarken, Gurtmarke, Abwehrspannung, zunehmender Bauchumfang, Schock ohne sichtbare Blutung
- Milz- und Leberrupturen bluten massiv nach innen
- Präklinisch nur begrenzte Therapie: **Sauerstoff, Zugänge, permissive Hypotonie, schneller Transport** in die chirurgische Klinik

## Pfählungsverletzungen

Fremdkörper werden **nie** am Einsatzort entfernt. Sie werden gepolstert und fixiert, der Patient wird schnell transportiert.

## Ausgetretene Darmschlingen

Nicht zurückdrücken, mit feuchten, sterilen Tüchern abdecken, Knie leicht anwinkeln.

*Merke:* Thorax und Abdomen sind Blutungsräume. Schock ohne sichtbare Blutung bedeutet innere Blutung, bis das Gegenteil bewiesen ist.
MD],
            ['Schock und Schockraum', <<<'MD'
# Schock und Schockraum

Schock ist ein **Missverhältnis zwischen Sauerstoffangebot und Sauerstoffbedarf** der Gewebe. Beim Trauma ist der hämorrhagische Schock am häufigsten.

## Schockformen

- **Hypovolämisch/hämorrhagisch:** Blut- oder Flüssigkeitsverlust
- **Obstruktiv:** Spannungspneumothorax, Herzbeuteltamponade, Lungenembolie
- **Distributiv:** Anaphylaxie, Sepsis, neurogener Schock bei Querschnitt
- **Kardiogen:** Pumpversagen, z. B. bei Herzinfarkt

## Zeichen

- Blasse, kalte, schweißige Haut
- Tachykardie, schwacher Puls
- Rekapillarisierungszeit über 2 Sekunden
- Unruhe, Verwirrtheit, später Bewusstseinstrübung
- Blutdruckabfall ist ein **Spätzeichen** (oft erst ab 30 Prozent Blutverlust)

## Therapie beim hämorrhagischen Schock

1. Blutungskontrolle
2. Zwei großlumige Zugänge (mindestens 18 G)
3. **Permissive Hypotonie:** systolischer Zielblutdruck **80–90 mmHg** bzw. tastbarer Radialispuls. Ausnahme Schädel-Hirn-Trauma: mindestens 110 mmHg.
4. Vollelektrolytlösung in kleinen Boli, Volumen nicht übertreiben
5. Tranexamsäure 1 g
6. Wärmeerhalt
7. Schneller Transport

## Schockraumanmeldung

Die Voranmeldung im Pillbox Hill Medical Center erfolgt über die Leitstelle oder direkt:
- Alter und Geschlecht
- Mechanismus
- Verletzungsmuster und Vitalparameter
- Maßnahmen
- Voraussichtliche Ankunftszeit

Im Schockraum übernimmt ein interdisziplinäres Team aus Notfallmedizin, Chirurgie und Anästhesie nach ATLS. Die Übergabe erfolgt strukturiert nach SINNHAFT.

*Merke:* Warte nicht auf den Blutdruckabfall. Tachykardie und kalte Haut sind die frühen Warnzeichen.
MD],
        ],
        'questions' => [
            ['Ab welcher Sturzhöhe gilt ein Sturz beim Erwachsenen als Hochrasanztrauma?', ['Über 1 Meter', 'Über 3 Meter', 'Über 10 Meter', 'Über 20 Meter'], [1], 'Ein Sturz aus über 3 Metern Höhe ist ein Schockraumkriterium.'],
            ['Was ist bei einer lebensbedrohlichen Blutung am Oberschenkel die Maßnahme der Wahl?', ['Hochlagern', 'Tourniquet', 'Kühlen', 'Pflaster'], [1], 'Bei lebensbedrohlicher Extremitätenblutung wird sofort ein Tourniquet angelegt.'],
            ['Was muss nach Anlage eines Tourniquets dokumentiert werden?', ['Die Blutgruppe', 'Die Uhrzeit der Anlage', 'Die Farbe des Tourniquets', 'Die Kontaktdaten des Herstellers'], [1], 'Die Anlagezeit ist für die Klinik entscheidend und wird notiert.'],
            ['Innerhalb welcher Zeit nach dem Trauma soll Tranexamsäure gegeben werden?', ['30 Minuten', '3 Stunden', '12 Stunden', '24 Stunden'], [1], 'Tranexamsäure wirkt nur innerhalb von 3 Stunden nach Trauma.'],
            ['Was gehört zur tödlichen Trias beim Trauma? (Mehrere Antworten möglich)', ['Hypothermie', 'Azidose', 'Koagulopathie', 'Hyperglykämie'], [0, 1, 2], 'Hypothermie, Azidose und Koagulopathie verstärken sich gegenseitig.'],
            ['Welches Hilfsmittel ist für den Transport bei Wirbelsäulenverdacht der Goldstandard?', ['Spineboard', 'Vakuummatratze', 'Tragestuhl', 'Nur HWS-Orthese'], [1], 'Die Vakuummatratze ist das Mittel der Wahl für den Transport.'],
            ['Welche Zeichen sprechen für einen Spannungspneumothorax? (Mehrere Antworten möglich)', ['Einseitig fehlendes Atemgeräusch', 'Gestaute Halsvenen', 'Zunehmende Atemnot und Schock', 'Bradykardie und Bluthochdruck'], [0, 1, 2], 'Fehlendes Atemgeräusch, Halsvenenstauung und Schock sind typische Zeichen.'],
            ['Wie wird mit einem Messer umgegangen, das noch im Bauch eines Patienten steckt?', ['Sofort herausziehen', 'Drehen, um die Tiefe zu prüfen', 'Belassen, polstern und fixieren', 'Abschneiden auf Hautniveau'], [2], 'Pfählende Fremdkörper werden nie präklinisch entfernt.'],
            ['Welcher systolische Zielblutdruck gilt bei permissiver Hypotonie ohne Schädel-Hirn-Trauma?', ['60–70 mmHg', '80–90 mmHg', '110–120 mmHg', 'Über 140 mmHg'], [1], 'Ziel sind 80 bis 90 mmHg, um Blutungen nicht zu verstärken.'],
            ['Welches Schockzeichen tritt typischerweise erst spät auf?', ['Tachykardie', 'Blasse kalte Haut', 'Blutdruckabfall', 'Unruhe'], [2], 'Der Blutdruck fällt oft erst ab etwa 30 Prozent Blutverlust.'],
        ],
    ],
    [
        'title' => 'Notfallpharmakologie',
        'description' => 'Grundlagen der Medikamentengabe im Rettungsdienst. Behandelt Zugänge, Analgesie, Sedierung und Narkose, Kreislaufmedikamente sowie den rechtssicheren Umgang mit Betäubungsmitteln.',
        'hours' => 6, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Applikationswege und Zugänge', <<<'MD'
# Applikationswege und Zugänge

Der Applikationsweg bestimmt, wie schnell und zuverlässig ein Medikament wirkt.

## Applikationswege

- **Intravenös (i.v.):** Schnellster und am besten steuerbarer Weg, Standard im Rettungsdienst.
- **Intraossär (i.o.):** Gleichwertige Alternative, wenn kein i.v.-Zugang möglich ist, z. B. bei Reanimation oder Schock.
- **Intramuskulär (i.m.):** z. B. Adrenalin bei Anaphylaxie in den seitlichen Oberschenkel.
- **Intranasal (i.n.):** Mit Zerstäuber (MAD), z. B. Fentanyl, Midazolam oder Naloxon – ideal bei Kindern.
- **Inhalativ:** Salbutamol, Ipratropium, Sauerstoff.
- **Sublingual/bukkal:** Nitroglycerin-Spray, Midazolam bukkal.
- **Rektal:** Diazepam-Rektiole beim Fieberkrampf.

## Periphervenöser Zugang

Größe nach Farbe (Gauge):
- **Grün 18 G** – Standard im Notfall
- **Grau 16 G** und **Orange 14 G** – für große Volumenmengen
- **Rosa 20 G** und **Blau 22 G** – für Kinder oder schwierige Venen

## Ablauf der Venenpunktion

1. Material vorbereiten, Handschuhe anziehen
2. Stauen, Vene tasten
3. Hautdesinfektion, Einwirkzeit beachten
4. Punktion im flachen Winkel, Rückfluss abwarten
5. Kanüle vorschieben, Stauung lösen, Mandrin entfernen
6. Fixieren und mit Infusion spülen

## Intraossärer Zugang

Mit Bohrsystem am **proximalen medialen Schienbein** oder am **Oberarmkopf**. Beim wachen Patienten vorher Lidocain i.o. geben, da die Infusion schmerzhaft ist.

## Die 6-R-Regel

Vor jeder Gabe prüfen: **richtiger Patient, richtiges Medikament, richtige Dosis, richtige Applikation, richtiger Zeitpunkt, richtige Dokumentation**.

*Merke:* Nach zwei erfolglosen i.v.-Versuchen beim kritischen Patienten direkt i.o. gehen.
MD],
            ['Analgesie', <<<'MD'
# Analgesie

Schmerz ist eines der häufigsten Symptome im Rettungsdienst. Eine angemessene Schmerztherapie ist **Pflicht** und verbessert die Versorgung, die Kooperation und den Kreislauf des Patienten.

## Schmerzerfassung

- **Numerische Rating-Skala (NRS):** 0 = kein Schmerz, 10 = stärkster vorstellbarer Schmerz
- Behandlungsbedürftig ab NRS **4** oder auf Wunsch des Patienten
- Ziel: NRS unter 4 bzw. für den Patienten erträglich
- Kinder: Smiley-Skala, bei Kleinkindern Verhaltensbeobachtung

## Nicht-medikamentöse Maßnahmen

- Lagerung und Ruhigstellung, Schienen von Frakturen
- Kühlen bei Prellungen und Verbrennungen (nur kurzzeitig)
- Beruhigende Kommunikation

## Medikamente

### Nicht-Opioide
- **Paracetamol:** 1 g i.v. als Kurzinfusion, auch bei Fieber
- **Metamizol:** 1 g langsam i.v., gut bei Koliken, Blutdruckabfall möglich

### Opioide (Betäubungsmittel)
- **Fentanyl:** 0,05–0,1 mg i.v. bzw. 1–2 µg/kg, schneller Wirkeintritt
- **Morphin:** 2–5 mg i.v. titriert, z. B. bei Herzinfarkt
- **Piritramid:** 3–7,5 mg i.v.
- Nebenwirkungen: **Atemdepression**, Übelkeit, Blutdruckabfall
- Antidot: **Naloxon** 0,04–0,4 mg i.v. titriert

### Esketamin
- 0,125–0,25 mg/kg i.v. als Analgetikum
- Erhält Schutzreflexe und Kreislauf – ideal beim Trauma
- Mit **Midazolam** 1–2 mg kombinieren, um Albträume zu vermeiden

## Überwachung

Jeder Patient unter Opioiden erhält **Pulsoxymetrie**, Sauerstoff bereit, Beatmungsbeutel griffbereit. Dosis, Uhrzeit und NRS vor und nach Gabe werden dokumentiert.

*Merke:* Titrieren statt Bolus. Lieber mehrmals kleine Dosen als eine zu große.
MD],
            ['Sedierung und Narkose', <<<'MD'
# Sedierung und Narkose

Sedierung und Narkose sind ärztliche Maßnahmen mit hohem Risiko. Notfallsanitäter assistieren und müssen die Medikamente und Abläufe sicher kennen.

## Sedierung

Ziel ist eine Beruhigung bei erhaltenen Schutzreflexen, z. B. bei Angst, Agitation oder zur Kardioversion.
- **Midazolam:** 1–2 mg i.v. titriert, Wirkeintritt nach 2–3 Minuten. Antidot **Flumazenil**.
- Bei erregten, aggressiven Patienten: zuerst deeskalieren, Polizei zum Eigenschutz hinzuziehen. Medikamentöse Sedierung nur aus medizinischer Indikation, nie als Strafe oder auf Wunsch der Polizei.

## Narkose

Die Notfallnarkose ist eine **Rapid Sequence Induction (RSI)**: schnelle Einleitung ohne Zwischenbeatmung, um Aspiration zu vermeiden.

### Komponenten
1. **Hypnotikum:** schaltet das Bewusstsein aus
2. **Analgetikum:** dämpft Schmerzreize
3. **Muskelrelaxans:** ermöglicht Intubation

### Typische Medikamente
- **Esketamin:** 0,5–1 mg/kg, kreislaufstabil, bevorzugt beim Schock
- **Propofol:** 1–2 mg/kg, starke Blutdrucksenkung
- **Etomidat:** 0,2–0,3 mg/kg, kreislaufneutral
- **Fentanyl:** 1–3 µg/kg
- **Rocuronium:** 1–1,2 mg/kg, Antagonist Sugammadex
- **Succinylcholin:** 1–1,5 mg/kg, sehr schneller Wirkeintritt, Kontraindikation Hyperkaliämie

## Indikationen zur Narkose

- GCS 8 oder weniger
- Respiratorische Insuffizienz trotz Therapie
- Schweres Trauma mit Schock
- Unbeherrschbare Schmerzen bei schwerem Trauma

## Nach der Einleitung

Narkoseerhaltung mit Midazolam und Fentanyl oder Esketamin, Kapnografie, Blutdruckkontrolle alle 3 Minuten. Bei Blutdruckabfall Noradrenalin bereithalten.

*Merke:* Kein Patient wird ohne Notarzt, Monitoring, Absaugung und Plan B narkotisiert.
MD],
            ['Kreislaufmedikamente', <<<'MD'
# Kreislaufmedikamente

Kreislaufwirksame Medikamente werden im Rettungsdienst bei Schock, Reanimation, Rhythmusstörungen und Blutdruckentgleisungen eingesetzt.

## Katecholamine

- **Adrenalin (Epinephrin) bei Reanimation:** 1 mg i.v. alle 3–5 Minuten
- **Adrenalin bei Anaphylaxie:** **0,5 mg i.m.** beim Erwachsenen, ggf. nach 5 Minuten wiederholen
- **Adrenalin bei schwerer Bradykardie oder Schock:** als Perfusor
- **Noradrenalin:** Gefäßverengend, bei Hypotonie nach Narkose oder im septischen Schock, über Perfusor 0,05–0,3 µg/kg/min
- **Akrinor (Cafedrin/Theodrenalin):** Kurzfristige Blutdruckanhebung in Boli

## Antiarrhythmika

- **Amiodaron:** Kammerflimmern in der Reanimation (300 mg), Tachykardien unter Monitoring
- **Atropin:** 0,5 mg i.v. bei symptomatischer Bradykardie, bis maximal 3 mg
- **Adenosin:** 6 mg schnell i.v., dann 12 mg bei supraventrikulärer Tachykardie, mit schnellem Nachspülen

## Blutdrucksenkung

- **Urapidil:** 5–25 mg i.v. titriert bei hypertensivem Notfall
- **Nitroglycerin:** 0,4–0,8 mg sublingual bei Lungenödem oder Angina pectoris. Kontraindiziert bei RR unter 90 mmHg und nach Einnahme von Potenzmitteln innerhalb von 24–48 Stunden.

## Akutes Koronarsyndrom

- **Acetylsalicylsäure:** 150–300 mg i.v. oder oral
- **Heparin:** 5000 I.E. i.v.
- Sauerstoff nur bei SpO2 unter 90 Prozent
- Analgesie mit Morphin

## Anaphylaxie

1. Adrenalin i.m.
2. Sauerstoff, Volumen
3. Prednisolon 250 mg i.v. und Antihistaminikum (Dimetinden)
4. Bei Bronchospasmus Salbutamol inhaliert

*Merke:* Bei Anaphylaxie ist **Adrenalin i.m.** die wichtigste Maßnahme – nicht zögern.
MD],
            ['Betäubungsmittel und BtM-Buch', <<<'MD'
# Betäubungsmittel und BtM-Buch

Betäubungsmittel (BtM) wie **Fentanyl, Morphin und Piritramid** unterliegen strengen gesetzlichen Vorschriften. Jede Ampulle muss lückenlos nachweisbar sein.

## Grundsätze

- BtM werden in einem **verschlossenen Fach** im Fahrzeug bzw. im Tresor der Klinik gelagert.
- Der Schlüssel oder Code wird nur an berechtigtes Personal ausgegeben.
- Die Anordnung zur Gabe erfolgt durch einen **Notarzt** oder nach freigegebenem Behandlungspfad durch berechtigte Notfallsanitäter.

## Das BtM-Buch im System

Jeder Zu- und Abgang wird im digitalen **BtM-Buch** dokumentiert:
1. Datum und Uhrzeit
2. Bezeichnung und Menge des Medikaments
3. Name des Patienten oder Einsatznummer
4. Verabreichende und anordnende Person
5. Neuer Bestand
6. Bei **Verwurf** (angebrochene Ampulle) die verworfene Menge mit Zeugen

## Prüfung

- Zu jedem **Schichtwechsel** wird der Bestand gezählt und gegengezeichnet.
- Abweichungen werden sofort der **Leitung** gemeldet, nicht selbst „ausgeglichen“.
- Monatliche Prüfung durch die verantwortliche Ärztin oder den verantwortlichen Arzt.

## BtM-Rezept

Für die ambulante Verordnung von BtM (z. B. starke Schmerzmittel nach Entlassung) wird im System ein **BtM-Rezept** ausgestellt. Nur Ärztinnen und Ärzte dürfen dieses Dokument erstellen. Es enthält Patientendaten, genaues Präparat, Menge, Gebrauchsanweisung, Datum und Unterschrift.

## Missbrauch

Die Entnahme von BtM zu privaten Zwecken, die Weitergabe an Dritte oder das Fälschen von Einträgen ist eine **Straftat**. Bei Verdacht wird die Leitung informiert, die ggf. das DOJ einschaltet. Das betroffene Personal wird sofort vom Dienst freigestellt.

*Merke:* Was nicht im BtM-Buch steht, wurde nicht gegeben – oder wurde gestohlen.
MD],
        ],
        'questions' => [
            ['Welcher Applikationsweg ist bei Anaphylaxie für Adrenalin zuerst empfohlen?', ['Intravenös als Bolus', 'Intramuskulär in den Oberschenkel', 'Inhalativ', 'Rektal'], [1], 'Adrenalin 0,5 mg i.m. ist die wichtigste Maßnahme bei Anaphylaxie.'],
            ['Welche Farbe hat eine 18-G-Venenverweilkanüle?', ['Rosa', 'Blau', 'Grün', 'Orange'], [2], '18 G ist grün und Standard im Notfall.'],
            ['Was ist das Antidot bei einer Opioid-Überdosierung?', ['Flumazenil', 'Naloxon', 'Atropin', 'Sugammadex'], [1], 'Naloxon hebt die Wirkung von Opioiden auf.'],
            ['Welche Vorteile hat Esketamin beim Traumapatienten? (Mehrere Antworten möglich)', ['Erhält weitgehend die Schutzreflexe', 'Wirkt kreislaufstabilisierend', 'Starke Analgesie', 'Senkt stark den Blutdruck'], [0, 1, 2], 'Esketamin ist analgetisch, kreislaufstabil und erhält die Schutzreflexe weitgehend.'],
            ['Ab welchem NRS-Wert ist eine Schmerztherapie in der Regel angezeigt?', ['Ab 1', 'Ab 4', 'Ab 7', 'Erst ab 10'], [1], 'Ab NRS 4 oder auf Wunsch des Patienten wird therapiert.'],
            ['Wann ist Nitroglycerin kontraindiziert? (Mehrere Antworten möglich)', ['Systolischer Blutdruck unter 90 mmHg', 'Einnahme von Potenzmitteln in den letzten 24–48 Stunden', 'Lungenödem bei hohem Blutdruck', 'Angina pectoris mit normalem Blutdruck'], [0, 1], 'Hypotonie und Potenzmittel sind Kontraindikationen.'],
            ['Was ist bei einer Abweichung des BtM-Bestands zu tun?', ['Fehlende Menge aus der Klinik auffüllen', 'Sofort der Leitung melden', 'Beim nächsten Einsatz ausgleichen', 'Nichts, kleine Abweichungen sind normal'], [1], 'Abweichungen werden sofort gemeldet und nie selbst korrigiert.'],
            ['Wer darf im System ein BtM-Rezept ausstellen?', ['Jeder Mitarbeitende', 'Nur Ärztinnen und Ärzte', 'Nur die Leitstelle', 'Rettungssanitäter'], [1], 'BtM-Rezepte dürfen ausschließlich von Ärztinnen und Ärzten ausgestellt werden.'],
            ['Welches Muskelrelaxans hat Sugammadex als Antagonisten?', ['Succinylcholin', 'Rocuronium', 'Midazolam', 'Propofol'], [1], 'Sugammadex hebt die Wirkung von Rocuronium auf.'],
            ['Wie lautet die erste Dosis Adenosin bei supraventrikulärer Tachykardie beim Erwachsenen?', ['0,5 mg', '6 mg', '50 mg', '300 mg'], [1], 'Adenosin wird mit 6 mg schnell i.v. begonnen, danach 12 mg.'],
        ],
    ],
    [
        'title' => 'Pädiatrische Notfälle',
        'description' => 'Besonderheiten der Notfallversorgung von Säuglingen und Kindern. Umfasst Dosierung nach Gewicht, Atemwegsnotfälle, Reanimation, häufige Krankheitsbilder und Kinderschutz.',
        'hours' => 5, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Besonderheiten Kinder und Dosierung nach Gewicht', <<<'MD'
# Besonderheiten Kinder und Dosierung nach Gewicht

Kinder sind keine kleinen Erwachsenen. Anatomie, Physiologie und Psyche unterscheiden sich deutlich.

## Anatomische Besonderheiten

- Großer Kopf, kurzer Hals, große Zunge
- Säuglinge sind bis etwa zum 6. Lebensmonat **Nasenatmer**
- Enge Atemwege, schon kleine Schwellungen führen zu starker Atemnot
- Große Körperoberfläche im Verhältnis zum Gewicht: **schnelle Auskühlung**

## Physiologische Besonderheiten

- Kinder kompensieren einen Schock lange über die Herzfrequenz, dann kommt es zum **plötzlichen Zusammenbruch**
- Hypoxie führt bei Kindern schnell zur **Bradykardie** – ein Alarmzeichen
- Hoher Sauerstoffverbrauch, geringe Reserven

## Normwerte

- **Säugling:** AF 30–40, HF 110–160, RR systolisch 70–90
- **Kleinkind (1–5 Jahre):** AF 20–30, HF 95–140, RR systolisch 80–100
- **Schulkind (6–12 Jahre):** AF 15–25, HF 80–120, RR systolisch 90–110

## Gewichtsschätzung

- Faustformel für 1–10 Jahre: **(Alter + 4) x 2 = kg**
- Besser: Längenbasiertes Maßband (Broselow-Band) oder Kindernotfalllineal
- Angabe der Eltern zum aktuellen Gewicht nutzen

## Dosierung

Medikamente werden **nach Kilogramm** berechnet. Beispiele:
- Adrenalin Reanimation: **10 µg/kg** (0,01 mg/kg)
- Defibrillation: **4 J/kg**
- Volumenbolus: **10 ml/kg** Vollelektrolyt
- Glukose bei Hypoglykämie: 0,2 g/kg

Jede Dosis wird im Team **laut berechnet und gegengeprüft** (Vier-Augen-Prinzip).

## Psychologie

Eltern einbeziehen, Kind möglichst auf dem Schoß lassen, auf Augenhöhe sprechen, Maßnahmen ehrlich ankündigen.

*Merke:* Bei Kindern wird aus einem Kommafehler schnell eine zehnfache Überdosis. Immer gegenrechnen!
MD],
            ['Atemwegsnotfälle beim Kind', <<<'MD'
# Atemwegsnotfälle beim Kind

Atemwegsprobleme sind die häufigste Ursache für lebensbedrohliche Zustände im Kindesalter. Ein Kreislaufstillstand bei Kindern ist meist **hypoxisch** bedingt.

## Warnzeichen der Atemnot

- Erhöhte Atemfrequenz, Nasenflügeln
- Einziehungen am Brustkorb (jugulär, interkostal, sternal)
- Stridor (Geräusch beim Einatmen) oder Giemen (beim Ausatmen)
- Zyanose, Unruhe oder zunehmende Erschöpfung und Schläfrigkeit

## Pseudokrupp (Krupp-Syndrom)

- Meist Kleinkinder, nachts, nach Infekt
- **Bellender Husten**, inspiratorischer Stridor, Heiserkeit
- Therapie: Ruhe, Kind auf dem Arm der Eltern, kühle Luft, **Prednisolon rektal** (100 mg), bei schwerer Form Adrenalin vernebelt

## Epiglottitis

- Selten, aber lebensbedrohlich
- Hohes Fieber, kloßige Sprache, **Speichelfluss**, sitzende Haltung
- **Nicht in den Rachen schauen**, nicht hinlegen, keine Manipulation – schneller, ruhiger Transport mit Notarzt

## Fremdkörperaspiration

- Plötzlicher Husten beim Spielen oder Essen
- **Effektiver Husten:** zum Husten ermutigen, beobachten
- **Ineffektiver Husten, bei Bewusstsein:** 5 Rückenschläge, dann 5 Thoraxstöße (Säugling) bzw. 5 Bauchstöße (Kind über 1 Jahr) im Wechsel
- **Bewusstlos:** Reanimation beginnen, vor Beatmung in den Mund schauen

## Asthma

- Giemen, verlängerte Ausatmung, sitzende Position
- Salbutamol inhaliert, Prednisolon, Sauerstoff
- „Stille Lunge“ ohne Atemgeräusch ist ein **Alarmzeichen**

## Grundsätze

1. Kind in angenehmer Position belassen
2. Sauerstoff möglichst ohne Angst geben (Eltern halten Maske)
3. Bei Erschöpfung: Beutel-Masken-Beatmung

*Merke:* Ein schreiendes Kind atmet. Ein stilles, erschöpftes Kind ist in Gefahr.
MD],
            ['Reanimation Kind und Säugling', <<<'MD'
# Reanimation Kind und Säugling

Die Kinderreanimation folgt den **ERC-Leitlinien für Paediatric Life Support (PLS)**. Wichtigster Unterschied: Der Stillstand ist meist durch **Sauerstoffmangel** verursacht.

## Ablauf

1. Bewusstsein prüfen, Hilfe rufen
2. Atemweg öffnen: beim Säugling **Neutralposition**, beim Kind leichte Überstreckung
3. Atmung prüfen (maximal 10 Sekunden)
4. **5 initiale Beatmungen**
5. Lebenszeichen prüfen
6. Thoraxkompressionen im Verhältnis **15:2** (professionelle Helfer)

## Thoraxkompressionen

- Druckpunkt: **unteres Drittel des Brustbeins**
- Tiefe: mindestens **ein Drittel** des Thoraxdurchmessers (Säugling etwa 4 cm, Kind etwa 5 cm)
- Frequenz: **100–120 pro Minute**
- Säugling: **Zwei-Daumen-Technik** mit umgreifenden Händen, bei einem Helfer zwei Finger
- Kind: ein oder zwei Handballen

## Erweiterte Maßnahmen

- **Adrenalin:** 10 µg/kg i.v./i.o., bei nicht schockbarem Rhythmus sofort, danach alle 3–5 Minuten
- **Defibrillation:** 4 J/kg, Kinderelektroden oder anterior-posterior kleben
- **Amiodaron:** 5 mg/kg nach dem 3. und 5. Schock
- Frühzeitig **intraossärer Zugang**

## Bradykardie

Eine Herzfrequenz unter **60 pro Minute** mit Zeichen schlechter Durchblutung wird beim Kind wie ein Kreislaufstillstand behandelt: Kompressionen beginnen.

## Ursachen

Auch beim Kind gelten die **4H und HITS**, wobei Hypoxie und Hypovolämie am häufigsten sind.

## Eltern

Eltern dürfen bei der Reanimation anwesend sein, wenn sie es wünschen. Eine Person aus dem Team betreut sie und erklärt die Maßnahmen.

*Merke:* Kinder: 5 Beatmungen zuerst, dann 15:2. Sauerstoff ist das wichtigste Medikament.
MD],
            ['Fieberkrampf und Exsikkose', <<<'MD'
# Fieberkrampf und Exsikkose

Fieberkrämpfe und Flüssigkeitsmangel gehören zu den häufigsten Notfällen bei Kleinkindern.

## Fieberkrampf

- Auftreten meist zwischen **6 Monaten und 5 Jahren**
- Ausgelöst durch schnellen Fieberanstieg bei Infekten
- **Einfacher Fieberkrampf:** generalisiert, unter 15 Minuten, einmal in 24 Stunden
- **Komplizierter Fieberkrampf:** länger als 15 Minuten, fokal oder wiederholt – weitere Abklärung nötig

### Maßnahmen
1. Kind vor Verletzungen schützen, nicht festhalten
2. Atemweg freihalten, Sauerstoff geben
3. Bei Krampf über **5 Minuten**: **Midazolam bukkal** (0,3 mg/kg, max. 10 mg) oder intranasal oder Diazepam rektal (5 mg unter 15 kg, 10 mg über 15 kg)
4. **Blutzucker messen**
5. Fiebersenkung: Paracetamol oder Ibuprofen, Kleidung lockern
6. Eltern beruhigen – Fieberkrämpfe sehen dramatisch aus, sind aber meist harmlos

Jedes Kind nach dem **ersten** Fieberkrampf wird in die Klinik gebracht, um eine Meningitis auszuschließen.

## Exsikkose (Austrocknung)

Ursachen: Erbrechen, Durchfall, Fieber, zu geringe Trinkmenge.

### Zeichen
- Leicht: Durst, trockene Schleimhäute
- Mittel: **eingesunkene Fontanelle** beim Säugling, wenig nasse Windeln, stehende Hautfalten
- Schwer: Tachykardie, Rekapillarisierungszeit über 2 Sekunden, Lethargie, Schock

### Maßnahmen
- Vitalparameter, Blutzucker
- Bei Schock: **10 ml/kg** Vollelektrolytlösung i.v. oder i.o., Wiederholung nach Reevaluation
- Wärmeerhalt
- Transport in eine Kinderklinik bzw. zur pädiatrischen Versorgung im Pillbox Hill Medical Center

*Merke:* Ein krampfendes Kind braucht immer eine Blutzuckermessung – Hypoglykämie ist ein behandelbarer Auslöser.
MD],
            ['Kinderschutz', <<<'MD'
# Kinderschutz

Rettungsdienstmitarbeitende sehen Kinder oft in ihrem häuslichen Umfeld. Sie sind daher in einer besonderen Verantwortung, Hinweise auf **Misshandlung, Vernachlässigung oder Missbrauch** zu erkennen.

## Warnzeichen

- Verletzungsmuster passt nicht zur geschilderten Ursache oder zum Entwicklungsstand (z. B. Säugling, der noch nicht krabbelt, „vom Sofa gefallen“ mit Oberschenkelbruch)
- Verzögertes Aufsuchen von Hilfe
- Wechselnde Erklärungen der Bezugspersonen
- **Hämatome** an untypischen Stellen: Ohren, Hals, Rücken, Gesäß, Innenseiten der Oberarme
- Geformte Verletzungen: Griffmarken, Gürtel, Zigarettenverbrennungen
- Verbrühungen mit scharfer Begrenzung („Strumpfmuster“)
- Verwahrloste Wohnung, unterernährtes Kind, fehlende Pflege
- **Schütteltrauma:** Säugling mit Bewusstseinsstörung, Krampfanfällen, Erbrechen ohne äußere Verletzung

## Richtiges Verhalten

1. Ruhig und professionell bleiben, **keine Vorwürfe** machen
2. Das Kind medizinisch versorgen – das hat Vorrang
3. Möglichst in die **Klinik transportieren**, auch bei leichten Verletzungen
4. Beobachtungen **wörtlich und sachlich** dokumentieren (wer hat was gesagt)
5. Verletzungen in der **Verletzungsdokumentation** genau beschreiben
6. Übergabe an die Klinik mit dem ausdrücklichen Hinweis auf den Verdacht

## Schweigepflicht und Meldung

Bei Gefahr für das Kindeswohl ist eine Offenbarung gegenüber Polizei oder Jugendhilfe im Rahmen eines **rechtfertigenden Notstands** zulässig. Die Entscheidung trifft idealerweise die ärztliche Leitung. Bei akuter Gefahr wird sofort das LSPD oder BCSO hinzugezogen.

## Verweigerung des Transports

Verweigern Eltern den Transport eines gefährdeten Kindes, wird der Notarzt und die Polizei nachgefordert.

*Merke:* Nicht ermitteln, aber wahrnehmen, dokumentieren und weitergeben.
MD],
        ],
        'questions' => [
            ['Wie lautet die Faustformel zur Gewichtsschätzung bei Kindern von 1 bis 10 Jahren?', ['Alter x 10', '(Alter + 4) x 2', '(Alter x 3) + 7', 'Alter + 20'], [1], 'Die Formel (Alter + 4) x 2 ergibt das geschätzte Gewicht in Kilogramm.'],
            ['Welches Kompressions-Beatmungs-Verhältnis gilt bei der Kinderreanimation durch professionelle Helfer?', ['30:2', '15:2', '5:1', '3:1'], [1], 'Professionelle Helfer reanimieren Kinder im Verhältnis 15:2.'],
            ['Wie viele initiale Beatmungen werden bei der Kinderreanimation gegeben?', ['Keine', '2', '5', '10'], [2], 'Da der Stillstand meist hypoxisch ist, beginnt man mit 5 Beatmungen.'],
            ['Wie hoch ist die Adrenalindosis bei der Kinderreanimation?', ['1 mg pauschal', '10 µg/kg', '100 µg/kg', '1 mg/kg'], [1], 'Kinder erhalten 10 µg/kg Adrenalin i.v. oder i.o.'],
            ['Welche Energie wird für die Defibrillation bei Kindern verwendet?', ['1 J/kg', '2 J/kg', '4 J/kg', '10 J/kg'], [2], 'Die Leitlinie empfiehlt 4 J/kg.'],
            ['Was ist bei Verdacht auf Epiglottitis zu vermeiden? (Mehrere Antworten möglich)', ['In den Rachen schauen', 'Das Kind hinlegen', 'Ruhiger Transport mit Notarzt', 'Manipulation am Atemweg ohne Not'], [0, 1, 3], 'Jede Manipulation kann einen kompletten Verschluss auslösen.'],
            ['Typisches Symptom des Pseudokrupps ist:', ['Bellender Husten mit inspiratorischem Stridor', 'Hohes Fieber mit Speichelfluss', 'Exspiratorisches Giemen ohne Husten', 'Bauchschmerzen'], [0], 'Pseudokrupp zeigt bellenden Husten, Stridor und Heiserkeit.'],
            ['Was ist bei jedem krampfenden Kind zwingend zu messen?', ['Blutdruck', 'Blutzucker', 'Temperatur im Ohr', 'Körpergröße'], [1], 'Eine Hypoglykämie ist ein behandelbarer Auslöser von Krampfanfällen.'],
            ['Welche Befunde sind Warnzeichen für eine Kindesmisshandlung? (Mehrere Antworten möglich)', ['Hämatome an Ohren und Gesäß', 'Verletzung passt nicht zur Schilderung', 'Scharf begrenzte Verbrühung an beiden Füßen', 'Aufgeschürftes Knie nach Fahrradsturz'], [0, 1, 2], 'Ein Knie nach Fahrradsturz ist eine typische, plausible Kinderverletzung.'],
            ['Ab welcher Herzfrequenz mit Zeichen schlechter Perfusion beginnt man beim Kind mit Thoraxkompressionen?', ['Unter 100', 'Unter 80', 'Unter 60', 'Unter 30'], [2], 'Eine Bradykardie unter 60 mit Perfusionsstörung wird wie ein Stillstand behandelt.'],
        ],
    ],
    [
        'title' => 'Atemwegsmanagement',
        'description' => 'Sicherung und Freihaltung der Atemwege vom einfachen Hilfsmittel bis zur Intubation. Mit Beutel-Masken-Beatmung, supraglottischen Atemwegen, Kapnografie und Vorgehen beim schwierigen Atemweg.',
        'hours' => 5, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Atemwegshilfen und Absaugung', <<<'MD'
# Atemwegshilfen und Absaugung

Ein freier Atemweg ist Voraussetzung für jede weitere Therapie. Bei bewusstlosen Patienten fällt die Zunge zurück und verlegt den Rachen.

## Basismaßnahmen

- **Überstrecken des Kopfes und Anheben des Kinns** (Head-tilt, Chin-lift)
- **Esmarch-Handgriff:** Unterkiefer nach vorne schieben, schont die HWS beim Trauma
- **Stabile Seitenlage** bei ausreichender Atmung ohne Trauma-Verdacht

## Guedeltubus (oropharyngeal)

- Größe: vom **Mundwinkel bis zum Ohrläppchen** messen
- Einführen mit der Öffnung nach oben, dann um 180 Grad drehen (beim Kind direkt in Endposition mit Spatel)
- Nur bei **tief bewusstlosen** Patienten ohne Schutzreflexe – sonst Würgen und Erbrechen

## Wendltubus (nasopharyngeal)

- Größe: von der **Nasenspitze bis zum Ohrläppchen**, Durchmesser etwa wie der kleine Finger des Patienten
- Gut toleriert bei erhaltenen Reflexen, z. B. bei Krampfanfall oder Intoxikation
- Gleitmittel verwenden, Einführen entlang des Nasenbodens
- Kontraindikation: Verdacht auf **Schädelbasisfraktur** (Brillenhämatom, Liquor aus der Nase)

## Absaugung

- Erbrochenes, Blut oder Sekret müssen sofort entfernt werden.
- **Starrer Absaugkatheter** (Yankauer) für den Mund-Rachen-Raum.
- **Flexible Katheter** für Tubus oder Nase.
- Unterdruck beim Erwachsenen bis etwa **-0,8 bar**, bei Kindern geringer.
- Absaugen maximal **10–15 Sekunden**, dann Sauerstoff geben.
- Nur unter Sicht absaugen.

## Sauerstoffgabe

- Nasenbrille: 1–4 l/min
- Maske mit Reservoir: 10–15 l/min, bis zu 90 Prozent Sauerstoff
- Ziel-SpO2 94–98 Prozent, bei COPD 88–92 Prozent

*Merke:* Die Absaugpumpe gehört bei jedem bewusstlosen Patienten eingeschaltet neben den Kopf.
MD],
            ['Beutel-Masken-Beatmung', <<<'MD'
# Beutel-Masken-Beatmung

Die **Beutel-Masken-Beatmung (BMV)** ist die wichtigste und zugleich anspruchsvollste Basisfertigkeit im Atemwegsmanagement. Wer sie beherrscht, kann fast jeden Patienten oxygenieren.

## Indikationen

- Atemstillstand oder Reanimation
- Unzureichende Atmung: Atemfrequenz unter 8 oder sehr flache Atmung
- Präoxygenierung und Überbrückung bei der Narkoseeinleitung

## Material

- Beatmungsbeutel mit **Reservoir** und Sauerstoff 15 l/min (Sauerstoffanteil nahe 100 Prozent)
- Passende Maske: von der **Nasenwurzel bis zur Kinnfalte**, Mund und Nase umschließend
- Guedeltubus als Hilfsmittel
- PEEP-Ventil bei Bedarf

## Technik

### C-Griff (Ein-Helfer-Methode)
- Daumen und Zeigefinger formen ein **C** und drücken die Maske auf
- Mittel-, Ring- und kleiner Finger bilden ein **E** und ziehen den Unterkiefer zur Maske

### Zwei-Helfer-Methode
- Ein Helfer hält die Maske mit beiden Händen (doppelter C-Griff), der andere drückt den Beutel
- Deutlich bessere Abdichtung, bei schwierigen Patienten immer bevorzugen

## Beatmungsparameter

- Volumen: so viel, dass sich der Thorax **sichtbar hebt** (etwa 6–7 ml/kg, ca. 500 ml)
- Dauer der Insufflation: etwa **1 Sekunde**
- Frequenz: 10 pro Minute beim Erwachsenen
- Langsam und gleichmäßig beatmen, um **Mageninsufflation** zu vermeiden

## Probleme

- Undichtigkeit bei Bart, Zahnlosigkeit oder Gesichtsverletzungen
- Magenblähung durch zu hohen Druck – Aspirationsgefahr
- Merkhilfe für schwierige Maskenbeatmung **BOOTS**: **B**art, **O**besity (Übergewicht), **O**lder (Alter), **T**oothless (zahnlos), **S**noring (Schnarchen)

*Merke:* Keine Intubation ohne sicheres Beherrschen der Maskenbeatmung. Bei Problemen: zwei Helfer und Guedeltubus.
MD],
            ['Supraglottische Atemwege', <<<'MD'
# Supraglottische Atemwege

**Supraglottische Atemwegshilfen (SGA)** liegen oberhalb der Stimmritze und ermöglichen eine Beatmung ohne Intubation. Sie sind für Notfallsanitäter das Standardverfahren der erweiterten Atemwegssicherung.

## Typen

- **Larynxtubus (LT/LTS-D):** Schlauch mit zwei Cuffs, der distale Cuff verschließt die Speiseröhre, der proximale den Rachen. Version mit Drainagekanal (LTS-D) erlaubt die Einlage einer Magensonde.
- **Larynxmaske (LMA) bzw. i-gel:** Maske, die sich über den Kehlkopfeingang legt. Die i-gel hat einen gelartigen Cuff ohne Blocken.

## Indikationen

- Reanimation, um kontinuierliche Kompressionen zu ermöglichen
- Bewusstloser Patient ohne Schutzreflexe, wenn Intubation nicht möglich oder nicht beherrscht wird
- Rescue-Verfahren beim schwierigen Atemweg

## Größenwahl Larynxtubus

- Größe 3: Körpergröße unter 155 cm
- Größe 4: 155–180 cm
- Größe 5: über 180 cm
- Farbcodierung am Konnektor beachten

## Einführen

1. Präoxygenieren, Material prüfen, Cuff testen und entblocken
2. Kopf in Neutral- oder leichter Schnüffelposition
3. Tubus entlang des harten Gaumens einführen, bis die Zahnmarkierung auf Höhe der Schneidezähne liegt
4. Cuffs mit der Blockerspritze blocken (Cuffdruck **60 cmH2O**)
5. Lagekontrolle: Thoraxexkursion, Auskultation, **Kapnografie**
6. Fixieren und Beißschutz

## Grenzen

- Kein sicherer Aspirationsschutz
- Hohe Beatmungsdrücke führen zu Undichtigkeiten
- Bei erhaltenen Reflexen wird die SGA nicht toleriert

*Merke:* Eine richtig liegende SGA zeigt eine regelrechte Kapnografiekurve. Ohne CO2-Kurve gilt die Lage als nicht gesichert.
MD],
            ['Intubation und Kapnografie', <<<'MD'
# Intubation und Kapnografie

Die **endotracheale Intubation** ist der Goldstandard der Atemwegssicherung. Sie bietet Aspirationsschutz und eine sichere Beatmung, erfordert aber regelmäßiges Training.

## Indikationen

- GCS 8 oder weniger mit fehlenden Schutzreflexen
- Respiratorische Insuffizienz
- Schweres Schädel-Hirn-Trauma oder Polytrauma
- Drohende Atemwegsverlegung (Inhalationstrauma, Anaphylaxie)

## Vorbereitung (Checkliste)

1. Monitoring vollständig: EKG, SpO2, RR, Kapnografie
2. Absaugung eingeschaltet
3. Laryngoskop (am besten **Videolaryngoskop**) geprüft
4. Tubus: Frauen **7,0–7,5**, Männer **7,5–8,0** Innendurchmesser, Führungsstab, Blockerspritze
5. Alternative bereit: Larynxtubus, Bougie, Notfallkoniotomie-Set
6. **Präoxygenierung** mit 100 Prozent Sauerstoff über 3 Minuten
7. Medikamente aufgezogen und beschriftet
8. Rollen im Team verteilt

## Durchführung

- Laryngoskop von rechts einführen, Zunge nach links verdrängen
- Spatel in die Vallecula, Zug in Richtung Griff (nicht hebeln)
- Stimmritze darstellen, Tubus unter Sicht einführen, Cuff knapp hinter die Stimmbänder
- Cuff blocken (20–30 cmH2O)
- Einführtiefe an der Zahnreihe etwa **21 cm** (Frau) bzw. **23 cm** (Mann)
- Maximal **30 Sekunden** pro Versuch, bei Abfall der SpO2 abbrechen und mit Maske beatmen

## Kapnografie

Die **Kapnografie** (etCO2-Kurve) ist die einzige zuverlässige Methode zur Lagekontrolle.
- Normwert etCO2: **35–45 mmHg**
- Keine Kurve: Fehlintubation in die Speiseröhre, bis zum Beweis des Gegenteils
- Plötzlicher Abfall: Dislokation, Verlegung, Kreislaufstillstand
- In der Reanimation zeigt ein Anstieg einen ROSC an

## Lagekontrolle zusätzlich

Auskultation über Magen und beiden Lungen, Thoraxbewegungen, Beschlagen des Tubus. Diese Zeichen allein sind aber **unzuverlässig**.

*Merke:* „No trace = wrong place.“ Ohne Kapnografie keine Intubation.
MD],
            ['Schwieriger Atemweg', <<<'MD'
# Schwieriger Atemweg

Ein schwieriger Atemweg liegt vor, wenn Maskenbeatmung, SGA-Einlage oder Intubation erschwert oder unmöglich sind. Er muss **vorhergesehen** und mit einem klaren Plan beherrscht werden.

## Vorhersage mit LEMON

- **L – Look:** Gesichtsverletzungen, kurzer Hals, Übergewicht, Bart
- **E – Evaluate 3-3-2:** Mundöffnung 3 Querfinger, Kinn-Zungenbein 3 Querfinger, Zungenbein-Schildknorpel 2 Querfinger
- **M – Mallampati:** Sicht auf Gaumen und Zäpfchen bei geöffnetem Mund
- **O – Obstruction:** Schwellung, Blut, Tumor, Fremdkörper
- **N – Neck mobility:** Eingeschränkte Beweglichkeit der HWS, z. B. bei Immobilisation

## Stufenplan

1. **Plan A:** Intubation mit Videolaryngoskop und Bougie, maximal zwei bis drei Versuche
2. **Plan B:** Supraglottischer Atemweg (Larynxtubus oder i-gel)
3. **Plan C:** Beutel-Masken-Beatmung mit zwei Helfern und Guedeltubus, Patient aufwachen lassen, wenn möglich
4. **Plan D:** **CICO-Situation** (Cannot Intubate, Cannot Oxygenate) – sofortige **Notfallkoniotomie**

## Notfallkoniotomie

- Durchführung durch den Notarzt
- Aufsuchen der **Membrana cricothyroidea** zwischen Schild- und Ringknorpel
- Skalpell-Bougie-Tubus-Technik: Querschnitt durch Haut und Membran, Bougie einführen, Tubus 6,0 darüber schieben
- Lagekontrolle mit Kapnografie

## Teamfaktoren

- Laut ankündigen: *„Wir haben einen schwierigen Atemweg, wir gehen auf Plan B.“*
- Fixierung auf einen einzigen Versuch vermeiden
- Sauerstoffsättigung ständig laut ansagen lassen
- Frühzeitig Hilfe nachfordern (zweiter Notarzt, Anästhesie)

## Nachbereitung

Ein schwieriger Atemweg wird im Einsatzprotokoll und als vertraulicher **Akteneintrag** in der Patientenakte vermerkt, damit spätere Behandler gewarnt sind.

*Merke:* Patienten sterben nicht an einer fehlgeschlagenen Intubation, sondern an fehlender Oxygenierung.
MD],
        ],
        'questions' => [
            ['Wie wird die Größe eines Guedeltubus bestimmt?', ['Nasenspitze bis Kinn', 'Mundwinkel bis Ohrläppchen', 'Daumenbreite des Patienten', 'Nach Körpergewicht'], [1], 'Der Guedeltubus wird vom Mundwinkel bis zum Ohrläppchen gemessen.'],
            ['Bei welchem Verdacht ist ein Wendltubus kontraindiziert?', ['Krampfanfall', 'Schädelbasisfraktur', 'Alkoholintoxikation', 'Hypoglykämie'], [1], 'Bei Schädelbasisfraktur kann der Tubus in den Schädel gelangen.'],
            ['Wie lange sollte ein Absaugvorgang maximal dauern?', ['3 Sekunden', '10–15 Sekunden', '30–45 Sekunden', '60 Sekunden'], [1], 'Längeres Absaugen verursacht Hypoxie.'],
            ['Welche Methode der Beutel-Masken-Beatmung ist die zuverlässigste?', ['Ein-Helfer-C-Griff', 'Zwei-Helfer-Methode', 'Mund-zu-Maske', 'Beatmung ohne Maske'], [1], 'Mit zwei Helfern ist die Abdichtung deutlich besser.'],
            ['Was ist die zuverlässigste Methode zur Lagekontrolle eines Endotrachealtubus?', ['Auskultation', 'Beschlagen des Tubus', 'Kapnografie', 'Sichtbare Thoraxbewegung'], [2], 'Nur die Kapnografie bestätigt die Lage zuverlässig.'],
            ['Was bedeutet eine fehlende etCO2-Kurve direkt nach der Intubation?', ['Normalbefund', 'Fehllage in der Speiseröhre bis zum Beweis des Gegenteils', 'Hyperventilation', 'Gerät muss nicht angeschlossen sein'], [1], 'No trace = wrong place.'],
            ['Wofür steht CICO?', ['Cannot Intubate, Cannot Oxygenate', 'Circulation Check Only', 'Cuff In, Cuff Out', 'Critical Care Oxygen'], [0], 'Bei CICO ist die sofortige Notfallkoniotomie indiziert.'],
            ['Welche Faktoren sprechen für eine schwierige Maskenbeatmung? (Mehrere Antworten möglich)', ['Vollbart', 'Zahnlosigkeit', 'Starkes Übergewicht', 'Junges Alter ohne Vorerkrankungen'], [0, 1, 2], 'Merkhilfe BOOTS: Bart, Obesity, Older, Toothless, Snoring.'],
            ['Welcher Normwert gilt für das etCO2?', ['10–20 mmHg', '35–45 mmHg', '60–80 mmHg', '90–100 mmHg'], [1], 'Der normale endtidale CO2-Wert liegt bei 35 bis 45 mmHg.'],
            ['Welche Aussagen zum Larynxtubus sind richtig? (Mehrere Antworten möglich)', ['Er bietet keinen sicheren Aspirationsschutz', 'Die Größe richtet sich nach der Körpergröße', 'Er wird unter Sicht durch die Stimmritze geschoben', 'Die Lage wird mit Kapnografie überprüft'], [0, 1, 3], 'Der Larynxtubus wird blind eingeführt und liegt oberhalb der Stimmritze.'],
        ],
    ],
    [
        'title' => 'Dokumentation und Recht im SAMS',
        'description' => 'Rechtliche Grundlagen der Arbeit im SAMS und der korrekte Umgang mit dem Dokumentensystem. Behandelt Dokumentationspflicht, Schweigepflicht, Einwilligung, Revers, Unterbringung und Behördenanfragen.',
        'hours' => 4, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Dokumentationspflicht und Einsatzprotokoll', <<<'MD'
# Dokumentationspflicht und Einsatzprotokoll

Jede medizinische Maßnahme muss dokumentiert werden. Die Dokumentation dient der **Patientensicherheit**, der Weiterbehandlung, der Qualitätssicherung und der rechtlichen Absicherung.

## Grundsatz

*„Was nicht dokumentiert ist, gilt als nicht gemacht.“* Vor Gericht beim DOJ ist das Einsatzprotokoll oft das wichtigste Beweismittel.

## Das Einsatzprotokoll

Für **jeden** Patientenkontakt wird im System ein Einsatzprotokoll angelegt, auch bei Transportverweigerung oder Bagatellen. Es enthält:

1. **Einsatzdaten:** Datum, Uhrzeit, Einsatzort, Einsatznummer, Fahrzeug, Besatzung
2. **Patientendaten:** Name, Geburtsdatum, Verknüpfung mit der Patientenakte
3. **Notfallgeschehen:** Auffindesituation, Unfallmechanismus
4. **Befunde nach xABCDE** und Vitalparameter mit Uhrzeiten
5. **Anamnese** nach SAMPLER
6. **Maßnahmen und Medikamente** mit Dosis, Weg und Uhrzeit
7. **Verdachtsdiagnose**
8. **Verlauf und Übergabe:** Zielklinik, Zustand bei Übergabe, übernehmende Person

## Anforderungen

- **Zeitnah:** spätestens bis Schichtende abschließen
- **Vollständig und wahr:** keine Beschönigung von Fehlern
- **Sachlich:** keine Wertungen wie „Junkie“ oder „Simulant“
- **Leserlich und verständlich:** gängige Abkürzungen verwenden

## Korrekturen

Abgeschlossene Protokolle werden **nicht gelöscht**. Korrekturen erfolgen als Nachtrag mit Datum und Name. Nachträgliches Verfälschen ist eine Urkundenfälschung und wird disziplinarisch und strafrechtlich verfolgt.

## Fälle

Mehrere zusammengehörige Einsätze und Dokumente eines Patienten (z. B. Unfall, OP, Nachsorge) können im System zu einem **Fall** zusammengefasst werden.

*Merke:* Gute Dokumentation schützt den Patienten und dich selbst.
MD],
            ['Schweigepflicht und Datenschutz', <<<'MD'
# Schweigepflicht und Datenschutz

Die **ärztliche Schweigepflicht** gilt für alle Mitarbeitenden der SAMS – Ärzte, Notfallsanitäter, Pflege, Verwaltung und Auszubildende. Sie ist Grundlage für das Vertrauen der Patienten.

## Was fällt unter die Schweigepflicht?

- Alle Informationen, die im Rahmen der Behandlung bekannt werden
- Diagnosen, Befunde, Medikamente, Drogenkonsum
- Die **Tatsache der Behandlung** selbst
- Persönliche Umstände, Gespräche, Beobachtungen in der Wohnung

## Gegenüber wem gilt sie?

- Gegenüber **Angehörigen** (außer mit Einverständnis)
- Gegenüber **Arbeitgebern**
- Gegenüber **Polizei (LSPD, BCSO)** und **Justiz (DOJ)**
- Gegenüber Kollegen, die nicht an der Behandlung beteiligt sind

## Ausnahmen (Offenbarungsbefugnis)

1. **Schweigepflichtentbindung** durch den Patienten (schriftlich im System dokumentiert)
2. **Richterliche Anordnung** oder Beschluss des DOJ
3. **Gesetzliche Meldepflichten**, z. B. bestimmte Infektionskrankheiten
4. **Rechtfertigender Notstand:** akute Gefahr für Leib und Leben Dritter, z. B. geplanter Mord oder Kindeswohlgefährdung

## Datenschutz im System

- Zugriff auf Patientenakten nur bei **Behandlungsbezug**
- Alle Zugriffe werden protokolliert
- **Vertrauliche Akteneinträge** (z. B. psychiatrische Diagnosen, HIV, Sexualdelikte) sind nur für berechtigte Personen sichtbar
- Tablet sperren, keine Ausdrucke liegen lassen
- Keine Screenshots oder Weitergabe über private Kanäle

## Verstöße

Die Verletzung der Schweigepflicht ist eine **Straftat** und führt zu disziplinarischen Konsequenzen bis zur Entlassung.

*Merke:* Auch der beste Freund im LSPD erfährt ohne Entbindung oder Anordnung nichts.
MD],
            ['Einwilligung, Revers und Unterbringung', <<<'MD'
# Einwilligung, Revers und Unterbringung

Jede medizinische Maßnahme ist rechtlich ein Eingriff in die körperliche Unversehrtheit und benötigt eine **Rechtfertigung** – in der Regel die Einwilligung des Patienten.

## Einwilligung

- **Ausdrücklich:** Der Patient stimmt zu, nachdem er über die Maßnahme aufgeklärt wurde.
- **Mutmaßlich:** Bei bewusstlosen Patienten wird angenommen, dass sie lebensrettenden Maßnahmen zustimmen würden.
- **Einwilligungsfähig** ist, wer Art, Bedeutung und Tragweite der Entscheidung verstehen kann. Alkohol, Drogen, Demenz oder schwere Schmerzen können die Fähigkeit aufheben.
- Bei **Minderjährigen** entscheiden die Sorgeberechtigten; im Notfall gilt die mutmaßliche Einwilligung.

## Behandlungsverweigerung und Revers

Ein einwilligungsfähiger Patient darf jede Behandlung ablehnen – auch wenn es unvernünftig ist.

### Ablauf
1. Einwilligungsfähigkeit prüfen und dokumentieren
2. **Aufklärung** über Risiken der Ablehnung bis hin zum Tod, in verständlichen Worten
3. Alternativen anbieten (Hausarzt, späterer Besuch im Pillbox Hill Medical Center)
4. Bei Zweifeln oder relevanter Erkrankung **Notarzt nachfordern**
5. Im System das Dokument **Revers** ausstellen und vom Patienten unterschreiben lassen, Zeugen benennen
6. Unterschrift verweigert: Vermerken, Zeugen unterschreiben

## Unterbringung

Besteht durch eine psychische Erkrankung eine **akute Gefahr für sich selbst oder andere**, kann eine Unterbringung gegen den Willen erfolgen.
- Voraussetzungen: akute Eigen- oder Fremdgefährdung, die anders nicht abwendbar ist
- Ärztliche Untersuchung und Ausstellung des Dokuments **Unterbringung** im System
- Durchsetzung bei Widerstand durch LSPD oder BCSO
- Richterliche Bestätigung durch das DOJ zeitnah, spätestens bis zum Ende des Folgetages

*Merke:* Ohne Einwilligung keine Behandlung – außer im Notfall mit mutmaßlicher Einwilligung oder bei rechtmäßiger Unterbringung.
MD],
            ['Zusammenarbeit mit Behörden', <<<'MD'
# Zusammenarbeit mit Behörden

Rettungsdienst, Polizei und Justiz arbeiten im Einsatz eng zusammen. Die Rollen sind aber klar getrennt: **Wir behandeln, die Polizei ermittelt, das DOJ entscheidet.**

## Am Einsatzort

- LSPD und BCSO sichern die Einsatzstelle, der Rettungsdienst versorgt.
- Die Polizei darf Patienten **nicht** ohne medizinische Freigabe aus der Behandlung nehmen.
- Festgenommene Patienten werden genauso behandelt wie alle anderen. Eine Polizeibegleitung im RTW ist möglich.
- Informationen über Diagnosen gibt der Rettungsdienst am Einsatzort nicht weiter. Erlaubt ist die Aussage, ob der Patient **gewahrsamsfähig** ist oder ins Krankenhaus muss.

## Behördenanfragen im System

Polizei und Justiz können im System eine **Behördenanfrage** stellen, z. B. für Befunde, Verletzungsdokumentationen oder Blutwerte.

### Prüfung einer Anfrage
1. Wer fragt an (Behörde, Dienstnummer)?
2. Welche Daten werden benötigt und wofür?
3. Liegt eine **Schweigepflichtentbindung** des Patienten vor?
4. Oder liegt eine **richterliche Anordnung** des DOJ vor?
5. Ohne eines davon: **Ablehnung** mit Begründung

Die **Freigabe** an Behörden erfolgt nur durch berechtigte Personen und nur in dem Umfang, der angefordert und erlaubt ist. Nur die nötigen Dokumente werden freigegeben, keine vollständige Akte.

## Blutentnahme auf behördliche Anordnung

Bei Verdacht auf Fahren unter Alkohol- oder Drogeneinfluss kann die Polizei eine Blutentnahme anordnen. Durchführung nur durch Ärztinnen oder Ärzte, Dokumentation im Formular **Blutentnahme auf behördliche Anordnung** mit Uhrzeit, anordnender Person und Probennummer.

## Aussagen vor Gericht

Mitarbeitende können als Zeugen geladen werden. Aussagen über medizinische Inhalte nur mit Entbindung oder gerichtlicher Anordnung.

*Merke:* Hilfsbereitschaft gegenüber Behörden ja – aber nie auf Kosten der Schweigepflicht.
MD],
            ['Dokumente im System richtig ausstellen', <<<'MD'
# Dokumente im System richtig ausstellen

Das Dokumentensystem der SAMS stellt Vorlagen für alle wichtigen Vorgänge bereit. Die Dokumente werden am Tablet erstellt und bei Bedarf am Drucker ausgegeben.

## Wichtige Dokumente

- **Einsatzprotokoll:** Pflicht bei jedem Patientenkontakt
- **Revers:** Bei Ablehnung von Behandlung oder Transport
- **Arbeitsunfähigkeitsbescheinigung (AU):** Zeitraum der Arbeitsunfähigkeit, ohne Diagnose für den Arbeitgeber
- **Attest:** Ärztliche Bescheinigung, z. B. über Sport- oder Diensttauglichkeit
- **Rezept und BtM-Rezept:** Verordnung von Medikamenten, BtM nur durch Ärzte
- **Arztbrief:** Zusammenfassung von Diagnose, Therapie und Empfehlungen nach Entlassung
- **Verletzungsdokumentation:** Genaue Beschreibung von Verletzungen mit Lage, Größe und Art, wichtig für spätere Ermittlungen
- **Blutentnahme auf behördliche Anordnung**
- **Todesbescheinigung:** Nur durch Ärztinnen und Ärzte nach Leichenschau
- **Unterbringung:** Bei akuter Eigen- oder Fremdgefährdung
- **Rechnung:** Abrechnung erbrachter Leistungen

## Grundregeln

1. Dokument immer mit der richtigen **Patientenakte** verknüpfen
2. Nur Dokumente ausstellen, für die man **berechtigt** ist
3. Inhalt **wahrheitsgemäß** – Gefälligkeitsatteste sind strafbar
4. Datum, Uhrzeit und Name des Ausstellers prüfen
5. Ausgedruckte Dokumente nur an den Patienten oder berechtigte Personen aushändigen

## Akteneinträge

Zusätzlich zu Dokumenten können Akteneinträge angelegt werden, z. B. Allergien, Dauermedikation oder Hinweise wie „schwieriger Atemweg“. Sensible Inhalte werden als **vertraulich** markiert.

## Typische Fehler

- Falscher Patient ausgewählt
- AU mit Diagnose an den Arbeitgeber
- Freigabe an Behörde ohne Entbindung oder Anordnung
- Protokoll nicht abgeschlossen

*Merke:* Ein Dokument ist eine Urkunde. Was du unterschreibst, musst du vertreten können.
MD],
        ],
        'questions' => [
            ['Wann muss ein Einsatzprotokoll angelegt werden?', ['Nur bei Transport in die Klinik', 'Bei jedem Patientenkontakt', 'Nur bei Reanimationen', 'Nur auf Anfrage der Polizei'], [1], 'Auch bei Transportverweigerung oder Bagatellen wird dokumentiert.'],
            ['Wie werden Fehler in einem abgeschlossenen Einsatzprotokoll korrigiert?', ['Protokoll löschen und neu anlegen', 'Als Nachtrag mit Datum und Name', 'Gar nicht', 'Durch die Polizei'], [1], 'Abgeschlossene Protokolle werden nicht gelöscht, sondern ergänzt.'],
            ['Unter welchen Voraussetzungen dürfen Patientendaten an das LSPD freigegeben werden? (Mehrere Antworten möglich)', ['Schweigepflichtentbindung durch den Patienten', 'Richterliche Anordnung des DOJ', 'Mündliche Bitte eines Officers', 'Freundschaft zum ermittelnden Detective'], [0, 1], 'Nur Entbindung oder richterliche Anordnung erlauben die Freigabe.'],
            ['Ein wacher, orientierter Patient lehnt den Transport ab. Was ist richtig?', ['Er wird gegen seinen Willen transportiert', 'Aufklärung, Dokumentation und Revers ausstellen', 'Die Polizei muss ihn festnehmen', 'Der Einsatz wird ohne Dokumentation beendet'], [1], 'Einwilligungsfähige Patienten dürfen ablehnen, dies wird im Revers dokumentiert.'],
            ['Was ist Voraussetzung für eine Unterbringung?', ['Unhöfliches Verhalten', 'Akute Eigen- oder Fremdgefährdung durch eine psychische Erkrankung', 'Wunsch der Angehörigen', 'Fehlende Krankenversicherung'], [1], 'Nur eine akute, nicht anders abwendbare Gefahr rechtfertigt die Unterbringung.'],
            ['Welche Information darf der Rettungsdienst am Einsatzort der Polizei ohne Entbindung mitteilen?', ['Die genaue Diagnose', 'Ob der Patient gewahrsamsfähig ist oder ins Krankenhaus muss', 'Die Drogenanamnese', 'Frühere Klinikaufenthalte'], [1], 'Die Aussage zur Gewahrsamsfähigkeit ist ohne Diagnosedetails möglich.'],
            ['Was gehört NICHT auf die AU für den Arbeitgeber?', ['Zeitraum der Arbeitsunfähigkeit', 'Name des Patienten', 'Die Diagnose', 'Ausstellende Ärztin'], [2], 'Der Arbeitgeber erfährt die Diagnose nicht.'],
            ['Wer darf eine Todesbescheinigung ausstellen?', ['Jeder Notfallsanitäter', 'Nur Ärztinnen und Ärzte nach Leichenschau', 'Das LSPD', 'Die Leitstelle'], [1], 'Die Todesbescheinigung ist eine ärztliche Aufgabe.'],
            ['Welche Inhalte sollten als vertraulicher Akteneintrag angelegt werden? (Mehrere Antworten möglich)', ['Psychiatrische Diagnose', 'HIV-Infektion', 'Hinweise zu einem Sexualdelikt', 'Blutgruppe'], [0, 1, 2], 'Besonders sensible Inhalte werden vertraulich markiert.'],
            ['Was gilt bei einem bewusstlosen Patienten ohne Patientenverfügung?', ['Keine Behandlung ohne Unterschrift', 'Mutmaßliche Einwilligung für lebensrettende Maßnahmen', 'Polizei muss zustimmen', 'Angehörige müssen schriftlich zustimmen'], [1], 'Es wird angenommen, dass der Patient lebensrettenden Maßnahmen zustimmen würde.'],
        ],
    ],
    [
        'title' => 'Massenanfall von Verletzten (MANV)',
        'description' => 'Führung und Organisation bei Großschadenslagen mit vielen Verletzten. Vermittelt Lagebeurteilung, Triage nach mSTaRT, Raumordnung, Kommunikation und Nachbereitung für den Leitenden Notarzt.',
        'hours' => 8, 'pass_percent' => 80, 'question_count' => 8, 'time_limit' => 25,
        'qualification' => 'Leitender Notarzt',
        'modules' => [
            ['Lagebeurteilung und Erstmaßnahmen', <<<'MD'
# Lagebeurteilung und Erstmaßnahmen

Ein **Massenanfall von Verletzten (MANV)** liegt vor, wenn die Zahl der Patienten die vorhandenen Rettungsmittel übersteigt. Das Ziel ändert sich: nicht mehr die bestmögliche Versorgung des Einzelnen, sondern das **Bestmögliche für möglichst viele**.

## Typische Lagen in San Andreas

- Massenkarambolage auf dem Highway
- Explosion oder Brand, z. B. in der Industrie von Elysian Island
- Schusswaffengebrauch mit vielen Verletzten (Amoklage)
- Busunglück oder Flugzeugabsturz am Flughafen Los Santos

## Das erste Fahrzeug

Das ersteintreffende Team übernimmt bis zum Eintreffen der Führungskräfte die **Einsatzleitung**. Die wichtigste Aufgabe ist **nicht** die Patientenversorgung, sondern die Erkundung und Meldung.

## Erstmaßnahmen

1. **Eigenschutz:** Gefahren erkennen (Feuer, Gefahrstoffe, Täter). Bei Bedrohungslagen nur in den von der Polizei freigegebenen Bereichen arbeiten.
2. **Erkundung:** Überblick verschaffen, Anzahl der Verletzten grob schätzen.
3. **Erste Lagemeldung** an die Leitstelle: Was ist passiert? Wie viele Verletzte (geschätzt)? Welche Gefahren bestehen? Welche Kräfte werden benötigt? Welche Anfahrtswege sind frei?
4. **Nachforderung:** RTW, NEF, LNA, OrgL, Polizei, Feuerwehr, ggf. RTH.
5. **Einrichtung eines Bereitstellungsraums** für nachrückende Fahrzeuge.

## Gefahrenmatrix (AAAACEEEE)

Atemgifte, Angst, Ausbreitung, Atomare Strahlung, Chemische Stoffe, Erkrankung, Explosion, Elektrizität, Einsturz.

## Fahrzeug positionieren

Das erste Fahrzeug bleibt als **Führungsfahrzeug** stehen, Blaulicht an, Schlüssel stecken. Es wird nicht für Transporte genutzt.

*Merke:* Wer als Erster ankommt und sofort behandelt, verliert den Überblick. Erst melden, dann ordnen, dann helfen.
MD],
            ['Triage nach mSTaRT', <<<'MD'
# Triage nach mSTaRT

Die **Sichtung (Triage)** teilt Patienten nach Behandlungsdringlichkeit ein. Das Verfahren **mSTaRT** (modified Simple Triage and Rapid Treatment) erlaubt eine Sichtung in unter **60 Sekunden** pro Patient – auch durch Rettungssanitäter.

## Sichtungskategorien

- **SK I – Rot:** Akute vitale Bedrohung, sofortige Behandlung
- **SK II – Gelb:** Schwer verletzt, aufgeschobene Behandlungsdringlichkeit
- **SK III – Grün:** Leicht verletzt, spätere Behandlung
- **SK IV – Blau:** Ohne Überlebenschance unter MANV-Bedingungen, betreuende Behandlung
- **Schwarz:** Tot

## Ablauf mSTaRT

1. **Gehfähig?** Ja: **Grün**. Patienten werden zu einem Sammelpunkt geschickt.
2. **Spritzende Blutung?** Ja: Blutung sofort stoppen (Tourniquet), **Rot**.
3. **Atmung vorhanden** nach Freimachen der Atemwege? Nein: **Schwarz** (bzw. tot). Ja: weiter.
4. **Atemfrequenz über 30** pro Minute? Ja: **Rot**.
5. **Radialispuls nicht tastbar** oder Rekapillarisierungszeit über 2 Sekunden? Ja: **Rot**.
6. **Befolgt Aufforderungen nicht**? Ja: **Rot**.
7. Ansonsten: **Gelb**.

Zusätzlich führen bestimmte Verletzungsmuster (z. B. penetrierende Rumpfverletzung, Verbrennung über 20 Prozent) zur Einstufung **Rot**.

## Lebensrettende Sofortmaßnahmen während der Sichtung

- Atemwege freimachen, stabile Seitenlage
- Tourniquet bei kritischer Blutung
- **Keine** zeitaufwendigen Maßnahmen wie Reanimation oder Zugänge

## Kennzeichnung

Jeder Patient erhält eine **Patientenanhängekarte** mit Nummer und Farbe. Die Sichtung wird im Verlauf durch einen Notarzt **wiederholt** (ärztliche Sichtung), da sich der Zustand ändern kann.

*Merke:* Im MANV wird nicht reanimiert. Wer nicht atmet, wird als tot bzw. schwarz eingestuft.
MD],
            ['Einsatzabschnitte und Patientenablage', <<<'MD'
# Einsatzabschnitte und Patientenablage

Eine klare **Raumordnung** verhindert Chaos. Die Einsatzstelle wird in Abschnitte gegliedert, die jeweils einen eigenen Abschnittsleiter haben.

## Räume eines MANV

- **Schadensgebiet:** Ort des Geschehens. Hier wird gesichtet und gerettet, nicht behandelt.
- **Patientenablage:** Sicherer Bereich nahe dem Schadensgebiet, in dem Patienten gesammelt und erstversorgt werden.
- **Behandlungsplatz:** Strukturierte Versorgung in Zelten oder Gebäuden, getrennt nach Sichtungskategorien.
- **Rettungsmittelhalteplatz:** Fahrzeuge warten auf Patienten, nah am Behandlungsplatz.
- **Bereitstellungsraum:** Sammelstelle für nachrückende Kräfte, außerhalb der Einsatzstelle.
- **Sammelstelle Leichtverletzte:** Für gehfähige Patienten, Betreuung durch Rettungssanitäter.
- **Hubschrauberlandeplatz:** Abseits, freie Fläche, durch Feuerwehr oder Polizei gesichert.

## Patientenablage

- Getrennte Bereiche für **Rot, Gelb und Grün**, farblich markiert (Planen, Flaggen)
- Rot: nah an der Abfahrt, damit schnell transportiert werden kann
- Pro rotem Patient ein Helfer, wenn möglich
- Registrierung jedes Patienten mit Anhängekarte und Zeit

## Einsatzabschnitte

1. **Medizinische Rettung/Sichtung**
2. **Behandlung**
3. **Transport**
4. **Bereitstellung**

Weitere Abschnitte (Brandbekämpfung, Absperrung) werden von Feuerwehr und Polizei geführt.

## Transportorganisation

Der Abschnitt **Transport** verteilt die Patienten auf Kliniken: Pillbox Hill Medical Center, Sandy Shores und Paleto Bay. Ziel: **keine Überlastung** einer Klinik. Rote Patienten zuerst, mit Notarztbegleitung, wenn verfügbar.

*Merke:* Patientenablage und Rettungsmittelhalteplatz müssen dicht beieinander, der Bereitstellungsraum aber außerhalb liegen.
MD],
            ['Kommunikation und Leitender Notarzt', <<<'MD'
# Kommunikation und Leitender Notarzt

Bei einem MANV übernimmt die **Sanitätseinsatzleitung (SanEL)** die Führung. Sie besteht aus dem **Leitenden Notarzt (LNA)** und dem **Organisatorischen Leiter Rettungsdienst (OrgL)**.

## Aufgaben des Leitenden Notarztes

- Medizinische Gesamtverantwortung
- Festlegung der **medizinischen Versorgungsstrategie**
- Überprüfung der Sichtung, ärztliche Nachsichtung
- Festlegung von **Transportprioritäten** und Zielkliniken
- Einsatz der verfügbaren Notärzte
- Beratung der Gesamteinsatzleitung

## Aufgaben des OrgL

- Organisation und Logistik
- Einrichtung der Räume und Abschnitte
- Führung der Fahrzeuge und Kräfte
- Kommunikation mit Leitstelle, Polizei und Feuerwehr

## Führungsstruktur

Die SanEL arbeitet mit der **Einsatzleitung der Feuerwehr** und der **Polizeiführung** (LSPD/BCSO) zusammen. Gemeinsame Absprachen erfolgen an einem festen **Führungsplatz** (Kommandowagen).

## Kommunikation

1. Eigene **Funkkanäle** für einzelne Abschnitte, um den Hauptkanal zu entlasten
2. Kurze, regelmäßige **Lagemeldungen** an die Leitstelle (z. B. alle 15 Minuten)
3. **Kliniken** werden früh informiert, damit sie Notfallpläne aktivieren und Kapazitäten melden
4. Meldungen nach Schema: Lage, Entschluss, Auftrag, Rückmeldung

## Führungsvorgang

- **Lagefeststellung:** Was ist los?
- **Planung:** Was wollen wir erreichen?
- **Befehlsgebung:** Wer macht was?
- **Kontrolle:** Wird es umgesetzt?

## Presse und Angehörige

Pressefragen werden an die zuständige Pressestelle verwiesen. Angehörige werden zu einer **Personenauskunftsstelle** geleitet. Keine Weitergabe von Patientennamen an Medien.

*Merke:* Der LNA behandelt nicht selbst – er führt. Wer behandelt, kann nicht führen.
MD],
            ['Abschluss und Nachbereitung', <<<'MD'
# Abschluss und Nachbereitung

Ein MANV endet nicht mit dem Abtransport des letzten Patienten. Ein geordneter Abschluss und eine ehrliche Nachbereitung sind entscheidend für Qualität und Gesundheit der Einsatzkräfte.

## Einsatzabschluss

1. **Letzte Sichtung** des Schadensgebiets: Sind wirklich alle Patienten gefunden?
2. Abgleich der **Patientenregistrierung** mit Transportlisten und Klinikaufnahmen
3. Rückmeldung an die Leitstelle mit Gesamtzahlen
4. Geordnete **Entlassung** der Kräfte aus dem Einsatz
5. Aufrüstung, Reinigung und Desinfektion der Fahrzeuge
6. Übergabe der Einsatzstelle an Polizei oder Feuerwehr

## Dokumentation

- Jeder Patient erhält ein **Einsatzprotokoll**, das später aus Anhängekarten und Notizen ergänzt wird
- Die SanEL erstellt einen **Einsatzbericht** mit Zeitstrahl, Anzahl Patienten je Sichtungskategorie, eingesetzten Kräften und Zielkliniken
- Alle Einsätze werden im System zu einem gemeinsamen **Fall** verknüpft
- Bei Straftaten: Verletzungsdokumentationen für spätere Anfragen von LSPD und DOJ vorbereiten, Freigabe nur nach den üblichen Regeln

## Einsatznachbesprechung

- **Kurzbesprechung (Hot Debrief)** direkt nach dem Einsatz: Was war gut, was war schwierig?
- **Ausführliche Nachbesprechung** einige Tage später mit allen Beteiligten, auch Polizei und Feuerwehr
- Fehler offen ansprechen, ohne Schuldzuweisung
- Ergebnisse fließen in Alarmpläne und Ausbildung ein

## Psychosoziale Notfallversorgung (PSNV)

Großschadenslagen belasten Einsatzkräfte stark. Anzeichen für Belastungsreaktionen sind Schlafstörungen, Flashbacks, Reizbarkeit oder Rückzug.
- Angebot von **Kriseninterventionsteams** und Peer-Gesprächen
- Führungskräfte achten aktiv auf ihr Team
- Niemand muss sich schämen, Hilfe in Anspruch zu nehmen

*Merke:* Nach dem Einsatz ist vor dem Einsatz – aber nur, wenn das Team gesund bleibt.
MD],
        ],
        'questions' => [
            ['Was ist die wichtigste Aufgabe des ersteintreffenden Teams bei einem MANV?', ['Sofort den schwersten Patienten behandeln', 'Erkundung und erste Lagemeldung', 'Patienten selbst in die Klinik fahren', 'Presse informieren'], [1], 'Das erste Team erkundet, meldet und ordnet, bevor behandelt wird.'],
            ['Welche Sichtungskategorie erhalten gehfähige Patienten nach mSTaRT?', ['Rot', 'Gelb', 'Grün', 'Blau'], [2], 'Gehfähige Patienten werden als Grün eingestuft.'],
            ['Ein Patient atmet mit 34 Atemzügen pro Minute. Welche Kategorie erhält er?', ['Grün', 'Gelb', 'Rot', 'Schwarz'], [2], 'Eine Atemfrequenz über 30 führt zur Kategorie Rot.'],
            ['Welche Maßnahmen sind während der mSTaRT-Sichtung erlaubt? (Mehrere Antworten möglich)', ['Atemwege freimachen', 'Tourniquet bei kritischer Blutung', 'Reanimation', 'Anlage eines Zugangs'], [0, 1], 'Nur kurze lebensrettende Sofortmaßnahmen sind vorgesehen.'],
            ['Wofür steht die Sichtungskategorie Blau?', ['Leicht verletzt', 'Ohne Überlebenschance unter MANV-Bedingungen, betreuende Behandlung', 'Kontaminiert', 'Bereits transportiert'], [1], 'SK IV (Blau) erhält eine betreuende Behandlung.'],
            ['Wer bildet die Sanitätseinsatzleitung? (Mehrere Antworten möglich)', ['Leitender Notarzt', 'Organisatorischer Leiter Rettungsdienst', 'Polizeipräsident', 'Pressesprecher'], [0, 1], 'Die SanEL besteht aus LNA und OrgL.'],
            ['Wo sollte der Bereitstellungsraum liegen?', ['Direkt im Schadensgebiet', 'Außerhalb der Einsatzstelle', 'Auf dem Behandlungsplatz', 'In der Klinik'], [1], 'Nachrückende Kräfte sammeln sich außerhalb, um die Einsatzstelle nicht zu verstopfen.'],
            ['Was ist eine Aufgabe des Leitenden Notarztes?', ['Selbst rote Patienten intubieren', 'Festlegung der Transportprioritäten und Zielkliniken', 'Absperrung der Straße', 'Brandbekämpfung'], [1], 'Der LNA führt medizinisch und legt Prioritäten fest.'],
            ['Wie wird verhindert, dass eine einzelne Klinik überlastet wird?', ['Alle Patienten ins Pillbox Hill Medical Center', 'Verteilung durch den Abschnitt Transport auf mehrere Kliniken', 'Patienten fahren selbst', 'Nur rote Patienten werden transportiert'], [1], 'Der Abschnitt Transport verteilt auf Pillbox Hill, Sandy Shores und Paleto Bay.'],
            ['Was gehört zur Nachbereitung eines MANV? (Mehrere Antworten möglich)', ['Einsatznachbesprechung', 'Angebot psychosozialer Unterstützung', 'Verknüpfung der Einsätze zu einem Fall', 'Löschen der Anhängekarten-Daten'], [0, 1, 2], 'Daten werden nicht gelöscht, sondern in die Dokumentation übernommen.'],
        ],
    ],
    [
        'title' => 'Notarzt-Lehrgang',
        'description' => 'Qualifizierung von Ärztinnen und Ärzten für den Notarztdienst der SAMS. Behandelt Rolle und Verantwortung, erweiterte Diagnostik, Notfallnarkose, Transportentscheidungen sowie Todesfeststellung und Leichenschau.',
        'hours' => 10, 'pass_percent' => 85, 'question_count' => 10, 'time_limit' => 30,
        'qualification' => 'Notarzt',
        'modules' => [
            ['Rolle und Verantwortung des Notarztes', <<<'MD'
# Rolle und Verantwortung des Notarztes

Der **Notarzt** ist der ärztliche Teamleiter am Einsatzort. Er trägt die medizinische Verantwortung für alle Patienten, die er behandelt, und für die Maßnahmen, die er anordnet.

## Voraussetzungen im SAMS

- Approbierte Ärztin oder approbierter Arzt
- Klinische Erfahrung in Notaufnahme, Anästhesie oder Intensivmedizin
- Erfolgreicher Abschluss dieses Lehrgangs
- Regelmäßige Fortbildung in ALS, Trauma, Pädiatrie und Atemwegsmanagement

## Notarztindikationen (Auswahl)

- Bewusstlosigkeit oder schwere Bewusstseinsstörung
- Schwere Atemnot, Atemstillstand
- Kreislaufstillstand, schwerer Schock
- Akutes Koronarsyndrom, Schlaganfall mit instabilen Vitalwerten
- Schwere Verletzungen, Schuss- und Stichverletzungen am Rumpf
- Krampfanfall über 5 Minuten (Status epilepticus)
- Schwere Verbrennungen, Ertrinken, Stromunfall
- Starke Schmerzen, die eine BtM-Gabe erfordern

## Führungsaufgaben

1. **Teamführung** mit klarer Kommunikation (Closed Loop: Anordnung, Wiederholung, Bestätigung)
2. Entscheidung über Diagnostik, Therapie und Transportziel
3. Delegation von Maßnahmen an Notfallsanitäter nach deren Kompetenz
4. **Anordnung und Dokumentation** von BtM-Gaben
5. Entscheidung über Abbruch einer Reanimation

## Rechtliche Verantwortung

- Der Notarzt haftet für **Anordnungsverantwortung**, der Ausführende für die **Durchführungsverantwortung**.
- Ablehnung von Maßnahmen durch Patienten wird ärztlich aufgeklärt und im **Revers** festgehalten.
- Ärztliche Dokumente (Todesbescheinigung, Unterbringung, BtM-Rezept, Blutentnahme auf Anordnung) dürfen nur Ärzte ausstellen.

## Crew Resource Management

- Alle Teammitglieder dürfen Bedenken äußern („10-für-10“: 10 Sekunden innehalten für die nächsten 10 Minuten)
- Fehler werden offen angesprochen
- Der Notarzt holt aktiv Rückmeldung ein: *„Hat jemand Einwände?“*

*Merke:* Ein guter Notarzt hat die Hände frei und den Kopf klar.
MD],
            ['Erweiterte Diagnostik am Einsatzort', <<<'MD'
# Erweiterte Diagnostik am Einsatzort

Der Notarzt nutzt neben der klinischen Untersuchung erweiterte Diagnostik, um schneller zu einer Arbeitsdiagnose zu kommen und die richtige Zielklinik zu wählen.

## 12-Kanal-EKG

- Bei jedem Brustschmerz, Synkope, Rhythmusstörung, nach ROSC
- **STEMI-Kriterien:** ST-Hebung in mindestens zwei benachbarten Ableitungen, mindestens 1 mm in Extremitätenableitungen bzw. 2 mm in V2–V3
- **Infarktlokalisation:** II, III, aVF inferior; V1–V4 anterior; I, aVL, V5–V6 lateral
- Bei inferiorem Infarkt rechtsventrikuläre Ableitungen (V4r) schreiben, Vorsicht mit Nitro
- STEMI: Transport direkt ins **Herzkatheterlabor**, Ziel Door-to-Balloon unter 90 Minuten nach Erstkontakt

## Point-of-Care-Sonografie (POCUS)

Nach dem **eFAST-Protokoll** beim Trauma:
1. Rechter Oberbauch (Morison-Pouch): freie Flüssigkeit
2. Linker Oberbauch (Milz-Niere)
3. Becken (Douglas-Raum, Blase)
4. Subxiphoidal: Perikarderguss
5. Beidseits Thorax: **Lungengleiten** – fehlendes Gleiten spricht für Pneumothorax

In der Reanimation hilft die Sonografie, Tamponade, Pneumothorax oder fehlende Herzaktion zu erkennen. Die Untersuchung darf Kompressionspausen **nicht verlängern** (maximal 10 Sekunden).

## Blutgasanalyse und Labor

- Point-of-Care-BGA: pH, pCO2, Laktat, Kalium, Hämoglobin
- Laktat über 4 mmol/l als Schockhinweis
- Blutzucker bei jeder Bewusstseinsstörung

## Neurologische Diagnostik

- **BE-FAST** beim Schlaganfall: Balance, Eyes, Face, Arm, Speech, Time
- Zeitpunkt „zuletzt gesund gesehen“ dokumentieren
- Pupillen und GCS im Verlauf

## Temperatur

Kerntemperatur bei Hypothermie, Ertrinken oder Hitzschlag messen (epitympanal oder ösophageal).

*Merke:* Diagnostik muss Konsequenzen haben. Wenn ein Befund nichts an Therapie oder Zielklinik ändert, darf er den Transport nicht verzögern.
MD],
            ['Notfallnarkose', <<<'MD'
# Notfallnarkose

Die präklinische **Notfallnarkose** gehört zu den risikoreichsten Maßnahmen im Rettungsdienst. Sie wird nur durch den Notarzt mit einem eingespielten Team durchgeführt.

## Indikationen

- Schweres Schädel-Hirn-Trauma mit GCS 8 oder weniger
- Respiratorische Insuffizienz trotz nicht-invasiver Maßnahmen
- Polytrauma mit Schock
- Drohende Atemwegsverlegung (Inhalationstrauma, Angioödem)
- Status epilepticus, der auf Benzodiazepine nicht anspricht

## Vorbereitung nach Checkliste

1. **Patient:** Lagerung (Oberkörper 20–30 Grad, wenn möglich), Präoxygenierung mit 100 Prozent für 3 Minuten oder 8 tiefe Atemzüge, Zugang sicher
2. **Material:** Videolaryngoskop, Tubus, Bougie, SGA, Absaugung, Koniotomie-Set, Beatmungsgerät
3. **Medikamente:** aufgezogen, beschriftet, Dosis laut genannt
4. **Monitoring:** EKG, SpO2, RR alle 3 Minuten, Kapnografie
5. **Team:** Rollen verteilt, Plan A bis D besprochen

## Medikamentenwahl

- **Hämodynamisch instabil:** Esketamin 1 mg/kg (reduziert 0,5 mg/kg), Fentanyl zurückhaltend, Rocuronium 1,2 mg/kg
- **Stabil:** Propofol 1–2 mg/kg oder Etomidat 0,3 mg/kg, Fentanyl 2–3 µg/kg, Rocuronium oder Succinylcholin
- Noradrenalin bereithalten für Blutdruckabfall nach Einleitung

## Ablauf der RSI

1. Präoxygenierung
2. Gabe von Analgetikum, Hypnotikum und Relaxans rasch nacheinander
3. Keine Zwischenbeatmung (außer bei Hypoxie: vorsichtige Maskenbeatmung)
4. Intubation nach 45–60 Sekunden
5. Lagekontrolle mit Kapnografie
6. Narkoseerhaltung, Beatmungsgerät einstellen: Tidalvolumen 6–8 ml/kg Idealgewicht, PEEP 5 cmH2O, etCO2 35–40 mmHg

## Komplikationen

- **Hypotonie** – häufigste Komplikation
- Hypoxie bei langen Intubationsversuchen
- Aspiration
- Fehlintubation

*Merke:* Die gefährlichste Phase ist nicht die Intubation, sondern die Minuten danach. Blutdruck engmaschig kontrollieren.
MD],
            ['Kritische Patienten und Transportentscheidung', <<<'MD'
# Kritische Patienten und Transportentscheidung

Die Wahl der **richtigen Zielklinik** und des **richtigen Transportmittels** ist eine der wichtigsten Entscheidungen des Notarztes. Der falsche Zielort kann den Patienten mehr gefährden als jede Maßnahme vor Ort.

## Prinzipien

- **Geeignete Klinik vor nächster Klinik:** Ein Patient mit STEMI gehört in ein Herzkatheterlabor, ein Polytrauma in ein Traumazentrum.
- **Zeitkritische Diagnosen:** STEMI, Schlaganfall, Polytrauma, Sepsis – jede Minute zählt.
- Bei instabilen Patienten, die die geeignete Klinik nicht erreichen würden, erst Stabilisierung in der nächsten Klinik.

## Kliniken der SAMS

- **Pillbox Hill Medical Center (Los Santos):** Maximalversorgung, Schockraum, Herzkatheter, Neurochirurgie, Intensivstation
- **Sandy Shores:** Grundversorgung und Notaufnahme, Stabilisierung, Weiterverlegung per RTH
- **Paleto Bay:** Grundversorgung im Norden, Stabilisierung, Weiterverlegung

## Transportmittel

- **RTW mit Notarztbegleitung:** Standard für kritische Patienten im Stadtgebiet
- **RTH:** bei langen Strecken aus Blaine County oder Paleto Bay, schwerem Trauma in unwegsamem Gelände, Zeitvorteil von über 15 Minuten
- Nachteile RTH: Enger Raum, eingeschränkte Maßnahmen im Flug, Wetterabhängigkeit

## Transportfähigkeit herstellen

1. Atemweg gesichert
2. Kritische Blutungen gestoppt
3. Zugang gesichert, Medikamente verfügbar
4. Immobilisation, Wärmeerhalt
5. Monitoring vollständig

## Voranmeldung

Strukturierte Anmeldung mit Alter, Verdachtsdiagnose, Vitalwerten, Maßnahmen und Ankunftszeit. Bei Schockraum, Herzkatheter oder Stroke-Unit spezifische Alarmierung nutzen.

## Transportverweigerung kritischer Patienten

Lehnt ein kritischer Patient ab, prüft der Notarzt die Einwilligungsfähigkeit. Bei Einwilligungsfähigkeit: Aufklärung und **Revers**. Fehlt sie und besteht Lebensgefahr, wird nach mutmaßlichem Willen behandelt, ggf. mit Unterstützung der Polizei.

*Merke:* Stabilisieren so viel wie nötig, so wenig wie möglich – und dann los.
MD],
            ['Todesfeststellung und Leichenschau', <<<'MD'
# Todesfeststellung und Leichenschau

Die **Feststellung des Todes** ist eine ausschließlich ärztliche Aufgabe. Sie ist medizinisch, rechtlich und menschlich anspruchsvoll.

## Sichere Todeszeichen

1. **Totenflecken (Livores):** ab etwa 20–30 Minuten, zunächst wegdrückbar
2. **Totenstarre (Rigor mortis):** beginnt nach 2–4 Stunden am Kiefer, vollständig nach 6–12 Stunden
3. **Fäulnis**
4. **Mit dem Leben nicht vereinbare Verletzungen** (z. B. Dekapitation, Hirnaustritt)

Ohne sicheres Todeszeichen wird – außer bei einer erfolglosen, ausreichend langen Reanimation – **reanimiert**.

## Unsichere Todeszeichen

Atemstillstand, Pulslosigkeit, weite lichtstarre Pupillen, Abkühlung. Sie rechtfertigen allein **keine** Todesfeststellung.

## Abbruch der Reanimation

Abbruch durch den Notarzt erwägen bei:
- Asystolie über **20 Minuten** trotz ALS ohne reversible Ursache
- Bekanntem Willen des Patienten (Patientenverfügung)
- Ausnahmen beachten: Hypothermie, Intoxikation, Kinder

## Leichenschau

Die Leichenschau erfolgt am **vollständig entkleideten** Körper von Kopf bis Fuß, einschließlich Rücken, Körperöffnungen und Bindehäuten (Petechien).

## Todesart

- **Natürlich:** Tod infolge innerer Erkrankung, kein Hinweis auf äußere Einwirkung
- **Nicht natürlich:** Unfall, Suizid, Tötung, Vergiftung, Behandlungsfehler
- **Ungeklärt:** keine sichere Zuordnung möglich

Bei **nicht natürlichem oder ungeklärtem** Tod wird **immer die Polizei (LSPD/BCSO)** verständigt. Der Leichnam wird nicht verändert, die Einsatzstelle gilt als möglicher Tatort.

## Todesbescheinigung

Im System wird die **Todesbescheinigung** ausgestellt mit Personalien, Todeszeitpunkt (bzw. Auffindezeit), Todesursache, Todesart und sicheren Todeszeichen. Bei Unsicherheit niemals „natürlich“ ankreuzen.

## Angehörige

Die Todesnachricht wird ruhig, klar und ohne Floskeln überbracht. Das Wort *„verstorben“* wird ausgesprochen. Krisenintervention anbieten.

*Merke:* Im Zweifel nie natürlich – lieber einmal zu oft die Polizei einbeziehen.
MD],
        ],
        'questions' => [
            ['Welche sind sichere Todeszeichen? (Mehrere Antworten möglich)', ['Totenflecken', 'Totenstarre', 'Weite lichtstarre Pupillen', 'Mit dem Leben nicht vereinbare Verletzungen'], [0, 1, 3], 'Weite Pupillen sind ein unsicheres Todeszeichen.'],
            ['Was ist bei einem ungeklärten oder nicht natürlichen Tod zwingend zu tun?', ['Leichnam sofort zum Bestatter', 'Polizei verständigen', 'Todesart natürlich ankreuzen', 'Angehörige den Leichnam waschen lassen'], [1], 'Bei nicht natürlichem oder ungeklärtem Tod wird immer die Polizei verständigt.'],
            ['Was ist die häufigste Komplikation nach einer Notfallnarkose?', ['Hypertonie', 'Hypotonie', 'Hyperthermie', 'Bradypnoe ohne Folgen'], [1], 'Nach Narkoseeinleitung fällt der Blutdruck häufig ab.'],
            ['Welches Hypnotikum ist bei einem hämodynamisch instabilen Patienten bevorzugt?', ['Propofol in voller Dosis', 'Esketamin', 'Midazolam 15 mg', 'Keines'], [1], 'Esketamin ist kreislaufstabil und wird beim Schock bevorzugt.'],
            ['Welche Ableitungen zeigen einen inferioren Infarkt?', ['V1–V4', 'I, aVL, V5–V6', 'II, III, aVF', 'Nur aVR'], [2], 'Der inferiore Infarkt zeigt sich in II, III und aVF.'],
            ['Was spricht in der Thoraxsonografie für einen Pneumothorax?', ['Vorhandenes Lungengleiten', 'Fehlendes Lungengleiten', 'B-Linien beidseits', 'Normaler Herzbeutel'], [1], 'Fehlendes Lungengleiten ist ein Hinweis auf einen Pneumothorax.'],
            ['Wohin wird ein Patient mit STEMI transportiert?', ['In die nächstgelegene Klinik ohne Herzkatheter', 'Direkt in eine Klinik mit Herzkatheterlabor', 'Nach Hause mit Rezept', 'In die Psychiatrie'], [1], 'Geeignete Klinik vor nächster Klinik: STEMI gehört ins Herzkatheterlabor.'],
            ['Welche Dokumente dürfen nur Ärztinnen und Ärzte ausstellen? (Mehrere Antworten möglich)', ['Todesbescheinigung', 'BtM-Rezept', 'Unterbringung', 'Einsatzprotokoll'], [0, 1, 2], 'Das Einsatzprotokoll erstellt jedes Teammitglied.'],
            ['Wann kann ein Abbruch der Reanimation erwogen werden?', ['Nach 2 Minuten Asystolie', 'Bei Asystolie über 20 Minuten trotz ALS ohne reversible Ursache', 'Bei jedem Kind nach 5 Minuten', 'Bei Hypothermie unter 30 °C sofort'], [1], 'Bei Hypothermie, Intoxikation und Kindern gelten Ausnahmen.'],
            ['Was bedeutet Closed-Loop-Kommunikation?', ['Funk nur auf geschlossenem Kanal', 'Anordnung, Wiederholung durch den Empfänger, Bestätigung', 'Kommunikation nur schriftlich', 'Keine Rückfragen erlaubt'], [1], 'Die Anordnung wird wiederholt und bestätigt, um Fehler zu vermeiden.'],
        ],
    ],
    [
        'title' => 'Hygiene und Infektionsschutz',
        'description' => 'Hygienestandards im Rettungsdienst und in der Klinik zum Schutz von Patienten und Personal. Behandelt Händedesinfektion, Schutzausrüstung, Aufbereitung, Infektionstransporte und das Vorgehen nach Nadelstichverletzungen.',
        'hours' => 3, 'pass_percent' => 75, 'question_count' => 8, 'time_limit' => 15,
        'qualification' => null,
        'modules' => [
            ['Basishygiene und Händedesinfektion', <<<'MD'
# Basishygiene und Händedesinfektion

Die **Hände** sind der wichtigste Übertragungsweg von Krankheitserregern. Konsequente Händedesinfektion verhindert einen Großteil der Infektionen, die im Gesundheitswesen erworben werden.

## Voraussetzungen

- Kurze, unlackierte Fingernägel, **keine künstlichen Nägel**
- **Kein Schmuck** an Händen und Unterarmen (auch keine Eheringe, Uhren, Armbänder)
- Dienstkleidung mit kurzen Ärmeln oder hochgekrempelt
- Kleine Wunden an den Händen wasserdicht abdecken

## Die fünf Momente der Händedesinfektion (WHO)

1. **Vor** Patientenkontakt
2. **Vor** aseptischen Tätigkeiten (z. B. Venenzugang, Intubation)
3. **Nach** Kontakt mit potenziell infektiösem Material (Blut, Sekrete)
4. **Nach** Patientenkontakt
5. **Nach** Kontakt mit der unmittelbaren Patientenumgebung

## Durchführung

- Ausreichend Desinfektionsmittel: etwa **3–5 ml**, die Hände müssen vollständig benetzt sein
- Einwirkzeit: **30 Sekunden**
- Alle Flächen einreiben: Handflächen, Handrücken, Fingerzwischenräume, Daumen, Fingerkuppen, Handgelenke
- Besonders oft vergessen: **Daumen und Fingerkuppen**

## Händewaschen

Waschen ersetzt nicht die Desinfektion. Gewaschen wird bei sichtbarer Verschmutzung, nach Toilettengang und bei Kontakt mit **sporenbildenden Erregern** (z. B. Clostridioides difficile), da Alkohol Sporen nicht abtötet.

## Handschuhe

- Einmalhandschuhe bei möglichem Kontakt mit Blut und Körperflüssigkeiten
- Handschuhe ersetzen **nicht** die Händedesinfektion: vor dem Anziehen und nach dem Ausziehen desinfizieren
- Zwischen Patienten Handschuhe wechseln
- Mit kontaminierten Handschuhen keine sauberen Flächen (Tablet, Lenkrad, Funkgerät) berühren

## Hautschutz

Regelmäßiges Eincremen mit Hautschutzpräparaten verhindert Risse – rissige Haut ist eine Eintrittspforte für Erreger.

*Merke:* Hygiene beginnt mit sauberen Händen – 30 Sekunden, jedes Mal.
MD],
            ['Persönliche Schutzausrüstung', <<<'MD'
# Persönliche Schutzausrüstung

Die **Persönliche Schutzausrüstung (PSA)** schützt Mitarbeitende vor Infektionen und Verletzungen. Sie wird je nach Gefährdung ausgewählt.

## Bestandteile

- **Einmalhandschuhe:** bei jedem Patientenkontakt mit möglichem Kontakt zu Körperflüssigkeiten
- **Schutzkittel:** flüssigkeitsdicht bei Spritzgefahr oder bekannter Infektion
- **Mund-Nasen-Schutz (MNS):** schützt den Patienten vor Tröpfchen des Personals
- **FFP2- oder FFP3-Maske:** schützt das Personal vor luftübertragenen Erregern (z. B. Tuberkulose, Masern, Influenza)
- **Schutzbrille oder Visier:** bei Spritzgefahr, Absaugung, Intubation
- **Schutzhelm und Sicherheitsschuhe** für technische Rettungen
- **Warnweste** im Straßenverkehr

## Reihenfolge beim Anlegen

1. Händedesinfektion
2. Schutzkittel
3. Atemschutzmaske (Dichtsitz prüfen)
4. Schutzbrille oder Visier
5. Handschuhe über die Kittelbündchen

## Reihenfolge beim Ablegen

Das Ablegen ist der gefährlichste Moment, da es dabei häufig zur Kontamination kommt.
1. Handschuhe ausziehen
2. Händedesinfektion
3. Schutzkittel von innen nach außen abrollen
4. Händedesinfektion
5. Schutzbrille von hinten abnehmen
6. Maske an den Bändern abnehmen, nicht an der Vorderseite berühren
7. Abschließende Händedesinfektion

## Auswahl nach Übertragungsweg

- **Kontakt** (z. B. MRSA, Noroviren): Handschuhe, Kittel
- **Tröpfchen** (z. B. Influenza): zusätzlich MNS bzw. FFP2
- **Aerogen** (z. B. Tuberkulose, Masern): FFP2 oder FFP3, Patient trägt MNS

## Patient

Wenn möglich, erhält auch der Patient einen MNS, um die Umgebung zu schützen.

*Merke:* PSA wirkt nur, wenn sie richtig angelegt und vor allem richtig abgelegt wird.
MD],
            ['Desinfektion von Fahrzeug und Material', <<<'MD'
# Desinfektion von Fahrzeug und Material

Nach jedem Einsatz muss das Fahrzeug so aufbereitet sein, dass der nächste Patient **keinem Infektionsrisiko** ausgesetzt ist.

## Routinedesinfektion nach jedem Einsatz

- **Wischdesinfektion** aller patientennahen Flächen: Trage, Haltegriffe, Monitor, Kabel, Blutdruckmanschette, Pulsoxymeter
- Sichtbare Verschmutzungen zuerst mit getränktem Tuch aufnehmen, dann desinfizieren
- Einwirkzeit des Desinfektionsmittels laut Herstellerangabe beachten
- Einmalmaterial verwerfen, Mehrwegmaterial aufbereiten

## Schlussdesinfektion

Nach Transport eines Patienten mit **meldepflichtiger oder hochansteckender Erkrankung**:
1. Fahrzeug außer Dienst nehmen (**Status 6**)
2. Alle Flächen im Patientenraum, auch Decke, Schränke und Türen, mit geeignetem Mittel (z. B. **viruzid** bei Noroviren) desinfizieren
3. Geräte und Taschen, die im Einsatz waren, komplett aufbereiten
4. Wäsche in geschlossenen Säcken als infektiös kennzeichnen
5. Erst nach Abtrocknen und Einwirkzeit wieder in Dienst

## Medizinprodukte

- **Unkritisch** (Kontakt mit intakter Haut): Wischdesinfektion
- **Semikritisch** (Kontakt mit Schleimhaut, z. B. Laryngoskopspatel): Desinfektion mit viruzidem Mittel oder Einmalprodukt
- **Kritisch** (Durchdringung der Haut): steril oder Einmalprodukt

## Abfall

- **Spitze und scharfe Gegenstände** sofort in den durchstichsicheren Abwurfbehälter, nie recappen
- Blutige Abfälle in reißfeste, dichte Säcke
- Abwurfbehälter nur bis zur Markierung füllen

## Dokumentation

Durchgeführte Schlussdesinfektionen werden mit Datum, Fahrzeug, Erreger und Mittel dokumentiert.

## Fahrerraum

Mit kontaminierten Handschuhen nicht in den Fahrerraum. Lenkrad, Funk und Tablet regelmäßig desinfizieren.

*Merke:* Der nächste Patient vertraut darauf, dass du nach dem letzten gründlich warst.
MD],
            ['Infektionstransport', <<<'MD'
# Infektionstransport

Ein **Infektionstransport** ist der Transport eines Patienten mit einer übertragbaren Erkrankung oder mit multiresistenten Erregern. Ziel ist der Schutz des Personals, anderer Patienten und der Öffentlichkeit.

## Typische Erreger

- **Multiresistente Erreger:** MRSA, VRE, MRGN – Übertragung über Kontakt
- **Noroviren:** Erbrechen und Durchfall, hoch ansteckend über Kontakt und Aerosole
- **Influenza und andere Atemwegsviren:** Tröpfchen
- **Tuberkulose, Masern, Windpocken:** aerogen
- **Hochkontagiöse lebensbedrohliche Erkrankungen** (z. B. hämorrhagisches Fieber): Sonderisolierung, Sonderfahrzeug

## Vorbereitung

1. Informationen bei Alarmierung oder Übergabe einholen: Welcher Erreger? Welcher Übertragungsweg?
2. Nicht benötigtes Material aus dem Patientenraum entfernen oder abdecken
3. PSA nach Übertragungsweg anlegen
4. Zielklinik vorab informieren, damit ein **Isolierzimmer** bereitsteht

## Während des Transports

- Nur so viele Mitarbeitende wie nötig im Patientenraum
- Der Fahrer bleibt **ohne Patientenkontakt** und ohne kontaminierte PSA
- Trennscheibe oder Tür zum Fahrerraum geschlossen
- Patient trägt, wenn möglich, MNS
- Keine Begleitpersonen ohne Schutz

## Übergabe

- Übergabe direkt im Isolierbereich der Klinik
- Hinweis auf den Erreger auch in der **Patientenakte** als Akteneintrag
- Weitergabe von Informationen über die Infektion nur an beteiligte Behandler, Schweigepflicht beachten

## Nach dem Transport

- PSA korrekt ablegen, Händedesinfektion
- **Schlussdesinfektion** des Fahrzeugs je nach Erreger
- Dokumentation im Einsatzprotokoll

## Unerwarteter Infektionsverdacht

Wird erst während des Einsatzes eine Infektion vermutet, wird die PSA sofort angepasst und die Leitstelle informiert. Kontaktpersonen werden notiert.

*Merke:* Information ist der beste Schutz. Frag nach dem Erreger, bevor du den Patientenraum betrittst.
MD],
            ['Nadelstichverletzung und Meldewege', <<<'MD'
# Nadelstichverletzung und Meldewege

**Nadelstich- und Schnittverletzungen** mit gebrauchten Instrumenten gehören zu den häufigsten Arbeitsunfällen im Gesundheitswesen. Hauptrisiken sind Infektionen mit **Hepatitis B, Hepatitis C und HIV**.

## Übertragungsrisiko nach Stich mit infektiösem Blut

- Hepatitis B: bis zu **30 Prozent** bei Ungeimpften
- Hepatitis C: etwa **0,5–3 Prozent**
- HIV: etwa **0,3 Prozent**

Die Hepatitis-B-Impfung ist daher für alle Mitarbeitenden Pflicht.

## Sofortmaßnahmen

1. **Blutfluss fördern:** Wunde nicht quetschen, aber ausbluten lassen (mindestens 1 Minute)
2. **Desinfizieren:** Wunde intensiv mit alkoholischem Desinfektionsmittel für mehrere Minuten spülen oder getränkten Tupfer auflegen
3. Bei Spritzern ins **Auge:** sofort mit reichlich Wasser oder Kochsalzlösung spülen
4. Bei Kontakt mit **Mundschleimhaut:** mehrfach mit Wasser oder Desinfektionslösung ausspülen

## Danach

- **Sofort** die Notaufnahme im Pillbox Hill Medical Center oder den Durchgangsarzt aufsuchen
- **HIV-Postexpositionsprophylaxe (PEP)** möglichst innerhalb von **2 Stunden**, spätestens nach 72 Stunden
- Blutentnahme beim Mitarbeitenden (Ausgangsstatus)
- Untersuchung des Indexpatienten auf HIV, HBV, HCV nur **mit dessen Einwilligung**

## Meldewege

1. Information an die **Schicht- oder Wachenleitung**
2. Dokumentation als **Arbeitsunfall** im System mit Uhrzeit, Hergang, Instrument, Indexpatient (als Fallnummer)
3. Meldung an den Betriebsarzt
4. Nachkontrollen nach 6 Wochen, 3 und 6 Monaten

## Prävention

- Kein **Recapping** von Kanülen
- Sicherheitskanülen verwenden
- Abwurfbehälter immer in Reichweite
- Ruhiges Arbeiten auch unter Zeitdruck, besonders bei unruhigen Patienten im fahrenden RTW

## Schweigepflicht

Der Infektionsstatus des Indexpatienten unterliegt der Schweigepflicht und darf nur den behandelnden Ärzten des Mitarbeitenden mitgeteilt werden.

*Merke:* Bluten, desinfizieren, sofort melden – Zeit ist entscheidend für die PEP.
MD],
        ],
        'questions' => [
            ['Wie lange beträgt die Einwirkzeit bei der hygienischen Händedesinfektion?', ['5 Sekunden', '15 Sekunden', '30 Sekunden', '3 Minuten'], [2], 'Die Einwirkzeit beträgt 30 Sekunden.'],
            ['Welche gehören zu den fünf Momenten der Händedesinfektion? (Mehrere Antworten möglich)', ['Vor Patientenkontakt', 'Vor aseptischen Tätigkeiten', 'Nach Patientenkontakt', 'Vor dem Betanken des Fahrzeugs'], [0, 1, 2], 'Das Betanken gehört nicht zu den fünf WHO-Momenten.'],
            ['Wann ist Händewaschen statt nur Desinfektion notwendig?', ['Vor jeder Injektion', 'Bei Kontakt mit sporenbildenden Erregern wie Clostridioides difficile', 'Nie', 'Nur zum Dienstende'], [1], 'Alkohol tötet Sporen nicht ab, daher zusätzlich waschen.'],
            ['Welche Maske schützt das Personal vor aerogen übertragenen Erregern wie Tuberkulose?', ['Mund-Nasen-Schutz', 'FFP2- oder FFP3-Maske', 'Stoffmaske', 'Keine Maske nötig'], [1], 'Nur FFP-Masken schützen den Träger zuverlässig vor Aerosolen.'],
            ['Was wird beim Ablegen der PSA zuerst ausgezogen?', ['Maske', 'Schutzbrille', 'Handschuhe', 'Schutzkittel'], [2], 'Zuerst die stark kontaminierten Handschuhe, dann Händedesinfektion.'],
            ['Was ist nach dem Transport eines Patienten mit Noroviren erforderlich?', ['Nur Trage abwischen', 'Schlussdesinfektion mit viruzidem Mittel', 'Fahrzeug lüften genügt', 'Keine Maßnahme'], [1], 'Noroviren erfordern eine Schlussdesinfektion mit viruzidem Mittel.'],
            ['Wie werden gebrauchte Kanülen entsorgt?', ['Schutzkappe aufsetzen und in den Müll', 'Sofort ohne Recapping in den durchstichsicheren Abwurfbehälter', 'In der Jackentasche sammeln', 'In den normalen Restmüll'], [1], 'Recapping ist verboten, Kanülen gehören sofort in den Abwurfbehälter.'],
            ['Was ist die richtige Sofortmaßnahme nach einer Nadelstichverletzung?', ['Wunde sofort zunähen', 'Blutfluss fördern und intensiv desinfizieren', 'Abwarten bis Schichtende', 'Nur ein Pflaster aufkleben'], [1], 'Ausbluten lassen und intensiv desinfizieren, dann sofort melden.'],
            ['Innerhalb welcher Zeit sollte eine HIV-Postexpositionsprophylaxe idealerweise begonnen werden?', ['Innerhalb von 2 Stunden', 'Innerhalb von 7 Tagen', 'Nach 4 Wochen', 'Nur bei Symptomen'], [0], 'Die PEP wirkt am besten innerhalb von 2 Stunden, spätestens nach 72 Stunden.'],
            ['Was gilt für den Fahrer bei einem Infektionstransport? (Mehrere Antworten möglich)', ['Er hat keinen Patientenkontakt', 'Er trägt keine kontaminierte PSA im Fahrerraum', 'Die Tür zum Patientenraum bleibt geschlossen', 'Er hilft mit denselben Handschuhen beim Umlagern und fährt dann'], [0, 1, 2], 'Der Fahrerraum bleibt sauber, der Fahrer ohne Patientenkontakt.'],
        ],
    ],
];
