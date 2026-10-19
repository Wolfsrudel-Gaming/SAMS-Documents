--[[
    SAMS – Items für ox_inventory
    1. Den Inhalt in ox_inventory/data/items.lua einfügen (innerhalb der return { … }-Tabelle).
    2. Alle Bilder aus install/images/ nach ox_inventory/web/images/ kopieren.
    3. ox_inventory neu starten.

    Abschnitte:
      A  Pflicht: Tablet und Dokument
      B  Fälscherwerkstatt (nur nötig, wenn Fälschungen im Websystem eingeschaltet sind)
      C  Medikamente für die Apotheke (optional; im Websystem unter „Medikamente & BtM“ beim Artikel eintragen
         oder dort mit „Standard-Items eintragen“ automatisch zuordnen). Die Wirkung regelt euer Medizin-Script –
         beim Einnehmen löst sams_tablet nur das Ereignis 'sams:medicationTaken' (Client) aus.
]]

-- ============================================================ A  Tablet und Dokument

['sams_tablet'] = {
    label = 'SAMS Tablet',
    weight = 600,
    stack = false,
    close = true,
    description = 'Dienst-Tablet mit Zugang zum Dokumentensystem',
    client = { export = 'sams_tablet.useTablet' },
},

['sams_document'] = {
    label = 'Dokument',
    weight = 5,
    stack = false,
    close = true,
    consume = 0,
    description = 'Ein ausgedrucktes Dokument',
    client = { export = 'sams_tablet.useDocument' },
},

-- ============================================================ B  Fälscherwerkstatt

['forgery_paper'] = {
    label = 'Sicherheitspapier (Rohling)',
    weight = 50,
    stack = true,
    close = true,
    description = 'Unbedrucktes Papier mit Guillochen und Wasserzeichen. Woher hast du das?',
},

['forgery_ink'] = {
    label = 'Spezialtoner',
    weight = 250,
    stack = true,
    close = true,
    description = 'Tonerkartusche für hochauflösenden Farbdruck',
},

-- ============================================================ C  Medikamente (optional)

['sams_ibuprofen600'] = { label = 'Ibuprofen 600 mg', weight = 40, stack = true, close = true, consume = 1, description = 'Schmerzmittel, Filmtabletten', client = { export = 'sams_tablet.useMedication' } },
['sams_ibuprofen400'] = { label = 'Ibuprofen 400 mg', weight = 40, stack = true, close = true, consume = 1, description = 'Schmerzmittel, Filmtabletten', client = { export = 'sams_tablet.useMedication' } },
['sams_paracetamol']  = { label = 'Paracetamol 500 mg', weight = 40, stack = true, close = true, consume = 1, description = 'Schmerz- und Fiebermittel', client = { export = 'sams_tablet.useMedication' } },
['sams_metamizol']    = { label = 'Metamizol 500 mg', weight = 40, stack = true, close = true, consume = 1, description = 'Starkes Schmerzmittel (Novaminsulfon)', client = { export = 'sams_tablet.useMedication' } },
['sams_tramadol']     = { label = 'Tramadol 50 mg', weight = 40, stack = true, close = true, consume = 1, description = 'Opioid-Schmerzmittel, Kapseln', client = { export = 'sams_tablet.useMedication' } },
['sams_morphin']      = { label = 'Morphin 10 mg (BtM)', weight = 40, stack = true, close = true, consume = 1, description = 'Betäubungsmittel – nur auf BtM-Rezept', client = { export = 'sams_tablet.useMedication' } },
['sams_oxycodon']     = { label = 'Oxycodon 10 mg (BtM)', weight = 40, stack = true, close = true, consume = 1, description = 'Betäubungsmittel – nur auf BtM-Rezept', client = { export = 'sams_tablet.useMedication' } },
['sams_fentanyl']     = { label = 'Fentanyl-Ampulle (BtM)', weight = 20, stack = true, close = true, consume = 1, description = 'Betäubungsmittel zur Injektion – nur durch Fachpersonal', client = { export = 'sams_tablet.useMedication' } },
['sams_amoxicillin']  = { label = 'Amoxicillin 1000 mg', weight = 40, stack = true, close = true, consume = 1, description = 'Antibiotikum, Filmtabletten', client = { export = 'sams_tablet.useMedication' } },
['sams_ondansetron']  = { label = 'Ondansetron 4 mg', weight = 30, stack = true, close = true, consume = 1, description = 'Mittel gegen Übelkeit, Schmelztabletten', client = { export = 'sams_tablet.useMedication' } },
['sams_salbutamol']   = { label = 'Salbutamol-Spray', weight = 80, stack = true, close = true, consume = 1, description = 'Asthmaspray 100 µg', client = { export = 'sams_tablet.useMedication' } },
['sams_cetirizin']    = { label = 'Cetirizin 10 mg', weight = 30, stack = true, close = true, consume = 1, description = 'Mittel gegen Allergien', client = { export = 'sams_tablet.useMedication' } },
['sams_bepanthen']    = { label = 'Wund- und Heilsalbe', weight = 60, stack = true, close = true, consume = 1, description = 'Salbe zur Wundversorgung, 20 g', client = { export = 'sams_tablet.useMedication' } },
['sams_bandage']      = { label = 'Mullbinde', weight = 50, stack = true, close = true, consume = 1, description = 'Verbandsmaterial', client = { export = 'sams_tablet.useMedication' } },
