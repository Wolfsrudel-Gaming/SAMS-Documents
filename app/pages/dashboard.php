<?php
defined('SAMS_ROOT') || exit;
$me = current_user();
$stats = [
    'Patientenakten'        => (int) scalar('SELECT COUNT(*) FROM patients'),
    'Dokumente gesamt'      => (int) scalar('SELECT COUNT(*) FROM documents'),
    'Dokumente heute'       => (int) scalar('SELECT COUNT(*) FROM documents WHERE issued_at >= ?', [date('Y-m-d 00:00:00')]),
    'Meine Dokumente'       => (int) scalar('SELECT COUNT(*) FROM documents WHERE issued_by = ?', [$me['id']]),
];
$recentDocs = all('SELECT d.*, p.first_name, p.last_name, u.name AS issuer_name FROM documents d
    LEFT JOIN patients p ON p.id = d.patient_id LEFT JOIN users u ON u.id = d.issued_by
    ORDER BY d.id DESC LIMIT 10');
$recentPatients = all('SELECT * FROM patients ORDER BY updated_at DESC LIMIT 8');

render('_dashboard', compact('stats', 'recentDocs', 'recentPatients', 'me') + ['title' => 'Übersicht']);
