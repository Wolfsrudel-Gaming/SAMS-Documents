--[[ Fahndungsabfrage: /kennzeichen [KENNZEICHEN] – ohne Angabe das Fahrzeug direkt vor dir ]]

local function vehicleInFront()
    local ped = PlayerPedId()
    local veh = GetVehiclePedIsIn(ped, false)
    if veh ~= 0 then
        -- im Fahrzeug: das Fahrzeug vor dem eigenen (Raycast)
        local from = GetOffsetFromEntityInWorldCoords(veh, 0.0, 2.5, 0.3)
        local to = GetOffsetFromEntityInWorldCoords(veh, 0.0, 20.0, 0.3)
        local ray = StartShapeTestCapsule(from.x, from.y, from.z, to.x, to.y, to.z, 2.0, 10, veh, 7)
        local _, hit, _, _, entity = GetShapeTestResult(ray)
        return hit == 1 and IsEntityAVehicle(entity) and entity or nil
    end
    local coords = GetEntityCoords(ped)
    local closest = lib.getClosestVehicle(coords, 6.0, false)
    return closest
end

local function showResult(label, res)
    if not res or res.error then
        return Notify(res and res.error or Config.Text.offline, 'error')
    end
    if not res.wanted then
        return Notify(Config.Text.wantedNone:format(label), 'success')
    end
    for _, e in ipairs(res.entries or {}) do
        Notify(('%s%s – %s (%s, %s)'):format(e.warrant and 'HAFTBEFEHL: ' or 'FAHNDUNG: ', e.plate ~= '' and e.plate or e.name, e.reason, e.danger_label, e.agency), 'error')
    end
    PlaySoundFrontend(-1, 'TIMER_STOP', 'HUD_MINI_GAME_SOUNDSET', true)
end

if Config.WantedCommand then
    RegisterCommand(Config.WantedCommand, function(_, args)
        local plate = args[1]
        if not plate or plate == '' then
            local veh = vehicleInFront()
            if not veh then return Notify(Config.Text.wantedNoVehicle, 'inform') end
            plate = GetVehicleNumberPlateText(veh)
        end
        plate = plate:gsub('%s+', ''):upper()
        showResult(plate, lib.callback.await('sams:wanted:check', false, { plate = plate }))
    end, false)
    TriggerEvent('chat:addSuggestion', '/' .. Config.WantedCommand, 'SAMS: Kennzeichen abfragen (ohne Angabe: Fahrzeug vor dir)', { { name = 'kennzeichen', help = 'optional' } })
end

exports('checkPlate', function(plate)
    return lib.callback.await('sams:wanted:check', false, { plate = plate })
end)
