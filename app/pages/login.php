<?php
defined('SAMS_ROOT') || exit;
if (current_user()) {
    redirect(url('dashboard'));
}
$error = '';
if (is_post()) {
    csrf_check();
    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] > 10) {
        $error = 'Zu viele Fehlversuche. Bitte später erneut versuchen.';
    } elseif (attempt_login((string) post('username'), (string) post('password'))) {
        $_SESSION['login_attempts'] = 0;
        audit('login');
        $next = (string) ($_GET['next'] ?? '');
        // Nur interne Weiterleitungen zulassen
        redirect($next !== '' && preg_match('#^/[^/\\\\]#', $next) ? $next : url('dashboard'));
    } else {
        $error = 'Benutzername oder Passwort falsch.';
    }
}
ob_start(); ?>
<div class="login-wrap">
    <form method="post" class="card login">
        <img class="login-logo" src="<?= e(setting('logo', 'assets/img/logo.svg')) ?>" alt="">
        <h1><?= e(setting('org_name', 'San Andreas Medical Services')) ?></h1>
        <p class="muted">Dokumenten- &amp; Aktensystem · Dienstzugang</p>
        <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
        <?= csrf_field() ?>
        <label>Benutzername<input name="username" autocomplete="username" required autofocus></label>
        <label>Passwort<input name="password" type="password" autocomplete="current-password" required></label>
        <button class="btn primary block">Anmelden</button>
        <p class="muted small center">Dokument prüfen? <a href="verify.php">Zur Echtheitsprüfung</a></p>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = 'Anmeldung';
require SAMS_ROOT . '/app/layout.php';
