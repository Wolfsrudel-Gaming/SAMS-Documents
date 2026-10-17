# SAMS – Dokumenten- & Aktensystem

Webbasiertes Dokumenten- und Aktensystem für den **San Andreas Medical Service (SAMS)** auf FiveM-Roleplay-Servern.
Medics und Ärzt/innen führen Patientenakten und stellen Dokumente aus, die wie echte deutsche Praxis- und Klinikdokumente
aussehen: Vordrucke in Originalfarben, Briefkopf nach DIN 5008 mit Falz- und Lochmarken, Arztstempel und Dienstsiegel
mit Tinteneffekt, handschriftliche Unterschrift, Barcode und QR-Code zur Echtheitsprüfung.

- **PHP 8.2+** und **MySQL/MariaDB**, keine Frameworks, keine Build-Schritte
- Betrieb auf einem Linux-Server, wahlweise mit **Docker** oder klassisch mit nginx + PHP-FPM
- **Auto-Update:** Ein neuer Stand auf GitHub ist nach spätestens ~2 Minuten auf dem Server aktiv (inkl. Backup, Datenbank-Migration und automatischem Zurücksetzen bei Fehlern)
- Konzept und Etappenplan: [`docs/KONZEPT.md`](docs/KONZEPT.md)

## Dokumentarten

| Kürzel | Dokument | Aussehen | Standard-Vorlage* |
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

\* Wer welches Dokument ausstellen darf, ist pro Rang frei einstellbar (*Verwaltung → Ränge & Rechte*). Die Spalte zeigt die Vorbelegung.

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
- **Personalverwaltung**: Konten werden von Admin/Chiefs angelegt, das System erzeugt ein Startpasswort, das beim ersten Login geändert werden muss. Passwort zurücksetzen, Konto sperren (mit Grund), alle Sitzungen beenden, 2FA zurücksetzen, Verlauf aller Änderungen
- **Ränge & Rechte**: frei anlegbare Ränge mit Rangstufe und einzelnen Rechten (über 40, z. B. pro Dokumentart). Hierarchie-Regel: Man verwaltet nur Konten und Ränge unterhalb der eigenen Stufe und vergibt nur Rechte, die man selbst hat. Optional „nur Personal der eigenen Abteilungen verwalten“
- **Abteilungen & Qualifikationen**: frei anlegbar, eine Person kann mehreren Abteilungen angehören; Dienstnummern werden manuell vergeben
- **Sicherheit**: Zwei-Faktor-Anmeldung per Authenticator-App (Pflicht pro Rang einstellbar, Standard für die Leitung) mit Wiederherstellungscodes, Sperre nach 5 Fehlversuchen
- **Unterschrift** einmalig im Profil mit Maus/Stift/Finger zeichnen; Arztstempel wird automatisch aus Name,
  Fachrichtung und Arzt-Nr. erzeugt
- **Zugriffsprotokoll**: wer hat wann welche Akte geöffnet, welches Dokument ausgestellt oder storniert
- **Einstellungen**: Name, Adresse, Logo, IK-Nummer, BSNR, Bankverbindung, Siegel-Umschrift, Währung, Nummernpräfix
- Vorschlagslisten für ICD-10-Diagnosen, Medikamente, Fachrichtungen und Leistungen
- **Im Spiel**: eigenes Tablet, Drucker in der Spielwelt, gedruckte Dokumente als Item (ansehen, zeigen, weitergeben) – siehe unten
- **JSON-API** für FiveM-Resourcen

## Installation

### Schnellstart: Einrichtungsskript (empfohlen)

Für einen frischen **Ubuntu/Debian-Server** übernimmt [`deploy/install.sh`](deploy/install.sh) die komplette Einrichtung inklusive Auto-Update:
Pakete, Systembenutzer `sams`, Deploy-Key, Code, `.env` mit zufälligen Passwörtern, Datenbank, nginx, HTTPS (Let's Encrypt),
Admin-Konto, Update-Timer und tägliche Backups. Wahlweise **Docker** oder **klassisch** (nginx + PHP-FPM + MariaDB).

1. Die Domain (z. B. `sams.deinserver.de`) per DNS auf den Server zeigen lassen.
2. Das Skript auf den Server bringen – das Repository ist privat, daher z. B.:
   - in GitHub `deploy/install.sh` öffnen → **Raw** → Inhalt kopieren → auf dem Server `nano install.sh`, einfügen, speichern, **oder**
   - vom eigenen PC: `scp deploy/install.sh root@dein-server:/root/`
3. Starten:
   ```bash
   sudo bash install.sh
   ```
4. Das Skript fragt Betriebsart, Domain, Branch und HTTPS ab. Danach zeigt es einen **Deploy-Key** an: Den in GitHub unter
   **Repository → Settings → Deploy keys → Add deploy key** eintragen (*Allow write access* **nicht** anhaken) und Enter drücken.
5. Am Ende legst du dein Hauptadministrator-Konto an. Das Skript zeigt zum Schluss die Daten für den optionalen GitHub-Webhook.

Danach stehen auf dem Server die Befehle `sams status`, `sams backup`, `sams backups` und `sams-update` (Update sofort ausführen) zur Verfügung.
Das Skript kann jederzeit erneut ausgeführt werden, z. B. um Domain oder Branch zu ändern – Daten und Passwörter bleiben erhalten.

### Manuelle Installation

Voraussetzungen: Linux-Server mit Git und entweder **Docker** oder **nginx + PHP 8.2+ (php-fpm, pdo_mysql, gd, mbstring) + MariaDB**.

#### 1. Code holen

Das Repository ist privat, der Server braucht daher einen **Deploy-Key** (nur Lesezugriff):

```bash
sudo adduser --system --group --shell /bin/bash --home /home/sams sams   # eigener Benutzer für die Anwendung
sudo -u sams ssh-keygen -t ed25519 -N "" -f /home/sams/.ssh/id_ed25519
sudo cat /home/sams/.ssh/id_ed25519.pub
```

Den angezeigten Schlüssel in GitHub unter **Repository → Settings → Deploy keys → Add deploy key** eintragen (ohne Schreibrecht). Dann:

```bash
sudo mkdir -p /var/www/sams && sudo chown sams:www-data /var/www/sams
sudo -u sams git clone git@github.com:Wolfsrudel-Gaming/SAMS-Documents.git /var/www/sams
cd /var/www/sams
sudo -u sams cp .env.example .env
sudo -u sams nano .env        # Passwörter, APP_URL, UPDATE_BRANCH setzen
```

> Die Vorlagen in `deploy/` gehen von `/var/www/sams` und dem Benutzer `sams` aus.

#### 2a. Start mit Docker

```bash
docker compose up -d --build
docker compose exec -T app php bin/sams migrate
docker compose exec app php bin/sams create-admin admin
```

In der `.env` für Docker: `DB_HOST=db` und `UPDATE_PHP_CMD=docker compose exec -T app php`.
Der Web-Container lauscht auf `127.0.0.1:8080` – davor gehört ein Reverse-Proxy mit HTTPS (Vorlage: [`deploy/nginx-proxy.conf`](deploy/nginx-proxy.conf)). Der Benutzer `sams` muss Docker benutzen dürfen (`sudo usermod -aG docker sams`).

#### 2b. Start ohne Docker

- Datenbank und Benutzer in MariaDB anlegen, Zugangsdaten in `.env` eintragen (`DB_HOST=127.0.0.1`)
- nginx: Vorlage [`deploy/nginx.conf`](deploy/nginx.conf) – **Webroot ist ausschließlich `public/`**
- `storage/` muss für PHP-FPM beschreibbar sein: `sudo chgrp -R www-data storage && sudo chmod -R g+rwX storage && sudo find storage -type d -exec chmod g+s {} +`
- Datenbank anlegen und Admin-Konto erstellen:

```bash
php bin/sams migrate
php bin/sams create-admin admin
```

(Alternativ führt `https://eure-domain/install.php` im Browser durch die Einrichtung.)

#### 3. Auto-Update einrichten

`bin/update.sh` prüft per `git fetch`, ob es auf dem eingestellten Branch (`UPDATE_BRANCH`) einen neuen Commit gibt. Wenn ja:

1. Datenbank-Backup (`storage/backups/…-pre-update…`)
2. Wartungsmodus an (Nutzer sehen „Update wird eingespielt“)
3. Code auf den neuen Stand bringen – versehentliche lokale Änderungen werden vorher als Patch in `storage/update/` gesichert
4. Datenbank-Migrationen ausführen
5. Wartungsmodus aus, optional Meldung nach Discord (`UPDATE_DISCORD_WEBHOOK`)

**Schlägt eine Migration fehl,** werden Code und Datenbank automatisch auf den vorherigen Stand zurückgesetzt. Der fehlerhafte Commit wird nicht erneut versucht, bis ein neuer Commit kommt oder im Admin-Bereich „Jetzt auf Updates prüfen“ geklickt wird.

Das Einrichtungsskript erledigt diesen Schritt automatisch. Manuell:

**Timer (systemd, empfohlen)** – prüft alle 2 Minuten:

```bash
sudo cp deploy/systemd/sams-update.* deploy/systemd/sams-backup.* /etc/systemd/system/
sudo nano /etc/systemd/system/sams-update.service   # User und Pfade prüfen
sudo systemctl daemon-reload
sudo systemctl enable --now sams-update.timer sams-update.path sams-backup.timer
```

Alternativ per Cron: [`deploy/crontab.example`](deploy/crontab.example).

**Sofort-Update per GitHub-Webhook (optional):** In GitHub unter *Settings → Webhooks* eintragen:
Payload-URL `https://eure-domain/hooks/github.php`, Content type `application/json`, Secret = `UPDATE_WEBHOOK_SECRET` aus der `.env`, Event „push“.
Der Webhook legt nur eine Anforderung ab; `sams-update.path` startet das Update dann sofort.

**Docker:** Ändert ein Update das Docker-Setup (`Dockerfile`, `docker-compose.yml`, `docker/`), baut das Update-Skript die Container automatisch neu.

**Kontrolle:** Admin-Bereich → *System & Updates* zeigt Version, ob der Timer läuft, das letzte Update, das Update-Protokoll und die Backups. Für Monitoring gibt es `https://eure-domain/health.php`.

> Wichtig: Auf dem Server nie direkt Code ändern – alle Änderungen kommen über GitHub. Konfiguration (`.env`) und Daten (`storage/`) werden vom Update nicht angefasst.

### Kommandozeile

```
php bin/sams status                  Version, Datenbank, offene Migrationen, letztes Update
php bin/sams migrate                 Datenbank-Migrationen ausführen
php bin/sams create-admin [name]     Hauptadministrator anlegen
php bin/sams reset-password <name>   Notfall: Einmalpasswort setzen, Sperre und 2FA aufheben
php bin/sams backup [tag]            Backup erstellen        php bin/sams backups   Backups auflisten
php bin/sams restore <datei>         Backup einspielen
php bin/sams maintenance on|off      Wartungsmodus
bin/update.sh [--force]              Update sofort ausführen
```

Mit Docker jeweils `docker compose exec -T app php bin/sams …` voranstellen.

### Backups

Täglich um 04:30 (`sams-backup.timer`) und vor jedem Update. Aufbewahrung: `BACKUP_KEEP_DAYS` (Standard 14 Tage), Ablage in `storage/backups/`. Für echte Ausfallsicherheit den Ordner zusätzlich auf einen anderen Server kopieren.

### Im Spiel: Tablet, Drucker und Dokument-Items

Die FiveM-Resource [`fivem/sams_tablet`](fivem/sams_tablet) bringt das System ins Spiel: Anmeldung im Tablet mit den normalen Zugangsdaten, Drucker in der Spielwelt (Verwaltung im Tablet per „Aktuelle Position übernehmen“), Druckaufträge, die am Drucker als ox_inventory-Item abgeholt werden, und Dokument-Items zum Ansehen (Klemmbrett-Animation), Zeigen und Weitergeben. Installation und Einstellungen: [`fivem/sams_tablet/README.md`](fivem/sams_tablet/README.md).

Voraussetzung ist HTTPS für das Websystem (das Einrichtungsskript richtet es ein), da das Tablet die Seite einbettet.

### API für FiveM-Resourcen

In der `.env` steht `API_KEY` (leer = API deaktiviert). Den Schlüssel nur serverseitig verwenden (server.lua bzw. `set sams_api_key` in der server.cfg), nie im Client.

```
GET  api.php?action=verify&code=K7F3QX9M          → Dokument prüfen (z. B. für LSPD-/checkdoc-Befehl)
GET  api.php?action=patient&citizen_id=ABC12345   → Notfalldaten (Blutgruppe, Allergien, Vorerkrankungen …)
GET  api.php?action=documents&citizen_id=ABC12345 → Dokumente eines Bürgers
GET  api.php?action=printers                      → Drucker in der Spielwelt
POST api.php?action=tablet_login | tablet_session | tablet_launch | tablet_logout
POST api.php?action=printer_jobs | job_pickup | jobs_since | document_item
Header: X-API-Key: <api_key>
```

Details zu allen Aktionen stehen im Kopf von [`public/api.php`](public/api.php).

## Eigene Dokumentarten

Dokumentarten sind in `app/documents.php` (Felder, Rolle, Kürzel) und `app/templates/<name>.php` (Druckvorlage)
definiert. Für ein neues Dokument einen Eintrag in `document_types()` ergänzen und eine Vorlage anlegen – Formular,
Validierung, Speicherung, Nummerierung und Echtheitsprüfung funktionieren automatisch. Feldtypen: `text`, `textarea`,
`date`, `time`, `number`, `select`, `radio`, `checkbox`, `checkboxes`, `items` (Tabellenzeilen), `bodymap`
(Körperschema), `signature` (Unterschrift auf dem Gerät), `heading`.

## Projektstruktur

```
public/              Webroot (einziger öffentlich erreichbarer Ordner)
  index.php          Router für alle internen Seiten
  verify.php         Öffentliche Echtheitsprüfung
  api.php            JSON-API für FiveM
  health.php         Statusabfrage für Monitoring
  hooks/github.php   GitHub-Webhook für Sofort-Updates
  assets/            CSS, JS, Logo, Schriften
app/                 Anwendungslogik, Seiten (pages/), Druckvorlagen (templates/)
database/migrations/ Datenbank-Migrationen (laufen automatisch beim Update)
bin/                 sams (Kommandozeile), update.sh (Auto-Update)
deploy/              systemd-Units, Cron, nginx-Vorlage
docker/, Dockerfile, docker-compose.yml
storage/             Datenbank (SQLite), Logs, Backups, Update-Status – nicht im Git
docs/                Konzept
```

**Neue Datenbank-Änderungen** immer als neue Datei in `database/migrations/` (z. B. `0003_ranks.php`) anlegen, nie eine bestehende Migration ändern.

## Hinweis

Fiktives System für Roleplay-Zwecke. Alle Einrichtungen, Kennungen und Formularnummern sind frei erfunden;
die Dokumente sind nicht für reale Verwendung bestimmt.

Verwendete Bibliotheken: [qrcode-generator](https://github.com/kazuhikoarase/qrcode-generator) (MIT),
[html2canvas](https://html2canvas.hertzen.com/) (MIT), Schrift „Herr Von Muellerhoff“ (SIL OFL 1.1).
