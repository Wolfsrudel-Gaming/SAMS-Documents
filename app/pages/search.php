<?php
defined('SAMS_ROOT') || exit;
/** Schnellsuche aus der Kopfleiste: Dokument-Nr./Prüfcode direkt öffnen, sonst Patientensuche. */
$q = trim((string) ($_GET['q'] ?? ''));
if ($q === '') {
    redirect(url('patients'));
}
$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $q));
$doc = one('SELECT id FROM documents WHERE number = ? OR verify_code = ?', [strtoupper($q), $code]);
if ($doc) {
    redirect(url('document', ['id' => $doc['id']]));
}
$p = one("SELECT id FROM patients WHERE citizen_id = ? AND citizen_id <> ''", [$q]);
if ($p) {
    redirect(url('patient', ['id' => $p['id']]));
}
if (preg_match('/^[A-Z]+-[A-Z]{2}-\d{4}/i', $q)) {
    redirect(url('documents', ['q' => $q]));
}
redirect(url('patients', ['q' => $q]));
