<?php
/**
 * Mehrere Standorte (z. B. Pillbox Hill, Sandy Shores, Paleto Bay) mit eigenem Briefkopf.
 * Ohne Standorte gilt der Briefkopf aus den Einstellungen wie bisher.
 */
return [
    'description' => 'Standorte',
    'up' => function (Schema $s): void {
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS locations (
            id {$s->pk()}, faction_id {$s->fk()} NOT NULL DEFAULT 1, name $str NOT NULL, short {$s->str(32)} DEFAULT '',
            org_sub $str DEFAULT '', org_street $str DEFAULT '', org_city $str DEFAULT '', city $str DEFAULT '',
            phone {$s->str(64)} DEFAULT '', fax {$s->str(64)} DEFAULT '', email $str DEFAULT '', bsnr {$s->str(64)} DEFAULT '',
            active INTEGER NOT NULL DEFAULT 1, sort INTEGER NOT NULL DEFAULT 0, created_at {$s->datetime()} NOT NULL, updated_at {$s->datetime()} NOT NULL
        ){$s->engine()}");
        $s->addColumn('users', 'location_id', $s->fk() . ' NULL');
        $s->addColumn('documents', 'location_id', $s->fk() . ' NULL');
    },
];
