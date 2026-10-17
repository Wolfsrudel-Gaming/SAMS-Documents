<?php
defined('SAMS_ROOT') || exit;
/** Lehrgang: Lernmodule durcharbeiten und Prüfung starten. */
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$course = one('SELECT c.*, q.name AS qualification FROM training_courses c LEFT JOIN qualifications q ON q.id = c.qualification_id WHERE c.id = ?', [$id]);
if (!$course || (!$course['active'] && !can('training.manage'))) {
    not_found('Lehrgang nicht gefunden.');
}
if (is_post()) {
    csrf_check();
    if (post('action') === 'read') {
        $mid = (int) post('module_id');
        if (scalar('SELECT id FROM training_modules WHERE id = ? AND course_id = ?', [$mid, $id]) && !in_array($mid, modules_read((int) $me['id'], $id), true)) {
            insert('training_progress', ['user_id' => $me['id'], 'module_id' => $mid, 'read_at' => now()]);
        }
        redirect(url('course', ['id' => $id]) . '#m' . $mid);
    }
    if (post('action') === 'start') {
        $blocker = exam_blocker($course, (int) $me['id']);
        if ($blocker !== '') {
            flash('error', $blocker);
            redirect(url('course', ['id' => $id]));
        }
        $aid = start_attempt($course, (int) $me['id']);
        audit('training.start', 'course', $id, $course['title']);
        redirect(url('exam', ['id' => $aid]));
    }
}
$modules = course_modules($id);
$read = modules_read((int) $me['id'], $id);
$attempts = all('SELECT * FROM training_attempts WHERE user_id = ? AND course_id = ? ORDER BY id DESC LIMIT 10', [$me['id'], $id]);
$passed = course_passed((int) $me['id'], $course);
$blocker = exam_blocker($course, (int) $me['id']);
$qCount = count(course_questions($id));
$open = one('SELECT * FROM training_attempts WHERE user_id = ? AND course_id = ? AND finished_at IS NULL ORDER BY id DESC LIMIT 1', [$me['id'], $id]);

ob_start(); ?>
<div class="page-head">
    <div><h1><?= e($course['title']) ?> <?= $passed ? '<span class="badge green">bestanden</span>' : '' ?></h1>
        <p class="muted"><?= count($modules) ?> Module · Prüfung: <?= (int) $course['question_count'] ?: $qCount ?> Fragen<?= (int) $course['time_limit'] ? ', ' . (int) $course['time_limit'] . ' Minuten' : '' ?>, bestanden ab <?= (int) $course['pass_percent'] ?> %<?= $course['qualification'] ? ' · Qualifikation: ' . e($course['qualification']) : '' ?></p></div>
    <div class="btn-row">
        <?php if (can('training.manage')): ?><a class="btn" href="<?= e(url('course_edit', ['id' => $id])) ?>"><?= icon('edit') ?>Bearbeiten</a><?php endif; ?>
        <a class="btn" href="<?= e(url('training')) ?>">Alle Lehrgänge</a>
    </div>
</div>
<?php if (trim((string) $course['description']) !== ''): ?><div class="card prose-ui"><?= light_markup((string) $course['description']) ?></div><?php endif; ?>
<div class="grid-side wide-side reverse">
    <section>
        <?php foreach ($modules as $i => $m): $isRead = in_array((int) $m['id'], $read, true); ?>
            <article class="card module<?= $isRead ? ' read' : '' ?>" id="m<?= (int) $m['id'] ?>">
                <div class="card-head"><h2><?= $i + 1 ?>. <?= e($m['title']) ?></h2><?= $isRead ? '<span class="badge green">gelesen</span>' : '' ?></div>
                <div class="prose-ui"><?= light_markup((string) $m['content']) ?></div>
                <?php if (!$isRead): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="read"><input type="hidden" name="module_id" value="<?= (int) $m['id'] ?>"><button class="btn small">Als gelesen markieren</button></form><?php endif; ?>
            </article>
        <?php endforeach; ?>
        <?php if (!$modules): ?><div class="card muted">Dieser Lehrgang hat noch keine Lernmodule.</div><?php endif; ?>
    </section>
    <section>
        <div class="card">
            <h2>Prüfung</h2>
            <?php if ($open): ?>
                <p>Du hast eine begonnene Prüfung.</p><a class="btn primary" href="<?= e(url('exam', ['id' => $open['id']])) ?>">Prüfung fortsetzen</a>
            <?php elseif ($blocker !== ''): ?>
                <p class="muted"><?= e($blocker) ?></p>
            <?php else: ?>
                <p>Die Fragen werden zufällig gezogen<?= (int) $course['time_limit'] ? ', du hast ' . (int) $course['time_limit'] . ' Minuten Zeit' : '' ?>. Bei Fragen mit Kästchen können mehrere Antworten richtig sein.</p>
                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="start"><button class="btn primary"><?= $passed ? 'Erneut ablegen' : 'Prüfung starten' ?></button></form>
            <?php endif; ?>
            <?php if ($attempts): ?>
                <h3 class="form-heading">Meine Versuche</h3>
                <table class="list compact"><tbody>
                <?php foreach ($attempts as $a): ?>
                    <tr onclick="location.href='<?= e(url('exam', ['id' => $a['id']])) ?>'"><td class="small"><?= e(fmt_datetime($a['started_at'])) ?></td>
                        <td class="right"><?= $a['finished_at'] ? (int) $a['score'] . ' % ' . ($a['passed'] ? '<span class="badge green">bestanden</span>' : '<span class="badge red">nicht bestanden</span>') : '<span class="badge orange">offen</span>' ?></td></tr>
                <?php endforeach; ?>
                </tbody></table>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = $course['title'];
require SAMS_ROOT . '/app/layout.php';
