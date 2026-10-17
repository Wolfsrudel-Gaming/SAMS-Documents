<?php
defined('SAMS_ROOT') || exit;
/** Medikamentenbestand: Artikel, Buchungen (Zugang, Entnahme, Verwurf, Korrektur), Bewegungen. */
$me = current_user();
$manage = can('stock.manage');

/** Patient aus Eingabe „Bürger-ID“ oder „#Akten-Nr.“ */
$findPatient = function (string $v): ?array {
    $v = trim($v);
    if ($v === '') {
        return null;
    }
    if (preg_match('/^#(\d+)$/', $v, $m)) {
        return one('SELECT * FROM patients WHERE id = ? AND merged_into IS NULL', [(int) $m[1]]);
    }
    return one('SELECT * FROM patients WHERE citizen_id = ? AND merged_into IS NULL', [$v]);
};

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'save_med') {
        require_perm('stock.manage');
        $data = [
            'name' => mb_substr(trim((string) post('name')), 0, 190),
            'keywords' => mb_substr(trim((string) post('keywords')), 0, 190),
            'is_btm' => post('is_btm') ? 1 : 0,
            'unit' => mb_substr(trim((string) post('unit')) ?: 'Packung', 0, 32),
            'min_stock' => max(0, (int) post('min_stock')),
            'item_name' => preg_replace('/[^a-z0-9_]/i', '', (string) post('item_name')),
            'item_count' => max(1, min(50, (int) post('item_count') ?: 1)),
            'active' => post('active', '1') ? 1 : 0,
            'updated_at' => now(),
        ];
        $old = one('SELECT * FROM medications WHERE id = ?', [(int) post('id')]);
        if ($data['name'] === '') {
            flash('error', 'Bitte eine Bezeichnung angeben.');
        } elseif ($old && $old['is_btm'] != $data['is_btm'] && !can('btm.manage')) {
            flash('error', 'Die BtM-Einstufung darf nur ändern, wer das BtM-Buch führt.');
        } elseif ($old) {
            update('medications', (int) $old['id'], $data);
            audit('stock.article', 'medication', (int) $old['id'], $data['name'] . ' geändert');
            flash('success', 'Artikel gespeichert.');
        } else {
            $id = insert('medications', $data + ['created_at' => now()]);
            audit('stock.article', 'medication', (int) $id, $data['name'] . ' angelegt');
            flash('success', 'Artikel angelegt.');
        }
        redirect(url('stock'));
    }
    if ($action === 'move') {
        $med = one('SELECT * FROM medications WHERE id = ?', [(int) post('medication_id')]);
        $kind = (string) post('kind');
        $qty = abs((int) post('quantity'));
        $patient = $findPatient((string) post('patient'));
        $witness = (int) post('witness_id') ? one('SELECT * FROM users WHERE id = ? AND active = 1', [(int) post('witness_id')]) : null;
        $note = trim((string) post('note'));
        $err = null;
        $needs = ['in' => 'stock.manage', 'out' => 'stock.dispense', 'loss' => 'stock.dispense', 'correct' => 'stock.manage'][$kind] ?? null;
        if (!$med || !$needs) {
            $err = 'Bitte Artikel und Buchungsart wählen.';
        } elseif (!can($needs) || ($med['is_btm'] && in_array($kind, ['in', 'correct'], true) && !can('btm.manage'))) {
            http_response_code(403);
            $err = 'Dafür fehlt dir die Berechtigung.';
        } elseif ($kind !== 'correct' && $qty < 1) {
            $err = 'Bitte eine Menge angeben.';
        } elseif (trim((string) post('patient')) !== '' && !$patient) {
            $err = 'Patient nicht gefunden (Bürger-ID oder #Aktennummer).';
        } elseif ($med['is_btm'] && $kind === 'out' && !$patient) {
            $err = 'Betäubungsmittel: Bei der Entnahme muss der Patient angegeben werden.';
        } elseif ($med['is_btm'] && $kind === 'loss' && (!$witness || (int) $witness['id'] === (int) $me['id'])) {
            $err = 'Betäubungsmittel: Verwurf/Vernichtung nur mit einer zweiten Person als Zeuge.';
        } elseif (in_array($kind, ['loss', 'correct'], true) && $note === '') {
            $err = 'Bitte einen Grund angeben.';
        }
        if (!$err) {
            $delta = match ($kind) { 'in' => $qty, 'correct' => (int) post('quantity') - (int) $med['stock'], default => -$qty };
            if ($kind === 'correct' && (int) post('quantity') < 0) {
                $err = 'Der Ist-Bestand kann nicht negativ sein.';
            } elseif ($kind === 'correct' && $delta === 0) {
                $err = 'Der Bestand stimmt bereits.';
            } elseif (!medication_move((int) $med['id'], $delta, $kind, ['patient_id' => $patient['id'] ?? null, 'witness_id' => $witness['id'] ?? null,
                'party' => (string) post('party'), 'note' => $note])) {
                $err = 'Nicht genug vorrätig (Bestand: ' . $med['stock'] . ' ' . $med['unit'] . ').';
            } else {
                audit('stock.' . $kind, 'medication', (int) $med['id'], MED_MOVE_KINDS[$kind] . ': ' . ($delta > 0 ? '+' : '') . $delta . ' ' . $med['unit'] . ' ' . $med['name']
                    . ($patient ? ' · ' . patient_name($patient, true) : ''));
                if ($patient && $kind === 'out') {
                    insert('entries', ['patient_id' => $patient['id'], 'type' => 'Medikation', 'title' => 'Medikament verabreicht/ausgegeben', 'content' => $qty . ' ' . $med['unit'] . ' ' . $med['name'] . ($note !== '' ? "\n" . $note : ''),
                        'confidential' => 0, 'created_by' => $me['id'], 'occurred_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
                }
                flash('success', MED_MOVE_KINDS[$kind] . ' gebucht: ' . $med['name'] . ' (neuer Bestand ' . ((int) $med['stock'] + $delta) . ').');
            }
        }
        if ($err) {
            flash('error', $err);
        }
        redirect(url('stock', array_filter(['med' => (int) post('return_med') ?: null])));
    }
}

$q = trim((string) ($_GET['q'] ?? ''));
$filter = (string) ($_GET['f'] ?? '');
$meds = array_values(array_filter(medications(!isset($_GET['all'])), fn($m) => ($q === '' || mb_stripos($m['name'] . ' ' . $m['keywords'], $q) !== false)
    && ($filter !== 'low' || (int) $m['stock'] <= (int) $m['min_stock']) && ($filter !== 'btm' || $m['is_btm'])));
$focus = isset($_GET['med']) ? one('SELECT * FROM medications WHERE id = ?', [(int) $_GET['med']]) : null;
$edit = $manage && isset($_GET['edit']) ? (one('SELECT * FROM medications WHERE id = ?', [(int) $_GET['edit']]) ?: []) : null;
$moves = all('SELECT m.*, x.name AS med_name, x.unit, u.name AS user_name, w.name AS witness_name, p.first_name, p.last_name FROM medication_moves m
    JOIN medications x ON x.id = m.medication_id LEFT JOIN users u ON u.id = m.user_id LEFT JOIN users w ON w.id = m.witness_id LEFT JOIN patients p ON p.id = m.patient_id'
    . ($focus ? ' WHERE m.medication_id = ' . (int) $focus['id'] : '') . ' ORDER BY m.id DESC LIMIT 40');
$low = (int) scalar('SELECT COUNT(*) FROM medications WHERE active = 1 AND min_stock > 0 AND stock <= min_stock');
$witnesses = array_values(array_filter(all('SELECT * FROM users WHERE active = 1 AND id <> ? ORDER BY name', [$me['id']]), fn($u) => in_array('stock.dispense', user_permissions($u), true)));
$patients = can('patients.view') ? all('SELECT id, first_name, last_name, citizen_id FROM patients WHERE archived_at IS NULL AND merged_into IS NULL ORDER BY updated_at DESC LIMIT 300') : [];
$kinds = array_filter(['out' => can('stock.dispense'), 'in' => $manage, 'loss' => can('stock.dispense'), 'correct' => $manage]);

ob_start(); ?>
<div class="page-head">
    <div><h1>Medikamentenbestand</h1><p class="muted"><?= count($meds) ?> Artikel<?= $low ? ' · <span class="badge red">' . $low . ' am Mindestbestand</span>' : '' ?> · alle Buchungen werden protokolliert</p></div>
    <div class="btn-row">
        <a class="btn" href="<?= e(url('btm')) ?>">BtM-Buch</a>
        <?php if ($manage): ?><a class="btn primary" href="<?= e(url('stock', ['edit' => 0])) ?>#med-form"><?= icon('plus') ?>Artikel anlegen</a><?php endif; ?>
    </div>
</div>
<form class="card filter" method="get"><input type="hidden" name="p" value="stock">
    <input name="q" value="<?= e($q) ?>" placeholder="Artikel suchen …">
    <select name="f"><option value="">Alle</option><option value="low"<?= selected($filter, 'low') ?>>Am Mindestbestand</option><option value="btm"<?= selected($filter, 'btm') ?>>Nur Betäubungsmittel</option></select>
    <label class="choice single"><input type="checkbox" name="all" value="1"<?= checked(isset($_GET['all'])) ?>> inaktive zeigen</label>
    <button class="btn">Filtern</button>
</form>
<div class="grid-side wide-side reverse">
<section>
    <div class="card">
        <table class="list">
            <thead><tr><th>Artikel</th><th class="right">Bestand</th><th class="right">Mindest</th><th>Item im Spiel</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($meds as $m): $isLow = (int) $m['min_stock'] > 0 && (int) $m['stock'] <= (int) $m['min_stock']; ?>
                <tr class="<?= $focus && (int) $focus['id'] === (int) $m['id'] ? 'selected' : '' ?>">
                    <td><a href="<?= e(url('stock', ['med' => $m['id']])) ?>"><?= e($m['name']) ?></a><?= $m['is_btm'] ? ' <span class="badge orange">BtM</span>' : '' ?><?= $m['active'] ? '' : ' <span class="badge grey">inaktiv</span>' ?></td>
                    <td class="right"><strong class="<?= $isLow ? 'text-danger' : '' ?>"><?= (int) $m['stock'] ?></strong> <small class="muted"><?= e($m['unit']) ?></small></td>
                    <td class="right muted"><?= (int) $m['min_stock'] ?: '–' ?></td>
                    <td class="small mono"><?= $m['item_name'] ? e($m['item_name']) . ((int) $m['item_count'] > 1 ? ' ×' . (int) $m['item_count'] : '') : '<span class="muted">–</span>' ?></td>
                    <td class="right"><?php if ($manage): ?><a class="btn small" href="<?= e(url('stock', ['edit' => $m['id']])) ?>#med-form">Bearbeiten</a><?php endif; ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$meds): ?><tr><td class="muted" colspan="5">Keine Artikel gefunden.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card">
        <div class="card-head"><h2>Bewegungen<?= $focus ? ': ' . e($focus['name']) : '' ?></h2><?php if ($focus): ?><a href="<?= e(url('stock')) ?>">alle</a><?php endif; ?></div>
        <table class="list compact">
            <thead><tr><th>Zeit</th><th>Art</th><?php if (!$focus): ?><th>Artikel</th><?php endif; ?><th class="right">Menge</th><th class="right">Bestand</th><th>Details</th></tr></thead>
            <tbody>
            <?php foreach ($moves as $mv): ?>
                <tr>
                    <td class="small"><?= e(fmt_datetime($mv['created_at'])) ?></td>
                    <td class="small"><?= e(MED_MOVE_KINDS[$mv['kind']] ?? $mv['kind']) ?><?= $mv['source'] === 'game' ? ' <span class="badge">Spiel</span>' : '' ?></td>
                    <?php if (!$focus): ?><td class="small"><?= e($mv['med_name']) ?></td><?php endif; ?>
                    <td class="right <?= $mv['delta'] < 0 ? 'text-danger' : '' ?>"><?= $mv['delta'] > 0 ? '+' : '' ?><?= (int) $mv['delta'] ?></td>
                    <td class="right"><?= (int) $mv['balance'] ?></td>
                    <td class="small"><?= e(implode(' · ', array_filter([$mv['user_name'], $mv['first_name'] ? patient_name($mv, true) : '', $mv['witness_name'] ? 'Zeuge: ' . $mv['witness_name'] : '', $mv['party'], $mv['note']]))) ?>
                        <?= $mv['document_id'] ? '<a href="' . e(url('document', ['id' => $mv['document_id']])) . '">Rezept</a>' : '' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$moves): ?><tr><td class="muted" colspan="6">Noch keine Buchungen.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<section>
    <?php if ($kinds): ?>
    <form method="post" class="card form-grid">
        <?= csrf_field() ?><input type="hidden" name="action" value="move"><input type="hidden" name="return_med" value="<?= (int) ($focus['id'] ?? 0) ?>">
        <h2 class="w-full">Buchen</h2>
        <div class="field w-full"><label>Artikel</label><select name="medication_id" required><option value="">– wählen –</option>
            <?php foreach (medications() as $m): ?><option value="<?= (int) $m['id'] ?>"<?= selected($m['id'], $focus['id'] ?? '') ?>><?= e($m['name']) ?> (<?= (int) $m['stock'] ?>)<?= $m['is_btm'] ? ' – BtM' : '' ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Art</label><select name="kind"><?php foreach ($kinds as $k => $_): ?><option value="<?= e($k) ?>"><?= e(MED_MOVE_KINDS[$k]) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label>Menge</label><input type="number" name="quantity" min="0" max="9999" value="1" required><small class="hint">bei Korrektur: Ist-Bestand</small></div>
        <div class="field w-full"><label>Patient (optional)</label><input name="patient" list="dl-stock-patients" placeholder="Bürger-ID oder #Aktennummer">
            <datalist id="dl-stock-patients"><?php foreach ($patients as $p): ?><option value="<?= e($p['citizen_id'] ?: '#' . $p['id']) ?>"><?= e(patient_name($p, true)) ?></option><?php endforeach; ?></datalist>
            <small class="hint">Bei Entnahme wird automatisch ein Akteneintrag geschrieben. Pflicht bei Betäubungsmitteln.</small></div>
        <div class="field"><label>Lieferant / Empfänger</label><input name="party" maxlength="190" placeholder="z. B. Großhandel, RTW 2"></div>
        <div class="field"><label>Zeuge</label><select name="witness_id"><option value="">–</option><?php foreach ($witnesses as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e(user_display($u)) ?></option><?php endforeach; ?></select></div>
        <div class="field w-full"><label>Bemerkung / Grund</label><input name="note" maxlength="190" placeholder="Pflicht bei Verwurf und Korrektur"></div>
        <div class="form-actions"><button class="btn primary">Buchen</button></div>
    </form>
    <?php endif; ?>
    <?php if ($edit !== null): $m = $edit + ['id' => 0, 'name' => '', 'keywords' => '', 'is_btm' => 0, 'unit' => 'Packung', 'min_stock' => 0, 'item_name' => '', 'item_count' => 1, 'active' => 1]; ?>
    <form method="post" class="card form-grid" id="med-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="save_med"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
        <h2 class="w-full"><?= $m['id'] ? 'Artikel bearbeiten' : 'Artikel anlegen' ?></h2>
        <div class="field w-full"><label>Bezeichnung</label><input name="name" required maxlength="190" value="<?= e($m['name']) ?>" placeholder="Präparat, Stärke, Packungsgröße"><small class="hint">Erscheint als Vorschlag im Rezept-Formular.</small></div>
        <div class="field w-full"><label>Weitere Suchbegriffe (Komma)</label><input name="keywords" maxlength="190" value="<?= e($m['keywords']) ?>" placeholder="z. B. Wirkstoff, Handelsname"><small class="hint">Damit werden Rezeptzeilen dem Artikel zugeordnet.</small></div>
        <div class="field"><label>Einheit</label><input name="unit" maxlength="32" value="<?= e($m['unit']) ?>"></div>
        <div class="field"><label>Mindestbestand</label><input type="number" name="min_stock" min="0" value="<?= (int) $m['min_stock'] ?>"><small class="hint">0 = keine Warnung</small></div>
        <div class="field"><label>ox_inventory-Item</label><input name="item_name" class="mono" maxlength="64" value="<?= e($m['item_name']) ?>" placeholder="z. B. painkillers"><small class="hint">leer = nur in der Klinik</small></div>
        <div class="field"><label>Items je Einheit</label><input type="number" name="item_count" min="1" max="50" value="<?= (int) $m['item_count'] ?>"></div>
        <div class="field w-full">
            <label class="choice single"><input type="checkbox" name="is_btm" value="1"<?= checked($m['is_btm']) ?><?= can('btm.manage') ? '' : ' disabled' ?>> Betäubungsmittel (BtM-Buch, nur auf BtM-Rezept)</label>
            <?php if (!can('btm.manage') && $m['is_btm']): ?><input type="hidden" name="is_btm" value="1"><?php endif; ?>
            <input type="hidden" name="active" value="0"><label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($m['active']) ?>> aktiv</label>
        </div>
        <div class="form-actions"><a class="btn" href="<?= e(url('stock')) ?>">Abbrechen</a><button class="btn primary">Speichern</button></div>
    </form>
    <?php endif; ?>
</section>
</div>
<?php
$content = ob_get_clean();
$title = 'Medikamentenbestand';
require SAMS_ROOT . '/app/layout.php';
