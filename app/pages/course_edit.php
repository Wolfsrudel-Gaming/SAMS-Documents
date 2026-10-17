<?php
defined('SAMS_ROOT') || exit;
/** Lehrgang bearbeiten: Einstellungen, Lernmodule, Prüfungsfragen, Ergebnisse. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$course = $id ? fone('training_courses', $id) : null;
if ($id && !$course) {
    not_found('Lehrgang nicht gefunden.');
}
$quals = all('SELECT * FROM qualifications WHERE ' . fw() . ' ORDER BY name');
$signers = array_values(array_filter(all('SELECT * FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name'), fn($u) => user_level($u) >= 50 || !empty($u['is_superadmin'])));
$errors = [];

/** Antworten aus der Textarea: eine pro Zeile, richtige beginnen mit * */
$parseOptions = function (string $raw): array {
    $opts = [];
    $correct = [];
    foreach (preg_split('/\r?\n/', $raw) as $line) {
        $line = trim($line);
        if ($line === '') {
            continue;
        }
        if (str_starts_with($line, '*')) {
            $correct[] = count($opts);
            $line = trim(substr($line, 1));
        }
        $opts[] = mb_substr($line, 0, 300);
    }
    return [$opts, $correct];
};

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'course') {
        $row = [
            'title' => mb_substr(trim((string) post('title')), 0, 190),
            'description' => mb_substr((string) post('description'), 0, 5000),
            'qualification_id' => (int) post('qualification_id') ?: null,
            'pass_percent' => max(1, min(100, (int) post('pass_percent') ?: 80)),
            'question_count' => max(0, (int) post('question_count')),
            'time_limit' => max(0, (int) post('time_limit')),
            'attempts_per_day' => max(0, (int) post('attempts_per_day')),
            'require_modules' => post('require_modules') ? 1 : 0,
            'certificate' => post('certificate') ? 1 : 0,
            'valid_months' => max(0, (int) post('valid_months')),
            'hours' => max(0, (int) post('hours')),
            'signer_id' => (int) post('signer_id') ?: null,
            'active' => post('active') ? 1 : 0,
            'updated_at' => now(),
        ];
        if ($row['title'] === '') {
            $errors[] = 'Bitte einen Titel angeben.';
        } else {
            if ($course) {
                update('training_courses', $id, $row);
                audit('training.update', 'course', $id, $row['title']);
            } else {
                $id = insert('training_courses', $row + ['created_by' => $me['id'], 'created_at' => now(), 'signer_id' => $row['signer_id'] ?: $me['id']]);
                audit('training.create', 'course', $id, $row['title']);
            }
            flash('success', 'Lehrgang gespeichert.');
            redirect(url('course_edit', ['id' => $id]));
        }
    }
    if (!$course) {
        not_found();
    }
    if ($action === 'module') {
        $mid = (int) post('module_id');
        $row = ['title' => mb_substr(trim((string) post('m_title')), 0, 190), 'content' => mb_substr((string) post('m_content'), 0, 30000), 'sort' => (int) post('m_sort')];
        if ($row['title'] !== '') {
            $mid ? q('UPDATE training_modules SET title = ?, content = ?, sort = ? WHERE id = ? AND course_id = ?', [$row['title'], $row['content'], $row['sort'], $mid, $id])
                : insert('training_modules', $row + ['course_id' => $id]);
            flash('success', 'Modul gespeichert.');
        }
    } elseif ($action === 'module_delete') {
        q('DELETE FROM training_progress WHERE module_id IN (SELECT id FROM training_modules WHERE id = ? AND course_id = ?)', [(int) post('module_id'), $id]);
        q('DELETE FROM training_modules WHERE id = ? AND course_id = ?', [(int) post('module_id'), $id]);
        flash('success', 'Modul gelöscht.');
    } elseif ($action === 'question') {
        $qid = (int) post('question_id');
        [$opts, $correct] = $parseOptions((string) post('q_options'));
        $text = mb_substr(trim((string) post('q_text')), 0, 2000);
        if ($text === '' || count($opts) < 2 || !$correct) {
            flash('error', 'Eine Frage braucht einen Text, mindestens zwei Antworten und mindestens eine richtige (mit * markieren).');
        } else {
            $row = ['question' => $text, 'options' => json_encode($opts, JSON_UNESCAPED_UNICODE), 'correct' => json_encode($correct), 'explanation' => mb_substr(trim((string) post('q_explanation')), 0, 2000), 'sort' => (int) post('q_sort')];
            $qid ? q('UPDATE training_questions SET question = ?, options = ?, correct = ?, explanation = ?, sort = ? WHERE id = ? AND course_id = ?', [...array_values($row), $qid, $id])
                : insert('training_questions', $row + ['course_id' => $id]);
            flash('success', 'Frage gespeichert.');
        }
    } elseif ($action === 'question_delete') {
        q('DELETE FROM training_questions WHERE id = ? AND course_id = ?', [(int) post('question_id'), $id]);
        flash('success', 'Frage gelöscht.');
    }
    redirect(url('course_edit', ['id' => $id]) . '#' . (str_starts_with((string) $action, 'question') ? 'questions' : 'modules'));
}

$form = $course ?? ['title' => '', 'description' => '', 'qualification_id' => null, 'pass_percent' => 80, 'question_count' => 10, 'time_limit' => 15,
    'attempts_per_day' => 3, 'require_modules' => 1, 'certificate' => 1, 'valid_months' => 0, 'hours' => 0, 'signer_id' => $me['id'], 'active' => 1];
if ($errors) {
    $form = array_merge($form, $_POST);
}
$modules = $course ? course_modules($id) : [];
$questions = $course ? course_questions($id) : [];
$results = $course ? all('SELECT a.*, u.name FROM training_attempts a JOIN users u ON u.id = a.user_id WHERE a.course_id = ? AND a.finished_at IS NOT NULL ORDER BY a.finished_at DESC LIMIT 50', [$id]) : [];
$optText = fn(array $q) => implode("\n", array_map(fn($o, $i) => (in_array($i, $q['correct'], true) ? '* ' : '') . $o, $q['options'], array_keys($q['options'])));

ob_start(); ?>
<div class="page-head">
    <div><h1><?= $course ? e($course['title']) : 'Neuer Lehrgang' ?></h1><p class="muted">Lehrgang verwalten</p></div>
    <div class="btn-row"><?php if ($course): ?><a class="btn" href="<?= e(url('course', ['id' => $id])) ?>">Ansehen</a><?php endif; ?><a class="btn" href="<?= e(url('training')) ?>">Alle Lehrgänge</a></div>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid">
    <h2>Einstellungen</h2>
    <?= csrf_field() ?><input type="hidden" name="action" value="course">
    <div class="field w-full"><label>Titel <span class="req">*</span></label><input name="title" value="<?= e($form['title']) ?>" required placeholder="z. B. Advanced Cardiac Life Support (ACLS)"></div>
    <div class="field w-full"><label>Beschreibung</label><textarea name="description" rows="3"><?= e($form['description']) ?></textarea></div>
    <div class="field w-third"><label>Qualifikation bei Bestehen</label><select name="qualification_id"><option value="">– keine –</option><?php foreach ($quals as $q): ?><option value="<?= (int) $q['id'] ?>"<?= selected($q['id'], $form['qualification_id']) ?>><?= e($q['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field w-third"><label>Bestanden ab (%)</label><input type="number" name="pass_percent" value="<?= (int) $form['pass_percent'] ?>" min="1" max="100"></div>
    <div class="field w-third"><label>Fragen pro Prüfung (0 = alle)</label><input type="number" name="question_count" value="<?= (int) $form['question_count'] ?>" min="0"></div>
    <div class="field w-third"><label>Zeitlimit (Minuten, 0 = keins)</label><input type="number" name="time_limit" value="<?= (int) $form['time_limit'] ?>" min="0"></div>
    <div class="field w-third"><label>Versuche pro Tag (0 = unbegrenzt)</label><input type="number" name="attempts_per_day" value="<?= (int) $form['attempts_per_day'] ?>" min="0"></div>
    <div class="field w-third"><label>Unterrichtseinheiten (Zertifikat)</label><input type="number" name="hours" value="<?= (int) $form['hours'] ?>" min="0"></div>
    <div class="field w-third"><label>Gültigkeit (Monate, 0 = unbegrenzt)</label><input type="number" name="valid_months" value="<?= (int) $form['valid_months'] ?>" min="0"></div>
    <div class="field w-third"><label>Zertifikat unterschreibt</label><select name="signer_id"><?php foreach ($signers as $s): ?><option value="<?= (int) $s['id'] ?>"<?= selected($s['id'], $form['signer_id']) ?>><?= e(user_display($s)) ?></option><?php endforeach; ?></select></div>
    <div class="field w-third">
        <label class="choice single"><input type="checkbox" name="require_modules" value="1"<?= checked($form['require_modules']) ?>> Prüfung erst nach allen Modulen</label>
        <label class="choice single"><input type="checkbox" name="certificate" value="1"<?= checked($form['certificate']) ?>> Zertifikat automatisch ausstellen</label>
        <label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($form['active']) ?>> aktiv</label>
    </div>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>

<?php if ($course): ?>
<section class="card" id="modules">
    <h2>Lernmodule (<?= count($modules) ?>)</h2>
    <?php foreach ($modules as $m): ?>
        <details class="edit-block"><summary><strong><?= e($m['title']) ?></strong> <small class="muted">Sortierung <?= (int) $m['sort'] ?></small></summary>
            <form method="post" class="form-grid">
                <?= csrf_field() ?><input type="hidden" name="module_id" value="<?= (int) $m['id'] ?>">
                <div class="field w-two-thirds"><label>Titel</label><input name="m_title" value="<?= e($m['title']) ?>"></div>
                <div class="field w-third"><label>Sortierung</label><input type="number" name="m_sort" value="<?= (int) $m['sort'] ?>"></div>
                <div class="field w-full"><label>Inhalt</label><textarea name="m_content" rows="10"><?= e($m['content']) ?></textarea></div>
                <div class="form-actions"><button class="btn small primary" name="action" value="module">Speichern</button><button class="btn small danger" name="action" value="module_delete" onclick="return confirm('Modul löschen?')">Löschen</button></div>
            </form>
        </details>
    <?php endforeach; ?>
    <details class="edit-block"<?= $modules ? '' : ' open' ?>><summary>+ Neues Modul</summary>
        <form method="post" class="form-grid">
            <?= csrf_field() ?><input type="hidden" name="module_id" value="0">
            <div class="field w-two-thirds"><label>Titel</label><input name="m_title"></div>
            <div class="field w-third"><label>Sortierung</label><input type="number" name="m_sort" value="<?= (count($modules) + 1) * 10 ?>"></div>
            <div class="field w-full"><label>Inhalt</label><textarea name="m_content" rows="8" placeholder="# Überschrift&#10;Text mit **fett** und *kursiv*&#10;- Aufzählung&#10;1. Schritt"></textarea>
                <div class="hint">Formatierung: <code># Überschrift</code>, <code>**fett**</code>, <code>*kursiv*</code>, <code>- Liste</code>, <code>1. Liste</code>, <code>[Link](https://…)</code></div></div>
            <div class="form-actions"><button class="btn small primary" name="action" value="module">Modul anlegen</button></div>
        </form>
    </details>
</section>

<section class="card" id="questions">
    <h2>Prüfungsfragen (<?= count($questions) ?>)</h2>
    <?php foreach ($questions as $q): ?>
        <details class="edit-block"><summary><?= e(mb_strimwidth($q['question'], 0, 110, '…')) ?> <small class="muted"><?= count($q['options']) ?> Antworten, <?= count($q['correct']) ?> richtig</small></summary>
            <form method="post" class="form-grid">
                <?= csrf_field() ?><input type="hidden" name="question_id" value="<?= (int) $q['id'] ?>">
                <div class="field w-two-thirds"><label>Frage</label><input name="q_text" value="<?= e($q['question']) ?>"></div>
                <div class="field w-third"><label>Sortierung</label><input type="number" name="q_sort" value="<?= (int) $q['sort'] ?>"></div>
                <div class="field w-half"><label>Antworten (richtige mit * am Anfang)</label><textarea name="q_options" rows="5" class="mono"><?= e($optText($q)) ?></textarea></div>
                <div class="field w-half"><label>Erklärung (nach der Abgabe sichtbar)</label><textarea name="q_explanation" rows="5"><?= e($q['explanation']) ?></textarea></div>
                <div class="form-actions"><button class="btn small primary" name="action" value="question">Speichern</button><button class="btn small danger" name="action" value="question_delete" onclick="return confirm('Frage löschen?')">Löschen</button></div>
            </form>
        </details>
    <?php endforeach; ?>
    <details class="edit-block"<?= $questions ? '' : ' open' ?>><summary>+ Neue Frage</summary>
        <form method="post" class="form-grid">
            <?= csrf_field() ?><input type="hidden" name="question_id" value="0">
            <div class="field w-two-thirds"><label>Frage</label><input name="q_text"></div>
            <div class="field w-third"><label>Sortierung</label><input type="number" name="q_sort" value="<?= (count($questions) + 1) * 10 ?>"></div>
            <div class="field w-half"><label>Antworten (richtige mit * am Anfang)</label><textarea name="q_options" rows="5" class="mono" placeholder="15:2&#10;* 30:2&#10;30:1&#10;5:1"></textarea></div>
            <div class="field w-half"><label>Erklärung</label><textarea name="q_explanation" rows="5"></textarea></div>
            <div class="form-actions"><button class="btn small primary" name="action" value="question">Frage anlegen</button></div>
        </form>
    </details>
</section>

<section class="card">
    <h2>Ergebnisse</h2>
    <table class="list compact">
        <thead><tr><th>Mitarbeiter/in</th><th>Abgegeben</th><th class="right">Ergebnis</th></tr></thead>
        <tbody>
        <?php foreach ($results as $r): ?>
            <tr onclick="location.href='<?= e(url('exam', ['id' => $r['id']])) ?>'"><td><?= e($r['name']) ?></td><td class="small"><?= e(fmt_datetime($r['finished_at'])) ?></td>
                <td class="right"><?= (int) $r['score'] ?> % <?= $r['passed'] ? '<span class="badge green">bestanden</span>' : '<span class="badge red">nicht bestanden</span>' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$results): ?><tr><td colspan="3" class="muted">Noch keine Prüfungen abgelegt.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Lehrgang bearbeiten';
require SAMS_ROOT . '/app/layout.php';
