<?php
defined('SAMS_ROOT') || exit;
/** Entwürfe: eigene, übergebene, eingereichte und zur Freigabe wartende Dokumente. */
$me = current_user();
$base = 'SELECT d.*, p.first_name, p.last_name, o.name AS owner_name, c.name AS creator_name, s.name AS submitter_name
    FROM document_drafts d LEFT JOIN patients p ON p.id = d.patient_id LEFT JOIN users o ON o.id = d.owner_id
    LEFT JOIN users c ON c.id = d.created_by LEFT JOIN users s ON s.id = d.submitted_by';
$mine = all("$base WHERE " . fw('d') . " AND d.owner_id = ? AND d.status IN ('draft', 'rejected') ORDER BY d.updated_at DESC", [$me['id']]);
$submitted = all("$base WHERE " . fw('d') . " AND d.submitted_by = ? AND d.status = 'pending' ORDER BY d.submitted_at DESC", [$me['id']]);
$toApprove = can('documents.approve')
    ? array_values(array_filter(all("$base WHERE " . fw('d') . " AND d.status = 'pending' ORDER BY d.submitted_at", []), 'can_approve'))
    : [];
$others = can('documents.drafts_all')
    ? all("$base WHERE " . fw('d') . " AND d.owner_id <> ? AND (d.submitted_by IS NULL OR d.submitted_by <> ?) ORDER BY d.updated_at DESC LIMIT 100", [$me['id'], $me['id']])
    : [];

$table = function (array $rows, string $empty, string $who = 'owner') {
    ob_start(); ?>
    <table class="list">
        <thead><tr><th>Dokument</th><th>Patient/in</th><th><?= $who === 'submitter' ? 'Eingereicht von' : 'Bei' ?></th><th>Stand</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $d): $t = doc_type($d['type']); ?>
            <tr onclick="location.href='<?= e(url('draft', ['id' => $d['id']])) ?>'">
                <td><span class="badge blue"><?= e($t['code'] ?? '?') ?></span> <?= e($t['title'] ?? $d['type']) ?>
                    <?php if ($d['note']): ?><br><small class="muted"><?= e(mb_strimwidth($d['note'], 0, 90, '…')) ?></small><?php endif; ?></td>
                <td><?= e($d['last_name'] ? patient_name($d, true) : '–') ?></td>
                <td class="small"><?= e($who === 'submitter' ? $d['submitter_name'] : $d['owner_name']) ?><?= (int) $d['created_by'] !== (int) $d['owner_id'] && $who === 'owner' ? '<br><span class="muted">angelegt von ' . e($d['creator_name']) . '</span>' : '' ?></td>
                <td class="small"><?= e(fmt_datetime($d['submitted_at'] && $d['status'] === 'pending' ? $d['submitted_at'] : $d['updated_at'])) ?></td>
                <td><?= draft_status_badge($d) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted"><?= e($empty) ?></td></tr><?php endif; ?>
        </tbody>
    </table>
    <?php
    return ob_get_clean();
};

ob_start(); ?>
<div class="page-head">
    <div><h1>Entwürfe &amp; Freigaben</h1><p class="muted">Begonnene Dokumente, Übergaben von Kolleg/innen und Dokumente, die auf Gegenzeichnung warten.</p></div>
    <?php if (can_issue_any()): ?><a class="btn primary" href="<?= e(url('document_new')) ?>"><?= icon('doc-plus') ?>Neues Dokument</a><?php endif; ?>
</div>
<?php if ($toApprove): ?>
<section class="card"><h2>Zur Freigabe (<?= count($toApprove) ?>)</h2><?= $table($toApprove, '', 'submitter') ?></section>
<?php endif; ?>
<section class="card"><h2>Meine Entwürfe (<?= count($mine) ?>)</h2><?= $table($mine, 'Keine offenen Entwürfe. Beim Ausstellen „Als Entwurf speichern“ wählen, um später weiterzumachen.') ?></section>
<section class="card"><h2>Von mir eingereicht (<?= count($submitted) ?>)</h2><?= $table($submitted, 'Nichts wartet auf Freigabe.', 'submitter') ?></section>
<?php if ($others): ?>
<section class="card"><h2>Alle weiteren Entwürfe</h2><?= $table($others, '') ?></section>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Entwürfe';
require SAMS_ROOT . '/app/layout.php';
