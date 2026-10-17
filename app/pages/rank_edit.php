<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$rank = $id ? one('SELECT * FROM ranks WHERE id = ?', [$id]) : null;
if ($id && !$rank) {
    not_found('Rang nicht gefunden.');
}
if ($rank && !can_manage_rank($rank)) {
    forbidden('Du kannst nur Ränge unterhalb deiner eigenen Rangstufe bearbeiten.');
}
$myLevel = user_level($me);
$maxLevel = $myLevel === PHP_INT_MAX ? 1000 : $myLevel - 1;
$myPerms = user_permissions($me);
// Die Einschränkung "nur eigene Abteilungen" darf jeder vergeben, der Ränge verwaltet
$grantable = array_merge($myPerms, ['users.scope_department']);
$perms = $rank ? rank_permissions($id) : ['patients.view', 'documents.view', 'entries.create'];
$data = $rank ?? ['name' => '', 'short' => '', 'level' => min(10, $maxLevel), 'color' => '#64717f', 'description' => ''];
$errors = [];
$members = $rank ? (int) scalar('SELECT COUNT(*) FROM users WHERE rank_id = ?', [$id]) : 0;

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete' && $rank) {
        if ($members > 0) {
            flash('error', 'Der Rang ist noch ' . $members . ' Konto(en) zugeordnet. Bitte zuerst umstufen.');
            redirect(url('rank_edit', ['id' => $id]));
        }
        q('DELETE FROM rank_permissions WHERE rank_id = ?', [$id]);
        q('DELETE FROM ranks WHERE id = ?', [$id]);
        audit('rank.delete', 'rank', $id, $rank['name']);
        flash('success', 'Rang gelöscht.');
        redirect(url('ranks'));
    }
    $data = [
        'name' => mb_substr(trim((string) post('name')), 0, 100),
        'short' => mb_substr(trim((string) post('short')), 0, 10),
        'level' => (int) post('level'),
        'color' => hex_color((string) post('color'), '#64717f'),
        'description' => mb_substr(trim((string) post('description')), 0, 500),
    ];
    if ($data['name'] === '') {
        $errors[] = 'Bitte einen Namen angeben.';
    }
    if ($data['level'] < 1 || $data['level'] > $maxLevel) {
        $errors[] = 'Die Rangstufe muss zwischen 1 und ' . $maxLevel . ' liegen (unterhalb deiner eigenen Stufe).';
    }
    $posted = array_values(array_intersect((array) ($_POST['perms'] ?? []), all_permissions()));
    // Rechte, die ich selbst nicht habe, kann ich weder vergeben noch entziehen
    $newPerms = array_values(array_unique(array_merge(
        array_intersect($posted, $grantable),
        array_diff($perms, $grantable)
    )));
    if ($errors) {
        $perms = $newPerms;
    } else {
        $row = $data + ['updated_at' => now()];
        if ($rank) {
            update('ranks', $id, $row);
        } else {
            $id = insert('ranks', $row + ['created_at' => now()]);
        }
        $added = array_diff($newPerms, $perms);
        $removed = $rank ? array_diff($perms, $newPerms) : [];
        q('DELETE FROM rank_permissions WHERE rank_id = ?', [$id]);
        foreach ($newPerms as $p) {
            insert('rank_permissions', ['rank_id' => $id, 'permission' => $p]);
        }
        $details = $data['name'] . ' (Stufe ' . $data['level'] . ')'
            . ($added ? ' – hinzugefügt: ' . implode(', ', $added) : '')
            . ($removed ? ' – entzogen: ' . implode(', ', $removed) : '');
        audit($rank ? 'rank.update' : 'rank.create', 'rank', $id, $details);
        flash('success', 'Rang gespeichert.');
        redirect(url('ranks'));
    }
}

ob_start(); ?>
<div class="page-head">
    <div><h1><?= $rank ? 'Rang bearbeiten: ' . e($rank['name']) : 'Neuer Rang' ?></h1>
        <p class="muted"><?= $rank ? $members . ' Konto(en) mit diesem Rang' : 'Rechte gelten für alle Konten mit diesem Rang.' ?></p></div>
    <a class="btn" href="<?= e(url('ranks')) ?>"><?= icon('back') ?>Alle Ränge</a>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid">
    <h2>Rang</h2>
    <?= csrf_field() ?>
    <div class="field w-third"><label>Bezeichnung <span class="req">*</span></label><input name="name" value="<?= e($data['name']) ?>" required placeholder="z. B. Oberarzt / Oberärztin"></div>
    <div class="field w-sixth"><label>Kürzel</label><input name="short" value="<?= e($data['short']) ?>" maxlength="10" placeholder="OA"></div>
    <div class="field w-sixth"><label>Rangstufe <span class="req">*</span></label><input type="number" name="level" value="<?= (int) $data['level'] ?>" min="1" max="<?= $maxLevel ?>" required></div>
    <div class="field w-sixth"><label>Farbe</label><div class="color-row"><input type="color" value="<?= e(hex_color((string) $data['color'], '#64717f')) ?>"><input type="text" name="color" value="<?= e($data['color']) ?>"></div></div>
    <div class="field w-full"><label>Beschreibung</label><input name="description" value="<?= e($data['description']) ?>" placeholder="Wofür ist dieser Rang gedacht?"></div>
    <p class="hint w-full">Höhere Stufe = höherer Rang. Deine eigene Stufe: <strong><?= $myLevel === PHP_INT_MAX ? 'Hauptadministrator (über allen)' : $myLevel ?></strong> – du kannst nur Stufen bis <?= $maxLevel ?> vergeben.</p>

    <h3 class="form-heading">Rechte</h3>
    <div class="field w-full perm-tools">
        <button type="button" class="btn small" data-perm-all="1">Alle auswählen</button>
        <button type="button" class="btn small" data-perm-all="0">Keine</button>
        <span class="hint">Ausgegraute Rechte besitzt du selbst nicht und kannst sie daher nicht ändern.</span>
    </div>
    <div class="perm-grid w-full">
        <?php foreach (permission_catalog() as $group => $items): ?>
            <fieldset class="perm-group">
                <legend><label class="choice"><input type="checkbox" class="perm-group-toggle"> <?= e($group) ?></label></legend>
                <?php foreach ($items as $key => $label): $allowed = in_array($key, $grantable, true); ?>
                    <label class="choice<?= $allowed ? '' : ' locked' ?><?= in_array($key, ['users.scope_department', 'security.2fa_required'], true) ? ' special' : '' ?>">
                        <input type="checkbox" name="perms[]" value="<?= e($key) ?>"<?= checked(in_array($key, $perms, true)) ?><?= $allowed ? '' : ' disabled' ?>>
                        <?= e($label) ?></label>
                <?php endforeach; ?>
            </fieldset>
        <?php endforeach; ?>
    </div>
    <div class="form-actions">
        <button class="btn primary">Speichern</button>
        <?php if ($rank): ?>
            <button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Rang löschen?')"<?= $members ? ' disabled title="Noch Konten zugeordnet"' : '' ?>>Rang löschen</button>
        <?php endif; ?>
    </div>
</form>
<?php
$content = ob_get_clean();
$title = $rank ? $rank['name'] : 'Neuer Rang';
require SAMS_ROOT . '/app/layout.php';
