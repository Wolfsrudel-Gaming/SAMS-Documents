<?php
defined('SAMS_ROOT') || exit;
/** Konto anlegen, bearbeiten und verwalten (Passwort, Sitzungen, 2FA, Sperre). */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$user = $id ? fone('users', $id) : null;
if ($id && !$user) {
    not_found('Konto nicht gefunden.');
}
if (!$user && !can('users.create')) {
    forbidden('Du darfst keine Konten anlegen.');
}
// Abteilungsbeschränkung auch für das Ansehen
if ($user && scoped_to_departments() && (int) $user['id'] !== (int) $me['id']
    && !array_intersect(user_department_ids((int) $user['id']), user_department_ids((int) $me['id']))) {
    forbidden('Dieses Konto gehört nicht zu deinen Abteilungen.');
}

$canEdit = !$user || can_manage_user($user, 'users.edit');
$isSelf = $user && (int) $user['id'] === (int) $me['id'];
$ranks = assignable_ranks();
$rankIds = array_map(fn($r) => (int) $r['id'], $ranks);
$departments = all('SELECT * FROM departments WHERE ' . fw() . ' ORDER BY sort, name');
$assignableDeps = array_map(fn($d) => (int) $d['id'], assignable_departments());
$qualifications = all('SELECT * FROM qualifications WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name');

$fields = ['username', 'name', 'title', 'badge_number', 'department', 'doctor_number', 'phone', 'discord_id'];
$data = $user ?? array_fill_keys($fields, '') + ['rank_id' => $ranks ? end($ranks)['id'] : null, 'active' => 1];
$userDeps = $user ? user_department_ids($id) : [];
$userQuals = $user ? user_qualification_ids($id) : [];
$errors = [];

/** Lesbare Änderungsübersicht für das Protokoll. */
function user_change_summary(array $old, array $new, array $oldDeps, array $newDeps, array $oldQ, array $newQ): string
{
    $labels = ['username' => 'Benutzer', 'name' => 'Name', 'title' => 'Titel', 'badge_number' => 'Dienstnr.', 'department' => 'Fachrichtung',
        'doctor_number' => 'Arzt-Nr.', 'phone' => 'Telefon', 'discord_id' => 'Discord-ID'];
    $out = [];
    foreach ($labels as $k => $l) {
        if ((string) ($old[$k] ?? '') !== (string) ($new[$k] ?? '')) {
            $out[] = "$l: " . ($old[$k] ?? '') . ' → ' . ($new[$k] ?? '');
        }
    }
    if ((int) ($old['rank_id'] ?? 0) !== (int) ($new['rank_id'] ?? 0)) {
        $out[] = 'Rang: ' . (rank_by_id((int) ($old['rank_id'] ?? 0))['name'] ?? '–') . ' → ' . (rank_by_id((int) $new['rank_id'])['name'] ?? '–');
    }
    $names = fn(array $ids, string $table) => $ids ? implode(', ', array_column(all("SELECT name FROM $table WHERE id IN (" . implode(',', array_map('intval', $ids)) . ')'), 'name')) : '–';
    if (array_diff($oldDeps, $newDeps) || array_diff($newDeps, $oldDeps)) {
        $out[] = 'Abteilungen: ' . $names($oldDeps, 'departments') . ' → ' . $names($newDeps, 'departments');
    }
    if (array_diff($oldQ, $newQ) || array_diff($newQ, $oldQ)) {
        $out[] = 'Qualifikationen: ' . $names($oldQ, 'qualifications') . ' → ' . $names($newQ, 'qualifications');
    }
    return implode('; ', $out);
}

if (is_post()) {
    csrf_check();
    $action = post('action', 'save');

    // --- Verwaltungsaktionen für bestehende Konten ---
    if ($user && $action !== 'save') {
        $perm = ['reset_password' => 'users.password_reset', 'end_sessions' => 'users.sessions', 'reset_2fa' => 'users.2fa_reset',
            'deactivate' => 'users.deactivate', 'reactivate' => 'users.deactivate'][$action] ?? null;
        if (!$perm || !can_manage_user($user, $perm) || $isSelf) {
            forbidden('Für diese Aktion fehlt dir die Berechtigung oder der Rang.');
        }
        switch ($action) {
            case 'reset_password':
                $pw = generate_password();
                update('users', $id, ['password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'must_change_password' => 1]);
                end_sessions($id);
                audit('user.password_reset', 'user', $id, $user['username']);
                $_SESSION['onetime'] = ['user_id' => $id, 'username' => $user['username'], 'password' => $pw, 'kind' => 'reset'];
                break;
            case 'end_sessions':
                end_sessions($id);
                audit('user.sessions_ended', 'user', $id, $user['username']);
                flash('success', 'Alle Sitzungen von ' . $user['name'] . ' wurden beendet.');
                break;
            case 'reset_2fa':
                update('users', $id, ['totp_secret' => '', 'totp_enabled' => 0, 'recovery_codes' => '', 'totp_last_step' => 0]);
                end_sessions($id);
                audit('user.2fa_reset', 'user', $id, $user['username']);
                flash('success', 'Die Zwei-Faktor-Anmeldung wurde zurückgesetzt. Ist sie für den Rang Pflicht, muss sie beim nächsten Login neu eingerichtet werden.');
                break;
            case 'deactivate':
                $reason = mb_substr((string) post('reason'), 0, 190);
                if ($reason === '') {
                    flash('error', 'Bitte einen Grund für die Sperre angeben.');
                    break;
                }
                update('users', $id, ['active' => 0, 'deactivated_reason' => $reason, 'deactivated_at' => now()]);
                end_sessions($id);
                audit('user.deactivate', 'user', $id, $user['username'] . ': ' . $reason);
                flash('success', 'Konto gesperrt. Ausgestellte Dokumente bleiben gültig.');
                break;
            case 'reactivate':
                update('users', $id, ['active' => 1, 'deactivated_reason' => '', 'deactivated_at' => null]);
                audit('user.reactivate', 'user', $id, $user['username']);
                flash('success', 'Konto entsperrt.');
                break;
        }
        redirect(url('user_edit', ['id' => $id]));
    }

    // --- Speichern ---
    if (!$canEdit) {
        forbidden('Du darfst dieses Konto nicht bearbeiten.');
    }
    $new = [];
    foreach ($fields as $f) {
        $new[$f] = mb_substr(trim((string) post($f)), 0, 190);
    }
    $new['rank_id'] = (int) post('rank_id');
    if (locations()) {
        $new['location_id'] = location_snapshot((int) post('location_id')) ? (int) post('location_id') : null;
    }
    $postedDeps = array_map('intval', (array) ($_POST['departments'] ?? []));
    // Nur zuweisbare Abteilungen ändern, andere bestehende Zuordnungen bleiben erhalten
    $newDeps = array_values(array_unique(array_merge(
        array_intersect($postedDeps, $assignableDeps),
        array_diff($userDeps, $assignableDeps)
    )));
    $qualIds = array_map(fn($q) => (int) $q['id'], $qualifications);
    $newQuals = array_values(array_intersect(array_map('intval', (array) ($_POST['qualifications'] ?? [])), $qualIds));

    if (!preg_match('/^[A-Za-z0-9._\-]{3,40}$/', $new['username'])) {
        $errors[] = 'Benutzername: 3–40 Zeichen, nur Buchstaben, Ziffern sowie . _ -';
    } elseif (scalar('SELECT id FROM users WHERE username = ? AND id <> ?', [$new['username'], $id])) {
        $errors[] = 'Dieser Benutzername ist bereits vergeben.';
    }
    if ($new['name'] === '') {
        $errors[] = 'Bitte einen Namen angeben.';
    }
    if ($new['badge_number'] !== '' && scalar('SELECT id FROM users WHERE badge_number = ? AND id <> ? AND ' . fw(), [$new['badge_number'], $id])) {
        $errors[] = 'Diese Dienstnummer ist bereits vergeben.';
    }
    if ($new['discord_id'] !== '' && !preg_match('/^\d{15,21}$/', $new['discord_id'])) {
        $errors[] = 'Die Discord-ID besteht aus 15–21 Ziffern (Discord: Rechtsklick auf den Namen → „ID kopieren“).';
    }
    $keepRank = $user && (int) $user['rank_id'] === $new['rank_id'];
    if (!$keepRank && !in_array($new['rank_id'], $rankIds, true)) {
        $errors[] = 'Diesen Rang darfst du nicht vergeben.';
    }

    if ($errors) {
        $data = $new + ($user ?? []);
        $userDeps = $newDeps;
        $userQuals = $newQuals;
    } else {
        $row = $new;
        if ($user) {
            update('users', $id, $row);
            $summary = user_change_summary($user, $row, $userDeps, $newDeps, $userQuals, $newQuals);
            // Laufbahn in der Personalakte fortschreiben
            if ((int) $user['rank_id'] !== (int) $row['rank_id']) {
                $oldLevel = (int) (rank_by_id((int) $user['rank_id'])['level'] ?? 0);
                $newRank = rank_by_id((int) $row['rank_id']);
                personnel_event($id, 'rank', ((int) ($newRank['level'] ?? 0) > $oldLevel ? 'Befördert zu ' : 'Rangänderung: ') . ($newRank['name'] ?? '–'));
            }
            if ($added = array_diff($newQuals, $userQuals)) {
                personnel_event($id, 'qualification', 'Qualifikation erworben: ' . implode(', ', array_column(all('SELECT name FROM qualifications WHERE id IN (' . implode(',', array_map('intval', $added)) . ')'), 'name')));
            }
            audit('user.update', 'user', $id, $user['username'] . ($summary !== '' ? ' – ' . $summary : ''));
            flash('success', 'Konto gespeichert.');
        } else {
            $pw = generate_password();
            $row += [
                'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'must_change_password' => 1,
                'role' => 'medic', 'active' => 1, 'created_by' => $me['id'], 'created_at' => now(),
            ];
            $id = insert('users', $row);
            personnel_event($id, 'hire', 'Eintritt als ' . (rank_by_id($row['rank_id'])['name'] ?? '–'));
            audit('user.create', 'user', $id, $row['username'] . ' – Rang: ' . (rank_by_id($row['rank_id'])['name'] ?? '–'));
            $_SESSION['onetime'] = ['user_id' => $id, 'username' => $row['username'], 'password' => $pw, 'kind' => 'create'];
        }
        q('DELETE FROM user_departments WHERE user_id = ?', [$id]);
        foreach ($newDeps as $d) {
            insert('user_departments', ['user_id' => $id, 'department_id' => $d]);
        }
        q('DELETE FROM user_qualifications WHERE user_id = ?', [$id]);
        foreach ($newQuals as $qid) {
            insert('user_qualifications', ['user_id' => $id, 'qualification_id' => $qid]);
        }
        redirect(url('user_edit', ['id' => $id]));
    }
}

// Einmalig anzuzeigende Zugangsdaten
$onetime = null;
if ($user && isset($_SESSION['onetime']) && (int) $_SESSION['onetime']['user_id'] === $id) {
    $onetime = $_SESSION['onetime'];
    unset($_SESSION['onetime']);
}
$history = $user ? all("SELECT a.*, u.name AS actor FROM audit_log a LEFT JOIN users u ON u.id = a.user_id
    WHERE a.object_type = 'user' AND a.object_id = ? ORDER BY a.id DESC LIMIT 15", [$id]) : [];
$currentRank = $user ? rank_by_id((int) $user['rank_id']) : null;
$ro = !$canEdit;
$readonly = $ro;
$actionLabels = ['user.create' => 'Konto angelegt', 'user.update' => 'Geändert', 'user.password_reset' => 'Passwort zurückgesetzt',
    'user.password' => 'Passwort selbst geändert', 'user.sessions_ended' => 'Sitzungen beendet', 'user.2fa_reset' => '2FA zurückgesetzt',
    'user.2fa_enabled' => '2FA aktiviert', 'user.2fa_disabled' => '2FA abgeschaltet', 'user.deactivate' => 'Gesperrt',
    'user.reactivate' => 'Entsperrt', 'user.signature' => 'Unterschrift geändert', 'user.2fa_codes' => 'Neue Wiederherstellungscodes',
    'user.2fa_recovery_used' => 'Wiederherstellungscode benutzt'];

ob_start(); ?>
<div class="page-head">
    <div>
        <h1><?= $user ? e(user_display($user)) : 'Neues Konto' ?>
            <?php if ($user): ?><?= rank_badge($currentRank) ?><?= $user['is_superadmin'] ? ' <span class="badge black">Hauptadministrator</span>' : '' ?><?= $user['active'] ? '' : ' <span class="badge red">gesperrt</span>' ?><?php endif; ?></h1>
        <p class="muted"><?= $user ? 'Benutzer <span class="mono">' . e($user['username']) . '</span> · angelegt ' . e(fmt_datetime($user['created_at'])) . ' · letzte Anmeldung ' . e(fmt_datetime($user['last_login']) ?: 'noch nie') : 'Das Startpasswort wird automatisch erzeugt und nach dem Speichern einmalig angezeigt.' ?></p>
    </div>
    <div class="btn-row">
        <?php if ($isSelf): ?><a class="btn" href="<?= e(url('profile')) ?>"><?= icon('user') ?>Mein Profil</a><?php endif; ?>
        <a class="btn" href="<?= e(url('users')) ?>"><?= icon('back') ?>Personalliste</a>
    </div>
</div>

<?php if ($onetime): $loginText = "Zugang zum " . org('org_short') . "-System\nAdresse: " . base_url() . "\nBenutzername: " . $onetime['username'] . "\n" . ($onetime['kind'] === 'create' ? 'Startpasswort' : 'Neues Einmalpasswort') . ": " . $onetime['password'] . "\nBeim ersten Login legst du ein eigenes Passwort fest."; ?>
<section class="card onetime">
    <h2><?= $onetime['kind'] === 'create' ? 'Konto angelegt – Zugangsdaten' : 'Passwort zurückgesetzt – neues Einmalpasswort' ?></h2>
    <p>Gib diese Daten an <strong><?= e($user['name']) ?></strong> weiter (z. B. per Discord-Direktnachricht). Das Passwort wird <strong>nur jetzt</strong> angezeigt und muss beim ersten Login geändert werden.</p>
    <div class="credentials">
        <div><span>Benutzername</span><strong class="mono"><?= e($onetime['username']) ?></strong></div>
        <div><span>Passwort</span><strong class="mono"><?= e($onetime['password']) ?></strong></div>
    </div>
    <button type="button" class="btn small" data-copy="<?= e($loginText) ?>"><?= icon('copy') ?>Nachricht mit Zugangsdaten kopieren</button>
</section>
<?php endif; ?>

<?php if ($user && !$user['active']): ?>
    <div class="flash error">Gesperrt seit <?= e(fmt_datetime($user['deactivated_at'])) ?><?= $user['deactivated_reason'] ? ' – Grund: ' . e($user['deactivated_reason']) : '' ?></div>
<?php endif; ?>
<?php if ($ro && $user): ?>
    <div class="flash warn"><?= $isSelf ? 'Dein eigenes Konto kannst du hier nur ansehen. Persönliche Angaben änderst du im Profil.' : 'Nur Ansicht: Dieses Konto hat denselben oder einen höheren Rang als du, oder dir fehlt die Berechtigung.' ?></div>
<?php endif; ?>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<div class="grid-side wide-side">
<form method="post" class="card form-grid">
    <h2>Stammdaten</h2>
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <h3 class="form-heading">Zugang &amp; Dienst</h3>
    <div class="field w-third"><label>Benutzername <span class="req">*</span></label><input name="username" value="<?= e($data['username']) ?>" required autocomplete="off"<?= $ro ? ' disabled' : '' ?>></div>
    <div class="field w-third"><label>Dienstnummer</label><input name="badge_number" value="<?= e($data['badge_number']) ?>" placeholder="z. B. <?= e(org('org_short')) ?>-104"<?= $ro ? ' disabled' : '' ?>></div>
    <div class="field w-third"><label>Rang <span class="req">*</span></label>
        <select name="rank_id"<?= $ro ? ' disabled' : '' ?>>
            <?php if ($user && $currentRank && !in_array((int) $currentRank['id'], $rankIds, true)): ?>
                <option value="<?= (int) $currentRank['id'] ?>" selected><?= e($currentRank['name']) ?></option>
            <?php endif; ?>
            <?php foreach ($ranks as $r): ?><option value="<?= (int) $r['id'] ?>"<?= selected($r['id'], $data['rank_id']) ?>><?= e($r['name']) ?> (Stufe <?= (int) $r['level'] ?>)</option><?php endforeach; ?>
        </select></div>

    <?php if (count(locations()) > 1): ?>
    <div class="field w-third"><label>Standort</label>
        <select name="location_id"<?= $ro ? ' disabled' : '' ?>><option value="">– Standard –</option><?php foreach (locations() as $l): ?><option value="<?= (int) $l['id'] ?>"<?= selected($l['id'], $data['location_id'] ?? '') ?>><?= e($l['name']) ?></option><?php endforeach; ?></select>
        <small class="hint">Briefkopf, der beim Ausstellen vorausgewählt ist</small></div>
    <?php endif; ?>
    <div class="field w-half"><span class="label">Abteilungen</span>
        <div class="choices vertical">
            <?php foreach ($departments as $d): $allowed = in_array((int) $d['id'], $assignableDeps, true); ?>
                <label class="choice<?= $allowed ? '' : ' locked' ?>"><input type="checkbox" name="departments[]" value="<?= (int) $d['id'] ?>"<?= checked(in_array((int) $d['id'], $userDeps, true)) ?><?= ($ro || !$allowed) ? ' disabled' : '' ?>> <?= tag_badge($d) ?> <?= e($d['name']) ?><?= $d['active'] ? '' : ' <small class="muted">(inaktiv)</small>' ?></label>
            <?php endforeach; ?>
            <?php if (!$departments): ?><span class="muted small">Noch keine Abteilungen angelegt.</span><?php endif; ?>
        </div></div>
    <div class="field w-half"><span class="label">Qualifikationen</span>
        <div class="choices vertical">
            <?php foreach ($qualifications as $qq): ?>
                <label class="choice"><input type="checkbox" name="qualifications[]" value="<?= (int) $qq['id'] ?>"<?= checked(in_array((int) $qq['id'], $userQuals, true)) ?><?= $ro ? ' disabled' : '' ?>> <?= tag_badge($qq) ?> <?= e($qq['name']) ?></label>
            <?php endforeach; ?>
            <?php if (!$qualifications): ?><span class="muted small">Noch keine Qualifikationen angelegt.</span><?php endif; ?>
        </div></div>

    <h3 class="form-heading">Angaben auf Dokumenten</h3>
    <?php require __DIR__ . '/_user_fields.php'; ?>
    <div class="field w-third"><label>Discord-ID (optional)</label><input name="discord_id" value="<?= e($data['discord_id']) ?>" placeholder="z. B. 284019347762003968" inputmode="numeric"<?= $ro ? ' disabled' : '' ?>></div>
    <?php if (!$ro): ?>
    <div class="form-actions"><button class="btn primary"><?= $user ? 'Speichern' : 'Konto anlegen' ?></button></div>
    <?php endif; ?>
</form>

<?php if ($user): ?>
<div>
    <section class="card">
        <h2>Sicherheit</h2>
        <dl class="kv">
            <dt>Passwort</dt><dd><?= $user['must_change_password'] ? '<span class="badge orange">Startpasswort, noch nicht geändert</span>' : 'geändert ' . e(fmt_datetime($user['password_changed_at']) ?: '–') ?></dd>
            <dt>2FA</dt><dd><?= $user['totp_enabled'] ? '<span class="badge green">aktiv</span>' : (in_array('security.2fa_required', rank_permissions((int) $user['rank_id']), true) ? '<span class="badge orange">Pflicht, noch nicht eingerichtet</span>' : '<span class="badge grey">nicht aktiv</span>') ?></dd>
            <dt>Unterschrift</dt><dd><?= $user['signature'] ? '<img class="sig-mini" src="' . e($user['signature']) . '" alt="">' : '<span class="muted">keine</span>' ?></dd>
        </dl>
        <?php if (!$isSelf): ?>
        <div class="action-list">
            <?php if (can_manage_user($user, 'users.password_reset')): ?>
                <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="reset_password" onclick="return confirm('Neues Einmalpasswort erzeugen? Das bisherige Passwort funktioniert danach nicht mehr.')"><?= icon('edit') ?>Passwort zurücksetzen</button></form>
            <?php endif; ?>
            <?php if (can_manage_user($user, 'users.sessions')): ?>
                <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="end_sessions" onclick="return confirm('Alle Sitzungen beenden? Die Person wird überall abgemeldet.')"><?= icon('logout') ?>Alle Sitzungen beenden</button></form>
            <?php endif; ?>
            <?php if ($user['totp_enabled'] && can_manage_user($user, 'users.2fa_reset')): ?>
                <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="reset_2fa" onclick="return confirm('2FA zurücksetzen (z. B. bei verlorenem Handy)?')"><?= icon('shield') ?>2FA zurücksetzen</button></form>
            <?php endif; ?>
            <?php if (can_manage_user($user, 'users.deactivate')): ?>
                <?php if ($user['active']): ?>
                    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="deactivate">
                        <input name="reason" placeholder="Grund, z. B. Kündigung" required maxlength="190">
                        <button class="btn danger" onclick="return confirm('Konto sperren? Die Person wird sofort abgemeldet.')">Sperren</button></form>
                <?php else: ?>
                    <form method="post"><?= csrf_field() ?><button class="btn" name="action" value="reactivate">Konto entsperren</button></form>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </section>
    <section class="card">
        <h2>Verlauf</h2>
        <table class="list compact">
            <tbody>
            <?php foreach ($history as $h): ?>
                <tr><td class="nowrap small"><?= e(fmt_datetime($h['created_at'])) ?></td>
                    <td><strong><?= e($actionLabels[$h['action']] ?? $h['action']) ?></strong> <small class="muted">durch <?= e($h['actor'] ?? 'System') ?></small>
                        <?php if (str_contains((string) $h['details'], ' – ')): ?><br><small><?= e(substr((string) $h['details'], strpos((string) $h['details'], ' – ') + 5)) ?></small><?php endif; ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$history): ?><tr><td class="muted">Keine Einträge.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
</div>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = $user ? $user['name'] : 'Neues Konto';
require SAMS_ROOT . '/app/layout.php';
