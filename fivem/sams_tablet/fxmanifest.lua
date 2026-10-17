fx_version 'cerulean'
game 'gta5'
lua54 'yes'

name 'sams_tablet'
description 'SAMS Tablet – Dokumentensystem im Spiel, Drucker und Dokument-Items'
author 'SAMS'
version '1.0.0'

shared_scripts {
    '@ox_lib/init.lua',
    'config.lua',
}

client_scripts {
    'client/utils.lua',
    'client/tablet.lua',
    'client/printers.lua',
    'client/document.lua',
}

server_scripts {
    'server/api.lua',
    'server/main.lua',
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
