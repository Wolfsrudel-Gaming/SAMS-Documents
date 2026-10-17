<?php
defined('SAMS_ROOT') || exit;
/**
 * Fahndung: Personen und Fahrzeuge zur Fahndung ausschreiben und erledigen.
 * Haftbefehle der Justiz erscheinen automatisch. Die Justiz sieht hier den Stand ihrer Haftbefehle.
 */
$me = current_user();
$manage = can('wanted.manage');
$justice = faction_kind() === 'justice';

if (is_post()) {
    csrf_check();
    require_perm('wanted.manage');
    $action = post('action');
    if ($action === 'create') {
        $kind = post('kind') === 'vehicle' ? 'vehicle' : 'person';
        $p = (int) post('patient_id') ? fone('patients', (int) post('patient_id')) : null;
        $row = [
            'kind' => $kind, 'patient_id' => $p['id'] ?? null,
            'citizen_id' => mb_substr(trim((string) ($p['citizen_id'] ?? post('citizen_id'))), 0, 64),
            'name' => mb_substr(trim($p ? patient_name($p) : (string) post('name')), 0, 190),
            'plate' => normalize_plate((string) post('plate')), 'vehicle' => mb_substr(trim((string) post('vehicle')), 0, 190),
            'reason' => mb_substr(trim((string) post('reason')), 0, 2000), 'danger' => max(0, min(3, (int) post('danger'))),
            'expires_at' => preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) post('expires')) ? post('expires') . ' 23:59:59' : null,
            'created_by' => $me['id'], 'created_at' => now(),
        ];
        if ($row['reason'] === '' || ($kind === 'person' && $row['name'] === '' && $row['citizen_id'] === '') || ($kind === 'vehicle' && $row['plate'] === '')) {
            flash('error', $kind === 'vehicle' ? 'Bitte Kennzeichen und Grund angeben.' : 'Bitte Person (Akte, Name oder Bürger-ID) und Grund angeben.');
        } else {
            $id = insert('wanted', $row);
            audit('wanted.create', 'wanted', (int) $id, ($kind === 'vehicle' ? $row['plate'] : $row['name']) . ': ' . mb_substr($row['reason'], 0, 120));
            notify_users_with('wanted.view', 'wanted', 'Neue Fahndung: ' . ($kind === 'vehicle' ? 'Fahrzeug ' . $row['plate'] : $row['name']), url('wanted'));
            flash('success', 'Zur Fahndung ausgeschrieben.');
        }
    } elseif ($action === 'close' && ($w = fone('wanted', (int) post('id'))) && $w['status'] === 'open') {
        update('wanted', (int) $w['id'], ['status' => 'closed', 'closed_at' => now(), 'closed_by' => $me['id'], 'close_note' => mb_substr(trim((string) post('note')) ?: 'erledigt', 0, 190)]);
        audit('wanted.close', 'wanted', (int) $w['id'], ($w['plate'] ?: $w['name']) . ': ' . post('note'));
        flash('success', 'Fahndung erledigt.');
    }
    redirect(url('wanted'));
}

$view = ($_GET['view'] ?? '') === 'closed' ? 'closed' : 'open';
$q = trim((string) ($_GET['q'] ?? ''));
$rows = $justice ? [] : all('SELECT w.*, u.name AS author, c.name AS closer, f.short AS source_short FROM wanted w LEFT JOIN users u ON u.id = w.created_by
    LEFT JOIN users c ON c.id = w.closed_by LEFT JOIN factions f ON f.id = w.source_faction_id WHERE ' . fw('w') . ' AND w.status = ?'
    . ($q !== '' ? ' AND (w.name LIKE ? OR w.citizen_id LIKE ? OR w.plate LIKE ? OR w.reason LIKE ?)' : '') . ' ORDER BY w.danger DESC, w.id DESC LIMIT 200',
    array_merge([$view], $q !== '' ? [like($q), like($q), like(normalize_plate($q) ?: $q), like($q)] : []));
// Justiz: eigene Haftbefehle mit Stand der Vollstreckung
$warrants = $justice ? all("SELECT d.*, (SELECT COUNT(*) FROM wanted w WHERE w.document_id = d.id AND w.status = 'open') AS open_count,
    (SELECT w.close_note FROM wanted w WHERE w.document_id = d.id AND w.status = 'closed' AND w.closed_by IS NOT NULL ORDER BY w.closed_at DESC LIMIT 1) AS executed
    FROM documents d WHERE d.type = 'doj_haftbefehl' AND " . fw('d') . ' ORDER BY d.issued_at DESC LIMIT 100') : [];
$records = $manage && !$justice ? all('SELECT id, first_name, last_name, citizen_id FROM patients WHERE archived_at IS NULL AND merged_into IS NULL AND ' . fw() . ' ORDER BY last_name LIMIT 500') : [];
$expired = fn($w) => $w['expires_at'] && $w['expires_at'] < now();

ob_start(); ?>
<div class="page-head">
    <div><h1>Fahndung</h1><p class="muted"><?= $justice ? 'Erlassene Haftbefehle und ihr Stand bei den Polizeibehörden.' : 'Personen und Fahrzeuge. Haftbefehle der Justiz erscheinen automatisch.' ?></p></div>
</div>
<?php if ($justice): ?>
<div class="card">
    <table class="list">
        <thead><tr><th>Haftbefehl</th><th>Person</th><th>Erlassen</th><th>Stand</th></tr></thead>
        <tbody>
        <?php foreach ($warrants as $d): $p = json_decode((string) $d['patient_snapshot'], true) ?: []; ?>
            <tr onclick="location.href='<?= e(url('document', ['id' => $d['id']])) ?>'"><td class="mono"><?= e($d['number']) ?></td><td><?= e($p ? patient_name($p, true) : '–') ?></td>
                <td class="small"><?= e(fmt_datetime($d['issued_at'])) ?></td>
                <td><?= $d['status'] === 'revoked' ? '<span class="badge grey">storniert</span>' : ($d['executed'] ? '<span class="badge green">vollstreckt</span> <small>' . e($d['executed']) . '</small>' : ((int) $d['open_count'] ? '<span class="badge red">in Fahndung</span>' : '<span class="badge grey">abgelaufen</span>')) ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$warrants): ?><tr><td colspan="4" class="muted">Noch keine Haftbefehle. Haftbefehle werden als Dokument „Haftbefehl“ erlassen.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<form class="card filter" action="index.php"><input type="hidden" name="p" value="wanted">
    <input name="q" value="<?= e($q) ?>" placeholder="Name, Bürger-ID, Kennzeichen oder Grund …">
    <select name="view" onchange="this.form.submit()"><option value="open">Offene Fahndungen</option><option value="closed"<?= selected($view, 'closed') ?>>Erledigt</option></select>
    <button class="btn">Suchen</button>
</form>
<div class="grid-side wide-side reverse">
<section>
    <?php foreach ($rows as $w): ?>
    <div class="card wanted-card danger-<?= (int) $w['danger'] ?><?= $expired($w) ? ' expired' : '' ?>">
        <div class="card-head">
            <h2><?= $w['kind'] === 'vehicle' ? '🚗 <span class="mono">' . e($w['plate']) . '</span> ' . e($w['vehicle']) : '👤 ' . e($w['name'] ?: 'unbekannt') ?>
                <?= $w['citizen_id'] ? '<small class="mono muted">' . e($w['citizen_id']) . '</small>' : '' ?></h2>
            <span class="badge <?= (int) $w['danger'] >= 3 ? 'red' : ((int) $w['danger'] === 2 ? 'orange' : 'grey') ?>"><?= e(WANTED_DANGER[(int) $w['danger']]) ?></span>
        </div>
        <p><?= nl((string) $w['reason']) ?></p>
        <p class="small muted">
            <?= $w['source'] === 'warrant' ? '<span class="badge blue">Haftbefehl ' . e($w['source_short'] ?: 'Justiz') . '</span> ' : '' ?>
            ausgeschrieben <?= e(fmt_datetime($w['created_at'])) ?><?= $w['author'] ? ' von ' . e($w['author']) : '' ?>
            <?= $w['expires_at'] ? ' · gültig bis ' . e(fmt_date($w['expires_at'])) . ($expired($w) ? ' (abgelaufen)' : '') : '' ?>
            <?= $w['patient_id'] ? ' · <a href="' . e(url('patient', ['id' => $w['patient_id']])) . '">Akte</a>' : '' ?>
            <?= $w['document_id'] && faction_share_for((int) $w['document_id']) ? ' · <a href="' . e(url('shared', ['id' => $w['document_id']])) . '">Haftbefehl ansehen</a>' : '' ?>
            <?= $w['status'] === 'closed' ? '<br>erledigt ' . e(fmt_datetime($w['closed_at'])) . ($w['closer'] ? ' von ' . e($w['closer']) : '') . ': ' . e($w['close_note']) : '' ?>
        </p>
        <?php if ($manage && $w['status'] === 'open'): ?>
            <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="close"><input type="hidden" name="id" value="<?= (int) $w['id'] ?>">
                <input name="note" placeholder="z. B. Festgenommen, Fahrzeug sichergestellt" maxlength="190"><button class="btn small">Erledigen</button></form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$rows): ?><div class="card muted"><?= $view === 'open' ? 'Keine offenen Fahndungen.' : 'Keine erledigten Fahndungen.' ?></div><?php endif; ?>
</section>
<?php if ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="create">
    <h2 class="w-full">Ausschreiben</h2>
    <div class="field w-full"><div class="choices"><label class="choice"><input type="radio" name="kind" value="person" checked> Person</label><label class="choice"><input type="radio" name="kind" value="vehicle"> Fahrzeug</label></div></div>
    <div class="field w-full"><label>Person aus <?= e(term('records')) ?></label><select name="patient_id"><option value="">– keine Akte / unbekannt –</option>
        <?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e(patient_name($r, true)) ?><?= $r['citizen_id'] ? ' · ' . e($r['citizen_id']) : '' ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Name (ohne Akte)</label><input name="name" maxlength="190"></div>
    <div class="field"><label>Bürger-ID</label><input name="citizen_id" maxlength="64"></div>
    <div class="field"><label>Kennzeichen</label><input name="plate" maxlength="16" class="mono" placeholder="z. B. 12ABC345"></div>
    <div class="field"><label>Fahrzeug</label><input name="vehicle" maxlength="190" placeholder="Modell, Farbe"></div>
    <div class="field w-full"><label>Grund</label><textarea name="reason" rows="3" required></textarea></div>
    <div class="field"><label>Gefährlichkeit</label><select name="danger"><?php foreach (WANTED_DANGER as $k => $l): ?><option value="<?= $k ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Gültig bis (optional)</label><input type="date" name="expires"></div>
    <div class="form-actions"><button class="btn primary">Ausschreiben</button></div>
</form>
<?php endif; ?>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Fahndung';
require SAMS_ROOT . '/app/layout.php';
