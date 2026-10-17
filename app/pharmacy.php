<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Medikamentenbestand mit lückenlosem Bewegungsprotokoll (für Betäubungsmittel
 * zugleich das BtM-Buch) und Einlösen von Rezepten – in der Klinik oder in
 * der Apotheke im Spiel (Ausgabe als ox_inventory-Items).
 */

const MED_MOVE_KINDS = [
    'in'       => 'Zugang',
    'out'      => 'Entnahme',
    'dispense' => 'Abgabe auf Rezept',
    'loss'     => 'Verwurf / Vernichtung',
    'correct'  => 'Korrektur',
    'check'    => 'Bestandsprüfung',
];

function medications(bool $activeOnly = true): array
{
    return all('SELECT * FROM medications' . ($activeOnly ? ' WHERE active = 1' : '') . ' ORDER BY name');
}

/**
 * Bestand buchen. Der Bestand kann nie negativ werden (atomare Prüfung in der Datenbank).
 * Gibt die Buchung zurück oder null, wenn nicht genug vorrätig ist.
 */
function medication_move(int $medId, int $delta, string $kind, array $opt = []): ?array
{
    $own = !db()->inTransaction();
    if ($own) {
        db()->beginTransaction();
    }
    try {
        if (q('UPDATE medications SET stock = stock + ?, updated_at = ? WHERE id = ? AND stock + ? >= 0', [$delta, now(), $medId, $delta])->rowCount() !== 1) {
            if ($own) {
                db()->rollBack();
            }
            return null;
        }
        $med = one('SELECT * FROM medications WHERE id = ?', [$medId]);
        $me = current_user();
        $move = [
            'medication_id' => $medId, 'delta' => $delta, 'balance' => (int) $med['stock'], 'kind' => $kind,
            'patient_id' => $opt['patient_id'] ?? null, 'document_id' => $opt['document_id'] ?? null,
            'user_id' => array_key_exists('user_id', $opt) ? $opt['user_id'] : ($me['id'] ?? null), 'witness_id' => $opt['witness_id'] ?? null,
            'party' => mb_substr((string) ($opt['party'] ?? ''), 0, 190), 'note' => mb_substr((string) ($opt['note'] ?? ''), 0, 190),
            'source' => $opt['source'] ?? 'web', 'created_at' => now(),
        ];
        $move['id'] = insert('medication_moves', $move);
        if ($own) {
            db()->commit();
        }
    } catch (Throwable $e) {
        if ($own && db()->inTransaction()) {
            db()->rollBack();
        }
        throw $e;
    }
    // Mindestbestand: einmal warnen, nach Auffüllen wieder scharf schalten
    if ((int) $med['min_stock'] > 0 && (int) $med['stock'] <= (int) $med['min_stock'] && !$med['low_warned']) {
        q('UPDATE medications SET low_warned = 1 WHERE id = ?', [$medId]);
        notify_users_with('stock.manage', 'stock', 'Mindestbestand erreicht: ' . $med['name'] . ' (' . $med['stock'] . ' ' . $med['unit'] . ')', url('stock'));
    } elseif ((int) $med['stock'] > (int) $med['min_stock'] && $med['low_warned']) {
        q('UPDATE medications SET low_warned = 0 WHERE id = ?', [$medId]);
    }
    return $move;
}

// ------------------------------------------------------------------ Rezepte

/** Passendes Medikament zu einer Rezeptzeile (längster Treffer aus Name/Suchbegriffen). */
function rx_match(string $text, ?array $meds = null): ?array
{
    $text = mb_strtolower($text);
    $best = null;
    $bestLen = 0;
    foreach ($meds ?? medications() as $m) {
        $terms = array_merge([$m['name']], array_map('trim', explode(',', (string) $m['keywords'])));
        foreach ($terms as $t) {
            $t = mb_strtolower($t);
            if ($t !== '' && mb_strlen($t) > $bestLen && str_contains($text, $t)) {
                $best = $m;
                $bestLen = mb_strlen($t);
            }
        }
    }
    return $best;
}

function rx_valid_days(bool $btm): int
{
    return $btm ? max(1, (int) setting('rx_valid_days_btm', '7')) : max(1, (int) setting('rx_valid_days', '28'));
}

/** Gültigkeit des Rezepts insgesamt: null = einlösbar, sonst Grund. */
function rx_problem(array $doc): ?string
{
    if ($doc['type'] !== 'rezept') {
        return 'Kein Rezept.';
    }
    if ($doc['status'] === 'revoked') {
        return 'Das Rezept wurde storniert.';
    }
    $data = is_array($doc['data']) ? $doc['data'] : (json_decode((string) $doc['data'], true) ?: []);
    $days = rx_valid_days(($data['form'] ?? '') === 'btm');
    if (strtotime($doc['issued_at']) < strtotime("-$days days")) {
        return 'Das Rezept ist abgelaufen (gültig ' . $days . ' Tage ab Ausstellung).';
    }
    return null;
}

/**
 * Positionen eines Rezepts mit Zuordnung und Status.
 * status: redeemed | ok | nomatch | btm_form | nostock | noitem
 */
function rx_lines(array $doc, bool $forGame = false): array
{
    $data = is_array($doc['data']) ? $doc['data'] : (json_decode((string) $doc['data'], true) ?: []);
    $isBtmForm = ($data['form'] ?? '') === 'btm';
    $done = [];
    foreach (all('SELECT r.*, u.name AS user_name FROM prescription_redemptions r LEFT JOIN users u ON u.id = r.user_id WHERE r.document_id = ?', [$doc['id']]) as $r) {
        $done[(int) $r['line']] = $r;
    }
    $meds = medications();
    $lines = [];
    foreach (array_values((array) ($data['items'] ?? [])) as $i => $item) {
        $text = trim((string) ($item['med'] ?? ''));
        if ($text === '') {
            continue;
        }
        // „2x …“ oder „2 × …“ am Anfang = Anzahl Packungen
        $qty = preg_match('/^\s*(\d{1,2})\s*[x×]\s*/iu', $text, $mm) ? max(1, min(5, (int) $mm[1])) : 1;
        $med = rx_match($text, $meds);
        $status = 'ok';
        if (isset($done[$i])) {
            $status = 'redeemed';
        } elseif (!$med) {
            $status = 'nomatch';
        } elseif ($med['is_btm'] && !$isBtmForm) {
            $status = 'btm_form';
        } elseif ((int) $med['stock'] < $qty) {
            $status = 'nostock';
        } elseif ($forGame && trim((string) $med['item_name']) === '') {
            $status = 'noitem';
        }
        $lines[] = ['line' => $i, 'text' => $text, 'dose' => (string) ($item['dose'] ?? ''), 'qty' => $qty, 'med' => $med, 'status' => $status, 'redemption' => $done[$i] ?? null];
    }
    return $lines;
}

const RX_STATUS = [
    'redeemed' => 'eingelöst', 'ok' => 'einlösbar', 'nomatch' => 'nicht im Sortiment', 'btm_form' => 'nur auf BtM-Rezept',
    'nostock' => 'nicht vorrätig', 'noitem' => 'nur in der Klinik erhältlich',
];

/**
 * Alle einlösbaren Positionen einlösen. Gibt [eingelöste Positionen, Hinweise] zurück.
 * Doppeltes Einlösen verhindert der eindeutige Index (document_id, line).
 */
function rx_redeem(array $doc, string $source, ?int $userId, string $player = '', string $license = ''): array
{
    $given = [];
    $notes = [];
    foreach (rx_lines($doc, $source === 'game') as $l) {
        if ($l['status'] !== 'ok') {
            if ($l['status'] !== 'redeemed') {
                $notes[] = $l['text'] . ': ' . RX_STATUS[$l['status']];
            }
            continue;
        }
        db()->beginTransaction();
        try {
            insert('prescription_redemptions', ['document_id' => $doc['id'], 'line' => $l['line'], 'medication_id' => $l['med']['id'], 'quantity' => $l['qty'],
                'user_id' => $userId, 'player' => mb_substr($player, 0, 190), 'license' => mb_substr($license, 0, 190), 'source' => $source, 'created_at' => now()]);
            $move = medication_move((int) $l['med']['id'], -$l['qty'], 'dispense', ['patient_id' => $doc['patient_id'], 'document_id' => $doc['id'], 'user_id' => $userId,
                'source' => $source, 'party' => $source === 'game' ? 'Apotheke (Spiel): ' . $player : '', 'note' => $doc['number']]);
            if (!$move) {
                db()->rollBack();
                $notes[] = $l['text'] . ': ' . RX_STATUS['nostock'];
                continue;
            }
            db()->commit();
        } catch (PDOException $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            continue; // gleichzeitig bereits eingelöst
        }
        $given[] = ['name' => $l['med']['name'], 'qty' => $l['qty'], 'item' => (string) $l['med']['item_name'], 'count' => $l['qty'] * max(1, (int) $l['med']['item_count']), 'btm' => (bool) $l['med']['is_btm']];
    }
    if ($given) {
        audit_as($userId, 'rx.redeem', 'document', (int) $doc['id'], $doc['number'] . ': ' . implode(', ', array_map(fn($g) => $g['qty'] . '× ' . $g['name'], $given))
            . ($source === 'game' ? ' (Apotheke im Spiel, ' . $player . ')' : ''));
    }
    return [$given, $notes];
}
