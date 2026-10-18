--[[
    SAMS Tablet – Brücke zur Leitstelle tetrascript (Resource tetra-core). Vollständig optional:
    Läuft tetra-core nicht oder ist die Anbindung im Websystem aus (Einstellungen → Spielserver → „Leitstelle tetrascript“),
    passiert hier nichts. tetra-core muss dafür nicht verändert werden.

    - FMS-Status: Hook statusChanged von tetra-core → Funkstatus der Person in SAMS (optional Dienst automatisch)
    - Einsätze: liest den Einsatzstand der Leitstelle (Tabelle tetra_dispatch_cad, gleiche Datenbank, nur lesend)
      und überträgt neue/geänderte Einsätze an das Websystem → „Leitstelle“ im Tablet, Bericht per Knopf
]]

local hookId = nil
local versions = {} ---@type table<string, string> Einsatz-ID → zuletzt übertragener Stand

local function tetraOn()
    return GameConfig and GameConfig.tetra == true and GetResourceState('tetra-core') == 'started'
end

local function onStatusChanged(device, status, previous, source)
    if not tetraOn() or type(device) ~= 'table' then return end
    local src = tonumber(device.owner)
    if not src or not GetPlayerName(src) then return end
    local s = SamsSession(src)
    if not s then return end
    CreateThread(function()
        local data = Api.post('tetra_status', { token = s.token, status = tonumber(status), issi = tostring(device.issi or ''), opta = tostring(device.opta or '') })
        if data and data.ok and data.duty ~= nil then
            TriggerEvent('sams:dutyChanged', src, data.duty)
            TriggerClientEvent('sams:duty', src, data.duty)
        end
    end)
end

local function registerHook()
    if hookId or GetResourceState('tetra-core') ~= 'started' then return end
    local ok, id = pcall(function() return exports['tetra-core']:OnHook('statusChanged', onStatusChanged) end)
    if ok then hookId = id end
end

AddEventHandler('onResourceStart', function(res)
    if res == 'tetra-core' then hookId = nil; SetTimeout(2000, registerHook) end
end)
AddEventHandler('onResourceStop', function(res)
    if res == 'tetra-core' then hookId = nil end
end)

--- Einsätze aus dem Leitstellen-Speicher lesen (nur lesend, oxmysql) und Geändertes übertragen.
local function syncIncidents()
    if GetResourceState('oxmysql') ~= 'started' then return end
    local p = promise.new()
    local ok = pcall(function()
        exports.oxmysql:scalar('SELECT payload FROM tetra_dispatch_cad WHERE id = 1', {}, function(r) p:resolve(r or false) end)
    end)
    if not ok then return end
    SetTimeout(10000, function() p:resolve(false) end)
    local payload = Citizen.Await(p)
    if type(payload) ~= 'string' or payload == '' then return end
    local okJson, cad = pcall(json.decode, payload)
    if not okJson or type(cad) ~= 'table' or type(cad.incidents) ~= 'table' then return end
    local changed = {}
    for id, inc in pairs(cad.incidents) do
        if type(inc) == 'table' then
            local stamp = tostring(inc.version or '') .. ':' .. tostring(inc.updatedAt or '') .. ':' .. tostring(inc.phase or '')
            local key = tostring(inc.id or id)
            if versions[key] ~= stamp then
                inc.id = inc.id or id
                changed[#changed + 1] = inc
                versions[key] = stamp
            end
        end
        if #changed >= 50 then break end
    end
    if #changed > 0 then
        local data = Api.post('dispatch_incidents', { incidents = changed })
        if not data or not data.ok then
            -- beim nächsten Durchlauf erneut versuchen
            for _, inc in ipairs(changed) do versions[tostring(inc.id)] = nil end
        end
    end
end

CreateThread(function()
    Wait(8000)
    while true do
        if tetraOn() then
            registerHook()
            pcall(syncIncidents)
        end
        Wait(((GameConfig and GameConfig.tetra_poll) or 20) * 1000)
    end
end)
