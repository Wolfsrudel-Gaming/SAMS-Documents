<?php
/** Ausbildungsinhalte Feuerwehr: Lehrgänge mit Lernmodulen und Prüfungsfragen. */
return [
    [
        'title' => 'Grundausbildung Feuerwehr (Truppmann)',
        'description' => 'Basislehrgang für neue Einsatzkräfte des Los Santos Fire Department. Vermittelt Aufgaben des LSFD, Fahrzeugkunde, persönliche Schutzausrüstung, die Einheiten im Löscheinsatz nach FwDV 3, Knoten und Stiche sowie Funk und FMS-Status.',
        'hours' => 8, 'pass_percent' => 70, 'question_count' => 10, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Aufgaben des LSFD und Fahrzeugkunde', <<<'MD'
# Aufgaben des LSFD und Fahrzeugkunde

Das **Los Santos Fire Department (LSFD)** ist für den abwehrenden und vorbeugenden Brandschutz sowie die technische Hilfeleistung im gesamten Staat San Andreas zuständig. Die Feuerwehr wird über den Notruf **911** alarmiert und arbeitet eng mit dem Rettungsdienst **SAMS** sowie den Polizeibehörden **LSPD** und **BCSO** zusammen.

## Die vier Grundaufgaben

Die klassische Merkformel lautet **„Retten – Löschen – Bergen – Schützen“**:

- **Retten:** Abwenden eines lebensbedrohlichen Zustands von Menschen und Tieren, z. B. durch Befreiung aus Zwangslagen oder Brandrauch.
- **Löschen:** Bekämpfen von Bränden aller Art – vom Mülltonnenbrand in Davis bis zum Waldbrand am Mount Chiliad.
- **Bergen:** Sicherstellen von Sachwerten oder toten Körpern.
- **Schützen:** Abwehr von Gefahren für Menschen, Tiere, Umwelt und Sachwerte, etwa bei Gefahrgutunfällen im Port of Los Santos.

## Wachen

| Wache | Einsatzbereich |
|---|---|
| Station 1 Davis | Süd-Los Santos, Hafen, Innenstadt |
| Station 7 Rockford Hills | West-Los Santos, Vinewood, Del Perro |
| Sandy Shores | Blaine County Süd, Grand Senora Desert |
| Paleto Bay | Blaine County Nord, Mount Chiliad |

## Fahrzeuge

- **HLF (Hilfeleistungslöschgruppenfahrzeug):** Das Standardfahrzeug für Brand und technische Hilfe. Besatzung 1/8 (eine Gruppe), Löschwassertank, Pumpe, hydraulisches Rettungsgerät.
- **TLF (Tanklöschfahrzeug):** Großer Wassertank (meist 3.000 bis 5.000 Liter), ideal für Wald- und Flächenbrände in Blaine County, wo Hydranten fehlen. Besatzung meist 1/2 (Truppbesatzung).
- **DLK (Drehleiter mit Korb):** Hubrettungsfahrzeug zur Menschenrettung aus oberen Geschossen und zur Brandbekämpfung von oben. Rettungshöhe ca. 23 m.
- **RW (Rüstwagen):** Umfangreiches technisches Gerät für schwere technische Hilfe, z. B. Lkw-Unfälle, Seilwinde, Stromerzeuger, Hebekissen.
- **ELW (Einsatzleitwagen):** Führungsfahrzeug mit Funk- und Kommunikationstechnik, Lagekarten und Arbeitsplätzen für die Einsatzleitung.

## Mannschaftsstärken

Taktische Einheiten werden als **Führer/Mannschaft** angegeben:
- **Trupp:** 1/2 (3 Kräfte)
- **Staffel:** 1/5 (6 Kräfte)
- **Gruppe:** 1/8 (9 Kräfte)
- **Zug:** 1/21 bzw. 1/2/19 (22 Kräfte)

*Merke:* Ein Einsatz beginnt mit der Alarmierung und endet erst, wenn das Fahrzeug wieder einsatzbereit an der Wache steht.
MD],
            ['Persönliche Schutzausrüstung', <<<'MD'
# Persönliche Schutzausrüstung (PSA)

Die persönliche Schutzausrüstung schützt die Einsatzkraft vor Hitze, Flammen, mechanischen Einwirkungen, Chemikalien und Witterung. **Ohne vollständige PSA wird kein Einsatz begonnen.** Der Grundsatz „Eigenschutz vor Fremdschutz“ gilt ausnahmslos.

## Bestandteile der Mindestschutzausrüstung

1. **Feuerwehrhelm** mit Nackenschutz und Visier: schützt vor herabfallenden Teilen, Hitze und Funkenflug. Der Kinnriemen ist immer geschlossen.
2. **Feuerwehrschutzanzug** (Überjacke und Überhose nach EN 469): mehrlagig, hitzebeständig, mit Reflexstreifen. Jacke und Hose müssen sich überlappen.
3. **Feuerwehrschutzhandschuhe:** schützen vor Schnitt, Hitze und Abrieb.
4. **Feuerwehrschutzschuhwerk:** Stiefel mit Stahlkappe und durchtrittsicherer Sohle.

## Ergänzende Ausrüstung

- **Flammschutzhaube:** schützt Hals, Ohren und Gesicht unter dem Helm – Pflicht für Atemschutzgeräteträger.
- **Feuerwehr-Haltegurt** mit Feuerwehrleine: zur Selbstrettung und Sicherung.
- **Warnweste:** bei Einsätzen im Straßenverkehr, etwa auf dem Del Perro Freeway oder der Route 68.
- **Gehörschutz** beim Arbeiten mit Trennschleifer oder Motorsäge.
- **Schutzbrille** bei Arbeiten mit Glas, Funkenflug oder Splittern.
- **Chemikalienschutzanzug (CSA)** nur nach Zusatzausbildung Gefahrgut.

## Anlegen und Kontrolle

Die PSA wird in der Fahrzeughalle bereitgehalten. Beim Alarm legt jeder seine Ausrüstung an, **bevor** das Fahrzeug ausrückt. Während der Fahrt wird nur der Sicherheitsgurt angelegt; Atemschutzgeräte im Mannschaftsraum dürfen nur angelegt werden, wenn die Halterung dafür ausgelegt ist.

Vor dem Einsatz kontrolliert sich der Trupp gegenseitig („Buddy-Check“): Helm geschlossen, Kragen hoch, Überlappungen korrekt, keine offenen Hautstellen.

## Pflege

- Nach jedem Brandeinsatz wird die PSA als **kontaminiert** betrachtet und nach Schwarz-Weiß-Trennung in einem Sack verpackt.
- Verschmutzte Kleidung wird nicht im Aufenthaltsraum oder Privatfahrzeug getragen.
- Beschädigungen (Risse, Brandlöcher, gerissene Reflexstreifen) werden sofort dem Gerätewart gemeldet.

*Merke:* Brandrauch enthält krebserregende Stoffe. Saubere Ausrüstung ist Gesundheitsschutz – auch nach dem Einsatz.
MD],
            ['Einheiten im Löscheinsatz (FwDV 3)', <<<'MD'
# Einheiten im Löscheinsatz (FwDV 3)

Die **Feuerwehr-Dienstvorschrift 3** regelt Aufgaben, Ausrüstung und Aufstellung der taktischen Einheiten im Lösch- und Hilfeleistungseinsatz. Grundlage ist die **Gruppe (1/8)**.

## Funktionen in der Gruppe

| Funktion | Hauptaufgabe |
|---|---|
| **Gruppenführer (GF)** | Führt die Gruppe, erkundet, gibt Befehle, meldet an die Einsatzleitung |
| **Melder (Me)** | Unterstützt den GF, übermittelt Nachrichten, übernimmt Sonderaufgaben |
| **Maschinist (Ma)** | Fährt das Fahrzeug, bedient die Pumpe und Aggregate |
| **Angriffstrupp (ATF + ATM)** | Rettet Menschen, nimmt das erste Strahlrohr vor, meist unter Atemschutz |
| **Wassertrupp (WTF + WTM)** | Stellt die Wasserversorgung her, ist danach **Sicherheitstrupp** oder Rettungstrupp |
| **Schlauchtrupp (STF + STM)** | Verlegt Schlauchleitungen, setzt den Verteiler, unterstützt den Angriffstrupp |

Die Abkürzung „F“ steht für **Truppführer**, „M“ für **Truppmann**.

## Sitzordnung im HLF

Der Gruppenführer sitzt vorne rechts neben dem Maschinisten. Im Mannschaftsraum sitzen hinter dem Fahrer Melder und Angriffstrupp, gefolgt von Wasser- und Schlauchtrupp. Diese feste Ordnung ermöglicht ein schnelles, geordnetes Absitzen.

## Absitzen und Antreten

Auf den Befehl **„Absitzen!“** verlässt die Mannschaft das Fahrzeug zur verkehrsabgewandten Seite und tritt hinter dem Fahrzeug an. Kein Trupp handelt eigenmächtig – jeder wartet auf seinen Einsatzbefehl.

## Staffel und Trupp

- In der **Staffel (1/5)** entfällt der Schlauchtrupp; seine Aufgaben übernimmt der Wassertrupp mit.
- Der **selbstständige Trupp (1/2)** wird nur für einfache Aufgaben eingesetzt, z. B. Kleinbrand oder Ölspur.

## Grundsätze

1. **Menschenrettung geht vor Brandbekämpfung.**
2. Der Angriffstrupp geht nie ohne gesicherte Wasserversorgung in einen Innenangriff.
3. Für jeden Atemschutztrupp im Innenangriff muss ein **Sicherheitstrupp** bereitstehen.
4. Trupps bleiben immer zusammen – niemand arbeitet allein im Gefahrenbereich.

*Merke:* Klare Rollen verhindern Chaos. Wer seine Funktion kennt, weiß nach dem Absitzen sofort, wohin er gehört.
MD],
            ['Der Löschangriff', <<<'MD'
# Der Löschangriff

Der Löschangriff ist der Kern der Brandbekämpfung. Ziel ist, Wasser in ausreichender Menge schnell und sicher an den Brandherd zu bringen.

## Arten des Löschangriffs

- **Löschangriff mit Bereitstellung:** Die Lage ist unklar. Die Gruppe stellt Gerät am Verteiler bereit, der Gruppenführer erkundet zuerst.
- **Löschangriff ohne Bereitstellung:** Die Lage ist klar. Der Gruppenführer gibt sofort konkrete Einsatzbefehle.
- **Schnellangriff:** Der Angriffstrupp geht mit der formstabilen Schnellangriffseinrichtung aus dem Tank vor – typisch bei Fahrzeug- oder Mülltonnenbränden.

## Aufbau der Wasserversorgung

1. **Wasserentnahmestelle** (Hydrant, Tank oder offenes Gewässer)
2. **Zubringerleitung** (B-Schläuche) zur Pumpe
3. **Feuerlöschkreiselpumpe** im Fahrzeug
4. **B-Leitung** zum **Verteiler**
5. **Angriffsleitungen** (C-Schläuche) zu den Strahlrohren

Der Verteiler wird so gesetzt, dass er außerhalb des Gefahrenbereichs, aber nah am Gebäudezugang liegt. Eine **Schlauchreserve** vor dem Brandobjekt ermöglicht dem Angriffstrupp das Vorgehen ins Gebäude ohne Zug auf der Leitung.

## Einsatzbefehl

Der Gruppenführer befiehlt nach dem Schema **Einheit – Auftrag – Mittel – Ziel – Weg**, zum Beispiel:

> „Angriffstrupp zur Menschenrettung mit erstem Rohr ins 1. OG über den Treppenraum vor!“

## Vorgehen im Innenangriff

- Vor dem Öffnen einer Tür: **Türkontrolle** (Temperatur prüfen, tief stehen, Strahlrohr bereit).
- Tür nur einen Spalt öffnen, Rauchgase kühlen, dann vorgehen.
- Rückzugsweg immer im Blick behalten; der Schlauch ist der „Ariadnefaden“ zurück ins Freie.
- Wasser sparsam und gezielt einsetzen – Wasserschäden können größer sein als der Brandschaden.

## Strahlrohrführung

Das **Hohlstrahlrohr** erlaubt Sprühstrahl zum Schutz und zur Rauchgaskühlung sowie Vollstrahl für große Wurfweiten. Kurze Impulse (**Pulsing**) kühlen die Rauchschicht, ohne das thermische Gleichgewicht zu zerstören.

*Merke:* „Wasser marsch!“ gibt der Truppführer an den Maschinisten – erst wenn das Rohr in Stellung ist.
MD],
            ['Knoten, Stiche und Funk', <<<'MD'
# Knoten, Stiche und Funk

## Knoten und Stiche

Jede Einsatzkraft muss die Feuerwehrknoten sicher – auch mit Handschuhen und bei Dunkelheit – beherrschen.

| Knoten / Stich | Verwendung |
|---|---|
| **Kreuzknoten** | Verbinden zweier gleich starker Leinen |
| **Schotenstich** | Verbinden ungleich starker Leinen |
| **Mastwurf** | Befestigen an Rohren, Pfählen; Sichern von Strahlrohren und Schläuchen beim Hochziehen |
| **Zimmermannsschlag** | Hochziehen von Rundhölzern und Geräten |
| **Halbschlag** | Zusätzliche Sicherung, Führung von Lasten |
| **Spierenstich** | Sichern von Leinenenden |
| **Rettungsknoten (Brustbund)** | Retten und Selbstretten von Personen mit der Feuerwehrleine |

Der **Rettungsknoten** ist der einzige Knoten, der zum Sichern von Personen verwendet wird.

## Funkverkehr

Der Funk ist das wichtigste Kommunikationsmittel. Es gilt: **kurz, klar, eindeutig.** Vor dem Sprechen hören, ob der Kanal frei ist; Sprechtaste kurz vor dem Sprechen drücken.

Aufbau eines Gesprächs:
> „Leitstelle Los Santos von HLF Station 1, kommen.“ – „Hier Leitstelle, kommen.“ – „Am Einsatzort eingetroffen, erkunden, kommen.“ – „Verstanden, Ende.“

## FMS-Status

Statusmeldungen werden per Tastendruck übermittelt und entlasten den Funkkanal.

| Status | Bedeutung |
|---|---|
| **0** | Dringender Sprechwunsch (Notfall) – priorisiert |
| **1** | Einsatzbereit über Funk (unterwegs) |
| **2** | Einsatzbereit auf der Wache |
| **3** | Einsatz übernommen / Anfahrt |
| **4** | Am Einsatzort eingetroffen |
| **5** | Sprechwunsch |
| **6** | Nicht einsatzbereit (außer Dienst) |

**Status 0** wird nur bei unmittelbarer Gefahr für Einsatzkräfte genutzt, etwa bei einem Angriff auf die Besatzung oder einem Atemschutznotfall ohne Sprechfunk.

## Funkdisziplin

- Keine privaten Gespräche über Funk.
- Ortsangaben mit Straßenname und Bezugspunkt, z. B. „Grove Street, Höhe Tankstelle“.
- Personenbezogene Daten so knapp wie möglich.

*Merke:* Wer Status 4 drückt, sagt der Leitstelle: „Wir sind da“ – die Lagemeldung folgt per Sprechfunk durch den Fahrzeugführer.
MD],
        ],
        'questions' => [
            ['Wofür steht die Merkformel der Grundaufgaben der Feuerwehr?', ['Rufen – Laufen – Bergen – Schützen', 'Retten – Löschen – Bergen – Schützen', 'Retten – Löschen – Melden – Sichern', 'Retten – Lagern – Bergen – Sperren'], [1], 'Die Grundaufgaben der Feuerwehr sind Retten, Löschen, Bergen und Schützen.'],
            ['Welches Fahrzeug eignet sich besonders für Waldbrände in Blaine County, wo keine Hydranten vorhanden sind?', ['ELW', 'DLK', 'TLF mit großem Wassertank', 'RW'], [2], 'Das Tanklöschfahrzeug führt mehrere tausend Liter Wasser mit und ist damit unabhängig von Hydranten.'],
            ['Welche Stärke hat eine Gruppe?', ['1/8', '1/5', '1/2', '1/21'], [0], 'Die Gruppe besteht aus einem Führer und acht Mann, also 9 Einsatzkräften.'],
            ['Welcher Trupp stellt nach FwDV 3 die Wasserversorgung her und übernimmt anschließend meist die Aufgabe des Sicherheitstrupps?', ['Angriffstrupp', 'Schlauchtrupp', 'Melder und Maschinist gemeinsam', 'Wassertrupp'], [3], 'Der Wassertrupp baut die Wasserversorgung auf und ist danach Sicherheitstrupp.'],
            ['Welche Aufgabe hat der Maschinist?', ['Er führt die Gruppe und erkundet die Lage', 'Er fährt das Fahrzeug und bedient Pumpe und Aggregate', 'Er nimmt das erste Rohr vor', 'Er verlegt die Angriffsleitungen'], [1], 'Der Maschinist ist Fahrer und bedient die Pumpe sowie die Aggregate.'],
            ['Wann wird ein Löschangriff mit Bereitstellung durchgeführt?', ['Wenn die Lage unklar ist und der Gruppenführer zunächst erkunden muss', 'Immer bei Fahrzeugbränden', 'Wenn bereits ein Strahlrohr im Einsatz ist', 'Nur bei Gefahrguteinsätzen'], [0], 'Bei unklarer Lage wird Gerät am Verteiler bereitgestellt, bis der Gruppenführer erkundet hat.'],
            ['Welcher Knoten ist für das Sichern und Retten von Personen vorgesehen?', ['Mastwurf', 'Zimmermannsschlag', 'Kreuzknoten', 'Rettungsknoten (Brustbund)'], [3], 'Nur der Rettungsknoten wird zum Retten und Selbstretten von Personen verwendet.'],
            ['Mit welchem Knoten verbindet man zwei ungleich starke Leinen?', ['Spierenstich', 'Schotenstich', 'Halbschlag', 'Kreuzknoten'], [1], 'Der Schotenstich verbindet ungleich starke Leinen, der Kreuzknoten gleich starke.'],
            ['Was bedeutet FMS-Status 3?', ['Am Einsatzort', 'Einsatzbereit auf der Wache', 'Einsatz übernommen', 'Sprechwunsch'], [2], 'Status 3 bedeutet Einsatz übernommen bzw. Anfahrt zum Einsatzort.'],
            ['Welche Status-Meldung wird bei unmittelbarer Gefahr für die Einsatzkräfte gedrückt?', ['Status 5', 'Status 6', 'Status 1', 'Status 0'], [3], 'Status 0 ist der priorisierte, dringende Sprechwunsch für Notfälle.'],
            ['Welche Aussagen zur persönlichen Schutzausrüstung sind richtig?', ['Nach einem Brandeinsatz gilt die PSA als kontaminiert', 'Die Flammschutzhaube ist nur bei Regen nötig', 'Ohne vollständige PSA wird kein Einsatz begonnen', 'Der Kinnriemen des Helms darf offen bleiben, wenn es heiß ist'], [0, 2], 'PSA gilt nach dem Brand als kontaminiert, und ohne vollständige PSA wird nicht in den Einsatz gegangen.'],
            ['Wohin sitzt die Mannschaft auf den Befehl „Absitzen!“ ab?', ['Zur verkehrsabgewandten Seite und tritt hinter dem Fahrzeug an', 'Direkt zum Brandobjekt', 'Zur Fahrbahnseite, um schneller zu sein', 'Jeder Trupp beginnt sofort selbstständig mit seiner Arbeit'], [0], 'Abgesessen wird zur sicheren, verkehrsabgewandten Seite; danach wird auf Befehle gewartet.'],
            ['Wozu dient die Schlauchreserve vor dem Brandobjekt?', ['Als Ersatz bei geplatzten Schläuchen', 'Damit der Angriffstrupp ohne Zug auf der Leitung ins Gebäude vorgehen kann', 'Zur Kühlung des Verteilers', 'Sie ist nur bei der Drehleiter nötig'], [1], 'Die Schlauchreserve gibt dem Angriffstrupp genügend Leitung für das Vorgehen im Gebäude.'],
            ['Was ist beim Öffnen einer Tür zu einem Brandraum zu beachten?', ['Tür schnell und vollständig aufreißen, damit Rauch abzieht', 'Kein Strahlrohr mitnehmen, um beweglich zu bleiben', 'Türkontrolle durchführen, tief stehen und Strahlrohr bereithalten', 'Vorher immer das Fenster einschlagen'], [2], 'Die Türkontrolle mit tiefer Position und einsatzbereitem Rohr schützt vor Durchzündungen.'],
        ],
    ],
    [
        'title' => 'Atemschutzgeräteträger',
        'description' => 'Lehrgang nach FwDV 7 für den Einsatz unter umluftunabhängigem Atemschutz. Behandelt Atemgifte, Gefahren der Einsatzstelle, den Pressluftatmer, Einsatzkurzprüfung, Atemschutzüberwachung, Notfallmanagement sowie Eignung und Belastungsübung.',
        'hours' => 10, 'pass_percent' => 80, 'question_count' => 10, 'time_limit' => 20,
        'qualification' => 'Atemschutzgeräteträger',
        'modules' => [
            ['Grundlagen FwDV 7 und Eignung', <<<'MD'
# Grundlagen FwDV 7 und Eignung

Die **Feuerwehr-Dienstvorschrift 7 „Atemschutz“** regelt Ausbildung, Einsatz und Überwachung von Atemschutzgeräteträgern. Sie gilt beim LSFD für alle Wachen – von Station 1 Davis bis Paleto Bay.

## Voraussetzungen

Atemschutzgeräteträger darf nur sein, wer:
1. mindestens **18 Jahre** alt ist,
2. die **Grundausbildung (Truppmann)** abgeschlossen hat,
3. **körperlich geeignet** ist – nachgewiesen durch die arbeitsmedizinische Vorsorgeuntersuchung **G 26.3** (heute DGUV-Grundsatz „Atemschutzgeräte“, Gerätegruppe 3),
4. den Lehrgang Atemschutzgeräteträger erfolgreich abgeschlossen hat.

## Eignungsuntersuchung G 26.3

Die Untersuchung führt ein ermächtigter Arzt durch, im Spiel ein Betriebsarzt der **SAMS**. Geprüft werden u. a. Herz-Kreislauf-System (Belastungs-EKG), Lungenfunktion, Seh- und Hörvermögen.

- Unter 50 Jahren: Nachuntersuchung alle **36 Monate**
- Ab 50 Jahren: alle **12 bis 24 Monate**
- Nach schwerer Erkrankung oder auf eigenen Wunsch jederzeit

Ohne gültige G 26.3 ist ein Einsatz unter Atemschutz **verboten**. Die Gültigkeit wird im System in der Personalakte hinterlegt.

## Fortbildungspflicht

Jeder Atemschutzgeräteträger muss **jährlich**:
- eine **Belastungsübung** in der Atemschutzübungsstrecke absolvieren,
- an einer **Unterweisung** teilnehmen,
- zusätzlich mindestens eine **Einsatzübung** oder einen realen Einsatz unter Atemschutz nachweisen.

Wer die jährliche Übung versäumt, wird bis zur Nachholung aus dem Atemschutzpool genommen.

## Belastungsübung

Die Übungsstrecke simuliert Einsatzbedingungen: Endlosleiter, Laufband, Schlaghammer, Kriechstrecke mit Hindernissen, Dunkelheit, Wärme und Lärm. Ziel ist, Belastbarkeit und den eigenen **Luftverbrauch** realistisch kennenzulernen. Der durchschnittliche Verbrauch liegt bei schwerer Arbeit bei etwa **40 bis 60 Litern pro Minute**.

## Grundsätze des Atemschutzeinsatzes

- Atemschutz wird **immer truppweise** eingesetzt, mindestens zwei Geräteträger.
- Der Trupp geht gemeinsam vor und kehrt gemeinsam zurück.
- Jeder Trupp braucht ein **Funkgerät**.
- Für jeden eingesetzten Trupp steht ein **Sicherheitstrupp** bereit.

*Merke:* Atemschutz ist kein Sport, sondern Lebensversicherung. Wer sich unwohl fühlt, meldet das – vor dem Anschließen, nicht danach.
MD],
            ['Gefahren der Einsatzstelle und Atemgifte', <<<'MD'
# Gefahren der Einsatzstelle und Atemgifte

Die Gefahren an Einsatzstellen werden mit der **AAAA-C-EEEE-Regel** (Merkhilfe „Angstschweiß“) systematisch erfasst:

| Buchstabe | Gefahr |
|---|---|
| **A** | **Atemgifte** |
| **A** | **Angst**reaktion |
| **A** | **Ausbreitung** |
| **A** | **Atomare** Gefahren (Strahlung) |
| **C** | **Chemische** Stoffe |
| **E** | **Erkrankung** / Verletzung |
| **E** | **Explosion** |
| **E** | **Elektrizität** |
| **E** | **Einsturz** |

## Einteilung der Atemgifte

Atemgifte werden nach ihrer Wirkung eingeteilt:

1. **Erstickend wirkende Stoffe:** verdrängen den Sauerstoff, z. B. Stickstoff, CO₂, Argon. Unter **17 Vol.-% Sauerstoff** treten erste Ausfallerscheinungen auf, unter 10 % Bewusstlosigkeit.
2. **Reiz- und ätzend wirkende Stoffe:** greifen Atemwege und Lunge an, z. B. Ammoniak, Chlor, Salzsäuredämpfe. Folge kann ein **toxisches Lungenödem** sein – oft Stunden später.
3. **Stoffe mit Wirkung auf Blut, Nerven und Zellen:** z. B. **Kohlenmonoxid (CO)** und **Blausäure (HCN)**. CO bindet etwa 250- bis 300-mal stärker an Hämoglobin als Sauerstoff.

## Brandrauch

Brandrauch ist immer ein **Atemgiftgemisch**. Er enthält CO, CO₂, HCN, Ruß, Aldehyde und krebserregende Kohlenwasserstoffe. Schon wenige Atemzüge können tödlich sein. Bei Bränden in Wohngebäuden in Davis oder Rockford Hills entsteht HCN vor allem aus Kunststoffen, Polstermöbeln und Dämmmaterial.

## Besondere Lagen

- **Humane Labs:** Forschungslabore mit biologischen und chemischen Stoffen – Atemschutz und ggf. Chemikalienschutz zwingend, Einsatz nur in Absprache mit dem Betreiber.
- **Port of Los Santos:** Containerlager mit unbekannten Stoffen, Gase in Schiffsräumen.
- **Kanalisation und Silos:** Sauerstoffmangel und Faulgase (H₂S, Methan).

## Angst als Gefahr

Enge, Dunkelheit, Hitze und Lärm können Panik auslösen. Panik erhöht den Luftverbrauch massiv. Gegenmittel sind gute Ausbildung, ruhige Atmung und enge Kommunikation im Trupp.

*Merke:* Filtergeräte schützen nicht vor Sauerstoffmangel und nicht vor Kohlenmonoxid in Brandrauch – im Brandeinsatz wird **ausschließlich umluftunabhängiger Atemschutz** getragen.
MD],
            ['Der Pressluftatmer und die Einsatzkurzprüfung', <<<'MD'
# Der Pressluftatmer und die Einsatzkurzprüfung

Der **Pressluftatmer (PA)** ist ein umluftunabhängiges, offenes Atemschutzgerät: Die Ausatemluft wird an die Umgebung abgegeben.

## Aufbau

- **Druckluftflasche:** meist 6,8 Liter Composite bei **300 bar** (ca. 1.800 Liter Luft) oder Stahlflasche 6 Liter bei 300 bar.
- **Flaschenventil** mit Handrad.
- **Druckminderer:** reduziert den Flaschendruck auf ca. 7 bis 9 bar Mitteldruck.
- **Lungenautomat:** liefert Luft bei Bedarf, im **Überdrucksystem** bleibt ein leichter Überdruck in der Maske, damit kein Rauch eindringt.
- **Manometer** mit Leuchtanzeige zur Druckkontrolle.
- **Warnsignal:** ertönt bei ca. **55 ± 5 bar** Restdruck.
- **Atemanschluss:** Vollmaske mit Sprechmembran, Innenmaske gegen Beschlagen.
- **Tragegestell** mit Bebänderung.

## Einsatzkurzprüfung

Unmittelbar vor jedem Einsatz führt jeder Geräteträger die **Einsatzkurzprüfung** durch – auch unter Zeitdruck:

1. **Sichtprüfung:** Gerät vollständig, Maske unbeschädigt, Flasche gefüllt und gesichert.
2. **Hochdruckdichtprüfung:** Flaschenventil öffnen, Druck ablesen (mindestens 90 % des Nenndrucks, also **≥ 270 bar**), Ventil schließen. Der Druckabfall darf innerhalb einer Minute höchstens **10 bar** betragen.
3. **Warnsignalprüfung:** Druck langsam ablassen, Warnsignal muss bei ca. 55 bar ertönen.
4. **Maskendichtprüfung:** Maske aufsetzen, Einatemöffnung verschließen, einatmen – die Maske muss sich ansaugen und dicht bleiben.
5. **Funktionsprüfung Lungenautomat:** anschließen, einige Atemzüge, Überdruck prüfen.

Erst danach meldet der Truppführer: **„Angriffstrupp einsatzbereit!“**

## Anlegen

Das Gerät wird angelegt, Bebänderung festgezogen, Flammschutzhaube über die Maskenbänderung gezogen, Helm aufgesetzt. Der Lungenautomat wird **erst kurz vor dem Betreten des Gefahrenbereichs** angeschlossen, um Luft zu sparen.

## Nach dem Einsatz

Der Geräteträger meldet sich bei der Atemschutzüberwachung ab. Das Gerät wird als **verbraucht** gekennzeichnet und in der Atemschutzwerkstatt geprüft, gereinigt und neu befüllt. Benutzte Masken werden desinfiziert.

*Merke:* Kein Atemschutzeinsatz ohne Einsatzkurzprüfung. Die zwei Minuten sind gut investiert.
MD],
            ['Atemschutzüberwachung und Rückzug', <<<'MD'
# Atemschutzüberwachung und Rückzug

Die **Atemschutzüberwachung** stellt sicher, dass jeder Trupp rechtzeitig zurückkehrt. Sie ist bei jedem Einsatz unter Atemschutz Pflicht und wird in der Regel vom **Maschinisten** oder einer eigens eingeteilten Kraft geführt, bei größeren Lagen vom Einsatzleiter an einer Atemschutzsammelstelle.

## Atemschutzüberwachungstafel

Erfasst werden:
- **Namen** der Truppmitglieder und Funkrufname,
- **Uhrzeit des Anschließens** (Beginn),
- **Flaschendruck** beim Anschließen,
- **Auftrag** und Einsatzabschnitt,
- Zeitpunkte der Druckabfragen,
- **Rückzugszeitpunkt** und Ende.

Die Tafel misst zusätzlich die abgelaufene Zeit. Bei moderner Technik übermitteln Telemetriesysteme Druck und Bewegungslosigkeit automatisch.

## Druckkontrollen und Zeit

- Nach **1/3** und **2/3** der erwarteten Einsatzzeit (in der Praxis etwa nach 10 und 20 Minuten) erfolgt eine **Druckabfrage** über Funk.
- Spätestens nach **30 Minuten** muss der Trupp zurück sein, sofern die Überwachung nicht anders bestimmt.
- Kann ein Trupp nicht erreicht werden, wird der Sicherheitstrupp eingesetzt.

## Rückzugsregel

Der Rückzug wird angetreten, wenn **der Druck auf die Hälfte** des Anfangsdrucks gesunken ist oder doppelt so viel Luft für den Rückweg benötigt wird wie für den Hinweg verbraucht wurde (**Rückzugsdruck = doppelter Hinwegverbrauch**). Maßgeblich ist immer das Truppmitglied mit dem **geringsten Druck**.

Beispiel: Anfangsdruck 300 bar, am Ziel 210 bar → Hinwegverbrauch 90 bar → für den Rückweg werden 180 bar benötigt → Rückzug spätestens bei **180 bar** Restdruck. Ist der Hinweg kürzer, gilt spätestens die Hälfte (hier **150 bar**). Maßgeblich ist immer der **höhere** der beiden Werte.

Ein Rückzug ist außerdem sofort einzuleiten bei:
- Ertönen des Warnsignals,
- Verletzung oder Unwohlsein eines Truppmitglieds,
- Ausfall der Funkverbindung,
- Gerätedefekt oder Befehl der Einsatzleitung.

## Kommunikation

Der Truppführer meldet: Erreichen des Ziels, Lageänderungen, Menschenfund, Rückzug und Verlassen des Gefahrenbereichs. Die Überwachung bestätigt jede Meldung und dokumentiert sie.

*Merke:* Der Trupp geht gemeinsam rein und gemeinsam raus – niemand bleibt allein zurück, auch wenn „nur noch ein Raum“ fehlt.
MD],
            ['Notfall, Mayday und Sicherheitstrupp', <<<'MD'
# Notfall, Mayday und Sicherheitstrupp

Ein Atemschutznotfall liegt vor, wenn ein Trupp seinen Rückweg nicht mehr aus eigener Kraft antreten kann oder sich in akuter Gefahr befindet.

## Typische Notfallsituationen

- Orientierungsverlust in verrauchten Gebäuden
- Einsturz, Einklemmung oder Sturz durch Decken
- Luftmangel oder Gerätedefekt
- Bewusstlosigkeit oder Verletzung eines Truppmitglieds
- Abriss oder Verlust der Schlauchleitung

## Notfallmeldung

Der Truppführer setzt sofort die **Mayday-Meldung** ab:

> „**Mayday, Mayday, Mayday** – hier Angriffstrupp HLF Station 7 – Standort 2. OG hinten links – Kollege eingeklemmt – Druck 120 bar – kommen.“

Merkhilfe: **Wer? Wo? Was? Wie viel Luft?** Nach dem Mayday hat der verunfallte Trupp **absoluten Funkvorrang**. Alle anderen Kräfte schweigen auf dem Kanal. Zusätzlich wird – sofern vorhanden – der **Notsignalgeber** (Bewegungslosmelder) manuell ausgelöst. Kann nicht gesprochen werden, wird am Funkgerät die Notruftaste bzw. **Status 0** gedrückt.

## Verhalten im Notfall

1. Ruhe bewahren, Atmung kontrollieren (Luft sparen).
2. Am Schlauch oder an der Wand orientieren.
3. Auf sich aufmerksam machen: Lampe, Klopfzeichen, Notsignal.
4. Wenn möglich: Rückzug zum nächsten sicheren Bereich, Fenster als Rettungsweg nutzen.

## Der Sicherheitstrupp

Der **Sicherheitstrupp** ist ein vollständig ausgerüsteter Atemschutztrupp, der außerhalb des Gefahrenbereichs **einsatzbereit** wartet. Er hat:
- angelegte Geräte (Lungenautomat noch nicht angeschlossen),
- eine eigene Schlauchleitung oder Rettungsleine,
- eine **Rettungsbaugruppe / Notfall-Atemluftversorgung** mit Ersatzmaske,
- Wärmebildkamera, Brechwerkzeug, Beleuchtung.

Er wird **nicht für andere Aufgaben** herangezogen. Wird der Sicherheitstrupp eingesetzt, muss sofort ein **neuer Sicherheitstrupp** gebildet werden. Gleichzeitig wird über die Leitstelle Unterstützung nachalarmiert, insbesondere **SAMS** mit Notarzt.

## Rettung des Verunfallten

Vorrang hat die **Luftversorgung**: Der Sicherheitstrupp schließt die Notfall-Atemluftversorgung an und bringt den Kollegen auf schnellstem Weg ins Freie – „Schnelligkeit vor Schonung“. Draußen übernimmt SAMS die Versorgung.

*Merke:* Ein Mayday-Ruf ist nie peinlich. Zu spät gerufen ist der eigentliche Fehler.
MD],
        ],
        'questions' => [
            ['Welche Untersuchung ist Voraussetzung für das Tragen von Pressluftatmern?', ['G 41 Arbeiten mit Absturzgefahr', 'G 26.3 Atemschutzgeräte Gruppe 3', 'G 25 Fahr- und Steuertätigkeit', 'Ein einfacher Sehtest'], [1], 'Atemschutzgeräteträger benötigen die Eignungsuntersuchung G 26.3.'],
            ['Wie oft muss die Belastungsübung in der Atemschutzübungsstrecke absolviert werden?', ['Alle fünf Jahre', 'Nur einmal während des Lehrgangs', 'Jährlich', 'Alle drei Monate'], [2], 'Nach FwDV 7 ist jährlich eine Belastungsübung nachzuweisen.'],
            ['Wofür steht das „C“ in der AAAA-C-EEEE-Regel?', ['Chemische Stoffe', 'Chaos an der Einsatzstelle', 'CO-Konzentration', 'Container'], [0], 'C steht für chemische Stoffe.'],
            ['Welche zwei Begriffe gehören zur E-Gruppe der Gefahrenmatrix?', ['Explosion', 'Erschöpfung der Leitstelle', 'Elektrizität', 'Entfernung'], [0, 2], 'Die vier E stehen für Erkrankung, Explosion, Elektrizität und Einsturz.'],
            ['Warum ist Kohlenmonoxid so gefährlich?', ['Es riecht stark und reizt die Augen', 'Es ist schwerer als Wasser', 'Es wirkt nur bei Temperaturen über 500 °C', 'Es bindet etwa 250- bis 300-mal stärker an Hämoglobin als Sauerstoff'], [3], 'CO verdrängt Sauerstoff vom Hämoglobin und ist geruchlos.'],
            ['Warum darf im Brandeinsatz kein Filtergerät getragen werden?', ['Filter sind zu teuer', 'Filtergeräte schützen weder vor Sauerstoffmangel noch zuverlässig vor CO im Brandrauch', 'Filtergeräte sind zu schwer', 'Filtergeräte sind nur für Polizeikräfte zugelassen'], [1], 'Im Brandeinsatz ist nur umluftunabhängiger Atemschutz zulässig.'],
            ['Welchen Mindestdruck muss eine 300-bar-Flasche bei der Einsatzkurzprüfung aufweisen?', ['200 bar', '150 bar', '270 bar (90 % des Nenndrucks)', '300 bar exakt'], [2], 'Gefordert sind mindestens 90 Prozent des Nenndrucks, also 270 bar.'],
            ['Wie groß darf der Druckabfall bei der Hochdruckdichtprüfung innerhalb einer Minute höchstens sein?', ['10 bar', '50 bar', '1 bar', '25 bar'], [0], 'Ein Druckabfall von maximal 10 bar pro Minute ist zulässig.'],
            ['Bei welchem Restdruck ertönt das Warnsignal des Pressluftatmers?', ['Bei 100 bar', 'Bei 150 bar', 'Bei 10 bar', 'Bei etwa 55 ± 5 bar'], [3], 'Das Warnsignal spricht bei etwa 55 bar an.'],
            ['Ein Trupp schließt mit 300 bar an und erreicht das Ziel mit 220 bar. Bei welchem Restdruck muss er spätestens den Rückzug antreten?', ['Bei 150 bar', 'Bei 160 bar', 'Erst beim Warnsignal', 'Bei 200 bar'], [1], 'Hinwegverbrauch 80 bar, für den Rückweg wird das Doppelte (160 bar) benötigt. Dieser Wert liegt über der Hälfte (150 bar) und ist daher maßgeblich.'],
            ['Wessen Flaschendruck ist für den Rückzug des Trupps maßgeblich?', ['Der des Truppführers', 'Der Durchschnitt beider Geräteträger', 'Der des Truppmitglieds mit dem geringsten Druck', 'Der des Sicherheitstrupps'], [2], 'Der Trupp richtet sich immer nach dem geringsten Flaschendruck.'],
            ['Was gehört in eine Mayday-Meldung?', ['Wer, wo, was ist passiert und wie viel Luft ist vorhanden', 'Nur das Wort „Hilfe“', 'Name des Einsatzleiters und Uhrzeit', 'Die Einsatznummer aus dem System'], [0], 'Die Meldung umfasst Einheit, Standort, Lage und Restdruck.'],
            ['Welche Aussage zum Sicherheitstrupp ist richtig?', ['Er darf zum Schlauchverlegen eingesetzt werden, solange nichts passiert', 'Er wird nur bei Großbränden gestellt', 'Er benötigt keine eigene Ausrüstung', 'Nach seinem Einsatz muss sofort ein neuer Sicherheitstrupp gebildet werden'], [3], 'Der Sicherheitstrupp ist ausschließlich für die Rettung von Einsatzkräften da und muss nach Einsatz sofort ersetzt werden.'],
            ['Wann wird der Lungenautomat angeschlossen?', ['Bereits auf der Wache', 'Kurz vor dem Betreten des Gefahrenbereichs', 'Während der Anfahrt', 'Erst wenn man Rauch riecht'], [1], 'Um Luft zu sparen, wird erst unmittelbar vor dem Gefahrenbereich angeschlossen.'],
        ],
    ],
    [
        'title' => 'Maschinist für Löschfahrzeuge',
        'description' => 'Lehrgang für Fahrer und Pumpenbediener der Lösch- und Hilfeleistungsfahrzeuge. Behandelt Fahrzeugkunde, Feuerlöschkreiselpumpe, Wasserversorgung, Fahren mit Sonder- und Wegerechten sowie Pflege und Prüfung der Fahrzeuge.',
        'hours' => 8, 'pass_percent' => 75, 'question_count' => 10, 'time_limit' => 20,
        'qualification' => 'Maschinist',
        'modules' => [
            ['Fahrzeugkunde für Maschinisten', <<<'MD'
# Fahrzeugkunde für Maschinisten

Der **Maschinist** ist verantwortlich für das Fahrzeug, die Pumpe und alle Aggregate. Er bringt die Mannschaft sicher an die Einsatzstelle und sorgt dafür, dass Wasser und Strom zuverlässig verfügbar sind.

## Fahrzeugtypen und Besonderheiten

| Fahrzeug | Wasser | Pumpe | Besonderheit |
|---|---|---|---|
| **HLF 20** | ca. 1.600–2.000 l | FPN 10-2000 | Hydraulischer Rettungssatz, Stromerzeuger, Lichtmast |
| **TLF 4000** | ca. 4.000 l | FPN 10-2000 | Wasserwerfer, geländegängig – Standard für Sandy Shores |
| **DLK 23/12** | – | – | Abstützung, Korb mit 3 Personen / 270 kg |
| **RW** | – | – | Seilwinde, Kran, Hebekissen, Stromerzeuger |

Die Bezeichnung **FPN 10-2000** bedeutet: **F**euerlösch**p**umpe **N**ormaldruck, Nennförderdruck **10 bar**, Nennförderstrom **2.000 l/min**.

## Beladung

Der Maschinist kennt die **Beladeplan-Belegung** aller Geräteräume (G1 bis G5, Heck). Typische Ordnung beim HLF:
- **G1 (links vorne):** Atemschutzgeräte, Feuerwehrleinen
- **G2:** Schläuche, Verteiler, Strahlrohre
- **G3 (Mitte):** Saugschläuche, Standrohr, Hydrantenschlüssel
- **Heck:** Pumpenbedienstand
- **G4–G6 (rechts):** hydraulisches Rettungsgerät, Stromerzeuger, Beleuchtung

## Fahrerlaubnis

Für die meisten Löschfahrzeuge ist die **Klasse C** erforderlich (über 7,5 t). Kleinere Fahrzeuge bis 4,75 t dürfen mit der Feuerwehrfahrberechtigung gefahren werden. Im LSFD wird die Fahrberechtigung nach Fahrschulung im System in der Personalakte eingetragen.

## Aufstellung an der Einsatzstelle

- Fahrzeug **nicht im Trümmerschatten** oder direkt vor dem Gebäude abstellen – die **DLK** braucht die Anfahrtsfläche.
- Auf Straßen: Fahrzeug **schräg** zur Absicherung stellen, Blaulicht und Warnblinker an, Heck mit Warnleitsystem.
- Hydranten und Zufahrten nicht zuparken.
- Bei Wind: möglichst **mit dem Wind im Rücken** aufstellen, damit Rauch und Gase vom Fahrzeug wegziehen.

## Aggregate

Der Maschinist bedient auch Stromerzeuger, Lichtmast, Lüfter und Tauchpumpen. Stromerzeuger werden im Freien betrieben (CO-Gefahr) und **nicht geerdet**, wenn sie im Schutzleitersystem (Schutztrennung) arbeiten.

*Merke:* Der Maschinist verlässt seinen Bedienstand nur in Absprache mit dem Gruppenführer – er ist die Lebensader für den Angriffstrupp.
MD],
            ['Die Feuerlöschkreiselpumpe', <<<'MD'
# Die Feuerlöschkreiselpumpe

Die **Feuerlöschkreiselpumpe** ist das Herzstück jedes Löschfahrzeugs. Sie erhöht den Druck des Wassers und fördert es zu den Strahlrohren.

## Funktionsprinzip

Ein schnell rotierendes **Laufrad** schleudert das Wasser durch Fliehkraft nach außen in das **Leitrad** bzw. Spiralgehäuse. Dabei wird Bewegungsenergie in Druck umgewandelt. Mehrstufige Pumpen hintereinandergeschalteter Laufräder erzeugen höhere Drücke.

Kreiselpumpen sind **nicht selbstansaugend** – sie benötigen eine **Entlüftungseinrichtung** (z. B. Kolben- oder Membranpumpe), um beim Saugbetrieb Luft aus Saugleitung und Pumpe zu entfernen.

## Bedienstand

- **Eingangsdruckmanometer (Manovakuummeter):** zeigt den Druck bzw. Unterdruck am Pumpeneingang.
- **Ausgangsdruckmanometer:** zeigt den Förderdruck.
- **Drehzahlregelung** bzw. Druckregelautomatik.
- **Tankumschaltung** und Niederdruckabgänge (B-Abgänge).
- **Motortemperatur und Pumpentemperatur.**

## Inbetriebnahme aus dem Tank

1. Nebenantrieb einschalten (Fahrzeug steht, Feststellbremse angezogen).
2. Tank-Pumpe-Ventil öffnen.
3. Abgang öffnen, sobald **„Wasser marsch!“** kommt.
4. Druck langsam erhöhen auf **5–8 bar** am Ausgang, je nach Länge der Leitung und Strahlrohr.

## Druckberechnung

Faustregel für den Pumpenausgangsdruck:

**Strahlrohrdruck + Reibungsverluste + Höhenunterschied**

- Hohlstrahlrohr: ca. **6 bar** am Rohr
- Reibungsverlust C-Schlauch (100 l/min): ca. **1 bar je 100 m**
- Höhenverlust: **1 bar je 10 m** Höhe

Beispiel: Rohr im 3. OG (ca. 10 m), 60 m Leitung → 6 + 0,6 + 1 ≈ **7,6 bar**.

## Kavitation und Überhitzung

- **Kavitation:** Bei zu hohem Unterdruck am Eingang verdampft Wasser, Dampfblasen implodieren und zerstören das Laufrad. Anzeichen: prasselndes Geräusch, schwankender Druck. Gegenmaßnahme: Drehzahl senken, Saughöhe verringern.
- **Überhitzung:** Läuft die Pumpe ohne Wasserabgabe, erwärmt sich das Wasser. Abhilfe: kleine Wassermenge über einen Abgang oder Kühlleitung ablassen.

## Druckstöße

Abgänge und Strahlrohre werden **langsam** geschlossen. Schlagartiges Schließen erzeugt **Druckstöße**, die Schläuche platzen lassen und Einsatzkräfte gefährden.

*Merke:* Ein Maschinist hört seine Pumpe. Veränderte Geräusche sind Warnzeichen – nie ignorieren.
MD],
            ['Wasserversorgung', <<<'MD'
# Wasserversorgung

Ohne Wasser kein Löschangriff. Der Maschinist muss schnell entscheiden, welche Wasserquelle genutzt wird und wie eine stabile Versorgung aufgebaut wird.

## Tankwasser als Erstversorgung

Der Fahrzeugtank ermöglicht einen schnellen Erstangriff. Ein HLF mit 2.000 l reicht mit einem C-Rohr (ca. 100–200 l/min) nur **10 bis 20 Minuten**. Daher wird **parallel** eine unabhängige Wasserversorgung aufgebaut.

## Hydranten

- **Unterflurhydrant:** im Boden, gekennzeichnet durch ein **Hinweisschild** (weißes Schild mit rotem Rand, Angaben zu Lage und Rohrdurchmesser). Benötigt **Standrohr**, **Hydrantenschlüssel** und Unterflurhydrantenschlüssel.
- **Überflurhydrant:** oberirdische Säule, im Stadtgebiet von Davis und Rockford Hills häufig. Wird mit dem Überflurhydrantenschlüssel geöffnet.

Vorgehen: Hydrant **spülen** (Schmutz und Steine auswerfen), Standrohr aufsetzen, Leitung anschließen, Hydrant **langsam** öffnen. Hydranten immer **vollständig öffnen oder schließen** – halb geöffnete Hydranten verursachen Ausspülungen.

Die Lage der Hydranten ist in den **Objektplänen im System** und auf den Hydrantenplänen der Wachen hinterlegt.

## Offenes Gewässer

In Blaine County, am Alamo Sea, am Zancudo River oder im **Port of Los Santos** kann aus offenen Gewässern gesaugt werden:

1. Saugstelle mit ausreichender Tiefe wählen (Saugkorb mind. 30 cm unter Wasseroberfläche, nicht auf dem Grund).
2. **Saugschläuche** (A-Saugschläuche) kuppeln, mit Saugkorb und Halteleine versehen.
3. Mit der Entlüftungseinrichtung ansaugen.
4. Maximale **geodätische Saughöhe** praktisch **7,5 m** – in der Praxis möglichst unter 3 m bleiben.

Zusätzlich kann eine **Tragkraftspritze** oder **Tauchpumpe** genutzt werden.

## Pendelverkehr

Wenn weder Hydrant noch Gewässer erreichbar sind – etwa beim Waldbrand am **Mount Chiliad** – wird ein **Pendelverkehr** eingerichtet:

- Tanklöschfahrzeuge fahren zwischen Füllstelle und Einsatzstelle.
- An der Einsatzstelle wird ein **Faltbehälter** oder Pufferbecken aufgestellt.
- Eine Pumpe saugt aus dem Behälter und speist den Löschangriff.
- Fahrwege werden als **Einbahnverkehr** organisiert, um Begegnungsverkehr zu vermeiden.

## Lange Wegstrecke

Bei langen Leitungen über Hunderte Meter werden **Verstärkerpumpen** in Abständen eingebaut, damit der Eingangsdruck an jeder Pumpe mindestens **1,5 bar** beträgt.

*Merke:* Die Wasserversorgung wird geplant, bevor der Tank leer ist – nicht danach.
MD],
            ['Fahren mit Sonder- und Wegerechten', <<<'MD'
# Fahren mit Sonder- und Wegerechten

Einsatzfahrten sind die gefährlichste Phase vieler Einsätze. Ein Unfall auf der Anfahrt hilft niemandem und bindet zusätzliche Kräfte.

## Sonderrechte (§ 35 StVO)

**Sonderrechte** befreien die Feuerwehr von den Vorschriften der Straßenverkehrsordnung, **soweit dies zur Erfüllung hoheitlicher Aufgaben dringend geboten ist**. Sie gelten unabhängig davon, ob Blaulicht eingeschaltet ist, setzen aber eine dringende Lage voraus.

Wichtig: Sonderrechte dürfen nur unter **gebührender Berücksichtigung der öffentlichen Sicherheit und Ordnung** ausgeübt werden. Der Fahrer haftet bei grober Fahrlässigkeit.

## Wegerecht (§ 38 StVO)

**Wegerecht** entsteht nur durch **blaues Blinklicht zusammen mit dem Einsatzhorn**. Es fordert alle anderen Verkehrsteilnehmer auf: **„Sofort freie Bahn schaffen!“**

Wegerecht darf nur verwendet werden, wenn:
- höchste Eile geboten ist, um **Menschenleben zu retten** oder schwere gesundheitliche Schäden abzuwenden,
- eine Gefahr für die öffentliche Sicherheit oder Ordnung abzuwenden ist,
- flüchtige Personen zu verfolgen sind (Polizei) oder bedeutende Sachwerte zu erhalten sind.

Blaulicht allein (ohne Horn) warnt nur und gibt **kein** Wegerecht.

## Sicheres Fahren

- **Kreuzungen** bei Rot nur mit Schrittgeschwindigkeit und erst, wenn alle Verkehrsteilnehmer reagiert haben – jede Richtung einzeln „freifahren“.
- Rettungsgasse auf dem Freeway nutzen, Abstand halten, mit Fehlreaktionen anderer rechnen.
- Bei Kolonnenfahrt mehrerer Fahrzeuge **verschiedene Hornsignale** oder ausreichende Abstände, da nachfolgende Fahrzeuge oft übersehen werden.
- Das Fahrzeug ist schwer (bis 16 t) und hat einen langen **Bremsweg** sowie hohen Schwerpunkt – Kurven bremsen, nicht in der Kurve.

## Rechtliche Konsequenzen

Unfälle mit Sonderrechten werden vom **LSPD** aufgenommen. Der Maschinist meldet jeden Unfall sofort an die Leitstelle und den Wachleiter und dokumentiert ihn im System. Im Zweifel geht **Sicherheit vor Schnelligkeit** – die Zeitersparnis einer riskanten Fahrt liegt meist unter einer Minute.

## Rückfahrt

Auf der Rückfahrt (Status 1) gelten **keine Sonderrechte** – das Fahrzeug fährt im normalen Straßenverkehr.

*Merke:* Blaulicht und Horn bitten um freie Bahn – sie erzwingen sie nicht.
MD],
            ['Fahrzeugpflege und Prüfungen', <<<'MD'
# Fahrzeugpflege und Prüfungen

Ein Löschfahrzeug muss jederzeit sofort einsatzbereit sein. Der Maschinist ist für den technischen Zustand seines Fahrzeugs mitverantwortlich.

## Fahrzeugcheck bei Dienstbeginn

Zu Schichtbeginn auf jeder Wache – ob Station 1 Davis oder Paleto Bay – prüft der Maschinist:

1. **Füllstände:** Kraftstoff (mind. 3/4), Motoröl, Kühlwasser, AdBlue, Löschwassertank voll, Schaummitteltank.
2. **Reifen:** Luftdruck und Profil, keine Beschädigungen.
3. **Beleuchtung:** Fahrlicht, Blaulicht, Umfeldbeleuchtung, Lichtmast.
4. **Akustische Signale:** Einsatzhorn kurz prüfen (rücksichtsvoll!).
5. **Funkgeräte und FMS:** Akkus geladen, Status 2 gesetzt.
6. **Beladung:** Vollständigkeit nach Beladeliste.
7. **Aggregate:** Stromerzeuger, Motorsäge, Trennschleifer betankt.

Ergebnisse werden im **Fahrzeugbuch** bzw. im System dokumentiert. Mängel, die die Einsatzbereitschaft beeinträchtigen, führen zu **Status 6** und werden sofort an den Wachleiter gemeldet.

## Nach dem Einsatz

- Tank auffüllen, Kraftstoff nachtanken.
- Schläuche tauschen: Benutzte Schläuche gehen in die **Schlauchwerkstatt** (Reinigung, Druckprüfung, Trocknung).
- Pumpe nach Saugbetrieb aus offenem Gewässer mit Klarwasser **spülen**, nach Schaumeinsatz ebenfalls.
- Verbrauchsmaterial ergänzen.
- Fahrzeug grob reinigen, insbesondere kontaminierte Bereiche (Schwarz-Weiß-Trennung).
- Erst danach **Status 2** melden.

## Regelmäßige Prüfungen

| Prüfung | Intervall |
|---|---|
| Pumpe: Trockensaugprobe / Druckprüfung | **monatlich** bzw. nach jeder Reparatur |
| Hauptuntersuchung (TÜV) | jährlich |
| Drehleiter-Sachkundigenprüfung | jährlich |
| Hydraulisches Rettungsgerät | jährlich |
| Druckschläuche | nach jedem Einsatz und jährlich |

Bei der **Trockensaugprobe** wird geprüft, ob die Pumpe dicht ist: Mit verschlossenen Abgängen wird ein Unterdruck von **0,8 bar** erzeugt; dieser darf innerhalb einer Minute um höchstens **0,1 bar** abfallen.

## Winterbetrieb

In den Höhenlagen von **Mount Chiliad** ist im Winter auf Frostschutz zu achten: Pumpe entwässern oder beheizen, Fahrzeughalle temperiert halten.

*Merke:* Ein Fahrzeug ist erst wieder einsatzbereit, wenn es vollständig, betankt und geprüft ist – nicht, wenn es nur in der Halle steht.
MD],
        ],
        'questions' => [
            ['Was bedeutet die Bezeichnung FPN 10-2000?', ['Feuerlöschpumpe Normaldruck, 10 bar Nennförderdruck, 2.000 l/min Nennförderstrom', 'Feuerwehrpumpe Niederdruck, 10 Schläuche, 2.000 bar', 'Fahrzeug mit 10 Plätzen und 2.000 l Tank', 'Feuerlöschpumpe mit 10 Laufrädern und 2.000 Umdrehungen'], [0], 'FPN steht für Feuerlöschpumpe Normaldruck mit 10 bar und 2.000 l/min.'],
            ['Warum benötigt eine Feuerlöschkreiselpumpe eine Entlüftungseinrichtung?', ['Um das Wasser zu kühlen', 'Um Druckstöße abzufangen', 'Weil Kreiselpumpen nicht selbstansaugend sind', 'Damit der Motor nicht überhitzt'], [2], 'Kreiselpumpen können Luft nicht selbstständig fördern und brauchen daher eine Entlüftung.'],
            ['Woran erkennt man Kavitation?', ['An einem prasselnden Geräusch und schwankendem Druck', 'An grünem Wasser im Tank', 'An einem steigenden Tankfüllstand', 'An der Blaulichtanzeige'], [0], 'Kavitation äußert sich durch Prasseln und Druckschwankungen; Drehzahl senken.'],
            ['Wie hoch ist der Höhenverlust bei der Druckberechnung als Faustregel?', ['10 bar je 1 m', '1 bar je 10 m', '0,1 bar je 100 m', 'Höhe spielt keine Rolle'], [1], 'Pro 10 Meter Höhenunterschied wird etwa 1 bar zusätzlich benötigt.'],
            ['Warum sollen Strahlrohre und Abgänge langsam geschlossen werden?', ['Um Wasser zu sparen', 'Weil die Pumpe sonst kalt wird', 'Damit keine Luft angesaugt wird', 'Um gefährliche Druckstöße zu vermeiden'], [3], 'Schnelles Schließen erzeugt Druckstöße, die Schläuche platzen lassen können.'],
            ['Was ist beim Öffnen eines Hydranten zu beachten?', ['Hydrant halb geöffnet lassen, um Wasser zu sparen', 'Vorher spülen und dann langsam vollständig öffnen', 'Nur mit dem Fahrzeug direkt darüber parken', 'Hydranten dürfen nur von der Wasserbehörde geöffnet werden'], [1], 'Hydranten werden gespült und dann langsam vollständig geöffnet, halb geöffnet entstehen Ausspülungen.'],
            ['Wie wird die Wasserversorgung beim Waldbrand am Mount Chiliad ohne Hydranten und Gewässer sichergestellt?', ['Durch Pendelverkehr mit Tanklöschfahrzeugen und Faltbehälter', 'Durch Abwarten auf Regen', 'Ausschließlich durch Löschflugzeuge', 'Mit einer einzigen langen C-Leitung'], [0], 'Ein Pendelverkehr mit Tankfahrzeugen und Pufferbehälter sichert die Versorgung.'],
            ['Wodurch entsteht das Wegerecht nach § 38 StVO?', ['Durch Blaulicht allein', 'Durch eine Warnweste', 'Durch blaues Blinklicht zusammen mit dem Einsatzhorn', 'Durch das Fahren in einer Kolonne'], [2], 'Nur die Kombination aus Blaulicht und Einsatzhorn begründet das Wegerecht.'],
            ['Welche Aussagen zu Sonderrechten sind richtig?', ['Sie gelten nur, soweit dies zur Aufgabenerfüllung dringend geboten ist', 'Der Fahrer haftet niemals für Unfälle', 'Sie gelten auch auf der Rückfahrt zur Wache', 'Sie sind unter gebührender Berücksichtigung der öffentlichen Sicherheit auszuüben'], [0, 3], 'Sonderrechte sind zweckgebunden und erfordern Rücksicht auf die öffentliche Sicherheit.'],
            ['Wie wird eine Kreuzung bei Rot mit Wegerecht überquert?', ['Mit unverminderter Geschwindigkeit, da alle anhalten müssen', 'Gar nicht, man muss immer warten', 'Nur wenn die Polizei vor Ort ist', 'Langsam, jede Fahrtrichtung einzeln freifahren und Reaktionen abwarten'], [3], 'Kreuzungen werden mit Schrittgeschwindigkeit und Blickkontakt freigefahren.'],
            ['Wie lange reicht ein Tank mit 2.000 l bei einem C-Rohr mit etwa 100 bis 200 l/min ungefähr?', ['10 bis 20 Minuten', '2 Stunden', '1 bis 2 Minuten', 'Unbegrenzt'], [0], '2.000 l geteilt durch 100 bis 200 l/min ergeben etwa 10 bis 20 Minuten.'],
            ['Welcher Status wird gemeldet, wenn ein Mangel die Einsatzbereitschaft beeinträchtigt?', ['Status 2', 'Status 6', 'Status 4', 'Status 3'], [1], 'Nicht einsatzbereite Fahrzeuge melden Status 6.'],
            ['Wie viel darf der Unterdruck bei der Trockensaugprobe innerhalb einer Minute höchstens abfallen?', ['0,5 bar', '1 bar', '0,1 bar', 'Er darf beliebig abfallen'], [2], 'Bei 0,8 bar Unterdruck ist ein Abfall von höchstens 0,1 bar pro Minute zulässig.'],
            ['Wo sollte ein Löschfahrzeug an einem Gebäudebrand möglichst nicht abgestellt werden?', ['Im Trümmerschatten und auf der Anfahrtsfläche der Drehleiter', 'Mit dem Wind im Rücken', 'In ausreichendem Abstand zum Gebäude', 'Neben dem Hydranten, ohne ihn zu verdecken'], [0], 'Trümmerschatten und DLK-Aufstellflächen sind freizuhalten.'],
        ],
    ],
    [
        'title' => 'Technische Hilfeleistung',
        'description' => 'Lehrgang zur Rettung eingeklemmter Personen und zur technischen Hilfe. Behandelt Verkehrsunfall-Taktik, patientengerechte Rettung in Abstimmung mit SAMS, hydraulisches Rettungsgerät, Stabilisierung, Absturzsicherung, Türöffnung sowie Elektro- und Hybridfahrzeuge.',
        'hours' => 8, 'pass_percent' => 75, 'question_count' => 10, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Verkehrsunfall mit eingeklemmter Person', <<<'MD'
# Verkehrsunfall mit eingeklemmter Person

Verkehrsunfälle auf dem Del Perro Freeway, der Great Ocean Highway oder der Route 68 gehören zu den häufigsten Einsätzen der technischen Hilfeleistung. Ziel ist die **schnelle, aber schonende Befreiung** der Patienten in enger Abstimmung mit dem Rettungsdienst **SAMS**.

## Einsatzablauf

1. **Absichern:** Fahrzeug schräg zur Absicherung stellen, Warnleitsystem, Beleuchtung. Bei Bedarf Vollsperrung durch **LSPD** oder **BCSO**.
2. **Erkunden:** Innerer und äußerer Erkundungskreis.
   - **Innerer Kreis** (ca. 1 m um das Fahrzeug): Personen, Einklemmung, Airbags, Kraftstoff, Antriebsart.
   - **Äußerer Kreis** (ca. 10 m): weitere Beteiligte, herausgeschleuderte Personen, Trümmer, auslaufende Stoffe.
3. **Brandschutz sicherstellen:** Feuerlöscher und ein Rohr in Bereitstellung.
4. **Erstzugang schaffen:** Ein Retter (oft mit SAMS-Kraft) gelangt zum Patienten, ohne Werkzeug wenn möglich (offene Tür, Seitenscheibe).
5. **Stabilisieren** des Fahrzeugs.
6. **Glasmanagement:** Scheiben kontrolliert entfernen, Patienten mit Folie oder Decke schützen.
7. **Versorgungsöffnung** und **Rettungsöffnung** schaffen.
8. **Patientengerechte Rettung** gemeinsam mit SAMS.

## Patientengerechte Rettung

Die Feuerwehr richtet die Technik nach dem **Zustand des Patienten** aus, nicht umgekehrt. Der Notarzt oder Notfallsanitäter von SAMS gibt die Dringlichkeit vor:

| Dringlichkeit | Vorgehen |
|---|---|
| **Sofortrettung** (Crash-Rettung) | Akute Lebensgefahr (Brand, Reanimation, Atemwegsverlegung) – Patient sofort und ohne große Rücksicht auf Wirbelsäulenschutz befreien |
| **Schnelle Rettung** | Patient instabil, Befreiung in ca. **20 Minuten** (Golden Hour beachten) |
| **Schonende Rettung** | Patient stabil – großzügige Rettungsöffnung, achsengerechte Rettung mit Spineboard |

## Rettungsöffnung

Die Rettungsöffnung muss so groß sein, dass der Patient **achsengerecht** (in Körperlängsachse) auf einem Spineboard herausgezogen werden kann. Typische Techniken:
- **Dachabnahme** (vollständig oder nach vorne/hinten geklappt),
- **Seitenöffnung** (Tür und B-Säule entfernen),
- **Heckrettung** bei Kleinwagen,
- **Armaturenbrett wegdrücken** bei eingeklemmten Beinen.

## Kommunikation

Ein Feuerwehrangehöriger und ein Rettungsdienstmitarbeiter bilden ein festes **Rettungsteam am Patienten**. Jeder Arbeitsschritt wird angekündigt („Achtung, Schere schneidet A-Säule links!“), damit der Patient beruhigt und geschützt werden kann.

*Merke:* Die Feuerwehr befreit, SAMS behandelt – die Taktik wird **gemeinsam** festgelegt.
MD],
            ['Hydraulisches Rettungsgerät und Stabilisierung', <<<'MD'
# Hydraulisches Rettungsgerät und Stabilisierung

## Komponenten

Das **hydraulische Rettungsgerät** besteht aus:
- **Hydraulikaggregat** (Motorpumpe) oder **Akku-Geräten** mit integrierter Pumpe,
- **Schläuchen** mit Schnellkupplungen,
- **Schere** (Schneidgerät) zum Durchtrennen von Säulen und Blechen,
- **Spreizer** zum Aufdrücken, Quetschen und Ziehen,
- **Kombigerät** (Schneiden und Spreizen),
- **Rettungszylinder** zum Wegdrücken großer Bauteile (z. B. Armaturenbrett),
- **Zubehör:** Schwellerauflage, Ketten, Zylinderabstützung.

Arbeitsdrücke liegen bei bis zu **700 bar**. Hydraulikschläuche nie knicken, nie überfahren und nicht unter Druck kuppeln.

## Sicherheitsregeln

1. **Körper nie zwischen Gerät und Fahrzeug** bringen, nie in die Schneid- oder Spreizbewegung greifen.
2. Schere möglichst **senkrecht** zum Bauteil ansetzen; tief in die Klinge schneiden.
3. **Airbags:** Nicht ausgelöste Airbags beachten, Sicherheitsabstände (z. B. 30 cm Seitenairbag, 60 cm Fahrerairbag, 90 cm Beifahrerairbag) einhalten. Gaszylinder und Gurtstraffer nicht durchtrennen – Karosserie vorher **entkleiden** (Verkleidung entfernen).
4. **Rettungsdatenblatt** des Fahrzeugmodells abrufen (über die Leitstelle oder im System), um verstärkte Bereiche und Airbags zu erkennen.
5. Schutzbrille bzw. Visier, Handschuhe, Gehörschutz tragen.

## Stabilisierung

Ein Unfallfahrzeug muss vor Beginn der technischen Rettung **stabilisiert** werden, damit es sich nicht bewegt und den Patienten nicht weiter verletzt.

- **Fahrzeug auf den Rädern:** Unterbauen mit **Keilen und Stufenkeilen** an Schwellern (mindestens vier Punkte), Luft aus den Reifen lassen oder Ventile ziehen, Zündung aus, Handbremse an, Gang einlegen bzw. Parkstellung.
- **Fahrzeug auf der Seite:** Abstützen mit **Abstützsystemen** (Stab-Fast o. ä.), Spanngurten und Holzkeilen, Dachseite und Bodenseite.
- **Fahrzeug auf dem Dach:** Unterbauen im Bereich der A- und C-Säule, Absicherung gegen Kippen.

## Elektrische Systeme

Bei konventionellen Fahrzeugen wird die **Zündung ausgeschaltet** und der Schlüssel mindestens **5 m** entfernt (Keyless-Systeme). Die **Batterie** wird erst nach Absprache abgeklemmt (zuerst Minuspol), da elektrische Sitzverstellung oder Fensterheber zur Rettung nützlich sein können.

## Weiteres Gerät

- **Hebekissen** zum Anheben schwerer Lasten (z. B. Lkw auf Pkw im Port of Los Santos).
- **Mehrzweckzug / Seilwinde** des RW zum Sichern und Ziehen.
- **Säbelsäge** und **Glassäge** für Verbundglas.

*Merke:* Erst stabilisieren, dann schneiden. Ein wackelndes Fahrzeug ist eine zweite Gefahr für Patient und Retter.
MD],
            ['Absturzsicherung', <<<'MD'
# Absturzsicherung

Bei Arbeiten mit Absturzgefahr – auf Dächern in Rockford Hills, an Steilhängen am Mount Chiliad, an Brücken oder Hafenkränen im Port of Los Santos – müssen sich Einsatzkräfte gegen Absturz sichern.

## Wann ist eine Sicherung erforderlich?

Eine Absturzsicherung ist notwendig, wenn
- an **ungesicherten Kanten** mit mehr als **3 m Absturzhöhe** gearbeitet wird,
- Arbeiten an Wasserflächen, Abhängen oder Schächten erfolgen,
- Lasten oder Personen über Kanten bewegt werden.

## Rückhalten und Auffangen

- **Rückhalten:** Die Sicherung ist so kurz eingestellt, dass die Einsatzkraft die Absturzkante **gar nicht erreichen** kann. Dies ist immer die bevorzugte Methode.
- **Auffangen:** Die Einsatzkraft kann abstürzen, wird aber durch ein **Auffangsystem** mit Falldämpfer gebremst. Dies erfordert Auffanggurt, Falldämpfer und ausreichenden Freiraum unter der Person.

Der **Feuerwehr-Haltegurt** ist nur für das **Rückhalten** und für Selbstrettung geeignet – **nicht** zum Auffangen von Stürzen.

## Ausrüstung im Gerätesatz Absturzsicherung

- Auffanggurte mit Brust- und Rückenöse,
- Halteseile mit Längeneinstellung,
- Bandschlingen und Karabiner,
- Falldämpfer,
- **Statikseil** (kernmantel, geringe Dehnung),
- Abseilgerät, Seilrolle, Kantenschutz.

## Anschlagpunkte

Anschlagpunkte müssen eine **Last von mindestens 10 kN** sicher aufnehmen können. Geeignet sind tragende Stahlträger, Betonpfeiler, massive Bäume oder das Feuerwehrfahrzeug (bei abgesicherter Bremse). Ungeeignet sind Geländer, Regenrinnen, Antennen und Fahnenstangen.

Seile werden an Kanten immer mit **Kantenschutz** geführt.

## Rettung aus Höhen und Tiefen

Die Absturzsicherung unterscheidet sich von der speziellen Höhenrettung. Bei komplexen Lagen (z. B. Person am Kran, in einem Schacht bei Humane Labs oder in der Felswand am Mount Chiliad) wird die **Höhenrettungsgruppe** nachgefordert. Bis dahin sichern Einsatzkräfte die Lage und halten Kontakt zur Person.

## Hängetrauma

Eine in einem Gurt **regungslos hängende Person** ist akut gefährdet: Durch das Absacken des Blutes in die Beine kann innerhalb weniger Minuten ein **Kreislaufversagen** auftreten. Sie muss so schnell wie möglich gerettet und an SAMS übergeben werden.

*Merke:* Rückhalten ist besser als Auffangen – wer die Kante nicht erreicht, kann nicht fallen.
MD],
            ['Türöffnung', <<<'MD'
# Türöffnung

Die Feuerwehr öffnet Türen, wenn hinter ihnen eine **Gefahr für Leben oder Gesundheit** vermutet wird – etwa eine hilflose Person, ein Wasserrohrbruch oder ein angebranntes Essen mit piependem Rauchwarnmelder.

## Rechtliche Grundlage

Die Türöffnung ist eine **Maßnahme der Gefahrenabwehr**. Sie erfolgt grundsätzlich **in Begleitung des LSPD** bzw. **BCSO**, das die Rechtmäßigkeit der Maßnahme beurteilt, den Zugang dokumentiert und die Wohnung anschließend sichert. Bei akuter Lebensgefahr darf die Feuerwehr auch ohne Polizei sofort öffnen. Die Feuerwehr öffnet **keine Türen für Ermittlungen** oder Zwangsräumungen.

## Vorgehen

1. **Lage erkunden:** Nachbarn befragen, Klingeln, Klopfen, Rufen. Sind Fenster, Balkon oder Hintereingang offen? Hat jemand einen Schlüssel (Hausmeister, Angehörige)?
2. **Mildestes Mittel wählen:** Die Tür soll möglichst **beschädigungsarm** geöffnet werden.
3. **SAMS** ist bei vermuteter hilfloser Person **immer** mitalarmiert und betritt nach Öffnung gemeinsam mit der Feuerwehr die Wohnung.
4. Nach Öffnung: Gefahr beseitigen, Wohnung an Polizei oder Berechtigte übergeben, Schadensumfang im **Einsatzbericht** festhalten.

## Techniken (vom mildesten zum stärksten Eingriff)

- **Zugang über offene Fenster** oder Balkon (ggf. mit Leiter oder DLK).
- **Falle zurückschieben** mit Türfallenöffner oder flexibler Folie, wenn die Tür nur zugezogen ist.
- **Ziehfix / Schließzylinder ziehen:** Zylinder wird mit Spezialwerkzeug angebohrt und herausgezogen.
- **Hebeln mit Halligan-Tool** oder Türöffnungswerkzeug am Schließblech.
- **Gewaltsames Öffnen** mit Ramme – nur bei akuter Lebensgefahr.

## Brandeinsatz

Bei Bränden wird die Tür mit der **Türkontrolle** und einem einsatzbereiten Strahlrohr geöffnet. Ein Rauchvorhang (mobiler Rauchverschluss) wird gesetzt, um den Treppenraum rauchfrei zu halten.

## Dokumentation

Im System wird erfasst: Grund der Öffnung, anwesende Polizeieinheit, Technik, entstandener Schaden, Verbleib der Wohnung (versiegelt, Schlüsseldienst, Übergabe an Angehörige).

*Merke:* So viel Gewalt wie nötig, so wenig wie möglich – aber kein Zögern, wenn ein Leben auf dem Spiel steht.
MD],
            ['Elektro- und Hybridfahrzeuge', <<<'MD'
# Elektro- und Hybridfahrzeuge

Elektrofahrzeuge prägen zunehmend den Verkehr in Los Santos. Sie bringen neue Gefahren durch **Hochvoltsysteme** und **Lithium-Ionen-Akkus** mit sich.

## Erkennen

- Kennzeichen- oder Typbezeichnung („e“, „Hybrid“, „EV“), fehlender Auspuff, Ladeanschluss.
- **Orange Hochvoltkabel** im Motorraum und Unterboden.
- Fahrzeug kann **lautlos** und trotzdem fahrbereit sein („Ready“-Anzeige).
- **Rettungsdatenblatt** gibt Lage der Batterie, Trennstellen und Kabel an.

## Gefahren

1. **Elektrische Spannung:** Hochvoltsysteme arbeiten mit **400 bis 800 Volt Gleichspannung**. Bei unbeschädigtem System sind die Kabel isoliert und eigensicher.
2. **Thermisches Durchgehen (Thermal Runaway):** Beschädigte Zellen können sich selbst erhitzen und eine Kettenreaktion auslösen – mit Stichflammen, Explosion und giftigen Gasen (u. a. **Fluorwasserstoff**).
3. **Wiederentzündung:** Akkus können sich **Stunden bis Tage** nach dem Unfall erneut entzünden.

## Deaktivieren

- Fahrzeug sichern (Parkstellung, Feststellbremse).
- **Zündung ausschalten**, Schlüssel mindestens **5 m** entfernen.
- 12-Volt-Bordnetzbatterie abklemmen – damit schaltet sich das Hochvoltsystem über Schütze ab.
- Falls vorgesehen: **Trennstelle / Service-Disconnect** nach Rettungsdatenblatt ziehen.
- **Orange Kabel niemals durchtrennen**, Batteriegehäuse nicht beschädigen.

## Brandbekämpfung

- Brennende Akkus benötigen **große Mengen Wasser** zur Kühlung (mehrere tausend Liter), Atemschutz ist Pflicht.
- Wasser ist auch bei Hochvolt geeignet – Mindestabstände: **1 m** bei Sprühstrahl, **5 m** bei Vollstrahl (C-Rohr).
- Kühlen, bis keine Temperaturerhöhung mehr mit der **Wärmebildkamera** messbar ist.
- Ggf. **Löschcontainer** oder Abschleppen mit Brandsicherheitswache.

## Nach dem Einsatz

Das Fahrzeug wird dem Abschleppdienst mit dem ausdrücklichen Hinweis auf **Wiederentzündungsgefahr** übergeben: Abstellen im Freien, mindestens **15 m** Abstand zu Gebäuden und anderen Fahrzeugen. Im Einsatzbericht wird die Übergabe dokumentiert.

## Hybridfahrzeuge

Hybride vereinen Verbrennungsmotor und Hochvoltsystem – damit bestehen **beide** Gefahrenarten: auslaufender Kraftstoff und Hochvolt. Der Motor kann jederzeit selbstständig anspringen, solange das System aktiv ist.

*Merke:* Ein lautloses Fahrzeug ist nicht automatisch ein ausgeschaltetes Fahrzeug.
MD],
        ],
        'questions' => [
            ['Welchen Bereich umfasst der innere Erkundungskreis bei einem Verkehrsunfall?', ['Etwa 1 m um das Unfallfahrzeug', 'Etwa 50 m um die Unfallstelle', 'Nur den Innenraum des Fahrzeugs', 'Den gesamten Freeway-Abschnitt'], [0], 'Der innere Kreis umfasst ca. 1 m um das Fahrzeug, der äußere ca. 10 m.'],
            ['Wer legt die Dringlichkeit der Rettung einer eingeklemmten Person fest?', ['Der Maschinist', 'Das LSPD', 'Der Rettungsdienst SAMS gemeinsam mit dem Einsatzleiter der Feuerwehr', 'Der Abschleppdienst'], [2], 'Die patientengerechte Rettung wird gemeinsam festgelegt, wobei SAMS den medizinischen Zustand beurteilt.'],
            ['Wann ist eine Sofortrettung (Crash-Rettung) angezeigt?', ['Bei einem stabilen Patienten mit Beinfraktur', 'Bei akuter Lebensgefahr wie Fahrzeugbrand oder Reanimationspflicht', 'Immer, um Zeit zu sparen', 'Nur auf Wunsch des Patienten'], [1], 'Die Sofortrettung wird nur bei akuter Lebensgefahr angewendet.'],
            ['Wie muss die Rettungsöffnung bei schonender Rettung beschaffen sein?', ['So klein wie möglich, um Material zu sparen', 'Groß genug, um den Patienten achsengerecht auf einem Spineboard herauszubringen', 'Immer über den Kofferraum', 'Nur über die Fahrertür'], [1], 'Die Rettungsöffnung muss eine achsengerechte Rettung ermöglichen.'],
            ['Was ist vor dem Einsatz von Schere und Spreizer am Unfallfahrzeug durchzuführen?', ['Fahrzeug stabilisieren', 'Reifen aufpumpen', 'Fahrzeug abschleppen', 'Patient aus dem Fahrzeug ziehen'], [0], 'Erst stabilisieren, dann schneiden.'],
            ['Welche Bauteile dürfen beim Schneiden nicht durchtrennt werden?', ['Dachholme ohne Einbauten', 'Türscharniere', 'Gaszylinder von Airbags und Gurtstraffer', 'Kunststoffverkleidungen'], [2], 'Airbag-Gaszylinder und Gurtstraffer dürfen nicht durchtrennt werden; vorher wird die Verkleidung entfernt.'],
            ['Welche Aussagen zur Absturzsicherung sind richtig?', ['Rückhalten ist dem Auffangen vorzuziehen', 'Der Feuerwehr-Haltegurt ist zum Auffangen von Stürzen geeignet', 'Ein Geländer ist immer ein geeigneter Anschlagpunkt', 'Anschlagpunkte müssen mindestens 10 kN aufnehmen können'], [0, 3], 'Rückhalten wird bevorzugt, und Anschlagpunkte müssen mindestens 10 kN tragen.'],
            ['Warum ist eine regungslos im Gurt hängende Person akut gefährdet?', ['Weil der Gurt reißen kann', 'Weil sie sich erkälten kann', 'Wegen des Hängetraumas mit drohendem Kreislaufversagen', 'Weil sie die Funkverbindung verliert'], [2], 'Beim Hängetrauma versackt Blut in den Beinen, was zum Kreislaufversagen führen kann.'],
            ['Wer begleitet die Feuerwehr in der Regel bei einer Türöffnung?', ['Das LSPD bzw. BCSO', 'Ein Schlüsseldienst', 'Der Vermieter', 'Niemand'], [0], 'Die Polizei begleitet Türöffnungen, beurteilt die Rechtmäßigkeit und sichert die Wohnung anschließend.'],
            ['Welche Technik ist bei einer Türöffnung zuerst zu prüfen?', ['Einsatz der Ramme', 'Zugang über ein offenes Fenster oder Balkon', 'Herausziehen des Schließzylinders', 'Aufschneiden der Tür mit dem Trennschleifer'], [1], 'Es wird stets das mildeste Mittel gewählt, z. B. ein offenes Fenster.'],
            ['Welche Farbe haben Hochvoltkabel in Elektrofahrzeugen?', ['Blau', 'Gelb-grün', 'Schwarz', 'Orange'], [3], 'Hochvoltleitungen sind orange gekennzeichnet.'],
            ['Wie wird das Hochvoltsystem eines E-Fahrzeugs in der Regel deaktiviert?', ['Orange Kabel durchtrennen', 'Zündung aus, Schlüssel entfernen und 12-Volt-Batterie abklemmen bzw. Trennstelle ziehen', 'Fahrzeug mit Wasser fluten', 'Das ist nicht möglich, man muss warten'], [1], 'Zündung aus, Schlüssel entfernen und 12-V-Bordnetz bzw. Service-Disconnect trennen.'],
            ['Welcher Mindestabstand gilt beim Löschen eines Hochvoltfahrzeugs mit Vollstrahl (C-Rohr)?', ['10 cm', '1 m', '5 m', '50 m'], [2], 'Bei Vollstrahl gelten 5 m, bei Sprühstrahl 1 m.'],
            ['Was ist bei der Übergabe eines verunfallten E-Fahrzeugs an den Abschleppdienst zu beachten?', ['Kein Hinweis nötig, das Fahrzeug ist gelöscht', 'Fahrzeug in einer Tiefgarage abstellen', 'Batterie sofort ausbauen lassen', 'Auf Wiederentzündungsgefahr hinweisen und im Freien mit Abstand abstellen'], [3], 'Akkus können sich Stunden oder Tage später wieder entzünden.'],
        ],
    ],
    [
        'title' => 'Gefahrgut / CBRN',
        'description' => 'Lehrgang für Einsätze mit chemischen, biologischen, radiologischen und nuklearen Gefahren nach FwDV 500. Behandelt GAMS-Regel, Kennzeichnung, Absperrbereiche, Messtechnik, Dekontamination sowie die Zusammenarbeit mit LSPD und SAMS.',
        'hours' => 10, 'pass_percent' => 80, 'question_count' => 10, 'time_limit' => 25,
        'qualification' => 'Gefahrgut',
        'modules' => [
            ['FwDV 500 und die GAMS-Regel', <<<'MD'
# FwDV 500 und die GAMS-Regel

Die **FwDV 500 „Einheiten im CBRN-Einsatz“** regelt das Vorgehen bei Gefahren durch **c**hemische, **b**iologische, **r**adiologische und **n**ukleare Stoffe. Typische Einsatzorte in San Andreas sind der **Port of Los Santos** (Containerumschlag), die Raffinerien und Tanklager in El Burro Heights, der Flughafen **LSIA** (Luftfracht) sowie die Forschungseinrichtung **Humane Labs** am Fuß des Mount Chiliad.

## Die GAMS-Regel

Für die ersten Einsatzkräfte vor Ort gilt die **GAMS-Regel**:

| Buchstabe | Maßnahme |
|---|---|
| **G** | **Gefahr erkennen** – Kennzeichnung, Geruch, Dampfwolken, Verletzte, Aussagen von Zeugen |
| **A** | **Absperren** – Gefahrenbereich festlegen, Zutritt verhindern |
| **M** | **Menschenrettung durchführen** – nur mit geeigneter Schutzausrüstung |
| **S** | **Spezialkräfte alarmieren** – Gefahrgutzug, Messtechnik, Dekon, Fachberater |

Die GAMS-Regel ist bewusst einfach gehalten: Auch der erste Streifenwagen des LSPD oder ein SAMS-Rettungswagen kann sie anwenden.

## Grundsätze

1. **Eigenschutz hat oberste Priorität.** Wer sich selbst kontaminiert, wird vom Retter zum Patienten.
2. Anfahrt und Aufstellung **mit dem Wind im Rücken**, möglichst von erhöhter Position.
3. **Nicht essen, trinken, rauchen** im Gefahrenbereich.
4. Kontakt mit dem Stoff vermeiden – nicht durch Pfützen oder Dampfwolken gehen.
5. Betroffene Personen werden **zurückgehalten**, bis klar ist, ob sie kontaminiert sind.

## Gefahrengruppen

Nach FwDV 500 werden Einsatzbereiche je nach Gefährlichkeit in Gruppen eingeteilt:
- **Gruppe I:** Bereiche, in denen nur geringe Gefahren bestehen – Einsatz mit normaler Schutzausrüstung und Atemschutz möglich.
- **Gruppe II:** Bereiche mit erhöhter Gefahr – Einsatz nur mit **Sonderausrüstung** und unter Aufsicht eines Fachkundigen.
- **Gruppe III:** Bereiche mit hoher Gefahr (z. B. Labore der Sicherheitsstufe 3/4, hochradioaktive Stoffe) – Einsatz nur mit Spezialkräften und Fachberatung, oft in Abstimmung mit dem Betreiber.

## Schutzausrüstung

- **Form 1:** Feuerwehrschutzkleidung + umluftunabhängiger Atemschutz.
- **Form 2:** zusätzlich spezielle Kontaminationsschutzkleidung.
- **Form 3:** **gasdichter Chemikalienschutzanzug (CSA)** mit Pressluftatmer.

Beim Einsatz im CSA ist die Einsatzzeit stark begrenzt – meist **unter 20 Minuten** Arbeitszeit, da Hitze und Belastung enorm sind.

*Merke:* GAMS – erst denken, dann handeln. Ein gestoppter Retter ist besser als ein kontaminierter.
MD],
            ['Kennzeichnung: Gefahrnummern und UN-Nummern', <<<'MD'
# Kennzeichnung: Gefahrnummern und UN-Nummern

Gefahrguttransporte auf der Straße sind nach **ADR** gekennzeichnet. Für die Feuerwehr sind zwei Kennzeichnungen besonders wichtig: die **orangefarbene Warntafel** und die **Gefahrzettel**.

## Die orangefarbene Warntafel

Die Tafel ist rechteckig, orange und trägt zwei Zahlenreihen:

- **Oben: Gefahrnummer** (Kemler-Zahl), 2 bis 3 Ziffern
- **Unten: UN-Nummer**, 4 Ziffern – identifiziert den Stoff eindeutig

Beispiel: **33 / 1203** = leicht entzündbarer flüssiger Stoff / **Benzin**.

## Bedeutung der Ziffern der Gefahrnummer

| Ziffer | Bedeutung |
|---|---|
| 2 | Entweichen von Gas durch Druck oder chemische Reaktion |
| 3 | Entzündbarkeit von Flüssigkeiten (Dämpfe) und Gasen |
| 4 | Entzündbarkeit fester Stoffe |
| 5 | Oxidierende (brandfördernde) Wirkung |
| 6 | Giftigkeit oder Ansteckungsgefahr |
| 7 | Radioaktivität |
| 8 | Ätzwirkung |
| 9 | Gefahr einer spontanen heftigen Reaktion |

- Eine **Verdopplung** der Ziffer bedeutet eine **Verstärkung** der Gefahr (z. B. 33 = leicht entzündbar, 66 = sehr giftig).
- Ist eine Gefahr ausreichend durch eine Ziffer beschrieben, wird eine **0** angehängt (z. B. 30 = entzündbarer flüssiger Stoff).
- Ein vorangestelltes **X** bedeutet: **reagiert gefährlich mit Wasser** (z. B. X423).

## Häufige Beispiele

| Gefahrnummer / UN | Stoff |
|---|---|
| 33 / 1203 | Benzin |
| 30 / 1202 | Dieselkraftstoff / Heizöl |
| 23 / 1965 | Flüssiggas (Propan/Butan) |
| 268 / 1005 | Ammoniak, wasserfrei |
| 80 / 1830 | Schwefelsäure |
| 22 / 1977 | Stickstoff, tiefgekühlt flüssig |

## Gefahrzettel

Rautenförmige Gefahrzettel zeigen die **Gefahrklasse** (1 bis 9) mit Symbolen, z. B. Flamme (Klasse 3), Totenkopf (Klasse 6.1), Kleeblatt (Klasse 7), Ätzwirkung (Klasse 8).

## Informationsquellen

- **Beförderungspapiere** im Fahrerhaus (Fahrerplatz, Fahrertür),
- **Schriftliche Weisungen** nach ADR,
- **Sicherheitsdatenblätter** im Betrieb,
- Stoffdatenbanken und **Fachberater** über die Leitstelle,
- **Objektpläne** im System, z. B. für Humane Labs oder Tanklager am Hafen.

*Merke:* Die UN-Nummer verrät den Stoff, die Gefahrnummer verrät die Gefahr.
MD],
            ['Absperrbereiche und Zusammenarbeit mit LSPD', <<<'MD'
# Absperrbereiche und Zusammenarbeit mit LSPD

Eine klare Raumordnung ist im CBRN-Einsatz lebenswichtig. Sie verhindert die Verschleppung von Kontamination und schützt Bevölkerung und Einsatzkräfte.

## Bereiche nach FwDV 500

| Bereich | Beschreibung |
|---|---|
| **Gefahrenbereich** | Unmittelbarer Bereich um die Schadstelle. Zutritt nur mit Schutzausrüstung und nach Registrierung. Erstabstand mindestens **50 m**. |
| **Absperrbereich** | Umfasst den Gefahrenbereich und den Bereich für Dekontamination und Bereitstellung. Erstabstand mindestens **100 m**. |
| **Außerhalb** | Bereitstellungsräume, Einsatzleitung, Patientenablage von SAMS, Presse. |

Die Erstabstände werden je nach Stoff, Windrichtung und Lage **vergrößert**, etwa bei Explosionsgefahr oder Gaswolken (dann oft mehrere hundert Meter). Ausdehnung in **Windrichtung** ist größer als gegen den Wind.

## Kennzeichnung

- Gefahrenbereich: rot-weißes Absperrband, Leitkegel, Schilder.
- Ein **einziger Zugang** und **Ausgang** über die **Dekontaminationsstelle**.
- Einsatzkräfte werden beim Betreten und Verlassen **registriert** (Name, Zeit, Ausrüstung, Auftrag).

## Rolle des LSPD und BCSO

Die Polizei übernimmt die **äußere Absperrung**:
- Straßensperren und Umleitungen, z. B. Sperrung des Elysian Fields Freeway bei einem Unfall im Port of Los Santos,
- Fernhalten von Schaulustigen und Medien,
- **Warnung und ggf. Evakuierung** der Bevölkerung mit Lautsprecherdurchsagen in Abstimmung mit der Feuerwehr,
- Verkehrslenkung für nachrückende Einheiten,
- Ermittlungen bei Verdacht auf **vorsätzliche Freisetzung** (Anschlag) – in diesem Fall ist der Einsatzort zugleich Tatort.

Polizeikräfte betreten den Gefahrenbereich **nicht ohne Schutzausrüstung**. Die Feuerwehr informiert die Polizei über Windrichtung und notwendige Abstände.

## Warnung der Bevölkerung

Bei Gasfreisetzungen gilt für die Bevölkerung: **Fenster und Türen schließen, Lüftung und Klimaanlage ausschalten, Radio einschalten**. Die Leitstelle kann Warnmeldungen über Sirenen und Warn-Apps auslösen.

## Einsatzleitung

Die Einsatzleitung (ELW) wird **außerhalb des Absperrbereichs, gegen den Wind** und möglichst erhöht eingerichtet. Ein Verbindungsbeamter des LSPD und ein Vertreter von SAMS (Organisatorischer Leiter oder Leitender Notarzt) sind dort präsent.

*Merke:* Wer die Absperrgrenzen ignoriert, trägt die Gefahr mit nach draußen.
MD],
            ['Messtechnik', <<<'MD'
# Messtechnik

Messungen klären, **welcher Stoff** in **welcher Konzentration** vorliegt und **wie weit** sich die Gefahr ausbreitet. Sie sind Grundlage für Schutzausrüstung, Absperrgrenzen und Evakuierung.

## Mehrgasmessgerät

Das wichtigste Messgerät der Feuerwehr ist der **Mehrgaswarner**. Er misst typischerweise:
- **Sauerstoff (O₂)** – Alarm bei unter **19 Vol.-%** oder über 23 Vol.-%,
- **Explosionsgrenze (Ex)** – Angabe in **% UEG** (untere Explosionsgrenze); Alarm 1 bei **10 % UEG**, Alarm 2 bei **20 % UEG**,
- **Kohlenmonoxid (CO)** – Alarm meist ab **30 ppm**,
- **Schwefelwasserstoff (H₂S)** – Alarm meist ab **5 ppm**.

Ab **50 % UEG** ist der Bereich sofort zu verlassen, da eine Zündung jederzeit möglich ist. Funkgeräte und Handlampen müssen in explosionsgefährdeten Bereichen **ex-geschützt** sein.

## Prüfröhrchen

**Prüfröhrchen** reagieren mit einem bestimmten Stoff durch **Farbumschlag**. Sie sind stoffspezifisch und dienen der Identifikation, z. B. Ammoniak, Chlor, Salzsäure. Wichtig: Querempfindlichkeiten beachten und die Pumpenhübe genau nach Anleitung zählen.

## Weitere Messtechnik

- **Photoionisationsdetektor (PID):** misst organische Dämpfe in sehr geringer Konzentration.
- **pH-Papier:** schnelle Einordnung, ob ein Stoff sauer oder basisch ist.
- **Strahlenmessgeräte:** **Dosisleistungsmesser** (µSv/h) und **Kontaminationsnachweisgeräte**. Jede Einsatzkraft im radiologischen Einsatz trägt einen **Dosimeter**.
- **Wärmebildkamera:** erkennt Füllstände von Behältern und Temperaturerhöhung.

## Strahlenschutz – die vier A

Gegen ionisierende Strahlung gilt die Regel:
1. **Abstand** halten (Dosisleistung sinkt mit dem Quadrat der Entfernung),
2. **Aufenthaltszeit** begrenzen,
3. **Abschirmung** nutzen,
4. **Aufnahme** radioaktiver Stoffe in den Körper verhindern.

Richtwert für Einsatzkräfte: **15 mSv pro Einsatz**, zur Rettung von Menschen bis **100 mSv**, nur freiwillig und einmal im Jahr **250 mSv**.

## Messtrupp und Dokumentation

Messungen werden von einem **Messtrupp** in geeigneter Schutzausrüstung durchgeführt. Jeder Messwert wird mit **Uhrzeit, Ort und Witterung** dokumentiert und an die Einsatzleitung gemeldet. In der Lagekarte im System entstehen so Messpunkte, aus denen die Ausbreitung abgeleitet wird – besonders wichtig bei Freisetzungen in Humane Labs.

*Merke:* Ein Messgerät, das nicht kalibriert ist, misst Vertrauen – keine Konzentration. Regelmäßige Anzeigeprüfung ist Pflicht.
MD],
            ['Dekontamination und Patientenversorgung', <<<'MD'
# Dekontamination und Patientenversorgung

Die **Dekontamination (Dekon)** entfernt gefährliche Stoffe von Personen und Ausrüstung. Ohne Dekon verlässt niemand den Gefahrenbereich.

## Dekon-Stufen

| Stufe | Inhalt |
|---|---|
| **Not-Dekon** | Sofort verfügbar: Abspülen mit Wasser aus einem Strahlrohr bzw. Notdusche, wenn eine Person akut gefährdet ist |
| **Standard-Dekon** | Bei jedem Einsatz mit CSA: Dekon-Platz mit Duschen, Bürsten, Auffangwanne |
| **Erweiterte Dekon** | Dekon-Einheit mit Zelt, Warmwasser und Dekon-Mitteln, z. B. bei biologischen Stoffen |

## Aufbau des Dekon-Platzes

Der Dekon-Platz liegt an der **Grenze zwischen Gefahrenbereich und Absperrbereich** und wird in **Schwarz** (kontaminiert) und **Weiß** (sauber) getrennt:
1. Ablage kontaminierter Geräte,
2. Grobreinigung (Abspülen, Abbürsten),
3. Feinreinigung mit geeignetem Dekon-Mittel,
4. Entkleiden mit Unterstützung, Ausrüstung in Säcken,
5. Übergabe auf die Weißseite, Erfassung und Versorgung.

Das **Dekon-Personal** trägt selbst Schutzausrüstung (mindestens eine Stufe unter dem Einsatztrupp).

## Verletzte und kontaminierte Personen

Bei Betroffenen gilt: **Erst Dekon, dann Versorgung im Weißbereich** – mit einer wichtigen Ausnahme: **Lebensrettende Sofortmaßnahmen** (z. B. Blutstillung, Atemweg freimachen) erfolgen bereits im Schwarzbereich durch geschützte Kräfte.

Ablauf der Zusammenarbeit mit **SAMS**:
- Die Feuerwehr rettet Personen aus dem Gefahrenbereich und führt die **Verletzten-Dekon** durch, bei Liegendpatienten auf Tragen.
- SAMS richtet eine **Patientenablage** außerhalb des Absperrbereichs ein und übernimmt die Patienten nach der Dekon auf der Weißseite.
- Die Feuerwehr übergibt den **Stoffnamen bzw. die UN-Nummer**, Expositionsdauer und Dekon-Maßnahmen, damit SAMS gezielt behandeln kann (z. B. Antidot bei Blausäure, Augenspülung bei Laugen).
- Rettungswagen werden **nicht** in den Absperrbereich gefahren, um sie nicht zu kontaminieren.

## Geräte-Dekon und Entsorgung

Ausrüstung und Löschwasser können kontaminiert sein. Abwasser wird **aufgefangen** (Auffangwanne, Abdichtung von Kanaleinläufen) und über Fachfirmen entsorgt. Kontaminierte Kleidung wird in gekennzeichneten Säcken gesammelt.

## Nachsorge

Alle Einsatzkräfte im Gefahrenbereich werden namentlich mit Stoff und Expositionszeit im **Einsatzbericht** erfasst. Bei Bedarf erfolgt eine ärztliche Nachuntersuchung bei SAMS. Diese Daten sind wichtig für spätere arbeitsmedizinische Fragen.

*Merke:* Schwarz bleibt schwarz, weiß bleibt weiß – wer die Trennung durchbricht, verschleppt die Gefahr.
MD],
        ],
        'questions' => [
            ['Wofür steht die GAMS-Regel?', ['Gefahr erkennen, Absperren, Menschenrettung, Spezialkräfte alarmieren', 'Gase ableiten, Atemschutz, Messen, Sichern', 'Gefahr melden, Abwarten, Messen, Sperren', 'Gruppe aufstellen, Angriff, Menschenrettung, Schaum'], [0], 'GAMS: Gefahr erkennen, Absperren, Menschenrettung durchführen, Spezialkräfte alarmieren.'],
            ['Wie sollten Einsatzfahrzeuge eine Gefahrgut-Einsatzstelle anfahren?', ['Möglichst von unten aus einer Senke', 'Mit dem Wind im Rücken und möglichst von erhöhter Position', 'Gegen den Wind, damit man den Stoff riecht', 'Direkt bis an das Unfallfahrzeug'], [1], 'Mit dem Wind im Rücken und erhöht wird eine Gefährdung durch Gase vermieden.'],
            ['Was bedeutet die Warntafel 33 / 1203?', ['Ätzender Stoff / Salzsäure', 'Giftiges Gas / Chlor', 'Leicht entzündbarer flüssiger Stoff / Benzin', 'Radioaktiver Stoff'], [2], 'Die Gefahrnummer 33 steht für leicht entzündbare Flüssigkeit, UN 1203 für Benzin.'],
            ['Was bedeutet ein vorangestelltes X in der Gefahrnummer?', ['Der Stoff ist explosiv', 'Der Stoff reagiert gefährlich mit Wasser', 'Der Stoff ist ungefährlich', 'Es handelt sich um einen Militärtransport'], [1], 'X bedeutet, dass der Stoff gefährlich mit Wasser reagiert.'],
            ['Welche Ziffer der Gefahrnummer steht für Ätzwirkung?', ['6', '7', '3', '8'], [3], 'Die Ziffer 8 steht für Ätzwirkung.'],
            ['Welche Information liefert die untere Zahl der orangefarbenen Warntafel?', ['Die UN-Nummer, die den Stoff identifiziert', 'Die Menge des Stoffes in Litern', 'Die Telefonnummer des Herstellers', 'Die Gefahrklasse nach Zollrecht'], [0], 'Die vierstellige UN-Nummer identifiziert den Stoff eindeutig.'],
            ['Wie groß ist der Erstabstand für den Gefahrenbereich nach FwDV 500 mindestens?', ['10 m', '50 m', '500 m', '5 m'], [1], 'Der Gefahrenbereich beträgt zunächst mindestens 50 m, der Absperrbereich mindestens 100 m.'],
            ['Welche Aufgaben übernimmt das LSPD bei einem Gefahrguteinsatz typischerweise?', ['Die äußere Absperrung und Verkehrslenkung', 'Das Messen im Gefahrenbereich ohne Schutzausrüstung', 'Die Warnung und ggf. Evakuierung der Bevölkerung in Abstimmung mit der Feuerwehr', 'Die Dekontamination der Patienten'], [0, 2], 'Die Polizei sperrt äußerlich ab, lenkt den Verkehr und unterstützt bei Warnung und Evakuierung.'],
            ['Ab welchem Wert der unteren Explosionsgrenze ist ein Bereich sofort zu verlassen?', ['5 % UEG', '100 % UEG', '50 % UEG', '1 % UEG'], [2], 'Ab 50 % UEG ist eine Zündung jederzeit möglich, der Bereich wird verlassen.'],
            ['Welche Regel gilt im Strahlenschutz?', ['Die vier A: Abstand, Aufenthaltszeit, Abschirmung, Aufnahme verhindern', 'GAMS', 'AAAA-C-EEEE', 'Retten, Löschen, Bergen, Schützen'], [0], 'Im Strahlenschutz gelten Abstand, Aufenthaltszeit, Abschirmung und Aufnahme verhindern.'],
            ['Wo wird der Dekon-Platz eingerichtet?', ['Im Zentrum der Schadstelle', 'Neben der Einsatzleitung im ELW', 'An der Wache Station 1 Davis', 'An der Grenze zwischen Gefahrenbereich und Absperrbereich'], [3], 'Der Dekon-Platz bildet den einzigen Übergang vom Gefahrenbereich in den Absperrbereich.'],
            ['Wann übernimmt SAMS kontaminierte Patienten in der Regel?', ['Direkt im Gefahrenbereich', 'Nach der Dekontamination auf der Weißseite', 'Erst im Krankenhaus', 'Gar nicht, die Feuerwehr transportiert selbst'], [1], 'SAMS übernimmt die Patienten nach der Dekon auf der Weißseite.'],
            ['Welche Maßnahmen dürfen bereits vor der Dekon im Schwarzbereich durchgeführt werden?', ['Ausführliche Anamnese', 'Transport im Rettungswagen', 'Lebensrettende Sofortmaßnahmen durch geschützte Kräfte', 'Essen und Trinken zur Stärkung'], [2], 'Lebensrettende Sofortmaßnahmen haben Vorrang und erfolgen durch geschützte Kräfte.'],
            ['Welche Informationen gibt die Feuerwehr bei der Patientenübergabe an SAMS weiter?', ['Nur den Namen des Patienten', 'Stoff bzw. UN-Nummer, Expositionsdauer und durchgeführte Dekon', 'Die Kennzeichen der beteiligten Fahrzeuge', 'Keine, das ist Aufgabe der Polizei'], [1], 'Stoffinformationen sind für die gezielte medizinische Behandlung entscheidend.'],
        ],
    ],
    [
        'title' => 'Gruppenführer',
        'description' => 'Führungslehrgang für die taktische Führung einer Gruppe nach FwDV 100 und FwDV 3. Behandelt Führungsvorgang, Befehlsschema, Einsatzleitung und Abschnittsbildung, Lagemeldungen, Einsatzbericht, Einsatznachbereitung und psychosoziale Notfallversorgung.',
        'hours' => 12, 'pass_percent' => 80, 'question_count' => 12, 'time_limit' => 30,
        'qualification' => 'Gruppenführer',
        'modules' => [
            ['Der Führungsvorgang', <<<'MD'
# Der Führungsvorgang

Der **Führungsvorgang** nach **FwDV 100** ist ein **zielgerichteter, immer wiederkehrender Denk- und Handlungsablauf**. Er endet erst mit dem Ende des Einsatzes und beginnt nach jeder Lageänderung von Neuem.

## Die drei Phasen

### 1. Lagefeststellung (Erkundung / Kontrolle)

Der Gruppenführer verschafft sich ein Bild der Lage:
- **Gefahrenlage:** Welche Gefahren bestehen für Menschen, Tiere, Umwelt, Sachwerte und Einsatzkräfte? Hilfsmittel ist die Gefahrenmatrix (**AAAA-C-EEEE**).
- **Schadenslage:** Was ist passiert, wie groß ist der Schaden, wie entwickelt er sich?
- **Eigene Lage:** Welche Kräfte und Mittel sind vor Ort oder auf der Anfahrt?
- **Umgebung:** Zufahrten, Wasserversorgung, Wetter, Tageszeit.

Die Erkundung erfolgt möglichst **von allen Seiten** (Rundumblick, ggf. mit dem Melder), bei Gebäuden auch durch Befragen von Bewohnern und Hausmeistern.

### 2. Planung

Die Planung besteht aus:
- **Beurteilung:** Welche Gefahr muss **zuerst** abgewehrt werden? Wo ist der **Schwerpunkt**? Welche Möglichkeiten gibt es, welche Vor- und Nachteile haben sie?
- **Entschluss:** Der Gruppenführer entscheidet sich für **eine** Lösung. Ein schneller, guter Entschluss ist besser als ein später, perfekter.

Grundregel der Beurteilung: **Menschenrettung vor Brandbekämpfung**, Gefahren mit der größten Auswirkung zuerst.

### 3. Befehlsgebung

Der Entschluss wird durch klare **Einsatzbefehle** umgesetzt. Danach beginnt der Kreislauf erneut mit der **Kontrolle**: Wirken die Maßnahmen? Hat sich die Lage verändert?

## Führungsstil

- **Auftragstaktik:** Der Führer gibt das Ziel vor und lässt den Weg offen – bevorzugt bei erfahrenen Trupps.
- **Befehlstaktik:** Ziel und Weg werden genau vorgegeben – sinnvoll bei hoher Gefahr oder unerfahrenen Kräften.

## Typische Fehler

- **Tunnelblick:** sofortige Konzentration auf das Offensichtliche, z. B. die Flammen im Erdgeschoss, während im Obergeschoss Personen am Fenster stehen.
- **Selbst mitarbeiten:** Der Gruppenführer greift selbst zum Werkzeug und verliert den Überblick.
- **Fehlende Kontrolle:** Befehle werden gegeben, aber deren Wirkung nicht überprüft.

*Merke:* Der Gruppenführer führt – er arbeitet nicht mit. Seine wichtigste Ausrüstung sind Augen, Ohren und das Funkgerät.
MD],
            ['Das Befehlsschema', <<<'MD'
# Das Befehlsschema

Ein Einsatzbefehl muss **kurz, klar, vollständig und eindeutig** sein. Nach FwDV 3 wird er in einer festen Reihenfolge gegeben.

## Die fünf Elemente

| Element | Frage | Beispiel |
|---|---|---|
| **Einheit** | Wer? | „Angriffstrupp …“ |
| **Auftrag** | Was? | „… zur Menschenrettung …“ |
| **Mittel** | Womit? | „… mit erstem C-Rohr und Fluchthaube …“ |
| **Ziel** | Wohin? | „… in die Wohnung 2. OG rechts …“ |
| **Weg** | Wie? | „… über den Treppenraum – vor!“ |

Zusammengesetzt:
> „**Angriffstrupp** zur **Menschenrettung** mit **erstem Rohr** in die **Wohnung 2. OG rechts** über den **Treppenraum** – vor!“

Bei der Befehlsgebung ohne Bereitstellung kann vorangestellt werden:
- **Wasserentnahmestelle** („Wasserentnahmestelle Hydrant vor Haus Nr. 12“),
- **Lage des Verteilers** („Verteiler an die Hausecke“).

## Rückmeldung und Kontrolle

Der Truppführer **wiederholt** den Befehl in Kurzform („Angriffstrupp verstanden, Menschenrettung 2. OG rechts über Treppenraum!“). So erkennt der Gruppenführer sofort Missverständnisse.

## Weitere Befehlsarten

- **Sammelbefehle** an mehrere Einheiten, z. B. „Wassertrupp und Schlauchtrupp: Wasserversorgung vom Hydranten zum Verteiler!“
- **Warnbefehle / Sofortbefehle:** „Gefahr – alle sofort zurück!“ Diese werden durch ein **Notsignal** (z. B. langes Hornsignal oder Funkdurchsage) unterstützt.
- **Befehle an Nachbarkräfte** (SAMS, LSPD) sind keine Befehle, sondern **Absprachen** bzw. Anforderungen über den Einsatzleiter – das LSFD hat keine Weisungsbefugnis gegenüber anderen Behörden, außer bei gesetzlicher Einsatzleitung.

## Befehlsgebung an Ort und Stelle

Befehle werden möglichst **persönlich** und **an einem Ort mit Sicht auf das Objekt** erteilt (meist hinter dem Fahrzeug nach dem Antreten). Über Funk werden Befehle ebenfalls nach dem Schema gegeben, aber besonders knapp.

## Häufige Fehler

- Fehlendes Ziel („Angriffstrupp, geh mal rein!“).
- Mehrere Befehle gleichzeitig an verschiedene Trupps ohne Pause.
- Unklare Ortsangaben ohne Bezugspunkt (links/rechts immer **von der Straßenseite gesehen** angeben).

*Merke:* Ein Befehl, der zweimal erklärt werden muss, war kein guter Befehl.
MD],
            ['Einsatzleitung und Abschnittsbildung', <<<'MD'
# Einsatzleitung und Abschnittsbildung

## Die Einsatzleitung

Die **Einsatzleitung** liegt beim ranghöchsten bzw. von der Leitstelle bestimmten Führer der Feuerwehr vor Ort. Bis zum Eintreffen eines höheren Führers übernimmt der **erste eintreffende Gruppenführer** die Einsatzleitung. Er bleibt verantwortlich, bis er die Leitung **ausdrücklich übergibt**.

Übergabe an einen höheren Führer (z. B. Zugführer oder Wachleiter von Station 7 Rockford Hills):
1. Lage, getroffene Maßnahmen, eingesetzte Kräfte,
2. offene Probleme und Gefahren,
3. Bestätigung: „Ich übernehme die Einsatzleitung.“ – Meldung an die Leitstelle.

## Führungsstufen

| Stufe | Einheit | Führungsmittel |
|---|---|---|
| A | Trupp, Staffel, Gruppe | ohne Führungseinheit |
| B | Zug | Führungstrupp (ELW 1) |
| C | Verband | Führungsgruppe |
| D | Großverband | Führungsstab |

Ein Gruppenführer kann **maximal 2 bis 5 Einheiten** sinnvoll führen (**Führungsspanne**). Werden mehr Kräfte benötigt, muss die Führung erweitert werden.

## Abschnittsbildung

Bei größeren Lagen wird die Einsatzstelle in **Einsatzabschnitte (EA)** unterteilt, die je einem Abschnittsleiter unterstellt sind. Abschnitte können nach folgenden Kriterien gebildet werden:
- **Räumlich:** z. B. EA Nord/Süd, EA Gebäude A/B, EA Dach.
- **Funktional:** z. B. EA Wasserversorgung, EA Menschenrettung, EA Bereitstellungsraum.
- **Organisatorisch:** z. B. EA Rettungsdienst (geführt durch SAMS), EA Polizei (geführt durch LSPD).

Beispiel: Großbrand einer Lagerhalle im **Port of Los Santos**
- EA 1: Brandbekämpfung Nordseite (Station 1 Davis)
- EA 2: Brandbekämpfung Südseite und Riegelstellung
- EA 3: Wasserversorgung aus dem Hafenbecken
- EA 4: Rettungsdienst (SAMS, Leitender Notarzt)
- Absperrung und Verkehrslenkung durch LSPD

## Bereitstellungsraum

Nachrückende Einheiten fahren zuerst in einen **Bereitstellungsraum**, der vom Einsatzleiter festgelegt und an die Leitstelle gemeldet wird. So verhindert man verstopfte Zufahrten. Die Einheiten melden sich beim Leiter des Bereitstellungsraums an.

## Kennzeichnung

Einsatzleiter und Abschnittsleiter tragen **Kennzeichnungswesten** („Einsatzleiter“, „Abschnittsleiter“). Der ELW wird durch eine **grüne Rundumleuchte** bzw. Kennleuchte als Führungsstelle markiert.

*Merke:* Wer alles selbst führen will, führt am Ende gar nichts. Abschnitte schaffen Übersicht.
MD],
            ['Lagemeldung und Einsatzbericht', <<<'MD'
# Lagemeldung und Einsatzbericht

## Lagemeldung an die Leitstelle

Kurz nach Eintreffen (Status 4) gibt der Gruppenführer eine **erste Lagemeldung** an die Leitstelle Los Santos. Sie ist entscheidend für Nachalarmierungen und die Information anderer Behörden.

Gliederung nach dem Schema **„Wo – Was – Wer – Welche Maßnahmen – Welche Nachforderung“**:

> „Leitstelle von HLF Station 1 – Lagemeldung: Grove Street 14, Davis. Brand im 2. OG, Mehrfamilienhaus, starke Rauchentwicklung, zwei Personen am Fenster. Wir gehen mit einem Trupp unter Atemschutz zur Menschenrettung über den Treppenraum vor, DLK wird in Stellung gebracht. Nachforderung: weiteres HLF und zwei Rettungswagen SAMS, kommen.“

Folgemeldungen erfolgen bei **jeder wesentlichen Lageänderung**, spätestens alle **15 bis 20 Minuten**. Typische Meldungen:
- „**Feuer unter Kontrolle**“ – keine weitere Ausbreitung mehr,
- „**Feuer aus**“ – keine offenen Flammen mehr,
- „**Einsatzstelle an LSPD übergeben**“,
- „**Einsatz beendet**, Einheiten rücken ein“.

## Der Einsatzbericht im System

Nach jedem Einsatz erstellt der Gruppenführer bzw. Einsatzleiter einen **Einsatzbericht** im Dokumentensystem. Er ist ein amtliches Dokument und Grundlage für Versicherungen, Ermittlungen des LSPD, Kostenbescheide und Statistiken.

Inhalt:
1. **Stammdaten:** Einsatznummer, Datum, Alarmzeit, Eintreffzeit, Ende, Einsatzort.
2. **Alarmierungsstichwort** und tatsächliche Lage.
3. **Eingesetzte Kräfte:** Fahrzeuge, Personal (mit Atemschutzeinsätzen), andere Behörden (SAMS, LSPD, BCSO).
4. **Maßnahmen** in zeitlicher Reihenfolge.
5. **Personenschäden:** gerettete, verletzte, verstorbene Personen; Übergabe an SAMS.
6. **Sachschäden** und vermutete Brandursache (nur Vermutung – die Ermittlung führt das LSPD).
7. **Besondere Vorkommnisse:** Unfälle von Einsatzkräften, beschädigtes Gerät, Gefahrstoffexposition.
8. **Verbrauchsmaterial** und Nachbereitungsbedarf.

## Grundsätze der Dokumentation

- **Sachlich und neutral** – keine Wertungen über Personen.
- **Zeitnah** – spätestens bis Schichtende.
- **Vollständig** – „Was nicht dokumentiert ist, hat nicht stattgefunden.“
- Datenschutz beachten: Personendaten nur, soweit notwendig.

*Merke:* Die erste Lagemeldung entscheidet, wie viel Hilfe kommt. Lieber früh zu viel nachfordern als zu spät zu wenig.
MD],
            ['Einsatznachbereitung und PSNV', <<<'MD'
# Einsatznachbereitung und PSNV

## Technische Nachbereitung

Nach dem Einsatz sorgt der Gruppenführer dafür, dass seine Einheit **schnellstmöglich wieder einsatzbereit** ist:
- Fahrzeug betanken, Tank füllen, Material ergänzen,
- Atemschutzgeräte und Schläuche tauschen,
- kontaminierte Ausrüstung nach **Schwarz-Weiß-Trennung** behandeln,
- Mängel und Schäden melden,
- Status 2 erst nach vollständiger Wiederherstellung.

## Einsatznachbesprechung

Eine kurze **Nachbesprechung** (Debriefing) direkt nach dem Einsatz oder auf der Wache hilft, Erfahrungen zu sichern:
1. Was war die Lage?
2. Was lief gut?
3. Was können wir besser machen?
4. Gab es Sicherheitsprobleme oder Beinahe-Unfälle?

Ziel ist **Lernen, nicht Schuldzuweisung**. Bei größeren Einsätzen findet eine strukturierte Nachbesprechung mit allen beteiligten Organisationen statt – auch mit SAMS und LSPD.

## Psychosoziale Notfallversorgung (PSNV)

Einsätze können Einsatzkräfte stark belasten, z. B. der Tod von Kindern, Einsätze mit vielen Verletzten, verunglückte Kollegen oder besonders schwere Verletzungsbilder bei Verkehrsunfällen am Great Ocean Highway.

### Belastungsreaktionen

Typische **normale Reaktionen auf ein unnormales Ereignis** sind:
- Schlafstörungen, Albträume,
- wiederkehrende Bilder (Flashbacks),
- Gereiztheit, Rückzug, Konzentrationsprobleme,
- körperliche Symptome (Herzrasen, Magenbeschwerden).

Halten Symptome länger als **vier Wochen** an, besteht der Verdacht auf eine **posttraumatische Belastungsstörung (PTBS)** – dann ist professionelle Hilfe nötig.

### Aufgaben des Gruppenführers

- Belastende Einsätze **erkennen** und ansprechen.
- Über die Leitstelle frühzeitig **PSNV-E** (Einsatzkräftenachsorge) anfordern – speziell geschulte Peers oder Fachkräfte.
- Für Betroffene vor Ort (Angehörige, Zeugen) die **PSNV-B** bzw. Krisenintervention anfordern, in Zusammenarbeit mit SAMS.
- Niemanden direkt nach einem schweren Einsatz alleine nach Hause schicken.
- Vertraulichkeit wahren.

### Angebote

- **Kurzbesprechung (Defusing)** noch am selben Tag in kleiner Gruppe,
- **Nachbesprechung (Debriefing)** einige Tage später,
- Einzelgespräche mit Peers oder Seelsorgern,
- Vermittlung an Psychotherapeuten.

*Merke:* Hilfe anzunehmen ist ein Zeichen von Professionalität, nicht von Schwäche. Der Gruppenführer lebt das vor.
MD],
        ],
        'questions' => [
            ['Aus welchen drei Phasen besteht der Führungsvorgang?', ['Alarmierung, Anfahrt, Einsatz', 'Lagefeststellung, Planung, Befehlsgebung', 'Erkunden, Löschen, Bergen', 'Melden, Warten, Handeln'], [1], 'Der Führungsvorgang gliedert sich in Lagefeststellung, Planung und Befehlsgebung.'],
            ['Woraus besteht die Planung im Führungsvorgang?', ['Aus Beurteilung und Entschluss', 'Aus Befehl und Rückmeldung', 'Aus Alarmierung und Anfahrt', 'Aus Lagemeldung und Einsatzbericht'], [0], 'Die Planung umfasst Beurteilung der Lage und den Entschluss.'],
            ['Welche Reihenfolge hat das Befehlsschema?', ['Auftrag, Einheit, Weg, Ziel, Mittel', 'Ziel, Weg, Einheit, Mittel, Auftrag', 'Einheit, Auftrag, Mittel, Ziel, Weg', 'Mittel, Einheit, Auftrag, Weg, Ziel'], [2], 'Das Schema lautet Einheit – Auftrag – Mittel – Ziel – Weg.'],
            ['Warum wiederholt der Truppführer den Einsatzbefehl?', ['Damit Missverständnisse sofort erkannt werden', 'Aus Höflichkeit', 'Weil der Gruppenführer ihn vergessen hat', 'Damit die Leitstelle mithört'], [0], 'Die Wiederholung dient der Kontrolle, ob der Befehl richtig verstanden wurde.'],
            ['Was kennzeichnet die Auftragstaktik?', ['Ziel und Weg werden genau vorgegeben', 'Der Führer arbeitet selbst mit', 'Es werden keine Befehle gegeben', 'Das Ziel wird vorgegeben, der Weg bleibt der Einheit überlassen'], [3], 'Bei der Auftragstaktik wird das Ziel vorgegeben, die Umsetzung bleibt offen.'],
            ['Wer übernimmt die Einsatzleitung bis zum Eintreffen eines höheren Führers?', ['Der erste eintreffende Gruppenführer', 'Der dienstälteste Maschinist', 'Der Notarzt von SAMS', 'Die Leitstelle aus der Ferne'], [0], 'Der erste eintreffende Gruppenführer ist Einsatzleiter, bis er die Leitung ausdrücklich übergibt.'],
            ['Wie viele Einheiten kann eine Führungskraft im Rahmen der Führungsspanne sinnvoll führen?', ['1', '2 bis 5', '10 bis 15', 'Beliebig viele'], [1], 'Die Führungsspanne liegt bei etwa 2 bis 5 unterstellten Einheiten.'],
            ['Nach welchen Kriterien können Einsatzabschnitte gebildet werden?', ['Nach Alphabet der Truppführer', 'Nach räumlichen und funktionalen Gesichtspunkten', 'Nach Dienstalter', 'Nach Farbe der Fahrzeuge'], [1], 'Abschnitte werden räumlich, funktional oder organisatorisch gebildet.'],
            ['Wozu dient ein Bereitstellungsraum?', ['Zur Lagerung von Löschmittel auf der Wache', 'Als Ruheraum für Atemschutzgeräteträger', 'Zum Sammeln nachrückender Einheiten, damit Zufahrten frei bleiben', 'Als Pressebereich'], [2], 'Nachrückende Kräfte sammeln sich im Bereitstellungsraum und werden von dort eingesetzt.'],
            ['Welche Inhalte gehören in die erste Lagemeldung an die Leitstelle?', ['Einsatzort und vorgefundene Lage', 'Die private Telefonnummer des Gruppenführers', 'Eingeleitete Maßnahmen und Nachforderungen', 'Die vermutlich Schuldigen'], [0, 2], 'Die Lagemeldung umfasst Ort, Lage, Maßnahmen und Nachforderungen.'],
            ['Was bedeutet die Meldung „Feuer unter Kontrolle“?', ['Das Feuer ist vollständig gelöscht', 'Das Feuer breitet sich nicht weiter aus', 'Die Polizei hat die Einsatzstelle übernommen', 'Die Brandursache ist geklärt'], [1], '„Feuer unter Kontrolle“ bedeutet keine weitere Ausbreitung, „Feuer aus“ folgt später.'],
            ['Wer ermittelt die Brandursache?', ['Der Gruppenführer verbindlich im Einsatzbericht', 'SAMS', 'Die Versicherung vor Ort', 'Das LSPD; die Feuerwehr gibt nur eine Vermutung an'], [3], 'Die Feuerwehr dokumentiert eine vermutete Ursache, die Ermittlung führt das LSPD.'],
            ['Wann besteht der Verdacht auf eine posttraumatische Belastungsstörung?', ['Wenn Belastungssymptome länger als etwa vier Wochen anhalten', 'Sofort nach jedem Einsatz', 'Nur wenn man selbst verletzt wurde', 'Niemals bei Einsatzkräften'], [0], 'Akute Belastungsreaktionen sind normal; über vier Wochen anhaltende Symptome deuten auf PTBS hin.'],
            ['Was ist bei einem besonders belastenden Einsatz Aufgabe des Gruppenführers?', ['Das Thema totschweigen, um die Moral nicht zu gefährden', 'Frühzeitig PSNV-E über die Leitstelle anfordern', 'Die betroffenen Kräfte sofort allein nach Hause schicken', 'Einen Disziplinarbericht schreiben'], [1], 'Der Gruppenführer erkennt Belastungen und fordert Einsatzkräftenachsorge an.'],
            ['Was ist ein typischer Führungsfehler?', ['Regelmäßige Kontrolle der Maßnahmen', 'Erkundung von allen Seiten', 'Klare Befehle nach Schema', 'Der Gruppenführer arbeitet selbst mit und verliert den Überblick'], [3], 'Ein Führer, der selbst mitarbeitet, verliert den Überblick über die Lage.'],
        ],
    ],
    [
        'title' => 'Vorbeugender Brandschutz und Brandschau',
        'description' => 'Lehrgang für Brandschutzprüfer des LSFD. Behandelt Brandschutzkonzepte, Flucht- und Rettungswege, Ablauf der Brandschau, Mängelklassen und Fristen, Brandschutzbescheinigung, Objektpläne im System und Rauchwarnmelder.',
        'hours' => 10, 'pass_percent' => 80, 'question_count' => 10, 'time_limit' => 25,
        'qualification' => 'Brandschutzprüfer',
        'modules' => [
            ['Grundlagen und Brandschutzkonzept', <<<'MD'
# Grundlagen und Brandschutzkonzept

Der **vorbeugende Brandschutz** soll verhindern, dass Brände entstehen, und dafür sorgen, dass sich ein ausgebrochener Brand nicht ausbreitet und Menschen sicher fliehen oder gerettet werden können. Er ist das Gegenstück zum **abwehrenden Brandschutz** (Brandbekämpfung).

## Schutzziele

Nach dem Bauordnungsrecht gelten vier Schutzziele. Bauliche Anlagen sind so zu errichten, dass
1. der **Entstehung** eines Brandes vorgebeugt wird,
2. der **Ausbreitung** von Feuer und Rauch vorgebeugt wird,
3. die **Rettung** von Menschen und Tieren möglich ist,
4. **wirksame Löscharbeiten** möglich sind.

## Säulen des Brandschutzes

- **Baulicher Brandschutz:** Feuerwiderstand von Wänden und Decken (z. B. F30 feuerhemmend, F90 feuerbeständig), **Brandabschnitte**, Brandwände, Rauchabschnitte, Treppenräume.
- **Anlagentechnischer Brandschutz:** Brandmeldeanlagen (BMA), Sprinkleranlagen, Rauch- und Wärmeabzugsanlagen (RWA), Sicherheitsbeleuchtung, Steigleitungen.
- **Organisatorischer Brandschutz:** Brandschutzordnung, Brandschutzbeauftragte, Räumungsübungen, Unterweisung des Personals, Feuerwehrpläne.
- **Abwehrender Brandschutz:** Zufahrten, Aufstellflächen, Löschwasserversorgung, Feuerwehrschlüsseldepot.

## Das Brandschutzkonzept

Für **Sonderbauten** (z. B. Hochhäuser in Downtown, Versammlungsstätten wie das Maze Bank Arena, Krankenhäuser, Hotels, der Flughafen **LSIA** oder Industrieanlagen im **Port of Los Santos**) ist ein **Brandschutzkonzept** erforderlich. Es wird von einem Fachplaner erstellt und beschreibt:

- Nutzung, Personenzahl und Gefährdungen,
- Brandabschnitte und Rauchabschnitte,
- Flucht- und Rettungswege,
- Anlagentechnik und deren Steuerung (Brandfallmatrix),
- Zufahrten und Flächen für die Feuerwehr,
- Löschwasserbedarf und -versorgung,
- organisatorische Maßnahmen.

Das LSFD nimmt im Baugenehmigungsverfahren **Stellung** zum Brandschutzkonzept, insbesondere zu den Belangen des abwehrenden Brandschutzes. Abweichungen vom Baurecht müssen durch **Kompensationsmaßnahmen** ausgeglichen werden.

## Rolle des Brandschutzprüfers

Der Brandschutzprüfer des LSFD:
- führt **Brandschauen** durch,
- berät Betreiber und Bauherren,
- erstellt und prüft **Objektpläne** für den Einsatzdienst,
- dokumentiert alles im System.

*Merke:* Jeder Brand, der durch vorbeugenden Brandschutz verhindert wird, ist der sicherste Einsatz – der, der nie stattfindet.
MD],
            ['Flucht- und Rettungswege', <<<'MD'
# Flucht- und Rettungswege

Flucht- und Rettungswege sind das Rückgrat der Personensicherheit. Im Brandfall tötet in den meisten Fällen nicht das Feuer, sondern der **Rauch** – deshalb müssen Wege kurz, rauchfrei und eindeutig sein.

## Grundsatz: zwei Rettungswege

Jede Nutzungseinheit mit Aufenthaltsräumen (Wohnung, Büro, Praxis) muss in jedem Geschoss über **zwei voneinander unabhängige Rettungswege** verfügen:

1. **Erster Rettungsweg:** immer **baulich**, in der Regel über einen **notwendigen Treppenraum**.
2. **Zweiter Rettungsweg:** entweder ein weiterer baulicher Weg (zweite Treppe) **oder** eine **mit Rettungsgeräten der Feuerwehr erreichbare Stelle** (Fenster, Balkon).

Bei Gebäuden, deren Brüstungshöhe über der Reichweite der tragbaren Leitern liegt (über **8 m**), muss die Feuerwehr über ein **Hubrettungsfahrzeug (DLK)** verfügen – sonst ist ein zweiter baulicher Rettungsweg erforderlich. Für die DLK müssen **Zufahrten und Aufstellflächen** dauerhaft freigehalten werden.

Ein zweiter Rettungsweg ist entbehrlich bei einem **Sicherheitstreppenraum**, in den Feuer und Rauch nicht eindringen können (typisch für Hochhäuser).

## Anforderungen

- **Breite:** mindestens **1,0 m** für Treppen in Gebäuden bis zu bestimmter Personenzahl; in Versammlungsstätten **1,20 m je 200 Personen**.
- **Länge:** Von jeder Stelle eines Aufenthaltsraums muss ein Ausgang ins Freie oder ein notwendiger Treppenraum in höchstens **35 m** Lauflänge erreichbar sein.
- **Türen** in Rettungswegen schlagen in **Fluchtrichtung** auf, wenn viele Personen betroffen sind, und müssen jederzeit **ohne Schlüssel** von innen geöffnet werden können (Panikbeschlag).
- **Kennzeichnung:** grüne Rettungszeichen mit Richtungspfeil, im Dunkeln erkennbar (lang nachleuchtend oder beleuchtet).
- **Sicherheitsbeleuchtung** in Sonderbauten.

## Typische Mängel

- **Verstellte Wege:** Kartons, Möbel, abgestellte Fahrräder im Treppenhaus in Davis-Mehrfamilienhäusern.
- **Verkeilte Brandschutz- oder Rauchschutztüren**, die offen gehalten werden, ohne Feststellanlage.
- **Abgeschlossene Notausgänge**, z. B. in Nachtclubs in Vinewood, aus Angst vor Diebstahl.
- Fehlende oder falsche **Beschilderung**.
- **Brandlasten** im Treppenraum (Kinderwagen, Dekoration, Sofas).

## Flucht- und Rettungsplan

In Sonderbauten und Arbeitsstätten müssen **Flucht- und Rettungspläne** aushängen. Sie zeigen den Standort („Sie befinden sich hier“), Fluchtwege, Sammelstelle, Feuerlöscher und Verhaltensregeln im Brandfall.

*Merke:* Ein Rettungsweg, der im Ernstfall verschlossen ist, ist kein Rettungsweg – sondern eine Falle.
MD],
            ['Ablauf der Brandschau', <<<'MD'
# Ablauf der Brandschau

Die **Brandschau** (Feuerbeschau) ist eine behördliche Überprüfung von Gebäuden mit erhöhter Brandgefahr oder vielen Personen. Sie dient der Feststellung von Mängeln, die eine Gefahr für Leben, Gesundheit oder Sachwerte darstellen.

## Brandschaupflichtige Objekte

Brandschaupflichtig sind insbesondere:
- Versammlungsstätten (Arenen, Kinos, Clubs, Kirchen),
- Krankenhäuser, Pflegeheime – z. B. Einrichtungen von SAMS,
- Hotels und Beherbergungsstätten,
- Schulen und Kindergärten,
- Verkaufsstätten über einer bestimmten Größe,
- Hochhäuser in Downtown,
- Industrie- und Lagerbetriebe mit Gefahrstoffen (Port of Los Santos, Humane Labs),
- Flughafen LSIA und Tankstellen.

Die **Regelfrist** beträgt je nach Gefährdung **3 bis 5 Jahre**; bei besonders gefährdeten Objekten kürzer.

## Ablauf

### 1. Vorbereitung
- Objektakte im System einsehen: letzte Brandschau, offene Mängel, Baugenehmigung, Brandschutzkonzept, Objektplan.
- Termin mit dem Betreiber vereinbaren (Ankündigung in der Regel **zwei Wochen** vorher). Bei Gefahr im Verzug kann die Brandschau **unangekündigt** erfolgen.

### 2. Eröffnungsgespräch
- Vorstellung, Zweck der Brandschau, Ansprechpartner des Betreibers (Brandschutzbeauftragter, Hausmeister).
- Fragen nach Nutzungsänderungen und Umbauten.

### 3. Begehung
Systematisch von **oben nach unten** oder entlang der Rettungswege. Geprüft werden:
- Rettungswege und Notausgänge,
- Brandschutztüren und Feststellanlagen,
- Brandmelde- und Löschanlagen (Prüfnachweise durch Sachverständige),
- Feuerlöscher (Prüfung alle **2 Jahre**),
- Lagerung brennbarer Stoffe,
- Elektroinstallation (offensichtliche Mängel),
- Zufahrten und Aufstellflächen der Feuerwehr, Hydranten,
- Feuerwehrschlüsseldepot und Feuerwehrlaufkarten bei BMA.

### 4. Abschlussgespräch
Festgestellte Mängel werden **vor Ort besprochen** und Fristen mitgeteilt. Der Betreiber erhält Gelegenheit zur Stellungnahme.

### 5. Brandschaubericht
Der Bericht wird im System erstellt und dem Betreiber zugestellt. Er enthält Objektdaten, Teilnehmer, Mängel mit Mängelklasse und Frist sowie die Unterschrift des Prüfers.

## Befugnisse

Der Brandschutzprüfer darf das Objekt **betreten** und Unterlagen einsehen. Bei **Gefahr im Verzug** kann die zuständige Behörde Sofortmaßnahmen anordnen, etwa die **Räumung** oder **Nutzungsuntersagung** – in der Praxis mit Unterstützung des LSPD.

*Merke:* Die Brandschau ist Beratung und Kontrolle zugleich. Ein freundlicher Ton und klare Fakten führen meist schneller zum Ziel als Drohungen.
MD],
            ['Mängelklassen, Fristen und Brandschutzbescheinigung', <<<'MD'
# Mängelklassen, Fristen und Brandschutzbescheinigung

Jeder bei der Brandschau festgestellte Mangel wird einer **Mängelklasse** zugeordnet. Daraus ergibt sich die **Frist** zur Beseitigung.

## Mängelklassen

| Klasse | Bezeichnung | Beispiel | Frist |
|---|---|---|---|
| **0** | Hinweis / Empfehlung | Zusätzliche Feuerlöscher wären sinnvoll | keine |
| **1** | Geringer Mangel | Fehlendes Rettungszeichen, Prüfplakette am Feuerlöscher abgelaufen | **bis 3 Monate** |
| **2** | Erheblicher Mangel | Defekte Brandschutztür, verstellte Zufahrt, fehlende Prüfnachweise der BMA | **bis 4 Wochen** |
| **3** | Gefährlicher Mangel | Verschlossener Notausgang bei laufendem Betrieb, ausgefallene BMA in einem Hotel | **sofort** – ggf. Nutzungsuntersagung |

## Nachschau

- Mängel der Klasse 1 werden in der Regel durch **schriftliche Erledigungsmeldung** des Betreibers nachgewiesen (z. B. Foto, Rechnung).
- Mängel der Klassen 2 und 3 erfordern eine **Nachschau** vor Ort durch den Brandschutzprüfer.
- Werden Fristen nicht eingehalten, wird ein **Verwaltungsverfahren** eingeleitet (Anordnung, Zwangsgeld, im Extremfall Schließung).

Alle Fristen werden im System hinterlegt; das System erinnert den Prüfer automatisch an anstehende Nachschauen.

## Sofortmaßnahmen bei Klasse 3

Bei einem gefährlichen Mangel darf der Betrieb **nicht** weiterlaufen, bis die Gefahr beseitigt ist. Mögliche Maßnahmen:
- Sofortiges Öffnen des Notausgangs,
- Einschränkung der Besucherzahl,
- **Brandsicherheitswache** des LSFD auf Kosten des Betreibers als Kompensation,
- Räumung und Nutzungsuntersagung in Abstimmung mit der Behördenleitung und dem LSPD.

## Brandschutzbescheinigung

Nach einer **mängelfreien Brandschau** – oder nach Beseitigung aller Mängel der Klassen 1 bis 3 – kann der Betreiber eine **Brandschutzbescheinigung** im System erhalten.

Inhalt der Bescheinigung:
- Objekt, Anschrift, Nutzung,
- Datum der Brandschau und ggf. der Nachschau,
- Bestätigung, dass zum Prüfzeitpunkt **keine offenen Mängel** bestanden,
- **Gültigkeit** bis zur nächsten Regelbrandschau,
- Name und Unterschrift des Brandschutzprüfers, Siegel des LSFD.

Die Bescheinigung ist keine Garantie für zukünftige Sicherheit: Sie bestätigt nur den Zustand zum Zeitpunkt der Prüfung. Bei **Nutzungsänderungen** oder Umbauten verliert sie ihre Gültigkeit, und eine erneute Prüfung ist erforderlich.

## Dokumentation

Jeder Mangel wird mit **Ort, Beschreibung, Rechtsgrundlage, Mängelklasse, Frist** und möglichst einem **Foto** dokumentiert. Formulierungen sind sachlich und konkret: nicht „Brandschutz schlecht“, sondern „Rauchschutztür Treppenraum 2. OG mit Holzkeil offen gehalten“.

*Merke:* Die Mängelklasse richtet sich nach der Gefahr für Menschen – nicht nach den Kosten der Beseitigung.
MD],
            ['Objektpläne und Rauchwarnmelder', <<<'MD'
# Objektpläne und Rauchwarnmelder

## Objektpläne im System

**Objektpläne** (Feuerwehrpläne nach DIN 14095) geben dem Einsatzleiter schnell alle wichtigen Informationen über ein Gebäude. Im LSFD werden sie digital im System gepflegt und stehen im ELW sowie auf den Fahrzeugtablets zur Verfügung.

### Inhalt eines Objektplans

- **Übersichtsplan:** Lage des Objekts, Zufahrten, Aufstellflächen, **Hydranten**, Löschwasserentnahmestellen, Sammelplatz.
- **Geschosspläne:** Treppenräume, Brandabschnitte, Rettungswege, Räume mit besonderen Gefahren.
- **Sonderpläne:** Gefahrstofflager, Gasabsperrungen, Elektro-Hauptschalter, Sprinklerzentrale, RWA-Bedienstellen.
- **Textteil:** Ansprechpartner mit 24-h-Erreichbarkeit, Personenzahlen, Besonderheiten (z. B. „Biologisches Labor S3 – Zutritt nur mit Betreiber“ bei **Humane Labs**).

### Pflege

- Der Betreiber ist verpflichtet, Änderungen zu melden.
- Der Brandschutzprüfer überprüft Objektpläne bei jeder Brandschau und gleicht sie mit der Realität ab.
- Wichtige Objekte (LSIA, Port of Los Santos, Krankenhäuser von SAMS, Hochhäuser) werden mindestens **alle zwei Jahre** aktualisiert.
- Neue Pläne werden auf den betroffenen Wachen (z. B. Station 1 Davis, Station 7 Rockford Hills) in einer **Objektbegehung** mit den Einsatzkräften vorgestellt.

## Brandmeldeanlagen und Feuerwehrlaufkarten

Bei Objekten mit **BMA** führt die Feuerwehr eine **Feuerwehrlaufkarte** für jede Meldergruppe. Sie zeigt den kürzesten Weg von der Brandmelderzentrale zum ausgelösten Melder. Zugang verschafft das **Feuerwehrschlüsseldepot (FSD)**, das bei Auslösung entriegelt wird.

## Rauchwarnmelder

**Rauchwarnmelder** sind die wirksamste Maßnahme zur Rettung von Menschenleben in Wohnungen. Die meisten Brandtoten sterben **nachts im Schlaf** durch Rauchgasvergiftung – ein Rauchwarnmelder weckt rechtzeitig.

### Pflicht und Montage

- Pflicht in **Schlafräumen, Kinderzimmern** und **Fluren**, die als Rettungsweg dienen.
- Montage **an der Decke**, möglichst in der **Raummitte**, mindestens **50 cm** Abstand zu Wänden und Möbeln.
- **Nicht** in Küchen und Bädern (Fehlalarme durch Dampf) – dort ggf. Wärmemelder.
- Geräte nach **DIN EN 14604**, empfohlen mit fest eingebauter **10-Jahres-Batterie** (Kennzeichen „Q“).

### Wartung

- Sichtprüfung und **Funktionstest** mindestens **einmal jährlich** (Prüfknopf).
- Melder nach spätestens **10 Jahren** austauschen.

### Einsatz bei piependem Rauchwarnmelder

Ein ausgelöster Rauchwarnmelder in einer verschlossenen Wohnung ist **immer** ernst zu nehmen: Die Feuerwehr erkundet von außen und öffnet die Tür gemeinsam mit dem LSPD (siehe Türöffnung), bei Rauchentwicklung sofort unter Atemschutz.

*Merke:* Ein Rauchwarnmelder kostet weniger als eine Tankfüllung – und rettet Leben. Aufklärung der Bevölkerung ist Teil unseres Auftrags.
MD],
        ],
        'questions' => [
            ['Welches gehört zu den vier Schutzzielen des Bauordnungsrechts?', ['Die Rettung von Menschen und Tieren muss möglich sein', 'Gebäude müssen möglichst preiswert sein', 'Jedes Gebäude benötigt eine Sprinkleranlage', 'Die Feuerwehr muss in 3 Minuten vor Ort sein'], [0], 'Zu den Schutzzielen gehören Brandentstehung verhindern, Ausbreitung verhindern, Rettung ermöglichen und Löscharbeiten ermöglichen.'],
            ['Welche Bauteilklassifizierung steht für „feuerbeständig“?', ['F30', 'F60', 'F90', 'F10'], [2], 'F90 steht für feuerbeständig, F30 für feuerhemmend.'],
            ['Für welche Gebäude ist in der Regel ein Brandschutzkonzept erforderlich?', ['Für Gartenhäuser', 'Für Sonderbauten wie Versammlungsstätten, Krankenhäuser oder Hochhäuser', 'Für jede Garage', 'Nur für Feuerwachen'], [1], 'Sonderbauten benötigen ein individuelles Brandschutzkonzept.'],
            ['Wie viele unabhängige Rettungswege muss jede Nutzungseinheit mit Aufenthaltsräumen grundsätzlich haben?', ['Einen', 'Drei', 'Zwei', 'Vier'], [2], 'Jede Nutzungseinheit benötigt zwei voneinander unabhängige Rettungswege.'],
            ['Was kann als zweiter Rettungsweg dienen?', ['Ein Aufzug', 'Eine mit Rettungsgeräten der Feuerwehr erreichbare Stelle wie ein Fenster', 'Ein Lichtschacht', 'Ein Lüftungskanal'], [1], 'Der zweite Rettungsweg kann über tragbare Leitern oder die DLK der Feuerwehr erfolgen.'],
            ['Welche Mängel sind bei Flucht- und Rettungswegen typisch?', ['Mit Keilen offen gehaltene Rauchschutztüren', 'Grüne beleuchtete Rettungszeichen', 'Abgeschlossene Notausgänge während des Betriebs', 'Türen mit Panikbeschlag'], [0, 2], 'Verkeilte Rauchschutztüren und verschlossene Notausgänge sind typische, gefährliche Mängel.'],
            ['In welche Richtung schlagen Türen in Rettungswegen bei vielen Personen auf?', ['In Fluchtrichtung', 'Gegen die Fluchtrichtung', 'Das ist beliebig', 'Sie müssen Schiebetüren sein'], [0], 'Türen in Rettungswegen öffnen in Fluchtrichtung, damit niemand im Gedränge eingeklemmt wird.'],
            ['Wie lang ist die Regelfrist für Brandschauen je nach Gefährdung?', ['Jährlich bei jedem Gebäude', '10 bis 15 Jahre', '3 bis 5 Jahre', 'Es gibt keine Fristen'], [2], 'Die Regelfrist liegt je nach Objekt bei 3 bis 5 Jahren.'],
            ['Welcher Mängelklasse entspricht ein verschlossener Notausgang bei laufendem Betrieb einer Diskothek?', ['Klasse 0', 'Klasse 1', 'Klasse 2', 'Klasse 3'], [3], 'Ein verschlossener Notausgang ist ein gefährlicher Mangel der Klasse 3 mit sofortiger Beseitigungspflicht.'],
            ['Welche Frist gilt in der Regel für einen erheblichen Mangel der Klasse 2?', ['Sofort', 'Bis zu 4 Wochen', 'Bis zu 3 Monate', 'Bis zur nächsten Regelbrandschau'], [1], 'Mängel der Klasse 2 sind innerhalb von bis zu vier Wochen zu beseitigen.'],
            ['Was bestätigt eine Brandschutzbescheinigung?', ['Dass das Gebäude für immer sicher ist', 'Dass zum Zeitpunkt der Prüfung keine offenen Mängel bestanden', 'Dass der Betreiber keine Brandschau mehr benötigt', 'Dass das Gebäude versichert ist'], [1], 'Die Bescheinigung bestätigt nur den mängelfreien Zustand zum Prüfzeitpunkt.'],
            ['Welche Informationen enthält der Übersichtsplan eines Objektplans?', ['Lage, Zufahrten, Aufstellflächen und Hydranten', 'Die Gehälter der Mitarbeiter', 'Nur die Telefonnummer des Hausmeisters', 'Ausschließlich die Innenausstattung'], [0], 'Der Übersichtsplan zeigt Lage, Zufahrten, Aufstellflächen und Löschwasserentnahmestellen.'],
            ['Wo sind Rauchwarnmelder mindestens vorgeschrieben?', ['In Küche und Bad', 'Nur im Keller', 'In Schlafräumen, Kinderzimmern und Fluren, die als Rettungsweg dienen', 'Nur in Gewerbegebäuden'], [2], 'Rauchwarnmelder sind in Schlafräumen, Kinderzimmern und Rettungswegflure Pflicht.'],
            ['Wie werden Rauchwarnmelder richtig montiert?', ['An der Wand in Bodennähe', 'An der Decke, möglichst in der Raummitte mit Abstand zu Wänden', 'Im Fensterrahmen', 'Direkt über dem Herd'], [1], 'Rauch steigt nach oben, daher gehört der Melder an die Decke in die Raummitte.'],
            ['Wann muss ein Rauchwarnmelder spätestens ausgetauscht werden?', ['Nach 10 Jahren', 'Nach 1 Jahr', 'Nach 25 Jahren', 'Nie'], [0], 'Rauchwarnmelder werden spätestens nach 10 Jahren ersetzt.'],
        ],
    ],
];
