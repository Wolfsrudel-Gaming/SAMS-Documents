--[[ Gemeinsame Hilfen: Animation mit Requisite, Benachrichtigungen, Sitzungsstatus ]]

Session = { loggedIn = false, user = nil }

local active = nil ---@type { dict: string, clip: string, obj: integer }|nil

--- Animation mit Requisite starten (z. B. Tablet oder Klemmbrett in der Hand).
function StartPropAnim(cfg)
    StopPropAnim()
    local ped = PlayerPedId()
    lib.requestAnimDict(cfg.dict)
    lib.requestModel(cfg.prop)
    local c = GetEntityCoords(ped)
    local obj = CreateObject(joaat(cfg.prop), c.x, c.y, c.z + 0.2, true, true, false)
    AttachEntityToEntity(obj, ped, GetPedBoneIndex(ped, cfg.bone), cfg.pos.x, cfg.pos.y, cfg.pos.z,
        cfg.rot.x, cfg.rot.y, cfg.rot.z, true, true, false, true, 1, true)
    SetModelAsNoLongerNeeded(joaat(cfg.prop))
    -- 49 = Schleife, nur Oberkörper, Bewegung möglich
    TaskPlayAnim(ped, cfg.dict, cfg.clip, 3.0, 3.0, -1, 49, 0, false, false, false)
    RemoveAnimDict(cfg.dict)
    active = { dict = cfg.dict, clip = cfg.clip, obj = obj }
end

function StopPropAnim()
    if not active then return end
    local ped = PlayerPedId()
    StopAnimTask(ped, active.dict, active.clip, 2.0)
    if DoesEntityExist(active.obj) then
        DetachEntity(active.obj, true, false)
        DeleteEntity(active.obj)
    end
    active = nil
end

function Notify(text, kind)
    lib.notify({ title = 'SAMS', description = text, type = kind or 'inform' })
end

AddEventHandler('onResourceStop', function(res)
    if res == GetCurrentResourceName() then
        StopPropAnim()
        SetNuiFocus(false, false)
    end
end)

RegisterNetEvent('sams:session', function(loggedIn, user)
    Session.loggedIn = loggedIn
    Session.user = user
    TriggerEvent('sams:sessionChanged')
end)

CreateThread(function()
    Wait(2000)
    local s = lib.callback.await('sams:tablet:status', false)
    if s then
        Session.loggedIn = s.loggedIn
        Session.user = s.user
        TriggerEvent('sams:sessionChanged')
    end
end)

--- Befehl registrieren, dessen Name im Websystem eingestellt wird (Einstellungen → Spielserver).
--- Steht in der config.lua nil/false, bleibt der Befehl aus; sonst gilt der Name aus dem Websystem bzw. die config.lua.
function SamsCommand(key, fallback, handler, help, params)
    if not fallback then return end
    CreateThread(function()
        local started = GetGameTimer()
        while not GlobalState.samsConfig and GetGameTimer() - started < 15000 do Wait(250) end
        local cfg = GlobalState.samsConfig
        local name = cfg and cfg.commands and cfg.commands[key]
        if type(name) ~= 'string' or name == '' then name = fallback end
        RegisterCommand(name, handler, false)
        if help then
            TriggerEvent('chat:addSuggestion', '/' .. name, help, params)
        end
    end)
end
