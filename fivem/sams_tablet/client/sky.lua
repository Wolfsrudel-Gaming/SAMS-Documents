--[[ Sky Ambulance: Vitalwerte für den Medic bereitstellen, Tod/Wiederbelebung melden, Test-Befehl für das Verletzungsprofil ]]

local function skyStarted()
    return GetResourceState('sky_ambulancejob') == 'started'
end

--- Der Server fragt die Vitalwerte dieses Spielers ab (Medic hat „Vitalwerte übernehmen“ gewählt).
lib.callback.register('sams:sky:profile', function()
    if not skyStarted() then return nil end
    local ok, profile = pcall(function() return exports.sky_ambulancejob:GetLocalInjuryProfile() end)
    if not ok or type(profile) ~= 'table' then return nil end
    -- nur die dokumentierten Zahlenwerte weitergeben
    local v = profile.vitals or {}
    return {
        vitalSchemaVersion = profile.vitalSchemaVersion,
        vitals = { heart_rate = v.heart_rate, oxygen_level = v.oxygen_level, blood_sugar = v.blood_sugar,
            blood_pressure = type(v.blood_pressure) == 'table' and { systolic = v.blood_pressure.systolic, diastolic = v.blood_pressure.diastolic } or nil },
        conditions = type(profile.conditions) == 'table' and { cardiacArrest = profile.conditions.cardiacArrest == true, activityMode = profile.conditions.activityMode } or {},
    }
end)

-- Tod/Wiederbelebung: jede Änderung des Todeszustands melden (der Server prüft mit isDead nach)
local lastDead = nil
AddEventHandler('sky_ambulancejob:deathStateChanged', function(state)
    if type(state) ~= 'table' then return end
    local dead = state.dead == true
    if dead ~= lastDead then
        lastDead = dead
        local cfg = GlobalState.samsConfig or {}
        if cfg.sky and cfg.sky_death then
            TriggerServerEvent('sams:sky:death', dead)
        end
    end
end)

-- Test-Befehl: eigenes Verletzungsprofil in der F8-Konsole ausgeben (zum Einrichten weiterer Übernahmen)
RegisterCommand('samsprofil', function()
    if not skyStarted() then return print('[sams_tablet] sky_ambulancejob ist nicht gestartet.') end
    local ok, profile = pcall(function() return exports.sky_ambulancejob:GetLocalInjuryProfile() end)
    print('[sams_tablet] GetLocalInjuryProfile():')
    print(ok and json.encode(profile, { indent = true }) or ('Fehler: ' .. tostring(profile)))
end, false)
