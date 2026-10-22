<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Standorte mit eigenem Briefkopf. Leere Felder übernehmen die Angaben aus den
 * Einstellungen. Beim Ausstellen wird der Standort ins Dokument eingefroren,
 * spätere Adressänderungen verändern bestehende Dokumente also nicht.
 */

const LOCATION_FIELDS = [
    'org_sub'    => 'Einrichtung (Zeile unter dem Namen)',
    'org_street' => 'Straße und Hausnummer',
    'org_city'   => 'PLZ und Ort',
    'city'       => 'Ort für „Ort, Datum“',
    'phone'      => 'Telefon',
    'fax'        => 'Fax',
    'email'      => 'E-Mail',
    'bsnr'       => 'Betriebsstättennummer (BSNR)',
];

function locations(bool $activeOnly = true): array
{
    static $cache = [];
    $k = (int) $activeOnly . ':' . fid();
    if (!isset($cache[$k])) {
        try {
            $cache[$k] = all('SELECT * FROM locations WHERE ' . fw() . ($activeOnly ? ' AND active = 1' : '') . ' ORDER BY sort, name');
        } catch (PDOException $e) {
            $cache[$k] = [];
        }
    }
    return $cache[$k];
}

/** Briefkopf-Angaben eines Standorts (für das Einfrieren im Dokument). */
function location_snapshot($id): ?array
{
    if (!$id) {
        return null;
    }
    foreach (locations(false) as $l) {
        if ((int) $l['id'] === (int) $id) {
            return array_intersect_key($l, array_flip(array_merge(['id', 'name', 'short'], array_keys(LOCATION_FIELDS))));
        }
    }
    return null;
}

/** Standard-Standort einer Person (eigener oder erster aktiver). */
function user_location_id(array $u): ?int
{
    $locs = locations();
    if (!$locs) {
        return null;
    }
    foreach ($locs as $l) {
        if ((int) $l['id'] === (int) ($u['location_id'] ?? 0)) {
            return (int) $l['id'];
        }
    }
    return (int) $locs[0]['id'];
}

/** Während des Renderns gilt der Briefkopf dieses Standorts (siehe org()). */
function with_location(?array $loc, callable $fn)
{
    $prev = $GLOBALS['sams_location'] ?? null;
    $GLOBALS['sams_location'] = $loc;
    try {
        return $fn();
    } finally {
        $GLOBALS['sams_location'] = $prev;
    }
}
