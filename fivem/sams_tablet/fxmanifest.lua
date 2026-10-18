fx_version 'cerulean'
game 'gta5'
lua54 'yes'

name 'sams_tablet'
description 'SAMS Tablet – Dokumentensystem im Spiel, Drucker und Dokument-Items'
author 'SAMS'
version '1.5.0'

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
}

server_scripts {
    'server/api.lua',
    'server/main.lua',
    'server/pharmacy.lua',
    'server/esx.lua',   -- optional: es_extended + oxmysql (Rechnungen, Spielerdaten, /akte)
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
