<?php
defined('SAMS_ROOT') || exit;
/** Tauglichkeit der Mitarbeitenden: Übersicht je Untersuchungsart, Anforderung beim Rettungsdienst, Nachweise von außerhalb eintragen. */
$me = current_user();
$manage = can('fitness.manage');
$staff = all('SELECT id, faction_id, name, badge_number, citizen_id FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name');
$staffById = array_column($staff, null, 'id');
$medical = array_values(array_filter(factions(), fn($f) => ($f['kind'] ?: 'medical') === 'medical' && (int) $f['id'] !== fid()));

if (is_post()) {
    csrf_check();
    require_perm('fitness.manage');
    $action = post('action');
    $user = $staffById[(int) post('user_id')] ?? null;
    $kind = array_key_exists((string) post('kind'), FITNESS_KINDS) ? (string) post('kind') : '';
    if ($action === 'request' && $user && $kind && ($to = array_values(array_filter($medical, fn($f) => (int) $f['id'] === (int) post('to_faction_id')))[0] ?? null)) {
        $ref = 'fitness:' . $user['id'] . ':' . $kind;
        if (scalar("SELECT id FROM agency_requests WHERE reference = ? AND status IN ('open', 'in_progress') AND " . fw(), [$ref])) {
            flash('error', 'Für diese Person und Untersuchung ist bereits eine Anforderung offen.');
        } else {
            $subject = 'Tauglichkeitsuntersuchung: ' . FITNESS_KINDS[$kind][0];
            $id = insert('agency_requests', ['to_faction_id' => (int) $to['id'], 'subject' => $subject, 'citizen_id' => (string) $user['citizen_id'], 'person' => $user['name'],
                'reference' => $ref, 'urgent' => post('urgent') ? 1 : 0, 'status' => 'open', 'created_by' => $me['id'], 'created_at' => now(),
                'body' => 'Bitte um arbeitsmedizinische Untersuchung „' . FITNESS_KINDS[$kind][0] . '“ für ' . $user['name'] . ($user['badge_number'] ? ' (Dienstnr. ' . $user['badge_number'] . ')' : '')
                    . ' und Ausstellung einer Bescheinigung über die Einsatztauglichkeit. Der Dienststelle wird nur das Ergebnis mitgeteilt.'
                    . (trim((string) post('note')) !== '' ? "\n\n" . mb_substr(trim((string) post('note')), 0, 1000) : '')]);
            $prev = fid();
            $from = org('org_short');
            set_faction_context((int) $to['id']);
            notify_users_with('requests.manage', 'request', 'Anfrage von ' . $from . ': ' . $subject, url('requests'), fn($u) => (int) $u['faction_id'] === fid());
            set_faction_context($prev);
            audit('fitness.request', 'request', $id, $user['name'] . ' – ' . FITNESS_KINDS[$kind][1] . ' → ' . $to['name']);
            flash('success', 'Untersuchung bei ' . $to['name'] . ' angefordert. Das Ergebnis erscheint hier automatisch.');
        }
    } elseif ($action === 'manual' && $user && $kind) {
        $result = array_key_exists((string) post('result'), FITNESS_RESULTS) ? (string) post('result') : 'geeignet';
        $examined = post_date('examined_on') ?: date('Y-m-d');
        $valid = in_array($result, ['geeignet', 'bedingt'], true) ? (post_date('valid_until') ?: fitness_default_until($kind, $examined)) : '';
        fitness_record($user, ['kind' => $kind, 'result' => $result, 'restrictions' => mb_substr(trim((string) post('restrictions')), 0, 2000), 'examined_on' => $examined,
            'valid_until' => $valid, 'examiner' => mb_substr(trim((string) post('examiner')), 0, 190), 'recorded_by' => $me['id']]);
        audit('fitness.record', 'user', (int) $user['id'], FITNESS_KINDS[$kind][1] . ': ' . FITNESS_RESULTS[$result][1]);
        flash('success', 'Nachweis eingetragen.');
    } elseif ($action === 'revoke' && ($r = fone('fitness_records', (int) post('id'))) && !$r['document_id']) {
        q('UPDATE fitness_records SET revoked = 1 WHERE id = ?', [$r['id']]);
        audit('fitness.revoke', 'user', (int) $r['user_id'], FITNESS_KINDS[$r['kind']][1] ?? $r['kind']);
        flash('success', 'Nachweis entfernt.');
    }
    redirect(url('fitness'));
}

$matrix = fitness_matrix($manage ? null : (int) $me['id']);
$rows = $manage ? $staff : array_values(array_filter($staff, fn($u) => (int) $u['id'] === (int) $me['id']));
$cols = FITNESS_DEFAULT_KINDS[faction_kind()] ?? array_keys(FITNESS_KINDS);
foreach ($matrix as $kinds) {
    $cols = array_merge($cols, array_keys($kinds));
}
$cols = array_values(array_intersect(array_keys(FITNESS_KINDS), $cols));
$history = all('SELECT r.*, u.name FROM fitness_records r JOIN users u ON u.id = r.user_id WHERE ' . fw('r') . ($manage ? '' : ' AND r.user_id = ' . (int) $me['id']) . ' ORDER BY r.id DESC LIMIT 100');
$open = $manage ? all("SELECT q.*, f.name AS to_name FROM agency_requests q JOIN factions f ON f.id = q.to_faction_id WHERE q.reference LIKE 'fitness:%' AND q.status IN ('open', 'in_progress') AND " . fw('q') . ' ORDER BY q.id DESC') : [];
$docLink = fn(array $r) => $r['document_id'] ? ' <a href="' . e(url('shared', ['id' => $r['document_id']])) . '" class="small">Bescheinigung</a>' : '';

ob_start(); ?>
<div class="page-head"><div><h1>Tauglichkeit</h1><p class="muted">Arbeitsmedizinische Einsatztauglichkeit der Mitarbeitenden. Ergebnisse des Rettungsdienstes kommen automatisch an – ohne Befunde, nur Ergebnis, Einschränkungen und Gültigkeit.</p></div></div>
<div class="card"><table class="list compact">
    <thead><tr><th>Mitarbeiter/in</th><?php foreach ($cols as $k): ?><th title="<?= e(FITNESS_KINDS[$k][0]) ?>"><?= e(FITNESS_KINDS[$k][1]) ?></th><?php endforeach; ?></tr></thead><tbody>
    <?php foreach ($rows as $u): ?>
        <tr><td><strong><?= e($u['name']) ?></strong><?php if ($u['citizen_id'] === '' || $u['citizen_id'] === null): ?><br><small class="muted" title="Ohne Bürger-ID werden Ergebnisse nur über den Namen zugeordnet">keine Bürger-ID<?= can('users.edit') ? ' · <a href="' . e(url('user_edit', ['id' => $u['id']])) . '">eintragen</a>' : '' ?></small><?php endif; ?></td>
        <?php foreach ($cols as $k): $r = $matrix[(int) $u['id']][$k] ?? null; [$color, $text] = fitness_state($r); ?>
            <td class="small"><span class="badge <?= e($color) ?>"<?= $r && $r['restrictions'] ? ' title="' . e($r['restrictions']) . '"' : '' ?>><?= e($text) ?></span></td>
        <?php endforeach; ?></tr>
    <?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="<?= count($cols) + 1 ?>" class="muted">Keine Mitarbeitenden.</td></tr><?php endif; ?>
</tbody></table></div>

<div class="grid-side wide-side reverse">
<section>
    <?php if ($open): ?>
    <div class="card"><h2>Angeforderte Untersuchungen</h2><table class="list compact"><thead><tr><th>Datum</th><th>Person</th><th>Untersuchung</th><th>bei</th></tr></thead><tbody>
        <?php foreach ($open as $q): ?><tr><td class="small"><?= e(fmt_date($q['created_at'])) ?></td><td><?= e($q['person']) ?></td><td class="small"><?= e(preg_replace('/^Tauglichkeitsuntersuchung: /', '', $q['subject'])) ?><?= $q['urgent'] ? ' <span class="badge red">dringend</span>' : '' ?></td><td class="small"><?= e($q['to_name']) ?> · <?= $q['status'] === 'open' ? 'offen' : 'in Bearbeitung' ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php endif; ?>
    <div class="card"><h2>Nachweise</h2><table class="list compact"><thead><tr><th>Untersucht</th><th>Person</th><th>Untersuchung</th><th>Ergebnis</th><th>gültig bis</th><th></th></tr></thead><tbody>
        <?php foreach ($history as $r): ?><tr<?= $r['revoked'] ? ' class="muted"' : '' ?>><td class="small"><?= e(fmt_date($r['examined_on'])) ?></td><td><?= e($r['name']) ?></td>
            <td class="small"><?= e(FITNESS_KINDS[$r['kind']][0] ?? $r['kind']) ?><br><span class="muted"><?= e($r['examiner']) ?></span><?= $docLink($r) ?></td>
            <td class="small"><?= status_badge(FITNESS_RESULTS, $r['result']) ?><?= $r['revoked'] ? ' <span class="badge grey">zurückgezogen</span>' : '' ?><?= $r['restrictions'] ? '<br>' . e($r['restrictions']) : '' ?></td>
            <td class="small"><?= e(fmt_date($r['valid_until']) ?: '–') ?></td>
            <td class="right"><?php if ($manage && !$r['document_id'] && !$r['revoked']): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="revoke"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn small" onclick="return confirm('Nachweis entfernen?')">Entfernen</button></form><?php endif; ?></td></tr><?php endforeach; ?>
        <?php if (!$history): ?><tr><td colspan="6" class="muted">Noch keine Nachweise.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php if ($manage): ?>
<section>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="request">
    <h2 class="w-full">Untersuchung anfordern</h2>
    <p class="muted small w-full">Geht als Behördenanfrage an den Rettungsdienst. Stellt dort eine Ärztin oder ein Arzt die Bescheinigung aus, erledigt sich die Anfrage und das Ergebnis erscheint hier.</p>
    <div class="field"><label>Mitarbeiter/in</label><select name="user_id" required><?php foreach ($staff as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Untersuchung</label><select name="kind"><?php foreach (FITNESS_KINDS as $k => $l): ?><option value="<?= e($k) ?>"><?= e($l[0]) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Rettungsdienst</label><select name="to_faction_id" required><?php foreach ($medical as $f): ?><option value="<?= (int) $f['id'] ?>"><?= e($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label class="choice"><input type="checkbox" name="urgent" value="1"> dringend</label></div>
    <div class="field w-full"><label>Hinweis</label><input name="note" maxlength="1000" placeholder="z. B. Einstellung, Wiederholungsuntersuchung, nach Tauchunfall"></div>
    <div class="form-actions"><button class="btn primary"<?= $medical ? '' : ' disabled' ?>>Anfordern</button></div>
</form>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="manual">
    <h2 class="w-full">Nachweis eintragen</h2>
    <p class="muted small w-full">Für Bescheinigungen von außerhalb (z. B. Fliegerarzt, Altbestand).</p>
    <div class="field"><label>Mitarbeiter/in</label><select name="user_id" required><?php foreach ($staff as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e($u['name']) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Untersuchung</label><select name="kind"><?php foreach (FITNESS_KINDS as $k => $l): ?><option value="<?= e($k) ?>"><?= e($l[0]) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Ergebnis</label><select name="result"><?php foreach (FITNESS_RESULTS as $k => [, $l]): ?><option value="<?= e($k) ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
    <div class="field"><label>Untersucht am</label><input type="date" name="examined_on" value="<?= e(date('Y-m-d')) ?>"></div>
    <div class="field"><label>Gültig bis (leer = Regelfrist)</label><input type="date" name="valid_until"></div>
    <div class="field"><label>Untersucht durch</label><input name="examiner" maxlength="190"></div>
    <div class="field w-full"><label>Einschränkungen</label><input name="restrictions" maxlength="2000"></div>
    <div class="form-actions"><button class="btn primary">Eintragen</button></div>
</form>
</section>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Tauglichkeit';
require SAMS_ROOT . '/app/layout.php';
