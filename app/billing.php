<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Abrechnung: Gebührenordnung (Preisliste), Rechnungsbeträge, Abweichungen
 * mit Begründung und Rechnungsstatus (offen / bezahlt / gemahnt).
 */

const PAY_STATUS = [
    'open'   => ['orange', 'offen'],
    'paid'   => ['green', 'bezahlt'],
    'dunned' => ['red', 'gemahnt'],
];
const DUNNING_DAYS = 7;

function price_list(bool $activeOnly = true): array
{
    static $cache = [];
    $key = $activeOnly ? 'a' : 'all';
    if (!isset($cache[$key])) {
        try {
            $cache[$key] = all('SELECT * FROM price_list WHERE ' . fw() . ($activeOnly ? ' AND active = 1' : '') . ' ORDER BY sort, code, name');
        } catch (PDOException $e) {
            $cache[$key] = []; // vor der Migration
        }
    }
    return $cache[$key];
}

/** Bezeichnung, wie sie in der Rechnung steht („R01 Notfalleinsatz Rettungswagen“). */
function price_label(array $p): string
{
    return trim($p['code'] . ' ' . $p['name']);
}

/** Preislisten-Eintrag zu einer Rechnungsposition (über Kürzel oder Bezeichnung). */
function price_lookup(string $text): ?array
{
    $text = mb_strtolower(trim($text));
    if ($text === '') {
        return null;
    }
    foreach (price_list() as $p) {
        if ($text === mb_strtolower(price_label($p)) || $text === mb_strtolower($p['name']) || ($p['code'] !== '' && $text === mb_strtolower($p['code']))) {
            return $p;
        }
    }
    return null;
}

function to_float($v): float
{
    return (float) str_replace(',', '.', (string) $v);
}

/** [netto, steuer, gesamt] einer Rechnung */
function invoice_totals(array $d): array
{
    $net = 0.0;
    foreach ((array) ($d['items'] ?? []) as $it) {
        $net += to_float(($it['qty'] ?? '') !== '' ? $it['qty'] : 1) * to_float($it['price'] ?? 0);
    }
    $tax = round($net * to_float($d['tax'] ?? 0) / 100, 2);
    return [$net, $tax, $net + $tax];
}

/**
 * Prüft Rechnungspositionen gegen die Preisliste: Abweichende Preise und
 * freie Positionen brauchen eine Begründung.
 */
function invoice_item_errors(array $d): array
{
    if (!price_list()) {
        return [];
    }
    $errors = [];
    foreach ((array) ($d['items'] ?? []) as $i => $it) {
        if (trim((string) ($it['text'] ?? '')) === '') {
            continue;
        }
        $reason = trim((string) ($it['reason'] ?? ''));
        $p = price_lookup((string) $it['text']);
        if (!$p && $reason === '') {
            $errors[] = 'Position ' . ($i + 1) . ': „' . $it['text'] . '“ steht nicht in der Gebührenordnung – bitte begründen.';
        } elseif ($p && abs(to_float($it['price'] ?? 0) * 100 - (int) $p['price_cents']) >= 1 && $reason === '') {
            $errors[] = 'Position ' . ($i + 1) . ': Preis weicht von der Gebührenordnung ab (' . money($p['price_cents'] / 100) . ') – bitte begründen.';
        }
    }
    return $errors;
}

function pay_status_badge(array $doc): string
{
    if (($doc['pay_status'] ?? '') === '') {
        return '';
    }
    [$c, $l] = PAY_STATUS[$doc['pay_status']] ?? ['grey', $doc['pay_status']];
    if ($doc['pay_status'] === 'dunned' && (int) $doc['dunning_level'] > 0) {
        $l = (int) $doc['dunning_level'] . '. Mahnung';
    }
    return '<span class="badge ' . $c . '">' . e($l) . '</span>';
}

/** Fälligkeit: Zahlungsziel ab Ausstellung bzw. Mahnfrist ab letzter Mahnung. */
function invoice_due(array $doc, array $d): string
{
    if (($doc['pay_status'] ?? '') === 'dunned' && !empty($doc['dunned_at'])) {
        return date('Y-m-d', strtotime($doc['dunned_at'] . ' +' . DUNNING_DAYS . ' days'));
    }
    return date('Y-m-d', strtotime(($doc['issued_at'] ?? now()) . ' +' . max(0, (int) ($d['due_days'] ?? 14)) . ' days'));
}

/** Für das Formular: Preise als JSON (Bezeichnung → Preis) zum automatischen Ausfüllen. */
function price_list_json(): string
{
    $out = [];
    foreach (price_list() as $p) {
        $out[price_label($p)] = round($p['price_cents'] / 100, 2);
    }
    return json_encode($out, JSON_UNESCAPED_UNICODE);
}

/** Haft-Einheiten je Katalogposition (Polizei/Justiz). */
function price_jail_json(): string
{
    $out = [];
    foreach (price_list() as $p) {
        if ((int) ($p['jail'] ?? 0) > 0) {
            $out[price_label($p)] = (int) $p['jail'];
        }
    }
    return json_encode((object) $out, JSON_UNESCAPED_UNICODE);
}
