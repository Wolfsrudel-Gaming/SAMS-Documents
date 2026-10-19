--[[
    SAMS Tablet – Brücke zur Leitstelle tetrascript (Resource tetra-core). Vollständig optional:
    Läuft tetra-core nicht oder ist die Anbindung im Websystem aus (Einstellungen → Spielserver → „Leitstelle tetrascript“),
    passiert hier nichts. Beide Systeme bleiben allein lauffähig.

    - FMS-Status: Hook statusChanged von tetra-core → Funkstatus der Person in SAMS (optional Dienst automatisch)
    - Einsätze: mit tetra-core ab Schnittstelle 2 sofort über den Hook incidentsChanged, sonst durch Lesen von
      tetra_dispatch_cad (gleiche Datenbank, nur lesend) → „Leitstelle“ im Tablet, Bericht per Knopf
    - ab Schnittstelle 2 zusätzlich: FMS-Status bei Dienstbeginn/-ende setzen (SetStatus) und Objektpläne mit
      Position als POI ins Objektverzeichnis der Leitstelle übertragen (SavePoi/RemovePoi)
]]

local hooks = {} ---@type table<string, number>
local apiVersion = 0
local versions = {} ---@type table<string, string> Einsatz-ID → zuletzt übertragener Stand
local lastHookAt = 0
local poiHashes = {} ---@type table<string, string> POI-ID → Stand

local function tetraOn()
    return GameConfig and GameConfig.tetra == true and GetResourceState('tetra-core') == 'started'
end

local function stamp(inc)
    return tostring(inc.version or '') .. ':' .. tostring(inc.updatedAt or '') .. ':' .. tostring(inc.phase or '')
end

--- Einsätze an das Websystem; bei Fehlern beim nächsten Durchlauf erneut.
local function pushIncidents(list)
    local changed = {}
    for _, inc in ipairs(list) do
        if type(inc) == 'table' and inc.id ~= nil then
            local key = tostring(inc.id)
            if versions[key] ~= stamp(inc) then
                versions[key] = stamp(inc)
                changed[#changed + 1] = inc
            end
        end
    end
    for i = 1, #changed, 50 do
        local chunk = { table.unpack(changed, i, math.min(i + 49, #changed)) }
        local data = Api.post('dispatch_incidents', { incidents = chunk })
        if not data or not data.ok then
            for _, inc in ipairs(chunk) do versions[tostring(inc.id)] = nil end
        end
    end
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

local function onIncidentsChanged(incidents)
    lastHookAt = os.time()
    if not tetraOn() or type(incidents) ~= 'table' then return end
    CreateThread(function() pushIncidents(incidents) end)
end

local function registerHooks()
    if GetResourceState('tetra-core') ~= 'started' then return end
    if apiVersion == 0 then
        local ok, v = pcall(function() return exports['tetra-core']:GetApiVersion() end)
        apiVersion = ok and tonumber(v) or 1
    end
    local function on(name, fn)
        if hooks[name] then return end
        local ok, id = pcall(function() return exports['tetra-core']:OnHook(name, fn) end)
        if ok and id and id ~= -1 then hooks[name] = id end
    end
    on('statusChanged', onStatusChanged)
    if apiVersion >= 2 then on('incidentsChanged', onIncidentsChanged) end
end

AddEventHandler('onResourceStart', function(res)
    if res == 'tetra-core' then hooks = {}; apiVersion = 0; poiHashes = {}; SetTimeout(2000, registerHooks) end
end)
AddEventHandler('onResourceStop', function(res)
    if res == 'tetra-core' then hooks = {}; apiVersion = 0 end
end)

--- Dienstbeginn/-ende in SAMS → FMS-Status am Funkgerät (nur mit Schnittstelle 2, Status in den Einstellungen wählbar).
AddEventHandler('sams:dutyChanged', function(src, onDuty)
    if not tetraOn() or apiVersion < 2 then return end
    local wanted = tonumber(onDuty and GameConfig.tetra_status_on or GameConfig.tetra_status_off)
    if not wanted then return end
    local ok, device = pcall(function() return exports['tetra-core']:GetPlayerDevice(tonumber(src)) end)
    if not ok or type(device) ~= 'table' or device.status == wanted then return end
    local name = GetPlayerName(src) or ''
    pcall(function() exports['tetra-core']:SetStatus(device.issi, wanted, 'external', ('SAMS: %s (%s)'):format(onDuty and 'Dienstbeginn' or 'Dienstende', name)) end)
end)

--- Einsätze aus dem Leitstellen-Speicher (ältere tetra-core-Stände ohne Hook, sonst als Sicherheitsnetz).
local function syncIncidents()
    if apiVersion >= 2 then
        local ok, list = pcall(function() return exports['tetra-core']:GetIncidents() end)
        if ok and type(list) == 'table' then return pushIncidents(list) end
    end
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
    local list = {}
    for id, inc in pairs(cad.incidents) do
        if type(inc) == 'table' then
            inc.id = inc.id or id
            list[#list + 1] = inc
        end
    end
    pushIncidents(list)
end

--- Objektpläne mit Position → Objektverzeichnis der Leitstelle (nur Geändertes; entfernte Objekte werden gelöscht).
local function syncPois()
    if apiVersion < 2 or not GameConfig.tetra_pois then return end
    local data = Api.post('dispatch_pois', {})
    if not data or not data.ok or type(data.pois) ~= 'table' then return end
    local seen = {}
    for _, poi in ipairs(data.pois) do
        seen[poi.id] = true
        local hash = json.encode(poi)
        if poiHashes[poi.id] ~= hash then
            local ok, res, err = pcall(function() return exports['tetra-core']:SavePoi(poi, 'SAMS') end)
            if ok and res then
                poiHashes[poi.id] = hash
            else
                print(('[sams_tablet] Objekt %s nicht an die Leitstelle übertragen: %s'):format(poi.name or poi.id, tostring(ok and err or res)))
            end
        end
    end
    for id in pairs(poiHashes) do
        if not seen[id] then
            pcall(function() exports['tetra-core']:RemovePoi(id, 'SAMS') end)
            poiHashes[id] = nil
        end
    end
end

CreateThread(function()
    Wait(8000)
    local poiAt = 0
    while true do
        if tetraOn() then
            registerHooks()
            -- Mit Hook genügt ein seltener Abgleich als Sicherheitsnetz
            if apiVersion < 2 or hooks.incidentsChanged == nil or os.time() - lastHookAt > 300 then
                pcall(syncIncidents)
            end
            if os.time() - poiAt >= 300 then
                poiAt = os.time()
                pcall(syncPois)
            end
        end
        Wait(((GameConfig and GameConfig.tetra_poll) or 20) * 1000)
    end
end)
