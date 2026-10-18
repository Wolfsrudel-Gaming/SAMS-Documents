<?php
/**
 * Mehrere FiveM-Server (Staaten) an einem Portal: eigener API-Schlüssel und eigene Spieleinstellungen je Server.
 * Drucker, Tablet-Anmeldungen und Spiel-Rechnungen gehören zu einem Server. Bestehendes bleibt beim Hauptserver
 * (dessen Schlüssel ist zunächst API_KEY aus der .env).
 */
return [
    'description' => 'Mehrere Spielserver (Staaten)',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS game_servers (
            id {$s->pk()}, name {$s->str()} NOT NULL, short {$s->str(16)} DEFAULT '', api_key {$s->str(96)} DEFAULT '', config {$s->text()},
            notes {$s->str()} DEFAULT '', active INTEGER NOT NULL DEFAULT 1, last_seen_at $dt NULL, last_ip {$s->str(64)} DEFAULT '',
            created_at $dt NOT NULL, updated_at $dt NULL
        ){$s->engine()}");
        if (!(int) $s->db->query('SELECT COUNT(*) FROM game_servers')->fetchColumn()) {
            $s->db->prepare("INSERT INTO game_servers (name, short, api_key, config, active, created_at) VALUES ('San Andreas', 'SA', '', '{}', 1, ?)")->execute([date('Y-m-d H:i:s')]);
        }
        $s->addColumn('printers', 'server_id', "$fk NULL");
        $s->addColumn('tablet_tokens', 'server_id', "$fk NULL");
        $s->addColumn('game_bills', 'server_id', "$fk NULL");
        $first = (int) $s->db->query('SELECT MIN(id) FROM game_servers')->fetchColumn();
        $s->db->exec("UPDATE printers SET server_id = $first WHERE server_id IS NULL");
        $s->db->exec("UPDATE game_bills SET server_id = $first WHERE server_id IS NULL");
    },
];
