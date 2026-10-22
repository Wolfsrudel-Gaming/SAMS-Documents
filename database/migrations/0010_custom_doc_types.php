<?php
/**
 * Etappe 3e: Eigene Dokumentarten aus dem Vorlagen-Editor.
 */
return [
    'description' => 'Vorlagen-Editor: eigene Dokumentarten',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS custom_doc_types (
            id $pk,
            type_key {$s->str(64)} NOT NULL UNIQUE,
            title $str NOT NULL,
            short $str NOT NULL DEFAULT '',
            code {$s->str(8)} NOT NULL,
            category $str NOT NULL DEFAULT 'Eigene Vorlagen',
            layout {$s->str(16)} NOT NULL DEFAULT 'letter',
            subject {$s->str(16)} NOT NULL DEFAULT 'patient',
            patient_mode {$s->str(16)} NOT NULL DEFAULT 'optional',
            seal {$s->str(16)} NOT NULL DEFAULT 'rect',
            fields $text,
            body $text,
            active INTEGER NOT NULL DEFAULT 1,
            created_by INTEGER NULL,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$s->engine()}");

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = 'settings.manage'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            if (!(int) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rankId AND permission = 'templates.manage'")->fetchColumn()) {
                $add->execute([$rankId, 'templates.manage']);
            }
        }
    },
];
