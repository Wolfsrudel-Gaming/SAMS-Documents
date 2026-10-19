<?php
// Mitgelieferte ox_inventory-Items: Item-Datei vollständig, Bilder vorhanden, Standard-Items im Bestand eintragen
$dir = dirname(__DIR__, 2) . '/fivem/sams_tablet/install';
preg_match_all("/^\\['([a-z0-9_]+)'\\]\\s*=/m", (string) file_get_contents($dir . '/ox_inventory_items.lua'), $m);
$items = $m[1];
check(count($items) === 18 && !array_diff(['sams_tablet', 'sams_document', 'forgery_paper', 'forgery_ink'], $items), 'Item-Datei enthält Tablet, Dokument, Fälschungsmaterial und Medikamente');
check(!array_filter($items, fn($i) => !is_file("$dir/images/$i.png")), 'Zu jedem Item ein Bild');
preg_match_all("/=> '(sams_[a-z0-9_]+)'/", (string) file_get_contents(dirname(__DIR__, 2) . '/app/pharmacy.php'), $d);
check(count($d[1]) === 14 && !array_diff($d[1], $items), 'Standard-Items des Bestands sind in der Item-Datei');
texec("UPDATE medications SET item_name = '' WHERE faction_id = 1 AND name = 'Cetirizin 10 mg Filmtbl. N1'");
$chief = (new Client(TEST_BASE))->login('chief');
$chief->get('/index.php?p=stock');
check($chief->has('Standard-Items eintragen'), 'Knopf im Medikamentenbestand');
$chief->post('/index.php?p=stock', ['action' => 'default_items']);
check(tval("SELECT item_name FROM medications WHERE faction_id = 1 AND name = 'Cetirizin 10 mg Filmtbl. N1'") === 'sams_cetirizin', 'Standard-Item eingetragen');
