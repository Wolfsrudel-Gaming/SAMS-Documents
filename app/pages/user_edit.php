<?php
defined('SAMS_ROOT') || exit;
$id = (int) ($_GET['id'] ?? 0);
$user = $id ? one('SELECT * FROM users WHERE id = ?', [$id]) : null;
if ($id && !$user) {
    not_found('Benutzer nicht gefunden.');
}
$fields = ['username', 'name', 'title', 'role', 'rank_name', 'department', 'badge_number', 'doctor_number', 'phone'];
$data = $user ?? array_fill_keys($fields, '') + ['role' => 'medic', 'active' => 1];
$errors = [];

if (is_post()) {
    csrf_check();
    foreach ($fields as $f) {
        $data[$f] = mb_substr((string) post($f), 0, 190);
    }
    $data['active'] = post('active') ? 1 : 0;
    $pw = (string) ($_POST['password'] ?? '');
    if (!array_key_exists($data['role'], ROLES)) {
        $data['role'] = 'medic';
    }
    if (!preg_match('/^[A-Za-z0-9._\-]{3,40}$/', $data['username'])) {
        $errors[] = 'Benutzername: 3–40 Zeichen, nur Buchstaben, Ziffern, Punkt, Binde- und Unterstrich.';
    } elseif (scalar('SELECT id FROM users WHERE username = ? AND id <> ?', [$data['username'], $id])) {
        $errors[] = 'Benutzername ist bereits vergeben.';
    }
    if ($data['name'] === '') {
        $errors[] = 'Name ist ein Pflichtfeld.';
    }
    if (!$user && strlen($pw) < 8) {
        $errors[] = 'Bitte ein Startpasswort mit mindestens 8 Zeichen vergeben.';
    } elseif ($pw !== '' && strlen($pw) < 8) {
        $errors[] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    }
    if ($user && (int) $user['id'] === (int) current_user()['id'] && ($data['role'] !== 'admin' || !$data['active'])) {
        $errors[] = 'Sie können sich nicht selbst die Admin-Rechte entziehen oder sich deaktivieren.';
    }
    if (!$errors) {
        $row = array_intersect_key($data, array_flip(array_merge($fields, ['active'])));
        if ($pw !== '') {
            $row['password_hash'] = password_hash($pw, PASSWORD_DEFAULT);
        }
        if (post('clear_signature')) {
            $row['signature'] = '';
        }
        if ($user) {
            update('users', $id, $row);
            audit('user.update', 'user', $id, $row['username']);
        } else {
            $row['created_at'] = now();
            $id = insert('users', $row);
            audit('user.create', 'user', $id, $row['username']);
        }
        flash('success', 'Benutzerkonto gespeichert.');
        redirect(url('users'));
    }
}

ob_start(); ?>
<div class="page-head">
    <h1><?= $user ? 'Konto bearbeiten: ' . e($user['name']) : 'Neues Benutzerkonto' ?></h1>
    <a class="btn" href="<?= e(url('users')) ?>">Zurück</a>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid" autocomplete="off">
    <?= csrf_field() ?>
    <h3 class="form-heading">Zugang</h3>
    <div class="field w-third"><label>Benutzername <span class="req">*</span></label><input name="username" value="<?= e($data['username']) ?>" required></div>
    <div class="field w-third"><label><?= $user ? 'Neues Passwort (leer = unverändert)' : 'Startpasswort *' ?></label><input type="password" name="password" autocomplete="new-password" minlength="8"></div>
    <div class="field w-sixth"><label>Rolle</label>
        <select name="role"><?php foreach (ROLES as $k => $l): ?><option value="<?= e($k) ?>"<?= selected($k, $data['role']) ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field w-sixth"><label class="choice single" style="margin-top:1.6rem"><input type="checkbox" name="active" value="1"<?= checked($data['active']) ?>> aktiv</label></div>
    <h3 class="form-heading">Angaben auf Dokumenten &amp; Stempel</h3>
    <?php require __DIR__ . '/_user_fields.php'; ?>
    <?php if ($user && $user['signature']): ?>
        <div class="field w-full"><span class="label">Hinterlegte Unterschrift</span><img class="sig-preview" src="<?= e($user['signature']) ?>" alt="">
            <label class="choice single"><input type="checkbox" name="clear_signature" value="1"> Unterschrift entfernen</label></div>
    <?php endif; ?>
    <p class="hint w-full">Rollen: <strong>Auszubildende</strong> lesen Akten und schreiben Einträge · <strong>Rettungsdienst</strong> zusätzlich Einsatzprotokoll, Revers, Rechnung, Schreiben · <strong>Ärztlicher Dienst</strong> alle Bescheinigungen und vertrauliche Einträge · <strong>Leitung</strong> zusätzlich Verwaltung.</p>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Benutzerkonto';
require SAMS_ROOT . '/app/layout.php';
