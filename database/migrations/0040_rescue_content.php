<?php
/** Fachinhalte Spezialrettung (TAS) für bestehende Fraktionen. Legt nur Fehlendes an. */
return [
    'description' => 'Fachinhalte Spezialrettung (TAS)',
    'up' => function (Schema $s): void {
        foreach ($s->db->query("SELECT id FROM factions WHERE kind = 'rescue'")->fetchAll(PDO::FETCH_COLUMN) as $fid) {
            seed_kind_content((int) $fid, 'rescue');
        }
    },
];
