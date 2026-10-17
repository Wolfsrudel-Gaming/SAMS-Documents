<?php
/**
 * Inhalte für weitere Fraktionen: Haft-Einheiten im Straf-/Bußgeldkatalog,
 * zusätzliche Aktenfelder (z. B. Aliasnamen, Gefährdungshinweise, Führerschein).
 */
return [
    'description' => 'Inhalte für weitere Fraktionen',
    'up' => function (Schema $s): void {
        $s->addColumn('price_list', 'jail', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('patients', 'extra', $s->text() . ' NULL');
    },
];
