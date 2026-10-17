<?php
defined('SAMS_ROOT') || exit;
/** Dienstzeiten: stempeln, wer ist im Dienst, Wochenübersicht, Korrekturen durch die Leitung. */
$me = current_user();
$manage = can('duty.manage');

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'toggle') {
        require_perm('duty.clock');
        $on = !duty_open_session((int) $me['id']);
        duty_set((int) $me['id'], $on, 'web');
        flash('success', $on ? 'Dienst begonnen. Gute Schicht!' : 'Dienst beendet.');
        $return = (string) post('return');
        redirect(preg_match('/^[a-z_]+$/', $return) && $return !== 'duty' ? url($return) : url('duty'));
    }
    require_perm('duty.manage');
    $parse = fn(string $k) => preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', (string) post($k)) ? str_replace('T', ' ', (string) post($k)) . ':00' : null;
    $target = one('SELECT * FROM users WHERE id = ?', [(int) post('user_id')]);
    if ($action === 'end' && ($s = one('SELECT * FROM duty_sessions WHERE id = ? AND ended_at IS NULL', [(int) post('session_id')]))) {
        update('duty_sessions', (int) $s['id'], ['ended_at' => now(), 'end_source' => 'manual', 'edited_by' => $me['id']]);
        audit('duty.correct', 'user', (int) $s['user_id'], 'Schicht #' . $s['id'] . ' beendet');
        flash('success', 'Schicht beendet.');
    } elseif ($action === 'delete' && ($s = one('SELECT * FROM duty_sessions WHERE id = ?', [(int) post('session_id')]))) {
        q('DELETE FROM duty_sessions WHERE id = ?', [$s['id']]);
        audit('duty.correct', 'user', (int) $s['user_id'], 'Schicht #' . $s['id'] . ' gelöscht (' . fmt_datetime($s['started_at']) . ')');
        flash('success', 'Schicht gelöscht.');
    } elseif (in_array($action, ['add', 'edit'], true)) {
        $start = $parse('started_at');
        $end = $parse('ended_at');
        $note = mb_substr(trim((string) post('note')), 0, 190);
        if (!$start || !$end || $end <= $start) {
            flash('error', 'Bitte gültigen Beginn und ein späteres Ende angeben.');
        } elseif ($action === 'add' && $target) {
            insert('duty_sessions', ['user_id' => $target['id'], 'started_at' => $start, 'ended_at' => $end, 'source' => 'manual', 'end_source' => 'manual', 'note' => $note, 'edited_by' => $me['id'], 'created_at' => now()]);
            audit('duty.correct', 'user', (int) $target['id'], 'Schicht nachgetragen ' . fmt_datetime($start) . ' – ' . fmt_datetime($end) . ($note !== '' ? ' (' . $note . ')' : ''));
            flash('success', 'Schicht nachgetragen.');
        } elseif ($action === 'edit' && ($s = one('SELECT * FROM duty_sessions WHERE id = ?', [(int) post('session_id')]))) {
            update('duty_sessions', (int) $s['id'], ['started_at' => $start, 'ended_at' => $end, 'note' => $note, 'edited_by' => $me['id']]);
            audit('duty.correct', 'user', (int) $s['user_id'], 'Schicht #' . $s['id'] . ' geändert: ' . fmt_datetime($start) . ' – ' . fmt_datetime($end));
            flash('success', 'Schicht geändert.');
        }
    }
    redirect(url('duty', array_filter(['user' => (int) post('user_id') ?: null, 'week' => post('week') ?: null])));
}

$week = week_start(preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) ($_GET['week'] ?? '')) ? $_GET['week'] : 'now');
$weekEnd = date('Y-m-d H:i:s', strtotime($week . ' +7 days'));
$weekParam = substr($week, 0, 10);
$userId = $manage ? (int) ($_GET['user'] ?? 0) : 0;
$focus = $userId ? one('SELECT * FROM users WHERE id = ?', [$userId]) : $me;
$open = duty_open_session((int) $me['id']);
$myWeek = duty_totals($week, $weekEnd, (int) $focus['id'])[(int) $focus['id']] ?? 0;
$weeks = [];
for ($i = 3; $i >= 0; $i--) {
    $ws = date('Y-m-d H:i:s', strtotime($week . " -$i weeks"));
    $weeks[$ws] = duty_totals($ws, date('Y-m-d H:i:s', strtotime($ws . ' +7 days')), (int) $focus['id'])[(int) $focus['id']] ?? 0;
}
$sessions = all('SELECT s.*, e.name AS editor FROM duty_sessions s LEFT JOIN users e ON e.id = s.edited_by
    WHERE s.user_id = ? AND s.started_at < ? AND (s.ended_at IS NULL OR s.ended_at > ?) ORDER BY s.started_at DESC', [$focus['id'], $weekEnd, $week]);
$onNow = can('duty.view') || $manage ? duty_on_now() : [];
$team = [];
if ($manage) {
    $totals = duty_totals($week, $weekEnd);
    foreach (all('SELECT * FROM users WHERE active = 1 ORDER BY name') as $u) {
        // Nur Personal, das stempeln darf (keine Behördenkonten)
        if (($totals[(int) $u['id']] ?? 0) > 0 || in_array('duty.clock', user_permissions($u), true)) {
            $team[] = $u + ['seconds' => $totals[(int) $u['id']] ?? 0];
        }
    }
    usort($team, fn($a, $b) => $b['seconds'] <=> $a['seconds']);
}
$dt = fn($v) => $v ? str_replace(' ', 'T', substr((string) $v, 0, 16)) : '';

ob_start(); ?>
<div class="page-head">
    <div><h1>Dienstzeiten</h1><p class="muted">Woche vom <?= e(fmt_date($week)) ?> bis <?= e(fmt_date(date('Y-m-d', strtotime($weekEnd . ' -1 day')))) ?> · vergessene Schichten enden automatisch nach <?= duty_max_hours() ?> Stunden</p></div>
    <div class="btn-row">
        <a class="btn" href="<?= e(url('duty', array_filter(['week' => date('Y-m-d', strtotime($week . ' -7 days')), 'user' => $userId ?: null]))) ?>">‹ Vorwoche</a>
        <a class="btn" href="<?= e(url('duty', array_filter(['user' => $userId ?: null]))) ?>">Diese Woche</a>
        <a class="btn" href="<?= e(url('duty', array_filter(['week' => date('Y-m-d', strtotime($week . ' +7 days')), 'user' => $userId ?: null]))) ?>">Nächste ›</a>
    </div>
</div>

<div class="grid-side">
    <section>
        <?php if ($onNow): ?>
        <div class="card">
            <h2>Jetzt im Dienst (<?= count($onNow) ?>)</h2>
            <div class="duty-now">
                <?php foreach ($onNow as $n): ?>
                    <div class="person"><span class="avatar"><?= e(mb_strtoupper(mb_substr($n['name'], 0, 1))) ?></span>
                        <div><strong><?= e(trim($n['title'] . ' ' . $n['name'])) ?></strong><small><?= e(user_rank_name($n)) ?> · seit <?= e(fmt_date($n['started_at'], 'H:i')) ?> (<?= e(duty_format(duty_seconds($n))) ?>)</small></div></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php elseif (can('duty.view')): ?>
        <div class="card"><h2>Jetzt im Dienst</h2><p class="muted">Niemand ist im Dienst.</p></div>
        <?php endif; ?>
        <?php if ($manage): ?>
        <div class="card">
            <h2>Team – KW <?= e(date('W', strtotime($week))) ?></h2>
            <table class="list compact">
                <thead><tr><th>Mitarbeiter/in</th><th class="right">Stunden</th></tr></thead>
                <tbody>
                <?php foreach ($team as $t): ?>
                    <tr onclick="location.href='<?= e(url('duty', ['user' => $t['id'], 'week' => $weekParam])) ?>'" class="<?= (int) $t['id'] === $userId ? 'selected' : '' ?>">
                        <td><?= e(user_display($t)) ?><br><small class="muted"><?= e(user_rank_name($t)) ?></small></td>
                        <td class="right<?= $t['seconds'] ? '' : ' muted' ?>"><?= e(duty_format($t['seconds'])) ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </section>
    <section>
        <?php if (can('duty.clock') && !$userId): ?>
        <div class="card">
            <h2>Mein Dienst</h2>
            <form method="post" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="toggle">
                <?php if ($open): ?>
                    <span>Im Dienst seit <strong><?= e(fmt_datetime($open['started_at'])) ?></strong> (<?= e(duty_format(duty_seconds($open))) ?>, <?= e(DUTY_SOURCES[$open['source']] ?? $open['source']) ?>)</span>
                    <button class="btn danger">Dienst beenden</button>
                <?php else: ?>
                    <span class="muted">Du bist außer Dienst.</span>
                    <button class="btn primary">Dienst beginnen</button>
                <?php endif; ?>
            </form>
            <p class="hint">Im Spiel stempelst du mit dem Befehl <code>/<?= e(setting('duty_command', 'dienst')) ?></code> oder über das Tablet.</p>
        </div>
        <?php endif; ?>
        <div class="card">
            <h2><?= $userId ? 'Dienstzeiten: ' . e(user_display($focus)) : 'Meine Woche' ?></h2>
            <div class="stats">
                <?php foreach ($weeks as $ws => $sec): ?>
                    <div class="stat<?= $ws === $week ? '' : ' muted-stat' ?>"><strong><?= e(duty_format($sec)) ?></strong><span>KW <?= e(date('W', strtotime($ws))) ?></span></div>
                <?php endforeach; ?>
            </div>
            <table class="list compact">
                <thead><tr><th>Beginn</th><th>Ende</th><th class="right">Dauer</th><th>Quelle</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($sessions as $s): ?>
                    <tr>
                        <td><?= e(fmt_datetime($s['started_at'])) ?></td>
                        <td><?= $s['ended_at'] ? e(fmt_datetime($s['ended_at'])) : '<span class="badge green">läuft</span>' ?></td>
                        <td class="right"><?= e(duty_format(duty_seconds($s))) ?></td>
                        <td class="small"><?= e(DUTY_SOURCES[$s['source']] ?? $s['source']) ?><?= $s['end_source'] && $s['end_source'] !== $s['source'] ? ' → ' . e(DUTY_SOURCES[$s['end_source']] ?? $s['end_source']) : '' ?>
                            <?= $s['note'] ? '<br><span class="muted">' . e($s['note']) . '</span>' : '' ?><?= $s['editor'] ? '<br><span class="muted">korrigiert von ' . e($s['editor']) . '</span>' : '' ?></td>
                        <td class="right"><?php if ($manage): ?>
                            <details class="inline-edit"><summary>bearbeiten</summary>
                                <form method="post" class="form-grid">
                                    <?= csrf_field() ?><input type="hidden" name="session_id" value="<?= (int) $s['id'] ?>"><input type="hidden" name="user_id" value="<?= $userId ?: '' ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>">
                                    <div class="field w-half"><label>Beginn</label><input type="datetime-local" name="started_at" value="<?= e($dt($s['started_at'])) ?>"></div>
                                    <div class="field w-half"><label>Ende</label><input type="datetime-local" name="ended_at" value="<?= e($dt($s['ended_at'] ?: now())) ?>"></div>
                                    <div class="field w-full"><input name="note" value="<?= e($s['note']) ?>" placeholder="Notiz"></div>
                                    <div class="form-actions"><button class="btn small primary" name="action" value="edit">Speichern</button>
                                        <?php if (!$s['ended_at']): ?><button class="btn small" name="action" value="end">Jetzt beenden</button><?php endif; ?>
                                        <button class="btn small danger" name="action" value="delete" onclick="return confirm('Schicht löschen?')">Löschen</button></div>
                                </form>
                            </details>
                        <?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$sessions): ?><tr><td colspan="5" class="muted">Keine Schichten in dieser Woche.</td></tr><?php endif; ?>
                </tbody>
                <tfoot><tr><td colspan="2" class="right"><strong>Summe</strong></td><td class="right"><strong><?= e(duty_format($myWeek)) ?></strong></td><td colspan="2"></td></tr></tfoot>
            </table>
            <?php if ($manage): ?>
                <details><summary>Schicht nachtragen<?= $userId ? '' : ' (für mich)' ?></summary>
                    <form method="post" class="form-grid">
                        <?= csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="user_id" value="<?= (int) $focus['id'] ?>"><input type="hidden" name="week" value="<?= e($weekParam) ?>">
                        <div class="field w-third"><label>Beginn</label><input type="datetime-local" name="started_at" required></div>
                        <div class="field w-third"><label>Ende</label><input type="datetime-local" name="ended_at" required></div>
                        <div class="field w-third"><label>Grund</label><input name="note" placeholder="z. B. Stempeln vergessen"></div>
                        <div class="form-actions"><button class="btn small">Nachtragen</button></div>
                    </form>
                </details>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = 'Dienstzeiten';
require SAMS_ROOT . '/app/layout.php';
