<?php
defined('SAMS_ROOT') || exit;
/** Einsätze aus der Leitstelle (z. B. tetrascript): Übersicht, Verlauf, Bericht per Knopf vorbelegt. */
$id = (int) ($_GET['id'] ?? 0);
$inc = $id ? fone('dispatch_incidents', $id) : null;
$reportType = DISPATCH_REPORT_TYPE[faction_kind()] ?? '';
$canReport = $reportType !== '' && can('documents.issue.' . $reportType) && doc_type($reportType);
$view = ($_GET['view'] ?? '') === 'closed' ? 'closed' : 'open';
$rows = $inc ? [] : all('SELECT * FROM dispatch_incidents WHERE ' . fw() . ($view === 'closed' ? " AND phase IN ('closed', 'cancelled')" : " AND phase NOT IN ('closed', 'cancelled')")
    . ' ORDER BY COALESCE(started_at, received_at) DESC LIMIT 200');
$onDuty = all('SELECT u.name, u.fms_status, u.fms_opta, u.fms_at FROM users u WHERE u.active = 1 AND u.fms_status IS NOT NULL AND u.fms_at >= ? AND ' . fw('u') . ' ORDER BY u.fms_status, u.name',
    [date('Y-m-d H:i:s', time() - 12 * 3600)]);

ob_start(); ?>
<?php if ($inc): $p = json_decode((string) $inc['payload'], true) ?: []; ?>
<div class="page-head">
    <div><h1><span class="mono"><?= e($inc['number'] ?: $inc['ext_id']) ?></span> <?= e($inc['keyword'] ? $inc['keyword'] . ' – ' : '') ?><?= e($inc['title']) ?></h1>
        <p class="muted"><?= status_badge(DISPATCH_PHASES, (string) $inc['phase']) ?> · <?= e($inc['address']) ?> · Priorität <?= (int) $inc['priority'] ?></p></div>
    <div class="btn-row"><a class="btn" href="<?= e(url('dispatch')) ?>">Zur Übersicht</a>
        <?php if ($inc['document_id']): ?><a class="btn primary" href="<?= e(url('document', ['id' => $inc['document_id']])) ?>">Bericht öffnen</a>
        <?php elseif ($canReport): ?><a class="btn primary" href="<?= e(url('document_new', ['type' => $reportType, 'dispatch' => $inc['id']])) ?>"><?= icon('doc-plus') ?><?= e(doc_type($reportType)['title']) ?> erstellen</a><?php endif; ?></div>
</div>
<div class="grid-side">
<section>
    <div class="card"><dl class="kv">
        <dt>Eingang</dt><dd><?= e(fmt_datetime($inc['started_at'])) ?></dd><dt>Alarmiert</dt><dd><?= e(fmt_datetime($inc['alarm_at']) ?: '–') ?></dd>
        <dt>Vor Ort</dt><dd><?= e(fmt_datetime($inc['onscene_at']) ?: '–') ?></dd><dt>Abgeschlossen</dt><dd><?= e(fmt_datetime($inc['closed_at']) ?: '–') ?><?= $inc['closure'] ? ' · ' . e($inc['closure']) : '' ?></dd>
        <dt>Melder/in</dt><dd><?= e($inc['caller'] ?: '–') ?></dd><dt>Betroffene</dt><dd><?= (int) $inc['patients'] ?></dd>
        <?php if (!empty($p['commander'])): ?><dt>Einsatzleitung</dt><dd><?= e($p['commander']) ?></dd><?php endif; ?>
    </dl>
    <?php if (trim((string) $inc['description']) !== ''): ?><p><?= nl($inc['description']) ?></p><?php endif; ?>
    <?php if (!empty($p['risks'])): ?><div class="alertbar"><div><strong><?= icon('warn') ?> Gefahren:</strong> <?= e($p['risks']) ?></div></div><?php endif; ?>
    <?php if (module_enabled('objects') && ($obj = object_by_text((string) $inc['address']))): ?><p><a class="btn small" href="<?= e(url('object', ['id' => $obj['id']])) ?>">Objektplan: <?= e($obj['name']) ?></a></p><?php endif; ?>
    </div>
    <div class="card"><h2>Einheiten</h2><table class="list compact"><thead><tr><th>Einheit</th><th>Alarm</th><th>Anfahrt</th><th>Vor Ort</th><th>Frei</th></tr></thead><tbody>
        <?php foreach ((array) ($p['assignments'] ?? []) as $a): $t = fn($k) => ($v = dispatch_time($a[$k] ?? null)) ? date('H:i', strtotime($v)) : '–'; ?>
            <tr><td><strong><?= e((string) ($a['label'] ?? $a['issi'] ?? '')) ?></strong> <small class="muted"><?= e((string) ($a['role'] ?? '')) ?></small></td><td class="small"><?= $t('assignedAt') ?></td><td class="small"><?= $t('enrouteAt') ?></td><td class="small"><?= $t('onSceneAt') ?></td><td class="small"><?= $t('releasedAt') ?></td></tr>
        <?php endforeach; ?>
    </tbody></table></div>
</section>
<section class="card"><h2>Einsatzverlauf</h2><table class="list compact"><tbody>
    <?php foreach (array_reverse((array) ($p['timeline'] ?? [])) as $t): ?><tr><td class="small"><?= e(($v = dispatch_time($t['at'] ?? null)) ? date('H:i', strtotime($v)) : '') ?></td><td><?= e((string) ($t['text'] ?? '')) ?> <small class="muted"><?= e((string) ($t['actor'] ?? '')) ?></small></td></tr><?php endforeach; ?>
</tbody></table></section>
</div>
<?php else: ?>
<div class="page-head"><div><h1>Leitstelle</h1><p class="muted">Einsätze und Funkstatus aus der Leitstelle. Aus abgeschlossenen Einsätzen entsteht per Knopf der Bericht.</p></div></div>
<div class="tabs"><a href="<?= e(url('dispatch')) ?>" class="<?= $view === 'open' ? 'active' : '' ?>">Laufend</a><a href="<?= e(url('dispatch', ['view' => 'closed'])) ?>" class="<?= $view === 'closed' ? 'active' : '' ?>">Abgeschlossen</a></div>
<div class="grid-side wide-side reverse">
<section class="card"><table class="list">
    <thead><tr><th>Einsatz</th><th>Ort</th><th>Beginn</th><th>Status</th><th>Bericht</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr onclick="location.href='<?= e(url('dispatch', ['id' => $r['id']])) ?>'"><td><span class="mono small"><?= e($r['number']) ?></span><br><?= e($r['keyword'] ? $r['keyword'] . ' – ' : '') ?><?= e($r['title']) ?></td>
        <td class="small"><?= e($r['address']) ?></td><td class="small"><?= e(fmt_datetime($r['started_at'] ?: $r['received_at'])) ?></td><td><?= status_badge(DISPATCH_PHASES, (string) $r['phase']) ?></td>
        <td><?= $r['document_id'] ? '<span class="badge green">erstellt</span>' : ($r['phase'] === 'closed' ? '<span class="badge orange">offen</span>' : '') ?></td></tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="5" class="muted"><?= game_global()['tetra'] ? 'Keine Einsätze.' : 'Die Leitstellen-Anbindung ist ausgeschaltet (Einstellungen → Spielserver).' ?></td></tr><?php endif; ?>
</tbody></table></section>
<section class="card"><h2>Funkstatus</h2><table class="list compact"><tbody>
    <?php foreach ($onDuty as $u): ?><tr><td><?= e($u['name']) ?> <small class="muted mono"><?= e($u['fms_opta']) ?></small></td><td><?= fms_badge((int) $u['fms_status']) ?></td><td class="small muted"><?= e(date('H:i', strtotime($u['fms_at']))) ?></td></tr><?php endforeach; ?>
    <?php if (!$onDuty): ?><tr><td class="muted">Kein Funkstatus in den letzten 12 Stunden.</td></tr><?php endif; ?>
</tbody></table></section>
</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Leitstelle';
require SAMS_ROOT . '/app/layout.php';
