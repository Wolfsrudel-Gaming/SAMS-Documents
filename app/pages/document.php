<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$id = (int) ($_GET['id'] ?? 0);
$doc = one('SELECT d.*, u.name AS revoker FROM documents d LEFT JOIN users u ON u.id = d.revoked_by WHERE d.id = ? AND ' . fw('d'), [$id]);
if (!$doc) {
    not_found('Dokument nicht gefunden.');
}
$type = doc_type($doc['type']);

if (is_post() && post('action') === 'revoke') {
    csrf_check();
    $reason = mb_substr((string) post('reason'), 0, 1000);
    if (!can_revoke($doc)) {
        forbidden('Dein Rang darf dieses Dokument nicht stornieren.');
    }
    if ($reason === '') {
        flash('error', 'Bitte einen Stornierungsgrund angeben.');
    } elseif ($doc['status'] !== 'revoked') {
        q('UPDATE documents SET status = ?, revoked_reason = ?, revoked_by = ?, revoked_at = ? WHERE id = ?', ['revoked', $reason, $me['id'], now(), $id]);
        audit('document.revoke', 'document', $id, $reason);
        wanted_close_for_document($id, 'Dokument storniert: ' . $reason);
        game_bill_cancel_for($id);
        fitness_revoke_for($id);
        notify_webhook('documents', log_embed('🚫 ' . ($type['title'] ?? 'Dokument') . ' storniert', '', 0xb3261e, [
            ['name' => 'Nummer', 'value' => $doc['number'], 'inline' => true], ['name' => 'Storniert von', 'value' => user_display($me), 'inline' => true]]));
        flash('success', 'Dokument wurde storniert. Die Echtheitsprüfung zeigt es ab sofort als ungültig an.');
    }
    redirect(url('document', ['id' => $id]));
}

if (is_post() && post('action') === 'pay_status') {
    csrf_check();
    require_perm('invoices.manage');
    if ($doc['type'] !== 'rechnung' || $doc['status'] === 'revoked') {
        forbidden('Nur für gültige Rechnungen.');
    }
    $to = (string) post('to');
    $note = mb_substr(trim((string) post('pay_note')), 0, 190);
    $row = match ($to) {
        'paid' => ['pay_status' => 'paid', 'paid_at' => now()],
        'dunned' => ['pay_status' => 'dunned', 'dunning_level' => min(3, (int) $doc['dunning_level'] + 1), 'dunned_at' => now()],
        'open' => ['pay_status' => 'open', 'paid_at' => null],
        default => null,
    };
    if ($row) {
        update('documents', $id, $row + ['pay_note' => $note]);
        if ($to === 'paid') {
            game_bill_cancel_for($id); // außerhalb des Spiels bezahlt: offene Rechnung im Spiel zurückziehen
        }
        $label = $to === 'dunned' ? $row['dunning_level'] . '. Mahnung' : PAY_STATUS[$to][1];
        audit('invoice.status', 'document', $id, $doc['number'] . ' → ' . $label . ($note !== '' ? ' (' . $note . ')' : ''));
        flash('success', 'Rechnung: ' . $label . '.' . ($to === 'dunned' ? ' Die Mahnung kann jetzt gedruckt oder im Spiel übergeben werden.' : ''));
    }
    redirect(url('document', ['id' => $id]));
}

if (is_post() && post('action') === 'game_bill') {
    csrf_check();
    if (!game_bill_allowed($doc)) {
        forbidden();
    }
    if ($problem = game_bill_problem($doc)) {
        flash('error', $problem);
    } else {
        game_bill_send($doc, (int) $me['id']);
        flash('success', 'Die Rechnung wird ins Spiel gesendet. Die Person kann sie dort bezahlen; die Zahlung wird hier automatisch verbucht.');
    }
    redirect(url('document', ['id' => $id]));
}

// Ausweis mit Foto-URL: Bild nachträglich herunterladen und fest im Dokument speichern
if (is_post() && post('action') === 'embed_photo') {
    csrf_check();
    require_perm('documents.issue.' . $doc['type']);
    $raw = json_decode((string) $doc['data'], true) ?: [];
    $photoUrl = (string) ($raw['photo'] ?? '');
    @unlink(storage_path('photos/' . sha1($photoUrl) . '.img.fail')); // sofort neu versuchen
    $img = preg_match('#^https?://#i', $photoUrl) && empty($doc['finalized_at']) ? remote_image_data_uri($photoUrl) : '';
    if ($img !== '') {
        $raw['photo_file'] = $img;
        update('documents', $id, ['data' => json_encode($raw, JSON_UNESCAPED_UNICODE)]);
        audit('document.photo_embedded', 'document', $id, $doc['number']);
        flash('success', 'Foto heruntergeladen und im Ausweis gespeichert.');
    } else {
        flash('error', 'Foto konnte nicht geladen werden: ' . (remote_image_error($photoUrl) ?: 'keine gültige Adresse'));
    }
    redirect(url('document', ['id' => $id]));
}

if (is_post() && in_array(post('action'), ['ic_sign', 'finalize'], true)) {
    csrf_check();
    if (post('action') === 'finalize') {
        if (!doc_can_finalize($doc)) {
            forbidden('Abschließen darf die ausstellende Person oder die Leitung.');
        }
        $err = doc_finalize($doc, (int) $me['id'], trim('im System von ' . $me['name'] . ' ' . (string) post('note')));
        flash($err ? 'error' : 'success', $err ?: 'Dokument abgeschlossen. Es kann nicht mehr unterschrieben oder geändert werden.');
    } else {
        $err = doc_sign($doc, (string) post('field'), (string) post('signature'), ['name' => (string) post('signer_name'), 'citizen_id' => (string) post('signer_citizen_id'),
            'source' => 'web', 'user_id' => (int) $me['id']]);
        flash($err ? 'error' : 'success', $err ?: 'Unterschrift gespeichert.');
    }
    redirect(url('document', ['id' => $id]) . '#signatures');
}

if (is_post() && in_array(post('action'), ['share', 'share_revoke'], true)) {
    csrf_check();
    require_perm('documents.share');
    if (post('action') === 'share_revoke') {
        $sh = one('SELECT * FROM document_shares WHERE id = ? AND document_id = ?', [(int) post('share_id'), $id]);
        if ($sh && !$sh['revoked']) {
            q('UPDATE document_shares SET revoked = 1 WHERE id = ?', [$sh['id']]);
            audit('document.share_revoke', 'document', $id, $doc['number'] . ' Freigabe #' . $sh['id']);
            flash('success', 'Freigabe widerrufen.');
        }
        redirect(url('document', ['id' => $id]) . '#shares');
    }
    $kind = in_array(post('kind'), ['user', 'faction'], true) ? (string) post('kind') : 'link';
    $targetFaction = $kind === 'faction' ? faction_by_id((int) post('target_faction_id')) : null;
    $agency = array_key_exists((string) post('agency'), SHARE_AGENCIES) ? (string) post('agency') : 'other';
    $hours = array_key_exists((string) post('hours'), SHARE_DURATIONS) ? (int) post('hours') : 168;
    $target = $kind === 'user' ? one('SELECT * FROM users WHERE id = ? AND active = 1', [(int) post('user_id')]) : null;
    if ($doc['status'] === 'revoked') {
        flash('error', 'Stornierte Dokumente können nicht freigegeben werden.');
    } elseif ($kind === 'user' && (!$target || ((int) $target['faction_id'] === fid() && !in_array('documents.shared_view', user_permissions($target), true)))) {
        flash('error', 'Bitte ein Konto wählen, das freigegebene Dokumente ansehen darf.');
    } elseif ($kind === 'faction' && (!$targetFaction || (int) $targetFaction['id'] === fid() || !$targetFaction['active'])) {
        flash('error', 'Bitte eine andere Behörde wählen.');
    } elseif ($kind === 'faction') {
        $hoursF = array_key_exists((string) post('hours'), SHARE_DURATIONS) ? (int) post('hours') : 168;
        insert('document_shares', ['document_id' => $id, 'kind' => 'faction', 'target_faction_id' => $targetFaction['id'], 'agency' => (string) $targetFaction['code'],
            'recipient' => mb_substr(trim((string) post('recipient')) ?: $targetFaction['name'], 0, 190), 'purpose' => mb_substr(trim((string) post('purpose')), 0, 190),
            'expires_at' => $hoursF > 0 ? date('Y-m-d H:i:s', time() + $hoursF * 3600) : null, 'created_by' => $me['id'], 'created_at' => now()]);
        audit('document.share', 'document', $id, $doc['number'] . ' → ' . $targetFaction['name']);
        $prev = fid();
        set_faction_context((int) $targetFaction['id']);
        notify_users_with('documents.view', 'share', 'Dokument von ' . (faction_by_id($prev)['short'] ?? '') . ' freigegeben: ' . ($type['title'] ?? 'Dokument') . ' ' . $doc['number'], url('shared', ['id' => $id]));
        set_faction_context($prev);
        flash('success', 'Dokument für ' . $targetFaction['name'] . ' freigegeben.');
    } else {
        $recipient = trim((string) post('recipient')) ?: ($target ? user_display($target) : '');
        $token = create_share($doc, $kind, $target ? (int) $target['id'] : null, $agency, $recipient, trim((string) post('purpose')), $hours);
        if ($target) {
            notify_user((int) $target['id'], 'share', 'Dokument für dich freigegeben: ' . ($type['title'] ?? 'Dokument') . ' ' . $doc['number'], url('shared', ['id' => $id]));
        }
        if ($token) {
            $_SESSION['new_share_url'] = share_url($token);
        }
        flash('success', $kind === 'link' ? 'Freigabe-Link erstellt. Er wird nur jetzt angezeigt – bitte kopieren.' : 'Dokument für ' . $recipient . ' freigegeben.');
    }
    redirect(url('document', ['id' => $id]) . '#shares');
}

if (is_post() && post('action') === 'rx_redeem' && $doc['type'] === 'rezept') {
    csrf_check();
    require_perm('stock.dispense');
    if ($problem = rx_problem($doc)) {
        flash('error', $problem);
    } else {
        [$given, $notes] = rx_redeem($doc, 'web', (int) $me['id']);
        if ($given) {
            flash('success', 'Ausgegeben: ' . implode(', ', array_map(fn($g) => $g['qty'] . '× ' . $g['name'], $given)) . '.');
        }
        foreach ($notes as $n) {
            flash('warn', $n);
        }
        if (!$given && !$notes) {
            flash('warn', 'Alle Positionen sind bereits eingelöst.');
        }
    }
    redirect(url('document', ['id' => $id]));
}

if (is_post() && post('action') === 'addendum') {
    csrf_check();
    if (!can_add_addendum($doc)) {
        forbidden('Du darfst zu diesem Dokument keinen Nachtrag schreiben.');
    }
    $text = mb_substr(trim((string) post('text')), 0, 10000);
    if ($text === '') {
        flash('error', 'Der Nachtrag ist leer.');
    } else {
        insert('document_addenda', ['document_id' => $id, 'text' => $text, 'author_snapshot' => json_encode(issuer_snapshot($me), JSON_UNESCAPED_UNICODE),
            'created_by' => $me['id'], 'created_at' => now()]);
        audit('document.addendum', 'document', $id, $doc['number'] . ': ' . mb_strimwidth($text, 0, 120, '…'));
        flash('success', 'Nachtrag gespeichert. Er erscheint als zusätzliches Blatt am Dokument und in der Echtheitsprüfung.');
    }
    redirect(url('document', ['id' => $id]) . '#addenda');
}

if (is_post() && in_array(post('action'), ['print', 'cancel_job'], true)) {
    csrf_check();
    if (post('action') === 'cancel_job') {
        $job = one('SELECT * FROM print_jobs WHERE id = ? AND document_id = ?', [(int) post('job_id'), $id]);
        if ($job && $job['status'] === 'queued' && ((int) $job['requested_by'] === (int) $me['id'] || can('printers.manage'))) {
            q("UPDATE print_jobs SET status = 'cancelled' WHERE id = ?", [$job['id']]);
            audit('document.print_cancel', 'document', $id, 'Auftrag #' . $job['id']);
            flash('success', 'Druckauftrag abgebrochen.');
        }
        redirect(url('document', ['id' => $id]) . '#print');
    }
    if (!can('documents.print')) {
        forbidden('Dein Rang darf keine Dokumente im Spiel drucken.');
    }
    if ($doc['status'] === 'revoked') {
        flash('error', 'Stornierte Dokumente können nicht gedruckt werden.');
        redirect(url('document', ['id' => $id]));
    }
    $printer = one('SELECT * FROM printers WHERE id = ? AND active = 1 AND ' . fw(), [(int) post('printer_id')]);
    if (!$printer) {
        flash('error', 'Bitte einen aktiven Drucker wählen.');
        redirect(url('document', ['id' => $id]) . '#print');
    }
    $created = 0;
    foreach (print_variants($doc['type']) as $variant => $label) {
        $copies = max(0, min(5, (int) ($_POST['copies'][$variant === '' ? 'doc' : $variant] ?? 0)));
        if ($copies > 0) {
            insert('print_jobs', ['document_id' => $id, 'printer_id' => $printer['id'], 'copy_variant' => $variant, 'copies' => $copies,
                'status' => 'queued', 'requested_by' => $me['id'], 'requested_at' => now()]);
            $created += $copies;
        }
    }
    if ($created === 0) {
        flash('error', 'Bitte mindestens ein Exemplar wählen.');
        redirect(url('document', ['id' => $id]) . '#print');
    }
    audit('document.print', 'document', $id, $doc['number'] . ' → ' . $printer['name'] . " ($created Expl.)");
    flash('success', "Druckauftrag gesendet: $created Exemplar(e) an „{$printer['name']}“. Hol das Dokument am Drucker ab.");
    $_SESSION['game_notify'] = ['type' => 'printQueued', 'payload' => ['printer' => $printer['name'], 'x' => (float) $printer['x'], 'y' => (float) $printer['y'], 'z' => (float) $printer['z'], 'count' => $created]];
    redirect(url('document', ['id' => $id]));
}

audit('document.view', 'document', $id, $doc['number']);
$doc['data'] = json_decode((string) $doc['data'], true) ?: [];
$doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
$doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
$copy = (string) ($_GET['copy'] ?? '');
$html = render_document($doc, ['copy' => $copy]);
$printers = active_printers();
if (multi_server()) {
    // Drucker des Staats, in dem man gerade im Spiel ist, zuerst
    $myServer = game_server_for_user((int) $me['id']);
    usort($printers, fn($a, $b) => [(int) ($a['server_id'] ?? 0) !== $myServer, $a['name']] <=> [(int) ($b['server_id'] ?? 0) !== $myServer, $b['name']]);
}
$jobs = all('SELECT j.*, p.name AS printer, u.name AS requester FROM print_jobs j JOIN printers p ON p.id = j.printer_id
    LEFT JOIN users u ON u.id = j.requested_by WHERE j.document_id = ? ORDER BY j.id DESC LIMIT 20', [$id]);
$gameNotify = $_SESSION['game_notify'] ?? null;
unset($_SESSION['game_notify']);
$fileName = preg_replace('/[^A-Za-z0-9\-]/', '_', $doc['number'] . '_' . ($doc['patient_snapshot']['last_name'] ?? ''));

ob_start(); ?>
<div class="page-head no-print">
    <div>
        <h1><?= e($type['title'] ?? $doc['type']) ?> <?= doc_status_badge($doc) ?> <?= pay_status_badge($doc) ?></h1>
        <p class="muted mono"><?= e($doc['number']) ?> · Prüfcode <?= e($doc['verify_code']) ?> · ausgestellt <?= e(fmt_datetime($doc['issued_at'])) ?> von <?= e(user_display($doc['issuer_snapshot'])) ?></p>
    </div>
    <div class="btn-row">
        <?php if (can('documents.print') && $doc['status'] !== 'revoked'): ?><a class="btn primary" href="#print"><?= icon('print') ?>Im Spiel drucken</a><?php endif; ?>
        <button class="btn" onclick="window.print()" data-browser-only>Drucken / PDF</button>
        <button class="btn" data-export-png="<?= e($fileName) ?>">Als Bild speichern</button>
        <button class="btn" data-copy="<?= e(verify_url($doc['verify_code'])) ?>">Prüflink kopieren</button>
        <?php if (can('documents.issue.' . $doc['type'])): ?><a class="btn" href="<?= e(url('document_new', ['type' => $doc['type'], 'from' => $id])) ?>">Als Vorlage verwenden</a><?php endif; ?>
        <?php if ($doc['patient_id']): ?><a class="btn" href="<?= e(url('patient', ['id' => $doc['patient_id']])) ?>">Zur Akte</a><?php endif; ?>
        <?php if (!empty($doc['case_id'])): ?><a class="btn" href="<?= e(url('case', ['id' => $doc['case_id']])) ?>">Zum Fall</a><?php endif; ?>
    </div>
</div>

<?php if ($doc['type'] === 'au'): ?>
<div class="tabs no-print copies">
    <?php foreach (['' => 'Alle Ausfertigungen', 'kasse' => 'Krankenkasse', 'arbeitgeber' => 'Arbeitgeber (ohne Diagnose)', 'versicherter' => 'Versicherte/r'] as $k => $l): ?>
        <a class="<?= $copy === $k ? 'active' : '' ?>" href="<?= e(url('document', ['id' => $id, 'copy' => $k ?: null])) ?>"><?= e($l) ?></a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($doc['status'] === 'revoked'): ?>
    <div class="flash error no-print"><strong>Storniert</strong> am <?= e(fmt_datetime($doc['revoked_at'])) ?> durch <?= e($doc['revoker']) ?>: <?= e($doc['revoked_reason']) ?></div>
<?php endif; ?>

<?php if ($doc['type'] === 'rechnung' && $doc['status'] !== 'revoked' && can('invoices.manage')): ?>
<section class="card no-print pay-card">
    <form method="post" class="inline-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="pay_status">
        <span><strong>Zahlung:</strong> <?= pay_status_badge($doc) ?> · <?= e(money($doc['amount_cents'] / 100)) ?>
            <?= $doc['pay_status'] === 'paid' ? ' · eingegangen am ' . e(fmt_date($doc['paid_at'])) : ' · fällig am ' . e(fmt_date(invoice_due($doc, $doc['data']))) ?>
            <?= $doc['pay_note'] ? ' · <span class="muted">' . e($doc['pay_note']) . '</span>' : '' ?></span>
        <input name="pay_note" maxlength="190" placeholder="Notiz (z. B. bar bei Entlassung)">
        <?php if ($doc['pay_status'] !== 'paid'): ?>
            <button class="btn small primary" name="to" value="paid">Als bezahlt markieren</button>
            <?php if ((int) $doc['dunning_level'] < 3): ?><button class="btn small" name="to" value="dunned" onclick="return confirm('<?= (int) $doc['dunning_level'] + 1 ?>. Mahnung erstellen?')"><?= (int) $doc['dunning_level'] + 1 ?>. Mahnung</button><?php endif; ?>
        <?php else: ?>
            <button class="btn small" name="to" value="open">Zahlung zurücknehmen</button>
        <?php endif; ?>
    </form>
    <?php $gameBill = game_bill_for($id); $gameProblem = game_bill_problem($doc); if ($gameBill || game_faction()['billing']): ?>
    <form method="post" class="inline-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="game_bill">
        <span><strong>Im Spiel:</strong> <?= $gameBill ? game_bill_badge($gameBill) . ' <small class="muted">' . e(fmt_datetime($gameBill['paid_at'] ?: ($gameBill['sent_at'] ?: $gameBill['created_at']))) . ($gameBill['status'] === 'failed' ? ' · ' . e($gameBill['error']) : '') . '</small>' : '<span class="muted">noch nicht gesendet</span>' ?></span>
        <?php if (!$gameProblem): ?><button class="btn small"><?= icon('external') ?>Ins Spiel senden</button>
        <?php elseif (!$gameBill || !in_array($gameBill['status'], ['pending', 'sent', 'cancel', 'paid'], true)): ?><small class="muted"><?= e($gameProblem) ?></small><?php endif; ?>
    </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($doc['type'] !== 'rechnung' && array_key_exists($doc['type'], GAME_BILL_TYPES) && $doc['status'] !== 'revoked' && game_bill_allowed($doc) && (game_faction()['billing'] || game_bill_for($id))):
    $gameBill = game_bill_for($id); $gameProblem = game_bill_problem($doc); ?>
<section class="card no-print pay-card">
    <form method="post" class="inline-form">
        <?= csrf_field() ?><input type="hidden" name="action" value="game_bill">
        <span><strong>Geldstrafe im Spiel:</strong> <?= e(money(game_bill_amount($doc))) ?> · <?= $gameBill ? game_bill_badge($gameBill) : '<span class="muted">noch nicht gesendet</span>' ?><?= $doc['pay_status'] === 'paid' ? ' · bezahlt am ' . e(fmt_date($doc['paid_at'])) : '' ?></span>
        <?php if (!$gameProblem): ?><button class="btn small"><?= icon('external') ?>Ins Spiel senden</button>
        <?php elseif (!$gameBill || !in_array($gameBill['status'], ['pending', 'sent', 'cancel', 'paid'], true)): ?><small class="muted"><?= e($gameProblem) ?></small><?php endif; ?>
    </form>
</section>
<?php endif; ?>

<?php if ($doc['type'] === 'rezept' && (can('stock.view') || can('stock.dispense'))): $rxLines = rx_lines($doc); $rxProblem = rx_problem($doc); ?>
<section class="card no-print rx-card">
    <div class="card-head"><h2>Einlösung</h2><?= $rxProblem ? '<span class="badge red">' . e($rxProblem) . '</span>' : '<span class="muted small">gültig bis ' . e(fmt_date(date('Y-m-d', strtotime($doc['issued_at'] . ' +' . rx_valid_days(($doc['data']['form'] ?? '') === 'btm') . ' days')))) . '</span>' ?></div>
    <table class="list compact">
        <tbody>
        <?php foreach ($rxLines as $l): ?>
            <tr><td><?= e($l['text']) ?><?= $l['qty'] > 1 ? ' <small class="muted">(' . $l['qty'] . ' Einheiten)</small>' : '' ?><br><small class="muted"><?= $l['med'] ? 'Sortiment: ' . e($l['med']['name']) . ' · Bestand ' . (int) $l['med']['stock'] : 'kein passender Artikel im Bestand' ?></small></td>
                <td class="right"><?php if ($l['redemption']): $r = $l['redemption']; ?><span class="badge green">eingelöst</span><br><small class="muted"><?= e(fmt_datetime($r['created_at'])) ?> · <?= $r['source'] === 'game' ? 'Apotheke (' . e($r['player']) . ')' : e((string) $r['user_name']) ?></small>
                    <?php else: ?><span class="badge <?= $l['status'] === 'ok' ? 'blue' : 'orange' ?>"><?= e(RX_STATUS[$l['status']]) ?></span><?php endif; ?></td></tr>
        <?php endforeach; ?>
        <?php if (!$rxLines): ?><tr><td class="muted">Keine Positionen.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <?php if (!$rxProblem && can('stock.dispense') && array_filter($rxLines, fn($l) => $l['status'] === 'ok')): ?>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="rx_redeem"><button class="btn primary">In der Klinik ausgeben</button>
            <span class="hint">bucht die einlösbaren Positionen aus dem Bestand aus</span></form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($doc['type'] === 'dienstausweis' && empty($doc['data']['photo_file']) && ($doc['data']['photo'] ?? '') !== '' && remote_image_error((string) $doc['data']['photo']) === '' && can('documents.issue.dienstausweis')): ?>
    <form method="post" class="flash warn no-print inline-form"><?= csrf_field() ?>Das Foto ist nur verlinkt. <button class="btn small" name="action" value="embed_photo">Foto herunterladen und im Ausweis speichern</button></form>
<?php endif; ?>
<?php if ($doc['type'] === 'dienstausweis' && empty($doc['data']['photo_file']) && ($photoUrl = (string) ($doc['data']['photo'] ?? '')) !== '' && ($photoErr = remote_image_error($photoUrl)) !== ''): ?>
    <div class="flash warn no-print">Das Foto konnte vom Server nicht geladen werden (<?= e($photoErr) ?>). Im Browser ist es evtl. sichtbar, beim „Als Bild speichern“ und im Spiel fehlt es dann.
        Am sichersten: Ausweis neu ausstellen und das Foto <strong>hochladen</strong> statt eine URL anzugeben<?= can('documents.issue.dienstausweis') ? ' – <a href="' . e(url('document_new', ['type' => 'dienstausweis', 'from' => $id])) . '">als Vorlage verwenden</a>' : '' ?>.
        <?php if (can('documents.issue.dienstausweis')): ?><form method="post" class="inline"><?= csrf_field() ?><button class="btn small" name="action" value="embed_photo">Erneut versuchen</button></form><?php endif; ?></div>
<?php endif; ?>
<div class="doc-stage" id="doc-stage"><?= $html ?></div>
<?php if ($doc['type'] === 'sv_untersuchung'): $cert = !empty($doc['data']['_certificate']) ? one('SELECT id, number FROM documents WHERE id = ?', [(int) $doc['data']['_certificate']]) : null; ?>
<div class="flash <?= $cert ? 'success' : 'warn' ?> no-print">Vertrauliche ärztliche Unterlage – nicht an die Dienststelle weitergeben.
    <?= $cert ? 'Die Bescheinigung ohne Befunde wurde automatisch ausgestellt: <a href="' . e(url('document', ['id' => $cert['id']])) . '">' . e($cert['number']) . '</a>.' : 'Keine Bescheinigung erzeugt: bitte mindestens eine Verwendung beurteilen.' ?></div>
<?php endif; ?>
<?php if ($doc['type'] === 'einsatztauglichkeit'): $sent = all('SELECT r.*, u.name, f.name AS faction FROM fitness_records r JOIN users u ON u.id = r.user_id JOIN factions f ON f.id = r.faction_id WHERE r.document_id = ?', [$id]); ?>
<div class="flash <?= $sent ? 'success' : 'warn' ?> no-print"><?php if ($sent): ?>Ergebnis (ohne Befunde) übermittelt an: <?= e(implode(', ', array_map(fn($r) => $r['faction'] . ' – ' . $r['name'] . ($r['revoked'] ? ' (zurückgezogen)' : ''), $sent))) ?>.
    <?php else: ?>Keiner Dienststelle zugeordnet: Weder Bürger-ID noch Name der Akte passen zu einem Konto einer Dienststelle mit Tauglichkeitsverwaltung. Die Bescheinigung kann unten manuell für die Behörde freigegeben werden.<?php endif; ?></div>
<?php endif; ?>

<?php if ($gameNotify): ?><span hidden data-game-notify="<?= e($gameNotify['type']) ?>" data-payload="<?= e(json_encode($gameNotify['payload'])) ?>"></span><?php endif; ?>

<?php $sign = doc_sign_state($doc); if ($sign['fields'] && ($doc['status'] !== 'revoked' || array_filter($sign['fields'], fn($f) => $f['signed']))): $pat = $doc['patient_snapshot']; ?>
<section class="card no-print" id="signatures">
    <div class="card-head"><h2>Unterschriften</h2>
        <?php if ($sign['final']): ?><span class="badge green">abgeschlossen <?= e(fmt_datetime($doc['finalized_at'])) ?></span><?= !$doc['final_hash'] || doc_hash_valid(one('SELECT * FROM documents WHERE id = ?', [$id])) ? '' : ' <span class="badge red">Prüfsumme weicht ab</span>' ?>
        <?php else: ?><span class="badge orange"><?= $sign['open'] ?> offen</span><?php endif; ?></div>
    <p class="hint"><?= $sign['receipt'] ? 'Diese Dokumentart hat kein eigenes Unterschriftsfeld. Die Empfänger/in kann den Empfang auf dem Ausdruck im Spiel oder hier bestätigen.' : 'Unterschriften werden auf dem Ausdruck im Spiel (Dokument benutzen → Unterschreiben) oder hier im Tablet geleistet.' ?>
        Jede Unterschrift ist sofort endgültig. Sind alle Felder unterschrieben, wird das Dokument automatisch abgeschlossen.</p>
    <table class="list compact">
        <tbody>
        <?php foreach ($sign['fields'] as $f): $l = $f['log']; ?>
            <tr><td><strong><?= e($f['label']) ?></strong></td>
                <td><?php if ($f['signed']): ?><img src="<?= e($doc['data'][$f['name']]) ?>" alt="" style="height:38px;vertical-align:middle">
                    <small class="muted"><?= $l ? e($l['signer_name']) . ' · ' . e(fmt_datetime($l['created_at'])) . ' · ' . ($l['source'] === 'game' ? 'im Spiel' . ($l['player'] ? ' (' . e($l['player']) . ')' : '') : 'Tablet/Browser') : 'beim Ausstellen' ?></small>
                <?php else: ?><span class="muted">offen</span><?php endif; ?></td>
                <td class="right"><?php if (!$f['signed'] && !$sign['final'] && $doc['status'] !== 'revoked'): ?><button type="button" class="btn small" onclick="document.getElementById('sigform-<?= e($f['name']) ?>').hidden = false; this.hidden = true">Hier unterschreiben lassen</button><?php endif; ?></td></tr>
            <?php if (!$f['signed'] && !$sign['final'] && $doc['status'] !== 'revoked'): ?>
            <tr id="sigform-<?= e($f['name']) ?>" hidden><td colspan="3">
                <form method="post" class="form-grid" onsubmit="if (!this.signature.value) { alert('Bitte zuerst unterschreiben.'); return false; } return confirm('Unterschrift endgültig speichern? Sie kann danach nicht mehr geändert werden.')">
                    <?= csrf_field() ?><input type="hidden" name="action" value="ic_sign"><input type="hidden" name="field" value="<?= e($f['name']) ?>">
                    <div class="field w-half"><label>Name der unterschreibenden Person</label><input name="signer_name" required maxlength="190" value="<?= e(str_contains($f['name'], 'patient') || $sign['receipt'] || str_contains($f['name'], 'betroffen') || str_contains($f['name'], 'mandant') ? patient_name($pat ?: null) : '') ?>"></div>
                    <div class="field w-half"><label>Bürger-ID (optional)</label><input name="signer_citizen_id" maxlength="64" value="<?= e($pat['citizen_id'] ?? '') ?>"></div>
                    <div class="field w-full"><div class="sigpad" data-input="sig-<?= e($f['name']) ?>"><canvas width="600" height="180"></canvas><button type="button" class="btn small sig-clear">Löschen</button></div>
                        <input type="hidden" id="sig-<?= e($f['name']) ?>" name="signature"></div>
                    <div class="form-actions"><button class="btn primary">Unterschrift speichern</button></div>
                </form></td></tr>
            <?php endif; ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php if (!$sign['final'] && $doc['status'] !== 'revoked' && doc_can_finalize($doc)): ?>
    <form method="post" class="inline-form" onsubmit="return confirm('Dokument abschließen? Offene Unterschriftsfelder bleiben leer, das Dokument ist danach endgültig.')"><?= csrf_field() ?><input type="hidden" name="action" value="finalize">
        <input name="note" maxlength="120" placeholder="Vermerk (z. B. Unterschrift verweigert)"><button class="btn small">Abschließen</button></form>
    <?php elseif ($sign['final']): ?>
        <p class="small muted">Abgeschlossen: <?= e($doc['finalized_note']) ?><?= $doc['final_hash'] ? ' · Prüfsumme <span class="mono">' . e(substr($doc['final_hash'], 0, 16)) . '…</span>' : '' ?></p>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (can('documents.print')): $variants = print_variants($doc['type']); $statusLabels = ['queued' => ['orange', 'wartet am Drucker'], 'printed' => ['green', 'abgeholt'], 'cancelled' => ['grey', 'abgebrochen']]; ?>
<section class="card no-print" id="print">
    <h2>Im Spiel drucken</h2>
    <?php if ($doc['status'] === 'revoked'): ?>
        <p class="muted">Stornierte Dokumente können nicht gedruckt werden.</p>
    <?php elseif (!$printers): ?>
        <p class="muted">Es sind noch keine Drucker eingerichtet.<?php if (can('printers.manage')): ?> <a href="<?= e(url('printers')) ?>">Drucker anlegen</a><?php endif; ?></p>
    <?php else: ?>
        <form method="post" class="form-grid print-form">
            <?= csrf_field() ?><input type="hidden" name="action" value="print">
            <div class="field w-half"><label>Drucker</label>
                <select name="printer_id" required>
                    <?php foreach ($printers as $pr): ?><option value="<?= (int) $pr['id'] ?>"><?= e(printer_label($pr)) ?><?= $pr['location'] ? ' – ' . e($pr['location']) : '' ?></option><?php endforeach; ?>
                </select></div>
            <div class="field w-half"><span class="label">Exemplare</span>
                <div class="copies-grid">
                    <?php foreach ($variants as $vk => $vl): ?>
                        <label><span><?= e($vl) ?></span><input type="number" name="copies[<?= e($vk === '' ? 'doc' : $vk) ?>]" value="<?= $doc['type'] === 'au' ? ($vk === 'arbeitgeber' || $vk === 'versicherter' ? 1 : 0) : 1 ?>" min="0" max="5"></label>
                    <?php endforeach; ?>
                </div></div>
            <p class="hint w-full">Das Dokument wird am Drucker als Item ausgegeben: Dort hingehen, Drucker benutzen und den Auftrag abholen. Das Item lässt sich ansehen, zeigen und weitergeben.</p>
            <div class="form-actions"><button class="btn primary"><?= icon('print') ?>Druckauftrag senden</button></div>
        </form>
    <?php endif; ?>
    <?php if ($jobs): ?>
        <h3 class="form-heading" style="margin-top:.8rem">Druckaufträge</h3>
        <table class="list compact">
            <thead><tr><th>Zeitpunkt</th><th>Drucker</th><th>Exemplar</th><th>Auftraggeber</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($jobs as $j): [$sc, $sl] = $statusLabels[$j['status']] ?? ['grey', $j['status']]; ?>
                <tr><td class="small"><?= e(fmt_datetime($j['requested_at'])) ?></td><td><?= e($j['printer']) ?></td>
                    <td class="small"><?= e(($variants[$j['copy_variant']] ?? 'Dokument') . ' × ' . $j['copies']) ?></td>
                    <td class="small"><?= e($j['requester']) ?></td>
                    <td><span class="badge <?= $sc ?>"><?= e($sl) ?></span><?= $j['status'] === 'printed' ? ' <small class="muted">' . e(fmt_datetime($j['picked_up_at'])) . ' · ' . e($j['picked_up_by']) . '</small>' : '' ?></td>
                    <td class="right"><?php if ($j['status'] === 'queued' && ((int) $j['requested_by'] === (int) $me['id'] || can('printers.manage'))): ?>
                        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="cancel_job"><input type="hidden" name="job_id" value="<?= (int) $j['id'] ?>"><button class="btn small">Abbrechen</button></form>
                    <?php endif; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php $addenda = document_addenda($id); if ($addenda || can_add_addendum($doc)): ?>
<section class="card no-print" id="addenda">
    <h2>Nachträge (<?= count($addenda) ?>)</h2>
    <div class="addenda-list">
        <?php foreach ($addenda as $i => $a): ?>
            <div class="addendum"><strong>Nachtrag Nr. <?= $i + 1 ?></strong> · <?= e(fmt_datetime($a['created_at'])) ?> · <?= e(user_display($a['author'])) ?><div><?= nl($a['text']) ?></div></div>
        <?php endforeach; ?>
    </div>
    <?php if (can_add_addendum($doc)): ?>
        <form method="post" class="form-grid">
            <?= csrf_field() ?><input type="hidden" name="action" value="addendum">
            <div class="field w-full"><label>Neuer Nachtrag</label><textarea name="text" rows="3" required placeholder="z. B. Berichtigung: Die Diagnose lautet korrekt S52.5 links (nicht rechts)."></textarea>
                <div class="hint">Ausgestellte Dokumente werden nie verändert. Ein Nachtrag wird als zusätzliches Blatt angehängt und kann nicht gelöscht werden.</div></div>
            <div class="form-actions"><button class="btn" onclick="return confirm('Nachtrag verbindlich hinzufügen?')">Nachtrag hinzufügen</button></div>
        </form>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if (can('documents.share')):
    $shares = all('SELECT s.*, u.name AS user_name, c.name AS creator FROM document_shares s LEFT JOIN users u ON u.id = s.user_id
        LEFT JOIN users c ON c.id = s.created_by WHERE s.document_id = ? ORDER BY s.id DESC', [$id]);
    $shareAccounts = array_values(array_filter(all('SELECT * FROM users WHERE active = 1 AND ' . fw() . ' ORDER BY name'), fn($u) => in_array('documents.shared_view', user_permissions($u), true)));
    $shareFactions = other_factions();
    $newShareUrl = $_SESSION['new_share_url'] ?? null;
    unset($_SESSION['new_share_url']); ?>
<section class="card no-print" id="shares">
    <h2>Freigabe für Behörden</h2>
    <?php if ($newShareUrl): ?>
        <div class="flash success share-new"><strong>Freigabe-Link:</strong>
            <input readonly value="<?= e($newShareUrl) ?>" onclick="this.select()"> <button type="button" class="btn small" data-copy="<?= e($newShareUrl) ?>">Kopieren</button></div>
    <?php endif; ?>
    <?php if ($shares): ?>
    <table class="list compact">
        <thead><tr><th>Empfänger</th><th>Art</th><th>Gültig bis</th><th class="right">Abrufe</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($shares as $sh): $active = share_active($sh); ?>
            <tr><td><strong><?= e($sh['kind'] === 'faction' ? (faction_by_id((int) $sh['target_faction_id'])['name'] ?? $sh['recipient']) : (SHARE_AGENCIES[$sh['agency']] ?? $sh['agency'])) ?></strong><?= $sh['recipient'] ? '<br><small>' . e($sh['recipient']) . '</small>' : '' ?><?= $sh['purpose'] ? '<br><small class="muted">' . e($sh['purpose']) . '</small>' : '' ?></td>
                <td class="small"><?= $sh['kind'] === 'user' ? 'Konto ' . e($sh['user_name']) : ($sh['kind'] === 'faction' ? 'Behörde' : 'Link') ?><br><span class="muted">von <?= e($sh['creator']) ?>, <?= e(fmt_datetime($sh['created_at'])) ?></span></td>
                <td class="small"><?= $sh['expires_at'] ? e(fmt_datetime($sh['expires_at'])) : 'unbegrenzt' ?></td>
                <td class="right small"><?= (int) $sh['views'] ?><?= $sh['last_viewed_at'] ? '<br><span class="muted">' . e(fmt_datetime($sh['last_viewed_at'])) . '</span>' : '' ?></td>
                <td><?= $active ? '<span class="badge green">aktiv</span>' : '<span class="badge grey">' . ($sh['revoked'] ? 'widerrufen' : 'abgelaufen') . '</span>' ?></td>
                <td class="right"><?php if ($active): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="share_revoke"><input type="hidden" name="share_id" value="<?= (int) $sh['id'] ?>"><button class="btn small">Widerrufen</button></form><?php endif; ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <?php if ($doc['status'] !== 'revoked'): ?>
    <details<?= $shares ? '' : ' open' ?>>
        <summary>Neue Freigabe</summary>
        <form method="post" class="form-grid">
            <?= csrf_field() ?><input type="hidden" name="action" value="share">
            <div class="field w-full"><div class="choices">
                <?php if ($shareFactions): ?><label class="choice"><input type="radio" name="kind" value="faction" checked> An eine Behörde im System (alle Berechtigten dort)</label><?php endif; ?>
                <label class="choice"><input type="radio" name="kind" value="link"<?= $shareFactions ? '' : ' checked' ?>> Geheimer Link (z. B. für Ermittlungsakte)</label>
                <label class="choice"><input type="radio" name="kind" value="user"<?= $shareAccounts ? '' : ' disabled' ?>> An ein Behördenkonto<?= $shareAccounts ? '' : ' (keine Konten mit dem Recht „Freigegebene Dokumente ansehen“)' ?></label>
            </div></div>
            <?php if ($shareFactions): ?><div class="field w-half"><label>Behörde (bei „An eine Behörde“)</label><select name="target_faction_id"><?php foreach ($shareFactions as $sf): ?><option value="<?= (int) $sf['id'] ?>"><?= e($sf['name']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
            <div class="field w-half"><label>Stelle (bei Link/Konto)</label><select name="agency"><?php foreach (SHARE_AGENCIES as $k => $l): ?><option value="<?= e($k) ?>"><?= e($l) ?></option><?php endforeach; ?></select></div>
            <div class="field w-half"><label>Gültigkeit</label><select name="hours"><?php foreach (SHARE_DURATIONS as $k => $l): ?><option value="<?= e($k) ?>"<?= selected($k, '168') ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
            <?php if ($shareAccounts): ?><div class="field w-half"><label>Konto (nur bei „An ein Behördenkonto“)</label><select name="user_id"><option value="">–</option><?php foreach ($shareAccounts as $u): ?><option value="<?= (int) $u['id'] ?>"><?= e(user_display($u)) ?></option><?php endforeach; ?></select></div><?php endif; ?>
            <div class="field w-half"><label>Empfänger/in</label><input name="recipient" maxlength="190" placeholder="z. B. Detective J. Ramirez, Badge 4521"></div>
            <div class="field w-full"><label>Zweck / Aktenzeichen</label><input name="purpose" maxlength="190" placeholder="z. B. Ermittlungsverfahren LSPD-2026-0815, Schweigepflichtentbindung liegt vor"></div>
            <p class="hint w-full">Der Empfänger sieht das vollständige Dokument inkl. medizinischer Angaben. Nur mit Schweigepflichtentbindung oder richterlicher Anordnung freigeben. Jeder Abruf wird protokolliert.</p>
            <div class="form-actions"><button class="btn primary">Freigeben</button></div>
        </form>
    </details>
    <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($doc['status'] !== 'revoked' && can_revoke($doc)): ?>
<details class="card no-print revoke">
    <summary>Dokument stornieren</summary>
    <form method="post" class="form-grid">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="revoke">
        <div class="field w-full"><label>Grund der Stornierung <span class="req">*</span></label><input name="reason" required placeholder="z.B. Fehlerhafte Angaben, ersetzt durch neues Dokument"></div>
        <div class="form-actions"><button class="btn danger" onclick="return confirm('Dokument wirklich stornieren? Dies kann nicht rückgängig gemacht werden.')">Stornieren</button></div>
    </form>
</details>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = ($type['short'] ?? 'Dokument') . ' ' . $doc['number'];
$withDocumentCss = true;
$scripts = ['assets/vendor/qrcode.js', 'assets/vendor/html2canvas.min.js', 'assets/js/document.js'];
require SAMS_ROOT . '/app/layout.php';
