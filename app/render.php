<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Bausteine für die Druckvorlagen (Briefkopf, Stempel, Unterschrift,
 * Barcode, Fußzeile). Die eigentlichen Vorlagen liegen in app/templates/.
 */

/**
 * Rendert ein Dokument zu HTML.
 * $doc: Datensatz aus der Tabelle documents (data/snapshots bereits dekodiert)
 * $opts: ['copy' => …, 'draft' => bool]
 */
function render_document(array $doc, array $opts = []): string
{
    $type = doc_type($doc['type']) ?? custom_type_from_snapshot($doc);
    if (!$type) {
        return '<p>Unbekannte Dokumentart.</p>';
    }
    $d = $doc['data'] ?? [];
    $p = $doc['patient_snapshot'] ?? [];
    $u = $doc['issuer_snapshot'] ?? [];
    $draft = !empty($opts['draft']);
    $copy = $opts['copy'] ?? '';
    $uid = 'd' . ($doc['id'] ?? 'x') . substr(md5(($doc['verify_code'] ?? '') . $copy . mt_rand()), 0, 6);

    // Briefkopf des Standorts: eingefroren im Dokument, bei Vorschauen aus der Auswahl im Formular
    $loc = empty($doc['id']) && !empty($d['_location']) ? location_snapshot($d['_location']) : ($u['location'] ?? null);
    // Briefkopf, Logo und Siegel der ausstellenden Fraktion (auch wenn eine andere Behörde das Dokument ansieht)
    $prevFaction = fid();
    set_faction_context((int) ($doc['faction_id'] ?? $prevFaction));
    try {
        $html = with_location($loc, function () use ($type, $doc, $d, $p, $u, $draft, $copy, $uid, $opts) {
            ob_start();
            require SAMS_ROOT . '/app/templates/' . $type['template'] . '.php';
            return ob_get_clean();
        });
        // Empfangsbestätigung (im Spiel unterschrieben) als Zusatzblatt
        if (!empty($d[IC_RECEIPT_FIELD])) {
            $html .= receipt_sheet($doc);
        }
        // Nachträge werden als eigenes Blatt angehängt (Original bleibt unverändert)
        if (empty($opts['no_addenda'])) {
            foreach (document_addenda((int) ($doc['id'] ?? 0)) as $i => $addendum) {
                $html .= addendum_sheet($doc, $addendum, $i + 1);
            }
        }
    } finally {
        set_faction_context($prevFaction);
    }

    $classes = 'doc-wrap' . ($draft ? ' is-draft' : '') . (($doc['status'] ?? '') === 'revoked' ? ' is-revoked' : '');
    return '<div class="' . $classes . '">' . $html . '</div>';
}

/** Blatt „Nachtrag Nr. n zu Dokument …“ im Protokoll-Stil. */
function addendum_sheet(array $doc, array $addendum, int $n): string
{
    $type = doc_type($doc['type']);
    $protoTitle = 'Nachtrag Nr. ' . $n;
    $protoSub = 'zu ' . ($type['short'] ?? 'Dokument') . ' vom ' . fmt_date($doc['issued_at'] ?? '');
    $sigDoc = ['issuer_snapshot' => $addendum['author'], 'issued_at' => $addendum['created_at']] + $doc;
    $p = $doc['patient_snapshot'] ?? [];
    ob_start(); ?>
    <section class="paper a4 proto addendum">
        <?= doc_watermark($doc, false) ?>
        <?php require SAMS_ROOT . '/app/templates/_protohead.php'; ?>
        <h3 class="psec">Bezug</h3>
        <div class="pgrid">
            <div class="pc w4"><span class="fl">Dokument</span><span class="fv"><?= e($type['title'] ?? $doc['type']) ?></span></div>
            <div class="pc w4"><span class="fl">Dokument-Nr. / Prüfcode</span><span class="fv mono"><?= e($doc['number'] ?? '') ?> · <?= e($doc['verify_code'] ?? '') ?></span></div>
            <div class="pc w4"><span class="fl">Patient/in</span><span class="fv"><?= e($p ? patient_name($p, true) : '–') ?></span></div>
        </div>
        <h3 class="psec">Nachtrag vom <?= e(fmt_datetime($addendum['created_at'])) ?></h3>
        <div class="addendum-text printed"><?= nl($addendum['text']) ?></div>
        <p class="small">Dieser Nachtrag ergänzt bzw. berichtigt das oben genannte Dokument. Das Originaldokument bleibt unverändert gültig, soweit es durch diesen Nachtrag nicht berichtigt wird.</p>
        <?= doc_signature($sigDoc, 'rect', 'Unterschrift Verfasser/in des Nachtrags') ?>
        <div style="flex:1"></div>
        <?= doc_footer($doc) ?>
    </section>
    <?php
    return ob_get_clean();
}

/** Deterministische Pseudo-Zufallszahl je Dokument (z.B. Stempelwinkel). */
function doc_rand(array $doc, string $salt, float $min, float $max): float
{
    $h = hexdec(substr(md5(($doc['verify_code'] ?? 'x') . $salt), 0, 8)) / 0xFFFFFFFF;
    return round($min + ($max - $min) * $h, 2);
}

function org(string $key): string
{
    $defaults = [
        'org_name'    => 'San Andreas Medical Services',
        'org_short'   => 'SAMS',
        'org_sub'     => 'Pillbox Hill Medical Center',
        'org_street'  => 'Elgin Avenue 1',
        'org_city'    => '90012 Los Santos, San Andreas',
        'city'        => 'Los Santos',
        'phone'       => '(555) 911-0100',
        'fax'         => '(555) 911-0199',
        'email'       => 'verwaltung@sams.ls',
        'web'         => 'www.sams.ls',
        'ik_number'   => '260 911 234',
        'bsnr'        => '72 911 3400',
        'director'    => 'Dr. med. Ärztliche Direktion',
        'admin_head'  => 'Verwaltungsdirektion',
        'bank'        => 'Maze Bank of Los Santos',
        'iban'        => 'SA21 0911 0000 0123 4567 89',
        'bic'         => 'MAZESALSXXX',
        'register'    => 'Körperschaft des öffentlichen Rechts · State of San Andreas',
        'tax_id'      => 'St.-Nr. 31/911/04711',
        'seal_text'   => 'SAN ANDREAS MEDICAL SERVICES',
        'currency'    => '$',
    ];
    // Weitere Fraktionen: kein Rückgriff auf die Musterdaten des Rettungsdienstes
    if (fid() !== 1) {
        $f = current_faction();
        $defaults = ['org_name' => $f['name'], 'org_short' => $f['short'] ?: $f['name'], 'seal_text' => mb_strtoupper($f['name']), 'currency' => '$', 'city' => 'Los Santos'];
    }
    $loc = $GLOBALS['sams_location'] ?? null;
    if ($loc && isset(LOCATION_FIELDS[$key]) && trim((string) ($loc[$key] ?? '')) !== '') {
        return (string) $loc[$key];
    }
    return (string) setting($key, $defaults[$key] ?? '');
}

/** Firmenlogo (Star of Life) */
function logo_html(string $class = 'doc-logo'): string
{
    $logo = setting('logo', '');
    if ($logo !== '') {
        return '<img class="' . e($class) . '" src="' . e($logo) . '" alt="">';
    }
    return '<img class="' . e($class) . '" src="assets/img/logo.svg" alt="">';
}

/** Briefkopf nach DIN 5008 mit Anschriftenfeld und Informationsblock. */
function doc_letterhead(array $doc, array $opts = []): string
{
    $p = $doc['patient_snapshot'] ?? [];
    $u = $doc['issuer_snapshot'] ?? [];
    $recipient = $opts['recipient'] ?? '';
    if ($recipient === '' && $p && empty($opts['no_patient_address'])) {
        $recipient = patient_salutation_name($p) . "\n" . ($p['address'] ?? '');
    }
    $info = $opts['info'] ?? [];
    $info = array_merge([
        'Dokument-Nr.' => $doc['number'] ?? '',
    ], $info, [
        'Ansprechpartner' => user_display($u),
        'Abteilung' => $u['department'] ?? '',
        'Telefon' => org('phone'),
        'Datum' => fmt_date($doc['issued_at'] ?? now()),
    ]);

    ob_start(); ?>
    <header class="lh">
        <div class="lh-brand">
            <?= logo_html() ?>
            <div>
                <div class="lh-name"><?= e(org('org_name')) ?></div>
                <div class="lh-sub"><?= e(org('org_sub')) ?></div>
                <?php if (!empty($opts['department'])): ?><div class="lh-dept"><?= e($opts['department']) ?></div><?php endif; ?>
            </div>
        </div>
        <div class="lh-contact">
            <?= e(org('org_street')) ?><br>
            <?= e(org('org_city')) ?><br>
            Tel. <?= e(org('phone')) ?><br>
            Fax <?= e(org('fax')) ?><br>
            <?= e(org('email')) ?>
        </div>
    </header>
    <div class="lh-row">
        <div class="lh-address">
            <div class="lh-sender"><?= e(org('org_short')) ?> · <?= e(org('org_street')) ?> · <?= e(org('org_city')) ?></div>
            <div class="lh-recipient"><?= nl(trim($recipient)) ?></div>
        </div>
        <table class="lh-info">
            <?php foreach ($info as $k => $v): if ($v === '' || $v === null) continue; ?>
                <tr><th><?= e($k) ?></th><td><?= e($v) ?></td></tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php
    return ob_get_clean();
}

function patient_salutation_name(array $p): string
{
    $prefix = ['m' => 'Herrn', 'w' => 'Frau'][$p['gender'] ?? ''] ?? '';
    return trim($prefix . ' ' . patient_name($p));
}

/** Kompakter Patientenkasten für Formulare und Berichte. */
function doc_patient_box(array $doc, bool $full = true): string
{
    $p = $doc['patient_snapshot'] ?? [];
    if (!$p) {
        return '';
    }
    ob_start(); ?>
    <table class="pbox">
        <tr>
            <th>Name, Vorname</th><td><strong><?= e(patient_name($p, true)) ?></strong></td>
            <th>geb. am</th><td><?= e(fmt_date($p['dob'] ?? '')) ?></td>
        </tr>
        <?php if ($full): ?>
        <tr>
            <th>Anschrift</th><td><?= e(str_replace("\n", ', ', (string) ($p['address'] ?? ''))) ?></td>
            <th>Bürger-ID</th><td class="mono"><?= e($p['citizen_id'] ?? '') ?></td>
        </tr>
        <tr>
            <th>Kostenträger</th><td><?= e($p['insurance'] ?? '') ?></td>
            <th>Vers.-Nr.</th><td class="mono"><?= e($p['insurance_number'] ?? '') ?></td>
        </tr>
        <?php endif; ?>
    </table>
    <?php
    return ob_get_clean();
}

/** Unterschriftsblock mit optionalem Stempel. $stamp: 'rect' | 'round' | '' */
function doc_signature(array $doc, string $stamp = 'rect', string $caption = '', bool $withPlace = true): string
{
    $u = $doc['issuer_snapshot'] ?? [];
    $caption = $caption ?: ((($u['doctor_number'] ?? '') !== '' || in_array($u['role'] ?? '', ['doctor', 'admin'], true)) ? 'Unterschrift und Stempel des Arztes / der Ärztin' : 'Unterschrift');
    $rot = doc_rand($doc, 'sig', -4, 3);
    ob_start(); ?>
    <div class="sigblock">
        <?php if ($withPlace): ?>
        <div class="sig-place"><span class="hand-filled"><?= e(org('city')) ?>, <?= e(fmt_date($doc['issued_at'] ?? now())) ?></span><span class="sig-cap">Ort, Datum</span></div>
        <?php endif; ?>
        <div class="sig-sign">
            <div class="sig-area">
                <?php if ($stamp !== ''): ?><div class="sig-stamp <?= e($stamp) ?>"><?= $stamp === 'round' ? seal_svg($doc) : doctor_stamp_svg($doc) ?></div><?php endif; ?>
                <?= signature_html($u, $rot) ?>
            </div>
            <span class="sig-cap"><?= e($caption) ?></span>
            <span class="sig-name"><?= e(user_display($u)) ?><?= !empty($u['rank_name']) ? ' · ' . e($u['rank_name']) : '' ?></span>
        </div>
        <?php if (!empty($u['cosigner'])): $c = $u['cosigner']; ?>
        <div class="sig-sign cosign">
            <div class="sig-area"><?= signature_html($c, doc_rand($doc, 'cosig', -3, 3)) ?></div>
            <span class="sig-cap">Gegengezeichnet / freigegeben am <?= e(fmt_date($c['at'] ?? '')) ?></span>
            <span class="sig-name"><?= e(user_display($c)) ?><?= !empty($c['rank_name']) ? ' · ' . e($c['rank_name']) : '' ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function signature_html(array $u, float $rot = -2): string
{
    if (!empty($u['signature']) && str_starts_with((string) $u['signature'], 'data:image/png;base64,')) {
        return '<img class="sig-img" style="transform:rotate(' . $rot . 'deg)" src="' . e($u['signature']) . '" alt="Unterschrift">';
    }
    return '<span class="sig-font" style="transform:rotate(' . $rot . 'deg)">' . e($u['name'] ?? '') . '</span>';
}

/** SVG-Filter für Stempelfarbe mit unregelmäßigem Farbauftrag. */
function ink_filter(string $id, int $seed): string
{
    return '<filter id="' . $id . '" x="-5%" y="-5%" width="110%" height="110%">'
        . '<feTurbulence type="fractalNoise" baseFrequency="0.75" numOctaves="2" seed="' . $seed . '" result="n"/>'
        . '<feDisplacementMap in="SourceGraphic" in2="n" scale="1.4" xChannelSelector="R" yChannelSelector="G" result="d"/>'
        . '<feTurbulence type="fractalNoise" baseFrequency="0.09" numOctaves="2" seed="' . ($seed + 7) . '" result="n2"/>'
        . '<feColorMatrix in="n2" type="matrix" values="0 0 0 0 0  0 0 0 0 0  0 0 0 0 0  0 0 0 2.6 -0.75" result="m"/>'
        . '<feComposite in="d" in2="m" operator="in" result="c"/>'
        . '<feComposite in="c" in2="d" operator="arithmetic" k1="0" k2="0.45" k3="0.55" k4="0"/>'
        . '</filter>';
}

/** Rechteckiger Arztstempel (blau). */
function doctor_stamp_svg(array $doc): string
{
    $u = $doc['issuer_snapshot'] ?? [];
    $id = 'ink' . substr(md5(($doc['verify_code'] ?? '') . 'rect' . mt_rand()), 0, 8);
    $seed = (int) (doc_rand($doc, 'seed', 1, 900));
    $rot = doc_rand($doc, 'stamprot', -5, 4);
    $lines = array_values(array_filter([
        ['t' => user_display($u), 'c' => 'big'],
        ['t' => $u['department'] ?? '', 'c' => ''],
        ['t' => org('org_name'), 'c' => ''],
        ['t' => org('org_street') . ' · ' . org('city'), 'c' => ''],
        ['t' => 'Tel. ' . org('phone') . (!empty($u['doctor_number']) ? ' · Arzt-Nr. ' . $u['doctor_number'] : ''), 'c' => 'small'],
        ['t' => 'BSNR ' . org('bsnr'), 'c' => 'small'],
    ], fn($l) => trim($l['t']) !== ''));
    $h = 14 + count($lines) * 11;
    $svg = '<svg class="stamp-svg" viewBox="0 0 250 ' . $h . '" style="transform:rotate(' . $rot . 'deg)" xmlns="http://www.w3.org/2000/svg">'
        . '<defs>' . ink_filter($id, $seed) . '</defs><g filter="url(#' . $id . ')" fill="#22409a" stroke="#22409a">'
        . '<rect x="2" y="2" width="246" height="' . ($h - 4) . '" rx="3" fill="none" stroke-width="1.6"/>';
    $y = 17;
    foreach ($lines as $l) {
        $size = $l['c'] === 'big' ? 12.5 : ($l['c'] === 'small' ? 8.8 : 9.8);
        $weight = $l['c'] === 'big' ? 700 : 400;
        $svg .= '<text x="125" y="' . $y . '" text-anchor="middle" stroke="none" font-family="Arial, Helvetica, sans-serif" font-size="' . $size . '" font-weight="' . $weight . '">' . e($l['t']) . '</text>';
        $y += 11;
    }
    return $svg . '</g></svg>';
}

/** Runder Dienstsiegel-Stempel. */
function seal_svg(array $doc, string $color = '#1c3c8c'): string
{
    $id = 'ink' . substr(md5(($doc['verify_code'] ?? '') . 'seal' . mt_rand()), 0, 8);
    $pid = 'arc' . substr($id, 3);
    $seed = (int) (doc_rand($doc, 'seed2', 1, 900));
    $rot = doc_rand($doc, 'sealrot', -18, 18);
    $text = mb_strtoupper(org('seal_text'));
    $bottom = mb_strtoupper(org('city')) . ' · ' . mb_strtoupper(org('org_short'));
    return '<svg class="seal-svg" viewBox="0 0 200 200" style="transform:rotate(' . $rot . 'deg)" xmlns="http://www.w3.org/2000/svg">'
        . '<defs>' . ink_filter($id, $seed)
        . '<path id="' . $pid . 't" d="M 26 104 A 74 74 0 1 1 174 104"/>'
        . '<path id="' . $pid . 'b" d="M 34 104 A 66 66 0 0 0 166 104"/></defs>'
        . '<g filter="url(#' . $id . ')" fill="' . $color . '" stroke="' . $color . '">'
        . '<circle cx="100" cy="100" r="94" fill="none" stroke-width="3"/>'
        . '<circle cx="100" cy="100" r="88" fill="none" stroke-width="1"/>'
        . '<circle cx="100" cy="100" r="56" fill="none" stroke-width="1.2"/>'
        . '<text font-family="Arial, Helvetica, sans-serif" font-size="13" font-weight="700" stroke="none">'
        . '<textPath href="#' . $pid . 't" startOffset="3" textLength="220" lengthAdjust="spacingAndGlyphs">' . e($text) . '</textPath></text>'
        . '<text font-family="Arial, Helvetica, sans-serif" font-size="11" font-weight="700" letter-spacing="2" stroke="none">'
        . '<textPath href="#' . $pid . 'b" startOffset="50%" text-anchor="middle">' . e($bottom) . '</textPath></text>'
        . '<text x="29" y="105" font-size="12" stroke="none" text-anchor="middle">★</text><text x="171" y="105" font-size="12" stroke="none" text-anchor="middle">★</text>'
        // Star of Life
        . '<g transform="translate(100 96)" stroke="none">'
        . '<g id="' . $pid . 'arm"><rect x="-7" y="-36" width="14" height="72" rx="1"/></g>'
        . '<use href="#' . $pid . 'arm" transform="rotate(60)"/><use href="#' . $pid . 'arm" transform="rotate(-60)"/>'
        . '<path d="M-1.6 -27 v50 M1.6 -27 v50" stroke="#fff" stroke-width="1.2"/>'
        . '<path d="M0 -24 c7 3 7 8 0 11 c-7 3 -7 8 0 11 c7 3 7 8 0 11" fill="none" stroke="#fff" stroke-width="2.2"/>'
        . '</g>'
        . '<text x="100" y="146" text-anchor="middle" font-family="Arial, Helvetica, sans-serif" font-size="8" stroke="none" letter-spacing="1">DIENSTSIEGEL</text>'
        . '</g></svg>';
}

/** Code-128-B-Barcode als SVG (serverseitig, damit er auch ohne JS druckt). */
function barcode_svg(string $text, float $height = 34): string
{
    static $patterns = [
        '212222', '222122', '222221', '121223', '121322', '131222', '122213', '122312', '132212', '221213',
        '221312', '231212', '112232', '122132', '122231', '113222', '123122', '123221', '223211', '221132',
        '221231', '213212', '223112', '312131', '311222', '321122', '321221', '312212', '322112', '322211',
        '212123', '212321', '232121', '111323', '131123', '131321', '112313', '132113', '132311', '211313',
        '231113', '231311', '112133', '112331', '132131', '113123', '113321', '133121', '313121', '211331',
        '231131', '213113', '213311', '213131', '311123', '311321', '331121', '312113', '312311', '332111',
        '314111', '221411', '431111', '111224', '111422', '121124', '121421', '141122', '141221', '112214',
        '112412', '122114', '122411', '142112', '142211', '241211', '221114', '413111', '241112', '134111',
        '111242', '121142', '121241', '114212', '124112', '124211', '411212', '421112', '421211', '212141',
        '214121', '412121', '111143', '111341', '131141', '114113', '114311', '411113', '411311', '113141',
        '114131', '311141', '411131', '211412', '211214', '211232', '2331112',
    ];
    $codes = [104];
    $text = preg_replace('/[^\x20-\x7E]/', '', $text);
    foreach (str_split($text) as $ch) {
        $codes[] = ord($ch) - 32;
    }
    $sum = 104;
    foreach (array_slice($codes, 1) as $i => $c) {
        $sum += $c * ($i + 1);
    }
    $codes[] = $sum % 103;
    $codes[] = 106;

    $x = 10; // Ruhezone
    $bars = '';
    foreach ($codes as $c) {
        $bar = true;
        foreach (str_split($patterns[$c]) as $w) {
            if ($bar) {
                $bars .= '<rect x="' . $x . '" y="0" width="' . $w . '" height="' . $height . '"/>';
            }
            $x += (int) $w;
            $bar = !$bar;
        }
    }
    $x += 10;
    return '<svg class="barcode" viewBox="0 0 ' . $x . ' ' . $height . '" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><g fill="#000">' . $bars . '</g></svg>';
}

/** QR-Code-Platzhalter, wird per JS (assets/vendor/qrcode.js) gefüllt. */
function qr_html(array $doc, string $class = 'qr'): string
{
    $code = $doc['verify_code'] ?? '';
    $data = $code !== '' ? verify_url($code) : base_url() . '/verify.php';
    return '<div class="' . e($class) . '" data-qr="' . e($data) . '"></div>';
}

/** Fußzeile mit Bankverbindung, Registrierung, Prüfcode. */
function doc_footer(array $doc, string $extra = ''): string
{
    ob_start(); ?>
    <footer class="df">
        <div class="df-verify">
            <?= qr_html($doc) ?>
            <div>
                <strong>Echtheit prüfen</strong><br>
                <?= e(preg_replace('#^https?://#', '', base_url())) ?>/verify.php<br>
                Prüfcode: <span class="mono"><?= e(chunk_split($doc['verify_code'] ?? '', 4, ' ')) ?></span>
                <div class="df-barcode"><?= barcode_svg($doc['number'] ?? 'ENTWURF') ?><span class="mono"><?= e($doc['number'] ?? '') ?></span></div>
            </div>
        </div>
        <div class="df-cols">
            <div><strong><?= e(org('org_name')) ?></strong><br><?= e(org('org_sub')) ?><br><?= e(org('org_street')) ?><br><?= e(org('org_city')) ?></div>
            <div>Tel. <?= e(org('phone')) ?><br>Fax <?= e(org('fax')) ?><br><?= e(org('email')) ?><br><?= e(org('web')) ?></div>
            <div><?= e(org('bank')) ?><br>IBAN <?= e(org('iban')) ?><br>BIC <?= e(org('bic')) ?><br>IK <?= e(org('ik_number')) ?></div>
            <div>Ärztliche Direktion:<br><?= e(org('director')) ?><br><?= e(org('register')) ?><br><?= e(org('tax_id')) ?></div>
        </div>
        <?php if ($extra !== ''): ?><div class="df-extra"><?= $extra ?></div><?php endif; ?>
    </footer>
    <?php
    return ob_get_clean();
}

/** Datumsfelder im Formularstil (TT MM JJ). */
function date_boxes($date): string
{
    $s = $date ? date('dmy', strtotime((string) $date)) : '      ';
    $out = '<span class="dboxes">';
    foreach (str_split($s, 2) as $part) {
        $out .= '<span>' . e($part) . '</span>';
    }
    return $out . '</span>';
}

/** Hilfsfunktion: Wert oder Platzhalterlinie */
function v($value, string $empty = '—'): string
{
    if (is_array($value)) {
        $value = implode(', ', $value);
    }
    return trim((string) $value) === '' ? '<span class="empty">' . e($empty) . '</span>' : nl((string) $value);
}

function opt_label(array $type, string $field, $value): string
{
    foreach ($type['fields'] as $f) {
        if (($f['name'] ?? '') === $field) {
            if (is_array($value)) {
                return implode(', ', array_map(fn($x) => $f['options'][$x] ?? $x, $value));
            }
            return (string) ($f['options'][$value] ?? $value);
        }
    }
    return is_array($value) ? implode(', ', $value) : (string) $value;
}

/** Wasserzeichen für stornierte Dokumente / Entwürfe */
function doc_watermark(array $doc, bool $draft): string
{
    if ($draft) {
        return '<div class="watermark">ENTWURF</div>';
    }
    if (($doc['status'] ?? '') === 'revoked') {
        return '<div class="watermark red">STORNIERT</div>';
    }
    return '';
}

/** Standard-A4-Seite mit Briefkopf, Inhalt, Fußzeile. */
function a4_open(array $doc, bool $draft, string $extraClass = ''): string
{
    return '<section class="paper a4 ' . e($extraClass) . '">'
        . '<span class="fold f1"></span><span class="fold f2"></span><span class="fold punch"></span>'
        . doc_watermark($doc, $draft);
}

function a4_close(): string
{
    return '</section>';
}

/**
 * Fremdes Bild (z. B. Ausweisfoto per URL) über den eigenen Server laden und als data-URI einbetten.
 * Grund: Beim „Als Bild speichern“ (html2canvas) lässt der Browser Bilder fremder Seiten ohne CORS-Freigabe weg.
 * Das Bild wird neu kodiert (max. 600 px), in storage/photos zwischengespeichert; Fehlschläge werden 2 Minuten gemerkt
 * (Grund steht in der .fail-Datei, siehe remote_image_error()).
 * Nur http(s) und nur öffentliche Adressen (kein Zugriff auf interne Dienste), höchstens 3 Weiterleitungen, 3 MB, 8 Sekunden.
 */
function remote_image_data_uri(string $url): string
{
    if (!preg_match('#^https?://#i', $url) || strlen($url) > 2000) {
        return '';
    }
    $file = storage_path('photos/' . sha1($url) . '.img');
    if (is_file($file)) {
        return (string) file_get_contents($file);
    }
    if (is_file($file . '.fail') && filemtime($file . '.fail') > time() - 120) {
        return '';
    }
    [$data, $error] = remote_image_fetch($url);
    if ($data === '') {
        @file_put_contents($file . '.fail', $error);
        return '';
    }
    file_put_contents($file, $data);
    @unlink($file . '.fail');
    return $data;
}

/** Grund, warum ein Foto nicht eingebettet werden konnte ('' = kein Fehler bekannt). */
function remote_image_error(string $url): string
{
    $fail = storage_path('photos/' . sha1($url) . '.img.fail');
    return is_file($fail) ? ((string) file_get_contents($fail) ?: 'unbekannter Fehler') : '';
}

/** @return array{0: string, 1: string} [data-URI, Fehlergrund] */
function remote_image_fetch(string $url): array
{
    $current = $url;
    for ($hop = 0; $hop < 4; $hop++) {
        $parts = parse_url($current);
        $host = trim((string) ($parts['host'] ?? ''), '[]');
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if ($host === '' || !in_array($scheme, ['http', 'https'], true)) {
            return ['', 'ungültige Adresse'];
        }
        // Adresse auflösen (IPv4, sonst IPv6) und nur öffentliche Ziele erlauben
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : (gethostbynamel($host)[0] ?? '');
        if ($ip === '' && function_exists('dns_get_record')) {
            $ip = (string) (@dns_get_record($host, DNS_AAAA)[0]['ipv6'] ?? '');
        }
        if ($ip === '') {
            return ['', 'Rechnername nicht auflösbar (DNS) – hat der Webserver Internetzugang?'];
        }
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) && !getenv('SAMS_ALLOW_LOCAL_FETCH')) {
            return ['', 'interne Adresse nicht erlaubt'];
        }
        $port = (int) ($parts['port'] ?? ($scheme === 'https' ? 443 : 80));
        $body = '';
        $ch = curl_init($current);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => false, CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 8, CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_RESOLVE => [$host . ':' . $port . ':' . (str_contains($ip, ':') ? '[' . $ip . ']' : $ip)], CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            // manche Bildseiten blockieren unbekannte Programme – wie ein Browser anfragen
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0 Safari/537.36',
            CURLOPT_HTTPHEADER => ['Accept: image/avif,image/webp,image/png,image/jpeg,image/*;q=0.8'],
            CURLOPT_WRITEFUNCTION => function ($c, $chunk) use (&$body) {
                $body .= $chunk;
                return strlen($body) > 3 * 1024 * 1024 ? 0 : strlen($chunk); // zu groß → abbrechen
            },
        ]);
        curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $location = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $err = curl_error($ch);
        curl_close($ch);
        if ($status >= 300 && $status < 400 && $location !== '') {
            $current = $location;
            continue;
        }
        if ($status === 0) {
            return ['', 'keine Verbindung (' . ($err ?: 'Zeitüberschreitung') . ')'];
        }
        if ($status !== 200) {
            return ['', 'HTTP ' . $status . ($status === 404 || $status === 403 ? ' – Link abgelaufen oder gesperrt? (Discord-Links verfallen nach einiger Zeit)' : '')];
        }
        $data = image_to_data_uri($body);
        return $data !== '' ? [$data, ''] : ['', 'keine Bilddatei (die Adresse muss direkt auf ein Bild zeigen, nicht auf eine Seite)'];
    }
    return ['', 'zu viele Weiterleitungen'];
}

/** Bilddaten prüfen, neu kodieren (ohne Metadaten), auf 600 px begrenzen und als data-URI liefern. */
function image_to_data_uri(string $bytes): string
{
    $info = @getimagesizefromstring($bytes);
    if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp', 'image/gif'], true) || $info[0] * $info[1] > 40_000_000) {
        return '';
    }
    $img = function_exists('imagecreatefromstring') ? @imagecreatefromstring($bytes) : false;
    if (!$img) {
        // GD kann das Format nicht (z. B. WebP ohne Unterstützung): Original einbetten, wenn es klein genug ist
        return strlen($bytes) <= 1500000 ? 'data:' . $info['mime'] . ';base64,' . base64_encode($bytes) : '';
    }
    $w = imagesx($img);
    $h = imagesy($img);
    $f = min(1, 600 / max($w, $h));
    $out = imagecreatetruecolor(max(1, (int) round($w * $f)), max(1, (int) round($h * $f)));
    imagealphablending($out, false);
    imagesavealpha($out, true);
    imagefill($out, 0, 0, imagecolorallocatealpha($out, 255, 255, 255, 127));
    imagecopyresampled($out, $img, 0, 0, 0, 0, imagesx($out), imagesy($out), $w, $h);
    ob_start();
    $png = $info['mime'] === 'image/png' || $info['mime'] === 'image/gif';
    $png ? imagepng($out, null, 6) : imagejpeg($out, null, 88);
    return 'data:image/' . ($png ? 'png' : 'jpeg') . ';base64,' . base64_encode((string) ob_get_clean());
}
