<?php
/**
 * Drucker in der Spielwelt, Druckaufträge, Tablet-Anmeldungen und
 * Ansichts-Tokens für Dokumente als Ingame-Item.
 */
return [
    'description' => 'Drucker, Druckaufträge, Tablet-Anmeldungen, Dokument-Tokens',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $dt = $s->datetime();
        $engine = $s->engine();

        $s->exec("CREATE TABLE IF NOT EXISTS printers (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            name $str NOT NULL,
            location $str DEFAULT '',
            x DOUBLE NOT NULL DEFAULT 0,
            y DOUBLE NOT NULL DEFAULT 0,
            z DOUBLE NOT NULL DEFAULT 0,
            heading DOUBLE NOT NULL DEFAULT 0,
            prop_model {$s->str(64)} DEFAULT 'prop_printer_01',
            show_blip INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_by $fk NULL,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$engine}");

        $s->exec("CREATE TABLE IF NOT EXISTS print_jobs (
            id $pk,
            document_id $fk NOT NULL,
            printer_id $fk NOT NULL,
            copy_variant {$s->str(32)} DEFAULT '',
            copies INTEGER NOT NULL DEFAULT 1,
            status {$s->str(16)} NOT NULL DEFAULT 'queued',
            requested_by $fk NULL,
            requested_at $dt NOT NULL,
            picked_up_at $dt NULL,
            picked_up_by $str DEFAULT '',
            picked_up_license $str DEFAULT ''
        ){$engine}");
        $s->index('idx_print_jobs_printer', 'print_jobs', 'printer_id, status');
        $s->index('idx_print_jobs_document', 'print_jobs', 'document_id');

        $s->exec("CREATE TABLE IF NOT EXISTS tablet_tokens (
            id $pk,
            user_id $fk NOT NULL,
            token_hash {$s->str(64)} NOT NULL,
            session_version INTEGER NOT NULL DEFAULT 1,
            player_name $str DEFAULT '',
            license $str DEFAULT '',
            launch_hash {$s->str(64)} DEFAULT '',
            launch_expires $dt NULL,
            created_at $dt NOT NULL,
            last_used_at $dt NULL,
            expires_at $dt NOT NULL,
            revoked INTEGER NOT NULL DEFAULT 0
        ){$engine}");
        $s->index('idx_tablet_tokens_hash', 'tablet_tokens', 'token_hash', true);
        $s->index('idx_tablet_tokens_launch', 'tablet_tokens', 'launch_hash');

        // Fälschungssicherer Schlüssel für die Ansicht eines Dokuments als Item
        $s->addColumn('documents', 'view_token', $s->str(64) . " DEFAULT ''");
        $st = $s->db->query("SELECT id FROM documents WHERE view_token = '' OR view_token IS NULL");
        $upd = $s->db->prepare('UPDATE documents SET view_token = ? WHERE id = ?');
        foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $upd->execute([bin2hex(random_bytes(20)), $id]);
        }
        $s->index('idx_documents_view_token', 'documents', 'view_token');

        // Neue Rechte an bestehende Ränge vergeben
        $ranks = $s->db->query('SELECT id FROM ranks')->fetchAll(PDO::FETCH_COLUMN);
        $has = $s->db->prepare('SELECT COUNT(*) FROM rank_permissions WHERE rank_id = ? AND permission LIKE ?');
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($ranks as $rankId) {
            $exists = function (string $p) use ($has, $rankId): bool {
                $has->execute([$rankId, $p]);
                return (int) $has->fetchColumn() > 0;
            };
            if ($exists('documents.issue.%') && !$exists('documents.print')) {
                $add->execute([$rankId, 'documents.print']);
            }
            if ($exists('settings.manage') && !$exists('printers.manage')) {
                $add->execute([$rankId, 'printers.manage']);
            }
        }
    },
];
