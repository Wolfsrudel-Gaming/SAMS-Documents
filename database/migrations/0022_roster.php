<?php
/**
 * Dienstplan: geplante Schichten mit Mindestbesetzung, Eintragungen und Tausch.
 */
return [
    'description' => 'Dienstplan',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS roster_shifts (
            id {$s->pk()}, title {$s->str()} NOT NULL DEFAULT '', department_id $fk NULL,
            starts_at $dt NOT NULL, ends_at $dt NOT NULL, min_staff INTEGER NOT NULL DEFAULT 1, max_staff INTEGER NOT NULL DEFAULT 0,
            note {$s->str()} DEFAULT '', staff_warned INTEGER NOT NULL DEFAULT 0, created_by $fk NULL, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_roster_shifts_start', 'roster_shifts', 'starts_at');
        $s->exec("CREATE TABLE IF NOT EXISTS roster_slots (
            id {$s->pk()}, shift_id $fk NOT NULL, user_id $fk NOT NULL, status {$s->str(16)} NOT NULL DEFAULT 'assigned',
            reminded INTEGER NOT NULL DEFAULT 0, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_roster_slot_unique', 'roster_slots', 'shift_id, user_id', true);
        $s->index('idx_roster_slot_user', 'roster_slots', 'user_id');

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach (['roster.view' => 'duty.clock', 'roster.signup' => 'duty.clock', 'roster.manage' => 'duty.manage'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = " . (int) $rankId . " AND permission = '$perm'")->fetchColumn()) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
