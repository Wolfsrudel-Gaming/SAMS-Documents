<?php
/**
 * Freigegebenes Dokument für Behörden (LSPD, Justiz …) über einen geheimen Link.
 * Jeder Abruf wird gezählt und im Zugriffsprotokoll vermerkt.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$token = (string) ($_GET['t'] ?? '');
if (!rate_limit('share', 60)) {
    rate_limited_exit();
}
$share = strlen($token) >= 32 ? one("SELECT * FROM document_shares WHERE token_hash = ? AND kind = 'link'", [hash('sha256', $token)]) : null;
$doc = $share ? one('SELECT * FROM documents WHERE id = ?', [$share['document_id']]) : null;
if ($doc) {
    set_faction_context((int) $doc['faction_id']);
}
$error = !$share || !$doc ? 'Diese Freigabe existiert nicht.' : (!share_active($share) ? ($share['revoked'] ? 'Diese Freigabe wurde widerrufen.' : 'Diese Freigabe ist abgelaufen.') : '');
header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');
if ($error === '') {
    share_touch($share, 'Link');
    $doc['data'] = json_decode((string) $doc['data'], true) ?: [];
    $doc['patient_snapshot'] = json_decode((string) $doc['patient_snapshot'], true) ?: [];
    $doc['issuer_snapshot'] = json_decode((string) $doc['issuer_snapshot'], true) ?: [];
    $html = render_document($doc, ['copy' => $doc['type'] === 'au' ? 'kasse' : '']);
}
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Freigegebenes Dokument · <?= e(org('org_short')) ?></title>
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/css/app.css?v=<?= SAMS_VERSION ?>">
<link rel="stylesheet" href="assets/css/document.css?v=<?= SAMS_VERSION ?>">
</head>
<body class="no-nav verify-page">
<main class="main">
    <div class="card share-banner no-print">
        <img src="<?= e(faction_logo()) ?>" alt="">
        <?php if ($error !== ''): ?>
            <div><h1>Freigegebenes Dokument</h1><p class="text-danger"><?= e($error) ?></p><p class="muted small">Bitte wenden Sie sich an <?= e(org('org_name')) ?>, <?= e(org('phone')) ?>.</p></div>
        <?php else: ?>
            <div>
                <h1>Vertrauliches Dokument</h1>
                <p>Freigegeben von <?= e(org('org_name')) ?> für <strong><?= e(SHARE_AGENCIES[$share['agency']] ?? $share['agency']) ?></strong><?= $share['recipient'] ? ' – ' . e($share['recipient']) : '' ?>
                    <?= $share['purpose'] ? '<br>Zweck: ' . e($share['purpose']) : '' ?>
                    <br><span class="muted small">Gültig <?= $share['expires_at'] ? 'bis ' . e(fmt_datetime($share['expires_at'])) : 'bis auf Widerruf' ?> · Abruf wird protokolliert · Weitergabe an Dritte nur im Rahmen des Verfahrens</span></p>
            </div>
            <button class="btn" onclick="window.print()">Drucken / PDF</button>
        <?php endif; ?>
    </div>
    <?php if ($error === ''): ?><div class="doc-stage"><?= $html ?></div><?php endif; ?>
</main>
<?php if ($error === ''): ?>
<script src="assets/vendor/qrcode.js?v=<?= SAMS_VERSION ?>"></script>
<script src="assets/js/document.js?v=<?= SAMS_VERSION ?>"></script>
<?php endif; ?>
</body>
</html>
