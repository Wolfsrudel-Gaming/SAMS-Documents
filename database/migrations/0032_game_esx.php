<?php
/**
 * Spielserver-Anbindung (ESX): Rechnungen ins Spiel (esx_billing) und ihr Zahlungsstand.
 * Die Einstellungen (Society-Konto, Jobs, Befehle, Schalter) liegen in settings und werden im Websystem gepflegt.
 */
return [
    'description' => 'Spielserver: Rechnungen ins Spiel',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS game_bills (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, document_id $fk NOT NULL, citizen_id {$s->str(64)} NOT NULL DEFAULT '',
            amount INTEGER NOT NULL DEFAULT 0, society {$s->str(64)} NOT NULL DEFAULT '', label {$s->str()} NOT NULL DEFAULT '',
            status {$s->str(16)} NOT NULL DEFAULT 'pending', esx_id INTEGER NULL, error {$s->str()} DEFAULT '',
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL, sent_at $dt NULL, paid_at $dt NULL
        ){$s->engine()}");
        $s->index('idx_game_bills_doc', 'game_bills', 'document_id');
        $s->index('idx_game_bills_status', 'game_bills', 'status');
    },
];
