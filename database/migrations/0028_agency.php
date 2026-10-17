<?php
/**
 * Zusammenarbeit der Fraktionen: Fahndung (Personen/Fahrzeuge, Haftbefehle der Justiz),
 * Behördenanfragen und Dokument-Freigaben an eine ganze Behörde.
 */
return [
    'description' => 'Fahndung, Behördenanfragen, Freigaben an Behörden',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS wanted (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, kind {$s->str(16)} NOT NULL DEFAULT 'person',
            patient_id $fk NULL, citizen_id {$s->str(64)} DEFAULT '', name $str DEFAULT '', plate {$s->str(16)} DEFAULT '', vehicle $str DEFAULT '',
            reason {$s->text()}, danger INTEGER NOT NULL DEFAULT 0, status {$s->str(16)} NOT NULL DEFAULT 'open', source {$s->str(16)} NOT NULL DEFAULT 'manual',
            document_id $fk NULL, source_faction_id $fk NULL, expires_at $dt NULL,
            created_by $fk NULL, created_at $dt NOT NULL, closed_at $dt NULL, closed_by $fk NULL, close_note $str DEFAULT ''
        ){$s->engine()}");
        $s->index('idx_wanted_faction', 'wanted', 'faction_id, status');
        $s->index('idx_wanted_citizen', 'wanted', 'citizen_id');
        $s->index('idx_wanted_plate', 'wanted', 'plate');
        $s->exec("CREATE TABLE IF NOT EXISTS agency_requests (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, to_faction_id $fk NOT NULL, subject $str NOT NULL, body {$s->text()},
            citizen_id {$s->str(64)} DEFAULT '', person $str DEFAULT '', reference $str DEFAULT '', urgent INTEGER NOT NULL DEFAULT 0,
            status {$s->str(16)} NOT NULL DEFAULT 'open', answer {$s->text()}, answered_by $fk NULL, answered_at $dt NULL,
            created_by $fk NULL, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_agreq_to', 'agency_requests', 'to_faction_id, status');
        $s->index('idx_agreq_from', 'agency_requests', 'faction_id, status');
        $s->addColumn('document_shares', 'target_faction_id', "$fk NULL");
        $s->addColumn('document_shares', 'request_id', "$fk NULL");

        // Bestehende Ränge: Anfragen sehen, wer Akten sieht; beantworten/stellen, wer Dokumente freigeben oder gegenzeichnen darf
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach (['requests.view' => ['patients.view'], 'requests.manage' => ['documents.share', 'documents.approve']] as $perm => $bases) {
            foreach ($bases as $basis) {
                foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                    if (!$s->db->query('SELECT COUNT(*) FROM rank_permissions WHERE rank_id = ' . (int) $rankId . " AND permission = '$perm'")->fetchColumn()) {
                        $add->execute([$rankId, $perm]);
                    }
                }
            }
        }
    },
];
