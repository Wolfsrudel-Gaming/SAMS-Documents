--[[ Drucker in der Spielwelt: Objekte, Interaktion, Abholung der Druckaufträge ]]

local spawned = {} ---@type table<number, { obj?: integer, zone?: any, point?: table, blip?: integer }>
local printers = {}
LastPrintNotice = 0

local function useTarget()
    return Config.UseTarget and GetResourceState('ox_target') == 'started'
end

local function clearPrinters()
    for _, s in pairs(spawned) do
        if s.zone then exports.ox_target:removeZone(s.zone) end
        if s.obj and DoesEntityExist(s.obj) then
            if useTarget() then exports.ox_target:removeLocalEntity(s.obj) end
            DeleteEntity(s.obj)
        end
        if s.point then s.point:remove() end
        if s.blip then RemoveBlip(s.blip) end
    end
    spawned = {}
    lib.hideTextUI()
end

local function pickup(printer, job)
    local ok = lib.progressBar({
        duration = Config.PrintDuration,
        label = Config.Text.printing,
        canCancel = true,
        disable = { move = true, car = true, combat = true },
        anim = Config.Anim.printer,
    })
    ClearPedTasks(PlayerPedId())
    if not ok then return end
    local res = lib.callback.await('sams:printer:pickup', false, printer.id, job.id, job.copies)
    if res and res.ok then
        PlaySoundFrontend(-1, 'PICK_UP', 'HUD_FRONTEND_DEFAULT_SOUNDSET', true)
        Notify(Config.Text.pickedUp:format((res.count > 1 and (res.count .. '× ') or '') .. res.label), 'success')
    else
        Notify(res and res.error or Config.Text.offline, 'error')
    end
end

local function openPrinter(printer)
    local res = lib.callback.await('sams:printer:jobs', false, printer.id)
    if not res or res.error then
        return Notify(res and res.error or Config.Text.offline, 'error')
    end
    if #res.jobs == 0 then
        if not res.loggedIn and res.pickup ~= 'anyone' then
            return Notify(Config.Text.loginHint, 'inform')
        end
        return Notify(Config.Text.noJobs, 'inform')
    end
    local options = {}
    for _, job in ipairs(res.jobs) do
        options[#options + 1] = {
            title = job.label .. (job.copies > 1 and (' (' .. job.copies .. '×)') or ''),
            description = ('%s · %s · %s'):format(job.number, job.requester ~= '' and job.requester or 'unbekannt', job.requested_at),
            icon = job.revoked and 'ban' or 'file-lines',
            onSelect = function() pickup(printer, job) end,
        }
    end
    lib.registerContext({ id = 'sams_printer', title = printer.name, options = options })
    lib.showContext('sams_printer')
end

local function addBlip(p)
    local blip = AddBlipForCoord(p.x, p.y, p.z)
    SetBlipSprite(blip, Config.PrinterBlip.sprite)
    SetBlipColour(blip, Config.PrinterBlip.color)
    SetBlipScale(blip, Config.PrinterBlip.scale)
    SetBlipAsShortRange(blip, true)
    BeginTextCommandSetBlipName('STRING')
    AddTextComponentSubstringPlayerName(Config.PrinterBlip.label .. ': ' .. p.name)
    EndTextCommandSetBlipName(blip)
    return blip
end

local function buildPrinters()
    clearPrinters()
    for _, p in ipairs(printers) do
        local s = {}
        local coords = vec3(p.x, p.y, p.z)
        if p.prop and p.prop ~= '' and IsModelInCdimage(joaat(p.prop)) then
            lib.requestModel(p.prop)
            s.obj = CreateObject(joaat(p.prop), p.x, p.y, p.z, false, false, false)
            SetEntityHeading(s.obj, p.heading)
            FreezeEntityPosition(s.obj, true)
            SetEntityInvincible(s.obj, true)
            SetModelAsNoLongerNeeded(joaat(p.prop))
        end
        if useTarget() then
            local opts = { {
                name = 'sams_printer_' .. p.id, icon = 'fa-solid fa-print', label = Config.Text.printerTarget,
                distance = Config.InteractDistance, onSelect = function() openPrinter(p) end,
            } }
            if s.obj then
                exports.ox_target:addLocalEntity(s.obj, opts)
            else
                s.zone = exports.ox_target:addSphereZone({ coords = coords + vec3(0, 0, 0.5), radius = 0.8, options = opts })
            end
        else
            s.point = lib.points.new({
                coords = coords, distance = Config.InteractDistance + 1.0,
                nearby = function(self)
                    if self.currentDistance <= Config.InteractDistance then
                        if not self.shown then lib.showTextUI(Config.Text.printerHelp); self.shown = true end
                        if IsControlJustReleased(0, 38) then openPrinter(p) end
                    elseif self.shown then
                        lib.hideTextUI(); self.shown = false
                    end
                end,
                onExit = function(self) if self.shown then lib.hideTextUI(); self.shown = false end end,
            })
        end
        if p.blip and Session.loggedIn then
            s.blip = addBlip(p)
        end
        spawned[p.id] = s
    end
end

RegisterNetEvent('sams:printers', function(list)
    printers = list or {}
    buildPrinters()
end)

AddEventHandler('sams:sessionChanged', function()
    buildPrinters()
end)

CreateThread(function()
    Wait(2500)
    printers = lib.callback.await('sams:printers:get', false) or {}
    buildPrinters()
end)

AddEventHandler('onResourceStop', function(res)
    if res == GetCurrentResourceName() then clearPrinters() end
end)

-- Ein eigener Druckauftrag liegt bereit (z. B. am PC erstellt)
RegisterNetEvent('sams:printer:jobReady', function(job)
    if GetGameTimer() - LastPrintNotice < 15000 then return end -- bereits durch das Tablet gemeldet
    Notify(Config.Text.jobReady:format(job.printer), 'inform')
    if Config.WaypointOnPrint then
        SetNewWaypoint(job.x + 0.0, job.y + 0.0)
    end
end)
