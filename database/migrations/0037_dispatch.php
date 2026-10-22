<?php
/**
 * Leitstellen-Anbindung (optional, z. B. tetrascript/tetra-core): Einsätze aus der Leitstelle und FMS-Status der Mitarbeitenden.
 * SAMS funktioniert ohne Leitstelle unverändert; die Daten kommen über api.php von der Brücke in sams_tablet.
 */
return [
    'description' => 'Leitstelle: Einsätze und FMS-Status',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS dispatch_incidents (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, source {$s->str(32)} NOT NULL DEFAULT 'tetra', ext_id {$s->str(64)} NOT NULL, number {$s->str(64)} DEFAULT '',
            org {$s->str(32)} DEFAULT '', category {$s->str(32)} DEFAULT '', keyword {$s->str(64)} DEFAULT '', title $str DEFAULT '', address $str DEFAULT '',
            caller $str DEFAULT '', description {$s->text()}, priority INTEGER NOT NULL DEFAULT 0, patients INTEGER NOT NULL DEFAULT 0, phase {$s->str(16)} DEFAULT '',
            closure $str DEFAULT '', started_at $dt NULL, alarm_at $dt NULL, onscene_at $dt NULL, closed_at $dt NULL, payload {$s->text()},
            document_id $fk NULL, received_at $dt NOT NULL, updated_at $dt NULL
        ){$s->engine()}");
        $s->index('idx_dispatch_ext', 'dispatch_incidents', 'source, ext_id');
        $s->index('idx_dispatch_faction', 'dispatch_incidents', 'faction_id, updated_at');
        $s->addColumn('users', 'fms_status', 'INTEGER NULL');
        $s->addColumn('users', 'fms_at', "$dt NULL");
        $s->addColumn('users', 'fms_opta', $s->str(32) . " DEFAULT ''");
        $s->addColumn('users', 'fms_issi', $s->str(16) . " DEFAULT ''");
    },
];
