<?php
/**
 * Andock-Schnittstelle für eigene Skripte und Tools: Integrationsschlüssel mit Rechten (Scopes) und Fraktionen,
 * Ereignisprotokoll für Abfrage und Webhooks.
 */
return [
    'description' => 'Integrationen (Schlüssel, Rechte, Webhooks, Ereignisse)',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $s->exec("CREATE TABLE IF NOT EXISTS integrations (
            id {$s->pk()}, name $str NOT NULL, token_hash {$s->str(64)} NOT NULL, token_prefix {$s->str(16)} DEFAULT '', scopes {$s->text()}, factions {$s->text()},
            event_filter $str DEFAULT '', webhook_url {$s->str(500)} DEFAULT '', webhook_secret {$s->str(64)} DEFAULT '', notes $str DEFAULT '',
            active INTEGER NOT NULL DEFAULT 1, last_used_at $dt NULL, last_ip {$s->str(64)} DEFAULT '', last_event_id INTEGER NOT NULL DEFAULT 0,
            webhook_error $str DEFAULT '', webhook_failures INTEGER NOT NULL DEFAULT 0, webhook_at $dt NULL, created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        ){$s->engine()}");
        $s->index('idx_integrations_token', 'integrations', 'token_hash');
        $s->exec("CREATE TABLE IF NOT EXISTS integration_events (
            id {$s->pk()}, event {$s->str(64)} NOT NULL, faction_id $fk NULL, server_id $fk NULL, integration_id $fk NULL, payload {$s->text()}, created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_integration_events_at', 'integration_events', 'created_at');
    },
];
