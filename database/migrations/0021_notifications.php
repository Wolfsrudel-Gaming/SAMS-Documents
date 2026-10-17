<?php
/**
 * Persönliche Benachrichtigungen (Glocke) und optionale Weiterleitung per Discord-DM.
 */
return [
    'description' => 'Benachrichtigungen',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $s->exec("CREATE TABLE IF NOT EXISTS notifications (
            id {$s->pk()}, user_id $fk NOT NULL, kind {$s->str(32)} NOT NULL DEFAULT '', title {$s->str()} NOT NULL,
            body {$s->text()}, link {$s->str()} DEFAULT '', read_at {$s->datetime()} NULL, created_at {$s->datetime()} NOT NULL
        ){$s->engine()}");
        $s->index('idx_notifications_user', 'notifications', 'user_id, read_at');
        $s->addColumn('users', 'notify_discord', 'INTEGER NOT NULL DEFAULT 0');
    },
];
