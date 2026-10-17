<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$data = $me;
$errors = [];
if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'profile') {
        // Rang, Dienstnummer und Abteilungen vergibt die Leitung – hier nur persönliche Angaben
        $row = [];
        foreach (['name', 'title', 'department', 'doctor_number', 'phone'] as $f) {
            $row[$f] = mb_substr(trim((string) post($f)), 0, 190);
        }
        if ($row['name'] === '') {
            $errors[] = 'Name darf nicht leer sein.';
        } else {
            update('users', (int) $me['id'], $row);
            audit('user.update', 'user', (int) $me['id'], $me['username'] . ' – eigenes Profil');
            flash('success', 'Profil gespeichert.');
            redirect(url('profile'));
        }
        $data = $row + $me;
    } elseif ($action === 'revoke_tablet') {
        q('UPDATE tablet_tokens SET revoked = 1 WHERE id = ? AND user_id = ?', [(int) post('token_id'), $me['id']]);
        flash('success', 'Tablet-Anmeldung beendet.');
        redirect(url('profile'));
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
    }
}
$rank = rank_by_id((int) $me['rank_id']);
$deps = all('SELECT d.* FROM departments d JOIN user_departments ud ON ud.department_id = d.id WHERE ud.user_id = ? ORDER BY d.sort, d.name', [$me['id']]);
$quals = all('SELECT q.* FROM qualifications q JOIN user_qualifications uq ON uq.qualification_id = q.id WHERE uq.user_id = ? ORDER BY q.sort, q.name', [$me['id']]);
$tablets = all('SELECT * FROM tablet_tokens WHERE user_id = ? AND revoked = 0 AND expires_at > ? AND session_version = ? ORDER BY last_used_at DESC', [$me['id'], now(), $me['session_version']]);
$twofaRequired = in_array('security.2fa_required', user_permissions($me), true);
$sampleDoc = ['issuer_snapshot' => issuer_snapshot($me), 'verify_code' => 'PROFIL' . $me['id']];
ob_start(); ?>
<div class="page-head"><div><h1>Mein Profil</h1><p class="muted"><?= e(user_rank_name($me)) ?> · Benutzer <span class="mono"><?= e($me['username']) ?></span></p></div></div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<div class="grid-2">
<div>
    <section class="card">
        <h2>Dienst</h2>
        <dl class="kv">
            <dt>Rang</dt><dd><?= rank_badge($rank) ?> <?= e($rank['name'] ?? '–') ?><?= $me['is_superadmin'] ? ' <span class="badge black">Hauptadministrator</span>' : '' ?></dd>
            <dt>Dienstnummer</dt><dd class="mono"><?= e($me['badge_number'] ?: '–') ?></dd>
            <dt>Abteilungen</dt><dd><?php foreach ($deps as $d): ?><?= tag_badge($d) ?> <?= e($d['name']) ?><br><?php endforeach; ?><?= $deps ? '' : '–' ?></dd>
            <dt>Qualifikationen</dt><dd><?php foreach ($quals as $qq): ?><?= tag_badge($qq) ?> <?= e($qq['name']) ?><br><?php endforeach; ?><?= $quals ? '' : '–' ?></dd>
        </dl>
        <p class="hint">Rang, Dienstnummer, Abteilungen und Qualifikationen werden von deiner Leitung gepflegt.</p>
        <p><a class="btn small" href="<?= e(url('personnel')) ?>"><?= icon('folder') ?>Meine Personalakte</a></p>
    </section>
    <form method="post" class="card form-grid">
        <h2>Angaben auf Dokumenten</h2>
        <?= csrf_field() ?><input type="hidden" name="action" value="profile">
        <?php require __DIR__ . '/_user_fields.php'; ?>
        <div class="form-actions"><button class="btn primary">Speichern</button></div>
    </form>
    <section class="card">
        <h2>Sicherheit</h2>
        <dl class="kv">
            <dt>Passwort</dt><dd>zuletzt geändert <?= e(fmt_datetime($me['password_changed_at']) ?: 'nie') ?> · <a href="<?= e(url('password_change')) ?>">Passwort ändern</a></dd>
            <dt>2FA</dt><dd><?= $me['totp_enabled'] ? '<span class="badge green">aktiv</span>' : '<span class="badge grey">nicht aktiv</span>' ?><?= $twofaRequired ? ' <span class="badge orange">Pflicht für deinen Rang</span>' : '' ?> · <a href="<?= e(url('twofa')) ?>"><?= $me['totp_enabled'] ? 'Verwalten' : 'Einrichten' ?></a></dd>
            <dt>Letzte Anmeldung</dt><dd><?= e(fmt_datetime($me['last_login'])) ?></dd>
        </dl>
        <h3 class="form-heading" style="margin-top:.8rem">Angemeldete Tablets im Spiel</h3>
        <?php if (!$tablets): ?><p class="muted small">Keine.</p><?php endif; ?>
        <?php foreach ($tablets as $t): ?>
            <form method="post" class="inline-form tablet-row"><?= csrf_field() ?><input type="hidden" name="action" value="revoke_tablet"><input type="hidden" name="token_id" value="<?= (int) $t['id'] ?>">
                <span><strong><?= e($t['player_name'] ?: 'Unbekannt') ?></strong><br><small class="muted">angemeldet <?= e(fmt_datetime($t['created_at'])) ?> · zuletzt aktiv <?= e(fmt_datetime($t['last_used_at'])) ?></small></span>
                <button class="btn small">Abmelden</button></form>
        <?php endforeach; ?>
    </section>
</div>

<div class="card">
    <h2>Unterschrift</h2>
    <p class="muted small">Unterschreibe einmalig mit Maus, Stift oder Finger. Die Unterschrift wird auf allen Dokumenten gedruckt, die du ausstellst. Bereits ausgestellte Dokumente behalten die damalige Unterschrift.</p>
    <form method="post" id="sigform">
        <?= csrf_field() ?><input type="hidden" name="action" value="signature">
        <div class="sigpad big" data-input="signature-input"><canvas width="700" height="220"></canvas><button type="button" class="btn small sig-clear">Neu</button></div>
        <input type="hidden" name="signature" id="signature-input" value="">
        <div class="form-actions"><button class="btn primary">Unterschrift speichern</button>
            <?php if ($me['signature']): ?><button class="btn danger" name="remove" value="1">Entfernen</button><?php endif; ?></div>
    </form>
    <h3 class="form-heading">So erscheinst du auf Dokumenten</h3>
    <div class="stamp-preview">
        <div class="sp-stamp"><?= doctor_stamp_svg($sampleDoc) ?></div>
        <div class="sp-sig"><?= signature_html($me, -3) ?></div>
    </div>
</div>
</div>
<?php
$content = ob_get_clean();
$title = 'Mein Profil';
$withDocumentCss = true;
require SAMS_ROOT . '/app/layout.php';
