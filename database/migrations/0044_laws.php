<?php
/** Gesetzbücher (je Staat oder für alle): Gesetzbuch → Bücher (StGB, StVO …) → Paragraphen mit Strafmaß. */
return [
    'description' => 'Gesetzbücher',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS law_codes (
            id {$s->pk()}, server_id $fk NULL, name $str NOT NULL, description {$s->text()}, source $str DEFAULT '', active INTEGER NOT NULL DEFAULT 1,
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        ){$s->engine()}");
        $s->exec("CREATE TABLE IF NOT EXISTS law_books (
            id {$s->pk()}, code_id $fk NOT NULL, title $str NOT NULL, short {$s->str(32)} DEFAULT '', sort INTEGER NOT NULL DEFAULT 0
        ){$s->engine()}");
        $s->index('idx_law_books', 'law_books', 'code_id, sort');
        $s->exec("CREATE TABLE IF NOT EXISTS law_sections (
            id {$s->pk()}, book_id $fk NOT NULL, number {$s->str(16)} NOT NULL, title $str DEFAULT '', body {$s->text()}, penalty $str DEFAULT '',
            jail_max INTEGER NOT NULL DEFAULT 0, fine_max INTEGER NOT NULL DEFAULT 0, penalty_mode {$s->str(8)} DEFAULT '', sort INTEGER NOT NULL DEFAULT 0
        ){$s->engine()}");
        $s->index('idx_law_sections', 'law_sections', 'book_id, sort');
        // Pflege: Leitung von Justiz und Verwaltung (der Hauptadministrator darf ohnehin alles)
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query("SELECT r.id FROM ranks r JOIN factions f ON f.id = r.faction_id WHERE f.kind IN ('justice', 'government')
            AND r.id IN (SELECT rank_id FROM rank_permissions WHERE permission = 'settings.manage')
            AND r.id NOT IN (SELECT rank_id FROM rank_permissions WHERE permission = 'laws.manage')")->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $add->execute([$id, 'laws.manage']);
        }
    },
];
