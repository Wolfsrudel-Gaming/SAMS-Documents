<?php
defined('SAMS_ROOT') || exit;
/** Für das eigene Konto freigegebene Dokumente (Behördenkonten wie LSPD oder Justiz). */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
if ($id) {
    $share = user_share_for($id);
    $doc = $share ? one('SELECT * FROM documents WHERE id = ?', [$id]) : null;
    if (!$doc) {
        not_found('Dieses Dokument ist für dich nicht (mehr) freigegeben.');
    }
    share_touch($share, 'Konto');
    $doc['data'] = json_decode((string) $doc['data'], true) ?: [];
    $doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
    $doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
    $type = doc_type($doc['type']);
    ob_start(); ?>
    <div class="page-head no-print">
        <div><h1><?= e($type['title'] ?? $doc['type']) ?> <?= doc_status_badge($doc) ?></h1>
            <p class="muted mono"><?= e($doc['number']) ?> · freigegeben<?= $share['expires_at'] ? ' bis ' . e(fmt_datetime($share['expires_at'])) : '' ?><?= $share['purpose'] ? ' · ' . e($share['purpose']) : '' ?></p></div>
        <div class="btn-row"><button class="btn" onclick="window.print()">Drucken / PDF</button><a class="btn" href="<?= e(url('shared')) ?>">Zur Liste</a></div>
    </div>
    <div class="doc-stage"><?= render_document($doc, ['copy' => $doc['type'] === 'au' ? 'kasse' : '']) ?></div>
    <?php
    $content = ob_get_clean();
    $title = $doc['number'];
    $withDocumentCss = true;
    $scripts = ['assets/vendor/qrcode.js', 'assets/js/document.js'];
    require SAMS_ROOT . '/app/layout.php';
    return;
}

$rows = array_values(array_filter(all("SELECT s.*, d.type, d.number, d.issued_at, d.status, d.patient_snapshot FROM document_shares s
    JOIN documents d ON d.id = s.document_id WHERE s.kind = 'user' AND s.user_id = ? ORDER BY s.id DESC", [$me['id']]), 'share_active'));
ob_start(); ?>
<div class="page-head"><div><h1>Für mich freigegeben</h1><p class="muted">Dokumente, die <?= e(org('org_short')) ?> für dein Konto freigegeben hat. Jeder Abruf wird protokolliert.</p></div></div>
<div class="card">
    <table class="list">
        <thead><tr><th>Dokument</th><th>Patient/in</th><th>Ausgestellt</th><th>Zweck</th><th>Freigabe bis</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $t = doc_type($r['type']); $p = json_decode((string) $r['patient_snapshot'], true) ?: []; ?>
            <tr onclick="location.href='<?= e(url('shared', ['id' => $r['document_id']])) ?>'">
                <td><span class="badge blue"><?= e($t['code'] ?? '?') ?></span> <?= e($t['title'] ?? $r['type']) ?><br><small class="mono muted"><?= e($r['number']) ?></small></td>
                <td><?= e($p ? patient_name($p, true) : '–') ?></td><td class="small"><?= e(fmt_datetime($r['issued_at'])) ?></td>
                <td class="small"><?= e($r['purpose']) ?></td><td class="small"><?= $r['expires_at'] ? e(fmt_datetime($r['expires_at'])) : 'unbegrenzt' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="5" class="muted">Derzeit sind keine Dokumente für dich freigegeben.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Für mich freigegeben';
require SAMS_ROOT . '/app/layout.php';
