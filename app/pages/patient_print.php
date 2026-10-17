<?php
defined('SAMS_ROOT') || exit;
/** Druckansicht der vollständigen Patientenakte (Karteikarte) */
$id = (int) ($_GET['id'] ?? 0);
$p = fone('patients', $id);
if (!$p) {
    not_found('Patientenakte nicht gefunden.');
}
audit('patient.print', 'patient', $id, patient_name($p));
$me = current_user();
$entries = all('SELECT e.*, u.name AS author, u.title AS author_title FROM entries e LEFT JOIN users u ON u.id = e.created_by WHERE e.patient_id = ? ORDER BY e.occurred_at', [$id]);
$docs = all('SELECT * FROM documents WHERE patient_id = ? ORDER BY issued_at', [$id]);
$doc = ['number' => 'AKTE-' . str_pad((string) $id, 6, '0', STR_PAD_LEFT), 'verify_code' => '', 'issuer_snapshot' => issuer_snapshot($me), 'issued_at' => now()];
$protoTitle = 'Patientenakte';
$protoSub = 'Auszug · vertraulich · ärztliche Schweigepflicht';
ob_start(); ?>
<div class="page-head no-print"><h1>Akte drucken: <?= e(patient_name($p)) ?></h1><button class="btn primary" onclick="window.print()">Drucken / PDF</button></div>
<div class="doc-stage">
<div class="doc-wrap">
<section class="paper a4 proto">
    <?php require SAMS_ROOT . '/app/templates/_protohead.php'; ?>
    <h3 class="psec">Stammdaten</h3>
    <div class="pgrid">
        <div class="pc w6"><span class="fl">Name, Vorname</span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
        <div class="pc w3"><span class="fl">geb. am</span><span class="fv"><?= e(fmt_date($p['dob'])) ?></span></div>
        <div class="pc w3"><span class="fl">Bürger-ID</span><span class="fv mono"><?= e($p['citizen_id']) ?></span></div>
        <div class="pc w6"><span class="fl">Anschrift</span><span class="fv"><?= nl($p['address']) ?></span></div>
        <div class="pc w3"><span class="fl">Telefon</span><span class="fv"><?= e($p['phone']) ?></span></div>
        <div class="pc w3"><span class="fl">Blutgruppe</span><span class="fv"><?= e($p['blood_type']) ?></span></div>
        <div class="pc w6"><span class="fl">Kostenträger</span><span class="fv"><?= e($p['insurance'] . ' ' . $p['insurance_number']) ?></span></div>
        <div class="pc w6"><span class="fl">Allergien</span><span class="fv"><?= nl($p['allergies']) ?></span></div>
        <div class="pc w6"><span class="fl">Vorerkrankungen</span><span class="fv"><?= nl($p['conditions']) ?></span></div>
        <div class="pc w6"><span class="fl">Dauermedikation</span><span class="fv"><?= nl($p['medication']) ?></span></div>
    </div>
    <h3 class="psec">Verlauf</h3>
    <table class="tbl">
        <thead><tr><th style="width:24mm">Datum</th><th>Eintrag</th><th style="width:34mm">Verfasser/in</th></tr></thead>
        <tbody>
        <?php foreach ($entries as $en): $hidden = !entry_visible($en); ?>
            <tr><td><?= e(fmt_datetime($en['occurred_at'])) ?></td>
                <td><strong><?= e($en['type']) ?>: <?= e($en['title']) ?></strong><br><?= $hidden ? '<em>nicht einsehbar</em>' : nl($en['content']) ?></td>
                <td><?= e(trim($en['author_title'] . ' ' . $en['author'])) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h3 class="psec">Ausgestellte Dokumente</h3>
    <table class="tbl">
        <?php foreach ($docs as $d): $t = doc_type($d['type']); ?>
            <tr><td class="mono"><?= e($d['number']) ?></td><td><?= e($t['title'] ?? $d['type']) ?></td><td><?= e(fmt_datetime($d['issued_at'])) ?></td><td><?= $d['status'] === 'revoked' ? 'storniert' : 'gültig' ?></td></tr>
        <?php endforeach; ?>
    </table>
    <p class="small">Ausdruck erstellt am <?= e(fmt_datetime(now())) ?> durch <?= e(user_display($me)) ?>.</p>
</section>
</div>
</div>
<?php
$content = ob_get_clean();
$title = 'Akte drucken';
$withDocumentCss = true;
require SAMS_ROOT . '/app/layout.php';
