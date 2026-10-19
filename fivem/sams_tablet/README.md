# sams_tablet – FiveM-Resource

Bringt das SAMS-Dokumentensystem ins Spiel:

- **Tablet** (Item oder `/tablet`) mit Tablet-Animation. Anmeldung mit denselben Zugangsdaten wie im Browser (inkl. 2FA), danach läuft das komplette Websystem im Tablet.
- **Drucker** in der Spielwelt. Die Leitung legt sie im Tablet unter *Verwaltung → Drucker* an: hinstellen, „Aktuelle Position übernehmen“, speichern. Ein Drucker-Objekt erscheint innerhalb einer Minute.
- **Drucken:** Am Dokument „Im Spiel drucken“ → Drucker wählen. Am Drucker benutzen (ox_target oder Taste E) → Auftrag wählen → das Dokument landet als Item im Inventar.
- **Dokument-Item:** Benutzen öffnet das Original-Dokument auf einem Klemmbrett (mit Animation). „Zeigen“ zeigt es dem nächsten Spieler in der Nähe. Weitergeben über das Inventar (ox_inventory). Stornierte Dokumente sind als STORNIERT erkennbar.

- **ESX** (optional): Rechnungen ins Spiel (esx_billing), Spielerdaten und `/akte` am nächsten Spieler – siehe unten.

## Voraussetzungen

- [ox_lib](https://github.com/overextended/ox_lib), [ox_inventory](https://github.com/overextended/ox_inventory)
- optional [ox_target](https://github.com/overextended/ox_target) (sonst Interaktion per Taste E)
- optional es_extended (ESX Legacy) mit esx_billing, esx_addonaccount und [oxmysql](https://github.com/overextended/oxmysql) für Rechnungen und `/akte`
- SAMS-Websystem ab Version 1.4 über **HTTPS** erreichbar (das Tablet bettet die Seite ein; ohne HTTPS funktioniert die Anmeldung im Tablet nicht)

## Installation

1. Ordner `sams_tablet` nach `resources/` kopieren.
2. In die `server.cfg` (nach ox_lib und ox_inventory):
   ```cfg
   set sams_url "https://sams.deinserver.de"
   set sams_api_key "Schlüssel aus dem Portal (Administration → Spielserver) bzw. API_KEY aus der .env"
   ensure sams_tablet
   ```
   Wichtig: `set`, **nicht** `setr` – sonst könnten Spieler den Schlüssel auslesen.
3. Items aus [`install/ox_inventory_items.lua`](install/ox_inventory_items.lua) in `ox_inventory/data/items.lua` einfügen.
4. Bilder aus `install/images/` nach `ox_inventory/web/images/` kopieren.
5. Tablet an Mitarbeitende ausgeben, z. B. per Admin-Befehl von ox_inventory: `/giveitem <id> sams_tablet 1`.

Den API-Schlüssel zeigt das Einrichtungsskript des Websystems am Ende an. Alternativ in der `.env` des Websystems nachsehen (`API_KEY=`).

## Einstellungen (`config.lua`)

| Einstellung | Bedeutung |
|---|---|
| `Config.Command` / `CommandRequiresItem` | Befehl zum Öffnen, optional nur mit Tablet-Item |
| `Config.UseTarget` | ox_target nutzen (falls gestartet), sonst Taste E |
| `Config.PrintDuration` | Dauer des Druckens (Fortschrittsbalken) |
| `Config.WaypointOnPrint` | Wegpunkt zum Drucker setzen, wenn ein Auftrag bereitliegt |
| `Config.PrinterBlip` | Kartensymbol für Drucker (nur für angemeldete Mitarbeitende, wenn im Websystem aktiviert) |
| `Config.DutyCommand` | Befehl zum Stempeln (Standard `/dienst`, `nil` = aus; Name im Websystem einstellbar) |
| `Config.AkteCommand` | Akte am nächsten Spieler (Standard `/akte`, `nil` = aus; Name im Websystem einstellbar) |
| `Config.BillSender` | Absender-Kennung der Rechnungen in esx_billing |
| `Config.DutyEndOnDisconnect` | Beim Verlassen des Servers automatisch ausstempeln |
| `Config.Pharmacies` | Apotheken (Position, optional Ped und Kartensymbol), an denen Rezepte eingelöst werden |
| `Config.Anim` | Animationen und Requisiten (Tablet, Klemmbrett, Drucker) |
| `Config.Text` | Alle Texte |

**Befehlsnamen, Society-Konten, Jobs und Schalter** stehen nicht in der `config.lua`, sondern im Websystem unter *Einstellungen → Spielserver* (je Fraktion bzw. für alle Fraktionen in der Hauptfraktion). Die Resource holt sie beim Start und danach alle 5 Minuten; neue Befehlsnamen gelten nach `restart sams_tablet`. Die Befehle in der `config.lua` sind nur der Notbetrieb, solange das Websystem nicht erreichbar ist (`nil` schaltet einen Befehl ganz ab).

**Wer am Drucker abholen darf**, stellt die Leitung im Websystem unter *Drucker → Abholung* ein: nur der Auftraggeber, alle angemeldeten Mitarbeitenden oder jeder Spieler.

## So funktioniert die Anmeldung

- Der Spieler meldet sich im Tablet an. Der Spielserver erhält vom Websystem einen Anmelde-Schlüssel, der **nur auf dem Server** gespeichert wird (an die FiveM-Lizenz gebunden, 30 Tage gültig).
- Beim Öffnen erzeugt der Server einen Einmal-Link (60 Sekunden gültig), mit dem das Tablet eine normale Sitzung im Websystem öffnet.
- Abmelden im Tablet, „Alle Sitzungen beenden“ durch die Leitung, Passwortwechsel oder Kontosperre beenden die Tablet-Anmeldung sofort. Aktive Tablets sieht jeder unter *Mein Profil*.

## Dienstzeit

- `/dienst` stempelt ein bzw. aus (erfordert die Anmeldung im Tablet und das Recht „Dienst beginnen/beenden“).
- Im Websystem geht das genauso über den Schalter oben rechts. Beide Wege führen zur selben Schicht.
- Beim Abmelden im Tablet und – je nach `Config.DutyEndOnDisconnect` – beim Verlassen des Servers wird automatisch ausgestempelt. Vergessene Schichten beendet das Websystem nach der eingestellten Höchstdauer.
- Andere Resourcen (z. B. Dispatch, Fahrzeuggarage) können den Status abfragen:

```lua
-- serverseitig
local imDienst = exports.sams_tablet:IsOnDuty(source)
exports.sams_tablet:SetOnDuty(source, true)
AddEventHandler('sams:dutyChanged', function(src, onDuty) ... end)
```

## Fahndung (Polizei)

- `/kennzeichen` fragt das Fahrzeug vor dir ab, `/kennzeichen ABC123` ein bestimmtes Kennzeichen – mit Halter/in aus dem Fahrzeugregister (Polizei/DMV), abgelaufener Zulassung und gestohlen/beschlagnahmt (`Config.WantedCommand`, nur im Dienst, wenn `Config.WantedRequiresDuty`).
- Erlaubt für Konten mit dem Recht „Fahndungen einsehen“ (Polizei). Treffer zeigen Grund, Gefährlichkeit und ob ein Haftbefehl der Justiz vorliegt.
- Für eigene MDT-Scripte (serverseitig): `exports.sams_tablet:CheckWanted(kennzeichen, buergerId)` und `exports.sams_tablet:CheckPerson(buergerId)` (Fahndung, Führerschein-/Waffenscheinstatus, gültige Erlaubnisse).
- Dienstzeit, Drucker und Dokumente funktionieren für alle Fraktionen gleich – jede Fraktion stellt ihre Drucker im eigenen Websystem-Bereich auf.

## ESX: Rechnungen und Akte am Spieler

Einstellen im Websystem unter *Einstellungen → Spielserver*:

| Einstellung | Bedeutung |
|---|---|
| ESX-Jobs | Jobnamen der Fraktion (z. B. `ambulance`), für „nur mit passendem Job“ |
| Society-Konto | Konto aus esx_addonaccount, auf das bezahlte Rechnungen gehen (z. B. `society_ambulance`) |
| Rechnungen ins Spiel senden | Knopf „Ins Spiel senden“ an Rechnungen/Bußgeldern |
| Akte am Spieler abrufen | `/akte`, optional nur im Dienst und/oder nur mit passendem Job |
| alle Fraktionen | Framework, Befehlsnamen, Reichweite für `/akte`, Name der esx_billing-Tabelle, Abgleich-Intervall |

**Rechnungen:** Am Dokument „Ins Spiel senden“. Die Resource legt den Eintrag in der Tabelle `billing` an (Empfänger = Bürger-ID der Akte, Ziel = Society-Konto), der Spieler wird benachrichtigt und bezahlt wie gewohnt über das Rechnungsmenü von esx_billing. Verschwindet der Eintrag (esx_billing löscht ihn beim Bezahlen), wird die Rechnung im Websystem als „im Spiel bezahlt“ verbucht. Wird die Rechnung im Websystem storniert oder bar bezahlt, entfernt die Resource den Eintrag im Spiel wieder. Hinweis: Wer Einträge in `billing` von Hand löscht (z. B. beim Löschen eines Charakters), löst damit ebenfalls „bezahlt“ aus.

**Bürger-ID:** Für ESX ist das der `identifier` des Charakters (bei Multicharacter z. B. `char1:…`). Akten, die über `/akte` angelegt werden, bekommen ihn automatisch.

**`/akte`:** Zeigt für den nächsten Spieler (Reichweite einstellbar) eine Kurzfassung der Akte der eigenen Fraktion – beim Rettungsdienst Blutgruppe, Allergien, Vorerkrankungen, Medikation, Patientenverfügung, Notfallkontakt, Hinweise und offene Fälle. „Akte im Tablet öffnen“ öffnet sie direkt; gibt es noch keine Akte, kann sie mit Bürger-ID, Name, Geburtsdatum und Geschlecht aus ESX angelegt werden (Recht „Akten anlegen und bearbeiten“). Die Spielerdaten liest der Server aus `xPlayer`, nie vom Client. Jeder Abruf steht im Zugriffsprotokoll.

Für andere Resourcen: `exports.sams_tablet:GetPlayerData(source)` (serverseitig), `exports.sams_tablet:showRecord()` (clientseitig).

## Sky Ambulance (sky_ambulancejob)

Einschalten im Websystem unter *Einstellungen → Spielserver → „Sky Ambulance anbinden“* (Schalter für Tod/Wiederbelebung und Medikamente einzeln). Genutzt werden nur die dokumentierten Schnittstellen von Sky (ab Version 2.37, Vitalwerte als Zahlen):

- **`/akte` → „Vitalwerte übernehmen“**: liest beim Patienten `GetLocalInjuryProfile()` (RR, HF, SpO₂, Blutzucker, Herzstillstand) und speichert die Werte im Vitalverlauf der Akte. Ein Einsatzprotokoll, das in der nächsten Stunde ausgestellt wird, ist damit vorbelegt.
- **`/akte` → „Behandeln (Sky Ambulance)“**: öffnet Skys Behandlung (`OpenTreatment`) für diese Person.
- **Tod und Wiederbelebung**: `deathStateChanged` beim Spieler, serverseitig mit `isDead` geprüft. Ist ein Medic (im Dienst, im Tablet angemeldet) im Umkreis von 30 m, entsteht der Eintrag in dessen Akte (Akte wird bei Bedarf aus ESX angelegt), sonst nur in vorhandenen Akten.
- **Medikamentengabe → Bestand/BtM-Buch**: In Skys `Config.CustomMedications` beim jeweiligen Medikament ergänzen:
  ```lua
  onFinishServer = function(self)
      exports.sams_tablet:SkyMedication(self.source, self.target, self.itemName)
  end,
  ```
  Im Websystem unter *Medikamente & BtM* beim Artikel dasselbe ox_inventory-Item eintragen. Jede Gabe bucht 1 Einheit mit Patient und Medic und erscheint in der Akte.
- **Sky Billing**: *Rechnungssystem im Spiel* auf „Sky Billing“ stellen. „Ins Spiel senden“ legt dann über `sky_jobs_base:CreateInvoice` eine Rechnung von der Fraktion (erster ESX-Job) an den Spieler an (zahlbar später, eindeutige Referenz). Bezahlt wird erkannt, wenn `balanceDue` 0 ist; Storno/Barzahlung im Websystem ruft `CancelInvoice`.
- **`/samsprofil`** (F8): gibt das eigene Verletzungsprofil aus. Bitte einmal mit Verletzungen ausführen und die Ausgabe schicken – Wunden und Körperzonen sind bei Sky nicht dokumentiert und werden erst danach übernommen.

Hinweis: Die Vitalwerte stammen aus dem Spiel des Patienten und dienen nur der Dokumentation.

## Leitstelle tetrascript (tetra-core)

SAMS und tetrascript sind zwei eigenständige Systeme. Jedes läuft allein; `sams_tablet` hat **keine** Abhängigkeit zu tetra-core, und SAMS bringt tetrascript weder mit noch installiert oder startet es. Die Verbindung entsteht nur, wenn beide auf dem Server laufen **und** sie ausdrücklich eingeschaltet wird: *Einstellungen → Spielserver → „Leitstelle tetrascript anbinden“* (Vorgabe: aus). Je Fraktion wird die Organisation der Leitstelle eingetragen (Vorgabe: Feuerwehr `fire`, Rettungsdienst `ems`, Polizei `police`).

- **FMS-Status**: Die Brücke hängt sich an den Hook `statusChanged` von tetra-core. Ist der Träger des Funkgeräts im Tablet angemeldet, erscheint sein Status (mit Rufname) im Websystem. Mit „Dienst automatisch mit Funkstatus“ beginnt Status 1–4 den Dienst, Status 6 beendet ihn.
- **Einsätze**: Die Brücke liest den Einsatzstand der Leitstelle aus der gemeinsamen Datenbank (Tabelle `tetra_dispatch_cad`, nur lesend) und überträgt neue bzw. geänderte Einsätze alle 20 Sekunden (einstellbar). Im Tablet unter *Leitstelle*: laufende und abgeschlossene Einsätze, Einheiten, Verlauf, Gefahren, passender Objektplan; „Einsatzbericht erstellen“ übernimmt Ort, Zeiten, Kräfte und Verlauf.
- Mit tetra-core ab Schnittstelle 2 (Branch `claude/sams-anbindung` von tetrascript, Export `GetApiVersion`) kommen Einsätze sofort über den Hook `incidentsChanged` statt alle 20 Sekunden. Zusätzlich lassen sich – jeweils einzeln einzuschalten, Vorgabe aus – der **FMS-Status bei Dienstbeginn/-ende** setzen und **Objektpläne mit Position als POI** ins Objektverzeichnis der Leitstelle übertragen. Ältere tetra-core-Stände funktionieren weiter wie oben.
- Läuft tetra-core nicht (oder die Anbindung ist aus), passiert nichts. tetra-core selbst kennt SAMS nicht: Es bietet nur allgemeine Exports/Hooks an, die jede Ressource nutzen kann.

## Dienstausweis als Karte

Dienstausweise erscheinen nicht auf dem Klemmbrett, sondern als Karte in der Hand (Animation `Config.Anim.card`). Klicken dreht die Karte um (Vorder-/Rückseite), Ziehen mit der Maus neigt und dreht sie frei, Leertaste/Pfeiltasten drehen ebenfalls. Beim „Zeigen“ sieht die andere Person die Karte genauso. Welche Dokumentarten als Karte gelten, steht in der Dokumentart (`'card' => true`).

## Unterschreiben auf dem Ausdruck

- Dokument-Item benutzen → unten auf dem Klemmbrett erscheinen die offenen Unterschriftsfelder („✍ Unterschrift betroffene Person“). Anklicken, mit der Maus unterschreiben, „Unterschreiben“.
- Unterschreiben darf, wer das Blatt im Inventar hat oder wem es gerade gezeigt wurde (10 Minuten lang). Der Name kommt aus ESX (Vor- und Nachname des Charakters), sonst der Spielername.
- Jede Unterschrift ist sofort endgültig und im Websystem gespeichert – alle Ausdrucke zeigen sie. Sind alle Felder unterschrieben, ist das Dokument abgeschlossen. „Abschließen“ (zweimal klicken) beendet es vorher, z. B. wenn die Unterschrift verweigert wird; das darf nur, wer das Blatt besitzt.
- Dokumente ohne eigenes Unterschriftsfeld können als „Empfang bestätigt“ unterschrieben werden (Zusatzblatt).

## Apotheke

- Ein ausgedrucktes Rezept (Dokument-Item) wird an einer Apotheke aus `Config.Pharmacies` eingelöst – per ox_target am Apotheken-Ped oder mit Taste E.
- Das Websystem ordnet jede Rezeptzeile einem Artikel aus *Medikamente & BtM* zu und gibt die dort eingetragenen **ox_inventory-Items** aus (Feld „ox_inventory-Item“ und „Items je Einheit“). Artikel ohne Item gibt es nur in der Klinik.
- „2x Ibuprofen …“ in der Rezeptzeile = zwei Einheiten. Betäubungsmittel gibt es nur auf dem BtM-Rezept. Rezepte gelten 28 Tage (BtM 7 Tage, einstellbar).
- Der Bestand wird ausgebucht (bei BtM im BtM-Buch). Jede Position lässt sich nur einmal einlösen; das Rezept bleibt als Beleg im Inventar und ist als „eingelöst“ gekennzeichnet.
- Die Medikamenten-Items selbst (z. B. `painkillers`) müssen in `ox_inventory/data/items.lua` existieren – ihre Wirkung regelt dein Medizin-Script.

## Exports

```lua
-- clientseitig
exports.sams_tablet:openTablet()
exports.sams_tablet:closeTablet()
exports.sams_tablet:toggleDuty()
```
