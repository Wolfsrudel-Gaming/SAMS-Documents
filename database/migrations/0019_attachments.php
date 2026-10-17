<?php
/**
 * Anhänge an Patientenakten und Fällen (Fotos, Röntgen-/CT-Bilder, Scans, PDF).
 * Die Dateien liegen außerhalb des Webroots in storage/attachments.
 */
return [
    'description' => 'Anhänge',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $str = $s->str();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS attachments (
            id {$s->pk()}, patient_id $fk NOT NULL, case_id $fk NULL, title $str NOT NULL, original_name $str DEFAULT '',
            stored_name {$s->str(120)} NOT NULL, mime {$s->str(64)} NOT NULL, size INTEGER NOT NULL DEFAULT 0,
            width INTEGER NOT NULL DEFAULT 0, height INTEGER NOT NULL DEFAULT 0, has_thumb INTEGER NOT NULL DEFAULT 0,
            confidential INTEGER NOT NULL DEFAULT 0, created_by $fk NULL, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_attachments_patient', 'attachments', 'patient_id');
        $s->index('idx_attachments_case', 'attachments', 'case_id');
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = 'entries.create'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            if (!(int) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rankId AND permission = 'attachments.upload'")->fetchColumn()) {
                $add->execute([$rankId, 'attachments.upload']);
            }
        }
    },
];
