<?php
defined('SAMS_ROOT') || exit;
$me = current_user();

// Bestehenden Entwurf weiterbearbeiten (?draft=ID)
$draftId = (int) ($_GET['draft'] ?? post('draft_id', 0));
$draft = $draftId ? fone('document_drafts', $draftId) : null;
if ($draftId && !$draft) {
    not_found('Entwurf nicht gefunden.');
}
if ($draft && !draft_editable($draft)) {
    redirect(url('draft', ['id' => $draftId]));
}

$typeKey = $draft ? $draft['type'] : (string) ($_GET['type'] ?? post('type', ''));
$type = $typeKey !== '' ? doc_type($typeKey) : null;
$patientId = $draft ? (int) $draft['patient_id'] : (int) ($_GET['patient'] ?? post('patient_id', 0));
$patient = $patientId ? fone('patients', (int) $patientId) : null;
// Dokument aus einer Fallakte heraus (Patient und Felder werden übernommen)
$caseId = $draft ? (int) $draft['case_id'] : (int) ($_GET['case'] ?? post('case_id', 0));
$case = $caseId ? one("SELECT * FROM cases WHERE id = ? AND status = 'open' AND " . fw(), [$caseId]) : null;
if ($case) {
    $patient = fone('patients', (int) $case['patient_id']);
    $patientId = (int) $patient['id'];
}
require_active_patient($patient);

// Schritt 1: Dokumentart wählen
if (!$type) {
    ob_start(); ?>
    <div class="page-head">
        <div><h1>Neues Dokument</h1><?php if ($patient): ?><p class="muted">für <?= e(patient_name($patient)) ?><?= $case ? ' · ' . e(term('case')) . ' ' . e($case['number']) : '' ?></p><?php endif; ?></div>
        <a class="btn" href="<?= e(url('drafts')) ?>">Meine Entwürfe</a>
    </div>
    <?php
    $byCat = [];
    $forEmployee = (int) ($_GET['employee'] ?? 0);
    foreach (document_types() as $k => $t) {
        // Aus der Personalakte heraus nur Personaldokumente anbieten
        if ($forEmployee && ($t['subject'] ?? '') !== 'employee') {
            continue;
        }
        if (empty($t['hidden'])) {
            $byCat[$t['category']][$k] = $t;
        }
    }
    foreach ($byCat as $cat => $types): ?>
        <section class="card">
            <h2><?= e($cat) ?></h2>
            <div class="tiles">
            <?php foreach ($types as $k => $t): $allowed = can('documents.issue.' . $k); ?>
                <a class="tile<?= $allowed ? '' : ' disabled' ?>" href="<?= $allowed ? e(url('document_new', ['type' => $k, 'patient' => $patientId ?: null, 'case' => $case['id'] ?? null, 'employee' => $forEmployee ?: null])) : '#' ?>"
                   title="<?= $allowed ? '' : 'Dein Rang darf dieses Dokument nicht ausstellen' ?>">
                    <span class="tile-code"><?= e($t['code']) ?></span>
                    <strong><?= e($t['title']) ?></strong>
                    <small><?= $allowed ? e($t['category']) . ($allowed && needs_approval($k, $me) ? ' · mit Freigabe' : '') : 'keine Berechtigung' ?></small>
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

if (!empty($type['hidden']) && !$draft) {
    not_found('Diese Dokumentart ist deaktiviert.');
}
if (!can('documents.issue.' . $type['key'])) {
    forbidden('Dein Rang darf „' . $type['title'] . '“ nicht ausstellen.');
}
$cfg = doc_type_config($type['key']);
$isEmployee = ($type['subject'] ?? '') === 'employee';
if ($isEmployee) {
    $patient = null;
    $case = null;
}
$approval = needs_approval($type['key'], $me);

// Vorbelegung: Entwurf, Vorlage (?from=ID) oder Standardwerte
$data = $draft ? (json_decode((string) $draft['data'], true) ?: []) : [];
$liveSignature = $draft ? (string) $draft['signature'] : '';
$fromId = (int) ($_GET['from'] ?? 0);
if (!$draft && $fromId && ($src = one('SELECT * FROM documents WHERE id = ? AND type = ? AND ' . fw(), [$fromId, $type['key']]))) {
    $data = json_decode((string) $src['data'], true) ?: [];
    if (!$patient && $src['patient_id']) {
        $patient = $src['patient_id'] ? fone('patients', (int) $src['patient_id']) : null;
        require_active_patient($patient);
    }
}
foreach ($type['fields'] as $f) {
    if (isset($f['name']) && !array_key_exists($f['name'], $data)) {
        $data[$f['name']] = field_default($f);
    }
}
// Sinnvolle Vorbelegungen aus Fall und Akte
if ($case && !$fromId && !$draft) {
    $data = prefill_from_case($type, $case, $data);
}
// Vorbelegung über die Adresse (?prefill[feld]=…, z. B. „Brandschau dokumentieren“) oder aus einem Leitstellen-Einsatz (?dispatch=ID)
$prefill = is_array($_GET['prefill'] ?? null) ? $_GET['prefill'] : [];
if (!$draft && !$fromId && (int) ($_GET['dispatch'] ?? 0) && ($dispatchInc = fone('dispatch_incidents', (int) $_GET['dispatch']))) {
    $prefill = dispatch_prefill($dispatchInc, $type['key']) + $prefill;
    $data['_dispatch'] = (int) $dispatchInc['id'];
}
if (!$draft && !$fromId && $prefill) {
    foreach ($type['fields'] as $f) {
        $n = $f['name'] ?? '';
        $v = is_string($prefill[$n] ?? null) ? trim($prefill[$n]) : '';
        $ft = $f['type'] ?? 'text';
        $okType = match ($ft) {
            'text', 'textarea' => true,
            'date' => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v),
            'time' => (bool) preg_match('/^\d{2}:\d{2}$/', $v),
            'select' => isset($f['options'][$v]),
            default => false,
        };
        // Textfelder nur, wenn leer; Datum/Uhrzeit/Auswahl ersetzen die Vorgabe (z. B. „heute“)
        if ($n !== '' && $v !== '' && $okType && (!in_array($ft, ['text', 'textarea'], true) || (string) ($data[$n] ?? '') === '')) {
            $data[$n] = mb_substr($v, 0, $ft === 'textarea' ? 5000 : 500);
        }
    }
}
// Frische Vitalwerte aus dem Spiel (Sky Ambulance) übernehmen
if ($patient && !$fromId && !$draft) {
    $data = prefill_vitals($type, $data, $patient);
}
if ($patient && $type['key'] === 'au' && empty($data['employer'])) {
    $data['employer'] = $patient['employer'];
}

// Personaldokumente: betroffenes Konto
$employees = $isEmployee ? all('SELECT * FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name') : [];
$employeeId = (int) ($data['_employee']['id'] ?? ($_GET['employee'] ?? 0));

// Kolleg/innen, an die ein Entwurf übergeben werden kann (dürfen diese Dokumentart ausstellen)
$colleagues = array_values(array_filter(all('SELECT * FROM users WHERE active = 1 AND id <> ? AND ' . fw() . ' ORDER BY name', [$me['id']]),
    fn($u) => in_array('documents.issue.' . $type['key'], user_permissions($u), true)));

$errors = [];
$preview = '';

if (is_post()) {
    csrf_check();
    $action = (string) post('action');
    if ($action === 'discard' && $draft) {
        q('DELETE FROM document_drafts WHERE id = ?', [$draftId]);
        audit('draft.delete', 'document', null, $type['short'] . ' Entwurf #' . $draftId);
        flash('success', 'Entwurf verworfen.');
        redirect(url('drafts'));
    }
    [$data, $errors] = collect_fields($type['fields'], is_array($_POST['d'] ?? null) ? $_POST['d'] : []);
    if ($case && (int) post('patient_id') !== (int) $case['patient_id']) {
        $case = null; // anderer Patient gewählt: Fallzuordnung entfällt
    }
    if ($isEmployee) {
        $employeeId = (int) post('employee_id');
        $emp = $employeeId ? one('SELECT * FROM users WHERE id = ? AND active = 1 AND ' . fw(), [$employeeId]) : null;
        if ($emp) {
            $data['_employee'] = employee_snapshot($emp);
        }
    } elseif (!$case) {
        $patient = post('patient_id') ? fone('patients', (int) post('patient_id')) : null;
        require_active_patient($patient);
    }
    if (locations()) {
        $data['_location'] = location_snapshot((int) post('_location')) ? (int) post('_location') : user_location_id($me);
    }
    if ((int) post('_dispatch') && fone('dispatch_incidents', (int) post('_dispatch'))) {
        $data['_dispatch'] = (int) post('_dispatch'); // Bericht zu einem Leitstellen-Einsatz
    }
    $sigMode = post('sig_mode') === 'live' ? 'live' : 'stored';
    $liveSignature = $sigMode === 'live' ? clean_signature(post('live_signature')) : '';

    // Entwürfe dürfen unvollständig sein; Prüfung erst beim Ausstellen/Einreichen
    $saveDraft = function (string $status, int $ownerId, string $note) use (&$draft, $type, &$patient, &$case, &$data, &$liveSignature, $me): int {
        $row = [
            'type' => $type['key'], 'patient_id' => $patient['id'] ?? null, 'case_id' => $case['id'] ?? null,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE), 'signature' => $liveSignature,
            'status' => $status, 'owner_id' => $ownerId, 'note' => $note, 'updated_at' => now(),
        ];
        if ($status === 'pending') {
            $row += ['submitted_by' => $me['id'], 'submitted_at' => now()];
        }
        if ($draft) {
            update('document_drafts', (int) $draft['id'], $row);
            return (int) $draft['id'];
        }
        return insert('document_drafts', $row + ['created_by' => $me['id'], 'created_at' => now()]);
    };

    if (in_array($action, ['issue', 'submit'], true)) {
        if ($type['patient'] === 'required' && !$patient) {
            $errors[] = 'Bitte eine ' . term('record') . ' auswählen.';
        }
        if ($isEmployee && empty($data['_employee'])) {
            $errors[] = 'Bitte die betroffene Person aus dem Personal auswählen.';
        }
        if (!empty($data['au_since']) && !empty($data['au_until']) && $data['au_until'] < $data['au_since']) {
            $errors[] = 'Das Ende der Arbeitsunfähigkeit liegt vor dem Beginn.';
        }
        if ($type['key'] === 'rechnung') {
            $errors = array_merge($errors, invoice_item_errors($data));
        }
        if ($sigMode === 'live' && $liveSignature === '') {
            $errors[] = 'Bitte unterschreiben oder die hinterlegte Unterschrift wählen.';
        }
    }

    if ($action === 'preview') {
        $issuerSnap = issuer_snapshot($me);
        if ($liveSignature !== '') {
            $issuerSnap['signature'] = $liveSignature;
        }
        $preview = render_document([
            'id' => 0, 'type' => $type['key'], 'number' => 'ENTWURF', 'data' => $data,
            'patient_snapshot' => document_person_snapshot($patient, $data) ?? [], 'issuer_snapshot' => $issuerSnap,
            'verify_code' => 'ENTWURF0', 'status' => 'valid', 'issued_at' => now(),
        ], ['draft' => true]);
    } elseif ($action === 'save') {
        $id = $saveDraft('draft', (int) $me['id'], '');
        audit($draft ? 'draft.update' : 'draft.create', 'document', null, $type['short'] . ' Entwurf #' . $id);
        flash('success', 'Entwurf gespeichert. Du findest ihn unter „Entwürfe“.');
        redirect(url('document_new', ['draft' => $id]));
    } elseif ($action === 'handover') {
        $to = (int) post('handover_to');
        $target = null;
        foreach ($colleagues as $c) {
            $target = (int) $c['id'] === $to ? $c : $target;
        }
        if (!$target) {
            $errors[] = 'Bitte eine Person wählen, die dieses Dokument ausstellen darf.';
        } else {
            $note = mb_substr(trim((string) post('handover_note')), 0, 1000);
            $id = $saveDraft('draft', (int) $target['id'], $note);
            audit('draft.handover', 'document', null, $type['short'] . ' Entwurf #' . $id . ' an ' . user_display($target));
            notify_user((int) $target['id'], 'draft', user_display($me) . ' hat dir einen Entwurf übergeben: ' . $type['title'], url('document_new', ['draft' => $id]), $note);
            flash('success', 'Entwurf an ' . user_display($target) . ' übergeben.');
            redirect(url('drafts'));
        }
    } elseif ($action === 'submit' && $approval && !$errors) {
        $id = $saveDraft('pending', (int) $me['id'], mb_substr(trim((string) post('submit_note')), 0, 1000));
        audit('draft.submit', 'document', null, $type['short'] . ' Entwurf #' . $id . ' zur Freigabe');
        notify_users_with('documents.approve', 'approval', 'Freigabe angefragt: ' . $type['title'] . ' von ' . user_display($me), url('draft', ['id' => $id]),
            fn($u) => user_level($u) >= $cfg['approval_by'] && (int) $u['id'] !== (int) $me['id']);
        notify_webhook('approvals', log_embed('🕓 Freigabe angefragt: ' . $type['title'], user_display($me) . ' hat ein Dokument zur Gegenzeichnung eingereicht. ' . base_url() . '/index.php?p=draft&id=' . $id, 0x9a6200));
        flash('success', 'Zur Freigabe eingereicht. Das Dokument wird ausgestellt, sobald es freigegeben ist.');
        redirect(url('drafts'));
    } elseif ($action === 'issue' && !$approval && !$errors) {
        $id = issue_document($type, $patient, $case, $data, $me, null, $liveSignature);
        if ($draft) {
            q('DELETE FROM document_drafts WHERE id = ?', [$draft['id']]);
        }
        flash('success', $type['title'] . ' wurde ausgestellt.');
        redirect(url('document', ['id' => $id]));
    }
}

$patients = all('SELECT id, first_name, last_name, dob, citizen_id FROM patients WHERE archived_at IS NULL AND merged_into IS NULL AND ' . fw() . ' ORDER BY last_name, first_name');
$hasStored = !empty($me['signature']);
$sigMode = is_post() ? (post('sig_mode') === 'live' ? 'live' : 'stored') : ($liveSignature !== '' || !$hasStored ? 'live' : 'stored');

ob_start(); ?>
<div class="page-head">
    <div>
        <h1><?= e($type['title']) ?> <?= $draft ? draft_status_badge($draft) : '' ?></h1>
        <p class="muted"><?= e($type['category']) ?> · Kürzel <?= e($type['code']) ?><?= $approval ? ' · <strong>benötigt Freigabe</strong> (ab Rangstufe ' . (int) $cfg['approval_by'] . ')' : '' ?></p>
    </div>
    <div class="btn-row">
        <div class="tabs mode-toggle" data-mode-for="#docform">
            <button type="button" data-mode="quick" class="<?= $cfg['mode'] === 'quick' ? 'active' : '' ?>">Schnell</button><button type="button" data-mode="full" class="<?= $cfg['mode'] === 'full' ? 'active' : '' ?>">Vollständig</button>
        </div>
        <?php if (!$draft): ?><a class="btn" href="<?= e(url('document_new', ['patient' => $patientId ?: null, 'case' => $case['id'] ?? null])) ?>">Andere Dokumentart</a><?php endif; ?>
    </div>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<?php if ($draft && $draft['status'] === 'rejected' && $draft['note']): ?><div class="flash warn"><strong>Zurückgewiesen:</strong> <?= e($draft['note']) ?></div>
<?php elseif ($draft && $draft['note']): ?><div class="flash warn"><strong>Hinweis zur Übergabe:</strong> <?= e($draft['note']) ?></div><?php endif; ?>

<form method="post" class="card form-grid docform<?= $cfg['mode'] === 'quick' ? ' quick' : '' ?>" id="docform" action="<?= e(url('document_new', ['type' => $type['key']])) ?>">
    <?php if (!empty($data['_dispatch'])): ?><input type="hidden" name="_dispatch" value="<?= (int) $data['_dispatch'] ?>"><?php endif; ?>
    <?= csrf_field() ?>
    <input type="hidden" name="type" value="<?= e($type['key']) ?>">
    <?php if ($draft): ?><input type="hidden" name="draft_id" value="<?= (int) $draft['id'] ?>"><?php endif; ?>
    <?php if ($case): ?><input type="hidden" name="case_id" value="<?= (int) $case['id'] ?>">
        <div class="field w-full keep"><div class="flash success">Aus <?= e(term('case')) ?> <strong><?= e($case['number']) ?></strong> · <?= e($case['title']) ?><?= $draft ? '' : ' – Angaben aus dem ' . e(term('case')) . ' wurden übernommen.' ?></div></div><?php endif; ?>
    <?php if ($isEmployee): ?>
    <h3 class="form-heading keep">Mitarbeiter/in <span class="req">*</span></h3>
    <div class="field w-full keep">
        <select name="employee_id" required>
            <option value="">– bitte wählen –</option>
            <?php foreach ($employees as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected($u['id'], $employeeId) ?>><?= e(user_display($u)) ?> · <?= e(user_rank_name($u)) ?><?= $u['badge_number'] ? ' · ' . e($u['badge_number']) : '' ?></option><?php endforeach; ?>
        </select>
    </div>
    <?php else: ?>
    <h3 class="form-heading keep"><?= e(term('person')) ?><?= $type['patient'] === 'required' ? ' <span class="req">*</span>' : ' (optional)' ?></h3>
    <div class="field w-full keep">
        <select name="patient_id" class="patient-select"<?= $type['patient'] === 'required' ? ' required' : '' ?>>
            <option value="">– <?= $type['patient'] === 'required' ? 'bitte wählen' : 'keine Akte' ?> –</option>
            <?php foreach ($patients as $pp): ?>
                <option value="<?= (int) $pp['id'] ?>"<?= selected($pp['id'], $patient['id'] ?? '') ?>><?= e(patient_name($pp, true)) ?> · geb. <?= e(fmt_date($pp['dob'])) ?><?= $pp['citizen_id'] ? ' · ' . e($pp['citizen_id']) : '' ?></option>
            <?php endforeach; ?>
        </select>
        <div class="hint">Nicht dabei? <a href="<?= e(url('patient_edit')) ?>" target="_blank">Neue Akte anlegen</a> und Seite neu laden.</div>
    </div>
    <?php endif; ?>
    <?php if (count(locations()) > 1): ?>
    <div class="field w-full keep"><label>Standort (Briefkopf)</label>
        <select name="_location"><?php foreach (locations() as $l): ?><option value="<?= (int) $l['id'] ?>"<?= selected($l['id'], $data['_location'] ?? user_location_id($me)) ?>><?= e($l['name']) ?></option><?php endforeach; ?></select></div>
    <?php elseif (locations()): ?><input type="hidden" name="_location" value="<?= (int) locations()[0]['id'] ?>">
    <?php endif; ?>
    <?php foreach ($type['fields'] as $f): ?>
        <?= render_field($f, isset($f['name']) ? ($data[$f['name']] ?? '') : null) ?>
    <?php endforeach; ?>

    <h3 class="form-heading keep">Unterschrift</h3>
    <div class="field w-full keep">
        <div class="choices">
            <label class="choice"><input type="radio" name="sig_mode" value="stored"<?= checked($sigMode === 'stored') ?><?= $hasStored ? '' : ' disabled' ?>> Hinterlegte Unterschrift<?= $hasStored ? '' : ' (keine im Profil hinterlegt)' ?></label>
            <label class="choice"><input type="radio" name="sig_mode" value="live"<?= checked($sigMode === 'live') ?>> Jetzt unterschreiben</label>
        </div>
        <div class="live-sig"<?= $sigMode === 'live' ? '' : ' hidden' ?>>
            <div class="sigpad" data-input="live_signature"><canvas width="600" height="180"></canvas><button type="button" class="btn small sig-clear">Löschen</button></div>
            <input type="hidden" id="live_signature" name="live_signature" value="<?= e($liveSignature) ?>">
        </div>
    </div>

    <div class="form-actions sticky keep">
        <button class="btn" name="action" value="preview" formnovalidate>Vorschau</button>
        <button class="btn" name="action" value="save" formnovalidate>Als Entwurf speichern</button>
        <?php if ($approval): ?>
            <button class="btn primary" name="action" value="submit">Zur Freigabe einreichen</button>
        <?php else: ?>
            <button class="btn primary" name="action" value="issue" onclick="return confirm('Dokument verbindlich ausstellen? Es kann danach nicht mehr geändert werden – Korrekturen nur per Nachtrag oder Stornierung.')">Verbindlich ausstellen</button>
        <?php endif; ?>
        <?php if ($draft): ?><button class="btn danger" name="action" value="discard" formnovalidate onclick="return confirm('Entwurf verwerfen?')">Verwerfen</button><?php endif; ?>
        <span class="muted small">Ausgestellt durch <?= e(user_display($me)) ?></span>
    </div>

    <details class="w-full handover keep">
        <summary>An Kollegin / Kollegen übergeben</summary>
        <div class="form-grid">
            <div class="field w-half"><label>Übergeben an</label>
                <select name="handover_to"><option value="">– bitte wählen –</option>
                    <?php foreach ($colleagues as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e(user_display($c)) ?><?= $c['badge_number'] ? ' (' . e($c['badge_number']) . ')' : '' ?></option><?php endforeach; ?>
                </select></div>
            <div class="field w-half"><label>Hinweis</label><input name="handover_note" maxlength="1000" placeholder="z. B. Befund fehlt noch, bitte fertigstellen"></div>
            <div class="form-actions"><button class="btn" name="action" value="handover" formnovalidate>Entwurf übergeben</button></div>
        </div>
    </details>
</form>

<?php if ($type['key'] === 'rechnung'): ?><script>window.SAMS_PRICES = <?= price_list_json() ?>; window.SAMS_JAIL = <?= price_jail_json() ?>;</script><?php endif; ?>
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
