--[[
    Wolfpack Documents Tablet – Apotheke (Server)
    Rezepte im Inventar finden, beim Websystem prüfen und einlösen, Medikamenten-Items ausgeben.
    Doppeltes Einlösen verhindert das Websystem (jede Position nur einmal).
]]

local busy = {} ---@type table<number, boolean>

local function license(src)
    return GetPlayerIdentifierByType(src, 'license') or ('player:' .. src)
end

local function nearPharmacy(src, index)
    local p = Config.Pharmacies[index]
    if not p then return false end
    local coords = GetEntityCoords(GetPlayerPed(src))
    return #(coords - vec3(p.coords.x, p.coords.y, p.coords.z)) <= Config.InteractDistance + 3.0
end

--- Rezept-Items des Spielers: { [token] = slot }
local function prescriptionSlots(src)
    local found = {}
    local slots = exports.ox_inventory:Search(src, 'slots', Config.DocumentItem) or {}
    for _, slot in pairs(slots) do
        local m = slot.metadata
        if m and m.sams_type == 'rezept' and type(m.sams_token) == 'string' and not found[m.sams_token] then
            found[m.sams_token] = slot
        end
    end
    return found
end

lib.callback.register('sams:pharmacy:list', function(src, index)
    if not nearPharmacy(src, index) then
        return { error = 'Du bist zu weit entfernt.' }
    end
    local slots = prescriptionSlots(src)
    local tokens = {}
    for token in pairs(slots) do tokens[#tokens + 1] = token end
    if #tokens == 0 then
        return { prescriptions = {} }
    end
    local data = Api.post('rx_check', { tokens = tokens })
    if not data or not data.ok then
        return { error = Config.Text.offline }
    end
    return { prescriptions = data.prescriptions }
end)

lib.callback.register('sams:pharmacy:redeem', function(src, index, token)
    if busy[src] or type(token) ~= 'string' or not nearPharmacy(src, index) then
        return { error = 'Gerade nicht möglich.' }
    end
    local slot = prescriptionSlots(src)[token]
    if not slot then
        return { error = Config.Text.noPrescription }
    end
    busy[src] = true
    -- Vorab prüfen, ob alles ins Inventar passt (sonst wäre das Rezept eingelöst, aber nichts ausgegeben)
    local check = Api.post('rx_check', { tokens = { token } })
    local rx = check and check.ok and check.prescriptions and check.prescriptions[1]
    if not rx then
        busy[src] = nil
        return { error = Config.Text.offline }
    end
    for _, line in ipairs(rx.lines or {}) do
        if line.status == 'ok' and not exports.ox_inventory:CanCarryItem(src, line.item, line.count) then
            busy[src] = nil
            return { error = Config.Text.noSpace }
        end
    end
    local data = Api.post('rx_redeem', { token = token, license = license(src), player = GetPlayerName(src) })
    if not data or not data.ok then
        busy[src] = nil
        return { error = data and data.error or Config.Text.offline }
    end
    local names = {}
    for _, it in ipairs(data.items or {}) do
        if exports.ox_inventory:AddItem(src, it.item, it.count) then
            names[#names + 1] = it.count .. '× ' .. it.name
        else
            print(('^1[sams_tablet] Item %s konnte nicht ausgegeben werden (Spieler %s, Rezept %s)^0'):format(it.item, src, data.number))
        end
    end
    -- Rezept als eingelöst kennzeichnen (bleibt als Beleg im Inventar)
    local meta = slot.metadata
    if not meta.sams_redeemed then
        meta.description = (meta.description or '') .. ' · eingelöst'
        meta.sams_redeemed = true
    end
    exports.ox_inventory:SetMetadata(src, slot.slot, meta)
    busy[src] = nil
    return { ok = true, given = table.concat(names, ', '), notes = data.notes }
end)

AddEventHandler('playerDropped', function()
    busy[source] = nil
end)
