--[[ Akte am nächsten Spieler: /akte zeigt die Kurzfassung (Allergien, Blutgruppe, Hinweise …) und öffnet die Akte im Tablet ]]

local function nearestPlayer()
    local cfg = GlobalState.samsConfig or {}
    local player = lib.getClosestPlayer(GetEntityCoords(PlayerPedId()), tonumber(cfg.akte_distance) or 3.0, false)
    return player and GetPlayerServerId(player) or nil
end

local function openRecord(target, create)
    local res = lib.callback.await('sams:akte:open', false, target, create)
    if not res or res.error then
        return Notify(res and res.error or Config.Text.offline, 'error')
    end
    OpenTablet(res)
end

local function showRecord()
    local target = nearestPlayer()
    if not target then
        return Notify(Config.Text.nobodyNear, 'inform')
    end
    local res = lib.callback.await('sams:akte:lookup', false, target)
    if not res or res.error then
        return Notify(res and res.error or Config.Text.offline, 'error')
    end
    local options = {}
    for _, line in ipairs(res.summary or {}) do
        options[#options + 1] = { title = line.label, description = line.value, readOnly = true }
    end
    -- Sky Ambulance: Behandlung starten und Vitalwerte in die Akte übernehmen
    local cfg = GlobalState.samsConfig or {}
    if cfg.sky and GetResourceState('sky_ambulancejob') == 'started' then
        table.insert(options, 1, { title = Config.Text.skyVitals, icon = 'heart-pulse', onSelect = function()
            local r = lib.callback.await('sams:sky:vitals', false, target)
            if not r or r.error then return Notify(r and r.error or Config.Text.offline, 'error') end
            Notify(('%s: %s'):format(r.name, r.text), r.cardiac_arrest and 'error' or 'success')
        end })
        table.insert(options, 1, { title = Config.Text.skyTreat, icon = 'kit-medical', onSelect = function()
            CreateThread(function()
                local started, reason = exports.sky_ambulancejob:OpenTreatment(target)
                if not started then Notify(Config.Text.skyTreatFail:format(type(reason) == 'table' and (reason.reason or '?') or tostring(reason)), 'error') end
            end)
        end })
    end
    if res.found then
        table.insert(options, 1, { title = Config.Text.akteOpen, icon = 'folder-open', onSelect = function() openRecord(target, false) end })
    elseif res.can_create then
        options[#options + 1] = { title = Config.Text.akteCreate, icon = 'folder-plus', onSelect = function() openRecord(target, true) end }
    else
        options[#options + 1] = { title = Config.Text.akteNone, description = Config.Text.akteNoRight, readOnly = true }
    end
    lib.registerContext({ id = 'sams_akte', title = ('%s · %s'):format(res.name or '?', res.record or 'Akte'), options = options })
    lib.showContext('sams_akte')
end

SamsCommand('akte', Config.AkteCommand, showRecord, 'SAMS: Akte der nächsten Person abrufen')

exports('showRecord', showRecord)
