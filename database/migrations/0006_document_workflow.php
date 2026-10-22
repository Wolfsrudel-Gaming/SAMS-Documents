<?php
/**
 * Etappe 3a: Entwürfe mit Übergabe, Freigaben (Gegenzeichnung) und Nachträge.
 */
return [
    'description' => 'Dokument-Entwürfe, Freigaben, Nachträge',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();

        // Entwürfe sind noch keine Dokumente: keine Nummer, kein Prüfcode
        $s->exec("CREATE TABLE IF NOT EXISTS document_drafts (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            type $str NOT NULL,
            patient_id $fk NULL,
            case_id $fk NULL,
            data $text,
            signature $text,
            status {$s->str(16)} NOT NULL DEFAULT 'draft',
            owner_id $fk NOT NULL,
            created_by $fk NOT NULL,
            submitted_by $fk NULL,
            submitted_at $dt NULL,
            reviewed_by $fk NULL,
            note $text,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_drafts_owner', 'document_drafts', 'owner_id, status');
        $s->index('idx_drafts_status', 'document_drafts', 'status');

        // Nachträge: Dokumente werden nie geändert, Korrekturen erscheinen als Nachtrag
        $s->exec("CREATE TABLE IF NOT EXISTS document_addenda (
            id $pk,
            document_id $fk NOT NULL,
            text $text,
            author_snapshot $text,
            created_by $fk NULL,
            created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_addenda_document', 'document_addenda', 'document_id');

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        // Freigeben und Nachträge zu fremden Dokumenten: ab Oberarzt-Stufe (70)
        foreach ($s->db->query('SELECT id FROM ranks WHERE level >= 70')->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            foreach (['documents.approve', 'documents.addendum'] as $perm) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
