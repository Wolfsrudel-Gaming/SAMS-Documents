<?php
defined('SAMS_ROOT') || exit;
/** Abteilungen und Qualifikationen frei anlegen und konfigurieren. */
$kinds = [
    'departments' => ['table' => 'departments', 'link' => 'user_departments', 'col' => 'department_id', 'perm' => 'departments.manage',
        'title' => 'Abteilungen', 'one' => 'Abteilung', 'color' => '#0b4f9c', 'example' => 'z. B. Rettungsdienst / Notarzt'],
    'qualifications' => ['table' => 'qualifications', 'link' => 'user_qualifications', 'col' => 'qualification_id', 'perm' => 'qualifications.manage',
        'title' => 'Qualifikationen', 'one' => 'Qualifikation', 'color' => '#2e7d4f', 'example' => 'z. B. Notarzt, Ausbilder/in'],
];
$tab = (string) ($_GET['tab'] ?? (can('departments.manage') ? 'departments' : 'qualifications'));
if (!isset($kinds[$tab]) || !can($kinds[$tab]['perm'])) {
    forbidden('Dafür fehlt deinem Rang die nötige Berechtigung.');
}
$k = $kinds[$tab];
$editId = (int) ($_GET['edit'] ?? 0);
$errors = [];

if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if ($id && !fone($k['table'], $id)) {
        not_found();
    }
    if (post('action') === 'delete' && $id) {
        $used = (int) scalar("SELECT COUNT(*) FROM {$k['link']} WHERE {$k['col']} = ?", [$id]);
        if ($used) {
            flash('error', "Noch $used Konto(en) zugeordnet. Bitte stattdessen deaktivieren oder zuerst die Zuordnungen entfernen.");
        } else {
            $name = scalar("SELECT name FROM {$k['table']} WHERE id = ?", [$id]);
            q("DELETE FROM {$k['table']} WHERE id = ?", [$id]);
            audit($tab . '.delete', $tab, $id, (string) $name);
            flash('success', $k['one'] . ' gelöscht.');
        }
        redirect(url('org', ['tab' => $tab]));
    }
    $row = [
        'name' => mb_substr(trim((string) post('name')), 0, 120),
        'short' => mb_substr(trim((string) post('short')), 0, 10),
        'color' => hex_color((string) post('color'), $k['color']),
        'description' => mb_substr(trim((string) post('description')), 0, 500),
        'sort' => (int) post('sort'),
        'active' => post('active') ? 1 : 0,
        'updated_at' => now(),
    ];
    if ($row['name'] === '') {
        $errors[] = 'Bitte einen Namen angeben.';
        $editId = $id;
    } else {
        if ($id) {
            update($k['table'], $id, $row);
            audit($tab . '.update', $tab, $id, $row['name']);
        } else {
            $id = insert($k['table'], $row + ['created_at' => now()]);
            audit($tab . '.create', $tab, $id, $row['name']);
        }
        flash('success', $k['one'] . ' gespeichert.');
        redirect(url('org', ['tab' => $tab]));
    }
}

$items = all("SELECT t.*, (SELECT COUNT(*) FROM {$k['link']} l JOIN users u ON u.id = l.user_id AND u.active = 1 WHERE l.{$k['col']} = t.id) AS members
    FROM {$k['table']} t WHERE " . fw('t') . " ORDER BY t.sort, t.name");
$edit = $editId ? fone($k['table'], $editId) : null;
$form = $edit ?? ['id' => 0, 'name' => '', 'short' => '', 'color' => $k['color'], 'description' => '', 'sort' => (count($items) + 1) * 10, 'active' => 1];
if ($errors) {
    $form = array_merge($form, array_intersect_key($_POST, $form));
}

ob_start(); ?>
<div class="page-head">
    <div><h1>Abteilungen &amp; Qualifikationen</h1><p class="muted">Frei anlegbar. Eine Person kann mehreren Abteilungen angehören und beliebig viele Qualifikationen haben.</p></div>
</div>
<div class="tabs" style="margin-bottom:12px">
    <?php foreach ($kinds as $key => $kk): if (!can($kk['perm'])) continue; ?>
        <a class="<?= $key === $tab ? 'active' : '' ?>" href="<?= e(url('org', ['tab' => $key])) ?>"><?= e($kk['title']) ?></a>
    <?php endforeach; ?>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<div class="grid-side wide-side reverse">
    <section class="card">
        <h2><?= e($k['title']) ?></h2>
        <table class="list">
            <thead><tr><th style="width:50px">Sort.</th><th>Bezeichnung</th><th>Beschreibung</th><th class="right">Aktive Mitglieder</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr class="<?= $it['active'] ? '' : 'inactive' ?>">
                    <td class="mono small"><?= (int) $it['sort'] ?></td>
                    <td><?= tag_badge($it) ?> <strong><?= e($it['name']) ?></strong></td>
                    <td class="small muted"><?= e($it['description']) ?></td>
                    <td class="right"><?php if ($tab === 'departments'): ?><a href="<?= e(url('users', ['dep' => $it['id']])) ?>"><?= (int) $it['members'] ?></a><?php else: ?><?= (int) $it['members'] ?><?php endif; ?></td>
                    <td><?= $it['active'] ? '<span class="badge green">aktiv</span>' : '<span class="badge grey">inaktiv</span>' ?></td>
                    <td class="right"><a href="<?= e(url('org', ['tab' => $tab, 'edit' => $it['id']])) ?>">bearbeiten</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$items): ?><tr><td colspan="6" class="muted">Noch keine Einträge.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
    <form method="post" class="card form-grid">
        <h2><?= $edit ? e($k['one']) . ' bearbeiten' : 'Neue ' . e($k['one']) ?></h2>
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <div class="field w-full"><label>Bezeichnung <span class="req">*</span></label><input name="name" value="<?= e($form['name']) ?>" required placeholder="<?= e($k['example']) ?>"></div>
        <div class="field w-third"><label>Kürzel</label><input name="short" value="<?= e($form['short']) ?>" maxlength="10"></div>
        <div class="field w-third"><label>Sortierung</label><input type="number" name="sort" value="<?= (int) $form['sort'] ?>"></div>
        <div class="field w-third"><label>Farbe</label><div class="color-row"><input type="color" value="<?= e(hex_color((string) $form['color'], $k['color'])) ?>"><input type="text" name="color" value="<?= e($form['color']) ?>"></div></div>
        <div class="field w-full"><label>Beschreibung</label><textarea name="description" rows="2"><?= e($form['description']) ?></textarea></div>
        <div class="field w-full"><label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($form['active']) ?>> aktiv (kann zugewiesen werden)</label></div>
        <div class="form-actions">
            <button class="btn primary">Speichern</button>
            <?php if ($edit): ?>
                <a class="btn" href="<?= e(url('org', ['tab' => $tab])) ?>">Abbrechen</a>
                <button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Wirklich löschen?')">Löschen</button>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = 'Abteilungen & Qualifikationen';
require SAMS_ROOT . '/app/layout.php';
