<?php
defined('SAMS_ROOT') || exit;
/** Einstellungen je Dokumentart: Freigabepflicht (Gegenzeichnung) und Standardmodus des Formulars. */
$ranks = all('SELECT * FROM ranks WHERE ' . fw() . ' ORDER BY level DESC');
$levels = [0 => 'keine Freigabe nötig'];
foreach ($ranks as $r) {
    $levels[(int) $r['level']] = 'unterhalb ' . $r['name'] . ' (Stufe ' . (int) $r['level'] . ')';
}
$byLevels = [];
foreach ($ranks as $r) {
    $byLevels[(int) $r['level']] = 'ab ' . $r['name'] . ' (Stufe ' . (int) $r['level'] . ')';
}

if (is_post()) {
    csrf_check();
    $config = [];
    foreach (document_types() as $key => $t) {
        $in = is_array($_POST['cfg'][$key] ?? null) ? $_POST['cfg'][$key] : [];
        $below = (int) ($in['approval_below'] ?? 0);
        $by = (int) ($in['approval_by'] ?? 70);
        $config[$key] = [
            'approval_below' => isset($levels[$below]) ? $below : 0,
            'approval_by' => isset($byLevels[$by]) ? $by : 70,
            'mode' => ($in['mode'] ?? '') === 'quick' ? 'quick' : 'full',
        ];
        if ($config[$key]['approval_below'] > $config[$key]['approval_by']) {
            // Wer freigibt, darf selbst nicht freigabepflichtig sein
            $config[$key]['approval_by'] = $config[$key]['approval_below'];
        }
    }
    save_setting('doc_type_config', json_encode($config));
    audit('settings.doc_types', '', null, 'Freigaben/Modus der Dokumentarten');
    flash('success', 'Einstellungen der Dokumentarten gespeichert.');
    redirect(url('doc_types'));
}

ob_start(); ?>
<div class="page-head">
    <div><h1>Dokumentarten</h1><p class="muted">Freigaben (Gegenzeichnung) und Formularmodus je Dokumentart.</p></div>
</div>
<form method="post" class="card">
    <?= csrf_field() ?>
    <table class="list">
        <thead><tr><th>Dokumentart</th><th>Freigabe nötig für Ränge …</th><th>Freigabe durch</th><th>Formular</th></tr></thead>
        <tbody>
        <?php foreach (document_types() as $key => $t): $c = doc_type_config($key); ?>
            <tr>
                <td><span class="badge blue"><?= e($t['code']) ?></span> <strong><?= e($t['title']) ?></strong><br><small class="muted"><?= e($t['category']) ?></small></td>
                <td><select name="cfg[<?= e($key) ?>][approval_below]"><?php foreach ($levels as $lv => $l): ?><option value="<?= $lv ?>"<?= selected($lv, $c['approval_below']) ?>><?= e($l) ?></option><?php endforeach; ?></select></td>
                <td><select name="cfg[<?= e($key) ?>][approval_by]"><?php foreach ($byLevels as $lv => $l): ?><option value="<?= $lv ?>"<?= selected($lv, $c['approval_by']) ?>><?= e($l) ?></option><?php endforeach; ?></select></td>
                <td><select name="cfg[<?= e($key) ?>][mode]"><option value="full"<?= selected('full', $c['mode']) ?>>Vollständig</option><option value="quick"<?= selected('quick', $c['mode']) ?>>Schnell (nur Pflichtfelder)</option></select></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="hint">Beispiel: Auszubildende brauchen für Einsatzprotokolle eine Gegenzeichnung → „unterhalb Rettungssanitäter“ und „ab Notfallsanitäter“. Freigeben darf, wer zusätzlich das Recht „Dokumente freigeben“ hat. Den Modus können Mitarbeitende im Formular jederzeit umschalten.</p>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Dokumentarten';
require SAMS_ROOT . '/app/layout.php';
