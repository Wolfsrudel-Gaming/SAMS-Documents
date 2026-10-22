<?php
/**
 * Ratenbegrenzung für öffentliche Seiten (Echtheitsprüfung, Freigabe-Links, Bürgerportal).
 */
return [
    'description' => 'Ratenbegrenzung',
    'up' => function (Schema $s): void {
        $s->exec("CREATE TABLE IF NOT EXISTS rate_hits (
            id {$s->pk()}, bucket {$s->str(120)} NOT NULL, created_at {$s->datetime()} NOT NULL
        ){$s->engine()}");
        $s->index('idx_rate_hits', 'rate_hits', 'bucket, created_at');
    },
];
