<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
start_session();

/**
 * Seite => benötigtes Recht.
 * null = öffentlich, '' = nur angemeldet, 'a|b' = eines der Rechte, 'x.*' = irgendein Recht mit diesem Präfix.
 */
$routes = [
    'login'           => null,
    'logout'          => '',
    'password_change' => '',
    'twofa'           => '',
    'dashboard'       => '',
    'profile'         => '',
    'search'          => '',
    'patients'        => 'patients.view',
    'patient'         => 'patients.view',
    'patient_edit'    => 'patients.edit',
    'patient_print'   => 'patients.view',
    'entry_edit'      => 'entries.create',
    'documents'       => 'documents.view',
    'document'        => 'documents.view',
    'document_new'    => 'documents.issue.*',
    'users'           => 'users.view',
    'user_edit'       => 'users.view',
    'ranks'           => 'ranks.manage',
    'rank_edit'       => 'ranks.manage',
    'org'             => 'departments.manage|qualifications.manage',
    'settings'        => 'settings.manage',
    'audit'           => 'audit.view',
    'system'          => 'system.manage',
    'printers'        => 'printers.manage',
];

$page = $_GET['p'] ?? 'dashboard';
if (!is_string($page) || !array_key_exists($page, $routes)) {
    not_found('Diese Seite existiert nicht.');
}

if ($routes[$page] !== null) {
    require_login();
    if ($routes[$page] !== '') {
        require_perm($routes[$page]);
    }
}

require SAMS_ROOT . '/app/pages/' . $page . '.php';
