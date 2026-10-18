<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Unterschriften im Spiel (IC). Unterschriftsfelder bleiben beim Ausstellen leer, wenn die Person nicht am Gerät
 * unterschreibt. Danach unterschreibt sie auf dem Ausdruck (Dokument-Item im Spiel) oder im Tablet. Eine Unterschrift
 * ist sofort endgültig. Mit dem Abschluss (automatisch, sobald alles unterschrieben ist) wird das ganze Dokument
 * gesperrt und eine Prüfsumme gespeichert – ab dann kann niemand mehr unterschreiben.
 * Dokumentarten ohne eigenes Unterschriftsfeld bekommen eine Empfangsbestätigung als Zusatzblatt.
 */

const IC_RECEIPT_FIELD = '_ic_receipt';

/** Unterschriftsfelder des Dokuments: Name => Bezeichnung. */
function doc_sign_fields(array $doc): array
{
    $data = is_array($doc['data']) ? $doc['data'] : (json_decode((string) $doc['data'], true) ?: []);
    $type = doc_type((string) $doc['type']) ?? custom_type_from_snapshot(['data' => $data] + $doc);
    $fields = is_array($data['_tpl']['fields'] ?? null) ? $data['_tpl']['fields'] : ($type['fields'] ?? []);
    $out = [];
    foreach ($fields as $f) {
        if (($f['type'] ?? '') === 'signature' && !empty($f['name'])) {
            $out[$f['name']] = (string) ($f['label'] ?? 'Unterschrift');
        }
    }
    // Karten (z. B. Dienstausweis) bekommen keine Empfangsbestätigung
    return $out ?: (doc_is_card($doc) ? [] : [IC_RECEIPT_FIELD => 'Unterschrift Empfänger/in (Empfang bestätigt)']);
}

/** Dokumentart wird als Karte (Vorder-/Rückseite) dargestellt? */
function doc_is_card(array $doc): bool
{
    return !empty((doc_type((string) $doc['type']) ?? [])['card']);
}

/** Stand der Unterschriften: final, Felder mit Status und Protokoll. */
function doc_sign_state(array $doc): array
{
    $data = is_array($doc['data']) ? $doc['data'] : (json_decode((string) $doc['data'], true) ?: []);
    $log = array_column(all('SELECT * FROM document_signatures WHERE document_id = ? ORDER BY id', [$doc['id']]), null, 'field');
    $fields = [];
    foreach (doc_sign_fields($doc) as $name => $label) {
        $signed = is_string($data[$name] ?? null) && $data[$name] !== '';
        $fields[] = ['name' => $name, 'label' => $label, 'signed' => $signed, 'log' => $log[$name] ?? null];
    }
    $open = count(array_filter($fields, fn($f) => !$f['signed']));
    return ['final' => !empty($doc['finalized_at']), 'fields' => $fields, 'open' => $open, 'receipt' => isset($fields[0]) && $fields[0]['name'] === IC_RECEIPT_FIELD];
}

/** Kurzfassung für das Spiel (keine Bilder). */
function doc_sign_public(array $doc): array
{
    $s = doc_sign_state($doc);
    return ['final' => $s['final'] || $doc['status'] === 'revoked', 'open' => $s['open'],
        'fields' => array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'], 'signed' => $f['signed'],
            'by' => $f['log']['signer_name'] ?? '', 'at' => $f['log'] ? fmt_datetime($f['log']['created_at']) : ''], $s['fields'])];
}

/** Prüfsumme über den Inhalt (Daten mit Unterschriften, Personalien, Aussteller). */
function doc_content_hash(array $doc): string
{
    return hash('sha256', implode('|', [$doc['number'], $doc['type'], (string) $doc['data'], (string) $doc['patient_snapshot'], (string) $doc['issuer_snapshot'], $doc['issued_at']]));
}

/**
 * Unterschrift speichern. $signer: name, citizen_id, source (game|web), user_id, player, license.
 * Gibt '' oder eine Fehlermeldung zurück.
 */
function doc_sign(array $doc, string $field, string $png, array $signer): string
{
    $fields = doc_sign_fields($doc);
    if ($doc['status'] === 'revoked') {
        return 'Das Dokument ist storniert.';
    }
    if (!empty($doc['finalized_at'])) {
        return 'Das Dokument ist abgeschlossen und kann nicht mehr unterschrieben werden.';
    }
    if (!isset($fields[$field])) {
        return 'Unbekanntes Unterschriftsfeld.';
    }
    if (!preg_match('#^data:image/png;base64,[A-Za-z0-9+/=]+$#', $png) || strlen($png) > 400000 || strlen($png) < 200) {
        return 'Die Unterschrift ist ungültig oder leer.';
    }
    $name = mb_substr(trim((string) ($signer['name'] ?? '')), 0, 190);
    if ($name === '') {
        return 'Bitte den Namen der unterschreibenden Person angeben.';
    }
    $db = db();
    $db->beginTransaction();
    try {
        // erneut lesen (gleichzeitiges Unterschreiben)
        $fresh = one('SELECT * FROM documents WHERE id = ?', [$doc['id']]);
        $data = json_decode((string) $fresh['data'], true) ?: [];
        if (!empty($fresh['finalized_at']) || (is_string($data[$field] ?? null) && $data[$field] !== '')) {
            $db->rollBack();
            return 'Dieses Feld wurde bereits unterschrieben.';
        }
        $data[$field] = $png;
        update('documents', (int) $doc['id'], ['data' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
        insert('document_signatures', ['document_id' => (int) $doc['id'], 'field' => $field, 'label' => mb_substr($fields[$field], 0, 190), 'signer_name' => $name,
            'signer_citizen_id' => mb_substr((string) ($signer['citizen_id'] ?? ''), 0, 64), 'source' => ($signer['source'] ?? 'game') === 'web' ? 'web' : 'game',
            'user_id' => $signer['user_id'] ?? null, 'player' => mb_substr((string) ($signer['player'] ?? ''), 0, 190), 'license' => mb_substr((string) ($signer['license'] ?? ''), 0, 190),
            'image_hash' => hash('sha256', $png), 'created_at' => now()]);
        $db->commit();
    } catch (Throwable $e) {
        $db->inTransaction() && $db->rollBack();
        throw $e;
    }
    audit_as($signer['user_id'] ?? null, 'document.signed', 'document', (int) $doc['id'], $doc['number'] . ': ' . $fields[$field] . ' – ' . $name . (($signer['source'] ?? 'game') === 'web' ? ' (Tablet/Browser)' : ' (im Spiel)'));
    // Alles unterschrieben → automatisch abschließen
    $after = one('SELECT * FROM documents WHERE id = ?', [$doc['id']]);
    if (doc_sign_state($after)['open'] === 0) {
        doc_finalize($after, $signer['user_id'] ?? null, 'alle Unterschriften vorhanden');
    }
    return '';
}

/** Dokument abschließen: keine weiteren Unterschriften, Prüfsumme speichern. */
function doc_finalize(array $doc, ?int $userId, string $note): string
{
    if (!empty($doc['finalized_at'])) {
        return 'Das Dokument ist bereits abgeschlossen.';
    }
    if ($doc['status'] === 'revoked') {
        return 'Das Dokument ist storniert.';
    }
    $fresh = one('SELECT * FROM documents WHERE id = ?', [$doc['id']]);
    $hash = doc_content_hash($fresh);
    q('UPDATE documents SET finalized_at = ?, finalized_by = ?, finalized_note = ?, final_hash = ? WHERE id = ? AND finalized_at IS NULL',
        [now(), $userId, mb_substr($note, 0, 190), $hash, $doc['id']]);
    audit_as($userId, 'document.finalized', 'document', (int) $doc['id'], $doc['number'] . ' abgeschlossen (' . $note . ')');
    return '';
}

/** Darf der Benutzer das Dokument im Browser/Tablet abschließen? Ausstellende Person oder wer alle Dokumente stornieren darf. */
function doc_can_finalize(array $doc): bool
{
    $me = current_user();
    return $me && ((int) $doc['issued_by'] === (int) $me['id'] || can('documents.revoke.all'));
}

/** Prüfsumme stimmt noch (unverändert seit Abschluss)? */
function doc_hash_valid(array $doc): bool
{
    return !empty($doc['final_hash']) && hash_equals((string) $doc['final_hash'], doc_content_hash($doc));
}

/** Zusatzblatt „Empfangsbestätigung“ für Dokumentarten ohne eigenes Unterschriftsfeld. */
function receipt_sheet(array $doc): string
{
    $sig = one('SELECT * FROM document_signatures WHERE document_id = ? AND field = ?', [$doc['id'] ?? 0, IC_RECEIPT_FIELD]);
    $type = doc_type((string) $doc['type']);
    $img = (string) ($doc['data'][IC_RECEIPT_FIELD] ?? '');
    ob_start(); ?>
    <section class="paper a6l receipt-sheet">
        <h3 class="psec">Empfangsbestätigung</h3>
        <p>Hiermit bestätige ich den Empfang des Dokuments <strong><?= e($type['title'] ?? 'Dokument') ?></strong> Nr. <span class="mono"><?= e($doc['number'] ?? '') ?></span>
            vom <?= e(fmt_date($doc['issued_at'] ?? '')) ?>.</p>
        <div class="sigblock"><div class="sig-sign"><div class="sig-area"><?= $img !== '' ? '<img class="sig-img" src="' . e($img) . '" alt="">' : '' ?></div>
            <span class="sig-cap"><?= $sig ? e($sig['signer_name']) . ', ' . e(fmt_datetime($sig['created_at'])) : 'Unterschrift Empfänger/in' ?></span></div></div>
    </section>
    <?php return (string) ob_get_clean();
}
