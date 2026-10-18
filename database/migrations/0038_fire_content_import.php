<?php
/** Fachinhalte Feuerwehr nachladen (Lehrgänge kamen nach 0036). Legt nur Fehlendes an. */
return [
    'description' => 'Fachinhalte Feuerwehr nachladen',
    'up' => function (Schema $s): void {
        foreach ($s->db->query("SELECT id FROM factions WHERE kind = 'fire'")->fetchAll(PDO::FETCH_COLUMN) as $fid) {
            seed_kind_content((int) $fid, 'fire');
        }
    },
];
