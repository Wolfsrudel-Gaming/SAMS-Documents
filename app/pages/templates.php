<?php
defined('SAMS_ROOT') || exit;
/** Vorlagen-Editor: eigene Dokumentarten mit Feldern, Layout und Text mit Platzhaltern. */
$me = current_user();
$id = (int) ($_GET['id'] ?? post('id', 0));
$isNew = isset($_GET['new']) || (is_post() && !$id);
$row = $id ? one('SELECT * FROM custom_doc_types WHERE id = ?', [$id]) : null;
if ($id && !$row) {
    not_found('Vorlage nicht gefunden.');
}
$ranks = all('SELECT * FROM ranks ORDER BY level DESC');
$errors = [];
$preview = '';
$form = $row ?? ['title' => '', 'short' => '', 'code' => '', 'category' => 'Eigene Vorlagen', 'layout' => 'letter', 'subject' => 'patient',
    'patient_mode' => 'optional', 'seal' => 'rect', 'fields' => '[]', 'body' => '', 'active' => 1];
$fields = json_decode((string) $form['fields'], true) ?: [];

if (is_post()) {
    csrf_check();
    [$fields, $errors] = clean_custom_fields(is_array($_POST['f'] ?? null) ? array_values($_POST['f']) : []);
    $form = [
        'title' => mb_substr(trim((string) post('title')), 0, 150),
        'short' => mb_substr(trim((string) post('short')), 0, 60),
        'code' => strtoupper(preg_replace('/[^A-Za-z]/', '', (string) post('code'))),
        'category' => mb_substr(trim((string) post('category')), 0, 80) ?: 'Eigene Vorlagen',
        'layout' => array_key_exists((string) post('layout'), CUSTOM_LAYOUTS) ? (string) post('layout') : 'letter',
        'subject' => post('subject') === 'employee' ? 'employee' : 'patient',
        'patient_mode' => in_array(post('patient_mode'), ['required', 'optional', 'none'], true) ? (string) post('patient_mode') : 'optional',
        'seal' => array_key_exists((string) post('seal'), CUSTOM_SEALS) ? (string) post('seal') : 'rect',
        'body' => mb_substr((string) post('body'), 0, 20000),
        'active' => post('active') ? 1 : 0,
        'fields' => json_encode($fields, JSON_UNESCAPED_UNICODE),
    ];
    if ($form['title'] === '') {
        $errors[] = 'Bitte einen Titel angeben.';
    }
    if (!preg_match('/^[A-Z]{2,3}$/', $form['code'])) {
        $errors[] = 'Das Kürzel muss aus 2–3 Buchstaben bestehen (erscheint in der Dokumentnummer).';
    } else {
        foreach (document_types() as $k => $t) {
            if ($t['code'] === $form['code'] && $k !== ($row['type_key'] ?? '')) {
                $errors[] = 'Das Kürzel ' . $form['code'] . ' wird bereits von „' . $t['title'] . '“ verwendet.';
            }
        }
    }

    if (post('action') === 'preview') {
        $def = custom_type_definition($form + ['type_key' => 'c_preview']);
        $data = [];
        foreach ($fields as $f) {
            if (isset($f['name'])) {
                $data[$f['name']] = match ($f['type']) {
                    'date' => date('Y-m-d'), 'time' => date('H:i'), 'number' => '1', 'checkbox' => '1', 'signature' => '',
                    'checkboxes' => array_slice(array_keys($f['options'] ?? []), 0, 1), 'select', 'radio' => (string) (array_key_first($f['options'] ?? []) ?? ''),
                    default => '[' . $f['label'] . ']',
                };
            }
        }
        if ($form['subject'] === 'employee') {
            $data['_employee'] = employee_snapshot($me);
        }
        $data['_tpl'] = custom_template_snapshot(['key' => 'c_preview'] + $def);
        $samplePatient = ['first_name' => 'Max', 'last_name' => 'Mustermann', 'dob' => '1990-01-01', 'gender' => 'm', 'citizen_id' => 'ABC12345', 'address' => "Grove Street 1\nLos Santos"];
        $preview = render_document(['id' => 0, 'type' => 'c_preview', 'number' => 'ENTWURF', 'data' => $data,
            'patient_snapshot' => $form['subject'] === 'employee' ? document_person_snapshot(null, $data) : ($form['patient_mode'] !== 'none' ? $samplePatient : []),
            'issuer_snapshot' => issuer_snapshot($me), 'verify_code' => 'ENTWURF0', 'status' => 'valid', 'issued_at' => now()], ['draft' => true]);
    } elseif (!$errors) {
        $form['updated_at'] = now();
        if ($row) {
            update('custom_doc_types', $id, $form);
            audit('templates.update', '', $id, $form['title']);
        } else {
            $key = 'c_' . substr(custom_slug($form['title']) ?: 'vorlage', 0, 40);
            $base = $key;
            for ($n = 2; scalar('SELECT id FROM custom_doc_types WHERE type_key = ?', [$key]) || doc_type($key); $n++) {
                $key = $base . '_' . $n;
            }
            $id = insert('custom_doc_types', $form + ['type_key' => $key, 'created_by' => $me['id'], 'created_at' => now()]);
            $row = ['type_key' => $key];
            audit('templates.create', '', $id, $form['title']);
        }
        // Ausstellrecht für die gewählten Ränge (nur Ränge, die man verwalten darf)
        $perm = 'documents.issue.' . ($row['type_key'] ?? '');
        $chosen = array_map('intval', (array) ($_POST['ranks'] ?? []));
        foreach ($ranks as $r) {
            if (!can_manage_rank($r) && empty($me['is_superadmin'])) {
                continue;
            }
            $has = (bool) scalar('SELECT COUNT(*) FROM rank_permissions WHERE rank_id = ? AND permission = ?', [$r['id'], $perm]);
            if (in_array((int) $r['id'], $chosen, true) && !$has) {
                insert('rank_permissions', ['rank_id' => $r['id'], 'permission' => $perm]);
            } elseif (!in_array((int) $r['id'], $chosen, true) && $has) {
                q('DELETE FROM rank_permissions WHERE rank_id = ? AND permission = ?', [$r['id'], $perm]);
            }
        }
        flash('success', 'Vorlage gespeichert.' . ($form['active'] ? ' Sie steht unter „Neues Dokument“ zur Verfügung.' : ''));
        redirect(url('templates', ['id' => $id]));
    }
}

// Übersicht
if (!$id && !$isNew) {
    $list = custom_doc_types();
    ob_start(); ?>
    <div class="page-head">
        <div><h1>Vorlagen-Editor</h1><p class="muted">Eigene Dokumentarten ohne Programmierung: Felder festlegen, Layout wählen, Text mit Platzhaltern schreiben.</p></div>
        <a class="btn primary" href="<?= e(url('templates', ['new' => 1])) ?>">+ Neue Vorlage</a>
    </div>
    <div class="card">
        <table class="list">
            <thead><tr><th>Kürzel</th><th>Dokumentart</th><th>Layout</th><th>Bezug</th><th class="right">Ausgestellt</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($list as $t): ?>
                <tr onclick="location.href='<?= e(url('templates', ['id' => $t['id']])) ?>'">
                    <td><span class="badge blue"><?= e($t['code']) ?></span></td>
                    <td><strong><?= e($t['title']) ?></strong><br><small class="muted"><?= e($t['category']) ?></small></td>
                    <td class="small"><?= e(explode(' (', CUSTOM_LAYOUTS[$t['layout']] ?? $t['layout'])[0]) ?></td>
                    <td class="small"><?= $t['subject'] === 'employee' ? 'Personal' : 'Patient/in' ?></td>
                    <td class="right"><?= (int) scalar('SELECT COUNT(*) FROM documents WHERE type = ?', [$t['type_key']]) ?></td>
                    <td><?= $t['active'] ? '<span class="badge green">aktiv</span>' : '<span class="badge grey">inaktiv</span>' ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$list): ?><tr><td colspan="6" class="muted">Noch keine eigenen Vorlagen. Beispiele: Hausverbot, Besucherausweis, Dienstplan-Tauschantrag, Materialanforderung, Belehrung.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    $content = ob_get_clean();
    $title = 'Vorlagen-Editor';
    require SAMS_ROOT . '/app/layout.php';
    return;
}

$grantedRanks = $row && isset($row['type_key']) ? array_map('intval', array_column(all('SELECT rank_id FROM rank_permissions WHERE permission = ?', ['documents.issue.' . $row['type_key']]), 'rank_id')) : [];
if (is_post()) {
    $grantedRanks = array_map('intval', (array) ($_POST['ranks'] ?? []));
}
$fieldRow = function (array $f, $i) {
    $opts = implode("\n", array_keys($f['options'] ?? []));
    ob_start(); ?>
    <tr class="fe-row">
        <td class="fe-move"><button type="button" class="btn-icon fe-up" title="nach oben">▲</button><button type="button" class="btn-icon fe-down" title="nach unten">▼</button></td>
        <td><input name="f[<?= $i ?>][label]" value="<?= e($f['label'] ?? '') ?>" placeholder="Bezeichnung"><input name="f[<?= $i ?>][name]" value="<?= e($f['name'] ?? '') ?>" placeholder="platzhalter" class="fe-name mono" title="Name für den Platzhalter {{name}}"></td>
        <td><select name="f[<?= $i ?>][type]" class="fe-type"><?php foreach (CUSTOM_FIELD_TYPES as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $f['type'] ?? 'text') ?>><?= e($l) ?></option><?php endforeach; ?></select></td>
        <td><select name="f[<?= $i ?>][width]"><?php foreach (CUSTOM_WIDTHS as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $f['width'] ?? 'full') ?>><?= e($l) ?></option><?php endforeach; ?></select></td>
        <td class="center"><input type="checkbox" name="f[<?= $i ?>][required]" value="1"<?= checked(!empty($f['required'])) ?> title="Pflichtfeld"></td>
        <td><textarea name="f[<?= $i ?>][options]" rows="2" placeholder="Optionen, eine pro Zeile" class="fe-opts"><?= e($opts) ?></textarea><input name="f[<?= $i ?>][placeholder]" value="<?= e($f['placeholder'] ?? '') ?>" placeholder="Hinweistext im Feld" class="fe-ph"></td>
        <td><button type="button" class="btn-icon fe-del" title="Feld entfernen">✕</button></td>
    </tr>
    <?php
    return ob_get_clean();
};

ob_start(); ?>
<div class="page-head">
    <div><h1><?= $row && isset($row['id']) ? 'Vorlage bearbeiten' : 'Neue Vorlage' ?></h1><p class="muted">Bereits ausgestellte Dokumente behalten ihr Aussehen – Änderungen gelten nur für neue Dokumente.</p></div>
    <a class="btn" href="<?= e(url('templates')) ?>">Zur Übersicht</a>
</div>
<?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
<form method="post" class="tpl-editor" action="<?= e(url('templates', $id ? ['id' => $id] : ['new' => 1])) ?>">
    <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $id ?>">
    <section class="card form-grid">
        <h2>Grunddaten</h2>
        <div class="field w-half"><label>Titel <span class="req">*</span></label><input name="title" value="<?= e($form['title']) ?>" required placeholder="z. B. Hausverbot"></div>
        <div class="field w-quarter"><label>Kurzname</label><input name="short" value="<?= e($form['short']) ?>" placeholder="für Listen"></div>
        <div class="field w-quarter"><label>Kürzel <span class="req">*</span></label><input name="code" value="<?= e($form['code']) ?>" maxlength="3" required placeholder="HV" class="mono"></div>
        <div class="field w-half"><label>Kategorie</label><input name="category" value="<?= e($form['category']) ?>" list="dl-tpl-cats"></div>
        <div class="field w-half"><label>Stempel</label><select name="seal"><?php foreach (CUSTOM_SEALS as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $form['seal']) ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
        <div class="field w-full"><span class="label">Layout</span><div class="choices"><?php foreach (CUSTOM_LAYOUTS as $k => $l): ?><label class="choice"><input type="radio" name="layout" value="<?= $k ?>"<?= checked($form['layout'] === $k) ?>> <?= e($l) ?></label><?php endforeach; ?></div></div>
        <div class="field w-half"><span class="label">Bezieht sich auf</span><div class="choices">
            <label class="choice"><input type="radio" name="subject" value="patient"<?= checked($form['subject'] === 'patient') ?>> Patient/in</label>
            <label class="choice"><input type="radio" name="subject" value="employee"<?= checked($form['subject'] === 'employee') ?>> Mitarbeiter/in (Personal)</label></div></div>
        <div class="field w-half"><label>Patientenakte</label><select name="patient_mode">
            <?php foreach (['required' => 'Pflicht', 'optional' => 'optional', 'none' => 'keine'] as $k => $l): ?><option value="<?= $k ?>"<?= selected($k, $form['patient_mode']) ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="field w-full"><label class="choice single"><input type="checkbox" name="active" value="1"<?= checked($form['active']) ?>> aktiv (kann ausgestellt werden)</label></div>
        <datalist id="dl-tpl-cats"><?php foreach (array_unique(array_column(document_types(), 'category')) as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
    </section>

    <section class="card">
        <h2>Felder</h2>
        <table class="list fe-table">
            <thead><tr><th></th><th>Bezeichnung / Platzhalter</th><th>Typ</th><th>Breite</th><th>Pflicht</th><th>Optionen / Hinweis</th><th></th></tr></thead>
            <tbody id="fe-body">
                <?php foreach ($fields as $i => $f): ?><?= $fieldRow($f, $i) ?><?php endforeach; ?>
            </tbody>
        </table>
        <template id="fe-template"><?= $fieldRow(['type' => 'text'], '__i__') ?></template>
        <button type="button" class="btn small" id="fe-add">+ Feld hinzufügen</button>
        <p class="hint">Ein Feld mit dem Platzhalter <code>empfaenger</code> wird beim Brief-Layout als Empfängeradresse verwendet. Felder, die nicht im Text vorkommen, erscheinen als Tabelle unter dem Text.</p>
    </section>

    <section class="card form-grid">
        <h2>Text</h2>
        <div class="field w-full"><textarea name="body" rows="10" placeholder="Hiermit wird {{patient.name}}, geb. {{patient.dob}}, ab sofort der Zutritt zu … untersagt. Grund: {{grund}}"><?= e($form['body']) ?></textarea>
            <div class="hint">Platzhalter: <span id="fe-ph-list"></span>
                <?php foreach (custom_placeholder_help() as $ph => $l): ?><code title="<?= e($l) ?>"><?= e($ph) ?></code> <?php endforeach; ?>
                · <code>**fett**</code></div></div>
    </section>

    <section class="card">
        <h2>Ausstellen dürfen</h2>
        <?php if (!can('ranks.manage')): ?><p class="hint">Ausstellrechte vergibt, wer Ränge verwalten darf (<em>Verwaltung → Ränge &amp; Rechte</em>, Recht „<?= e($form['title'] ?: 'Vorlage') ?> ausstellen“).</p><?php endif; ?>
        <div class="choices">
            <?php foreach ($ranks as $r): $can = can_manage_rank($r) || !empty($me['is_superadmin']); ?>
                <label class="choice"><input type="checkbox" name="ranks[]" value="<?= (int) $r['id'] ?>"<?= checked(in_array((int) $r['id'], $grantedRanks, true)) ?><?= $can ? '' : ' disabled' ?>> <?= e($r['name']) ?></label>
            <?php endforeach; ?>
        </div>
    </section>

    <div class="form-actions sticky">
        <button class="btn" name="action" value="preview" formnovalidate>Vorschau</button>
        <button class="btn primary" name="action" value="save">Speichern</button>
    </div>
</form>
<?php if ($preview): ?>
<section class="preview" id="preview"><h2>Vorschau (mit Beispieldaten)</h2><div class="doc-stage"><?= $preview ?></div></section>
<script>document.getElementById('preview').scrollIntoView({behavior: 'smooth'});</script>
<?php endif; ?>
<script>
(function () {
    var body = document.getElementById('fe-body'), tpl = document.getElementById('fe-template'), n = body.children.length + 1000;
    function sync() {
        body.querySelectorAll('.fe-row').forEach(function (tr) {
            var t = tr.querySelector('.fe-type').value;
            tr.querySelector('.fe-opts').hidden = ['select', 'radio', 'checkboxes'].indexOf(t) < 0;
            tr.querySelector('.fe-ph').hidden = ['text', 'textarea', 'number'].indexOf(t) < 0;
            tr.querySelector('.fe-name').hidden = t === 'heading';
        });
        var names = Array.prototype.map.call(body.querySelectorAll('.fe-row'), function (tr) {
            var t = tr.querySelector('.fe-type').value, nm = tr.querySelector('.fe-name').value || slug(tr.querySelector('input').value);
            return t === 'heading' || t === 'signature' || !nm ? '' : '<code>{{' + nm + '}}</code> ';
        });
        document.getElementById('fe-ph-list').innerHTML = names.join('');
    }
    function slug(s) { return s.toLowerCase().replace(/ä/g, 'ae').replace(/ö/g, 'oe').replace(/ü/g, 'ue').replace(/ß/g, 'ss').replace(/[^a-z0-9]+/g, '_').replace(/^_|_$/g, ''); }
    document.getElementById('fe-add').addEventListener('click', function () {
        body.insertAdjacentHTML('beforeend', tpl.innerHTML.replace(/__i__/g, String(n++)));
        sync();
        body.lastElementChild.querySelector('input').focus();
    });
    body.addEventListener('click', function (e) {
        var tr = e.target.closest('.fe-row');
        if (!tr) { return; }
        if (e.target.classList.contains('fe-del')) { tr.remove(); }
        if (e.target.classList.contains('fe-up') && tr.previousElementSibling) { tr.parentNode.insertBefore(tr, tr.previousElementSibling); }
        if (e.target.classList.contains('fe-down') && tr.nextElementSibling) { tr.parentNode.insertBefore(tr.nextElementSibling, tr); }
        sync();
    });
    body.addEventListener('change', sync);
    body.addEventListener('input', function (e) {
        var tr = e.target.closest('.fe-row');
        if (tr && e.target === tr.querySelector('input') && !tr.querySelector('.fe-name').dataset.touched) { tr.querySelector('.fe-name').value = slug(e.target.value); sync(); }
        if (e.target.classList.contains('fe-name')) { e.target.dataset.touched = '1'; }
    });
    body.querySelectorAll('.fe-name').forEach(function (i) { if (i.value) { i.dataset.touched = '1'; } });
    if (!body.children.length) { document.getElementById('fe-add').click(); }
    sync();
})();
</script>
<?php
$content = ob_get_clean();
$title = 'Vorlagen-Editor';
$withDocumentCss = true;
$scripts = ['assets/vendor/qrcode.js', 'assets/js/document.js'];
require SAMS_ROOT . '/app/layout.php';
