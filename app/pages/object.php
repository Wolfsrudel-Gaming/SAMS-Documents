<?php
defined('SAMS_ROOT') || exit;
/** Objektplan: Angaben pflegen, Pläne und Fotos hochladen. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$o = $id ? fone('objects', $id) : null;
if (!$o) {
    not_found('Objekt nicht gefunden.');
}
$text = ['address' => 190, 'contact' => 190, 'keybox' => 190, 'hazards' => 4000, 'access' => 4000, 'notes' => 4000];
if (is_post()) {
    csrf_check();
    require_perm('objects.manage');
    $action = post('action');
    if ($action === 'save') {
        $row = ['name' => mb_substr(trim((string) post('name')), 0, 190) ?: $o['name'], 'kind' => in_array(post('kind'), OBJECT_KINDS, true) ? post('kind') : $o['kind'],
            'risk' => max(0, min(3, (int) post('risk'))), 'last_inspection' => post_date('last_inspection'), 'next_inspection' => post_date('next_inspection'), 'updated_at' => now()];
        foreach ($text as $k => $max) {
            $row[$k] = mb_substr(trim((string) post($k)), 0, $max);
        }
        update('objects', $id, $row);
        audit('object.update', 'object', $id, $row['name']);
        flash('success', 'Objekt gespeichert.');
    } elseif ($action === 'upload') {
        [$fileId, $err] = store_object_file($_FILES['file'] ?? [], $id, mb_substr(trim((string) post('title')), 0, 190), (int) $me['id']);
        if ($err) {
            flash('error', $err);
        } else {
            audit('object.file', 'object', $id, $o['name'] . ': ' . post('title'));
            flash('success', 'Plan hochgeladen.');
        }
    } elseif ($action === 'delete_file' && ($f = one('SELECT * FROM object_files WHERE id = ? AND object_id = ?', [(int) post('file_id'), $id]))) {
        q('DELETE FROM object_files WHERE id = ?', [$f['id']]);
        foreach ([attachment_dir() . '/' . $f['stored_name'], attachment_dir() . '/thumb_' . $f['stored_name']] as $path) {
            is_file($path) && @unlink($path);
        }
        audit('object.file_delete', 'object', $id, $f['title']);
        flash('success', 'Plan entfernt.');
    }
    redirect(url('object', ['id' => $id]));
}
$files = all('SELECT * FROM object_files WHERE object_id = ? ORDER BY id', [$id]);
$manage = can('objects.manage');
$labels = ['address' => 'Adresse', 'contact' => 'Ansprechpartner/in (Name, Telefon)', 'keybox' => 'Schlüsseldepot / Zugang', 'hazards' => 'Gefahren und Gefahrstoffe', 'access' => 'Zufahrt, Aufstellflächen, Hydranten', 'notes' => 'Sonstige Hinweise'];

ob_start(); ?>
<div class="page-head">
    <div><h1><?= e($o['name']) ?></h1><p class="muted"><?= e($o['kind']) ?> · Risiko <?= e(OBJECT_RISK[(int) $o['risk']]) ?></p></div>
    <a class="btn" href="<?= e(url('objects')) ?>">Zur Übersicht</a>
</div>
<?php if (trim((string) $o['hazards']) !== ''): ?><div class="alertbar"><div><strong><?= icon('warn') ?> Gefahren:</strong> <?= nl($o['hazards']) ?></div></div><?php endif; ?>
<div class="grid-side">
<section>
    <div class="card">
        <h2>Pläne und Fotos</h2>
        <div class="att-grid">
        <?php foreach ($files as $f): ?>
            <figure class="att"><a class="att-thumb" href="<?= e(url('object_file', ['id' => $f['id']])) ?>" target="_blank">
                <?= $f['has_thumb'] ? '<img src="' . e(url('object_file', ['id' => $f['id'], 'thumb' => 1])) . '" alt="">' : '<span class="att-pdf">' . e(strtoupper(pathinfo($f['stored_name'], PATHINFO_EXTENSION))) . '</span>' ?></a>
                <figcaption><?= e($f['title']) ?>
                <?php if ($manage): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="delete_file"><input type="hidden" name="file_id" value="<?= (int) $f['id'] ?>"><button class="link small" onclick="return confirm('Plan entfernen?')">entfernen</button></form><?php endif; ?></figcaption></figure>
        <?php endforeach; ?>
        <?php if (!$files): ?><p class="muted">Noch keine Pläne.</p><?php endif; ?>
        </div>
        <?php if ($manage): ?>
        <form method="post" enctype="multipart/form-data" class="inline-form att-upload"><?= csrf_field() ?><input type="hidden" name="action" value="upload">
            <input name="title" placeholder="z. B. Grundriss EG, Feuerwehrplan" maxlength="190"><input type="file" name="file" accept="image/*,application/pdf" required><button class="btn small">Hochladen</button></form>
        <?php endif; ?>
    </div>
    <div class="card">
        <div class="card-head"><h2>Brandschau & Dokumente</h2>
            <?php if (can('documents.issue.fd_brandschau')): ?><a class="btn small" href="<?= e(url('document_new', ['type' => 'fd_brandschau', 'prefill' => ['objekt' => $o['name']]])) ?>">Brandschau dokumentieren</a><?php endif; ?></div>
        <p class="small muted">Letzte Brandschau: <?= e(fmt_date($o['last_inspection']) ?: '–') ?> · nächste: <?= e(fmt_date($o['next_inspection']) ?: '–') ?>. Brandschau- und Nachschauberichte mit diesem Objektnamen übernehmen die Termine automatisch.</p>
        <table class="list compact"><tbody>
        <?php foreach (object_documents($o) as $d): ?><tr onclick="location.href='<?= e(url('document', ['id' => $d['id']])) ?>'"><td class="small"><?= e(fmt_date($d['issued_at'])) ?></td><td><?= e(doc_type($d['type'])['title'] ?? $d['type']) ?></td><td class="mono small"><?= e($d['number']) ?></td><td><?= $d['status'] === 'revoked' ? '<span class="badge grey">storniert</span>' : '' ?></td></tr><?php endforeach; ?>
        </tbody></table>
    </div>
    <?php if (!$manage): ?>
    <div class="card"><dl class="kv"><?php foreach ($labels as $k => $l): ?><dt><?= e($l) ?></dt><dd><?= nl($o[$k] ?: '–') ?></dd><?php endforeach; ?>
        <dt>Letzte Brandschau</dt><dd><?= e(fmt_date($o['last_inspection']) ?: '–') ?></dd><dt>Nächste Brandschau</dt><dd><?= e(fmt_date($o['next_inspection']) ?: '–') ?></dd></dl></div>
    <?php endif; ?>
</section>
<?php if ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="save">
    <div class="field w-full"><label>Name</label><input name="name" required maxlength="190" value="<?= e($o['name']) ?>"></div>
    <div class="field"><label>Art</label><select name="kind"><?php foreach (OBJECT_KINDS as $k): ?><option<?= selected($k, $o['kind']) ?>><?= e($k) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Risiko</label><select name="risk"><?php foreach (OBJECT_RISK as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, (int) $o['risk']) ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <?php foreach ($labels as $k => $l): ?>
        <div class="field w-full"><label><?= e($l) ?></label><?php if ($text[$k] > 190): ?><textarea name="<?= $k ?>" rows="3"><?= e($o[$k]) ?></textarea><?php else: ?><input name="<?= $k ?>" maxlength="190" value="<?= e($o[$k]) ?>"><?php endif; ?></div>
    <?php endforeach; ?>
    <div class="field"><label>Letzte Brandschau</label><input type="date" name="last_inspection" value="<?= e($o['last_inspection']) ?>"></div>
    <div class="field"><label>Nächste Brandschau</label><input type="date" name="next_inspection" value="<?= e($o['next_inspection']) ?>"></div>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = $o['name'];
require SAMS_ROOT . '/app/layout.php';
