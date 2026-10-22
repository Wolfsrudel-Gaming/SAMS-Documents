<?php
defined('SAMS_ROOT') || exit;
/**
 * Haftbuch / Gewahrsam: Aufnahme mit Grund, Haft-Einheiten, Zelle, Effekten, Haftfähigkeit, Rechtsbeistand und Telefonat;
 * Entlassung mit Vermerk. Offene Haftbefehle können bei der Aufnahme als vollstreckt geschlossen werden.
 */
$me = current_user();
$manage = can('custody.manage');
$cells = setting_list('custody_cells', 'Zelle 1 | Zelle 2 | Zelle 3 | Sammelzelle');

if (is_post()) {
    csrf_check();
    require_perm('custody.manage');
    $action = post('action');
    if ($action === 'intake') {
        $person = person_from_post();
        $row = $person + [
            'arrested_at' => now(), 'arrested_by' => $me['id'], 'reason' => mb_substr(trim((string) post('reason')), 0, 4000),
            'jail_units' => max(0, min(9999, (int) post('jail_units'))), 'cell' => mb_substr(trim((string) post('cell')), 0, 64),
            'belongings' => mb_substr(trim((string) post('belongings')), 0, 4000), 'fit_check' => '', 'lawyer' => post('lawyer') ? 1 : 0,
            'phone_call' => 0, 'status' => 'in', 'created_at' => now(),
        ];
        if ($row['name'] === '' || $row['reason'] === '') {
            flash('error', 'Bitte Person und Grund angeben.');
            redirect(url('custody'));
        }
        $id = insert('custody', $row);
        audit('custody.intake', 'custody', $id, $row['name'] . ': ' . mb_substr($row['reason'], 0, 120));
        $msg = $row['name'] . ' in Gewahrsam genommen.';
        if (post('close_wanted') && $row['citizen_id'] !== '') {
            foreach (wanted_lookup($row['citizen_id']) as $w) {
                if ((int) $w['faction_id'] === fid()) {
                    update('wanted', (int) $w['id'], ['status' => 'closed', 'closed_at' => now(), 'closed_by' => $me['id'], 'close_note' => 'Festgenommen, Gewahrsam']);
                    $msg .= ' Fahndung/Haftbefehl geschlossen.';
                }
            }
        }
        // Haftfähigkeit durch den Rettungsdienst prüfen lassen (Behördenanfrage)
        if (post('request_medical') && module_enabled('requests') && ($medical = medical_faction_ids())) {
            $to = (int) $medical[0];
            $rid = insert('agency_requests', ['to_faction_id' => $to, 'subject' => 'Haftfähigkeit prüfen: ' . $row['name'], 'citizen_id' => $row['citizen_id'], 'person' => $row['name'],
                'body' => 'Bitte Haftfähigkeit der in Gewahrsam genommenen Person prüfen (' . ($row['cell'] ?: 'Gewahrsam') . ').', 'reference' => 'Haftbuch #' . $id, 'urgent' => 1,
                'status' => 'open', 'created_by' => $me['id'], 'created_at' => now()]);
            $prev = fid();
            set_faction_context($to);
            notify_users_with('requests.manage', 'request', '❗ Haftfähigkeit prüfen: ' . $row['name'], url('requests', ['id' => $rid]));
            set_faction_context($prev);
            $msg .= ' Anfrage an den Rettungsdienst gesendet.';
        }
        flash('success', $msg);
    } elseif (($c = fone('custody', (int) post('id'))) && $c['status'] === 'in') {
        if ($action === 'update') {
            update('custody', (int) $c['id'], ['fit_check' => array_key_exists((string) post('fit_check'), CUSTODY_FIT) ? post('fit_check') : '', 'lawyer' => post('lawyer') ? 1 : 0,
                'phone_call' => post('phone_call') ? 1 : 0, 'cell' => mb_substr(trim((string) post('cell')), 0, 64), 'jail_units' => max(0, min(9999, (int) post('jail_units')))]);
            audit('custody.update', 'custody', (int) $c['id'], $c['name']);
            flash('success', 'Gespeichert.');
        } elseif ($action === 'release') {
            $to = post('to') === 'transferred' ? 'transferred' : 'released';
            update('custody', (int) $c['id'], ['status' => $to, 'released_at' => now(), 'released_by' => $me['id'], 'release_note' => mb_substr(trim((string) post('note')) ?: ($to === 'transferred' ? 'überstellt' : 'entlassen'), 0, 190)]);
            audit('custody.' . $to, 'custody', (int) $c['id'], $c['name'] . ': ' . post('note'));
            flash('success', $c['name'] . ($to === 'transferred' ? ' überstellt.' : ' entlassen.'));
        }
    }
    redirect(url('custody'));
}

$in = all("SELECT c.*, u.name AS officer FROM custody c LEFT JOIN users u ON u.id = c.arrested_by WHERE c.status = 'in' AND " . fw('c') . ' ORDER BY c.arrested_at');
$past = all("SELECT c.*, u.name AS officer, r.name AS releaser FROM custody c LEFT JOIN users u ON u.id = c.arrested_by LEFT JOIN users r ON r.id = c.released_by
    WHERE c.status <> 'in' AND " . fw('c') . ' ORDER BY c.released_at DESC LIMIT 50');
$records = $manage ? record_options() : [];

ob_start(); ?>
<div class="page-head"><div><h1>Haftbuch</h1><p class="muted">Personen im Gewahrsam, Haftfähigkeit, Rechte der festgenommenen Person und Entlassung.</p></div></div>
<div class="grid-side wide-side reverse">
<section>
    <?php foreach ($in as $c): $due = custody_release_due($c); ?>
    <div class="card">
        <div class="card-head"><h2><?= $c['patient_id'] ? '<a href="' . e(url('patient', ['id' => $c['patient_id']])) . '">' . e($c['name']) . '</a>' : e($c['name']) ?>
            <?= $c['citizen_id'] !== '' ? '<small class="mono muted">' . e($c['citizen_id']) . '</small>' : '' ?></h2>
            <span class="badge <?= $c['fit_check'] === 'unfit' ? 'red' : ($c['fit_check'] === '' ? 'orange' : 'green') ?>"><?= e(CUSTODY_FIT[$c['fit_check']] ?? '') ?></span></div>
        <p><?= nl((string) $c['reason']) ?></p>
        <p class="small muted">seit <?= e(fmt_datetime($c['arrested_at'])) ?> · <?= e($c['officer'] ?? '–') ?> · <?= e($c['cell'] ?: 'ohne Zelle') ?>
            <?= $c['jail_units'] ? ' · ' . (int) $c['jail_units'] . ' Haft-Einheiten' . ($due ? ', Entlassung ab ' . e(fmt_datetime($due)) . ($due <= now() ? ' <strong>(fällig)</strong>' : '') : '') : '' ?></p>
        <?php if (trim((string) $c['belongings']) !== ''): ?><p class="small"><strong>Effekten:</strong> <?= e($c['belongings']) ?></p><?php endif; ?>
        <?php if ($manage): ?>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <select name="fit_check"><?php foreach (CUSTODY_FIT as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $c['fit_check']) ?>><?= e($l) ?></option><?php endforeach; ?></select>
            <input name="cell" value="<?= e($c['cell']) ?>" list="cells" placeholder="Zelle" style="max-width:9rem">
            <input type="number" name="jail_units" value="<?= (int) $c['jail_units'] ?>" min="0" style="max-width:6rem" title="Haft-Einheiten">
            <label class="choice single"><input type="checkbox" name="lawyer" value="1"<?= checked($c['lawyer']) ?>> Rechtsbeistand verlangt</label>
            <label class="choice single"><input type="checkbox" name="phone_call" value="1"<?= checked($c['phone_call']) ?>> Telefonat gewährt</label>
            <button class="btn small">Speichern</button></form>
        <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="release"><input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
            <input name="note" maxlength="190" placeholder="Vermerk (z. B. Haft verbüßt, Kaution, an DOJ)">
            <button class="btn small primary" name="to" value="released">Entlassen</button><button class="btn small" name="to" value="transferred">Überstellen</button></form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (!$in): ?><div class="card muted">Zurzeit niemand im Gewahrsam.</div><?php endif; ?>
    <div class="card"><h2>Zuletzt entlassen</h2>
        <table class="list compact"><tbody>
        <?php foreach ($past as $c): ?><tr><td><?= e($c['name']) ?></td><td class="small"><?= e(fmt_datetime($c['arrested_at'])) ?> – <?= e(fmt_datetime($c['released_at'])) ?></td><td class="small"><?= e($c['release_note']) ?> <span class="muted">(<?= e($c['releaser'] ?? '') ?>)</span></td></tr><?php endforeach; ?>
        <?php if (!$past): ?><tr><td class="muted">Noch keine Einträge.</td></tr><?php endif; ?>
        </tbody></table></div>
</section>
<?php if ($manage): ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="intake">
    <h2 class="w-full">In Gewahrsam nehmen</h2>
    <?php if ($records): ?><div class="field w-full"><label>Person (<?= e(term('record')) ?>)</label><select name="patient_id"><option value="">–</option><?php foreach ($records as $r): ?><option value="<?= (int) $r['id'] ?>"><?= e(patient_name($r, true)) ?><?= $r['citizen_id'] ? ' · ' . e($r['citizen_id']) : '' ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field"><label>oder Name</label><input name="name" maxlength="190"></div>
    <div class="field"><label>Bürger-ID</label><input name="citizen_id" maxlength="64"></div>
    <div class="field w-full"><label>Grund / Tatvorwurf</label><textarea name="reason" rows="3" required></textarea></div>
    <div class="field"><label>Haft-Einheiten</label><input type="number" name="jail_units" min="0" value="0"></div>
    <div class="field"><label>Zelle</label><input name="cell" list="cells" value="<?= e($cells[0] ?? '') ?>"><datalist id="cells"><?php foreach ($cells as $cl): ?><option value="<?= e($cl) ?>"><?php endforeach; ?></datalist></div>
    <div class="field w-full"><label>Effekten (abgenommene Gegenstände)</label><textarea name="belongings" rows="2" placeholder="z. B. Handy, Geldbörse mit $120, Schlüssel"></textarea></div>
    <div class="field w-full"><div class="choices">
        <label class="choice"><input type="checkbox" name="lawyer" value="1"> Rechtsbeistand verlangt</label>
        <?php if (module_enabled('wanted')): ?><label class="choice"><input type="checkbox" name="close_wanted" value="1" checked> Offene Fahndungen/Haftbefehle als vollstreckt schließen</label><?php endif; ?>
        <?php if (module_enabled('requests') && medical_faction_ids()): ?><label class="choice"><input type="checkbox" name="request_medical" value="1"> Haftfähigkeit durch Rettungsdienst prüfen lassen</label><?php endif; ?>
    </div></div>
    <div class="form-actions"><button class="btn primary">Aufnehmen</button></div>
</form>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Haftbuch';
require SAMS_ROOT . '/app/layout.php';
