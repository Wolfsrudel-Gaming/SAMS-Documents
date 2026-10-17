<?php
defined('SAMS_ROOT') || exit;
$id = (int) ($_GET['id'] ?? 0);
$p = one('SELECT * FROM patients WHERE id = ?', [$id]);
if (!$p) {
    not_found('Patientenakte nicht gefunden.');
}
$me = current_user();
$archived = patient_archived($p);

if (is_post()) {
    csrf_check();
    if (post('action') === 'portal_link') {
        require_perm('portal.link');
        $c = link_citizen((string) post('code'), $id, (int) $me['id']);
        if ($c) {
            audit('portal.link', 'patient', $id, patient_name($p) . ' ↔ Discord ' . $c['discord_name']);
            flash('success', 'Bürgerportal verknüpft: ' . ($c['ic_name'] ?: $c['discord_name']) . ' sieht jetzt die eigenen Dokumente.');
        } else {
            flash('error', 'Der Code ist ungültig oder abgelaufen.');
        }
        redirect(url('patient', ['id' => $id]));
    }
    if (post('action') === 'delete' && $archived) {
        // Endgültig löschen nur aus dem Archiv heraus (Archivieren statt Löschen)
        require_perm('patients.delete');
        db()->beginTransaction();
        q('DELETE FROM entries WHERE patient_id = ?', [$id]);
        q('UPDATE documents SET case_id = NULL WHERE case_id IN (SELECT id FROM cases WHERE patient_id = ?)', [$id]);
        q('DELETE FROM cases WHERE patient_id = ?', [$id]);
        q('DELETE FROM patients WHERE id = ?', [$id]);
        db()->commit();
        audit('patient.delete', 'patient', $id, patient_name($p));
        flash('success', 'Patientenakte endgültig gelöscht. Ausgestellte Dokumente bleiben erhalten.');
        redirect(url('patients', ['view' => 'archive']));
    }
    require_perm('patients.archive');
    if (post('action') === 'archive' && !$archived) {
        $reason = mb_substr(trim((string) post('reason')), 0, 190);
        update('patients', $id, ['archived_at' => now(), 'archived_reason' => $reason, 'archived_by' => $me['id'], 'updated_at' => now()]);
        q("UPDATE cases SET status = 'closed', closed_at = ?, closed_by = ? WHERE patient_id = ? AND status = 'open'", [now(), $me['id'], $id]);
        audit('patient.archive', 'patient', $id, patient_name($p) . ($reason !== '' ? ' – ' . $reason : ''));
        flash('success', 'Akte archiviert. Offene Fälle wurden abgeschlossen.');
    } elseif (post('action') === 'restore' && $archived) {
        update('patients', $id, ['archived_at' => null, 'archived_reason' => '', 'archived_by' => null, 'updated_at' => now()]);
        audit('patient.restore', 'patient', $id, patient_name($p));
        flash('success', 'Akte reaktiviert.');
    }
    redirect(url('patient', ['id' => $id]));
}

audit('patient.view', 'patient', $id, patient_name($p));

$entries = all('SELECT e.*, u.name AS author, u.title AS author_title, c.number AS case_number, dp.name AS department
    FROM entries e LEFT JOIN users u ON u.id = e.created_by LEFT JOIN cases c ON c.id = e.case_id LEFT JOIN departments dp ON dp.id = e.department_id
    WHERE e.patient_id = ? ORDER BY e.occurred_at DESC, e.id DESC', [$id]);
$cases = all("SELECT c.*, d.name AS department,
        (SELECT COUNT(*) FROM entries e WHERE e.case_id = c.id) AS entry_count,
        (SELECT COUNT(*) FROM documents x WHERE x.case_id = c.id) AS doc_count
    FROM cases c LEFT JOIN departments d ON d.id = c.department_id WHERE c.patient_id = ?
    ORDER BY CASE WHEN c.status = 'open' THEN 0 ELSE 1 END, COALESCE(c.incident_at, c.created_at) DESC", [$id]);
$archivedBy = $archived && $p['archived_by'] ? one('SELECT name FROM users WHERE id = ?', [$p['archived_by']]) : null;
$docs = all('SELECT d.*, u.name AS issuer_name FROM documents d LEFT JOIN users u ON u.id = d.issued_by
    WHERE d.patient_id = ? ORDER BY d.issued_at DESC', [$id]);

// Chronologische Akte: Einträge und Dokumente gemeinsam
$timeline = [];
foreach ($entries as $en) {
    $timeline[] = ['kind' => 'entry', 'at' => $en['occurred_at'], 'row' => $en];
}
foreach ($docs as $d) {
    $timeline[] = ['kind' => 'doc', 'at' => $d['issued_at'], 'row' => $d];
}
usort($timeline, fn($a, $b) => strcmp($b['at'], $a['at']));


ob_start(); ?>
<?php if ($archived): ?>
<div class="flash warn archived-banner">
    <div><strong>Archivierte Akte</strong> seit <?= e(fmt_datetime($p['archived_at'])) ?><?= $archivedBy ? ' durch ' . e($archivedBy['name']) : '' ?><?= $p['archived_reason'] ? ' · ' . e($p['archived_reason']) : '' ?>. Die Akte ist schreibgeschützt.</div>
    <form method="post" class="inline-form"><?= csrf_field() ?>
        <?php if (can('patients.archive')): ?><button class="btn small" name="action" value="restore">Reaktivieren</button><?php endif; ?>
        <?php if (can('patients.delete')): ?><button class="btn small danger" name="action" value="delete" onclick="return confirm('Akte inkl. aller Einträge und Fälle endgültig löschen? Das kann nicht rückgängig gemacht werden. Ausgestellte Dokumente bleiben erhalten.')">Endgültig löschen</button><?php endif; ?>
    </form>
</div>
<?php endif; ?>
<div class="patient-banner<?= $archived ? ' archived' : '' ?>">
    <div class="pb-avatar"><?= icon('user') ?></div>
    <div>
        <div class="pb-name"><?= e(patient_name($p, true)) ?> <?= $p['deceased_at'] ? '<span class="badge black">† ' . e(fmt_date($p['deceased_at'])) . '</span>' : '' ?></div>
        <div class="pb-facts">
            <span><b>geb.</b><?= e(fmt_date($p['dob']) ?: '–') ?><?= $p['dob'] ? ' (' . e(age($p['dob'])) . ' J.)' : '' ?></span>
            <span><b>Geschl.</b><?= e(['m' => 'männlich', 'w' => 'weiblich', 'd' => 'divers'][$p['gender']] ?? '–') ?></span>
            <span><b>Bürger-ID</b><span class="mono"><?= e($p['citizen_id'] ?: '–') ?></span></span>
            <span><b>Blutgruppe</b><?= e($p['blood_type'] ?: '–') ?></span>
            <span><b>Akte</b><span class="mono"><?= str_pad((string) $p['id'], 6, '0', STR_PAD_LEFT) ?></span></span>
        </div>
    </div>
    <div class="btn-row">
        <?php if (!$archived): ?>
            <?php if (can('patients.edit')): ?><a class="btn" href="<?= e(url('patient_edit', ['id' => $id])) ?>"><?= icon('edit') ?>Stammdaten</a><?php endif; ?>
            <?php if (can('cases.manage')): ?><a class="btn" href="<?= e(url('case_edit', ['patient' => $id])) ?>"><?= icon('plus') ?>Fall</a><?php endif; ?>
            <?php if (can('entries.create')): ?><a class="btn" href="<?= e(url('entry_edit', ['patient' => $id])) ?>"><?= icon('plus') ?>Akteneintrag</a><?php endif; ?>
        <?php endif; ?>
        <a class="btn" href="<?= e(url('patient_print', ['id' => $id])) ?>" target="_blank"><?= icon('print') ?>Drucken</a>
        <?php if (!$archived && can_issue_any()): ?><a class="btn primary" href="<?= e(url('document_new', ['patient' => $id])) ?>"><?= icon('doc-plus') ?>Dokument ausstellen</a><?php endif; ?>
    </div>
</div>

<?php if (trim((string) $p['allergies']) !== '' || trim((string) $p['flags']) !== ''): ?>
<div class="alertbar">
    <?php if (trim((string) $p['allergies']) !== ''): ?><div><strong><?= icon('warn') ?> Allergien:</strong> <?= e($p['allergies']) ?></div><?php endif; ?>
    <?php if (trim((string) $p['flags']) !== ''): ?><div><strong>Hinweise:</strong> <?php foreach (array_filter(array_map('trim', explode(',', $p['flags']))) as $f): ?><span class="badge orange"><?= e($f) ?></span> <?php endforeach; ?></div><?php endif; ?>
</div>
<?php endif; ?>

<div class="grid-side">
    <section class="card facts">
        <h2>Stammdaten</h2>
        <dl>
            <dt>Geburtsdatum</dt><dd><?= e(fmt_date($p['dob'])) ?><?= $p['dob'] ? ' (' . e(age($p['dob'])) . ' Jahre)' : '' ?></dd>
            <dt>Geschlecht</dt><dd><?= e(['m' => 'männlich', 'w' => 'weiblich', 'd' => 'divers'][$p['gender']] ?? '–') ?></dd>
            <dt>Bürger-ID</dt><dd class="mono"><?= e($p['citizen_id'] ?: '–') ?></dd>
            <dt>Telefon</dt><dd><?= e($p['phone'] ?: '–') ?></dd>
            <dt>Anschrift</dt><dd><?= nl($p['address'] ?: '–') ?></dd>
            <dt>Beruf / Arbeitgeber</dt><dd><?= e(trim($p['occupation'] . ($p['employer'] ? ' · ' . $p['employer'] : '')) ?: '–') ?></dd>
            <dt>Versicherung</dt><dd><?= e($p['insurance'] ?: '–') ?><?= $p['insurance_number'] ? '<br><span class="mono">' . e($p['insurance_number']) . '</span>' : '' ?></dd>
        </dl>
        <h2>Medizinisch</h2>
        <dl>
            <dt>Blutgruppe</dt><dd><?= e($p['blood_type'] ?: 'unbekannt') ?></dd>
            <dt>Größe / Gewicht</dt><dd><?= e(($p['height'] ? $p['height'] . ' cm' : '–') . ' / ' . ($p['weight'] ? $p['weight'] . ' kg' : '–')) ?></dd>
            <dt>Vorerkrankungen</dt><dd><?= nl($p['conditions'] ?: '–') ?></dd>
            <dt>Dauermedikation</dt><dd><?= nl($p['medication'] ?: '–') ?></dd>
            <dt>Organspender</dt><dd><?= e($p['organ_donor'] ?: 'unbekannt') ?></dd>
            <dt>Patientenverfügung</dt><dd><?= $p['living_will'] ? 'liegt vor' : 'nein' ?></dd>
            <dt>Notfallkontakt</dt><dd><?= nl($p['emergency_contact'] ?: '–') ?></dd>
        </dl>
        <?php if (trim((string) $p['notes']) !== ''): ?>
            <h2>Notizen</h2><p><?= nl($p['notes']) ?></p>
        <?php endif; ?>
        <?php if (can('portal.link')): $linked = all('SELECT * FROM citizens WHERE patient_id = ?', [$id]); ?>
            <h2>Bürgerportal</h2>
            <?php foreach ($linked as $lc): ?><p class="small">Verknüpft mit Discord <strong><?= e($lc['discord_name']) ?></strong><?= $lc['linked_at'] ? ' seit ' . e(fmt_date($lc['linked_at'])) : '' ?></p><?php endforeach; ?>
            <form method="post" class="inline-form">
                <?= csrf_field() ?><input type="hidden" name="action" value="portal_link">
                <input name="code" placeholder="Verknüpfungscode, z. B. K7F3-QX9M" maxlength="12" required>
                <button class="btn small">Verknüpfen</button>
            </form>
        <?php endif; ?>
                <?php if (!$archived && can('patients.archive')): ?>
            <h2>Archiv</h2>
            <form method="post" class="inline-form" onsubmit="return confirm('Akte archivieren? Sie bleibt lesbar, aber es können keine neuen Einträge oder Dokumente mehr angelegt werden.')">
                <?= csrf_field() ?><input name="reason" placeholder="Grund (z. B. verzogen, verstorben)" maxlength="190">
                <button class="btn small" name="action" value="archive">Archivieren</button>
            </form>
        <?php endif; ?>
    </section>

    <section>
        <div class="card">
            <div class="card-head"><h2>Fälle</h2>
                <?php if (!$archived && can('cases.manage')): ?><a class="btn small" href="<?= e(url('case_edit', ['patient' => $id])) ?>">+ Neuer Fall</a><?php endif; ?></div>
            <table class="list compact case-list">
                <tbody>
                <?php foreach ($cases as $c): ?>
                    <tr onclick="location.href='<?= e(url('case', ['id' => $c['id']])) ?>'">
                        <td><strong><?= e($c['title']) ?></strong><br><small class="muted mono"><?= e($c['number']) ?></small> <small class="muted">· <?= e(CASE_KINDS[$c['kind']] ?? $c['kind']) ?><?= $c['department'] ? ' · ' . e($c['department']) : '' ?></small></td>
                        <td class="small"><?= e(fmt_datetime($c['incident_at'] ?: $c['created_at'])) ?></td>
                        <td class="small right"><?= (int) $c['entry_count'] ?> Eintr. · <?= (int) $c['doc_count'] ?> Dok.</td>
                        <td class="right"><?= case_status_badge($c) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$cases): ?><tr><td class="muted">Noch keine Fälle. Ein Fall bündelt Einträge und Dokumente zu einem Einsatz oder einer Behandlung.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card">
            <div class="card-head"><h2>Verlauf</h2>
                <div class="tabs" data-filter="#timeline">
                    <button class="active" data-show="all">Alles</button><button data-show="entry">Einträge</button><button data-show="doc">Dokumente</button>
                </div>
            </div>
            <div id="timeline" class="timeline">
            <?php foreach ($timeline as $item): $r = $item['row']; ?>
                <?php if ($item['kind'] === 'entry'): ?>
                    <article class="tl entry" data-kind="entry">
                        <div class="tl-date"><?= e(fmt_date($r['occurred_at'])) ?><small><?= e(fmt_date($r['occurred_at'], 'H:i')) ?></small></div>
                        <div class="tl-body">
                            <div class="tl-head">
                                <span class="badge"><?= e($r['type']) ?></span>
                                <strong><?= e($r['title']) ?></strong>
                                <?php if ($r['confidential']): ?><span class="badge purple" title="Nur ärztlicher Dienst und Verfasser">vertraulich</span><?php endif; ?>
                                <?php if ($r['department']): ?><span class="badge" title="Nur für Mitglieder dieser Abteilung"><?= e($r['department']) ?></span><?php endif; ?>
                                <?php if ($r['case_number']): ?><a class="badge blue" href="<?= e(url('case', ['id' => $r['case_id']])) ?>"><?= e($r['case_number']) ?></a><?php endif; ?>
                                <?php if (!$archived): ?><a class="tl-edit" href="<?= e(url('entry_edit', ['id' => $r['id']])) ?>">bearbeiten</a><?php endif; ?>
                            </div>
                            <?php if (entry_visible($r)): ?>
                                <div class="tl-text"><?= nl($r['content']) ?></div>
                            <?php else: ?>
                                <div class="tl-text muted"><em>Inhalt für dich nicht einsehbar (vertraulich oder einer anderen Abteilung vorbehalten).</em></div>
                            <?php endif; ?>
                            <small class="muted"><?= e(trim($r['author_title'] . ' ' . $r['author'])) ?></small>
                        </div>
                    </article>
                <?php else: $t = doc_type($r['type']); ?>
                    <article class="tl doc" data-kind="doc">
                        <div class="tl-date"><?= e(fmt_date($r['issued_at'])) ?><small><?= e(fmt_date($r['issued_at'], 'H:i')) ?></small></div>
                        <div class="tl-body">
                            <div class="tl-head">
                                <span class="badge blue"><?= e($t['code'] ?? '?') ?></span>
                                <a href="<?= e(url('document', ['id' => $r['id']])) ?>"><strong><?= e($t['title'] ?? $r['type']) ?></strong></a>
                                <?= doc_status_badge($r) ?>
                            </div>
                            <small class="muted mono"><?= e($r['number']) ?></small> · <small class="muted"><?= e($r['issuer_name']) ?></small>
                        </div>
                    </article>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php if (!$timeline): ?><p class="muted">Noch keine Einträge in dieser Akte.</p><?php endif; ?>
            </div>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
$title = patient_name($p);
require SAMS_ROOT . '/app/layout.php';
