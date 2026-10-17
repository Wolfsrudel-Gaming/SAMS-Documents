<?php
defined('SAMS_ROOT') || exit;
/** Entwurf ansehen: Vorschau, Freigeben/Zurückweisen, Einreichung zurückziehen. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$draft = one('SELECT d.*, s.name AS submitter_name, o.name AS owner_name FROM document_drafts d
    LEFT JOIN users s ON s.id = d.submitted_by LEFT JOIN users o ON o.id = d.owner_id WHERE d.id = ?', [$id]);
if (!$draft || !draft_visible($draft)) {
    not_found('Entwurf nicht gefunden.');
}
$type = doc_type($draft['type']);
$patient = $draft['patient_id'] ? one('SELECT * FROM patients WHERE id = ?', [$draft['patient_id']]) : null;
$case = $draft['case_id'] ? one('SELECT * FROM cases WHERE id = ?', [$draft['case_id']]) : null;
$data = json_decode((string) $draft['data'], true) ?: [];

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'approve' && can_approve($draft)) {
        $author = one('SELECT * FROM users WHERE id = ?', [$draft['submitted_by']]);
        if (!$author) {
            flash('error', 'Das einreichende Konto existiert nicht mehr.');
            redirect(url('draft', ['id' => $id]));
        }
        require_active_patient($patient);
        // Fall darf inzwischen abgeschlossen sein – die Zuordnung bleibt trotzdem erhalten
        $docId = issue_document($type, $patient, $case, $data, $author, $me, (string) $draft['signature']);
        q('DELETE FROM document_drafts WHERE id = ?', [$id]);
        audit('draft.approve', 'document', $docId, $type['short'] . ' von ' . user_display($author));
        notify_webhook('approvals', log_embed('✅ Freigegeben: ' . $type['title'], 'Eingereicht von ' . user_display($author) . ', freigegeben von ' . user_display($me) . '.', 0x1e7a43));
        flash('success', 'Freigegeben und ausgestellt.');
        redirect(url('document', ['id' => $docId]));
    }
    if ($action === 'reject' && can_approve($draft)) {
        $note = mb_substr(trim((string) post('note')), 0, 1000);
        if ($note === '') {
            flash('error', 'Bitte begründen, was geändert werden muss.');
            redirect(url('draft', ['id' => $id]));
        }
        update('document_drafts', $id, ['status' => 'rejected', 'owner_id' => $draft['submitted_by'], 'reviewed_by' => $me['id'], 'note' => $note, 'updated_at' => now()]);
        audit('draft.reject', 'document', null, $type['short'] . ' Entwurf #' . $id . ': ' . $note);
        notify_webhook('approvals', log_embed('↩️ Zurückgewiesen: ' . $type['title'], 'Entwurf von ' . $draft['submitter_name'] . ' wurde von ' . user_display($me) . ' zurückgewiesen.', 0xb3261e));
        flash('success', 'Zurückgewiesen. Der Entwurf liegt wieder bei ' . $draft['submitter_name'] . '.');
        redirect(url('drafts'));
    }
    if ($action === 'withdraw' && $draft['status'] === 'pending' && (int) $draft['submitted_by'] === (int) $me['id']) {
        update('document_drafts', $id, ['status' => 'draft', 'owner_id' => $me['id'], 'updated_at' => now()]);
        audit('draft.withdraw', 'document', null, $type['short'] . ' Entwurf #' . $id);
        flash('success', 'Einreichung zurückgezogen. Du kannst den Entwurf wieder bearbeiten.');
        redirect(url('document_new', ['draft' => $id]));
    }
    forbidden('Diese Aktion ist für dich nicht möglich.');
}

$author = one('SELECT * FROM users WHERE id = ?', [$draft['submitted_by'] ?: $draft['owner_id']]);
$issuerSnap = issuer_snapshot($author ?: $me);
if ($draft['signature']) {
    $issuerSnap['signature'] = $draft['signature'];
}
$preview = render_document([
    'id' => 0, 'type' => $draft['type'], 'number' => 'ENTWURF', 'data' => $data,
    'patient_snapshot' => document_person_snapshot($patient, $data) ?? [], 'issuer_snapshot' => $issuerSnap,
    'verify_code' => 'ENTWURF0', 'status' => 'valid', 'issued_at' => now(),
], ['draft' => true]);

ob_start(); ?>
<div class="page-head no-print">
    <div>
        <h1><?= e($type['title']) ?> <?= draft_status_badge($draft) ?></h1>
        <p class="muted">Entwurf #<?= (int) $id ?><?= $patient ? ' · ' . e(patient_name($patient, true)) : '' ?><?= $case ? ' · Fall ' . e($case['number']) : '' ?>
            · <?= $draft['status'] === 'pending' ? 'eingereicht von ' . e($draft['submitter_name']) . ' am ' . e(fmt_datetime($draft['submitted_at'])) : 'bei ' . e($draft['owner_name']) ?></p>
    </div>
    <div class="btn-row">
        <?php if (draft_editable($draft)): ?><a class="btn primary" href="<?= e(url('document_new', ['draft' => $id])) ?>"><?= icon('edit') ?>Bearbeiten</a><?php endif; ?>
        <?php if ($draft['status'] === 'pending' && (int) $draft['submitted_by'] === (int) $me['id']): ?>
            <form method="post" class="inline-form"><?= csrf_field() ?><button class="btn" name="action" value="withdraw">Einreichung zurückziehen</button></form>
        <?php endif; ?>
        <a class="btn" href="<?= e(url('drafts')) ?>">Zur Übersicht</a>
    </div>
</div>
<?php if ($draft['note']): ?><div class="flash warn no-print"><strong><?= $draft['status'] === 'rejected' ? 'Zurückgewiesen' : 'Hinweis' ?>:</strong> <?= e($draft['note']) ?></div><?php endif; ?>

<?php if (can_approve($draft)): ?>
<section class="card no-print">
    <h2>Freigabe</h2>
    <p>Mit der Freigabe wird das Dokument im Namen von <strong><?= e($draft['submitter_name']) ?></strong> ausgestellt und von dir gegengezeichnet. Deine Unterschrift erscheint neben der des Verfassers.</p>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <div class="field w-full"><label>Begründung bei Zurückweisung</label><input name="note" maxlength="1000" placeholder="z. B. Diagnose unvollständig, bitte ICD ergänzen"></div>
        <div class="form-actions">
            <button class="btn primary" name="action" value="approve" onclick="return confirm('Dokument freigeben und verbindlich ausstellen?')">Freigeben &amp; ausstellen</button>
            <button class="btn danger" name="action" value="reject">Zurückweisen</button>
        </div>
    </form>
</section>
<?php endif; ?>

<div class="doc-stage"><?= $preview ?></div>
<?php
$content = ob_get_clean();
$title = 'Entwurf';
$withDocumentCss = true;
$scripts = ['assets/vendor/qrcode.js', 'assets/js/document.js'];
require SAMS_ROOT . '/app/layout.php';
