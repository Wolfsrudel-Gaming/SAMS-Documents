<?php
/**
 * Öffentliche Echtheitsprüfung – für LSPD, Arbeitgeber, Behörden usw.
 * Zeigt nur Metadaten, keine medizinischen Inhalte.
 */
declare(strict_types=1);
require dirname(__DIR__) . '/app/bootstrap.php';

$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($_GET['c'] ?? '')));
if ($code !== '' && !rate_limit('verify', 60)) {
    rate_limited_exit();
}
$doc = null;
$type = null;
if ($code !== '') {
    $doc = one('SELECT * FROM documents WHERE verify_code = ?', [$code]);
    $type = $doc ? doc_type($doc['type']) : null;
}
$issuer = $doc ? (json_decode((string) $doc['issuer_snapshot'], true) ?: []) : [];
$patient = $doc ? (json_decode((string) $doc['patient_snapshot'], true) ?: []) : [];
$state = 'none';
if ($code !== '') {
    $state = !$doc ? 'unknown' : ($doc['status'] === 'revoked' ? 'revoked' : ((($doc['valid_until'] ?? '') !== '' && $doc['valid_until'] < date('Y-m-d')) ? 'expired' : 'valid'));
}
$states = [
    'valid'   => ['ok', 'Dokument echt und gültig', 'Dieses Dokument wurde von ' . org('org_name') . ' ausgestellt und ist im System als gültig hinterlegt.'],
    'expired' => ['warn', 'Dokument echt, aber abgelaufen', 'Die Gültigkeitsdauer dieses Dokuments ist überschritten.'],
    'revoked' => ['bad', 'Dokument storniert', 'Dieses Dokument wurde vom Aussteller für ungültig erklärt und darf nicht mehr verwendet werden.'],
    'unknown' => ['bad', 'Kein Dokument gefunden', 'Zu diesem Prüfcode existiert kein Dokument. Es könnte sich um eine Fälschung handeln.'],
];
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Echtheitsprüfung · <?= e(org('org_short')) ?></title>
<link rel="icon" href="assets/img/logo.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/css/app.css?v=<?= SAMS_VERSION ?>">
</head>
<body class="no-nav verify-page">
<main class="main">
    <div class="verify card">
        <div class="verify-head">
            <img src="<?= e(setting('logo', 'assets/img/logo.svg')) ?>" alt="">
            <div><h1>Echtheitsprüfung</h1><p class="muted"><?= e(org('org_name')) ?> · <?= e(org('org_sub')) ?></p></div>
        </div>
        <form method="get" class="verify-form">
            <input name="c" value="<?= e($code !== '' ? chunk_split($code, 4, ' ') : '') ?>" placeholder="Prüfcode, z.B. K7F3 QX9M" autocomplete="off" required autofocus>
            <button class="btn primary">Prüfen</button>
        </form>
        <?php if ($state !== 'none'): [$cls, $head, $text] = $states[$state]; ?>
            <div class="verify-result <?= $cls ?>">
                <div class="vr-icon"><?= $cls === 'ok' ? '✔' : ($cls === 'warn' ? '!' : '✖') ?></div>
                <div><strong><?= e($head) ?></strong><p><?= e($text) ?></p></div>
            </div>
            <?php if ($doc): ?>
            <dl class="verify-facts">
                <dt>Dokumentart</dt><dd><?= e($type['title'] ?? $doc['type']) ?></dd>
                <dt>Dokument-Nr.</dt><dd class="mono"><?= e($doc['number']) ?></dd>
                <dt>Ausgestellt am</dt><dd><?= e(fmt_datetime($doc['issued_at'])) ?></dd>
                <?php if ($doc['valid_until']): ?><dt>Gültig bis</dt><dd><?= e(fmt_date($doc['valid_until'])) ?></dd><?php endif; ?>
                <dt>Ausgestellt von</dt><dd><?= e(user_display($issuer)) ?><?= !empty($issuer['rank_name']) ? ', ' . e($issuer['rank_name']) : '' ?><?= !empty($issuer['badge_number']) ? ' (Dienstnr. ' . e($issuer['badge_number']) . ')' : '' ?></dd>
                <?php if ($patient): ?><dt>Ausgestellt für</dt><dd><?= e(initials_name($patient)) ?><?= !empty($patient['dob']) ? ', geb. ' . e(fmt_date($patient['dob'], 'Y')) : '' ?></dd><?php endif; ?>
                <?php if (!empty($issuer['cosigner'])): ?><dt>Gegengezeichnet</dt><dd><?= e(user_display($issuer['cosigner'])) ?><?= !empty($issuer['cosigner']['rank_name']) ? ', ' . e($issuer['cosigner']['rank_name']) : '' ?></dd><?php endif; ?>
                <?php foreach (document_addenda((int) $doc['id']) as $i => $a): ?><dt>Nachtrag Nr. <?= $i + 1 ?></dt><dd><?= e(fmt_datetime($a['created_at'])) ?> · <?= e(user_display($a['author'])) ?></dd><?php endforeach; ?>
                <?php if ($state === 'revoked'): ?><dt>Storniert am</dt><dd><?= e(fmt_datetime($doc['revoked_at'])) ?></dd><?php endif; ?>
            </dl>
            <p class="muted small">Vergleichen Sie Dokument-Nr. und Angaben mit dem vorliegenden Original. Aus Datenschutzgründen werden keine medizinischen Inhalte angezeigt.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="muted">Geben Sie den Prüfcode ein, der unten auf jedem Dokument des <?= e(org('org_short')) ?> aufgedruckt ist, oder scannen Sie den QR-Code.</p>
        <?php endif; ?>
    </div>
    <p class="center muted small">Rückfragen: <?= e(org('phone')) ?> · <?= e(org('email')) ?></p>
</main>
</body>
</html>
