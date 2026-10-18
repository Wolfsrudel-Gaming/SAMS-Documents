<?php
defined('SAMS_ROOT') || exit;
$yn = ['1' => 'ja', '0' => 'nein'];
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
        'ik_number'  => ['Institutionskennzeichen (IK, nur Rettungsdienst)', '260 911 234'],
        'bsnr'       => ['Betriebsstätten-Nr. (nur Rettungsdienst)', '72 911 3400'],
        'director'   => [STAMP_PROFILES[faction_kind()]['head'] ?? 'Leitung', faction_kind() === 'medical' ? 'Dr. med. Ärztliche Direktion' : 'Name der Leitung'],
        'register'   => ['Rechtsform / Register', 'Körperschaft des öffentlichen Rechts · State of San Andreas'],
        'tax_id'     => ['Steuernummer', 'St.-Nr. 31/911/04711'],
        'bank'       => ['Bank', 'Maze Bank of Los Santos'],
        'iban'       => ['IBAN', 'SA21 0911 0000 0123 4567 89'],
        'bic'        => ['BIC', 'MAZESALSXXX'],
    ],
    'Sprache' => [
        'language' => ['Sprache der Oberfläche (Dokumente bleiben deutsch)', 'de', 'select', UI_LANGUAGES],
    ],
    'Design' => [
        'theme_primary' => ['Primärfarbe (Schaltflächen, Markierungen)', '#0b4f9c', 'color'],
        'theme_accent'  => ['Akzentfarbe (Warnhinweise)', '#c8102e', 'color'],
        'theme_nav'     => ['Farbe Kopfleiste', '#1c2b3b', 'color'],
        'stamp_color'   => ['Stempel- und Siegelfarbe (leer = passend zur Fraktionsart)', STAMP_PROFILES[faction_kind()]['color'] ?? '#22409a', 'color'],
    ],
    'Dokumente' => [
        'doc_prefix' => ['Präfix Dokumentnummern', 'SAMS'],
        'seal_text'  => ['Umschrift Dienstsiegel', mb_strtoupper((string) (current_faction()['name'] ?? 'SAN ANDREAS MEDICAL SERVICES'))],
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
    'Apotheke' => [
        'rx_valid_days'     => ['Rezepte gültig (Tage ab Ausstellung)', '28'],
        'rx_valid_days_btm' => ['BtM-Rezepte gültig (Tage ab Ausstellung)', '7'],
    ],
    'Dienstzeiten' => [
        'duty_max_hours' => ['Vergessene Schichten automatisch beenden nach (Stunden)', '12'],
        'roster_lock_hours' => ['Dienstplan: Austragen bis … Stunden vor Schichtbeginn (danach nur Tausch)', '12'],
    ],
    'Behörden-Werkzeuge' => [
        'license_types'        => ['Lizenzarten (durch | getrennt)', implode(' | ', array_map('trim', explode('|', LICENSE_DEFAULT_TYPES[faction_kind()] ?? LICENSE_DEFAULT_TYPES['default'])))],
        'evidence_storage'     => ['Lagerorte für Asservaten (durch | getrennt)', 'Asservatenkammer | Waffenschrank | Kühlraum (biologische Spuren) | Fahrzeughalle'],
        'custody_cells'        => ['Zellen / Gewahrsamsräume (durch | getrennt)', 'Zelle 1 | Zelle 2 | Zelle 3 | Sammelzelle'],
        'custody_unit_minutes' => ['Haft-Einheit entspricht … Minuten (für die geplante Entlassung)', '1'],
        'court_rooms'          => ['Sitzungssäle (durch | getrennt)', 'Saal 1 | Saal 2 | Besprechungsraum'],
    ],
    'Spielserver' => [
        'game_jobs'      => ['ESX-Jobs dieser Fraktion (Komma)', implode(',', GAME_DEFAULT_JOBS[faction_kind()] ?? [])],
        'game_society'   => ['Society-Konto für Zahlungen', 'society_' . (GAME_DEFAULT_JOBS[faction_kind()][0] ?? 'job')],
        'game_billing'   => ['Rechnungen ins Spiel senden (esx_billing)', '1', 'select', $yn],
        'game_akte'      => ['Akte am Spieler abrufen (Befehl /akte)', '1', 'select', $yn],
        'game_akte_duty' => ['Aktenabruf nur im Dienst', '1', 'select', $yn],
        'game_akte_job'  => ['Aktenabruf nur mit passendem ESX-Job', '0', 'select', $yn],
        'game_tetra_org' => ['Organisation in der Leitstelle (tetrascript: fire, ems, police)', DISPATCH_DEFAULT_ORG[faction_kind()] ?? ''],
        'game_tetra_duty' => ['Dienst automatisch mit Funkstatus (1–4 beginnt, 6 beendet)', '0', 'select', $yn],
    ],
    'Spielserver – alle Fraktionen' => [
        'ingame_framework'     => ['Framework', 'esx', 'select', ['esx' => 'ESX (Spielerdaten, Rechnungen)', 'none' => 'ohne Framework']],
        'ingame_cmd_tablet'    => ['Befehl Tablet (ohne /, leer = Vorgabe)', 'tablet'],
        'duty_command'         => ['Befehl Dienst', 'dienst'],
        'ingame_cmd_akte'      => ['Befehl Akte am nächsten Spieler', 'akte'],
        'ingame_cmd_plate'     => ['Befehl Kennzeichenabfrage', 'kennzeichen'],
        'ingame_akte_distance' => ['Reichweite Aktenabruf (Meter)', '3'],
        'ingame_billing_table' => ['Tabelle von esx_billing in der Spiel-Datenbank', 'billing'],
        'ingame_bill_interval' => ['Rechnungen abgleichen alle … Sekunden', '30'],
        'ingame_billing_provider' => ['Rechnungssystem im Spiel', 'esx_billing', 'select', ['esx_billing' => 'esx_billing (Tabelle billing)', 'sky' => 'Sky Billing (sky_jobs_base)']],
        'ingame_sky'           => ['Sky Ambulance (sky_ambulancejob) anbinden', '0', 'select', $yn],
        'ingame_sky_death'     => ['Sky: Tod/Wiederbelebung in die Akte', '1', 'select', $yn],
        'ingame_sky_meds'      => ['Sky: Medikamentengabe im Bestand/BtM-Buch buchen', '1', 'select', $yn],
        'ingame_tetra'         => ['Leitstelle tetrascript (tetra-core) anbinden', '0', 'select', $yn],
        'ingame_tetra_poll'    => ['Leitstelle: Einsätze abgleichen alle … Sekunden', '20'],
    ],
];
// Weitere Fraktionen pflegen nur ihre eigenen Angaben; systemweite Einstellungen bleiben der Hauptfraktion vorbehalten
// Übergeordnete Verwaltung: nur für den Hauptadministrator (gilt für alle Dokumente, die er ausstellt)
if (!empty(current_user()['is_superadmin'])) {
    $keys['Administration (übergeordnet, Stempel des Hauptadministrators)'] = [
        'admin_org_name'    => ['Bezeichnung der Verwaltung', ADMIN_STAMP_DEFAULTS['admin_org_name']],
        'admin_department'  => ['Abteilung (Stempel)', ADMIN_STAMP_DEFAULTS['admin_department']],
        'admin_title'       => ['Funktion unter der Unterschrift', ADMIN_STAMP_DEFAULTS['admin_title']],
        'admin_seal_text'   => ['Umschrift Siegel oben', ADMIN_STAMP_DEFAULTS['admin_seal_text']],
        'admin_seal_bottom' => ['Umschrift Siegel unten', ADMIN_STAMP_DEFAULTS['admin_seal_bottom']],
        'admin_stamp_color' => ['Stempelfarbe', ADMIN_STAMP_DEFAULTS['admin_stamp_color'], 'color'],
    ];
}
// Werkzeuge nur zeigen, wenn die Fraktion das passende Modul nutzt
foreach (['license_types' => 'licenses', 'evidence_storage' => 'evidence', 'custody_cells' => 'custody', 'custody_unit_minutes' => 'custody', 'court_rooms' => 'court'] as $sk => $sm) {
    if (!module_enabled($sm)) {
        unset($keys['Behörden-Werkzeuge'][$sk]);
    }
}
if (!$keys['Behörden-Werkzeuge']) {
    unset($keys['Behörden-Werkzeuge']);
}
if (fid() !== 1) {
    unset($keys['Sicherheit'], $keys['Bürgerportal'], $keys['Apotheke'], $keys['Dienstzeiten']['duty_max_hours'], $keys['Spielserver – alle Fraktionen']);
}
if (is_post()) {
    csrf_check();
    foreach ($keys as $group) {
        foreach ($group as $k => $_) {
            $v = mb_substr((string) post($k), 0, 300);
            if ((str_starts_with($k, 'theme_') || in_array($k, ['stamp_color', 'admin_stamp_color'], true)) && $v !== '' && !preg_match('/^#[0-9a-f]{6}$/i', $v)) {
                $v = '';
            }
            if (in_array($k, ['portal_appointment_kinds', 'license_types', 'evidence_storage', 'custody_cells', 'court_rooms'], true)) {
                $v = implode("\n", array_filter(array_map('trim', explode('|', $v))));
            } elseif ($k === 'portal_enabled') {
                $v = $v === '0' ? '0' : '1';
            }
            if ($k === 'language' && !isset(UI_LANGUAGES[$v])) {
                $v = 'de';
            }
            if ($k === 'session_idle_minutes') {
                $v = (string) max(0, min(1440, (int) $v));
            }
            if ($k === 'duty_max_hours') {
                $v = (string) max(1, min(48, (int) $v ?: 12));
            } elseif (in_array($k, ['rx_valid_days', 'rx_valid_days_btm'], true)) {
                $v = (string) max(1, min(365, (int) $v ?: 28));
            } elseif ($k === 'custody_unit_minutes') {
                $v = $v === '' ? '' : (string) max(1, min(60, (int) $v));
            } elseif ($k === 'roster_lock_hours') {
                $v = (string) max(0, min(168, (int) $v));
            } elseif ($k === 'duty_command' || $k === 'game_society' || str_starts_with($k, 'ingame_cmd_') || $k === 'ingame_billing_table') {
                $v = preg_replace('/[^a-z0-9_]/i', '', $v);
            } elseif ($k === 'game_tetra_org') {
                $v = preg_replace('/[^a-z0-9_]/', '', strtolower($v));
            } elseif ($k === 'ingame_tetra_poll') {
                $v = $v === '' ? '' : (string) max(10, min(300, (int) $v));
            } elseif ($k === 'game_jobs') {
                $v = implode(',', array_filter(array_map(fn($j) => preg_replace('/[^a-z0-9_]/i', '', trim($j)), explode(',', $v))));
            } elseif ($k === 'ingame_akte_distance') {
                $v = $v === '' ? '' : (string) max(1, min(10, (float) str_replace(',', '.', $v)));
            } elseif ($k === 'ingame_bill_interval') {
                $v = $v === '' ? '' : (string) max(15, min(600, (int) $v));
            }
            if (($items = $group[$k]) && ($items[2] ?? '') === 'select' && !isset($items[3][$v])) {
                $v = $items[1];
            }
            save_setting($k, $v);
        }
    }
    if (post('modules_form')) {
        $mods = array_values(array_intersect((array) ($_POST['modules'] ?? []), array_keys(FACTION_MODULES)));
        if ($mods !== faction_modules()) {
            update('factions', fid(), ['modules' => json_encode($mods)]);
            factions(true, true);
            audit('faction.modules', 'faction', fid(), implode(', ', $mods) ?: 'keine');
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
            <?php if (($def[2] ?? '') === 'select'): ?>
                <div class="field w-half"><label><?= e($label) ?></label><select name="<?= e($k) ?>"><?php foreach ($def[3] as $ov => $ol): ?><option value="<?= e($ov) ?>"<?= selected($ov, setting($k, $placeholder)) ?>><?= e($ol) ?></option><?php endforeach; ?></select></div>
            <?php elseif (($def[2] ?? '') === 'color'): $cv = hex_color((string) setting($k, ''), $placeholder); ?>
                <div class="field w-third"><label><?= e($label) ?></label>
                    <div class="color-row"><input type="color" value="<?= e($cv) ?>"><input type="text" name="<?= e($k) ?>" value="<?= e(setting($k, '')) ?>" placeholder="<?= e($placeholder) ?>"></div></div>
            <?php else: ?>
                <div class="field w-half"><label><?= e($label) ?></label><input name="<?= e($k) ?>" value="<?= e(str_replace("\n", ' | ', (string) setting($k, ''))) ?>" placeholder="<?= e($placeholder) ?>"></div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <h3 class="form-heading">Umfang (Module dieser Fraktion)</h3>
    <input type="hidden" name="modules_form" value="1">
    <div class="field w-full"><div class="choices">
        <?php foreach (FACTION_MODULES as $mk => $ml): ?><label class="choice"><input type="checkbox" name="modules[]" value="<?= e($mk) ?>"<?= module_enabled($mk) ? ' checked' : '' ?>> <?= e($ml) ?></label><?php endforeach; ?>
    </div><p class="hint">Ausgeschaltete Module verschwinden aus Menü und Rechten aller Ränge dieser Fraktion; Daten bleiben erhalten.</p></div>
    <p class="hint w-full">Spielserver: Befehle werden beim Start der Resource übernommen (danach <code>restart sams_tablet</code>), alles andere innerhalb weniger Minuten.</p>
    <h3 class="form-heading">Logo</h3>
    <div class="field w-half"><label>Eigenes Logo (PNG/JPG/WEBP, quadratisch empfohlen)</label><input type="file" name="logo" accept="image/png,image/jpeg,image/webp"></div>
    <div class="field w-half"><span class="label">Aktuell</span><img src="<?= e(faction_logo()) ?>" alt="" style="height:64px">
        <?php if (setting('logo', '') !== ''): ?><label class="choice single"><input type="checkbox" name="remove_logo" value="1"> Standardlogo verwenden</label><?php endif; ?></div>
    <p class="hint w-full">Leere Felder verwenden den grau angezeigten Standardwert.</p>
    <div class="form-actions"><button class="btn primary">Speichern</button></div>
</form>
<?php
$content = ob_get_clean();
$title = 'Einstellungen';
require SAMS_ROOT . '/app/layout.php';
