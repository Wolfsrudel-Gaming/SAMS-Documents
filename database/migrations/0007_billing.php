<?php
/**
 * Etappe 3b: Gebührenordnung (Preisliste) und Rechnungsstatus.
 */
return [
    'description' => 'Preisliste und Rechnungsstatus',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $dt = $s->datetime();

        $s->exec("CREATE TABLE IF NOT EXISTS price_list (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            code {$s->str(32)} NOT NULL DEFAULT '',
            name $str NOT NULL,
            category $str DEFAULT '',
            price_cents INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            sort INTEGER NOT NULL DEFAULT 0,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$s->engine()}");

        // Rechnungsstatus direkt am Dokument (nur für Rechnungen genutzt)
        $s->addColumn('documents', 'pay_status', "{$s->str(16)} DEFAULT ''");
        $s->addColumn('documents', 'amount_cents', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('documents', 'paid_at', "$dt NULL");
        $s->addColumn('documents', 'dunning_level', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('documents', 'dunned_at', "$dt NULL");
        $s->addColumn('documents', 'pay_note', "$str DEFAULT ''");
        $s->index('idx_documents_pay', 'documents', 'type, pay_status');

        // Bestehende Rechnungen übernehmen
        $rows = $s->db->query("SELECT id, data, issued_at FROM documents WHERE type = 'rechnung'")->fetchAll(PDO::FETCH_ASSOC);
        $upd = $s->db->prepare('UPDATE documents SET pay_status = ?, amount_cents = ?, paid_at = ? WHERE id = ?');
        foreach ($rows as $r) {
            $d = json_decode((string) $r['data'], true) ?: [];
            $net = 0.0;
            foreach ((array) ($d['items'] ?? []) as $it) {
                $net += (float) str_replace(',', '.', (string) ($it['qty'] ?: 1)) * (float) str_replace(',', '.', (string) ($it['price'] ?: 0));
            }
            $total = $net * (1 + (float) str_replace(',', '.', (string) ($d['tax'] ?? 0)) / 100);
            $paid = !empty($d['paid']);
            $upd->execute([$paid ? 'paid' : 'open', (int) round($total * 100), $paid ? $r['issued_at'] : null, $r['id']]);
        }

        // Start-Gebührenordnung (frei änderbar)
        if (!(int) $s->db->query('SELECT COUNT(*) FROM price_list')->fetchColumn()) {
            $now = date('Y-m-d H:i:s');
            $ins = $s->db->prepare('INSERT INTO price_list (code, name, category, price_cents, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $i = 0;
            foreach ([
                ['R01', 'Notfalleinsatz Rettungswagen', 'Rettungsdienst', 50000],
                ['R02', 'Notarzteinsatz', 'Rettungsdienst', 75000],
                ['R03', 'Luftrettung (Helikopter)', 'Rettungsdienst', 250000],
                ['R04', 'Krankentransport', 'Rettungsdienst', 20000],
                ['A01', 'Ambulante Erstversorgung', 'Ambulant', 15000],
                ['A02', 'Ärztliche Untersuchung', 'Ambulant', 10000],
                ['A03', 'Wundversorgung / Naht', 'Ambulant', 20000],
                ['A04', 'Gipsverband / Schienung', 'Ambulant', 25000],
                ['D01', 'Röntgen', 'Diagnostik', 30000],
                ['D02', 'CT', 'Diagnostik', 80000],
                ['D03', 'Labor (Blutbild)', 'Diagnostik', 8000],
                ['S01', 'Stationäre Behandlung (pro Tag)', 'Stationär', 40000],
                ['S02', 'Operation', 'Stationär', 300000],
                ['B01', 'Ausstellung Attest', 'Bescheinigungen', 5000],
                ['B02', 'Tauglichkeitsuntersuchung', 'Bescheinigungen', 25000],
                ['B03', 'Blutentnahme auf behördliche Anordnung', 'Bescheinigungen', 15000],
                ['B04', 'Psychologisches Gutachten', 'Bescheinigungen', 60000],
            ] as [$code, $name, $cat, $cents]) {
                $ins->execute([$code, $name, $cat, $cents, $i += 10, $now, $now]);
            }
        }

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['prices.manage' => 'settings.manage', 'invoices.manage' => 'documents.issue.rechnung'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
