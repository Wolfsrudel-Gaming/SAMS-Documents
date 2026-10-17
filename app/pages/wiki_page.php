<?php
defined('SAMS_ROOT') || exit;
/** Wiki-Seite anzeigen, inkl. Versionsgeschichte. */
$slug = (string) ($_GET['slug'] ?? '');
$pg = one('SELECT p.*, u.name AS editor FROM wiki_pages p LEFT JOIN users u ON u.id = p.updated_by WHERE p.slug = ? AND ' . fw('p'), [$slug]);
if (!$pg) {
    if (can('wiki.edit')) {
        flash('warn', 'Diese Seite gibt es noch nicht – leg sie jetzt an.');
        redirect(url('wiki_edit', ['title' => ucfirst(str_replace(['-', '_'], ' ', $slug))]));
    }
    not_found('Wiki-Seite nicht gefunden.');
}
$revId = (int) ($_GET['rev'] ?? 0);
$rev = $revId ? one('SELECT r.*, u.name AS editor FROM wiki_revisions r LEFT JOIN users u ON u.id = r.user_id WHERE r.id = ? AND r.page_id = ?', [$revId, $pg['id']]) : null;
$revisions = all('SELECT r.id, r.created_at, r.note, u.name AS editor FROM wiki_revisions r LEFT JOIN users u ON u.id = r.user_id WHERE r.page_id = ? ORDER BY r.id DESC LIMIT 30', [$pg['id']]);

ob_start(); ?>
<div class="page-head">
    <div><h1><?= e($rev['title'] ?? $pg['title']) ?></h1>
        <p class="muted"><?= e($pg['category']) ?> · zuletzt geändert <?= e(fmt_datetime($pg['updated_at'])) ?><?= $pg['editor'] ? ' von ' . e($pg['editor']) : '' ?></p></div>
    <div class="btn-row">
        <?php if (can('wiki.edit')): ?><a class="btn" href="<?= e(url('wiki_edit', ['id' => $pg['id']])) ?>"><?= icon('edit') ?>Bearbeiten</a><?php endif; ?>
        <a class="btn" href="<?= e(url('wiki')) ?>">Wiki</a>
    </div>
</div>
<?php if ($rev): ?><div class="flash warn">Ältere Version vom <?= e(fmt_datetime($rev['created_at'])) ?><?= $rev['editor'] ? ' (' . e($rev['editor']) . ')' : '' ?> · <a href="<?= e(url('wiki_page', ['slug' => $slug])) ?>">zur aktuellen Version</a></div><?php endif; ?>
<div class="grid-side wide-side reverse">
    <article class="card prose-ui wiki-body"><?= wiki_render((string) ($rev['content'] ?? $pg['content'])) ?></article>
    <section class="card">
        <h2>Versionen</h2>
        <ul class="wiki-list">
            <li><a href="<?= e(url('wiki_page', ['slug' => $slug])) ?>"><?= $rev ? '' : '<strong>' ?>Aktuell<?= $rev ? '' : '</strong>' ?></a><small class="muted"><?= e(fmt_datetime($pg['updated_at'])) ?></small></li>
            <?php foreach ($revisions as $r): ?>
                <li><a href="<?= e(url('wiki_page', ['slug' => $slug, 'rev' => $r['id']])) ?>"><?= (int) $r['id'] === $revId ? '<strong>' : '' ?><?= e(fmt_datetime($r['created_at'])) ?><?= (int) $r['id'] === $revId ? '</strong>' : '' ?></a>
                    <small class="muted"><?= e($r['editor'] ?? '') ?><?= $r['note'] ? ' · ' . e($r['note']) : '' ?></small></li>
            <?php endforeach; ?>
        </ul>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = $pg['title'];
require SAMS_ROOT . '/app/layout.php';
