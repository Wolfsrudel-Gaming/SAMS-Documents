--[[
    SAMS Tablet – Einstellungen

    Adresse und API-Schlüssel des Websystems gehören NICHT hierher, sondern in die server.cfg
    (nur der Server kennt sie, Spieler können sie nicht auslesen):

        set sams_url "https://sams.deinserver.de"
        set sams_api_key "Wert von API_KEY aus der .env des Websystems"
]]

Config = {}

-- Befehlsnamen: Die Namen werden im Websystem eingestellt (Einstellungen → Spielserver) und beim Start der Resource
-- übernommen. Die Werte hier gelten nur, solange das Websystem nicht erreichbar ist; nil schaltet einen Befehl ganz ab.

-- Tablet öffnen: Befehl (nil = kein Befehl) und/oder Item
Config.Command = 'tablet'
Config.TabletItem = 'sams_tablet'
-- Befehl nur erlauben, wenn das Tablet-Item im Inventar ist
Config.CommandRequiresItem = true

-- Item, das beim Drucken ausgegeben wird
Config.DocumentItem = 'sams_document'

-- Drucker
Config.UseTarget = true                -- ox_target verwenden, falls gestartet (sonst Taste E)
Config.InteractDistance = 1.6          -- Meter
Config.PrintDuration = 4500            -- Druckdauer in ms (Fortschrittsbalken)
Config.PrinterSyncInterval = 60        -- Sekunden: Drucker-Liste vom Websystem abgleichen
Config.JobPollInterval = 6             -- Sekunden: neue Druckaufträge prüfen (Benachrichtigung)
Config.WaypointOnPrint = true          -- Wegpunkt zum Drucker setzen, wenn ein Auftrag bereitliegt
Config.PrinterBlip = { sprite = 184, color = 1, scale = 0.7, label = 'SAMS Drucker' }

-- Dienstzeit: Befehl zum Stempeln (nil = aus) und automatisches Ausstempeln beim Verlassen des Servers
Config.DutyCommand = 'dienst'
Config.DutyEndOnDisconnect = true

-- Apotheke: Rezepte (Dokument-Items) gegen Medikamenten-Items einlösen.
-- Welche ox_inventory-Items es gibt, wird im Websystem unter „Medikamente & BtM“ je Artikel eingetragen.
Config.Pharmacies = {
    { name = 'Apotheke Pillbox Hill', coords = vec4(318.6, -585.4, 43.28, 160.0), ped = 's_m_m_doctor_01', blip = true },
}
Config.PharmacyBlip = { sprite = 51, color = 2, scale = 0.7 }
Config.PharmacyDuration = 3000         -- ms Fortschrittsbalken beim Einlösen

-- Fahndung: Kennzeichenabfrage per Befehl (nil = aus); nur für Konten mit dem Recht „Fahndungen einsehen“
Config.WantedCommand = 'kennzeichen'
Config.WantedRequiresDuty = true       -- nur im Dienst

-- Akte am nächsten Spieler (/akte). Name des Befehls, Reichweite und Schalter (nur im Dienst, nur mit Job)
-- werden im Websystem unter Einstellungen → Spielserver gepflegt; nil = Befehl aus.
Config.AkteCommand = 'akte'

-- Rechnungen ins Spiel (esx_billing): Society-Konto und Abgleich stellt das Websystem ein (Einstellungen → Spielserver).
-- Absender-Kennung in der Tabelle billing (esx_billing braucht keinen angemeldeten Absender für Society-Rechnungen).
Config.BillSender = 'sams'

-- Dokument zeigen: maximale Entfernung zum anderen Spieler
Config.ShowDistance = 3.0

-- Animationen
Config.Anim = {
    tablet = {
        dict = 'amb@code_human_in_bus_passenger_idles@female@tablet@base', clip = 'base',
        prop = 'prop_cs_tablet', bone = 60309, pos = vec3(0.03, 0.002, -0.0), rot = vec3(10.0, 160.0, 0.0),
    },
    clipboard = {
        dict = 'missfam4', clip = 'base',
        prop = 'p_amb_clipboard_01', bone = 36029, pos = vec3(0.16, 0.08, 0.1), rot = vec3(-130.0, -50.0, 0.0),
    },
    printer = { scenario = 'PROP_HUMAN_PARKING_METER' },
}

-- Texte
Config.Text = {
    printerTarget = 'Drucker benutzen',
    printerHelp = '[E] Drucker benutzen',
    noJobs = 'Am Drucker liegen keine Dokumente für dich.',
    loginHint = 'Melde dich im Tablet an, um deine Druckaufträge abzuholen.',
    printing = 'Dokument wird gedruckt …',
    pickedUp = 'Du hast %s erhalten.',
    jobReady = 'Dein Dokument liegt am Drucker „%s“ bereit.',
    noTablet = 'Du hast kein Tablet dabei.',
    offline = 'Das SAMS-System ist gerade nicht erreichbar.',
    noSpace = 'Du hast keinen Platz im Inventar.',
    nobodyNear = 'Niemand in der Nähe.',
    showed = 'Du zeigst das Dokument.',
    dutyOn = 'Du bist jetzt im Dienst.',
    dutyOff = 'Du bist jetzt außer Dienst (%s).',
    dutyLogin = 'Melde dich zuerst im Tablet an, um zu stempeln.',
    pharmacyTarget = 'Rezept einlösen',
    pharmacyHelp = '[E] Rezept einlösen',
    noPrescription = 'Du hast kein Rezept dabei.',
    redeeming = 'Rezept wird eingelöst …',
    redeemed = 'Erhalten: %s',
    wantedNone = '%s: keine Fahndung.',
    wantedNoVehicle = 'Kein Fahrzeug vor dir. Kennzeichen mit angeben, z. B. ABC123.',
    akteNoPlayerData = 'Spielerdaten nicht verfügbar (ESX nicht gestartet?).',
    akteOpen = 'Akte im Tablet öffnen',
    akteCreate = 'Akte anlegen und öffnen',
    akteNone = 'Keine Akte vorhanden',
    akteNoRight = 'Du darfst keine Akten anlegen.',
    akteJob = 'Aktenabruf nur im passenden Job.',
    billReceived = 'Neue Rechnung: %s über $%s. Bezahlen im Rechnungsmenü.',
    wantedDuty = 'Fahndungsabfragen nur im Dienst.',
}
