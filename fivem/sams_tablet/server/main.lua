--[[
    SAMS Tablet – Server
    Tablet-Anmeldungen, Drucker, Druckaufträge abholen, Dokumente zeigen.
]]

local sessions = {} ---@type table<number, { token: string, user: table }>
local onDuty = {} ---@type table<number, boolean>
local printers = {} ---@type table<number, table>
local shownTo = {} ---@type table<number, table<string, number>> gezeigte Dokumente (Unterschrift erlaubt bis)

-- ---------------------------------------------------------------- Hilfen --

local function license(src)
    return GetPlayerIdentifierByType(src, 'license') or ('player:' .. src)
end

local function kvpKey(src)
    return 'sams_token:' .. license(src)
end

--- Gespeicherte Tablet-Anmeldung des Spielers laden (bleibt über Neustarts erhalten).
local function getSession(src)
    if sessions[src] then
        return sessions[src]
    end
    local token = GetResourceKvpString(kvpKey(src))
    if not token or token == '' then
        return nil
    end
    local data, status = Api.post('tablet_session', { token = token })
    if data and data.ok then
        sessions[src] = { token = token, user = data.user }
        onDuty[src] = data.user.on_duty == true
        return sessions[src]
    end
    if status == 401 then
        DeleteResourceKvp(kvpKey(src))
    end
    return nil
end

--- Für die übrigen Server-Dateien (z. B. esx.lua): angemeldete Tablet-Sitzung des Spielers
SamsSession = getSession

--- Nächste/r angemeldete/r Rettungsdienst-Mitarbeiter/in im Dienst (für Sky Ambulance). Gibt Sitzung oder nil.
function SamsNearestMedic(coords, maxDist)
    local best, bestDist = nil, maxDist
    for src, s in pairs(sessions) do
        if onDuty[src] and s.user and s.user.faction_kind == 'medical' then
            local d = #(GetEntityCoords(GetPlayerPed(src)) - coords)
            if d <= bestDist then best, bestDist = s, d end
        end
    end
    return best
end

local function clearSession(src)
    sessions[src] = nil
    onDuty[src] = nil
    DeleteResourceKvp(kvpKey(src))
    TriggerClientEvent('sams:session', src, false)
end

local function findPrinter(id)
    for _, p in ipairs(printers) do
        if p.id == id then
            return p
        end
    end
end

local function nearPrinter(src, printer)
    local coords = GetEntityCoords(GetPlayerPed(src))
    return #(coords - vec3(printer.x, printer.y, printer.z)) <= Config.InteractDistance + 3.0
end

-- ------------------------------------------------------------------ Tablet --

lib.callback.register('sams:tablet:status', function(src)
    local s = getSession(src)
    return s and { loggedIn = true, user = s.user } or { loggedIn = false }
end)

lib.callback.register('sams:tablet:open', function(src)
    if not Api.configured() then
        return { error = Config.Text.offline }
    end
    local s = getSession(src)
    if not s then
        return { loggedIn = false }
    end
    local data, status = Api.post('tablet_launch', { token = s.token })
    if not data or not data.ok then
        if status == 401 then
            clearSession(src)
            return { loggedIn = false }
        end
        return { error = Config.Text.offline }
    end
    return { loggedIn = true, user = s.user, url = data.url, origin = data.origin }
end)

lib.callback.register('sams:tablet:login', function(src, form)
    if type(form) ~= 'table' or type(form.username) ~= 'string' or type(form.password) ~= 'string' then
        return { status = 'invalid', message = 'Ungültige Eingabe.' }
    end
    local data = Api.post('tablet_login', {
        username = form.username:sub(1, 64),
        password = form.password:sub(1, 256),
        code = type(form.code) == 'string' and form.code:sub(1, 16) or '',
        license = license(src),
        player = GetPlayerName(src),
    })
    if not data or not data.status then
        return { status = 'error', message = Config.Text.offline }
    end
    if data.status ~= 'ok' then
        return { status = data.status, message = data.message }
    end
    sessions[src] = { token = data.token, user = data.user }
    SetResourceKvp(kvpKey(src), data.token)
    local launch = Api.post('tablet_launch', { token = data.token }) or {}
    TriggerClientEvent('sams:session', src, true, data.user)
    return { status = 'ok', user = data.user, url = launch.url, origin = launch.origin }
end)

lib.callback.register('sams:tablet:logout', function(src)
    local s = sessions[src] or getSession(src)
    if s then
        -- Abmelden beendet auch den Dienst (danach ist kein Ausstempeln mehr möglich)
        if onDuty[src] then
            Api.post('duty_set', { token = s.token, on = false })
            TriggerEvent('sams:dutyChanged', src, false)
        end
        Api.post('tablet_logout', { token = s.token })
    end
    clearSession(src)
    return true
end)

-- Abmeldung erfolgte im Websystem (Token ist dort bereits gesperrt)
RegisterNetEvent('sams:tablet:webLogout', function()
    clearSession(source)
end)

-- ----------------------------------------------------------------- Dienst --

local function setDuty(src, on, silent)
    local s = getSession(src)
    if not s then
        return { error = Config.Text.dutyLogin }
    end
    local data = Api.post('duty_set', { token = s.token, on = on })
    if not data or not data.ok then
        return { error = data and data.error or Config.Text.offline }
    end
    onDuty[src] = data.on_duty
    TriggerEvent('sams:dutyChanged', src, data.on_duty)
    if not silent then
        TriggerClientEvent('sams:duty', src, data.on_duty)
    end
    return { ok = true, onDuty = data.on_duty, seconds = data.seconds }
end

lib.callback.register('sams:duty:toggle', function(src)
    local current = onDuty[src]
    if current == nil and getSession(src) then
        local st = Api.post('duty_status', { token = sessions[src].token })
        current = st and st.on_duty or false
    end
    return setDuty(src, not current)
end)

--- Für andere Resourcen: exports.sams_tablet:IsOnDuty(source)
exports('IsOnDuty', function(src)
    return onDuty[src] == true
end)
exports('SetOnDuty', function(src, on)
    return setDuty(src, on == true, false)
end)

AddEventHandler('playerDropped', function()
    local src = source
    if Config.DutyEndOnDisconnect and onDuty[src] and sessions[src] then
        Api.post('duty_set', { token = sessions[src].token, on = false })
        TriggerEvent('sams:dutyChanged', src, false)
    end
    sessions[src] = nil
    onDuty[src] = nil
    shownTo[src] = nil
end)

-- ---------------------------------------------------------------- Drucker --

local function syncPrinters()
    local data = Api.get('printers')
    if data and data.printers then
        printers = data.printers
        TriggerClientEvent('sams:printers', -1, printers)
    end
end

CreateThread(function()
    Wait(1500)
    while true do
        syncPrinters()
        Wait(Config.PrinterSyncInterval * 1000)
    end
end)

lib.callback.register('sams:printers:get', function()
    return printers
end)

lib.callback.register('sams:printer:jobs', function(src, printerId)
    local printer = findPrinter(printerId)
    if not printer then
        return { error = 'Dieser Drucker ist nicht mehr aktiv.' }
    end
    if not nearPrinter(src, printer) then
        return { error = 'Du bist zu weit vom Drucker entfernt.' }
    end
    local s = getSession(src)
    local data = Api.post('printer_jobs', { printer_id = printerId, token = s and s.token or nil })
    if not data or not data.ok then
        return { error = Config.Text.offline }
    end
    return { jobs = data.jobs, loggedIn = data.logged_in, pickup = data.pickup }
end)

lib.callback.register('sams:printer:pickup', function(src, printerId, jobId, copies)
    local printer = findPrinter(printerId)
    if not printer or not nearPrinter(src, printer) then
        return { error = 'Du bist zu weit vom Drucker entfernt.' }
    end
    copies = math.max(1, math.min(5, tonumber(copies) or 1))
    if not exports.ox_inventory:CanCarryItem(src, Config.DocumentItem, copies) then
        return { error = Config.Text.noSpace }
    end
    local s = getSession(src)
    local data = Api.post('job_pickup', {
        job_id = jobId, printer_id = printerId, token = s and s.token or nil,
        license = license(src), player = GetPlayerName(src),
    })
    if not data or not data.ok then
        return { error = data and data.error or Config.Text.offline }
    end
    local added = exports.ox_inventory:AddItem(src, Config.DocumentItem, data.count or 1, data.metadata)
    if not added then
        print(('^1[sams_tablet] Item konnte nicht ausgegeben werden (Spieler %s, Auftrag %s)^0'):format(src, jobId))
        return { error = Config.Text.noSpace }
    end
    return { ok = true, label = data.metadata.label, count = data.count or 1 }
end)

-- Benachrichtigung, sobald ein eigener Druckauftrag bereitliegt (auch wenn er am PC erstellt wurde)
CreateThread(function()
    Wait(3000)
    local lastId = -1
    while lastId < 0 do
        local init = Api.post('jobs_since', { since_id = -1 })
        if init and init.ok then lastId = init.last_id else Wait(30000) end
    end
    while true do
        Wait(Config.JobPollInterval * 1000)
        if next(sessions) then
            local data = Api.post('jobs_since', { since_id = lastId })
            if data and data.ok then
                lastId = data.last_id or lastId
                for _, job in ipairs(data.jobs or {}) do
                    for src, s in pairs(sessions) do
                        if s.user and s.user.id == job.user_id then
                            TriggerClientEvent('sams:printer:jobReady', src, job)
                        end
                    end
                end
            end
        end
    end
end)

-- -------------------------------------------------------------- Dokumente --

lib.callback.register('sams:document:url', function(src, token, variant)
    if type(token) ~= 'string' or #token < 32 then
        return nil
    end
    local data = Api.post('document_item', { token = token, variant = variant or '' })
    return data and data.ok and data or nil
end)

--- Dokument einem Spieler in der Nähe zeigen. Geprüft wird, ob der Zeigende das Dokument wirklich besitzt.
RegisterNetEvent('sams:document:show', function(target, token, variant, label)
    local src = source
    target = tonumber(target)
    if not target or target == src or not GetPlayerName(target) or type(token) ~= 'string' then
        return
    end
    local a, b = GetEntityCoords(GetPlayerPed(src)), GetEntityCoords(GetPlayerPed(target))
    if #(a - b) > Config.ShowDistance + 1.0 then
        return
    end
    local count = exports.ox_inventory:Search(src, 'count', Config.DocumentItem, { sams_token = token })
    if not count or count < 1 then
        return
    end
    local data = Api.post('document_item', { token = token, variant = variant or '' })
    if data and data.ok then
        -- Wem ein Dokument gezeigt wird, darf es 10 Minuten lang unterschreiben (z. B. Patient/in unterschreibt den Revers)
        shownTo[target] = shownTo[target] or {}
        shownTo[target][token] = os.time() + 600
        TriggerClientEvent('sams:document:view', target, {
            url = data.url, label = type(label) == 'string' and label:sub(1, 120) or data.number,
            from = GetPlayerName(src), shown = true, revoked = data.revoked, token = token, variant = variant, signing = data.signing,
        })
    end
end)

-- ------------------------------------------------- Unterschrift im Spiel --

local function ownsDocument(src, token)
    local count = exports.ox_inventory:Search(src, 'count', Config.DocumentItem, { sams_token = token })
    return count and count > 0
end

local function signerOf(src)
    local pd = SamsPlayerData and SamsPlayerData(src)
    if pd and pd.first_name ~= '' then
        return (pd.first_name .. ' ' .. (pd.last_name or '')):gsub('%s+$', ''), pd.citizen_id or ''
    end
    return GetPlayerName(src), ''
end

--- Unterschriftsfeld auf dem Blatt unterschreiben: Besitzer/in des Blatts oder wem es gerade gezeigt wird.
lib.callback.register('sams:document:sign', function(src, token, field, png)
    if type(token) ~= 'string' or #token < 32 or type(field) ~= 'string' or type(png) ~= 'string' or #png > 150000 then
        return { error = Config.Text.signInvalid }
    end
    local granted = shownTo[src] and shownTo[src][token] and shownTo[src][token] > os.time()
    if not granted and not ownsDocument(src, token) then
        return { error = Config.Text.signNoAccess }
    end
    local name, cid = signerOf(src)
    local data = Api.post('doc_sign', { token = token, field = field:sub(1, 64), signature = png, signer_name = name, signer_citizen_id = cid,
        player = GetPlayerName(src), license = license(src) })
    if not data then return { error = Config.Text.offline } end
    if not data.ok then return { error = data.error } end
    return { ok = true, signing = data.signing }
end)

--- Dokument abschließen (nur wer das Blatt besitzt): keine weiteren Unterschriften möglich.
lib.callback.register('sams:document:finalize', function(src, token)
    if type(token) ~= 'string' or not ownsDocument(src, token) then
        return { error = Config.Text.signNoAccess }
    end
    local name = signerOf(src)
    local data = Api.post('doc_finalize', { token = token, signer_name = name, player = GetPlayerName(src), license = license(src) })
    if not data then return { error = Config.Text.offline } end
    if not data.ok then return { error = data.error } end
    return { ok = true, signing = data.signing }
end)

-- ------------------------------------------------------------- Fahndung --

--- Kennzeichen oder Bürger-ID abfragen (nur angemeldete Berechtigte, z. B. Polizei im Dienst).
lib.callback.register('sams:wanted:check', function(src, query)
    if type(query) ~= 'table' then return { error = 'Ungültige Abfrage.' } end
    local s = getSession(src)
    if not s then return { error = Config.Text.dutyLogin } end
    if Config.WantedRequiresDuty and not onDuty[src] then
        return { error = Config.Text.wantedDuty }
    end
    local data, status = Api.post('wanted_check', {
        token = s.token,
        plate = type(query.plate) == 'string' and query.plate:sub(1, 16) or nil,
        citizen_id = type(query.citizen_id) == 'string' and query.citizen_id:sub(1, 64) or nil,
    })
    if not data then return { error = Config.Text.offline } end
    if not data.ok then return { error = data.error or Config.Text.offline } end
    return data
end)

--- Für andere Resourcen (z. B. eigenes MDT): offene Fahndungen zu Kennzeichen/Bürger-ID.
exports('CheckWanted', function(plate, citizenId)
    return Api.post('wanted_check', { plate = plate, citizen_id = citizenId })
end)

exports('CheckPerson', function(citizenId)
    return Api.post('person_check', { citizen_id = citizenId })
end)
