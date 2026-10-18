<?php
/**
 * Handbuch für neu angelegte Fraktionen (Polizei, Justiz, Feuerwehr, Verwaltung).
 * Gemeinsame Seiten plus je Art eine Seite zu den Fachfunktionen.
 */
$all = [
    'Handbuch: Erste Schritte' => <<<'MD'
Willkommen! Hier stehen die wichtigsten Abläufe. Passe die Seiten gern an eure Dienstvorschriften an.

## Anmelden
- Im Browser mit Benutzername und Passwort, im Spiel über das **Tablet** mit denselben Daten.
- Unter *Mein Profil* einmal die **Unterschrift** hinterlegen – sie erscheint auf allen Dokumenten.

## Oberfläche
- Links das Menü (nur, was dein Rang darf), oben die **Suche** und die **Glocke** mit Benachrichtigungen.
- **Im Dienst / Außer Dienst** stempelt deine Dienstzeit (im Spiel `/dienst`).

## Weiter
- [[Handbuch: Dokumente]]
- [[Handbuch: Dienst und Organisation]]
- [[Handbuch: Zusammenarbeit mit anderen Behörden]]
MD,
    'Handbuch: Dokumente' => <<<'MD'
## Ausstellen
1. *Neues Dokument* → Dokumentart wählen.
2. Person wählen (falls nötig), Felder ausfüllen, **Vorschau** prüfen, **Ausstellen**.
3. Das Dokument bekommt Nummer, Prüfcode und QR-Code. Jede/r kann die Echtheit prüfen.

## Danach
- Ausgestellte Dokumente werden nicht geändert: **Nachtrag** oder **Stornierung** mit Begründung.
- **Im Spiel drucken** und am Drucker abholen – das Dokument ist dann ein Item.
- **Freigeben** an eine andere Behörde oder per geheimem Link.

## Eigene Vorlagen
- Die Leitung legt im **Vorlagen-Editor** eigene Dokumentarten an (Felder, Layout, Text mit Platzhaltern).
MD,
    'Handbuch: Dienst und Organisation' => <<<'MD'
- **Dienstzeiten**: stempeln im Browser, im Tablet oder mit `/dienst`; Korrekturen durch die Leitung.
- **Dienstplan**: in Schichten eintragen, zum Tausch anbieten, übernehmen. Erinnerung eine Stunde vorher.
- **Ausbildung**: Lehrgänge mit Online-Prüfung (Theorie). Qualifikation und Zertifikat gibt es erst nach der praktischen Prüfung bei der Ausbildung.
- **Schwarzes Brett** und **Nachrichten** für interne Kommunikation.
- **Personalakte**: Laufbahn, Beförderungen, Abmahnungen – die eigene Akte siehst du selbst.
MD,
    'Handbuch: Zusammenarbeit mit anderen Behörden' => <<<'MD'
## Behördenanfragen
- Unter *Behördenanfragen* stellst du eine Anfrage an eine andere Fraktion (z. B. Polizei → Rettungsdienst: Blutentnahme, Justiz → Polizei: Ermittlungsbericht).
- Die andere Seite beantwortet sie und kann Dokumente direkt freigeben. Den Stand siehst du jederzeit.

## Freigaben
- Ein Dokument kann für eine ganze Behörde freigegeben werden. Medizinische Daten sieht eine andere Behörde nur, wenn sie ausdrücklich freigegeben wurden.

## Fahndung und Haftbefehle
- Von der Justiz erlassene Haftbefehle erscheinen automatisch in der Fahndung der Polizei.
MD,
];
return [
    '_all' => $all,
    'police' => $all + [
        'Handbuch: Polizeiarbeit' => <<<'MD'
## Personenakten
- Jede Person hat eine Akte mit Aliasnamen, Gefährdungshinweisen, Führerschein-/Waffenschein-Status und Fahrzeugen.
- **Einträge** dokumentieren Kontrollen, Vernehmungen, Festnahmen, Durchsuchungen und Sicherstellungen.
- **Vorgänge** bündeln alles zu einem Ermittlungsverfahren.

## Dokumente
- Strafanzeige, Festnahme-, Durchsuchungs-, Sicherstellungs- und Vernehmungsprotokoll, Unfallbericht, Bußgeldbescheid, Platzverweis, Ermittlungsbericht.
- **Bußgeldbescheide** nutzen den *Bußgeld- und Strafkatalog*; Zahlungen werden wie Rechnungen verfolgt.

## Fahndung
- Personen und Fahrzeuge (Kennzeichen) zur Fahndung ausschreiben, mit Gefährlichkeit und Grund.
- Haftbefehle der Justiz erscheinen automatisch; nach der Festnahme die Fahndung erledigen.
- Im Spiel kann die Fahndung über die Schnittstelle (z. B. Kennzeichenabfrage) abgefragt werden.
MD,
    ],
    'justice' => $all + [
        'Handbuch: Justiz' => <<<'MD'
## Verfahren
- Ein **Verfahren** bündelt Anklage, Beschlüsse, Ladungen und Urteil zu einer Person.
- Die Polizei kann Ermittlungsberichte und Protokolle für die Justiz freigeben oder über eine Anfrage liefern.

## Dokumente
- Anklageschrift, Strafbefehl, Urteil, Haftbefehl, Durchsuchungsbeschluss, Ladung, Beschluss, Vollmacht.
- **Haftbefehle** erscheinen automatisch in der Fahndung aller Polizeibehörden; wird der Haftbefehl storniert, endet die Fahndung.
MD,
    ],
    'fire' => $all + [
        'Handbuch: Feuerwehr' => <<<'MD'
## Dokumente
- Einsatzbericht (Brand / Technische Hilfe), Brandschaubericht, Brandschutzbescheinigung, Gebührenbescheid.
- Verletzte Personen übergebt ihr an den Rettungsdienst – dort entsteht die Patientenakte.
MD,
    ],
    'rescue' => $all + [
        'Handbuch: Spezialrettung' => <<<'MD'
## Aufgaben
Gelände-, Wasser- und Luftrettung, Vermisstensuche, Bergung und Absicherung (z. B. Strandwache).

## Dokumente
- Einsatzbericht Spezialrettung, Suchprotokoll, Tauchprotokoll, Flugbericht, Bergungsprotokoll, Wachbericht, Kostenbescheid.
- Gerettete Personen übergebt ihr an den Rettungsdienst (SAMS) – dort entsteht die Patientenakte. Für größere Lagen gibt es gemeinsame Einsatzberichte.
- Flugbericht mit Kennung und Flugzeit trägt den Flug automatisch ins **Flugbuch** ein.

## Tauglichkeit
- Unter **Tauglichkeit** siehst du, welche Untersuchungen (Atemschutz, Tauchen, Höhe, Luftrettung, Wasser) gültig sind und wann sie ablaufen.
- Die Leitung fordert Untersuchungen beim Rettungsdienst an. Das Ergebnis kommt automatisch – die Befunde bleiben beim Arzt (Schweigepflicht).
- Damit die Zuordnung klappt, trägt die Leitung am Konto die **Bürger-ID** aus dem Spiel ein.
- Der Rettungsdienst untersucht mit dem **Untersuchungsbogen Sonderverwendungsfähigkeit** nach unserem Standard (Anforderungen je Verwendung stehen unter *Tauglichkeit*). Die Leitung erhält die **Bescheinigung über die Sonderverwendungsfähigkeit** mit Profil (PULHES), Beurteilung je Verwendung und Einschränkungen.
MD,
    ],
    'government' => $all + [
        'Handbuch: Verwaltung' => <<<'MD'
## Dokumente
- Bescheid, Genehmigung / Lizenz, Gewerbeanmeldung, Bescheinigung, Gebührenbescheid.
- Bürgerakten sammeln Anträge und Bescheide zu einer Person.
MD,
    ],
];
