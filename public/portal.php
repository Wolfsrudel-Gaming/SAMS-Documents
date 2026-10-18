<?php
/**
 * Bürgerportal: Anmeldung mit Discord, eigene Dokumente, Termine, Anträge, Bewerbungen.
 * Getrennt vom Websystem des Personals (eigene Seite, eigener Anmeldestatus).
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';
start_session();

$self = 'portal.php';
$purl = fn(array $q = []) => $self . ($q ? '?' . http_build_query($q) : '');
$redirectUri = base_url() . '/portal.php?action=callback';
$action = (string) ($_GET['action'] ?? '');
$page = (string) ($_GET['page'] ?? 'home');
$devLogin = (bool) cfg('portal_dev_login', false);
if ((is_post() || in_array($action, ['login', 'callback'], true)) && !rate_limit('portal', 40)) {
    rate_limited_exit();
}

if (!portal_enabled()) {
    http_response_code(503);
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;padding:2rem">Das Bürgerportal ist derzeit nicht verfügbar.</body>');
}

// ------------------------------------------------------------- Anmeldung
if ($action === 'login') {
    if (!discord_configured()) {
        flash('error', 'Die Discord-Anmeldung ist noch nicht eingerichtet.');
        redirect($purl());
    }
    redirect(discord_authorize_url($redirectUri));
}
if ($action === 'callback') {
    $profile = discord_configured() ? discord_callback($redirectUri) : null;
    if (!$profile) {
        flash('error', 'Die Anmeldung mit Discord ist fehlgeschlagen. Bitte erneut versuchen.');
        redirect($purl());
    }
    citizen_login($profile);
    redirect($purl());
}
if ($action === 'devlogin' && $devLogin && is_post()) {
    csrf_check();
    $id = preg_replace('/\D/', '', (string) post('discord_id'));
    if ($id !== '') {
        citizen_login(['id' => $id, 'global_name' => trim((string) post('name')) ?: 'Testbürger ' . $id]);
    }
    redirect($purl());
}
if ($action === 'logout' && is_post()) {
    csrf_check();
    unset($_SESSION['citizen_id']);
    redirect($purl());
}

$c = current_citizen();

// -------------------------------------------------------------- Aktionen
if ($c && is_post()) {
    csrf_check();
    $do = (string) post('do');
    if ($do === 'profile') {
        update('citizens', (int) $c['id'], ['ic_name' => mb_substr(trim((string) post('ic_name')), 0, 120), 'phone' => mb_substr(trim((string) post('phone')), 0, 40)]);
        flash('success', 'Angaben gespeichert.');
        redirect($purl());
    }
    if ($do === 'appointment') {
        $kind = in_array(post('kind'), appointment_kinds(), true) ? (string) post('kind') : appointment_kinds()[0];
        $open = (int) scalar("SELECT COUNT(*) FROM appointments WHERE citizen_id = ? AND status = 'requested'", [$c['id']]);
        if ($open >= 3) {
            flash('error', 'Du hast bereits drei offene Terminanfragen.');
        } elseif (trim((string) $c['ic_name']) === '' && !$c['patient_id']) {
            flash('error', 'Bitte trage zuerst unter „Meine Angaben“ deinen Namen (im Spiel) ein.');
        } else {
            insert('appointments', ['citizen_id' => $c['id'], 'patient_id' => $c['patient_id'], 'kind' => $kind,
                'preferred' => mb_substr(trim((string) post('preferred')), 0, 190), 'note' => mb_substr(trim((string) post('note')), 0, 2000), 'created_at' => now()]);
            notify_webhook('portal', log_embed('📅 Terminanfrage: ' . $kind, ($c['ic_name'] ?: $c['discord_name']) . ($c['patient_id'] ? '' : ' (nicht verknüpft)') . ' · Wunsch: ' . (trim((string) post('preferred')) ?: '–'), 0x0b4f9c));
            flash('success', 'Terminanfrage gesendet. Du siehst hier, sobald ' . org('org_short') . ' den Termin bestätigt.');
        }
        redirect($purl(['page' => 'appointments']));
    }
    if ($do === 'cancel_appointment') {
        q("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND citizen_id = ? AND status IN ('requested', 'confirmed')", [(int) post('id'), $c['id']]);
        redirect($purl(['page' => 'appointments']));
    }
    if ($do === 'request') {
        $kind = array_key_exists((string) post('kind'), REQUEST_KINDS) ? (string) post('kind') : 'sonstiges';
        $text = mb_substr(trim((string) post('text')), 0, 5000);
        if (in_array($kind, ['aktenkopie', 'schweigepflicht'], true) && !$c['patient_id']) {
            flash('error', 'Dafür muss dein Konto zuerst mit deiner Patientenakte verknüpft sein.');
        } elseif ($text === '' && $kind !== 'aktenkopie') {
            flash('error', 'Bitte beschreibe dein Anliegen.');
        } else {
            insert('portal_requests', ['citizen_id' => $c['id'], 'patient_id' => $c['patient_id'], 'kind' => $kind,
                'subject' => mb_substr(trim((string) post('subject')), 0, 190), 'text' => $text, 'created_at' => now()]);
            notify_webhook('portal', log_embed('📝 Antrag: ' . REQUEST_KINDS[$kind], ($c['ic_name'] ?: $c['discord_name']), 0x0b4f9c));
            flash('success', 'Antrag eingereicht.');
        }
        redirect($purl(['page' => 'requests']));
    }
    if ($do === 'apply') {
        $row = [
            'ic_name' => mb_substr(trim((string) post('ic_name')), 0, 120), 'age' => max(0, min(120, (int) post('age'))),
            'phone' => mb_substr(trim((string) post('phone')), 0, 40), 'department' => mb_substr(trim((string) post('department')), 0, 120),
            'experience' => mb_substr(trim((string) post('experience')), 0, 5000), 'motivation' => mb_substr(trim((string) post('motivation')), 0, 5000),
            'availability' => mb_substr(trim((string) post('availability')), 0, 190),
        ];
        // „Fraktion|Bereich“ aus der Auswahl
        [$applyFaction, $applyDep] = array_pad(explode('|', (string) post('department'), 2), 2, '');
        $pf = array_column(portal_factions(), null, 'id');
        $row['faction_id'] = isset($pf[(int) $applyFaction]) ? (int) $applyFaction : 1;
        $row['department'] = mb_substr(trim($applyDep), 0, 120);
        if ($row['ic_name'] === '' || $row['age'] < 16 || mb_strlen($row['motivation']) < 50) {
            flash('error', 'Bitte Namen, Alter (mind. 16) und eine Motivation mit mindestens 50 Zeichen angeben.');
        } elseif (scalar("SELECT COUNT(*) FROM applications WHERE citizen_id = ? AND faction_id = ? AND status IN ('new', 'invited')", [$c['id'], $row['faction_id']])) {
            flash('error', 'Du hast bereits eine laufende Bewerbung.');
        } else {
            insert('applications', $row + ['citizen_id' => $c['id'], 'created_at' => now()]);
            notify_webhook('portal', log_embed('🙋 Neue Bewerbung: ' . $row['ic_name'], 'Bereich: ' . ($row['department'] ?: 'egal') . ' · Discord: ' . $c['discord_name'], 0x1e7a43));
            flash('success', 'Vielen Dank für deine Bewerbung! Wir melden uns.');
        }
        redirect($purl(['page' => 'apply']));
    }
}

$patient = $c && $c['patient_id'] ? one('SELECT * FROM patients WHERE id = ?', [$c['patient_id']]) : null;
$tabs = ['home' => 'Start', 'documents' => 'Meine Dokumente', 'appointments' => 'Termine', 'requests' => 'Anträge', 'apply' => 'Bewerben'];
if (!isset($tabs[$page])) {
    $page = 'home';
}
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Bürgerportal · <?= e(org('org_short')) ?></title>
<link rel="icon" href="<?= e(faction_logo()) ?>">
<link rel="stylesheet" href="assets/css/app.css?v=<?= e(SAMS_VERSION) ?>">
<?= theme_style() ?>
</head>
<body class="no-nav portal">
<header class="portal-head">
    <div class="portal-brand"><img src="<?= e(faction_logo()) ?>" alt=""><div><strong><?= e(org('org_name')) ?></strong><span>Bürgerportal</span></div></div>
    <?php if ($c): ?>
        <form method="post" action="<?= e($purl(['action' => 'logout'])) ?>" class="portal-user">
            <?php if ($c['avatar']): ?><img src="<?= e($c['avatar']) ?>" alt="" referrerpolicy="no-referrer"><?php endif; ?>
            <span><?= e($c['ic_name'] ?: $c['discord_name']) ?></span><?= csrf_field() ?><button class="btn small">Abmelden</button>
        </form>
    <?php endif; ?>
</header>
<main class="portal-main">
    <?php foreach (flashes() as [$type, $msg]): ?><div class="flash <?= e($type) ?>"><?= e($msg) ?></div><?php endforeach; ?>

<?php if (!$c): ?>
    <section class="card portal-login">
        <h1>Willkommen im Bürgerportal</h1>
        <div class="prose-ui"><?= light_markup((string) setting('portal_intro', "Hier siehst du deine ärztlichen Dokumente, fragst Termine an (z. B. für die Waffenschein-Untersuchung), stellst Anträge und kannst dich beim " . org('org_short') . " bewerben.")) ?></div>
        <?php if (discord_configured()): ?>
            <a class="btn primary discord-btn" href="<?= e($purl(['action' => 'login'])) ?>">Mit Discord anmelden</a>
            <p class="hint">Wir erhalten von Discord nur deinen Benutzernamen und deine ID.</p>
        <?php else: ?>
            <p class="muted">Die Anmeldung mit Discord ist noch nicht eingerichtet.</p>
        <?php endif; ?>
        <?php if ($devLogin): ?>
            <form method="post" action="<?= e($purl(['action' => 'devlogin'])) ?>" class="form-grid dev-login">
                <?= csrf_field() ?><h3 class="form-heading">Testanmeldung (PORTAL_DEV_LOGIN – nicht für den Live-Betrieb)</h3>
                <div class="field w-half"><label>Discord-ID</label><input name="discord_id" inputmode="numeric" required></div>
                <div class="field w-half"><label>Name</label><input name="name"></div>
                <div class="form-actions"><button class="btn">Testanmeldung</button></div>
            </form>
        <?php endif; ?>
    </section>
<?php else: ?>
    <nav class="tabs portal-tabs"><?php foreach ($tabs as $k => $l): ?><a class="<?= $page === $k ? 'active' : '' ?>" href="<?= e($purl(['page' => $k])) ?>"><?= e($l) ?></a><?php endforeach; ?></nav>

    <?php if ($page === 'home'): ?>
        <div class="grid-2">
            <section class="card">
                <h2>Patientenakte</h2>
                <?php if ($patient): ?>
                    <p>Dein Konto ist mit der Akte von <strong><?= e(patient_name($patient)) ?></strong> (geb. <?= e(fmt_date($patient['dob'])) ?>) verknüpft.</p>
                    <p><a class="btn" href="<?= e($purl(['page' => 'documents'])) ?>">Meine Dokumente</a></p>
                <?php else: ?>
                    <p>Um deine Dokumente zu sehen, verknüpft ein Mitarbeiter des <?= e(org('org_short')) ?> dein Konto mit deiner Patientenakte. Nenne dafür bei deinem nächsten Besuch diesen Code:</p>
                    <div class="link-code"><?= e(citizen_link_code($c)) ?></div>
                    <p class="hint">Der Code ist 24 Stunden gültig und wird danach automatisch erneuert.</p>
                <?php endif; ?>
            </section>
            <form method="post" class="card form-grid">
                <h2>Meine Angaben</h2>
                <?= csrf_field() ?><input type="hidden" name="do" value="profile">
                <div class="field w-full"><label>Name im Spiel (Vorname Nachname)</label><input name="ic_name" value="<?= e($c['ic_name']) ?>" maxlength="120"></div>
                <div class="field w-full"><label>Telefonnummer im Spiel</label><input name="phone" value="<?= e($c['phone']) ?>" maxlength="40"></div>
                <div class="form-actions"><button class="btn">Speichern</button></div>
            </form>
        </div>

    <?php elseif ($page === 'documents'): $docs = portal_documents($c); ?>
        <section class="card">
            <h2>Meine Dokumente</h2>
            <?php if (!$patient): ?><p class="muted">Dein Konto ist noch nicht mit einer Patientenakte verknüpft (siehe Start).</p>
            <?php else: ?>
            <table class="list">
                <tbody>
                <?php foreach ($docs as $d): $t = doc_type($d['type']); ?>
                    <tr><td><span class="badge blue"><?= e($t['code'] ?? '?') ?></span> <strong><?= e($t['title'] ?? $d['type']) ?></strong><br><small class="mono muted"><?= e($d['number']) ?></small></td>
                        <td class="small"><?= e(fmt_date($d['issued_at'])) ?></td><td><?= doc_status_badge($d) ?></td>
                        <td class="right"><a class="btn small" href="doc.php?t=<?= e(rawurlencode((string) $d['view_token'])) ?><?= $d['type'] === 'au' ? '&amp;copy=versicherter' : '' ?>" target="_blank">Ansehen / Drucken</a></td></tr>
                <?php endforeach; ?>
                <?php if (!$docs): ?><tr><td class="muted">Noch keine Dokumente.</td></tr><?php endif; ?>
                </tbody>
            </table>
            <p class="hint">Bestimmte vertrauliche Unterlagen (z. B. Gutachten) erhältst du auf Antrag.</p>
            <?php endif; ?>
        </section>

    <?php elseif ($page === 'appointments'): $appts = all('SELECT * FROM appointments WHERE citizen_id = ? ORDER BY id DESC LIMIT 30', [$c['id']]); ?>
        <div class="grid-side wide-side reverse">
            <section class="card">
                <h2>Meine Termine</h2>
                <table class="list">
                    <tbody>
                    <?php foreach ($appts as $a): ?>
                        <tr><td><strong><?= e($a['kind']) ?></strong><br><small class="muted">angefragt <?= e(fmt_datetime($a['created_at'])) ?><?= $a['preferred'] ? ' · Wunsch: ' . e($a['preferred']) : '' ?></small>
                            <?php if ($a['status'] === 'confirmed'): ?><br><strong class="text-ok">Termin: <?= e(fmt_datetime($a['scheduled_at'])) ?><?= $a['location'] ? ' · ' . e($a['location']) : '' ?></strong><?php endif; ?>
                            <?php if ($a['staff_note']): ?><br><small>Hinweis des <?= e(org('org_short')) ?>: <?= e($a['staff_note']) ?></small><?php endif; ?></td>
                            <td><?= portal_status_badge($a['status']) ?></td>
                            <td class="right"><?php if (in_array($a['status'], ['requested', 'confirmed'], true)): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="do" value="cancel_appointment"><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><button class="btn small" onclick="return confirm('Termin absagen?')">Absagen</button></form><?php endif; ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$appts): ?><tr><td class="muted">Noch keine Termine.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </section>
            <form method="post" class="card form-grid">
                <h2>Termin anfragen</h2>
                <?= csrf_field() ?><input type="hidden" name="do" value="appointment">
                <div class="field w-full"><label>Anliegen</label><select name="kind"><?php foreach (appointment_kinds() as $k): ?><option><?= e($k) ?></option><?php endforeach; ?></select></div>
                <div class="field w-full"><label>Wunschtermin</label><input name="preferred" maxlength="190" placeholder="z. B. Freitag ab 20 Uhr"></div>
                <div class="field w-full"><label>Bemerkung</label><textarea name="note" rows="3"></textarea></div>
                <div class="form-actions"><button class="btn primary">Anfrage senden</button></div>
            </form>
        </div>

    <?php elseif ($page === 'requests'): $reqs = all('SELECT * FROM portal_requests WHERE citizen_id = ? ORDER BY id DESC LIMIT 30', [$c['id']]); ?>
        <div class="grid-side wide-side reverse">
            <section class="card">
                <h2>Meine Anträge</h2>
                <table class="list">
                    <tbody>
                    <?php foreach ($reqs as $r): ?>
                        <tr><td><strong><?= e(REQUEST_KINDS[$r['kind']] ?? $r['kind']) ?></strong><?= $r['subject'] ? ' – ' . e($r['subject']) : '' ?><br><small class="muted"><?= e(fmt_datetime($r['created_at'])) ?></small>
                            <?php if ($r['reply']): ?><div class="portal-reply"><strong>Antwort des <?= e(org('org_short')) ?>:</strong> <?= nl($r['reply']) ?></div><?php endif; ?></td>
                            <td><?= portal_status_badge($r['status']) ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (!$reqs): ?><tr><td class="muted">Noch keine Anträge.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </section>
            <form method="post" class="card form-grid">
                <h2>Neuer Antrag</h2>
                <?= csrf_field() ?><input type="hidden" name="do" value="request">
                <div class="field w-full"><label>Art</label><select name="kind"><?php foreach (REQUEST_KINDS as $k => $l): ?><option value="<?= e($k) ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
                <div class="field w-full"><label>Betreff</label><input name="subject" maxlength="190" placeholder="z. B. Entbindung gegenüber LSPD für Verfahren 0815"></div>
                <div class="field w-full"><label>Beschreibung</label><textarea name="text" rows="5"></textarea></div>
                <div class="form-actions"><button class="btn primary">Einreichen</button></div>
            </form>
        </div>

    <?php elseif ($page === 'apply'): $apps = all('SELECT * FROM applications WHERE citizen_id = ? ORDER BY id DESC', [$c['id']]); ?>
        <div class="grid-side wide-side reverse">
            <section class="card">
                <h2>Meine Bewerbungen</h2>
                <?php foreach ($apps as $a): ?>
                    <p><strong><?= e((faction_by_id((int) $a['faction_id'])['short'] ?? '') . ' · ' . ($a['department'] ?: 'Allgemein')) ?></strong> · <?= e(fmt_date($a['created_at'])) ?> <?= portal_status_badge($a['status']) ?></p>
                <?php endforeach; ?>
                <?php if (!$apps): ?><p class="muted">Du hast dich noch nicht beworben.</p><?php endif; ?>
            </section>
            <form method="post" class="card form-grid">
                <h2>Bewerbung</h2>
                <?= csrf_field() ?><input type="hidden" name="do" value="apply">
                <div class="field w-half"><label>Name im Spiel</label><input name="ic_name" value="<?= e($c['ic_name']) ?>" required></div>
                <div class="field w-quarter"><label>Alter (Charakter)</label><input type="number" name="age" min="16" max="120" required></div>
                <div class="field w-quarter"><label>Telefon</label><input name="phone" value="<?= e($c['phone']) ?>"></div>
                <div class="field w-half"><label>Behörde und Bereich</label><select name="department" required>
                    <?php foreach (portal_factions() as $pf): ?><optgroup label="<?= e($pf['name']) ?>"><option value="<?= (int) $pf['id'] ?>|">egal</option>
                        <?php foreach (db_column_list('SELECT name FROM departments WHERE active = 1 AND faction_id = ' . (int) $pf['id'] . ' ORDER BY sort, name') as $d): ?><option value="<?= (int) $pf['id'] ?>|<?= e($d) ?>"><?= e($d) ?></option><?php endforeach; ?></optgroup><?php endforeach; ?></select></div>
                <div class="field w-half"><label>Verfügbarkeit</label><input name="availability" placeholder="z. B. abends unter der Woche"></div>
                <div class="field w-full"><label>Erfahrung (im Spiel und im RP)</label><textarea name="experience" rows="3"></textarea></div>
                <div class="field w-full"><label>Warum möchtest du zu uns? (mind. 50 Zeichen)</label><textarea name="motivation" rows="5" required minlength="50"></textarea></div>
                <div class="form-actions"><button class="btn primary">Bewerbung absenden</button></div>
            </form>
        </div>
    <?php endif; ?>
<?php endif; ?>
</main>
<footer class="portal-foot"><?= e(org('org_name')) ?> · <?= e(org('org_street')) ?>, <?= e(org('org_city')) ?> · <a href="verify.php">Echtheitsprüfung</a></footer>
</body>
</html>
