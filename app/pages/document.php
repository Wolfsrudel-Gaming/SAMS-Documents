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
    if ((int) $doc['issued_by'] !== (int) $me['id'] && !can('admin')) {
        forbidden('Nur der Aussteller oder die Leitung können ein Dokument stornieren.');
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

audit('document.view', 'document', $id, $doc['number']);
$doc['data'] = json_decode((string) $doc['data'], true) ?: [];
$doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
$doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
$copy = (string) ($_GET['copy'] ?? '');
$html = render_document($doc, ['copy' => $copy]);
$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $doc['number'] . '_' . ($doc['patient_snapshot']['last_name'] ?? ''));

ob_start(); ?>
<div class="page-head no-print">
    <div>
        <h1><?= e($type['title'] ?? $doc['type']) ?> <?= doc_status_badge($doc) ?></h1>
        <p class="muted mono"><?= e($doc['number']) ?> · Prüfcode <?= e($doc['verify_code']) ?> · ausgestellt <?= e(fmt_datetime($doc['issued_at'])) ?> von <?= e(user_display($doc['issuer_snapshot'])) ?></p>
    </div>
    <div class="btn-row">
        <button class="btn primary" onclick="window.print()">Drucken / PDF</button>
        <button class="btn" data-export-png="<?= e($fileName) ?>">Als Bild speichern</button>
        <button class="btn" data-copy="<?= e(verify_url($doc['verify_code'])) ?>">Prüflink kopieren</button>
        <?php if (can($type['role'] ?? 'admin')): ?><a class="btn" href="<?= e(url('document_new', ['type' => $doc['type'], 'from' => $id])) ?>">Als Vorlage verwenden</a><?php endif; ?>
        <?php if ($doc['patient_id']): ?><a class="btn" href="<?= e(url('patient', ['id' => $doc['patient_id']])) ?>">Zur Akte</a><?php endif; ?>
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

<?php if ($doc['status'] !== 'revoked' && ((int) $doc['issued_by'] === (int) $me['id'] || can('admin'))): ?>
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
