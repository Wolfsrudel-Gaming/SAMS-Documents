<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$doc = one('SELECT d.*, u.name AS revoker FROM documents d LEFT JOIN users u ON u.id = d.revoked_by WHERE d.id = ?', [$id]);
if (!$doc) {
    not_found('Dokument nicht gefunden.');
}
$type = doc_type($doc['type']);

if (is_post() && post('action') === 'revoke') {
    csrf_check();
    $reason = mb_substr((string) post('reason'), 0, 1000);
    if (!can_revoke($doc)) {
        forbidden('Dein Rang darf dieses Dokument nicht stornieren.');
    }
    if ($reason === '') {
        flash('error', 'Bitte einen Stornierungsgrund angeben.');
    } elseif ($doc['status'] !== 'revoked') {
        q('UPDATE documents SET status = ?, revoked_reason = ?, revoked_by = ?, revoked_at = ? WHERE id = ?', ['revoked', $reason, $me['id'], now(), $id]);
        audit('document.revoke', 'document', $id, $reason);
        flash('success', 'Dokument wurde storniert. Die Echtheitsprüfung zeigt es ab sofort als ungültig an.');
    }
    redirect(url('document', ['id' => $id]));
}

if (is_post() && post('action') === 'addendum') {
    csrf_check();
    if (!can_add_addendum($doc)) {
        forbidden('Du darfst zu diesem Dokument keinen Nachtrag schreiben.');
    }
    $text = mb_substr(trim((string) post('text')), 0, 10000);
    if ($text === '') {
        flash('error', 'Der Nachtrag ist leer.');
    } else {
        insert('document_addenda', ['document_id' => $id, 'text' => $text, 'author_snapshot' => json_encode(issuer_snapshot($me), JSON_UNESCAPED_UNICODE),
            'created_by' => $me['id'], 'created_at' => now()]);
        audit('document.addendum', 'document', $id, $doc['number'] . ': ' . mb_strimwidth($text, 0, 120, '…'));
        flash('success', 'Nachtrag gespeichert. Er erscheint als zusätzliches Blatt am Dokument und in der Echtheitsprüfung.');
    }
    redirect(url('document', ['id' => $id]) . '#addenda');
}

if (is_post() && in_array(post('action'), ['print', 'cancel_job'], true)) {
    csrf_check();
    if (post('action') === 'cancel_job') {
        $job = one('SELECT * FROM print_jobs WHERE id = ? AND document_id = ?', [(int) post('job_id'), $id]);
        if ($job && $job['status'] === 'queued' && ((int) $job['requested_by'] === (int) $me['id'] || can('printers.manage'))) {
            q("UPDATE print_jobs SET status = 'cancelled' WHERE id = ?", [$job['id']]);
            audit('document.print_cancel', 'document', $id, 'Auftrag #' . $job['id']);
            flash('success', 'Druckauftrag abgebrochen.');
        }
        redirect(url('document', ['id' => $id]) . '#print');
    }
    if (!can('documents.print')) {
        forbidden('Dein Rang darf keine Dokumente im Spiel drucken.');
    }
    if ($doc['status'] === 'revoked') {
        flash('error', 'Stornierte Dokumente können nicht gedruckt werden.');
        redirect(url('document', ['id' => $id]));
    }
    $printer = one('SELECT * FROM printers WHERE id = ? AND active = 1', [(int) post('printer_id')]);
    if (!$printer) {
        flash('error', 'Bitte einen aktiven Drucker wählen.');
        redirect(url('document', ['id' => $id]) . '#print');
    }
    $created = 0;
    foreach (print_variants($doc['type']) as $variant => $label) {
        $copies = max(0, min(5, (int) ($_POST['copies'][$variant === '' ? 'doc' : $variant] ?? 0)));
        if ($copies > 0) {
            insert('print_jobs', ['document_id' => $id, 'printer_id' => $printer['id'], 'copy_variant' => $variant, 'copies' => $copies,
                'status' => 'queued', 'requested_by' => $me['id'], 'requested_at' => now()]);
            $created += $copies;
        }
    }
    if ($created === 0) {
        flash('error', 'Bitte mindestens ein Exemplar wählen.');
        redirect(url('document', ['id' => $id]) . '#print');
    }
    audit('document.print', 'document', $id, $doc['number'] . ' → ' . $printer['name'] . " ($created Expl.)");
    flash('success', "Druckauftrag gesendet: $created Exemplar(e) an „{$printer['name']}“. Hol das Dokument am Drucker ab.");
    $_SESSION['game_notify'] = ['type' => 'printQueued', 'payload' => ['printer' => $printer['name'], 'x' => (float) $printer['x'], 'y' => (float) $printer['y'], 'z' => (float) $printer['z'], 'count' => $created]];
    redirect(url('document', ['id' => $id]));
}

audit('document.view', 'document', $id, $doc['number']);
$doc['data'] = json_decode((string) $doc['data'], true) ?: [];
$doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
$doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
$copy = (string) ($_GET['copy'] ?? '');
$html = render_document($doc, ['copy' => $copy]);
$printers = active_printers();
$jobs = all('SELECT j.*, p.name AS printer, u.name AS requester FROM print_jobs j JOIN printers p ON p.id = j.printer_id
    LEFT JOIN users u ON u.id = j.requested_by WHERE j.document_id = ? ORDER BY j.id DESC LIMIT 20', [$id]);
$gameNotify = $_SESSION['game_notify'] ?? null;
unset($_SESSION['game_notify']);
$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $doc['number'] . '_' . ($doc['patient_snapshot']['last_name'] ?? ''));

ob_start(); ?>
<div class="page-head no-print">
    <div>
        <h1><?= e($type['title'] ?? $doc['type']) ?> <?= doc_status_badge($doc) ?></h1>
        <p class="muted mono"><?= e($doc['number']) ?> · Prüfcode <?= e($doc['verify_code']) ?> · ausgestellt <?= e(fmt_datetime($doc['issued_at'])) ?> von <?= e(user_display($doc['issuer_snapshot'])) ?></p>
    </div>
    <div class="btn-row">
        <?php if (can('documents.print') && $doc['status'] !== 'revoked'): ?><a class="btn primary" href="#print"><?= icon('print') ?>Im Spiel drucken</a><?php endif; ?>
        <button class="btn" onclick="window.print()" data-browser-only>Drucken / PDF</button>
        <button class="btn" data-export-png="<?= e($fileName) ?>">Als Bild speichern</button>
        <button class="btn" data-copy="<?= e(verify_url($doc['verify_code'])) ?>">Prüflink kopieren</button>
        <?php if (can('documents.issue.' . $doc['type'])): ?><a class="btn" href="<?= e(url('document_new', ['type' => $doc['type'], 'from' => $id])) ?>">Als Vorlage verwenden</a><?php endif; ?>
        <?php if ($doc['patient_id']): ?><a class="btn" href="<?= e(url('patient', ['id' => $doc['patient_id']])) ?>">Zur Akte</a><?php endif; ?>
        <?php if (!empty($doc['case_id'])): ?><a class="btn" href="<?= e(url('case', ['id' => $doc['case_id']])) ?>">Zum Fall</a><?php endif; ?>
    </div>
</div>

<?php if ($doc['type'] === 'au'): ?>
<div class="tabs no-print copies">
    <?php foreach (['' => 'Alle Ausfertigungen', 'kasse' => 'Krankenkasse', 'arbeitgeber' => 'Arbeitgeber (ohne Diagnose)', 'versicherter' => 'Versicherte/r'] as $k => $l): ?>
        <a class="<?= $copy === $k ? 'active' : '' ?>" href="<?= e(url('document', ['id' => $id, 'copy' => $k ?: null])) ?>"><?= e($l) ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($doc['status'] === 'revoked'): ?>
    <div class="flash error no-print"><strong>Storniert</strong> am <?= e(fmt_datetime($doc['revoked_at'])) ?> durch <?= e($doc['revoker']) ?>: <?= e($doc['revoked_reason']) ?></div>
<?php endif; ?>

<div class="doc-stage" id="doc-stage"><?= $html ?></div>

<?php if ($gameNotify): ?><span hidden data-game-notify="<?= e($gameNotify['type']) ?>" data-payload="<?= e(json_encode($gameNotify['payload'])) ?>"></span><?php endif; ?>

<?php if (can('documents.print')): $variants = print_variants($doc['type']); $statusLabels = ['queued' => ['orange', 'wartet am Drucker'], 'printed' => ['green', 'abgeholt'], 'cancelled' => ['grey', 'abgebrochen']]; ?>
<section class="card no-print" id="print">
    <h2>Im Spiel drucken</h2>
    <?php if ($doc['status'] === 'revoked'): ?>
        <p class="muted">Stornierte Dokumente können nicht gedruckt werden.</p>
    <?php elseif (!$printers): ?>
        <p class="muted">Es sind noch keine Drucker eingerichtet.<?php if (can('printers.manage')): ?> <a href="<?= e(url('printers')) ?>">Drucker anlegen</a><?php endif; ?></p>
    <?php else: ?>
        <form method="post" class="form-grid print-form">
            <?= csrf_field() ?><input type="hidden" name="action" value="print">
            <div class="field w-half"><label>Drucker</label>
                <select name="printer_id" required>
                    <?php foreach ($printers as $pr): ?><option value="<?= (int) $pr['id'] ?>"><?= e($pr['name']) ?><?= $pr['location'] ? ' – ' . e($pr['location']) : '' ?></option><?php endforeach; ?>
                </select></div>
            <div class="field w-half"><span class="label">Exemplare</span>
                <div class="copies-grid">
                    <?php foreach ($variants as $vk => $vl): ?>
                        <label><span><?= e($vl) ?></span><input type="number" name="copies[<?= e($vk === '' ? 'doc' : $vk) ?>]" value="<?= $doc['type'] === 'au' ? ($vk === 'arbeitgeber' || $vk === 'versicherter' ? 1 : 0) : 1 ?>" min="0" max="5"></label>
                    <?php endforeach; ?>
                </div></div>
            <p class="hint w-full">Das Dokument wird am Drucker als Item ausgegeben: Dort hingehen, Drucker benutzen und den Auftrag abholen. Das Item lässt sich ansehen, zeigen und weitergeben.</p>
            <div class="form-actions"><button class="btn primary"><?= icon('print') ?>Druckauftrag senden</button></div>
        </form>
    <?php endif; ?>
    <?php if ($jobs): ?>
        <h3 class="form-heading" style="margin-top:.8rem">Druckaufträge</h3>
        <table class="list compact">
            <thead><tr><th>Zeitpunkt</th><th>Drucker</th><th>Exemplar</th><th>Auftraggeber</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($jobs as $j): [$sc, $sl] = $statusLabels[$j['status']] ?? ['grey', $j['status']]; ?>
                <tr><td class="small"><?= e(fmt_datetime($j['requested_at'])) ?></td><td><?= e($j['printer']) ?></td>
                    <td class="small"><?= e(($variants[$j['copy_variant']] ?? 'Dokument') . ' × ' . $j['copies']) ?></td>
                    <td class="small"><?= e($j['requester']) ?></td>
                    <td><span class="badge <?= $sc ?>"><?= e($sl) ?></span><?= $j['status'] === 'printed' ? ' <small class="muted">' . e(fmt_datetime($j['picked_up_at'])) . ' · ' . e($j['picked_up_by']) . '</small>' : '' ?></td>
                    <td class="right"><?php if ($j['status'] === 'queued' && ((int) $j['requested_by'] === (int) $me['id'] || can('printers.manage'))): ?>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cancel_job"><input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>"><button class="btn small">Abbrechen</button></form>
                    <?php endif; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php $addenda = document_addenda($id); if ($addenda || can_add_addendum($doc)): ?>
<section class="card no-print" id="addenda">
    <h2>Nachträge (<?= count($addenda) ?>)</h2>
    <div class="addenda-list">
        <?php foreach ($addenda as $i => $a): ?>
            <div class="addendum"><strong>Nachtrag Nr. <?= $i + 1 ?></strong> · <?= e(fmt_datetime($a['created_at'])) ?> · <?= e(user_display($a['author'])) ?><div><?= nl($a['text']) ?></div></div>
        <?php endforeach; ?>
    </div>
    <?php if (can_add_addendum($doc)): ?>
        <form method="post" class="form-grid">
            <?= csrf_field() ?><input type="hidden" name="action" value="addendum">
            <div class="field w-full"><label>Neuer Nachtrag</label><textarea name="text" rows="3" required placeholder="z. B. Berichtigung: Die Diagnose lautet korrekt S52.5 links (nicht rechts)."></textarea>
                <div class="hint">Ausgestellte Dokumente werden nie verändert. Ein Nachtrag wird als zusätzliches Blatt angehängt und kann nicht gelöscht werden.</div></div>
            <div class="form-actions"><button class="btn" onclick="return confirm('Nachtrag verbindlich hinzufügen?')">Nachtrag hinzufügen</button></div>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($doc['status'] !== 'revoked' && can_revoke($doc)): ?>
<details class="card no-print revoke">
    <summary>Dokument stornieren</summary>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="revoke">
        <div class="field w-full"><label>Grund der Stornierung <span class="req">*</span></label><input name="reason" required placeholder="z.B. Fehlerhafte Angaben, ersetzt durch neues Dokument"></div>
        <div class="form-actions"><button class="btn danger" onclick="return confirm('Dokument wirklich stornieren? Dies kann nicht rückgängig gemacht werden.')">Stornieren</button></div>
    </form>
</details>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = ($type['short'] ?? 'Dokument') . ' ' . $doc['number'];
$withDocumentCss = true;
$scripts = ['assets/vendor/qrcode.js', 'assets/vendor/html2canvas.min.js', 'assets/js/document.js'];
require SAMS_ROOT . '/app/layout.php';
