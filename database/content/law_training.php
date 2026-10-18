<?php
/** Ausbildungsinhalte Anwaltskanzlei: Lehrgänge mit Lernmodulen und Prüfungsfragen. */
return [
    [
        'title' => 'Grundlagen der Kanzleiarbeit',
        'description' => 'Basislehrgang für alle Mitarbeitenden einer Anwaltskanzlei. Vermittelt Rolle und Pflichten der Anwaltschaft, Mandatsannahme und Interessenkonflikt-Prüfung, Verschwiegenheit, Fristenkontrolle und Aktenführung sowie die Arbeit mit Mandantenakten, Vollmachten, Schriftsätzen und Behördenanfragen im Portal.',
        'hours' => 6, 'pass_percent' => 70, 'question_count' => 10, 'time_limit' => 20,
        'qualification' => null,
        'modules' => [
            ['Rolle und Pflichten der Anwaltschaft', <<<'MD'
# Rolle und Pflichten der Anwaltschaft

Anwältinnen und Anwälte sind im Staat San Andreas **unabhängige Organe der Rechtspflege**. Sie gehören weder zum Superior Court of San Andreas noch zur Staatsanwaltschaft (DOJ) oder zur Polizei. Ihre Aufgabe ist es, die Interessen ihrer Mandanten zu vertreten – in Strafverfahren, Zivilstreitigkeiten und gegenüber Behörden.

## Einseitig, aber nicht grenzenlos

Die Kanzlei steht **auf der Seite des Mandanten**. Sie muss nicht neutral sein und muss auch nicht sagen, was dem Mandanten schadet. Gleichzeitig gelten feste Grenzen:

- **Keine Lüge** gegenüber dem Gericht.
- **Keine Beeinflussung von Zeugen** und keine gefälschten oder versteckten Beweise.
- **Keine Beihilfe** zu Straftaten des Mandanten.

Erlaubt ist dagegen, auf Beweislücken hinzuweisen, Zweifel herauszuarbeiten und Freispruch zu beantragen – auch wenn der Mandant intern eingeräumt hat, die Tat begangen zu haben.

## Die Grundpflichten

| Pflicht | Bedeutung |
|---|---|
| **Unabhängigkeit** | Keine Bindungen an Behörden oder Organisationen, die die Vertretung beeinträchtigen |
| **Verschwiegenheit** | Alles aus dem Mandat bleibt in der Kanzlei |
| **Verbot widerstreitender Interessen** | Nie beide Seiten in derselben Sache vertreten |
| **Sachlichkeit** | Keine herabsetzenden Äußerungen, keine bewussten Unwahrheiten |
| **Sorgfalt** | Fristen wahren, Akten ordentlich führen |

## Rollen in der Kanzlei

- **Kanzleileitung**: Mandatsannahme, Personal, Abrechnung.
- **Anwältin / Anwalt**: Beratung, Schriftsätze, Vertretung.
- **Strafverteidiger/in**: nach dem Lehrgang Strafverteidigung.
- **Prozessanwalt/-anwältin**: nach dem Lehrgang Prozessführung vor Gericht.
- **Kanzleimitarbeiter/in**: Empfang, Termine, Fristen, Akten.

## Gesetze kennen

Die Gesetzbücher eures Staats – etwa StGB, StVO, WaffG und BtMG – findet ihr im Portal unter **Gesetze**. Jedes Gesetzbuch ist eigenständig ab §1 gezählt, und die Inhalte werden vom Server gepflegt. Arbeitet daher **immer mit dem aktuellen Text** aus dem Portal und nicht aus dem Gedächtnis. Strafen werden in **Hafteinheiten (HE)** und **Dollar ($)** bemessen.

## Konsequenzen

Wer Berufspflichten verletzt, riskiert eine Rüge, den Ausschluss aus einem Verfahren oder den Entzug der Zulassung. Handlungen wie Strafvereitelung oder Beweismittelunterdrückung sind zusätzlich Straftaten.

*Merke:* Parteilich für den Mandanten – aber immer im Rahmen des Rechts.
MD],
            ['Mandatsannahme, Interessenkonflikt und Verschwiegenheit', <<<'MD'
# Mandatsannahme, Interessenkonflikt und Verschwiegenheit

Mit der Annahme eines Mandats übernimmt die Kanzlei Verantwortung und Haftung. Deshalb folgt die Mandatsannahme einem festen Ablauf.

## Ablauf der Mandatsannahme

1. **Erstkontakt**: Name, Erreichbarkeit, kurzes Anliegen, Dringlichkeit (Festnahme? laufende Frist?).
2. **Interessenkonflikt prüfen** – *bevor* Details besprochen werden.
3. **Erstgespräch** und Aufnahme des Sachverhalts.
4. **Honorar** besprechen und schriftlich festhalten.
5. **Vollmacht** erteilen lassen.
6. **Akte anlegen** und Fristen eintragen.

## Interessenkonflikt-Prüfung

Eine Kanzlei darf nie **widerstreitende Interessen** vertreten. Typische Konflikte:

- Die Kanzlei vertritt bereits die **Gegenpartei** oder den Geschädigten.
- **Zwei Mitbeschuldigte** derselben Tat möchten von derselben Kanzlei verteidigt werden.
- Eine Anwältin war in derselben Sache früher auf der Gegenseite tätig.
- Persönliche Nähe zu Opfer oder Gegner.

Geprüft wird, indem die Namen **aller Beteiligten** in den Mandantenakten gesucht werden. Bei Treffern entscheidet die Kanzleileitung. Das Ergebnis wird in der Akte vermerkt. Besteht ein Konflikt, wird das Mandat abgelehnt. Dem Anfragenden wird nur gesagt, dass die Kanzlei aus berufsrechtlichen Gründen nicht tätig werden kann – **ohne** Hinweise auf andere Mandanten. Ein Verweis auf das **Anwaltsverzeichnis** hilft ihm weiter.

## Ablehnung von Mandaten

Ein Mandat wird auch abgelehnt, wenn der Mandant rechtswidriges Handeln verlangt oder die Kanzlei eine laufende Frist nicht sicher wahren kann. Die Ablehnung erfolgt **unverzüglich** und mit Hinweis auf die Frist.

## Verschwiegenheit

Unter die Verschwiegenheit fällt **alles**, was im Mandat bekannt wird – sogar die Tatsache, dass jemand Mandant ist. Sie gilt gegenüber jedermann: Polizei, Staatsanwaltschaft, Presse, Familie und Freunden.

Anwältinnen und Anwälte haben ein **Zeugnisverweigerungsrecht** über das ihnen Anvertraute. Ausnahmen sind eng:

- ausdrückliche **Entbindung** durch den Mandanten,
- Verteidigung gegen Vorwürfe des Mandanten (z. B. Honorarstreit), nur soweit nötig,
- Abwendung einer unmittelbar bevorstehenden schweren Straftat gegen Leib und Leben.

## Praktisch

- Mandate nie in der Öffentlichkeit oder über Funk besprechen.
- Haftbesuche nur ohne Mithörer.
- Akten nur für Berechtigte zugänglich machen.

*Merke:* Erst Konfliktprüfung, dann Sachverhalt, dann Vollmacht.
MD],
            ['Fristen und Aktenführung', <<<'MD'
# Fristen und Aktenführung

Fristen sind das größte Haftungsrisiko jeder Kanzlei. Eine versäumte Frist kann einen Strafbefehl, ein Urteil oder einen Bescheid **endgültig** machen – auch wenn er falsch war.

## Wichtige Fristen

| Frist | Wogegen | Folge bei Versäumnis |
|---|---|---|
| Einspruchsfrist | Strafbefehl | Strafbefehl wird rechtskräftig |
| Rechtsmittelfrist | Urteil | Urteil wird rechtskräftig |
| Widerspruchsfrist | Behördenbescheid | Bescheid wird bestandskräftig |
| Gerichtliche Frist | Stellungnahme, Erwiderung | Vortrag kann zurückgewiesen werden |

Die Länge einer Frist steht in der Entscheidung oder Rechtsbehelfsbelehrung. Ist sie unklar, fragt ihr beim Gericht nach – ihr **vermutet keine Frist**.

## Das Vier-Augen-Prinzip

1. Jedes eingehende Schreiben sofort auf Fristen lesen.
2. **Ablaufdatum und Vorfrist** notieren (die Vorfrist liegt einige Tage vorher).
3. Frist im Portal eintragen, damit sie im **Gerichtskalender** erscheint.
4. Eine **zweite Person** prüft die Berechnung.
5. Nach Einreichung des Schriftsatzes die **Erledigung** mit Uhrzeit vermerken.

Fristen werden nie nur im Kopf geführt. Bei Abwesenheit übernimmt eine benannte Vertretung. Fristverlängerungen werden **vor** Ablauf beantragt.

Ist doch eine Frist versäumt, wird sofort die Kanzleileitung informiert und der Mandant ehrlich aufgeklärt. Vertuschen macht alles schlimmer.

## Aktenführung

Die Akte muss so geführt sein, dass eine Kollegin sie ohne Rückfrage übernehmen kann. Sie enthält:

- Stammdaten, Aktenzeichen von Gericht und Staatsanwaltschaft,
- Vollmacht und Honorarvereinbarung,
- Vermerk über die Konfliktprüfung,
- Gesprächsnotizen,
- Schriftverkehr und Entscheidungen,
- Unterlagen aus der Akteneinsicht,
- Fristen mit Erledigungsvermerk.

## Grundsätze

- **Zeitnah**: Notizen am selben Tag.
- **Sachlich**: Wertungen kennzeichnen.
- **Vollständig**: auch Telefonate und gescheiterte Kontaktversuche.
- **Chronologisch**: Datum, Uhrzeit, Bearbeiter.
- **Vertraulich**: nur für Berechtigte.

Nach Ende des Mandats wird die Akte mit einem **Abschlussvermerk** geschlossen und archiviert, nicht gelöscht. So bleiben spätere Konfliktprüfungen möglich.

*Merke:* Keine Frist ohne Eintrag, kein Eintrag ohne Gegenzeichnung.
MD],
            ['Arbeiten im Portal', <<<'MD'
# Arbeiten im Portal

Das Portal ist das zentrale Werkzeug der Kanzlei. Hier werden Mandate geführt, Dokumente erstellt und an Gericht und Behörden übermittelt.

## Mandantenakten

Für jedes Mandat wird eine **Mandantenakte** angelegt. Vorher wird nach allen Beteiligten gesucht (Interessenkonflikt-Prüfung). Die Akte erhält einen klaren Betreff und die Aktenzeichen von Gericht und Staatsanwaltschaft. Akten werden **nicht** an andere Fraktionen freigegeben; übermittelt werden nur einzelne Dokumente, wenn das nötig ist.

## Vollmacht

Die Dokumentart **Vollmacht** enthält Mandant, Bevollmächtigte, **Umfang** und die Unterschrift des Mandanten. Voreingestellt ist die Vertretung im Straf- und Ermittlungsverfahren, Akteneinsicht und Empfang von Zustellungen. Darf die Kanzlei einen Vergleich schließen oder Geld empfangen, muss das **ausdrücklich** im Umfang stehen. Ohne Vollmacht gibt es keine Akteneinsicht.

## Schriftsätze mit „Einreichen bei“

1. Dokument in der Akte anlegen und ausfüllen.
2. Gegenlesen lassen.
3. Unter **„Einreichen bei“** den richtigen Empfänger wählen – etwa den Superior Court für einen Einspruch gegen einen Strafbefehl, die Staatsanwaltschaft für einen Einstellungsantrag, die Behörde für einen Widerspruch.
4. Einreichen. Datum und Uhrzeit sind für die Fristwahrung maßgeblich.
5. Erledigung der Frist vermerken.

## Behördenanfragen

Über **Behördenanfragen** fragt die Kanzlei Unterlagen bei anderen Fraktionen an: Polizeiberichte beim LSPD oder Sheriff, Akteneinsicht bei der Staatsanwaltschaft, Behandlungsberichte bei SAMS. Die Gegenseite kann in ihrer Antwort Dokumente **direkt freigeben**. Eine gute Anfrage nennt Aktenzeichen, Mandant, konkreten Umfang und hat die Vollmacht als Anlage. Bei SAMS ist zusätzlich eine **Schweigepflichtentbindung** des Patienten nötig.

## Gerichtskalender

Der **Gerichtskalender** zeigt Verhandlungen, Säle und Fristen. Mit dem Anwaltszugang seht ihr **nur eure eigenen** Termine und Fristen. Prüft ihn zu jedem Dienstbeginn.

## Gesetze und Strafrechner

Unter **Gesetze** findet ihr die Gesetzbücher und den **Strafrechner**, der aus ausgewählten Paragraphen die zu erwartende Strafe in HE und $ berechnet.

## Anwaltsverzeichnis

Im **Anwaltsverzeichnis** stehen Anwältinnen und Anwälte mit Kanzlei, Qualifikationen und Erreichbarkeit. Haltet euren Eintrag aktuell und sachlich – Erfolgsversprechen sind unzulässig.

*Merke:* Ein Schriftsatz beim falschen Empfänger wahrt keine Frist.
MD],
        ],
        'questions' => [
            ['Welche Stellung haben Anwältinnen und Anwälte in San Andreas?', ['Sie sind Angestellte des Superior Court', 'Sie gehören zur Staatsanwaltschaft', 'Sie sind unabhängige Organe der Rechtspflege', 'Sie sind Hilfsbeamte der Polizei'], [2], 'Die Anwaltschaft ist unabhängig und vertritt allein ihre Mandanten.'],
            ['Was ist der Verteidigung gegenüber dem Gericht ausdrücklich verboten?', ['Das Gericht bewusst belügen', 'Auf Beweislücken hinweisen', 'Freispruch beantragen', 'Schweigen zu Informationen, die dem Mandanten schaden'], [0], 'Die Verteidigung muss nicht alles sagen, darf aber nicht lügen.'],
            ['Wann wird die Interessenkonflikt-Prüfung durchgeführt?', ['Nach der Hauptverhandlung', 'Vor dem Erstgespräch, bevor Details besprochen werden', 'Erst wenn die Gegenseite sich beschwert', 'Nur bei Zivilverfahren'], [1], 'Vertrauliche Informationen dürfen erst nach der Konfliktprüfung ausgetauscht werden.'],
            ['Zwei Mitbeschuldigte derselben Tat wollen von derselben Kanzlei verteidigt werden. Was gilt?', ['Kein Problem, das spart Kosten', 'Erlaubt, wenn beide zustimmen', 'Erlaubt, wenn verschiedene Anwälte der Kanzlei tätig werden', 'Nicht zulässig, da widerstreitende Interessen drohen'], [3], 'Schon die Möglichkeit, dass einer den anderen belastet, schließt eine gemeinsame Verteidigung aus.'],
            ['Was fällt unter die anwaltliche Verschwiegenheit?', ['Nur Geständnisse des Mandanten', 'Alles, was im Mandat bekannt wird, einschließlich der Tatsache des Mandats', 'Nur schriftliche Unterlagen', 'Nichts gegenüber der Polizei'], [1], 'Die Verschwiegenheit ist umfassend und gilt gegenüber jedermann.'],
            ['Wie sagt man einem Anfragenden ab, wenn ein Interessenkonflikt besteht?', ['Man nennt den anderen Mandanten', 'Man erklärt den Konflikt im Detail', 'Man verweist auf berufsrechtliche Gründe, ohne Rückschlüsse auf andere Mandanten zu ermöglichen', 'Man antwortet gar nicht'], [2], 'Gründe, die andere Mandanten offenlegen, werden nicht genannt.'],
            ['Was gehört zum Vier-Augen-Prinzip der Fristenkontrolle?', ['Eine zweite Person prüft die Fristberechnung', 'Zwei Anwälte unterschreiben jedes Schreiben', 'Der Mandant trägt die Frist selbst ein', 'Fristen werden doppelt so lang notiert'], [0], 'Die Gegenzeichnung verhindert Rechenfehler.'],
            ['Was tut ihr, wenn eine Frist in einer Entscheidung unklar ist?', ['Eine übliche Frist vermuten', 'Beim Gericht nachfragen', 'Die Frist ignorieren', 'Bis zur nächsten Ladung warten'], [1], 'Fristen werden nicht vermutet, sondern geklärt.'],
            ['Was geschieht mit einer Akte nach Ende des Mandats?', ['Sie wird sofort gelöscht', 'Sie wird dem Gegner übergeben', 'Sie wird an die Polizei übermittelt', 'Sie erhält einen Abschlussvermerk und wird archiviert'], [3], 'Archivierte Akten ermöglichen spätere Konfliktprüfungen.'],
            ['Was muss ausdrücklich in der Vollmacht stehen, damit die Kanzlei einen Vergleich schließen darf?', ['Nichts, das ist immer erlaubt', 'Die Befugnis zum Abschluss von Vergleichen im Umfang der Vollmacht', 'Die Telefonnummer des Gegners', 'Das Honorar'], [1], 'Weitreichende Befugnisse wie Vergleich oder Geldempfang müssen ausdrücklich erteilt werden.'],
            ['Was ist bei einer Behördenanfrage an SAMS zusätzlich zur Vollmacht nötig?', ['Eine Schweigepflichtentbindung des Patienten', 'Ein Gerichtsurteil', 'Die Zustimmung der Polizei', 'Nichts weiter'], [0], 'Ärztliche Unterlagen gibt SAMS nur mit Einwilligung des Patienten heraus.'],
            ['Was seht ihr mit dem Anwaltszugang im Gerichtskalender?', ['Alle Termine des Gerichts', 'Termine aller Kanzleien', 'Nur Termine der Staatsanwaltschaft', 'Nur die eigenen Termine und Fristen als Rechtsbeistand'], [3], 'Der Anwaltszugang schützt die Vertraulichkeit anderer Verfahren.'],
        ],
    ],
    [
        'title' => 'Strafverteidigung',
        'description' => 'Lehrgang zur Strafverteidigerin bzw. zum Strafverteidiger. Behandelt Erstgespräch und Verhalten bei Festnahme, Haftbesuch, Akteneinsicht und Zusammenarbeit mit Staatsanwaltschaft, Polizei und SAMS, den Einspruch gegen Strafbefehle, Absprachen sowie Strafzumessung und Gesamtstrafe mit dem Strafrechner.',
        'hours' => 10, 'pass_percent' => 80, 'question_count' => 10, 'time_limit' => 25,
        'qualification' => 'Strafverteidiger/in',
        'modules' => [
            ['Erstgespräch und Festnahme', <<<'MD'
# Erstgespräch und Festnahme

Viele Mandate beginnen mit einem Anruf: „Mein Bruder wurde gerade festgenommen.“ In diesen ersten Stunden werden oft die wichtigsten Weichen gestellt.

## Der Anruf bei einer Festnahme

1. **Wo** ist der Mandant? (Wache Mission Row, Sheriff-Station Sandy Shores, Paleto Bay …)
2. **Was** wird ihm vorgeworfen, soweit bekannt?
3. Ist er **verletzt**? Dann auf Versorgung durch SAMS bestehen.
4. Klare Botschaft: **„Keine Angaben zur Sache. Nur sagen, dass du einen Anwalt willst.“**

## Auf der Wache

- Als Verteidiger ausweisen und **Vollmacht** vorlegen oder vor Ort unterschreiben lassen.
- Ein **unverzügliches und ungestörtes** Gespräch mit dem Mandanten verlangen.
- Bei Vernehmungen anwesend sein. Der Mandant darf vor jeder Antwort Rücksprache halten.
- **Dokumentieren**: Uhrzeit, Namen bzw. Dienstnummern, Belehrungen, Ablauf.

Wurden Rechte missachtet – etwa keine Belehrung oder kein Zugang zum Anwalt –, können eure Notizen später ein **Beweisverwertungsverbot** begründen.

## Rechte des Festgenommenen

- Schweigerecht
- Recht auf einen Rechtsbeistand
- Recht auf medizinische Versorgung
- Recht, den Tatvorwurf zu erfahren

## Das Erstgespräch

Im Erstgespräch – auf der Wache oder in der Kanzlei – geht es um Vertrauen und Information:

1. **Belehrung**: Alles bleibt vertraulich. Der Mandant sollte euch die Wahrheit sagen, damit ihr Risiken einschätzen könnt.
2. **Freie Schilderung** ohne Unterbrechung.
3. **Nachfragen**: Wann, wo, wer, was hat die Polizei gefunden?
4. **Verfahrensstand**: Ermittlungsverfahren, Strafbefehl, Anklage, Haft?
5. **Fristen** sofort eintragen.
6. **Einschätzung** ohne Versprechen.
7. Honorar und Vollmacht klären.

## Die wichtigste Empfehlung

Bis zur **Akteneinsicht** macht der Mandant keine Angaben zur Sache. Das Schweigen darf ihm nicht zum Nachteil ausgelegt werden. Erst wenn ihr wisst, was die Ermittler wissen, entscheidet ihr gemeinsam, ob eine Einlassung sinnvoll ist.

## Tabu

- Zu einer falschen Aussage raten.
- Zeugen beeinflussen oder Beweise verschwinden lassen.
- Ein Ergebnis garantieren.

*Merke:* Sachlich bleiben, Rechte einfordern, alles dokumentieren.
MD],
            ['Haftbesuch, Akteneinsicht und Zusammenarbeit', <<<'MD'
# Haftbesuch, Akteneinsicht und Zusammenarbeit

## Haftbesuch

Befindet sich der Mandant in Gewahrsam, Untersuchungshaft oder im Strafvollzug, hat er Anspruch auf Besuch durch seinen Verteidiger.

- Besuch anmelden, **Ausweis und Vollmacht** mitführen.
- Sicherheitskontrollen akzeptieren, Akten dürfen mitgenommen werden.
- Gespräch in einem **Raum ohne Mithörer**. Wird das verweigert, höflich bestehen und dokumentieren.
- Gesundheit des Mandanten erfragen, Verfahrensstand erklären, nächste Termine nennen.

**Verboten** sind das Einschmuggeln von Gegenständen (Telefon, Waffen, Drogen, Bargeld) und das Überbringen von Nachrichten, die der Verdunkelung dienen. Wer das tut, macht sich selbst strafbar.

## Akteneinsicht

Ohne Kenntnis der Ermittlungsakte gibt es keine sinnvolle Verteidigung.

| Verfahrensstand | Antrag bei |
|---|---|
| Ermittlungsverfahren | Staatsanwaltschaft (DOJ) |
| Nach Anklage | Superior Court of San Andreas |
| Polizeiunterlagen | über die Staatsanwaltschaft oder per Behördenanfrage |

Der Antrag nennt Aktenzeichen, Mandant, Vollmacht und den Umfang: vollständige Akte mit Beweismittelverzeichnis, Vernehmungen, Gutachten und medizinischen Berichten. Solange Ermittlungen laufen, kann die Einsicht beschränkt werden, wenn sonst der Ermittlungszweck gefährdet wäre. Vernehmungen des Beschuldigten und Gutachten müssen aber zugänglich sein. Verweigerungen lasst ihr euch schriftlich begründen und beantragt notfalls eine gerichtliche Entscheidung.

Bei der Auswertung markiert ihr Widersprüche, fehlende Belehrungen, Zeitlücken und mögliche entlastende Beweise. Die Akte wird mit dem Mandanten besprochen, aber **nicht an Dritte** weitergegeben.

## Zusammenarbeit mit der Staatsanwaltschaft

Die Staatsanwaltschaft muss **belastende und entlastende** Umstände ermitteln. Nutzt das: Weist auf entlastende Beweise hin und stellt vor einer Anklageentscheidung eine **Stellungnahme** mit Antrag auf Einstellung.

## Zusammenarbeit mit der Polizei

LSPD und Sheriff machen ihre Arbeit, ihr eure. Bei Durchsuchungen verlangt ihr die Rechtsgrundlage und ein Verzeichnis der beschlagnahmten Gegenstände, greift aber **nicht körperlich** ein.

## Zusammenarbeit mit SAMS

Medizinische Unterlagen über euren Mandanten – etwa Verletzungen nach einer Festnahme oder Blutwerte – erhaltet ihr nur mit einer **Schweigepflichtentbindung**. Sie nennt Patient, entbundene Stelle, Empfänger, konkreten Umfang und ist unterschrieben. Die Anfrage läuft als **Behördenanfrage** an den Rettungsdienst. Befunde über **Opfer oder Zeugen** erhaltet ihr nur über die Ermittlungsakte.

*Merke:* Erst Akteneinsicht, dann Verteidigungsstrategie.
MD],
            ['Strafbefehl, Einspruch und Absprachen', <<<'MD'
# Strafbefehl, Einspruch und Absprachen

## Der Strafbefehl

Bei einfacheren Taten kann das Gericht auf Antrag der Staatsanwaltschaft einen **Strafbefehl** erlassen. Er setzt eine Strafe in **HE** und/oder **$** fest, ohne dass eine Hauptverhandlung stattfindet. Wird kein Einspruch eingelegt, wirkt er wie ein rechtskräftiges Urteil.

## Einspruch

Der Einspruch muss **innerhalb der Einspruchsfrist** eingelegt werden, die im Strafbefehl genannt ist. Sie beginnt mit der Zustellung und wird sofort eingetragen. Der Einspruch geht an das **Gericht** – im Portal über „Einreichen bei“ an den Superior Court, nicht an die Polizei.

Formulierung: *„Gegen den Strafbefehl vom … lege ich namens und in Vollmacht meines Mandanten Einspruch ein.“* Eine Begründung ist nicht zwingend.

## Wann lohnt sich ein Einspruch?

- Der Mandant bestreitet die Tat und die Beweislage ist zweifelhaft.
- Die Strafe ist zu hoch, mildernde Umstände fehlen.
- Paragraphen wurden falsch angewendet oder **doppelt gezählt**.
- Nebenfolgen wie ein Lizenzentzug sind falsch angeordnet.

## Teileinspruch

Der Einspruch kann auf die **Rechtsfolgen** beschränkt werden. Dann steht die Schuld fest, verhandelt wird nur über die Höhe der Strafe.

## Risiko

Nach dem Einspruch folgt die Hauptverhandlung. Das Gericht ist **nicht an den Strafbefehl gebunden** und kann auch höher bestrafen. Das muss der Mandant wissen. Der Einspruch kann bis zur Urteilsverkündung zurückgenommen werden.

## Absprachen mit der Staatsanwaltschaft

Eine **Verständigung** kann sinnvoll sein, wenn die Beweislage für einen Teil der Vorwürfe erdrückend ist oder der Mandant das Verfahren schnell beenden möchte.

1. Mandant beraten: Vor- und Nachteile, realistische Strafe.
2. **Ausdrückliche Zustimmung** des Mandanten einholen.
3. Kontakt zur Staatsanwaltschaft aufnehmen.
4. Inhalt **schriftlich** festhalten: Vorwürfe, Geständnis, Strafobergrenze.
5. In der Hauptverhandlung **offenlegen**; das Gericht entscheidet.

## Grenzen

- Über die Strafe entscheidet allein das **Gericht**. Die Staatsanwaltschaft kann nur einen Antrag zusagen.
- Ein Geständnis muss **wahr** sein.
- Geheime Absprachen hinter dem Rücken des Gerichts sind unzulässig.

## Pflichtverteidigung

In schweren Fällen bestellt das Gericht einen **Pflichtverteidiger**, oft aus dem Anwaltsverzeichnis. Er hat dieselben Rechte und Pflichten wie ein Wahlverteidiger und verteidigt mit derselben Sorgfalt. Vergütet wird nach den Sätzen des Gerichts.

*Merke:* Kein Einspruch ohne Strafrechner, keine Absprache ohne Zustimmung des Mandanten.
MD],
            ['Strafzumessung, Gesamtstrafe und Strafrechner', <<<'MD'
# Strafzumessung, Gesamtstrafe und Strafrechner

Auch wenn ein Freispruch unrealistisch ist, arbeitet die Verteidigung weiter: für eine **angemessene Strafe**.

## Strafrahmen

Jeder Paragraph im Gesetzbuch eures Staats – StGB, StVO, WaffG, BtMG und weitere – legt einen Rahmen oder Regelsatz in **HE** und **$** fest. Die Gesetzbücher findet ihr im Portal unter **Gesetze**. Jedes Gesetzbuch ist eigenständig ab §1 gezählt, die Inhalte unterscheiden sich von Server zu Server. Arbeitet deshalb **immer mit dem aktuellen Text** im Portal.

## Mildernde Umstände

- **Frühes Geständnis** und erkennbare Reue
- **Wiedergutmachung** des Schadens, Entschuldigung beim Opfer
- **Keine Vorstrafen**
- **Kooperation** bei der Aufklärung
- Besondere persönliche Umstände, eigene Verletzungen

## Erschwerende Umstände

- Einschlägige Vorstrafen
- Gewalt gegen Beamte oder Rettungskräfte
- Gefährdung Unbeteiligter
- Flucht und Widerstand

## Gesamtstrafe

Werden mehrere Taten in einem Verfahren abgeurteilt, regelt das Gesetzbuch eures Servers, wie die Strafen zusammengeführt werden:

| Modell | Wirkung |
|---|---|
| **Addition** | Einzelstrafen werden zusammengezählt |
| **Gesamtstrafe** | höher als die höchste Einzelstrafe, aber niedriger als die Summe |
| **Höchstgrenze** | Deckelung von HE oder $ |

Prüft immer, ob mehrere Paragraphen wirklich **eigenständige Taten** betreffen oder ob ein Delikt das andere mit umfasst. Doppelt gezählte Tatbestände sind ein häufiger Fehler und ein guter Grund für einen Einspruch.

## Der Strafrechner

Unter **Gesetze** findet ihr den **Strafrechner**. Er berechnet aus den ausgewählten Paragraphen die zu erwartende Strafe.

1. Vorwürfe aus Anklage oder Strafbefehl Punkt für Punkt in Paragraphen übersetzen.
2. Tatbestand und Strafrahmen im Gesetzbuch nachlesen.
3. Paragraphen im Strafrechner auswählen.
4. Ergebnis mit dem Strafbefehl bzw. Antrag der Staatsanwaltschaft vergleichen.
5. Abweichungen notieren und Ergebnis in der Mandantenakte ablegen.

## Grenzen des Rechners

Der Strafrechner bildet den **Regelfall** ab. Mildernde und erschwerende Umstände bewertet das Gericht. Ob mehrere Paragraphen nebeneinander anwendbar sind, ist eine **rechtliche Frage**, die ihr selbst beantworten müsst.

## Szenarien rechnen

Vor jeder Beratung und vor jedem Plädoyer rechnet ihr mindestens drei Szenarien: Höchststrafe, wahrscheinliche Strafe, Strafe bei Teilfreispruch. So könnt ihr den Mandanten ehrlich beraten und einen **realistischen Antrag** stellen.

*Merke:* Der Strafrechner rechnet – die rechtliche Bewertung macht ihr.
MD],
        ],
        'questions' => [
            ['Was rät die Verteidigung dem Mandanten bis zur Akteneinsicht?', ['Ein umfassendes Geständnis abzulegen', 'Keine Angaben zur Sache zu machen', 'Der Polizei seine eigene Version ausführlich zu erzählen', 'Sich mit den Zeugen abzusprechen'], [1], 'Erst wenn bekannt ist, was die Ermittler wissen, wird über eine Einlassung entschieden.'],
            ['Was dokumentiert ihr bei einer Festnahme auf der Wache?', ['Nur das Wetter', 'Die Lieblingsfarbe des Beamten', 'Uhrzeit, Namen bzw. Dienstnummern, Belehrungen und Ablauf', 'Nichts, das macht die Polizei'], [2], 'Diese Notizen können ein Beweisverwertungsverbot begründen.'],
            ['Was ist beim Haftbesuch verboten?', ['Akten mitnehmen', 'Nach der Gesundheit des Mandanten fragen', 'Ein Gespräch ohne Mithörer verlangen', 'Ein Telefon für den Mandanten einschmuggeln'], [3], 'Das Einschmuggeln von Gegenständen ist strafbar und kostet die Zulassung.'],
            ['Wo beantragt ihr im Ermittlungsverfahren Akteneinsicht?', ['Bei der Staatsanwaltschaft (DOJ)', 'Beim Rettungsdienst', 'Bei der Presse', 'Beim Mandanten'], [0], 'Nach Anklageerhebung ist das Gericht zuständig.'],
            ['Wie erhaltet ihr Behandlungsunterlagen eures Mandanten bei SAMS?', ['Durch einen Anruf beim Notarzt', 'Per Behördenanfrage mit Schweigepflichtentbindung des Mandanten', 'Über die Polizei ohne Einwilligung', 'Gar nicht'], [1], 'Ohne Einwilligung des Patienten gilt die ärztliche Schweigepflicht.'],
            ['Wie erhaltet ihr Befunde über das Opfer einer Tat?', ['Direkt bei SAMS', 'Über die Familie des Opfers', 'Über die Ermittlungsakte im Rahmen der Akteneinsicht', 'Gar nicht, niemals'], [2], 'Befunde Dritter gelangen über die Staatsanwaltschaft in die Akte.'],
            ['An wen richtet sich der Einspruch gegen einen Strafbefehl?', ['An die Polizei', 'An den Geschädigten', 'An SAMS', 'An das Gericht'], [3], 'Im Portal wird er über „Einreichen bei“ an den Superior Court übermittelt.'],
            ['Welches Risiko besteht nach einem Einspruch gegen einen Strafbefehl?', ['Das Gericht ist nicht gebunden und kann auch höher bestrafen', 'Keins, die Strafe kann nur sinken', 'Der Anwalt verliert seine Zulassung', 'Der Mandant muss sofort in Haft'], [0], 'Dieses Risiko muss mit dem Mandanten besprochen werden.'],
            ['Was bedeutet ein auf die Rechtsfolgen beschränkter Einspruch?', ['Die Schuld wird neu verhandelt', 'Die Schuld steht fest, verhandelt wird nur über die Strafhöhe', 'Der Strafbefehl ist aufgehoben', 'Die Staatsanwaltschaft entscheidet allein'], [1], 'Der Teileinspruch ist sinnvoll, wenn die Tat eindeutig, die Strafe aber zu hoch ist.'],
            ['Wer entscheidet bei einer Verständigung letztlich über die Strafe?', ['Die Staatsanwaltschaft', 'Die Verteidigung', 'Das Gericht', 'Die Polizei'], [2], 'Die Staatsanwaltschaft kann nur einen Antrag zusagen.'],
            ['Was ist ein typischer Fehler in Strafbefehlen, den ihr prüfen solltet?', ['Zu kleine Schriftgröße', 'Fehlende Unterschrift des Mandanten', 'Falsches Wetter im Sachverhalt', 'Doppelt gezählte Tatbestände für dieselbe Tat'], [3], 'Prüft, ob ein Delikt das andere mit umfasst.'],
            ['Was kann der Strafrechner unter Gesetze nicht leisten?', ['Die rechtliche Bewertung mildernder Umstände und der Anwendbarkeit mehrerer Paragraphen', 'Die Strafe in HE berechnen', 'Die Geldstrafe in $ berechnen', 'Mehrere Paragraphen auswählen'], [0], 'Der Rechner bildet den Regelfall ab, die rechtliche Prüfung macht die Verteidigung.'],
        ],
    ],
    [
        'title' => 'Prozessführung vor Gericht',
        'description' => 'Lehrgang zur Prozessanwältin bzw. zum Prozessanwalt. Behandelt Etikette, Robe und Ablauf im Gerichtssaal, die Hauptverhandlung mit Zeugenbefragung und Beweisanträgen, Plädoyer und Rechtsmittel sowie Zivilklage, Vergleich und Widerspruch gegen Bescheide.',
        'hours' => 10, 'pass_percent' => 80, 'question_count' => 10, 'time_limit' => 25,
        'qualification' => 'Prozessanwalt/-anwältin',
        'modules' => [
            ['Etikette, Robe und Ablauf im Gerichtssaal', <<<'MD'
# Etikette, Robe und Ablauf im Gerichtssaal

Vor dem **Superior Court of San Andreas** treten Anwältinnen und Anwälte als Organe der Rechtspflege auf. Ein souveräner Auftritt beginnt mit der Kenntnis der Ordnung im Saal.

## Robe und Kleidung

- **Schwarze Robe** über formeller, gedeckter Kleidung: helles Hemd oder Bluse, dunkle Hose oder Rock, dunkle Schuhe.
- **Nicht** im Saal: Sonnenbrillen, Mützen, Masken, auffälliger Schmuck, sichtbare Waffen.
- Außerhalb des Saals (Kanzlei, Wache, Haftbesuch) ist keine Robe nötig, aber seriöse Kleidung.

## Anrede

| Person | Anrede |
|---|---|
| Richterin / Richter | „Euer Ehren“ oder „Hohes Gericht“ |
| Staatsanwaltschaft | „Frau Staatsanwältin“ / „Herr Staatsanwalt“ |
| Anwälte der Gegenseite | „Frau Kollegin“ / „Herr Kollege“ |
| Zeugen | „Frau …“ / „Herr …“ mit Nachnamen |

Die Anrede wird auch in hitzigen Momenten eingehalten.

## Verhalten

- **Pünktlich**, etwa zehn Minuten vor Beginn, Termine aus dem Gerichtskalender.
- Telefon stumm.
- **Aufstehen**, wenn das Gericht den Saal betritt oder verlässt, und bei der Urteilsverkündung.
- Sprechen, wenn das Gericht das Wort erteilt.
- Für vertrauliche Rücksprache mit dem Mandanten um eine **kurze Unterbrechung** bitten.

## Ablauf eines Termins

1. Aufruf der Sache, Beteiligte betreten den Saal.
2. Gericht betritt den Saal – alle stehen.
3. Feststellung der Anwesenheit und Belehrung.
4. Verhandlung nach der Verfahrensordnung.
5. Schlussvorträge.
6. Beratung des Gerichts.
7. Urteilsverkündung – alle stehen.
8. Rechtsmittelbelehrung, Schluss der Sitzung.

## Kommunikation mit dem Gericht

- Grundsätzlich **schriftlich** und mit Aktenzeichen über „Einreichen bei“.
- **Keine einseitigen Gespräche** mit dem Richter über die Sache ohne die Gegenseite.
- Terminkollisionen **sofort** melden und Verlegung beantragen. Bis zur Entscheidung gilt der alte Termin.

## Hausrecht

Das Gericht übt das Hausrecht aus und kann Ordnungsmittel bis zur Entfernung aus dem Saal verhängen. Kritik an Entscheidungen äußert ihr **sachlich**: durch Beanstandung zu Protokoll oder durch Rechtsmittel – nicht durch Beschimpfungen.

## Haltung

Kollegial gegenüber der Staatsanwaltschaft, respektvoll gegenüber Zeugen, ruhig gegenüber dem Gericht. Gegenüber Presse und Publikum keine Kommentare ohne Zustimmung des Mandanten.

*Merke:* Das Gericht ist nicht euer Gegner – es ist die Instanz, die ihr überzeugen müsst.
MD],
            ['Hauptverhandlung, Zeugen und Beweisanträge', <<<'MD'
# Hauptverhandlung, Zeugen und Beweisanträge

## Ablauf der Hauptverhandlung im Strafverfahren

1. Aufruf und Anwesenheit
2. Belehrung des Angeklagten
3. **Verlesung der Anklage** durch die Staatsanwaltschaft
4. **Erklärung des Angeklagten** – Äußerung oder Schweigen
5. **Beweisaufnahme**
6. **Schlussvorträge**: zuerst Staatsanwaltschaft, dann Verteidigung
7. **Letztes Wort** des Angeklagten
8. Beratung und Urteil

Nur was in der Hauptverhandlung verhandelt wurde, darf in das Urteil einfließen.

## Zeugenbefragung

- **Eigene Zeugen**: offene Fragen („Was haben Sie gesehen?“), keine Suggestivfragen.
- **Zeugen der Gegenseite**: kurze, präzise Fragen, die auf Widersprüche und Wahrnehmungsgrenzen zielen („Wie weit waren Sie entfernt? War es dunkel?“).
- Immer respektvoll, auch gegenüber belastenden Zeugen. Rettungskräfte von SAMS sind keine Partei und werden sachlich befragt.

## Beanstandungen

Ist eine Frage der Gegenseite unzulässig, erhebt ihr sofort eine Beanstandung: *„Einspruch, Euer Ehren – die Frage ist suggestiv.“* Dann wartet ihr auf die Entscheidung. Verfahrensfehler lasst ihr **protokollieren**, denn sie können später ein Rechtsmittel tragen.

## Der Beweisantrag

Ein ordentlicher Beweisantrag hat drei Elemente:

| Element | Beispiel |
|---|---|
| **Beweistatsache** | „Der Angeklagte war am … um 21:30 Uhr im Diner in Sandy Shores.“ |
| **Beweismittel** | „Zeugin Frau …, Bedienung im Diner.“ |
| **Konnexität** | „Sie hat den Angeklagten zu dieser Zeit bedient.“ |

Unklare Anregungen („Man könnte mal die Kameras prüfen“) sind keine Beweisanträge und können leicht übergangen werden.

## Beweismittel

Zeugen, Sachverständige (z. B. ärztliches Gutachten), Urkunden (Verträge, Einsatzberichte) und Augenschein (Fotos, Videos).

## Ablehnung durch das Gericht

Das Gericht darf Beweisanträge nur aus bestimmten Gründen ablehnen – etwa weil die Tatsache bedeutungslos oder bereits bewiesen ist oder das Beweismittel unerreichbar ist. Die Ablehnung erfolgt durch **Beschluss mit Begründung**. Lasst sie protokollieren.

## Beweisverwertungsverbote

Wurden Beweise rechtswidrig erlangt, etwa ohne Belehrung, widersprecht ihr der Verwertung **rechtzeitig**, in der Regel direkt nach der jeweiligen Beweiserhebung. Ein später Widerspruch kann wirkungslos sein.

## Vorbereitung

Akte ausgewertet, Zeugenliste mit Fragen, Beweisanträge schriftlich vorbereitet, Strafrahmen mit dem Strafrechner geprüft, Mandant über den Ablauf informiert.

*Merke:* Tatsache, Beweismittel, Konnexität – sonst ist es kein Beweisantrag.
MD],
            ['Plädoyer und Rechtsmittel', <<<'MD'
# Plädoyer und Rechtsmittel

## Das Plädoyer

Der Schlussvortrag der Verteidigung folgt auf den der Staatsanwaltschaft und kommt vor dem letzten Wort des Angeklagten. Er ist die letzte Gelegenheit, das Gericht zu überzeugen.

### Grundregeln

- **Nur Verhandeltes**: Neue Tatsachen haben im Plädoyer nichts zu suchen.
- **Struktur statt Pathos**.
- **Kurz**: Im Rollenspiel sind drei bis fünf Minuten meist ideal.
- **Respektvoll**: Argumente angreifen, nicht Personen.

### Aufbau

1. **Einleitung**: Kern des Falls.
2. **Beweiswürdigung**: Welche Beweise tragen nicht, wo bleiben Zweifel?
3. **Rechtliche Würdigung**: Ist der Tatbestand erfüllt? Welcher Paragraph passt nicht?
4. **Strafzumessung (hilfsweise)**: mildernde Umstände.
5. **Konkreter Antrag**: Freispruch, hilfsweise eine Strafe von höchstens … HE und … $.

### Der Zweifelssatz

Bleiben nach der Beweisaufnahme **vernünftige Zweifel** an der Schuld, muss das Gericht zugunsten des Angeklagten entscheiden. Das Plädoyer arbeitet diese Zweifel heraus.

### Typische Fehler

- Beweisaufnahme nur nacherzählen.
- Den Mandanten trotz eindeutiger Beweise als völlig unschuldig darstellen – das kostet Glaubwürdigkeit.
- Keinen konkreten Antrag stellen.

## Rechtsmittel

Ein Urteil kann angegriffen werden, solange es nicht **rechtskräftig** ist. Welche Instanzen es gibt, legt euer Server fest.

| Rechtsmittel | Prüfung | Neue Beweise? |
|---|---|---|
| **Berufung** | Tatsachen und Recht werden neu verhandelt | ja |
| **Revision** | nur Rechtsfehler | nein |

Typische Revisionsgründe sind falsche Gesetzesanwendung, fehlerhaft abgelehnte Beweisanträge, verletzte Verfahrensrechte und fehlerhafte Strafzumessung. Deshalb ist das **Protokollieren** von Fehlern in der Verhandlung so wichtig.

### Fristen

Rechtsmittel werden **innerhalb der Rechtsmittelfrist** eingelegt, die das Gericht bei Verkündung mitteilt. Die Frist wird sofort eingetragen. Der Schriftsatz geht über „Einreichen bei“ an das Gericht.

### Prüfschema

1. Ist der Mandant beschwert?
2. Welche Fehler gab es?
3. Welches Risiko besteht – etwa wenn auch die Staatsanwaltschaft ein Rechtsmittel einlegt?
4. Was will der Mandant nach Beratung?

### Rechtsmittelverzicht

Ein Verzicht direkt im Saal macht das Urteil sofort rechtskräftig. Er wird nur nach **ruhiger Beratung** erklärt. Mit Rechtskraft wird die Strafe in HE vollstreckt und die Geldstrafe in $ fällig.

*Merke:* Ohne Protokoll keine Revision, ohne Antrag kein Plädoyer.
MD],
            ['Zivilprozess, Vergleich und Verwaltungsverfahren', <<<'MD'
# Zivilprozess, Vergleich und Verwaltungsverfahren

Prozessanwältinnen und Prozessanwälte vertreten nicht nur in Strafsachen, sondern auch in Zivilstreitigkeiten und gegen Behörden.

## Außergerichtliche Forderung

Vor jeder Klage steht in der Regel ein **Aufforderungsschreiben**: Vertretungsanzeige mit Hinweis auf die Vollmacht, kurzer Sachverhalt, Forderung in $ oder konkrete Handlung, **klares Datum** als Frist und die Ankündigung gerichtlicher Schritte. Drohungen mit Strafanzeigen, um eine Zahlung zu erzwingen, sind unzulässig.

## Die Zivilklage

Die Klageschrift an den Superior Court enthält:

1. **Rubrum**: Kläger, Beklagter, Prozessbevollmächtigte.
2. **Anträge**: z. B. „Der Beklagte wird verurteilt, an den Kläger 25.000 $ zu zahlen.“
3. **Sachverhalt** chronologisch mit Beweisangeboten.
4. **Rechtliche Begründung**.

Das Gericht darf **nicht mehr zusprechen, als beantragt** wurde. Der Antrag muss daher vollständig und genau sein. Die Klage wird über „Einreichen bei“ eingereicht, das Gericht stellt sie zu und setzt dem Beklagten eine Frist zur Erwiderung. In der mündlichen Verhandlung versucht das Gericht meist zuerst eine **gütliche Einigung**.

## Der Vergleich

Ein **Vergleich** beendet den Streit durch **gegenseitiges Nachgeben**. Er ist schnell, planbar, kostensparend und schont die Beziehung der Parteien.

| Art | Merkmal |
|---|---|
| Außergerichtlich | schriftlicher Vertrag der Parteien |
| Gerichtlich | im Protokoll festgehalten, beendet das Verfahren, vollstreckbar |

Ein Vergleich regelt Leistung, Fälligkeit, Abgeltung aller Ansprüche, Kosten und gegebenenfalls einen Widerrufsvorbehalt. Er wird nur mit **ausdrücklicher Zustimmung** des Mandanten geschlossen, und die Vollmacht muss die Befugnis dazu enthalten.

## Widerspruch gegen Bescheide

Behörden entziehen Lizenzen, verhängen Bußgelder oder lehnen Anträge ab. Gegen einen solchen **Bescheid** ist zunächst der **Widerspruch** bei der Behörde gegeben – **innerhalb der Widerspruchsfrist** aus der Rechtsbehelfsbelehrung.

Typische Fehler der Behörden:

- Der Betroffene wurde **nicht angehört**.
- Die Maßnahme ist **unverhältnismäßig**.
- Die Behörde war **nicht zuständig**.
- Die Begründung fehlt.

Hilft die Behörde nicht ab, ergeht ein Widerspruchsbescheid. Dagegen kann **Klage** beim Superior Court erhoben werden. Bei sofortigem Vollzug, etwa einer Fahrzeugbeschlagnahme, kommt ein **Eilantrag** beim Gericht in Betracht.

## Akteneinsicht im Verwaltungsverfahren

Die Behördenakte fordert ihr per **Behördenanfrage** an. Die Anfrage hemmt keine Frist – legt den Widerspruch notfalls fristwahrend ein und begründet nach.

*Merke:* Ein klarer Antrag ist die halbe Klage.
MD],
        ],
        'questions' => [
            ['Wie wird die Richterin im Gerichtssaal angeredet?', ['„Frau Kollegin“', '„Euer Ehren“ oder „Hohes Gericht“', '„Chefin“', 'Mit dem Vornamen'], [1], 'Die Anrede wird auch in hitzigen Momenten eingehalten.'],
            ['Wann stehen alle Anwesenden im Gerichtssaal auf?', ['Wenn das Gericht den Saal betritt und bei der Urteilsverkündung', 'Nur wenn der Angeklagte spricht', 'Bei jeder Zeugenaussage', 'Nie'], [0], 'Aufstehen zeigt Respekt vor dem Gericht.'],
            ['Was ist bei der Kommunikation mit dem Gericht unzulässig?', ['Schriftliche Anträge mit Aktenzeichen', 'Terminverlegung bei Kollision beantragen', 'Organisatorische Fragen an die Geschäftsstelle', 'Einseitige Gespräche mit dem Richter über die Sache ohne die Gegenseite'], [3], 'Was das Gericht erfährt, muss auch die Gegenseite erfahren können.'],
            ['In welcher Reihenfolge halten die Beteiligten im Strafverfahren ihre Schlussvorträge?', ['Verteidigung, dann Staatsanwaltschaft', 'Angeklagter, dann Verteidigung', 'Staatsanwaltschaft, dann Verteidigung, danach das letzte Wort des Angeklagten', 'Gericht, dann Staatsanwaltschaft'], [2], 'Der Angeklagte hat immer das letzte Wort.'],
            ['Welche drei Elemente hat ein ordentlicher Beweisantrag?', ['Beweistatsache, Beweismittel, Konnexität', 'Datum, Uhrzeit, Unterschrift', 'Anklage, Urteil, Strafe', 'Kläger, Beklagter, Streitwert'], [0], 'Fehlt ein Element, ist es nur eine Beweisanregung.'],
            ['Wie lehnt das Gericht einen Beweisantrag ab?', ['Durch Ignorieren', 'Durch Beschluss mit Begründung', 'Durch einen Anruf beim Anwalt', 'Gar nicht, Beweisanträge müssen immer erhoben werden'], [1], 'Die Ablehnung sollte protokolliert werden, sie kann ein Rechtsmittel tragen.'],
            ['Wann muss der Widerspruch gegen die Verwertung eines rechtswidrig erlangten Beweises erklärt werden?', ['Erst im Plädoyer', 'In der Revision', 'Nach Rechtskraft', 'Rechtzeitig, in der Regel direkt nach der jeweiligen Beweiserhebung'], [3], 'Ein später Widerspruch kann wirkungslos sein.'],
            ['Was gilt, wenn nach der Beweisaufnahme vernünftige Zweifel an der Schuld bleiben?', ['Das Gericht muss zugunsten des Angeklagten entscheiden', 'Der Angeklagte muss seine Unschuld beweisen', 'Die Verhandlung wird endlos fortgesetzt', 'Die Staatsanwaltschaft entscheidet'], [0], 'Das ist der Zweifelssatz.'],
            ['Worin unterscheidet sich die Revision von der Berufung?', ['Die Revision verhandelt alle Tatsachen neu', 'In der Revision werden neue Zeugen gehört', 'Die Revision prüft nur Rechtsfehler, ohne neue Beweisaufnahme', 'Es gibt keinen Unterschied'], [2], 'Die Berufung verhandelt Tatsachen und Recht neu.'],
            ['Was folgt aus einem Rechtsmittelverzicht direkt im Saal?', ['Das Urteil kann noch drei Instanzen durchlaufen', 'Das Urteil wird sofort rechtskräftig', 'Das Urteil wird aufgehoben', 'Die Strafe halbiert sich'], [1], 'Ein Verzicht wird nur nach ruhiger Beratung erklärt.'],
            ['Warum muss der Klageantrag im Zivilprozess genau formuliert sein?', ['Weil das Gericht sonst die Klage veröffentlicht', 'Weil der Beklagte sonst automatisch gewinnt', 'Weil die Polizei ihn prüft', 'Weil das Gericht nicht mehr zusprechen darf, als beantragt wurde'], [3], 'Ein klarer Antrag ist die halbe Klage.'],
            ['Was ist der erste Rechtsbehelf gegen einen Bescheid, etwa einen Lizenzentzug?', ['Der Widerspruch bei der Behörde innerhalb der Widerspruchsfrist', 'Eine Strafanzeige gegen den Sachbearbeiter', 'Die Revision', 'Ein Haftbesuch'], [0], 'Erst nach dem Widerspruchsbescheid folgt die Klage beim Gericht.'],
        ],
    ],
];
