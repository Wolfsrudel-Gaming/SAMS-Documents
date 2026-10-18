<?php
/** Ausbildungsinhalte TAS (Spezialrettung Gelände, Wasser, Luft): Lehrgänge mit Lernmodulen und Prüfungsfragen. */
return [
    [
        'title' => 'Grundausbildung Spezialrettung (TAS)',
        'description' => 'Basislehrgang für alle Einsatzkräfte der TAS. Vermittelt Aufgaben und Einsatzarten der Spezialrettung in Gelände, Wasser und Luft, Alarmierung, Funk und FMS-Status, Eigenschutz, die Zusammenarbeit mit SAMS, LSFD und LSPD sowie die Einsatzdokumentation im System.',
        'hours' => 8, 'pass_percent' => 70, 'question_count' => 10, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Aufgaben und Einsatzarten der TAS', <<<'MD'
# Aufgaben und Einsatzarten der TAS

Die **TAS** ist die Spezialrettung des Staates San Andreas. Sie kommt immer dann zum Einsatz, wenn der reguläre Rettungsdienst **SAMS** einen Patienten nicht oder nicht sicher erreichen kann: im unwegsamen Gelände, auf und unter dem Wasser sowie aus der Luft. Vorbild sind die deutschen Organisationen **Bergwacht**, **DLRG**, **Wasserwacht** und die **Luftrettung**.

## Grundauftrag

Die TAS hat drei Kernaufgaben:

1. **Erreichen** – den Patienten trotz schwieriger Umgebung sicher erreichen.
2. **Versorgen** – medizinische Erstversorgung unter erschwerten Bedingungen.
3. **Transportieren** – den Patienten an einen Übergabepunkt bringen, an dem der Rettungsdienst übernehmen kann.

Die TAS ersetzt den Rettungsdienst nicht, sondern **ergänzt** ihn. Die endgültige Versorgung erfolgt im Krankenhaus, in der Regel im **Pillbox Hill Medical Center**.

## Die drei Einsatzarten

| Einsatzart | Typische Lagen | Einsatzorte |
|---|---|---|
| **Gelände** | Absturz, Verletzung beim Wandern, Fahrzeug im Steilhang | Mount Chiliad, Raton Canyon, Mount Gordo, Tataviam Mountains |
| **Wasser** | Badeunfall, Ertrinken, gekentertes Boot, Person in Strömung | Vespucci Beach, Del Perro Pier, Alamo Sea, Zancudo River, Hafen |
| **Luft** | Windenrettung, schneller Transport, Suche aus der Luft | gesamter Staat, Schwerpunkt Blaine County |

Viele Einsätze sind **kombiniert**: Eine Person stürzt im Raton Canyon in den Fluss – hier werden Geländeretter, Strömungsretter und gegebenenfalls der Hubschrauber gleichzeitig benötigt.

## Qualifikationen innerhalb der TAS

Nach der Grundausbildung können sich Einsatzkräfte spezialisieren:

- **Bergretter/in** – Seiltechnik und Geländerettung
- **Rettungsschwimmer/in** – Strandwache und Strömungsrettung
- **Rettungstaucher/in** – Suche und Rettung unter Wasser
- **Bootsführer/in** – Führen des Rettungsbootes
- **Luftretter/in** – Windenoperator und Luftrettung

Ergänzend gibt es den Lehrgang **Suche und Ortung** für Vermisstenlagen.

## Grundsätze

- **Eigenschutz vor Fremdschutz:** Ein verletzter Retter ist ein weiterer Patient.
- **Nur qualifiziert handeln:** Wer keine Taucherqualifikation hat, taucht nicht. Wer keine Seilausbildung hat, seilt nicht ab.
- **Teamarbeit:** Spezialrettung wird niemals allein durchgeführt. Das kleinste Einsatzelement ist der **Trupp aus zwei Kräften**.

*Merke:* Die TAS bringt den Patienten aus der Gefahr – der Rettungsdienst bringt ihn ins Krankenhaus.
MD],
            ['Alarmierung, Funk und FMS-Status', <<<'MD'
# Alarmierung, Funk und FMS-Status

## Alarmierung

Notrufe gehen über die **911** bei der Leitstelle ein. Die Leitstelle entscheidet anhand des Meldebildes, ob neben SAMS auch die TAS alarmiert wird. Typische Stichworte sind:

- **„Person abgestürzt / Gelände“** – Geländerettung
- **„Person im Wasser“** – Wasserrettung, Rettungsschwimmer und Boot
- **„Person unter Wasser“** – zusätzlich Rettungstaucher
- **„Windenrettung“** – Hubschrauber mit Luftretter
- **„Vermisste Person“** – Suche und Ortung

Die Einsatzkräfte erhalten die Alarmierung über den Pager bzw. das Tablet. Wichtig ist eine **schnelle Rückmeldung** über den Status, damit die Leitstelle weiß, wer ausrückt.

## FMS-Status

Der **Funkmeldestatus (FMS)** ermöglicht kurze, eindeutige Meldungen ohne Sprechfunk.

| Status | Bedeutung |
|---|---|
| **1** | Einsatzbereit über Funk (unterwegs) |
| **2** | Einsatzbereit auf der Wache |
| **3** | Einsatz übernommen / Anfahrt |
| **4** | Am Einsatzort eingetroffen |
| **5** | Sprechwunsch |
| **6** | Nicht einsatzbereit |
| **7** | Patient aufgenommen / Transport |
| **8** | Am Ziel (Übergabepunkt oder Krankenhaus) angekommen |

Für die TAS bedeutet **Status 7**, dass der Patient gerettet und in der Obhut des Teams ist, etwa nach der Aufnahme an Bord des Bootes oder des Hubschraubers. **Status 8** wird gemeldet, wenn der Übergabepunkt erreicht ist.

## Sprechfunk

Funksprüche folgen dem Schema **„Wer ruft wen – Nachricht – Ende“**. Beispiel:

> „Leitstelle von TAS 1-1, Status 4, Raton Canyon, Höhe Brücke. Eine Person ca. 30 Meter unterhalb des Weges, ansprechbar. Wir beginnen mit dem Seilaufbau. Kommen.“

Regeln:

- **Kurz und klar** sprechen, erst denken, dann drücken.
- **Ortsangaben** mit Bezugspunkt angeben (Straße, Brücke, Pier, Gipfel).
- **Lagemeldungen** bei Eintreffen, bei Veränderungen und bei Einsatzende.
- **Nachforderungen** frühzeitig stellen, denn Hubschrauber und Boot brauchen Anlaufzeit.

## Funk im Gelände und am Wasser

In Schluchten und hinter Bergen kann der Funk abreißen. Dann wird ein **Relais** gebildet: Eine Einsatzkraft bleibt an einer erhöhten Stelle und gibt Meldungen weiter. Am Wasser werden Funkgeräte **wasserdicht** getragen.

*Merke:* Status 4 sagt der Leitstelle „Wir sind da“, die Lagemeldung folgt immer per Sprechfunk.
MD],
            ['Eigenschutz und Sicherheit im Einsatz', <<<'MD'
# Eigenschutz und Sicherheit im Einsatz

Die Spezialrettung findet dort statt, wo bereits ein Mensch verunglückt ist. Die Gefahr, die den Patienten getroffen hat, bedroht auch die Retter. Deshalb gilt ohne Ausnahme: **Eigenschutz vor Fremdschutz.**

## Gefahren nach Einsatzart

**Gelände:**
- Absturz, Steinschlag, lockerer Untergrund
- Dunkelheit, Hitze in der Grand Senora Desert, Kälte am Mount Chiliad
- Erschöpfung durch lange Anmarschwege

**Wasser:**
- Ertrinken des Retters durch Umklammerung des Patienten
- Strömung, Walzen und Unterströmungen
- Unterkühlung, schlechte Sicht, Treibgut

**Luft:**
- Rotoren und Heckrotor
- Downwash (starker Abwind) mit fliegenden Gegenständen
- Absturz aus der offenen Kabine

## Persönliche Schutzausrüstung

Die PSA richtet sich nach dem Einsatz:

| Einsatz | Mindestausrüstung |
|---|---|
| Gelände | Helm, Handschuhe, Sitzgurt, feste Schuhe, Stirnlampe |
| Wasser (Ufer) | Rettungsweste, Helm, Wurfsack, Messer |
| Wasser (im Wasser) | Kälteschutzanzug, Rettungsweste mit Schnellbefreiung, Flossen |
| Luft | Helm mit Gehörschutz, Gurt, Schutzbrille, Handschuhe |

**Ohne passende PSA wird kein Einsatz begonnen.** Wer am Ufer einer reißenden Strömung steht, trägt eine Rettungsweste, auch wenn er nicht ins Wasser geht.

## Die Rettungsreihenfolge

In der Wasserrettung gilt die bewährte Reihenfolge **„Reden – Reichen – Werfen – Rudern – Schwimmen – Tauchen“**. Je weiter hinten eine Methode steht, desto höher ist das Risiko für den Retter. Zuerst wird immer versucht, den Patienten vom sicheren Standort aus zu erreichen.

## Abbruchkriterien

Jeder Einsatzleiter und jede Einsatzkraft darf einen Einsatz unterbrechen, wenn die Gefahr nicht mehr beherrschbar ist, etwa bei Gewitter, einsetzendem Steinschlag oder steigender Strömung. Ein Einsatzabbruch ist **keine Schwäche**, sondern verantwortungsvolles Handeln.

## Gesundheit

- Ausreichend trinken, besonders in Blaine County.
- Nach belastenden Einsätzen das Angebot der **Einsatznachsorge** nutzen.
- Verletzungen im Einsatz immer melden und dokumentieren.

*Merke:* Erst sichern, dann retten. Ein Retter, der selbst verunglückt, bindet alle Kräfte.
MD],
            ['Zusammenarbeit mit SAMS, LSFD und LSPD', <<<'MD'
# Zusammenarbeit mit SAMS, LSFD und LSPD

Kaum ein Einsatz der TAS wird von ihr allein abgearbeitet. Eine gute Zusammenarbeit entscheidet über Zeit und damit über das Leben des Patienten.

## SAMS – Rettungsdienst

Der Rettungsdienst **SAMS** übernimmt die notfallmedizinische Versorgung und den Transport ins Krankenhaus.

- Die TAS bringt den Patienten zu einem **Übergabepunkt**, z. B. an den Parkplatz am Fuß des Mount Chiliad, an den Steg am Del Perro Pier oder auf das **Dach des Pillbox Hill Medical Center**.
- Bei der Übergabe wird nach dem **SINNHAFT**- oder **ABCDE-Schema** berichtet: Was ist passiert, welche Verletzungen, welche Maßnahmen, wie lange im Wasser bzw. in der Kälte.
- Ist ein Notarzt vor Ort, entscheidet er über die medizinischen Maßnahmen.

## LSFD – Feuerwehr

Das **Los Santos Fire Department** unterstützt mit:

- technischer Rettung, z. B. bei Fahrzeugen im Steilhang
- Ausleuchtung der Einsatzstelle
- Drehleiter als Anschlagpunkt oder für Rettungen von oben
- Absicherung bei Bränden im Gelände

Bei gemeinsamen Einsätzen wird eine **gemeinsame Einsatzleitung** gebildet. Jede Organisation führt ihre eigenen Kräfte, Absprachen laufen über die Einsatzleiter.

## LSPD und BCSO – Polizei

Die Polizei ist zuständig für:

- **Absperrung** und Verkehrsregelung, etwa an der Great Ocean Highway
- **Vermisstenfälle** – die Polizei führt das Ermittlungsverfahren, die TAS unterstützt mit Suchkräften
- **Todesfälle und Leichenbergung** – bei einem Verstorbenen wird stets die Polizei informiert, da eine Straftat nicht ausgeschlossen werden kann
- Sicherung von Beweismitteln

Die TAS verändert an einem möglichen Tatort nur das, was zur Rettung unbedingt nötig ist, und dokumentiert diese Veränderungen.

## Einsatzleitung

Bei größeren Lagen gibt es einen **Einsatzleiter TAS**. Er:

- erkundet die Lage und legt die Rettungsmethode fest
- teilt Trupps und Aufgaben zu
- hält Verbindung zur Leitstelle und zu den anderen Organisationen
- entscheidet über Nachforderung und Abbruch

## Kommunikation

Gemeinsame Funkkanäle werden zu Beginn festgelegt. Fachbegriffe der TAS (z. B. „Flaschenzug“, „Signalmann“) sind anderen Organisationen nicht immer bekannt – deshalb **einfach und eindeutig** sprechen.

*Merke:* Jede Organisation hat ihre Stärken. Wer die Zuständigkeiten kennt, verliert keine Zeit.
MD],
            ['Dokumentation im System', <<<'MD'
# Dokumentation im System

Jeder Einsatz der TAS wird **vollständig und zeitnah** im Dokumentensystem erfasst. Die Dokumentation dient der Qualitätssicherung, dem Rechtsschutz der Einsatzkräfte und der Übergabe an den Rettungsdienst.

## Einsatzbericht

Der Einsatzbericht enthält mindestens:

- **Einsatznummer, Datum und Uhrzeiten** (Alarm, Status 3, Status 4, Status 7, Status 8, Einsatzende)
- **Einsatzort** möglichst genau, z. B. „Raton Canyon, 200 m flussabwärts der Eisenbahnbrücke“
- **Einsatzart** (Gelände, Wasser, Luft, Suche)
- **Beteiligte Kräfte** und Fahrzeuge, Boot oder Hubschrauber
- **Lage beim Eintreffen** und **durchgeführte Maßnahmen**
- **Patientendaten**, soweit bekannt, und Übergabe an wen
- **Besonderheiten**, z. B. Einsatzabbruch, Verletzung eines Retters, Materialschaden

## Spezielle Protokolle

Je nach Einsatz werden zusätzliche Protokolle angelegt:

| Protokoll | Inhalt |
|---|---|
| **Tauchprotokoll** | Taucher, Signalmann, Tauchzeit, Tiefe, Luftverbrauch |
| **Suchprotokoll** | Suchsektoren, Suchtrupps, Zeiten, Ergebnisse |
| **Windenprotokoll** | Anzahl der Windengänge, Last, Besonderheiten |
| **Materialprotokoll** | Benutzte Seile, Sturzbelastung, Schäden |

Ein Seil, das eine Sturzbelastung erfahren hat, muss im Materialprotokoll vermerkt und überprüft werden, bevor es wieder eingesetzt wird.

## Grundsätze guter Dokumentation

- **Wahrheitsgemäß:** Nur das, was tatsächlich gemacht oder beobachtet wurde.
- **Zeitnah:** Direkt nach dem Einsatz, solange die Erinnerung frisch ist.
- **Sachlich:** Keine Wertungen über Patienten oder Kollegen.
- **Vollständig:** Auch Maßnahmen, die nicht funktioniert haben, gehören hinein.
- **Datenschutz:** Patientendaten nur im System, nicht in privaten Notizen oder Nachrichten.

## Übergabe an SAMS

Die wichtigsten Daten für den Rettungsdienst werden mündlich übergeben und anschließend im System nachgetragen. Für den Rettungsdienst besonders wichtig sind:

- Unfallmechanismus (Sturzhöhe, Zeit im Wasser)
- Bewusstseinslage im Verlauf
- Wärmeerhalt und durchgeführte Reanimation

## Nachbereitung

Nach dem Einsatz folgt eine kurze **Einsatznachbesprechung**: Was lief gut, was kann besser werden? Erkenntnisse fließen in Ausbildung und Ausrüstung ein.

*Merke:* Was nicht dokumentiert ist, ist nicht geschehen.
MD],
        ],
        'questions' => [
            ['Welche drei Kernaufgaben hat die TAS?', ['Löschen, Bergen, Schützen', 'Erreichen, Versorgen, Transportieren zum Übergabepunkt', 'Ermitteln, Festnehmen, Absperren', 'Diagnostizieren, Operieren, Entlassen'], [1], 'Die TAS erreicht den Patienten, versorgt ihn und bringt ihn zu einem Übergabepunkt an den Rettungsdienst.'],
            ['Welche Einsatzarten deckt die TAS ab?', ['Nur Bergrettung', 'Brandbekämpfung und technische Hilfe', 'Gelände-, Wasser- und Luftrettung', 'Ausschließlich Hubschraubertransporte'], [2], 'Die TAS ist die Spezialrettung für Gelände, Wasser und Luft.'],
            ['Was ist das kleinste Einsatzelement der TAS?', ['Eine Einzelperson', 'Der Trupp aus zwei Kräften', 'Die Gruppe aus neun Kräften', 'Der Zug'], [1], 'Spezialrettung wird niemals allein durchgeführt, das kleinste Element ist der Trupp.'],
            ['Was bedeutet FMS-Status 7?', ['Patient aufgenommen / Transport', 'Sprechwunsch', 'Am Einsatzort eingetroffen', 'Nicht einsatzbereit'], [0], 'Status 7 wird gemeldet, wenn der Patient aufgenommen ist und transportiert wird.'],
            ['Welcher Status wird gemeldet, wenn der Übergabepunkt oder das Krankenhaus erreicht ist?', ['Status 4', 'Status 2', 'Status 5', 'Status 8'], [3], 'Status 8 bedeutet „Am Ziel angekommen“.'],
            ['Was tut man, wenn im Raton Canyon die Funkverbindung zur Leitstelle abreißt?', ['Den Einsatz sofort abbrechen', 'Auf das Mobiltelefon des Patienten ausweichen', 'Eine Funkrelaisstelle an einem erhöhten Punkt einrichten', 'Lauter ins Funkgerät sprechen'], [2], 'Eine Einsatzkraft an erhöhter Stelle gibt als Relais die Meldungen weiter.'],
            ['Wie lautet die richtige Reihenfolge der Wasserrettung (vom geringsten zum höchsten Risiko)?', ['Schwimmen – Tauchen – Werfen – Reden', 'Reden – Reichen – Werfen – Rudern – Schwimmen – Tauchen', 'Tauchen – Rudern – Reichen – Reden', 'Werfen – Schwimmen – Reden – Reichen'], [1], 'Zuerst wird versucht, den Patienten vom sicheren Standort aus zu erreichen.'],
            ['Welche Aussagen zum Eigenschutz sind richtig?', ['Ein Einsatzabbruch bei unbeherrschbarer Gefahr ist verantwortungsvolles Handeln', 'Wer am Ufer einer reißenden Strömung steht, trägt eine Rettungsweste', 'Ohne PSA darf man starten, wenn es schnell gehen muss', 'Eigenschutz geht vor Fremdschutz'], [0, 1, 3], 'Eigenschutz hat immer Vorrang, PSA ist Pflicht und ein Abbruch ist legitim.'],
            ['Wer ist bei einem Todesfall im Einsatz immer zu informieren?', ['Die Presse', 'Die Polizei (LSPD bzw. BCSO)', 'Nur die Angehörigen', 'Niemand, die TAS birgt selbstständig'], [1], 'Bei einem Verstorbenen wird stets die Polizei informiert, da eine Straftat nicht ausgeschlossen werden kann.'],
            ['Welche Organisation führt bei einem Vermisstenfall das Ermittlungsverfahren?', ['Die Polizei', 'Die TAS', 'SAMS', 'Das LSFD'], [0], 'Die Polizei ist zuständig, die TAS unterstützt mit Suchkräften.'],
            ['Wohin bringt die TAS einen Patienten in der Regel?', ['Direkt nach Hause', 'In den OP', 'Zu einem Übergabepunkt an den Rettungsdienst, z. B. das Dach des Pillbox Hill Medical Center', 'Zur nächsten Polizeistation'], [2], 'Die TAS übergibt an SAMS, die endgültige Versorgung erfolgt im Krankenhaus.'],
            ['Was muss mit einem Seil geschehen, das eine Sturzbelastung erfahren hat?', ['Nichts, Seile halten ewig', 'Es wird im Materialprotokoll vermerkt und vor erneutem Einsatz überprüft', 'Es wird nur gewaschen', 'Es wird an die Feuerwehr verschenkt'], [1], 'Sturzbelastete Seile müssen dokumentiert und geprüft werden.'],
            ['Welche Grundsätze gelten für die Dokumentation?', ['Wahrheitsgemäß und zeitnah', 'Patientendaten dürfen privat weitergeschickt werden', 'Auch erfolglose Maßnahmen werden dokumentiert', 'Sachlich und ohne Wertungen'], [0, 2, 3], 'Die Dokumentation ist wahr, zeitnah, vollständig und sachlich; Datenschutz ist zu beachten.'],
        ],
    ],
    [
        'title' => 'Geländerettung und Seiltechnik',
        'description' => 'Lehrgang zur Bergretterin bzw. zum Bergretter nach dem Vorbild der Bergwacht. Behandelt Sicherungsgrundsätze, Anschlagpunkte, Flaschenzugsysteme, Abseilen und Ablassen, den Transport mit der Gebirgstrage, Einsatzgebiete Mount Chiliad und Raton Canyon sowie Wetterkunde und Unterkühlung.',
        'hours' => 12, 'pass_percent' => 80, 'question_count' => 12, 'time_limit' => 25,
        'qualification' => 'Bergretter/in',
        'modules' => [
            ['Sicherungsgrundsätze und Material', <<<'MD'
# Sicherungsgrundsätze und Material

In der Geländerettung hängt das Leben von Retter und Patient am Seil. Deshalb gelten feste Grundsätze, die **ohne Ausnahme** eingehalten werden.

## Die wichtigsten Grundsätze

1. **Redundanz:** Jedes lasttragende System ist doppelt ausgeführt. Ein Fehler darf nie zum Absturz führen. In der Regel gibt es ein **Hauptseil** (Arbeitsseil) und ein **Sicherungsseil**.
2. **Partnercheck:** Vor jedem Einsatz am Seil kontrollieren sich die Retter gegenseitig: Gurt geschlossen und richtig angelegt, Karabiner verschlossen, Knoten korrekt und mit Überstand, Abseilgerät richtig eingelegt.
3. **Absturzsicherung ab der Kante:** Wer sich im absturzgefährdeten Bereich bewegt, ist gesichert. Die Kante wird nie ungesichert betreten.
4. **Kantenschutz:** Seile über scharfen Fels- oder Betonkanten werden mit Kantenschutz versehen, denn eine belastete Kante kann ein Seil in Sekunden durchtrennen.
5. **Klare Kommandos:** Am Seil gibt nur eine Person die Kommandos, alle anderen bestätigen.

## Persönliche Ausrüstung

- **Helm** mit Kinnriemen – Schutz vor Steinschlag und Anprall
- **Sitzgurt** (bei Bedarf mit Brustgurt)
- **Handschuhe** für Seilarbeit
- **Abseilgerät** mit Panikfunktion bzw. Blockierfunktion
- **Karabiner mit Verschlusssicherung** (Schrauber oder Twistlock)
- **Prusikschlingen** und **Bandschlingen**
- **Stirnlampe**, Messer, Funkgerät

## Seile

In der Rettung werden **statische Seile** (Halbstatikseile, ca. 10,5–11 mm) verwendet. Sie dehnen sich nur wenig, was das Ablassen und Aufziehen kontrollierbar macht. **Dynamische Seile** aus dem Klettersport fangen Stürze weich ab, eignen sich aber schlecht für Flaschenzüge, weil viel Zugweg in der Dehnung verloren geht.

Seile werden vor jedem Einsatz durch die Hand laufen gelassen und auf Beschädigungen, Verhärtungen und Mantelschäden geprüft. Kontakt mit Säuren, Batterieflüssigkeit und Kraftstoff macht ein Seil unbrauchbar.

## Knoten

| Knoten | Verwendung |
|---|---|
| **Achterknoten (gesteckt)** | Einbinden in den Gurt |
| **Achterschlinge** | Seilende in einen Karabiner einhängen |
| **Mastwurf** | Einstellbare Selbstsicherung am Anschlagpunkt |
| **Prusik** | Klemmknoten als Rücklaufsperre oder Sicherung |
| **Halbmastwurf (HMS)** | Sichern und Ablassen über einen HMS-Karabiner |

*Merke:* Kein Seil ohne zweites Seil, kein Retter ohne Partnercheck.
MD],
            ['Anschlagpunkte und Flaschenzüge', <<<'MD'
# Anschlagpunkte und Flaschenzüge

## Anschlagpunkte

Der **Anschlagpunkt** ist die Stelle, an der das Seilsystem befestigt wird. Er muss die zu erwartende Last **sicher und mit Reserve** halten.

**Geeignete Anschlagpunkte:**
- dicke, gesund verwurzelte Bäume (Faustregel: Stammdurchmesser mindestens 15–20 cm)
- große, fest im Boden liegende Felsblöcke
- Bohrhaken an Felswänden
- Fahrzeuge (Achse oder Abschleppöse, Fahrzeug gesichert, Motor aus, Handbremse an, Schlüssel abgezogen)
- Leitplankenpfosten nur nach Prüfung

**Ungeeignet:** abgestorbene Bäume, lose Steine, Verkehrsschilder, Geländer mit unbekannter Befestigung.

Ist ein einzelner Punkt nicht sicher genug, wird ein **Ausgleichsstand** aus zwei oder mehr Punkten gebaut, die über eine Bandschlinge zusammengeführt werden. Der Winkel zwischen den Schenkeln sollte **unter 60°** liegen, denn je größer der Winkel, desto stärker wird jeder einzelne Punkt belastet. Bei 120° trägt jeder Punkt bereits die volle Last.

## Flaschenzüge

Muss ein Patient aus einer Schlucht **nach oben** gezogen werden, reicht reine Muskelkraft oft nicht aus. Ein **Flaschenzug** verringert die nötige Zugkraft.

| System | Theoretische Übersetzung | Bemerkung |
|---|---|---|
| **2:1** | halbe Kraft | einfacher Aufbau |
| **3:1 (Z-System)** | ein Drittel der Kraft | Standard in der Rettung |
| **5:1 / 6:1** | ein Fünftel bzw. Sechstel | für schwere Lasten, viel Seil nötig |

Durch Reibung in Umlenkrollen und Karabinern ist die tatsächliche Übersetzung geringer als die theoretische. Deshalb werden **Rollen** statt bloßer Karabiner verwendet.

## Aufbau des 3:1 (Z-System)

1. Hauptseil am Anschlagpunkt über eine Rolle mit **Rücklaufsperre** (z. B. Prusik) führen.
2. Weiter unten am belasteten Seil eine **Seilklemme** oder einen Prusik setzen.
3. Das Zugseil durch eine Rolle an dieser Klemme zurückführen.
4. Beim Zug läuft das System zusammen, die Rücklaufsperre hält die Last, die Klemme wird wieder nach unten geschoben.

## Sicherheit beim Aufziehen

- Das **Sicherungsseil** läuft immer mit und wird nachgeführt.
- Kommandos: „Zug!“, „Halt!“, „Ablassen!“ – kurz und laut.
- Bei Blockade oder Kantenproblem sofort **„Halt!“**.

*Merke:* Ein Anschlagpunkt ist nur so gut wie sein schwächstes Teil.
MD],
            ['Abseilen, Ablassen und Gebirgstrage', <<<'MD'
# Abseilen, Ablassen und Gebirgstrage

## Abseilen

Beim **Abseilen** bewegt sich der Retter selbst am Seil nach unten, gebremst durch sein Abseilgerät. Er hat beide Hände an Seil und Gerät, die **Bremshand verlässt nie das Seil**.

Vor dem Abseilen:
- Partnercheck durchführen
- Seilende mit einem **Endknoten** sichern, damit niemand vom Seilende rutschen kann
- Prüfen, ob das Seil bis zum Ziel reicht
- Zusätzliche Sicherung durch ein Sicherungsseil von oben oder einen Prusik unterhalb des Abseilgeräts

## Ablassen

Beim **Ablassen** wird eine Last (Retter, Patient oder beide) von oben über ein Bremssystem kontrolliert nach unten gelassen. Der Bediener oben steuert die Geschwindigkeit. Ablassen ist in der Rettung oft sicherer als Abseilen, weil die Kontrolle bei einer gesicherten Person am Stand liegt.

Für das **Ablassen mit Retterbegleitung** hängt sich ein Retter zusammen mit der Trage ins System. Er hält die Trage von der Felswand weg und betreut den Patienten.

## Gebirgstrage

Die **Gebirgstrage** (z. B. Akja-Trage oder Korbtrage) ist für Gelände ausgelegt:

- stabiler Korb, in den der Patient eingeschnallt wird
- **Vakuummatratze** als Unterlage zur Ruhigstellung
- abnehmbares Rad für Wege
- Anschlagpunkte für eine **Tragespinne** zum Ablassen und für den Windenbetrieb

Der Patient wird **immer** vollständig angeschnallt, auch für kurze Strecken. Kopf und Helm des Patienten werden geschützt, bei Steinschlaggefahr erhält er ebenfalls einen Helm.

## Tragen im Gelände

- In flachem Gelände tragen **sechs Retter**, damit regelmäßig gewechselt werden kann.
- Kopf voran bergauf, Füße voran bergab – so bleibt der Kopf höher.
- Bei steilem Gelände wird die Trage zusätzlich über ein Seil gesichert.
- Rücksprache mit dem Patienten halten, Schmerzen und Bewusstsein kontrollieren.

## Horizontale oder vertikale Lage

Für das Ablassen an einer Felswand wird die Trage meist **horizontal** gehängt, damit der Patient flach liegt und der Kreislauf geschont wird. Eine vertikale Lage ist nur in Ausnahmen (enge Spalten) und nur kurzzeitig vertretbar.

*Merke:* Die Bremshand verlässt nie das Seil – weder beim Abseilen noch beim Ablassen.
MD],
            ['Einsatzgebiete Mount Chiliad und Raton Canyon', <<<'MD'
# Einsatzgebiete Mount Chiliad und Raton Canyon

## Mount Chiliad

Der **Mount Chiliad** ist mit rund 800 m der höchste Berg im Staat San Andreas. Er liegt im Norden von Blaine County zwischen **Paleto Bay** und **Grapeseed**.

**Typische Einsätze:**
- gestürzte Wanderer und Mountainbiker auf den Trails
- Paragleiter und Fallschirmspringer mit Fehllandung
- Fahrzeuge, die von den Serpentinen abgekommen sind
- Erschöpfung und Orientierungsverlust bei Dunkelheit

**Besonderheiten:**
- Die **Seilbahn** führt bis zum Gipfel und kann zum Materialtransport genutzt werden, sofern sie in Betrieb ist.
- Am Gipfel ist es deutlich kühler und windiger als im Tal.
- Die Anfahrt erfolgt über Paleto Bay oder den Great Ocean Highway; Wanderwege sind nur mit Geländefahrzeug oder zu Fuß erreichbar.
- **Übergabepunkte** an SAMS: Parkplatz an der Talstation, Paleto Bay Medical Center oder Hubschrauberlandeplatz.

## Raton Canyon

Der **Raton Canyon** ist eine tiefe Schlucht im Nordwesten, durch die der **Cassidy Creek** bzw. Zancudo-Zufluss fließt. Bekannt sind die **Eisenbahnbrücke** und die Straßenbrücke.

**Typische Einsätze:**
- Abstürze von der Brücke oder vom Schluchtrand
- Kletterunfälle an den Felswänden
- Personen im Fluss, kombiniert mit Strömungsrettung
- Base-Jumper mit Fehlsprung

**Besonderheiten:**
- Steile, teilweise brüchige Wände mit **Steinschlaggefahr**
- Eingeschränkter **Funkempfang** am Grund der Schlucht – Relais einplanen
- Rettung oft nur **vertikal** über Seilsysteme oder per Hubschrauberwinde möglich
- Die Eisenbahnbrücke darf nur nach Rücksprache mit der Leitstelle betreten werden (Zugverkehr sperren lassen)

## Erkundung

Vor jedem Seileinsatz wird erkundet:

1. Wo liegt der Patient, ist er ansprechbar?
2. Gibt es einen Zustieg ohne Seil?
3. Welche Anschlagpunkte stehen oben zur Verfügung?
4. Wie hoch ist die Wand, reicht das Seil?
5. Wohin wird der Patient gebracht – nach oben, nach unten oder per Winde?

Die Entscheidung zwischen **bodengebundener Rettung** und **Windenrettung** fällt der Einsatzleiter gemeinsam mit der Hubschrauberbesatzung. Bei schlechter Sicht, Dunkelheit oder starkem Wind ist der Hubschrauber oft nicht einsetzbar.

*Merke:* Im Gelände entscheidet die Erkundung über Erfolg und Sicherheit der Rettung.
MD],
            ['Wetter und Unterkühlung', <<<'MD'
# Wetter und Unterkühlung

## Wetter im Gebirge

Auch in San Andreas kann das Wetter am Berg schnell umschlagen. Die Bergretter beobachten:

- **Gewitter:** Größte Gefahr im Gebirge. Bei Blitzschlaggefahr Grate, Gipfel, einzelne Bäume und Metallgegenstände meiden. Seilarbeiten an exponierten Stellen werden unterbrochen. Faustregel: Liegen zwischen Blitz und Donner weniger als 10 Sekunden, ist das Gewitter gefährlich nah.
- **Wind:** Kühlt den Körper stark aus (Windchill) und kann den Hubschraubereinsatz verhindern.
- **Nebel:** Erschwert Orientierung und Suche, verhindert oft den Flugbetrieb.
- **Regen:** Macht Fels und Wege rutschig, erhöht Steinschlag und Unterkühlung.
- **Hitze:** In der Grand Senora Desert und an Südhängen drohen Hitzeerschöpfung und Hitzschlag – auch bei den Rettern.

## Unterkühlung (Hypothermie)

Eine Unterkühlung liegt vor, wenn die **Körperkerntemperatur unter 35 °C** sinkt. Sie entsteht durch Kälte, Nässe, Wind und Bewegungslosigkeit – also genau bei verletzten Personen im Gelände.

| Stadium | Kerntemperatur | Zeichen |
|---|---|---|
| **I (leicht)** | 35–32 °C | wach, Kältezittern |
| **II (mittel)** | 32–28 °C | Bewusstseinstrübung, Zittern hört auf |
| **III (schwer)** | 28–24 °C | bewusstlos, Vitalzeichen vorhanden |
| **IV** | unter 24 °C | Atem- und Kreislaufstillstand möglich |

## Maßnahmen

- **Vorsichtig bewegen:** Patienten möglichst flach lagern und nicht aktiv bewegen lassen. Ruckartige Bewegungen können kaltes Blut aus den Armen und Beinen in den Körperkern bringen und Herzrhythmusstörungen auslösen (**Bergungstod**).
- **Weiteren Wärmeverlust verhindern:** Nasse Kleidung vorsichtig entfernen oder aufschneiden, in Decken und **Rettungsdecke** einpacken, Windschutz schaffen, isolierende Unterlage verwenden.
- **Aktive Wärme** am Rumpf (Wärmepacks an Brust und Achseln), nicht an Armen und Beinen.
- **Keine Massage**, kein Alkohol, kein heißes Bad.
- Warme, gezuckerte Getränke nur bei wachen Patienten mit erhaltenem Schluckreflex.

## Reanimation bei Unterkühlung

Bei Kreislaufstillstand wird reanimiert. Es gilt der Grundsatz: **„Niemand ist tot, bis er warm und tot ist.“** Stark unterkühlte Patienten können auch nach langer Reanimation überleben. Der Transport in eine Klinik mit Möglichkeit zur Wiedererwärmung wird frühzeitig angemeldet.

*Merke:* Ein Patient im Gelände kühlt immer aus – Wärmeerhalt beginnt beim ersten Kontakt.
MD],
        ],
        'questions' => [
            ['Was bedeutet Redundanz in der Seiltechnik?', ['Möglichst wenig Material verwenden', 'Jedes lasttragende System ist doppelt ausgeführt, z. B. Haupt- und Sicherungsseil', 'Nur dynamische Seile verwenden', 'Das Seil wird nach jedem Einsatz entsorgt'], [1], 'Ein einzelner Fehler darf nie zum Absturz führen, daher wird doppelt gesichert.'],
            ['Welche Seile werden in der Rettung bevorzugt verwendet?', ['Dynamische Kletterseile', 'Reepschnüre', 'Statische Seile (Halbstatik)', 'Wäscheleinen'], [2], 'Statische Seile dehnen sich kaum und erlauben kontrolliertes Ablassen und Aufziehen.'],
            ['Was wird beim Partnercheck kontrolliert?', ['Gurt richtig angelegt und geschlossen', 'Karabiner verschlossen', 'Knoten korrekt mit Überstand', 'Die Uhrzeit'], [0, 1, 2], 'Gurt, Karabiner, Knoten und Abseilgerät werden gegenseitig kontrolliert.'],
            ['Welcher Knoten wird zum Einbinden in den Gurt verwendet?', ['Kreuzknoten', 'Gesteckter Achterknoten', 'Schotenstich', 'Halbschlag'], [1], 'Der gesteckte Achterknoten ist der Standardknoten zum Einbinden.'],
            ['Welcher Anschlagpunkt ist ungeeignet?', ['Ein dicker, gesund verwurzelter Baum', 'Ein gesicherter Bohrhaken', 'Ein großer, fest im Boden liegender Felsblock', 'Ein Verkehrsschild'], [3], 'Verkehrsschilder und lose Gegenstände sind als Anschlagpunkt ungeeignet.'],
            ['Warum sollte der Winkel in einem Ausgleichsstand unter 60° liegen?', ['Weil die Belastung jedes Punktes mit größerem Winkel steigt', 'Damit das Seil schöner aussieht', 'Weil sonst der Knoten aufgeht', 'Das spielt keine Rolle'], [0], 'Bei 120° trägt jeder Punkt bereits die volle Last.'],
            ['Welche theoretische Übersetzung hat das Z-System?', ['2:1', '3:1', '6:1', '1:1'], [1], 'Das Z-System ist ein 3:1-Flaschenzug und Standard in der Rettung.'],
            ['Wozu dient der Prusik am Anschlagpunkt eines Flaschenzugs?', ['Als Dekoration', 'Zum Verlängern des Seils', 'Als Rücklaufsperre, die die Last hält', 'Zum Abschneiden des Seils'], [2], 'Die Rücklaufsperre hält die Last, während die Klemme neu gesetzt wird.'],
            ['Was gilt beim Abseilen für die Bremshand?', ['Sie verlässt nie das Seil', 'Sie hält sich an der Felswand fest', 'Sie bedient das Funkgerät', 'Sie wird nur bei Wind benötigt'], [0], 'Die Bremshand bleibt immer am Seil, sonst ist die Bremswirkung weg.'],
            ['Wie wird eine Gebirgstrage im Gelände getragen?', ['Kopf voran bergab', 'Kopf voran bergauf, Füße voran bergab', 'Immer seitlich', 'Der Patient wird nicht angeschnallt, damit er sich abstützen kann'], [1], 'So bleibt der Kopf des Patienten höher als die Füße.'],
            ['Was ist im Raton Canyon vor dem Betreten der Eisenbahnbrücke zu tun?', ['Nichts, Brücken sind immer sicher', 'Den Zugverkehr über die Leitstelle sperren lassen', 'Nur einen Helm aufsetzen', 'Hupen'], [1], 'Die Brücke darf nur nach Rücksprache mit der Leitstelle betreten werden.'],
            ['Ab welcher Körperkerntemperatur spricht man von Unterkühlung?', ['Unter 37 °C', 'Unter 30 °C', 'Unter 35 °C', 'Unter 38 °C'], [2], 'Hypothermie beginnt bei einer Kerntemperatur unter 35 °C.'],
            ['Welche Maßnahmen sind bei Unterkühlung richtig?', ['Arme und Beine kräftig massieren', 'Patient vorsichtig und möglichst flach bewegen', 'Weiteren Wärmeverlust durch Decken, Windschutz und Isolation verhindern', 'Aktive Wärme am Rumpf'], [1, 2, 3], 'Massage kann kaltes Blut in den Kern treiben und den Bergungstod auslösen.'],
            ['Was bedeutet der Grundsatz „Niemand ist tot, bis er warm und tot ist“?', ['Stark unterkühlte Patienten werden auch bei langem Stillstand reanimiert', 'Man wartet mit der Reanimation, bis der Patient warm ist', 'Unterkühlte werden nicht reanimiert', 'Nur warme Patienten dürfen transportiert werden'], [0], 'Unterkühlung schützt das Gehirn, daher wird lange reanimiert.'],
        ],
    ],
    [
        'title' => 'Rettungsschwimmer und Strömungsrettung',
        'description' => 'Lehrgang zur Rettungsschwimmerin bzw. zum Rettungsschwimmer nach dem Vorbild von DLRG und Wasserwacht. Behandelt den Wachdienst an Vespucci Beach und Del Perro, das Erkennen von Ertrinkenden, Rettungsgriffe, Rettungsboje und Rettungsbrett, Strömungsrettung an Alamo Sea und Zancudo River sowie die Reanimation nach Ertrinken.',
        'hours' => 12, 'pass_percent' => 80, 'question_count' => 12, 'time_limit' => 25,
        'qualification' => 'Rettungsschwimmer/in',
        'modules' => [
            ['Strandwache Vespucci und Del Perro', <<<'MD'
# Strandwache Vespucci und Del Perro

Die Strände von **Vespucci Beach** und **Del Perro Beach** gehören zu den meistbesuchten Orten in Los Santos. Die TAS stellt hier in der Saison den **Wasserrettungsdienst** mit Rettungsschwimmern auf den Wachtürmen.

## Aufgaben der Strandwache

- **Prävention:** Badegäste auf Gefahren hinweisen, bevor etwas passiert – das ist die wichtigste Aufgabe.
- **Beobachtung:** Wasserfläche ständig und systematisch beobachten.
- **Rettung:** Personen in Not schnell erreichen und an Land bringen.
- **Erste Hilfe:** Versorgung kleiner Verletzungen, Quallenkontakt, Sonnenstich bis zur Übergabe an SAMS.

## Wachturm und Beflaggung

Die Türme sind mit Fernglas, Funkgerät, Rettungsboje, Rettungsbrett, Erste-Hilfe-Material und Defibrillator (AED) ausgestattet. Die Beflaggung zeigt den Badegästen die Lage:

| Flagge | Bedeutung |
|---|---|
| **Rot-Gelb** | Bewachter Badebereich, Baden erlaubt |
| **Gelb** | Baden gefährlich, für Kinder und ungeübte Schwimmer verboten |
| **Rot** | Badeverbot |

Badegäste sollen nur **zwischen den rot-gelben Flaggen** schwimmen.

## Beobachtungstechnik

- Wasserfläche in **Sektoren** einteilen und diese regelmäßig abscannen (etwa alle 10 bis 30 Sekunden).
- Besonders auf **Risikogruppen** achten: Kinder, ältere Menschen, Personen unter Alkoholeinfluss, Nichtschwimmer mit Luftmatratze.
- Beobachtungsposition alle **30 bis 60 Minuten wechseln**, um die Aufmerksamkeit zu erhalten.
- Gefährliche Bereiche kennen: Pfeiler des **Del Perro Pier**, Sandbänke, Rippströmungen.

## Rippströmungen

Eine **Rippströmung (Brandungsrückstrom)** ist eine schmale, seewärts gerichtete Strömung zwischen Sandbänken. Sie erkennt man an:

- einer ruhigen, dunkleren Lücke in der Brandung
- Schaum, Sand oder Treibgut, das seewärts zieht

Wer hineingerät, soll **nicht gegen die Strömung** schwimmen, sondern **parallel zum Strand** hinausschwimmen oder sich treiben lassen und winken.

## Kommunikation

Jeder Turm hat Funkverbindung zu den Nachbartürmen und zur Leitstelle. Geht ein Rettungsschwimmer ins Wasser, übernimmt der Nachbarturm die Beobachtung seines Abschnitts.

*Merke:* Die beste Rettung ist die, die gar nicht erst nötig wird.
MD],
            ['Erkennen von Ertrinkenden', <<<'MD'
# Erkennen von Ertrinkenden

Ertrinken ist **leise**. Anders als in Filmen schreien Ertrinkende meist nicht und winken nicht. Wer das nicht weiß, übersieht den Notfall direkt vor sich.

## Der instinktive Ertrinkungsreflex

Ein Mensch, der zu ertrinken droht, zeigt typische Zeichen:

- **Kopf tief im Wasser**, Mund auf Höhe der Wasseroberfläche, Kopf nach hinten geneigt
- **Senkrechte Haltung** im Wasser, keine Beinbewegung
- **Arme seitlich**, die nach unten auf das Wasser drücken („Leiterklettern“)
- **Glasiger, leerer Blick**, Augen geschlossen oder Haare vor dem Gesicht
- **Keine Hilferufe** möglich, weil der Atem nur zum Luftholen reicht
- Kein Vorwärtskommen trotz scheinbarer Schwimmbewegung

Diese Phase dauert oft nur **20 bis 60 Sekunden**, bevor die Person untergeht.

## Wassernot

Davor liegt häufig die Phase der **Wassernot**: Die Person kann noch rufen und winken, ist aber erschöpft oder in Panik. Hier kann sie oft noch selbst nach einem Rettungsmittel greifen.

## Stille Gefahren

- **Kinder** gehen besonders schnell und lautlos unter.
- **Kälteschock:** Bei Sprung in kaltes Wasser (z. B. vom Pier) kommt es zum unkontrollierten Einatmen und Hyperventilation.
- **Alkohol** verschlechtert Urteilsvermögen und Koordination.
- **Herz-Kreislauf-Probleme** bei älteren Badegästen.

## Handeln

1. **Erkennen und alarmieren:** Pfeifsignal, Funk an Nachbarturm und Leitstelle.
2. **Position merken:** Bezugspunkt an Land (Turm, Palme, Pier-Pfeiler) und auf dem Wasser anpeilen, falls die Person untergeht.
3. **Rettungsmittel mitnehmen:** Niemals ohne Boje oder Brett ins Wasser.
4. **Auf dem kürzesten sicheren Weg** zur Person: so weit wie möglich laufen, dann schwimmen.

## Untergegangene Person

Ist die Person bereits untergegangen, wird die **letzte bekannte Position** markiert und sofort gemeldet. Rettungsschwimmer beginnen mit einer Suche durch Abtauchen im Flachwasser; bei größerer Tiefe werden **Rettungstaucher** nachgefordert. Jede Minute zählt.

*Merke:* Wer nicht ruft und nicht winkt, ist nicht unbedingt in Sicherheit – oft ist er gerade am Ertrinken.
MD],
            ['Rettungsgriffe, Boje und Rettungsbrett', <<<'MD'
# Rettungsgriffe, Boje und Rettungsbrett

## Annäherung

Ein Ertrinkender in Panik klammert sich an alles, was er erreichen kann – auch an den Retter. Deshalb gilt:

- Mit **Blickkontakt** annähern und laut ansprechen.
- In **sicherem Abstand** (ca. 2 m) stoppen.
- Zuerst das **Rettungsmittel** anbieten, nicht den eigenen Körper.

## Rettungsboje

Die **Rettungsboje** (Rescue Tube oder Gurtretter) ist das Standardmittel der Strandwache.

- Der Retter schwimmt mit umgelegtem Gurt, die Boje zieht hinterher.
- Am Ziel wird die Boje dem Patienten **zugeschoben**. Er hält sich daran fest.
- Bei bewusstlosen Personen wird die Boje um den Brustkorb gelegt und geschlossen.
- Der Retter schleppt den Patienten mit Abstand an Land.

## Rettungsbrett

Das **Rettungsbrett** eignet sich für weite Strecken und mehrere Personen.

- Schnelles Paddeln in Bauch- oder Kniestellung.
- Der Patient wird quer über das Brett gezogen, dann längs gedreht.
- Mehrere erschöpfte Schwimmer können sich daran festhalten, bis das Boot kommt.

## Schleppgriffe

Ist kein Rettungsmittel greifbar, wird mit Schleppgriffen gerettet:

| Griff | Anwendung |
|---|---|
| **Kopfgriff** | Hände an beiden Seiten des Kopfes, Rückenschwimmen; ruhige Patienten |
| **Achselgriff** | Hände unter den Achseln, gut für längere Strecken |
| **Fesselschleppgriff** | Arm des Patienten fixiert; für unruhige Patienten |
| **Seemannsgriff** | Retter greift über die Brust, schnelles Schleppen |

Atemwege des Patienten bleiben **immer über Wasser**.

## Befreiungsgriffe

Wird der Retter umklammert, befreit er sich: **Abtauchen**, denn der Ertrinkende will nach oben und lässt los. Anschließend Griffe wie das **Lösen aus der Halsumklammerung** durch Hochdrücken der Ellbogen. Danach erneut Abstand gewinnen und Boje anbieten.

## An Land bringen

- In der Brandung mit dem Rücken zu den Wellen gehen und Patienten schützen.
- An Land mit mehreren Helfern tragen.
- Bei Verdacht auf **Wirbelsäulenverletzung** (Kopfsprung ins Flachwasser) Kopf in Längsachse stabilisieren und mit Spineboard bergen.

*Merke:* Zuerst das Rettungsmittel – der eigene Körper ist das letzte Mittel.
MD],
            ['Strömungsrettung Alamo Sea und Zancudo River', <<<'MD'
# Strömungsrettung Alamo Sea und Zancudo River

Fließgewässer sind gefährlicher als das Meer an einem bewachten Strand. Strömung entwickelt enorme Kräfte: Schon **knietiefes Wasser** kann bei starker Strömung einen Erwachsenen umwerfen.

## Einsatzgebiete

- **Zancudo River:** Fließt aus den Bergen durch Blaine County bis zum Meer bei Fort Zancudo. Nach Regen schnell steigend, mit Treibholz und Untiefen.
- **Alamo Sea:** Großer Binnensee bei Sandy Shores. Grundsätzlich ruhig, aber mit plötzlichen Winden, kalten Tiefenschichten und Zuflüssen. Häufig Boots- und Jetski-Unfälle.
- **Raton Canyon:** Enge Schlucht mit schneller Strömung und Felsen.

## Gefahren im Fließgewässer

- **Walze (Rücklauf):** Hinter Wehren und Stufen entsteht eine kreisende Strömung, aus der man kaum herauskommt. Walzen sind lebensgefährlich.
- **Siebe:** Hindernisse, durch die Wasser fließt, aber kein Mensch, z. B. Treibholz und Gitter.
- **Unterspülungen** an Felsen.
- **Fußfalle:** Wer in der Strömung steht, kann den Fuß einklemmen und wird vom Wasser niedergedrückt. Deshalb **nie in der Strömung aufstehen**, solange das Wasser schneller als Schrittgeschwindigkeit fließt.

## Schwimmposition

- **Defensive Schwimmposition:** Rückenlage, Füße voran und an der Oberfläche, Blick flussabwärts. Hindernisse werden mit den Füßen abgefangen.
- **Aktive Schwimmposition:** Bauchlage, Kraulen schräg zur Strömung in Richtung eines **Kehrwassers** (ruhiger Bereich hinter Hindernissen).

## Rettung vom Ufer

Nach der Reihenfolge **Reden – Reichen – Werfen** zuerst vom Ufer aus:

- **Wurfsack:** Der Retter wirft den Sack über den Patienten hinweg, damit das Seil auf ihm landet. Danach wird der Patient im **Pendel** ans Ufer geschwungen, der Retter steht dabei stromabwärts eines sicheren Punktes.
- **Posten stromabwärts:** Weitere Kräfte mit Wurfsäcken sichern unterhalb, falls der erste Wurf misslingt.
- **Posten stromaufwärts:** Ein Beobachter warnt vor Treibgut.

## Ausrüstung

Strömungsretter tragen **Wildwasserhelm**, **Rettungsweste mit Schnellbefreiungsgurt**, Kälteschutzanzug, Messer und Pfeife. **Niemals** wird ein Seil fest am Körper eines Retters im Wasser angebunden, ohne dass er sich sofort lösen kann – sonst droht er unter Wasser gezogen zu werden.

*Merke:* Nie in der Strömung aufstehen, nie fest anbinden, immer stromabwärts sichern.
MD],
            ['Reanimation nach Ertrinken', <<<'MD'
# Reanimation nach Ertrinken

Beim Ertrinken entsteht der Kreislaufstillstand durch **Sauerstoffmangel**. Deshalb unterscheidet sich die Reanimation von Ertrunkenen in wichtigen Punkten von der Standardreanimation.

## Bergung und erste Einschätzung

- Patient so schnell wie möglich aus dem Wasser holen, **horizontal** lagern, wenn er unterkühlt ist.
- **Bewusstsein prüfen:** laut ansprechen, Schultern rütteln.
- **Atemwege freimachen:** Kopf überstrecken, Kinn anheben, grobe Verunreinigungen entfernen.
- **Atmung prüfen:** maximal 10 Sekunden sehen, hören, fühlen.

Kein Versuch, Wasser aus der Lunge zu „schütteln“ oder zu drücken – das kostet Zeit und ist wirkungslos.

## Besonderheiten beim Ertrinken

1. **Fünf initiale Beatmungen:** Weil die Ursache der Sauerstoffmangel ist, beginnt man mit **5 Beatmungen**, bevor die Herzdruckmassage startet.
2. Danach **30 Kompressionen zu 2 Beatmungen**, Drucktiefe 5–6 cm, Frequenz 100–120 pro Minute.
3. **AED** so früh wie möglich anschließen; Brustkorb vorher abtrocknen.
4. **Erbrechen** ist häufig: Patient kurz zur Seite drehen, Mund ausräumen, weiter reanimieren.
5. Wenn vorhanden, **Sauerstoff** und Beutel-Masken-Beatmung verwenden.

## Reanimation im Wasser

Im Wasser ist eine wirksame Herzdruckmassage nicht möglich. Bei kurzer Distanz zum Ufer hat die schnelle Bergung Vorrang. Ausgebildete Rettungsschwimmer können bei großer Distanz und mit Auftriebsmittel bereits **Beatmungen im Wasser** durchführen.

## Unterkühlung

Wer lange im Wasser war, ist meist unterkühlt. Nasse Kleidung entfernen, Wärme erhalten, Reanimation nicht zu früh beenden. Es gilt auch hier: **„Niemand ist tot, bis er warm und tot ist.“**

## Beinahe-Ertrinken

Auch Patienten, die nach einer Rettung wieder wach sind und gut atmen, müssen **immer** vom Rettungsdienst untersucht und in der Regel ins Krankenhaus gebracht werden. Eingeatmetes Wasser kann noch Stunden später zu einer **Verschlechterung der Lungenfunktion** führen.

## Übergabe an SAMS

Wichtige Informationen für den Rettungsdienst:

- geschätzte **Zeit unter Wasser** bzw. im Wasser
- **Wassertemperatur** und Art des Gewässers (Meer, Süßwasser)
- Beginn und Dauer der Reanimation, Anzahl der Schocks
- Verdacht auf Wirbelsäulenverletzung

*Merke:* Beim Ertrunkenen zuerst fünfmal beatmen – Sauerstoff ist das, was fehlt.
MD],
        ],
        'questions' => [
            ['Was ist die wichtigste Aufgabe der Strandwache?', ['Die Prävention: Badegäste auf Gefahren hinweisen, bevor etwas passiert', 'Strafzettel verteilen', 'Liegestühle vermieten', 'Den Pier reinigen'], [0], 'Die beste Rettung ist die, die gar nicht erst nötig wird.'],
            ['Was bedeutet eine rot-gelbe Flagge am Strand?', ['Badeverbot', 'Haialarm', 'Bewachter Badebereich, Baden erlaubt', 'Nur für Surfer'], [2], 'Zwischen den rot-gelben Flaggen ist der bewachte Badebereich.'],
            ['Wie verhält man sich in einer Rippströmung richtig?', ['Mit aller Kraft direkt gegen die Strömung zum Strand schwimmen', 'Parallel zum Strand hinausschwimmen oder treiben lassen und winken', 'Abtauchen und am Grund entlanglaufen', 'Laut schreien und nicht bewegen'], [1], 'Rippströmungen sind schmal, parallel zum Strand gelangt man aus ihnen heraus.'],
            ['Welche Zeichen sprechen für einen Ertrinkenden?', ['Kopf tief im Wasser, nach hinten geneigt', 'Senkrechte Haltung ohne Beinbewegung', 'Lautes Rufen und kräftiges Winken', 'Arme drücken seitlich auf das Wasser'], [0, 1, 3], 'Ertrinken ist leise, Ertrinkende können meist nicht rufen oder winken.'],
            ['Was ist zu tun, wenn eine Person vor Eintreffen des Retters untergeht?', ['Abwarten, bis sie wieder auftaucht', 'Den Einsatz beenden', 'Den Strand räumen', 'Letzte bekannte Position markieren, melden und gegebenenfalls Rettungstaucher nachfordern'], [3], 'Die letzte bekannte Position ist entscheidend für die Suche.'],
            ['Warum nähert sich der Retter einem Ertrinkenden nur bis auf etwa 2 m?', ['Um Kraft zu sparen', 'Weil der Ertrinkende in Panik den Retter umklammern kann', 'Weil das Wasser dort tiefer ist', 'Damit er besser sieht'], [1], 'Zuerst wird das Rettungsmittel angeboten, nicht der eigene Körper.'],
            ['Wie befreit sich ein Retter am besten aus einer Umklammerung?', ['Abtauchen, denn der Ertrinkende will nach oben und lässt los', 'Den Patienten schlagen', 'Ans Ufer rufen', 'Stillhalten und warten'], [0], 'Durch Abtauchen lockert der Ertrinkende seinen Griff.'],
            ['Was gilt beim Stehen in einem Fließgewässer mit starker Strömung?', ['Man sollte möglichst schnell aufstehen', 'Nicht aufstehen, solange die Strömung schneller als Schrittgeschwindigkeit ist, wegen der Fußfalle', 'Aufstehen ist nur barfuß gefährlich', 'Stehen ist immer sicherer als schwimmen'], [1], 'Ein eingeklemmter Fuß kann dazu führen, dass man vom Wasser niedergedrückt wird.'],
            ['Wie sieht die defensive Schwimmposition im Fließgewässer aus?', ['Bauchlage, Kopf voran', 'Senkrecht im Wasser treten', 'Rückenlage, Füße voran an der Oberfläche, Blick flussabwärts', 'Seitenlage mit angezogenen Beinen'], [2], 'Mit den Füßen voran werden Hindernisse abgefangen.'],
            ['Warum ist eine Walze hinter einem Wehr gefährlich?', ['Sie ist besonders warm', 'Die kreisende Strömung hält Personen fest, aus ihr kommt man kaum heraus', 'Sie zieht nur Boote an', 'Sie ist ungefährlich'], [1], 'Walzen sind lebensgefährlich und werden gemieden.'],
            ['Wohin wird ein Wurfsack geworfen?', ['Über den Patienten hinweg, damit das Seil auf ihm landet', 'Direkt an den Kopf des Patienten', 'Weit stromabwärts vor den Patienten ins Leere', 'Gar nicht, er wird nur gezeigt'], [0], 'Das Seil soll über den Patienten fallen, damit er es greifen kann.'],
            ['Womit beginnt die Reanimation eines Ertrunkenen?', ['Mit 30 Kompressionen', 'Mit dem Ausschütteln des Wassers aus der Lunge', 'Mit fünf initialen Beatmungen', 'Mit Wärmepacks'], [2], 'Ursache ist der Sauerstoffmangel, daher zuerst fünf Beatmungen.'],
            ['Was gilt für einen Patienten nach Beinahe-Ertrinken, der wieder wach ist und gut atmet?', ['Er kann sofort nach Hause gehen', 'Er darf weiter baden', 'Er braucht nur ein Handtuch', 'Er wird immer vom Rettungsdienst untersucht, da sich die Lungenfunktion noch Stunden später verschlechtern kann'], [3], 'Eingeatmetes Wasser kann verzögert zu Lungenproblemen führen.'],
        ],
    ],
    [
        'title' => 'Rettungstaucher',
        'description' => 'Lehrgang zur Rettungstaucherin bzw. zum Rettungstaucher nach dem Vorbild der Einsatztaucher von DLRG und Wasserwacht. Behandelt Tauchphysik, Ausrüstung, Signalmann und Leinenzeichen, Tauchprotokoll, Sicht und Strömung, Dekompression sowie Suche und Bergung im Hafenbecken.',
        'hours' => 14, 'pass_percent' => 80, 'question_count' => 12, 'time_limit' => 30,
        'qualification' => 'Rettungstaucher/in',
        'modules' => [
            ['Tauchphysik und Tauchmedizin', <<<'MD'
# Tauchphysik und Tauchmedizin

Wer taucht, bewegt sich in einer Umgebung, für die der menschliche Körper nicht gemacht ist. Die wichtigsten physikalischen Gesetze muss jeder Rettungstaucher kennen.

## Druck

An der Wasseroberfläche herrscht ein Luftdruck von etwa **1 bar**. Je **10 m Wassertiefe** kommt **1 bar** hinzu.

| Tiefe | Umgebungsdruck |
|---|---|
| 0 m | 1 bar |
| 10 m | 2 bar |
| 20 m | 3 bar |
| 30 m | 4 bar |

## Gesetz von Boyle-Mariotte

Bei gleicher Temperatur ist das **Produkt aus Druck und Volumen konstant**. Ein Luftvolumen von 6 Litern an der Oberfläche hat in 10 m Tiefe nur noch 3 Liter. Daraus folgt:

- Der **Luftverbrauch** steigt mit der Tiefe: In 10 m verbraucht der Taucher doppelt so viel Luft wie an der Oberfläche.
- Die **größte relative Volumenänderung** liegt zwischen 0 und 10 m – gerade im Flachwasser ist also Vorsicht geboten.
- Luft in Körperhöhlen (Mittelohr, Nebenhöhlen, Lunge) wird komprimiert bzw. dehnt sich beim Aufstieg aus.

## Barotraumen

- **Ohren:** Beim Abtauchen muss frühzeitig und regelmäßig **Druckausgleich** gemacht werden (z. B. Valsalva-Manöver). Schmerzen bedeuten: stoppen, etwas aufsteigen, erneut ausgleichen.
- **Lunge:** Beim Aufstieg mit angehaltener Luft dehnt sich die Atemluft aus und kann die Lunge zerreißen (**Lungenüberdruckunfall**). Deshalb gilt: **Beim Aufstieg nie die Luft anhalten**, gleichmäßig weiteratmen.

## Gesetz von Henry

Die Menge eines Gases, die sich in einer Flüssigkeit löst, ist proportional zum Druck. Unter Wasser löst sich deshalb mehr **Stickstoff** im Blut und Gewebe. Steigt man zu schnell auf, perlt er aus – es kommt zur **Dekompressionskrankheit**.

## Stickstoffnarkose

Ab etwa **30 m** kann Stickstoff eine rauschähnliche Wirkung haben („Tiefenrausch“): Urteilsvermögen und Reaktionsfähigkeit lassen nach. Rettungstauchgänge in Los Santos finden meist deutlich flacher statt.

## Wärmeverlust

Wasser entzieht dem Körper etwa **25-mal schneller** Wärme als Luft. Auch im warmen Pazifik vor Los Santos ist bei längeren Tauchgängen ein Kälteschutzanzug erforderlich, in tieferen Schichten der Alamo Sea erst recht.

## Tauchtauglichkeit

Rettungstaucher müssen regelmäßig **ärztlich auf Tauchtauglichkeit** untersucht werden. Bei Erkältung, verstopfter Nase, Alkohol oder Medikamenteneinfluss wird **nicht getaucht**.

*Merke:* Je 10 m ein bar mehr – und beim Aufstieg niemals die Luft anhalten.
MD],
            ['Ausrüstung, Signalmann und Leinenzeichen', <<<'MD'
# Ausrüstung, Signalmann und Leinenzeichen

## Ausrüstung des Rettungstauchers

- **Tauchanzug:** Nasstauchanzug oder Trockentauchanzug, je nach Temperatur und Dauer
- **Vollgesichtsmaske** (bevorzugt in der Einsatztaucherei): schützt vor verschmutztem Wasser und ermöglicht Sprechfunk
- **Drucklufttauchgerät:** Flasche mit Atemregler, Inhalt meist 200 bar
- **Zweitautomat (Oktopus)** für Notfälle
- **Tarierweste** zur Regulierung des Auftriebs
- **Bleigewichte** mit Schnellabwurf
- **Tauchcomputer**, Finimeter (Druckanzeige), Kompass
- **Messer** (gut erreichbar, gegen Verfangen in Leinen und Netzen)
- **Lampe**, auch bei Tag
- **Leinengeschirr** zur Befestigung der Signalleine

Vor jedem Tauchgang folgt der **Gerätecheck**: Flaschendruck, Atemregler, Tarierweste, Bleigurt, Leine.

## Der Signalmann

In der Rettungstaucherei taucht der Taucher in der Regel **an der Leine**. Am anderen Ende steht der **Signalmann** an Land oder im Boot. Er:

- hält die Leine ständig in der Hand, nie um die Hand gewickelt
- führt die Leine leicht gespannt nach, damit Signale spürbar sind
- überwacht Tauchzeit und Richtung
- gibt Suchmuster vor
- alarmiert den **Sicherungstaucher** im Notfall

Der **Sicherungstaucher** ist voll ausgerüstet und kann innerhalb kürzester Zeit ins Wasser gehen. **Kein Einsatztauchgang ohne Signalmann und Sicherungstaucher.**

## Leinenzeichen

Bei schlechter Sicht und ohne Sprechfunk wird über Zugsignale kommuniziert. Typische Leinenzeichen (angelehnt an die Praxis der Einsatztaucher):

| Zeichen | Signalmann an Taucher | Taucher an Signalmann |
|---|---|---|
| **1x ziehen** | Alles in Ordnung? | Alles in Ordnung |
| **2x ziehen** | Weiter nach links/rechts (Richtungswechsel) | Leine nachgeben |
| **3x ziehen** | Auftauchen | Leine einholen |
| **4x ziehen** | – | Gefunden / Hilfe benötigt (je nach Absprache) |
| **Dauerziehen** | Sofort auftauchen (Gefahr) | **Notfall – sofort heraufziehen** |

Jedes Zeichen wird **mit demselben Zeichen bestätigt**. Bleibt eine Bestätigung aus, wird nachgefragt; bleibt sie erneut aus, geht der Sicherungstaucher ins Wasser.

Die genaue Bedeutung der Zeichen wird **vor jedem Tauchgang besprochen**, damit es keine Missverständnisse gibt.

*Merke:* Der Signalmann ist die Lebensversicherung des Tauchers – die Leine wird nie losgelassen.
MD],
            ['Tauchprotokoll, Sicht und Strömung', <<<'MD'
# Tauchprotokoll, Sicht und Strömung

## Das Tauchprotokoll

Jeder Einsatztauchgang wird im **Tauchprotokoll** festgehalten. Der Tauchgruppenführer oder Signalmann führt es während des Einsatzes.

Inhalte:

- Datum, Einsatzort, Einsatznummer
- **Taucher**, **Signalmann**, **Sicherungstaucher**, Tauchgruppenführer
- Wassertemperatur, Sicht, Strömung, Wetter
- **Anfangsdruck** der Flasche (z. B. 200 bar)
- **Abtauchzeit** und **Auftauchzeit**
- **maximale Tiefe**
- **Enddruck** der Flasche
- Suchmuster und Ergebnis
- Besonderheiten (Verfangen, Abbruch, Beschwerden)

Das Protokoll ist Grundlage für die **Planung von Wiederholungstauchgängen** und wird im System als Anhang zum Einsatzbericht gespeichert.

## Luftplanung

Der Taucher beendet den Tauchgang rechtzeitig. Eine bewährte Regel ist die **Drittelregel**: ein Drittel der Luft für den Hinweg, ein Drittel für den Rückweg, ein Drittel als Reserve. Spätestens bei einer **Reserve von 50 bar** wird der Aufstieg eingeleitet.

## Sicht

Im **Hafenbecken** des Port of Los Santos, im Zancudo River oder nach Regen beträgt die Sicht oft **unter einem Meter**, manchmal null. Dann wird **„blind“ getastet**:

- Der Taucher bewegt sich langsam, eine Hand tastet vor, die andere schützt den Kopf.
- Er verlässt sich auf die Leine und die Signale.
- Gefahren wie Metallteile, Glasscherben, Angelschnüre und Netze erkennt er nur durch Tasten – Handschuhe sind Pflicht.

## Strömung

Strömung erhöht den Luftverbrauch und die Gefahr, abgetrieben zu werden.

- Ab etwa **0,5 m/s** (knapp 2 km/h) wird Tauchen deutlich anstrengend, bei stärkerer Strömung ist ein Einsatz oft nur mit besonderer Technik möglich.
- Gegen die Strömung abtauchen, damit der Rückweg leichter fällt.
- Die Leine darf nicht in Hindernissen hängen bleiben.
- Im Fließgewässer ist die Gefahr durch **Siebe und Walzen** zu beachten – hier ist ein Tauchgang ggf. abzulehnen.

## Abbruchkriterien

Ein Tauchgang wird abgebrochen bei:

- Ausfall der Leinenverbindung
- Beschwerden des Tauchers (Kälte, Schmerzen, Panik)
- Erreichen der Luftreserve
- Verschlechterung von Strömung, Wetter oder Sicht
- Gefahr durch Schiffsverkehr, besonders im Hafen

*Merke:* Ein Tauchgang endet mit vollständigem Protokoll – nicht erst, wenn die Flasche leer ist.
MD],
            ['Dekompression und Tauchunfälle', <<<'MD'
# Dekompression und Tauchunfälle

## Dekompression

Während des Tauchgangs löst sich Stickstoff im Gewebe (Gesetz von Henry). Beim Aufstieg muss er langsam über die Lunge abgeatmet werden. Deshalb gilt:

- **Aufstiegsgeschwindigkeit** maximal ca. **10 m pro Minute**, auf den letzten Metern langsamer.
- **Sicherheitsstopp** von 3 Minuten in 3 bis 5 m Tiefe bei jedem Tauchgang tiefer als etwa 10 m.
- **Nullzeit beachten:** Die Nullzeit ist die Zeit, die man in einer bestimmten Tiefe bleiben kann, ohne beim Aufstieg Dekompressionsstopps zu benötigen. Sie wird vom Tauchcomputer angezeigt.
- Rettungstauchgänge sind grundsätzlich als **Nullzeittauchgänge** zu planen.

## Wiederholungstauchgänge

Nach einem Tauchgang ist noch Reststickstoff im Körper. Folgetauchgänge haben kürzere Nullzeiten. Nach mehreren Tauchgängen ist eine **Oberflächenpause** einzuhalten. **Fliegen nach dem Tauchen** (z. B. Transport mit dem Hubschrauber) ist erst nach einer Wartezeit von mindestens 12 bis 24 Stunden unbedenklich. Das ist wichtig, wenn ein Taucher im Einsatz anschließend per Hubschrauber verlegt werden soll.

## Dekompressionskrankheit

Perlt Stickstoff im Körper aus, entstehen Gasblasen in Gewebe und Blutgefäßen.

**Zeichen** (oft erst Minuten bis Stunden nach dem Tauchgang):
- Hautjucken, Hautflecken
- Gelenk- und Gliederschmerzen
- Taubheitsgefühle, Lähmungen
- Schwindel, Seh- und Sprachstörungen
- Atemnot, Bewusstlosigkeit

## Lungenüberdruckunfall und arterielle Gasembolie

Ein zu schneller Aufstieg mit angehaltener Luft kann die Lunge schädigen. Luft gelangt ins Blut und kann Gefäße im Gehirn verschließen – Symptome ähneln einem Schlaganfall und treten meist **sofort** nach dem Auftauchen auf.

## Erste Hilfe beim Tauchunfall

1. **Notruf** und Meldung „Tauchunfall“ – Druckkammer wird über den Rettungsdienst angefragt.
2. **100 % Sauerstoff** so früh wie möglich, egal wie leicht die Symptome erscheinen.
3. Patient **flach lagern**, bei Bewusstlosigkeit stabile Seitenlage.
4. Wärmeerhalt.
5. Bei Bewusstsein: Flüssigkeit trinken lassen.
6. Bei Kreislaufstillstand: Reanimation.
7. **Tauchcomputer und Tauchprotokoll** mit dem Patienten übergeben.

Ein **erneutes Abtauchen** zur „nassen Rekompression“ ist verboten.

*Merke:* Jeder Verdacht auf Tauchunfall bekommt Sauerstoff – sofort und hochdosiert.
MD],
            ['Suche und Bergung im Hafenbecken', <<<'MD'
# Suche und Bergung im Hafenbecken

## Einsatzort Port of Los Santos

Das **Hafenbecken** im Süden von Los Santos ist ein typisches Einsatzgebiet: Fahrzeuge stürzen von Kais, Personen fallen von Schiffen oder springen ins Wasser. Bedingungen:

- Tiefe meist 8 bis 15 m
- Sicht oft unter 1 m, schlammiger Grund
- **Schiffsverkehr** und Schraubenwasser
- Hindernisse: Kaimauern, Spundwände, Leinen, Schrott

Vor jedem Tauchgang lässt die Leitstelle über den Hafen den **Schiffsverkehr im Suchbereich einstellen**. Das Taucherboot setzt die Flagge **Alpha** („Ich habe Taucher unten, halten Sie Abstand“).

## Suchmuster

| Muster | Beschreibung | Einsatz |
|---|---|---|
| **Halbkreissuche** | Signalmann am Ufer, Taucher schwimmt im Bogen, nach jedem Bogen Leine verlängern | Suche von der Kaimauer aus |
| **Kreissuche** | Mittelpunkt mit Gewicht/Boje, Taucher zieht Kreise mit wachsendem Radius | letzte bekannte Position bekannt |
| **Pendelsuche / Streifensuche** | Taucher pendelt zwischen zwei Linien | große Flächen |
| **Leinensuche mit mehreren Tauchern** | Taucher an einer Querleine nebeneinander | flaches, großes Gebiet |

Der Abstand der Bahnen richtet sich nach der Sicht: Bei null Sicht wird nur so weit versetzt, wie der Taucher mit ausgestrecktem Arm tasten kann.

## Lebensrettung oder Bergung

Es wird unterschieden:

- **Rettung:** Es besteht noch Aussicht, eine lebende Person zu finden. Bei Ertrinken gilt ein Zeitfenster von etwa **60 bis 90 Minuten**, in kaltem Wasser auch länger. Hier wird schnell und mit erhöhtem Einsatz gearbeitet, aber nie ohne Sicherung.
- **Bergung:** Ein Überleben ist ausgeschlossen. Hier steht die Sicherheit der Taucher absolut im Vordergrund, es gibt keinen Zeitdruck.

## Bergung von Verstorbenen

- Vor der Bergung wird die **Polizei (LSPD) informiert**, da eine Straftat nicht ausgeschlossen werden kann.
- Fundlage und Fundort werden möglichst genau festgehalten (Tiefe, Position, Bezug zur Kaimauer).
- Der Körper wird **würdevoll** und möglichst ohne Veränderung geborgen, z. B. in einem Bergesack.
- Gegenstände in der Umgebung werden nicht entfernt, sondern nur gemeldet.
- Fahrzeuge werden in Abstimmung mit Polizei und LSFD mit Hebesäcken oder Kran geborgen. Taucher halten sich dabei **nie unter schwebenden Lasten** auf.

## Nachsorge

Bergungen sind psychisch belastend. Nach dem Einsatz wird das Angebot der Einsatznachsorge genutzt, und der Einsatz wird im Team nachbesprochen.

*Merke:* Bei der Bergung gibt es keinen Zeitdruck – die Sicherheit der Taucher geht vor, und die Polizei ist immer informiert.
MD],
        ],
        'questions' => [
            ['Wie hoch ist der Umgebungsdruck in 20 m Wassertiefe?', ['2 bar', '3 bar', '4 bar', '20 bar'], [1], '1 bar Luftdruck plus 1 bar je 10 m Wassertiefe ergibt 3 bar.'],
            ['Wie verändert sich der Luftverbrauch in 10 m Tiefe im Vergleich zur Oberfläche?', ['Er halbiert sich', 'Er bleibt gleich', 'Er verdoppelt sich', 'Er verzehnfacht sich'], [2], 'Bei doppeltem Druck wird je Atemzug die doppelte Luftmenge benötigt (Boyle-Mariotte).'],
            ['Warum darf beim Aufstieg nie die Luft angehalten werden?', ['Weil sich die Atemluft ausdehnt und die Lunge reißen kann', 'Weil man sonst schneller sinkt', 'Weil die Maske beschlägt', 'Weil der Tauchcomputer sonst ausfällt'], [0], 'Es droht ein Lungenüberdruckunfall mit Gasembolie.'],
            ['Welches Gesetz erklärt die Entstehung der Dekompressionskrankheit?', ['Gesetz von Ohm', 'Gesetz von Newton', 'Gesetz von Archimedes', 'Gesetz von Henry'], [3], 'Unter Druck löst sich mehr Stickstoff im Gewebe, der bei zu schnellem Aufstieg ausperlt.'],
            ['Welche Aufgaben hat der Signalmann?', ['Leine ständig führen und leicht gespannt halten', 'Tauchzeit überwachen', 'Bei Notfall den Sicherungstaucher alarmieren', 'Die Leine an einem Poller festbinden und Pause machen'], [0, 1, 2], 'Der Signalmann hält die Leine immer in der Hand und überwacht den Taucher.'],
            ['Was bedeutet Dauerziehen vom Taucher an den Signalmann?', ['Alles in Ordnung', 'Notfall – sofort heraufziehen', 'Leine nachgeben', 'Richtungswechsel'], [1], 'Dauerziehen ist das Notsignal.'],
            ['Was geschieht, wenn ein Leinenzeichen auch nach Nachfrage nicht bestätigt wird?', ['Der Signalmann wartet einfach weiter', 'Der Einsatz wird beendet und alle gehen nach Hause', 'Der Sicherungstaucher geht ins Wasser', 'Der Signalmann lässt die Leine los'], [2], 'Ohne Bestätigung wird von einem Notfall ausgegangen.'],
            ['Welche Angaben gehören ins Tauchprotokoll?', ['Anfangs- und Enddruck der Flasche', 'Abtauch- und Auftauchzeit', 'Maximale Tiefe', 'Lieblingsmusik des Tauchers'], [0, 1, 2], 'Druck, Zeiten, Tiefe und Besetzung sind Pflichtangaben.'],
            ['Wie lautet die Drittelregel der Luftplanung?', ['Ein Drittel Hinweg, ein Drittel Rückweg, ein Drittel Reserve', 'Ein Drittel der Taucher bleibt an Land', 'Jeder dritte Tauchgang entfällt', 'Nach einem Drittel der Zeit wird das Suchmuster gewechselt'], [0], 'So bleibt immer ausreichend Reserve für Notfälle.'],
            ['Welche maximale Aufstiegsgeschwindigkeit gilt als Richtwert?', ['30 m pro Minute', '10 m pro Minute', '1 m pro Minute', 'So schnell wie möglich'], [1], 'Etwa 10 m pro Minute, auf den letzten Metern langsamer.'],
            ['Was ist die wichtigste Erste-Hilfe-Maßnahme bei Verdacht auf einen Tauchunfall?', ['Erneutes Abtauchen zur Rekompression', 'Warme Dusche', 'Abwarten, ob Symptome verschwinden', '100 % Sauerstoff so früh wie möglich'], [3], 'Sauerstoff hilft beim Abbau der Gasblasen und wird bei jedem Verdacht gegeben.'],
            ['Welches Suchmuster eignet sich, wenn die letzte bekannte Position im Hafenbecken markiert ist?', ['Kreissuche um den markierten Punkt', 'Zufällige Suche', 'Suche nur an der Oberfläche', 'Gar keine Suche'], [0], 'Die Kreissuche mit wachsendem Radius beginnt an der letzten bekannten Position.'],
            ['Was ist vor der Bergung eines Verstorbenen im Hafenbecken zu tun?', ['Den Körper sofort ohne Absprache bergen und Gegenstände einsammeln', 'Die Polizei informieren und Fundort und Fundlage genau festhalten', 'Die Presse informieren', 'Den Fundort nicht dokumentieren'], [1], 'Eine Straftat ist nicht ausgeschlossen, daher wird die Polizei informiert und dokumentiert.'],
            ['Wo darf sich ein Taucher bei der Bergung eines Fahrzeugs nie aufhalten?', ['Im Boot', 'An der Kaimauer', 'Unter schwebenden Lasten', 'Neben dem Signalmann'], [2], 'Hebesäcke oder Kranlasten können abstürzen.'],
        ],
    ],
    [
        'title' => 'Bootsführer Rettungsboot',
        'description' => 'Lehrgang zur Bootsführerin bzw. zum Bootsführer für die Rettungsboote der TAS nach dem Vorbild der Wasserrettungsdienste von DLRG und Wasserwacht. Behandelt Bootskunde, Manöver, das Mensch-über-Bord-Manöver, Anlegen, Navigation sowie den Einsatz mit Rettungstauchern.',
        'hours' => 12, 'pass_percent' => 80, 'question_count' => 12, 'time_limit' => 25,
        'qualification' => 'Bootsführer/in',
        'modules' => [
            ['Bootskunde und Sicherheit', <<<'MD'
# Bootskunde und Sicherheit

## Bootstypen der TAS

| Boot | Eigenschaften | Einsatzgebiet |
|---|---|---|
| **Rettungsboot (RTB)** | Festrumpfschlauchboot (RIB), 6–8 m, Außenborder, Besatzung 2–4 | Küste Vespucci, Del Perro, Port of Los Santos |
| **Kleines Schlauchboot** | leicht, per Trailer oder von Hand zu Wasser, flacher Tiefgang | Alamo Sea, Zancudo River |
| **Jetski mit Rettungsschlitten** | sehr schnell und wendig | Brandungszone, Strandwache |

## Begriffe

- **Bug:** vorderes Ende des Bootes
- **Heck:** hinteres Ende
- **Backbord:** links in Fahrtrichtung (rote Positionslaterne)
- **Steuerbord:** rechts in Fahrtrichtung (grüne Positionslaterne)
- **Luv:** die dem Wind zugewandte Seite
- **Lee:** die dem Wind abgewandte Seite
- **Tiefgang:** wie tief das Boot ins Wasser ragt

## Ausrüstung an Bord

- Rettungswesten für alle Personen an Bord und für Patienten
- Rettungsring oder Wurfleine, Bootshaken
- Anker mit Leine, Paddel
- Erste-Hilfe-Ausstattung, Sauerstoff, AED, Rettungsdecken
- Funkgerät (Betriebsfunk und Seefunk), Lampe, Signalmittel
- Lenzpumpe, Werkzeug, Reservekraftstoff
- **Quickstopp (Notstoppschalter)** mit Reißleine

## Die Reißleine

Der Bootsführer ist während der Fahrt **immer mit der Reißleine (Killswitch)** am Körper verbunden. Geht er über Bord, stoppt der Motor sofort. Ein führerloses, kreisendes Boot ist eine tödliche Gefahr für die Person im Wasser.

## Pflichten des Bootsführers

- Er ist für **Boot, Besatzung und Patienten verantwortlich**.
- Er prüft vor dem Auslaufen: Kraftstoff, Motor, Ausrüstung, Wetter, Rettungswesten an.
- Er hält die **Ausweichregeln** ein: Grundsätzlich weicht aus, wer den anderen an Steuerbord hat (Steuerbord vor Backbord); Fahrzeuge unter Segel und manövrierunfähige Schiffe haben Vorrang. Im Hafen hat die Großschifffahrt Vorrang.
- Er passt die Geschwindigkeit an: In Badezonen, im Hafen und nahe Tauchern wird **langsam** gefahren.

## Besatzung

- **Bootsführer:** fährt und führt.
- **Bootsgast / Rettungsschwimmer:** Ausguck, Rettung der Person, Bedienung der Leinen.
- **Weitere Kräfte:** Versorgung des Patienten, Funk.

*Merke:* Kein Ablegen ohne Reißleine und ohne Rettungsweste für alle.
MD],
            ['Manöver und Anlegen', <<<'MD'
# Manöver und Anlegen

## Grundlagen der Bootsführung

Ein Boot hat **keine Bremse**. Gestoppt wird durch Gas wegnehmen und kurzes **Rückwärtsfahren**. Gesteuert wird ein Außenborder über das Heck: Das Heck schwenkt aus, der Bug folgt. Bei langsamer Fahrt ist das Boot weniger steuerfähig, da die Ruderwirkung von der Anströmung abhängt.

**Wind und Strömung** versetzen das Boot. Der Bootsführer beobachtet daher ständig:

- Woher kommt der Wind? (Flaggen, Wellen, Rauch)
- Wohin läuft die Strömung? (Bojen, Treibgut, Wasser an Pfählen)

Ein Manöver wird möglichst **gegen Wind oder Strömung** gefahren, denn so lässt sich das Boot langsamer und genauer steuern.

## Wichtige Manöver

- **Aufstoppen:** Auskuppeln, Fahrt laufen lassen, kurz rückwärts einkuppeln.
- **Wenden auf engem Raum:** Wechselweise vorwärts und rückwärts mit voll eingeschlagenem Motor.
- **Ansteuern einer Person:** Langsam, gegen den Wind, und so, dass die Person auf der Seite des Bootsführers liegt.
- **Fahrt in Wellen:** Wellen schräg von vorn nehmen, nicht genau quer – sonst droht Kentern.

## Anlegen

Vor dem Anlegen werden **Fender** ausgebracht und **Festmacherleinen** vorbereitet.

1. Anlegestelle beobachten: Platz, Wind, Strömung, Wellen.
2. Langsam und in spitzem Winkel (ca. 30–45°) **gegen Wind oder Strömung** an den Steg fahren.
3. Kurz vor dem Steg auskuppeln und durch kurzes Rückwärtsfahren aufstoppen; das Heck schwenkt dabei heran.
4. Der Bootsgast steigt **nicht springend** über, sondern tritt erst über, wenn das Boot ruhig am Steg liegt.
5. Zuerst die Leine festmachen, die gegen Wind oder Strömung hält.

**Niemals** darf ein Körperteil zwischen Boot und Steg geraten – Hand und Fuß sind keine Fender.

## Ablegen

- Leinen in umgekehrter Reihenfolge lösen.
- Mit dem Wind bzw. der Strömung vom Steg wegdrehen lassen.
- Bei auflandigem Wind zuerst das Heck freifahren (rückwärts eindampfen in die Achterspring).

## Anlegestellen im Einsatz

- **Del Perro Pier**: Übergabesteg an SAMS
- **Vespucci-Kanäle**: enge Kanäle, nur Schrittgeschwindigkeit
- **Port of Los Santos**: auf Großschifffahrt achten
- **Alamo Sea**: Stege bei Sandy Shores, flaches Ufer – auf Tiefgang achten

*Merke:* Langsam heran, gegen Wind oder Strömung, Fender raus und nie springen.
MD],
            ['Mensch über Bord', <<<'MD'
# Mensch über Bord

Das **Mensch-über-Bord-Manöver (MOB)** ist das wichtigste Manöver eines Rettungsbootes. Es wird nicht nur bei eigener Besatzung angewendet, sondern ist das Grundprinzip für jede Aufnahme einer Person aus dem Wasser.

## Sofortmaßnahmen

1. **Laut rufen:** „Mensch über Bord an Backbord/Steuerbord!“
2. **Auskuppeln** bzw. Gas weg, damit die Schraube die Person nicht verletzt. Das Heck wird **von der Person weggedreht** – also Ruder zur Seite der Person.
3. **Rettungsmittel werfen:** Rettungsring oder Wurfleine in die Nähe der Person.
4. **Ausguck bestimmen:** Eine Person zeigt **ununterbrochen** mit ausgestrecktem Arm auf den Menschen im Wasser und lässt ihn nicht aus den Augen.
5. **MOB-Taste** am GPS drücken, um die Position zu speichern.
6. **Funkmeldung** an die Leitstelle.

## Anfahrt

- Die Person wird **gegen den Wind** bzw. gegen die Strömung angefahren, damit das Boot nicht auf sie treibt.
- Die Person liegt am Ende auf der **Seite des Bootsführers**, damit er sie sehen kann.
- Kurz vorher **auskuppeln**; das Boot läuft langsam an die Person heran.
- Bei Wellengang wird die Person auf der **Lee-Seite** des Bootes aufgenommen, wo das Boot sie vor Wind und Wellen schützt.

## Aufnahme

- **Motor ausgekuppelt** während der Aufnahme, bei Gefahr Motor ganz stoppen.
- Die Person wird an der Seite neben dem Bug oder an der Bordwand mittschiffs aufgenommen, **nie am Heck** (Schraube).
- Erschöpfte oder bewusstlose Personen werden mit zwei Kräften an Händen und Weste über die Bordwand gezogen. Bei Unterkühlung möglichst **horizontal** bergen, z. B. mit Bergenetz oder Bergeschlaufe.
- Bei Verdacht auf Wirbelsäulenverletzung: Spineboard im Wasser unterschieben.

## Nach der Aufnahme

- Patient vor Wind schützen, Rettungsdecke, Erstversorgung.
- **Status 7** und Funkmeldung mit Übergabepunkt.
- Ruhig und ohne harte Schläge über die Wellen zum Übergabesteg fahren.

## Typische Fehler

- Person aus den Augen verloren, weil niemand als Ausguck bestimmt wurde.
- Zu schnelle Anfahrt und Überfahren der Person.
- Motor eingekuppelt während der Aufnahme.
- Aufnahme an der Luv-Seite, sodass das Boot auf die Person gedrückt wird.

*Merke:* Rufen – Auskuppeln – Werfen – Zeigen. Die Schraube bleibt weit weg vom Menschen.
MD],
            ['Navigation', <<<'MD'
# Navigation

Navigation bedeutet, jederzeit zu wissen, **wo man ist**, **wohin man will** und **welche Gefahren** auf dem Weg liegen.

## Betonnung und Seezeichen

Rund um den **Port of Los Santos** sind Fahrwasser durch Tonnen markiert. Es gilt das System der Region A (wie in Europa), das in der Ausbildung der TAS verwendet wird:

| Zeichen | Bedeutung |
|---|---|
| **Rote Tonne** (Stumpftonne) | Backbordseite des Fahrwassers bei Einfahrt vom Meer |
| **Grüne Tonne** (Spitztonne) | Steuerbordseite des Fahrwassers bei Einfahrt vom Meer |
| **Gelbe Tonne** | Sondergebiet, z. B. Badezone oder Wassersportgebiet |
| **Schwarz-gelb** (Kardinaltonne) | Gefahrenstelle, Seite durch Toppzeichen angegeben |

**Merkspruch:** Bei der Einfahrt in den Hafen liegt Rot links.

## Positionsbestimmung

- **GPS / Kartenplotter:** Hauptmittel zur Positionsbestimmung. Positionen werden in Koordinaten oder als Kartenbezug angegeben.
- **Landmarken:** Del Perro Pier, Riesenrad am Pier, Leuchttürme, Hafenkräne, Mount Chiliad als Peilpunkt im Norden.
- **Kompass:** Kurs und Peilung bei Ausfall der Elektronik.
- **Echolot:** Zeigt die Wassertiefe; wichtig in der flachen Alamo Sea und in der Mündung des Zancudo River.

## Einsatznavigation

Bei der Alarmierung erhält der Bootsführer eine Position. Er plant:

1. **Kürzester sicherer Weg** – nicht immer die Luftlinie. Untiefen, Sperrgebiete (z. B. vor **Fort Zancudo**) und Fahrwasser der Großschifffahrt beachten.
2. **Geschwindigkeit** an Seegang und Sicht anpassen.
3. **Übergabepunkt** für SAMS festlegen, z. B. Del Perro Pier oder Steg am Hafen.

## Fahrt bei Nacht und schlechter Sicht

- **Positionslaternen** einschalten: rot Backbord, grün Steuerbord, weiß Hecklicht bzw. Topplicht.
- Blaulicht als Sondersignal im Einsatz.
- Geschwindigkeit deutlich reduzieren, zusätzlichen Ausguck einteilen.
- Suchscheinwerfer kurz und gezielt einsetzen, nicht andere Schiffsführer blenden.

## Wetter

Vor dem Auslaufen wird der **Wetterbericht** geprüft. Starker Wind, Gewitter oder hoher Seegang können ein Auslaufen unmöglich machen. Die Entscheidung über das Auslaufen trifft der Bootsführer – er kann eine Fahrt aus Sicherheitsgründen **ablehnen**.

*Merke:* Rot links bei der Einfahrt – und wer nicht weiß, wo er ist, fährt langsam.
MD],
            ['Einsatz mit Rettungstauchern', <<<'MD'
# Einsatz mit Rettungstauchern

Das Rettungsboot ist häufig die **Arbeitsplattform** für Rettungstaucher, etwa bei Suchen in der Alamo Sea oder vor dem Del Perro Pier. Der Bootsführer trägt dabei besondere Verantwortung.

## Vorbereitung

- Absprache mit dem **Tauchgruppenführer**: Suchgebiet, Suchmuster, Anzahl der Taucher, Zeichen.
- Platz an Bord schaffen für Flaschen, Leinen und Sicherungstaucher.
- **Taucherflagge Alpha** (weiß-blau) setzen: „Ich habe Taucher unten – halten Sie Abstand und fahren Sie langsam.“
- Andere Boote und gegebenenfalls Hafenbehörde über Funk informieren.

## Positionierung

- Das Boot wird an der **letzten bekannten Position** verankert oder mit einer Boje markiert.
- **Verankern:** Anker gegen Wind bzw. Strömung ausbringen, ausreichend Leine (etwa dreifache Wassertiefe), Boot treibt an die gewünschte Position.
- Alternativ hält der Bootsführer das Boot mit dem Motor auf Position – nur mit großer Vorsicht und ausreichend Abstand zur Taucherleine.

## Motor und Schraube

Die Schraube ist die größte Gefahr für einen Taucher an der Oberfläche.

- Während Taucher im Wasser sind, ist der **Motor ausgekuppelt**.
- Beim Auftauchen und beim Aufnehmen der Taucher ist der Motor **aus**.
- Taucher steigen am Bug oder seitlich ein, **nie in der Nähe der Schraube**.
- Der Bootsführer achtet ständig auf **Luftblasen** und die Taucherleine.

## Arbeit an Bord

- Der **Signalmann** kann vom Boot aus arbeiten; er sitzt stabil und hat freie Sicht auf Leine und Wasser.
- Der **Sicherungstaucher** sitzt voll ausgerüstet bereit.
- Der Bootsführer führt Funkverkehr mit der Leitstelle und behält Schiffsverkehr und Wetter im Blick.

## Aufnahme eines Tauchers

1. Taucher signalisiert Auftauchen.
2. Motor aus.
3. Taucher reicht Gewichte und Tauchgerät an, dann steigt er über Leiter oder Bordwand ein.
4. Bei einem **Tauchunfall** sofort Sauerstoff, Status melden und auf kürzestem Weg zum Übergabepunkt.

## Nach dem Einsatz

- Material sichern, Flaschendrücke im Tauchprotokoll vermerken.
- Boot reinigen, betanken, Mängel melden.
- Fahrtenbuch bzw. Bootsprotokoll im System ausfüllen.

*Merke:* Taucher unten heißt: Flagge Alpha, Motor ausgekuppelt, Augen auf die Blasen.
MD],
        ],
        'questions' => [
            ['Welche Seite eines Bootes ist Backbord?', ['Rechts in Fahrtrichtung', 'Links in Fahrtrichtung', 'Vorne', 'Hinten'], [1], 'Backbord ist links, Steuerbord rechts in Fahrtrichtung.'],
            ['Welche Farbe hat die Positionslaterne an Steuerbord?', ['Rot', 'Weiß', 'Grün', 'Blau'], [2], 'Steuerbord grün, Backbord rot.'],
            ['Wozu dient die Reißleine (Killswitch) am Bootsführer?', ['Zum Festmachen am Steg', 'Zum Schleppen von Patienten', 'Als Ersatz für das Ankertau', 'Sie stoppt den Motor, wenn der Bootsführer über Bord geht'], [3], 'Ein führerloses, kreisendes Boot ist eine tödliche Gefahr.'],
            ['Wie wird ein Anlegemanöver idealerweise gefahren?', ['Mit Vollgas quer zum Steg', 'Langsam, im spitzen Winkel und gegen Wind oder Strömung', 'Mit dem Wind im Rücken, damit es schneller geht', 'Rückwärts mit voller Fahrt'], [1], 'Gegen Wind oder Strömung lässt sich das Boot langsam und genau steuern.'],
            ['Was gilt beim Anlegen für den Bootsgast?', ['Er springt frühzeitig auf den Steg', 'Er hält das Boot mit dem Fuß vom Steg ab', 'Er tritt erst über, wenn das Boot ruhig am Steg liegt', 'Er bleibt immer im Wasser'], [2], 'Hände und Füße sind keine Fender, gesprungen wird nicht.'],
            ['Was sind die ersten Maßnahmen beim Mensch-über-Bord-Manöver?', ['Laut rufen und Seite angeben', 'Auskuppeln und Heck von der Person wegdrehen', 'Rettungsmittel werfen und Ausguck bestimmen', 'Mit Vollgas weiterfahren, um Anlauf zu nehmen'], [0, 1, 2], 'Rufen – Auskuppeln – Werfen – Zeigen.'],
            ['Welche Aufgabe hat der Ausguck beim MOB-Manöver?', ['Er bedient den Motor', 'Er zeigt ununterbrochen auf die Person im Wasser', 'Er funkt mit der Leitstelle', 'Er holt den Anker ein'], [1], 'Die Person darf nie aus den Augen verloren werden.'],
            ['Wo wird eine Person nie ins Boot aufgenommen?', ['Am Heck in der Nähe der Schraube', 'An der Bordwand mittschiffs', 'Seitlich neben dem Bug', 'Auf der Lee-Seite'], [0], 'Die Schraube ist lebensgefährlich, Aufnahme nie am Heck.'],
            ['Was bedeutet eine rote Tonne bei der Einfahrt vom Meer in den Hafen (Region A)?', ['Steuerbordseite des Fahrwassers', 'Badezone', 'Backbordseite des Fahrwassers', 'Ankerplatz'], [2], 'Merkspruch: Bei der Einfahrt liegt Rot links.'],
            ['Wozu dient das Echolot?', ['Zur Messung der Wassertiefe', 'Zur Bestimmung der Windrichtung', 'Zum Funken', 'Zum Ortsbestimmen per Satellit'], [0], 'Das Echolot zeigt die Wassertiefe, wichtig in flachen Gewässern wie der Alamo Sea.'],
            ['Wer entscheidet, ob ein Rettungsboot bei schlechtem Wetter ausläuft?', ['Der Patient', 'Die Presse', 'Der Hafenmeister allein', 'Der Bootsführer, der aus Sicherheitsgründen ablehnen kann'], [3], 'Der Bootsführer ist für Boot und Besatzung verantwortlich.'],
            ['Was bedeutet die Flagge Alpha (weiß-blau)?', ['Ich habe Taucher unten – Abstand halten und langsam fahren', 'Boot zu verkaufen', 'Ich benötige Kraftstoff', 'Badeverbot am Strand'], [0], 'Die Flagge Alpha warnt vor Tauchern im Wasser.'],
            ['In welchem Zustand ist der Motor, während Taucher im Wasser sind?', ['Mit Vollgas eingekuppelt', 'Ausgekuppelt, beim Aufnehmen der Taucher aus', 'Im Rückwärtsgang', 'Das spielt keine Rolle'], [1], 'Die Schraube ist die größte Gefahr für Taucher an der Oberfläche.'],
        ],
    ],
    [
        'title' => 'Luftretter / Windenoperator',
        'description' => 'Lehrgang zur Luftretterin bzw. zum Luftretter mit Ausbildung als Windenoperator nach dem Vorbild der deutschen Luftrettung und Bergwacht. Behandelt Rettungswinde, Bergetau und Rettungsschlinge, Downwash, Landeplatzeinweisung, Crew Resource Management, Absturzsicherung im Hubschrauber sowie die Übergabe an SAMS am Pillbox-Dach.',
        'hours' => 14, 'pass_percent' => 85, 'question_count' => 12, 'time_limit' => 30,
        'qualification' => 'Luftretter/in',
        'modules' => [
            ['Rettungswinde und Windenbetrieb', <<<'MD'
# Rettungswinde und Windenbetrieb

Die **Rettungswinde** ermöglicht es, Retter und Patienten an Orten aufzunehmen, an denen der Hubschrauber nicht landen kann: in Schluchten des **Raton Canyon**, an Steilhängen des **Mount Chiliad**, auf Booten oder im offenen Wasser vor der Küste.

## Aufbau

- **Windenausleger** an der Seite des Hubschraubers
- **Windenseil** aus Stahl, je nach Typ 50 bis 90 m lang
- **Lasthaken** mit Sicherung am Seilende
- **Bedienelemente** für den Windenoperator (Hand-Bedienteil) und eine Notbedienung für den Piloten
- **Seilschneidvorrichtung (Cable Cutter)**: Im Notfall kann das Seil gekappt werden, wenn es sich verfängt und den Hubschrauber gefährdet

Die maximale Last liegt typischerweise bei etwa **270 kg** – genug für Retter und Patient in der Trage, aber nicht mehr.

## Rollen der Besatzung

| Rolle | Aufgabe |
|---|---|
| **Pilot** | Führt den Hubschrauber, hält die Schwebeposition, hat die Gesamtverantwortung |
| **Windenoperator** | Bedient die Winde, beobachtet Seil und Last, gibt dem Piloten Kommandos |
| **Luftretter** | Hängt am Seil, versorgt und sichert den Patienten am Boden |
| **Notarzt / Notfallsanitäter** | Medizinische Versorgung, ggf. ebenfalls am Seil |

## Ablauf einer Windenrettung

1. **Erkundung** aus der Luft: Lage des Patienten, Hindernisse, Wind, Abwindfläche.
2. **Briefing** an Bord: Wer geht am Seil hinab, welches Bergemittel, welche Zeichen.
3. **Sicherheitscheck**: Gurte, Karabiner, Haken, Funk.
4. **Ablassen** des Luftretters, der Windenoperator meldet Abstände an den Piloten.
5. **Bodenkontakt**: Luftretter hängt sich aus oder bleibt eingehängt (je nach Lage).
6. **Versorgung** und Vorbereitung des Patienten.
7. **Aufnahme** von Patient und Retter.
8. **Einholen** und Sicherung an Bord.

## Statische Aufladung

Der Hubschrauber lädt sich durch die Rotorbewegung **elektrostatisch** auf. Bevor der Haken am Boden berührt wird, muss er **Erdkontakt** gehabt haben, sonst droht ein schmerzhafter Stromschlag. Bodenpersonal greift nie in den Haken, bevor er den Boden oder das Wasser berührt hat.

## Grenzen

- Starker oder böiger Wind, Nebel und Dunkelheit ohne Nachtsichtausrüstung
- Hindernisse wie Stromleitungen und Seilbahnkabel (z. B. Seilbahn am Mount Chiliad)
- Zu große Höhe oder Leistungsgrenze des Hubschraubers bei Hitze

*Merke:* Der Haken berührt zuerst den Boden – dann erst der Mensch.
MD],
            ['Bergetau, Rettungsschlinge und Bergemittel', <<<'MD'
# Bergetau, Rettungsschlinge und Bergemittel

Je nach Lage und Zustand des Patienten wird ein passendes **Bergemittel** gewählt.

## Rettungsschlinge (Bergeschlinge)

Die **Rettungsschlinge** ist ein gepolsterter Gurt, der unter den Achseln um den Oberkörper gelegt wird.

- Geeignet für **wache, unverletzte oder leicht verletzte** Personen
- Schnell anzulegen, ideal im Wasser oder bei Bootsbesatzungen
- Der Patient hält die Arme **unten**, nicht nach oben – sonst kann er herausrutschen
- Wird immer zusammen mit einem Luftretter verwendet, der den Patienten umfasst (**Doppelbergung**)

**Achtung:** Bei Personen, die lange in kaltem Wasser waren, wird die senkrechte Bergung in der Schlinge möglichst vermieden. Durch die aufrechte Haltung kann der Kreislauf zusammenbrechen. Besser ist eine **horizontale Bergung** mit Bergesack, Doppelschlinge oder Trage.

## Bergesitz / Rettungsdreieck

Das **Rettungsdreieck** ist ein Sitzgurt, der schnell angelegt werden kann. Der Patient sitzt darin sicherer als in der Schlinge. Geeignet für Personen mit leichten Verletzungen, die nicht liegend transportiert werden müssen.

## Bergesack und Gebirgstrage

Für **schwer verletzte** Patienten, bei Wirbelsäulenverdacht oder Unterkühlung:

- Patient wird in **Vakuummatratze** gelagert
- Dann in den **Bergesack** oder die **Trage** eingeschnallt
- Aufhängung über eine **Tragespinne** mit vier Gurten
- Eine **Führungsleine (Tagline)** verhindert, dass sich die Trage unter dem Hubschrauber dreht

Drehen ist gefährlich: Die Trage kann in Rotation geraten und den Patienten schwer schädigen. Die Führungsleine wird vom Bodenpersonal gehalten und vor dem Einholen gelöst bzw. mit einer Sollbruchstelle versehen.

## Bergetau (Fixtau)

Bei manchen Hubschraubern wird statt einer Winde ein **Bergetau** (festes Tau mit Lasthaken) verwendet. Die Länge ist fest, der Pilot steuert die Höhe. Der Patient wird **nicht ins Innere** geholt, sondern hängend zu einem nahen, sicheren Absetzplatz geflogen (z. B. Parkplatz an der Talstation am Mount Chiliad), wo er umgelagert wird.

## Auswahl des Bergemittels

| Patient | Bergemittel |
|---|---|
| wach, unverletzt | Rettungsschlinge oder Rettungsdreieck |
| leicht verletzt | Rettungsdreieck mit Luftretter |
| schwer verletzt, Wirbelsäulenverdacht | Vakuummatratze + Trage/Bergesack |
| unterkühlt nach Wasserunfall | horizontale Bergung |
| bewusstlos | Trage oder Bergesack |

*Merke:* Kalte Patienten liegen – nie senkrecht in der Schlinge.
MD],
            ['Downwash und Landeplatzeinweisung', <<<'MD'
# Downwash und Landeplatzeinweisung

## Downwash

Der Rotor drückt Luft mit großer Kraft nach unten. Dieser **Abwind (Downwash)** erreicht im Schwebeflug Windgeschwindigkeiten, die einem **Sturm** entsprechen.

Gefahren:

- **Fliegende Gegenstände:** Planen, Decken, Kleidung, Äste, Sand, Steine
- **Steinschlag** an Hängen, ausgelöst durch den Abwind
- **Sichtbehinderung** durch aufgewirbelten Staub oder Sand (Brownout), z. B. in der Grand Senora Desert oder am Strand von Vespucci
- **Auskühlung** des Patienten
- **Wellen und Gischt** am Wasser, die den Patienten untertauchen können
- Umgeworfene Personen und Material

Maßnahmen:

- Lose Gegenstände sichern oder entfernen
- Patient mit fixierter Decke schützen, Augen schützen
- Schutzbrille und Helm für alle im Abwindbereich
- Im Wasser den Schwebeflug **seitlich versetzt** ansetzen, damit der Patient nicht direkt unter dem Rotor liegt

## Landeplatzauswahl

Ein Landeplatz muss:

- **eben** sein (geringe Neigung)
- mindestens etwa **25 x 25 m** frei sein, bei Nacht größer
- frei von **Hindernissen** wie Masten, Stromleitungen, Bäumen sein
- über einen **freien An- und Abflugsektor** verfügen
- fester Untergrund ohne lose Teile

## Landeplatzeinweisung

Die Einweisung erfolgt durch **eine einzige** Einsatzkraft:

- Einweiser steht **mit dem Rücken zum Wind** am Rand des Landeplatzes, sodass der Hubschrauber gegen den Wind landen kann.
- Er hält beide Arme in **Y-Form** nach oben: „Ich bin der Einweiser, hier landen.“
- Er bleibt stehen, bis der Hubschrauber gelandet ist und die Rotoren laufen.
- Bei Gefahr kreuzt er die Arme über dem Kopf und winkt: **Landung abbrechen**.

Funkkontakt mit dem Piloten wird, wenn möglich, gehalten.

## Annäherung an den Hubschrauber

- Nur nach **Zeichen des Piloten** oder der Besatzung nähern.
- Immer **von vorn oder seitlich vorn** im Sichtbereich des Piloten.
- **Niemals** in den Bereich des **Heckrotors** gehen – er ist im Betrieb fast unsichtbar.
- Gebückt gehen, besonders an Hängen: Am Hang wird **nur von der Talseite** angenähert, da der Rotor bergseitig näher am Boden ist.
- Keine langen Gegenstände (Infusionsständer, Antennen) senkrecht tragen.

*Merke:* Von vorn, im Blick des Piloten, nie zum Heckrotor und am Hang nur von unten.
MD],
            ['Crew Resource Management und Kommunikation', <<<'MD'
# Crew Resource Management und Kommunikation

In der Luftrettung arbeiten mehrere Menschen unter hohem Druck an einer komplexen Aufgabe. Viele Unfälle in der Luftfahrt entstehen nicht durch technische Fehler, sondern durch **menschliche Faktoren**. Das **Crew Resource Management (CRM)** ist ein Konzept, um diese Fehler zu vermeiden.

## Grundsätze des CRM

- **Kenne deine Umgebung:** Gelände, Wetter, Hindernisse, Kraftstoff.
- **Antizipiere und plane:** Was passiert als Nächstes, was tun wir, wenn es schiefgeht?
- **Kommuniziere klar:** Eindeutige Begriffe, geschlossene Kommunikationsschleifen.
- **Verteile die Arbeitsbelastung:** Jeder weiß, was er zu tun hat.
- **Sprich Bedenken offen an:** Jedes Crewmitglied darf und muss „Stopp“ sagen – unabhängig von Rang und Erfahrung.
- **Überprüfe und hinterfrage:** Checklisten konsequent nutzen.

## Geschlossene Kommunikationsschleife

Jede Anweisung wird **wiederholt** und bestätigt:

> Pilot: „Bereit für Winde?“
> Windenoperator: „Bereit für Winde, Luftretter eingehängt, Haken gesichert.“
> Pilot: „Verstanden, Winde frei.“

## Windenkommandos

Der Pilot sieht den Bereich unter dem Hubschrauber **nicht**. Er ist vollständig auf die Ansagen des Windenoperators angewiesen. Kommandos sind kurz, standardisiert und enthalten **Richtung und Entfernung**:

| Kommando | Bedeutung |
|---|---|
| „Vorwärts 10“ | 10 Meter nach vorn |
| „Rechts 5“ | 5 Meter nach rechts |
| „Langsam vorwärts … stopp“ | Feinkorrektur |
| „Halten“ | Position halten |
| „Höhe gut“ | Schwebehöhe passt |
| „Luftretter am Boden“ | Bodenkontakt |
| „Last frei“ | Last hängt frei, kein Bodenkontakt |
| „Abbruch, Abbruch, Abbruch“ | Sofortiger Abbruch des Manövers |

Das Wort **„Abbruch“** wird dreimal wiederholt und darf von jedem Crewmitglied ausgesprochen werden.

## Sterile Cockpit

In kritischen Phasen (Start, Landung, Windenbetrieb) gilt das Prinzip des **sterilen Cockpits**: Nur einsatzrelevante Kommunikation, keine Gespräche nebenbei.

## Handzeichen

Fällt der Funk aus, verständigen sich Luftretter am Boden und Windenoperator über **Handzeichen**, etwa: Daumen hoch = bereit zum Hochziehen, waagerecht ausgestreckter Arm mit kreisender Hand = Seil ablassen, Arm quer vor dem Hals = Abbruch.

## Briefing und Debriefing

- **Briefing** vor dem Einsatz: Plan, Rollen, Risiken, Abbruchkriterien.
- **Debriefing** nach dem Einsatz: offene Nachbesprechung ohne Schuldzuweisung.

*Merke:* Der Pilot ist im Schwebeflug blind nach unten – die Augen des Piloten sind der Windenoperator.
MD],
            ['Absturzsicherung und Übergabe am Pillbox-Dach', <<<'MD'
# Absturzsicherung und Übergabe am Pillbox-Dach

## Absturzsicherung im Hubschrauber

Beim Windenbetrieb ist die **Schiebetür offen**. Jede Person, die sich im Bereich der offenen Tür bewegt, ist gesichert.

- Jedes Crewmitglied trägt einen **Gurt** und ist über eine **Sicherungsleine (Lanyard)** an einem zugelassenen Anschlagpunkt in der Kabine eingehängt, **bevor die Tür geöffnet wird**.
- Die Länge der Sicherungsleine ist so eingestellt, dass man an der Tür arbeiten, aber **nicht hinausfallen** kann.
- Beim Ein- und Aushängen am Windenhaken gilt: **Erst einhängen, dann aushängen** – zu keinem Zeitpunkt ist der Luftretter ungesichert.
- Patienten sind an Bord angeschnallt, Trage und Material sind fixiert.
- Lose Gegenstände in der Kabine werden gesichert, damit sie nicht hinausfallen oder in den Rotor geraten.
- Helm mit Gehörschutz und Intercom, Handschuhe für Seilarbeit.

## Checkliste vor dem Öffnen der Tür

1. Alle Crewmitglieder gesichert?
2. Lose Gegenstände verstaut?
3. Winde geprüft, Haken gesichert?
4. Intercom funktioniert?
5. Pilot bestätigt: „Tür öffnen frei“.

## Flug zum Krankenhaus

Nach der Rettung meldet die Besatzung **Status 7**, den Zustand des Patienten und die voraussichtliche Ankunftszeit an die Leitstelle. Diese informiert das Krankenhaus, meist das **Pillbox Hill Medical Center**.

## Übergabe am Pillbox-Dach

Das **Dach des Pillbox Hill Medical Center** verfügt über einen Hubschrauberlandeplatz. Dort wartet ein Team von **SAMS**.

Ablauf:

1. Landung, Rotor läuft oder wird abgestellt (Entscheidung des Piloten).
2. Das SAMS-Team nähert sich **erst nach Freigabe** durch Pilot oder Besatzung, von vorn und im Sichtbereich.
3. Patient wird **gemeinsam** von der Trage auf die Krankenhaustrage umgelagert.
4. **Übergabe** nach festem Schema, z. B. **ABCDE** bzw. ein strukturiertes Übergabeschema:
   - Name, Alter (falls bekannt)
   - Unfallhergang und Rettungsmethode (z. B. Windenrettung aus 40 m, Zeit im Wasser)
   - Befunde und Vitalwerte
   - durchgeführte Maßnahmen und Medikamente
   - Besonderheiten (Unterkühlung, Tauchunfall)
5. **Status 8** an die Leitstelle.
6. Rückkehr zum Hubschrauber, Ausrüstung prüfen, Einsatzbereitschaft herstellen.

## Dokumentation

Im System wird ein **Windenprotokoll** angelegt: Anzahl der Windengänge, Seillänge, Lasten, Besonderheiten. Die Winde hat eine begrenzte Lebensdauer der Seile, deshalb ist die genaue Erfassung wichtig.

*Merke:* Erst einhängen, dann aushängen – niemand steht ungesichert an der offenen Tür.
MD],
        ],
        'questions' => [
            ['Was ist zu beachten, bevor das Bodenpersonal den Windenhaken anfasst?', ['Nichts, der Haken ist ungefährlich', 'Der Haken muss Boden- oder Wasserkontakt gehabt haben, um die statische Aufladung abzuleiten', 'Man trägt Gummistiefel und greift sofort zu', 'Der Pilot muss landen'], [1], 'Der Hubschrauber lädt sich elektrostatisch auf, der Haken muss zuerst geerdet werden.'],
            ['Wozu dient der Cable Cutter an der Rettungswinde?', ['Zum Kürzen des Seils nach jedem Einsatz', 'Zum Öffnen der Tür', 'Zum Kappen des Seils im Notfall, wenn es den Hubschrauber gefährdet', 'Zum Durchtrennen der Kleidung des Patienten'], [2], 'Verfängt sich das Seil, kann es zur Rettung des Hubschraubers gekappt werden.'],
            ['Wer gibt dem Piloten während des Windenbetriebs Richtungskommandos?', ['Der Windenoperator', 'Der Patient', 'Die Leitstelle', 'Niemand, der Pilot sieht alles selbst'], [0], 'Der Pilot sieht den Bereich unter dem Hubschrauber nicht und ist auf den Windenoperator angewiesen.'],
            ['Wie hält eine Person in der Rettungsschlinge ihre Arme?', ['Nach oben gestreckt', 'Nach unten, damit sie nicht herausrutscht', 'Vor dem Gesicht', 'Am Windenseil'], [1], 'Mit erhobenen Armen kann die Person aus der Schlinge rutschen.'],
            ['Welches Bergemittel wird für einen unterkühlten Patienten nach Wasserunfall bevorzugt?', ['Rettungsschlinge senkrecht', 'Bergetau ohne Sicherung', 'Rettungsdreieck', 'Horizontale Bergung mit Trage, Bergesack oder Doppelschlinge'], [3], 'Bei senkrechter Bergung kann der Kreislauf eines Unterkühlten zusammenbrechen.'],
            ['Wozu dient die Führungsleine (Tagline) an der Trage?', ['Sie verhindert das Drehen der Trage unter dem Hubschrauber', 'Sie ersetzt das Windenseil', 'Sie dient zur Kommunikation', 'Sie wärmt den Patienten'], [0], 'Eine rotierende Trage kann den Patienten schwer schädigen.'],
            ['Welche Gefahren gehen vom Downwash aus?', ['Fliegende Gegenstände und Steinschlag', 'Sichtbehinderung durch aufgewirbelten Sand', 'Auskühlung des Patienten', 'Erhöhte Funkreichweite'], [0, 1, 2], 'Der Abwind erreicht Sturmstärke und wirbelt alles Lose auf.'],
            ['Wie steht der Einweiser bei der Landeplatzeinweisung?', ['Mit dem Gesicht zum Wind in der Mitte des Platzes', 'Mit dem Rücken zum Wind am Rand des Landeplatzes, Arme in Y-Form', 'Hinter dem Heckrotor', 'Er läuft dem Hubschrauber entgegen'], [1], 'So kann der Hubschrauber gegen den Wind auf den Einweiser zu landen.'],
            ['Von wo nähert man sich einem Hubschrauber am Hang?', ['Von der Bergseite', 'Von hinten', 'Von der Talseite, im Sichtbereich des Piloten', 'Über den Heckrotor'], [2], 'Bergseitig ist der Rotor näher am Boden.'],
            ['Was bedeutet eine geschlossene Kommunikationsschleife im CRM?', ['Funk ist nur in geschlossenen Räumen erlaubt', 'Jede Anweisung wird wiederholt und bestätigt', 'Nur der Pilot spricht', 'Es wird gar nicht kommuniziert'], [1], 'Durch Wiederholung werden Missverständnisse vermieden.'],
            ['Wer darf im Hubschrauber „Abbruch“ ansagen?', ['Nur der Pilot', 'Nur der Notarzt', 'Nur die Leitstelle', 'Jedes Crewmitglied'], [3], 'Im CRM darf und muss jeder unabhängig vom Rang Bedenken äußern.'],
            ['Welche Regel gilt beim Ein- und Aushängen am Windenhaken?', ['Erst aushängen, dann einhängen', 'Erst einhängen, dann aushängen – nie ungesichert', 'Man darf kurz ungesichert sein, wenn es schnell geht', 'Sicherung ist nur bei Nacht nötig'], [1], 'Zu keinem Zeitpunkt ist der Luftretter ungesichert.'],
            ['Wann darf sich das SAMS-Team auf dem Pillbox-Dach dem Hubschrauber nähern?', ['Sofort beim Aufsetzen', 'Erst nach Freigabe durch Pilot oder Besatzung, von vorn im Sichtbereich', 'Von hinten, um nicht zu stören', 'Erst nach zwei Stunden'], [1], 'Annäherung nur nach Freigabe und nie zum Heckrotor.'],
            ['Welche Angaben gehören in die Übergabe an SAMS nach einer Windenrettung?', ['Unfallhergang und Rettungsmethode', 'Befunde, Vitalwerte und Maßnahmen', 'Besonderheiten wie Unterkühlung', 'Die Kraftstoffmenge des Hubschraubers'], [0, 1, 2], 'Die Übergabe folgt einem strukturierten Schema mit allen medizinisch relevanten Informationen.'],
        ],
    ],
    [
        'title' => 'Suche und Ortung',
        'description' => 'Lehrgang für Einsatzkräfte der TAS bei Vermisstenlagen nach dem Vorbild der Suchkomponenten von Bergwacht und Rettungshundestaffeln. Behandelt das Verhalten vermisster Personen (Lost Person Behaviour), Suchgebiete und Sektoren, Linien- und Flächensuche, Drohne und Wärmebild, Suchhunde, das Suchprotokoll sowie die Zusammenarbeit mit dem LSPD.',
        'hours' => 10, 'pass_percent' => 75, 'question_count' => 12, 'time_limit' => 25,
        'qualification' => null,
        'modules' => [
            ['Vermisstensuche und Lost Person Behaviour', <<<'MD'
# Vermisstensuche und Lost Person Behaviour

Eine Vermisstensuche beginnt nicht im Gelände, sondern mit **Informationen**. Wer weiß, wer gesucht wird, sucht an den richtigen Stellen.

## Zuständigkeit

Eine Vermisstenanzeige nimmt die **Polizei (LSPD bzw. BCSO)** auf. Sie entscheidet, ob eine Gefahr für Leib und Leben vorliegt, und fordert die TAS zur Suche an. Die Polizei bleibt **Herrin des Verfahrens**, die TAS stellt die Suchkräfte und die fachliche Suchplanung.

## Die Befragung

Von Angehörigen und Zeugen werden möglichst viele Informationen erfragt:

- **Personenbeschreibung:** Alter, Größe, Kleidung (Farben!), Schuhe, Brille
- **Zuletzt gesehen (Point Last Seen, PLS):** Wo, wann, von wem?
- **Gesundheitszustand:** Vorerkrankungen, Medikamente, Demenz, Diabetes, psychische Erkrankungen, Suizidgefahr
- **Ausrüstung:** Handy (Akkustand?), Wasser, Lampe, Fahrzeug
- **Gewohnheiten:** Lieblingsorte, Wanderrouten, frühere Vorfälle
- **Gegenstände mit Geruch** für Suchhunde (ungewaschenes Kleidungsstück, in sauberer Tüte, nicht von anderen angefasst)

## Lost Person Behaviour

Aus der Auswertung tausender Suchen weiß man, dass sich vermisste Personen je nach **Kategorie** typisch verhalten:

| Kategorie | Typisches Verhalten |
|---|---|
| **Kleinkinder (1–3 Jahre)** | bewegen sich nur kurze Strecken, verstecken sich, reagieren oft nicht auf Rufen |
| **Kinder (7–12 Jahre)** | laufen weiter, nutzen Wege, verstecken sich aus Angst vor Ärger |
| **Wanderer** | folgen Wegen, verlaufen sich an Abzweigungen, suchen erhöhte Punkte für Handyempfang |
| **Menschen mit Demenz** | laufen geradeaus, bis sie auf ein Hindernis stoßen (Gebüsch, Zaun, Graben), bleiben dort oft stecken, reagieren selten auf Rufen |
| **Suizidgefährdete Personen** | suchen Orte mit Bedeutung oder Aussicht auf, z. B. Brücken, Aussichtspunkte |
| **Personen unter Alkoholeinfluss** | legen sich oft in der Nähe nieder, Unterkühlungsgefahr |

## Statistische Distanzen

Die meisten Vermissten werden innerhalb eines bestimmten **Radius um den Point Last Seen** gefunden. Für jede Kategorie gibt es Erfahrungswerte, z. B. findet man Menschen mit Demenz überwiegend **innerhalb von 1–2 km**. Diese Werte helfen, das Suchgebiet zu begrenzen.

## Dringlichkeit

Die Dringlichkeit steigt bei:

- Kindern und älteren Menschen
- Vorerkrankungen oder fehlenden Medikamenten
- Suizidgefahr
- schlechtem Wetter, Hitze in der Wüste, Kälte am Mount Chiliad, Nacht

*Merke:* Die besten Suchtrupps nützen nichts, wenn sie am falschen Ort suchen – erst befragen, dann planen.
MD],
            ['Suchgebiete und Sektoren', <<<'MD'
# Suchgebiete und Sektoren

## Vom Gesamtgebiet zum Sektor

Ausgehend vom **Point Last Seen (PLS)** oder dem **Last Known Point (LKP)** – dem letzten sicheren Hinweis, etwa einem abgestellten Auto am Parkplatz des Mount Chiliad – wird das **Suchgebiet** festgelegt.

1. **Theoretisches Suchgebiet:** Kreis um den PLS mit dem statistischen Radius der Personenkategorie.
2. **Eingrenzung:** Natürliche Grenzen wie Flüsse (Zancudo River), Steilwände (Raton Canyon), Straßen (Great Ocean Highway) und Zäune (Fort Zancudo) begrenzen das Gebiet.
3. **Einteilung in Sektoren:** Das Gebiet wird in **Suchsektoren** aufgeteilt, die ein Suchtrupp in etwa **zwei bis vier Stunden** absuchen kann.

## Anforderungen an Sektoren

- Grenzen sind **im Gelände erkennbar**: Wege, Bäche, Zäune, Waldränder.
- Jeder Sektor erhält eine **eindeutige Bezeichnung** (z. B. A1, A2, B1).
- Sektoren werden in der Lagekarte eingezeichnet und im System hinterlegt.
- Die Größe richtet sich nach Gelände, Bewuchs und eingesetzter Suchmethode.

## Priorisierung

Nicht alle Sektoren sind gleich wahrscheinlich. Sie werden nach **Wahrscheinlichkeit** priorisiert:

- Wege und Pfade rund um den PLS
- Orte, an denen sich die Person laut Befragung aufhalten könnte
- **Gefahrenstellen:** Abbruchkanten, Gewässer, Schluchten
- Bereiche, in die die Personenkategorie typischerweise geht

## Hasty Search (schnelle Erstsuche)

Unmittelbar nach Alarmierung werden **schnelle Trupps** eingesetzt, die die wahrscheinlichsten Orte prüfen, bevor die systematische Suche beginnt:

- Wege und Trails ablaufen und rufen
- Gefahrenstellen (Kanten, Ufer) kontrollieren
- Aussichtspunkte, Hütten, Unterstände prüfen
- **Spuren** sichern (Fußabdrücke, weggeworfene Gegenstände)

## Eingrenzung (Containment)

Damit die Person das Suchgebiet nicht verlässt, werden **Zugänge besetzt**: Parkplätze, Straßenkreuzungen, Seilbahnstationen. Die Polizei unterstützt mit Streifenwagen an den Ausfallstraßen.

## Lagekarte

Auf der Lagekarte werden eingetragen:

- PLS / LKP
- Suchgebiet und Sektoren mit Status (offen, in Suche, abgesucht)
- Funde und Hinweise mit Uhrzeit
- Standorte von Einsatzleitung, Bereitstellungsraum, Übergabepunkt SAMS

*Merke:* Ein gutes Suchgebiet ist begrenzt, in Sektoren geteilt und nach Wahrscheinlichkeit sortiert.
MD],
            ['Linien- und Flächensuche', <<<'MD'
# Linien- und Flächensuche

## Grundsatz

Jede Suchmethode hat eine bestimmte **Auffindewahrscheinlichkeit (Probability of Detection, POD)**. Eine schnelle Suche deckt viel Fläche ab, übersieht aber mehr. Eine gründliche Suche findet mehr, braucht aber viel Zeit und Personal.

## Wegesuche

Trupps laufen Wege, Trails und Straßenränder ab. Sie rufen den Namen der Person, bleiben stehen und **horchen**. Schnell, geeignet für die Erstsuche.

## Liniensuche (Linear Search)

Die **Liniensuche** wird an linienförmigen Strukturen eingesetzt:

- entlang von **Flussufern** (Zancudo River, Ufer der Alamo Sea)
- an **Abbruchkanten** und Schluchträndern
- entlang von Straßen, Bahnlinien und Zäunen

Zwei Sucher gehen auf beiden Seiten der Linie oder eine Kette folgt der Struktur. Besonders wichtig sind Stellen, an denen Personen hängen bleiben können (Gebüsch, Treibholz am Ufer).

## Flächensuche (Kettensuche)

Bei der **Flächensuche** gehen mehrere Sucher in einer **Kette** nebeneinander durch das Gelände.

- **Abstand** zwischen den Suchern abhängig von Bewuchs und Sicht: im offenen Gelände 10–20 m, im dichten Buschwerk 2–5 m.
- Ein **Kettenführer** in der Mitte gibt Tempo und Richtung vor.
- Die **Flügelmänner** an den Enden markieren die Grenze (Absperrband, GPS-Track).
- Die Kette bleibt **auf gleicher Höhe**; wer ein Hindernis umgeht, meldet es.
- Bei Fund: **Kette stoppt**, Fundort markieren und melden.

Flächensuchen sind personalintensiv, liefern aber eine hohe Auffindewahrscheinlichkeit.

## Rufen und Horchen

Ansprechbare Personen werden oft durch **Rufen und Horchen** gefunden:

1. Alle Sucher rufen den Namen.
2. Danach **absolute Ruhe** für etwa 30 Sekunden.
3. Auf Antworten, Klopfen oder Geräusche achten.

Bei Kleinkindern und Menschen mit Demenz ist das wenig erfolgreich, weil sie oft nicht antworten.

## Spuren und Hinweise

Hinweise (Kleidungsstücke, Verpackungen, Fußspuren) werden:

- **nicht berührt**, sondern markiert
- mit GPS-Position, Uhrzeit und Foto dokumentiert
- sofort an die Einsatzleitung gemeldet

Sie können für die Polizei als **Beweismittel** wichtig sein.

## Sicherheit der Suchkräfte

- Suchtrupps bestehen mindestens aus **zwei Personen**.
- Jeder Trupp hat Funk, Karte bzw. GPS, Lampe, Wasser und Erste-Hilfe-Material.
- In absturzgefährdetem Gelände (Raton Canyon) suchen nur **Bergretter** mit Sicherung.
- Feste Meldezeiten an die Einsatzleitung.

*Merke:* Schnell suchen, wo es wahrscheinlich ist – gründlich suchen, wo es nötig ist.
MD],
            ['Drohne, Wärmebild und Suchhunde', <<<'MD'
# Drohne, Wärmebild und Suchhunde

## Drohnen

Drohnen (unbemannte Luftfahrzeuge) sind heute ein Standardmittel der Suche.

**Vorteile:**
- schnelle Übersicht über große, schwer zugängliche Flächen
- Einsatz in Schluchten und an Steilhängen ohne Gefährdung von Suchkräften
- Live-Bild an die Einsatzleitung
- wesentlich günstiger und schneller verfügbar als ein Hubschrauber

**Grenzen:**
- begrenzte Akkulaufzeit (ca. 20–40 Minuten), Ersatzakkus nötig
- Wind, Regen und Nebel
- dichter Wald verdeckt die Sicht
- **Flugbeschränkungen**: Über **Fort Zancudo** und im Bereich des **Los Santos International Airport** ist der Drohnenflug ohne Genehmigung verboten. Bei Hubschraubereinsätzen im Gebiet müssen Drohnen **landen** oder ihre Höhe mit der Hubschrauberbesatzung abstimmen.

Ein Drohnenteam besteht aus **Pilot** (steuert und überwacht den Luftraum) und **Beobachter** (wertet das Bild aus). Der Pilot hält **Sichtkontakt** zur Drohne.

## Wärmebild

Die **Wärmebildkamera** zeigt Temperaturunterschiede. Ein Mensch hebt sich vom kühleren Hintergrund ab.

- Am besten **nachts, am frühen Morgen** oder bei kühlem Wetter.
- Bei großer **Hitze am Tag** (Grand Senora Desert) sind Boden und Steine oft so warm wie der Körper – das Wärmebild ist dann kaum brauchbar.
- Kleidung, Rettungsdecken und dichtes Laub schirmen ab.
- **Tiere** (Rehe, Kojoten, Pumas) erzeugen ähnliche Signaturen – Treffer müssen überprüft werden.
- Wärmebild sieht **nicht durch Wasser** und nicht durch Glas.

## Suchhunde

Rettungshunde finden Menschen über den **Geruch**. Es werden unterschieden:

| Hundeart | Arbeitsweise | Einsatz |
|---|---|---|
| **Flächensuchhund** | sucht frei und weitflächig nach **jedem** menschlichen Geruch, zeigt durch Bellen oder Bringsel an | abgegrenzte Sektoren, Wald, Gelände |
| **Mantrailer** | verfolgt die **individuelle Spur** einer bestimmten Person anhand eines Geruchsartikels | Bestimmung der Laufrichtung vom PLS, auch im bebauten Gebiet |
| **Wasserortungshund** | zeigt vom Boot aus Geruch von Personen unter Wasser an | Suche in Seen und Flüssen, mit Tauchern |

**Regeln für den Hundeeinsatz:**
- Hunde werden **früh** eingesetzt, bevor viele Menschen das Gebiet „verunreinigen“.
- Den Sektor eines Flächensuchhundes betreten keine anderen Suchkräfte.
- Den **Geruchsartikel** für den Mantrailer nur mit Handschuhen bzw. in sauberer Tüte sichern.
- Hunde brauchen **Pausen**, Wasser und Schatten, besonders bei Hitze.

*Merke:* Drohne und Wärmebild finden schnell, Hunde finden gründlich – am besten wirken sie zusammen.
MD],
            ['Suchprotokoll und Zusammenarbeit mit dem LSPD', <<<'MD'
# Suchprotokoll und Zusammenarbeit mit dem LSPD

## Das Suchprotokoll

Jede Suche wird im **Suchprotokoll** im System dokumentiert. Es ist das zentrale Führungsmittel und Grundlage für spätere Ermittlungen.

**Kopfdaten:**
- Einsatznummer, Datum, Einsatzleiter TAS, Ansprechpartner Polizei
- Daten der vermissten Person, Kategorie, PLS / LKP
- Wetter und Lichtverhältnisse

**Für jeden Sektor:**
- Sektorbezeichnung und Grenzen
- eingesetzter Trupp bzw. Hundeteam, Drohne
- **Suchmethode** (Wegesuche, Liniensuche, Flächensuche, Hund, Drohne)
- **Beginn und Ende** der Suche
- Abstand der Sucher, geschätzte **Auffindewahrscheinlichkeit**
- Ergebnis: ohne Befund, Hinweis, Fund
- GPS-Track als Anhang

**Hinweise und Funde:**
- Uhrzeit, Position, Beschreibung, Foto
- wer gefunden hat, an wen gemeldet

## Warum so genau?

- Abgesuchte Sektoren werden nicht doppelt durchsucht, offene Sektoren nicht vergessen.
- Bei **Schichtwechsel** oder Übergabe an eine andere Einsatzleitung ist der Stand nachvollziehbar.
- Wird die Suche fortgesetzt, kann ein Sektor gezielt mit **anderer Methode** erneut abgesucht werden.
- Die Polizei benötigt die Dokumentation für die **Ermittlungsakte**.

## Zusammenarbeit mit dem LSPD

Die Polizei ist bei Vermisstenfällen **verfahrensführend**. Die Rollen:

| LSPD / BCSO | TAS |
|---|---|
| Aufnahme der Vermisstenanzeige | Fachliche Suchplanung |
| Befragung von Angehörigen und Zeugen | Stellung von Suchtrupps, Hunden, Drohnen, Boot und Hubschrauber |
| Handyortung, Kameraauswertung, Fahndung | Rettung und Erstversorgung bei Fund |
| Bewertung möglicher Straftaten | Dokumentation im Suchprotokoll |
| Information der Angehörigen und Presse | Lagemeldungen an die Polizei |

Die Einsatzleitung TAS und der polizeiliche Ansprechpartner arbeiten an einem **gemeinsamen Führungsstandort**.

## Vorgehen bei Fund

**Lebend gefunden:**
1. Erstversorgung, Wärmeerhalt
2. Meldung an Einsatzleitung: „Person gefunden, lebend, Zustand …“
3. Rettung und Übergabe an **SAMS**
4. Polizei informieren, die Suche wird beendet

**Verstorben aufgefunden:**
1. Fundort **nicht verändern**, keine Gegenstände entfernen
2. Meldung über Funk möglichst **verschlüsselt** bzw. per Telefon, damit Unbeteiligte und Presse nicht mithören
3. Fundort absichern, **Polizei** übernimmt
4. Die Information der Angehörigen erfolgt **ausschließlich durch die Polizei**

## Einsatzende

Die Suche endet bei Fund oder auf Entscheidung der Polizei. Alle Trupps melden sich ab, Material und Protokolle werden vervollständigt, und es folgt eine Nachbesprechung.

*Merke:* Die Polizei führt das Verfahren – die TAS sucht, rettet und dokumentiert.
MD],
        ],
        'questions' => [
            ['Wer nimmt eine Vermisstenanzeige auf und ist verfahrensführend?', ['Die TAS', 'Die Polizei (LSPD bzw. BCSO)', 'SAMS', 'Das LSFD'], [1], 'Die Polizei führt das Verfahren, die TAS stellt Suchkräfte und Suchplanung.'],
            ['Was bedeutet die Abkürzung PLS?', ['Point Last Seen – Ort, an dem die Person zuletzt gesehen wurde', 'Police Lead Searcher', 'Personen-Lokalisierungs-System', 'Primärer Landeplatz Süd'], [0], 'Der PLS ist Ausgangspunkt der Suchplanung.'],
            ['Welches Verhalten ist typisch für Menschen mit Demenz?', ['Sie folgen immer gekennzeichneten Wegen', 'Sie rufen laut um Hilfe', 'Sie laufen geradeaus, bis sie auf ein Hindernis stoßen, und reagieren selten auf Rufen', 'Sie suchen erhöhte Punkte für Handyempfang'], [2], 'Menschen mit Demenz bleiben häufig in Gebüsch oder an Zäunen stecken.'],
            ['Welche Informationen werden bei der Befragung erhoben?', ['Kleidung und Farben', 'Vorerkrankungen und Medikamente', 'Lieblingsorte und Gewohnheiten', 'Die Steuernummer der Person'], [0, 1, 2], 'Personenbeschreibung, Gesundheit und Gewohnheiten helfen bei der Suchplanung.'],
            ['Wie werden Grenzen von Suchsektoren gewählt?', ['Möglichst gerade Linien auf der Karte, egal ob im Gelände sichtbar', 'Gar nicht, jeder sucht, wo er will', 'Nach Lust der Suchtrupps', 'Möglichst an im Gelände erkennbaren Strukturen wie Wegen, Bächen und Zäunen'], [3], 'Suchtrupps müssen die Grenzen vor Ort erkennen können.'],
            ['Was ist eine Hasty Search?', ['Eine schnelle Erstsuche der wahrscheinlichsten Orte vor der systematischen Suche', 'Die Suche mit Hunden', 'Eine Suche nur bei Nacht', 'Die Abschlussbesprechung'], [0], 'Schnelle Trupps prüfen Wege, Gefahrenstellen und Unterstände.'],
            ['Wo wird bevorzugt eine Liniensuche eingesetzt?', ['In offenen Wiesen', 'Entlang von Flussufern, Abbruchkanten und Straßen', 'Nur in Gebäuden', 'Im Hafenbecken unter Wasser'], [1], 'Die Liniensuche folgt linienförmigen Strukturen.'],
            ['Was passiert bei einer Kettensuche, wenn ein Sucher einen Fund macht?', ['Die Kette läuft weiter', 'Der Sucher nimmt den Gegenstand mit', 'Die Kette stoppt, der Fundort wird markiert und gemeldet', 'Der Sucher geht nach Hause'], [2], 'Funde werden markiert, nicht berührt und sofort gemeldet.'],
            ['Wann ist eine Wärmebildkamera am wenigsten aussagekräftig?', ['In einer kalten Nacht', 'Am frühen Morgen', 'Bei bewölktem, kühlem Wetter', 'Bei großer Tageshitze in der Grand Senora Desert'], [3], 'Ist der Boden so warm wie der Körper, fehlt der Kontrast.'],
            ['Was gilt für Drohnenflüge über Fort Zancudo?', ['Sie sind ohne Genehmigung verboten', 'Sie sind jederzeit erlaubt', 'Sie sind nur nachts erlaubt', 'Sie sind nur mit Wärmebild erlaubt'], [0], 'Militärgelände und Flughafenbereiche unterliegen Flugbeschränkungen.'],
            ['Worin unterscheidet sich ein Mantrailer von einem Flächensuchhund?', ['Der Mantrailer sucht nur unter Wasser', 'Der Mantrailer verfolgt die individuelle Spur einer bestimmten Person anhand eines Geruchsartikels', 'Es gibt keinen Unterschied', 'Der Flächensuchhund benötigt immer einen Geruchsartikel'], [1], 'Flächensuchhunde zeigen jeden menschlichen Geruch an, Mantrailer folgen einer bestimmten Spur.'],
            ['Warum ist ein genaues Suchprotokoll wichtig?', ['Abgesuchte Sektoren werden nicht doppelt durchsucht', 'Bei Schichtwechsel ist der Stand nachvollziehbar', 'Die Polizei benötigt es für die Ermittlungsakte', 'Es ersetzt die Suche'], [0, 1, 2], 'Das Suchprotokoll ist zentrales Führungsmittel und Teil der Ermittlungsakte.'],
            ['Was ist zu tun, wenn eine vermisste Person verstorben aufgefunden wird?', ['Die Person sofort wegtragen und Gegenstände einsammeln', 'Fundort nicht verändern, verschlüsselt melden und die Polizei übernehmen lassen', 'Die Angehörigen selbst anrufen', 'Den Fund offen über Funk mit vollem Namen melden'], [1], 'Die Polizei übernimmt, Angehörige werden nur von der Polizei informiert.'],
            ['Wer informiert die Angehörigen über den Tod einer vermissten Person?', ['Der Suchtrupp, der gefunden hat', 'Die Presse', 'Der Hundeführer', 'Ausschließlich die Polizei'], [3], 'Die Überbringung der Todesnachricht ist Aufgabe der Polizei.'],
        ],
    ],
];
