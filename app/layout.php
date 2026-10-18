<?php
defined('SAMS_ROOT') || exit;
/** @var string $content */
$user = current_user();
$current = $_GET['p'] ?? 'dashboard';
$orgShort = org('org_short');
$navGroups = [
    (faction_kind() === 'medical' ? 'Klinik' : 'Dienst') => [
        'dashboard'    => ['Übersicht', '', 'home'],
        'board'        => ['Schwarzes Brett', 'board.view|board.manage', 'docs'],
        'messages'     => ['Nachrichten', 'messages.use', 'external'],
        'patients'     => [term('records'), 'patients.view', 'patients'],
        'wanted'       => ['Fahndung', 'wanted.view|wanted.manage', 'search'],
        'requests'     => ['Behördenanfragen', 'requests.view|requests.manage', 'external'],
        'documents'    => ['Dokumente', 'documents.view', 'docs'],
        'document_new' => ['Neues Dokument', 'documents.issue.*', 'doc-plus'],
        'drafts'       => ['Entwürfe & Freigaben', 'documents.issue.*|documents.approve|documents.drafts_all', 'edit'],
        'invoices'     => [term('billing'), 'invoices.manage', 'docs'],
        'duty'         => ['Dienstzeiten', 'duty.clock|duty.view|duty.manage', 'clock'],
        'roster'       => ['Dienstplan', 'roster.view|roster.manage', 'clock'],
        'stock'        => ['Medikamente & BtM', 'stock.view|stock.manage', 'shield'],
        'training'     => ['Ausbildung', 'training.view|training.manage', 'shield'],
        'wiki'         => ['Wiki', 'wiki.view|wiki.edit', 'folder'],
        'shared'       => ['Freigegebene Dokumente', 'documents.shared_view|documents.view|requests.view', 'external'],
        'portal_admin' => ['Bürgerportal', 'portal.manage|portal.applications', 'users'],
    ],
    'Verwaltung' => [
        'users'    => ['Personal', 'users.view', 'users'],
        'ranks'    => ['Ränge & Rechte', 'ranks.manage', 'shield'],
        'org'      => ['Abteilungen & Qualifikationen', 'departments.manage|qualifications.manage', 'folder'],
        'settings' => ['Einstellungen', 'settings.manage', 'settings'],
        'locations' => ['Standorte', 'settings.manage', 'folder'],
        'doc_types' => ['Dokumentarten', 'settings.manage', 'docs'],
        'templates' => ['Vorlagen-Editor', 'templates.manage', 'edit'],
        'prices'   => [term('catalog'), 'prices.manage', 'settings'],
        'printers' => ['Drucker', 'printers.manage', 'print'],
        'stats'    => ['Statistik', 'stats.view', 'log'],
        'discord'  => ['Discord', 'discord.manage', 'external'],
        'audit'    => ['Zugriffsprotokoll', 'audit.view', 'log'],
        'system'   => ['System & Updates', 'system.manage', 'server'],
        'factions' => ['Fraktionen', !empty($user['is_superadmin']) ? '' : 'factions.none', 'users'],
    ],
];
// Unterseiten der passenden Navigation zuordnen
$activeMap = ['patient' => 'patients', 'patient_edit' => 'patients', 'patient_print' => 'patients', 'entry_edit' => 'patients', 'case' => 'patients', 'case_edit' => 'patients', 'patient_merge' => 'patients', 'btm' => 'stock',
    'document' => 'documents', 'draft' => 'drafts', 'user_edit' => 'users', 'personnel' => 'users', 'course' => 'training', 'exam' => 'training', 'course_edit' => 'training', 'wiki_page' => 'wiki', 'wiki_edit' => 'wiki', 'wiki_lists' => 'wiki', 'rank_edit' => 'ranks', 'password_change' => 'profile', 'twofa' => 'profile'];
$active = $activeMap[$current] ?? $current;
?><!doctype html>
<html lang="<?= e(ui_lang()) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e(($title ?? 'Übersicht') . ' · ' . $orgShort) ?></title>
<link rel="icon" href="<?= e(faction_logo()) ?>">
<link rel="stylesheet" href="assets/css/app.css?v=<?= e(SAMS_VERSION) ?>">
<?php if (!empty($withDocumentCss)): ?>
<link rel="stylesheet" href="assets/css/document.css?v=<?= e(SAMS_VERSION) ?>">
<?php endif; ?>
<?= theme_style() ?>
</head>
<body class="<?= $user ? 'has-nav' : 'no-nav' ?> page-<?= e($current) ?>"<?= $user && idle_minutes() > 0 ? ' data-idle="' . idle_minutes() . '"' : '' ?>>
<?php if ($user): ?>
<header class="topbar">
    <button class="nav-toggle" type="button" aria-label="<?= e(t('Menü')) ?>"><?= icon('menu') ?></button>
    <a class="brand" href="<?= e(url('dashboard')) ?>">
        <img src="<?= e(faction_logo()) ?>" alt="">
        <span class="brand-name"><?= e($orgShort) ?></span>
        <span class="brand-sub"><?= e(org('org_sub') ?: org('org_name')) ?></span>
    </a>
    <form class="quicksearch" action="index.php" role="search">
        <input type="hidden" name="p" value="search">
        <?= icon('search') ?>
        <input name="q" id="quicksearch" placeholder="<?= e(faction_kind() === 'medical' ? t('Suchen: Patient, Diagnose, Dokument-Nr., Wiki, Personal …') : 'Suchen: Name, Bürger-ID, Dokument-Nr., Wiki, Personal …') ?>" autocomplete="off">
        <kbd>F2</kbd>
    </form>
    <?php if (!empty($user['is_superadmin']) && count(factions()) > 1): ?>
        <form method="post" action="<?= e(url('factions')) ?>" class="faction-switch" title="Ansicht wechseln">
            <?= csrf_field() ?><input type="hidden" name="action" value="switch"><input type="hidden" name="return" value="<?= e($current) ?>">
            <select name="faction_id" onchange="this.form.submit()"><?php foreach (factions() as $fx): ?><option value="<?= (int) $fx['id'] ?>"<?= selected($fx['id'], fid()) ?>><?= e($fx['short'] ?: $fx['name']) ?></option><?php endforeach; ?></select>
        </form>
    <?php endif; ?>
    <div class="topbar-right">
        <?php $unread = notifications_unread(); ?>
        <details class="bell">
            <summary title="<?= e(t('Benachrichtigungen')) ?>"><?= icon('bell') ?><b class="bell-count"<?= $unread ? '' : ' hidden' ?>><?= $unread ?></b></summary>
            <div class="bell-menu">
                <div class="bell-head"><strong><?= e(t('Benachrichtigungen')) ?></strong><a href="<?= e(url('notifications')) ?>"><?= e(t('Alle')) ?></a></div>
                <?php foreach (notifications_recent() as $n): ?>
                    <a class="<?= $n['read_at'] ? '' : 'unread' ?>" href="<?= e(url('notifications', ['open' => $n['id']])) ?>"><?= e($n['title']) ?><small><?= e(time_ago($n['created_at'])) ?></small></a>
                <?php endforeach; ?>
                <?php if (!notifications_recent(1)): ?><p class="muted small"><?= e(t('Keine Benachrichtigungen.')) ?></p><?php endif; ?>
            </div>
        </details>
        <?php if (can('duty.clock')): $dutyOpen = duty_open_session((int) $user['id']); ?>
            <form method="post" action="<?= e(url('duty')) ?>" class="duty-toggle<?= $dutyOpen ? ' on' : '' ?>">
                <?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="return" value="<?= e($current) ?>">
                <button title="<?= e(t($dutyOpen ? 'Dienst beenden' : 'Dienst beginnen')) ?>"><span class="dot"></span><?= $dutyOpen ? e(t('Im Dienst')) . ' <small data-elapsed="' . duty_seconds($dutyOpen) . '">' . e(duty_format(duty_seconds($dutyOpen))) . '</small>' : e(t('Außer Dienst')) ?></button>
            </form>
        <?php endif; ?>
        <a class="userchip" href="<?= e(url('profile')) ?>" title="<?= e(t('Mein Profil')) ?>">
            <span class="avatar"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span>
            <span class="who"><strong><?= e(user_display($user)) ?></strong><small><?= e(user_rank_name($user)) ?></small></span>
        </a>
        <form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="iconbtn" title="<?= e(t('Abmelden')) ?>"><?= icon('logout') ?></button></form>
    </div>
</header>
<nav class="sidenav">
    <?php foreach ($navGroups as $group => $items):
        $visible = array_filter($items, fn($i) => $i[1] === '' || can($i[1]));
        if (!$visible) continue; ?>
        <div class="nav-group"><?= e(t($group)) ?></div>
        <?php foreach ($visible as $p => [$label, $role, $ic]): ?>
            <a href="<?= e(url($p)) ?>" title="<?= e(t($label)) ?>" class="<?= $active === $p ? 'active' : '' ?>"><?= icon($ic) ?><span><?= e(t($label)) ?></span><?php $cnt = match ($p) { 'drafts' => draft_inbox_count(), 'board' => board_unread_count(), 'messages' => messages_unread_count(), 'portal_admin' => portal_open_count(), 'wanted' => wanted_open_count(), 'requests' => request_open_count(), default => 0 }; if ($cnt): ?><b class="nav-count"><?= $cnt ?></b><?php endif; ?></a>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <div class="nav-group"><?= e(t('Extern')) ?></div>
    <a href="verify.php" target="_blank"><?= icon('shield') ?><span><?= e(t('Echtheitsprüfung')) ?></span></a>
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
    <span class="sb-item"><span class="dot ok"></span><?= e(t('Verbunden')) ?></span>
    <span class="sb-item"><?= icon('user') ?><?= e($user['username']) ?> · <?= e(user_rank_name($user)) ?></span>
    <span class="sb-item hide-sm"><?= e(org('org_name')) ?></span>
    <span class="sb-spacer"></span>
    <span class="sb-item hide-sm"><?= e(version_label()) ?></span>
    <span class="sb-item"><?= icon('clock') ?><span data-clock><?= e(date('d.m.Y H:i')) ?></span></span>
</footer>
<?php endif; ?>
<?= datalist_html() ?>
<script src="assets/js/app.js?v=<?= e(SAMS_VERSION) ?>"></script>
<script src="assets/js/tablet-bridge.js?v=<?= e(SAMS_VERSION) ?>"></script>
<?php foreach ($scripts ?? [] as $s): ?>
<script src="<?= e($s) ?>?v=<?= e(SAMS_VERSION) ?>"></script>
<?php endforeach; ?>
</body>
</html>
