# sams_tablet – FiveM-Resource

Bringt das SAMS-Dokumentensystem ins Spiel:

- **Tablet** (Item oder `/tablet`) mit Tablet-Animation. Anmeldung mit denselben Zugangsdaten wie im Browser (inkl. 2FA), danach läuft das komplette Websystem im Tablet.
- **Drucker** in der Spielwelt. Die Leitung legt sie im Tablet unter *Verwaltung → Drucker* an: hinstellen, „Aktuelle Position übernehmen“, speichern. Ein Drucker-Objekt erscheint innerhalb einer Minute.
- **Drucken:** Am Dokument „Im Spiel drucken“ → Drucker wählen. Am Drucker benutzen (ox_target oder Taste E) → Auftrag wählen → das Dokument landet als Item im Inventar.
- **Dokument-Item:** Benutzen öffnet das Original-Dokument auf einem Klemmbrett (mit Animation). „Zeigen“ zeigt es dem nächsten Spieler in der Nähe. Weitergeben über das Inventar (ox_inventory). Stornierte Dokumente sind als STORNIERT erkennbar.

## Voraussetzungen

- [ox_lib](https://github.com/overextended/ox_lib), [ox_inventory](https://github.com/overextended/ox_inventory)
- optional [ox_target](https://github.com/overextended/ox_target) (sonst Interaktion per Taste E)
- SAMS-Websystem ab Version 1.4 über **HTTPS** erreichbar (das Tablet bettet die Seite ein; ohne HTTPS funktioniert die Anmeldung im Tablet nicht)

## Installation

1. Ordner `sams_tablet` nach `resources/` kopieren.
2. In die `server.cfg` (nach ox_lib und ox_inventory):
   ```cfg
   set sams_url "https://sams.deinserver.de"
   set sams_api_key "API_KEY aus der .env des Websystems"
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
| `Config.Anim` | Animationen und Requisiten (Tablet, Klemmbrett, Drucker) |
| `Config.Text` | Alle Texte |

**Wer am Drucker abholen darf**, stellt die Leitung im Websystem unter *Drucker → Abholung* ein: nur der Auftraggeber, alle angemeldeten Mitarbeitenden oder jeder Spieler.

## So funktioniert die Anmeldung

- Der Spieler meldet sich im Tablet an. Der Spielserver erhält vom Websystem einen Anmelde-Schlüssel, der **nur auf dem Server** gespeichert wird (an die FiveM-Lizenz gebunden, 30 Tage gültig).
- Beim Öffnen erzeugt der Server einen Einmal-Link (60 Sekunden gültig), mit dem das Tablet eine normale Sitzung im Websystem öffnet.
- Abmelden im Tablet, „Alle Sitzungen beenden“ durch die Leitung, Passwortwechsel oder Kontosperre beenden die Tablet-Anmeldung sofort. Aktive Tablets sieht jeder unter *Mein Profil*.

## Exports

```lua
exports.sams_tablet:openTablet()
exports.sams_tablet:closeTablet()
```
