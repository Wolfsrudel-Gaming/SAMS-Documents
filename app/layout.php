<?php
defined('SAMS_ROOT') || exit;
/** @var string $content */
$user = current_user();
$current = $_GET['p'] ?? 'dashboard';
$orgShort = setting('org_short', 'SAMS');
$nav = [
    'dashboard' => ['Übersicht', 'trainee'],
    'patients'  => ['Patientenakten', 'trainee'],
    'documents' => ['Dokumente', 'trainee'],
    'document_new' => ['Neues Dokument', 'medic'],
];
$adminNav = [
    'users'    => ['Personal', 'admin'],
    'settings' => ['Einstellungen', 'admin'],
    'audit'    => ['Zugriffsprotokoll', 'admin'],
    'system'   => ['System & Updates', 'admin'],
];
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Übersicht') . ' · ' . $orgShort . ' Dokumentensystem') ?></title>
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/css/app.css?v=<?= SAMS_VERSION ?>">
<?php if (!empty($withDocumentCss)): ?>
<link rel="stylesheet" href="assets/css/document.css?v=<?= SAMS_VERSION ?>">
<?php endif; ?>
</head>
<body class="<?= $user ? 'has-nav' : 'no-nav' ?> page-<?= e($current) ?>">
<?php if ($user): ?>
<aside class="sidebar">
    <a class="brand" href="<?= e(url('dashboard')) ?>">
        <img src="<?= e(setting('logo', 'assets/img/logo.svg')) ?>" alt="">
        <span><strong><?= e($orgShort) ?></strong><small>Dokumente &amp; Akten</small></span>
    </a>
    <nav>
        <?php foreach ($nav as $p => [$label, $role]): if (!can($role)) continue; ?>
            <a href="<?= e(url($p)) ?>" class="<?= $current === $p ? 'active' : '' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <?php if (can('admin')): ?>
            <div class="nav-sep">Verwaltung</div>
            <?php foreach ($adminNav as $p => [$label, $role]): ?>
                <a href="<?= e(url($p)) ?>" class="<?= $current === $p ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="nav-sep">Öffentlich</div>
        <a href="verify.php" target="_blank">Echtheitsprüfung ↗</a>
    </nav>
    <div class="me">
        <a href="<?= e(url('profile')) ?>" class="<?= $current === 'profile' ? 'active' : '' ?>">
            <strong><?= e(user_display($user)) ?></strong>
            <small><?= e($user['rank_name'] ?: ROLES[$user['role']]) ?></small>
        </a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="link">Abmelden</button></form>
    </div>
</aside>
<button class="nav-toggle" type="button" aria-label="Menü">☰</button>
<?php endif; ?>
<main class="main">
    <?php foreach (flashes() as [$type, $msg]): ?>
        <div class="flash <?= e($type) ?>"><?= e($msg) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>
<?= datalist_html() ?>
<script src="assets/js/app.js?v=<?= SAMS_VERSION ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="<?= e($s) ?>?v=<?= SAMS_VERSION ?>"></script>
<?php endforeach; ?>
</body>
</html>
