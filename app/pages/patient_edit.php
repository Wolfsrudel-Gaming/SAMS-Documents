<?php
defined('SAMS_ROOT') || exit;
$id = (int) ($_GET['id'] ?? 0);
$patient = $id ? one('SELECT * FROM patients WHERE id = ?', [$id]) : null;
if ($id && !$patient) {
    not_found('Patientenakte nicht gefunden.');
}

$fields = [
    'first_name', 'last_name', 'dob', 'gender', 'citizen_id', 'phone', 'address', 'occupation', 'employer',
    'insurance', 'insurance_number', 'blood_type', 'height', 'weight', 'allergies', 'conditions', 'medication',
    'emergency_contact', 'organ_donor', 'flags', 'notes', 'deceased_at',
];
$data = $patient ?? array_fill_keys($fields, '') + ['living_will' => 0];
$errors = [];

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete') {
        require_role('admin');
        q('DELETE FROM patients WHERE id = ?', [$id]);
        audit('patient.delete', 'patient', $id, patient_name($patient));
        flash('success', 'Patientenakte gelöscht.');
        redirect(url('patients'));
    }
    foreach ($fields as $f) {
        $data[$f] = mb_substr((string) post($f), 0, 5000);
    }
    $data['living_will'] = post('living_will') ? 1 : 0;
    if ($data['first_name'] === '' || $data['last_name'] === '') {
        $errors[] = 'Vor- und Nachname sind Pflichtfelder.';
    }
    if ($data['dob'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['dob'])) {
        $errors[] = 'Ungültiges Geburtsdatum.';
    }
    if (!$errors) {
        $row = array_intersect_key($data, array_flip(array_merge($fields, ['living_will'])));
        $row['updated_at'] = now();
        if ($patient) {
            update('patients', $id, $row);
            audit('patient.update', 'patient', $id, patient_name($row));
            flash('success', 'Akte gespeichert.');
        } else {
            $row['created_by'] = current_user()['id'];
            $row['created_at'] = now();
            $id = insert('patients', $row);
            audit('patient.create', 'patient', $id, patient_name($row));
            flash('success', 'Patientenakte angelegt.');
        }
        redirect(url('patient', ['id' => $id]));
    }
}

$val = fn($k) => e($data[$k] ?? '');
ob_start(); ?>
<div class="page-head">
    <h1><?= $patient ? 'Akte bearbeiten: ' . e(patient_name($patient)) : 'Neue Patientenakte' ?></h1>
    <?php if ($patient): ?><a class="btn" href="<?= e(url('patient', ['id' => $id])) ?>">Zurück zur Akte</a><?php endif; ?>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <h3 class="form-heading">Personalien</h3>
    <div class="field w-third"><label>Nachname <span class="req">*</span></label><input name="last_name" value="<?= $val('last_name') ?>" required></div>
    <div class="field w-third"><label>Vorname <span class="req">*</span></label><input name="first_name" value="<?= $val('first_name') ?>" required></div>
    <div class="field w-sixth"><label>Geburtsdatum</label><input type="date" name="dob" value="<?= $val('dob') ?>"></div>
    <div class="field w-sixth"><label>Geschlecht</label>
        <select name="gender"><?php foreach (['' => '–', 'm' => 'männlich', 'w' => 'weiblich', 'd' => 'divers'] as $k => $v): ?><option value="<?= e($k) ?>"<?= selected($k, $data['gender']) ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
    <div class="field w-third"><label>Bürger-ID / State-ID</label><input name="citizen_id" value="<?= $val('citizen_id') ?>" placeholder="z.B. citizenid aus dem Framework"></div>
    <div class="field w-third"><label>Telefon</label><input name="phone" value="<?= $val('phone') ?>"></div>
    <div class="field w-third"><label>Beruf</label><input name="occupation" value="<?= $val('occupation') ?>"></div>
    <div class="field w-half"><label>Anschrift</label><textarea name="address" rows="2"><?= $val('address') ?></textarea></div>
    <div class="field w-half"><label>Arbeitgeber</label><textarea name="employer" rows="2"><?= $val('employer') ?></textarea></div>

    <h3 class="form-heading">Versicherung</h3>
    <div class="field w-half"><label>Krankenversicherung</label><input name="insurance" value="<?= $val('insurance') ?>" list="dl-insurance" placeholder="z.B. San Andreas Health Insurance"></div>
    <div class="field w-half"><label>Versichertennummer</label><input name="insurance_number" value="<?= $val('insurance_number') ?>"></div>
    <datalist id="dl-insurance"><option value="San Andreas Health Insurance (SAHI)"><option value="Los Santos Krankenkasse (LSKK)"><option value="Blaine County Health"><option value="Privat versichert"><option value="Nicht versichert / Selbstzahler"></datalist>

    <h3 class="form-heading">Medizinische Stammdaten</h3>
    <div class="field w-sixth"><label>Blutgruppe</label>
        <select name="blood_type"><?php foreach (['', 'A Rh+', 'A Rh-', 'B Rh+', 'B Rh-', 'AB Rh+', 'AB Rh-', '0 Rh+', '0 Rh-'] as $bt): ?><option<?= selected($bt, $data['blood_type']) ?>><?= e($bt) ?></option><?php endforeach; ?></select></div>
    <div class="field w-sixth"><label>Größe (cm)</label><input name="height" value="<?= $val('height') ?>"></div>
    <div class="field w-sixth"><label>Gewicht (kg)</label><input name="weight" value="<?= $val('weight') ?>"></div>
    <div class="field w-sixth"><label>Organspender</label>
        <select name="organ_donor"><?php foreach (['' => 'unbekannt', 'ja' => 'Ja', 'nein' => 'Nein', 'eingeschränkt' => 'Eingeschränkt'] as $k => $v): ?><option value="<?= e($k) ?>"<?= selected($k, $data['organ_donor']) ?>><?= e($v) ?></option><?php endforeach; ?></select></div>
    <div class="field w-third"><label class="choice single" style="margin-top:1.6rem"><input type="checkbox" name="living_will" value="1"<?= checked(!empty($data['living_will'])) ?>> Patientenverfügung liegt vor</label></div>
    <div class="field w-half"><label>Allergien / Unverträglichkeiten</label><textarea name="allergies" rows="2"><?= $val('allergies') ?></textarea></div>
    <div class="field w-half"><label>Vorerkrankungen</label><textarea name="conditions" rows="2"><?= $val('conditions') ?></textarea></div>
    <div class="field w-half"><label>Dauermedikation</label><textarea name="medication" rows="2"><?= $val('medication') ?></textarea></div>
    <div class="field w-half"><label>Notfallkontakt</label><textarea name="emergency_contact" rows="2"><?= $val('emergency_contact') ?></textarea></div>

    <h3 class="form-heading">Interne Hinweise</h3>
    <div class="field w-half"><label>Kennzeichnungen (kommagetrennt)</label><input name="flags" value="<?= $val('flags') ?>" placeholder="z.B. Aggressiv, Hausverbot, Begleitung LSPD"></div>
    <div class="field w-half"><label>Verstorben am</label><input type="date" name="deceased_at" value="<?= $val('deceased_at') ?>"></div>
    <div class="field w-full"><label>Notizen</label><textarea name="notes" rows="3"><?= $val('notes') ?></textarea></div>

    <div class="form-actions">
        <button class="btn primary">Speichern</button>
        <?php if ($patient && can('admin')): ?>
            <button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Akte inkl. aller Einträge endgültig löschen? Ausgestellte Dokumente bleiben erhalten.')">Akte löschen</button>
        <?php endif; ?>
    </div>
</form>
<?php
$content = ob_get_clean();
$title = $patient ? 'Akte bearbeiten' : 'Neue Akte';
require SAMS_ROOT . '/app/layout.php';
