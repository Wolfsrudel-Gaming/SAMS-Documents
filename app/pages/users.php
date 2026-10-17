<?php
defined('SAMS_ROOT') || exit;
$users = all('SELECT u.*, (SELECT COUNT(*) FROM documents d WHERE d.issued_by = u.id) AS doc_count FROM users u ORDER BY active DESC, name');
ob_start(); ?>
<div class="page-head">
    <div><h1>Personal</h1><p class="muted"><?= count($users) ?> Benutzerkonten</p></div>
    <a class="btn primary" href="<?= e(url('user_edit')) ?>">+ Neues Konto</a>
</div>
<div class="card">
<table class="list">
    <thead><tr><th>Name</th><th>Benutzername</th><th>Rolle</th><th>Dienstgrad / Abteilung</th><th>Dienstnr.</th><th>Letzter Login</th><th class="right">Dok.</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr onclick="location.href='<?= e(url('user_edit', ['id' => $u['id']])) ?>'" class="<?= $u['active'] ? '' : 'inactive' ?>">
            <td><strong><?= e(user_display($u)) ?></strong><?= $u['active'] ? '' : ' <span class="badge grey">deaktiviert</span>' ?><?= $u['signature'] ? '' : ' <span class="badge orange" title="Keine Unterschrift hinterlegt">ohne Unterschrift</span>' ?></td>
            <td class="mono"><?= e($u['username']) ?></td>
            <td><?= e(ROLES[$u['role']] ?? $u['role']) ?></td>
            <td><?= e(trim($u['rank_name'] . ($u['department'] ? ' · ' . $u['department'] : ''))) ?></td>
            <td class="mono"><?= e($u['badge_number']) ?></td>
            <td><?= e(fmt_datetime($u['last_login'])) ?></td>
            <td class="right"><?= (int) $u['doc_count'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php
$content = ob_get_clean();
$title = 'Personal';
require SAMS_ROOT . '/app/layout.php';
