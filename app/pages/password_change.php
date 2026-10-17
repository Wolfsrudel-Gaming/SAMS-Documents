<?php
defined('SAMS_ROOT') || exit;
/** Passwort ändern – Pflicht nach Startpasswort oder Zurücksetzen durch die Leitung. */
$me = current_user();
$forced = !empty($me['must_change_password']);
$errors = [];

if (is_post()) {
    csrf_check();
    $old = (string) ($_POST['old'] ?? '');
    $new = (string) ($_POST['new'] ?? '');
    if (!password_verify($old, $me['password_hash'])) {
        $errors[] = $forced ? 'Das Startpasswort ist falsch.' : 'Das aktuelle Passwort ist falsch.';
    }
    $errors = array_merge($errors, password_problems($new, $me['username']));
    if ($new !== (string) ($_POST['new2'] ?? '')) {
        $errors[] = 'Die beiden neuen Passwörter stimmen nicht überein.';
    }
    if ($old !== '' && $new === $old) {
        $errors[] = 'Das neue Passwort muss sich vom bisherigen unterscheiden.';
    }
    if (!$errors) {
        update('users', (int) $me['id'], [
            'password_hash' => password_hash($new, PASSWORD_DEFAULT),
            'must_change_password' => 0,
            'password_changed_at' => now(),
        ]);
        // Andere Sitzungen (z. B. mit dem alten Passwort) beenden, die eigene behalten
        end_sessions((int) $me['id'], true);
        audit('user.password', 'user', (int) $me['id']);
        flash('success', 'Dein Passwort wurde geändert.');
        redirect(url($forced ? 'dashboard' : 'profile'));
    }
}

ob_start(); ?>
<div class="page-head"><div><h1><?= $forced ? 'Neues Passwort festlegen' : 'Passwort ändern' ?></h1>
    <p class="muted"><?= e(user_display($me)) ?> · Benutzer <span class="mono"><?= e($me['username']) ?></span></p></div></div>
<?php if ($forced): ?>
    <div class="flash warn">Du meldest dich mit einem Startpasswort an. Bitte lege jetzt ein eigenes Passwort fest, das nur du kennst.</div>
<?php endif; ?>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid narrow-form">
    <h2>Passwort</h2>
    <?= csrf_field() ?>
    <div class="field w-full"><label><?= $forced ? 'Startpasswort' : 'Aktuelles Passwort' ?></label><input type="password" name="old" autocomplete="current-password" required autofocus></div>
    <div class="field w-half"><label>Neues Passwort</label><input type="password" name="new" autocomplete="new-password" minlength="10" required></div>
    <div class="field w-half"><label>Neues Passwort wiederholen</label><input type="password" name="new2" autocomplete="new-password" minlength="10" required></div>
    <p class="hint w-full">Mindestens 10 Zeichen. Tipp: ein Satz oder mehrere Wörter sind sicherer und leichter zu merken als kurze Zeichenfolgen.</p>
    <div class="form-actions">
        <button class="btn primary">Passwort speichern</button>
        <?php if (!$forced): ?><a class="btn" href="<?= e(url('profile')) ?>">Abbrechen</a><?php endif; ?>
    </div>
</form>
<?php
$content = ob_get_clean();
$title = 'Passwort ändern';
require SAMS_ROOT . '/app/layout.php';
