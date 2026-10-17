# SAMS – Dokumenten- & Aktensystem

Webbasiertes Dokumenten- und Aktensystem für den **San Andreas Medical Service (SAMS)** auf FiveM-Roleplay-Servern.
Medics und Ärzt/innen führen Patientenakten und stellen Dokumente aus, die wie echte deutsche Praxis- und Klinikdokumente
aussehen: Vordrucke in Originalfarben, Briefkopf nach DIN 5008 mit Falz- und Lochmarken, Arztstempel und Dienstsiegel
mit Tinteneffekt, handschriftliche Unterschrift, Barcode und QR-Code zur Echtheitsprüfung.

- **PHP 8.1+**, keine Frameworks, kein Composer, kein Node – einfach hochladen
- **SQLite** (ohne Einrichtung) oder **MySQL/MariaDB**
- Läuft auf jedem normalen Webspace, als Unterseite der Community-Website oder im Spiel (NUI/iframe)

## Dokumentarten

| Kürzel | Dokument | Aussehen | Mindestrolle |
|---|---|---|---|
| AU | Arbeitsunfähigkeitsbescheinigung | A5-Vordruck rosa, 3 Ausfertigungen (Krankenkasse, Arbeitgeber **ohne Diagnose**, Versicherte/r) | Arzt |
| AT | Ärztliches Attest | A4-Brief | Arzt |
| RX | Rezept | A6-Vordruck: Kassen- (rosa), Privat- (blau), BtM-Rezept (gelb), aut idem, noctu … | Arzt |
| UW | Überweisung / Krankenhauseinweisung | A5-Vordruck grün | Arzt |
| EP | Rettungsdienstliches Einsatzprotokoll | A4-Formular mit GCS, Vitalwerten, Körperschema, Maßnahmen, NACA | Rettungsdienst |
| RV | Behandlungs-/Transportverweigerung (Revers) | A4, mit Unterschrift des Patienten auf dem Gerät | Rettungsdienst |
| AB | Arztbrief / Entlassungsbericht | A4-Brief | Arzt |
| VB | Ärztlicher Befundbericht über Verletzungen (für LSPD/Gericht) | A4 mit Verletzungstabelle und Körperschema, Dienstsiegel | Arzt |
| BE | Blutentnahmeprotokoll (BAK/Drogen auf polizeiliche Anordnung) | A4-Formular | Arzt |
| TZ | Tauglichkeitszeugnis (Waffenschein, Führerschein, Pilot, Polizeidienst, Haftfähigkeit …) | A4 mit Dienstsiegel | Arzt |
| TB | Todesbescheinigung | A4-Formular, vertraulicher/nichtvertraulicher Teil | Arzt |
| RE | Rechnung | A4-Brief mit Positionen, Summen, „BEZAHLT“-Stempel | Rettungsdienst |
| SC | Allgemeines Schreiben | A4-Brief, optional mit Dienstsiegel | Rettungsdienst |

Jedes Dokument erhält eine fortlaufende Nummer (z. B. `SAMS-AU-2026-00042`) und einen Prüfcode.
Ausgestellte Dokumente sind **unveränderlich** (Patienten- und Ausstellerdaten werden eingefroren) und können nur
**storniert** werden. Fehler korrigieren: stornieren → „Als Vorlage verwenden“ → neu ausstellen.

**Ausgabe:** Drucken bzw. „Als PDF speichern“ im Browser (A4/A5/A6 werden in der richtigen Papiergröße erzeugt) oder
**„Als Bild speichern“** (PNG, z. B. zum Weitergeben in Discord oder per In-Game-Handy).

## Weitere Funktionen

- **Patientenakten**: Personalien, Bürger-ID (z. B. `citizenid` aus ESX/QBCore), Versicherung, Blutgruppe, Allergien
  (Warnhinweis), Vorerkrankungen, Medikation, Notfallkontakt, Organspende, Patientenverfügung, Kennzeichnungen
- **Akteneinträge** (Behandlung, Einsatz, Psychologisches Gespräch …) mit SOAP-/ABCDE-Vorlagen, optional **vertraulich**
  (nur ärztlicher Dienst); chronologischer Verlauf aus Einträgen und Dokumenten; Akte als PDF drucken
- **Öffentliche Echtheitsprüfung** `verify.php` – QR-Code scannen oder Prüfcode eingeben. Zeigt gültig / abgelaufen /
  storniert / nicht gefunden, aber keine medizinischen Inhalte (nur Initialen + Geburtsjahr)
- **Rollen**: Auszubildende · Rettungsdienst · Ärztlicher Dienst · Leitung
- **Unterschrift** einmalig im Profil mit Maus/Stift/Finger zeichnen; Arztstempel wird automatisch aus Name,
  Fachrichtung und Arzt-Nr. erzeugt
- **Zugriffsprotokoll**: wer hat wann welche Akte geöffnet, welches Dokument ausgestellt oder storniert
- **Einstellungen**: Name, Adresse, Logo, IK-Nummer, BSNR, Bankverbindung, Siegel-Umschrift, Währung, Nummernpräfix
- Vorschlagslisten für ICD-10-Diagnosen, Medikamente, Fachrichtungen und Leistungen
- **JSON-API** für FiveM-Resourcen (siehe unten)

## Installation

1. Alle Dateien auf den Webspace hochladen (z. B. nach `https://deine-community.de/sams/`).
2. Sicherstellen, dass das Verzeichnis `data/` vom Webserver beschreibbar ist (bei SQLite) – bzw. dass PHP
   `config.php` im Hauptverzeichnis anlegen darf.
3. `install.php` im Browser aufrufen, Datenbank wählen und das erste Leitungs-Konto anlegen.
4. `install.php` danach vom Server löschen.
5. Einloggen → **Einstellungen** (Name, Adresse, Logo …) → **Personal** anlegen → jede/r Mitarbeitende hinterlegt im
   **Profil** Titel, Fachrichtung, Arzt-Nr. und die Unterschrift.

Lokal testen: `php -S localhost:8080` im Projektordner, dann `http://localhost:8080` öffnen.

### Webserver-Hinweise

- **Apache**: Die mitgelieferten `.htaccess`-Dateien sperren `app/`, `data/` und `config.php`.
- **nginx**: diese Regeln ergänzen:
  ```nginx
  location ~ ^/(app|data)/ { deny all; }
  location ~ /(config|config\.sample)\.php$ { deny all; }
  ```
  (Zusätzlich erhält die SQLite-Datei bei der Installation einen zufälligen Namen.)
- **HTTPS** wird dringend empfohlen.

### Einbindung im Spiel (FiveM NUI / Tablet-Resource)

Das System kann direkt per URL in einer NUI-Seite oder einem iframe geladen werden. Läuft die Einbindung über eine
andere Domain, in `config.php` `'cookie_samesite' => 'None'` setzen (erfordert HTTPS), damit die Anmeldung im iframe
funktioniert.

### API für FiveM-Resourcen

In `config.php` steht ein `api_key` (wird bei der Installation erzeugt; leer = API deaktiviert).
Den Schlüssel nur serverseitig verwenden (server.lua), nie im Client.

```
GET api.php?action=verify&code=K7F3QX9M          → Dokument prüfen (z. B. für LSPD-/checkdoc-Befehl)
GET api.php?action=patient&citizen_id=ABC12345   → Notfalldaten (Blutgruppe, Allergien, Vorerkrankungen …)
GET api.php?action=documents&citizen_id=ABC12345 → Dokumente eines Bürgers
Header: X-API-Key: <api_key>
```

Beispiel (server.lua):

```lua
PerformHttpRequest('https://deine-community.de/sams/api.php?action=verify&code=' .. code, function(status, body)
    local data = json.decode(body)
    if data and data.found then
        print(('%s %s – Status: %s'):format(data.type, data.number, data.status))
    end
end, 'GET', '', { ['X-API-Key'] = 'DEIN_API_KEY' })
```

## Eigene Dokumentarten

Dokumentarten sind in `app/documents.php` (Felder, Rolle, Kürzel) und `app/templates/<name>.php` (Druckvorlage)
definiert. Für ein neues Dokument einen Eintrag in `document_types()` ergänzen und eine Vorlage anlegen – Formular,
Validierung, Speicherung, Nummerierung und Echtheitsprüfung funktionieren automatisch. Feldtypen: `text`, `textarea`,
`date`, `time`, `number`, `select`, `radio`, `checkbox`, `checkboxes`, `items` (Tabellenzeilen), `bodymap`
(Körperschema), `signature` (Unterschrift auf dem Gerät), `heading`.

## Projektstruktur

```
index.php            Router (alle internen Seiten)
verify.php           Öffentliche Echtheitsprüfung
api.php              JSON-API
install.php          Einrichtung
app/                 Logik, Seiten (pages/), Druckvorlagen (templates/)
assets/              CSS, JS, Logo, Unterschrift-Schrift, Bibliotheken
data/                SQLite-Datenbank (nicht öffentlich)
```

## Hinweis

Fiktives System für Roleplay-Zwecke. Alle Einrichtungen, Kennungen und Formularnummern sind frei erfunden;
die Dokumente sind nicht für reale Verwendung bestimmt.

Verwendete Bibliotheken: [qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator) (MIT),
[html2canvas](https://html2canvas.hertzen.com/) (MIT), Schrift „Herr Von Muellerhoff“ (SIL OFL 1.1).
