<?php
/**
 * Handbuch für Mitarbeitende – wird als Wiki-Kategorie „Handbuch“ angelegt
 * (nur fehlende Seiten, eigene Änderungen bleiben erhalten).
 */
return [
    'Handbuch: Erste Schritte' => <<<'MD'
Willkommen! Dieses Handbuch erklärt die wichtigsten Abläufe. Die Seiten kannst du wie jede Wiki-Seite an euren Dienst anpassen.

## Anmelden
- Im Browser mit Benutzername und Passwort (beim ersten Login vergibst du ein eigenes Passwort), im Spiel über das **Tablet** mit denselben Daten.
- Leitung und Ärzte haben meist die **Zwei-Faktor-Anmeldung** – einrichten unter *Mein Profil → Zwei-Faktor*.
- Unter *Mein Profil* hinterlegst du einmal deine **Unterschrift**; sie wird auf alle Dokumente gedruckt.

## Die Oberfläche
- Links das Menü – du siehst nur, was dein Rang darf.
- Oben die **Suche**: Name, Bürger-ID, Dokument-Nr., Prüfcode, Diagnose, Wiki … Exakte Nummern öffnen direkt.
- Die **Glocke** zeigt persönliche Benachrichtigungen (Freigaben, Nachrichten, Dienstplan). Auf Wunsch kommen sie auch per Discord-DM (*Benachrichtigungen → Einstellungen*).
- Der Schalter **Im Dienst / Außer Dienst** stempelt deine Dienstzeit.

## Weiter
- [[Handbuch: Patientenakten und Fälle]]
- [[Handbuch: Dokumente ausstellen]]
- [[Handbuch: Dienstzeit und Dienstplan]]
- [[Handbuch: Medikamente und BtM-Buch]]
- [[Handbuch: Im Spiel]]
- [[Handbuch: Für die Leitung]]
MD,
    'Handbuch: Patientenakten und Fälle' => <<<'MD'
## Akte suchen oder anlegen
- *Patientenakten* → suchen nach Name, Bürger-ID, Telefon. Gibt es keine Akte: **Neue Patientenakte**.
- Die **Bürger-ID** ist die Kennung aus dem Spiel – damit finden Tablet und Bürgerportal die richtige Akte.
- Allergien und Kennzeichnungen erscheinen als Warnhinweis oben in der Akte.

## Einträge
- **Akteneintrag** für jede Behandlung, jeden Einsatz, jedes Gespräch. Vorlagen (SOAP, ABCDE) helfen beim Ausfüllen.
- **Vertraulich** = nur ärztlicher Dienst; **nur Abteilung** = z. B. nur Psychologie.
- Einträge sind nachvollziehbar: Änderungen werden protokolliert.

## Fälle
- Ein **Fall** bündelt alles zu einem Ereignis (Verkehrsunfall, stationärer Aufenthalt). „Dokument aus Fall“ übernimmt Diagnose, Ort und Hergang.
- Abgeschlossene Fälle bleiben lesbar.

## Anhänge
- Fotos, Röntgenbilder, Scans oder PDF unter *Anhänge* in Akte oder Fall hochladen. Bilder werden verkleinert und von Metadaten befreit.

## Doppelte Akten
- *Patientenakten → Doppelte Akten*: das System schlägt mögliche Doppelungen vor. Beim **Zusammenführen** wählst du je Feld den richtigen Wert; alles wandert in die Zielakte.

## Archiv
- Akten werden archiviert, nicht gelöscht. Archivierte Akten sind schreibgeschützt und lassen sich reaktivieren.
MD,
    'Handbuch: Dokumente ausstellen' => <<<'MD'
## Ablauf
1. *Neues Dokument* → Dokumentart wählen (nur die, die dein Rang ausstellen darf).
2. Patient/in wählen, Felder ausfüllen. Vorschläge (ICD-10, Medikamente …) erscheinen beim Tippen.
3. **Vorschau** prüfen, dann **Ausstellen**. Das Dokument bekommt eine Nummer und einen **Prüfcode/QR-Code**.

## Entwürfe, Übergabe, Freigabe
- **Entwurf speichern** und später weitermachen, oder an eine Kollegin/einen Kollegen **übergeben**.
- Manche Dokumentarten brauchen eine **Freigabe** (z. B. bei Auszubildenden). Dann reichst du ein, und eine berechtigte Person zeichnet gegen. Du wirst über die Glocke benachrichtigt.

## Nach dem Ausstellen
- Ausgestellte Dokumente werden **nicht geändert** – stattdessen gibt es **Nachträge** oder die **Stornierung** mit Begründung.
- **Im Spiel drucken**: Drucker wählen, am Drucker abholen – das Dokument ist dann ein Item im Inventar.
- **Für Behörden freigeben**: geheimer Link mit Ablaufzeit oder direkt an ein LSPD-/Justiz-Konto.
- Jede/r kann die Echtheit unter *Echtheitsprüfung* (QR-Code) prüfen – ohne medizinische Inhalte.

## Rezepte
- Positionen möglichst aus der Vorschlagsliste wählen – dann erkennt die Apotheke das Medikament sicher. „2x …“ am Anfang = zwei Packungen.
- **Betäubungsmittel** nur auf dem **BtM-Rezept** (gelb). Rezepte gelten 28 Tage, BtM-Rezepte 7 Tage.
- Eingelöst wird in der Klinik („In der Klinik ausgeben“ am Rezept) oder in der Apotheke im Spiel.

## Standorte
- Gibt es mehrere Standorte, wählst du beim Ausstellen den **Briefkopf**. Vorausgewählt ist dein Standort.
MD,
    'Handbuch: Dienstzeit und Dienstplan' => <<<'MD'
## Dienstzeit stempeln
- Oben rechts **Im Dienst / Außer Dienst**, im Spiel mit `/dienst` oder im Tablet. Beides ist dieselbe Schicht.
- Vergessene Schichten enden automatisch nach der eingestellten Höchstdauer. Korrekturen macht die Leitung unter *Dienstzeiten*.

## Dienstplan
- Unter *Dienstplan* siehst du die Woche mit allen Schichten. **Rot** = unterbesetzt, **grün** = genug Personal.
- **Eintragen** in eine Schicht mit freien Plätzen. **Austragen** ist bis kurz vor Beginn möglich (Standard: 12 Stunden).
- Danach: **Zum Tausch anbieten** – du bleibst eingeteilt, bis jemand **übernimmt**. Kolleg/innen werden benachrichtigt.
- Eine Stunde vor Beginn erinnert dich die Glocke. Bei laufenden und vergangenen Schichten zeigt ✓ deine tatsächlich gestempelte Zeit.

## Für die Planung
- Schichten anlegen (auch für mehrere Wochentage auf einmal), Mindest- und Höchstbesetzung festlegen, Personal **einteilen**.
- **Schichten der Vorwoche übernehmen** kopiert den Plan ohne Einteilung.
- 24 Stunden vor einer unterbesetzten Schicht meldet das System das an alle mit Planungsrecht.
MD,
    'Handbuch: Medikamente und BtM-Buch' => <<<'MD'
## Bestand
- *Medikamente & BtM* zeigt alle Artikel mit Bestand. Rot = Mindestbestand erreicht (die Leitung wird benachrichtigt).
- **Buchen**: Entnahme (mit Patient → automatischer Akteneintrag), Zugang, Verwurf, Korrektur. Der Bestand kann nie negativ werden.
- Jede Buchung wird mit Zeit, Person und Grund protokolliert.

## Betäubungsmittel (BtM)
- Entnahme **nur mit Patient**, Verwurf/Vernichtung **nur mit Zeuge** (zweite Person).
- Zugang und Korrektur nur, wer das BtM-Buch führt.
- Das **BtM-Buch** zeigt je Mittel eine Karteikarte pro Monat mit Übertrag, Zugang, Abgang und Bestand – druckbar.
- **Bestand prüfen**: tatsächlich gezählte Menge eintragen. Stimmt sie nicht, zuerst eine Korrektur mit Begründung buchen.

## Rezepte einlösen
- In der Klinik: am Rezept **In der Klinik ausgeben** – die einlösbaren Positionen werden ausgebucht.
- In der Apotheke im Spiel gibt es die passenden Items. Jede Rezeptposition lässt sich nur einmal einlösen.
MD,
    'Handbuch: Im Spiel' => <<<'MD'
## Tablet
- Tablet-Item benutzen oder `/tablet`. Einmal anmelden – die Anmeldung bleibt erhalten, bis du dich abmeldest.
- Im Tablet funktioniert alles wie im Browser.

## Drucker
- Beim Drucken wählst du einen Drucker in der Spielwelt. Sobald der Auftrag bereit ist, bekommst du eine Meldung (und einen Wegpunkt).
- Am Drucker abholen (ox_target oder Taste E) – das Dokument landet als Item im Inventar.

## Dokument-Items
- Benutzen = ansehen (Klemmbrett), **Zeigen** = einer Person in der Nähe zeigen, Weitergeben wie jedes Item.
- Stornierte Dokumente werden beim Ansehen als storniert markiert.

## Apotheke
- Mit einem Rezept im Inventar an die Apotheke gehen und **Rezept einlösen** wählen. Du erhältst die Medikamenten-Items; das Rezept bleibt als „eingelöst“ im Inventar.

## Dienst
- `/dienst` stempelt ein und aus (Tablet-Anmeldung nötig). Beim Verlassen des Servers wirst du automatisch ausgestempelt.
MD,
    'Handbuch: Für die Leitung' => <<<'MD'
## Personal
- *Personal* → Konto anlegen: das System erzeugt ein Startpasswort. Rang, Abteilungen, Qualifikationen und Dienstnummer vergeben.
- Man verwaltet nur Konten und Ränge **unterhalb** der eigenen Rangstufe und vergibt nur Rechte, die man selbst hat.
- Die **Personalakte** führt die Laufbahn automatisch (Rang, Qualifikationen, Beförderungen, Abmahnungen).

## Ränge und Rechte
- *Ränge & Rechte*: jedes Recht einzeln, z. B. pro Dokumentart. 2FA-Pflicht pro Rang.

## Einstellungen
- **Briefkopf**, Logo, Farben, Bankverbindung, Nummernkreise unter *Einstellungen*; weitere Standorte mit eigenem Briefkopf unter *Standorte*.
- Rezept-Gültigkeit, Sperrfrist im Dienstplan, automatische Abmeldung (Opt-in) und Größe von Anhängen ebenfalls dort.
- **Dokumentarten**: Freigabepflicht und Modus je Art; eigene Dokumentarten im **Vorlagen-Editor**.

## Kontrolle
- **Zugriffsprotokoll**: wer hat wann welche Akte geöffnet oder welches Dokument ausgestellt.
- **Statistik**: Aktivität, Fallzahlen, Finanzen; Wochen-/Monatsbericht nach Discord.

## System
- Updates kommen automatisch von GitHub. Vor dem Einspielen wird gesichert, danach getestet – bei einem Fehler wird automatisch zurückgerollt. Stand und Verlauf unter *System & Updates*.
MD,
];
