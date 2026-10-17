<?php
defined('SAMS_ROOT') || exit;
/** Dienstplan: Wochenansicht, eintragen/austragen, Tausch, Einteilung durch die Leitung. */
$me = current_user();
$myId = (int) $me['id'];
$manage = can('roster.manage');
$weekIn = (string) ($_POST['week'] ?? ($_GET['week'] ?? ''));
$week = week_start(preg_match('/^\d{4}-\d{2}-\d{2}$/', $weekIn) ? $weekIn : 'now');
$weekParam = substr($week, 0, 10);
$weekEnd = date('Y-m-d H:i:s', strtotime($week . ' +7 days'));

if (is_post()) {
    csrf_check();
    $action = post('action');
    $back = url('roster', ['week' => post('week') ?: $weekParam]);
    $s = roster_shift((int) post('shift_id'));
    $mySlot = $s ? (array_values(array_filter($s['slots'], fn($r) => (int) $r['user_id'] === $myId))[0] ?? null) : null;
    $link = $s ? url('roster', ['week' => substr(week_start($s['starts_at']), 0, 10)]) : '';

    if (in_array($action, ['signup', 'leave', 'offer', 'unoffer', 'take'], true)) {
        require_perm('roster.signup');
        if (!$s) {
            not_found();
        }
        if ($action === 'signup') {
            $err = roster_assign($s, $myId);
            $err ? flash('error', $err) : audit('roster.signup', 'roster', (int) $s['id'], roster_label($s));
            $err ?: flash('success', 'Eingetragen: ' . roster_label($s));
        } elseif ($action === 'leave' && $mySlot) {
            if (roster_locked($s) && !$manage) {
                flash('error', 'So kurz vor Beginn kannst du dich nicht mehr austragen – biete die Schicht zum Tausch an.');
            } else {
                q('DELETE FROM roster_slots WHERE id = ?', [$mySlot['id']]);
                audit('roster.leave', 'roster', (int) $s['id'], roster_label($s));
                flash('success', 'Ausgetragen.');
            }
        } elseif ($action === 'offer' && $mySlot) {
            update('roster_slots', (int) $mySlot['id'], ['status' => 'offered']);
            audit('roster.offer', 'roster', (int) $s['id'], roster_label($s));
            $inShift = array_map('intval', array_column($s['slots'], 'user_id'));
            notify_users_with('roster.signup', 'roster', 'Schicht zur Übernahme: ' . roster_label($s) . ' (von ' . user_display($me) . ')', $link,
                fn($u) => !in_array((int) $u['id'], $inShift, true));
            flash('success', 'Die Schicht wird zur Übernahme angeboten. Du bleibst eingeteilt, bis jemand übernimmt.');
        } elseif ($action === 'unoffer' && $mySlot) {
            update('roster_slots', (int) $mySlot['id'], ['status' => 'assigned']);
            flash('success', 'Tauschangebot zurückgezogen.');
        } elseif ($action === 'take') {
            $slot = one("SELECT * FROM roster_slots WHERE id = ? AND shift_id = ? AND status = 'offered'", [(int) post('slot_id'), $s['id']]);
            if (!$slot || (int) $slot['user_id'] === $myId || $mySlot) {
                flash('error', 'Dieses Angebot ist nicht mehr verfügbar.');
            } elseif ($o = roster_overlap($myId, $s)) {
                flash('error', 'Überschneidung mit ' . roster_label($o) . '.');
            } elseif (q("UPDATE roster_slots SET user_id = ?, status = 'assigned', reminded = 0 WHERE id = ? AND status = 'offered'", [$myId, $slot['id']])->rowCount() === 1) {
                notify_user((int) $slot['user_id'], 'roster', user_display($me) . ' hat deine Schicht übernommen', $link, roster_label($s));
                audit('roster.take', 'roster', (int) $s['id'], roster_label($s) . ' übernommen von ' . user_display(one('SELECT name, title FROM users WHERE id = ?', [$slot['user_id']])));
                flash('success', 'Schicht übernommen: ' . roster_label($s));
            }
        }
        redirect($back);
    }

    require_perm('roster.manage');
    if (in_array($action, ['create', 'edit'], true)) {
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) post('date')) ? (string) post('date') : '';
        $from = preg_match('/^\d{2}:\d{2}$/', (string) post('from')) ? (string) post('from') : '';
        $to = preg_match('/^\d{2}:\d{2}$/', (string) post('to')) ? (string) post('to') : '';
        if (!$date || !$from || !$to) {
            flash('error', 'Bitte Datum, Beginn und Ende angeben.');
            redirect($back);
        }
        $data = [
            'title' => mb_substr(trim((string) post('title')), 0, 120),
            'department_id' => (int) post('department_id') ?: null,
            'min_staff' => max(0, min(50, (int) post('min_staff'))),
            'max_staff' => max(0, min(50, (int) post('max_staff'))),
            'note' => mb_substr(trim((string) post('note')), 0, 190),
        ];
        // Wiederholen an weiteren Wochentagen derselben Woche
        $dates = [$date];
        if ($action === 'create') {
            foreach ((array) ($_POST['repeat'] ?? []) as $d) {
                $d = (int) $d;
                if ($d >= 1 && $d <= 7) {
                    $dates[] = date('Y-m-d', strtotime(week_start($date) . ' +' . ($d - 1) . ' days'));
                }
            }
        }
        $n = 0;
        foreach (array_unique($dates) as $d) {
            $start = "$d $from:00";
            $end = $to > $from ? "$d $to:00" : date('Y-m-d', strtotime("$d +1 day")) . " $to:00";
            if ($action === 'edit' && $s) {
                update('roster_shifts', (int) $s['id'], $data + ['starts_at' => $start, 'ends_at' => $end, 'staff_warned' => 0]);
                foreach ($s['slots'] as $r) {
                    notify_user((int) $r['user_id'], 'roster', 'Schicht geändert: ' . roster_label(['title' => $data['title'], 'starts_at' => $start, 'ends_at' => $end]), $link);
                }
                audit('roster.edit', 'roster', (int) $s['id'], roster_label($data + ['starts_at' => $start, 'ends_at' => $end]));
            } else {
                $id = insert('roster_shifts', $data + ['starts_at' => $start, 'ends_at' => $end, 'created_by' => $myId, 'created_at' => now()]);
                audit('roster.create', 'roster', (int) $id, roster_label($data + ['starts_at' => $start, 'ends_at' => $end]));
            }
            $n++;
        }
        flash('success', $action === 'edit' ? 'Schicht gespeichert.' : $n . ' Schicht(en) angelegt.');
        redirect(url('roster', ['week' => substr(week_start($date), 0, 10)]));
    } elseif ($action === 'delete' && $s) {
        foreach ($s['slots'] as $r) {
            notify_user((int) $r['user_id'], 'roster', 'Schicht entfällt: ' . roster_label($s), $link);
        }
        q('DELETE FROM roster_slots WHERE shift_id = ?', [$s['id']]);
        q('DELETE FROM roster_shifts WHERE id = ?', [$s['id']]);
        audit('roster.delete', 'roster', (int) $s['id'], roster_label($s));
        flash('success', 'Schicht gelöscht.');
    } elseif ($action === 'assign' && $s && ($u = one('SELECT * FROM users WHERE id = ? AND active = 1 AND ' . fw(), [(int) post('user_id')]))) {
        $err = roster_assign($s, (int) $u['id'], true);
        if ($err) {
            flash('error', user_display($u) . ': ' . $err);
        } else {
            notify_user((int) $u['id'], 'roster', 'Du wurdest eingeteilt: ' . roster_label($s), $link);
            audit('roster.assign', 'roster', (int) $s['id'], user_display($u) . ' → ' . roster_label($s));
            flash('success', user_display($u) . ' eingeteilt.');
        }
    } elseif ($action === 'remove' && $s && ($slot = one('SELECT * FROM roster_slots WHERE id = ? AND shift_id = ?', [(int) post('slot_id'), $s['id']]))) {
        q('DELETE FROM roster_slots WHERE id = ?', [$slot['id']]);
        notify_user((int) $slot['user_id'], 'roster', 'Du wurdest ausgetragen: ' . roster_label($s), $link);
        audit('roster.remove', 'roster', (int) $s['id'], roster_label($s));
        flash('success', 'Ausgetragen.');
    } elseif ($action === 'copy_week') {
        // Schichten der Vorwoche ohne Einteilung übernehmen (bereits vorhandene gleiche Zeiten werden übersprungen)
        $prev = date('Y-m-d H:i:s', strtotime($week . ' -7 days'));
        $n = 0;
        foreach (all('SELECT * FROM roster_shifts WHERE starts_at >= ? AND starts_at < ? AND ' . fw(), [$prev, $week]) as $old) {
            $start = date('Y-m-d H:i:s', strtotime($old['starts_at'] . ' +7 days'));
            $end = date('Y-m-d H:i:s', strtotime($old['ends_at'] . ' +7 days'));
            if (!scalar('SELECT COUNT(*) FROM roster_shifts WHERE starts_at = ? AND ends_at = ? AND title = ? AND ' . fw(), [$start, $end, $old['title']])) {
                insert('roster_shifts', ['title' => $old['title'], 'department_id' => $old['department_id'], 'starts_at' => $start, 'ends_at' => $end,
                    'min_staff' => $old['min_staff'], 'max_staff' => $old['max_staff'], 'note' => $old['note'], 'created_by' => $myId, 'created_at' => now()]);
                $n++;
            }
        }
        audit('roster.copy', 'roster', null, "$n Schichten aus KW " . date('W', strtotime($prev)));
        flash($n ? 'success' : 'warn', $n ? "$n Schicht(en) aus der Vorwoche übernommen." : 'Keine neuen Schichten in der Vorwoche gefunden.');
    }
    redirect($back);
}

$shifts = roster_shifts($week, $weekEnd);
$byDay = [];
foreach ($shifts as $s) {
    $byDay[date('Y-m-d', strtotime($s['starts_at']))][] = $s;
}
$departments = all('SELECT * FROM departments WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name');
$staff = $manage ? array_values(array_filter(all('SELECT * FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name'), fn($u) => in_array('roster.signup', user_permissions($u), true) || in_array('duty.clock', user_permissions($u), true))) : [];
$edit = $manage && isset($_GET['edit']) ? roster_shift((int) $_GET['edit']) : null;
$myHours = 0;
foreach ($shifts as $s) {
    if (in_array($myId, array_map('intval', array_column($s['slots'], 'user_id')), true)) {
        $myHours += strtotime($s['ends_at']) - strtotime($s['starts_at']);
    }
}
$under = count(array_filter($shifts, fn($s) => count($s['slots']) < (int) $s['min_staff'] && strtotime($s['ends_at']) > time()));
$days = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'];

ob_start(); ?>
<div class="page-head">
    <div><h1>Dienstplan</h1><p class="muted">KW <?= e(date('W', strtotime($week))) ?> · <?= e(fmt_date($week)) ?> – <?= e(fmt_date(date('Y-m-d', strtotime($weekEnd . ' -1 day')))) ?>
        · meine geplanten Stunden: <strong><?= e(duty_format($myHours)) ?></strong><?= $under ? ' · <span class="badge red">' . $under . ' unterbesetzt</span>' : '' ?></p></div>
    <div class="btn-row">
        <a class="btn" href="<?= e(url('roster', ['week' => date('Y-m-d', strtotime($week . ' -7 days'))])) ?>">‹ Vorwoche</a>
        <a class="btn" href="<?= e(url('roster')) ?>">Diese Woche</a>
        <a class="btn" href="<?= e(url('roster', ['week' => date('Y-m-d', strtotime($week . ' +7 days'))])) ?>">Nächste ›</a>
    </div>
</div>

<div class="roster-week">
<?php for ($i = 0; $i < 7; $i++): $day = date('Y-m-d', strtotime($week . " +$i days")); ?>
    <div class="roster-day<?= $day === date('Y-m-d') ? ' today' : '' ?>">
        <h3><?= e($days[$i]) ?> <small><?= e(fmt_date($day, 'd.m.')) ?></small></h3>
        <?php foreach ($byDay[$day] ?? [] as $s):
            $count = count($s['slots']);
            $mine = array_values(array_filter($s['slots'], fn($r) => (int) $r['user_id'] === $myId))[0] ?? null;
            $past = strtotime($s['ends_at']) < time();
            $running = !$past && strtotime($s['starts_at']) <= time();
            $state = $count < (int) $s['min_staff'] ? 'under' : ((int) $s['max_staff'] > 0 && $count >= (int) $s['max_staff'] ? 'full' : 'ok'); ?>
            <div class="shift <?= $state ?><?= $mine ? ' mine' : '' ?><?= $past ? ' past' : '' ?>" id="shift-<?= (int) $s['id'] ?>">
                <div class="shift-head">
                    <strong><?= e(fmt_date($s['starts_at'], 'H:i')) ?>–<?= e(fmt_date($s['ends_at'], 'H:i')) ?></strong>
                    <span class="badge <?= $state === 'under' ? 'red' : ($state === 'full' ? 'grey' : 'green') ?>" title="besetzt / Mindestbesetzung<?= (int) $s['max_staff'] ? ' (höchstens ' . (int) $s['max_staff'] . ')' : '' ?>"><?= $count ?>/<?= (int) $s['min_staff'] ?><?= (int) $s['max_staff'] ? ' · max ' . (int) $s['max_staff'] : '' ?></span>
                </div>
                <div class="shift-title"><?= e($s['title'] ?: 'Schicht') ?><?= $s['department'] ? ' <small class="muted">· ' . e($s['department']) . '</small>' : '' ?><?= $running ? ' <span class="badge green">läuft</span>' : '' ?></div>
                <?php if ($s['note']): ?><div class="small muted"><?= e($s['note']) ?></div><?php endif; ?>
                <ul class="shift-staff">
                    <?php foreach ($s['slots'] as $r): $att = ($past || $running) ? roster_attended($s, (int) $r['user_id']) : 0; ?>
                        <li class="<?= $r['status'] === 'offered' ? 'offered' : '' ?>">
                            <span><?= e(trim($r['user_title'] . ' ' . $r['name'])) ?>
                                <?php if ($past || $running): ?><span class="small <?= $att > 0 ? 'ok' : 'muted' ?>" title="gestempelte Dienstzeit in dieser Schicht"><?= $att > 0 ? '✓ ' . e(duty_format($att)) : '–' ?></span><?php endif; ?>
                                <?= $r['status'] === 'offered' ? '<span class="badge orange">Tausch</span>' : '' ?></span>
                            <?php if (!$past && $r['status'] === 'offered' && (int) $r['user_id'] !== $myId && !$mine && can('roster.signup')): ?>
                                <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="take"><input type="hidden" name="shift_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="slot_id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>"><button class="btn small primary">Übernehmen</button></form>
                            <?php elseif ($manage && !$past): ?>
                                <form method="post" onsubmit="return confirm('Austragen?')"><?= csrf_field() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="shift_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="slot_id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>"><button class="btn small" title="Austragen">✕</button></form>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if (!$past): ?>
                <div class="shift-actions">
                    <?php if (can('roster.signup')): ?>
                    <form method="post"><?= csrf_field() ?><input type="hidden" name="shift_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>">
                        <?php if (!$mine && roster_free($s) !== 0): ?><button class="btn small primary" name="action" value="signup">Eintragen</button>
                        <?php elseif ($mine && $mine['status'] === 'offered'): ?><button class="btn small" name="action" value="unoffer">Angebot zurückziehen</button>
                        <?php elseif ($mine): ?>
                            <?php if (!roster_locked($s) || $manage): ?><button class="btn small" name="action" value="leave">Austragen</button><?php endif; ?>
                            <button class="btn small" name="action" value="offer">Zum Tausch anbieten</button>
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                    <?php if ($manage): ?>
                        <form method="post" class="assign"><?= csrf_field() ?><input type="hidden" name="action" value="assign"><input type="hidden" name="shift_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>">
                            <select name="user_id" onchange="this.form.submit()"><option value="">+ einteilen …</option>
                                <?php foreach ($staff as $u): if (in_array((int) $u['id'], array_map('intval', array_column($s['slots'], 'user_id')), true)) continue; ?>
                                    <option value="<?= (int) $u['id'] ?>"><?= e(user_display($u)) ?></option><?php endforeach; ?></select></form>
                        <a class="btn small" href="<?= e(url('roster', ['week' => $weekParam, 'edit' => $s['id']])) ?>#shift-form">Bearbeiten</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (empty($byDay[$day])): ?><p class="muted small">Keine Schichten</p><?php endif; ?>
    </div>
<?php endfor; ?>
</div>

<?php if ($manage): $e = $edit; ?>
<div class="grid-2">
<form method="post" class="card form-grid" id="shift-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="<?= $e ? 'edit' : 'create' ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>">
    <?php if ($e): ?><input type="hidden" name="shift_id" value="<?= (int) $e['id'] ?>"><?php endif; ?>
    <h2 class="w-full"><?= $e ? 'Schicht bearbeiten' : 'Schicht anlegen' ?></h2>
    <div class="field"><label>Bezeichnung</label><input name="title" value="<?= e($e['title'] ?? '') ?>" placeholder="z. B. Frühdienst Rettungsdienst" maxlength="120"></div>
    <div class="field"><label>Abteilung</label><select name="department_id"><option value="">– alle –</option><?php foreach ($departments as $d): ?><option value="<?= (int) $d['id'] ?>"<?= selected($d['id'], $e['department_id'] ?? '') ?>><?= e($d['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Datum</label><input type="date" name="date" required value="<?= e($e ? substr($e['starts_at'], 0, 10) : (date('Y-m-d') >= $weekParam && date('Y-m-d') < substr($weekEnd, 0, 10) ? date('Y-m-d') : $weekParam)) ?>"></div>
    <div class="field"><label>Beginn – Ende</label><div class="inline-form"><input type="time" name="from" required value="<?= e($e ? substr($e['starts_at'], 11, 5) : '18:00') ?>"><input type="time" name="to" required value="<?= e($e ? substr($e['ends_at'], 11, 5) : '22:00') ?>"></div><small class="hint">Ende vor Beginn = Nachtschicht bis zum Folgetag</small></div>
    <div class="field"><label>Mindestbesetzung</label><input type="number" name="min_staff" min="0" max="50" value="<?= (int) ($e['min_staff'] ?? 2) ?>"></div>
    <div class="field"><label>Höchstbesetzung</label><input type="number" name="max_staff" min="0" max="50" value="<?= (int) ($e['max_staff'] ?? 0) ?>"><small class="hint">0 = unbegrenzt</small></div>
    <div class="field w-full"><label>Hinweis</label><input name="note" value="<?= e($e['note'] ?? '') ?>" maxlength="190" placeholder="z. B. Treffpunkt Pillbox, Großveranstaltung"></div>
    <?php if (!$e): ?>
    <div class="field w-full"><label>Auch an diesen Tagen derselben Woche anlegen</label>
        <div class="choices"><?php foreach ($days as $i => $d): ?><label class="choice"><input type="checkbox" name="repeat[]" value="<?= $i + 1 ?>"> <?= e(mb_substr($d, 0, 2)) ?></label><?php endforeach; ?></div></div>
    <?php endif; ?>
    <div class="form-actions">
        <?php if ($e): ?><a class="btn" href="<?= e(url('roster', ['week' => $weekParam])) ?>">Abbrechen</a>
            <button class="btn danger" name="action" value="delete" formnovalidate onclick="return confirm('Schicht löschen? Eingeteilte werden benachrichtigt.')">Löschen</button><?php endif; ?>
        <button class="btn primary">Speichern</button>
    </div>
</form>
<div class="card">
    <h2>Planung</h2>
    <form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="action" value="copy_week"><input type="hidden" name="week" value="<?= e($weekParam) ?>">
        <button class="btn">Schichten der Vorwoche übernehmen</button></form>
    <p class="hint">Übernimmt Zeiten, Bezeichnungen und Besetzung der Vorwoche – ohne Einteilung. Vorhandene gleiche Schichten werden übersprungen.</p>
    <p class="hint">Austragen ist bis <?= roster_lock_hours() ?> Stunden vor Beginn möglich, danach nur noch per Tausch (Einstellungen → Dienstzeiten). Eine Stunde vor Beginn wird erinnert, 24 Stunden vorher meldet das System Unterbesetzung an die Planung.</p>
    <p class="hint">Bei laufenden und vergangenen Schichten zeigt ✓ die tatsächlich gestempelte Dienstzeit.</p>
</div>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Dienstplan';
require SAMS_ROOT . '/app/layout.php';
