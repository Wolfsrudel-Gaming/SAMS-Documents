--[[ Wolfpack Documents Tablet – Fälscherwerkstatt: Rechner mit Drucker an geheimen Orten (ohne Kartenmarkierung). ]]

local points, props = {}, {}
local busy, shown = false, false

local function hideText()
    if shown then lib.hideTextUI(); shown = false end
end

local function clear()
    for _, p in ipairs(points) do p:remove() end
    for _, o in pairs(props) do if DoesEntityExist(o) then DeleteEntity(o) end end
    points, props = {}, {}
    hideText()
end

local function startForge(site)
    if busy then return end
    busy = true
    hideText()
    local res = lib.callback.await('sams:forge:start', false, site.id)
    if not res or res.error then
        Notify(res and res.error or Config.Text.offline, 'error')
        busy = false
        return
    end
    local slice = math.floor(res.duration * 1000 / (res.checks + 1))
    local passed = 0
    for i = 1, res.checks do
        local ok = lib.progressBar({
            duration = slice, label = Config.Text.forgeWorking:format(i, res.checks), canCancel = true, useWhileDead = false,
            disable = { move = true, car = true, combat = true }, anim = Config.Anim.forge,
        })
        if not ok then
            lib.callback.await('sams:forge:abort', false)
            Notify(Config.Text.forgeAborted, 'error')
            busy = false
            return
        end
        if lib.skillCheck({ res.difficulty, res.difficulty }, { 'w', 'a', 's', 'd' }) then passed = passed + 1 end
    end
    lib.progressBar({ duration = slice, label = Config.Text.forgePrepare, canCancel = false, disable = { move = true, car = true, combat = true }, anim = Config.Anim.forge })
    local c = GetEntityCoords(PlayerPedId())
    local street = GetStreetNameFromHashKey(GetStreetNameAtCoord(c.x, c.y, c.z))
    local r = lib.callback.await('sams:forge:finish', false, site.id, passed, street)
    busy = false
    if not r or r.error then
        Notify(r and r.error or Config.Text.offline, 'error')
        return
    end
    SetNuiFocus(true, true)
    SendNUIMessage({ action = 'openForge', url = r.url, origin = r.origin })
end

local function closeForge()
    SetNuiFocus(false, false)
    SendNUIMessage({ action = 'closeForge' })
end

RegisterNUICallback('forgeDone', function(_, cb)
    cb({})
    closeForge()
    lib.progressBar({ duration = Config.PrintDuration, label = Config.Text.forgePrinting, canCancel = false, disable = { move = true, combat = true }, anim = Config.Anim.forge })
    local r = lib.callback.await('sams:forge:collect', false)
    if r and r.ok then Notify(Config.Text.forgeDone:format(r.label), 'success') else Notify(r and r.error or Config.Text.offline, 'error') end
end)

RegisterNUICallback('forgeCancel', function(_, cb)
    cb({})
    closeForge()
end)

local function setup(state)
    clear()
    if type(state) ~= 'table' or not state.enabled then return end
    for _, s in ipairs(state.sites or {}) do
        local coords = vector3(s.x, s.y, s.z)
        local point = lib.points.new({ coords = coords, distance = 30 })
        function point:onEnter()
            if s.prop and s.prop ~= '' and not props[s.id] then
                local model = joaat(s.prop)
                if IsModelInCdimage(model) then
                    lib.requestModel(model)
                    local obj = CreateObject(model, s.x, s.y, s.z, false, false, false)
                    SetEntityHeading(obj, s.heading or 0.0)
                    FreezeEntityPosition(obj, true)
                    SetModelAsNoLongerNeeded(model)
                    props[s.id] = obj
                end
            end
        end
        function point:onExit()
            if props[s.id] and DoesEntityExist(props[s.id]) then DeleteEntity(props[s.id]) end
            props[s.id] = nil
            hideText()
        end
        function point:nearby()
            if self.currentDistance <= Config.InteractDistance + 0.4 and not busy then
                if not shown then lib.showTextUI(Config.Text.forgeUse); shown = true end
                if IsControlJustReleased(0, 38) then startForge(s) end
            elseif shown then
                hideText()
            end
        end
        points[#points + 1] = point
    end
end

AddStateBagChangeHandler('samsForgery', 'global', function(_, _, value) setup(value) end)
CreateThread(function()
    Wait(3000)
    setup(GlobalState.samsForgery)
end)

AddEventHandler('onResourceStop', function(res)
    if res == GetCurrentResourceName() then clear() end
end)
