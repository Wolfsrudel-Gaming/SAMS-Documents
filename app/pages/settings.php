<?php
defined('SAMS_ROOT') || exit;
$keys = [
    'Einrichtung' => [
        'org_name'   => ['Name der Einrichtung', 'San Andreas Medical Services'],
        'org_short'  => ['Kurzname', 'SAMS'],
        'org_sub'    => ['Standort / Zusatz', 'Pillbox Hill Medical Center'],
        'org_street' => ['Straße, Nr.', 'Elgin Avenue 1'],
        'org_city'   => ['PLZ, Ort', '90012 Los Santos, San Andreas'],
        'city'       => ['Ort (für „Ort, Datum“)', 'Los Santos'],
        'phone'      => ['Telefon', '(555) 911-0100'],
        'fax'        => ['Fax', '(555) 911-0199'],
        'email'      => ['E-Mail', 'verwaltung@sams.ls'],
        'web'        => ['Website', 'www.sams.ls'],
    ],
    'Kennungen & Fußzeile' => [
        'ik_number'  => ['Institutionskennzeichen (IK)', '260 911 234'],
        'bsnr'       => ['Betriebsstätten-Nr.', '72 911 3400'],
        'director'   => ['Ärztliche Direktion', 'Dr. med. Ärztliche Direktion'],
        'register'   => ['Rechtsform / Register', 'Körperschaft des öffentlichen Rechts · State of San Andreas'],
        'tax_id'     => ['Steuernummer', 'St.-Nr. 31/911/04711'],
        'bank'       => ['Bank', 'Maze Bank of Los Santos'],
        'iban'       => ['IBAN', 'SA21 0911 0000 0123 4567 89'],
        'bic'        => ['BIC', 'MAZESALSXXX'],
    ],
    'Design' => [
        'theme_primary' => ['Primärfarbe (Schaltflächen, Markierungen)', '#0b4f9c', 'color'],
        'theme_accent'  => ['Akzentfarbe (Warnhinweise)', '#c8102e', 'color'],
        'theme_nav'     => ['Farbe Kopfleiste', '#1c2b3b', 'color'],
    ],
    'Dokumente' => [
        'doc_prefix' => ['Präfix Dokumentnummern', 'SAMS'],
        'seal_text'  => ['Umschrift Dienstsiegel', 'SAN ANDREAS MEDICAL SERVICES'],
        'currency'   => ['Währungssymbol', '$'],
    ],
    'Bürgerportal' => [
        'portal_enabled'           => ['Bürgerportal aktiv (1 = ja, 0 = nein)', '1'],
        'portal_intro'             => ['Begrüßungstext', ''],
        'portal_appointment_kinds' => ['Terminarten (durch | getrennt)', 'Allgemeine Sprechstunde | Untersuchung Waffenschein | Untersuchung Führerschein / Fluglizenz | Einstellungsuntersuchung | Psychologisches Gespräch | Nachsorge / Fäden ziehen | Impfung'],
        'portal_hidden_types'      => ['Im Portal ausgeblendete Dokumentarten (Kürzel-Schlüssel, Komma)', 'psych_gutachten,unterbringung,blutentnahme,verletzung,todesbescheinigung'],
    ],
    'Sicherheit' => [
        'session_idle_minutes' => ['Automatisch abmelden nach … Minuten ohne Aktivität (0 = aus)', '0'],
        'attachment_max_mb'    => ['Maximale Größe von Anhängen in MB (begrenzt zusätzlich durch PHP/Webserver)', '10'],
    ],
    'Dienstzeiten' => [
        'duty_max_hours' => ['Vergessene Schichten automatisch beenden nach (Stunden)', '12'],
        'duty_command'   => ['Befehl im Spiel (Hinweistext, ohne /)', 'dienst'],
    ],
];
if (is_post()) {
    csrf_check();
    foreach ($keys as $group) {
        foreach ($group as $k => $_) {
            $v = mb_substr((string) post($k), 0, 300);
            if (str_starts_with($k, 'theme_') && $v !== '' && !preg_match('/^#[0-9a-f]{6}$/i', $v)) {
                $v = '';
            }
            if ($k === 'portal_appointment_kinds') {
                $v = implode("\n", array_filter(array_map('trim', explode('|', $v))));
            } elseif ($k === 'portal_enabled') {
                $v = $v === '0' ? '0' : '1';
            }
            if ($k === 'session_idle_minutes') {
                $v = (string) max(0, min(1440, (int) $v));
            }
            if ($k === 'duty_max_hours') {
                $v = (string) max(1, min(48, (int) $v ?: 12));
            } elseif ($k === 'duty_command') {
                $v = preg_replace('/[^a-z0-9_]/i', '', $v) ?: 'dienst';
            }
            save_setting($k, $v);
        }
    }
    if (post('remove_logo')) {
        save_setting('logo', '');
    }
    if (!empty($_FILES['logo']['tmp_name']) && is_uploaded_file($_FILES['logo']['tmp_name'])) {
        $info = @getimagesize($_FILES['logo']['tmp_name']);
        $mime = $info['mime'] ?? (mime_content_type($_FILES['logo']['tmp_name']) ?: '');
        if (in_array($mime, ['image/png', 'image/jpeg', 'image/webp'], true) && $_FILES['logo']['size'] < 600000) {
            save_setting('logo', 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($_FILES['logo']['tmp_name'])));
        } else {
            flash('error', 'Logo: nur PNG, JPG oder WEBP bis 600 KB.');
        }
    }
    audit('settings.update');
    flash('success', 'Einstellungen gespeichert.');
    redirect(url('settings'));
}
ob_start(); ?>
<div class="page-head"><div><h1>Einstellungen</h1><p class="muted">Diese Angaben erscheinen in Briefkopf, Stempeln und Fußzeilen aller neuen und bestehenden Dokumente.</p></div></div>
<form method="post" enctype="multipart/form-data" class="card form-grid">
    <?= csrf_field() ?>
    <?php foreach ($keys as $group => $items): ?>
        <h3 class="form-heading"><?= e($group) ?></h3>
        <?php foreach ($items as $k => $def): [$label, $placeholder] = $def; ?>
            <?php if (($def[2] ?? '') === 'color'): $cv = hex_color((string) setting($k, ''), $placeholder); ?>
                <div class="field w-third"><label><?= e($label) ?></label>
                    <div class="color-row"><input type="color" value="<?= e($cv) ?>"><input type="text" name="<?= e($k) ?>" value="<?= e(setting($k, '')) ?>" placeholder="<?= e($placeholder) ?>"></div></div>
            <?php else: ?>
                <div class="field w-half"><label><?= e($label) ?></label><input name="<?= e($k) ?>" value="<?= e(str_replace("\n", ' | ', (string) setting($k, ''))) ?>" placeholder="<?= e($placeholder) ?>"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <h3 class="form-heading">Logo</h3>
    <div class="field w-half"><label>Eigenes Logo (PNG/JPG/WEBP, quadratisch empfohlen)</label><input type="file" name="logo" accept="image/png,image/jpeg,image/webp"></div>
    <div class="field w-half"><span class="label">Aktuell</span><img src="<?= e(setting('logo', 'assets/img/logo.svg')) ?>" alt="" style="height:64px">
        <?php if (setting('logo', '') !== ''): ?><label class="choice single"><input type="checkbox" name="remove_logo" value="1"> Standardlogo verwenden</label><?php endif; ?></div>
    <p class="hint w-full">Leere Felder verwenden den grau angezeigten Standardwert.</p>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Einstellungen';
require SAMS_ROOT . '/app/layout.php';
