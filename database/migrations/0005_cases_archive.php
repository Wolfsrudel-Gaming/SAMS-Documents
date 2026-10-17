<?php
/**
 * Etappe 2: Fallakten, Archivierung von Patientenakten und
 * Abteilungs-Sichtbarkeit von Akteneinträgen.
 */
return [
    'description' => 'Fallakten, Archiv, Abteilungs-Sichtbarkeit von Einträgen',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();

        $s->exec("CREATE TABLE IF NOT EXISTS cases (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            patient_id $fk NOT NULL,
            number $str NOT NULL DEFAULT '',
            title $str NOT NULL,
            kind {$s->str(32)} NOT NULL DEFAULT 'notfall',
            status {$s->str(16)} NOT NULL DEFAULT 'open',
            department_id $fk NULL,
            incident_at $dt NULL,
            location $str DEFAULT '',
            incident $text,
            diagnosis $text,
            summary $text,
            created_by $fk NULL,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL,
            closed_at $dt NULL,
            closed_by $fk NULL
        ){$s->engine()}");
        $s->index('idx_cases_patient', 'cases', 'patient_id, status');

        $s->addColumn('entries', 'case_id', "$fk NULL");
        $s->addColumn('entries', 'department_id', "$fk NULL");
        $s->addColumn('documents', 'case_id', "$fk NULL");
        $s->index('idx_entries_case', 'entries', 'case_id');
        $s->index('idx_documents_case', 'documents', 'case_id');

        $s->addColumn('patients', 'archived_at', "$dt NULL");
        $s->addColumn('patients', 'archived_reason', "$str DEFAULT ''");
        $s->addColumn('patients', 'archived_by', "$fk NULL");

        // Neue Rechte an passende Ränge vergeben
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $with = fn(string $perm) => $s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$perm'")->fetchAll(PDO::FETCH_COLUMN);
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach ([
            'cases.manage' => 'entries.create',             // Fälle anlegen/abschließen: wer Einträge schreibt
            'patients.archive' => 'patients.delete',        // Archivieren: wer löschen darf
            'entries.all_departments' => 'settings.manage', // Einträge aller Abteilungen: Leitung
        ] as $perm => $basis) {
            foreach ($with($basis) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
