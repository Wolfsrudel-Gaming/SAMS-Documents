<?php
/**
 * Dokumentenfälschung im Spiel: geheime Fälscherwerkstätten und erstellte Fälschungen.
 * Fälschungen liegen bewusst NICHT in documents – ihr Prüfcode wird von der Echtheitsprüfung nie gefunden.
 */
return [
    'description' => 'Dokumentenfälschung (Werkstätten, Fälschungen)',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS forgery_sites (
            id {$s->pk()}, name $str NOT NULL, x DOUBLE NOT NULL DEFAULT 0, y DOUBLE NOT NULL DEFAULT 0, z DOUBLE NOT NULL DEFAULT 0, heading DOUBLE NOT NULL DEFAULT 0,
            prop_model {$s->str(64)} DEFAULT '', notes $str DEFAULT '', server_id $fk NULL, active INTEGER NOT NULL DEFAULT 1, created_at $dt NOT NULL, updated_at $dt NULL
        ){$s->engine()}");
        $s->exec("CREATE TABLE IF NOT EXISTS forgeries (
            id {$s->pk()}, session_hash {$s->str(64)} NOT NULL, view_token {$s->str(64)} NOT NULL, verify_code {$s->str(16)} NOT NULL, number {$s->str(64)} DEFAULT '',
            type {$s->str(64)} DEFAULT '', faction_id $fk NULL, data {$s->text()}, patient_snapshot {$s->text()}, issuer_snapshot {$s->text()},
            quality INTEGER NOT NULL DEFAULT 0, allowed_types {$s->text()}, status {$s->str(16)} NOT NULL DEFAULT 'session', site_id $fk NULL, server_id $fk NULL,
            license $str DEFAULT '', player $str DEFAULT '', checked_count INTEGER NOT NULL DEFAULT 0, last_checked_at $dt NULL,
            created_at $dt NOT NULL, done_at $dt NULL, expires_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_forgeries_session', 'forgeries', 'session_hash');
        $s->index('idx_forgeries_view', 'forgeries', 'view_token');
        $s->index('idx_forgeries_code', 'forgeries', 'verify_code');
    },
];
