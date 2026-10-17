<?php
defined('SAMS_ROOT') || exit;
/** Fallakte: bündelt Einträge und Dokumente zu einem Einsatz bzw. einer Behandlung. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$case = one('SELECT c.*, d.name AS department, u.name AS author, cu.name AS closer FROM cases c
    LEFT JOIN departments d ON d.id = c.department_id LEFT JOIN users u ON u.id = c.created_by LEFT JOIN users cu ON cu.id = c.closed_by
    WHERE c.id = ?', [$id]);
if (!$case) {
    not_found('Fall nicht gefunden.');
}
$p = one('SELECT * FROM patients WHERE id = ?', [$case['patient_id']]);
$archived = patient_archived($p);

if (is_post()) {
    csrf_check();
    if (handle_attachment_post((int) $p['id'], $id)) {
        redirect(url('case', ['id' => $id]) . '#attachments');
    }
    require_perm('cases.manage');
    $action = post('action');
    if ($action === 'close' && $case['status'] === 'open') {
        update('cases', $id, ['status' => 'closed', 'closed_at' => now(), 'closed_by' => $me['id'], 'updated_at' => now()]);
        audit('case.close', 'case', $id, $case['number']);
        flash('success', 'Fall abgeschlossen.');
    } elseif ($action === 'reopen' && $case['status'] === 'closed' && !$archived) {
        update('cases', $id, ['status' => 'open', 'closed_at' => null, 'closed_by' => null, 'updated_at' => now()]);
        audit('case.reopen', 'case', $id, $case['number']);
        flash('success', 'Fall wieder geöffnet.');
    }
    redirect(url('case', ['id' => $id]));
}

audit('case.view', 'case', $id, $case['number']);
$entries = all('SELECT e.*, u.name AS author, u.title AS author_title FROM entries e LEFT JOIN users u ON u.id = e.created_by
    WHERE e.case_id = ? ORDER BY e.occurred_at, e.id', [$id]);
$docs = all('SELECT d.*, u.name AS issuer_name FROM documents d LEFT JOIN users u ON u.id = d.issued_by WHERE d.case_id = ? ORDER BY d.issued_at', [$id]);
$open = $case['status'] === 'open' && !$archived;

ob_start(); ?>
<div class="page-head">
    <div>
        <h1><?= e($case['title']) ?> <?= case_status_badge($case) ?></h1>
        <p class="muted"><span class="mono"><?= e($case['number']) ?></span> · <?= e(CASE_KINDS[$case['kind']] ?? $case['kind']) ?> ·
            <a href="<?= e(url('patient', ['id' => $p['id']])) ?>"><?= e(patient_name($p, true)) ?></a></p>
    </div>
    <div class="btn-row">
        <?php if ($open): ?>
            <a class="btn" href="<?= e(url('entry_edit', ['patient' => $p['id'], 'case' => $id])) ?>"><?= icon('plus') ?>Eintrag</a>
            <?php if (can_issue_any()): ?><a class="btn primary" href="<?= e(url('document_new', ['patient' => $p['id'], 'case' => $id])) ?>"><?= icon('doc-plus') ?>Dokument aus Fall</a><?php endif; ?>
        <?php endif; ?>
        <?php if (can('cases.manage') && !$archived): ?>
            <a class="btn" href="<?= e(url('case_edit', ['id' => $id])) ?>"><?= icon('edit') ?>Bearbeiten</a>
            <form method="post" class="inline-form"><?= csrf_field() ?>
                <?php if ($case['status'] === 'open'): ?>
                    <button class="btn" name="action" value="close" onclick="return confirm('Fall abschließen? Er kann später wieder geöffnet werden.')">Abschließen</button>
                <?php else: ?>
                    <button class="btn" name="action" value="reopen">Wieder öffnen</button>
                <?php endif; ?>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php if ($archived): ?><div class="flash warn">Die Patientenakte ist archiviert. Der Fall ist schreibgeschützt.</div><?php endif; ?>

<div class="grid-side">
    <section class="card facts">
        <h2>Falldaten</h2>
        <dl>
            <dt>Fallnummer</dt><dd class="mono"><?= e($case['number']) ?></dd>
            <dt>Art</dt><dd><?= e(CASE_KINDS[$case['kind']] ?? $case['kind']) ?></dd>
            <dt>Zeitpunkt</dt><dd><?= e($case['incident_at'] ? fmt_datetime($case['incident_at']) : '–') ?></dd>
            <dt>Ort</dt><dd><?= e($case['location'] ?: '–') ?></dd>
            <dt>Abteilung</dt><dd><?= e($case['department'] ?: '–') ?></dd>
            <dt>Angelegt</dt><dd><?= e(fmt_datetime($case['created_at'])) ?><br><small class="muted"><?= e($case['author']) ?></small></dd>
            <?php if ($case['status'] === 'closed'): ?><dt>Abgeschlossen</dt><dd><?= e(fmt_datetime($case['closed_at'])) ?><br><small class="muted"><?= e($case['closer']) ?></small></dd><?php endif; ?>
        </dl>
        <h2>Hergang / Anlass</h2><p><?= nl($case['incident'] ?: '–') ?></p>
        <h2>Diagnose(n)</h2><p><?= nl($case['diagnosis'] ?: '–') ?></p>
        <?php if (trim((string) $case['summary']) !== ''): ?><h2>Zusammenfassung</h2><p><?= nl($case['summary']) ?></p><?php endif; ?>
    </section>

    <section>
        <div class="card">
            <h2>Einträge (<?= count($entries) ?>)</h2>
            <div class="timeline">
            <?php foreach ($entries as $r): ?>
                <article class="tl entry">
                    <div class="tl-date"><?= e(fmt_date($r['occurred_at'])) ?><small><?= e(fmt_date($r['occurred_at'], 'H:i')) ?></small></div>
                    <div class="tl-body">
                        <div class="tl-head"><span class="badge"><?= e($r['type']) ?></span> <strong><?= e($r['title']) ?></strong>
                            <?php if ($r['confidential']): ?><span class="badge purple">vertraulich</span><?php endif; ?>
                            <?php if (!$archived): ?><a class="tl-edit" href="<?= e(url('entry_edit', ['id' => $r['id']])) ?>">bearbeiten</a><?php endif; ?></div>
                        <?php if (entry_visible($r)): ?><div class="tl-text"><?= nl($r['content']) ?></div>
                        <?php else: ?><div class="tl-text muted"><em>Inhalt für dich nicht einsehbar.</em></div><?php endif; ?>
                        <small class="muted"><?= e(trim($r['author_title'] . ' ' . $r['author'])) ?></small>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$entries): ?><p class="muted">Noch keine Einträge zu diesem Fall.</p><?php endif; ?>
            </div>
        </div>
        <?= attachments_card((int) $p['id'], $id, $archived) ?>
        <div class="card">
            <h2>Dokumente (<?= count($docs) ?>)</h2>
            <table class="list compact">
                <tbody>
                <?php foreach ($docs as $d): $t = doc_type($d['type']); ?>
                    <tr onclick="location.href='<?= e(url('document', ['id' => $d['id']])) ?>'">
                        <td><span class="badge blue"><?= e($t['code'] ?? '?') ?></span> <?= e($t['title'] ?? $d['type']) ?></td>
                        <td class="mono small"><?= e($d['number']) ?></td><td class="small"><?= e(fmt_datetime($d['issued_at'])) ?></td><td><?= doc_status_badge($d) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$docs): ?><tr><td class="muted">Noch keine Dokumente zu diesem Fall.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = $case['number'];
require SAMS_ROOT . '/app/layout.php';
