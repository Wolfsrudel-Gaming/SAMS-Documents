<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Personalakte: Laufbahn-Einträge (Eintritt, Rang, Beförderung, Abmahnung,
 * Ausbildung …), Personaldokumente und Notizen. Viele Einträge entstehen
 * automatisch (Rangwechsel, ausgestellte Personaldokumente).
 */

const PERSONNEL_KINDS = [
    'hire'          => ['green', 'Eintritt'],
    'rank'          => ['blue', 'Rang'],
    'promotion'     => ['blue', 'Beförderung'],
    'qualification' => ['purple', 'Qualifikation'],
    'training'      => ['purple', 'Ausbildung'],
    'praise'        => ['green', 'Belobigung'],
    'warning'       => ['orange', 'Abmahnung'],
    'note'          => ['grey', 'Notiz'],
    'contract'      => ['grey', 'Vertrag'],
    'document'      => ['grey', 'Dokument'],
    'termination'   => ['red', 'Austritt'],
];
/** Einträge, die von Hand angelegt werden können */
const PERSONNEL_MANUAL = ['note', 'praise', 'warning', 'training', 'qualification', 'termination'];

function personnel_event(int $userId, string $kind, string $title, string $text = '', ?string $date = null, bool $confidential = false, ?int $documentId = null): void
{
    if (!$confidential && in_array($kind, ['hire', 'rank', 'promotion', 'qualification', 'training', 'praise', 'termination'], true)) {
        $u = one('SELECT name, title FROM users WHERE id = ?', [$userId]);
        notify_webhook('personnel', log_embed('👤 ' . user_display($u) . ' – ' . (PERSONNEL_KINDS[$kind][1] ?? $kind), $title, $kind === 'termination' ? 0xb3261e : 0x0b4f9c));
    }
    if (!$confidential) {
        notify_user($userId, 'personnel', 'Neuer Eintrag in deiner Personalakte: ' . $title, url('personnel'));
    }
    insert('personnel_events', [
        'user_id' => $userId, 'kind' => $kind, 'title' => mb_substr($title, 0, 190), 'text' => $text,
        'event_date' => $date ?: date('Y-m-d'), 'confidential' => $confidential ? 1 : 0, 'document_id' => $documentId,
        'created_by' => current_user()['id'] ?? null, 'created_at' => now(),
    ]);
}

/** Laufbahn-Eintrag zu einem ausgestellten Personaldokument. */
function personnel_event_for_document(int $docId, array $type, array $data): void
{
    $emp = $data['_employee'] ?? null;
    if (empty($emp['id'])) {
        return;
    }
    [$kind, $title, $date] = match ($type['key']) {
        'befoerderung' => ['promotion', (['befoerderung' => 'Beförderung', 'ernennung' => 'Ernennung', 'auszeichnung' => 'Auszeichnung', 'jubilaeum' => 'Dienstjubiläum'][$data['kind'] ?? ''] ?? 'Urkunde') . ': ' . ($data['new_rank'] ?? ''), $data['effective'] ?? null],
        'abmahnung' => ['warning', (['ermahnung' => 'Ermahnung', 'abmahnung' => 'Abmahnung', 'letzte' => 'Letztmalige Abmahnung'][$data['level'] ?? ''] ?? 'Abmahnung'), $data['incident_date'] ?? null],
        'kuendigung' => ['termination', 'Kündigung zum ' . fmt_date($data['end_date'] ?? ''), null],
        'ausbildungsnachweis' => ['training', 'Lehrgang: ' . ($data['course'] ?? ''), $data['date_to'] ?? null],
        'arbeitsvertrag' => ['contract', 'Arbeitsvertrag: ' . ($data['position'] ?? ''), $data['start'] ?? null],
        default => ['document', $type['title'], null],
    };
    personnel_event((int) $emp['id'], $kind, $title, '', $date, $kind === 'warning', $docId);
}

/** Darf der angemeldete Benutzer die Personalakte sehen? (eigene immer) */
function can_view_personnel(array $target): bool
{
    $me = current_user();
    return (int) $target['id'] === (int) $me['id'] || (can('personnel.view') && (!scoped_to_departments() || can_manage_user($target, 'users.view') || array_intersect(user_department_ids((int) $me['id']), user_department_ids((int) $target['id']))));
}

function personnel_kind_badge(string $kind): string
{
    [$c, $l] = PERSONNEL_KINDS[$kind] ?? ['grey', $kind];
    return '<span class="badge ' . $c . '">' . e($l) . '</span>';
}
