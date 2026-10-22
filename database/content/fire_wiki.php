<?php
/** Wiki-Inhalte Feuerwehr: SOPs, Technik, Atemschutz, Gefahrgut, Brandschutz, Organisation. */
return [
    ['title' => 'SOP Wohnungsbrand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Der Wohnungsbrand ist der häufigste Brandeinsatz des LSFD, typisch in Mehrfamilienhäusern in Davis, Rancho oder Strawberry. Standardalarm: **HLF + DLK + ELW**, SAMS wird parallel alarmiert. Grundsatz: **Menschenrettung vor Brandbekämpfung**.

## Anfahrt und Lageerkundung
- Bereits auf der Anfahrt Objektplan im System prüfen (siehe [[Objektpläne und Feuerwehrpläne]]).
- Fahrzeugaufstellung: DLK an die Front mit vermuteten Personen, HLF nicht in den Anleiterbereich stellen.
- Einheitsführer erkundet **360 Grad**, spricht mit Anrufer, Nachbarn, Polizei: Wie viele Personen? Welches Geschoss? Ist der Treppenraum rauchfrei?

## Einsatzablauf
1. **Angriffstrupp** unter Atemschutz mit C-Rohr über den Treppenraum zur Brandwohnung, Menschenrettung und Brandbekämpfung (siehe [[Atemschutz-Einsatzgrundsätze]]).
2. **Wassertrupp** stellt Wasserversorgung her, Verteiler vor dem Gebäude (siehe [[Wasserversorgung und Hydranten]]).
3. **Sicherheitstrupp** steht ausgerüstet bereit (siehe [[Sicherheitstrupp und Rückzugsweg]]).
4. DLK als zweiter Rettungsweg, ggf. Rettung über das Fenster.
5. Treppenraum mit Rauchschutzvorhang schützen, Überdrucklüfter erst nach Absprache mit dem Angriffstrupp.

## Besonderheiten
- Türöffnung immer mit Türkontrolle (Temperatur, Rauch unter der Tür), Gefahr der Rauchdurchzündung, siehe [[Rauchdurchzündung und Flashover]].
- Gerettete Personen sofort an SAMS übergeben, Patientenablage außerhalb des Gefahrenbereichs (siehe [[Zusammenarbeit mit SAMS]]).
- Nachbarwohnungen kontrollieren, Wärmebildkamera einsetzen.

## Nach dem Einsatz
- Kontrolle auf Glutnester, Brandstelle an Polizei oder Eigentümer übergeben.
- Bei unklarer Ursache Brandstelle sichern, siehe [[Zusammenarbeit mit der Polizei]].
- **Einsatzbericht** im System anlegen, Atemschutz-Nachweise eintragen (siehe [[Einsatznachbereitung und Einsatzbericht]]).
MD],
    ['title' => 'SOP Kellerbrand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Kellerbrände gehören zu den gefährlichsten Brandeinsätzen: hohe Temperaturen, Nullsicht, verwinkelte Gänge und nur ein Zugang. In Los Santos betrifft das vor allem Altbauten in Mission Row, Tiefgaragen in Pillbox Hill und Lagerkeller im Gewerbegebiet La Mesa.

## Lageerkundung
- Kellerzugänge (innen und außen) und Lichtschächte feststellen.
- Gibt es Gas-, Strom- oder Heizungsanlagen im Keller? Hausmeister oder Eigentümer befragen.
- Rauchausbreitung in den Treppenraum? Dann haben Bewohner Vorrang, Treppenraum zuerst rauchfrei halten.

## Taktik
1. Treppenraum mit **Rauchschutzvorhang** an der Kellertür sichern.
2. Angriffstrupp mit C-Rohr, **Wärmebildkamera** und Leinensicherung bzw. Schlauchleitung als Rückweg. Die Schlauchleitung ist der Rückzugsweg (siehe [[Sicherheitstrupp und Rückzugsweg]]).
3. Strom im Keller abschalten lassen, Gas abschiebern.
4. Sicherheitstrupp zwingend, Atemschutzüberwachung mit kurzen Rückmeldeintervallen ([[Atemschutzüberwachung]]).
5. Entrauchung erst nach Lokalisierung des Brandherdes, Abluftöffnung vorher schaffen.

## Gefahren
- Hitzestau, Durchzündung beim Öffnen von Kellerabteilen, siehe [[Rauchdurchzündung und Flashover]].
- Lithium-Akkus (E-Bikes, Roller) in Kellerräumen – siehe [[SOP Elektrofahrzeug-Brand]].
- Gefahr durch gelagerte Chemikalien, Gasflaschen, Lacke.
- Atemluftverbrauch steigt stark, Rückzug rechtzeitig planen.

## Nachbereitung
Kellerbrände führen oft zu hohem Atemschutzaufwand. Alle Trupps im **Einsatzbericht** mit Einsatzzeiten erfassen, Geräte zur Prüfung geben ([[Geräteprüfung und Wartung]]). CO-Messung im ganzen Gebäude vor Freigabe.
MD],
    ['title' => 'SOP Fahrzeugbrand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Fahrzeugbrände sind in Los Santos alltäglich – auf dem Highway, auf Parkplätzen in Vespucci oder nach Verkehrsunfällen. Standard: **HLF**, bei Verletzten zusätzlich SAMS, bei Verkehrsbehinderung LSPD bzw. BCSO.

## Absicherung
- Fahrzeug des LSFD schräg als Schutzschild aufstellen, Warnbeleuchtung an.
- Absperrung durch Polizei anfordern (siehe [[Zusammenarbeit mit der Polizei]]).
- Windrichtung beachten, Annäherung von **schräg vorne oder hinten**, nie frontal von vorne oder von der Seite in Höhe der Stoßfänger und Gasdruckdämpfer.

## Einsatzablauf
1. Personen im oder am Fahrzeug? Menschenrettung hat Vorrang.
2. Angriffstrupp **unter Atemschutz** mit Hohlstrahlrohr, Brandrauch von Fahrzeugen ist hochgiftig.
3. Innenraum und Motorraum ablöschen, Motorhaube nur mit Werkzeug (Halligan) anheben.
4. Bei Felgen- und Magnesiumbränden: viel Wasser oder Sonderlöschmittel, siehe [[Löschmittel und ihre Anwendung]].
5. Kraftstoff auslaufend? Bindemittel, Schaum, Kanalisation abdecken, siehe [[SOP Ölspur]].

## Besondere Antriebe
- **Elektro- und Hybridfahrzeuge**: siehe [[SOP Elektrofahrzeug-Brand]].
- **Gasfahrzeuge (LPG/CNG)**: Abblasen von Sicherheitsventilen möglich, Abstand halten, Tank kühlen.
- Polizeifahrzeuge: Munition im Fahrzeug möglich – Rücksprache mit LSPD.

## Abschluss
- Fahrzeug nach Ablöschen mit Wärmebildkamera kontrollieren.
- Bei Verdacht auf Brandstiftung (mehrere Brandherde, Brandbeschleuniger-Geruch) Polizei informieren, nichts unnötig verändern.
- Kennzeichen, Halter, Abschleppdienst im **Einsatzbericht** vermerken.
MD],
    ['title' => 'SOP Elektrofahrzeug-Brand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Brennende Lithium-Ionen-Akkus verhalten sich anders als konventionelle Fahrzeugbrände: **Thermisches Durchgehen** (Thermal Runaway) kann nach Stunden erneut einsetzen, die Rauchgase enthalten Fluorwasserstoff und andere toxische Stoffe.

## Erkennen
- Typenbezeichnung, Ladekabel, fehlender Auspuff, Kennzeichen mit E-Zusatz.
- Zischende Geräusche, weiß-grauer Rauch, Stichflammen aus dem Unterboden.
- Bei Unklarheit: Fahrzeug als Elektrofahrzeug behandeln.

## Maßnahmen
1. Großzügiger Gefahrenbereich, Annäherung nur unter **umluftunabhängigem Atemschutz**.
2. Fahrzeug gegen Wegrollen sichern, Zündung aus, Schlüssel mindestens 5 m entfernen.
3. Brandbekämpfung mit **viel Wasser**, Ziel ist die Kühlung des Akkus. Ein einmaliges Ablöschen reicht nicht.
4. Temperaturkontrolle mit Wärmebildkamera über mindestens 30–60 Minuten.
5. Bei erneutem Aufflammen: Abrollbehälter mit Wasserbad über Wechsellader anfordern (siehe [[Sonderfahrzeuge ELW, GW-G und Wechsellader]]).

## Nicht tun
- Kein Schneiden im Bereich der orangefarbenen Hochvoltleitungen.
- Kein Schaum als alleiniges Löschmittel, keine Löschdecken ohne Kühlung.
- Den Akku nicht anbohren ohne Freigabe des Einsatzleiters.

## Übergabe
Abschleppdienst ausdrücklich auf **Brandgefahr** hinweisen, Fahrzeug nur im Freien mit Abstand abstellen lassen. Im **Einsatzbericht** Wasserverbrauch, Dauer der Kühlung und Übergabeempfänger dokumentieren. Für E-Scooter und Akkus in Gebäuden gilt sinngemäß dasselbe, siehe [[SOP Kellerbrand]].
MD],
    ['title' => 'SOP Tankstellenbrand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Tankstellen (Ron, Globe Oil, LTD) gibt es in der ganzen Stadt und auf dem Land, etwa an der Route 68 bei Sandy Shores oder am Great Ocean Highway bei Paleto Bay. Ein Brand an der Zapfsäule kann schnell eskalieren.

## Alarmstufe
Mindestens **2 HLF, TLF, DLK, ELW**, bei Beteiligung eines Tankwagens zusätzlich **GW-G** und Schaummittelreserve. SAMS und Polizei werden mitalarmiert.

## Erste Maßnahmen
1. Aufstellung **mit dem Wind**, außerhalb des Gefahrenbereichs (mind. 50 m, bei Tankwagen 100 m+).
2. Not-Aus der Tankstelle betätigen (Kassenbereich) oder durch Personal betätigen lassen.
3. Räumung von Tankstelle und Umgebung durch Polizei.
4. Menschenrettung – Kunden, Personal, Personen in Fahrzeugen.

## Brandbekämpfung
- Auslaufender Kraftstoff: **Schaum** (Mittelschaum oder Schwerschaum), keinesfalls Vollstrahl in brennende Flüssigkeit – siehe [[Löschmittel und ihre Anwendung]].
- Zapfsäulenbrand: Pulverlöscher für kleine Brände, anschließend Schaumdecke.
- Benachbarte Fahrzeuge, Gebäude und Tanks **kühlen**, Riegelstellung aufbauen.
- Unterirdische Tanks sind meist sicher, Domschächte jedoch kontrollieren.

## Tankwagen
Bei brennendem oder leckgeschlagenem Tankwagen gilt die [[SOP Gefahrgutunfall (GAMS)]]. UN-Nummer und Gefahrnummer an der orangefarbenen Warntafel ablesen (Fernglas).

## Nachsorge
- Kanalisation abdichten, Umweltbehörde informieren.
- Messung mit Ex-Warngerät vor Freigabe.
- Gemeinsamer Bericht mit Polizei, da häufig Straftaten (Raub, Brandstiftung) im Hintergrund stehen.
MD],
    ['title' => 'SOP Waldbrand / Vegetationsbrand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Vegetationsbrände drohen besonders im trockenen Sommer: Vinewood Hills, Tataviam Mountains, Raton Canyon, die Ebenen rund um Grand Senora Desert und die Wälder bei Paleto Forest. Wind und Hanglage bestimmen die Ausbreitung.

## Lage
- Ausbreitungsrichtung, Windgeschwindigkeit, Geländeneigung (Feuer läuft bergauf schneller).
- Gefährdete Objekte: Villen in den Vinewood Hills, Farmen in Grapeseed, Camping am Mount Chiliad.
- Frühzeitig **Abschnitte** bilden ([[Abschnittsbildung bei Großlagen]]).

## Taktik
1. **Flankenangriff** von der verbrannten Seite (Ankerpunkt), nie frontal gegen die Feuerfront.
2. **TLF** als Hauptlöschfahrzeuge mit Schnellangriff, Pendelverkehr zur Wasserversorgung organisieren.
3. Riegelstellungen vor Bebauung, Schneisen mit Werkzeug (Feuerpatsche, Rechen).
4. **Feuerwehrhubschrauber** mit Außenlastbehälter anfordern, Wasseraufnahme z. B. Alamo Sea oder Land Act Reservoir (siehe [[Feuerwehrhubschrauber und Luftrettung]]).
5. Evakuierung gemeinsam mit BCSO/LSPD, Sammelstellen festlegen.

## Sicherheit (LACES)
- **Lookouts** – Beobachter, **Anchor/Communication** – Funkverbindung, **Escape Routes** – Fluchtwege, **Safety Zones** – sichere Bereiche.
- Fahrzeuge immer in Fluchtrichtung abstellen, Motor laufen lassen.
- Persönliche Schutzausrüstung: leichte Vegetationsbrand-Kleidung, viel trinken – siehe [[Unfallverhütung und Eigenschutz]].

## Nachbereitung
Nachlöscharbeiten können Tage dauern. Brandwache einteilen, Glutnester mit Wärmebildkamera suchen. Bei Verdacht auf Brandstiftung DOJ und Polizei einbinden. **Einsatzbericht** mit Karte der betroffenen Fläche.
MD],
    ['title' => 'SOP Brand in Hochhaus', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Hochhäuser in Downtown Los Santos (Pillbox Hill, Maze Bank Tower, Arcadius Business Center) und Hotels in Del Perro erfordern besondere Taktik. Ab etwa dem 8. Geschoss reicht die Drehleiter nicht mehr – der Angriff erfolgt **ausschließlich von innen**.

## Alarmierung
Mindestens **3 HLF, 2 DLK, RW, ELW**, Führungsdienst, SAMS mit erhöhtem Aufgebot.

## Ablauf
1. Anfahrt an die **Brandmeldezentrale** (BMZ) und das Feuerwehr-Bedienfeld, Laufkarte entnehmen.
2. Feuerwehraufzug nutzen – nur wenn freigegeben und nur bis **zwei Geschosse unter** dem Brandgeschoss.
3. **Bereitstellungsraum** zwei Geschosse unter dem Brand: Atemschutzsammelplatz, Geräte, Sicherheitstrupp.
4. Steigleitung (trocken oder nass) nutzen, Einspeisung durch Wassertrupp.
5. Angriff über den Sicherheitstreppenraum, Treppenraum rauchfrei halten.

## Menschenrettung
- Personen in oberen Geschossen bleiben oft besser in ihrer Wohnung bzw. ihrem Büro (Türen zu, Fenster markieren) als durch einen verrauchten Treppenraum zu flüchten.
- Rettung über Hubschrauber vom Dach nur in Ausnahmefällen.

## Führung
- **Abschnitte** nach Geschossen bilden, siehe [[Abschnittsbildung bei Großlagen]].
- Atemschutzüberwachung zentral im Bereitstellungsraum ([[Atemschutzüberwachung]]).
- Hohe Anzahl an Atemschutzgeräteträgern einplanen, frühzeitig nachalarmieren.

## Vorbeugend
Für alle Hochhäuser liegen **Feuerwehrpläne** im System vor ([[Objektpläne und Feuerwehrpläne]]). Mängel bei der [[Brandschau – Ablauf]] werden im Brandschaubericht festgehalten.
MD],
    ['title' => 'SOP Lagerhallenbrand', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Lagerhallen in Elysian Island, Cypress Flats, am Port of Los Santos und in Murrieta Heights bergen große Brandlasten, oft unbekannte Stoffe und die Gefahr des Einsturzes der Dachkonstruktion.

## Lageerkundung
- Was wird gelagert? Lagerverzeichnis, Betreiber, Objektplan im System.
- Gefahrstoffe? Kennzeichnung an Toren und Regalen. Bei Gefahrgut gilt zusätzlich [[SOP Gefahrgutunfall (GAMS)]].
- Personen in der Halle (Schichtpersonal, Obdachlose)?
- Bauart: Stahlhallen verlieren nach 10–15 Minuten Brandeinwirkung ihre Tragfähigkeit.

## Taktik
1. **Innenangriff nur kurz** und nur zur Menschenrettung oder bei begrenztem Brand.
2. Bei Vollbrand: **Außenangriff** mit B-Rohren, Wenderohr der DLK, Werfer.
3. **Riegelstellung** zum Schutz angrenzender Hallen und Tanks.
4. Wasserversorgung über lange Wegstrecke, ggf. Hafenwasser über Pumpen (siehe [[Wasserversorgung und Hydranten]]).
5. Rauchgaswarnung an die Bevölkerung über Polizei und Leitstelle.

## Führung
- Frühzeitige Abschnittsbildung (Abschnitte Nord, Süd, Wasserversorgung, Bereitstellung).
- ELW besetzen, Lagekarte führen.
- Einsturzbereich (1,5-fache Gebäudehöhe) freihalten und deutlich kommunizieren.

## Nach dem Einsatz
- Brandwache stellen, Bagger zum Auseinanderziehen anfordern.
- Löschwasser auffangen, Umweltbelastung prüfen.
- Brandursachenermittlung durch LSPD, Absprachen im gemeinsamen Bericht. Brandschutzmängel im Nachgang über den Vorbeugenden Brandschutz verfolgen ([[Brandschau – Ablauf]]).
MD],
    ['title' => 'Rauchdurchzündung und Flashover', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Zwei Phänomene, die jedes Jahr Feuerwehrleute töten. Jeder Atemschutzgeräteträger im LSFD muss Warnzeichen erkennen und richtig handeln können.

## Flashover (Feuerübersprung)
Im Raum steigt die Temperatur durch Strahlungswärme so weit, dass sich alle brennbaren Oberflächen gleichzeitig entzünden (ab ca. 500–600 °C in der Rauchschicht).
- **Warnzeichen**: rasch sinkende Rauchschicht, starke Hitze von oben (Helm wird heiß), Flammen in der Rauchschicht (Rollover), Pyrolysegase aus Möbeln.
- **Gegenmaßnahme**: Rauchschicht mit kurzen Sprühstößen kühlen (Pulsing), tief bleiben, bei Unsicherheit **Rückzug**.

## Rauchdurchzündung (Backdraft)
In einem geschlossenen, sauerstoffarmen Raum sammeln sich brennbare Gase. Wird eine Tür geöffnet, strömt Luft ein und das Gemisch zündet explosionsartig.
- **Warnzeichen**: pulsierender Rauch an Türspalten, gelblich-brauner Rauch, rußgeschwärzte Fenster ohne sichtbare Flammen, Pfeifgeräusche, heiße Türklinke.
- **Gegenmaßnahme**: Tür kontrolliert öffnen (Türöffnungsprozedur), seitlich stehen, Rohr bereithalten, ggf. Tür sofort wieder schließen. Vertikale Ventilation vorher erwägen.

## Türöffnungsprozedur
1. Tür von unten nach oben mit Handrücken oder Wärmebildkamera prüfen.
2. Trupp seitlich in Deckung, Rohr offen.
3. Tür einen Spalt öffnen, Sprühstoß in den Rauch, Tür schließen, kurz warten.
4. Erneut öffnen und vorgehen.

## Merke
Nur ein gekühlter Rauch ist ein sicherer Rauch. Rückzugsweg ist jederzeit freizuhalten ([[Sicherheitstrupp und Rückzugsweg]]). Bei Durchzündung mit Verletzten: [[Atemschutz-Notfall (Mayday)]].
MD],
    ['title' => 'Löschmittel und ihre Anwendung', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Die Wahl des Löschmittels richtet sich nach der **Brandklasse**. Ein falsches Löschmittel kann den Brand verschlimmern oder die Einsatzkräfte gefährden.

| Brandklasse | Stoffe | Geeignete Löschmittel |
|---|---|---|
| A | feste Stoffe (Holz, Papier, Textil) | Wasser, Schaum, ABC-Pulver |
| B | flüssige/flüssig werdende Stoffe (Benzin, Öl, Wachs) | Schaum, Pulver, CO₂ |
| C | Gase (Propan, Methan, Wasserstoff) | Pulver; vorrangig Gaszufuhr unterbrechen |
| D | Metalle (Magnesium, Aluminium, Natrium) | Metallbrandpulver, trockener Sand |
| F | Speiseöle/-fette | Fettbrandlöscher, niemals Wasser |

## Wasser
Bestes Kühlmittel. Sprühstrahl für Innenangriff und Rauchgaskühlung, Vollstrahl für Wurfweite. **Gefahren**: Fettexplosion, Metallbrand, Stromleitungen (Mindestabstände nach VDE beachten: Niederspannung Sprühstrahl 1 m, Vollstrahl 5 m mit C-Rohr).

## Schaum
- **Schwerschaum** (Verschäumung bis 20): Flüssigkeitsbrände, große Wurfweite.
- **Mittelschaum** (bis 200): Fluten von Räumen, Abdecken von Lachen.
- **Leichtschaum** (über 200): Fluten großer Hallen.
Zumischung über Zumischer am HLF oder TLF, Schaummittelvorrat beachten.

## Pulver und CO₂
- Pulver: schnelle Löschwirkung, aber keine Kühlung – Rückzündung möglich.
- CO₂: für elektrische Anlagen und Serverräume, Erstickungsgefahr in geschlossenen Räumen.

## Praxis Los Santos
- Tankstellen: Schaum, siehe [[SOP Tankstellenbrand]].
- Elektrofahrzeuge: viel Wasser, siehe [[SOP Elektrofahrzeug-Brand]].
- Restaurant-Küchen am Del Perro Pier: Fettbrand, Löschanlage der Küche und Fettbrandlöscher.
MD],
    ['title' => 'Wasserversorgung und Hydranten', 'category' => 'Brandbekämpfung', 'content' => <<<'MD'
Ohne Wasser kein Löscherfolg. Der **Wassertrupp** ist für die Wasserversorgung verantwortlich, die Planung übernimmt der Einheitsführer bzw. bei Großlagen ein eigener Abschnitt.

## Wasserentnahmestellen
- **Unterflurhydranten**: in der Stadt (Downtown, Davis, Vespucci). Hinweisschild mit Abstandsangabe beachten. Standrohr, Schlüssel und Schlauch benötigt.
- **Überflurhydranten**: in Gewerbegebieten, am Hafen, in Teilen von Sandy Shores.
- **Offene Gewässer**: Alamo Sea, Land Act Reservoir, Hafenbecken, Pazifik (Salzwasser – Pumpen danach spülen).
- **Löschwasserteiche und Zisternen**: auf Farmen in Grapeseed und an der Route 68.

## Aufbau der Löschwasserversorgung
1. Wassertrupp setzt Standrohr, B-Leitung zum Fahrzeug (Pumpe).
2. Vom Fahrzeug B-Leitung zum **Verteiler**.
3. Vom Verteiler C-Leitungen zu den Trupps.
4. Bis zur Herstellung: Tankinhalt des Fahrzeugs nutzen (HLF ca. 2.000 l, TLF 4.000–5.000 l).

## Lange Wegstrecke
- Ländlicher Bereich (Blaine County): **Pendelverkehr** mit TLF oder Wasserförderung über lange Wegstrecke mit Verstärkerpumpen alle ca. 500–800 m.
- Wechsellader mit Abrollbehälter Wasser oder Schlauch anfordern, siehe [[Sonderfahrzeuge ELW, GW-G und Wechsellader]].

## Hinweise
- Hydranten im Winter und nach Gebrauch melden, defekte Hydranten im System erfassen.
- Bei großen Lagen eigenen Abschnitt „Wasserversorgung“ bilden ([[Abschnittsbildung bei Großlagen]]).
- Wasserverbrauch im **Einsatzbericht** schätzen.
MD],
    ['title' => 'SOP Verkehrsunfall eingeklemmte Person', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Verkehrsunfälle mit eingeklemmten Personen ereignen sich vor allem auf dem Del Perro Freeway, der Route 68 und dem Great Ocean Highway. Standard: **HLF + RW**, SAMS mit Notarzt, Polizei zur Absicherung.

## Grundsätze
- **Patientenorientierte Rettung**: Die Rettungsmethode richtet sich nach dem Zustand des Patienten, den SAMS beurteilt.
- **Sofortrettung** (Crash-Rettung) nur bei unmittelbarer Lebensgefahr (Brand, Reanimation).
- Ziel: Rettung innerhalb der **Golden Hour**, technische Rettung idealerweise in unter 20 Minuten.

## Ablauf
1. **Absicherung** der Einsatzstelle, Brandschutz sicherstellen (Pulverlöscher, Schnellangriff).
2. Fahrzeug **stabilisieren** (Unterbaukeile, Stab-Fast), Zündung aus, Batterie abklemmen – bei Elektrofahrzeugen Hochvolt-System deaktivieren (Rettungskarte).
3. **Innerer Retter** (Feuerwehr oder SAMS) betreut den Patienten im Fahrzeug, Glasmanagement mit Abdeckung.
4. Airbags lokalisieren, nicht ausgelöste Airbags meiden (Sicherheitsabstände 30/60/90 cm).
5. **Erstöffnung** schaffen (Tür), danach **Versorgungsöffnung**, dann **Rettungsöffnung** (z. B. Dach abnehmen, Tür-B-Säule entfernen).
6. Patient achsengerecht mit Spineboard oder Schaufeltrage übergeben.

## Kommunikation
Einheitsführer Feuerwehr und SAMS sprechen sich ständig ab. Kommandos zum Bewegen des Patienten gibt **ein** Verantwortlicher, siehe [[Zusammenarbeit mit SAMS]].

## Geräte
Rettungsschere, Spreizer, Rettungszylinder (siehe [[Fahrzeugkunde Rüstwagen]]), Glasmanagement-Set, Stabilisierungssystem.

## Nachbereitung
Ölspur und Trümmer beseitigen ([[SOP Ölspur]]), Unfallaufnahme durch LSPD/BCSO nicht behindern. Gemeinsamer **Einsatzbericht** mit SAMS und Polizei möglich.
MD],
    ['title' => 'SOP Person unter Fahrzeug', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Personen unter Pkw, Lkw, Bussen oder Straßenbahnen sind ein zeitkritischer Notfall. Oft gerät jemand bei Unfällen mit Fußgängern in Downtown oder bei Arbeitsunfällen im Hafen unter ein Fahrzeug.

## Erste Maßnahmen
1. Fahrzeug gegen Wegrollen sichern (Handbremse, Unterlegkeile, Gang einlegen), Motor aus.
2. Kontakt zum Patienten herstellen, SAMS beginnt Versorgung so weit möglich.
3. Lage der Person feststellen: Ist sie eingeklemmt oder nur darunter?

## Anheben
- **Hebekissen** (Niederdruck oder Hochdruck) oder Wagenheber/Rettungszylinder einsetzen.
- **Grundregel**: Was angehoben wird, wird sofort unterbaut („Heben – Unterbauen – Heben“).
- Anheben nur so viel wie nötig, gleichmäßig, mit klaren Kommandos („Heben!“ – „Stopp!“).
- Bei schweren Fahrzeugen (Bus, Lkw) zusätzlich Rüstwagen oder Kran anfordern, siehe [[Fahrzeugkunde Rüstwagen]].

## Retten
- Patient mit Schaufeltrage oder Spineboard herausziehen, achsengerecht.
- Kommando zur Rettung gibt der Einsatzleiter nach Absprache mit SAMS.
- Fahrzeug bleibt bis zum Abschluss der Rettung unterbaut.

## Besonderheiten
- Bei Zügen und Straßenbahnen: Fahrstrom abschalten lassen, Erdung durch Bahnpersonal.
- Kraftstoff tritt aus: Brandschutz sicherstellen.
- Psychische Belastung für Beteiligte und Fahrer hoch, PSNV anbieten ([[Psychosoziale Notfallversorgung (PSNV-E)]]).

## Dokumentation
Fahrzeugdaten, eingesetzte Geräte und Zeiten im **Einsatzbericht** erfassen. Polizei zur Unfallaufnahme einbeziehen.
MD],
    ['title' => 'SOP Tragehilfe für den Rettungsdienst', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Die Tragehilfe ist der häufigste Einsatz zur Unterstützung von SAMS: schwergewichtige Patienten, enge Treppenhäuser in Altbauten, Patienten in Hanglagen der Vinewood Hills oder unwegsames Gelände bei Sandy Shores.

## Alarmierung
SAMS fordert über die Leitstelle an, Stichwort „Tragehilfe“. In der Regel genügt ein **HLF**, bei schweren Patienten oder Rettung über Fenster zusätzlich **DLK** mit Krankentragehalterung.

## Ablauf
1. Rückmeldung an die Leitstelle mit Status 3 und 4 (siehe [[Funkdisziplin und FMS-Status]]).
2. Absprache mit dem SAMS-Teamleiter: Zustand des Patienten, Lagerung, gewünschter Transportweg.
3. Weg freiräumen, ggf. Türen aushängen, Beleuchtung sicherstellen.
4. Geeignetes Rettungsmittel wählen: Tragestuhl, Schleifkorbtrage, Vakuummatratze, Schwerlasttrage.
5. Tragen mit mindestens vier Kräften, klare Kommandos. Medizinische Verantwortung bleibt bei SAMS.

## Drehleiterrettung
- Wenn der Treppenraum zu eng ist oder der Patient liegend transportiert werden muss.
- Krankentragehalterung am Korb, Patient wird auf der Trage gesichert.
- Ein Feuerwehrangehöriger begleitet im Korb, siehe [[Fahrzeugkunde Drehleiter]].

## Hinweise
- Rückenschonendes Heben, Eigenschutz beachten ([[Unfallverhütung und Eigenschutz]]).
- Patientenwürde wahren, Schaulustige fernhalten.
- Kurzen **Einsatzbericht** anlegen, Verweis auf das SAMS-Einsatzprotokoll. Siehe auch [[Zusammenarbeit mit SAMS]].
MD],
    ['title' => 'SOP Türöffnung', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Eine Türöffnung erfolgt bei **Gefahr im Verzug**: hilflose Person hinter verschlossener Tür, Wasserrohrbruch, Rauchmelder piept, Gasgeruch. Reine Schlüsseldienstleistungen ohne Notlage gehören nicht zu den Aufgaben des LSFD.

## Voraussetzungen
- Konkreter Hinweis auf eine Notlage (Hilferufe, Angehörige, Hausnotruf, SAMS vor Ort).
- **Polizei** wird grundsätzlich hinzugezogen, da es sich um einen Eingriff in die Unverletzlichkeit der Wohnung handelt (siehe [[Zusammenarbeit mit der Polizei]]).
- SAMS ist bei medizinischen Notfällen bereits vor Ort oder alarmiert.

## Vorgehen
1. Klingeln, klopfen, rufen, Nachbarn befragen, Fenster und Balkone prüfen.
2. Zuerst **zerstörungsarme Methoden**: Fallenziehen mit Folie, Türöffnungsset, Fensteröffnung über DLK oder Leiter.
3. Erst danach gewaltsam: Ziehfix am Profilzylinder, Halligan-Tool, im Notfall Ramme.
4. Bei Gasgeruch: keine funkenreißenden Werkzeuge, keine Klingel – [[SOP Gasaustritt]].
5. Nach Öffnung: SAMS den Vortritt lassen, sobald die Lage sicher ist.

## Nach der Öffnung
- Wohnung nicht unverschlossen zurücklassen: Polizei übernimmt, Notverschluss oder Schlüsseldienst.
- Schäden dokumentieren (Fotos), Eigentümer informieren.
- **Einsatzbericht** mit Begründung der Gefahrenlage, Uhrzeiten und anwesender Polizeistreife.

## Hinweis
Im Zweifel geht der Schutz von Leben vor dem Sachschaden. Die Entscheidung trifft der Einheitsführer.
MD],
    ['title' => 'SOP Ölspur', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Ölspuren und auslaufende Betriebsstoffe nach Unfällen gefährden den Verkehr und die Umwelt. Kleinere Ölspuren beseitigt teilweise die Straßenmeisterei, das LSFD wird bei Gefahr für Verkehr oder Gewässer alarmiert.

## Gefahren
- Rutschgefahr für Zweiräder und Fahrzeuge.
- Eintrag in die Kanalisation und in Gewässer (Los Santos River, Hafenbecken, Alamo Sea).
- Bei Kraftstoff: Brand- und Explosionsgefahr.

## Maßnahmen
1. **Absicherung** durch Polizei und eigenes Fahrzeug, Warnschilder „Ölspur“.
2. Quelle feststellen und stoppen (Auffangwanne unter Fahrzeug, Leckdichtkissen).
3. **Bindemittel** ausbringen, einarbeiten (Besen), aufnehmen, in Behältern sammeln.
4. Gullys mit Kanalabdeckung schützen.
5. Bei großer Menge oder Gewässergefahr: **Ölsperre** über Rettungsboot ausbringen, siehe [[Rettungsboot und Hafeneinsätze]].
6. Bei Kraftstoff: Brandschutz mit Schaumrohr bereitstellen.

## Abschluss
- Fahrbahn auf Griffigkeit prüfen, ggf. Spezialreinigung anfordern.
- Verantwortlichen (Fahrzeughalter) über Polizei feststellen, Kosten werden in Rechnung gestellt.
- Menge an Bindemittel, Länge der Spur und Verursacher im **Einsatzbericht** vermerken.

## Hinweis
Sind größere Mengen unbekannter Stoffe ausgetreten, wird nach [[SOP Gefahrgutunfall (GAMS)]] vorgegangen.
MD],
    ['title' => 'SOP Einsturz', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Gebäudeeinstürze entstehen durch Explosionen, Brände, Baumängel oder Fahrzeuganprall. In Los Santos betrifft das Baustellen in Downtown, Altbauten in Rancho oder Industrieanlagen in Murrieta Heights.

## Phasen der Rettung
1. **Erkundung und Sicherung**: Weitere Einsturzgefahr? Gas, Strom, Wasser abschalten.
2. **Oberflächenrettung**: Sichtbare und leicht erreichbare Personen retten.
3. **Rettung aus Hohlräumen**: Durchsuchen von Hohlräumen (Rufen, Klopfen, Horchen).
4. **Gezielte Rettung** mit Bergungsgerät unter Abstützung.
5. **Enttrümmerung** mit schwerem Gerät, erst wenn keine Vermissten mehr erwartet werden.

## Maßnahmen
- Absperrbereich festlegen, Polizei sperrt großräumig ab.
- **Ruhephasen** anordnen, damit Klopfzeichen gehört werden.
- Abstützen mit Holz und Rettungszylindern, siehe [[Fahrzeugkunde Rüstwagen]].
- Statiker/Baufachberater anfordern.
- Atemschutz bei Staub- oder Gasbelastung.

## Führung
- Frühzeitig **Abschnitte** bilden (Schadensbereich, Bereitstellung, Patientenablage), siehe [[Abschnittsbildung bei Großlagen]].
- Vermisstenliste gemeinsam mit Polizei und SAMS führen.
- Bei vielen Verletzten gilt [[MANV – Aufgaben der Feuerwehr]].

## Sicherheit
Einsatzkräfte arbeiten nie allein im Trümmerbereich. Sicherheitsbeobachter benennen, Rückzugssignal vereinbaren (drei lange Töne). Siehe auch [[Unfallverhütung und Eigenschutz]].

## Nachbereitung
Ausführlicher **Einsatzbericht**, bei Verdacht auf Straftat (Sprengstoff, Bauverstoß) Informationsweitergabe an Polizei und DOJ.
MD],
    ['title' => 'SOP Flugzeugunfall LSIA', 'category' => 'Technische Hilfe', 'content' => <<<'MD'
Am **Los Santos International Airport (LSIA)** und am Sandy Shores Airfield muss jederzeit mit Luftfahrzeugunfällen gerechnet werden. Fort Zancudo ist militärisches Gelände – dort arbeitet das LSFD nur auf Anforderung und mit Freigabe des Militärs.

## Alarmierung
Großalarm: mehrere **TLF** mit Schaum, HLF, RW, DLK, ELW, Führungsdienst, SAMS mit MANV-Stichwort, Polizei.

## Anfahrt
- Zufahrt über die vorgegebenen Tore, Lotsenfahrzeug des Flughafens nutzen.
- Freigabe des Towers für das Befahren von Rollwegen und Pisten abwarten.
- Anfahrt **mit dem Wind**, Aufstellung in sicherem Abstand zu Triebwerken und Tanks.

## Einsatztaktik
1. **Rettungsgasse freischäumen** zu den Notausgängen, Rumpf kühlen.
2. Menschenrettung über Notrutschen und Türen, Personen zur Sammelstelle führen.
3. Kerosinlachen mit Schaum abdecken, siehe [[Löschmittel und ihre Anwendung]].
4. Gefahren beachten: Triebwerkseinlauf und -abgas, Fahrwerk, Druckbehälter, Sauerstoffflaschen, Gefahrgut in der Fracht.
5. Patientenablage und Sichtung durch SAMS ([[MANV – Aufgaben der Feuerwehr]]).

## Kleinflugzeuge und Hubschrauber
- Rotoren auslaufen lassen, Annäherung nur im Sichtbereich des Piloten.
- Rettungssysteme (Fallschirm-Rakete) bei Ultraleichtflugzeugen beachten.

## Nachbereitung
Wrackteile nicht verändern, Unfallstelle für die Untersuchungsbehörde und Polizei sichern. Umfangreicher gemeinsamer **Einsatzbericht** mit SAMS und Polizei.
MD],
    ['title' => 'SOP Gasaustritt', 'category' => 'Gefahrgut / CBRN', 'content' => <<<'MD'
Gasgeruch in Wohnungen, beschädigte Gasleitungen bei Bauarbeiten oder undichte Propanflaschen in Campingwagen bei Sandy Shores. Erdgas ist leichter als Luft, Propan/Butan schwerer und sammelt sich in Kellern und Senken.

## Anfahrt
- Ohne Sondersignal in den letzten Metern, nicht direkt vor das Gebäude fahren.
- Mit dem Wind anfahren, Fahrzeuge mit Abstand aufstellen.

## Maßnahmen
1. **Zündquellen vermeiden**: keine Klingeln, kein Licht schalten, Funkgeräte und Handys nur außerhalb des Ex-Bereichs, ex-geschützte Geräte nutzen.
2. **Messen** mit Ex-Warngerät (Mehrgasmessgerät), Ex-Bereich festlegen (UEG-Werte: Warnung bei 10 % UEG, Rückzug bei 20–25 % UEG).
3. Personen aus dem Gefahrenbereich bringen, Nachbarwohnungen räumen (Polizei).
4. Gasversorger alarmieren, Hauptabsperreinrichtung schließen.
5. **Lüften** durch Fenster öffnen von außen nach innen, kein elektrischer Lüfter im Ex-Bereich.
6. Brandschutz sicherstellen: Trupp unter Atemschutz mit Schaum- oder C-Rohr bereit.

## Brennendes Gas
Brennendes Gas **nicht löschen**, solange die Zufuhr nicht unterbrochen ist – sonst bildet sich eine explosionsfähige Wolke. Umgebung kühlen, Gaszufuhr abstellen lassen.

## Abschluss
- Freimessung des Gebäudes vor Rückkehr der Bewohner.
- Übergabe an Gasversorger, im **Einsatzbericht** Messwerte und Uhrzeiten dokumentieren.
- Bei größeren Leckagen oder Tankwagen siehe [[SOP Gefahrgutunfall (GAMS)]], Spezialgerät auf dem GW-G ([[Sonderfahrzeuge ELW, GW-G und Wechsellader]]).
MD],
    ['title' => 'SOP Gefahrgutunfall (GAMS)', 'category' => 'Gefahrgut / CBRN', 'content' => <<<'MD'
Bei Unfällen mit Gefahrstoffen – Tanklastzüge auf dem Highway, Leckagen im Port of Los Santos, Chemikalienlager in Elysian Island – arbeitet die ersteintreffende Einheit nach der **GAMS-Regel** (FwDV 500).

## GAMS-Regel
- **G – Gefahr erkennen**: Kennzeichnung (orangefarbene Warntafel mit Gefahrnummer oben und UN-Nummer unten, Gefahrzettel), Begleitpapiere, Fahrerbefragung, Wahrnehmungen (Geruch, Dampf, Verfärbung).
- **A – Absperren**: Gefahrenbereich mindestens **50 m**, bei unbekannten Stoffen oder Brand **100 m**, Absperrbereich durch Polizei.
- **M – Menschenrettung durchführen**: unter geeigneter Schutzausrüstung, nur so weit vertretbar.
- **S – Spezialkräfte alarmieren**: GW-G, Fachberater Chemie, Messkomponente, Dekontaminationseinheit.

## Weitere Maßnahmen
1. Aufstellung **mit dem Wind**, erhöht und außerhalb von Senken.
2. Stoffidentifikation über UN-Nummer und Datenbank im ELW.
3. Atemschutz und je nach Stoff **Chemikalienschutzanzug (CSA)**, Form 3.
4. Dekontaminationsplatz an der Grenze des Gefahrenbereichs einrichten.
5. Leckagen abdichten, Auffangen, Kanalisation schützen.
6. Warnung der Bevölkerung über Leitstelle und Polizei (Fenster und Türen schließen).

## Verletzte
Kontaminierte Patienten werden vor Übergabe an SAMS **dekontaminiert** (Not-Dekon). SAMS über Stoff und Exposition informieren, siehe [[Zusammenarbeit mit SAMS]].

## Siehe auch
- Biologische und radiologische Lagen: [[SOP CBRN-Einsatz Humane Labs]]
- Atemschutz unter CSA: [[Atemschutz-Einsatzgrundsätze]]
- Nachbereitung, Dekon und Hygiene: [[Hygiene nach dem Einsatz (Schwarz-Weiß-Trennung)]]
MD],
    ['title' => 'SOP CBRN-Einsatz Humane Labs', 'category' => 'Gefahrgut / CBRN', 'content' => <<<'MD'
Die **Humane Labs and Research** am Fuß des Mount Chiliad sind das Objekt mit dem höchsten CBRN-Gefährdungspotenzial im Ausrückebereich. Dort werden biologische Agenzien und Chemikalien erforscht. Für das Objekt existiert ein ausführlicher Feuerwehrplan im System.

## Alarmierung
Jede Alarmierung zu Humane Labs löst mindestens **HLF, GW-G, ELW, Führungsdienst** aus, zusätzlich Fachberater Chemie/Biologie. SAMS und BCSO werden informiert.

## Vorgehen
1. Treffpunkt mit dem Werkschutz am Haupttor, **keine eigenmächtige Einfahrt**.
2. Lageinformation vom Sicherheitsbeauftragten einholen: betroffener Bereich, Schutzstufe der Labore (S1–S4), gelagerte Stoffe.
3. Gefahrenbereich und Absperrbereich nach FwDV 500 festlegen, GAMS-Regel anwenden ([[SOP Gefahrgutunfall (GAMS)]]).
4. Einsatz in Laborbereichen nur mit **CSA** bzw. Infektionsschutzausrüstung, Atemschutzüberwachung ([[Atemschutzüberwachung]]).
5. **Dekon-Stufe 2 oder 3** vor Verlassen des Gefahrenbereichs.

## Radiologische Gefahren
- Dosisleistungsmessgerät und Dosimeter für jede Einsatzkraft.
- Grundregeln: **Abstand, Abschirmung, Aufenthaltsdauer begrenzen, Aufnahme verhindern** (AAAA-Regel, ergänzt um Ausrüstung).
- Dosiswerte werden personenbezogen erfasst.

## Nach dem Einsatz
- Alle Einsatzkräfte werden namentlich registriert, ggf. ärztliche Nachuntersuchung.
- Ausrüstung bleibt bis zur Freigabe im Dekon-Bereich.
- Vertraulichkeit: Angaben über Forschungsgegenstände nur im **Einsatzbericht** und gegenüber berechtigten Stellen (DOJ, Behörden).
MD],
    ['title' => 'Atemschutz-Einsatzgrundsätze', 'category' => 'Atemschutz', 'content' => <<<'MD'
Atemschutzeinsätze werden im LSFD nach den Grundsätzen der **FwDV 7** durchgeführt. Nur tauglichkeitsuntersuchte, ausgebildete und unterwiesene Kräfte dürfen Atemschutzgeräte tragen.

## Voraussetzungen
- Gültige arbeitsmedizinische Untersuchung (G 26.3), siehe [[Fitness und Atemschutztauglichkeit]].
- Abgeschlossener Atemschutzlehrgang, jährliche Belastungsübung und Unterweisung.
- Nachweise werden im System als **Atemschutz-Nachweis** geführt.

## Grundregeln
- Atemschutztrupps bestehen aus mindestens **zwei Personen**, die gemeinsam vor- und zurückgehen.
- Der Truppführer ist für den Trupp verantwortlich.
- Funkverbindung zum Einheitsführer bzw. zur Atemschutzüberwachung muss bestehen.
- Für jeden eingesetzten Trupp steht ein **Sicherheitstrupp** bereit ([[Sicherheitstrupp und Rückzugsweg]]).
- Einsatzkurzprüfung vor jedem Anschluss des Lungenautomaten.

## Druckkontrolle und Rückzug
- Flaschendruck beim Einsatzbeginn melden (typisch 300 bar).
- **Rückzug**, wenn der Druck auf das Doppelte dessen gesunken ist, der für den Rückweg benötigt wurde – spätestens beim Ansprechen des Restdruckwarners (ca. 55 bar).
- Druckkontrolle durch Truppführer regelmäßig, Meldung bei 1/3 und 2/3 der Einsatzzeit.

## Nach dem Einsatz
- Erholungsphase einhalten, Flüssigkeit aufnehmen.
- Geräte zur Atemschutzwerkstatt, Prüfung nach Gebrauch ([[Geräteprüfung und Wartung]]).
- Einsatzzeit, Druck und Gerätenummer im Atemschutz-Nachweis dokumentieren.

## Siehe auch
[[Atemschutzüberwachung]], [[Atemschutz-Notfall (Mayday)]].
MD],
    ['title' => 'Atemschutzüberwachung', 'category' => 'Atemschutz', 'content' => <<<'MD'
Die Atemschutzüberwachung stellt sicher, dass jederzeit bekannt ist, **wer, wo, wie lange** unter Atemschutz eingesetzt ist. Sie ist bei jedem Atemschutzeinsatz durchzuführen.

## Verantwortung
- Grundsätzlich überwacht der **Einheitsführer**, er kann die Aufgabe an den Maschinisten oder eine andere Kraft übertragen.
- Bei mehreren Trupps oder Großlagen wird eine eigene Atemschutzüberwachung mit **Atemschutzsammelplatz** eingerichtet.

## Inhalte der Überwachungstafel
- Name des Truppführers und Truppmanns, Funkrufname des Trupps.
- Uhrzeit des Anschlusses, niedrigster Flaschendruck beim Start.
- Auftrag und Einsatzort (z. B. „Wohnung 2. OG links“).
- Zeitpunkte der Rückmeldungen und Druckwerte.
- Uhrzeit des Rückzugs und der Rückkehr.

## Zeitkontrolle
- Nach **1/3 und 2/3** der zu erwartenden Einsatzzeit wird der Trupp zur Druckmeldung aufgefordert.
- Bleibt eine Rückmeldung aus: erneut rufen, dann **Sicherheitstrupp** in Marsch setzen.
- Bei Überschreiten der Einsatzzeit: Notfallmaßnahmen nach [[Atemschutz-Notfall (Mayday)]].

## Digitale Erfassung
Am Einsatzende werden die Daten der Tafel in den **Atemschutz-Nachweis** im System übertragen und dem **Einsatzbericht** zugeordnet. Dazu gehören Gerätenummern, Maskennummern und Einsatzdauer jedes Geräteträgers.

## Tipps
- Tafel gut sichtbar, wettergeschützt und an einem festen Ort.
- Leitstelle bei Großlagen über Anzahl eingesetzter Trupps informieren.
- Siehe auch [[Atemschutz-Einsatzgrundsätze]].
MD],
    ['title' => 'Atemschutz-Notfall (Mayday)', 'category' => 'Atemschutz', 'content' => <<<'MD'
Ein Atemschutznotfall liegt vor, wenn ein Trupp sich nicht mehr selbst retten kann: verletzt, eingeklemmt, orientierungslos, Luftmangel oder Ausfall des Geräts. Jede Sekunde zählt.

## Notfallmeldung
Der betroffene Trupp setzt über Funk ab:
> **„Mayday, Mayday, Mayday – hier [Funkrufname] – [Standort] – [Lage] – Mayday kommen.“**

- Notfallmeldung hat **absoluten Vorrang**, alle anderen Gespräche verstummen.
- Bewegungsmelder/Notsignalgeber aktivieren, Taschenlampe in Richtung Decke.
- Zugeordneter Einheitsführer bestätigt und leitet Maßnahmen ein.

## Maßnahmen der Einsatzleitung
1. **Sicherheitstrupp** sofort einsetzen ([[Sicherheitstrupp und Rückzugsweg]]).
2. Funkverkehr auf einen eigenen Kanal verlegen, Mayday-Kanal nur für den Notfall.
3. Weitere Sicherheitstrupps bilden, Leitstelle informieren, Nachalarmierung.
4. SAMS an der Einsatzstelle in Bereitschaft für die verunfallte Kraft.
5. Brandbekämpfung zum Schutz des Notfallortes fortführen.

## Verhalten des betroffenen Trupps
- Ruhe bewahren, Atmung kontrollieren – Panik erhöht den Luftverbrauch.
- Am Schlauch orientieren (Kupplungsrichtung zeigt den Rückweg).
- Möglichst flach am Boden, Wand oder Fenster suchen.

## Nach dem Notfall
- Einsatz für die betroffenen Kräfte sofort beenden, ärztliche Untersuchung.
- **PSNV-E** anbieten ([[Psychosoziale Notfallversorgung (PSNV-E)]]).
- Beteiligte Geräte sicherstellen, nicht reinigen – Unfalluntersuchung.
- Detaillierter **Einsatzbericht** und Unfallmeldung an die Dienststellenleitung.
MD],
    ['title' => 'Sicherheitstrupp und Rückzugsweg', 'category' => 'Atemschutz', 'content' => <<<'MD'
Der **Sicherheitstrupp** ist die Lebensversicherung der vorgehenden Atemschutztrupps. Er wird bei jedem Atemschutzeinsatz gestellt – ohne Ausnahme.

## Aufgaben des Sicherheitstrupps
- Steht mit angelegtem Atemschutz (Maske noch nicht aufgesetzt) am Zugang zum Gefahrenbereich bereit.
- Hat ein eigenes Rohr bzw. eine Schlauchleitung, Wärmebildkamera, Notfallset (Rettungshaube, Ersatz-Lungenautomat), Bergetuch und Werkzeug.
- Verfolgt den Funkverkehr der eingesetzten Trupps.
- Wird **nicht** für andere Aufgaben eingesetzt.

## Einsatz des Sicherheitstrupps
1. Auf Befehl des Einheitsführers oder bei einer Mayday-Meldung ([[Atemschutz-Notfall (Mayday)]]).
2. Vorgehen entlang der Schlauchleitung des verunfallten Trupps.
3. Lageerkundung, Versorgung mit Atemluft, Rettung aus dem Gefahrenbereich.
4. Sofort muss ein **neuer Sicherheitstrupp** gebildet werden.

## Rückzugsweg
- Die **Schlauchleitung** ist der Rückweg. Sie darf nicht verlegt oder abgekuppelt werden.
- Bei Einsätzen ohne Schlauchleitung (Menschensuche) Feuerwehrleine oder Orientierungsleine verwenden.
- Türen hinter dem Trupp gegen Zufallen sichern.
- Zweiter Rettungsweg über tragbare Leiter oder DLK frühzeitig herstellen.
- Truppführer prägt sich den Weg ein und meldet Standortwechsel.

## Merke
Kein Trupp geht in einen Bereich, aus dem er nicht sicher zurückkommt. Der Rückzug ist kein Versagen, sondern Teil der Taktik. Siehe auch [[Atemschutz-Einsatzgrundsätze]].
MD],
    ['title' => 'Fahrzeugkunde HLF', 'category' => 'Fahrzeuge & Geräte', 'content' => <<<'MD'
Das **Hilfeleistungslöschgruppenfahrzeug (HLF 20)** ist das Standardfahrzeug jeder LSFD-Wache und rückt zu fast allen Einsätzen als erstes aus. Es vereint Brandbekämpfung und technische Hilfe.

## Besatzung
Gruppe **1/8**: Einheitsführer (Gruppenführer), Maschinist, Melder, Angriffstrupp, Wassertrupp, Schlauchtrupp (je Truppführer und Truppmann). Bei Personalmangel als Staffel 1/5 besetzt.

## Beladung (Auswahl)
- **Löschwasser**: ca. 2.000 l, Schaummittel 120 l, Feuerlöschkreiselpumpe FPN 10-2000.
- **Schnellangriff** mit formstabilem Schlauch, C- und B-Schläuche, Verteiler, Strahlrohre.
- 4 Pressluftatmer im Mannschaftsraum, Wärmebildkamera, Überdrucklüfter.
- **Hydraulischer Rettungssatz**: Schere, Spreizer, Rettungszylinder, Stabilisierung.
- Steckleiter, Schiebleiter, Multifunktionsleiter.
- Stromerzeuger, Beleuchtung (Lichtmast), Motorsäge.
- Erste-Hilfe-Ausstattung inkl. AED und Sauerstoff.

## Einsatzwert
Das HLF kann eigenständig einen Wohnungsbrand ([[SOP Wohnungsbrand]]) bekämpfen und einen Verkehrsunfall abarbeiten ([[SOP Verkehrsunfall eingeklemmte Person]]). Für größere Lagen ergänzen DLK, RW und TLF.

## Standorte
- Station 1 Davis: HLF 1/1, HLF 1/2
- Station 7 Rockford Hills: HLF 7/1
- Station Sandy Shores: HLF 3/1 (geländegängig)
- Station Paleto Bay: HLF 4/1

## Hinweise
Fahrzeugcheck zu Schichtbeginn nach Checkliste, Mängel im System melden. Siehe [[Geräteprüfung und Wartung]] und [[Wachen und Ausrückebereiche]].
MD],
    ['title' => 'Fahrzeugkunde Drehleiter', 'category' => 'Fahrzeuge & Geräte', 'content' => <<<'MD'
Die **Drehleiter mit Korb (DLK 23/12)** stellt den zweiten Rettungsweg sicher und dient als Arbeitsplattform in der Höhe. Rettungshöhe 23 m bei 12 m Ausladung.

## Besatzung
Trupp **1/2**: Maschinist (Drehleitermaschinist mit Sonderausbildung), Einheitsführer und ggf. ein Truppmann.

## Einsatzmöglichkeiten
- **Menschenrettung** aus oberen Geschossen bis ca. 8. OG.
- Brandbekämpfung von oben mit **Wenderohr** am Korb.
- Krankentragehalterung für liegenden Patiententransport ([[SOP Tragehilfe für den Rettungsdienst]]).
- Höhenrettung, Absturzsicherung, Ausleuchtung von Einsatzstellen.
- Abseilpunkt für die Höhenrettung ([[SOP Höhenrettung Mount Chiliad]]).

## Aufstellung
- **Anleiterbereich frühzeitig freihalten** – das erste HLF fährt am Objekt vorbei.
- Tragfähiger, ebener Untergrund, Abstützungen auf Unterlegplatten.
- Freileitungen, Bäume, Balkone und Vordächer beachten.
- Neigungsausgleich prüfen, Windgeschwindigkeit über 12 m/s nur eingeschränkt.
- Feuerwehrzufahrten und Aufstellflächen sind in Objektplänen im System markiert ([[Objektpläne und Feuerwehrpläne]]).

## Sicherheit
- Korbbelastung max. 3 Personen bzw. 270 kg.
- Im Korb immer mit Auffanggurt gesichert.
- Leiter nie unbeaufsichtigt ausgefahren lassen.

## Standorte
DLK in Station 1 Davis und Station 7 Rockford Hills. Für Sandy Shores und Paleto Bay wird die DLK aus Los Santos nachalarmiert – Anfahrtszeit beachten, frühzeitig anfordern.
MD],
    ['title' => 'Fahrzeugkunde Rüstwagen', 'category' => 'Fahrzeuge & Geräte', 'content' => <<<'MD'
Der **Rüstwagen (RW)** führt schwere technische Ausrüstung mit, die über die Beladung des HLF hinausgeht. Er ist in Station 1 Davis stationiert und deckt das gesamte Stadtgebiet ab.

## Besatzung
Trupp **1/2**, unterstützt durch die Mannschaft des HLF.

## Beladung (Auswahl)
- Zweiter hydraulischer Rettungssatz mit Hochleistungsschere und Pedalschneider.
- **Hebekissen** (Hochdruck bis 60 t, Niederdruck), Rettungszylinder, Stab-Fast-Stabilisierung.
- **Seilwinde** (Zugkraft ca. 50 kN) und Mehrzweckzug.
- Lichtmast, Stromerzeuger 13 kVA, Beleuchtungssätze.
- Plasmaschneider, Trennschleifer, Motorkettensäge, Bohrhammer.
- Abstützmaterial für Einsturz (Holz, Keile), Bergetuch, Unterbaumaterial.
- Ölbindemittel, Leckabdichtung, Auffangbehälter.

## Typische Einsätze
- Lkw- und Busunfälle auf dem Highway, [[SOP Person unter Fahrzeug]].
- Schwere technische Rettung bei [[SOP Verkehrsunfall eingeklemmte Person]].
- Gebäudeeinsturz und Abstützung ([[SOP Einsturz]]).
- Bergung von Fahrzeugen aus Gräben und Gewässern.
- Arbeitsunfälle im Port of Los Santos.

## Hinweise
- Die Seilwinde nur mit Umlenkrolle und Sicherheitsabstand (Seilrisszone) betreiben.
- Hebekissen nie ohne Unterbau, nie mehr als zwei übereinander.
- Nach jedem Einsatz Hydraulik und Hebekissen prüfen ([[Geräteprüfung und Wartung]]).
MD],
    ['title' => 'Sonderfahrzeuge ELW, GW-G und Wechsellader', 'category' => 'Fahrzeuge & Geräte', 'content' => <<<'MD'
Neben HLF, DLK und RW verfügt das LSFD über Sonderfahrzeuge, die bei besonderen Lagen nachalarmiert werden.

## Einsatzleitwagen (ELW 1 / ELW 2)
- **ELW 1**: Führungsfahrzeug des Zugführers, Funk, Lagekarte, Laptop mit Zugriff auf Objektpläne und Gefahrstoffdatenbank.
- **ELW 2**: rollende Einsatzleitung für Großlagen mit Besprechungsraum, mehreren Funkarbeitsplätzen und Stabsfunktionen.
- Siehe [[Befehlsschema und Einsatzleitung]].

## Gerätewagen Gefahrgut (GW-G)
- Chemikalienschutzanzüge, Messgeräte (Ex/Ox/Tox, Dosisleistung, PID), Abdichtmaterial, Umfüllpumpen (ex-geschützt), Auffangbehälter, Dekontaminationsausrüstung.
- Stationiert in Station 1 Davis, Einsatz bei [[SOP Gefahrgutunfall (GAMS)]] und [[SOP Gasaustritt]].

## Tanklöschfahrzeug (TLF 4000)
- 4.000–5.000 l Wasser, 500 l Schaummittel, Werfer auf dem Dach.
- Hauptfahrzeug bei Vegetationsbränden und an Tankstellen ([[SOP Tankstellenbrand]]).

## Wechsellader (WLF) mit Abrollbehältern (AB)
- **AB-Atemschutz**: Atemluftreserve und Geräte bei Großbränden.
- **AB-Schlauch**: 2.000 m B-Schlauch für lange Wegstrecken.
- **AB-Wasser**: Wasserbad für Elektrofahrzeuge ([[SOP Elektrofahrzeug-Brand]]).
- **AB-Mulde** und **AB-Sonderlöschmittel**.

## Weitere Fahrzeuge
- Rettungsboot im Hafen ([[Rettungsboot und Hafeneinsätze]]).
- Feuerwehrhubschrauber ([[Feuerwehrhubschrauber und Luftrettung]]).
- Mannschaftstransportwagen (MTW) für Personal und Logistik.
MD],
    ['title' => 'Geräteprüfung und Wartung', 'category' => 'Fahrzeuge & Geräte', 'content' => <<<'MD'
Nur geprüfte Geräte sind einsatzbereit. Prüfungen werden im LSFD nach festen Intervallen durchgeführt und im System als **Geräteprüfung** dokumentiert.

## Tägliche Kontrolle (Schichtbeginn)
- Fahrzeugcheck: Kraftstoff, Öl, Reifen, Beleuchtung, Sondersignal, Funk.
- Wassertank und Schaummittel voll.
- Atemschutzgeräte: Flaschendruck, Sichtkontrolle, Einsatzkurzprüfung.
- Akkus von Wärmebildkamera, Funkgeräten, Lampen und Akkurettungsgeräten geladen.

## Monatliche und jährliche Prüfungen
| Gerät | Intervall |
|---|---|
| Pressluftatmer | nach jedem Gebrauch, sonst halbjährlich |
| Atemschutzmasken | nach Gebrauch, sonst halbjährlich |
| Hydraulischer Rettungssatz | jährlich und nach Gebrauch |
| Leitern | jährlich |
| Schläuche | nach jedem Gebrauch (Druckprüfung) |
| Hebekissen | jährlich, Sichtprüfung nach Einsatz |
| Gas-Messgeräte | Funktionstest vor Einsatz, Kalibrierung halbjährlich |
| Drehleiter | jährlich durch Sachkundigen |

## Ablauf der Dokumentation
1. Gerät im System auswählen (Inventarnummer).
2. Prüfart, Ergebnis und Prüfer eintragen.
3. Mängel: Gerät **sofort außer Dienst** stellen, Kennzeichnung „Defekt“, Meldung an Gerätewart.
4. Ersatzgerät aus dem Lager verlasten.

## Verantwortlichkeiten
- **Gerätewart** jeder Wache koordiniert Prüfungen.
- Atemschutzgerätewart ist für Masken, Lungenautomaten und Flaschen zuständig.
- Jede Einsatzkraft ist verpflichtet, festgestellte Mängel zu melden.

Siehe auch [[Atemschutz-Einsatzgrundsätze]] und [[Wachalltag und Schichtdienst]].
MD],
    ['title' => 'SOP Wasserrettung', 'category' => 'Wasserrettung & Höhenrettung', 'content' => <<<'MD'
Wasserrettungseinsätze reichen von Schwimmern in Not am Vespucci Beach über Fahrzeuge im Hafenbecken bis zu Booten in Seenot auf der Alamo Sea. Zeit ist entscheidend: Nach wenigen Minuten unter Wasser drohen schwere Hirnschäden.

## Alarmierung
HLF, Rettungsboot, ggf. Feuerwehrhubschrauber, SAMS. Bei Personen unter Wasser **Taucher** der Feuerwehr bzw. Polizei. Strandabschnitte: Zusammenarbeit mit Lifeguards.

## Grundsätze
- **Eigenschutz**: Keine Rettung ohne Rettungsweste und Sicherung. Strömung, Kälte und Brandung unterschätzen viele.
- **Reihenfolge**: Ansprechen – Werfen (Rettungsring, Wurfsack) – Reichen (Stange, Leiter) – Rudern/Boot – Schwimmen als letzte Option.
- Letzte Sichtposition der Person **markieren** (Peilung an Landmarken), Zeitpunkt merken.

## Ablauf
1. Rettungsboot zu Wasser, siehe [[Rettungsboot und Hafeneinsätze]].
2. Person sichern, aus dem Wasser holen – möglichst horizontal (Bergeschlaufe, Netz).
3. Übergabe an SAMS, bei Ertrinken sofort Reanimation.
4. Fahrzeug im Wasser: Personen in der Regel nur über Taucher rettbar, Fahrzeug nicht ohne Sicherung betreten.

## Eis- und Flussrettung
Im Los Santos River (Kanal) bei Starkregen extreme Strömung – nur mit Leinensicherung vom Ufer, Strömungsretter einsetzen.

## Hubschrauber
Bei großen Entfernungen (Offshore, Alamo Sea) Feuerwehrhubschrauber mit Rettungswinde, siehe [[Feuerwehrhubschrauber und Luftrettung]].

## Dokumentation
Uhrzeiten (Alarm, Ankunft, Rettung, Übergabe), Wassertemperatur und Strömungsverhältnisse im **Einsatzbericht** erfassen.
MD],
    ['title' => 'Rettungsboot und Hafeneinsätze', 'category' => 'Wasserrettung & Höhenrettung', 'content' => <<<'MD'
Das LSFD betreibt ein **Rettungsboot (RTB)** am Port of Los Santos und ein kleineres Boot am Alamo Sea (Station Sandy Shores). Zusätzlich steht ein Feuerlöschboot im Hafen bereit.

## Rettungsboot (RTB 2)
- Besatzung: Bootsführer + 2 Rettungsschwimmer.
- Ausrüstung: Rettungswesten, Wurfsäcke, Bergenetz, Spineboard, Erste-Hilfe-Ausstattung inkl. AED, Ölsperre, Sonar.
- Höchstgeschwindigkeit ca. 35 kn, Einsatz auch bei Seegang bis 4 Bft.

## Feuerlöschboot
- Wasserwerfer mit Seewasserpumpe, Förderleistung über 10.000 l/min.
- Einsatz bei Schiffsbränden, Bränden an Kaianlagen und Lagerhallen in Elysian Island ([[SOP Lagerhallenbrand]]).
- Kann als **Pumpe für die Löschwasserversorgung** an Land dienen ([[Wasserversorgung und Hydranten]]).

## Typische Hafeneinsätze
- Person über Bord, Personen im Hafenbecken.
- Schiffsbrand: Rücksprache mit Kapitän/Hafenmeister, Gefahrgut-Ladung (Container mit Gefahrzetteln) – [[SOP Gefahrgutunfall (GAMS)]].
- Umweltschäden: Ölsperre ausbringen ([[SOP Ölspur]]).
- Unterstützung der Polizei bei Suchaktionen.

## Sicherheit
- Jeder an Bord trägt Rettungsweste.
- Funkverbindung zur Leitstelle und zum Hafenmeister.
- Bei Nacht: Suchscheinwerfer, Wärmebildkamera.

## Wartung
Boote werden nach jeder Fahrt gespült (Salzwasser), Motoren monatlich probegefahren. Prüfungen im System unter **Geräteprüfung** dokumentieren. Siehe auch [[SOP Wasserrettung]].
MD],
    ['title' => 'SOP Höhenrettung Mount Chiliad', 'category' => 'Wasserrettung & Höhenrettung', 'content' => <<<'MD'
Der **Mount Chiliad** ist mit Wanderwegen, Seilbahn, Mountainbike-Trails und steilen Felswänden ein Schwerpunkt für Höhen- und Tiefenrettung. Ähnliche Einsätze gibt es an Klippen bei Paleto Cove, am Raton Canyon und an Kränen auf Baustellen in Downtown.

## Alarmierung
Höhenrettungsgruppe (Station Paleto Bay und Station 1), HLF, Feuerwehrhubschrauber, SAMS, BCSO.

## Lageerkundung
- Standort der Person (GPS-Koordinaten über 911-Anruf, Seilbahnstation, Wanderweg).
- Zugang: zu Fuß, Geländefahrzeug, Hubschrauber?
- Verletzungsmuster von SAMS oder Anrufer.
- Wetter, Tageslicht, Steinschlaggefahr.

## Rettungstechniken
1. **Absturzsicherung** für alle Einsatzkräfte im absturzgefährdeten Bereich (> 3 m oder Kante).
2. Anschlagpunkte: Felsen, Bäume, Fahrzeuge, mobile Dreibeine.
3. **Abseilen** eines Höhenretters zum Patienten, Sicherung des Patienten mit Rettungsdreieck oder Schleifkorbtrage.
4. Aufzug oder Ablassen mit **Flaschenzugsystem** und redundanter Sicherung (Zwei-Seil-Technik).
5. Alternativ: **Windenrettung** durch den Hubschrauber ([[Feuerwehrhubschrauber und Luftrettung]]).

## Seilbahn
Bei Störung der Chiliad-Seilbahn: Betreiber kontaktieren, Bergung über Notfahrt oder Abseilen aus der Gondel. Kommunikation mit Fahrgästen über Lautsprecher.

## Sicherheit
- Nur ausgebildete Höhenretter am Seil.
- Vier-Augen-Prinzip bei jedem Anschlag und Knoten.
- Ausrüstung nach Einsatz prüfen ([[Geräteprüfung und Wartung]]).

## Dokumentation
Gemeinsamer **Einsatzbericht** mit SAMS, Koordinaten und eingesetzte Technik festhalten.
MD],
    ['title' => 'Feuerwehrhubschrauber und Luftrettung', 'category' => 'Wasserrettung & Höhenrettung', 'content' => <<<'MD'
Der **Feuerwehrhubschrauber „Fire 1“** des LSFD ist am LSIA stationiert und wird für Vegetationsbrände, Wasser- und Höhenrettung sowie Erkundungsflüge eingesetzt. Den Patiententransport übernimmt der Rettungshubschrauber von SAMS.

## Einsatzmöglichkeiten
- **Brandbekämpfung aus der Luft** mit Außenlastbehälter (ca. 1.000–2.000 l), Wasseraufnahme aus Alamo Sea, Land Act Reservoir, Pazifik.
- **Windenrettung** bei Wasserrettung und in unzugänglichem Gelände.
- **Lageerkundung** bei Großbränden, Waldbränden und Großschadenslagen, Livebild an den ELW.
- Transport von Höhenrettern und Material zu abgelegenen Einsatzstellen.

## Anforderung
Über die Leitstelle durch den Einsatzleiter. Angaben: Einsatzzweck, Koordinaten, Landeplatz, Wasserentnahmestelle, Hindernisse (Freileitungen).

## Landeplatz
- Mindestens 30 × 30 m, frei von losen Gegenständen, Hindernisse markieren.
- Einweiser mit Gehörschutz und Schutzbrille, Annäherung nur von vorne im Sichtbereich des Piloten.
- Bei Nacht: Ausleuchtung ohne Blenden des Piloten.

## Zusammenarbeit bei Waldbränden
- Lufteinsätze werden durch einen **Luftkoordinator** im ELW koordiniert.
- Bodenkräfte vor Abwurf warnen und aus dem Abwurfbereich nehmen ([[SOP Waldbrand / Vegetationsbrand]]).
- Polizeihubschrauber und Hubschrauber von Medien über Leitstelle abstimmen.

## Fort Zancudo
Überflug des militärischen Sperrgebiets Fort Zancudo nur nach Freigabe.

## Siehe auch
[[SOP Wasserrettung]], [[SOP Höhenrettung Mount Chiliad]].
MD],
    ['title' => 'Brandschau – Ablauf', 'category' => 'Vorbeugender Brandschutz', 'content' => <<<'MD'
Die **Brandschau** ist eine Begehung baulicher Anlagen zur Feststellung von Brandschutzmängeln. Sie wird vom Sachgebiet Vorbeugender Brandschutz des LSFD durchgeführt und im System als **Brandschaubericht** dokumentiert.

## Betroffene Objekte
- Versammlungsstätten (Clubs in Vinewood, Theater, Diamond Casino).
- Hotels, Krankenhäuser (Pillbox Hill Medical Center), Schulen, Pflegeheime.
- Industrie und Lager (Port of Los Santos, Elysian Island), Tankstellen.
- Hochhäuser in Downtown.

## Intervalle
Je nach Gefährdung alle **1 bis 5 Jahre**, außerplanmäßig nach Beschwerden, Bränden oder Nutzungsänderungen.

## Ablauf
1. **Vorbereitung**: Objektakte und letzten Brandschaubericht im System sichten, Termin mit dem Betreiber vereinbaren.
2. **Eingangsgespräch**: Betreiber, Brandschutzbeauftragter, ggf. Behördenvertreter.
3. **Begehung**: Rettungswege, Notausgänge, Brandschutztüren, Feuerlöscher, Brandmeldeanlage, Feuerwehrzufahrt, Rauchabzüge, Lagerung brennbarer Stoffe.
4. **Abschlussgespräch**: Mängel erläutern, Fristen setzen.
5. **Brandschaubericht** im System anlegen: Mängelliste mit Kategorie (gering / erheblich / gefährlich), Fotos, Fristen.
6. **Nachschau** nach Fristablauf.

## Mängelkategorien
- **Gering**: Hinweis, Behebung bei Gelegenheit.
- **Erheblich**: Behebung innerhalb der Frist, Nachschau erforderlich.
- **Gefährlich**: Sofortmaßnahme, ggf. Nutzungsuntersagung in Abstimmung mit dem DOJ.

## Siehe auch
[[Brandschutzbescheinigung]], [[Rauchwarnmelder und Brandschutzauflagen]], [[Objektpläne und Feuerwehrpläne]].
MD],
    ['title' => 'Brandschutzbescheinigung', 'category' => 'Vorbeugender Brandschutz', 'content' => <<<'MD'
Die **Brandschutzbescheinigung** des LSFD bestätigt, dass ein Objekt zum Zeitpunkt der Prüfung den geltenden Brandschutzvorschriften entspricht. Sie ist Voraussetzung für viele Gewerbe- und Veranstaltungsgenehmigungen in Los Santos und Blaine County.

## Wann wird sie benötigt?
- Eröffnung oder Übernahme von Gaststätten, Clubs, Bars und Geschäften.
- Veranstaltungen mit mehr als 200 Personen (Konzerte, Rennen, Märkte, z. B. am Del Perro Pier).
- Lagerung von Gefahrstoffen oder Druckgasbehältern.
- Werkstätten, Tankstellen, Tuningbetriebe.

## Antrag
1. Betreiber stellt Antrag über das System oder bei einer Wache.
2. Unterlagen: Grundriss mit Rettungswegen, Nutzungsbeschreibung, Personenzahl, Brandschutzeinrichtungen.
3. Termin für die Prüfung ([[Brandschau – Ablauf]]).

## Prüfung
- Mindestens **zwei unabhängige Rettungswege**, beleuchtet und gekennzeichnet.
- Feuerlöscher in ausreichender Zahl, geprüft (Prüfplakette).
- Rauchwarnmelder bzw. Brandmeldeanlage ([[Rauchwarnmelder und Brandschutzauflagen]]).
- Freie Feuerwehrzufahrt und Aufstellflächen.
- Elektrische Anlagen ohne offensichtliche Mängel.

## Ausstellung
- Bescheinigung im System als Dokument mit Gültigkeitsdatum (in der Regel 1 Jahr, bei Veranstaltungen nur für den Veranstaltungszeitraum).
- Auflagen werden im Dokument aufgeführt.
- Bei Ablehnung: schriftliche Begründung und Mängelliste.

## Widerruf
Bei schwerwiegenden Verstößen kann die Bescheinigung widerrufen werden. Die Information geht an das DOJ und ggf. die Polizei zur Durchsetzung ([[Zusammenarbeit mit dem DOJ]]).
MD],
    ['title' => 'Rauchwarnmelder und Brandschutzauflagen', 'category' => 'Vorbeugender Brandschutz', 'content' => <<<'MD'
Rauchwarnmelder retten Leben: Die meisten Brandtoten sterben nicht an Flammen, sondern im Schlaf an einer **Rauchgasvergiftung**. Im Staat San Andreas gelten verbindliche Auflagen, deren Einhaltung das LSFD bei Brandschauen prüft.

## Rauchwarnmelderpflicht
- In allen Wohnungen: **Schlafzimmer, Kinderzimmer und Flure**, die als Rettungsweg dienen.
- Montage an der Decke, mittig im Raum, mindestens 50 cm von Wänden.
- Jährliche Funktionsprüfung durch Eigentümer oder Mieter.
- Empfehlung: Melder mit 10-Jahres-Batterie, in großen Häusern Vernetzung.

## Gewerbliche Auflagen
- **Feuerlöscher**: Anzahl nach Grundfläche und Brandgefährdung, Prüfung alle 2 Jahre.
- **Notausgänge** jederzeit frei und von innen ohne Schlüssel zu öffnen.
- **Sicherheitsbeleuchtung** und Fluchtwegkennzeichnung.
- **Brandschutztüren** schließen selbsttätig, keine Keile.
- Brandmeldeanlagen in Versammlungsstätten, Hotels und Hochhäusern.
- Brandschutzordnung und Unterweisung der Mitarbeitenden.

## Typische Mängel in Los Santos
- Verstellte Notausgänge in Clubs und Bars.
- Fehlende Rauchmelder in Mietwohnungen in Davis und Strawberry.
- Unsachgemäße Lagerung von Gasflaschen in Werkstätten in Sandy Shores.
- Überlastete Mehrfachsteckdosen.

## Einsatzrelevanz
Piepende Rauchmelder sind ein häufiges Einsatzstichwort, oft mit Türöffnung ([[SOP Türöffnung]]). Jede festgestellte Rauchmelderauslösung mit Brand wird im **Einsatzbericht** vermerkt, um die Wirksamkeit der Auflagen zu belegen.

## Siehe auch
[[Brandschau – Ablauf]], [[Brandschutzbescheinigung]].
MD],
    ['title' => 'Objektpläne und Feuerwehrpläne', 'category' => 'Vorbeugender Brandschutz', 'content' => <<<'MD'
Für besondere Objekte führt das LSFD **Feuerwehrpläne** im System. Sie geben ersteintreffenden Kräften schnell die wichtigsten Informationen und sind auf den Tablets der Fahrzeuge und im ELW abrufbar.

## Objekte mit Feuerwehrplan (Auswahl)
- LSIA (Terminals, Hangars, Tanklager)
- Port of Los Santos und Lagerhallen Elysian Island
- Humane Labs and Research ([[SOP CBRN-Einsatz Humane Labs]])
- Pillbox Hill Medical Center und Sandy Shores Medical Center
- Hochhäuser in Downtown (Maze Bank Tower, FIB Building)
- Diamond Casino, Del Perro Pier, Vinewood Bowl
- Tanklager und große Tankstellen (Ron, Globe Oil)
- Ölraffinerie Palmer-Taylor Power Station

## Inhalt eines Feuerwehrplans
1. **Übersichtsplan**: Zufahrten, Aufstellflächen, Hydranten, Sammelplätze.
2. **Geschosspläne**: Treppenräume, Aufzüge, Brandabschnitte, Steigleitungen.
3. **Sonderpläne**: Gefahrstofflager, Gasabsperrungen, Elektro-Hauptschalter, Löschanlagen.
4. **Textteil**: Ansprechpartner (24 h erreichbar), Besonderheiten, Personenzahl.

## Erstellung und Pflege
- Erstellung durch den Betreiber nach Vorgaben des LSFD, Prüfung durch den Vorbeugenden Brandschutz.
- Aktualisierung bei baulichen Änderungen, spätestens alle 2 Jahre.
- Abgleich bei jeder Brandschau ([[Brandschau – Ablauf]]).

## Nutzung im Einsatz
- Einheitsführer ruft den Plan bereits auf der Anfahrt ab.
- Laufkarten der Brandmeldeanlage ergänzen die Pläne ([[SOP Brand in Hochhaus]]).
- Fehler oder Abweichungen im Plan nach dem Einsatz im **Einsatzbericht** melden, damit der Plan korrigiert wird.
MD],
    ['title' => 'Befehlsschema und Einsatzleitung', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Einsätze des LSFD werden nach dem **Führungsvorgang** der FwDV 3 geführt: Lagefeststellung – Planung – Befehlsgebung – Kontrolle (Regelkreis). Klare Befehle verhindern Missverständnisse.

## Einsatzleitung
- Der **erste eintreffende Einheitsführer** übernimmt die Einsatzleitung, bis ein höherer Führungsdienst eintrifft und die Leitung **ausdrücklich** übernimmt.
- Die Übernahme wird der Leitstelle gemeldet („Einsatzleitung übernimmt Zugführer 1“).
- Der Einsatzleiter trägt eine **Kennzeichnungsweste**.

## Führungsstufen
| Stufe | Führung | Einheiten |
|---|---|---|
| A | Gruppen-/Staffelführer | 1 Fahrzeug |
| B | Zugführer mit ELW 1 | Zug (HLF, DLK, ELW) |
| C | Verbandsführer mit Führungsgruppe | mehrere Züge |
| D | Einsatzleitung mit Stab (ELW 2) | Großlagen |

## Befehlsschema (Einheit – Auftrag – Mittel – Ziel – Weg)
> **„Wassertrupp – zur Menschenrettung – mit C-Rohr – in die Wohnung 2. OG links – über den Treppenraum – vor!“**

- Bei der Gruppe nach FwDV 3: Wasserentnahmestelle, Lage des Verteilers, Einheit, Auftrag, Mittel, Ziel, Weg.
- Befehle werden **wiederholt** (Befehlsquittung).
- Rückmeldung nach Ausführung oder bei Problemen.

## Lageerkundung
Erkundung umfasst **Gefahren** (Merkhilfe AAAAC-EEEE: Atemgifte, Angstreaktion, Ausbreitung, Atomare Strahlung, Chemische Stoffe, Erkrankung/Verletzung, Explosion, Elektrizität, Einsturz) sowie Schadensobjekt, Personen und Umgebung.

## Siehe auch
[[Abschnittsbildung bei Großlagen]], [[Lagemeldung und Rückmeldung]], [[Alarm- und Ausrückeordnung]].
MD],
    ['title' => 'Abschnittsbildung bei Großlagen', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Ab einer bestimmten Größe kann ein Einsatzleiter nicht mehr alle Einheiten direkt führen. Die **Führungsspanne** liegt bei 2 bis 5 unterstellten Einheiten. Darüber werden **Einsatzabschnitte (EA)** gebildet.

## Arten der Abschnittsbildung
- **Räumlich**: Abschnitt Nord, Süd, Gebäude A, Geschoss 10–15 ([[SOP Brand in Hochhaus]]).
- **Funktional**: Abschnitt Wasserversorgung, Abschnitt Atemschutz, Abschnitt Patientenablage, Abschnitt Bereitstellungsraum.
- **Kombiniert**: bei Großschadenslagen wie Waldbränden in den Vinewood Hills oder Hafenbränden.

## Typische Abschnitte
1. **Brandbekämpfung / Menschenrettung** (ggf. mehrere)
2. **Wasserversorgung** ([[Wasserversorgung und Hydranten]])
3. **Bereitstellungsraum** – nachrückende Einheiten melden sich dort
4. **Atemschutzsammelplatz** mit zentraler Atemschutzüberwachung ([[Atemschutzüberwachung]])
5. **Rettungsdienst** – geführt durch SAMS (Organisatorischer Leiter, LNA), siehe [[MANV – Aufgaben der Feuerwehr]]
6. **Polizei** – Absperrung, Verkehr, Ermittlung

## Führung der Abschnitte
- Jeder Abschnitt hat einen **Abschnittsleiter** mit eigenem Funkkanal.
- Abschnittsleiter melden regelmäßig an die Einsatzleitung (Lage, Kräftebedarf).
- Die Einsatzleitung führt eine Lagekarte im ELW.

## Bereitstellungsraum
- Wird außerhalb des Gefahrenbereichs mit guter Zufahrt eingerichtet (z. B. Parkplatz Maze Bank Arena).
- Fahrzeuge melden sich beim Leiter Bereitstellungsraum, nicht direkt an der Einsatzstelle.

## Dokumentation
Abschnittsgliederung, Abschnittsleiter und Zeiten werden im **Einsatzbericht** aufgeführt, bei gemeinsamen Lagen mit SAMS und Polizei im gemeinsamen Bericht. Siehe [[Befehlsschema und Einsatzleitung]].
MD],
    ['title' => 'Alarm- und Ausrückeordnung', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Die **Alarm- und Ausrückeordnung (AAO)** legt fest, welche Fahrzeuge bei welchem Einsatzstichwort alarmiert werden. Die Leitstelle alarmiert automatisch nach AAO, der Einsatzleiter kann jederzeit nachfordern.

## Einsatzstichworte (Auswahl)
| Stichwort | Bedeutung | Alarmierte Kräfte |
|---|---|---|
| B1 | Kleinbrand (Müll, Pkw) | 1 HLF |
| B2 | Mittelbrand (Wohnung, Keller) | HLF, DLK, ELW |
| B3 | Großbrand (Lagerhalle, Hochhaus) | 2–3 HLF, DLK, TLF, RW, ELW, Führungsdienst |
| B-Wald | Vegetationsbrand | 2 TLF, HLF, ELW, ggf. Hubschrauber |
| TH1 | Kleine Hilfeleistung (Ölspur, Tür) | 1 HLF |
| TH-VU-P | VU mit eingeklemmter Person | HLF, RW, ELW + SAMS |
| ABC1/2 | Gefahrgut / Gasaustritt | HLF, GW-G, ELW |
| W-Rett | Wasserrettung | HLF, RTB, ggf. Hubschrauber |
| H-Rett | Höhenrettung | HLF, DLK, Höhenrettungsgruppe |
| LSIA | Luftfahrzeugunfall | Großalarm ([[SOP Flugzeugunfall LSIA]]) |

## Nächstes geeignetes Fahrzeug
Es rückt immer das **nächste freie und geeignete** Fahrzeug aus – unabhängig von der Wachzugehörigkeit. Deshalb ist der korrekte FMS-Status entscheidend ([[Funkdisziplin und FMS-Status]]).

## Ländlicher Bereich
In Blaine County (Sandy Shores, Grapeseed, Paleto Bay) sind Anfahrtswege lang. Bei B2 und höher wird automatisch die DLK aus Los Santos mitalarmiert.

## Nachalarmierung
- Stichwort erhöhen („Erhöhung auf B3“).
- Gezielt Sonderfahrzeuge anfordern ([[Sonderfahrzeuge ELW, GW-G und Wechsellader]]).

Siehe auch [[Wachen und Ausrückebereiche]] und [[Notrufabfrage 911 und Alarmierung]].
MD],
    ['title' => 'Wachen und Ausrückebereiche', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Das LSFD betreibt vier Feuerwachen, die gemeinsam das Stadtgebiet von Los Santos und Blaine County abdecken. Ziel ist eine **Hilfsfrist** von 8 Minuten in der Stadt und 15 Minuten im ländlichen Raum.

## Station 1 – Davis
- **Hauptwache** und Sitz der Dienststellenleitung.
- Fahrzeuge: 2 HLF, DLK, RW, ELW 1, ELW 2, GW-G, TLF, Wechsellader mit Abrollbehältern.
- Ausrückebereich: South Los Santos, Downtown, Port of Los Santos, Elysian Island, LSIA (gemeinsam mit Flughafenfeuerwehr).

## Station 7 – Rockford Hills
- Fahrzeuge: HLF, DLK, TLF.
- Ausrückebereich: Rockford Hills, Vinewood, Vinewood Hills, Del Perro, Vespucci, Richman.
- Schwerpunkt: Villen in Hanglage, Vegetationsbrände, Hochhäuser in Del Perro.

## Station Sandy Shores
- Fahrzeuge: HLF (geländegängig), TLF, Rettungsboot Alamo Sea.
- Ausrückebereich: Sandy Shores, Grand Senora Desert, Harmony, Route 68, Sandy Shores Airfield.
- Schwerpunkt: Vegetationsbrände, Verkehrsunfälle, Wasserrettung Alamo Sea.

## Station Paleto Bay
- Fahrzeuge: HLF, TLF, Höhenrettungsgruppe.
- Ausrückebereich: Paleto Bay, Grapeseed, Mount Chiliad, Paleto Forest, Great Ocean Highway Nord.
- Schwerpunkt: Höhenrettung ([[SOP Höhenrettung Mount Chiliad]]), Waldbrände, Humane Labs.

## Sonderstandorte
- Feuerwehrhubschrauber und Rettungsboot am LSIA bzw. Port of Los Santos.
- Fort Zancudo: militärische Feuerwehr, Unterstützung nur auf Anforderung.

## Siehe auch
[[Alarm- und Ausrückeordnung]], [[Wachalltag und Schichtdienst]], [[Fahrzeugkunde HLF]].
MD],
    ['title' => 'Dienstgrade und Laufbahn LSFD', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Das LSFD gliedert sich in Laufbahnen, die sich an der deutschen Berufsfeuerwehr orientieren. Jeder Dienstgrad ist mit Ausbildungsvoraussetzungen und Führungsaufgaben verbunden.

## Mannschaftslaufbahn
| Dienstgrad | Funktion | Voraussetzung |
|---|---|---|
| Anwärter / Probationary Firefighter | Ausbildung, Einsatz unter Aufsicht | Einstellung, Grundausbildung |
| Feuerwehrmann/-frau | Truppmann | Abschluss Grundausbildung, Atemschutz |
| Oberfeuerwehrmann/-frau | erfahrener Truppmann, Maschinist | Maschinistenlehrgang |
| Hauptfeuerwehrmann/-frau | Truppführer | Truppführerlehrgang, 2 Jahre Dienst |

## Gruppenführerlaufbahn
- **Brandmeister**: Gruppenführer HLF, Einheitsführer.
- **Oberbrandmeister**: Wachabteilungsführer-Stellvertreter.
- **Hauptbrandmeister**: Wachabteilungsführer, Ausbilder.

## Zugführer- und Leitungslaufbahn
- **Brandinspektor / Brandoberinspektor**: Zugführer, Führungsdienst B.
- **Brandamtmann / Brandamtsrat**: Wachleiter, Verbandsführer.
- **Brandrat / Branddirektor**: Abteilungsleitung, Einsatzleiter Großlagen.
- **Fire Chief (Leiter der Feuerwehr)**: Dienststellenleitung LSFD.

## Zusatzqualifikationen
Drehleitermaschinist, Höhenretter, Rettungsschwimmer, Bootsführer, Gefahrgut/CBRN, Atemschutzgerätewart, Brandschutzprüfer, PSNV-Peer, Notfallsanitäter (Doppelqualifikation mit SAMS).

## Beförderung
- Beförderungen erfolgen durch die Dienststellenleitung nach Lehrgang, Bewährung und Beurteilung.
- Lehrgänge und Qualifikationen werden in der Personalakte im System hinterlegt.
- Siehe auch [[Wachalltag und Schichtdienst]].
MD],
    ['title' => 'Wachalltag und Schichtdienst', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Das LSFD arbeitet im **24-Stunden-Schichtdienst** in drei Wachabteilungen. Ein typischer Wachtag folgt einem festen Rhythmus, unterbrochen durch Einsätze.

## Tagesablauf (Beispiel Station 1 Davis)
| Uhrzeit | Tätigkeit |
|---|---|
| 07:30 | Dienstantritt, Antreten, Einteilung der Funktionen |
| 07:45 | Fahrzeug- und Geräteübernahme ([[Geräteprüfung und Wartung]]) |
| 09:00 | Dienstsport |
| 10:00 | Ausbildung / Übung (Theorie oder Praxis) |
| 12:00 | Mittagessen |
| 13:00 | Werkstattdienst, Brandschau, Objektbegehungen |
| 16:00 | Wachdienst, Fahrzeugpflege |
| 18:00 | Abendessen, Bereitschaftszeit |
| 22:00 | Ruhezeit (einsatzbereit) |
| 07:30 | Übergabe an die nächste Wachabteilung |

## Funktionen
- Einteilung auf Fahrzeuge und Funktionen (Gruppenführer, Maschinist, Angriffstrupp …) erfolgt durch den Wachabteilungsführer.
- Die Einteilung wird im System erfasst, damit die Leitstelle die Besatzungsstärke kennt.

## Ausbildung
- Mindestens **monatliche Atemschutzübung**, jährliche Belastungsübung ([[Fitness und Atemschutztauglichkeit]]).
- Regelmäßige Übungen an Objekten wie dem Port of Los Santos oder Humane Labs.
- Gemeinsame Übungen mit SAMS und Polizei.

## Verhalten auf der Wache
- Einsatzkleidung griffbereit, Ausrückzeit tagsüber 60 s, nachts 120 s.
- FMS-Status 2 auf der Wache, Status 1 bei Fahrten ([[Funkdisziplin und FMS-Status]]).
- Schwarz-Weiß-Trennung in den Fahrzeughallen ([[Hygiene nach dem Einsatz (Schwarz-Weiß-Trennung)]]).

## Übergabe
Offene Mängel, laufende Einsätze und Besonderheiten werden schriftlich im Wachbuch des Systems übergeben.
MD],
    ['title' => 'Einsatznachbereitung und Einsatzbericht', 'category' => 'Organisation & Dienst', 'content' => <<<'MD'
Nach jedem Einsatz ist das Fahrzeug wieder einsatzbereit zu machen und der Einsatz im System zu dokumentieren. Der **Einsatzbericht** ist ein rechtlich relevantes Dokument und kann vom DOJ als Beweismittel angefordert werden.

## Wiederherstellung der Einsatzbereitschaft
1. Geräte reinigen, verbrauchtes Material ersetzen, Atemschutzgeräte tauschen.
2. Wasser- und Schaummitteltank auffüllen, tanken.
3. Defekte Geräte melden ([[Geräteprüfung und Wartung]]).
4. Status 2 an die Leitstelle melden.

## Einsatzbericht (Dokumentart „Einsatzbericht“)
Pflichtangaben:
- Einsatznummer, Stichwort, Einsatzort, Alarmzeit, Ankunftszeit, Einsatzende.
- Eingesetzte Fahrzeuge und Besatzungen.
- Lage beim Eintreffen, durchgeführte Maßnahmen in chronologischer Reihenfolge.
- Gerettete und verletzte Personen (Übergabe an SAMS mit Uhrzeit).
- Eingesetzte Atemschutztrupps (Verknüpfung mit dem **Atemschutz-Nachweis**).
- Schäden, Verbrauchsmaterial, Wasserverbrauch.
- Übergabe der Einsatzstelle (an Eigentümer, Polizei).
- Besonderheiten: Unfälle von Einsatzkräften, Verdacht auf Brandstiftung.

## Gemeinsame Einsatzberichte
Bei Lagen mit SAMS und Polizei kann ein **gemeinsamer Einsatzbericht** erstellt werden, in dem jede Organisation ihre Abschnitte ergänzt. Siehe [[Zusammenarbeit mit SAMS]] und [[Zusammenarbeit mit der Polizei]].

## Einsatznachbesprechung
- Kurze Nachbesprechung nach jedem größeren Einsatz: Was lief gut, was verbessern?
- Belastende Einsätze: PSNV anbieten ([[Psychosoziale Notfallversorgung (PSNV-E)]]).

## Fristen
Einsatzberichte sind **bis Schichtende** fertigzustellen und vom Einheitsführer freizugeben.
MD],
    ['title' => 'Funkdisziplin und FMS-Status', 'category' => 'Funk & Leitstelle', 'content' => <<<'MD'
Der Funkverkehr ist das Nervensystem des Einsatzes. Kurze, klare Meldungen und korrekte Statusmeldungen über das **Funkmeldesystem (FMS)** sind Pflicht.

## FMS-Status
| Status | Bedeutung |
|---|---|
| **1** | Einsatzbereit über Funk (unterwegs) |
| **2** | Einsatzbereit auf der Wache |
| **3** | Einsatz übernommen / Anfahrt |
| **4** | Am Einsatzort eingetroffen |
| **5** | Sprechwunsch |
| **6** | Nicht einsatzbereit (Werkstatt, Personalmangel) |
| **7** | Patient aufgenommen (beim LSFD selten) |
| **8** | Am Zielort (z. B. Krankenhaus) |
| **0** | Dringender Sprechwunsch / Notruf – höchste Priorität |

## Funkdisziplin
- **Erst denken, dann drücken, dann sprechen.**
- Gesprächsaufbau: „Leitstelle von Florian Los Santos 1/HLF/1, kommen.“ – „Hier Leitstelle, kommen.“
- Keine Klarnamen von Patienten, keine privaten Gespräche.
- Status 0 nur in echter Gefahr (Bedrohung, Unfall von Einsatzkräften). Mayday siehe [[Atemschutz-Notfall (Mayday)]].
- Bei Großlagen eigene Einsatzkanäle je Abschnitt.

## Funkrufnamen
„Florian Los Santos“ + Wache/Fahrzeugtyp/Nummer, z. B. *Florian Los Santos 7/DLK/1* oder *Florian Blaine 3/HLF/1* (Sandy Shores).

## Häufige Fehler
- Status 2 vergessen nach Rückkehr – Leitstelle hält Fahrzeug für unterwegs.
- Status 4 zu spät – Zeitstempel im **Einsatzbericht** werden ungenau.
- Lange Lagemeldungen ohne Struktur, siehe [[Lagemeldung und Rückmeldung]].
MD],
    ['title' => 'Notrufabfrage 911 und Alarmierung', 'category' => 'Funk & Leitstelle', 'content' => <<<'MD'
Alle Notrufe für Feuerwehr, Rettungsdienst und Polizei laufen über die Notrufnummer **911**. Die gemeinsame Leitstelle von LSFD und SAMS übernimmt Feuer- und Rettungsdienstnotrufe, Polizeinotrufe werden an LSPD/BCSO weitergeleitet.

## Strukturierte Abfrage (5 W)
1. **Wo** ist der Notfallort? (Adresse, Postleitzahl-Bereich, markante Punkte, GPS)
2. **Was** ist passiert?
3. **Wie viele** Betroffene / Verletzte?
4. **Welche** Verletzungen oder Gefahren (Feuer, Rauch, Gas, eingeklemmt)?
5. **Warten** auf Rückfragen – der Anrufer legt nicht auf.

## Alarmierung
- Disponent wählt das Einsatzstichwort, das System schlägt die Fahrzeuge nach AAO vor ([[Alarm- und Ausrückeordnung]]).
- Alarmierung über Funkmeldeempfänger und Wachalarm, Einsatzdepesche mit Ort, Stichwort und Objektplan-Verknüpfung auf das Fahrzeugtablet.
- Parallelalarmierung von SAMS und Polizei bei entsprechenden Stichworten.

## Telefonische Anleitung
Die Leitstelle gibt Anrufern Anweisungen bis zum Eintreffen:
- Bei Brand: Wohnung verlassen, Tür schließen, nicht den Aufzug nutzen; bei verrauchtem Treppenraum in der Wohnung bleiben und am Fenster bemerkbar machen.
- Bei Reanimation: Telefonreanimation.
- Bei Gasgeruch: kein Licht, keine Klingel, Gebäude verlassen.

## Rückmeldungen
Die Leitstelle erwartet Statusmeldungen und eine **erste Lagemeldung** nach Eintreffen ([[Lagemeldung und Rückmeldung]]). Alle Zeitstempel übernimmt das System in den **Einsatzbericht**.
MD],
    ['title' => 'Lagemeldung und Rückmeldung', 'category' => 'Funk & Leitstelle', 'content' => <<<'MD'
Die **erste Lagemeldung** des ersteintreffenden Einheitsführers entscheidet, ob ausreichend Kräfte unterwegs sind. Sie muss schnell, kurz und strukturiert sein.

## Aufbau der Lagemeldung
1. **Wer** meldet: Funkrufname.
2. **Wo**: Einsatzort bestätigen oder korrigieren.
3. **Was** liegt vor: Art und Umfang des Schadens.
4. **Was** wird getan: eingeleitete Maßnahmen.
5. **Was** wird benötigt: Nachforderungen oder Entwarnung.

### Beispiel
> „Leitstelle von Florian Los Santos 1/HLF/1: Grove Street, Mehrfamilienhaus, Brand im 2. OG, Flammenschlag aus zwei Fenstern, eine Person am Fenster 3. OG. Menschenrettung über Drehleiter, ein Trupp unter Atemschutz im Innenangriff. Erhöhung auf B3, ein weiterer Rettungswagen SAMS, kommen.“

## Folgemeldungen
- Bei **Lageänderung** sofort (Ausbreitung, Personen gefunden, Rückzug).
- Regelmäßig bei längeren Einsätzen (ca. alle 15–20 Minuten).
- „Feuer unter Kontrolle“ und „Feuer aus“ als feste Meldungen mit Uhrzeit.
- Abschlussmeldung: „Einsatzstelle an Eigentümer übergeben, rücken ein, Status 1.“

## Rückmeldungen innerhalb der Einheit
Trupps melden Auftrag ausgeführt, Personenfunde und Probleme an den Einheitsführer. Atemschutztrupps melden zusätzlich Druckwerte ([[Atemschutzüberwachung]]).

## Hinweise
- Keine Spekulationen über Ursachen oder Schuldige im Funk – das gehört in die Ermittlung der Polizei ([[Zusammenarbeit mit der Polizei]]).
- Die Lagemeldungen werden im **Einsatzbericht** chronologisch übernommen.
- Siehe auch [[Funkdisziplin und FMS-Status]] und [[Befehlsschema und Einsatzleitung]].
MD],
    ['title' => 'Zusammenarbeit mit SAMS', 'category' => 'Zusammenarbeit', 'content' => <<<'MD'
Feuerwehr und Rettungsdienst arbeiten bei fast jedem größeren Einsatz Hand in Hand. **San Andreas Medical Services (SAMS)** ist für die medizinische Versorgung verantwortlich, das LSFD für Rettung aus Gefahrenbereichen und technische Unterstützung.

## Aufgabenteilung
| LSFD | SAMS |
|---|---|
| Menschenrettung aus dem Gefahrenbereich | Medizinische Versorgung und Transport |
| Technische Rettung (VU, Einsturz) | Festlegung der patientenorientierten Rettung |
| Brandschutz und Absicherung | Sichtung bei MANV |
| Tragehilfe, Drehleiterrettung | Übergabe ans Krankenhaus |
| Dekontamination | Medizinische Behandlung nach Dekon |

## Grundsätze
- SAMS betritt den **Gefahrenbereich nicht** – Patienten werden an der Grenze übergeben.
- An der Einsatzstelle sprechen sich die Einheitsführer beider Organisationen persönlich ab.
- Bei Verkehrsunfällen bestimmt SAMS die Dringlichkeit, das LSFD die Technik ([[SOP Verkehrsunfall eingeklemmte Person]]).
- Feuerwehrkräfte mit Sanitätsausbildung unterstützen bei Reanimation und Versorgung.

## Patientenübergabe
Uhrzeit, Fundort, Auffindesituation, durchgeführte Maßnahmen (z. B. Sauerstoff, Lagerung), Expositionen (Rauch, Gefahrstoff). Die Übergabe wird im **Einsatzbericht** vermerkt.

## Gemeinsame Dokumentation
- **Gemeinsame Einsatzberichte** im System: LSFD und SAMS ergänzen jeweils ihre Abschnitte.
- Das SAMS-Einsatzprotokoll bleibt das medizinische Dokument, das LSFD verweist darauf.

## Siehe auch
[[SOP Tragehilfe für den Rettungsdienst]], [[MANV – Aufgaben der Feuerwehr]], [[Einsatznachbereitung und Einsatzbericht]].
MD],
    ['title' => 'Zusammenarbeit mit der Polizei', 'category' => 'Zusammenarbeit', 'content' => <<<'MD'
Bei vielen Einsätzen sind **LSPD** (Stadtgebiet) bzw. **BCSO** (Blaine County) vor Ort. Die Zusammenarbeit umfasst Absperrung, Verkehrslenkung, Eigensicherung und die **Brandursachenermittlung**.

## Absperrung und Verkehr
- Polizei übernimmt äußere Absperrung, Verkehrsregelung und Räumung.
- Feuerwehr legt den **Gefahrenbereich** fest und teilt ihn der Polizei mit.
- Feuerwehrzufahrten und Bereitstellungsräume freihalten lassen.

## Eigensicherung
- Bei Einsätzen mit Gewaltpotenzial (Schusswaffengebrauch, Bandenbrand in Davis oder Rancho, Brand nach Raub an Tankstellen) wartet das LSFD am **Bereitstellungspunkt**, bis die Polizei die Lage für sicher erklärt.
- Bei Bedrohung: sofort zurückziehen, Status 0 ([[Funkdisziplin und FMS-Status]]).

## Brandursachenermittlung
- Ermittlungen führt die Polizei, das LSFD unterstützt mit Fachwissen.
- **Hinweise auf Brandstiftung**: mehrere Brandherde, Brandbeschleuniger-Geruch, aufgebrochene Türen, entfernte Rauchmelder, ungewöhnlicher Brandverlauf.
- Spuren möglichst **nicht verändern**, Fotos vom Zustand bei Eintreffen.
- Beobachtungen (Personen, Fahrzeuge, Türen offen/zu) genau festhalten.
- Brandstelle nach dem Einsatz an die Polizei übergeben (Beschlagnahme).

## Weitere Schnittstellen
- Türöffnung ([[SOP Türöffnung]]), Personensuche, Suizidversuche.
- Unterstützung bei Fahrzeugbergung nach Verfolgungsfahrten.

## Dokumentation
Name und Dienstnummer des Polizeiverantwortlichen im **Einsatzbericht** vermerken. Gemeinsame Einsatzberichte sind möglich. Aussagen vor Gericht siehe [[Zusammenarbeit mit dem DOJ]].
MD],
    ['title' => 'Zusammenarbeit mit dem DOJ', 'category' => 'Zusammenarbeit', 'content' => <<<'MD'
Das **Department of Justice (DOJ)** von San Andreas ist für Strafverfolgung, Gerichtsverfahren und Verwaltungsentscheidungen zuständig. Das LSFD liefert Berichte, Gutachten und Zeugenaussagen.

## Anlässe
- Strafverfahren wegen **Brandstiftung**, fahrlässiger Brandverursachung oder Körperverletzung.
- Verstöße gegen Brandschutzauflagen, Nutzungsuntersagungen ([[Brandschau – Ablauf]]).
- Widerruf einer [[Brandschutzbescheinigung]].
- Zivilverfahren nach Schäden (Versicherungen, Haftung).
- Unfälle mit Einsatzfahrzeugen.

## Anforderung von Unterlagen
- Das DOJ fordert **Einsatzberichte**, Brandschauberichte und Fotos schriftlich über die Dienststellenleitung an.
- Herausgabe nur über die Dienststellenleitung, nicht durch einzelne Einsatzkräfte.
- Personenbezogene medizinische Daten werden nicht vom LSFD, sondern von SAMS herausgegeben.

## Zeugenaussagen
- Einsatzkräfte können als Zeugen geladen werden. Vor Gericht nur über **eigene Wahrnehmungen** aussagen, keine Vermutungen.
- Vorbereitung anhand des eigenen Einsatzberichts – deshalb müssen Berichte genau, sachlich und zeitnah verfasst werden ([[Einsatznachbereitung und Einsatzbericht]]).
- Dienstkleidung tragen, Dienstnummer bereithalten.

## Gutachten
Für komplexe Fälle (Großbrände, Explosionen) erstellt ein Brandsachverständiger des LSFD ein Gutachten zur Brandentstehung, in Abstimmung mit der Polizei ([[Zusammenarbeit mit der Polizei]]).

## Grundsatz
Das LSFD ist zur **Neutralität** verpflichtet. Einsatzkräfte äußern sich gegenüber Presse und Beteiligten nicht zu Schuldfragen.
MD],
    ['title' => 'MANV – Aufgaben der Feuerwehr', 'category' => 'Zusammenarbeit', 'content' => <<<'MD'
Ein **Massenanfall von Verletzten (MANV)** liegt vor, wenn mehr Patienten als verfügbare Rettungsmittel vorhanden sind – etwa nach einem Busunfall auf dem Highway, einer Explosion im Port of Los Santos oder einem Flugzeugunfall am LSIA.

## Führung
- Gemeinsame Einsatzleitung: Einsatzleiter LSFD, **Leitender Notarzt (LNA)** und **Organisatorischer Leiter Rettungsdienst (OrgL)** von SAMS, Polizeiführer.
- Die medizinische Führung liegt bei SAMS, die Gesamteinsatzleitung bei Gefahrenlagen meist beim LSFD.

## Aufgaben des LSFD
1. **Menschenrettung** aus dem Gefahrenbereich, Transport zur Patientenablage.
2. **Erstsichtung** in Unterstützung von SAMS (Sichtungskategorien Rot / Gelb / Grün / Blau / Schwarz).
3. Aufbau der **Patientenablage** und des Behandlungsplatzes (Zelte, Beleuchtung, Strom, Heizung).
4. **Tragehilfe** zwischen Ablage, Behandlungsplatz und Rettungsmittelhalteplatz.
5. Brandschutz, Absicherung, Dekontamination bei CBRN-Lagen ([[SOP Gefahrgutunfall (GAMS)]]).
6. Ordnung des **Rettungsmittelhalteplatzes** gemeinsam mit SAMS.

## Raumordnung
- Gefahrenbereich → Patientenablage → Behandlungsplatz → Rettungsmittelhalteplatz → Krankenhaus.
- Klare Ein- und Ausfahrtswege, Polizei sichert ab.

## Kommunikation
- Eigener Funkkanal für den Abschnitt Rettungsdienst ([[Abschnittsbildung bei Großlagen]]).
- Patientenzahlen regelmäßig an die Leitstelle melden.

## Nachbereitung
- Gemeinsamer **Einsatzbericht** mit SAMS und Polizei.
- **PSNV-E** für alle Einsatzkräfte anbieten ([[Psychosoziale Notfallversorgung (PSNV-E)]]).
- Siehe auch [[Zusammenarbeit mit SAMS]].
MD],
    ['title' => 'Hygiene nach dem Einsatz (Schwarz-Weiß-Trennung)', 'category' => 'Sicherheit & Gesundheit', 'content' => <<<'MD'
Brandrauch enthält krebserregende Stoffe (PAK, Dioxine, Ruß), die sich auf Kleidung, Ausrüstung und Haut ablagern. Die **Schwarz-Weiß-Trennung** schützt Einsatzkräfte vor Langzeitschäden und verhindert die Verschleppung von Schadstoffen in Fahrzeuge und Wachen.

## An der Einsatzstelle
1. Nach dem Atemschutzeinsatz **grobe Reinigung** der Schutzkleidung (abbürsten, ggf. mit Wasser abspülen), Atemschutzmaske erst danach absetzen.
2. Kontaminierte Kleidung im **Schwarzbereich** ausziehen, Handschuhe zuletzt.
3. Kleidung und Geräte in **luftdichte Säcke** verpacken und kennzeichnen.
4. Hände, Gesicht und Hals mit Reinigungstüchern säubern.
5. Ersatzkleidung (Hygieneboard am HLF) anziehen.
6. Rückfahrt in sauberer Kleidung, kontaminiertes Material im Geräteraum, nicht in der Mannschaftskabine.

## Auf der Wache
- **Schwarzbereich**: Anlieferung kontaminierter Ausrüstung, Waschmaschinen, Atemschutzwerkstatt.
- **Weißbereich**: Umkleide, Aufenthaltsräume, Küche, Ruheräume.
- Duschen **innerhalb einer Stunde** nach dem Einsatz.
- Einsatzkleidung niemals mit nach Hause nehmen.

## Besondere Einsätze
- Bei Gefahrgut- und CBRN-Einsätzen erfolgt eine formale **Dekontamination** ([[SOP CBRN-Einsatz Humane Labs]]).
- Bei infektiösen Patienten: Schutzausrüstung nach Vorgabe von SAMS.

## Dokumentation
Expositionen (Brandrauch, Gefahrstoffe, Asbest) werden im **Einsatzbericht** und in der persönlichen Expositionsdokumentation erfasst. Siehe auch [[Unfallverhütung und Eigenschutz]].
MD],
    ['title' => 'Psychosoziale Notfallversorgung (PSNV-E)', 'category' => 'Sicherheit & Gesundheit', 'content' => <<<'MD'
Einsatzkräfte erleben Situationen, die außerhalb normaler Erfahrung liegen: tote Kinder, schwere Verletzungen, verunglückte Kollegen. Die **Psychosoziale Notfallversorgung für Einsatzkräfte (PSNV-E)** hilft, diese Erlebnisse zu verarbeiten.

## Belastende Einsätze
- Tod oder schwere Verletzung von Kindern.
- Verletzte oder getötete Kolleginnen und Kollegen ([[Atemschutz-Notfall (Mayday)]]).
- MANV-Lagen ([[MANV – Aufgaben der Feuerwehr]]).
- Suizide, Gewalttaten, Einsätze mit persönlichem Bezug.
- Lange, erfolglose Rettungsversuche.

## Warnzeichen
- Schlafstörungen, Albträume, wiederkehrende Bilder (Flashbacks).
- Reizbarkeit, Rückzug, erhöhter Alkoholkonsum.
- Konzentrationsprobleme, Gefühl der Taubheit.
Diese Reaktionen sind **normal** in den ersten Tagen. Halten sie länger als 4 Wochen an, ist professionelle Hilfe nötig.

## Angebote im LSFD
1. **Einsatznachbesprechung** direkt nach dem Einsatz durch den Einheitsführer.
2. **Peers**: speziell ausgebildete Kolleginnen und Kollegen für vertrauliche Gespräche.
3. **Einsatzkräftenachsorge** mit Fachkräften (Psychologen, Seelsorger) nach 24–72 Stunden.
4. Vermittlung an Therapie bei anhaltenden Beschwerden – auch über SAMS.

## Grundsätze
- Inanspruchnahme ist **freiwillig und vertraulich** und hat keine dienstlichen Nachteile.
- Führungskräfte achten aktiv auf ihre Mannschaft und bieten Hilfe an.
- Inhalte von Peer-Gesprächen werden nicht im Einsatzbericht dokumentiert, lediglich die Tatsache, dass PSNV angeboten wurde.

## Siehe auch
[[Einsatznachbereitung und Einsatzbericht]], [[Wachalltag und Schichtdienst]].
MD],
    ['title' => 'Unfallverhütung und Eigenschutz', 'category' => 'Sicherheit & Gesundheit', 'content' => <<<'MD'
Eigenschutz geht vor Fremdschutz. Eine verletzte Einsatzkraft kann niemandem mehr helfen und bindet zusätzliche Kräfte.

## Persönliche Schutzausrüstung (PSA)
- **Brandeinsatz**: Feuerwehrhelm mit Visier und Nackenschutz, Flammschutzhaube, Überjacke und Überhose, Handschuhe, Stiefel – vollständig geschlossen.
- **Technische Hilfe**: Helm mit Visier, Schutzbrille, Schnittschutzhandschuhe, ggf. Gehörschutz.
- **Verkehrsflächen**: Warnkleidung, auch über der Einsatzkleidung.
- **Vegetationsbrand**: leichte Schutzkleidung, Atemschutzmaske gegen Partikel ([[SOP Waldbrand / Vegetationsbrand]]).
- **Wasser**: Rettungsweste, Trockenanzug ([[SOP Wasserrettung]]).

## Gefahren an der Einsatzstelle
- **Straßenverkehr**: Häufigste Unfallursache für Einsatzkräfte. Absicherung mit Fahrzeug, Leitkegeln, Polizei.
- **Elektrizität**: Abstände einhalten, Anlagen freischalten lassen.
- **Einsturz**: Gefahrenbereiche markieren ([[SOP Einsturz]]).
- **Absturz**: Absturzsicherung ab 3 m Höhe.
- **Hitze**: Trinken, Pausen, Kühlung – Hitzschlaggefahr im Sommer in Sandy Shores.
- **Gewalt**: Bei Bedrohungslagen Rückzug und Polizei ([[Zusammenarbeit mit der Polizei]]).

## Einsatzfahrten
- Sondersignal entbindet nicht von Sorgfalt: Kreuzungen nur mit Schrittgeschwindigkeit bei Rot.
- Gurtpflicht für alle Insassen, Atemschutzgeräte in Halterungen.

## Unfallmeldung
Jeder Dienstunfall – auch kleine Verletzungen – wird gemeldet und im **Einsatzbericht** sowie in der Unfallanzeige dokumentiert. Nur so ist die Absicherung gewährleistet.

## Siehe auch
[[Hygiene nach dem Einsatz (Schwarz-Weiß-Trennung)]], [[Fitness und Atemschutztauglichkeit]].
MD],
    ['title' => 'Fitness und Atemschutztauglichkeit', 'category' => 'Sicherheit & Gesundheit', 'content' => <<<'MD'
Feuerwehrdienst ist körperliche Schwerstarbeit. Ein Atemschutzeinsatz bei Hitze kann den Puls auf über 180 Schläge pro Minute treiben. Körperliche Fitness ist daher Dienstpflicht.

## Arbeitsmedizinische Untersuchung (G 26.3)
- Voraussetzung für das Tragen von Pressluftatmern.
- Erstuntersuchung vor dem Atemschutzlehrgang.
- **Nachuntersuchung**: unter 50 Jahren alle 3 Jahre, ab 50 Jahren jährlich.
- Durchgeführt durch den Betriebsarzt (in Zusammenarbeit mit SAMS).
- Ergebnis (tauglich / bedingt tauglich / nicht tauglich) wird im **Atemschutz-Nachweis** im System hinterlegt – ohne medizinische Details.

## Jährliche Belastungsübung
- Atemschutz-Übungsstrecke mit Endlosleiter, Laufband, Hammerschlag und Kriechstrecke.
- Mindestens einmal jährlich, zusätzlich ein Einsatz oder eine Einsatzübung unter Atemschutz.
- Wer die Übung nicht absolviert, darf bis zur Nachholung keinen Atemschutz tragen.

## Dienstsport
- Täglicher Dienstsport im Wachalltag ([[Wachalltag und Schichtdienst]]).
- Schwerpunkte: Ausdauer, Rumpfstabilität, Kraft, Beweglichkeit.
- Jährlicher Fitnesstest (z. B. Treppensteigen mit Ausrüstung, Schlauchziehen, Dummy-Rettung 80 kg).

## Gesundheit
- Ausreichend trinken, gesunde Ernährung auf der Wache.
- Erkrankungen (Infekt, Fieber) melden – kein Atemschutz bei akuter Erkrankung.
- Herz-Kreislauf-Risiken ernst nehmen, Vorsorge nutzen.
- Psychische Gesundheit ist Teil der Fitness ([[Psychosoziale Notfallversorgung (PSNV-E)]]).

## Siehe auch
[[Atemschutz-Einsatzgrundsätze]], [[Unfallverhütung und Eigenschutz]].
MD],
];
