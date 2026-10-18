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
    'ping'            => '',
    'notifications'   => '',
    'patients'        => 'patients.view',
    'patient'         => 'patients.view',
    'case'            => 'patients.view',
    'case_edit'       => 'cases.manage',
    'patient_edit'    => 'patients.edit',
    'patient_print'   => 'patients.view',
    'attachment'      => 'patients.view',
    'patient_merge'   => 'patients.merge',
    'entry_edit'      => 'entries.create',
    'documents'       => 'documents.view',
    'document'        => 'documents.view',
    'document_new'    => 'documents.issue.*',
    'drafts'          => 'documents.issue.*|documents.approve|documents.drafts_all',
    'draft'           => 'documents.issue.*|documents.approve|documents.drafts_all',
    'doc_types'       => 'settings.manage',
    'invoices'        => 'invoices.manage',
    'shared'          => '',
    'prices'          => 'prices.manage',
    'templates'       => 'templates.manage',
    'duty'            => 'duty.clock|duty.view|duty.manage',
    'roster'          => 'roster.view|roster.manage',
    'stock'           => 'stock.view|stock.manage',
    'locations'       => 'settings.manage',
    'factions'        => '',
    'wanted'          => 'wanted.view|wanted.manage',
    'custody'         => 'custody.view|custody.manage',
    'evidence'        => 'evidence.view|evidence.manage',
    'evidence_item'   => 'evidence.view|evidence.manage',
    'vehicles'        => 'vehicles.view|vehicles.manage',
    'licenses'        => 'licenses.view|licenses.manage',
    'court'           => 'court.view|court.manage',
    'objects'         => 'objects.view|objects.manage',
    'object'          => 'objects.view|objects.manage',
    'object_file'     => 'objects.view|objects.manage',
    'joint'           => 'joint.view|joint.write',
    'joint_report'    => 'joint.view|joint.write',
    'equipment'       => 'equipment.view|equipment.manage',
    'dispatch'        => 'documents.view',
    'breathing'       => 'breathing.view|breathing.manage',
    'requests'        => 'requests.view|requests.manage',
    'btm'             => 'stock.view|btm.manage',
    'personnel'       => '',
    'training'        => 'training.view|training.manage',
    'course'          => 'training.view|training.manage',
    'exam'            => 'training.view|training.manage',
    'course_edit'     => 'training.manage',
    'wiki'            => 'wiki.view|wiki.edit',
    'wiki_page'       => 'wiki.view|wiki.edit',
    'wiki_edit'       => 'wiki.edit',
    'wiki_lists'      => 'lists.manage',
    'board'           => 'board.view|board.manage',
    'messages'        => 'messages.use',
    'portal_admin'    => 'portal.manage|portal.applications',
    'discord_login'   => null,
    'discord'         => 'discord.manage',
    'stats'           => 'stats.view',
    'report'          => 'stats.view',
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
