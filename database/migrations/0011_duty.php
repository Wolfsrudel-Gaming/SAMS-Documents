<?php
/**
 * Etappe 4: Dienstzeiterfassung (On-/Off-Duty im Spiel, Stempeln im Browser).
 */
return [
    'description' => 'Dienstzeiten',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS duty_sessions (
            id $pk,
            user_id $fk NOT NULL,
            started_at $dt NOT NULL,
            ended_at $dt NULL,
            source {$s->str(16)} NOT NULL DEFAULT 'web',
            end_source {$s->str(16)} NOT NULL DEFAULT '',
            note $str DEFAULT '',
            edited_by $fk NULL,
            created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_duty_user', 'duty_sessions', 'user_id, started_at');
        $s->index('idx_duty_open', 'duty_sessions', 'ended_at');

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['duty.clock' => 'patients.view', 'duty.view' => 'patients.view', 'duty.manage' => 'users.edit'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
