<?php
defined('SAMS_ROOT') || exit;
$ranks = all('SELECT r.*, (SELECT COUNT(*) FROM users u WHERE u.rank_id = r.id AND u.active = 1) AS members,
    (SELECT COUNT(*) FROM rank_permissions p WHERE p.rank_id = r.id) AS perm_count
    FROM ranks r ORDER BY r.level DESC, r.name');
$myLevel = user_level(current_user());
$total = count(all_permissions());

ob_start(); ?>
<div class="page-head">
    <div><h1>Ränge &amp; Rechte</h1><p class="muted">Die Rangstufe bestimmt die Hierarchie: Konten und Ränge kann man nur unterhalb der eigenen Stufe verwalten.</p></div>
    <a class="btn primary" href="<?= e(url('rank_edit')) ?>"><?= icon('plus') ?>Neuer Rang</a>
</div>
<div class="card">
<table class="list">
    <thead><tr><th style="width:70px">Stufe</th><th>Rang</th><th>Beschreibung</th><th class="right">Rechte</th><th class="right">Aktive Mitglieder</th><th>2FA</th></tr></thead>
    <tbody>
    <?php foreach ($ranks as $r): $editable = can_manage_rank($r); ?>
        <tr<?= $editable ? ' onclick="location.href=\'' . e(url('rank_edit', ['id' => $r['id']])) . '\'"' : ' class="locked-row" title="Nur Ränge unterhalb deiner eigenen Stufe sind bearbeitbar"' ?>>
            <td class="mono"><?= (int) $r['level'] ?></td>
            <td><?= rank_badge($r) ?> <strong><?= e($r['name']) ?></strong></td>
            <td class="small muted"><?= e($r['description']) ?></td>
            <td class="right"><?= (int) $r['perm_count'] ?> / <?= $total ?></td>
            <td class="right"><a href="<?= e(url('users', ['rank' => $r['id']])) ?>" onclick="event.stopPropagation()"><?= (int) $r['members'] ?></a></td>
            <td><?= in_array('security.2fa_required', rank_permissions((int) $r['id']), true) ? '<span class="badge orange">Pflicht</span>' : '' ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php
$content = ob_get_clean();
$title = 'Ränge & Rechte';
require SAMS_ROOT . '/app/layout.php';
