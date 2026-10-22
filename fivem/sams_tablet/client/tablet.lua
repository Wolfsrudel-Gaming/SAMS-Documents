--[[ Tablet: öffnet das Websystem (Wolfpack Documents) im Spiel (Login-Maske, danach Websystem im Tablet-Rahmen) ]]

local isOpen = false

--- Tablet öffnen; res (optional) enthält eine bereits geholte Einmal-URL, z. B. direkt auf eine Akte.
local function openTablet(res)
    if isOpen then return end
    res = type(res) == 'table' and res or lib.callback.await('sams:tablet:open', false)
    if not res then return end
    if res.error then
        return Notify(res.error, 'error')
    end
    isOpen = true
    Session.loggedIn = res.loggedIn
    Session.user = res.user
    StartPropAnim(Config.Anim.tablet)
    SetNuiFocus(true, true)
    SendNUIMessage({ action = 'openTablet', loggedIn = res.loggedIn, url = res.url, origin = res.origin, user = res.user })
end

local function closeTablet()
    if not isOpen then return end
    isOpen = false
    SetNuiFocus(false, false)
    SendNUIMessage({ action = 'closeTablet' })
    StopPropAnim()
end

-- ox_inventory: Item "sams_tablet" benutzen (client = { export = 'sams_tablet.useTablet' })
exports('useTablet', function()
    openTablet()
end)

SamsCommand('tablet', Config.Command, function()
    if Config.CommandRequiresItem and Config.TabletItem then
        local count = exports.ox_inventory:Search('count', Config.TabletItem)
        if not count or count < 1 then
            return Notify(Config.Text.noTablet, 'error')
        end
    end
    openTablet()
end, 'SAMS: Tablet öffnen')

OpenTablet = openTablet
exports('openTablet', function() openTablet() end)
exports('closeTablet', closeTablet)

-- ------------------------------------------------------- NUI-Rückmeldungen --

RegisterNUICallback('closeTablet', function(_, cb)
    closeTablet()
    cb(true)
end)

RegisterNUICallback('login', function(form, cb)
    local res = lib.callback.await('sams:tablet:login', false, form)
    if res and res.status == 'ok' then
        Session.loggedIn = true
        Session.user = res.user
    end
    cb(res or { status = 'error', message = Config.Text.offline })
end)

RegisterNUICallback('logout', function(_, cb)
    lib.callback.await('sams:tablet:logout', false)
    Session.loggedIn = false
    Session.user = nil
    cb(true)
end)

-- Abmeldung erfolgte im Websystem selbst
RegisterNUICallback('webLogout', function(_, cb)
    TriggerServerEvent('sams:tablet:webLogout')
    Session.loggedIn = false
    Session.user = nil
    cb(true)
end)

-- Websystem fragt die aktuelle Position an (z. B. für einen neuen Drucker)
RegisterNUICallback('getCoords', function(_, cb)
    local ped = PlayerPedId()
    local c = GetEntityCoords(ped)
    local street = GetStreetNameFromHashKey(GetStreetNameAtCoord(c.x, c.y, c.z))
    local found, groundZ = GetGroundZFor_3dCoord(c.x, c.y, c.z, false)
    cb({ x = c.x, y = c.y, z = c.z, h = GetEntityHeading(ped), ground = found and groundZ or nil, street = street })
end)

-- Websystem meldet einen neuen Druckauftrag
RegisterNUICallback('printQueued', function(data, cb)
    if type(data) == 'table' and data.x and Config.WaypointOnPrint then
        SetNewWaypoint(data.x + 0.0, data.y + 0.0)
    end
    LastPrintNotice = GetGameTimer()
    Notify(('Druckauftrag an „%s“ gesendet. Hol das Dokument dort ab.'):format(data and data.printer or 'Drucker'), 'success')
    cb(true)
end)
