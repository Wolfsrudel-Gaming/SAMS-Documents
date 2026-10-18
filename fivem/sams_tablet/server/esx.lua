--[[
    SAMS Tablet – Spielserver-Anbindung (ESX)
    - Einstellungen aus dem Websystem (Einstellungen → Spielserver): Befehle, Society-Konten, Jobs, Schalter
    - Rechnungen ins Spiel: legt Einträge in der Tabelle von esx_billing an und meldet die Zahlung zurück
    - /akte: Spielerdaten (identifier, Name, Geburtsdatum, Geschlecht) des nächsten Spielers an das Websystem

    Ohne es_extended/oxmysql bleibt dieser Teil still; Tablet, Drucker usw. funktionieren weiter.
]]

GameConfig = nil ---@type table|nil

local ESX = nil

local function loadConfig()
    local data = Api.post('game_config', {})
    if data and data.ok then
        if not GameConfig and data.server and data.server.name and data.server.name ~= '' then
            print(('[sams_tablet] Mit dem Portal verbunden als Spielserver „%s“'):format(data.server.name))
        end
        GameConfig = data
        -- an alle Spieler (keine Geheimnisse): Befehlsnamen und Reichweite
        GlobalState.samsConfig = { commands = data.commands, framework = data.framework, akte_distance = data.akte_distance,
            sky = data.sky == true and GetResourceState('sky_ambulancejob') == 'started', sky_death = data.sky_death == true }
    end
end

CreateThread(function()
    while true do
        loadConfig()
        Wait((GameConfig and 300 or 30) * 1000)
    end
end)

CreateThread(function()
    if GetResourceState('es_extended') == 'missing' then return end
    while GetResourceState('es_extended') ~= 'started' do Wait(500) end
    ESX = exports.es_extended:getSharedObject()
end)

local function esxReady()
    return ESX ~= nil and GameConfig ~= nil and GameConfig.framework == 'esx'
end

local function factionCfg(id)
    for _, f in ipairs(GameConfig and GameConfig.factions or {}) do
        if f.id == id then return f end
    end
end

-- ------------------------------------------------------------ Spielerdaten --

--- ESX-Daten eines Spielers (nur auf dem Server gelesen – nie vom Client übernommen).
local function playerData(src)
    if not ESX then return nil end
    local x = ESX.GetPlayerFromId(src)
    if not x then return nil end
    local function var(k)
        local ok, v = pcall(x.get, k)
        if ok and v ~= nil then return v end
        return x.variables and x.variables[k] or nil
    end
    local first, last = var('firstName'), var('lastName')
    if not first or first == '' then
        local name = x.getName and x.getName() or ''
        first, last = name:match('^(%S+)%s+(.+)$')
        first = first or name
    end
    local job = x.job or (x.getJob and x.getJob()) or {}
    return {
        citizen_id = x.identifier or (x.getIdentifier and x.getIdentifier()) or '',
        first_name = first or '', last_name = last or '',
        dob = tostring(var('dateofbirth') or ''), sex = tostring(var('sex') or ''),
        job = job.name or '',
    }
end

exports('GetPlayerData', playerData)
SamsPlayerData = playerData -- für main.lua (Unterschriften)

-- -------------------------------------------------------------- Rechnungen --

local function mysql(method, query, params)
    if GetResourceState('oxmysql') ~= 'started' then return nil end
    local p, done = promise.new(), false
    local function finish(res)
        if not done then done = true; p:resolve(res) end
    end
    local ok = pcall(function()
        if method == 'insert' then exports.oxmysql:insert(query, params, finish)
        elseif method == 'update' then exports.oxmysql:update(query, params, finish)
        else exports.oxmysql:query(query, params, finish) end
    end)
    if not ok then return nil end
    SetTimeout(10000, function() finish(nil) end)
    return Citizen.Await(p)
end

local function notifyCitizen(identifier, text)
    local x = ESX and ESX.GetPlayerFromIdentifier(identifier)
    if x then
        TriggerClientEvent('ox_lib:notify', x.source, { title = 'Rechnung', description = text, type = 'inform' })
    end
end

--- Sky Billing (sky_jobs_base): Rechnung von der Fraktion (Society/Job) an den Spieler. Gibt die Rechnungs-ID oder nil, Fehler.
local function skyCreate(b)
    local ok, res = pcall(function()
        return exports.sky_jobs_base:CreateInvoice({
            from = { type = 'society', job = b.job },
            to = { type = 'player', identifier = b.citizen_id, name = b.name },
            createdBy = { type = 'society', job = b.job },
            amount = b.amount, reason = b.label, reference = b.reference,
            depositToSociety = true, paymentMode = 'later', sourceType = 'sams',
        })
    end)
    if not ok or type(res) ~= 'table' then return nil, 'sky_jobs_base nicht erreichbar' end
    if not res.success then return nil, tostring(res.error or 'abgelehnt') end
    local inv = res.data or {}
    return tostring(inv.id or inv.number or ''), nil
end

local function skySync(sync)
    if GetResourceState('sky_jobs_base') ~= 'started' then return end
    for _, b in ipairs(sync.pending or {}) do
        local ref, err = skyCreate(b)
        if ref and ref ~= '' then
            Api.post('bill_result', { id = b.id, result = 'sent', ext_ref = ref })
            notifyCitizen(b.citizen_id, Config.Text.billReceived:format(b.label, b.amount))
        else
            Api.post('bill_result', { id = b.id, result = 'failed', error = 'Sky Billing: ' .. tostring(err) })
        end
    end
    for _, b in ipairs(sync.cancel or {}) do
        local ok, res = pcall(function() return exports.sky_jobs_base:CancelInvoice(b.ext_ref) end)
        if ok then
            local inv = select(2, pcall(function() return exports.sky_jobs_base:GetInvoice(b.ext_ref) end))
            local paid = type(inv) == 'table' and inv.data and tonumber(inv.data.balanceDue) == 0
            Api.post('bill_result', { id = b.id, result = paid and 'paid' or 'cancelled' })
        end
    end
    -- bezahlt, wenn nichts mehr offen ist (balanceDue = 0); storniert in Sky → zurückgezogen
    for _, b in ipairs(sync.open or {}) do
        local ok, res = pcall(function() return exports.sky_jobs_base:GetInvoice(b.ext_ref) end)
        if ok and type(res) == 'table' and res.success and res.data then
            local inv = res.data
            if tonumber(inv.balanceDue) == 0 and (tonumber(inv.amountPaid) or 0) > 0 then
                Api.post('bill_result', { id = b.id, result = 'paid' })
            elseif inv.status == 'cancelled' or inv.status == 'canceled' then
                Api.post('bill_result', { id = b.id, result = 'cancelled' })
            end
        end
    end
end

local function syncBills()
    local sync = Api.post('bills_sync', {})
    if not sync or not sync.ok then return end
    if sync.provider == 'sky' then return skySync(sync) end
    local tbl = tostring(sync.table or 'billing'):gsub('[^%w_]', '')

    for _, b in ipairs(sync.pending or {}) do
        local id = mysql('insert', ('INSERT INTO `%s` (identifier, sender, target_type, target, label, amount) VALUES (?, ?, ?, ?, ?, ?)'):format(tbl),
            { b.citizen_id, Config.BillSender or 'sams', 'society', b.society, b.label, b.amount })
        if id and id > 0 then
            Api.post('bill_result', { id = b.id, result = 'sent', esx_id = id })
            notifyCitizen(b.citizen_id, Config.Text.billReceived:format(b.label, b.amount))
        else
            Api.post('bill_result', { id = b.id, result = 'failed', error = ('Eintrag in Tabelle %s fehlgeschlagen (oxmysql gestartet? Tabelle vorhanden?)'):format(tbl) })
        end
    end

    for _, b in ipairs(sync.cancel or {}) do
        local affected = mysql('update', ('DELETE FROM `%s` WHERE id = ?'):format(tbl), { b.esx_id })
        if affected ~= nil then
            -- Zeile schon weg: wurde in der Zwischenzeit im Spiel bezahlt
            Api.post('bill_result', { id = b.id, result = affected > 0 and 'cancelled' or 'paid' })
        end
    end

    -- esx_billing löscht die Zeile beim Bezahlen: fehlt sie, ist die Rechnung bezahlt
    local open = sync.open or {}
    if #open > 0 then
        local ids, marks = {}, {}
        for _, b in ipairs(open) do
            ids[#ids + 1] = b.esx_id
            marks[#marks + 1] = '?'
        end
        local rows = mysql('query', ('SELECT id FROM `%s` WHERE id IN (%s)'):format(tbl, table.concat(marks, ',')), ids)
        if rows then
            local present = {}
            for _, r in ipairs(rows) do present[tonumber(r.id)] = true end
            for _, b in ipairs(open) do
                if not present[b.esx_id] then
                    Api.post('bill_result', { id = b.id, result = 'paid' })
                end
            end
        end
    end
end

CreateThread(function()
    Wait(5000)
    while true do
        if esxReady() and (GetResourceState('oxmysql') == 'started' or (GameConfig and GameConfig.billing_provider == 'sky')) then
            syncBills()
        end
        Wait(((GameConfig and GameConfig.bill_interval) or 30) * 1000)
    end
end)

-- ------------------------------------------------------------------ /akte --

--- Gemeinsame Prüfung: angemeldet, Ziel in Reichweite, ESX-Daten vorhanden, ggf. passender Job.
local function akteTarget(src, target)
    target = tonumber(target)
    if not target or target == src or not GetPlayerName(target) then
        return nil, Config.Text.nobodyNear
    end
    local s = SamsSession(src)
    if not s then return nil, Config.Text.dutyLogin end
    local dist = (GameConfig and GameConfig.akte_distance or 3.0) + 1.0
    if #(GetEntityCoords(GetPlayerPed(src)) - GetEntityCoords(GetPlayerPed(target))) > dist then
        return nil, Config.Text.nobodyNear
    end
    if not esxReady() then return nil, Config.Text.akteNoPlayerData end
    local f = factionCfg(s.user and s.user.faction_id or 1)
    if f and f.akte_job then
        local me = playerData(src)
        local okJob = false
        for _, j in ipairs(f.jobs or {}) do
            if me and me.job == j then okJob = true end
        end
        if not okJob then return nil, Config.Text.akteJob end
    end
    local pd = playerData(target)
    if not pd or pd.citizen_id == '' then return nil, Config.Text.akteNoPlayerData end
    return { session = s, player = pd }
end

SamsAkteTarget = function(src, target) return akteTarget(src, target) end -- für sky.lua

lib.callback.register('sams:akte:lookup', function(src, target)
    local t, err = akteTarget(src, target)
    if not t then return { error = err } end
    local p = t.player
    local data = Api.post('record_lookup', { token = t.session.token, citizen_id = p.citizen_id, first_name = p.first_name, last_name = p.last_name, dob = p.dob, sex = p.sex })
    if not data then return { error = Config.Text.offline } end
    if not data.ok then return { error = data.error or Config.Text.offline } end
    return data
end)

lib.callback.register('sams:akte:open', function(src, target, create)
    local t, err = akteTarget(src, target)
    if not t then return { error = err } end
    local p = t.player
    local data = Api.post('record_open', { token = t.session.token, citizen_id = p.citizen_id, first_name = p.first_name, last_name = p.last_name,
        dob = p.dob, sex = p.sex, create = create == true })
    if not data then return { error = Config.Text.offline } end
    if not data.ok then return { error = data.error or Config.Text.offline } end
    return { loggedIn = true, user = t.session.user, url = data.url, origin = data.origin }
end)
