<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$q = trim((string) ($_GET['q'] ?? ''));
$rankFilter = (int) ($_GET['rank'] ?? 0);
$depFilter = (int) ($_GET['dep'] ?? 0);
$status = (string) ($_GET['status'] ?? 'active');

$where = ['1=1'];
$params = [];
if ($q !== '') {
    $esc = db_driver() === 'mysql' ? '' : " ESCAPE '\\'";
    $where[] = "(LOWER(u.name) LIKE LOWER(:q)$esc OR LOWER(u.username) LIKE LOWER(:q)$esc OR u.badge_number LIKE :q$esc)";
    $params['q'] = like($q);
}
if ($rankFilter) {
    $where[] = 'u.rank_id = :rank';
    $params['rank'] = $rankFilter;
}
if ($depFilter) {
    $where[] = 'u.id IN (SELECT user_id FROM user_departments WHERE department_id = :dep)';
    $params['dep'] = $depFilter;
}
if ($status === 'active' || $status === 'inactive') {
    $where[] = 'u.active = ' . ($status === 'active' ? 1 : 0);
}
if (scoped_to_departments()) {
    $mine = user_department_ids((int) $me['id']) ?: [0];
    $where[] = '(u.id = ' . (int) $me['id'] . ' OR u.id IN (SELECT user_id FROM user_departments WHERE department_id IN (' . implode(',', $mine) . ')))';
}
$w = implode(' AND ', $where);
$per = 50;
$page = max(1, (int) ($_GET['page'] ?? 1));
$total = (int) scalar("SELECT COUNT(*) FROM users u WHERE $w", $params);
$users = all("SELECT u.*, r.name AS r_name, r.short AS r_short, r.color AS r_color, r.level AS r_level
    FROM users u LEFT JOIN ranks r ON r.id = u.rank_id WHERE $w
    ORDER BY u.active DESC, u.is_superadmin DESC, r.level DESC, u.name LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$pages = max(1, (int) ceil($total / $per));

// Abteilungen der angezeigten Konten in einer Abfrage laden
$deps = [];
$depById = [];
foreach (all('SELECT * FROM departments ORDER BY sort, name') as $d) {
    $depById[(int) $d['id']] = $d;
}
if ($users) {
    $ids = implode(',', array_map(fn($u) => (int) $u['id'], $users));
    foreach (all("SELECT user_id, department_id FROM user_departments WHERE user_id IN ($ids)") as $row) {
        if (isset($depById[(int) $row['department_id']])) {
            $deps[(int) $row['user_id']][] = $depById[(int) $row['department_id']];
        }
    }
}
$ranks = all('SELECT * FROM ranks ORDER BY level DESC, name');
$counts = ['active' => (int) scalar('SELECT COUNT(*) FROM users WHERE active = 1'), 'inactive' => (int) scalar('SELECT COUNT(*) FROM users WHERE active = 0')];

ob_start(); ?>
<div class="page-head">
    <div><h1>Personal</h1><p class="muted"><?= $counts['active'] ?> aktiv · <?= $counts['inactive'] ?> gesperrt<?= scoped_to_departments() ? ' · Ansicht auf deine Abteilungen beschränkt' : '' ?></p></div>
    <div class="btn-row">
        <?php if (can('ranks.manage')): ?><a class="btn" href="<?= e(url('ranks')) ?>"><?= icon('shield') ?>Ränge &amp; Rechte</a><?php endif; ?>
        <?php if (can('users.create')): ?><a class="btn primary" href="<?= e(url('user_edit')) ?>"><?= icon('plus') ?>Neues Konto</a><?php endif; ?>
    </div>
</div>
<form class="card filter" action="index.php">
    <input type="hidden" name="p" value="users">
    <input name="q" value="<?= e($q) ?>" placeholder="Name, Benutzername oder Dienstnummer …">
    <select name="rank"><option value="">Alle Ränge</option>
        <?php foreach ($ranks as $r): ?><option value="<?= (int) $r['id'] ?>"<?= selected($r['id'], $rankFilter) ?>><?= e($r['name']) ?></option><?php endforeach; ?></select>
    <select name="dep"><option value="">Alle Abteilungen</option>
        <?php foreach ($depById as $d): ?><option value="<?= (int) $d['id'] ?>"<?= selected($d['id'], $depFilter) ?>><?= e($d['name']) ?></option><?php endforeach; ?></select>
    <select name="status">
        <option value="active"<?= selected('active', $status) ?>>Aktive</option>
        <option value="inactive"<?= selected('inactive', $status) ?>>Gesperrte</option>
        <option value="all"<?= selected('all', $status) ?>>Alle</option>
    </select>
    <button class="btn">Filtern</button>
</form>
<div class="card">
<table class="list">
    <thead><tr><th>Name</th><th>Dienstnr.</th><th>Rang</th><th>Abteilungen</th><th>Benutzer</th><th>Sicherheit</th><th>Letzte Anmeldung</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr onclick="location.href='<?= e(url('user_edit', ['id' => $u['id']])) ?>'" class="<?= $u['active'] ? '' : 'inactive' ?>">
            <td><strong><?= e(user_display($u)) ?></strong>
                <?= $u['is_superadmin'] ? ' <span class="badge black" title="Hauptadministrator">Admin</span>' : '' ?>
                <?= $u['active'] ? '' : ' <span class="badge red">gesperrt</span>' ?></td>
            <td class="mono"><?= e($u['badge_number'] ?: '–') ?></td>
            <td><?= $u['r_name'] ? rank_badge(['name' => $u['r_name'], 'short' => $u['r_short'], 'color' => $u['r_color'], 'level' => $u['r_level']]) . ' <small>' . e($u['r_name']) . '</small>' : rank_badge(null) ?></td>
            <td><?php foreach ($deps[(int) $u['id']] ?? [] as $d): ?><?= tag_badge($d) ?> <?php endforeach; ?></td>
            <td class="mono small"><?= e($u['username']) ?></td>
            <td><?= $u['totp_enabled'] ? '<span class="badge green">2FA</span>' : '' ?>
                <?= $u['must_change_password'] ? '<span class="badge orange" title="Hat das Startpasswort noch nicht geändert">Startpasswort</span>' : '' ?>
                <?= $u['signature'] ? '' : '<span class="badge grey" title="Keine Unterschrift hinterlegt">ohne Unterschrift</span>' ?></td>
            <td class="small"><?= e(fmt_datetime($u['last_login']) ?: 'noch nie') ?></td>
            <td class="right"><?php if (can('personnel.view')): ?><a href="<?= e(url('personnel', ['id' => $u['id']])) ?>" onclick="event.stopPropagation()" title="Personalakte"><?= icon('folder') ?></a><?php endif; ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$users): ?><tr><td colspan="8" class="muted">Keine Konten gefunden.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($pages > 1): ?><div class="pager">
    <?php for ($i = 1; $i <= $pages; $i++): ?><a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(url('users', ['q' => $q, 'rank' => $rankFilter ?: null, 'dep' => $depFilter ?: null, 'status' => $status, 'page' => $i])) ?>"><?= $i ?></a><?php endfor; ?>
</div><?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Personal';
require SAMS_ROOT . '/app/layout.php';
