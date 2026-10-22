# Wolfpack Documents – Andock-Schnittstelle für eigene Skripte

Mit dieser Schnittstelle docken eigene Skripte an Wolfpack Documents an, z. B. ein Discord-Bot, eine Webseite, ein Blitzer- oder Kennzeichenscanner, ein Krankenbett-Skript oder ein eigenes MDT.
Es gibt zwei Wege. Beide nutzen **dieselben Aufrufe** mit denselben Feldern.

| Weg | Für | Anmeldung |
|---|---|---|
| **Im Spiel (Lua)** | Resourcen auf einem FiveM-Server, auf dem `sams_tablet` läuft | keine – `sams_tablet` nutzt den Schlüssel des Spielservers |
| **HTTP** | alles außerhalb des Spielservers | eigener Schlüssel unter *Administration → Schnittstellen* |

**Grundsätze**

- Medizinische Inhalte gehen nie hinaus. Akten und Dokumente des Rettungsdienstes sind für alle lesenden Aufrufe gesperrt.
- Ereignisse aus Rettungsdienst und Kanzleien enthalten keine Personendaten (Schweigepflicht, Mandatsgeheimnis).
- Schreiben ist erlaubt, wenn Recht und Fraktion freigegeben sind: Akteneintrag, Benachrichtigung, Anfrage, Fahndung.
- Jede Änderung steht im Zugriffsprotokoll mit dem Namen der Schnittstelle bzw. des Spielers.

---

## 1. Im Spiel (Lua)

Exports nur serverseitig und aus einem Thread aufrufen. Sie warten auf die Antwort des Websystems.

```lua
CreateThread(function()
    local res = exports.sams_tablet:Call('person', { citizen_id = 'ABC123' })
    if res.ok and res.wanted then
        print('Achtung, Fahndung! Gefährlichkeit ' .. res.danger)
    end
end)
```

`Call(name, params, source?)`: Mit `source` wird der am Tablet angemeldete Spieler als Urheber eingetragen. Ohne `source` handelt der Spielserver.

| Kurzform | entspricht |
|---|---|
| `GetUser(source)` | Konto des Spielers im Dokumentensystem: `{ id, name, rank, badge, faction, faction_id, faction_kind, on_duty, … }` oder `nil` |
| `IsOnDuty(source)` / `SetOnDuty(source, on)` | Dienst des Spielers (sofort, ohne Websystem-Abfrage für `IsOnDuty`) |
| `GetOnDuty(faction)` | `duty` |
| `SetDuty(who, on)` | `duty_set` (`who` = Spieler-ID oder `{ username = … }`) |
| `VerifyDocument(code)` | `verify` |
| `GetDocument(source, slot)` | Dokument-Item im Inventarplatz: Metadaten, Ansichts-URL, storniert?, Unterschriften |
| `PersonCheck(citizenId)` | `person` |
| `WantedCheck({ plate, citizen_id })` | `wanted` |
| `AddRecordEntry(data, source?)` | `entry_create` |
| `SendAlert(faction, title, body, opts)` | `alert` |
| `CreateRequest(data, source?)` | `request_create` |
| `CreateWanted(data, source?)` / `CloseWanted(id, note, source?)` | `wanted_create` / `wanted_close` |
| `GetLaws(code, q)` | `laws` |

**Ereignisse im Spiel.** `sams_tablet` fragt alle 10 Sekunden neue Ereignisse ab und gibt sie als Server-Events weiter. Einstellbar sind `Config.EventRelay` und `Config.EventRelayInterval`.

```lua
AddEventHandler('sams:event', function(name, data, id) end)            -- alle Ereignisse
AddEventHandler('sams:event:duty.changed', function(data, id) end)      -- ein bestimmtes
AddEventHandler('sams:dutyChanged', function(source, onDuty) end)       -- sofort, nur Spieler dieses Servers
```

---

## 2. HTTP

**Schlüssel anlegen:** *Administration → Schnittstellen → Neue Schnittstelle*. Dort Rechte und Fraktionen wählen (keine Auswahl bedeutet alle Fraktionen) und optional eine Webhook-Adresse eintragen.
Der Schlüssel (`samsi_…`) wird nur einmal angezeigt.

```
GET  https://dein-portal.example/integration.php?action=ping
POST https://dein-portal.example/integration.php?action=entry_create
Authorization: Bearer samsi_…
Content-Type: application/json
```

Parameter gehen als JSON-Body (POST) oder als Query (GET).

**Antworten:**

- Immer JSON mit `"ok": true|false`, bei Fehlern mit `"error"`.
- Der HTTP-Status ist immer **200**. Ausnahmen sind nur 401 (Schlüssel ungültig oder gesperrt) und 503 (Wartung).
- Das ist Absicht: FXServer (`PerformHttpRequest`) verwirft bei Fehlercodes den Antworttext.
- Zu viele Anfragen: `{"ok": false, "error": "rate_limited"}`. Die Vorgabe sind 240 Anfragen pro Minute und Schlüssel, einstellbar.

```bash
curl -s -H "Authorization: Bearer $SAMS_TOKEN" "https://dein-portal.example/integration.php?action=person&citizen_id=ABC123"
```

```js
// Node.js (z. B. Discord-Bot)
const res = await fetch(`${SAMS_URL}/integration.php?action=alert`, {
  method: 'POST',
  headers: { Authorization: `Bearer ${SAMS_TOKEN}`, 'Content-Type': 'application/json' },
  body: JSON.stringify({ faction: 'lspd', title: 'Banküberfall Fleeca', body: 'Stiller Alarm', on_duty: true }),
}).then(r => r.json());
```

```lua
-- Lua auf einem Server OHNE sams_tablet (z. B. anderer Spielserver nur mit Integrationsschlüssel)
PerformHttpRequest(url .. '/integration.php?action=verify&code=ABCD1234', function(status, body)
    local res = json.decode(body or '{}')
end, 'GET', '', { Authorization = 'Bearer ' .. token })
```

---

## 3. Aufrufe

`faction` ist die ID, der Code oder das Kürzel einer Fraktion (z. B. `1`, `sams`, `LSPD`).

Wird `faction` weggelassen, gilt:

- bei einem Aufruf mit Spieler die Fraktion des Spielers;
- bei einem Schlüssel mit genau einer freigegebenen Fraktion diese Fraktion.

| Aufruf | Recht | Parameter | Antwort (Auszug) |
|---|---|---|---|
| `ping` | – | – | `name, version, scopes, factions` |
| `factions` | – | – | freigegebene Fraktionen mit Modulen |
| `person` | `persons.read` | `citizen_id` | `wanted, warrant, danger, license_driver, license_weapon, permits[], licenses[], name, dob, records[]` (ohne Rettungsdienst) |
| `wanted` | `wanted.read` | `citizen_id` und/oder `plate` | `wanted, entries[], registry[], flagged[]` |
| `wanted_create` | `wanted.write` | `faction, kind (person/vehicle), citizen_id?, name?, plate?, vehicle?, reason, danger 0–3, expires (JJJJ-MM-TT)?` | `id` |
| `wanted_close` | `wanted.write` | `id, note?` | – |
| `verify` | `documents.read` | `code` | `found, status, expired, type, number, issued_at, valid_until, issuer, patient, url` |
| `documents` | `documents.read` | `citizen_id, limit?` | `documents[]` (nur Eckdaten, ohne Rettungsdienst) |
| `duty` | `duty.read` | `faction?` | `count, on_duty[] {user_id, name, badge, rank, faction, since, seconds, fms}` |
| `duty_set` | `duty.write` | `user_id`, `username` oder `discord_id`; dazu `on` | `on_duty, since` |
| `entry_create` | `entries.write` | `faction, citizen_id, title, content, type?, create?, first_name?, last_name?, dob?, sex?` | `entry_id, record_id, record_created, type` |
| `alert` | `alerts.write` | `faction, title, body?, permission?, on_duty?, link?` | `notified` (Anzahl) |
| `request_create` | `requests.write` | `from, to, subject, body, citizen_id?, person?, reference?, urgent?` | `id` |
| `laws` | `laws.read` | `code?, q?, body?, limit?` | `codes[], code, sections[] {ref, title, penalty, jail_max, fine_max}` |
| `events` | `events.read` | `since` (-1 = aktuelle Position), `limit?` | `last_id, events[]` |

Hinweise zu einzelnen Aufrufen:

- **`entry_create`:**
  - `type` muss zu den Eintragsarten der Fraktion passen, sonst wird „Sonstiges“ verwendet.
  - Mit `create: true` und Name wird eine fehlende Akte angelegt.
  - Der Eintrag endet mit dem Namen der Schnittstelle.
- **`alert`:**
  - Die Meldung landet in der Glocke der Mitglieder. Wer Discord-Nachrichten eingeschaltet hat, bekommt sie zusätzlich als Direktnachricht.
  - `permission` beschränkt die Empfänger auf ein Recht, z. B. `wanted.view`.
  - `on_duty: true` schickt die Meldung nur an Mitglieder im Dienst.
- **`duty_set`:** Das Konto braucht das Recht „Dienstzeit stempeln“. Als Quelle steht dann „Schnittstelle“ im Dienstzeitbuch.

---

## 4. Ereignisse

| Ereignis | Daten |
|---|---|
| `document.issued` | `id, number, type, title, status, issued_at, valid_until, issuer, verify_code, verify_url, faction, citizen_id?, person?` |
| `document.revoked` | wie oben plus `reason` |
| `duty.changed` | `user_id, name, badge, on_duty, source, seconds, faction` |
| `wanted.created` / `wanted.closed` | `id, kind, name, citizen_id, plate, vehicle, reason, danger, warrant, status, faction` |
| `request.created` / `request.answered` | `id, subject, status, urgent, reference, from, to` |
| `bill.paid` | `document_id, number, type, amount, citizen_id, faction` |
| `integration.test` | Testereignis aus der Adminseite |

`citizen_id` und `person` fehlen bei Rettungsdienst und Kanzleien.

Ereignisse werden 14 Tage aufbewahrt. Jedes Ereignis hat eine fortlaufende `id`. Empfänger sollten Doppelte über diese ID erkennen, denn bei einem Fehler wird erneut zugestellt.

**Abfragen (Polling):**

1. Einmal `events` mit `since = -1` aufrufen. Das liefert die aktuelle Position `last_id`.
2. Danach regelmäßig `events` mit `since = <last_id>` aufrufen.

**Webhook:**

- Neue Ereignisse gehen gesammelt per POST an die eingetragene Adresse:
  - direkt nach dem Ereignis;
  - sonst spätestens mit dem nächsten Cron-Lauf.
- Body: `{"integration": "…", "sent_at": "…", "events": [ {id, event, faction_id, created_at, data}, … ]}`.
- Jede 2xx-Antwort gilt als zugestellt.
- Bei Fehlern wird später erneut zugestellt, mit wachsender Pause bis zu einer Stunde. Die Reihenfolge bleibt erhalten.
- Signatur: `X-SAMS-Signature: sha256=` + HMAC-SHA256 über `X-SAMS-Timestamp + "." + Body` mit dem Webhook-Geheimnis.
- „Nur diese Ereignisse“ filtert, z. B. `document.*, duty.changed`.

```js
// Signatur prüfen (Node.js/Express)
const crypto = require('crypto');
app.post('/sams', express.raw({ type: 'application/json' }), (req, res) => {
  const ts = req.get('X-SAMS-Timestamp');
  const expected = 'sha256=' + crypto.createHmac('sha256', SECRET).update(ts + '.' + req.body).digest('hex');
  if (!crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(req.get('X-SAMS-Signature') || ''))) return res.sendStatus(401);
  if (Math.abs(Date.now() / 1000 - Number(ts)) > 300) return res.sendStatus(401); // zu alt
  for (const ev of JSON.parse(req.body).events) console.log(ev.event, ev.data);
  res.sendStatus(204);
});
```

```php
// Signatur prüfen (PHP)
$body = file_get_contents('php://input');
$ok = hash_equals('sha256=' . hash_hmac('sha256', $_SERVER['HTTP_X_SAMS_TIMESTAMP'] . '.' . $body, $secret), $_SERVER['HTTP_X_SAMS_SIGNATURE'] ?? '');
```

---

## 5. Sicherheit

- Schlüssel nur serverseitig speichern, nie in einen Browser oder in einen FiveM-Client. In der `server.cfg` mit `set` eintragen, nie mit `setr`.
- Jedem Skript nur die Rechte und Fraktionen geben, die es braucht.
- Den Schlüssel bei Verdacht sofort unter *Schnittstellen* neu erzeugen oder sperren.
- Wolfpack Documents speichert nur einen Hash des Schlüssels. Ein verlorener Schlüssel lässt sich nicht wieder anzeigen, sondern nur neu erzeugen.
- Die älteren Aktionen von `api.php` (Tablet, Drucker, Rechnungen …) gehören zu `sams_tablet` und brauchen den Schlüssel des Spielservers. Eigene Skripte nutzen stattdessen die Aufrufe dieser Seite.
