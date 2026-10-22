fx_version 'cerulean'
game 'gta5'
lua54 'yes'

name 'sams_tablet'
description 'Wolfpack Documents – Tablet, Drucker und Dokument-Items im Spiel'
author 'Wolfpack Gaming'
version '1.12.0'

shared_scripts {
    '@ox_lib/init.lua',
    'config.lua',
}

client_scripts {
    'client/utils.lua',
    'client/tablet.lua',
    'client/printers.lua',
    'client/document.lua',
    'client/duty.lua',
    'client/pharmacy.lua',
    'client/wanted.lua',
    'client/akte.lua',
    'client/sky.lua',
    'client/forgery.lua',
    'client/medication.lua',
}

server_scripts {
    'server/api.lua',
    'server/main.lua',
    'server/pharmacy.lua',
    'server/esx.lua',   -- optional: es_extended + oxmysql (Rechnungen, Spielerdaten, /akte)
    'server/sky.lua',   -- optional: sky_ambulancejob (Vitalwerte, Tod/Wiederbelebung, Medikamente, Sky Billing)
    'server/tetra.lua', -- optional: Leitstelle tetrascript (tetra-core): FMS-Status, Einsätze
    'server/forgery.lua', -- optional: Fälscherwerkstatt (im Websystem einzuschalten)
    'server/integrations.lua', -- Exports und Ereignisse für eigene Resourcen (docs/API.md)
}

ui_page 'web/index.html'

files {
    'web/index.html',
    'web/style.css',
    'web/app.js',
    'web/logo.svg',
}

dependencies {
    'ox_lib',
    'ox_inventory',
}
