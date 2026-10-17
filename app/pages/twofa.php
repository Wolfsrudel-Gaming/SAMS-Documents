<?php
defined('SAMS_ROOT') || exit;
/** Zwei-Faktor-Anmeldung einrichten oder abschalten. */
$me = current_user();
$required = in_array('security.2fa_required', user_permissions($me), true);
$errors = [];
$codes = $_SESSION['recovery_codes_once'] ?? null;
unset($_SESSION['recovery_codes_once']);

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'enable' && empty($me['totp_enabled'])) {
        $secret = (string) ($_SESSION['totp_setup'] ?? '');
        $step = totp_verify($secret, (string) post('code'));
        if ($step === null) {
            $errors[] = 'Der Code stimmt nicht. Prüfe, ob die Uhrzeit auf dem Handy stimmt, und versuche es erneut.';
        } else {
            $plain = recovery_codes_generate();
            update('users', (int) $me['id'], [
                'totp_secret' => $secret, 'totp_enabled' => 1, 'totp_last_step' => $step,
                'recovery_codes' => recovery_codes_hash($plain),
            ]);
            unset($_SESSION['totp_setup']);
            audit('user.2fa_enabled', 'user', (int) $me['id']);
            $_SESSION['recovery_codes_once'] = $plain;
            flash('success', 'Zwei-Faktor-Anmeldung ist aktiv.');
            redirect(url('twofa'));
        }
    } elseif ($action === 'disable' && !empty($me['totp_enabled'])) {
        if ($required) {
            $errors[] = 'Für deinen Rang ist die Zwei-Faktor-Anmeldung Pflicht.';
        } elseif (!password_verify((string) ($_POST['password'] ?? ''), $me['password_hash'])) {
            $errors[] = 'Passwort falsch.';
        } else {
            update('users', (int) $me['id'], ['totp_secret' => '', 'totp_enabled' => 0, 'recovery_codes' => '']);
            audit('user.2fa_disabled', 'user', (int) $me['id']);
            flash('success', 'Zwei-Faktor-Anmeldung wurde abgeschaltet.');
            redirect(url('profile'));
        }
    } elseif ($action === 'new_codes' && !empty($me['totp_enabled'])) {
        $plain = recovery_codes_generate();
        update('users', (int) $me['id'], ['recovery_codes' => recovery_codes_hash($plain)]);
        audit('user.2fa_codes', 'user', (int) $me['id']);
        $_SESSION['recovery_codes_once'] = $plain;
        redirect(url('twofa'));
    }
}

if (empty($me['totp_enabled'])) {
    $_SESSION['totp_setup'] ??= totp_new_secret();
    $secret = $_SESSION['totp_setup'];
}

ob_start(); ?>
<div class="page-head"><div><h1>Zwei-Faktor-Anmeldung</h1><p class="muted">Schützt dein Konto zusätzlich mit einem Code aus einer Authenticator-App.</p></div>
    <?php if (empty($me['must_change_password']) && !($required && empty($me['totp_enabled']))): ?><a class="btn" href="<?= e(url('profile')) ?>"><?= icon('back') ?>Zum Profil</a><?php endif; ?></div>
<?php if ($required && empty($me['totp_enabled'])): ?>
    <div class="flash warn">Für deinen Rang ist die Zwei-Faktor-Anmeldung Pflicht. Bitte richte sie jetzt ein, um fortzufahren.</div>
<?php endif; ?>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<?php if ($codes): ?>
<section class="card onetime">
    <h2>Wiederherstellungscodes – jetzt sichern!</h2>
    <p>Mit diesen Codes kommst du in dein Konto, falls du dein Handy verlierst. Jeder Code funktioniert <strong>einmal</strong>. Sie werden nur jetzt angezeigt.</p>
    <div class="codes mono"><?php foreach ($codes as $c): ?><span><?= e($c) ?></span><?php endforeach; ?></div>
    <button type="button" class="btn small" data-copy="<?= e(implode("\n", $codes)) ?>"><?= icon('copy') ?>Kopieren</button>
</section>
<?php endif; ?>

<?php if (empty($me['totp_enabled'])): ?>
<section class="card">
    <h2>Einrichten</h2>
    <div class="twofa-setup">
        <div class="twofa-qr" data-qr="<?= e(totp_uri($secret, $me['username'])) ?>"></div>
        <ol>
            <li>Installiere eine Authenticator-App (z. B. Google Authenticator, Microsoft Authenticator oder Authy).</li>
            <li>Scanne den QR-Code – oder gib den Schlüssel manuell ein:<br><code class="secret"><?= e(chunk_split($secret, 4, ' ')) ?></code></li>
            <li>Gib den 6-stelligen Code aus der App ein:</li>
        </ol>
    </div>
    <form method="post" class="form-grid">
        <?= csrf_field() ?><input type="hidden" name="action" value="enable">
        <div class="field w-third"><input name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="6" required class="mono code-input" placeholder="123456"></div>
        <div class="field w-third"><button class="btn primary">Aktivieren</button></div>
    </form>
</section>
<?php else: ?>
<section class="card">
    <h2>Status</h2>
    <p><span class="badge green">aktiv</span> Bei jeder Anmeldung wird ein Code aus deiner App abgefragt.
        Verbleibende Wiederherstellungscodes: <strong><?= count(json_decode((string) $me['recovery_codes'] ?: '[]', true) ?: []) ?></strong></p>
    <div class="form-actions">
        <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="new_codes" onclick="return confirm('Neue Codes erzeugen? Die alten werden ungültig.')">Neue Wiederherstellungscodes</button></form>
        <?php if (!$required): ?>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="disable">
            <input type="password" name="password" placeholder="Passwort zur Bestätigung" required>
            <button class="btn danger">Abschalten</button></form>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Zwei-Faktor-Anmeldung';
$scripts = ['assets/vendor/qrcode.js', 'assets/js/document.js'];
require SAMS_ROOT . '/app/layout.php';
