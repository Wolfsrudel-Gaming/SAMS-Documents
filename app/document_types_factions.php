<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Dokumentarten für Polizei, Justiz, Feuerwehr und Verwaltung. Sie nutzen die
 * Vorlagen-Engine (template 'custom'): Brief mit Briefkopf oder Protokoll mit
 * Kästchenraster. Beim Ausstellen wird die Vorlage im Dokument eingefroren.
 * Platzhalter im Text: {{feldname}}, {{patient.name}}, {{issuer.name}}, {{org.name}} …
 */
function document_types_factions(): array
{
    $t = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'text', 'width' => 'full'] + $o;
    $date = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'date', 'width' => 'quarter'] + $o;
    $time = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'time', 'width' => 'quarter'];
    $area = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'textarea', 'rows' => 4, 'width' => 'full'] + $o;
    $sel = fn(string $name, string $label, array $opts, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'select', 'width' => 'third', 'options' => array_combine($opts, $opts)] + $o;
    $radio = fn(string $name, string $label, array $opts, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'radio', 'width' => 'full', 'options' => array_combine($opts, $opts)] + $o;
    $check = fn(string $name, string $label, array $o = []) => ['name' => $name, 'label' => $label, 'type' => 'checkbox', 'width' => 'third'] + $o;
    $head = fn(string $label) => ['type' => 'heading', 'label' => $label];
    $sign = fn(string $name, string $label) => ['name' => $name, 'label' => $label, 'type' => 'signature', 'width' => 'half'];
    $az = $t('aktenzeichen', 'Aktenzeichen', ['width' => 'third', 'placeholder' => 'z. B. 2 Js 123/26']);

    $def = function (string $title, string $short, string $code, string $kind, string $role, string $category, string $layout, array $fields, string $body = '', string $patient = 'required', string $seal = 'round', ?string $valid = null): array {
        return array_filter([
            'title' => $title, 'short' => $short, 'code' => $code, 'category' => $category, 'role' => $role, 'kinds' => [$kind],
            'patient' => $patient, 'template' => 'custom', 'fields' => $fields, 'valid' => $valid,
            'custom' => ['layout' => $layout, 'body' => $body, 'seal' => $seal, 'title' => $title],
        ], fn($v) => $v !== null);
    };

    return [
        // ================================================================ Polizei
        'pd_anzeige' => $def('Strafanzeige', 'Strafanzeige', 'SA', 'police', 'officer', 'Ermittlungen', 'protocol', [
            $head('Tat'), $date('tatdatum', 'Tatdatum', ['required' => true, 'default' => 'today']), $time('tatzeit', 'Tatzeit'),
            $t('tatort', 'Tatort', ['width' => 'half', 'required' => true]), $t('delikt', 'Delikt(e)', ['required' => true, 'list' => 'services']),
            $area('sachverhalt', 'Sachverhalt', ['required' => true, 'rows' => 6]), $area('beweismittel', 'Beweismittel / Spuren', ['rows' => 3]),
            $head('Beteiligte'), $t('anzeigeerstatter', 'Anzeigeerstatter/in', ['width' => 'half']), $t('geschaedigt', 'Geschädigte/r', ['width' => 'half']),
            $area('zeugen', 'Zeug/innen', ['rows' => 2]), $sign('sig_anzeige', 'Unterschrift Anzeigeerstatter/in'),
        ], '', 'optional'),
        'pd_festnahme' => $def('Festnahmeprotokoll', 'Festnahme', 'FN', 'police', 'officer', 'Einsatz', 'protocol', [
            $head('Festnahme'), $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'),
            $t('ort', 'Ort der Festnahme', ['width' => 'half', 'required' => true]), $t('delikte', 'Tatvorwurf / Delikte', ['required' => true, 'list' => 'services']),
            $area('grund', 'Grund und Hergang', ['required' => true]),
            $head('Rechte und Maßnahmen'), $check('belehrt', 'Über Rechte belehrt (Miranda)'), $time('belehrt_um', 'Belehrt um'), $check('anwalt', 'Rechtsbeistand verlangt'),
            $check('durchsucht', 'Person durchsucht'), $check('rettungsdienst', 'Rettungsdienst hinzugezogen'), $check('fesselung', 'Fesselung angewendet'),
            $area('gegenstaende', 'Sichergestellte Gegenstände', ['rows' => 3]), $area('verletzungen', 'Verletzungen', ['rows' => 2]),
            $head('Verbleib'), ['name' => 'haft', 'label' => 'Haft-Einheiten', 'type' => 'number', 'width' => 'quarter'],
            $sel('verbleib', 'Verbleib', ['Gewahrsam / Zelle', 'Vorführung Haftrichter', 'Bolingbroke Penitentiary', 'Krankenhaus', 'Entlassen']),
            $sign('sig_festgenommen', 'Unterschrift festgenommene Person'),
        ]),
        'pd_durchsuchung' => $def('Durchsuchungsprotokoll', 'Durchsuchung', 'DS', 'police', 'officer', 'Einsatz', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('beginn', 'Beginn'), $time('ende', 'Ende'),
            $radio('objekt', 'Durchsucht wurde', ['Person', 'Fahrzeug', 'Wohnung', 'Geschäftsräume', 'Sonstiges'], ['required' => true]),
            $t('bezeichnung', 'Anschrift / Kennzeichen / Beschreibung', ['required' => true]),
            $radio('grundlage', 'Rechtsgrundlage', ['Gefahr im Verzug', 'Richterlicher Beschluss', 'Einwilligung'], ['required' => true]),
            $t('beschluss', 'Beschluss-Nr. (falls vorhanden)', ['width' => 'half']), $area('fund', 'Aufgefundene Gegenstände', ['required' => true]),
            $area('zeugen', 'Anwesende / Zeug/innen', ['rows' => 2]), $sign('sig_betroffen', 'Unterschrift betroffene Person'),
        ], '', 'optional'),
        'pd_sicherstellung' => $def('Sicherstellungsprotokoll', 'Sicherstellung', 'SI', 'police', 'officer', 'Einsatz', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Ort', ['width' => 'half']),
            $area('gegenstaende', 'Gegenstände (Bezeichnung, Menge, Asservaten-Nr.)', ['required' => true, 'rows' => 6]),
            $area('grund', 'Grund der Sicherstellung', ['rows' => 2]), $sel('verbleib', 'Verbleib', ['Asservatenkammer', 'Vernichtung', 'Herausgabe an Berechtigte', 'Weitergabe an DOJ']),
            $sign('sig_betroffen', 'Unterschrift betroffene Person'),
        ], '', 'optional'),
        'pd_vernehmung' => $def('Vernehmungsprotokoll', 'Vernehmung', 'VN', 'police', 'officer', 'Ermittlungen', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('beginn', 'Beginn'), $time('ende', 'Ende'),
            $radio('rolle', 'Vernommen als', ['Beschuldigte/r', 'Zeug/in', 'Geschädigte/r'], ['required' => true]),
            $check('belehrt', 'Belehrung erfolgt'), $check('anwalt', 'Rechtsbeistand anwesend'), $t('vorgang', 'Bezug / Vorgang', ['width' => 'third']),
            $area('aussage', 'Angaben zur Sache', ['required' => true, 'rows' => 10]), $sign('sig_vernommen', 'Unterschrift vernommene Person'),
        ]),
        'pd_unfall' => $def('Verkehrsunfallbericht', 'Unfallbericht', 'VU', 'police', 'officer', 'Verkehr', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Unfallort', ['width' => 'half', 'required' => true]),
            $area('beteiligte', 'Beteiligte (Fahrzeug, Kennzeichen, Fahrer/in, Versicherung)', ['required' => true]),
            $area('hergang', 'Unfallhergang', ['required' => true]), $area('verletzte', 'Verletzte', ['rows' => 2]),
            $t('schaden', 'Sachschaden (geschätzt)', ['width' => 'third']), $sel('ursache', 'Hauptursache', ['Geschwindigkeit', 'Vorfahrt', 'Abstand', 'Alkohol / Drogen', 'Ablenkung', 'Technischer Defekt', 'Sonstiges']),
            $check('alkohol', 'Alkohol-/Drogentest durchgeführt'), $area('massnahmen', 'Maßnahmen (Abschleppen, Führerschein, Anzeige …)', ['rows' => 2]),
        ], '', 'optional'),
        'pd_platzverweis' => $def('Platzverweis', 'Platzverweis', 'PV', 'police', 'officer', 'Einsatz', 'letter', [
            $t('ort', 'Örtlicher Bereich', ['required' => true]), $t('dauer', 'Dauer', ['width' => 'half', 'required' => true, 'placeholder' => 'z. B. 24 Stunden']),
            $area('grund', 'Grund', ['required' => true, 'rows' => 3]),
        ], "Gegenüber **{{patient.name}}** wird hiermit ein Platzverweis für folgenden Bereich ausgesprochen:\n\n**{{ort}}** – Dauer: **{{dauer}}**\n\nGrund: {{grund}}\n\nBei Zuwiderhandlung kann die Maßnahme mit unmittelbarem Zwang durchgesetzt und eine Ingewahrsamnahme angeordnet werden."),
        'pd_ermittlung' => $def('Ermittlungsbericht', 'Ermittlungsbericht', 'EB', 'police', 'officer', 'Ermittlungen', 'letter', [
            $area('empfaenger', 'Empfänger', ['rows' => 3, 'placeholder' => "Department of Justice\nStaatsanwaltschaft"]), $az,
            $t('delikte', 'Tatvorwurf', ['list' => 'services']), $area('ergebnis', 'Ermittlungsergebnis', ['required' => true, 'rows' => 10]),
            $area('beweise', 'Beweismittel', ['rows' => 3]), $area('antrag', 'Anregung / Antrag', ['rows' => 2, 'placeholder' => 'z. B. Erlass eines Haftbefehls']),
        ], '', 'optional'),
        'pd_waffenschein' => $def('Waffenschein', 'Waffenschein', 'WS', 'police', 'lead', 'Erlaubnisse', 'letter', [
            $sel('klasse', 'Erlaubnis', ['Waffenbesitzkarte', 'Kleiner Waffenschein', 'Waffenschein (Führen)', 'Jagdschein'], ['required' => true]),
            $date('gueltig_bis', 'Gültig bis', ['required' => true]), $area('auflagen', 'Auflagen', ['rows' => 2]),
        ], "Hiermit wird **{{patient.name}}**, geboren am {{patient.dob}}, die folgende Erlaubnis erteilt:\n\n**{{klasse}}** – gültig bis {{gueltig_bis}}\n\nAuflagen: {{auflagen}}\n\nDie Erlaubnis ist mitzuführen und auf Verlangen vorzuzeigen. Sie kann bei Verstößen jederzeit widerrufen werden.", 'required', 'round', 'gueltig_bis'),

        // ================================================================= Justiz
        'doj_anklage' => $def('Anklageschrift', 'Anklage', 'AK', 'justice', 'prosecutor', 'Strafverfahren', 'letter', [
            $t('gericht', 'An das Gericht', ['width' => 'half', 'default' => 'Superior Court of San Andreas']), $az,
            $area('tatvorwurf', 'Tatvorwurf', ['required' => true]), $area('sachverhalt', 'Wesentliches Ergebnis der Ermittlungen', ['rows' => 6]),
            $area('beweismittel', 'Beweismittel', ['rows' => 3]), $area('antrag', 'Antrag', ['rows' => 2, 'default' => 'Es wird beantragt, die Anklage zur Hauptverhandlung zuzulassen.']),
        ], "**{{patient.name}}**, geboren am {{patient.dob}}, wird angeklagt:\n\n{{tatvorwurf}}"),
        'doj_strafbefehl' => $def('Strafbefehl', 'Strafbefehl', 'SB', 'justice', 'judge', 'Strafverfahren', 'letter', [
            $az, $area('tat', 'Tat', ['required' => true]), $sel('strafe', 'Rechtsfolge', ['Geldstrafe', 'Freiheitsstrafe', 'Geld- und Freiheitsstrafe'], ['required' => true]),
            $t('betrag', 'Geldstrafe (Betrag)', ['width' => 'third']), ['name' => 'haft', 'label' => 'Haft-Einheiten', 'type' => 'number', 'width' => 'third'],
        ], "Gegen **{{patient.name}}** wird wegen folgender Tat\n\n{{tat}}\n\nauf Antrag der Staatsanwaltschaft ein Strafbefehl erlassen: **{{strafe}}** – {{betrag}} {{haft}} Haft-Einheiten.\n\nGegen diesen Strafbefehl kann innerhalb von zwei Wochen nach Zustellung Einspruch eingelegt werden. Andernfalls wird er rechtskräftig."),
        'doj_urteil' => $def('Urteil', 'Urteil', 'UR', 'justice', 'judge', 'Strafverfahren', 'letter', [
            $az, $date('verhandlung', 'Verhandlungstag', ['required' => true]), $area('tenor', 'Urteilstenor', ['required' => true]),
            $area('gruende', 'Gründe', ['rows' => 8]), $area('rechtsmittel', 'Rechtsmittelbelehrung', ['rows' => 2, 'default' => 'Gegen dieses Urteil ist binnen einer Woche die Berufung zulässig.']),
        ], "**Im Namen des Volkes**\n\nIn der Strafsache gegen **{{patient.name}}**, geboren am {{patient.dob}}, hat das Gericht aufgrund der Hauptverhandlung vom {{verhandlung}} für Recht erkannt:\n\n{{tenor}}"),
        'doj_haftbefehl' => $def('Haftbefehl', 'Haftbefehl', 'HB', 'justice', 'judge', 'Beschlüsse', 'letter', [
            $az, $area('tatvorwurf', 'Tatvorwurf', ['required' => true]),
            $radio('haftgrund', 'Haftgrund', ['Fluchtgefahr', 'Verdunkelungsgefahr', 'Wiederholungsgefahr', 'Schwere der Tat'], ['required' => true]),
            $date('gueltig_bis', 'Gültig bis', ['required' => true]), $check('gefaehrlich', 'Person gilt als gefährlich / bewaffnet'),
        ], "Gegen **{{patient.name}}**, geboren am {{patient.dob}}, wird die Untersuchungshaft angeordnet.\n\nTatvorwurf: {{tatvorwurf}}\n\nHaftgrund: **{{haftgrund}}**\n\nDie Polizeibehörden werden ersucht, die beschuldigte Person festzunehmen und unverzüglich dem Gericht vorzuführen. Der Haftbefehl gilt bis {{gueltig_bis}}.", 'required', 'round', 'gueltig_bis'),
        'doj_durchsuchung' => $def('Durchsuchungsbeschluss', 'Durchsuchungsbeschluss', 'DB', 'justice', 'judge', 'Beschlüsse', 'letter', [
            $az, $t('objekt', 'Zu durchsuchendes Objekt (Wohnung, Fahrzeug, Person)', ['required' => true]),
            $area('zweck', 'Zweck / gesuchte Gegenstände', ['required' => true]), $date('gueltig_bis', 'Gültig bis', ['required' => true]),
        ], "Auf Antrag der Staatsanwaltschaft wird die Durchsuchung von\n\n**{{objekt}}**\n\nder Person **{{patient.name}}** angeordnet. Zweck: {{zweck}}\n\nDer Beschluss ist bis {{gueltig_bis}} zu vollziehen.", 'optional', 'round', 'gueltig_bis'),
        'doj_ladung' => $def('Ladung', 'Ladung', 'LA', 'justice', 'clerk', 'Verfahren', 'letter', [
            $az, $date('termin', 'Termin', ['required' => true]), $time('uhrzeit', 'Uhrzeit'), $t('ort', 'Ort', ['width' => 'half', 'default' => 'Superior Court, Saal 1']),
            $sel('als', 'Geladen als', ['Angeklagte/r', 'Zeug/in', 'Sachverständige/r', 'Nebenkläger/in']),
        ], "Sie werden in der Sache {{aktenzeichen}} als **{{als}}** zum Termin am **{{termin}} um {{uhrzeit}}** in {{ort}} geladen.\n\nBitte bringen Sie dieses Schreiben und einen Ausweis mit. Bei unentschuldigtem Ausbleiben kann eine Vorführung angeordnet werden."),
        'doj_beschluss' => $def('Beschluss', 'Beschluss', 'BE', 'justice', 'judge', 'Beschlüsse', 'letter', [
            $az, $area('tenor', 'Tenor', ['required' => true]), $area('gruende', 'Gründe', ['rows' => 6]),
        ], "In der Sache {{aktenzeichen}} ergeht folgender Beschluss:\n\n{{tenor}}", 'optional'),
        'doj_vollmacht' => $def('Vollmacht', 'Vollmacht', 'VM', 'justice', 'lawyer', 'Anwaltschaft', 'letter', [
            $az, $area('umfang', 'Umfang der Vollmacht', ['required' => true, 'default' => 'Vertretung im Straf- und Ermittlungsverfahren, Akteneinsicht, Empfang von Zustellungen.']),
            $sign('sig_mandant', 'Unterschrift Mandant/in'),
        ], "Hiermit erteilt **{{patient.name}}** Vollmacht an {{issuer.name}} für:\n\n{{umfang}}", 'required', 'none'),

        // ============================================================= Feuerwehr
        'fd_einsatz' => $def('Einsatzbericht', 'Einsatzbericht', 'EB', 'fire', 'fire', 'Einsatz', 'protocol', [
            $date('datum', 'Datum', ['required' => true, 'default' => 'today']), $time('alarm', 'Alarmierung'), $time('ankunft', 'Eintreffen'), $time('ende', 'Einsatzende'),
            $sel('art', 'Einsatzart', ['Brand', 'Technische Hilfe', 'Verkehrsunfall', 'Gefahrgut', 'Wasserrettung', 'Fehlalarm', 'Sonstiges'], ['required' => true]),
            $t('ort', 'Einsatzort', ['width' => 'half', 'required' => true]), $area('lage', 'Lage bei Eintreffen', ['required' => true]),
            $area('massnahmen', 'Maßnahmen', ['required' => true]), $t('kraefte', 'Eingesetzte Kräfte / Fahrzeuge'),
            $area('verletzte', 'Verletzte / Betroffene', ['rows' => 2]), $check('rd', 'An Rettungsdienst übergeben'), $check('polizei', 'Polizei vor Ort'),
            $t('schaden', 'Schaden (geschätzt)', ['width' => 'third']),
        ], '', 'none'),
        'fd_brandschau' => $def('Brandschaubericht', 'Brandschau', 'BS', 'fire', 'fire', 'Vorbeugender Brandschutz', 'protocol', [
            $t('objekt', 'Objekt / Anschrift', ['required' => true]), $t('betreiber', 'Betreiber/in', ['width' => 'half']), $date('datum', 'Datum', ['required' => true, 'default' => 'today']),
            $area('maengel', 'Festgestellte Mängel', ['rows' => 6]), $date('frist', 'Frist zur Beseitigung'),
            $radio('ergebnis', 'Ergebnis', ['Keine Mängel', 'Mängel – Nachschau erforderlich', 'Erhebliche Mängel – Nutzung untersagt'], ['required' => true]),
        ], '', 'none'),
        'fd_bescheinigung' => $def('Brandschutzbescheinigung', 'Brandschutzbescheinigung', 'BB', 'fire', 'lead', 'Vorbeugender Brandschutz', 'letter', [
            $t('objekt', 'Objekt / Anschrift', ['required' => true]), $t('betreiber', 'Betreiber/in', ['required' => true]), $date('gueltig_bis', 'Gültig bis', ['required' => true]),
            $area('auflagen', 'Auflagen', ['rows' => 2]),
        ], "Für das Objekt **{{objekt}}** (Betreiber/in: {{betreiber}}) wird bescheinigt, dass bei der Brandschau keine Mängel festgestellt wurden, die einer Nutzung entgegenstehen.\n\nGültig bis: **{{gueltig_bis}}**\n\nAuflagen: {{auflagen}}", 'none', 'round', 'gueltig_bis'),

        // ============================================================ Verwaltung
        'gov_bescheid' => $def('Bescheid', 'Bescheid', 'BS', 'government', 'gov', 'Bescheide', 'letter', [
            $az, $t('betreff', 'Betreff', ['required' => true]), $area('tenor', 'Entscheidung', ['required' => true]), $area('gruende', 'Begründung', ['rows' => 5]),
            $area('rechtsbehelf', 'Rechtsbehelfsbelehrung', ['rows' => 2, 'default' => 'Gegen diesen Bescheid kann innerhalb eines Monats Widerspruch erhoben werden.']),
        ], "**{{betreff}}**\n\n{{tenor}}", 'optional'),
        'gov_genehmigung' => $def('Genehmigung / Lizenz', 'Genehmigung', 'GL', 'government', 'gov', 'Genehmigungen', 'letter', [
            $sel('art', 'Art', ['Gewerbeerlaubnis', 'Gaststättenerlaubnis / Ausschank', 'Taxi- / Personenbeförderung', 'Veranstaltung', 'Waffenhandel', 'Sondernutzung', 'Sonstiges'], ['required' => true]),
            $t('gegenstand', 'Gegenstand / Ort', ['required' => true]), $date('gueltig_bis', 'Gültig bis', ['required' => true]), $area('auflagen', 'Auflagen', ['rows' => 2]),
        ], "**{{patient.name}}** wird hiermit folgende Genehmigung erteilt:\n\n**{{art}}** – {{gegenstand}}\n\nGültig bis {{gueltig_bis}}. Auflagen: {{auflagen}}", 'required', 'round', 'gueltig_bis'),
        'gov_gewerbe' => $def('Gewerbeanmeldung', 'Gewerbeanmeldung', 'GA', 'government', 'gov', 'Gewerbe', 'protocol', [
            $t('firma', 'Name des Betriebs', ['required' => true, 'width' => 'half']), $t('anschrift', 'Betriebsanschrift', ['width' => 'half']),
            $area('taetigkeit', 'Angemeldete Tätigkeit', ['required' => true, 'rows' => 3]), $date('beginn', 'Beginn', ['default' => 'today']),
            ['name' => 'mitarbeiter', 'label' => 'Anzahl Beschäftigte', 'type' => 'number', 'width' => 'quarter'], $sign('sig_inhaber', 'Unterschrift Inhaber/in'),
        ]),
        'gov_bescheinigung' => $def('Bescheinigung', 'Bescheinigung', 'BC', 'government', 'gov', 'Bescheide', 'letter', [
            $t('betreff', 'Betreff', ['required' => true]), $area('text', 'Text', ['required' => true, 'rows' => 6]),
        ], "**{{betreff}}**\n\n{{text}}"),
    ];
}
