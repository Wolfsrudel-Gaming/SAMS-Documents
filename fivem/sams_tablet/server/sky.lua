--[[
    Wolfpack Documents Tablet – Anbindung Sky Ambulance (sky_ambulancejob), nur über dessen dokumentierte Schnittstellen.
    Einschalten im Websystem: Einstellungen → Spielserver → „Sky Ambulance anbinden“.

    - Vitalwerte: Der Patient-Client liest exports.sky_ambulancejob:GetLocalInjuryProfile() (vitalSchemaVersion 2)
    - Tod/Wiederbelebung: Client-Ereignis sky_ambulancejob:deathStateChanged, geprüft mit exports.sky_ambulancejob:isDead()
    - Medikamentengabe: in Config.CustomMedications von Sky onFinishServer eintragen:
          onFinishServer = function(self) exports.sams_tablet:SkyMedication(self.source, self.target, self.itemName) end
]]

local function skyOn(key)
    return GameConfig and GameConfig[key or 'sky'] == true and GetResourceState('sky_ambulancejob') == 'started'
end

local function playerFields(pd)
    return { citizen_id = pd.citizen_id, first_name = pd.first_name, last_name = pd.last_name, dob = pd.dob, sex = pd.sex }
end

-- ---------------------------------------------------------- Vitalwerte --

--- Vitalwerte des Spielers vor dir in die Akte übernehmen (aus /akte).
lib.callback.register('sams:sky:vitals', function(src, target)
    if not skyOn() then return { error = Config.Text.skyOff } end
    local t, err = SamsAkteTarget(src, target)
    if not t then return { error = err } end
    local profile = lib.callback.await('sams:sky:profile', tonumber(target))
    if type(profile) ~= 'table' or profile.vitalSchemaVersion ~= 2 or type(profile.vitals) ~= 'table' then
        return { error = Config.Text.skyNoVitals }
    end
    local v, bp, c = profile.vitals, profile.vitals.blood_pressure or {}, profile.conditions or {}
    local payload = playerFields(t.player)
    payload.token = t.session.token
    payload.vitals = { heart_rate = v.heart_rate, systolic = bp.systolic, diastolic = bp.diastolic, spo2 = v.oxygen_level, blood_sugar = v.blood_sugar,
        cardiac_arrest = c.cardiacArrest == true, activity = c.activityMode }
    local data = Api.post('record_vitals', payload)
    if not data then return { error = Config.Text.offline } end
    if not data.ok then return { error = data.error } end
    return data
end)

-- --------------------------------------------- Tod und Wiederbelebung --

local lastDeath = {} ---@type table<number, { dead: boolean, at: number }>

RegisterNetEvent('sams:sky:death', function(dead)
    local src = source
    if not skyOn('sky_death') or type(dead) ~= 'boolean' then return end
    -- Gegenprüfung serverseitig (Sky ist maßgeblich), doppelte Meldungen und Spam verhindern
    local ok, isDead = pcall(function() return exports.sky_ambulancejob:isDead(src) end)
    if not ok or isDead ~= dead then return end
    local last = lastDeath[src]
    if last and (last.dead == dead or os.time() - last.at < 20) then return end
    lastDeath[src] = { dead = dead, at = os.time() }
    local pd = SamsPlayerData and SamsPlayerData(src)
    if not pd or pd.citizen_id == '' then return end
    local medic = SamsNearestMedic(GetEntityCoords(GetPlayerPed(src)), 30.0)
    local payload = playerFields(pd)
    payload.dead = dead
    payload.medic_token = medic and medic.token or nil
    Api.post('sky_death', payload)
end)

AddEventHandler('playerDropped', function()
    lastDeath[source] = nil
end)

-- ------------------------------------------------- Medikamentengabe --

--- Aus Config.CustomMedications von Sky (onFinishServer): Gabe buchen. medic/patient = Server-IDs.
exports('SkyMedication', function(medic, patient, itemName)
    if not skyOn('sky_meds') or type(itemName) ~= 'string' then return false end
    CreateThread(function()
        local s = SamsSession(tonumber(medic))
        local pd = SamsPlayerData and SamsPlayerData(tonumber(patient) or tonumber(medic))
        if not s or not pd then return end
        local payload = playerFields(pd)
        payload.token = s.token
        payload.item = itemName
        local data = Api.post('sky_medication', payload)
        if data and data.ok then
            TriggerClientEvent('ox_lib:notify', tonumber(medic), { title = 'SAMS', description = data.text, type = 'inform' })
        end
    end)
    return true
end)
