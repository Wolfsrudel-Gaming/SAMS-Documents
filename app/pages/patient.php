<?php
defined('SAMS_ROOT') || exit;
$id = (int) ($_GET['id'] ?? 0);
$p = one('SELECT * FROM patients WHERE id = ?', [$id]);
if (!$p) {
    not_found('Patientenakte nicht gefunden.');
}
$me = current_user();
audit('patient.view', 'patient', $id, patient_name($p));

$entries = all('SELECT e.*, u.name AS author, u.title AS author_title FROM entries e LEFT JOIN users u ON u.id = e.created_by
    WHERE e.patient_id = ? ORDER BY e.occurred_at DESC, e.id DESC', [$id]);
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

$canSeeConfidential = fn($en) => !$en['confidential'] || can('entries.confidential') || (int) $en['created_by'] === (int) $me['id'];

ob_start(); ?>
<div class="patient-banner">
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
        <a class="btn" href="<?= e(url('patient_edit', ['id' => $id])) ?>"><?= icon('edit') ?>Stammdaten</a>
        <a class="btn" href="<?= e(url('patient_print', ['id' => $id])) ?>" target="_blank"><?= icon('print') ?>Drucken</a>
        <a class="btn" href="<?= e(url('entry_edit', ['patient' => $id])) ?>"><?= icon('plus') ?>Akteneintrag</a>
        <?php if (can_issue_any()): ?><a class="btn primary" href="<?= e(url('document_new', ['patient' => $id])) ?>"><?= icon('doc-plus') ?>Dokument ausstellen</a><?php endif; ?>
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
    </section>

    <section>
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
                                <a class="tl-edit" href="<?= e(url('entry_edit', ['id' => $r['id']])) ?>">bearbeiten</a>
                            </div>
                            <?php if ($canSeeConfidential($r)): ?>
                                <div class="tl-text"><?= nl($r['content']) ?></div>
                            <?php else: ?>
                                <div class="tl-text muted"><em>Inhalt nur für den ärztlichen Dienst einsehbar.</em></div>
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
