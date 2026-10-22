<?php
/** Untersuchungsbogen Sonderverwendungsfähigkeit: Ausstellrecht für alle, die das Tauglichkeitszeugnis ausstellen dürfen. */
return [
    'description' => 'Untersuchungsbogen Sonderverwendungsfähigkeit',
    'up' => function (Schema $s): void {
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $ids = $s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = 'documents.issue.tauglichkeit'")->fetchAll(PDO::FETCH_COLUMN);
        $has = array_flip($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = 'documents.issue.sv_untersuchung'")->fetchAll(PDO::FETCH_COLUMN));
        foreach ($ids as $id) {
            if (!isset($has[$id])) {
                $add->execute([$id, 'documents.issue.sv_untersuchung']);
            }
        }
    },
];
