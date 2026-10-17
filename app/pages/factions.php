<?php
defined('SAMS_ROOT') || exit;
/** Fraktionen verwalten (nur Hauptadministrator): anlegen, Module wählen, Ansicht wechseln. */
$me = current_user();
if (empty($me['is_superadmin'])) {
    forbidden('Fraktionen verwaltet nur der Hauptadministrator.');
}

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'switch') {
        $f = faction_by_id((int) post('faction_id'));
        if ($f) {
            $_SESSION['faction_view'] = (int) $f['id'];
            flash('success', 'Ansicht: ' . $f['name']);
        }
        $return = (string) post('return');
        redirect(preg_match('/^[a-z_]+$/', $return) ? url($return) : url('dashboard'));
    }
    if ($action === 'create') {
        $name = mb_substr(trim((string) post('name')), 0, 120);
        $short = mb_substr(trim((string) post('short')), 0, 32);
        $kind = (string) post('kind');
        if ($name === '' || !isset(FACTION_KINDS[$kind])) {
            flash('error', 'Bitte Name und Art angeben.');
            redirect(url('factions'));
        }
        $id = create_faction($name, $short, $kind, preg_match('/^#[0-9a-f]{6}$/i', (string) post('color')) ? (string) post('color') : '');
        audit('faction.create', 'faction', $id, $name . ' (' . FACTION_KINDS[$kind]['label'] . ')');
        $msg = 'Fraktion „' . $name . '“ angelegt – mit Rängen, Abteilungen, Briefkopf und Handbuch.';
        $username = trim((string) post('lead_username'));
        if ($username !== '') {
            if (!preg_match('/^[A-Za-z0-9._\-]{3,40}$/', $username) || scalar('SELECT id FROM users WHERE username = ?', [$username])) {
                flash('error', 'Leitungskonto nicht angelegt: Benutzername ungültig oder vergeben.');
            } else {
                $pw = generate_password();
                $rank = one('SELECT * FROM ranks WHERE faction_id = ? ORDER BY level DESC LIMIT 1', [$id]);
                $uid = insert('users', ['username' => $username, 'name' => mb_substr(trim((string) post('lead_name')) ?: $username, 0, 190), 'faction_id' => $id,
                    'rank_id' => $rank['id'] ?? null, 'password_hash' => password_hash($pw, PASSWORD_DEFAULT), 'must_change_password' => 1, 'role' => 'medic',
                    'active' => 1, 'created_by' => $me['id'], 'created_at' => now()]);
                audit('user.create', 'user', (int) $uid, $username . ' – Leitung ' . $name);
                $msg .= ' Leitungskonto „' . $username . '“ mit Startpasswort ' . $pw . ' (beim ersten Login ändern).';
            }
        }
        flash('success', $msg);
        redirect(url('factions'));
    }
    if ($action === 'save' && ($f = faction_by_id((int) post('id')))) {
        $modules = array_values(array_intersect((array) ($_POST['modules'] ?? []), array_keys(FACTION_MODULES)));
        update('factions', (int) $f['id'], [
            'name' => mb_substr(trim((string) post('name')) ?: $f['name'], 0, 120),
            'short' => mb_substr(trim((string) post('short')), 0, 32),
            'color' => preg_match('/^#[0-9a-f]{6}$/i', (string) post('color')) ? (string) post('color') : $f['color'],
            'active' => (int) $f['id'] === 1 ? 1 : (post('active') ? 1 : 0),
            'modules' => json_encode($modules),
        ]);
        audit('faction.update', 'faction', (int) $f['id'], $f['name'] . ': Module ' . implode(', ', $modules));
        flash('success', 'Fraktion gespeichert.');
        redirect(url('factions'));
    }
}

$rows = factions(false);
$members = [];
foreach (all('SELECT faction_id, COUNT(*) AS n FROM users WHERE active = 1 GROUP BY faction_id') as $r) {
    $members[(int) $r['faction_id']] = (int) $r['n'];
}
$edit = isset($_GET['edit']) ? faction_by_id((int) $_GET['edit']) : null;

ob_start(); ?>
<div class="page-head">
    <div><h1>Fraktionen</h1><p class="muted">Jede Fraktion hat eigene Ränge, Abteilungen, Akten, Dokumente, Einstellungen und Briefköpfe. Daten anderer Fraktionen sind nur über Freigaben und Anfragen sichtbar.</p></div>
</div>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Fraktion</th><th>Art</th><th>Module</th><th class="right">Konten</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $f): ?>
            <tr class="<?= (int) $f['id'] === fid() ? 'selected' : '' ?>">
                <td><span class="dot-color" style="background:<?= e($f['color'] ?: '#64717f') ?>"></span> <strong><?= e($f['name']) ?></strong><?= $f['short'] ? ' <span class="badge">' . e($f['short']) . '</span>' : '' ?><?= $f['active'] ? '' : ' <span class="badge grey">inaktiv</span>' ?></td>
                <td class="small"><?= e(FACTION_KINDS[$f['kind']]['label'] ?? $f['kind']) ?></td>
                <td class="small"><?= e(implode(', ', array_map(fn($m) => explode(' ', FACTION_MODULES[$m])[0], faction_modules((int) $f['id'])))) ?: '–' ?></td>
                <td class="right"><?= $members[(int) $f['id']] ?? 0 ?></td>
                <td class="right nowrap">
                    <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="switch"><input type="hidden" name="faction_id" value="<?= (int) $f['id'] ?>"><button class="btn small"<?= (int) $f['id'] === fid() ? ' disabled' : '' ?>>Ansicht</button></form>
                    <a class="btn small" href="<?= e(url('factions', ['edit' => $f['id']])) ?>#fac-form">Bearbeiten</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="hint">„Ansicht“ wechselt deine Sicht als Hauptadministrator in die Fraktion – Menü, Rechte, Einstellungen und Daten gelten dann für diese Fraktion.</p>
</section>
<section>
<?php if ($edit): ?>
    <form method="post" class="card form-grid" id="fac-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
        <h2 class="w-full">Fraktion bearbeiten</h2>
        <div class="field w-full"><label>Name</label><input name="name" required maxlength="120" value="<?= e($edit['name']) ?>"></div>
        <div class="field"><label>Kürzel</label><input name="short" maxlength="32" value="<?= e($edit['short']) ?>"></div>
        <div class="field"><label>Farbe</label><input type="color" name="color" value="<?= e($edit['color'] ?: '#0b4f9c') ?>"></div>
        <div class="field w-full"><span class="label">Module (<?= e(FACTION_KINDS[$edit['kind']]['label'] ?? '') ?>)</span>
            <div class="choices vertical"><?php foreach (FACTION_MODULES as $m => $label): ?><label class="choice"><input type="checkbox" name="modules[]" value="<?= e($m) ?>"<?= checked(module_enabled($m, (int) $edit['id'])) ?>> <?= e($label) ?></label><?php endforeach; ?></div>
            <small class="hint">Grundfunktionen (Personal, Dokumente, Dienstzeit, Dienstplan, Ausbildung, Wiki, Kommunikation, Drucken) haben alle Fraktionen.</small></div>
        <?php if ((int) $edit['id'] !== 1): ?><div class="field w-full"><label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($edit['active']) ?>> aktiv (inaktive Fraktionen: Konten können sich nicht mehr anmelden)</label></div><?php endif; ?>
        <div class="form-actions"><a class="btn" href="<?= e(url('factions')) ?>">Abbrechen</a><button class="btn primary">Speichern</button></div>
    </form>
<?php endif; ?>
    <form method="post" class="card form-grid">
        <?= csrf_field() ?><input type="hidden" name="action" value="create">
        <h2 class="w-full">Fraktion anlegen</h2>
        <div class="field w-full"><label>Name</label><input name="name" required maxlength="120" placeholder="z. B. Los Santos Police Department"></div>
        <div class="field"><label>Kürzel</label><input name="short" maxlength="32" placeholder="LSPD"></div>
        <div class="field"><label>Art</label><select name="kind"><?php foreach (FACTION_KINDS as $k => $kd): if ($k === 'medical') continue; ?><option value="<?= e($k) ?>"><?= e($kd['label']) ?></option><?php endforeach; ?><option value="medical">Rettungsdienst / Klinik (weitere)</option></select></div>
        <div class="field"><label>Farbe</label><input type="color" name="color" value="#1d3a6e"></div>
        <h3 class="form-heading">Leitungskonto (optional)</h3>
        <div class="field"><label>Benutzername</label><input name="lead_username" maxlength="40" placeholder="z. B. chief.miller"></div>
        <div class="field"><label>Name</label><input name="lead_name" maxlength="190" placeholder="Vorname Nachname"></div>
        <p class="hint w-full">Angelegt werden passende Ränge mit Rechten, Abteilungen, Briefkopf, Logo und ein Handbuch im Wiki – alles anschließend frei änderbar. Das Leitungskonto bekommt den höchsten Rang.</p>
        <div class="form-actions"><button class="btn primary">Anlegen</button></div>
    </form>
</section>
</div>
<?php
$content = ob_get_clean();
$title = 'Fraktionen';
require SAMS_ROOT . '/app/layout.php';
