# SAMS Dokumenten- & Aktensystem – Konzept Version 1

Stand: 17.10.2026 · Status: **freigegeben** · Grundgerüst, Design und Etappe 1–4 umgesetzt

Dieses Konzept fasst die Fragerunde (60 Fragen) und die anschließenden Festlegungen zusammen.
Es beschreibt, was in Version 1 gebaut wird, in welcher Reihenfolge, und was bewusst später kommt.

---

## 1. Ziel

Ein Akten-, Dokumenten- und Verwaltungssystem für den SAMS auf einem FiveM-Roleplay-Server:

- Medics führen Patientenakten und stellen Dokumente aus, die wie echte Dokumente aussehen
  (deutsch-amerikanischer Stil: deutsche Sprache und Aufbau, San-Andreas-Setting, Dollar).
- Bedienung im Browser **und** auf einem eigenen Tablet im Spiel.
- Ausgelegt für ein kleines Startteam, technisch aber für **bis zu 500 Mitarbeitende**.
- Vorbereitet für weitere Fraktionen (LSPD, DOJ, Feuerwehr) und für einen späteren Verkauf an andere Server.

## 2. Festlegungen aus der Fragerunde

| Thema | Entscheidung |
|---|---|
| Hosting | Eigener Linux-vServer, Betreuung durch den Admin selbst |
| Datenbank | Eigene MySQL/MariaDB (getrennt von der FiveM-Datenbank) |
| Framework | Standalone – keine Anbindung an QBCore/ESX nötig |
| Login V1 | **Konten mit Benutzername/Passwort**, angelegt durch Admin bzw. Chiefs. Jeder ändert sein Passwort selbst. Dieselben Zugangsdaten gelten im Tablet. |
| Login später | Discord-Login und automatische Anmeldung im Spiel als Zusatz |
| Charaktere | Keine automatische Übernahme aus dem Spiel. Patientenakten werden manuell angelegt. |
| Ränge/Rechte | Frei konfigurierbare Ränge mit einzelnen Rechten. Später können Discord-Rollen den Rang steuern. |
| Abteilungen | Rettungsdienst/Notarzt, Klinik/Chirurgie, Psychologie/Psychiatrie, Luft-/Wasserrettung. Teilweise getrennt: Stammdaten für alle, Fachbereichseinträge (z. B. Psychologie) nur für die Abteilung. |
| Tablet | **Eigenes Tablet** (eigene FiveM-Resource), zusätzlich Browser |
| Dokument-Items | ox_inventory, Dokument wird beim Zeigen mit Papier-Prop + Animation als Original angezeigt |
| Dokumente | Alle bisherigen + Psych. Gutachten, Impfnachweis, OP-Bericht/Aufklärung, interne Dokumente, „alles, was anfallen kann“ |
| Vorlagen | Vorlagen-Editor im Browser für eigene Dokumentarten |
| Unterschrift | Hinterlegt oder live – wählbar pro Dokument |
| Design | Standard-Logo, im Admin-Panel änderbar. Oberfläche im Stil echter Krankenhaus-Software (KIS). |
| Abläufe | Freigaben/Gegenzeichnung pro Dokumentart einstellbar. Entwürfe speicherbar und an Kollegen übergebbar. Korrektur per Nachtrag. |
| Fallakte | Ein Fall bündelt alle Dokumente eines Vorfalls und füllt sie vor. Schnell- und Vollmodus. |
| Abrechnung | Rechnungen mit Status (offen/bezahlt/gemahnt), Preisliste mit begründeter Abweichung. Versicherungen existieren im Spiel. |
| Behörden | Freigabe einzelner Dokumente für LSPD/Justiz |
| Bürger | Bürgerportal: eigene Dokumente, Termine buchen, Anträge, Bewerbungen |
| Personal | Komplette Personalverwaltung, Ausbildung mit Prüfungen, internes Wiki |
| Dienstzeit | On-/Off-Duty im Spiel, manuell als Ausweichlösung |
| Kommunikation | Schwarzes Brett und interne Nachrichten |
| Discord | Benachrichtigungen (Freigaben, Personal-Log, Dokument-Log, DM an Patienten). Es gibt einen Haupt- und einen SAMS-Discord. |
| Statistik | Aktivität, Fallzahlen, Finanzen, Qualität. Wochenbericht nach Discord und als PDF. |
| Datenschutz | Archivierung statt Löschen, tägliche Backups, 2FA-Pflicht für die Leitung, Zugriffsprotokoll für Leitung und Datenschutz-Rolle |
| Leitstelle | **Nicht in V1.** Später mit Live-Karte, Disponent und Selbstdisposition. |
| Geräte | PC muss voll funktionieren, Tablet mit Stift als Bonus |

## 3. Technischer Aufbau

**Entscheidung: Wir bauen auf dem bestehenden PHP-Code weiter**, räumen ihn aber auf, damit er die
Anforderungen (500 Nutzer, Tablet, mehrere Fraktionen, Verkauf) trägt:

- **PHP 8.2+**, MySQL/MariaDB als Standard (SQLite bleibt nur zum lokalen Testen)
- **Datenbank-Migrationen** statt eines festen Schemas, damit Updates ohne Datenverlust laufen
- **Docker-Compose-Setup** (PHP, nginx, MariaDB) plus Anleitung für die Installation ohne Docker
- **Mandantenfähigkeit vorbereitet:** Kerndaten erhalten eine Fraktions-Zuordnung, damit später
  LSPD/DOJ eigene Bereiche bekommen können
- **Interne JSON-API** für das Tablet (Anmeldung per Token mit denselben Zugangsdaten)
- **Echtzeit-Dienst** (kleiner separater Dienst, z. B. Node.js mit WebSockets) erst, wenn er gebraucht
  wird: Benachrichtigungen im Tablet, später die Leitstelle
- Die bestehenden Dokument-Vorlagen bleiben erhalten und werden weiterentwickelt

## 4. Etappen

Jede Etappe endet mit einem testbaren Stand. Nach jeder Etappe gibt es Feedback, bevor die nächste startet.

### Etappe 1 – Benutzer-, Rang- und Rechteverwaltung ✅ *umgesetzt in v1.3.0*

Ziel: Der Admin kann sofort Chiefs anlegen, Chiefs legen ihre Leute an, jeder meldet sich an und ändert sein Passwort.

**Konten**
- Anlegen durch Admin oder berechtigte Chiefs: Benutzername, Name, Titel, Dienstnummer, Rang,
  Abteilung(en), Qualifikationen, Telefon, optional Discord-ID (für die spätere Verknüpfung)
- Das System erzeugt ein **Startpasswort**, das einmal angezeigt wird (zum Kopieren und Weitergeben)
- **Pflicht zur Passwortänderung beim ersten Login**
- **Passwort zurücksetzen** durch Chiefs, ergibt ein neues Einmalpasswort
- **Deaktivieren statt Löschen**, mit Grund (z. B. Kündigung). Ausgestellte Dokumente bleiben gültig. Wiederaktivierung ist möglich.
- **Alle Sitzungen beenden**, z. B. bei Kündigung oder Verdacht auf Missbrauch
- Benutzerliste mit Suche, Filtern (Rang, Abteilung, aktiv/inaktiv) und Seitenaufteilung, für 500 Konten ausgelegt
- Eigenes Profil: Passwort ändern, Angaben auf Dokumenten, Unterschrift

**Ränge und Rechte**
- Ränge frei anlegbar: Name, Kürzel, Rangstufe (Hierarchie), Farbe, Abteilung (optional)
- Jedem Rang werden einzelne Rechte per Häkchen zugewiesen (Katalog siehe Abschnitt 5)
- **Hierarchie-Regel:** Wer Konten verwalten darf, kann nur Personen mit einem *niedrigeren* Rang
  anlegen, bearbeiten oder befördern und nur niedrigere Ränge vergeben
- Das **Admin-Konto** aus der Installation hat immer alle Rechte und kann nicht gelöscht oder herabgestuft werden
- Vorlage beim Einrichten: eine sinnvolle Rangstruktur (Admin, Chief, Oberarzt, Arzt, Notfallsanitäter, Rettungssanitäter, Auszubildende/r), frei änderbar
- Abteilungen sind als eigene Tabelle verwaltbar

**Sicherheit**
- Login-Schutz gegen Durchprobieren, in der Datenbank gespeichert (pro Benutzer und IP)
- **2FA (Authenticator-App)** ist Pflicht für Ränge mit dem Recht „2FA erforderlich“ (Standard: Leitung)
- Jede Änderung an Konten, Rängen und Rechten wird protokolliert (wer, was, vorher → nachher)

**Umstellung des Bestands:** die bisherigen vier festen Rollen werden zu Rängen mit passenden Rechten migriert.

### Etappe 2 – Akten und Fallakte ✅ *umgesetzt in v1.5.0*
- Patientenakten erweitert: Archivierung (schreibgeschützt, reaktivierbar), Abteilungs-Sichtbarkeit einzelner Einträge (z. B. nur Psychologie)
- **Fallakte:** ein Fall (z. B. ein Verkehrsunfall) mit Fallnummer `SAMS-F-JJJJ-NNNNN` bündelt Einträge und Dokumente; „Dokument aus Fall“ übernimmt Diagnose, Ort, Hergang und Datum
- Fälle abschließen / wieder öffnen; in abgeschlossene Fälle keine neuen Einträge
- Archiv mit Suche (Filter Aktiv/Archiv/Alle), **Archivieren statt Löschen**: endgültiges Löschen nur noch aus dem Archiv mit eigenem Recht
- Neue Rechte: `patients.archive`, `cases.manage`, `entries.all_departments`
- *Offen für später:* Verknüpfung mit dem Bürgerportal (Etappe 6)

### Etappe 3 – Dokumente ✅ *umgesetzt in v1.6.0 – v1.10.0*
- ✅ Neue Dokumentarten *(v1.9.0)*: psychologisches Gutachten, Unterbringungsantrag (5150), Impfnachweis, OP-Bericht,
  OP-Aufklärung/Einwilligung, Dienstausweis, Ausbildungsnachweis, Beförderungsurkunde, Abmahnung,
  Kündigung, Arbeitsvertrag; Personaldokumente beziehen sich auf ein Personalkonto
- ✅ **Entwürfe:** speichern (auch unvollständig), später fertigstellen, an Kollegen übergeben (mit Hinweis)
- ✅ **Freigaben:** pro Dokumentart einstellbar unter *Verwaltung → Dokumentarten* („Freigabe nötig unterhalb Rang X, freigeben ab Rang Y“); Gegenzeichnung erscheint auf dem Dokument und in der Echtheitsprüfung, Zurückweisen mit Begründung
- ✅ **Nachträge** statt Änderungen: eigenes Blatt am Dokument, in der Echtheitsprüfung mit Datum und Verfasser (ohne Inhalt)
- ✅ **Unterschrift:** hinterlegt oder live, beim Ausstellen wählbar
- ✅ **Schnell- und Vollmodus** pro Dokumentart (Standard einstellbar, im Formular umschaltbar)
- ✅ **Preisliste** (Gebührenordnung) im Admin-Panel, Preis wird in Rechnungen automatisch übernommen, Abweichungen und freie Positionen nur mit Begründung (steht auf der Rechnung). **Rechnungsstatus** offen/bezahlt/gemahnt (bis 3. Mahnung, die Rechnung wird dann als Mahnschreiben gedruckt), Rechnungsübersicht mit Summen und Überfälligkeit *(v1.7.0)*
- ✅ **Freigabe für Behörden:** einzelne Dokumente für LSPD/BCSO/Justiz per geheimem Link (24 h / 7 / 30 Tage / bis Widerruf) oder an ein Behördenkonto (eigener Rang mit nur „Freigegebene Dokumente ansehen“) freischalten; Abrufe werden gezählt und protokolliert, jederzeit widerrufbar *(v1.8.0)*
- ✅ **Vorlagen-Editor** *(v1.10.0)*: Leitung legt neue Dokumentarten im Browser an (Felder per Klick, Layout Brief/Formular/Protokoll, Text mit Platzhaltern wie `{{patient.name}}` und `**fett**`, Stempel, Bezug Patient oder Personal, Ausstellrechte je Rang). Die Vorlage wird beim Ausstellen eingefroren – spätere Änderungen verändern keine bestehenden Dokumente.
- ✅ Logo, Farben und Briefkopf im Admin-Panel

### Etappe 4 – Tablet und FiveM-Resource ✅ *Tablet, Drucker und Dokument-Items v1.4.0, Dienstzeit v1.11.0*
- Eigene Resource `sams_tablet`: Tablet-Item und Befehl, eigene NUI-Oberfläche mit Tablet-Rahmen und Animation
- Anmeldung im Tablet mit denselben Zugangsdaten, die Sitzung bleibt pro Spieler gemerkt
- Dokumente werden als **ox_inventory-Item mit Metadaten** an den Patienten übergeben
- Dokument zeigen: Papier-Prop, Animation, Original-Ansicht beim Gegenüber
- ✅ On-/Off-Duty: `/dienst` im Spiel oder Schalter in der Kopfleiste des Websystems (dieselbe Schicht), automatisches Ausstempeln beim Abmelden/Verlassen, vergessene Schichten enden nach einstellbarer Höchstdauer; „Jetzt im Dienst“, Wochenstunden, Korrekturen und Nachträge durch die Leitung; `IsOnDuty`-Export und `sams:dutyChanged`-Event für andere Resourcen
- Absicherung: Die Resource spricht serverseitig mit einem API-Schlüssel, nie aus dem Client

### Etappe 5 – Personal, Ausbildung, Wiki, Kommunikation
- **Personalakte:** Lebenslauf im Dienst, Beförderungen, Abmahnungen, Notizen, Qualifikationen, Dienstzeiten
- **Ausbildung:** Ausbildungspläne, Module, Online-Prüfungen, automatische Zertifikate/Urkunden
- **Wiki:** SOPs, Medikamentenliste, Behandlungsabläufe. Pflegt auch die Vorschlagslisten in den Formularen.
- **Schwarzes Brett** mit Lesebestätigung, **interne Nachrichten**

### Etappe 6 – Bürgerportal *(benötigt Discord-Login für Bürger)*
- Discord-Login für Bürger, Verknüpfung mit der Patientenakte durch einen Medic
- Eigene Dokumente ansehen und herunterladen
- Termine anfragen (z. B. Waffenschein-Untersuchung), Bestätigung durch den SAMS
- Anträge (Aktenkopie, Schweigepflichtentbindung, Beschwerde) und Bewerbungen

### Etappe 7 – Statistik und Discord
- Dashboards: Aktivität pro Mitarbeiter, Fallzahlen, Finanzen, Qualität
- Wochen- und Monatsbericht automatisch nach Discord und als PDF
- Discord-Webhooks: Freigaben, Personal-Log, Dokument-Log, DM an Patienten
- Discord-Login für Mitarbeitende und **Rollen-Sync** (Discord-Rolle → Rang)

### Später (nach Version 1)
- Leitstelle: Notrufe aus dem Spiel, Live-Karte, Fahrzeugstatus, Disponent und Selbstdisposition
- Mehrere Standorte mit eigenem Briefkopf
- Rezept in der Apotheke gegen Medikamenten-Items einlösen
- Weitere Fraktionen (LSPD, DOJ, Feuerwehr)
- Verkaufsfähigkeit: Mehrsprachigkeit, Installer, Lizenzierung, neutrale Standardtexte

## 5. Rechte-Katalog (Etappe 1)

Rechte werden pro Rang vergeben. Die Liste wächst mit jeder Etappe.

| Bereich | Rechte |
|---|---|
| Benutzer | ansehen · anlegen · bearbeiten · Passwort zurücksetzen · deaktivieren · Sitzungen beenden |
| Ränge | Ränge und Rechte verwalten · Abteilungen verwalten |
| Akten | ansehen · anlegen/bearbeiten · archivieren · löschen · vertrauliche Einträge sehen · Abteilungseinträge sehen (je Abteilung) |
| Dokumente | ausstellen (je Dokumentart) · eigene stornieren · alle stornieren · freigeben/gegenzeichnen · für Behörden freigeben |
| Verwaltung | Einstellungen · Preisliste · Vorlagen-Editor |
| Kontrolle | Zugriffsprotokoll sehen (Leitung, Datenschutz) · Statistiken sehen |
| Sicherheit | 2FA erforderlich |

## 6. Datenmodell (Überblick)

```
factions        Fraktionen (vorerst nur SAMS)
departments     Abteilungen
ranks           Ränge (Stufe, Farbe, Abteilung) ── rank_permissions (Rang ↔ Recht)
users           Konten (Rang, Status, Passwort, 2FA, Discord-ID) ── user_departments, user_qualifications
sessions        aktive Sitzungen (Browser und Tablet), einzeln beendbar
login_attempts  Login-Schutz
patients        Patientenakten (Archiv-Status)
cases           Fallakten ── entries, documents
documents       Dokumente (Status: Entwurf, wartet auf Freigabe, gültig, storniert) ── document_addenda (Nachträge)
templates       eigene Dokumentarten aus dem Vorlagen-Editor
audit_log       Protokoll (inkl. vorher/nachher bei Änderungen)
settings        Einstellungen, Logo, Briefkopf
```

## 7. Sicherheit und Datenschutz
- Passwörter nur als Hash (bcrypt/argon2), Startpasswörter einmalig sichtbar
- 2FA für Leitungsränge, Login-Schutz, Sitzungsverwaltung
- Rechteprüfung serverseitig bei jeder Aktion, nicht nur in der Oberfläche
- Zugriffsprotokoll für Akten und Dokumente
- Archivierung statt Löschen, tägliches automatisches Datenbank-Backup mit Aufbewahrungsfrist
- HTTPS-Pflicht im Betrieb, Docker-Setup mit nginx

## 8. Entscheidungen zu Etappe 1
1. **Dienstnummern** werden vorerst manuell eingetragen (müssen eindeutig sein).
2. **Startpasswort** wird einmal angezeigt und von der Leitung weitergegeben (Kopierknopf für eine fertige Nachricht).
3. **Rangstruktur** ist frei anlegbar. Vorlage: Administrator (100) → Ärztliche Direktion/Chief (90) → Oberarzt (70) → Arzt (50) → Notfallsanitäter (30) → Rettungssanitäter (20) → Auszubildende (10).
4. **Abteilungsbeschränkung** ist ein Recht pro Rang („nur Personal der eigenen Abteilungen verwalten“).
5. **Mehrere Abteilungen pro Person** sind erlaubt. Abteilungen und Qualifikationen sind frei anlegbar.
