--[[
    SAMS Tablet – Andock-Schnittstelle für eigene Resourcen auf diesem Server.
    Andere Skripte brauchen keinen eigenen Schlüssel: sams_tablet spricht mit seinem Server-Schlüssel mit dem Websystem.
    Alle Exports nur serverseitig und aus einem Thread (CreateThread) aufrufen – sie warten auf die Antwort.

        local res = exports.sams_tablet:Call('person', { citizen_id = 'ABC123' })
        if res and res.ok then print(res.wanted, res.license_driver) end

    Ereignisse aus dem Websystem (Dokument ausgestellt, Dienstbeginn, Fahndung …) kommen als Server-Events:

        AddEventHandler('sams:event', function(name, data, id) … end)          -- alle
        AddEventHandler('sams:event:document.issued', function(data, id) … end) -- eines

    Ausführlich mit allen Aufrufen und Feldern: docs/API.md im Websystem.
]]

local relayLast = nil ---@type integer|nil

--- Allgemeiner Aufruf. src (optional): Spieler, dessen Tablet-Anmeldung als Urheber eingetragen wird.
---@return table|nil
local function call(name, params, src)
    if type(name) ~= 'string' or name == '' then return { ok = false, error = 'Aufruf fehlt' } end
    local body = { call = name, params = type(params) == 'table' and params or {} }
    if src then
        local s = SamsSession(tonumber(src))
        if not s then return { ok = false, error = 'Spieler ist nicht am Tablet angemeldet.' } end
        body.token = s.token
    end
    local data = Api.post('ext', body)
    return data or { ok = false, error = 'Websystem nicht erreichbar' }
end
SamsCall = call

exports('Call', call)

--- Angemeldetes SAMS-Konto eines Spielers: { id, name, rank, badge, faction, faction_id, faction_kind, on_duty, … } oder nil.
exports('GetUser', function(src)
    local s = SamsSession(tonumber(src))
    return s and s.user or nil
end)

--- Wer ist im Dienst? faction: ID, Code oder Kürzel (nil = alle Fraktionen).
exports('GetOnDuty', function(faction)
    return call('duty', { faction = faction })
end)

--- Echtheitsprüfung über den Prüfcode unten auf dem Dokument.
exports('VerifyDocument', function(code)
    return call('verify', { code = code })
end)

--- Dokument-Item in einem Inventarplatz: Metadaten und Stand im Websystem (Ansichts-URL, storniert, Unterschriften).
exports('GetDocument', function(src, slot)
    local item = exports.ox_inventory:GetSlot(tonumber(src), tonumber(slot))
    if not item or item.name ~= Config.DocumentItem or type(item.metadata) ~= 'table' or not item.metadata.sams_token then
        return { ok = false, error = 'Kein Dokument in diesem Platz.' }
    end
    local data = Api.post('document_item', { token = item.metadata.sams_token, variant = item.metadata.sams_variant or '' }) or { ok = false, error = 'Websystem nicht erreichbar' }
    data.metadata = item.metadata
    return data
end)

--- Personen- und Fahndungsabfrage (Kurzformen).
exports('PersonCheck', function(citizenId) return call('person', { citizen_id = citizenId }) end)
exports('WantedCheck', function(query)
    query = type(query) == 'table' and query or { plate = query }
    return call('wanted', { plate = query.plate, citizen_id = query.citizen_id })
end)

--- Akteneintrag: { faction, citizen_id, title, content, type?, create?, first_name?, last_name?, dob?, sex? }; src = Urheber (optional).
exports('AddRecordEntry', function(data, src) return call('entry_create', data, src) end)

--- Benachrichtigung an die Mitglieder einer Fraktion: opts = { body?, permission?, on_duty?, link? }.
exports('SendAlert', function(faction, title, body, opts)
    local p = type(opts) == 'table' and opts or {}
    p.faction, p.title, p.body = faction, title, body or p.body
    return call('alert', p)
end)

--- Behördenanfrage: { from, to, subject, body, citizen_id?, person?, reference?, urgent? }; src = Urheber (optional).
exports('CreateRequest', function(data, src) return call('request_create', data, src) end)

--- Fahndung: { faction, kind = 'person'|'vehicle', citizen_id?, name?, plate?, vehicle?, reason, danger 0–3, expires? }.
exports('CreateWanted', function(data, src) return call('wanted_create', data, src) end)
exports('CloseWanted', function(id, note, src) return call('wanted_close', { id = id, note = note }, src) end)

--- Dienst für ein Konto stempeln: who = { user_id | username | discord_id } oder Spieler-ID (dann eigenes Konto).
exports('SetDuty', function(who, on)
    if type(who) == 'table' then
        local p = { on = on == true }
        for k, v in pairs(who) do p[k] = v end
        return call('duty_set', p)
    end
    return call('duty_set', { on = on == true }, who)
end)

--- Gesetzbuch: code (ID oder Name, nil = das dieses Servers), q = Suchtext.
exports('GetLaws', function(code, q) return call('laws', { code = code, q = q }) end)

-- --------------------------------------------------------- Ereignisse --

local function relay()
    if relayLast == nil then
        local data = call('events', { since = -1 })
        if data and data.ok then relayLast = tonumber(data.last_id) or 0 end
        return
    end
    local data = call('events', { since = relayLast, limit = 200 })
    if not data or not data.ok then return end
    for _, ev in ipairs(data.events or {}) do
        TriggerEvent('sams:event', ev.event, ev.data, ev.id)
        TriggerEvent('sams:event:' .. ev.event, ev.data, ev.id)
    end
    relayLast = tonumber(data.last_id) or relayLast
end

CreateThread(function()
    if not Config.EventRelay then return end
    Wait(10000)
    while true do
        if Api.configured() then pcall(relay) end
        Wait(math.max(3, tonumber(Config.EventRelayInterval) or 10) * 1000)
    end
end)
