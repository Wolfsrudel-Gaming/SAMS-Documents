<?php
/**
 * Etappe 3c: Freigabe einzelner Dokumente für Behörden (LSPD, Justiz …)
 * per geheimem Link oder an ein Benutzerkonto.
 */
return [
    'description' => 'Dokument-Freigaben für Behörden',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $dt = $s->datetime();

        $s->exec("CREATE TABLE IF NOT EXISTS document_shares (
            id $pk,
            document_id $fk NOT NULL,
            kind {$s->str(16)} NOT NULL DEFAULT 'link',
            token_hash {$s->str(64)} NULL,
            user_id $fk NULL,
            agency {$s->str(32)} NOT NULL DEFAULT '',
            recipient $str DEFAULT '',
            purpose $str DEFAULT '',
            expires_at $dt NULL,
            revoked INTEGER NOT NULL DEFAULT 0,
            views INTEGER NOT NULL DEFAULT 0,
            last_viewed_at $dt NULL,
            created_by $fk NULL,
            created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_shares_document', 'document_shares', 'document_id');
        $s->index('idx_shares_token', 'document_shares', 'token_hash');
        $s->index('idx_shares_user', 'document_shares', 'user_id');

        // Teilen dürfen Ränge ab Arzt-Stufe (50)
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query('SELECT id FROM ranks WHERE level >= 50')->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            if (!(int) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rankId AND permission = 'documents.share'")->fetchColumn()) {
                $add->execute([$rankId, 'documents.share']);
            }
        }
    },
];
