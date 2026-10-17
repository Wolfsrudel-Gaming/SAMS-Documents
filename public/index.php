<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';
start_session();

$routes = [
    // Seite            => Mindestrolle (null = öffentlich)
    'login'             => null,
    'logout'            => 'trainee',
    'dashboard'         => 'trainee',
    'patients'          => 'trainee',
    'patient'           => 'trainee',
    'patient_edit'      => 'trainee',
    'patient_print'     => 'trainee',
    'entry_edit'        => 'trainee',
    'documents'         => 'trainee',
    'document_new'      => 'medic',
    'document'          => 'trainee',
    'users'             => 'admin',
    'user_edit'         => 'admin',
    'settings'          => 'admin',
    'audit'             => 'admin',
    'system'            => 'admin',
    'profile'           => 'trainee',
];

$page = $_GET['p'] ?? 'dashboard';
if (!is_string($page) || !array_key_exists($page, $routes)) {
    not_found('Diese Seite existiert nicht.');
}

if ($routes[$page] !== null) {
    require_role($routes[$page]);
}

require SAMS_ROOT . '/app/pages/' . $page . '.php';
