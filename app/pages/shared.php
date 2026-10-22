<?php
defined('SAMS_ROOT') || exit;
/** Für das eigene Konto oder die eigene Behörde freigegebene Dokumente anderer Stellen. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
if (!can('documents.shared_view') && !can('documents.view|requests.view')) {
    forbidden();
}
if ($id) {
    $share = faction_share_for($id);
    $doc = $share ? one('SELECT * FROM documents WHERE id = ?', [$id]) : null;
    if (!$doc) {
        not_found('Dieses Dokument ist für dich nicht (mehr) freigegeben.');
    }
    share_touch($share, $share['kind'] === 'faction' ? 'Behörde ' . (current_faction()['short'] ?: current_faction()['name']) . ' · ' . user_display($me) : 'Konto');
    $doc['data'] = json_decode((string) $doc['data'], true) ?: [];
    $doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
    $doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
    $type = doc_type($doc['type']);
    $from = faction_by_id((int) $doc['faction_id']);
    ob_start(); ?>
    <div class="page-head no-print">
        <div><h1><?= e($type['title'] ?? $doc['type']) ?> <?= doc_status_badge($doc) ?></h1>
            <p class="muted mono"><?= e($doc['number']) ?> · <?= e($from['name'] ?? '') ?> · freigegeben<?= $share['expires_at'] ? ' bis ' . e(fmt_datetime($share['expires_at'])) : '' ?><?= $share['purpose'] ? ' · ' . e($share['purpose']) : '' ?></p></div>
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

$factionShares = can('documents.view|requests.view');
$rows = array_values(array_filter(all("SELECT s.*, d.type, d.number, d.issued_at, d.status, d.patient_snapshot, d.faction_id AS doc_faction FROM document_shares s
    JOIN documents d ON d.id = s.document_id WHERE (s.kind = 'user' AND s.user_id = ?)" . ($factionShares ? " OR (s.kind = 'faction' AND s.target_faction_id = " . fid() . ')' : '') . ' ORDER BY s.id DESC LIMIT 300',
    [$me['id']]), 'share_active'));
ob_start(); ?>
<div class="page-head"><div><h1>Freigegebene Dokumente</h1><p class="muted">Dokumente anderer Stellen, die für dein Konto oder deine Behörde freigegeben wurden. Jeder Abruf wird protokolliert.</p></div></div>
<div class="card">
    <table class="list">
        <thead><tr><th>Dokument</th><th>Von</th><th>Person</th><th>Ausgestellt</th><th>Zweck</th><th>Freigabe bis</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): $t = doc_type($r['type']); $p = json_decode((string) $r['patient_snapshot'], true) ?: []; $f = faction_by_id((int) $r['doc_faction']); ?>
            <tr onclick="location.href='<?= e(url('shared', ['id' => $r['document_id']])) ?>'">
                <td><span class="badge blue"><?= e($t['code'] ?? '?') ?></span> <?= e($t['title'] ?? $r['type']) ?><br><small class="mono muted"><?= e($r['number']) ?></small></td>
                <td class="small"><?= e($f['short'] ?? '') ?: e($f['name'] ?? '') ?><?= $r['kind'] === 'user' ? '<br><span class="muted">an dich</span>' : '' ?></td>
                <td><?= e($p ? patient_name($p, true) : '–') ?></td><td class="small"><?= e(fmt_datetime($r['issued_at'])) ?></td>
                <td class="small"><?= e($r['purpose']) ?></td><td class="small"><?= $r['expires_at'] ? e(fmt_datetime($r['expires_at'])) : 'unbegrenzt' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="muted">Derzeit sind keine Dokumente freigegeben.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$title = 'Freigegebene Dokumente';
require SAMS_ROOT . '/app/layout.php';
