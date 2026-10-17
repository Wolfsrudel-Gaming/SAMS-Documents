--[[
    SAMS Tablet – Einstellungen

    Adresse und API-Schlüssel des Websystems gehören NICHT hierher, sondern in die server.cfg
    (nur der Server kennt sie, Spieler können sie nicht auslesen):

        set sams_url "https://sams.deinserver.de"
        set sams_api_key "Wert von API_KEY aus der .env des Websystems"
]]

Config = {}

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
}
