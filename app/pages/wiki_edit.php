<?php
defined('SAMS_ROOT') || exit;
/** Wiki-Seite anlegen/bearbeiten. Jede Änderung legt eine Version an. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$pg = $id ? fone('wiki_pages', $id) : null;
if ($id && !$pg) {
    not_found('Wiki-Seite nicht gefunden.');
}
$form = $pg ?? ['title' => (string) ($_GET['title'] ?? ''), 'category' => 'Allgemein', 'content' => '', 'pinned' => 0];
$errors = [];
if (is_post()) {
    csrf_check();
    if (post('action') === 'delete' && $pg) {
        q('DELETE FROM wiki_revisions WHERE page_id = ?', [$id]);
        q('DELETE FROM wiki_pages WHERE id = ?', [$id]);
        audit('wiki.delete', '', $id, $pg['title']);
        flash('success', 'Seite gelöscht.');
        redirect(url('wiki'));
    }
    $form = [
        'title' => mb_substr(trim((string) post('title')), 0, 190),
        'category' => mb_substr(trim((string) post('category')), 0, 80) ?: 'Allgemein',
        'content' => mb_substr((string) post('content'), 0, 100000),
        'pinned' => post('pinned') ? 1 : 0,
    ];
    $slug = wiki_slug($form['title']);
    if ($form['title'] === '') {
        $errors[] = 'Bitte einen Titel angeben.';
    } elseif (scalar('SELECT id FROM wiki_pages WHERE slug = ? AND id <> ? AND ' . fw(), [$slug, $id])) {
        $errors[] = 'Es gibt bereits eine Seite mit diesem Titel.';
    }
    if (!$errors) {
        if ($pg) {
            // Alte Fassung als Version sichern
            insert('wiki_revisions', ['page_id' => $id, 'title' => $pg['title'], 'content' => $pg['content'], 'user_id' => $pg['updated_by'], 'note' => '', 'created_at' => $pg['updated_at']]);
            update('wiki_pages', $id, $form + ['slug' => $slug, 'updated_by' => $me['id'], 'updated_at' => now()]);
            audit('wiki.update', '', $id, $form['title'] . (post('note') ? ' – ' . mb_substr((string) post('note'), 0, 100) : ''));
        } else {
            $id = insert('wiki_pages', $form + ['slug' => $slug, 'created_by' => $me['id'], 'created_at' => now(), 'updated_by' => $me['id'], 'updated_at' => now()]);
            audit('wiki.create', '', $id, $form['title']);
        }
        flash('success', 'Seite gespeichert.');
        redirect(url('wiki_page', ['slug' => $slug]));
    }
}
$cats = array_column(all('SELECT DISTINCT category FROM wiki_pages WHERE ' . fw() . ' ORDER BY category'), 'category');

ob_start(); ?>
<div class="page-head"><h1><?= $pg ? 'Seite bearbeiten' : 'Neue Wiki-Seite' ?></h1><a class="btn" href="<?= e($pg ? url('wiki_page', ['slug' => $pg['slug']]) : url('wiki')) ?>">Abbrechen</a></div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <div class="field w-half"><label>Titel <span class="req">*</span></label><input name="title" value="<?= e($form['title']) ?>" required></div>
    <div class="field w-quarter"><label>Kategorie</label><input name="category" value="<?= e($form['category']) ?>" list="dl-wiki-cats"><datalist id="dl-wiki-cats"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?><option value="SOP"><option value="Medikamente"><option value="Verwaltung"></datalist></div>
    <div class="field w-quarter"><label class="choice single" style="margin-top:1.6rem"><input type="checkbox" name="pinned" value="1"<?= checked($form['pinned']) ?>> oben anheften</label></div>
    <div class="field w-full"><label>Inhalt</label><textarea name="content" rows="20" class="wiki-editor"><?= e($form['content']) ?></textarea>
        <div class="hint"><code># Überschrift</code> <code>## Unterüberschrift</code> <code>**fett**</code> <code>*kursiv*</code> <code>- Liste</code> <code>1. Liste</code> <code>[[Andere Seite]]</code> <code>[Text](https://…)</code></div></div>
    <?php if ($pg): ?><div class="field w-full"><label>Änderungsnotiz (optional)</label><input name="note" maxlength="100" placeholder="z. B. Dosierung Adrenalin aktualisiert"></div><?php endif; ?>
    <div class="form-actions"><button class="btn primary">Speichern</button>
        <?php if ($pg): ?><button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Seite mit allen Versionen löschen?')">Löschen</button><?php endif; ?></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Wiki bearbeiten';
require SAMS_ROOT . '/app/layout.php';
