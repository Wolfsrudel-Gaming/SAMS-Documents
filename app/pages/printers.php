<?php
defined('SAMS_ROOT') || exit;
/** Drucker in der Spielwelt verwalten. Positionen lassen sich im Ingame-Tablet direkt übernehmen. */
$editId = (int) ($_GET['edit'] ?? 0);
$errors = [];
$pickupRules = [
    'own'    => 'Nur wer den Druck beauftragt hat (im Tablet angemeldet)',
    'staff'  => 'Alle im Tablet angemeldeten Mitarbeitenden',
    'anyone' => 'Jeder Spieler am Drucker (auch ohne Anmeldung)',
];

if (is_post()) {
    csrf_check();
    $action = post('action', 'save');
    $id = (int) post('id');
    if ($id && !fone('printers', $id)) {
        not_found();
    }
    if ($action === 'pickup_rule' && fid() === 1) {
        $rule = (string) post('print_pickup');
        if (isset($pickupRules[$rule])) {
            save_setting('print_pickup', $rule);
            audit('printers.pickup_rule', '', null, $rule);
            flash('success', 'Abholregel gespeichert.');
        }
        redirect(url('printers'));
    }
    if ($action === 'delete' && $id) {
        $p = one('SELECT * FROM printers WHERE id = ?', [$id]);
        q("UPDATE print_jobs SET status = 'cancelled' WHERE printer_id = ? AND status = 'queued'", [$id]);
        q('DELETE FROM printers WHERE id = ?', [$id]);
        audit('printers.delete', 'printer', $id, (string) ($p['name'] ?? ''));
        flash('success', 'Drucker gelöscht. Offene Aufträge wurden abgebrochen.');
        redirect(url('printers'));
    }
    $num = fn(string $k) => round((float) str_replace(',', '.', (string) post($k)), 3);
    $row = [
        'name' => mb_substr(trim((string) post('name')), 0, 120),
        'location' => mb_substr(trim((string) post('location')), 0, 190),
        'x' => $num('x'), 'y' => $num('y'), 'z' => $num('z'), 'heading' => round($num('heading'), 1),
        'prop_model' => array_key_exists((string) post('prop_model'), PRINT_PROPS) ? (string) post('prop_model') : 'prop_printer_01',
        'show_blip' => post('show_blip') ? 1 : 0,
        'active' => post('active') ? 1 : 0,
        'updated_at' => now(),
    ];
    if ($row['name'] === '') {
        $errors[] = 'Bitte einen Namen angeben.';
    }
    if ($row['x'] == 0.0 && $row['y'] == 0.0) {
        $errors[] = 'Bitte eine Position angeben (im Tablet: „Aktuelle Position übernehmen“).';
    }
    if ($errors) {
        $editId = $id;
    } else {
        if ($id) {
            update('printers', $id, $row);
            audit('printers.update', 'printer', $id, $row['name']);
        } else {
            $id = insert('printers', $row + ['created_by' => current_user()['id'], 'created_at' => now()]);
            audit('printers.create', 'printer', $id, $row['name']);
        }
        flash('success', 'Drucker gespeichert. Die Spielwelt übernimmt die Änderung innerhalb einer Minute.');
        redirect(url('printers'));
    }
}

$printers = all("SELECT p.*, (SELECT COUNT(*) FROM print_jobs j WHERE j.printer_id = p.id AND j.status = 'queued') AS queued,
    (SELECT COUNT(*) FROM print_jobs j WHERE j.printer_id = p.id AND j.status = 'printed') AS printed FROM printers p WHERE " . fw('p') . " ORDER BY p.name");
$edit = $editId ? fone('printers', $editId) : null;
$form = $edit ?? ['id' => 0, 'name' => '', 'location' => '', 'x' => '', 'y' => '', 'z' => '', 'heading' => '', 'prop_model' => 'prop_printer_01', 'show_blip' => 0, 'active' => 1];
if ($errors) {
    $form = array_merge($form, array_intersect_key($_POST, $form));
}
$recent = all('SELECT j.*, p.name AS printer, d.number, d.type, d.id AS doc_id, u.name AS requester FROM print_jobs j
    JOIN printers p ON p.id = j.printer_id JOIN documents d ON d.id = j.document_id LEFT JOIN users u ON u.id = j.requested_by
    WHERE ' . fw('p') . ' ORDER BY j.id DESC LIMIT 25');
$statusLabels = ['queued' => ['orange', 'wartet'], 'printed' => ['green', 'abgeholt'], 'cancelled' => ['grey', 'abgebrochen']];

ob_start(); ?>
<div class="page-head">
    <div><h1>Drucker</h1><p class="muted">Drucker in der Spielwelt, an denen ausgestellte Dokumente als Item abgeholt werden.</p></div>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<div class="grid-side wide-side reverse">
    <div>
        <section class="card">
            <h2>Drucker</h2>
            <table class="list">
                <thead><tr><th>Name</th><th>Standort</th><th>Position</th><th>Prop</th><th class="right">Wartend</th><th class="right">Gedruckt</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($printers as $p): ?>
                    <tr class="<?= $p['active'] ? '' : 'inactive' ?>">
                        <td><strong><?= e($p['name']) ?></strong><?= $p['active'] ? '' : ' <span class="badge grey">inaktiv</span>' ?><?= $p['show_blip'] ? ' <span class="badge blue">Karte</span>' : '' ?></td>
                        <td class="small"><?= e($p['location']) ?></td>
                        <td class="mono small"><?= e(sprintf('%.1f, %.1f, %.1f', $p['x'], $p['y'], $p['z'])) ?></td>
                        <td class="mono small"><?= e($p['prop_model'] ?: '–') ?></td>
                        <td class="right"><?= (int) $p['queued'] ?></td>
                        <td class="right"><?= (int) $p['printed'] ?></td>
                        <td class="right"><a href="<?= e(url('printers', ['edit' => $p['id']])) ?>">bearbeiten</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$printers): ?><tr><td colspan="7" class="muted">Noch keine Drucker. Stell dich im Spiel an die gewünschte Stelle, öffne das Tablet und lege hier einen Drucker an.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </section>
        <section class="card">
            <h2>Letzte Druckaufträge</h2>
            <table class="list compact">
                <thead><tr><th>Zeitpunkt</th><th>Dokument</th><th>Drucker</th><th>Auftraggeber</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $j): [$sc, $sl] = $statusLabels[$j['status']] ?? ['grey', $j['status']]; ?>
                    <tr><td class="small"><?= e(fmt_datetime($j['requested_at'])) ?></td>
                        <td><a class="mono small" href="<?= e(url('document', ['id' => $j['doc_id']])) ?>"><?= e($j['number']) ?></a> <small>× <?= (int) $j['copies'] ?></small></td>
                        <td class="small"><?= e($j['printer']) ?></td><td class="small"><?= e($j['requester']) ?></td>
                        <td><span class="badge <?= $sc ?>"><?= e($sl) ?></span><?= $j['picked_up_by'] ? ' <small class="muted">' . e($j['picked_up_by']) . '</small>' : '' ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$recent): ?><tr><td colspan="5" class="muted">Noch keine Druckaufträge.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
    <div>
        <form method="post" class="card form-grid" id="printer-form">
            <h2><?= $edit ? 'Drucker bearbeiten' : 'Neuer Drucker' ?></h2>
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
            <div class="field w-full"><label>Name <span class="req">*</span></label><input name="name" value="<?= e($form['name']) ?>" required placeholder="z. B. Drucker Notaufnahme"></div>
            <div class="field w-full"><label>Standort-Beschreibung</label><input name="location" value="<?= e($form['location']) ?>" placeholder="z. B. Pillbox, 1. OG, Anmeldung"></div>
            <div class="field w-full" data-game-only hidden>
                <button type="button" class="btn primary block" id="use-position"><?= icon('home') ?>Aktuelle Position übernehmen</button>
                <div class="hint">Stell dich mit deiner Figur direkt vor die Stelle, an der der Drucker stehen soll, und schau in die gewünschte Richtung.</div>
            </div>
            <div class="field w-half"><label>X</label><input name="x" value="<?= e($form['x']) ?>" inputmode="decimal" required></div>
            <div class="field w-half"><label>Y</label><input name="y" value="<?= e($form['y']) ?>" inputmode="decimal" required></div>
            <div class="field w-half"><label>Z</label><input name="z" value="<?= e($form['z']) ?>" inputmode="decimal" required></div>
            <div class="field w-half"><label>Ausrichtung (°)</label><input name="heading" value="<?= e($form['heading']) ?>" inputmode="decimal"></div>
            <div class="field w-full"><label>Objekt in der Spielwelt</label>
                <select name="prop_model"><?php foreach (PRINT_PROPS as $k => $l): ?><option value="<?= e($k) ?>"<?= selected($k, $form['prop_model']) ?>><?= e($l) ?><?= $k ? " ($k)" : '' ?></option><?php endforeach; ?></select></div>
            <div class="field w-full">
                <label class="choice single"><input type="checkbox" name="show_blip" value="1"<?= checked($form['show_blip']) ?>> Auf der Karte anzeigen (nur für angemeldete Mitarbeitende)</label>
                <label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($form['active']) ?>> aktiv</label>
            </div>
            <div class="form-actions">
                <button class="btn primary">Speichern</button>
                <?php if ($edit): ?>
                    <a class="btn" href="<?= e(url('printers')) ?>">Abbrechen</a>
                    <button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Drucker löschen? Offene Aufträge werden abgebrochen.')">Löschen</button>
                <?php endif; ?>
            </div>
        </form>
        <?php if (fid() === 1): ?>
        <form method="post" class="card form-grid">
            <h2>Abholung</h2>
            <?= csrf_field() ?><input type="hidden" name="action" value="pickup_rule">
            <div class="field w-full"><span class="label">Wer darf Dokumente am Drucker abholen?</span>
                <div class="choices vertical">
                    <?php foreach ($pickupRules as $k => $l): ?><label class="choice"><input type="radio" name="print_pickup" value="<?= e($k) ?>"<?= checked(pickup_rule() === $k) ?>> <?= e($l) ?></label><?php endforeach; ?>
                </div></div>
            <div class="form-actions"><button class="btn">Speichern</button></div>
        </form>
        <?php endif; ?>
    </div>
</div>
<script>
    (function () {
        var btn = document.getElementById('use-position');
        if (!btn) { return; }
        btn.addEventListener('click', function () {
            if (!window.SAMSGame) { return; }
            btn.disabled = true;
            window.SAMSGame.request('getCoords').then(function (c) {
                var f = document.getElementById('printer-form');
                // Drucker leicht vor die Figur setzen, damit man davor stehen kann
                var rad = (c.h || 0) * Math.PI / 180;
                f.x.value = (c.x - Math.sin(rad) * 0.8).toFixed(2);
                f.y.value = (c.y + Math.cos(rad) * 0.8).toFixed(2);
                f.z.value = (c.ground != null ? c.ground : c.z - 1.0).toFixed(2);
                f.heading.value = (((c.h || 0) + 180) % 360).toFixed(1);
                if (!f.location.value && c.street) { f.location.value = c.street; }
            }).catch(function (e) { alert(e.message); }).finally(function () { btn.disabled = false; });
        });
    })();
</script>
<?php
$content = ob_get_clean();
$title = 'Drucker';
require SAMS_ROOT . '/app/layout.php';
