<?php
defined('SAMS_ROOT') || exit;
/** Dokumentenfälschung verwalten (nur Hauptadministrator): Schalter, Hürden, geheime Werkstätten, Übersicht. */
$me = current_user();
if (empty($me['is_superadmin'])) {
    forbidden('Fälschungen verwaltet nur der Hauptadministrator.');
}

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'settings') {
        $types = array_values(array_intersect((array) ($_POST['types'] ?? []), array_keys(document_types_all())));
        $materials = implode(', ', array_map(fn($m) => $m['item'] . ':' . $m['count'], (function () {
            $out = [];
            foreach (explode(',', (string) post('materials')) as $part) {
                if (preg_match('/^\s*([a-z0-9_]+)\s*(?::\s*(\d+))?\s*$/i', $part, $m)) {
                    $out[] = ['item' => strtolower($m[1]), 'count' => max(1, min(20, (int) ($m[2] ?? 1)))];
                }
            }
            return $out;
        })()));
        $vals = ['forgery_enabled' => post('enabled') ? '1' : '0', 'forgery_types' => implode(', ', $types), 'forgery_materials' => $materials,
            'forgery_original' => post('original') ? '1' : '0', 'forgery_duration' => (string) max(10, min(600, (int) post('duration'))),
            'forgery_cooldown' => (string) max(0, min(1440, (int) post('cooldown'))), 'forgery_checks' => (string) max(1, min(10, (int) post('checks'))),
            'forgery_difficulty' => array_key_exists((string) post('difficulty'), FORGERY_DIFFICULTIES) ? (string) post('difficulty') : 'medium',
            'forgery_alert' => (string) max(0, min(100, (int) post('alert')))];
        foreach ($vals as $k => $v) {
            save_setting($k, $v);
        }
        audit('forgery.settings', 'settings', 0, $vals['forgery_enabled'] === '1' ? 'Fälschungen an' : 'Fälschungen aus');
        flash('success', 'Gespeichert. Der Spielserver übernimmt es innerhalb weniger Minuten.');
    } elseif ($action === 'site') {
        $num = fn(string $k) => round((float) str_replace(',', '.', (string) post($k)), 3);
        $row = ['name' => mb_substr(trim((string) post('name')), 0, 190), 'x' => $num('x'), 'y' => $num('y'), 'z' => $num('z'), 'heading' => round($num('heading'), 1),
            'prop_model' => preg_replace('/[^a-z0-9_]/i', '', (string) post('prop_model')), 'notes' => mb_substr(trim((string) post('notes')), 0, 190),
            'server_id' => game_server((int) post('server_id')) ? (int) post('server_id') : null, 'active' => post('active') ? 1 : 0, 'updated_at' => now()];
        if ($row['name'] === '' || ($row['x'] == 0.0 && $row['y'] == 0.0)) {
            flash('error', 'Bitte Name und Position angeben.');
        } elseif ((int) post('id')) {
            update('forgery_sites', (int) post('id'), $row);
            flash('success', 'Werkstatt gespeichert.');
        } else {
            insert('forgery_sites', $row + ['created_at' => now()]);
            flash('success', 'Werkstatt angelegt.');
        }
        audit('forgery.site', 'forgery_site', (int) post('id'), $row['name']);
    } elseif ($action === 'delete_site') {
        q('DELETE FROM forgery_sites WHERE id = ?', [(int) post('id')]);
        flash('success', 'Werkstatt entfernt.');
    }
    redirect(url('forgery'));
}

$cfg = ['enabled' => forgery_enabled(), 'types' => forgery_types(), 'materials' => forgery_setting('forgery_materials'), 'original' => forgery_setting('forgery_original') === '1',
    'duration' => forgery_setting('forgery_duration'), 'cooldown' => forgery_setting('forgery_cooldown'), 'checks' => forgery_setting('forgery_checks'),
    'difficulty' => forgery_setting('forgery_difficulty'), 'alert' => forgery_setting('forgery_alert')];
$sites = all('SELECT * FROM forgery_sites ORDER BY id');
$edit = (int) ($_GET['edit'] ?? 0) ? one('SELECT * FROM forgery_sites WHERE id = ?', [(int) $_GET['edit']]) : null;
$recent = all("SELECT f.*, s.name AS site, fa.name AS authority FROM forgeries f LEFT JOIN forgery_sites s ON s.id = f.site_id LEFT JOIN factions fa ON fa.id = f.faction_id
    WHERE f.status = 'done' ORDER BY f.id DESC LIMIT 50");
$allTypes = document_types_all();
uasort($allTypes, fn($a, $b) => strcmp($a['title'], $b['title']));

ob_start(); ?>
<div class="page-head"><div><h1>Fälschungen</h1><p class="muted">Kriminelle können an geheimen Fälscherwerkstätten im Spiel Dokumente nachmachen. Fälschungen sehen echt aus, ihr Prüfcode existiert aber nicht – die Echtheitsprüfung schlägt fehl. Schlechte Fälschungen haben zusätzlich sichtbare Mängel (z. B. fehlende Mikroschrift am unteren Rand, unscharfer Stempel, falsche Farben).</p></div></div>
<div class="grid-side wide-side reverse">
<section>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="settings">
    <h2 class="w-full">Schalter und Hürden</h2>
    <div class="field w-full"><label class="choice"><input type="checkbox" name="enabled" value="1"<?= checked($cfg['enabled']) ?>> Fälschungen im Spiel erlauben</label></div>
    <div class="field w-full"><label>Verbrauchsmaterial (Item:Anzahl, mit Komma getrennt – Items müssen in ox_inventory existieren)</label><input name="materials" value="<?= e($cfg['materials']) ?>" placeholder="forgery_paper:1, forgery_ink:1"></div>
    <div class="field w-full"><label class="choice"><input type="checkbox" name="original" value="1"<?= checked($cfg['original']) ?>> Echtes Original als Vorlage nötig (nur Dokumentarten, von denen die Person ein echtes Exemplar dabei hat)</label></div>
    <div class="field w-third"><label>Arbeitszeit (Sekunden)</label><input type="number" name="duration" min="10" max="600" value="<?= e($cfg['duration']) ?>"></div>
    <div class="field w-third"><label>Abklingzeit je Person (Minuten)</label><input type="number" name="cooldown" min="0" max="1440" value="<?= e($cfg['cooldown']) ?>"></div>
    <div class="field w-third"><label>Alarm-Risiko (% – anonymer Hinweis an die Polizei)</label><input type="number" name="alert" min="0" max="100" value="<?= e($cfg['alert']) ?>"></div>
    <div class="field w-third"><label>Geschicklichkeitsprüfungen</label><input type="number" name="checks" min="1" max="10" value="<?= e($cfg['checks']) ?>"></div>
    <div class="field w-third"><label>Schwierigkeit</label><select name="difficulty"><?php foreach (FORGERY_DIFFICULTIES as $k => $l): ?><option value="<?= e($k) ?>"<?= selected($k, $cfg['difficulty']) ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
    <p class="hint w-full">Qualität = Anteil bestandener Prüfungen (mit etwas Zufall). Ab 85 % sieht die Fälschung echt aus, darunter fehlt die Mikroschrift und der Stempel ist unscharf, unter 60 % stimmen Farben und Stempelposition nicht, unter 35 % auch Schrift und Papier. Ohne eine bestandene Prüfung misslingt der Druck – das Material ist trotzdem weg.</p>
    <div class="field w-full"><label>Fälschbare Dokumentarten</label><div class="choices columns">
        <?php foreach ($allTypes as $k => $t): if (!empty($t['hidden'])) continue; ?><label class="choice"><input type="checkbox" name="types[]" value="<?= e($k) ?>"<?= checked(in_array($k, $cfg['types'], true)) ?>> <?= e($t['title']) ?></label><?php endforeach; ?>
    </div></div>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<div class="card"><h2>Zuletzt gefälscht</h2>
    <table class="list compact"><thead><tr><th>Zeit</th><th>Dokument</th><th>Behörde</th><th>Qualität</th><th>Werkstatt</th><th>Person (Spieler)</th><th>Geprüft</th></tr></thead><tbody>
    <?php foreach ($recent as $r): $t = $allTypes[$r['type']] ?? null; ?><tr><td class="small"><?= e(fmt_datetime($r['done_at'])) ?></td><td><?= e($t['title'] ?? $r['type']) ?><br><span class="mono small muted"><?= e($r['number']) ?> · <?= e($r['verify_code']) ?></span></td>
        <td class="small"><?= e($r['authority'] ?? '–') ?></td><td><span class="badge <?= (int) $r['quality'] >= 85 ? 'red' : ((int) $r['quality'] >= 60 ? 'orange' : 'grey') ?>"><?= (int) $r['quality'] ?> %</span></td>
        <td class="small"><?= e($r['site'] ?? '–') ?></td><td class="small"><?= e($r['player']) ?><br><span class="mono muted"><?= e(mb_strimwidth($r['license'], 0, 24, '…')) ?></span></td>
        <td class="small"><?= (int) $r['checked_count'] ?>×<?= $r['last_checked_at'] ? '<br>' . e(fmt_datetime($r['last_checked_at'])) : '' ?></td></tr><?php endforeach; ?>
    <?php if (!$recent): ?><tr><td colspan="7" class="muted">Noch keine Fälschungen.</td></tr><?php endif; ?>
    </tbody></table>
    <p class="hint">Nur hier sichtbar. „Geprüft“ zählt, wie oft der Prüfcode in der Echtheitsprüfung eingegeben wurde.</p>
</div>
</section>
<section>
<div class="card"><h2>Werkstätten</h2><p class="small muted">Geheime Orte ohne Kartenmarkierung. Nur wer davon weiß, findet sie.</p>
    <table class="list compact"><tbody>
    <?php foreach ($sites as $s): ?><tr><td><strong><?= e($s['name']) ?></strong><?= $s['active'] ? '' : ' <span class="badge grey">aus</span>' ?><br><span class="mono small muted"><?= e(sprintf('%.1f, %.1f, %.1f', $s['x'], $s['y'], $s['z'])) ?><?= multi_server() ? ' · ' . e(game_server_label((int) $s['server_id']) ?: 'alle Staaten') : '' ?></span></td>
        <td class="right"><a href="<?= e(url('forgery', ['edit' => $s['id']])) ?>">bearbeiten</a></td></tr><?php endforeach; ?>
    <?php if (!$sites): ?><tr><td class="muted">Noch keine Werkstatt.</td></tr><?php endif; ?>
    </tbody></table></div>
<form method="post" class="card form-grid" id="site-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="site"><input type="hidden" name="id" value="<?= (int) ($edit['id'] ?? 0) ?>">
    <h2 class="w-full"><?= $edit ? 'Werkstatt bearbeiten' : 'Neue Werkstatt' ?></h2>
    <div class="field w-full"><label>Name (nur intern)</label><input name="name" required maxlength="190" value="<?= e($edit['name'] ?? '') ?>" placeholder="z. B. Keller Grove Street"></div>
    <div class="field w-full" data-game-only hidden><button type="button" class="btn block" id="use-position"><?= icon('home') ?>Aktuelle Position übernehmen</button></div>
    <div class="field w-third"><label>X</label><input name="x" required value="<?= e($edit['x'] ?? '') ?>"></div><div class="field w-third"><label>Y</label><input name="y" required value="<?= e($edit['y'] ?? '') ?>"></div>
    <div class="field w-third"><label>Z</label><input name="z" value="<?= e($edit['z'] ?? '') ?>"></div><div class="field w-third"><label>Blickrichtung</label><input name="heading" value="<?= e($edit['heading'] ?? '') ?>"></div>
    <div class="field w-two-thirds"><label>Prop (leer = keiner, z. B. prop_laptop_01a)</label><input name="prop_model" value="<?= e($edit['prop_model'] ?? 'prop_laptop_01a') ?>"></div>
    <?php if (count(game_servers()) > 1): ?><div class="field w-full"><label>Staat</label><select name="server_id"><option value="0">alle</option><?php foreach (game_servers() as $gs): ?><option value="<?= (int) $gs['id'] ?>"<?= selected($gs['id'], $edit['server_id'] ?? '') ?>><?= e($gs['name']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
    <div class="field w-full"><label>Notiz</label><input name="notes" maxlength="190" value="<?= e($edit['notes'] ?? '') ?>"></div>
    <div class="field w-full"><label class="choice"><input type="checkbox" name="active" value="1"<?= checked((bool) ($edit['active'] ?? 1)) ?>> aktiv</label></div>
    <div class="form-actions"><?php if ($edit): ?><a class="btn" href="<?= e(url('forgery')) ?>">Abbrechen</a><?php endif; ?><button class="btn primary">Speichern</button></div>
</form>
<?php if ($edit): ?><form method="post" onsubmit="return confirm('Werkstatt entfernen?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete_site"><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><button class="btn small danger">Werkstatt entfernen</button></form><?php endif; ?>
</section>
</div>
<script>
(function () {
    var btn = document.getElementById('use-position');
    btn && btn.addEventListener('click', function () {
        if (!window.SAMSGame) { return; }
        window.SAMSGame.request('getCoords').then(function (c) {
            var f = document.getElementById('site-form');
            f.x.value = c.x.toFixed(2); f.y.value = c.y.toFixed(2); f.z.value = (c.ground != null ? c.ground : c.z - 1).toFixed(2); f.heading.value = (c.h || 0).toFixed(1);
        }).catch(function (e) { alert(e.message); });
    });
})();
</script>
<?php
$content = ob_get_clean();
$title = 'Fälschungen';
require SAMS_ROOT . '/app/layout.php';
