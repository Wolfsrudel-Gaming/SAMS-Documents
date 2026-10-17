--[[ Dokument-Item: ansehen (Klemmbrett), anderen Spielern zeigen ]]

local viewing = nil ---@type table|nil

local function closeViewer()
    if not viewing then return end
    viewing = nil
    SetNuiFocus(false, false)
    SendNUIMessage({ action = 'closeDocument' })
    StopPropAnim()
end

local function openViewer(data, own)
    viewing = data
    viewing.own = own
    StartPropAnim(Config.Anim.clipboard)
    SetNuiFocus(true, true)
    SendNUIMessage({
        action = 'viewDocument', url = data.url, label = data.label, from = data.from,
        canShow = own, revoked = data.revoked,
    })
end

-- ox_inventory: Item "sams_document" benutzen (client = { export = 'sams_tablet.useDocument' })
exports('useDocument', function(_, slot)
    local meta = slot and slot.metadata or {}
    if not meta.sams_token then
        return Notify('Dieses Blatt ist leer.', 'error')
    end
    local info = lib.callback.await('sams:document:url', false, meta.sams_token, meta.sams_variant)
    if not info then
        return Notify(Config.Text.offline, 'error')
    end
    openViewer({ url = info.url, label = meta.label or info.number, token = meta.sams_token, variant = meta.sams_variant, revoked = info.revoked }, true)
end)

-- Jemand zeigt mir ein Dokument
RegisterNetEvent('sams:document:view', function(data)
    if viewing then closeViewer() end
    openViewer(data, false)
end)

RegisterNUICallback('closeDocument', function(_, cb)
    closeViewer()
    cb(true)
end)

RegisterNUICallback('showDocument', function(_, cb)
    cb(true)
    if not viewing or not viewing.own then return end
    local player = lib.getClosestPlayer(GetEntityCoords(PlayerPedId()), Config.ShowDistance, false)
    if not player then
        return Notify(Config.Text.nobodyNear, 'error')
    end
    TriggerServerEvent('sams:document:show', GetPlayerServerId(player), viewing.token, viewing.variant, viewing.label)
    Notify(Config.Text.showed, 'success')
end)
