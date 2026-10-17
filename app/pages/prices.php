<?php
defined('SAMS_ROOT') || exit;
/** Gebührenordnung: Leistungen mit festem Preis. Abweichungen in Rechnungen brauchen eine Begründung. */
$errors = [];
if (is_post()) {
    csrf_check();
    $id = (int) post('id');
    if ($id && !fone('price_list', $id)) {
        not_found();
    }
    if (post('action') === 'delete' && $id) {
        // Bestehende Rechnungen behalten ihren Text, daher nur deaktivieren statt löschen
        update('price_list', $id, ['active' => 0, 'updated_at' => now()]);
        audit('prices.deactivate', '', $id, (string) scalar('SELECT name FROM price_list WHERE id = ?', [$id]));
        flash('success', 'Leistung deaktiviert.');
        redirect(url('prices'));
    }
    $row = [
        'code' => mb_substr(strtoupper(trim((string) post('code'))), 0, 32),
        'name' => mb_substr(trim((string) post('name')), 0, 190),
        'category' => mb_substr(trim((string) post('category')), 0, 190),
        'price_cents' => (int) round(to_float(post('price')) * 100),
        'sort' => (int) post('sort'),
        'active' => post('active') ? 1 : 0,
        'updated_at' => now(),
    ];
    if ($row['name'] === '') {
        $errors[] = 'Bitte eine Bezeichnung angeben.';
    }
    if ($row['price_cents'] < 0) {
        $errors[] = 'Der Preis darf nicht negativ sein.';
    }
    if ($row['code'] !== '' && scalar('SELECT id FROM price_list WHERE code = ? AND id <> ? AND ' . fw(), [$row['code'], $id])) {
        $errors[] = 'Das Kürzel ' . $row['code'] . ' ist bereits vergeben.';
    }
    if (!$errors) {
        if ($id) {
            update('price_list', $id, $row);
            audit('prices.update', '', $id, price_label($row) . ' ' . money($row['price_cents'] / 100));
        } else {
            $id = insert('price_list', $row + ['created_at' => now()]);
            audit('prices.create', '', $id, price_label($row) . ' ' . money($row['price_cents'] / 100));
        }
        flash('success', 'Gebührenordnung gespeichert.');
        redirect(url('prices'));
    }
}
$editId = (int) ($_GET['edit'] ?? post('id', 0));
$rows = price_list(false);
$edit = $editId ? fone('price_list', $editId) : null;
$form = $edit ?? ['id' => 0, 'code' => '', 'name' => '', 'category' => '', 'price_cents' => 0, 'sort' => (count($rows) + 1) * 10, 'active' => 1];
if ($errors) {
    $form = array_merge($form, ['code' => post('code'), 'name' => post('name'), 'category' => post('category'), 'price_cents' => (int) round(to_float(post('price')) * 100)]);
}

ob_start(); ?>
<div class="page-head">
    <div><h1>Gebührenordnung</h1><p class="muted">Feste Preise für Leistungen. In Rechnungen wird der Preis automatisch übernommen; Abweichungen und freie Positionen müssen begründet werden.</p></div>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<div class="grid-side wide-side reverse">
    <section class="card">
        <table class="list">
            <thead><tr><th>Kürzel</th><th>Leistung</th><th>Kategorie</th><th class="right">Preis</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr class="<?= $r['active'] ? '' : 'inactive' ?>"><td class="mono"><?= e($r['code']) ?></td><td><?= e($r['name']) ?><?= $r['active'] ? '' : ' <span class="badge grey">inaktiv</span>' ?></td>
                    <td class="small"><?= e($r['category']) ?></td><td class="right"><?= e(money($r['price_cents'] / 100)) ?></td>
                    <td class="right"><a href="<?= e(url('prices', ['edit' => $r['id']])) ?>">bearbeiten</a></td></tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="5" class="muted">Noch keine Leistungen.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
    <form method="post" class="card form-grid">
        <h2><?= $edit ? 'Leistung bearbeiten' : 'Neue Leistung' ?></h2>
        <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $form['id'] ?>">
        <div class="field w-third"><label>Kürzel</label><input name="code" value="<?= e($form['code']) ?>" maxlength="32" placeholder="R01"></div>
        <div class="field w-two-thirds"><label>Bezeichnung <span class="req">*</span></label><input name="name" value="<?= e($form['name']) ?>" required></div>
        <div class="field w-half"><label>Kategorie</label><input name="category" value="<?= e($form['category']) ?>" list="dl-price-cats"></div>
        <div class="field w-half"><label>Preis (<?= e(org('currency')) ?>)</label><input name="price" value="<?= e(number_format($form['price_cents'] / 100, 2, '.', '')) ?>" inputmode="decimal" required></div>
        <div class="field w-half"><label>Sortierung</label><input type="number" name="sort" value="<?= (int) $form['sort'] ?>"></div>
        <div class="field w-half"><label class="choice single" style="margin-top:1.6rem"><input type="checkbox" name="active" value="1"<?= checked($form['active']) ?>> aktiv</label></div>
        <datalist id="dl-price-cats"><?php foreach (array_unique(array_filter(array_column($rows, 'category'))) as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
        <div class="form-actions">
            <button class="btn primary">Speichern</button>
            <?php if ($edit): ?><a class="btn" href="<?= e(url('prices')) ?>">Abbrechen</a>
                <?php if ($edit['active']): ?><button class="btn danger" name="action" value="delete" formnovalidate>Deaktivieren</button><?php endif; ?><?php endif; ?>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
$title = 'Gebührenordnung';
require SAMS_ROOT . '/app/layout.php';
