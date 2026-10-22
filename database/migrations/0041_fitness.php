<?php
/** Tauglichkeit der Mitarbeitenden: Ergebnisse ärztlicher Einsatztauglichkeits-Untersuchungen (ohne Befunde) und Bürger-ID am Konto. */
return [
    'description' => 'Einsatztauglichkeit der Mitarbeitenden',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->addColumn('users', 'citizen_id', "{$s->str(64)} DEFAULT ''");
        $s->exec("CREATE TABLE IF NOT EXISTS fitness_records (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, user_id $fk NOT NULL, kind {$s->str(16)} NOT NULL, result {$s->str(16)} NOT NULL,
            restrictions {$s->text()}, examined_on {$s->str(10)} DEFAULT '', valid_until {$s->str(10)} DEFAULT '', examiner $str DEFAULT '',
            document_id $fk NULL, source_faction_id $fk NULL, revoked INTEGER NOT NULL DEFAULT 0, reminded INTEGER NOT NULL DEFAULT 0,
            recorded_by $fk NULL, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_fitness_user', 'fitness_records', 'faction_id, user_id, kind');
        $s->index('idx_fitness_doc', 'fitness_records', 'document_id');

        // Modul für bestehende TAS und Feuerwehren (mit eigener Modulauswahl); Rechte für bestehende Ränge
        $byFaction = [];
        foreach ($s->db->query('SELECT id, kind, modules FROM factions')->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $kind = $f['kind'] ?: 'medical';
            $mods = json_decode((string) $f['modules'], true);
            if (is_array($mods) && in_array($kind, ['rescue', 'fire'], true) && !in_array('fitness', $mods, true)) {
                $mods[] = 'fitness';
                $s->db->prepare('UPDATE factions SET modules = ? WHERE id = ?')->execute([json_encode(array_values($mods)), $f['id']]);
            }
            $byFaction[(int) $f['id']] = $kind;
        }
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query('SELECT id, faction_id FROM ranks')->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $kind = $byFaction[(int) $r['faction_id']] ?? '';
            $has = $s->db->query('SELECT permission FROM rank_permissions WHERE rank_id = ' . (int) $r['id'])->fetchAll(PDO::FETCH_COLUMN);
            $grant = [];
            if (in_array($kind, ['rescue', 'fire'], true)) {
                if (in_array('documents.view', $has, true)) {
                    $grant[] = 'fitness.view';
                }
                if (in_array('users.edit', $has, true)) {
                    $grant[] = 'fitness.manage';
                }
            }
            if (in_array('documents.issue.tauglichkeit', $has, true)) {
                $grant[] = 'documents.issue.einsatztauglichkeit'; // Ärzt/innen im Rettungsdienst
            }
            foreach (array_diff($grant, $has) as $p) {
                $add->execute([$r['id'], $p]);
            }
        }
    },
];
