<?php
/**
 * Etappe 7: Warteschlange für Discord-Nachrichten, Rechte für Statistik und Discord.
 */
return [
    'description' => 'Discord-Warteschlange, Statistik',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $text = $s->text();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS discord_queue (
            id $pk, kind {$s->str(16)} NOT NULL, target {$s->str(255)} NOT NULL, payload $text,
            attempts INTEGER NOT NULL DEFAULT 0, last_error {$s->str(255)} DEFAULT '', created_at $dt NOT NULL, sent_at $dt NULL
        ){$s->engine()}");
        $s->index('idx_discord_queue', 'discord_queue', 'sent_at, attempts');

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['stats.view' => 'users.edit', 'discord.manage' => 'settings.manage'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
