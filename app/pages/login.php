<?php
defined('SAMS_ROOT') || exit;
if (current_user()) {
    redirect(url('dashboard'));
}
$error = '';
$step = isset($_SESSION['pending_2fa']) ? '2fa' : 'password';
$next = (string) ($_GET['next'] ?? '');
$target = $next !== '' && preg_match('#^/[^/\\\\]#', $next) ? $next : url('dashboard');

if (is_post()) {
    csrf_check();
    if (post('action') === 'cancel') {
        unset($_SESSION['pending_2fa']);
        redirect(url('login'));
    }
    if ($step === '2fa') {
        $result = attempt_2fa((string) post('code'));
        if ($result === 'ok') {
            audit('login', 'user', (int) current_user()['id'], '2FA');
            redirect($target);
        }
        if ($result === 'expired') {
            $step = 'password';
            $error = 'Die Anmeldung ist abgelaufen. Bitte erneut anmelden.';
        } else {
            $error = 'Der Code ist ungültig.';
        }
    } else {
        $username = (string) post('username');
        $result = attempt_login($username, (string) post('password'));
        switch ($result) {
            case 'ok':
                audit('login', 'user', (int) current_user()['id']);
                redirect($target);
                // no break
            case '2fa':
                $step = '2fa';
                break;
            case 'blocked':
                $error = 'Zu viele Fehlversuche. Bitte in ' . LOGIN_WINDOW_MIN . ' Minuten erneut versuchen.';
                break;
            case 'inactive':
                $error = 'Dieses Konto ist gesperrt. Bitte wende dich an deine Leitung.';
                break;
            case 'faction_inactive':
                $error = 'Deine Fraktion ist derzeit deaktiviert.';
                break;
            default:
                $error = 'Benutzername oder Passwort falsch.';
        }
    }
}
ob_start(); ?>
<div class="login-wrap">
    <form method="post" class="card login">
        <div class="login-head">
            <img src="<?= e(faction_logo()) ?>" alt="">
            <div><strong><?= e(org('org_name')) ?></strong><small><?= e(t('Klinisches Informationssystem · Dienstzugang')) ?></small></div>
        </div>
        <div class="login-body">
            <?php if ($error): ?><div class="flash error"><?= e($error) ?></div><?php endif; ?>
            <?= csrf_field() ?>
            <?php if ($step === '2fa'): ?>
                <p class="muted"><?= e(t('Gib den 6-stelligen Code aus deiner Authenticator-App ein. Ohne Handy kannst du einen Wiederherstellungscode verwenden.')) ?></p>
                <label><?= e(t('Bestätigungscode')) ?><input name="code" inputmode="numeric" autocomplete="one-time-code" required autofocus maxlength="12" class="mono code-input"></label>
                <button class="btn primary block"><?= e(t('Bestätigen')) ?></button>
                <button class="link small" name="action" value="cancel" formnovalidate><?= e(t('Abbrechen')) ?></button>
            <?php else: ?>
                <label><?= e(t('Benutzername')) ?><input name="username" autocomplete="username" required autofocus value="<?= e(post('username')) ?>"></label>
                <label><?= e(t('Passwort')) ?><input name="password" type="password" autocomplete="current-password" required></label>
                <button class="btn primary block"><?= e(t('Anmelden')) ?></button>
                <?php if (discord_configured()): ?><a class="btn block discord-btn" href="<?= e(url('discord_login')) ?>"><?= e(t('Mit Discord anmelden')) ?></a><?php endif; ?>
                <p class="muted small"><?= e(t('Passwort vergessen? Deine Leitung kann es zurücksetzen.')) ?></p>
            <?php endif; ?>
        </div>
        <div class="login-foot"><span><a href="verify.php"><?= e(t('Dokument prüfen')) ?></a></span><span><?= e(version_label()) ?></span></div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = 'Anmeldung';
require SAMS_ROOT . '/app/layout.php';
