<?php
/**
 * Mehr Fachinhalte für den Rettungsdienst: Rechte für die neuen Dokumentvorlagen,
 * über 100 Wiki-Seiten (SOPs, Krankheitsbilder, Medikamente, Organisation) und
 * Lehrgänge mit Lernmodulen und Prüfungsfragen. Legt nur Fehlendes an.
 */
return [
    'description' => 'Fachinhalte Rettungsdienst (Vorlagen, Wiki, Ausbildung)',
    'up' => function (Schema $s): void {
        // Ausstellrechte: ärztliche Vorlagen wie das Attest, alle übrigen wie das Einsatzprotokoll
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach (document_types_medical() as $key => $t) {
            $basis = $t['role'] === 'doctor' ? 'documents.issue.attest' : 'documents.issue.einsatzprotokoll';
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$s->db->query('SELECT COUNT(*) FROM rank_permissions WHERE rank_id = ' . (int) $rankId . " AND permission = 'documents.issue.$key'")->fetchColumn()) {
                    $add->execute([$rankId, "documents.issue.$key"]);
                }
            }
        }
        foreach ($s->db->query("SELECT id FROM factions WHERE kind = 'medical' OR id = 1")->fetchAll(PDO::FETCH_COLUMN) as $fid) {
            seed_medical_content((int) $fid);
        }
    },
];
