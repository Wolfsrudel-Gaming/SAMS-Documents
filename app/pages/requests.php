<?php
defined('SAMS_ROOT') || exit;
/**
 * Behördenanfragen: z. B. Polizei → Rettungsdienst (Blutentnahme, Befund), Justiz → Polizei
 * (Ermittlungsbericht). Die Antwort kann eigene Dokumente für die anfragende Behörde freigeben.
 */
$me = current_user();
$manage = can('requests.manage');
$id = (int) ($_GET['id'] ?? 0);
$targets = array_values(array_filter(other_factions(), fn($f) => module_enabled('requests', (int) $f['id'])));

if (is_post()) {
    csrf_check();
    require_perm('requests.manage');
    $action = post('action');
    if ($action === 'create') {
        $to = (int) post('to_faction_id');
        $row = [
            'to_faction_id' => $to, 'subject' => mb_substr(trim((string) post('subject')), 0, 190), 'body' => mb_substr(trim((string) post('body')), 0, 10000),
            'citizen_id' => mb_substr(trim((string) post('citizen_id')), 0, 64), 'person' => mb_substr(trim((string) post('person')), 0, 190),
            'reference' => mb_substr(trim((string) post('reference')), 0, 190), 'urgent' => post('urgent') ? 1 : 0,
            'status' => 'open', 'created_by' => $me['id'], 'created_at' => now(),
        ];
        if (!in_array($to, array_map(fn($f) => (int) $f['id'], $targets), true) || $row['subject'] === '' || $row['body'] === '') {
            flash('error', 'Bitte Behörde, Betreff und Anfrage angeben.');
            redirect(url('requests', ['new' => 1]));
        }
        $rid = insert('agency_requests', $row);
        audit('request.create', 'request', (int) $rid, $row['subject'] . ' → ' . (faction_by_id($to)['name'] ?? ''));
        $from = current_faction();
        $prev = fid();
        set_faction_context($to);
        notify_users_with('requests.manage', 'request', ($row['urgent'] ? '❗ ' : '') . 'Anfrage von ' . ($from['short'] ?: $from['name']) . ': ' . $row['subject'], url('requests', ['id' => $rid]));
        set_faction_context($prev);
        flash('success', 'Anfrage gesendet.');
        redirect(url('requests', ['id' => $rid]));
    }
    $r = agency_request((int) post('id'));
    if (!$r) {
        not_found();
    }
    $incoming = (int) $r['to_faction_id'] === fid();
    if ($incoming && in_array($action, ['progress', 'answer', 'decline'], true)) {
        $answer = mb_substr(trim((string) post('answer')), 0, 10000);
        $status = ['progress' => 'in_progress', 'answer' => 'answered', 'decline' => 'declined'][$action];
        if ($action !== 'progress' && $answer === '') {
            flash('error', 'Bitte eine Antwort bzw. Begründung angeben.');
            redirect(url('requests', ['id' => $r['id']]));
        }
        update('agency_requests', (int) $r['id'], ['status' => $status] + ($action === 'progress' ? [] : ['answer' => $answer, 'answered_by' => $me['id'], 'answered_at' => now()]));
        // Dokumente der eigenen Fraktion für die anfragende Behörde freigeben
        $shared = 0;
        if ($action === 'answer' && can('documents.share')) {
            $requester = faction_by_id((int) $r['faction_id']);
            foreach (array_map('intval', (array) ($_POST['docs'] ?? [])) as $docId) {
                $doc = one("SELECT * FROM documents WHERE id = ? AND status <> 'revoked' AND " . fw(), [$docId]);
                if ($doc && $requester) {
                    insert('document_shares', ['document_id' => $doc['id'], 'kind' => 'faction', 'target_faction_id' => $requester['id'], 'agency' => (string) $requester['code'],
                        'recipient' => (string) $requester['name'], 'purpose' => 'Anfrage: ' . $r['subject'], 'request_id' => $r['id'], 'expires_at' => date('Y-m-d H:i:s', time() + 30 * 86400),
                        'created_by' => $me['id'], 'created_at' => now()]);
                    audit('document.share', 'document', (int) $doc['id'], $doc['number'] . ' → ' . $requester['name'] . ' (Anfrage #' . $r['id'] . ')');
                    $shared++;
                }
            }
        }
        audit('request.' . $action, 'request', (int) $r['id'], $r['subject'] . ($shared ? " · $shared Dokument(e) freigegeben" : ''));
        if ($action !== 'progress' && $r['created_by']) {
            $prev = fid();
            set_faction_context((int) $r['faction_id']);
            notify_user((int) $r['created_by'], 'request', 'Anfrage ' . ($action === 'answer' ? 'beantwortet' : 'abgelehnt') . ': ' . $r['subject'], url('requests', ['id' => $r['id']]), mb_substr($answer, 0, 300));
            set_faction_context($prev);
        }
        flash('success', ['progress' => 'Als „in Bearbeitung“ markiert.', 'answer' => 'Antwort gesendet' . ($shared ? " ($shared Dokument(e) freigegeben)." : '.'), 'decline' => 'Anfrage abgelehnt.'][$action]);
    }
    redirect(url('requests', ['id' => $r['id']]));
}

$names = [];
foreach (factions(false) as $f) {
    $names[(int) $f['id']] = $f['short'] ?: $f['name'];
}
if ($id) {
    $r = agency_request($id);
    if (!$r) {
        not_found('Anfrage nicht gefunden.');
    }
    $incoming = (int) $r['to_faction_id'] === fid();
    $author = one('SELECT name, title FROM users WHERE id = ?', [$r['created_by']]);
    $answerer = $r['answered_by'] ? one('SELECT name, title FROM users WHERE id = ?', [$r['answered_by']]) : null;
    $sharedDocs = all("SELECT s.*, d.number, d.type, d.issued_at FROM document_shares s JOIN documents d ON d.id = s.document_id WHERE s.request_id = ? AND s.kind = 'faction'", [$r['id']]);
    // Passende eigene Dokumente zur Person (für die Antwort)
    $candidates = $incoming && $manage && can('documents.share') ? all("SELECT d.* FROM documents d LEFT JOIN patients p ON p.id = d.patient_id WHERE " . fw('d') . " AND d.status <> 'revoked'"
        . ($r['citizen_id'] !== '' ? ' AND p.citizen_id = ?' : '') . ' ORDER BY d.issued_at DESC LIMIT 30', $r['citizen_id'] !== '' ? [$r['citizen_id']] : []) : [];
}
$tab = ($_GET['tab'] ?? 'in') === 'out' ? 'out' : 'in';
$list = $id ? [] : all('SELECT r.*, u.name AS author FROM agency_requests r LEFT JOIN users u ON u.id = r.created_by WHERE ' . ($tab === 'in' ? 'r.to_faction_id' : 'r.faction_id') . ' = ?
    ORDER BY r.status IN (\'open\', \'in_progress\') DESC, r.urgent DESC, r.id DESC LIMIT 200', [fid()]);
$badge = fn($s) => '<span class="badge ' . ['open' => 'orange', 'in_progress' => 'blue', 'answered' => 'green', 'declined' => 'grey'][$s] . '">' . e(REQUEST_STATUS[$s]) . '</span>';

ob_start(); ?>
<div class="page-head">
    <div><h1>Behördenanfragen</h1><p class="muted">Anfragen an andere Fraktionen stellen und eingehende beantworten. Antworten können Dokumente direkt freigeben.</p></div>
    <div class="btn-row"><?php if ($id): ?><a class="btn" href="<?= e(url('requests')) ?>">Zur Liste</a><?php endif; ?>
        <?php if ($manage && $targets): ?><a class="btn primary" href="<?= e(url('requests', ['new' => 1])) ?>#new"><?= icon('plus') ?>Neue Anfrage</a><?php endif; ?></div>
</div>
<?php if ($id): ?>
<div class="grid-side wide-side">
<section class="card">
    <div class="card-head"><h2><?= $r['urgent'] ? '❗ ' : '' ?><?= e($r['subject']) ?></h2><?= $badge($r['status']) ?></div>
    <p class="small muted"><?= e($names[(int) $r['faction_id']] ?? '?') ?> → <?= e($names[(int) $r['to_faction_id']] ?? '?') ?> · <?= e(fmt_datetime($r['created_at'])) ?> · <?= e(user_display($author)) ?>
        <?= $r['person'] || $r['citizen_id'] ? '<br>Person: <strong>' . e($r['person']) . '</strong> ' . ($r['citizen_id'] ? '<span class="mono">' . e($r['citizen_id']) . '</span>' : '') : '' ?>
        <?= $r['reference'] ? '<br>Bezug: ' . e($r['reference']) : '' ?></p>
    <div class="prose-ui"><?= nl($r['body']) ?></div>
    <?php if ($r['answer']): ?>
        <h3>Antwort <?= $answerer ? '<small class="muted">· ' . e(user_display($answerer)) . ', ' . e(fmt_datetime($r['answered_at'])) . '</small>' : '' ?></h3>
        <div class="prose-ui"><?= nl($r['answer']) ?></div>
    <?php endif; ?>
    <?php if ($sharedDocs): ?>
        <h3>Freigegebene Dokumente</h3>
        <ul class="plain"><?php foreach ($sharedDocs as $sd): $t = doc_type($sd['type']); ?>
            <li><?php if (!$incoming && share_active($sd)): ?><a href="<?= e(url('shared', ['id' => $sd['document_id']])) ?>"><?= e($t['title'] ?? $sd['type']) ?> <span class="mono"><?= e($sd['number']) ?></span></a><?php else: ?><?= e($t['title'] ?? $sd['type']) ?> <span class="mono"><?= e($sd['number']) ?></span><?php endif; ?>
                <small class="muted">· bis <?= e(fmt_date($sd['expires_at'])) ?></small></li><?php endforeach; ?></ul>
    <?php endif; ?>
</section>
<?php if ($incoming && $manage && in_array($r['status'], ['open', 'in_progress'], true)): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
    <h2 class="w-full">Bearbeiten</h2>
    <div class="field w-full"><label>Antwort</label><textarea name="answer" rows="6"></textarea></div>
    <?php if ($candidates): ?>
    <div class="field w-full"><span class="label">Dokumente freigeben (30 Tage)</span>
        <div class="choices vertical"><?php foreach ($candidates as $c): $t = doc_type($c['type']); $cp = json_decode((string) $c['patient_snapshot'], true) ?: []; ?>
            <label class="choice"><input type="checkbox" name="docs[]" value="<?= (int) $c['id'] ?>"> <?= e($t['short'] ?? $c['type']) ?> <span class="mono small"><?= e($c['number']) ?></span> <small class="muted"><?= e($cp ? patient_name($cp, true) : '') ?> · <?= e(fmt_date($c['issued_at'])) ?></small></label>
        <?php endforeach; ?></div>
        <small class="hint">Medizinische Inhalte nur mit Schweigepflichtentbindung oder richterlicher Anordnung freigeben.</small></div>
    <?php endif; ?>
    <div class="form-actions">
        <?php if ($r['status'] === 'open'): ?><button class="btn" name="action" value="progress" formnovalidate>In Bearbeitung</button><?php endif; ?>
        <button class="btn" name="action" value="decline">Ablehnen</button>
        <button class="btn primary" name="action" value="answer">Antworten</button>
    </div>
</form>
<?php endif; ?>
</div>
<?php else: ?>
<div class="tabs page-tabs"><a href="<?= e(url('requests')) ?>" class="<?= $tab === 'in' ? 'active' : '' ?>">Eingang (<?= request_open_count() ?> offen)</a><a href="<?= e(url('requests', ['tab' => 'out'])) ?>" class="<?= $tab === 'out' ? 'active' : '' ?>">Gestellte Anfragen</a></div>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Anfrage</th><th><?= $tab === 'in' ? 'Von' : 'An' ?></th><th>Person</th><th>Datum</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($list as $l): ?>
            <tr onclick="location.href='<?= e(url('requests', ['id' => $l['id']])) ?>'"><td><?= $l['urgent'] ? '❗ ' : '' ?><strong><?= e($l['subject']) ?></strong><?= $l['reference'] ? '<br><small class="muted">' . e($l['reference']) . '</small>' : '' ?></td>
                <td><?= e($names[(int) ($tab === 'in' ? $l['faction_id'] : $l['to_faction_id'])] ?? '?') ?></td><td class="small"><?= e($l['person'] ?: $l['citizen_id']) ?></td>
                <td class="small"><?= e(fmt_datetime($l['created_at'])) ?></td><td><?= $badge($l['status']) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$list): ?><tr><td colspan="5" class="muted">Keine Anfragen.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php if ($manage && $targets): ?>
<form method="post" class="card form-grid" id="new">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <h2 class="w-full">Neue Anfrage</h2>
    <div class="field w-full"><label>An</label><select name="to_faction_id" required><option value="">– Behörde wählen –</option><?php foreach ($targets as $f): ?><option value="<?= (int) $f['id'] ?>"<?= selected($f['id'], $_GET['to'] ?? '') ?>><?= e($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field w-full"><label>Betreff</label><input name="subject" required maxlength="190" placeholder="z. B. Blutentnahme / Befundbericht angefordert"></div>
    <div class="field"><label>Person</label><input name="person" maxlength="190"></div>
    <div class="field"><label>Bürger-ID</label><input name="citizen_id" maxlength="64"></div>
    <div class="field w-full"><label>Aktenzeichen / Bezug</label><input name="reference" maxlength="190"></div>
    <div class="field w-full"><label>Anfrage</label><textarea name="body" rows="6" required></textarea></div>
    <div class="field w-full"><label class="choice single"><input type="checkbox" name="urgent" value="1"> dringend</label></div>
    <div class="form-actions"><button class="btn primary">Senden</button></div>
</form>
<?php endif; ?>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Behördenanfragen';
require SAMS_ROOT . '/app/layout.php';
