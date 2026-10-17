<?php
/**
 * Doppelte Patientenakten zusammenführen: Verweis auf die verbleibende Akte.
 */
return [
    'description' => 'Akten zusammenführen',
    'up' => function (Schema $s): void {
        $s->addColumn('patients', 'merged_into', "{$s->fk()} NULL");
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = 'patients.archive'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            if (!(int) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rankId AND permission = 'patients.merge'")->fetchColumn()) {
                $add->execute([$rankId, 'patients.merge']);
            }
        }
    },
];
