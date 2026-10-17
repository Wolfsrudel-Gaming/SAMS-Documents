<?php
defined('SAMS_ROOT') || exit;
/** Vorschlagslisten der Dokumentformulare pflegen (eine Zeile = ein Vorschlag). */
$me = current_user();
if (is_post()) {
    csrf_check();
    $key = (string) post('list_key');
    if (isset(SUGGESTION_LISTS[$key])) {
        if (post('action') === 'reset') {
            q('DELETE FROM suggestion_lists WHERE list_key = ?', [$key]);
            flash('success', 'Liste auf die eingebauten Vorschläge zurückgesetzt.');
        } else {
            $items = implode("\n", array_slice(array_values(array_unique(array_filter(array_map(fn($l) => mb_substr(trim($l), 0, 200), preg_split('/\r?\n/', (string) post('items'))), 'strlen'))), 0, 2000));
            if (scalar('SELECT id FROM suggestion_lists WHERE list_key = ?', [$key])) {
                q('UPDATE suggestion_lists SET items = ?, updated_by = ?, updated_at = ? WHERE list_key = ?', [$items, $me['id'], now(), $key]);
            } else {
                insert('suggestion_lists', ['list_key' => $key, 'items' => $items, 'updated_by' => $me['id'], 'updated_at' => now()]);
            }
            flash('success', SUGGESTION_LISTS[$key] . ' gespeichert (' . count(array_filter(explode("\n", $items), 'strlen')) . ' Einträge).');
        }
        audit('lists.update', '', null, SUGGESTION_LISTS[$key]);
    }
    redirect(url('wiki_lists') . '#l-' . $key);
}
$builtin = datalists_builtin();
ob_start(); ?>
<div class="page-head">
    <div><h1>Vorschlagslisten</h1><p class="muted">Diese Einträge erscheinen beim Tippen in den Dokumentformularen als Vorschläge. Eine Zeile = ein Eintrag.</p></div>
    <a class="btn" href="<?= e(url('wiki')) ?>">Wiki</a>
</div>
<div class="lists-grid">
<?php foreach (SUGGESTION_LISTS as $key => $label): $custom = suggestion_list($key); $items = $custom ?? ($builtin[$key] ?? []); ?>
    <form method="post" class="card" id="l-<?= e($key) ?>">
        <h2><?= e($label) ?> <small class="muted"><?= count($items) ?> Einträge<?= $custom === null ? ' · eingebaut' : ' · angepasst' ?></small></h2>
        <?= csrf_field() ?><input type="hidden" name="list_key" value="<?= e($key) ?>">
        <textarea name="items" rows="14" class="mono small"><?= e(implode("\n", $items)) ?></textarea>
        <div class="form-actions"><button class="btn primary" name="action" value="save">Speichern</button>
            <?php if ($custom !== null): ?><button class="btn" name="action" value="reset" onclick="return confirm('Eigene Liste verwerfen und eingebaute Vorschläge verwenden?')">Zurücksetzen</button><?php endif; ?></div>
    </form>
<?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Vorschlagslisten';
require SAMS_ROOT . '/app/layout.php';
