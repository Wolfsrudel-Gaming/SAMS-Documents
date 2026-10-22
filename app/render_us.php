<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * US-Gestaltung der Dokumente (Einstellungen → Portal → Gestaltung „USA“).
 *
 * Alle Beschriftungen bleiben deutsch (bzw. folgen der Dokumentsprache über dt()), Aufbau und Optik folgen
 * US-Vorbildern: Papierformat US Letter, keine DIN-Falzmarken, Behördenkopf mit Wappen, Formularnummer mit
 * Revisionsstand, nummerierte Formularfelder, Gerichts-Rubrum („Volk des Staates San Andreas gegen …“) auf
 * Schriftsatzpapier mit Zeilennummern, Patientenetikett im Klinikformular, Verteilerzeile für Durchschläge.
 *
 * Jede Dokumentart bekommt ihre eigene Gestaltung aus einer Familie (style) plus eigener Einstellung
 * (app/us_layouts/*.php): Formularnummer, Banner, Farbe, Nummerierung, Abschnittsbuchstaben, Zusatzblöcke.
 */

/** Familien der US-Gestaltung. */
const US_STYLES = [
    'police'      => 'Polizeibericht (Behördenkopf, Fall-Nr., nummerierte Felder, Prüfvermerk)',
    'court'       => 'Gerichtsdokument (Schriftsatzpapier, Rubrum, Anordnung)',
    'pleading'    => 'Anwaltsschriftsatz (Kanzleiblock, Rubrum, Schriftsatzpapier)',
    'medical'     => 'Klinikformular (Patientenetikett, Formularleiste)',
    'ems'         => 'Rettungsdienstbericht (Einsatzleiste, Zeiten)',
    'fire'        => 'Feuerwehrbericht (Einsatznummer, Abschnitte A–Z)',
    'rescue'      => 'Spezialrettung (Einsatznummer, Abschnitte)',
    'gov'         => 'Behördenschreiben (Staatswappen, Aktenzeichen)',
    'certificate' => 'Urkunde (Zierrahmen, Siegel)',
    'hr'          => 'Personalformular (Personalabteilung, Mitarbeiterblock)',
];

/** Standardfamilie je Fraktionsart. */
const US_KIND_STYLES = ['medical' => 'medical', 'police' => 'police', 'justice' => 'court', 'law' => 'pleading', 'fire' => 'fire', 'rescue' => 'rescue', 'government' => 'gov'];

/** Einstellungen aller Dokumentarten (app/us_layouts/*.php: Schlüssel => Einstellung). */
function us_layout_registry(): array
{
    static $all = null;
    if ($all === null) {
        $all = [];
        foreach (glob(SAMS_ROOT . '/app/us_layouts/*.php') ?: [] as $file) {
            $all += (array) require $file;
        }
    }
    return $all;
}

/**
 * Gestaltung einer Dokumentart: style, form (Formularnummer), title/subtitle, banner, color, numbered (Felder nummerieren),
 * sections ('letters'|'numbers'|false), columns, blocks (Zusatzblöcke), copies (Verteiler), caption (Rubrum), seal.
 */
function us_layout(array $type): array
{
    $key = (string) ($type['key'] ?? '');
    $kind = (string) (is_array($type['kinds'] ?? null) ? ($type['kinds'][0] ?? 'medical') : faction_kind());
    $cfg = us_layout_registry()[$key] ?? [];
    $style = $cfg['style'] ?? (($type['kinds'] ?? null) === '*' ? 'hr' : (US_KIND_STYLES[$kind] ?? 'gov'));
    return $cfg + [
        'style' => $style,
        'form' => '',
        'title' => $type['custom']['title'] ?? $type['title'] ?? '',
        'subtitle' => '',
        'banner' => '',
        'color' => '',
        'numbered' => in_array($style, ['police', 'fire', 'rescue', 'medical', 'ems'], true),
        'sections' => in_array($style, ['fire', 'rescue'], true) ? 'letters' : false,
        'blocks' => [],
        'copies' => [],
        'caption' => in_array($style, ['court', 'pleading'], true),
        'seal' => in_array($style, ['court', 'gov', 'certificate'], true) ? 'round' : '',
    ];
}

/** Formularnummer mit Revisionsstand, z. B. „LSPD 03.11 (Rev. 10/26)“. */
function us_form_number(array $doc, array $cfg, array $type = []): string
{
    $form = (string) ($cfg['form'] ?? '');
    if ($form === '') {
        $form = mb_strtoupper(org('org_short')) . ' ' . ($type['code'] ?? 'DOC');
    }
    return $form . ' (Rev. ' . date('m/y', strtotime((string) ($doc['issued_at'] ?? now()))) . ')';
}

function us_paper_open(array $doc, bool $draft, string $extraClass = ''): string
{
    return '<section class="paper letter us ' . e($extraClass) . '">' . doc_watermark($doc, $draft);
}

/** Wert eines Feldes für den Druck (Auswahl als Kästchenreihe wie auf US-Vordrucken). */
function us_field_value(array $f, $v): string
{
    $type = $f['type'] ?? 'text';
    if (in_array($type, ['radio', 'select', 'checkboxes'], true) && !empty($f['options']) && count($f['options']) <= 8) {
        $sel = array_map('strval', (array) $v);
        $out = '';
        foreach ($f['options'] as $key => $label) {
            if ((string) $key === '') {
                continue;
            }
            $out .= '<span class="us-opt">' . box(in_array((string) $key, $sel, true)) . ' ' . e((string) $label) . '</span>';
        }
        return '<span class="us-opts">' . $out . '</span>';
    }
    if ($type === 'checkbox') {
        return '<span class="us-opts"><span class="us-opt">' . box(!empty($v)) . ' ' . e(dt('ja')) . '</span><span class="us-opt">' . box(empty($v)) . ' ' . e(dt('nein')) . '</span></span>';
    }
    return nl(custom_field_value($f, $v));
}

/** Behördenkopf je Familie. */
function us_header(array $doc, array $cfg, array $type = []): string
{
    $style = $cfg['style'];
    $title = dt((string) $cfg['title']);
    $sub = (string) $cfg['subtitle'] !== '' ? dt((string) $cfg['subtitle']) : '';
    $d = $doc['data'] ?? [];
    $case = (string) ($d['aktenzeichen'] ?? '');
    $number = doc_code_display((string) ($doc['number'] ?? ''));
    $state = mb_strtoupper(dt('Staat San Andreas'));
    ob_start();
    if (in_array($style, ['court', 'pleading'], true)): ?>
        <?php if ($style === 'pleading'): $u = $doc['issuer_snapshot'] ?? []; ?>
        <div class="us-atty">
            <?= e(user_display($u)) ?><?= ($u['badge_number'] ?? '') !== '' ? ' (' . e(dt('Zulassungs-Nr.')) . ' ' . e($u['badge_number']) . ')' : '' ?><br>
            <?= e(org('org_name')) ?><br><?= e(org('org_street')) ?><br><?= e(org('org_city')) ?><br><?= e(dt('Tel.')) ?> <?= e(org('phone')) ?><br>
            <?= e(dt('Rechtsbeistand für')) ?>: <?= e(($doc['patient_snapshot'] ?? []) ? patient_name($doc['patient_snapshot']) : '—') ?>
        </div>
        <?php endif; ?>
        <div class="us-court-name"><?= $style === 'court' ? e(mb_strtoupper(org('org_name'))) : e(mb_strtoupper(dt('Gericht des Staates San Andreas'))) ?><small><?= e(org('org_sub') !== '' ? org('org_sub') : org('org_city')) ?></small></div>
        <?php if ($cfg['caption']): ?><?= us_caption($doc, $cfg, $title, $sub) ?><?php else: ?><h1 class="us-title-plain"><?= e(mb_strtoupper($title)) ?></h1><?php if ($sub !== ''): ?><div class="us-sub-plain"><?= e($sub) ?></div><?php endif; ?><?php endif; ?>
    <?php elseif ($style === 'certificate'): ?>
        <div class="us-cert-head">
            <?= logo_html('us-cert-logo') ?>
            <div class="us-cert-state"><?= e($state) ?></div>
            <div class="us-cert-org"><?= e(org('org_name')) ?></div>
            <h1 class="us-cert-title"><?= e($title) ?></h1>
            <?php if ($sub !== ''): ?><div class="us-cert-sub"><?= e($sub) ?></div><?php endif; ?>
        </div>
    <?php elseif ($style === 'medical' || $style === 'ems' || $style === 'hr'): $p = $doc['patient_snapshot'] ?? []; $emp = $d['_employee'] ?? []; ?>
        <header class="us-mhead">
            <div class="us-mhead-org"><?= logo_html('us-mlogo') ?><div><b><?= e(org('org_name')) ?></b><br><?= e(org('org_sub')) ?><br><small><?= e(org('org_street')) ?> · <?= e(org('org_city')) ?> · <?= e(dt('Tel.')) ?> <?= e(org('phone')) ?></small></div></div>
            <?php if ($style === 'hr'): ?>
                <div class="us-label hr"><span class="us-label-cap"><?= e(dt('Personalabteilung')) ?></span>
                    <div><?= e(dt('Mitarbeiter/in')) ?>: <b><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ($p ? patient_name($p) : '')))) ?></b></div>
                    <div><?= e(dt('Dienstnummer')) ?>: <span class="mono"><?= e($emp['badge_number'] ?? '') ?></span> · <?= e(dt('Rang')) ?>: <?= e($emp['rank_name'] ?? '') ?></div></div>
            <?php else: ?>
                <div class="us-label"><span class="us-label-cap"><?= e(dt('Patientenetikett')) ?></span>
                    <div><b><?= e($p ? patient_name($p, true) : dt('UNBEKANNTE PERSON')) ?></b></div>
                    <div><?= e(dt('geb. am')) ?> <?= e(fmt_date($p['dob'] ?? '')) ?> · <?= e(strtoupper((string) ($p['gender'] ?? ''))) ?></div>
                    <div><?= e(dt('Bürger-ID')) ?> <span class="mono"><?= e($p['citizen_id'] ?? '') ?></span></div></div>
            <?php endif; ?>
        </header>
        <div class="us-mtitle"><h1><?= e(mb_strtoupper($title)) ?></h1><?php if ($sub !== ''): ?><small><?= e($sub) ?></small><?php endif; ?><span class="mono"><?= e($number) ?></span></div>
    <?php else: /* police, fire, rescue, gov */ ?>
        <header class="us-ahead">
            <?= logo_html('us-alogo') ?>
            <div class="us-ahead-c">
                <div class="us-ahead-state"><?= e($state) ?></div>
                <div class="us-ahead-org"><?= e(mb_strtoupper(org('org_name'))) ?></div>
                <div class="us-ahead-sub"><?= e(org('org_sub')) ?></div>
            </div>
            <div class="us-ahead-case">
                <span class="fl"><?= e($case !== '' ? dt('Aktenzeichen') : dt((string) (($cfg['case_label'] ?? '') ?: 'Dokument-Nr.'))) ?></span>
                <b class="mono"><?= e($case !== '' ? $case : $number) ?></b>
                <?= barcode_svg($case !== '' ? $case : ($number ?: 'ENTWURF'), 18) ?>
            </div>
        </header>
        <div class="us-band"><h1><?= e(mb_strtoupper($title)) ?></h1><?php if ($sub !== ''): ?><small><?= e($sub) ?></small><?php endif; ?></div>
    <?php endif;
    if ((string) $cfg['banner'] !== ''): ?><div class="us-banner"><?= e(dt((string) $cfg['banner'])) ?></div><?php endif;
    return ob_get_clean();
}

/** Rubrum: „Volk des Staates San Andreas gegen …“ mit Aktenzeichen und Dokumenttitel. */
function us_caption(array $doc, array $cfg, string $title, string $sub = ''): string
{
    $p = $doc['patient_snapshot'] ?? [];
    $d = $doc['data'] ?? [];
    $party = (string) ($cfg['party'] ?? 'Volk des Staates San Andreas');
    ob_start(); ?>
    <table class="us-caption">
        <tr>
            <td class="us-caption-l">
                <b><?= e(mb_strtoupper(dt($party))) ?>,</b><br><span class="small"><?= e(dt((string) ($cfg['party_role'] ?? 'Anklagende Partei'))) ?>,</span>
                <div class="us-caption-v"><?= e(dt('gegen')) ?></div>
                <b><?= e($p ? mb_strtoupper(patient_name($p)) : '______________________') ?>,</b><br><span class="small"><?= e(dt((string) ($cfg['defendant_role'] ?? 'Beschuldigte Person'))) ?>.</span>
            </td>
            <td class="us-caption-r">
                <span class="fl"><?= e(dt('Aktenzeichen')) ?></span><b class="mono"><?= e((string) ($d['aktenzeichen'] ?? '') ?: doc_code_display((string) ($doc['number'] ?? ''))) ?></b>
                <div class="us-caption-title"><?= e(mb_strtoupper($title)) ?></div>
                <?php if ($sub !== ''): ?><div class="small"><?= e($sub) ?></div><?php endif; ?>
            </td>
        </tr>
    </table>
    <?php
    return ob_get_clean();
}

/** Block mit den Angaben zur betroffenen Person bzw. zum Personal (nummeriert). */
function us_person_block(array $doc, array $cfg, int &$n): string
{
    $p = $doc['patient_snapshot'] ?? [];
    $emp = ($doc['data'] ?? [])['_employee'] ?? [];
    if (in_array($cfg['style'], ['medical', 'ems', 'hr'], true) || ($cfg['caption'] === true && $p) || !empty($cfg['no_person']) || (!$p && !$emp)) {
        return ''; // Klinik und Personal: Etikett oben; Gericht: Name steht im Rubrum
    }
    $num = function (string $label) use ($cfg, &$n): string {
        return ($cfg['numbered'] ? (++$n) . '. ' : '') . dt($label);
    };
    $sex = ['m' => dt('männlich'), 'w' => dt('weiblich'), 'd' => dt('divers')][$p['gender'] ?? ''] ?? '';
    ob_start(); ?>
    <div class="us-sec"><?= e(mb_strtoupper(dt($emp && !$p ? 'Mitarbeiter/in' : (string) ($cfg['person_label'] ?? 'Angaben zur Person')))) ?></div>
    <div class="us-grid">
        <?php if ($p): ?>
        <div class="us-cell w6"><span class="fl"><?= e($num('Name, Vorname')) ?></span><span class="fv"><b><?= e(patient_name($p, true)) ?></b></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($num('geb. am')) ?></span><span class="fv"><?= e(fmt_date($p['dob'] ?? '')) ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($num('Geschlecht')) ?></span><span class="fv"><?= e($sex) ?></span></div>
        <div class="us-cell w2"><span class="fl"><?= e($num('Bürger-ID')) ?></span><span class="fv mono"><?= e($p['citizen_id'] ?? '') ?></span></div>
        <?php if (($p['address'] ?? '') !== ''): ?><div class="us-cell w12"><span class="fl"><?= e($num('Anschrift')) ?></span><span class="fv"><?= e(str_replace("\n", ', ', (string) $p['address'])) ?></span></div><?php endif; ?>
        <?php else: ?>
        <div class="us-cell w6"><span class="fl"><?= e($num('Name')) ?></span><span class="fv"><b><?= e(trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''))) ?></b></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($num('Rang')) ?></span><span class="fv"><?= e($emp['rank_name'] ?? '') ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e($num('Dienstnummer')) ?></span><span class="fv mono"><?= e($emp['badge_number'] ?? '') ?></span></div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/** Felder als US-Formularraster (nummeriert, Abschnitte mit Buchstaben) oder als Fließtext-Abschnitte. */
function us_fields(array $fields, array $d, array $cfg, int &$n, array $skip = []): string
{
    $wmap = ['full' => 'w12', 'half' => 'w6', 'third' => 'w4', 'quarter' => 'w3'];
    $letter = 0;
    $prose = in_array($cfg['style'], ['court', 'pleading', 'gov', 'certificate'], true);
    ob_start();
    $open = false;
    foreach ($fields as $f) {
        $t = $f['type'] ?? 'text';
        if ($t === 'signature' || (isset($f['name']) && in_array($f['name'], $skip, true)) || ($f['print'] ?? true) === false) {
            continue;
        }
        if ($t === 'heading') {
            if ($open) {
                echo '</div>';
                $open = false;
            }
            $prefix = $cfg['sections'] === 'letters' ? chr(65 + $letter++) . ' – ' : ($cfg['sections'] === 'numbers' ? (++$letter) . '. ' : '');
            echo '<div class="us-sec">' . e($prefix . mb_strtoupper(dt((string) $f['label']))) . '</div>';
            continue;
        }
        $v = $d[$f['name']] ?? '';
        if ($prose) {
            if (trim(is_array($v) ? implode('', $v) : (string) $v) === '' || ($t === 'checkbox' && empty($v))) {
                continue;
            }
            echo '<div class="us-para"><b>' . e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt((string) $f['label'])) . ':</b> ' . (in_array($t, ['radio', 'select', 'checkboxes'], true) ? e(custom_field_value($f, $v)) : nl(custom_field_value($f, $v))) . '</div>';
            continue;
        }
        if (!$open) {
            echo '<div class="us-grid">';
            $open = true;
        }
        $nOpt = count($f['options'] ?? []);
        $w = in_array($t, ['textarea', 'items'], true) || (in_array($t, ['radio', 'checkboxes', 'select'], true) && $nOpt > 5) ? 'w12'
            : (in_array($t, ['radio', 'checkboxes', 'select'], true) && $nOpt > 3 ? 'w6' : ($wmap[$f['width'] ?? ''] ?? 'w4'));
        $val = $t === 'items' ? us_items_table($f, (array) $v) : us_field_value($f, $v);
        echo '<div class="us-cell ' . $w . ($t === 'textarea' ? ' tall' : '') . '"><span class="fl">' . e(($cfg['numbered'] ? (++$n) . '. ' : '') . dt((string) $f['label'])) . '</span><span class="fv">' . $val . '</span></div>';
    }
    if ($open) {
        echo '</div>';
    }
    return ob_get_clean();
}

/** Große „Gültig bis“-Box (Genehmigungen, Lizenzen, Urkunden): Feld aus der Art-Definition 'valid'. */
function us_valid_box(array $doc, array $cfg): string
{
    $type = doc_type((string) ($doc['type'] ?? '')) ?? [];
    $until = (string) (($doc['data'] ?? [])[$type['valid'] ?? ''] ?? '');
    return '<div class="us-valid"><span class="fl">' . e(dt('Gültig bis')) . '</span><b>' . ($until !== '' ? e(fmt_date($until)) : e(dt('unbefristet'))) . '</b></div>';
}

/** Zeitleiste für Einsatzberichte: Felder aus $cfg['times'] (Feldname => Beschriftung). */
function us_times_strip(array $doc, array $cfg): string
{
    $d = $doc['data'] ?? [];
    $out = '';
    foreach ((array) ($cfg['times'] ?? []) as $field => $label) {
        $out .= '<div><span class="fl">' . e(dt((string) $label)) . '</span><b class="mono">' . e((string) ($d[$field] ?? '')) . '</b></div>';
    }
    return $out === '' ? '' : '<div class="us-times">' . $out . '</div>';
}

function us_items_table(array $f, array $rows): string
{
    $cols = $f['columns'] ?? [];
    $rows = array_values(array_filter($rows, fn($r) => is_array($r) && trim(implode('', array_map('strval', $r))) !== ''));
    $out = '<table class="us-items"><tr>';
    foreach ($cols as $c) {
        $out .= '<th>' . e(dt((string) ($c['label'] ?? ''))) . '</th>';
    }
    $out .= '</tr>';
    foreach ($rows ?: [[]] as $r) {
        $out .= '<tr>';
        foreach ($cols as $k => $c) {
            $out .= '<td>' . e((string) ($r[$k] ?? '')) . '</td>';
        }
        $out .= '</tr>';
    }
    return $out . '</table>';
}

/** Zusatzblöcke nach US-Vorbild. */
function us_blocks(array $blocks, array $doc, array $cfg): string
{
    $out = '';
    foreach ($blocks as $b) {
        $out .= match ($b) {
            'miranda' => '<div class="us-box us-miranda"><b>' . e(dt('Belehrung über die Rechte (Miranda)')) . '</b><ol>'
                . '<li>' . e(dt('Sie haben das Recht zu schweigen.')) . '</li>'
                . '<li>' . e(dt('Alles, was Sie sagen, kann und wird vor Gericht gegen Sie verwendet werden.')) . '</li>'
                . '<li>' . e(dt('Sie haben das Recht auf einen Anwalt und darauf, dass er bei der Vernehmung anwesend ist.')) . '</li>'
                . '<li>' . e(dt('Wenn Sie sich keinen Anwalt leisten können, wird Ihnen vor jeder Vernehmung einer gestellt.')) . '</li></ol></div>',
            'oath' => '<div class="us-box us-oath">' . e(dt((string) (($cfg['oath_text'] ?? '') ?: 'Ich versichere unter Strafe des Meineids nach den Gesetzen des Staates San Andreas, dass die vorstehenden Angaben wahr und richtig sind.'))) . '</div>',
            'order' => '<div class="us-order">' . e(mb_strtoupper(dt('Es wird angeordnet:'))) . '</div>',
            'notice' => implode('', array_map(fn($txt) => '<div class="us-box us-notice"><b>' . e(mb_strtoupper(dt('Wichtiger Hinweis'))) . '</b> ' . e(dt((string) $txt)) . '</div>',
                (array) ($cfg['notice'] ?? 'Bitte bewahren Sie dieses Dokument sorgfältig auf und legen Sie es auf Verlangen vor.'))),
            'notice2' => implode('', array_map(fn($txt) => '<div class="us-box us-notice"><b>' . e(mb_strtoupper(dt('Erklärung'))) . '</b> ' . e(dt((string) $txt)) . '</div>', (array) ($cfg['notice2'] ?? []))),
            'validbox' => us_valid_box($doc, $cfg),
            'times' => us_times_strip($doc, $cfg),
            'tearoff' => '<div class="us-tear"><span>✂ ' . e(dt('Hier abtrennen')) . '</span><div class="us-tear-body"><div><span class="fl">' . e(dt('Dokument-Nr.')) . '</span><b class="mono">' . e(doc_code_display((string) ($doc['number'] ?? ''))) . '</b></div><div><span class="fl">' . e(dt('Datum')) . '</span>' . e(fmt_date($doc['issued_at'] ?? now())) . '</div><div><span class="fl">' . e(dt('Empfang bestätigt (Unterschrift)')) . '</span><span class="us-line"></span></div></div></div>',
            'hipaa' => '<div class="us-box us-notice small">' . e(dt('Vertraulich – enthält geschützte Gesundheitsinformationen. Weitergabe nur mit Einwilligung der betroffenen Person oder auf gesetzlicher Grundlage.')) . '</div>',
            'confidential' => '<div class="us-conf">' . e(mb_strtoupper(dt('Vertraulich – nur für den Dienstgebrauch'))) . '</div>',
            'thumbprint' => '<div class="us-thumb"><div class="us-thumb-box"></div><span class="fl">' . e(dt('Rechter Daumenabdruck')) . '</span></div>',
            'photo' => '<div class="us-photo"><span class="fl">' . e(dt('Lichtbild')) . '</span></div>',
            'review' => '<div class="us-review"><div><span class="fl">' . e(dt('Geprüft von (Vorgesetzte/r)')) . '</span><span class="us-line"></span></div><div><span class="fl">' . e(dt('Dienstnummer')) . '</span><span class="us-line"></span></div><div><span class="fl">' . e(dt('Datum')) . '</span><span class="us-line"></span></div></div>',
            'receipt' => '<div class="us-review"><div><span class="fl">' . e(dt('Ausfertigung erhalten (Unterschrift)')) . '</span><span class="us-line"></span></div><div><span class="fl">' . e(dt('Datum / Uhrzeit')) . '</span><span class="us-line"></span></div></div>',
            default => '',
        };
    }
    return $out;
}

/** Unterschriften im US-Stil: Unterschriftslinie mit Name, Funktion, Dienstnummer und Datum. */
function us_signatures(array $doc, array $cfg, array $extra = []): string
{
    $u = $doc['issuer_snapshot'] ?? [];
    $d = $doc['data'] ?? [];
    $seal = (string) $cfg['seal'];
    $roleCap = (string) ($cfg['sig_caption'] ?? '') !== '' ? $cfg['sig_caption'] : match ($cfg['style']) {
        'court' => 'Richter/in',
        'pleading' => 'Rechtsanwalt / Rechtsanwältin',
        'medical', 'ems' => faction_kind() === 'medical' && ($u['doctor_number'] ?? '') !== '' ? 'Behandelnde/r Ärztin / Arzt' : 'Unterschrift',
        'police' => 'Unterschrift Beamte/r',
        default => 'Unterschrift',
    };
    ob_start(); ?>
    <div class="us-sigs">
        <div class="us-sig">
            <div class="us-sig-area"><?php if ($seal === 'round'): ?><div class="sig-stamp round"><?= seal_svg($doc) ?></div><?php elseif ($seal === 'rect'): ?><div class="sig-stamp rect"><?= doctor_stamp_svg($doc) ?></div><?php endif; ?><?= signature_html($u, doc_rand($doc, 'ussig', -4, 3)) ?></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt($roleCap)) ?></span>
            <span class="us-sig-name"><?= e(user_display($u)) ?><?= !empty($u['rank_name']) ? ', ' . e($u['rank_name']) : '' ?><?= ($u['badge_number'] ?? '') !== '' ? ' · ' . e(dt('Dienstnummer')) . ' ' . e($u['badge_number']) : '' ?></span>
        </div>
        <div class="us-sig us-sig-date">
            <div class="us-sig-area"><span class="hand-filled"><?= e(fmt_date($doc['issued_at'] ?? now())) ?></span></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt('Datum')) ?></span>
            <span class="us-sig-name"><?= e(org('city')) ?></span>
        </div>
        <?php foreach ((array) ($cfg['extra_sigs'] ?? []) as $cap): ?>
        <div class="us-sig"><div class="us-sig-area"></div><span class="us-sig-line"></span><span class="fl"><?= e(dt((string) $cap)) ?></span></div>
        <?php endforeach; ?>
        <?php foreach ($extra as $f): $img = $d[$f['name']] ?? ''; ?>
        <div class="us-sig">
            <div class="us-sig-area"><?php if ($img !== ''): ?><img class="sig-img" src="<?= e($img) ?>" alt=""><?php endif; ?></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt((string) $f['label'])) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if (!empty($u['cosigner'])): $c = $u['cosigner']; ?>
        <div class="us-sig">
            <div class="us-sig-area"><?= signature_html($c, doc_rand($doc, 'uscosig', -3, 3)) ?></div>
            <span class="us-sig-line"></span>
            <span class="fl"><?= e(dt('Gegengezeichnet / freigegeben am {date}', ['date' => fmt_date($c['at'] ?? '')])) ?></span>
            <span class="us-sig-name"><?= e(user_display($c)) ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/** Fußzeile: Formularnummer, Verteiler, Echtheitsprüfung, Seitenzahl. */
function us_footer(array $doc, array $cfg = [], array $type = []): string
{
    $copies = $cfg['copies'] ?? [];
    ob_start(); ?>
    <footer class="us-foot">
        <?php if ($copies): ?><div class="us-copies"><?= e(dt('Verteiler')) ?>: <?php foreach (array_values($copies) as $i => $c): ?><span class="us-copy c<?= $i ?>"><?= e(dt((string) $c)) ?></span><?php endforeach; ?></div><?php endif; ?>
        <div class="us-foot-row">
            <span class="us-form mono"><?= e(us_form_number($doc, $cfg, $type)) ?></span>
            <span class="us-verify"><?= qr_html($doc, 'qr qr-small') ?><span><?= e(dt('Echtheit prüfen')) ?>: <?= e(preg_replace('#^https?://#', '', base_url())) ?>/verify.php<br><?= e(dt('Prüfcode:')) ?> <b class="mono"><?= e(chunk_split(doc_code_display((string) ($doc['verify_code'] ?? '')), 4, ' ')) ?></b></span></span>
            <span class="us-page"><?= e(dt('Seite {n} von {m}', ['n' => 1, 'm' => 1])) ?></span>
        </div>
        <?php if (!isset($doc['_forged']) || (int) $doc['_forged'] >= 85): ?><div class="df-micro" aria-hidden="true"><?= e(str_repeat(mb_strtoupper(org('org_short')) . ' · ' . dt('ECHT') . ' · ' . doc_code_display((string) ($doc['number'] ?? '')) . ' · ', 12)) ?></div><?php endif; ?>
    </footer>
    <?php
    return ob_get_clean();
}

/** Briefkopf der eingebauten Vorlagen in US-Gestaltung (zentrierter Behördenkopf, Empfänger links, Angaben rechts). */
function us_letterhead(array $doc, array $opts = []): string
{
    $p = $doc['patient_snapshot'] ?? [];
    $u = $doc['issuer_snapshot'] ?? [];
    $recipient = $opts['recipient'] ?? '';
    if ($recipient === '' && $p && empty($opts['no_patient_address'])) {
        $recipient = patient_salutation_name($p) . "\n" . ($p['address'] ?? '');
    }
    $info = array_merge([dt('Dokument-Nr.') => doc_code_display((string) ($doc['number'] ?? ''))], $opts['info'] ?? [],
        [dt('Ansprechpartner') => user_display($u), dt('Abteilung') => $u['department'] ?? '', dt('Datum') => fmt_date($doc['issued_at'] ?? now())]);
    ob_start(); ?>
    <header class="us-lh">
        <?= logo_html('us-lh-logo') ?>
        <div class="us-lh-state"><?= e(mb_strtoupper(dt('Staat San Andreas'))) ?></div>
        <div class="us-lh-org"><?= e(org('org_name')) ?></div>
        <div class="us-lh-sub"><?= e(org('org_sub')) ?><?= !empty($opts['department']) ? ' · ' . e(dt((string) $opts['department'])) : '' ?></div>
        <div class="us-lh-addr"><?= e(org('org_street')) ?> · <?= e(org('org_city')) ?> · <?= e(dt('Tel.')) ?> <?= e(org('phone')) ?> · <?= e(org('email')) ?></div>
    </header>
    <div class="us-lh-row">
        <div class="us-lh-to"><?= nl(trim((string) $recipient)) ?></div>
        <table class="us-lh-info"><?php foreach ($info as $k => $v): if ($v === '' || $v === null) continue; ?><tr><th><?= e(dt((string) $k)) ?></th><td><?= e((string) $v) ?></td></tr><?php endforeach; ?></table>
    </div>
    <?php
    return ob_get_clean();
}
