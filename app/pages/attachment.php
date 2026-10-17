<?php
defined('SAMS_ROOT') || exit;
/** Anhang ausliefern (nur mit Rechteprüfung, Dateien liegen außerhalb des Webroots). */
$a = one('SELECT * FROM attachments WHERE id = ?', [(int) ($_GET['id'] ?? 0)]);
if (!$a || !attachment_visible($a)) {
    not_found('Anhang nicht gefunden.');
}
$thumb = isset($_GET['thumb']) && $a['has_thumb'];
$path = attachment_path($a, $thumb);
if (!is_file($path)) {
    not_found('Die Datei fehlt auf dem Server.');
}
if (!$thumb) {
    audit('attachment.view', 'patient', (int) $a['patient_id'], $a['title']);
}
while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: ' . $a['mime']);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
// Bilder dürfen keine Skripte ausführen (bei PDF würde die Sandbox den Browser-Betrachter blockieren)
if (str_starts_with($a['mime'], 'image/')) {
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox");
}
$name = preg_replace('/[^\w.\- ]+/u', '_', $a['title']) . '.' . pathinfo($a['stored_name'], PATHINFO_EXTENSION);
header('Content-Disposition: ' . (isset($_GET['download']) ? 'attachment' : 'inline') . '; filename="' . $name . '"');
readfile($path);
exit;
