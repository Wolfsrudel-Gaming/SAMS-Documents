<?php
/**
 * Etappe 3d: Rechte für die neuen Dokumentarten an passende Ränge vergeben.
 */
return [
    'description' => 'Rechte für neue Dokumentarten',
    'up' => function (Schema $s): void {
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        $grant = [
            // ärztliche Dokumente: wer Arztbriefe schreiben darf
            'documents.issue.arztbrief' => ['psych_gutachten', 'unterbringung', 'op_bericht', 'op_aufklaerung', 'impfnachweis'],
            // Personaldokumente: wer Personal bearbeiten darf
            'users.edit' => ['dienstausweis', 'befoerderung', 'ausbildungsnachweis', 'abmahnung', 'kuendigung', 'arbeitsvertrag'],
        ];
        foreach ($grant as $basis => $types) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                foreach ($types as $t) {
                    if (!$has((int) $rankId, 'documents.issue.' . $t)) {
                        $add->execute([$rankId, 'documents.issue.' . $t]);
                    }
                }
            }
        }
    },
];
