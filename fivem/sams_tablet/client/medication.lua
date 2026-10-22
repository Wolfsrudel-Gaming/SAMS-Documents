--[[ Medikamenten-Items (optional, siehe install/ox_inventory_items.lua, Abschnitt C): einnehmen bzw. anwenden.
     Die eigentliche Wirkung regelt euer Medizin-Script über das Ereignis 'sams:medicationTaken'. ]]

local kinds = {
    sams_salbutamol = { label = 'Spray anwenden …', anim = { dict = 'mp_player_inteat@burger', clip = 'mp_player_int_eat_burger' } },
    sams_bepanthen = { label = 'Salbe auftragen …', anim = { dict = 'missheistdockssetup1clipboard@idle_a', clip = 'idle_a' } },
    sams_bandage = { label = 'Verband anlegen …', anim = { dict = 'missheistdockssetup1clipboard@idle_a', clip = 'idle_a' } },
    sams_fentanyl = { label = 'Injektion …', anim = { dict = 'rcmpaparazzo_1ig_4', clip = 'miranda_shooting_up' } },
}

exports('useMedication', function(data)
    local k = kinds[data.name] or { label = 'Einnehmen …', anim = { dict = 'mp_suicide', clip = 'pill' } }
    local ok = lib.progressBar({ duration = 3500, label = k.label, canCancel = true, useWhileDead = false, disable = { combat = true, car = false }, anim = k.anim })
    if not ok then return end
    exports.ox_inventory:useItem(data, function(used)
        if used then
            TriggerEvent('sams:medicationTaken', data.name, data.label)
            Notify(('%s angewendet.'):format(data.label or data.name), 'success')
        end
    end)
end)
