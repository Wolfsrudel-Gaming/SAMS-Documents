<?php
defined('SAMS_ROOT') || exit;
/** Ausbildung: Lehrgänge und eigener Stand. */
$me = current_user();
$manage = can('training.manage');
$courses = all('SELECT c.*, q.name AS qualification FROM training_courses c LEFT JOIN qualifications q ON q.id = c.qualification_id'
    . ($manage ? '' : ' WHERE c.active = 1') . ' ORDER BY c.sort, c.title');

ob_start(); ?>
<div class="page-head">
    <div><h1>Ausbildung</h1><p class="muted">Lehrgänge mit Lernmodulen und Online-Prüfung. Nach bestandener Prüfung gibt es automatisch Zertifikat und Qualifikation.</p></div>
    <?php if ($manage): ?><a class="btn primary" href="<?= e(url('course_edit')) ?>">+ Neuer Lehrgang</a><?php endif; ?>
</div>
<div class="course-grid">
    <?php foreach ($courses as $c):
        $modules = course_modules((int) $c['id']);
        $read = count(array_intersect(array_map('intval', array_column($modules, 'id')), modules_read((int) $me['id'], (int) $c['id'])));
        $passed = course_passed((int) $me['id'], $c);
        $pct = $modules ? round($read / count($modules) * 100) : 0; ?>
        <a class="card course-card<?= $c['active'] ? '' : ' inactive' ?>" href="<?= e(url('course', ['id' => $c['id']])) ?>">
            <div class="course-head"><strong><?= e($c['title']) ?></strong>
                <?= $passed ? '<span class="badge green">bestanden</span>' : '' ?><?= $c['active'] ? '' : '<span class="badge grey">inaktiv</span>' ?></div>
            <p class="small muted"><?= e(mb_strimwidth((string) $c['description'], 0, 160, '…')) ?></p>
            <div class="progress"><span style="width:<?= $passed ? 100 : $pct ?>%"></span></div>
            <div class="small muted"><?= count($modules) ?> Module · <?= $read ?> gelesen<?= $c['qualification'] ? ' · Qualifikation: ' . e($c['qualification']) : '' ?>
                <?= $passed && (int) $c['valid_months'] ? ' · gültig bis ' . e(fmt_date(date('Y-m-d', strtotime($passed['finished_at'] . ' +' . (int) $c['valid_months'] . ' months')))) : '' ?></div>
        </a>
    <?php endforeach; ?>
    <?php if (!$courses): ?><div class="card muted">Noch keine Lehrgänge.</div><?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Ausbildung';
require SAMS_ROOT . '/app/layout.php';
