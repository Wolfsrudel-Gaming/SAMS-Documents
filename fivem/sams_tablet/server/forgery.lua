--[[
    SAMS Tablet – Fälscherwerkstatt (optional, im Websystem unter Administration → Fälschungen einzuschalten).
    Hürden, alle serverseitig geprüft: Werkstatt in Reichweite, Verbrauchsmaterial, echtes Original als Vorlage,
    Mindest-Arbeitszeit, Abklingzeit je Spieler, Alarm-Risiko. Die Qualität ergibt sich aus den bestandenen
    Geschicklichkeitsprüfungen. Das gedruckte Blatt ist ein normales Dokument-Item – nur der Prüfcode existiert nicht.
]]

local running = {} ---@type table<number, { site: number, started: number, originals: string[] }>
local sessions = {} ---@type table<number, string>
local cooldown = {} ---@type table<string, number>

local function license(src)
    return GetPlayerIdentifierByType(src, 'license') or ('player:' .. src)
end

local function cfg()
    return GameConfig and GameConfig.forgery or { enabled = false }
end

local function findSite(id)
    for _, s in ipairs(cfg().sites or {}) do
        if s.id == id then return s end
    end
end

local function near(src, s)
    return #(GetEntityCoords(GetPlayerPed(src)) - vector3(s.x, s.y, s.z)) <= 3.0
end

local function itemLabel(name)
    local ok, items = pcall(function() return exports.ox_inventory:Items(name) end)
    return ok and type(items) == 'table' and items.label or name
end

-- Werkstätten an die Spieler (nur Position und Prop – ob man dort etwas kann, prüft der Server)
CreateThread(function()
    local last = nil
    while true do
        local c = cfg()
        local state = { enabled = c.enabled == true, sites = c.enabled and c.sites or {} }
        local encoded = json.encode(state)
        if encoded ~= last then
            last = encoded
            GlobalState.samsForgery = state
        end
        Wait(30000)
    end
end)

lib.callback.register('sams:forge:start', function(src, siteId)
    local c = cfg()
    local s = c.enabled and findSite(tonumber(siteId)) or nil
    if not s or not near(src, s) then return { error = Config.Text.forgeNothing } end
    if running[src] then return { error = Config.Text.forgeBusy } end
    local lic = license(src)
    if cooldown[lic] and os.time() < cooldown[lic] then
        return { error = Config.Text.forgeCooldown:format(math.ceil((cooldown[lic] - os.time()) / 60)) }
    end
    for _, m in ipairs(c.materials or {}) do
        if (exports.ox_inventory:Search(src, 'count', m.item) or 0) < m.count then
            return { error = Config.Text.forgeMaterial:format(m.count, itemLabel(m.item)) }
        end
    end
    local originals = {}
    for _, slot in pairs(exports.ox_inventory:Search(src, 'slots', Config.DocumentItem) or {}) do
        if slot.metadata and slot.metadata.sams_token then originals[#originals + 1] = slot.metadata.sams_token end
    end
    if c.original and #originals == 0 then return { error = Config.Text.forgeOriginal } end
    running[src] = { site = s.id, started = os.time(), originals = originals }
    return { ok = true, duration = c.duration or 60, checks = c.checks or 4, difficulty = c.difficulty or 'medium' }
end)

lib.callback.register('sams:forge:abort', function(src)
    running[src] = nil
    return true
end)

lib.callback.register('sams:forge:finish', function(src, siteId, passed, street)
    local r = running[src]
    running[src] = nil
    local c = cfg()
    local s = c.enabled and findSite(tonumber(siteId)) or nil
    if not r or not s or r.site ~= s.id or not near(src, s) then return { error = Config.Text.forgeNothing } end
    if os.time() - r.started < math.floor((c.duration or 60) * 0.8) then return { error = Config.Text.forgeNothing } end
    -- Material ist verbraucht – auch wenn der Druck misslingt
    for _, m in ipairs(c.materials or {}) do
        if not exports.ox_inventory:RemoveItem(src, m.item, m.count) then
            return { error = Config.Text.forgeMaterial:format(m.count, itemLabel(m.item)) }
        end
    end
    cooldown[license(src)] = os.time() + (c.cooldown or 0) * 60
    if (c.alert or 0) > 0 and math.random(100) <= c.alert then
        local streetName = type(street) == 'string' and street:sub(1, 80) or ''
        CreateThread(function() Api.post('forge_alert', { street = streetName }) end)
        TriggerEvent('sams:forgeryAlert', src, vector3(s.x, s.y, s.z), streetName) -- für eigene Dispatch-Skripte
    end
    local checks = math.max(1, c.checks or 4)
    passed = math.max(0, math.min(checks, math.floor(tonumber(passed) or 0)))
    if passed == 0 then return { error = Config.Text.forgeFailed } end
    local quality = math.max(1, math.min(100, math.floor(passed / checks * 100) + math.random(-8, 4)))
    local data = Api.post('forge_start', { site_id = s.id, quality = quality, originals = r.originals, license = license(src), player = GetPlayerName(src) })
    if not data or not data.ok then return { error = data and data.error or Config.Text.offline } end
    sessions[src] = data.session
    return { ok = true, url = data.url, origin = data.origin }
end)

lib.callback.register('sams:forge:collect', function(src)
    local session = sessions[src]
    if not session then return { error = Config.Text.forgeNothing } end
    if not exports.ox_inventory:CanCarryItem(src, Config.DocumentItem, 1) then return { error = Config.Text.noSpace } end
    local data = Api.post('forge_result', { session = session, license = license(src) })
    if not data or not data.ok then return { error = data and data.error or Config.Text.offline } end
    sessions[src] = nil
    if not exports.ox_inventory:AddItem(src, Config.DocumentItem, 1, data.metadata) then return { error = Config.Text.noSpace } end
    return { ok = true, label = data.metadata.label }
end)

AddEventHandler('playerDropped', function()
    running[source] = nil
    sessions[source] = nil
end)
