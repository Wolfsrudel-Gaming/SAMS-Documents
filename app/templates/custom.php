<?php
defined('SAMS_ROOT') || exit;
/**
 * Eigene Dokumentart aus dem Vorlagen-Editor. Verwendet die beim Ausstellen
 * eingefrorene Vorlage (_tpl), sonst die aktuelle Definition (Vorschau/Entwurf).
 */
$tpl = is_array($d['_tpl'] ?? null) ? $d['_tpl'] : ($type['custom'] ?? []);
$fields = array_values(array_filter($tpl['fields'] ?? $type['fields'], fn($f) => ($f['print'] ?? true) !== false)); // 'print' => false: nur Steuerfeld (z. B. „Einreichen bei“)
$layout = $tpl['layout'] ?? 'letter';
$seal = $tpl['seal'] ?? 'rect';
$title = $tpl['title'] ?? $type['title'];
$body = (string) ($tpl['body'] ?? '');
$emp = $d['_employee'] ?? [];
$isPatient = $p && empty($p['employee']);
$used = custom_used_fields($body);
$sigFields = array_filter($fields, fn($f) => ($f['type'] ?? '') === 'signature');
$stamp = $seal === 'none' ? '' : $seal;
$wmap = ['full' => 'w12', 'half' => 'w6', 'third' => 'w4', 'quarter' => 'w3'];

$extraSignatures = function () use ($sigFields, $d) {
    $out = '';
    foreach ($sigFields as $f) {
        $img = $d[$f['name']] ?? '';
        $out .= '<div class="sigblock"><div class="sig-sign"><div class="sig-area">'
            . ($img !== '' ? '<img class="sig-img" src="' . e($img) . '" alt="">' : '')
            . '</div><span class="sig-cap">' . e($f['label']) . '</span></div></div>';
    }
    return $out;
};

if ($layout === 'letter'):
    $recipient = '';
    foreach ($fields as $f) {
        if (($f['name'] ?? '') === 'empfaenger' || ($f['name'] ?? '') === 'recipient') {
            $recipient = (string) ($d[$f['name']] ?? '');
            $used[] = $f['name'];
        }
    }
    if ($recipient === '' && $emp) {
        $recipient = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? '')) . "\n" . dt('– im Hause –');
    }
    echo a4_open($doc, $draft);
    echo doc_letterhead($doc, ['recipient' => $recipient, 'no_patient_address' => $recipient !== '' || !$isPatient]);
    $rest = array_filter($fields, fn($f) => isset($f['name']) && !in_array($f['name'], $used, true) && !in_array($f['type'], ['signature', 'heading'], true)
        && trim(custom_field_value($f, $d[$f['name']] ?? '')) !== '' && !($f['type'] === 'checkbox' && empty($d[$f['name']])));
    ?>
    <div class="doc-body">
        <h1 class="doc-title"><?= e($title) ?></h1>
        <?php if ($isPatient): ?><?= doc_patient_box($doc, false) ?><?php endif; ?>
        <?php if ($body !== ''): ?><div class="prose"><?= custom_render_body($body, $doc, $fields) ?></div><?php endif; ?>
        <?php if ($rest): ?>
            <table class="tbl two">
                <?php foreach ($rest as $f): ?><tr><th><?= e($f['label']) ?></th><td><?= nl(custom_field_value($f, $d[$f['name']] ?? '')) ?></td></tr><?php endforeach; ?>
            </table>
        <?php endif; ?>
        <?= doc_signature($doc, $stamp) ?>
        <?php if ($sigFields): ?><div class="proto-sign"><?= $extraSignatures() ?></div><?php endif; ?>
    </div>
    <?= doc_footer($doc) ?>
    <?= a4_close() ?>
<?php else:
    $protoTitle = $title;
    $protoSub = org('org_sub');
    echo a4_open($doc, $draft, 'proto custom-' . $layout);
    require __DIR__ . '/_protohead.php';
    ?>
    <?php if ($isPatient): ?>
        <div class="pgrid">
            <div class="pc w6"><span class="fl"><?= e(dt('Name, Vorname')) ?></span><span class="fv"><strong><?= e(patient_name($p, true)) ?></strong></span></div>
            <div class="pc w3"><span class="fl"><?= e(dt('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
            <div class="pc w3"><span class="fl"><?= e(dt('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
        </div>
    <?php elseif ($emp): ?>
        <div class="pgrid">
            <div class="pc w6"><span class="fl"><?= e(dt('Mitarbeiter/in')) ?></span><span class="fv"><strong><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?></strong></span></div>
            <div class="pc w3"><span class="fl"><?= e(dt('Rang')) ?></span><span class="fv"><?= e($emp['rank_name'] ?? '') ?></span></div>
            <div class="pc w3"><span class="fl"><?= e(dt('Dienstnummer')) ?></span><span class="fv mono"><?= e($emp['badge_number'] ?? '') ?></span></div>
        </div>
    <?php endif; ?>
    <?php
    // Felder als Raster; Zwischenüberschriften beginnen einen neuen Abschnitt
    $open = false;
    foreach ($fields as $f):
        if ($f['type'] === 'signature') {
            continue;
        }
        if ($f['type'] === 'heading'):
            if ($open) { echo '</div>'; $open = false; } ?>
            <h3 class="psec"><?= e($f['label']) ?></h3>
        <?php continue; endif;
        if (!$open) { echo '<div class="pgrid">'; $open = true; }
        $v = $d[$f['name']] ?? '';
        $w = $wmap[$f['width'] ?? 'full'] ?? 'w12'; ?>
        <div class="pc <?= $w ?><?= $f['type'] === 'textarea' ? ' tall' : '' ?>"><span class="fl"><?= e($f['label']) ?></span>
            <?php if ($f['type'] === 'checkbox'): ?><span class="fv chk"><?= box(!empty($v)) ?> <?= e(dt('ja')) ?></span>
            <?php elseif (in_array($f['type'], ['checkboxes', 'radio', 'select'], true)): ?><span class="fv chk"><?php foreach ($f['options'] ?? [] as $ov => $o): /* gespeichert wird der Schlüssel, gedruckt die (im US-Set übersetzte) Bezeichnung */ ?><span class="nowrap"><?= box(is_array($v) ? in_array((string) $ov, array_map('strval', $v), true) : (string) $v === (string) $ov) ?> <?= e((string) $o) ?></span> <?php endforeach; ?></span>
            <?php else: ?><span class="fv"><?= nl(custom_field_value($f, $v)) ?></span><?php endif; ?>
        </div>
    <?php endforeach;
    if ($open) { echo '</div>'; } ?>
    <?php if ($body !== ''): ?><div class="revers-text"><?= custom_render_body($body, $doc, $fields) ?></div><?php endif; ?>
    <div class="proto-sign<?= count($sigFields) > 1 ? ' three' : '' ?>">
        <?= doc_signature($doc, $stamp, '', false) ?>
        <?= $extraSignatures() ?>
    </div>
    <?= doc_footer($doc) ?>
    <?= a4_close() ?>
<?php endif;
