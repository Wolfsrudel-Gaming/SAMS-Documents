<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$typeKey = (string) ($_GET['type'] ?? post('type', ''));
$type = $typeKey !== '' ? doc_type($typeKey) : null;
$patientId = (int) ($_GET['patient'] ?? post('patient_id', 0));
$patient = $patientId ? one('SELECT * FROM patients WHERE id = ?', [$patientId]) : null;
// Dokument aus einer Fallakte heraus (Patient und Felder werden übernommen)
$caseId = (int) ($_GET['case'] ?? post('case_id', 0));
$case = $caseId ? one("SELECT * FROM cases WHERE id = ? AND status = 'open'", [$caseId]) : null;
if ($case) {
    $patient = one('SELECT * FROM patients WHERE id = ?', [$case['patient_id']]);
    $patientId = (int) $patient['id'];
}
require_active_patient($patient);

// Schritt 1: Dokumentart wählen
if (!$type) {
    ob_start(); ?>
    <div class="page-head">
        <div><h1>Neues Dokument</h1><?php if ($patient): ?><p class="muted">für <?= e(patient_name($patient)) ?><?= $case ? ' · Fall ' . e($case['number']) : '' ?></p><?php endif; ?></div>
    </div>
    <?php
    $byCat = [];
    foreach (document_types() as $k => $t) {
        $byCat[$t['category']][$k] = $t;
    }
    foreach ($byCat as $cat => $types): ?>
        <section class="card">
            <h2><?= e($cat) ?></h2>
            <div class="tiles">
            <?php foreach ($types as $k => $t): $allowed = can('documents.issue.' . $k); ?>
                <a class="tile<?= $allowed ? '' : ' disabled' ?>" href="<?= $allowed ? e(url('document_new', ['type' => $k, 'patient' => $patientId ?: null, 'case' => $case['id'] ?? null])) : '#' ?>"
                   title="<?= $allowed ? '' : 'Dein Rang darf dieses Dokument nicht ausstellen' ?>">
                    <span class="tile-code"><?= e($t['code']) ?></span>
                    <strong><?= e($t['title']) ?></strong>
                    <small><?= $allowed ? e($t['category']) : 'keine Berechtigung' ?></small>
                </a>
            <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach;
    $content = ob_get_clean();
    $title = 'Neues Dokument';
    require SAMS_ROOT . '/app/layout.php';
    return;
}

if (!can('documents.issue.' . $type['key'])) {
    forbidden('Dein Rang darf „' . $type['title'] . '“ nicht ausstellen.');
}

// Vorbelegung: aus Vorlage (?from=ID) oder Standardwerten
$data = [];
$fromId = (int) ($_GET['from'] ?? 0);
if ($fromId && ($src = one('SELECT * FROM documents WHERE id = ? AND type = ?', [$fromId, $type['key']]))) {
    $data = json_decode((string) $src['data'], true) ?: [];
    if (!$patient && $src['patient_id']) {
        $patient = one('SELECT * FROM patients WHERE id = ?', [$src['patient_id']]);
    }
}
foreach ($type['fields'] as $f) {
    if (isset($f['name']) && !array_key_exists($f['name'], $data)) {
        $data[$f['name']] = field_default($f);
    }
}
// Sinnvolle Vorbelegungen aus Fall und Akte
if ($case && !$fromId) {
    $data = prefill_from_case($type, $case, $data);
}
if ($patient && $type['key'] === 'au' && empty($data['employer'])) {
    $data['employer'] = $patient['employer'];
}

$errors = [];
$preview = '';

if (is_post()) {
    csrf_check();
    [$data, $errors] = collect_fields($type['fields'], is_array($_POST['d'] ?? null) ? $_POST['d'] : []);
    if ($case && (int) post('patient_id') !== (int) $case['patient_id']) {
        $case = null; // anderer Patient gewählt: Fallzuordnung entfällt
    }
    if (!$case && ($patient = one('SELECT * FROM patients WHERE id = ?', [(int) post('patient_id')]))) {
        require_active_patient($patient);
    }
    if ($type['patient'] === 'required' && !$patient) {
        $errors[] = 'Bitte eine Patientenakte auswählen.';
    }
    if (!empty($data['au_since']) && !empty($data['au_until']) && $data['au_until'] < $data['au_since']) {
        $errors[] = 'Das Ende der Arbeitsunfähigkeit liegt vor dem Beginn.';
    }

    $docRow = [
        'id' => 0,
        'type' => $type['key'],
        'number' => post('action') === 'preview' ? 'ENTWURF' : '',
        'data' => $data,
        'patient_snapshot' => patient_snapshot($patient) ?? [],
        'issuer_snapshot' => issuer_snapshot($me),
        'verify_code' => 'ENTWURF0',
        'status' => 'valid',
        'issued_at' => now(),
    ];

    if (post('action') === 'preview') {
        $preview = render_document($docRow, ['draft' => true]);
    } elseif (!$errors) {
        db()->beginTransaction();
        try {
            do {
                $code = random_code(8);
            } while (scalar('SELECT id FROM documents WHERE verify_code = ?', [$code]));
            $validUntil = isset($type['valid']) ? (string) ($data[$type['valid']] ?? '') : '';
            $id = insert('documents', [
                'number' => next_document_number($type),
                'type' => $type['key'],
                'patient_id' => $patient['id'] ?? null,
                'case_id' => $case['id'] ?? null,
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
                'patient_snapshot' => json_encode(patient_snapshot($patient), JSON_UNESCAPED_UNICODE),
                'issuer_snapshot' => json_encode(issuer_snapshot($me), JSON_UNESCAPED_UNICODE),
                'verify_code' => $code,
                'view_token' => bin2hex(random_bytes(20)),
                'status' => 'valid',
                'issued_by' => $me['id'],
                'issued_at' => now(),
                'valid_until' => $validUntil,
            ]);
            if ($patient) {
                q('UPDATE patients SET updated_at = ? WHERE id = ?', [now(), $patient['id']]);
                if ($type['key'] === 'todesbescheinigung' && !empty($data['mark_deceased'])) {
                    q('UPDATE patients SET deceased_at = ? WHERE id = ?', [$data['death_date'], $patient['id']]);
                }
            }
            db()->commit();
        } catch (Throwable $ex) {
            db()->rollBack();
            throw $ex;
        }
        audit('document.issue', 'document', $id, $type['short'] . ' ' . patient_name($patient));
        flash('success', $type['title'] . ' wurde ausgestellt.');
        redirect(url('document', ['id' => $id]));
    }
}

$patients = all('SELECT id, first_name, last_name, dob, citizen_id FROM patients WHERE archived_at IS NULL ORDER BY last_name, first_name');

ob_start(); ?>
<div class="page-head">
    <div>
        <h1><?= e($type['title']) ?></h1>
        <p class="muted"><?= e($type['category']) ?> · Kürzel <?= e($type['code']) ?></p>
    </div>
    <a class="btn" href="<?= e(url('document_new', ['patient' => $patientId ?: null, 'case' => $case['id'] ?? null])) ?>">Andere Dokumentart</a>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>

<form method="post" class="card form-grid docform" action="<?= e(url('document_new', ['type' => $type['key']])) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= e($type['key']) ?>">
    <?php if ($case): ?><input type="hidden" name="case_id" value="<?= (int) $case['id'] ?>">
        <div class="field w-full"><div class="flash success">Aus Fall <strong><?= e($case['number']) ?></strong> · <?= e($case['title']) ?> – Diagnose, Ort und Hergang wurden übernommen.</div></div><?php endif; ?>
    <h3 class="form-heading">Patient/in<?= $type['patient'] === 'required' ? ' <span class="req">*</span>' : ' (optional)' ?></h3>
    <div class="field w-full">
        <select name="patient_id" class="patient-select"<?= $type['patient'] === 'required' ? ' required' : '' ?>>
            <option value="">– <?= $type['patient'] === 'required' ? 'bitte wählen' : 'keine Akte' ?> –</option>
            <?php foreach ($patients as $pp): ?>
                <option value="<?= (int) $pp['id'] ?>"<?= selected($pp['id'], $patient['id'] ?? '') ?>><?= e(patient_name($pp, true)) ?> · geb. <?= e(fmt_date($pp['dob'])) ?><?= $pp['citizen_id'] ? ' · ' . e($pp['citizen_id']) : '' ?></option>
            <?php endforeach; ?>
        </select>
        <div class="hint">Nicht dabei? <a href="<?= e(url('patient_edit')) ?>" target="_blank">Neue Akte anlegen</a> und Seite neu laden.</div>
    </div>
    <?php foreach ($type['fields'] as $f): ?>
        <?= render_field($f, isset($f['name']) ? ($data[$f['name']] ?? '') : null) ?>
    <?php endforeach; ?>
    <div class="form-actions sticky">
        <button class="btn" name="action" value="preview" formnovalidate>Vorschau</button>
        <button class="btn primary" name="action" value="issue" onclick="return confirm('Dokument verbindlich ausstellen? Es kann danach nicht mehr geändert, nur noch storniert werden.')">Verbindlich ausstellen</button>
        <span class="muted small">Ausgestellt durch <?= e(user_display($me)) ?><?= empty($me['signature']) ? ' · <strong>keine Unterschrift hinterlegt</strong>' : '' ?></span>
    </div>
</form>

<?php if ($preview): ?>
<section class="preview" id="preview">
    <h2>Vorschau</h2>
    <div class="doc-stage"><?= $preview ?></div>
</section>
<script>document.getElementById('preview').scrollIntoView({behavior: 'smooth'});</script>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = $type['short'] . ' ausstellen';
$withDocumentCss = true;
$scripts = ['assets/vendor/qrcode.js', 'assets/js/document.js'];
require SAMS_ROOT . '/app/layout.php';
