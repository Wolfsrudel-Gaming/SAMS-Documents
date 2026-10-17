<?php
/**
 * Mandantenfähigkeit vorbereiten: Fraktionen (vorerst nur SAMS).
 * Später können LSPD, DOJ oder Feuerwehr eigene Bereiche bekommen.
 */
return [
    'description' => 'Fraktionen anlegen und Kerndaten der Fraktion SAMS zuordnen',
    'up' => function (Schema $s): void {
        $s->exec('CREATE TABLE IF NOT EXISTS factions (
            id ' . $s->pk() . ',
            code ' . $s->str(32) . ' NOT NULL UNIQUE,
            name ' . $s->str() . ' NOT NULL,
            active INTEGER NOT NULL DEFAULT 1,
            created_at ' . $s->datetime() . ' NOT NULL
        )' . $s->engine());
        if (!$s->db->query('SELECT COUNT(*) FROM factions')->fetchColumn()) {
            $st = $s->db->prepare('INSERT INTO factions (id, code, name, created_at) VALUES (1, ?, ?, ?)');
            $st->execute(['sams', 'San Andreas Medical Services', date('Y-m-d H:i:s')]);
        }
        foreach (['users', 'patients', 'entries', 'documents'] as $table) {
            $s->addColumn($table, 'faction_id', $s->fk() . ' NOT NULL DEFAULT 1');
            $s->index("idx_{$table}_faction", $table, 'faction_id');
        }
    },
];
