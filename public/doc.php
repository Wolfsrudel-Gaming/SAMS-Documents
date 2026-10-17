<?php
/**
 * Ansicht eines gedruckten Dokuments – Inhalt des Ingame-Items "Dokument".
 * Zugriff nur über den geheimen Ansichts-Schlüssel des Dokuments (steckt in den Item-Metadaten).
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$token = (string) ($_GET['t'] ?? '');
$doc = strlen($token) >= 32 ? one('SELECT * FROM documents WHERE view_token = ?', [$token]) : null;
if (!$doc) {
    http_response_code(404);
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;padding:2rem;color:#fff;background:#222">Dokument nicht gefunden.</body>');
}
$doc['data'] = json_decode((string) $doc['data'], true) ?: [];
$doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
$doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
$copy = preg_replace('/[^a-z]/', '', (string) ($_GET['copy'] ?? ''));
if ($doc['type'] === 'au' && $copy === '') {
    $copy = 'versicherter';
}
$html = render_document($doc, ['copy' => $copy]);
header('Cache-Control: private, max-age=60');
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title><?= e($doc['number']) ?></title>
<link rel="stylesheet" href="assets/css/document.css?v=<?= e(SAMS_VERSION) ?>">
<style>
    html, body { margin: 0; background: transparent; }
    body { display: flex; justify-content: center; padding: 14px 0; overflow-x: hidden; }
    #fit { transform-origin: top center; }
    .doc-stage { padding: 0; gap: 14px; }
    .paper { box-shadow: 0 6px 24px rgba(0,0,0,.45); }
</style>
</head>
<body>
<div id="fit"><div class="doc-stage"><?= $html ?></div></div>
<script src="assets/vendor/qrcode.js?v=<?= e(SAMS_VERSION) ?>"></script>
<script src="assets/js/document.js?v=<?= e(SAMS_VERSION) ?>"></script>
<script>
    // Dokument an die Fensterbreite anpassen (Klemmbrett-Ansicht im Spiel)
    (function () {
        var fit = document.getElementById('fit');
        function scale() {
            fit.style.transform = '';
            var w = fit.scrollWidth, h = fit.scrollHeight;
            var s = Math.min(1.6, (window.innerWidth - 20) / w);
            fit.style.transform = 'scale(' + s + ')';
            document.body.style.height = (h * s + 28) + 'px';
        }
        window.addEventListener('resize', scale);
        // ESC schließt das Klemmbrett im Spiel
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && window.parent !== window) { window.parent.postMessage({ sams: 'closeDocument' }, '*'); }
        });
        window.addEventListener('load', scale);
        scale();
    })();
</script>
</body>
</html>
