<?php
/**
 * SAMS Dokumenten- & Aktensystem – Konfiguration
 *
 * Diese Datei nach "config.php" kopieren und anpassen.
 * install.php erzeugt config.php auf Wunsch automatisch.
 */
return [
    // Öffentliche Basis-URL ohne abschließenden Slash (wird für QR-Codes /
    // Verifizierungslinks auf den Dokumenten verwendet).
    'base_url' => 'https://sams.example.com',

    // Datenbank: 'sqlite' (keine Einrichtung nötig) oder 'mysql'
    'db' => [
        'driver'   => 'sqlite',
        'sqlite'   => __DIR__ . '/data/sams.sqlite',
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'database' => 'sams',
        'username' => 'sams',
        'password' => '',
        'charset'  => 'utf8mb4',
    ],

    // Session-Cookie. Wird das System im Spiel per NUI/iframe (z.B. Tablet-
    // Resource) von einer anderen Domain eingebunden, 'None' setzen –
    // dann ist HTTPS zwingend erforderlich.
    'cookie_samesite' => 'Lax',

    // API-Schlüssel für api.php (z.B. für eine FiveM-Resource). Leer = API aus.
    'api_key' => '',

    // Zeitzone für Datum/Uhrzeit auf den Dokumenten
    'timezone' => 'Europe/Berlin',
];
