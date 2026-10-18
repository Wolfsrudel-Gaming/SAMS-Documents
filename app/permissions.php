<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Rechte-Katalog und Prüfungen.
 *
 * Rechte hängen am Rang. Der Hauptadministrator (is_superadmin) hat immer alle Rechte.
 * Hierarchie-Regel: Konten und Ränge darf man nur unterhalb der eigenen Rangstufe verwalten,
 * und man kann nur Rechte vergeben, die man selbst besitzt.
 */
function permission_catalog(): array
{
    static $cache = [];
    if (isset($cache[fid()])) {
        return $cache[fid()];
    }
    $docs = [];
    foreach (document_types() as $key => $t) {
        $docs['documents.issue.' . $key] = $t['title'];
    }
    $catalog = [
        term('records') => [
            'patients.view'        => 'Akten ansehen',
            'patients.edit'        => 'Akten anlegen und bearbeiten',
            'patients.delete'      => 'Archivierte Akten endgültig löschen',
            'patients.archive'     => 'Akten archivieren und reaktivieren',
            'cases.manage'         => 'Fallakten anlegen, bearbeiten und abschließen',
            'entries.create'       => 'Akteneinträge schreiben',
            'attachments.upload'   => 'Anhänge hochladen (Fotos, Röntgenbilder, Scans, PDF)',
            'patients.merge'       => 'Doppelte Akten zusammenführen',
            'entries.confidential' => 'Vertrauliche Einträge lesen',
            'entries.edit_all'     => 'Einträge anderer bearbeiten',
            'entries.all_departments' => 'Abteilungseinträge aller Abteilungen lesen',
        ],
        'Dokumente' => [
            'documents.view'        => 'Dokumente ansehen',
            'documents.revoke.own'  => 'Eigene Dokumente stornieren',
            'documents.revoke.all'  => 'Alle Dokumente stornieren',
            'documents.print'       => 'Dokumente im Spiel drucken',
            'documents.approve'     => 'Dokumente freigeben (Gegenzeichnung)',
            'documents.addendum'    => 'Nachträge zu fremden Dokumenten schreiben',
            'documents.drafts_all'  => 'Alle Entwürfe einsehen',
            'documents.share'       => 'Dokumente für Behörden freigeben (Link oder Konto)',
            'documents.shared_view' => 'Für das eigene Konto freigegebene Dokumente ansehen (z. B. LSPD-Konten)',
        ],
        'Abrechnung' => [
            'invoices.manage' => 'Rechnungen verwalten (bezahlt, mahnen)',
            'prices.manage'   => 'Gebührenordnung (Preisliste) pflegen',
        ],
        'Dokumente ausstellen' => $docs,
        'Personal' => [
            'users.view'           => 'Personalliste ansehen',
            'users.create'         => 'Konten anlegen',
            'users.edit'           => 'Konten bearbeiten, Rang und Abteilungen vergeben',
            'users.password_reset' => 'Passwörter zurücksetzen',
            'users.deactivate'     => 'Konten sperren und entsperren',
            'users.sessions'       => 'Sitzungen anderer beenden',
            'users.2fa_reset'      => '2FA anderer zurücksetzen',
            'users.scope_department' => 'Einschränkung: nur Personal der eigenen Abteilungen verwalten',
            'personnel.view'         => 'Personalakten ansehen',
            'personnel.edit'         => 'Einträge in Personalakten schreiben',
            'personnel.confidential' => 'Vertrauliche Personalakten-Einträge sehen (z. B. Abmahnungen)',
        ],
        'Dienstzeiten' => [
            'duty.clock'  => 'Dienst beginnen/beenden (stempeln)',
            'duty.view'   => 'Sehen, wer im Dienst ist',
            'duty.manage' => 'Dienstzeiten aller einsehen und korrigieren',
            'roster.view'   => 'Dienstplan ansehen',
            'roster.signup' => 'Sich in Schichten eintragen, austragen und tauschen',
            'roster.manage' => 'Dienstplan erstellen, Personal einteilen',
        ],
        'Polizei & Behörden' => [
            'evidence.view'   => 'Asservaten ansehen',
            'evidence.manage' => 'Asservaten erfassen, ein-/auslagern, übergeben, vernichten',
            'vehicles.view'   => 'Fahrzeugregister ansehen',
            'vehicles.manage' => 'Fahrzeuge registrieren und ändern',
            'custody.view'    => 'Haftbuch ansehen',
            'custody.manage'  => 'Personen in Gewahrsam nehmen und entlassen',
            'licenses.view'   => 'Lizenzen und Führerscheine ansehen',
            'licenses.manage' => 'Lizenzen ausstellen, verlängern, sperren und entziehen',
            'objects.view'    => 'Objektpläne ansehen',
            'objects.manage'  => 'Objektpläne anlegen und pflegen',
            'joint.view'      => 'Gemeinsame Einsatzberichte ansehen',
            'joint.write'     => 'Gemeinsame Einsatzberichte anlegen und eigenen Teil schreiben',
            'equipment.view'   => 'Geräte und Prüffristen ansehen',
            'equipment.manage' => 'Geräte anlegen und Prüfungen eintragen',
            'breathing.view'   => 'Atemschutz-Nachweise ansehen',
            'breathing.manage' => 'Atemschutz-Nachweise pflegen (Eignung, Übungen, Einsätze)',
        ],
        'Gericht' => [
            'court.view'   => 'Gerichtskalender und Fristen ansehen',
            'court.manage' => 'Termine und Fristen anlegen und bearbeiten',
            'court.own'    => 'Einschränkung: nur Termine und Fristen, an denen man selbst beteiligt ist (Anwaltszugang)',
        ],
        'Medikamente' => [
            'stock.view'     => 'Medikamentenbestand ansehen',
            'stock.dispense' => 'Medikamente ausgeben und Rezepte einlösen',
            'stock.manage'   => 'Bestand pflegen (Artikel, Zugänge, Korrekturen)',
            'btm.manage'     => 'BtM-Buch führen und prüfen (Betäubungsmittel)',
        ],
        'Ausbildung' => [
            'training.view'   => 'Lehrgänge lernen und Prüfungen ablegen',
            'training.manage' => 'Lehrgänge, Module und Prüfungsfragen verwalten, Ergebnisse sehen',
        ],
        'Wiki' => [
            'wiki.view'    => 'Wiki lesen',
            'wiki.edit'    => 'Wiki-Seiten anlegen und bearbeiten',
            'lists.manage' => 'Vorschlagslisten der Formulare pflegen (ICD, Medikamente …)',
        ],
        'Kommunikation' => [
            'board.view'   => 'Schwarzes Brett lesen',
            'board.manage' => 'Aushänge veröffentlichen und Lesebestätigungen einsehen',
            'messages.use' => 'Interne Nachrichten senden und empfangen',
        ],
        'Bürgerportal' => [
            'portal.manage'       => 'Terminanfragen und Anträge aus dem Bürgerportal bearbeiten, Bürgerkonten verwalten',
            'portal.link'         => 'Bürgerkonten mit Patientenakten verknüpfen',
            'portal.applications' => 'Bewerbungen bearbeiten',
        ],
        'Organisation' => [
            'ranks.manage'          => 'Ränge und Rechte verwalten',
            'departments.manage'    => 'Abteilungen verwalten',
            'qualifications.manage' => 'Qualifikationen verwalten',
            'printers.manage'       => 'Drucker in der Spielwelt verwalten',
        ],
        'System' => [
            'settings.manage' => 'Einstellungen (Briefkopf, Design, Logo)',
            'templates.manage' => 'Vorlagen-Editor: eigene Dokumentarten anlegen',
            'stats.view'       => 'Statistik und Berichte ansehen',
            'discord.manage'   => 'Discord-Anbindung (Webhooks, Berichte, Rollen-Sync) einrichten',
            'audit.view'      => 'Zugriffsprotokoll ansehen',
            'system.manage'   => 'System, Updates und Backups',
        ],
        'Fahndung' => [
            'wanted.view'   => 'Fahndungen und Haftbefehle einsehen',
            'wanted.manage' => 'Personen und Fahrzeuge zur Fahndung ausschreiben und Fahndungen erledigen',
        ],
        'Behördenanfragen' => [
            'requests.view'   => 'Anfragen der eigenen Behörde einsehen',
            'requests.manage' => 'Anfragen an andere Behörden stellen und eingehende beantworten',
        ],
        'Sicherheit' => [
            'security.2fa_required' => 'Zwei-Faktor-Anmeldung ist Pflicht',
        ],
    ];
    // Nur Rechte der Module dieser Fraktion; Systemverwaltung nur in der Hauptfraktion
    foreach ($catalog as $group => $perms) {
        $keep = array_flip(faction_filter_permissions(array_keys($perms)));
        if (fid() !== 1) {
            unset($keep['system.manage']);
        }
        $catalog[$group] = array_intersect_key($perms, $keep);
        if (!$catalog[$group]) {
            unset($catalog[$group]);
        }
    }
    return $cache[fid()] = $catalog;
}

function all_permissions(): array
{
    return array_merge(...array_values(array_map('array_keys', permission_catalog())));
}

/** Rechte eines Rangs (gecacht). */
function rank_permissions(?int $rankId): array
{
    static $cache = [];
    if (!$rankId) {
        return [];
    }
    if (!isset($cache[$rankId])) {
        $cache[$rankId] = array_column(all('SELECT permission FROM rank_permissions WHERE rank_id = ?', [$rankId]), 'permission');
    }
    return $cache[$rankId];
}

function user_permissions(?array $u): array
{
    static $memo = [];
    if (!$u) {
        return [];
    }
    $k = ($u['id'] ?? 0) . ':' . fid() . ':' . ($u['rank_id'] ?? 0) . ':' . ($u['is_superadmin'] ?? 0) . ':' . ($u['faction_id'] ?? 1);
    return $memo[$k] ??= user_permissions_uncached($u);
}

function user_permissions_uncached(array $u): array
{
    if (!empty($u['is_superadmin'])) {
        // Hauptadministrator: alle Fähigkeiten, aber keine Einschränkungen. Die 2FA-Pflicht richtet sich nach seinem Rang.
        $perms = array_diff(all_permissions(), ['users.scope_department', 'security.2fa_required', 'court.own']);
        if (in_array('security.2fa_required', rank_permissions(isset($u['rank_id']) ? (int) $u['rank_id'] : null), true)) {
            $perms[] = 'security.2fa_required';
        }
        return faction_filter_permissions(array_values($perms));
    }
    // Rechte gelten nur in der eigenen Fraktion
    if ((int) ($u['faction_id'] ?? 1) !== fid()) {
        return [];
    }
    return faction_filter_permissions(rank_permissions(isset($u['rank_id']) ? (int) $u['rank_id'] : null));
}

/** Hat der angemeldete Benutzer das Recht? Mehrere Rechte mit | = eines davon genügt. */
function can(string $permission): bool
{
    $perms = user_permissions(current_user());
    foreach (explode('|', $permission) as $p) {
        if (str_ends_with($p, '*')) {
            $prefix = substr($p, 0, -1);
            foreach ($perms as $have) {
                if (str_starts_with($have, $prefix)) {
                    return true;
                }
            }
        } elseif (in_array($p, $perms, true)) {
            return true;
        }
    }
    return false;
}

function require_perm(string $permission): void
{
    require_login();
    if (!can($permission)) {
        forbidden('Dafür fehlt deinem Rang die nötige Berechtigung.');
    }
}

function can_issue_any(): bool
{
    return can('documents.issue.*');
}

function rank_by_id(?int $id): ?array
{
    static $cache = [];
    if (!$id) {
        return null;
    }
    return $cache[$id] ??= one('SELECT * FROM ranks WHERE id = ?', [$id]);
}

/** Rangstufe eines Kontos. Der Hauptadministrator steht über allen Rängen. */
function user_level(?array $u): int
{
    if (!$u) {
        return -1;
    }
    if (!empty($u['is_superadmin'])) {
        return PHP_INT_MAX;
    }
    return (int) (rank_by_id(isset($u['rank_id']) ? (int) $u['rank_id'] : null)['level'] ?? 0);
}

function user_department_ids(int $userId): array
{
    return array_map('intval', array_column(all('SELECT department_id FROM user_departments WHERE user_id = ?', [$userId]), 'department_id'));
}

function user_qualification_ids(int $userId): array
{
    return array_map('intval', array_column(all('SELECT qualification_id FROM user_qualifications WHERE user_id = ?', [$userId]), 'qualification_id'));
}

/** Ist die Verwaltung auf die eigenen Abteilungen beschränkt? */
function scoped_to_departments(): bool
{
    return can('users.scope_department');
}

/** Darf ich dieses Konto verwalten (Hierarchie + ggf. Abteilungsbeschränkung)? */
function can_manage_user(array $target, string $permission = 'users.edit'): bool
{
    $me = current_user();
    if (!$me || !can($permission)) {
        return false;
    }
    if (!empty($me['is_superadmin'])) {
        return (int) $target['id'] !== (int) $me['id'] || $permission === 'users.edit';
    }
    if ((int) $target['id'] === (int) $me['id'] || !empty($target['is_superadmin'])) {
        return false;
    }
    if (user_level($target) >= user_level($me)) {
        return false;
    }
    if (scoped_to_departments()) {
        return (bool) array_intersect(user_department_ids((int) $target['id']), user_department_ids((int) $me['id']));
    }
    return true;
}

/** Ränge, die ich vergeben darf (unterhalb der eigenen Stufe). */
function assignable_ranks(): array
{
    $level = user_level(current_user());
    return array_values(array_filter(all('SELECT * FROM ranks WHERE ' . fw() . ' ORDER BY level DESC, name'), fn($r) => (int) $r['level'] < $level));
}

/** Abteilungen, die ich zuweisen darf. */
function assignable_departments(): array
{
    $deps = all('SELECT * FROM departments WHERE active = 1 AND ' . fw() . ' ORDER BY sort, name');
    if (!scoped_to_departments()) {
        return $deps;
    }
    $mine = user_department_ids((int) current_user()['id']);
    return array_values(array_filter($deps, fn($d) => in_array((int) $d['id'], $mine, true)));
}

/** Darf ich diesen Rang bearbeiten? */
function can_manage_rank(array $rank): bool
{
    return can('ranks.manage') && (int) $rank['level'] < user_level(current_user());
}

function rank_badge(?array $rank): string
{
    if (!$rank) {
        return '<span class="badge grey">ohne Rang</span>';
    }
    return '<span class="rank-badge" style="--rc:' . e(hex_color((string) $rank['color'], '#64717f')) . '" title="Stufe ' . (int) $rank['level'] . '">'
        . e($rank['short'] !== '' ? $rank['short'] : $rank['name']) . '</span>';
}

function tag_badge(array $item): string
{
    return '<span class="tag-badge" style="--tc:' . e(hex_color((string) $item['color'], '#64717f')) . '" title="' . e($item['name']) . '">'
        . e($item['short'] !== '' ? $item['short'] : $item['name']) . '</span>';
}

/** Name des Rangs eines Kontos für die Anzeige. */
function user_rank_name(?array $u): string
{
    if (!$u) {
        return '';
    }
    if (!empty($u['rank_name']) && empty($u['rank_id'])) {
        return (string) $u['rank_name'];
    }
    return (string) (rank_by_id(isset($u['rank_id']) ? (int) $u['rank_id'] : null)['name'] ?? '');
}

function can_revoke(array $doc): bool
{
    return can('documents.revoke.all') || (can('documents.revoke.own') && (int) $doc['issued_by'] === (int) (current_user()['id'] ?? 0));
}
