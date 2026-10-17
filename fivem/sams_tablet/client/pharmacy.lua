--[[ Apotheken in der Spielwelt: Rezept-Items gegen Medikamente einlösen ]]

local spawned = {}

local function useTarget()
    return Config.UseTarget and GetResourceState('ox_target') == 'started'
end

local function redeem(index, rx)
    local ok = lib.progressBar({
        duration = Config.PharmacyDuration, label = Config.Text.redeeming, canCancel = true,
        disable = { move = true, car = true, combat = true },
    })
    if not ok then return end
    local res = lib.callback.await('sams:pharmacy:redeem', false, index, rx.token)
    if res and res.ok then
        PlaySoundFrontend(-1, 'PICK_UP', 'HUD_FRONTEND_DEFAULT_SOUNDSET', true)
        Notify(Config.Text.redeemed:format(res.given), 'success')
        for _, n in ipairs(res.notes or {}) do Notify(n, 'inform') end
    else
        Notify(res and res.error or Config.Text.offline, 'error')
    end
end

local function openPharmacy(index)
    local res = lib.callback.await('sams:pharmacy:list', false, index)
    if not res or res.error then
        return Notify(res and res.error or Config.Text.offline, 'error')
    end
    if #res.prescriptions == 0 then
        return Notify(Config.Text.noPrescription, 'inform')
    end
    local options = {}
    for _, rx in ipairs(res.prescriptions) do
        local lines, redeemable = {}, false
        for _, l in ipairs(rx.lines or {}) do
            lines[#lines + 1] = ('%s – %s'):format(l.text, l.label)
            if l.status == 'ok' then redeemable = true end
        end
        options[#options + 1] = {
            title = ('Rezept %s (%s)'):format(rx.patient ~= '' and rx.patient or '', rx.issued_at),
            description = rx.problem or table.concat(lines, '\n'),
            icon = 'prescription-bottle-medical',
            disabled = rx.problem ~= nil or not redeemable,
            onSelect = function() redeem(index, rx) end,
        }
    end
    lib.registerContext({ id = 'sams_pharmacy', title = Config.Pharmacies[index].name, options = options })
    lib.showContext('sams_pharmacy')
end

CreateThread(function()
    for i, p in ipairs(Config.Pharmacies or {}) do
        local s = {}
        local coords = vec3(p.coords.x, p.coords.y, p.coords.z)
        if p.ped then
            lib.requestModel(p.ped)
            s.ped = CreatePed(4, joaat(p.ped), p.coords.x, p.coords.y, p.coords.z - 1.0, p.coords.w or 0.0, false, true)
            FreezeEntityPosition(s.ped, true)
            SetEntityInvincible(s.ped, true)
            SetBlockingOfNonTemporaryEvents(s.ped, true)
            SetModelAsNoLongerNeeded(joaat(p.ped))
        end
        if useTarget() then
            local opts = { { name = 'sams_pharmacy_' .. i, icon = 'fa-solid fa-prescription-bottle-medical', label = Config.Text.pharmacyTarget,
                distance = Config.InteractDistance + 0.5, onSelect = function() openPharmacy(i) end } }
            if s.ped then
                exports.ox_target:addLocalEntity(s.ped, opts)
            else
                s.zone = exports.ox_target:addSphereZone({ coords = coords, radius = 1.0, options = opts })
            end
        else
            s.point = lib.points.new({
                coords = coords, distance = Config.InteractDistance + 1.5,
                nearby = function(self)
                    if self.currentDistance <= Config.InteractDistance + 0.5 then
                        if not self.shown then lib.showTextUI(Config.Text.pharmacyHelp); self.shown = true end
                        if IsControlJustReleased(0, 38) then openPharmacy(i) end
                    elseif self.shown then
                        lib.hideTextUI(); self.shown = false
                    end
                end,
                onExit = function(self) if self.shown then lib.hideTextUI(); self.shown = false end end,
            })
        end
        if p.blip then
            s.blip = AddBlipForCoord(coords.x, coords.y, coords.z)
            SetBlipSprite(s.blip, Config.PharmacyBlip.sprite)
            SetBlipColour(s.blip, Config.PharmacyBlip.color)
            SetBlipScale(s.blip, Config.PharmacyBlip.scale)
            SetBlipAsShortRange(s.blip, true)
            BeginTextCommandSetBlipName('STRING')
            AddTextComponentSubstringPlayerName(p.name)
            EndTextCommandSetBlipName(s.blip)
        end
        spawned[i] = s
    end
end)

AddEventHandler('onResourceStop', function(res)
    if res ~= GetCurrentResourceName() then return end
    for _, s in pairs(spawned) do
        if s.ped and DoesEntityExist(s.ped) then DeleteEntity(s.ped) end
        if s.zone then exports.ox_target:removeZone(s.zone) end
        if s.point then s.point:remove() end
        if s.blip then RemoveBlip(s.blip) end
    end
end)
