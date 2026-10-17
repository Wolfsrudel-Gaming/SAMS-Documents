<?php
defined('SAMS_ROOT') || exit;
/** Fallakte anlegen oder bearbeiten (Einsatz, Behandlung, Aufenthalt …). */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$case = $id ? fone('cases', $id) : null;
if ($id && !$case) {
    not_found('Fall nicht gefunden.');
}
$patientId = $case ? (int) $case['patient_id'] : (int) ($_GET['patient'] ?? 0);
$patient = fone('patients', (int) $patientId);
if (!$patient) {
    not_found('Patientenakte nicht gefunden.');
}
require_active_patient($patient);
$departments = all('SELECT * FROM departments WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name');

$data = $case ?? ['title' => '', 'kind' => 'notfall', 'department_id' => null, 'incident_at' => now(), 'location' => '', 'incident' => '', 'diagnosis' => '', 'summary' => ''];
$errors = [];

if (is_post()) {
    csrf_check();
    $data['title'] = mb_substr(trim((string) post('title')), 0, 190);
    $data['kind'] = array_key_exists((string) post('kind'), CASE_KINDS) ? (string) post('kind') : 'sonstiges';
    $dep = (int) post('department_id');
    $data['department_id'] = $dep && in_array($dep, array_map('intval', array_column($departments, 'id')), true) ? $dep : null;
    $when = (string) post('incident_at');
    $data['incident_at'] = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $when) ? str_replace('T', ' ', $when) . ':00' : null;
    foreach (['location' => 190, 'incident' => 20000, 'diagnosis' => 5000, 'summary' => 20000] as $k => $max) {
        $data[$k] = mb_substr(trim((string) post($k)), 0, $max);
    }
    if ($data['title'] === '') {
        $errors[] = 'Bitte einen Titel angeben (z. B. „Verkehrsunfall Route 68“).';
    }
    if (!$errors) {
        $row = array_intersect_key($data, array_flip(['title', 'kind', 'department_id', 'incident_at', 'location', 'incident', 'diagnosis', 'summary']));
        $row['updated_at'] = now();
        if ($case) {
            update('cases', $id, $row);
            audit('case.update', 'case', $id, $case['number'] . ' ' . $row['title']);
        } else {
            $row += ['patient_id' => $patientId, 'number' => next_case_number(), 'status' => 'open', 'created_by' => $me['id'], 'created_at' => now()];
            $id = insert('cases', $row);
            audit('case.create', 'case', $id, $row['number'] . ' ' . $row['title']);
        }
        q('UPDATE patients SET updated_at = ? WHERE id = ?', [now(), $patientId]);
        flash('success', 'Fall gespeichert.');
        redirect(url('case', ['id' => $id]));
    }
}

ob_start(); ?>
<div class="page-head">
    <h1><?= $case ? 'Fall bearbeiten' : 'Neuer Fall' ?> <small class="muted">· <?= e(patient_name($patient)) ?></small></h1>
    <a class="btn" href="<?= e($case ? url('case', ['id' => $id]) : url('patient', ['id' => $patientId])) ?>">Zurück</a>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <div class="field w-full"><label>Titel <span class="req">*</span></label><input name="title" value="<?= e($data['title']) ?>" required placeholder="z. B. Verkehrsunfall Route 68, Fahrer PKW"></div>
    <div class="field w-third"><label>Art</label>
        <select name="kind"><?php foreach (CASE_KINDS as $k => $l): ?><option value="<?= e($k) ?>"<?= selected($k, $data['kind']) ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field w-third"><label>Zeitpunkt</label><input type="datetime-local" name="incident_at" value="<?= e($data['incident_at'] ? str_replace(' ', 'T', substr((string) $data['incident_at'], 0, 16)) : '') ?>"></div>
    <div class="field w-third"><label>Federführende Abteilung</label>
        <select name="department_id"><option value="">– keine –</option>
            <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>"<?= selected($d['id'], $data['department_id'] ?? '') ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field w-full"><label>Einsatz-/Behandlungsort</label><input name="location" value="<?= e($data['location']) ?>" placeholder="z. B. Route 68 / Höhe Harmony, oder Pillbox Hill Medical Center"></div>
    <div class="field w-full"><label>Hergang / Anlass</label><textarea name="incident" rows="4" placeholder="Was ist passiert? Wie wurde der Patient vorgefunden?"><?= e($data['incident']) ?></textarea></div>
    <div class="field w-full"><label>Diagnose(n)</label><textarea name="diagnosis" rows="3" placeholder="Eine Diagnose pro Zeile, gern mit ICD-10 (z. B. S52.5 Distale Radiusfraktur)"><?= e($data['diagnosis']) ?></textarea>
        <div class="hint">Wird beim Ausstellen von Dokumenten aus diesem Fall automatisch übernommen.</div></div>
    <div class="field w-full"><label>Zusammenfassung / Epikrise</label><textarea name="summary" rows="5" placeholder="Verlauf, Therapie, Entlassung, Empfehlungen"><?= e($data['summary']) ?></textarea></div>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<?php
$content = ob_get_clean();
$title = $case ? 'Fall bearbeiten' : 'Neuer Fall';
require SAMS_ROOT . '/app/layout.php';
