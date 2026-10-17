<?php
defined('SAMS_ROOT') || exit;
const ENTRY_TYPES = ['Behandlung', 'Notfalleinsatz', 'Untersuchung', 'Verlaufsnotiz', 'Psychologisches Gespräch', 'Labor', 'Bildgebung', 'Operation', 'Medikation', 'Telefonat', 'Sonstiges'];

$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$entry = $id ? one('SELECT * FROM entries WHERE id = ?', [$id]) : null;
if ($id && !$entry) {
    not_found('Eintrag nicht gefunden.');
}
$patientId = $entry ? (int) $entry['patient_id'] : (int) ($_GET['patient'] ?? 0);
$patient = one('SELECT * FROM patients WHERE id = ?', [$patientId]);
if (!$patient) {
    not_found('Patientenakte nicht gefunden.');
}
// Bearbeiten nur durch Verfasser oder ärztlichen Dienst
if ($entry && (int) $entry['created_by'] !== (int) $me['id'] && !can('entries.edit_all')) {
    forbidden('Nur der Verfasser oder Berechtigte dürfen diesen Eintrag bearbeiten.');
}

$data = $entry ?? ['type' => 'Behandlung', 'title' => '', 'content' => '', 'confidential' => 0, 'occurred_at' => date('Y-m-d H:i:s')];
$errors = [];

if (is_post()) {
    csrf_check();
    if (post('action') === 'delete' && $entry) {
        q('DELETE FROM entries WHERE id = ?', [$id]);
        audit('entry.delete', 'patient', $patientId, $entry['title']);
        flash('success', 'Eintrag gelöscht.');
        redirect(url('patient', ['id' => $patientId]));
    }
    $data['type'] = in_array(post('type'), ENTRY_TYPES, true) ? post('type') : 'Sonstiges';
    $data['title'] = mb_substr((string) post('title'), 0, 190);
    $data['content'] = mb_substr((string) post('content'), 0, 50000);
    $data['confidential'] = post('confidential') ? 1 : 0;
    $when = (string) post('occurred_at');
    $data['occurred_at'] = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $when) ? str_replace('T', ' ', $when) . ':00' : now();
    if ($data['title'] === '') {
        $errors[] = 'Bitte einen Titel angeben.';
    }
    if (!$errors) {
        $row = array_intersect_key($data, array_flip(['type', 'title', 'content', 'confidential', 'occurred_at']));
        $row['updated_at'] = now();
        if ($entry) {
            update('entries', $id, $row);
            audit('entry.update', 'patient', $patientId, $row['title']);
        } else {
            $row += ['patient_id' => $patientId, 'created_by' => $me['id'], 'created_at' => now()];
            insert('entries', $row);
            audit('entry.create', 'patient', $patientId, $row['title']);
        }
        q('UPDATE patients SET updated_at = ? WHERE id = ?', [now(), $patientId]);
        flash('success', 'Eintrag gespeichert.');
        redirect(url('patient', ['id' => $patientId]));
    }
}

ob_start(); ?>
<div class="page-head">
    <h1><?= $entry ? 'Eintrag bearbeiten' : 'Neuer Akteneintrag' ?> <small class="muted">· <?= e(patient_name($patient)) ?></small></h1>
    <a class="btn" href="<?= e(url('patient', ['id' => $patientId])) ?>">Zurück zur Akte</a>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?>
    <div class="field w-third"><label>Art</label>
        <select name="type"><?php foreach (ENTRY_TYPES as $t): ?><option<?= selected($t, $data['type']) ?>><?= e($t) ?></option><?php endforeach; ?></select></div>
    <div class="field w-third"><label>Zeitpunkt</label><input type="datetime-local" name="occurred_at" value="<?= e(str_replace(' ', 'T', substr($data['occurred_at'], 0, 16))) ?>"></div>
    <div class="field w-third"><label class="choice single" style="margin-top:1.6rem"><input type="checkbox" name="confidential" value="1"<?= checked($data['confidential']) ?>> Vertraulich (nur ärztlicher Dienst)</label></div>
    <div class="field w-full"><label>Titel <span class="req">*</span></label><input name="title" value="<?= e($data['title']) ?>" required placeholder="z.B. Versorgung Schnittwunde li. Unterarm"></div>
    <div class="field w-full"><label>Inhalt</label>
        <textarea name="content" rows="14" placeholder="Anamnese:&#10;Befund:&#10;Diagnose:&#10;Therapie:&#10;Procedere:"><?= e($data['content']) ?></textarea>
        <div class="hint">Vorlage: <button type="button" class="btn small" data-insert="Anamnese:&#10;&#10;Befund:&#10;&#10;Diagnose:&#10;&#10;Therapie:&#10;&#10;Procedere:">SOAP</button>
            <button type="button" class="btn small" data-insert="A – Atemwege:&#10;B – Atmung:&#10;C – Kreislauf:&#10;D – Neurologie:&#10;E – Exposure / Umgebung:">ABCDE</button></div>
    </div>
    <div class="form-actions">
        <button class="btn primary">Speichern</button>
        <?php if ($entry): ?><button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Eintrag löschen?')">Löschen</button><?php endif; ?>
    </div>
</form>
<?php
$content = ob_get_clean();
$title = 'Akteneintrag';
require SAMS_ROOT . '/app/layout.php';
