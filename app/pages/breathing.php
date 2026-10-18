<?php
defined('SAMS_ROOT') || exit;
/**
 * Atemschutz-Nachweise (FwDV 7): Eignungsuntersuchung (G26.3), jährliche Belastungsübung und Unterweisung,
 * Einsätze und Übungen unter Atemschutz. Wer nicht alles gültig hat, ist als „nicht einsatzbereit“ markiert.
 */
$me = current_user();
$manage = can('breathing.manage');
$staff = all('SELECT id, name, badge_number FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name');
$staffIds = array_column($staff, 'id');

if (is_post()) {
    csrf_check();
    require_perm('breathing.manage');
    $uid = (int) post('user_id');
    if (!in_array($uid, array_map('intval', $staffIds), true)) {
        forbidden();
    }
    $st = one('SELECT * FROM breathing_status WHERE user_id = ?', [$uid]);
    if (!$st) {
        insert('breathing_status', ['user_id' => $uid, 'faction_id' => fid(), 'updated_at' => now()]);
        $st = one('SELECT * FROM breathing_status WHERE user_id = ?', [$uid]);
    }
    if (post('action') === 'status') {
        $g26 = post_date('g26_until');
        q('UPDATE breathing_status SET g26_until = ?, last_exercise = ?, last_instruction = ?, note = ?, reminded = ?, updated_at = ? WHERE user_id = ?',
            [$g26, post_date('last_exercise'), post_date('last_instruction'), mb_substr(trim((string) post('note')), 0, 190), $g26 !== $st['g26_until'] ? 0 : (int) $st['reminded'], now(), $uid]);
        audit('breathing.status', 'user', $uid, 'Eignung bis ' . $g26);
        flash('success', 'Nachweise gespeichert.');
    } elseif (post('action') === 'log') {
        $kind = in_array(post('kind'), BREATHING_KINDS, true) ? (string) post('kind') : 'Einsatz';
        $date = post_date('log_date') ?: date('Y-m-d');
        insert('breathing_log', ['user_id' => $uid, 'log_date' => $date, 'kind' => $kind, 'minutes' => max(0, min(600, (int) post('minutes'))),
            'note' => mb_substr(trim((string) post('note')), 0, 190), 'recorded_by' => $me['id'], 'created_at' => now()]);
        // Belastungsübung und Unterweisung aktualisieren automatisch die Nachweise
        if (str_starts_with($kind, 'Belastungsübung') && $date > (string) $st['last_exercise']) {
            q('UPDATE breathing_status SET last_exercise = ?, updated_at = ? WHERE user_id = ?', [$date, now(), $uid]);
        } elseif ($kind === 'Unterweisung' && $date > (string) $st['last_instruction']) {
            q('UPDATE breathing_status SET last_instruction = ?, updated_at = ? WHERE user_id = ?', [$date, now(), $uid]);
        }
        audit('breathing.log', 'user', $uid, $kind . ' ' . $date);
        flash('success', 'Eingetragen.');
    }
    redirect(url('breathing', ['user' => $uid]));
}

// Atemschutzgeräteträger: Qualifikation, vorhandene Nachweise oder Einträge
$qualified = array_map('intval', array_column(all("SELECT uq.user_id FROM user_qualifications uq JOIN qualifications q ON q.id = uq.qualification_id WHERE q.faction_id = ? AND q.name LIKE '%Atemschutz%'", [fid()]), 'user_id'));
$status = array_column(all('SELECT * FROM breathing_status WHERE ' . fw()), null, 'user_id');
$stats = array_column(all("SELECT user_id, COUNT(*) AS n, COALESCE(SUM(minutes), 0) AS minutes FROM breathing_log WHERE " . fw() . ' AND log_date >= ? GROUP BY user_id', [date('Y-m-d', strtotime('-12 months'))]), null, 'user_id');
$rows = array_values(array_filter($staff, fn($u) => in_array((int) $u['id'], $qualified, true) || isset($status[$u['id']]) || isset($stats[$u['id']])));
$sel = (int) ($_GET['user'] ?? 0);
$selUser = $sel && in_array($sel, array_map('intval', $staffIds), true) ? array_values(array_filter($staff, fn($u) => (int) $u['id'] === $sel))[0] : null;
$log = $selUser ? all('SELECT l.*, u.name AS by_name FROM breathing_log l LEFT JOIN users u ON u.id = l.recorded_by WHERE l.user_id = ? ORDER BY l.log_date DESC, l.id DESC LIMIT 50', [$sel]) : [];
$ready = count(array_filter($rows, fn($u) => breathing_state($status[$u['id']] ?? null)['ok']));

ob_start(); ?>
<div class="page-head"><div><h1>Atemschutz</h1><p class="muted">Eignungsuntersuchung (G26.3), Belastungsübung und Unterweisung je 12 Monate gültig. Einsätze und Übungen unter Atemschutz.</p></div></div>
<div class="stats"><div class="stat"><strong><?= count($rows) ?></strong><span>Atemschutzgeräteträger/innen</span></div><div class="stat"><strong><?= $ready ?></strong><span>einsatzbereit</span></div><div class="stat"><strong><?= count($rows) - $ready ?></strong><span>gesperrt / Nachweis fehlt</span></div></div>
<div class="grid-side wide-side reverse">
<section class="card">
    <table class="list">
        <thead><tr><th>Name</th><th>Eignung bis</th><th>Belastungsübung</th><th>Unterweisung</th><th>12 Monate</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $u): $st = $status[$u['id']] ?? null; $state = breathing_state($st); $s = $stats[$u['id']] ?? ['n' => 0, 'minutes' => 0]; ?>
            <tr onclick="location.href='<?= e(url('breathing', ['user' => $u['id']])) ?>'"<?= (int) $u['id'] === $sel ? ' class="selected"' : '' ?>>
                <td><?= e($u['name']) ?><?= $u['badge_number'] ? ' <small class="muted mono">' . e($u['badge_number']) . '</small>' : '' ?></td>
                <td class="small"><?= e(fmt_date($st['g26_until'] ?? '') ?: '–') ?></td><td class="small"><?= e(fmt_date($st['last_exercise'] ?? '') ?: '–') ?></td>
                <td class="small"><?= e(fmt_date($st['last_instruction'] ?? '') ?: '–') ?></td><td class="small"><?= (int) $s['n'] ?> · <?= (int) $s['minutes'] ?> min</td>
                <td><?= $state['ok'] ? '<span class="badge green">einsatzbereit</span>' : '<span class="badge red">gesperrt</span><br><small class="muted">' . e(implode(', ', $state['issues'])) . '</small>' ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="6" class="muted">Noch keine Atemschutzgeräteträger/innen. Sie erscheinen mit der Qualifikation „Atemschutzgeräteträger“ oder sobald Nachweise eingetragen sind.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>
<section>
<?php if ($manage): $st = $selUser ? ($status[$sel] ?? null) : null; ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="status">
    <h2 class="w-full">Nachweise</h2>
    <div class="field w-full"><label>Person</label><select name="user_id" onchange="location.href='<?= e(url('breathing')) ?>&user=' + this.value"><option value="">–</option><?php foreach ($staff as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected($u['id'], $sel) ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Eignung (G26.3) gültig bis</label><input type="date" name="g26_until" value="<?= e($st['g26_until'] ?? '') ?>"></div>
    <div class="field"><label>Letzte Belastungsübung</label><input type="date" name="last_exercise" value="<?= e($st['last_exercise'] ?? '') ?>"></div>
    <div class="field"><label>Letzte Unterweisung</label><input type="date" name="last_instruction" value="<?= e($st['last_instruction'] ?? '') ?>"></div>
    <div class="field w-full"><label>Bemerkung</label><input name="note" maxlength="190" value="<?= e($st['note'] ?? '') ?>"></div>
    <div class="form-actions"><button class="btn primary"<?= $selUser ? '' : ' disabled' ?>>Speichern</button></div>
</form>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="log"><input type="hidden" name="user_id" value="<?= (int) $sel ?>">
    <h2 class="w-full">Einsatz / Übung eintragen<?= $selUser ? ': ' . e($selUser['name']) : '' ?></h2>
    <div class="field"><label>Datum</label><input type="date" name="log_date" value="<?= e(date('Y-m-d')) ?>"></div>
    <div class="field"><label>Art</label><select name="kind"><?php foreach (BREATHING_KINDS as $k): ?><option><?= e($k) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Minuten unter Atemschutz</label><input type="number" name="minutes" min="0" max="600" value="20"></div>
    <div class="field w-full"><label>Bemerkung</label><input name="note" maxlength="190" placeholder="z. B. Einsatz Lagerhalle Elysian Island, Innenangriff"></div>
    <div class="form-actions"><button class="btn primary"<?= $selUser ? '' : ' disabled' ?>>Eintragen</button></div>
</form>
<?php endif; ?>
<?php if ($log): ?>
<div class="card"><h2>Verlauf</h2><table class="list compact"><tbody>
    <?php foreach ($log as $l): ?><tr><td class="small"><?= e(fmt_date($l['log_date'])) ?></td><td><?= e($l['kind']) ?></td><td class="small"><?= (int) $l['minutes'] ?> min</td><td class="small"><?= e($l['note']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</section>
</div>
<?php
$content = ob_get_clean();
$title = 'Atemschutz';
require SAMS_ROOT . '/app/layout.php';
