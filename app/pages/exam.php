<?php
defined('SAMS_ROOT') || exit;
/** Prüfung ablegen bzw. Ergebnis ansehen. Bewertet wird nur auf dem Server. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$attempt = one('SELECT * FROM training_attempts WHERE id = ?', [$id]);
if (!$attempt || ((int) $attempt['user_id'] !== (int) $me['id'] && !can('training.manage'))) {
    not_found('Prüfung nicht gefunden.');
}
$course = one('SELECT * FROM training_courses WHERE id = ?', [$attempt['course_id']]);
$own = (int) $attempt['user_id'] === (int) $me['id'];
$all = array_column(course_questions((int) $course['id']), null, 'id');
$plan = array_values(array_filter(json_decode((string) $attempt['questions'], true) ?: [], fn($p) => isset($all[$p['id']])));

if (is_post() && $own && !$attempt['finished_at']) {
    csrf_check();
    $result = grade_attempt($attempt, $course, is_array($_POST['a'] ?? null) ? $_POST['a'] : []);
    $docId = null;
    update('training_attempts', $id, ['finished_at' => now(), 'answers' => json_encode($result['answers']), 'score' => $result['score'],
        'passed' => $result['passed'] ? 1 : 0, 'overtime' => $result['overtime'] ? 1 : 0]);
    if ($result['passed']) {
        $docId = complete_course($course, $me, $result['score']);
        if ($docId) {
            update('training_attempts', $id, ['document_id' => $docId]);
        }
    }
    audit('training.finish', 'course', (int) $course['id'], $course['title'] . ': ' . $result['score'] . ' % ' . ($result['passed'] ? 'bestanden' : 'nicht bestanden'));
    flash($result['passed'] ? 'success' : 'error', $result['passed'] ? 'Bestanden mit ' . $result['score'] . ' %! ' . ($docId ? 'Dein Zertifikat wurde ausgestellt.' : '')
        : ($result['overtime'] ? 'Die Zeit war abgelaufen – ' : 'Leider nicht bestanden – ') . $result['score'] . ' % (nötig: ' . (int) $course['pass_percent'] . ' %).');
    redirect(url('exam', ['id' => $id]));
}

$finished = (bool) $attempt['finished_at'];
$answers = json_decode((string) $attempt['answers'], true) ?: [];
$deadline = (int) $course['time_limit'] > 0 ? strtotime($attempt['started_at']) + (int) $course['time_limit'] * 60 : 0;
$examinee = $own ? $me : one('SELECT * FROM users WHERE id = ?', [$attempt['user_id']]);

ob_start(); ?>
<div class="page-head">
    <div><h1>Prüfung: <?= e($course['title']) ?></h1>
        <p class="muted"><?= e(user_display($examinee)) ?> · begonnen <?= e(fmt_datetime($attempt['started_at'])) ?> · <?= count($plan) ?> Fragen · bestanden ab <?= (int) $course['pass_percent'] ?> %</p></div>
    <?php if (!$finished && $deadline): ?><div class="exam-timer" data-remaining="<?= max(0, $deadline - time()) ?>">--:--</div><?php endif; ?>
</div>
<?php if ($finished): ?>
    <div class="result-banner <?= $attempt['passed'] ? 'ok' : 'bad' ?>">
        <strong><?= (int) $attempt['score'] ?> %</strong>
        <span><?= $attempt['passed'] ? 'Bestanden' : 'Nicht bestanden' ?><?= $attempt['overtime'] ? ' (Zeit überschritten)' : '' ?></span>
        <?php if ($attempt['document_id'] && ($own || can('documents.view'))): ?><a class="btn" href="<?= e(can('documents.view') ? url('document', ['id' => $attempt['document_id']]) : url('personnel')) ?>">Zertifikat ansehen</a><?php endif; ?>
        <a class="btn" href="<?= e(url('course', ['id' => $course['id']])) ?>">Zum Lehrgang</a>
    </div>
<?php endif; ?>
<form method="post" class="exam" id="exam-form">
    <?= csrf_field() ?>
    <?php foreach ($plan as $n => $p): $q = $all[$p['id']]; $multi = count($q['correct']) > 1; $chosen = array_map('intval', $answers[$p['id']] ?? []);
        $right = $finished && (function () use ($chosen, $q) { $a = $chosen; $b = $q['correct']; sort($a); sort($b); return $a === $b; })(); ?>
        <fieldset class="card question<?= $finished ? ($right ? ' right' : ' wrong') : '' ?>">
            <legend><?= $n + 1 ?>. <?= e($q['question']) ?><?= $multi ? ' <small class="muted">(mehrere Antworten richtig)</small>' : '' ?></legend>
            <?php foreach ($p['order'] as $oi): if (!isset($q['options'][$oi])) continue;
                $isCorrect = in_array((int) $oi, $q['correct'], true); $isChosen = in_array((int) $oi, $chosen, true); ?>
                <label class="answer<?= $finished && $isCorrect ? ' correct' : '' ?><?= $finished && $isChosen && !$isCorrect ? ' chosen-wrong' : '' ?>">
                    <input type="<?= $multi ? 'checkbox' : 'radio' ?>" name="a[<?= (int) $p['id'] ?>][]" value="<?= (int) $oi ?>"<?= checked($isChosen) ?><?= $finished ? ' disabled' : '' ?>>
                    <?= e($q['options'][$oi]) ?>
                </label>
            <?php endforeach; ?>
            <?php if ($finished && trim((string) $q['explanation']) !== ''): ?><p class="explanation"><?= e($q['explanation']) ?></p><?php endif; ?>
        </fieldset>
    <?php endforeach; ?>
    <?php if (!$finished && $own): ?>
        <div class="form-actions sticky"><button class="btn primary" onclick="return confirm('Prüfung abgeben?')">Prüfung abgeben</button></div>
    <?php endif; ?>
</form>
<?php if (!$finished && $deadline): ?>
<script>
(function () {
    var el = document.querySelector('.exam-timer'), end = Date.now() + parseInt(el.dataset.remaining, 10) * 1000;
    function tick() {
        var s = Math.max(0, Math.round((end - Date.now()) / 1000));
        el.textContent = Math.floor(s / 60) + ':' + ('0' + s % 60).slice(-2);
        el.classList.toggle('low', s < 60);
        if (s === 0) { document.getElementById('exam-form').submit(); return; }
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Prüfung';
require SAMS_ROOT . '/app/layout.php';
