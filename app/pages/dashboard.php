<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
// Inhalte nur zeigen, wenn die Rechte dazu vorhanden sind (z. B. nicht für Behördenkonten)
$stats = [];
if (can('patients.view')) {
    $stats['Aktive Akten'] = (int) scalar('SELECT COUNT(*) FROM patients WHERE archived_at IS NULL AND ' . fw());
    $stats['Offene ' . term('cases')] = (int) scalar("SELECT COUNT(*) FROM cases WHERE status = 'open' AND " . fw());
}
if (can('documents.view')) {
    $stats['Dokumente heute'] = (int) scalar('SELECT COUNT(*) FROM documents WHERE issued_at >= ? AND ' . fw(), [date('Y-m-d 00:00:00')]);
    $stats['Meine Dokumente'] = (int) scalar('SELECT COUNT(*) FROM documents WHERE issued_by = ?', [$me['id']]);
}
if (can('documents.issue.*|documents.approve')) {
    $stats['Entwürfe & Freigaben'] = draft_inbox_count();
}
if (can('duty.view')) {
    $stats['Im Dienst'] = count(duty_on_now());
}
if (can('documents.shared_view')) {
    $stats['Für mich freigegeben'] = count(array_filter(all("SELECT * FROM document_shares WHERE kind = 'user' AND user_id = ?", [$me['id']]), 'share_active'));
}
$recentDocs = can('documents.view') ? all('SELECT d.*, p.first_name, p.last_name, u.name AS issuer_name FROM documents d
    LEFT JOIN patients p ON p.id = d.patient_id LEFT JOIN users u ON u.id = d.issued_by
    WHERE ' . fw('d') . ' ORDER BY d.id DESC LIMIT 10') : null;
$recentPatients = can('patients.view') ? all('SELECT * FROM patients WHERE archived_at IS NULL AND ' . fw() . ' ORDER BY updated_at DESC LIMIT 8') : null;

// Wichtige oder zu bestätigende Aushänge oben anzeigen
$notices = [];
if (can('board.view')) {
    $deps = user_department_ids((int) $me['id']);
    $notices = array_values(array_filter(visible_announcements($me), fn($a) => announcement_for($a, $deps, user_level($me))
        && (($a['require_ack'] && !$a['acknowledged_at']) || ($a['priority'] === 'important' && !$a['read_at']))));
}
$myShifts = can('roster.view') ? roster_upcoming((int) $me['id']) : [];
render('_dashboard', compact('stats', 'recentDocs', 'recentPatients', 'me', 'notices', 'myShifts') + ['title' => 'Übersicht']);
