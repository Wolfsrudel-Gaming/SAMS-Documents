<?php
/**
 * Medikamentenbestand, Bewegungen (inkl. BtM-Buch) und Rezept-Einlösungen.
 */
return [
    'description' => 'Medikamentenbestand und Apotheke',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS medications (
            id {$s->pk()}, name {$s->str()} NOT NULL, keywords {$s->str()} DEFAULT '', is_btm INTEGER NOT NULL DEFAULT 0,
            unit {$s->str(32)} NOT NULL DEFAULT 'Packung', stock INTEGER NOT NULL DEFAULT 0, min_stock INTEGER NOT NULL DEFAULT 0,
            item_name {$s->str(64)} DEFAULT '', item_count INTEGER NOT NULL DEFAULT 1, active INTEGER NOT NULL DEFAULT 1,
            low_warned INTEGER NOT NULL DEFAULT 0, created_at $dt NOT NULL, updated_at $dt NOT NULL
        ){$s->engine()}");
        $s->exec("CREATE TABLE IF NOT EXISTS medication_moves (
            id {$s->pk()}, medication_id $fk NOT NULL, delta INTEGER NOT NULL, balance INTEGER NOT NULL, kind {$s->str(16)} NOT NULL,
            patient_id $fk NULL, document_id $fk NULL, user_id $fk NULL, witness_id $fk NULL, party {$s->str()} DEFAULT '',
            note {$s->str()} DEFAULT '', source {$s->str(16)} NOT NULL DEFAULT 'web', created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_med_moves', 'medication_moves', 'medication_id, created_at');
        $s->exec("CREATE TABLE IF NOT EXISTS prescription_redemptions (
            id {$s->pk()}, document_id $fk NOT NULL, line INTEGER NOT NULL, medication_id $fk NULL, quantity INTEGER NOT NULL DEFAULT 1,
            user_id $fk NULL, player {$s->str()} DEFAULT '', license {$s->str()} DEFAULT '', source {$s->str(16)} NOT NULL DEFAULT 'web', created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_rx_line', 'prescription_redemptions', 'document_id, line', true);

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach (['stock.view' => 'patients.view', 'stock.dispense' => 'entries.create', 'stock.manage' => 'documents.approve', 'btm.manage' => 'documents.approve'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = " . (int) $rankId . " AND permission = '$perm'")->fetchColumn()) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }

        // Startsortiment (Bestand 0, Items werden in der Bestandsverwaltung zugeordnet)
        if (!$s->db->query('SELECT COUNT(*) FROM medications')->fetchColumn()) {
            $now = date('Y-m-d H:i:s');
            $ins = $s->db->prepare('INSERT INTO medications (name, keywords, is_btm, unit, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ([
                ['Ibuprofen 600 mg Filmtbl. N2', 'Ibuprofen', 0], ['Ibuprofen 400 mg Filmtbl. N1', '', 0], ['Paracetamol 500 mg Tbl. N2', 'Paracetamol', 0],
                ['Metamizol 500 mg Tbl. N2', 'Metamizol, Novaminsulfon', 0], ['Tramadol 50 mg Kps. N1', 'Tramadol', 0],
                ['Morphin 10 mg Retardtbl. N1 (BtM)', 'Morphin', 1], ['Oxycodon 10 mg Retardtbl. N1 (BtM)', 'Oxycodon', 1],
                ['Fentanyl 0,1 mg/2 ml Ampulle (BtM)', 'Fentanyl', 1], ['Amoxicillin 1000 mg Filmtbl. N2', 'Amoxicillin', 0],
                ['Ondansetron 4 mg Schmelztbl. N1', 'Ondansetron', 0], ['Salbutamol Dosieraerosol 100 µg', 'Salbutamol', 0],
                ['Cetirizin 10 mg Filmtbl. N1', 'Cetirizin', 0], ['Bepanthen Wund- und Heilsalbe 20 g', 'Bepanthen', 0],
                ['Mullbinde / Verbandsmaterial', 'Verband, Mullbinde', 0],
            ] as [$name, $kw, $btm]) {
                $ins->execute([$name, $kw, $btm, $btm ? 'Stück' : 'Packung', $now, $now]);
            }
        }
    },
];
