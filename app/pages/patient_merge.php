<?php
defined('SAMS_ROOT') || exit;
/** Doppelte Akten finden und zusammenführen. */
$me = current_user();
$target = (int) ($_GET['target'] ?? post('target', 0));
$source = (int) ($_GET['source'] ?? post('source', 0));

if (is_post()) {
    csrf_check();
    try {
        $moved = merge_patients($target, $source, is_array($_POST['pick'] ?? null) ? $_POST['pick'] : [], (int) $me['id']);
        flash('success', 'Akten zusammengeführt (' . array_sum($moved) . ' Einträge, Dokumente, Fälle und Anhänge übernommen).');
        redirect(url('patient', ['id' => $target]));
    } catch (RuntimeException $e) {
        flash('error', $e->getMessage());
        redirect(url('patient_merge'));
    }
}

$a = $target ? one('SELECT * FROM patients WHERE id = ? AND merged_into IS NULL AND ' . fw(), [$target]) : null;
$b = $source ? one('SELECT * FROM patients WHERE id = ? AND merged_into IS NULL AND ' . fw(), [$source]) : null;
$count = fn(int $pid) => array_sum(array_map(fn($t) => (int) scalar("SELECT COUNT(*) FROM $t WHERE patient_id = ?", [$pid]), ['entries', 'documents', 'cases', 'attachments']));

ob_start(); ?>
<div class="page-head">
    <div><h1>Akten zusammenführen</h1><p class="muted">Doppelt angelegte Personen in eine Akte übernehmen. Die doppelte Akte wird archiviert und leitet auf die verbleibende um.</p></div>
    <a class="btn" href="<?= e(url('patients')) ?>"><?= e(term('records')) ?></a>
</div>
<?php if ($a && $b && $a['id'] !== $b['id']): ?>
    <form method="post" class="card">
        <?= csrf_field() ?><input type="hidden" name="target" value="<?= (int) $a['id'] ?>"><input type="hidden" name="source" value="<?= (int) $b['id'] ?>">
        <h2>Welche Angaben sollen bleiben?</h2>
        <table class="list merge-table">
            <thead><tr><th></th>
                <th>Bleibt: Akte <?= str_pad((string) $a['id'], 6, '0', STR_PAD_LEFT) ?><br><small class="muted"><?= $count((int) $a['id']) ?> Einträge/Dokumente/Fälle/Anhänge · angelegt <?= e(fmt_date($a['created_at'])) ?></small></th>
                <th>Wird übernommen: Akte <?= str_pad((string) $b['id'], 6, '0', STR_PAD_LEFT) ?><br><small class="muted"><?= $count((int) $b['id']) ?> Einträge/Dokumente/Fälle/Anhänge · angelegt <?= e(fmt_date($b['created_at'])) ?></small></th></tr></thead>
            <tbody>
            <?php foreach (MERGE_FIELDS as $f => $label): $va = (string) $a[$f]; $vb = (string) $b[$f]; $same = $va === $vb; ?>
                <tr class="<?= $same ? 'muted' : '' ?>"><th><?= e($label) ?></th>
                    <td><label class="choice"><input type="radio" name="pick[<?= e($f) ?>]" value="target"<?= checked($va !== '' || $vb === '') ?><?= $same ? ' disabled' : '' ?>> <?= e($va !== '' ? ($f === 'dob' ? fmt_date($va) : $va) : '–') ?></label></td>
                    <td><label class="choice"><input type="radio" name="pick[<?= e($f) ?>]" value="source"<?= checked($va === '' && $vb !== '') ?><?= $same ? ' disabled' : '' ?>> <?= e($vb !== '' ? ($f === 'dob' ? fmt_date($vb) : $vb) : '–') ?></label></td></tr>
            <?php endforeach; ?>
            <?php foreach (MERGE_COMBINE as $f => $label): if (trim((string) $a[$f] . $b[$f]) === '') continue; ?>
                <tr><th><?= e($label) ?></th><td colspan="2" class="small"><?= nl(trim((string) $a[$f]) ?: '–') ?><?php if (trim((string) $b[$f]) !== ''): ?><br><span class="badge blue">+ wird ergänzt</span> <?= nl($b[$f]) ?><?php endif; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="hint">Alle Einträge, Fälle, Dokumente, Anhänge, Termine und Bürgerportal-Verknüpfungen wandern in die verbleibende Akte. Bereits ausgestellte Dokumente bleiben unverändert (eingefrorene Angaben).</p>
        <div class="form-actions">
            <button class="btn primary" onclick="return confirm('Akten jetzt zusammenführen? Das lässt sich nicht automatisch rückgängig machen.')">Zusammenführen</button>
            <a class="btn" href="<?= e(url('patient_merge', ['target' => $b['id'], 'source' => $a['id']])) ?>">Seiten tauschen</a>
            <a class="btn" href="<?= e(url('patient_merge')) ?>">Abbrechen</a>
        </div>
    </form>
<?php else: $pairs = find_duplicate_patients(); ?>
    <div class="card">
        <h2>Mögliche Doppelungen (<?= count($pairs) ?>)</h2>
        <table class="list">
            <thead><tr><th>Akte A</th><th>Akte B</th><th>Hinweis</th><th></th></tr></thead>
            <tbody>
            <?php foreach (array_slice($pairs, 0, 100) as $p): ?>
                <tr><td><a href="<?= e(url('patient', ['id' => $p['a']['id']])) ?>"><?= e(patient_name($p['a'], true)) ?></a><br><small class="muted"><?= str_pad((string) $p['a']['id'], 6, '0', STR_PAD_LEFT) ?> · <?= e(fmt_date($p['a']['dob']) ?: 'ohne Geburtsdatum') ?><?= $p['a']['citizen_id'] ? ' · ' . e($p['a']['citizen_id']) : '' ?></small></td>
                    <td><a href="<?= e(url('patient', ['id' => $p['b']['id']])) ?>"><?= e(patient_name($p['b'], true)) ?></a><br><small class="muted"><?= str_pad((string) $p['b']['id'], 6, '0', STR_PAD_LEFT) ?> · <?= e(fmt_date($p['b']['dob']) ?: 'ohne Geburtsdatum') ?><?= $p['b']['citizen_id'] ? ' · ' . e($p['b']['citizen_id']) : '' ?></small></td>
                    <td><span class="badge <?= $p['reason'] === 'Gleiche Bürger-ID' ? 'red' : ($p['reason'] === 'Gleicher Name' ? 'grey' : 'orange') ?>"><?= e($p['reason']) ?></span></td>
                    <td class="right"><a class="btn small" href="<?= e(url('patient_merge', ['target' => $p['a']['id'], 'source' => $p['b']['id']])) ?>">Prüfen</a></td></tr>
            <?php endforeach; ?>
            <?php if (!$pairs): ?><tr><td colspan="4" class="muted">Keine Doppelungen gefunden.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <form class="card filter" action="index.php">
        <input type="hidden" name="p" value="patient_merge">
        <input type="number" name="target" placeholder="Akte, die bleibt (Nr.)" required min="1" value="<?= $target ?: '' ?>">
        <input type="number" name="source" placeholder="Doppelte Akte (Nr.)" required min="1">
        <button class="btn">Manuell zusammenführen</button>
    </form>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Akten zusammenführen';
require SAMS_ROOT . '/app/layout.php';
