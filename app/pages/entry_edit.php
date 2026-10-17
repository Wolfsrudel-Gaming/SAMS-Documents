<?php
defined('SAMS_ROOT') || exit;
const ENTRY_TYPES = ['Behandlung', 'Notfalleinsatz', 'Untersuchung', 'Verlaufsnotiz', 'Psychologisches Gespräch', 'Labor', 'Bildgebung', 'Operation', 'Medikation', 'Telefonat', 'Sonstiges'];

$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$entry = $id ? fone('entries', $id) : null;
if ($id && !$entry) {
    not_found('Eintrag nicht gefunden.');
}
$patientId = $entry ? (int) $entry['patient_id'] : (int) ($_GET['patient'] ?? 0);
$patient = fone('patients', (int) $patientId);
if (!$patient) {
    not_found('Patientenakte nicht gefunden.');
}
require_active_patient($patient);
// Bearbeiten nur durch Verfasser oder ärztlichen Dienst – und nur, wenn der Eintrag lesbar ist
if ($entry && (int) $entry['created_by'] !== (int) $me['id'] && (!can('entries.edit_all') || !entry_visible($entry))) {
    forbidden('Nur der Verfasser oder Berechtigte dürfen diesen Eintrag bearbeiten.');
}

// Fälle der Akte und Abteilungen, auf die der Eintrag beschränkt werden kann
$cases = all('SELECT id, number, title, status FROM cases WHERE patient_id = ? ORDER BY id DESC', [$patientId]);
$caseIds = array_map('intval', array_column($cases, 'id'));
$departments = can('entries.all_departments')
    ? all('SELECT id, name FROM departments WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name')
    : array_values(array_filter(all('SELECT id, name FROM departments WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name'),
        fn($d) => in_array((int) $d['id'], user_department_ids((int) $me['id']), true)));
$depIds = array_map('intval', array_column($departments, 'id'));

$presetCase = (int) ($_GET['case'] ?? 0);
$data = $entry ?? ['type' => 'Behandlung', 'title' => '', 'content' => '', 'confidential' => 0, 'occurred_at' => date('Y-m-d H:i:s'),
    'case_id' => in_array($presetCase, $caseIds, true) ? $presetCase : null, 'department_id' => null];
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
    $caseId = (int) post('case_id');
    $data['case_id'] = in_array($caseId, $caseIds, true) ? $caseId : null;
    // Neue Zuordnungen nur zu offenen Fällen; eine bestehende Zuordnung bleibt erhalten
    $closed = array_map('intval', array_column(array_filter($cases, fn($c) => $c['status'] === 'closed'), 'id'));
    if ($data['case_id'] && in_array($data['case_id'], $closed, true) && (int) ($entry['case_id'] ?? 0) !== $data['case_id']) {
        $errors[] = 'Der gewählte Fall ist abgeschlossen. Bitte zuerst wieder öffnen.';
    }
    $depId = (int) post('department_id');
    // Eine fremde Abteilung beim Bearbeiten nicht stillschweigend entfernen
    $data['department_id'] = in_array($depId, $depIds, true) ? $depId
        : ($entry && $depId && $depId === (int) $entry['department_id'] ? $depId : null);
    $when = (string) post('occurred_at');
    $data['occurred_at'] = preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $when) ? str_replace('T', ' ', $when) . ':00' : now();
    if ($data['title'] === '') {
        $errors[] = 'Bitte einen Titel angeben.';
    }
    if (!$errors) {
        $row = array_intersect_key($data, array_flip(['type', 'title', 'content', 'confidential', 'occurred_at', 'case_id', 'department_id']));
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
        redirect($row['case_id'] ? url('case', ['id' => $row['case_id']]) : url('patient', ['id' => $patientId]));
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
    <div class="field w-half"><label>Fall</label>
        <select name="case_id"><option value="">– keinem Fall zuordnen –</option>
            <?php foreach ($cases as $c): ?><option value="<?= (int) $c['id'] ?>"<?= selected($c['id'], $data['case_id'] ?? '') ?>><?= e($c['number'] . ' · ' . $c['title']) ?><?= $c['status'] === 'closed' ? ' (abgeschlossen)' : '' ?></option><?php endforeach; ?>
        </select></div>
    <div class="field w-half"><label>Sichtbar für</label>
        <select name="department_id"><option value="">alle mit Aktenzugriff</option>
            <?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>"<?= selected($d['id'], $data['department_id'] ?? '') ?>>nur Abteilung <?= e($d['name']) ?></option><?php endforeach; ?>
            <?php if (!empty($data['department_id']) && !in_array((int) $data['department_id'], $depIds, true)): ?><option value="<?= (int) $data['department_id'] ?>" selected>andere Abteilung (unverändert)</option><?php endif; ?>
        </select>
        <div class="hint">Z. B. psychologische Gespräche nur für die Psychologie sichtbar machen.</div></div>
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
