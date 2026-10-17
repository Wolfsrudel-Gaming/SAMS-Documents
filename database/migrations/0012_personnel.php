<?php
/**
 * Etappe 5a: Personalakte (Laufbahn-Einträge, Personaldokumente am Konto).
 */
return [
    'description' => 'Personalakte',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS personnel_events (
            id $pk,
            user_id $fk NOT NULL,
            kind {$s->str(24)} NOT NULL DEFAULT 'note',
            title $str NOT NULL,
            text $text,
            event_date {$s->str(10)} NOT NULL,
            confidential INTEGER NOT NULL DEFAULT 0,
            document_id $fk NULL,
            created_by $fk NULL,
            created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_personnel_user', 'personnel_events', 'user_id, event_date');

        // Personaldokumente direkt dem Konto zuordnen (bisher nur in den Dokumentdaten)
        $s->addColumn('documents', 'employee_id', "$fk NULL");
        $s->index('idx_documents_employee', 'documents', 'employee_id');
        $upd = $s->db->prepare('UPDATE documents SET employee_id = ? WHERE id = ?');
        foreach ($s->db->query("SELECT id, data FROM documents WHERE data LIKE '%\"_employee\"%'")->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $d = json_decode((string) $r['data'], true) ?: [];
            if (!empty($d['_employee']['id'])) {
                $upd->execute([(int) $d['_employee']['id'], $r['id']]);
            }
        }

        // Eintrittsdatum als erster Laufbahn-Eintrag
        $ins = $s->db->prepare('INSERT INTO personnel_events (user_id, kind, title, event_date, created_at) VALUES (?, ?, ?, ?, ?)');
        foreach ($s->db->query('SELECT id, created_at FROM users')->fetchAll(PDO::FETCH_ASSOC) as $u) {
            $ins->execute([$u['id'], 'hire', 'Konto angelegt / Eintritt', substr((string) $u['created_at'], 0, 10), date('Y-m-d H:i:s')]);
        }

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['personnel.view' => 'users.view', 'personnel.edit' => 'users.edit', 'personnel.confidential' => 'users.edit'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
