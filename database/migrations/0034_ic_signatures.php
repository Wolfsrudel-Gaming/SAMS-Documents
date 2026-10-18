<?php
/**
 * Unterschriften im Spiel (IC): Unterschriftsfelder eines ausgestellten Dokuments werden auf dem Ausdruck oder im Tablet
 * unterschrieben. Jede Unterschrift wird protokolliert; nach dem Abschluss ist das Dokument endgültig (mit Prüfsumme).
 */
return [
    'description' => 'Unterschriften im Spiel und Abschluss von Dokumenten',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $s->exec("CREATE TABLE IF NOT EXISTS document_signatures (
            id {$s->pk()}, document_id $fk NOT NULL, field {$s->str(64)} NOT NULL, label {$s->str()} DEFAULT '', signer_name {$s->str()} NOT NULL DEFAULT '',
            signer_citizen_id {$s->str(64)} DEFAULT '', source {$s->str(16)} NOT NULL DEFAULT 'game', user_id $fk NULL, player {$s->str()} DEFAULT '',
            license {$s->str()} DEFAULT '', image_hash {$s->str(64)} NOT NULL DEFAULT '', created_at $dt NOT NULL
        ){$s->engine()}");
        $s->index('idx_docsig_doc', 'document_signatures', 'document_id');
        $s->addColumn('documents', 'finalized_at', "$dt NULL");
        $s->addColumn('documents', 'finalized_by', "$fk NULL");
        $s->addColumn('documents', 'finalized_note', $s->str() . " DEFAULT ''");
        $s->addColumn('documents', 'final_hash', $s->str(64) . " DEFAULT ''");
    },
];
