<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$data = $me;
$errors = [];
if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'profile') {
        $row = [];
        foreach (['name', 'title', 'rank_name', 'department', 'badge_number', 'doctor_number', 'phone'] as $f) {
            $row[$f] = mb_substr((string) post($f), 0, 190);
        }
        if ($row['name'] === '') {
            $errors[] = 'Name darf nicht leer sein.';
        } else {
            update('users', (int) $me['id'], $row);
            flash('success', 'Profil gespeichert.');
            redirect(url('profile'));
        }
        $data = $row + $me;
    } elseif ($action === 'signature') {
        $sig = post('remove') ? '' : (string) ($_POST['signature'] ?? '');
        if ($sig === '' && !post('remove')) {
            $errors[] = 'Bitte zuerst im Feld unterschreiben.';
        } elseif ($sig === '' || (preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $sig) && strlen($sig) < 400000)) {
            update('users', (int) $me['id'], ['signature' => $sig]);
            audit('user.signature', 'user', (int) $me['id']);
            flash('success', $sig === '' ? 'Unterschrift entfernt.' : 'Unterschrift gespeichert. Sie erscheint ab sofort auf neu ausgestellten Dokumenten.');
            redirect(url('profile'));
        } else {
            $errors[] = 'Ungültige Unterschrift.';
        }
    } elseif ($action === 'password') {
        $old = (string) ($_POST['old'] ?? '');
        $new = (string) ($_POST['new'] ?? '');
        if (!password_verify($old, $me['password_hash'])) {
            $errors[] = 'Aktuelles Passwort ist falsch.';
        } elseif (strlen($new) < 8) {
            $errors[] = 'Neues Passwort muss mindestens 8 Zeichen haben.';
        } elseif ($new !== (string) ($_POST['new2'] ?? '')) {
            $errors[] = 'Die Passwörter stimmen nicht überein.';
        } else {
            update('users', (int) $me['id'], ['password_hash' => password_hash($new, PASSWORD_DEFAULT)]);
            audit('user.password', 'user', (int) $me['id']);
            flash('success', 'Passwort geändert.');
            redirect(url('profile'));
        }
    }
}
$sampleDoc = ['issuer_snapshot' => issuer_snapshot($me), 'verify_code' => 'PROFIL' . $me['id']];
ob_start(); ?>
<div class="page-head"><div><h1>Mein Profil</h1><p class="muted"><?= e(ROLES[$me['role']]) ?> · Benutzer <span class="mono"><?= e($me['username']) ?></span></p></div></div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<div class="grid-2">
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="profile">
    <h3 class="form-heading">Angaben auf Dokumenten</h3>
    <?php require __DIR__ . '/_user_fields.php'; ?>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>

<div class="card">
    <h3 class="form-heading">Unterschrift</h3>
    <p class="muted small">Unterschreiben Sie einmalig mit Maus, Stift oder Finger. Die Unterschrift wird auf allen Dokumenten gedruckt, die Sie ausstellen. Bereits ausgestellte Dokumente behalten die damalige Unterschrift.</p>
    <form method="post" id="sigform">
        <?= csrf_field() ?><input type="hidden" name="action" value="signature">
        <div class="sigpad big" data-input="signature-input"><canvas width="700" height="220"></canvas><button type="button" class="btn small sig-clear">Neu</button></div>
        <input type="hidden" name="signature" id="signature-input" value="">
        <div class="form-actions"><button class="btn primary">Unterschrift speichern</button>
            <?php if ($me['signature']): ?><button class="btn danger" name="remove" value="1">Entfernen</button><?php endif; ?></div>
    </form>
    <h3 class="form-heading">So erscheinen Sie auf Dokumenten</h3>
    <div class="stamp-preview">
        <div class="sp-stamp"><?= doctor_stamp_svg($sampleDoc) ?></div>
        <div class="sp-sig"><?= signature_html($me, -3) ?></div>
    </div>
</div>
</div>

<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="password">
    <h3 class="form-heading">Passwort ändern</h3>
    <div class="field w-third"><label>Aktuelles Passwort</label><input type="password" name="old" autocomplete="current-password" required></div>
    <div class="field w-third"><label>Neues Passwort</label><input type="password" name="new" autocomplete="new-password" minlength="8" required></div>
    <div class="field w-third"><label>Wiederholen</label><input type="password" name="new2" autocomplete="new-password" minlength="8" required></div>
    <div class="form-actions"><button class="btn">Passwort ändern</button></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Mein Profil';
$withDocumentCss = true;
require SAMS_ROOT . '/app/layout.php';
