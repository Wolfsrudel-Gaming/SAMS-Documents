<?php
defined('SAMS_ROOT') || exit;
/** Personalakte: Laufbahn, Personaldokumente, Dienstzeiten und Notizen. */
$me = current_user();
$id = (int) ($_GET['id'] ?? $me['id']);
$target = one('SELECT * FROM users WHERE id = ?', [$id]);
if (!$target || !can_view_personnel($target)) {
    not_found('Personalakte nicht gefunden.');
}
$own = (int) $target['id'] === (int) $me['id'];
$canEdit = !$own && can_manage_user($target, 'personnel.edit');
$seeConfidential = can('personnel.confidential') && !$own;

if (is_post()) {
    csrf_check();
    if (!$canEdit) {
        forbidden('Du darfst in diese Personalakte nicht schreiben.');
    }
    if (post('action') === 'delete_event') {
        $ev = one('SELECT * FROM personnel_events WHERE id = ? AND user_id = ?', [(int) post('event_id'), $id]);
        if ($ev && !$ev['document_id'] && in_array($ev['kind'], PERSONNEL_MANUAL, true)) {
            q('DELETE FROM personnel_events WHERE id = ?', [$ev['id']]);
            audit('personnel.delete', 'user', $id, $ev['title']);
            flash('success', 'Eintrag entfernt.');
        }
        redirect(url('personnel', ['id' => $id]));
    }
    $kind = in_array(post('kind'), PERSONNEL_MANUAL, true) ? (string) post('kind') : 'note';
    $titleText = mb_substr(trim((string) post('title')), 0, 190);
    $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) post('event_date')) ? (string) post('event_date') : date('Y-m-d');
    if ($titleText === '') {
        flash('error', 'Bitte einen Titel angeben.');
    } else {
        personnel_event($id, $kind, $titleText, mb_substr(trim((string) post('text')), 0, 5000), $date, (bool) post('confidential'));
        audit('personnel.add', 'user', $id, PERSONNEL_KINDS[$kind][1] . ': ' . $titleText);
        flash('success', 'Eintrag hinzugefügt.');
    }
    redirect(url('personnel', ['id' => $id]));
}

$events = all('SELECT e.*, u.name AS author FROM personnel_events e LEFT JOIN users u ON u.id = e.created_by WHERE e.user_id = ? ORDER BY e.event_date DESC, e.id DESC', [$id]);
// Eigene Akte: vertrauliche Notizen der Leitung ausblenden, Dokumente an die Person bleiben sichtbar
$events = array_values(array_filter($events, fn($e) => !$e['confidential'] || $seeConfidential || ($own && $e['document_id'])));
$docs = all('SELECT * FROM documents WHERE employee_id = ? ORDER BY issued_at DESC', [$id]);
$deps = all('SELECT d.* FROM departments d JOIN user_departments ud ON ud.department_id = d.id WHERE ud.user_id = ? ORDER BY d.sort', [$id]);
$quals = all('SELECT q.* FROM qualifications q JOIN user_qualifications uq ON uq.qualification_id = q.id WHERE uq.user_id = ? ORDER BY q.name', [$id]);
$weeks = [];
for ($i = 7; $i >= 0; $i--) {
    $ws = date('Y-m-d H:i:s', strtotime(week_start() . " -$i weeks"));
    $weeks[$ws] = duty_totals($ws, date('Y-m-d H:i:s', strtotime($ws . ' +7 days')), $id)[$id] ?? 0;
}
$maxWeek = max(1, max($weeks));
$employeeTypes = array_filter(document_types(), fn($t, $k) => ($t['subject'] ?? '') === 'employee' && empty($t['hidden']) && can('documents.issue.' . $k), ARRAY_FILTER_USE_BOTH);
$rank = rank_by_id((int) $target['rank_id']);

ob_start(); ?>
<div class="patient-banner staff">
    <div class="pb-avatar"><?= e(mb_strtoupper(mb_substr($target['name'], 0, 1))) ?></div>
    <div>
        <div class="pb-name"><?= e(user_display($target)) ?> <?= rank_badge($rank) ?> <?= $target['active'] ? '' : '<span class="badge red">gesperrt</span>' ?></div>
        <div class="pb-facts">
            <span><b>Dienstnr.</b><span class="mono"><?= e($target['badge_number'] ?: '–') ?></span></span>
            <span><b>Abteilungen</b><?= $deps ? implode(' ', array_map('tag_badge', $deps)) : '–' ?></span>
            <span><b>Im Dienst seit</b><?= e(fmt_date($target['created_at'])) ?></span>
            <?php if ($target['last_login']): ?><span><b>Letzte Anmeldung</b><?= e(fmt_datetime($target['last_login'])) ?></span><?php endif; ?>
        </div>
    </div>
    <div class="btn-row">
        <?php if (!$own && can('users.view')): ?><a class="btn" href="<?= e(url('user_edit', ['id' => $id])) ?>"><?= icon('edit') ?>Konto</a><?php endif; ?>
        <?php if ($employeeTypes && !$own): ?><a class="btn primary" href="<?= e(url('document_new', ['employee' => $id])) ?>"><?= icon('doc-plus') ?>Personaldokument</a><?php endif; ?>
    </div>
</div>

<div class="grid-side">
    <section>
        <div class="card facts">
            <h2>Qualifikationen</h2>
            <p><?= $quals ? implode(' ', array_map('tag_badge', $quals)) : '<span class="muted">keine eingetragen</span>' ?></p>
            <h2>Dienstzeit (8 Wochen)</h2>
            <div class="weekbars">
                <?php foreach ($weeks as $ws => $sec): ?>
                    <div title="KW <?= e(date('W', strtotime($ws))) ?>: <?= e(duty_format($sec)) ?>"><span style="height:<?= round($sec / $maxWeek * 100) ?>%"></span><small><?= e(date('W', strtotime($ws))) ?></small></div>
                <?php endforeach; ?>
            </div>
            <p class="small muted">Diese Woche: <strong><?= e(duty_format(end($weeks))) ?></strong> · Ø <?= e(duty_format((int) (array_sum($weeks) / count($weeks)))) ?> pro Woche<?php if (can('duty.manage')): ?> · <a href="<?= e(url('duty', ['user' => $id])) ?>">Details</a><?php endif; ?></p>
        </div>
        <div class="card">
            <h2>Personaldokumente (<?= count($docs) ?>)</h2>
            <table class="list compact">
                <tbody>
                <?php foreach ($docs as $d): $t = doc_type($d['type']); ?>
                    <tr<?= can('documents.view') ? " onclick=\"location.href='" . e(url('document', ['id' => $d['id']])) . "'\"" : '' ?>>
                        <td><span class="badge blue"><?= e($t['code'] ?? '?') ?></span> <?= e($t['title'] ?? $d['type']) ?><br><small class="mono muted"><?= e($d['number']) ?></small></td>
                        <td class="small right"><?= e(fmt_date($d['issued_at'])) ?><br><?= doc_status_badge($d) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$docs): ?><tr><td class="muted">Keine Personaldokumente.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
    <section>
        <div class="card">
            <h2>Laufbahn</h2>
            <div class="timeline">
            <?php foreach ($events as $ev): ?>
                <article class="tl">
                    <div class="tl-date"><?= e(fmt_date($ev['event_date'])) ?></div>
                    <div class="tl-body">
                        <div class="tl-head"><?= personnel_kind_badge($ev['kind']) ?> <strong><?= e($ev['title']) ?></strong>
                            <?php if ($ev['confidential']): ?><span class="badge purple">vertraulich</span><?php endif; ?>
                            <?php if ($ev['document_id'] && can('documents.view')): ?><a class="tl-edit" href="<?= e(url('document', ['id' => $ev['document_id']])) ?>">Dokument</a><?php endif; ?>
                            <?php if ($canEdit && !$ev['document_id'] && in_array($ev['kind'], PERSONNEL_MANUAL, true)): ?>
                                <form method="post" class="tl-edit"><?= csrf_field() ?><input type="hidden" name="action" value="delete_event"><input type="hidden" name="event_id" value="<?= (int) $ev['id'] ?>"><button class="btn-icon" title="Eintrag entfernen" onclick="return confirm('Eintrag entfernen?')">✕</button></form>
                            <?php endif; ?></div>
                        <?php if (trim((string) $ev['text']) !== ''): ?><div class="tl-text"><?= nl($ev['text']) ?></div><?php endif; ?>
                        <?php if ($ev['author']): ?><small class="muted"><?= e($ev['author']) ?></small><?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
            <?php if (!$events): ?><p class="muted">Noch keine Einträge.</p><?php endif; ?>
            </div>
        </div>
        <?php if ($canEdit): ?>
        <form method="post" class="card form-grid">
            <h2>Eintrag hinzufügen</h2>
            <?= csrf_field() ?>
            <div class="field w-third"><label>Art</label><select name="kind"><?php foreach (PERSONNEL_MANUAL as $k): ?><option value="<?= $k ?>"><?= e(PERSONNEL_KINDS[$k][1]) ?></option><?php endforeach; ?></select></div>
            <div class="field w-third"><label>Datum</label><input type="date" name="event_date" value="<?= e(date('Y-m-d')) ?>"></div>
            <div class="field w-third"><label class="choice single" style="margin-top:1.6rem"><input type="checkbox" name="confidential" value="1"> vertraulich (nur Leitung)</label></div>
            <div class="field w-full"><label>Titel <span class="req">*</span></label><input name="title" required placeholder="z. B. Lob für vorbildliche Einsatzführung beim MANV am Pier"></div>
            <div class="field w-full"><label>Details</label><textarea name="text" rows="3"></textarea></div>
            <p class="hint w-full">Abmahnungen, Beförderungen oder Zertifikate am besten als Personaldokument ausstellen – der Eintrag entsteht dann automatisch.</p>
            <div class="form-actions"><button class="btn primary">Hinzufügen</button></div>
        </form>
        <?php endif; ?>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = 'Personalakte ' . $target['name'];
require SAMS_ROOT . '/app/layout.php';
