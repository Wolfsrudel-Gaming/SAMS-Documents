<?php
/** Spezialrettung (TAS): Flugbuch mit Luftfahrzeugen und Flügen. Die Fraktionsart selbst steckt im Code (FACTION_KINDS). */
return [
    'description' => 'Spezialrettung: Flugbuch und Luftfahrzeuge',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS aircraft (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, callsign {$s->str(32)} NOT NULL, registration {$s->str(32)} DEFAULT '', kind {$s->str(32)} DEFAULT 'Hubschrauber',
            model $str DEFAULT '', status {$s->str(16)} NOT NULL DEFAULT 'ok', minutes_total INTEGER NOT NULL DEFAULT 0, maintenance_at INTEGER NOT NULL DEFAULT 0,
            maintenance_interval INTEGER NOT NULL DEFAULT 0, notes {$s->text()}, active INTEGER NOT NULL DEFAULT 1, created_at $dt NOT NULL, updated_at $dt NULL
        ){$s->engine()}");
        $s->index('idx_aircraft_faction', 'aircraft', 'faction_id');
        $s->exec("CREATE TABLE IF NOT EXISTS flights (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, aircraft_id $fk NOT NULL, flown_on {$s->str(10)} NOT NULL, pilot_id $fk NULL, crew $str DEFAULT '',
            mission $str DEFAULT '', start_place $str DEFAULT '', end_place $str DEFAULT '', minutes INTEGER NOT NULL DEFAULT 0, winch_ops INTEGER NOT NULL DEFAULT 0,
            landings INTEGER NOT NULL DEFAULT 0, document_id $fk NULL, recorded_by $fk NULL, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_flights_faction', 'flights', 'faction_id, flown_on');
        $s->index('idx_flights_pilot', 'flights', 'pilot_id');
    },
];
