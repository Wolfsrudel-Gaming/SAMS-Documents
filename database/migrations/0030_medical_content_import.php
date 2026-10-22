<?php
/**
 * Fachinhalte Rettungsdienst nachladen (Wiki-Seiten und Lehrgänge), falls 0029 vor den
 * Inhaltsdateien lief. Legt nur Fehlendes an.
 */
return [
    'description' => 'Fachinhalte Rettungsdienst nachladen',
    'up' => function (Schema $s): void {
        foreach ($s->db->query("SELECT id FROM factions WHERE kind = 'medical' OR id = 1")->fetchAll(PDO::FETCH_COLUMN) as $fid) {
            seed_medical_content((int) $fid);
        }
    },
];
