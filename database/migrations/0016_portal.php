<?php
/**
 * Etappe 6: Bürgerportal – Bürgerkonten (Discord), Termine, Anträge, Bewerbungen.
 */
return [
    'description' => 'Bürgerportal',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $e = $s->engine();
        $s->exec("CREATE TABLE IF NOT EXISTS citizens (
            id $pk, discord_id {$s->str(32)} NOT NULL UNIQUE, discord_name $str DEFAULT '', avatar $str DEFAULT '',
            ic_name $str DEFAULT '', phone {$s->str(40)} DEFAULT '', patient_id $fk NULL, link_code {$s->str(16)} NULL, link_expires $dt NULL,
            linked_by $fk NULL, linked_at $dt NULL, blocked INTEGER NOT NULL DEFAULT 0, created_at $dt NOT NULL, last_login $dt NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS appointments (
            id $pk, citizen_id $fk NOT NULL, patient_id $fk NULL, kind $str NOT NULL, preferred $str DEFAULT '', note $text,
            status {$s->str(16)} NOT NULL DEFAULT 'requested', scheduled_at $dt NULL, location $str DEFAULT '', staff_note $text,
            handled_by $fk NULL, handled_at $dt NULL, created_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS portal_requests (
            id $pk, citizen_id $fk NOT NULL, patient_id $fk NULL, kind {$s->str(32)} NOT NULL, subject $str DEFAULT '', text $text,
            status {$s->str(16)} NOT NULL DEFAULT 'open', reply $text, handled_by $fk NULL, handled_at $dt NULL, created_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS applications (
            id $pk, citizen_id $fk NOT NULL, ic_name $str NOT NULL, age INTEGER NOT NULL DEFAULT 0, phone {$s->str(40)} DEFAULT '',
            department $str DEFAULT '', experience $text, motivation $text, availability $str DEFAULT '',
            status {$s->str(16)} NOT NULL DEFAULT 'new', staff_note $text, handled_by $fk NULL, handled_at $dt NULL, created_at $dt NOT NULL
        ){$e}");
        $s->index('idx_citizens_patient', 'citizens', 'patient_id');
        $s->index('idx_appointments_status', 'appointments', 'status');
        $s->index('idx_requests_status', 'portal_requests', 'status');

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['portal.manage' => 'patients.edit', 'portal.link' => 'patients.edit', 'portal.applications' => 'users.edit'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
