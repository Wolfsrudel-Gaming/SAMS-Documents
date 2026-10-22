<?php
/** Koordinaten an Objektplänen: Objekte erscheinen in der Leitstelle (tetrascript) als POI auf der Karte. */
return [
    'description' => 'Objektpläne mit Koordinaten',
    'up' => function (Schema $s): void {
        $s->addColumn('objects', 'x', 'DOUBLE NULL');
        $s->addColumn('objects', 'y', 'DOUBLE NULL');
        $s->addColumn('objects', 'z', 'DOUBLE NULL');
    },
];
