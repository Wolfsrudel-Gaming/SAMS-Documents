--[[ Dienstzeit: Stempeln per Befehl, Rückmeldung bei Statuswechsel ]]

local function formatDuration(seconds)
    seconds = tonumber(seconds) or 0
    return ('%d:%02d h'):format(math.floor(seconds / 3600), math.floor(seconds % 3600 / 60))
end

local function toggleDuty()
    local res = lib.callback.await('sams:duty:toggle', false)
    if not res then return end
    if res.error then
        return Notify(res.error, 'error')
    end
    if res.onDuty then
        Notify(Config.Text.dutyOn, 'success')
    else
        Notify(Config.Text.dutyOff:format(formatDuration(res.seconds)), 'inform')
    end
end

if Config.DutyCommand then
    RegisterCommand(Config.DutyCommand, toggleDuty, false)
    TriggerEvent('chat:addSuggestion', '/' .. Config.DutyCommand, 'SAMS: Dienst beginnen oder beenden')
end

exports('toggleDuty', toggleDuty)

-- Statuswechsel von anderer Stelle (z. B. über das Websystem im Tablet oder eine andere Resource)
RegisterNetEvent('sams:duty', function(onDuty)
    if Session.user then
        Session.user.on_duty = onDuty
    end
    TriggerEvent('sams:dutyChangedClient', onDuty)
end)
