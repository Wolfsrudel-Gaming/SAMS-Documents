--[[
    Verbindung zum SAMS-Websystem (api.php).
    Adresse und Schlüssel kommen aus der server.cfg (set sams_url / set sams_api_key).
]]

Api = {}

local baseUrl = (GetConvar('sams_url', ''):gsub('/+$', ''))
local apiKey = GetConvar('sams_api_key', '')

local function urlencode(s)
    return (tostring(s):gsub('[^%w%-_%.~]', function(c) return string.format('%%%02X', string.byte(c)) end))
end

function Api.configured()
    return baseUrl ~= '' and apiKey ~= ''
end

--- Anfrage an die API. Blockiert die aufrufende Coroutine bis zur Antwort.
---@return table|nil data, integer status
local function request(method, action, payload)
    if not Api.configured() then
        return nil, 0
    end
    local url = ('%s/api.php?action=%s'):format(baseUrl, action)
    local body = ''
    if method == 'GET' and payload then
        for k, v in pairs(payload) do
            url = url .. '&' .. urlencode(k) .. '=' .. urlencode(v)
        end
    elseif payload then
        body = json.encode(payload)
    end

    local p = promise.new()
    PerformHttpRequest(url, function(status, resBody)
        local ok, data = pcall(json.decode, resBody or '')
        p:resolve({ status = status, data = ok and type(data) == 'table' and data or nil })
    end, method, body, {
        ['Content-Type'] = 'application/json',
        ['Accept'] = 'application/json',
        ['X-API-Key'] = apiKey,
    })
    local res = Citizen.Await(p)
    if res.status == 0 then
        print(('^3[sams_tablet] Websystem nicht erreichbar (%s)^0'):format(action))
    elseif res.status == 401 and action ~= 'tablet_session' and action ~= 'tablet_launch' then
        print('^1[sams_tablet] API-Schlüssel abgelehnt – sams_api_key in der server.cfg prüfen.^0')
    end
    return res.data, res.status
end

function Api.get(action, params)
    return request('GET', action, params)
end

function Api.post(action, payload)
    return request('POST', action, payload)
end

CreateThread(function()
    if not Api.configured() then
        print('^1[sams_tablet] Bitte in der server.cfg setzen: set sams_url "https://…" und set sams_api_key "…"^0')
    end
end)
