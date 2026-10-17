<?php
defined('SAMS_ROOT') || exit;
/** @var string $content */
$user = current_user();
$current = $_GET['p'] ?? 'dashboard';
$orgShort = setting('org_short', 'SAMS');
$navGroups = [
    'Klinik' => [
        'dashboard'    => ['Übersicht', '', 'home'],
        'patients'     => ['Patientenakten', 'patients.view', 'patients'],
        'documents'    => ['Dokumente', 'documents.view', 'docs'],
        'document_new' => ['Neues Dokument', 'documents.issue.*', 'doc-plus'],
    ],
    'Verwaltung' => [
        'users'    => ['Personal', 'users.view', 'users'],
        'ranks'    => ['Ränge & Rechte', 'ranks.manage', 'shield'],
        'org'      => ['Abteilungen & Qualifikationen', 'departments.manage|qualifications.manage', 'folder'],
        'settings' => ['Einstellungen', 'settings.manage', 'settings'],
        'audit'    => ['Zugriffsprotokoll', 'audit.view', 'log'],
        'system'   => ['System & Updates', 'system.manage', 'server'],
    ],
];
// Unterseiten der passenden Navigation zuordnen
$activeMap = ['patient' => 'patients', 'patient_edit' => 'patients', 'patient_print' => 'patients', 'entry_edit' => 'patients',
    'document' => 'documents', 'user_edit' => 'users', 'rank_edit' => 'ranks', 'password_change' => 'profile', 'twofa' => 'profile'];
$active = $activeMap[$current] ?? $current;
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Übersicht') . ' · ' . $orgShort) ?></title>
<link rel="icon" href="<?= e(setting('logo', 'assets/img/logo.svg')) ?>">
<link rel="stylesheet" href="assets/css/app.css?v=<?= e(SAMS_VERSION) ?>">
<?php if (!empty($withDocumentCss)): ?>
<link rel="stylesheet" href="assets/css/document.css?v=<?= e(SAMS_VERSION) ?>">
<?php endif; ?>
<?= theme_style() ?>
</head>
<body class="<?= $user ? 'has-nav' : 'no-nav' ?> page-<?= e($current) ?>">
<?php if ($user): ?>
<header class="topbar">
    <button class="nav-toggle" type="button" aria-label="Menü"><?= icon('menu') ?></button>
    <a class="brand" href="<?= e(url('dashboard')) ?>">
        <img src="<?= e(setting('logo', 'assets/img/logo.svg')) ?>" alt="">
        <span class="brand-name"><?= e($orgShort) ?></span>
        <span class="brand-sub"><?= e(setting('org_sub', 'Pillbox Hill Medical Center')) ?></span>
    </a>
    <form class="quicksearch" action="index.php" role="search">
        <input type="hidden" name="p" value="search">
        <?= icon('search') ?>
        <input name="q" id="quicksearch" placeholder="Patient, Bürger-ID, Dokument-Nr. oder Prüfcode …" autocomplete="off">
        <kbd>F2</kbd>
    </form>
    <div class="topbar-right">
        <a class="userchip" href="<?= e(url('profile')) ?>" title="Mein Profil">
            <span class="avatar"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
            <span class="who"><strong><?= e(user_display($user)) ?></strong><small><?= e(user_rank_name($user)) ?></small></span>
        </a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="iconbtn" title="Abmelden"><?= icon('logout') ?></button></form>
    </div>
</header>
<nav class="sidenav">
    <?php foreach ($navGroups as $group => $items):
        $visible = array_filter($items, fn($i) => $i[1] === '' || can($i[1]));
        if (!$visible) continue; ?>
        <div class="nav-group"><?= e($group) ?></div>
        <?php foreach ($visible as $p => [$label, $role, $ic]): ?>
            <a href="<?= e(url($p)) ?>" title="<?= e($label) ?>" class="<?= $active === $p ? 'active' : '' ?>"><?= icon($ic) ?><span><?= e($label) ?></span></a>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <div class="nav-group">Extern</div>
    <a href="verify.php" target="_blank"><?= icon('shield') ?><span>Echtheitsprüfung</span></a>
</nav>
<?php endif; ?>
<main class="main">
    <?php foreach (flashes() as [$type, $msg]): ?>
        <div class="flash <?= e($type) ?>"><?= e($msg) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
</main>
<?php if ($user): ?>
<footer class="statusbar">
    <span class="sb-item"><span class="dot ok"></span>Verbunden</span>
    <span class="sb-item"><?= icon('user') ?><?= e($user['username']) ?> · <?= e(user_rank_name($user)) ?></span>
    <span class="sb-item hide-sm"><?= e(setting('org_name', 'San Andreas Medical Services')) ?></span>
    <span class="sb-spacer"></span>
    <span class="sb-item hide-sm"><?= e(version_label()) ?></span>
    <span class="sb-item"><?= icon('clock') ?><span data-clock><?= e(date('d.m.Y H:i')) ?></span></span>
</footer>
<?php endif; ?>
<?= datalist_html() ?>
<script src="assets/js/app.js?v=<?= e(SAMS_VERSION) ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="<?= e($s) ?>?v=<?= e(SAMS_VERSION) ?>"></script>
<?php endforeach; ?>
</body>
</html>
