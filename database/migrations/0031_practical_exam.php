<?php
/**
 * Hardcore-RP: Bestandene Online-Prüfung = nur Theorie. Qualifikation und Zertifikat gibt es erst
 * nach der praktischen Prüfung, die eine Ausbilderin / ein Ausbilder bestätigt.
 */
return [
    'description' => 'Praktische Prüfung',
    'up' => function (Schema $s): void {
        $s->addColumn('training_attempts', 'practical', $s->str(16) . " NOT NULL DEFAULT ''"); // '', passed, failed
        $s->addColumn('training_attempts', 'practical_at', $s->datetime() . ' NULL');
        $s->addColumn('training_attempts', 'practical_by', $s->fk() . ' NULL');
        $s->addColumn('training_attempts', 'practical_note', $s->str() . " DEFAULT ''");
    },
];
