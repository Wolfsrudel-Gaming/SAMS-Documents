<?php
/**
 * Etappe 5d: Schwarzes Brett mit Lesebestätigung und interne Nachrichten.
 */
return [
    'description' => 'Schwarzes Brett und Nachrichten',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $e = $s->engine();
        $s->exec("CREATE TABLE IF NOT EXISTS announcements (
            id $pk, faction_id $fk NOT NULL DEFAULT 1, title $str NOT NULL, content $text,
            priority {$s->str(16)} NOT NULL DEFAULT 'normal', department_id $fk NULL, min_level INTEGER NOT NULL DEFAULT 0,
            require_ack INTEGER NOT NULL DEFAULT 0, pinned INTEGER NOT NULL DEFAULT 0, expires_at $dt NULL, archived INTEGER NOT NULL DEFAULT 0,
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS announcement_reads (
            id $pk, announcement_id $fk NOT NULL, user_id $fk NOT NULL, read_at $dt NOT NULL, acknowledged_at $dt NULL
        ){$e}");
        $s->index('idx_ann_reads', 'announcement_reads', 'announcement_id, user_id');
        $s->exec("CREATE TABLE IF NOT EXISTS message_threads (
            id $pk, subject $str NOT NULL, created_by $fk NULL, created_at $dt NOT NULL, last_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS thread_members (
            id $pk, thread_id $fk NOT NULL, user_id $fk NOT NULL, last_read_id INTEGER NOT NULL DEFAULT 0, archived INTEGER NOT NULL DEFAULT 0
        ){$e}");
        $s->index('idx_thread_members', 'thread_members', 'user_id, thread_id');
        $s->exec("CREATE TABLE IF NOT EXISTS messages (
            id $pk, thread_id $fk NOT NULL, sender_id $fk NULL, body $text, created_at $dt NOT NULL
        ){$e}");
        $s->index('idx_messages_thread', 'messages', 'thread_id, id');

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['board.view' => 'duty.clock', 'messages.use' => 'duty.clock', 'board.manage' => 'users.edit'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
