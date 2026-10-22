<?php
/**
 * Ansicht eines gedruckten Dokuments – Inhalt des Ingame-Items "Dokument".
 * Zugriff nur über den geheimen Ansichts-Schlüssel des Dokuments (steckt in den Item-Metadaten).
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$token = (string) ($_GET['t'] ?? '');
$doc = strlen($token) >= 32 ? one('SELECT * FROM documents WHERE view_token = ?', [$token]) : null;
// Gefälschtes Dokument (Fälscherwerkstatt): sieht aus wie echt, der Prüfcode existiert aber nicht
$forged = !$doc ? forgery_by_view_token($token) : null;
if ($forged) {
    $doc = forgery_doc($forged);
    $doc['data'] = json_encode($doc['data']);
    $doc['patient_snapshot'] = json_encode($doc['patient_snapshot']);
    $doc['issuer_snapshot'] = json_encode($doc['issuer_snapshot']);
}
if (!$doc) {
    http_response_code(404);
    exit('<!doctype html><meta charset="utf-8"><body style="font-family:system-ui;padding:2rem;color:#fff;background:#222">Dokument nicht gefunden.</body>');
}
set_faction_context((int) $doc['faction_id']);
$doc['data'] = json_decode((string) $doc['data'], true) ?: [];
$doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
$doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
$copy = preg_replace('/[^a-z]/', '', (string) ($_GET['copy'] ?? ''));
if ($doc['type'] === 'au' && $copy === '') {
    $copy = 'versicherter';
}
$html = render_document($doc, ['copy' => $copy] + ($forged ? ['no_addenda' => true] : []));
if ($forged) {
    $html = '<div class="' . e(forgery_flaw_class((int) $forged['quality'])) . '">' . $html . '</div>';
}
// Karten (Dienstausweis): Vorder- und Rückseite als drehbare Karte statt Blatt
$card = doc_is_card($doc) && !isset($_GET['flat']);
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
<body<?= $card ? ' class="card-mode"' : '' ?>>
<?php if ($card): ?>
<style>
    body.card-mode { display: grid; place-items: center; min-height: 100vh; padding: 0; overflow: hidden; user-select: none; }
    .card-scene { perspective: 1400px; }
    .card3d { position: relative; transform-style: preserve-3d; transition: transform .6s cubic-bezier(.2, .7, .2, 1); cursor: grab; }
    .card3d.dragging { transition: none; cursor: grabbing; }
    .card3d .face { position: absolute; inset: 0; backface-visibility: hidden; -webkit-backface-visibility: hidden; }
    .card3d .face.front { position: relative; }
    .card3d .face.back { transform: rotateY(180deg); }
    .card3d .paper.idcard { margin: 0; box-shadow: 0 18px 50px rgba(0, 0, 0, .55); }
    .card3d .face::after { content: ''; position: absolute; inset: 0; border-radius: 3.2mm; pointer-events: none; mix-blend-mode: soft-light;
        background: linear-gradient(calc(110deg + var(--ry, 0deg)), transparent 30%, rgba(255, 255, 255, .55) 45%, rgba(120, 200, 255, .35) 50%, rgba(255, 160, 220, .3) 55%, transparent 70%); }
    .card-hint { position: fixed; bottom: 10px; left: 0; right: 0; text-align: center; font: 12px system-ui, sans-serif; color: rgba(255, 255, 255, .85); text-shadow: 0 1px 2px #000; }
</style>
<div class="card-scene"><div class="card3d" id="card"><div class="face front"></div><div class="face back"></div></div></div>
<div class="card-hint">Klicken: umdrehen · Ziehen: Karte drehen · ESC: schließen</div>
<div id="src" hidden><?= $html ?></div>
<?php else: ?>
<div id="fit"><div class="doc-stage"><?= $html ?></div></div>
<?php endif; ?>
<script src="assets/vendor/qrcode.js?v=<?= e(SAMS_VERSION) ?>"></script>
<script src="assets/js/document.js?v=<?= e(SAMS_VERSION) ?>"></script>
<?php if ($card): ?>
<script>
    // Drehbare Karte: Klick dreht um, Ziehen neigt/dreht frei, beim Loslassen rastet die nächste Seite ein
    (function () {
        var card = document.getElementById('card'), src = document.getElementById('src');
        var front = src.querySelector('.paper.idcard.front') || src.querySelector('.paper'), back = src.querySelector('.paper.idcard.back');
        if (front) { card.querySelector('.face.front').appendChild(front); }
        if (back) { card.querySelector('.face.back').appendChild(back); } else { card.querySelector('.face.back').remove(); }
        var ry = 0, rx = 0, base = 0, start = null, moved = 0, scale = 1;
        function fit() {
            var w = card.offsetWidth || 323, h = card.offsetHeight || 204;
            scale = Math.min((window.innerWidth * 0.86) / w, (window.innerHeight * 0.78) / h, 3.2);
            apply();
        }
        function apply() {
            card.style.transform = 'scale(' + scale + ') rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
            card.style.setProperty('--ry', ((ry % 360) + 360) % 360 + 'deg');
        }
        card.addEventListener('pointerdown', function (e) { start = { x: e.clientX, y: e.clientY, ry: ry }; moved = 0; card.setPointerCapture(e.pointerId); card.classList.add('dragging'); });
        card.addEventListener('pointermove', function (e) {
            if (!start) { return; }
            var dx = e.clientX - start.x, dy = e.clientY - start.y;
            moved = Math.max(moved, Math.abs(dx) + Math.abs(dy));
            ry = start.ry + dx * 0.6;
            rx = Math.max(-35, Math.min(35, -dy * 0.35));
            apply();
        });
        function release() {
            if (!start) { return; }
            card.classList.remove('dragging');
            if (moved < 6 && back) { base += 180; } else { base = Math.round(ry / 180) * 180; }
            if (!back) { base = 0; }
            ry = base; rx = 0; start = null;
            apply();
        }
        card.addEventListener('pointerup', release);
        card.addEventListener('pointercancel', release);
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && window.parent !== window) { window.parent.postMessage({ sams: 'closeDocument' }, '*'); }
            if ((e.key === ' ' || e.key === 'ArrowLeft' || e.key === 'ArrowRight') && back) { base += e.key === 'ArrowLeft' ? -180 : 180; ry = base; rx = 0; apply(); }
        });
        // Größe ohne Animation setzen (sonst „wächst“ die Karte beim Öffnen)
        function fitNow() { card.classList.add('dragging'); fit(); void card.offsetWidth; card.classList.remove('dragging'); }
        window.addEventListener('resize', fitNow);
        window.addEventListener('load', fitNow);
        fitNow();
    })();
</script>
<?php endif; ?>
<script>
    // Dokument an die Fensterbreite anpassen (Klemmbrett-Ansicht im Spiel)
    (function () {
        if (!document.getElementById('fit')) { return; }
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
