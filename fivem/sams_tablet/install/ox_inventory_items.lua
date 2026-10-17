--[[
    In ox_inventory/data/items.lua einfügen (innerhalb der return { … }-Tabelle).
    Bilder aus install/images/ nach ox_inventory/web/images/ kopieren.
]]

['sams_tablet'] = {
    label = 'SAMS Tablet',
    weight = 600,
    stack = false,
    close = true,
    description = 'Dienst-Tablet des San Andreas Medical Service',
    client = {
        export = 'sams_tablet.useTablet',
    },
},

['sams_document'] = {
    label = 'Dokument',
    weight = 5,
    stack = false,
    close = true,
    consume = 0,
    description = 'Ein ausgedrucktes Dokument',
    client = {
        export = 'sams_tablet.useDocument',
    },
},
