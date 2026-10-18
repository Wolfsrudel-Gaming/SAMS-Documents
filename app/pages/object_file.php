<?php
defined('SAMS_ROOT') || exit;
/** Plan/Foto eines Objekts ausliefern (nur für die eigene Fraktion). */
$f = one('SELECT f.* FROM object_files f JOIN objects o ON o.id = f.object_id WHERE f.id = ? AND ' . fw('o'), [(int) ($_GET['id'] ?? 0)]);
if (!$f) {
    not_found('Datei nicht gefunden.');
}
$thumb = isset($_GET['thumb']) && $f['has_thumb'];
$path = attachment_dir() . '/' . ($thumb ? 'thumb_' : '') . $f['stored_name'];
if (!is_file($path)) {
    not_found('Die Datei fehlt auf dem Server.');
}
while (ob_get_level()) {
    ob_end_clean();
}
header('Content-Type: ' . $f['mime']);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');
if (str_starts_with($f['mime'], 'image/')) {
    header("Content-Security-Policy: default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox");
}
header('Content-Disposition: inline; filename="' . preg_replace('/[^\w.\- ]+/u', '_', $f['title']) . '.' . pathinfo($f['stored_name'], PATHINFO_EXTENSION) . '"');
readfile($path);
exit;
