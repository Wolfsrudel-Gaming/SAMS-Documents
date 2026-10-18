<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Mehrere Fraktionen in einer Installation: Rettungsdienst, Polizei, Justiz,
 * Feuerwehr und Verwaltung. Jede Fraktion hat eigene Ränge, Abteilungen,
 * Akten, Dokumente, Einstellungen (Briefkopf, Logo, Farben, Discord) und
 * nutzt nur die Module, die zu ihr passen. Fraktion 1 ist die ursprüngliche.
 *
 * Der Fraktionskontext steht in $GLOBALS['sams_fid'] (gesetzt beim Laden des
 * Benutzers, in API/Portal/Cron ausdrücklich). Der Hauptadministrator kann die
 * Ansicht wechseln.
 */

/** Zusatzmodule; Grundfunktionen (Personal, Dienst, Wiki, Kommunikation …) haben alle. */
const FACTION_MODULES = [
    'records'  => 'Akten zu Personen mit Einträgen, Vorgängen und Anhängen',
    'billing'  => 'Rechnungen bzw. Bußgelder mit Katalog',
    'stock'    => 'Medikamentenbestand, BtM-Buch und Rezepte',
    'wanted'   => 'Fahndung und Haftbefehle',
    'portal'   => 'Bürgerportal (Termine, Anträge, Bewerbungen)',
    'requests' => 'Behördenanfragen zwischen Fraktionen',
    'evidence' => 'Asservatenverwaltung mit lückenloser Beweismittelkette',
    'vehicles' => 'Fahrzeugregister (Halter, Zulassung, gestohlen/beschlagnahmt)',
    'custody'  => 'Haftbuch / Gewahrsam',
    'court'    => 'Gerichtskalender, Fristen und Anwaltszugänge',
    'licenses' => 'Führerscheine und Lizenzen mit Ablaufdatum',
    'objects'  => 'Objektpläne (Gebäude, Gefahren, Schlüsseldepots, Pläne)',
    'joint'    => 'Gemeinsame Einsatzberichte mehrerer Behörden',
    'equipment' => 'Geräte & Prüffristen (Wartung, Prüfprotokolle)',
    'breathing' => 'Atemschutz-Nachweise (Eignung, Belastungsübung, Einsätze)',
    'flightlog' => 'Flugbuch und Luftfahrzeuge (Flugstunden, Wartung, Windeneinsätze)',
];

const FACTION_KINDS = [
    'medical' => [
        'label' => 'Rettungsdienst / Klinik', 'logo' => 'assets/img/logo.svg', 'color' => '#0b4f9c',
        'modules' => ['records', 'billing', 'stock', 'portal', 'requests', 'joint', 'equipment'],
        'terms' => ['record' => 'Patientenakte', 'records' => 'Patientenakten', 'person' => 'Patient/in', 'persons' => 'Patienten',
            'case' => 'Fall', 'cases' => 'Fälle', 'billing' => 'Rechnungen', 'catalog' => 'Gebührenordnung', 'staff' => 'Personal'],
    ],
    'police' => [
        'label' => 'Polizei', 'logo' => 'assets/img/kinds/police.svg', 'color' => '#1d3a6e',
        'modules' => ['records', 'billing', 'wanted', 'requests', 'evidence', 'vehicles', 'custody', 'licenses', 'joint'],
        'terms' => ['record' => 'Personenakte', 'records' => 'Personenakten', 'person' => 'Person', 'persons' => 'Personen',
            'case' => 'Vorgang', 'cases' => 'Vorgänge', 'billing' => 'Bußgelder', 'catalog' => 'Bußgeld- und Strafkatalog', 'staff' => 'Personal'],
    ],
    'justice' => [
        'label' => 'Justiz', 'logo' => 'assets/img/kinds/justice.svg', 'color' => '#5b3a1e',
        'modules' => ['records', 'billing', 'wanted', 'requests', 'court'],
        'terms' => ['record' => 'Personenakte', 'records' => 'Personenakten', 'person' => 'Beteiligte/r', 'persons' => 'Beteiligte',
            'case' => 'Verfahren', 'cases' => 'Verfahren', 'billing' => 'Kostenrechnungen', 'catalog' => 'Kostenverzeichnis', 'staff' => 'Personal'],
    ],
    'fire' => [
        'label' => 'Feuerwehr', 'logo' => 'assets/img/kinds/fire.svg', 'color' => '#a3161a',
        'modules' => ['billing', 'requests', 'objects', 'joint', 'equipment', 'breathing'],
        'terms' => ['record' => 'Akte', 'records' => 'Akten', 'person' => 'Betroffene/r', 'persons' => 'Betroffene',
            'case' => 'Einsatz', 'cases' => 'Einsätze', 'billing' => 'Gebührenbescheide', 'catalog' => 'Gebührenordnung', 'staff' => 'Personal'],
    ],
    'rescue' => [
        'label' => 'Spezialrettung (Gelände, Wasser, Luft)', 'logo' => 'assets/img/kinds/rescue.svg', 'color' => '#d35400',
        'modules' => ['records', 'billing', 'requests', 'joint', 'equipment', 'flightlog'],
        'terms' => ['record' => 'Einsatzakte', 'records' => 'Einsatzakten', 'person' => 'Gerettete/r', 'persons' => 'Gerettete',
            'case' => 'Einsatz', 'cases' => 'Einsätze', 'billing' => 'Kostenbescheide', 'catalog' => 'Kostenordnung', 'staff' => 'Personal'],
    ],
    'government' => [
        'label' => 'Verwaltung / Sonstige', 'logo' => 'assets/img/kinds/government.svg', 'color' => '#2f5d50',
        'modules' => ['records', 'billing', 'requests', 'licenses', 'vehicles'],
        'terms' => ['record' => 'Bürgerakte', 'records' => 'Bürgerakten', 'person' => 'Bürger/in', 'persons' => 'Bürger',
            'case' => 'Vorgang', 'cases' => 'Vorgänge', 'billing' => 'Gebührenbescheide', 'catalog' => 'Gebührenverzeichnis', 'staff' => 'Personal'],
    ],
];

/**
 * Rang-Vorlagen beim Anlegen einer Fraktion: [Name, Kürzel, Stufe, Farbe, Stufe der Rechte, Rollen für Dokumentarten].
 * Stufen der Rechte: trainee < staff < senior < lead < chief.
 */
const FACTION_RANK_TEMPLATES = [
    'medical' => [
        ['Ärztliche Direktion / Chief', 'CHF', 90, '#7a1f2b', 'chief', ['medic', 'doctor', 'staff']],
        ['Oberarzt / Oberärztin', 'OA', 70, '#0b4f9c', 'lead', ['medic', 'doctor', 'staff']],
        ['Arzt / Ärztin', 'A', 50, '#1f6fb2', 'senior', ['medic', 'doctor']],
        ['Notfallsanitäter/in', 'NFS', 30, '#c8102e', 'staff', ['medic']],
        ['Rettungssanitäter/in', 'RS', 20, '#d9822b', 'staff', ['medic']],
        ['Auszubildende/r', 'AZB', 10, '#64717f', 'trainee', []],
    ],
    'police' => [
        ['Chief of Police', 'COP', 95, '#0d1f3c', 'chief', ['officer', 'lead', 'staff']],
        ['Assistant Chief', 'AC', 90, '#1d3a6e', 'chief', ['officer', 'lead', 'staff']],
        ['Captain', 'CPT', 80, '#28508f', 'lead', ['officer', 'lead', 'staff']],
        ['Lieutenant', 'LT', 70, '#2f63ad', 'lead', ['officer', 'lead']],
        ['Sergeant', 'SGT', 60, '#3a74c4', 'senior', ['officer', 'lead']],
        ['Detective', 'DET', 55, '#4a6b8a', 'senior', ['officer']],
        ['Senior Officer', 'SO', 40, '#5b84c8', 'staff', ['officer']],
        ['Officer', 'OFC', 30, '#7096d1', 'staff', ['officer']],
        ['Cadet', 'CDT', 10, '#64717f', 'trainee', []],
    ],
    'justice' => [
        ['Präsident/in des Gerichts', 'PRÄS', 95, '#3d250f', 'chief', ['judge', 'prosecutor', 'clerk', 'staff']],
        ['Richter/in', 'RI', 80, '#5b3a1e', 'lead', ['judge', 'clerk']],
        ['Oberstaatsanwalt / -anwältin', 'OStA', 75, '#7a1f2b', 'lead', ['prosecutor', 'clerk']],
        ['Staatsanwalt / -anwältin', 'StA', 60, '#9b2c3a', 'senior', ['prosecutor', 'clerk']],
        ['Rechtsanwalt / -anwältin', 'RA', 40, '#2f5d50', 'staff', ['lawyer']],
        ['Justizangestellte/r', 'JA', 30, '#64717f', 'staff', ['clerk']],
        ['Referendar/in', 'REF', 10, '#8a96a3', 'trainee', []],
    ],
    'fire' => [
        ['Fire Chief', 'FC', 95, '#5c0b0e', 'chief', ['fire', 'lead', 'staff']],
        ['Battalion Chief', 'BC', 85, '#7d0f13', 'lead', ['fire', 'lead', 'staff']],
        ['Captain', 'CPT', 70, '#a3161a', 'lead', ['fire', 'lead']],
        ['Lieutenant', 'LT', 60, '#c0392b', 'senior', ['fire', 'lead']],
        ['Engineer', 'ENG', 45, '#d35400', 'staff', ['fire']],
        ['Firefighter', 'FF', 30, '#e67e22', 'staff', ['fire']],
        ['Probationary Firefighter', 'PFF', 10, '#64717f', 'trainee', []],
    ],
    'rescue' => [
        ['Leitung Spezialrettung', 'LSR', 95, '#7a3b00', 'chief', ['rescue', 'pilot', 'lead', 'staff']],
        ['Stellv. Leitung', 'SLR', 85, '#9c4a00', 'chief', ['rescue', 'pilot', 'lead', 'staff']],
        ['Einsatzleiter/in', 'EL', 70, '#b85400', 'lead', ['rescue', 'pilot', 'lead']],
        ['Pilot/in', 'PIL', 60, '#1f5f8b', 'senior', ['rescue', 'pilot']],
        ['Luftretter/in', 'LR', 50, '#d35400', 'staff', ['rescue']],
        ['Spezialretter/in', 'SR', 40, '#e67e22', 'staff', ['rescue']],
        ['Rettungsschwimmer/in', 'RSW', 30, '#f39c12', 'staff', ['rescue']],
        ['Anwärter/in', 'ANW', 10, '#64717f', 'trainee', []],
    ],
    'government' => [
        ['Bürgermeister/in', 'BGM', 95, '#1f3d34', 'chief', ['gov', 'lead', 'staff']],
        ['Amtsleitung', 'AL', 75, '#2f5d50', 'lead', ['gov', 'lead', 'staff']],
        ['Sachbearbeiter/in', 'SB', 40, '#3f7d6a', 'staff', ['gov']],
        ['Auszubildende/r', 'AZB', 10, '#64717f', 'trainee', []],
    ],
];

const FACTION_DEPARTMENT_TEMPLATES = [
    'medical'    => [['Rettungsdienst', 'RD', '#c8102e'], ['Klinik', 'KLI', '#0b4f9c']],
    'police'     => [['Streifendienst', 'PAT', '#1d3a6e'], ['Kriminalpolizei', 'CID', '#4a6b8a'], ['Verkehrspolizei', 'TRF', '#2f8f6f'], ['Spezialeinheit', 'SWAT', '#14202c'], ['Ausbildung', 'ACA', '#8a6d1d']],
    'justice'    => [['Gericht', 'CRT', '#5b3a1e'], ['Staatsanwaltschaft', 'DA', '#7a1f2b'], ['Anwaltschaft', 'BAR', '#2f5d50'], ['Justizverwaltung', 'ADM', '#64717f']],
    'fire'       => [['Brandbekämpfung', 'FIRE', '#a3161a'], ['Technische Hilfe', 'RES', '#d35400'], ['Vorbeugender Brandschutz', 'PREV', '#8a6d1d']],
    'rescue'     => [['Geländerettung', 'GR', '#8a5a2b'], ['Wasserrettung & Tauchen', 'WR', '#1f6fb2'], ['Luftrettung', 'LR', '#d35400'], ['Suche & Ortung', 'SO', '#5b6b2e'], ['Ausbildung', 'AUS', '#8a6d1d']],
    'government' => [['Bürgeramt', 'BA', '#2f5d50'], ['Ordnungsamt', 'OA', '#5b6b2e']],
];

/** Startkatalog (Kürzel, Bezeichnung, Kategorie, Betrag, Haft-Einheiten) für Bußgeld-/Gebührenbescheide. */
const FACTION_CATALOG_TEMPLATES = [
    'police' => [
        ['V01', 'Geschwindigkeitsüberschreitung bis 20 km/h', 'Verkehr', 250, 0], ['V02', 'Geschwindigkeitsüberschreitung über 20 km/h', 'Verkehr', 500, 0],
        ['V03', 'Rotlichtverstoß', 'Verkehr', 400, 0], ['V04', 'Falschparken / Behinderung', 'Verkehr', 150, 0], ['V05', 'Fahren ohne Fahrerlaubnis', 'Verkehr', 1500, 0],
        ['V06', 'Fahren unter Alkohol- oder Drogeneinfluss', 'Verkehr', 2000, 10], ['V07', 'Unerlaubtes Entfernen vom Unfallort', 'Verkehr', 3000, 15],
        ['V08', 'Gefährlicher Eingriff in den Straßenverkehr', 'Verkehr', 5000, 20],
        ['S01', 'Beleidigung', 'Straftaten', 500, 0], ['S02', 'Körperverletzung', 'Straftaten', 2500, 10], ['S03', 'Gefährliche Körperverletzung', 'Straftaten', 5000, 25],
        ['S04', 'Diebstahl', 'Straftaten', 1500, 5], ['S05', 'Raub', 'Straftaten', 8000, 30], ['S06', 'Bewaffneter Raubüberfall', 'Straftaten', 15000, 45],
        ['S07', 'Besitz von Betäubungsmitteln', 'Straftaten', 2000, 10], ['S08', 'Handel mit Betäubungsmitteln', 'Straftaten', 10000, 40],
        ['S09', 'Unerlaubter Waffenbesitz', 'Straftaten', 5000, 20], ['S10', 'Widerstand gegen Vollstreckungsbeamte', 'Straftaten', 3000, 15],
        ['S11', 'Flucht vor der Polizei', 'Straftaten', 2500, 10], ['S12', 'Sachbeschädigung', 'Straftaten', 1000, 0], ['S13', 'Hausfriedensbruch', 'Straftaten', 800, 0],
        ['S14', 'Bestechung', 'Straftaten', 6000, 20],
    ],
    'justice' => [
        ['K01', 'Gerichtsgebühr Strafverfahren', 'Gebühren', 1000, 0], ['K02', 'Gerichtsgebühr Zivilverfahren', 'Gebühren', 500, 0], ['K03', 'Zeugenentschädigung', 'Auslagen', 100, 0],
        ['K04', 'Sachverständigengutachten', 'Auslagen', 750, 0], ['K05', 'Pflichtverteidigung', 'Auslagen', 1500, 0], ['K06', 'Bearbeitung Kaution', 'Gebühren', 250, 0],
    ],
    'fire' => [
        ['F01', 'Fehlalarm Brandmeldeanlage', 'Einsätze', 500, 0], ['F02', 'Technische Hilfeleistung (je Stunde)', 'Einsätze', 300, 0], ['F03', 'Ölspurbeseitigung', 'Einsätze', 400, 0],
        ['F04', 'Brandschau', 'Vorbeugender Brandschutz', 250, 0], ['F05', 'Brandsicherheitswache (je Stunde)', 'Vorbeugender Brandschutz', 200, 0],
    ],
    'rescue' => [
        ['R01', 'Rettung / Bergung aus unwegsamem Gelände', 'Einsätze', 1500, 0], ['R02', 'Wasserrettung', 'Einsätze', 1200, 0], ['R03', 'Hubschraubereinsatz (je Minute)', 'Luftrettung', 150, 0],
        ['R04', 'Windenbergung', 'Luftrettung', 2500, 0], ['R05', 'Vermisstensuche (je Stunde)', 'Suche', 400, 0], ['R06', 'Fahrzeugbergung aus Gewässer', 'Bergung', 3000, 0],
        ['R07', 'Tierrettung', 'Einsätze', 300, 0], ['R08', 'Absicherung Veranstaltung / Strandwache (je Stunde)', 'Absicherung', 200, 0], ['R09', 'Mutwilliger Fehlalarm', 'Einsätze', 800, 0],
    ],
    'government' => [
        ['G01', 'Gewerbeanmeldung', 'Gewerbe', 150, 0], ['G02', 'Gaststättenerlaubnis', 'Gewerbe', 750, 0], ['G03', 'Veranstaltungsgenehmigung', 'Genehmigungen', 400, 0],
        ['G04', 'Taxikonzession', 'Genehmigungen', 600, 0], ['G05', 'Beglaubigung', 'Bürgeramt', 50, 0], ['G06', 'Ausweisdokument', 'Bürgeramt', 100, 0],
    ],
];

/** Rechte je Stufe (Dokumentrechte kommen aus den Rollen). Rechte nicht aktiver Module werden ausgefiltert. */
function faction_tier_permissions(string $tier): array
{
    $trainee = ['patients.view', 'documents.view', 'duty.clock', 'duty.view', 'roster.view', 'roster.signup', 'training.view', 'wiki.view',
        'board.view', 'messages.use', 'stock.view', 'wanted.view', 'requests.view', 'evidence.view', 'vehicles.view', 'custody.view', 'court.view',
        'licenses.view', 'objects.view', 'joint.view', 'equipment.view', 'breathing.view', 'flightlog.view'];
    $staff = array_merge($trainee, ['patients.edit', 'entries.create', 'attachments.upload', 'cases.manage', 'documents.revoke.own', 'documents.print',
        'documents.share', 'stock.dispense', 'wanted.manage', 'requests.manage', 'evidence.manage', 'vehicles.manage', 'custody.manage', 'court.manage',
        'licenses.manage', 'objects.manage', 'joint.write', 'equipment.manage', 'breathing.manage', 'flightlog.manage']);
    $senior = array_merge($staff, ['entries.confidential', 'documents.approve', 'documents.addendum', 'users.view', 'personnel.view', 'patients.archive']);
    $lead = array_merge($senior, ['entries.edit_all', 'entries.all_departments', 'documents.revoke.all', 'documents.drafts_all', 'patients.merge',
        'users.create', 'users.edit', 'users.password_reset', 'users.deactivate', 'users.sessions', 'personnel.edit', 'duty.manage', 'roster.manage',
        'training.manage', 'wiki.edit', 'lists.manage', 'board.manage', 'invoices.manage', 'stock.manage', 'btm.manage', 'stats.view', 'audit.view',
        'portal.manage', 'portal.applications', 'security.2fa_required']);
    $chief = array_merge($lead, ['patients.delete', 'users.2fa_reset', 'personnel.confidential', 'ranks.manage', 'departments.manage',
        'qualifications.manage', 'printers.manage', 'settings.manage', 'templates.manage', 'prices.manage', 'discord.manage', 'portal.link']);
    return array_values(array_unique(['trainee' => $trainee, 'staff' => $staff, 'senior' => $senior, 'lead' => $lead, 'chief' => $chief][$tier] ?? $trainee));
}

// ------------------------------------------------------------------ Kontext

/** Aktuelle Fraktion (Standard 1). */
function fid(): int
{
    return (int) ($GLOBALS['sams_fid'] ?? 1);
}

function set_faction_context(int $id): void
{
    $GLOBALS['sams_fid'] = max(1, $id);
}

/** Fraktion nach dem Laden des Benutzers setzen (Hauptadministrator: gewählte Ansicht). */
function faction_context_for_user(?array $u): void
{
    if (!$u) {
        return;
    }
    $id = (int) ($u['faction_id'] ?? 1);
    if (!empty($u['is_superadmin']) && !empty($_SESSION['faction_view']) && faction_by_id((int) $_SESSION['faction_view'])) {
        $id = (int) $_SESSION['faction_view'];
    }
    set_faction_context($id);
}

function factions(bool $activeOnly = true, bool $reload = false): array
{
    static $cache = null;
    if ($cache === null || $reload) {
        try {
            $cache = all('SELECT * FROM factions ORDER BY sort, id');
        } catch (PDOException $e) {
            $cache = [['id' => 1, 'code' => 'sams', 'name' => 'San Andreas Medical Services', 'kind' => 'medical', 'short' => 'SAMS', 'active' => 1, 'modules' => null, 'color' => '#0b4f9c']];
        }
    }
    return $activeOnly ? array_values(array_filter($cache, fn($f) => (int) $f['active'] === 1)) : $cache;
}

function faction_by_id(int $id): ?array
{
    foreach (factions(false) as $f) {
        if ((int) $f['id'] === $id) {
            return $f + ['kind' => 'medical', 'short' => '', 'modules' => null];
        }
    }
    return null;
}

function current_faction(): array
{
    return faction_by_id(fid()) ?? ['id' => 1, 'name' => 'San Andreas Medical Services', 'kind' => 'medical', 'short' => 'SAMS', 'modules' => null, 'code' => 'sams', 'active' => 1];
}

function faction_kind(?int $id = null): string
{
    $k = (string) (($id ? faction_by_id($id) : current_faction())['kind'] ?? 'medical');
    return isset(FACTION_KINDS[$k]) ? $k : 'medical';
}

/** Aktive Zusatzmodule einer Fraktion (eigene Auswahl oder Vorgabe der Art). */
function faction_modules(?int $id = null): array
{
    $f = $id ? faction_by_id($id) : current_faction();
    $own = json_decode((string) ($f['modules'] ?? ''), true);
    return is_array($own) ? array_values(array_intersect($own, array_keys(FACTION_MODULES))) : FACTION_KINDS[faction_kind($id)]['modules'];
}

function module_enabled(string $module, ?int $factionId = null): bool
{
    return in_array($module, faction_modules($factionId), true);
}

/** Begriff passend zur Fraktion (z. B. „Patientenakte“ / „Personenakte“). */
function term(string $key, ?int $factionId = null): string
{
    return FACTION_KINDS[faction_kind($factionId)]['terms'][$key] ?? FACTION_KINDS['medical']['terms'][$key] ?? $key;
}

/** Modul, zu dem ein Recht gehört (null = Grundfunktion). */
function permission_module(string $perm): ?string
{
    static $map = [
        'patients.' => 'records', 'entries.' => 'records', 'cases.' => 'records', 'attachments.' => 'records',
        'invoices.' => 'billing', 'prices.' => 'billing', 'stock.' => 'stock', 'btm.' => 'stock',
        'wanted.' => 'wanted', 'portal.' => 'portal', 'requests.' => 'requests', 'evidence.' => 'evidence', 'vehicles.' => 'vehicles',
        'custody.' => 'custody', 'court.' => 'court', 'licenses.' => 'licenses', 'objects.' => 'objects', 'joint.' => 'joint',
        'equipment.' => 'equipment', 'breathing.' => 'breathing', 'flightlog.' => 'flightlog',
    ];
    foreach ($map as $prefix => $module) {
        if (str_starts_with($perm, $prefix)) {
            return $module;
        }
    }
    return null;
}

/** Rechte auf die Module und Dokumentarten der aktuellen Fraktion beschränken. */
function faction_filter_permissions(array $perms, ?int $factionId = null): array
{
    $modules = faction_modules($factionId);
    $types = null;
    return array_values(array_filter($perms, function ($p) use ($modules, &$types, $factionId) {
        $m = permission_module($p);
        if ($m !== null && !in_array($m, $modules, true)) {
            return false;
        }
        if (str_starts_with($p, 'documents.issue.')) {
            $types ??= document_types($factionId);
            return isset($types[substr($p, 16)]);
        }
        return true;
    }));
}

// ----------------------------------------------------------- Einstellungen

/** Einstellungen, die jede Fraktion selbst hat (ohne Rückgriff auf Fraktion 1). */
function faction_setting_scoped(string $key): bool
{
    static $keys = ['city', 'phone', 'fax', 'email', 'web', 'ik_number', 'bsnr', 'director', 'admin_head', 'bank', 'iban', 'bic', 'register',
        'tax_id', 'seal_text', 'currency', 'doc_prefix', 'logo', 'discord_role_map', 'discord_duty_log', 'language', 'doc_type_config', 'roster_lock_hours',
        'license_types', 'evidence_storage', 'court_rooms', 'custody_unit_minutes', 'custody_cells', 'stamp_color'];
    return in_array($key, $keys, true) || (bool) preg_match('/^(org_|theme_|webhook_|report_|game_)/', $key);
}

/** Schlüssel in der Tabelle settings für die aktuelle Fraktion. */
function faction_setting_key(string $key): string
{
    return fid() !== 1 && faction_setting_scoped($key) ? 'f' . fid() . ':' . $key : $key;
}

// --------------------------------------------------------------- Anlegen

/** Neue Fraktion mit Rängen, Abteilungen, Briefkopf und Handbuch anlegen. Gibt die ID zurück. */
function create_faction(string $name, string $short, string $kind, string $color = ''): int
{
    $kind = isset(FACTION_KINDS[$kind]) ? $kind : 'government';
    $code = substr(custom_slug($short ?: $name), 0, 24) ?: 'fraktion';
    while (scalar('SELECT id FROM factions WHERE code = ?', [$code])) {
        $code .= '_' . random_int(2, 99);
    }
    $now = now();
    db()->beginTransaction();
    try {
        $id = (int) insert('factions', ['code' => $code, 'name' => $name, 'short' => $short, 'kind' => $kind, 'color' => $color ?: FACTION_KINDS[$kind]['color'],
            'active' => 1, 'sort' => (int) scalar('SELECT COALESCE(MAX(sort), 0) + 1 FROM factions'), 'created_at' => $now]);
        factions(true, true); // neue Fraktion bekannt machen (Art, Module)
        $prev = fid();
        set_faction_context($id);
        foreach (FACTION_RANK_TEMPLATES[$kind] as [$rname, $rshort, $level, $rcolor, $tier, $roles]) {
            $rankId = insert('ranks', ['faction_id' => $id, 'name' => $rname, 'short' => $rshort, 'level' => $level, 'color' => $rcolor, 'description' => '', 'created_at' => $now, 'updated_at' => $now]);
            $perms = faction_tier_permissions($tier);
            if (in_array('lawyer', $roles, true)) {
                // Anwaltszugang: im Gerichtskalender nur eigene Termine und Fristen
                $perms = array_merge(array_diff($perms, ['court.manage']), ['court.own']);
            }
            foreach (document_types($id) as $key => $t) {
                $role = $t['role'] ?? '';
                // Für alle Fraktionen gültige Arten: Personaldokumente für die Leitung, Schreiben für alle außer Auszubildenden
                $generic = ($t['kinds'] ?? null) === '*' && ($role === 'admin' ? in_array('staff', $roles, true) : $tier !== 'trainee');
                if ($generic || in_array($role, $roles, true)) {
                    $perms[] = 'documents.issue.' . $key;
                }
            }
            foreach (array_unique(faction_filter_permissions($perms, $id)) as $p) {
                insert('rank_permissions', ['rank_id' => $rankId, 'permission' => $p]);
            }
        }
        foreach (FACTION_DEPARTMENT_TEMPLATES[$kind] as $i => [$dname, $dshort, $dcolor]) {
            insert('departments', ['faction_id' => $id, 'name' => $dname, 'short' => $dshort, 'color' => $dcolor, 'description' => '', 'sort' => $i, 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        }
        $identity = ['org_name' => $name, 'org_short' => $short ?: $name, 'doc_prefix' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $short ?: $name), 0, 6)) ?: 'DOK',
            'seal_text' => mb_strtoupper($name), 'logo' => FACTION_KINDS[$kind]['logo'], 'theme_primary' => $color ?: FACTION_KINDS[$kind]['color'], 'city' => 'Los Santos', 'currency' => '$'];
        foreach ($identity as $k => $v) {
            save_setting(faction_setting_key($k), (string) $v);
        }
        foreach (FACTION_CATALOG_TEMPLATES[$kind] ?? [] as $i => [$code, $pname, $cat, $amount, $jail]) {
            insert('price_list', ['faction_id' => $id, 'code' => $code, 'name' => $pname, 'category' => $cat, 'price_cents' => $amount * 100, 'jail' => $jail,
                'sort' => ($i + 1) * 10, 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        }
        faction_seed_content($id, $kind);
        seed_kind_content($id, $kind);
        set_faction_context($prev);
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
    setting('__reset');
    return $id;
}

/** Wiki-Startseiten (Handbuch) für eine neue Fraktion. */
function faction_seed_content(int $id, string $kind): void
{
    $now = now();
    $pages = require SAMS_ROOT . '/database/content/handbuch_generic.php';
    $first = true;
    foreach ($pages[$kind] ?? ($kind === 'medical' ? require SAMS_ROOT . '/database/content/handbuch.php' : $pages['_all']) as $title => $content) {
        if (!scalar('SELECT id FROM wiki_pages WHERE faction_id = ? AND slug = ?', [$id, wiki_slug($title)])) {
            insert('wiki_pages', ['faction_id' => $id, 'slug' => wiki_slug($title), 'title' => $title, 'category' => 'Handbuch', 'content' => $content,
                'pinned' => $first ? 1 : 0, 'created_at' => $now, 'updated_at' => $now]);
        }
        $first = false;
    }
}

// ------------------------------------------------------------ Datentrennung

/** SQL-Bedingung „gehört zur aktuellen Fraktion“ (Ganzzahl, daher sicher eingebettet). */
function fw(string $alias = ''): string
{
    return ($alias !== '' ? $alias . '.' : '') . 'faction_id = ' . fid();
}

/** Datensatz der aktuellen Fraktion per ID (null, wenn er einer anderen gehört). */
function fone(string $table, int $id): ?array
{
    return one("SELECT * FROM $table WHERE id = ? AND " . fw(), [$id]);
}

/** Aufgabe für jede aktive Fraktion im jeweiligen Kontext ausführen (Cron). */
function each_faction(callable $fn): void
{
    $prev = fid();
    foreach (factions() as $f) {
        set_faction_context((int) $f['id']);
        try {
            $fn($f);
        } finally {
            set_faction_context($prev);
        }
    }
}

/** IDs aller Fraktionen einer Art (z. B. alle Rettungsdienste). */
function faction_ids_of_kind(string $kind): array
{
    $ids = array_map(fn($f) => (int) $f['id'], array_filter(factions(false), fn($f) => ($f['kind'] ?? 'medical') === $kind));
    return $ids ?: [0];
}

function medical_faction_ids(): array
{
    return faction_ids_of_kind('medical');
}

/** Logo der aktuellen Fraktion (eigenes oder das Symbol ihrer Art). */
function faction_logo(): string
{
    return (string) setting('logo', FACTION_KINDS[faction_kind()]['logo']);
}

/** Präfix für Dokument- und Fallnummern. */
function doc_prefix(): string
{
    $short = preg_replace('/[^A-Za-z0-9]/', '', (string) (current_faction()['short'] ?? ''));
    return (string) setting('doc_prefix', fid() === 1 ? 'SAMS' : (strtoupper($short) ?: 'DOK'));
}

// ---------------------------------------------------------- Abrechnung je Art

/** Darstellung der Abrechnung (Dokumentart „rechnung“) je Fraktionsart. */
const BILLING_LABELS = [
    'medical' => ['title' => 'Rechnung', 'item' => 'Leistung', 'items' => 'Leistungen', 'date' => 'Leistungsdatum', 'intro' => 'Für die erbrachten Leistungen erlauben wir uns, wie folgt zu berechnen:', 'office' => 'Abrechnungsstelle', 'total' => 'Rechnungsbetrag'],
    'police' => ['title' => 'Bußgeldbescheid', 'item' => 'Tatbestand', 'items' => 'Tatbestände', 'date' => 'Tatzeit', 'intro' => 'Gegen Sie wird wegen der nachstehend aufgeführten Verstöße ein Bußgeld festgesetzt:', 'office' => 'Bußgeldstelle', 'total' => 'Festgesetzter Betrag'],
    'justice' => ['title' => 'Kostenrechnung', 'item' => 'Kostenposition', 'items' => 'Kosten', 'date' => 'Datum', 'intro' => 'In der oben bezeichneten Sache werden folgende Kosten erhoben:', 'office' => 'Kostenstelle', 'total' => 'Gesamtbetrag'],
    'fire' => ['title' => 'Gebührenbescheid', 'item' => 'Leistung', 'items' => 'Leistungen', 'date' => 'Einsatzdatum', 'intro' => 'Für den Einsatz werden nach der Gebührenordnung folgende Gebühren erhoben:', 'office' => 'Gebührenstelle', 'total' => 'Gebühr gesamt'],
    'rescue' => ['title' => 'Kostenbescheid', 'item' => 'Leistung', 'items' => 'Leistungen', 'date' => 'Einsatzdatum', 'intro' => 'Für den Rettungs- bzw. Bergungseinsatz werden folgende Kosten erhoben:', 'office' => 'Kostenstelle', 'total' => 'Kosten gesamt'],
    'government' => ['title' => 'Gebührenbescheid', 'item' => 'Gebühr', 'items' => 'Gebühren', 'date' => 'Datum', 'intro' => 'Für die nachstehenden Amtshandlungen werden folgende Gebühren erhoben:', 'office' => 'Gebührenstelle', 'total' => 'Gebühr gesamt'],
];

function billing_labels(?int $factionId = null): array
{
    return BILLING_LABELS[faction_kind($factionId)] ?? BILLING_LABELS['medical'];
}

/** Arbeitet der Katalog mit Haft-Einheiten (Polizei, Justiz)? */
function catalog_has_jail(?int $factionId = null): bool
{
    return in_array(faction_kind($factionId), ['police', 'justice'], true);
}

// ----------------------------------------------------------- Akten je Art

const DANGER_FLAGS = ['bewaffnet' => 'bewaffnet', 'gewaltbereit' => 'gewaltbereit', 'flucht' => 'Fluchtgefahr', 'btm' => 'BtM-Konsum', 'psych' => 'psychisch auffällig', 'ansteckend' => 'Ansteckungsgefahr'];
const LICENSE_STATES = ['' => 'unbekannt', 'gueltig' => 'gültig', 'keiner' => 'keiner', 'entzogen' => 'entzogen', 'gesperrt' => 'gesperrt'];

/** Zusätzliche Aktenfelder je Fraktionsart (gespeichert in patients.extra). */
function record_extra_fields(?string $kind = null): array
{
    $kind ??= faction_kind();
    $person = [
        ['name' => 'aliases', 'label' => 'Aliasnamen / Spitznamen', 'type' => 'text'],
        ['name' => 'gang', 'label' => 'Gruppierung / Organisation', 'type' => 'text'],
        ['name' => 'danger', 'label' => 'Gefährdungshinweise', 'type' => 'checkboxes', 'options' => DANGER_FLAGS],
        ['name' => 'license_driver', 'label' => 'Führerschein', 'type' => 'select', 'options' => LICENSE_STATES],
        ['name' => 'license_weapon', 'label' => 'Waffenschein', 'type' => 'select', 'options' => LICENSE_STATES],
        ['name' => 'vehicles', 'label' => 'Fahrzeuge (Kennzeichen, Modell, Farbe)', 'type' => 'textarea'],
        ['name' => 'description', 'label' => 'Personenbeschreibung (Größe, Statur, Tattoos …)', 'type' => 'textarea'],
    ];
    return match ($kind) {
        'police' => $person,
        'justice' => array_merge([['name' => 'lawyer', 'label' => 'Rechtsbeistand', 'type' => 'text']], array_slice($person, 0, 3), [['name' => 'priors', 'label' => 'Vorstrafen (Kurzfassung)', 'type' => 'textarea']]),
        'government' => [
            ['name' => 'business', 'label' => 'Gewerbe / Firma', 'type' => 'text'],
            ['name' => 'licenses', 'label' => 'Lizenzen und Genehmigungen', 'type' => 'textarea'],
        ],
        default => [],
    };
}

function record_extra(array $p): array
{
    return json_decode((string) ($p['extra'] ?? ''), true) ?: [];
}

/** Anzeigewert eines Zusatzfeldes. */
function record_extra_value(array $f, $v): string
{
    if (is_array($v)) {
        return implode(', ', array_map(fn($k) => $f['options'][$k] ?? $k, $v));
    }
    return isset($f['options']) ? (string) ($f['options'][$v] ?? $v) : (string) $v;
}

/** Arten von Akteneinträgen je Fraktionsart. */
function entry_types(?string $kind = null): array
{
    return match ($kind ?? faction_kind()) {
        'police' => ['Vorgang / Notiz', 'Anzeige', 'Personenkontrolle', 'Verkehrskontrolle', 'Vernehmung', 'Festnahme', 'Durchsuchung', 'Sicherstellung', 'Gefährderansprache', 'Platzverweis', 'Telefonat', 'Sonstiges'],
        'justice' => ['Notiz', 'Eingang', 'Verhandlung', 'Beschluss', 'Urteil', 'Akteneinsicht', 'Vollstreckung', 'Telefonat', 'Sonstiges'],
        'fire' => ['Einsatz', 'Brandschau', 'Beratung', 'Notiz', 'Sonstiges'],
        'government' => ['Notiz', 'Antrag', 'Bescheid', 'Vorsprache', 'Telefonat', 'Sonstiges'],
        'rescue' => ['Einsatz', 'Rettung', 'Suche', 'Bergung', 'Übergabe an SAMS', 'Notiz', 'Sonstiges'],
        default => ['Behandlung', 'Notfalleinsatz', 'Untersuchung', 'Verlaufsnotiz', 'Psychologisches Gespräch', 'Labor', 'Bildgebung', 'Operation', 'Medikation', 'Telefonat', 'Sonstiges'],
    };
}

/** Textvorlagen für Akteneinträge: [Bezeichnung => Text]. */
function entry_templates(?string $kind = null): array
{
    return match ($kind ?? faction_kind()) {
        'police' => [
            'Sachverhalt' => "Ort / Zeit:\n\nBeteiligte:\n\nSachverhalt:\n\nMaßnahmen:\n\nVerbleib:",
            'Vernehmung' => "Vernommen als: Beschuldigte/r / Zeug/in\nBelehrung erfolgt: ja\n\nAngaben zur Sache:\n\nUnterschrift verweigert: nein",
            'Festnahme' => "Grund der Festnahme:\nRechte verlesen um:\nDurchsuchung / Sicherstellungen:\nVerletzungen / Rettungsdienst:\nVerbleib (Zelle / Haftrichter):",
        ],
        'justice' => [
            'Verhandlung' => "Termin:\nAnwesend:\n\nVerlauf:\n\nErgebnis:",
            'Beschluss' => "Antrag:\n\nEntscheidung:\n\nBegründung:",
        ],
        'fire' => ['Einsatz' => "Alarmierung:\nLage bei Eintreffen:\nMaßnahmen:\nEinsatzende:"],
        'government' => ['Antrag' => "Anliegen:\n\nUnterlagen:\n\nErgebnis:"],
        'rescue' => ['Rettung' => "Einsatzort / Koordinaten:\nLage bei Eintreffen:\nRettungsweg / Technik:\nZustand der Person:\nÜbergabe (SAMS / selbstständig):"],
        default => [
            'SOAP' => "Anamnese:\n\nBefund:\n\nDiagnose:\n\nTherapie:\n\nProcedere:",
            'ABCDE' => "A – Atemwege:\nB – Atmung:\nC – Kreislauf:\nD – Neurologie:\nE – Exposure / Umgebung:",
        ],
    };
}

/** Bezeichnungen der Fall-/Vorgangsfelder je Fraktionsart. */
function case_labels(?string $kind = null): array
{
    return match ($kind ?? faction_kind()) {
        'police' => ['incident' => 'Sachverhalt', 'incident_ph' => 'Was ist passiert? Wer war beteiligt?', 'diagnosis' => 'Tatvorwurf / Delikte', 'diagnosis_ph' => 'Ein Delikt pro Zeile'],
        'justice' => ['incident' => 'Sachverhalt', 'incident_ph' => 'Gegenstand des Verfahrens', 'diagnosis' => 'Anklagepunkte / Gegenstand', 'diagnosis_ph' => 'Ein Punkt pro Zeile'],
        'fire' => ['incident' => 'Lage', 'incident_ph' => 'Lage bei Eintreffen', 'diagnosis' => 'Maßnahmen', 'diagnosis_ph' => 'Eine Maßnahme pro Zeile'],
        'rescue' => ['incident' => 'Lage', 'incident_ph' => 'Wo und wie wurde die Person gefunden? Gelände, Gewässer, Wetter', 'diagnosis' => 'Maßnahmen / Rettungstechnik', 'diagnosis_ph' => 'Eine Maßnahme pro Zeile'],
        'government' => ['incident' => 'Anliegen', 'incident_ph' => 'Worum geht es?', 'diagnosis' => 'Ergebnis / Bescheid', 'diagnosis_ph' => ''],
        default => ['incident' => 'Hergang / Anlass', 'incident_ph' => 'Was ist passiert? Wie wurde der Patient vorgefunden?', 'diagnosis' => 'Diagnose(n)', 'diagnosis_ph' => 'Eine Diagnose pro Zeile, gern mit ICD-10 (z. B. S52.5 Distale Radiusfraktur)'],
    };
}

/**
 * Fachinhalte für den Rettungsdienst einspielen (Wiki-Seiten, Lehrgänge mit Modulen und Prüfungsfragen).
 * Nur Fehlendes wird angelegt – eigene Änderungen bleiben erhalten. Gibt [Wiki, Lehrgänge] zurück.
 */
function seed_medical_content(int $factionId): array
{
    return seed_kind_content($factionId, 'medical');
}

/**
 * Fachinhalte einer Fraktionsart laden: database/content/<art>_wiki*.php und <art>_training.php.
 * Legt nur Fehlendes an; fehlende Qualifikationen der Lehrgänge werden angelegt (außer beim Rettungsdienst).
 */
function seed_kind_content(int $factionId, string $kind): array
{
    $now = now();
    $dir = SAMS_ROOT . '/database/content/';
    $wiki = 0;
    $files = glob($dir . $kind . '_wiki*.php') ?: [];
    sort($files);
    foreach (array_map('basename', $files) as $file) {
        foreach (is_file($dir . $file) ? require $dir . $file : [] as $pg) {
            $slug = wiki_slug($pg['title']);
            if (!scalar('SELECT id FROM wiki_pages WHERE faction_id = ? AND slug = ?', [$factionId, $slug])) {
                insert('wiki_pages', ['faction_id' => $factionId, 'slug' => $slug, 'title' => $pg['title'], 'category' => $pg['category'], 'content' => trim($pg['content']),
                    'pinned' => 0, 'created_at' => $now, 'updated_at' => $now]);
                $wiki++;
            }
        }
    }
    $courses = 0;
    $sort = (int) scalar('SELECT COALESCE(MAX(sort), 0) FROM training_courses WHERE faction_id = ?', [$factionId]);
    foreach (is_file($dir . $kind . '_training.php') ? require $dir . $kind . '_training.php' : [] as $c) {
        if (scalar('SELECT id FROM training_courses WHERE faction_id = ? AND title = ?', [$factionId, $c['title']])) {
            continue;
        }
        $qual = $c['qualification'] ? scalar('SELECT id FROM qualifications WHERE faction_id = ? AND name = ?', [$factionId, $c['qualification']]) : null;
        if ($c['qualification'] && !$qual && $kind !== 'medical') {
            $qual = insert('qualifications', ['faction_id' => $factionId, 'name' => $c['qualification'], 'short' => mb_strtoupper(mb_substr(preg_replace('/[^\p{L}]/u', '', $c['qualification']), 0, 3)),
                'color' => '#a3161a', 'description' => 'Erworben mit dem Lehrgang „' . $c['title'] . '“ (Theorie und praktische Prüfung).', 'sort' => 0, 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);
        }
        $cid = insert('training_courses', ['faction_id' => $factionId, 'title' => $c['title'], 'description' => $c['description'], 'qualification_id' => $qual ?: null,
            'pass_percent' => $c['pass_percent'], 'question_count' => $c['question_count'], 'time_limit' => $c['time_limit'], 'hours' => $c['hours'],
            'active' => 1, 'sort' => $sort += 10, 'created_at' => $now, 'updated_at' => $now]);
        foreach (array_values($c['modules']) as $i => [$mtitle, $mcontent]) {
            insert('training_modules', ['course_id' => $cid, 'title' => $mtitle, 'content' => trim($mcontent), 'sort' => ($i + 1) * 10]);
        }
        foreach (array_values($c['questions']) as $i => [$q, $opts, $correct, $expl]) {
            insert('training_questions', ['course_id' => $cid, 'question' => $q, 'options' => json_encode(array_values($opts), JSON_UNESCAPED_UNICODE),
                'correct' => json_encode(array_values($correct)), 'explanation' => $expl, 'sort' => ($i + 1) * 10]);
        }
        $courses++;
    }
    return [$wiki, $courses];
}
