<?php
/**
 * Mehrere Fraktionen (Rettungsdienst, Polizei, Justiz, Feuerwehr, Verwaltung) in einer Installation.
 * Jede Fraktion hat eigene Ränge, Abteilungen, Akten, Dokumente, Einstellungen und Briefköpfe.
 */
return [
    'description' => 'Mehrere Fraktionen',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $s->addColumn('factions', 'kind', $s->str(16) . " NOT NULL DEFAULT 'medical'");
        $s->addColumn('factions', 'short', $s->str(32) . " DEFAULT ''");
        $s->addColumn('factions', 'color', $s->str(16) . " DEFAULT '#0b4f9c'");
        $s->addColumn('factions', 'modules', $s->text() . ' NULL');
        $s->addColumn('factions', 'sort', 'INTEGER NOT NULL DEFAULT 0');
        $s->db->exec("UPDATE factions SET short = 'SAMS' WHERE id = 1 AND (short IS NULL OR short = '')");

        foreach (['audit_log', 'custom_doc_types', 'duty_sessions', 'roster_shifts', 'medications', 'appointments', 'portal_requests', 'applications', 'message_threads'] as $t) {
            $s->addColumn($t, 'faction_id', "$fk NOT NULL DEFAULT 1");
            $s->index("idx_{$t}_faction", $t, 'faction_id');
        }
        foreach (['cases', 'document_drafts', 'price_list', 'printers', 'training_courses', 'announcements', 'ranks', 'departments', 'qualifications', 'locations'] as $t) {
            $s->index("idx_{$t}_faction", $t, 'faction_id');
        }

        // Wiki-Adressen nur noch je Fraktion eindeutig
        if ($s->mysql()) {
            try {
                $s->db->exec('ALTER TABLE wiki_pages DROP INDEX slug');
            } catch (PDOException $e) {
                // bereits umgestellt
            }
        } else {
            $sql = (string) $s->db->query("SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'wiki_pages'")->fetchColumn();
            if (stripos($sql, 'UNIQUE') !== false) {
                $s->db->exec('ALTER TABLE wiki_pages RENAME TO wiki_pages_old');
                $s->db->exec(str_ireplace(['CREATE TABLE wiki_pages', 'NOT NULL UNIQUE'], ['CREATE TABLE wiki_pages', 'NOT NULL'], $sql));
                $s->db->exec('INSERT INTO wiki_pages SELECT * FROM wiki_pages_old');
                $s->db->exec('DROP TABLE wiki_pages_old');
            }
        }
        $s->index('idx_wiki_faction_slug', 'wiki_pages', 'faction_id, slug', true);
    },
];
