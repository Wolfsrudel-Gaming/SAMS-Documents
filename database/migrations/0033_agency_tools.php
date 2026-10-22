<?php
/**
 * Behörden-Werkzeuge: Asservaten, Fahrzeugregister, Haftbuch (Polizei), Gerichtskalender und Fristen (Justiz),
 * Lizenzen mit Ablauf (Verwaltung/DMV, Polizei), Objektpläne (Feuerwehr), gemeinsame Einsatzberichte (alle).
 */
return [
    'description' => 'Behörden-Werkzeuge (Asservaten, Fahrzeuge, Haftbuch, Gericht, Lizenzen, Objekte, gemeinsame Berichte)',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $txt = $s->text();
        $e = $s->engine();
        $s->exec("CREATE TABLE IF NOT EXISTS evidence (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, number {$s->str(32)} NOT NULL DEFAULT '', title $str NOT NULL, description $txt,
            category {$s->str(64)} DEFAULT '', quantity $str DEFAULT '', storage $str DEFAULT '', status {$s->str(16)} NOT NULL DEFAULT 'stored',
            patient_id $fk NULL, case_id $fk NULL, owner $str DEFAULT '', found_at $dt NULL, found_location $str DEFAULT '',
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_evidence_faction', 'evidence', 'faction_id, status');
        $s->exec("CREATE TABLE IF NOT EXISTS evidence_moves (
            id {$s->pk()}, evidence_id $fk NOT NULL, action {$s->str(16)} NOT NULL, note $str DEFAULT '', counterpart $str DEFAULT '',
            user_id $fk NULL, created_at $dt NOT NULL
        )$e");
        $s->index('idx_evidence_moves', 'evidence_moves', 'evidence_id');
        $s->exec("CREATE TABLE IF NOT EXISTS vehicles (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, plate {$s->str(16)} NOT NULL, model $str DEFAULT '', color {$s->str(64)} DEFAULT '', vin {$s->str(64)} DEFAULT '',
            patient_id $fk NULL, owner_name $str DEFAULT '', owner_citizen_id {$s->str(64)} DEFAULT '', status {$s->str(16)} NOT NULL DEFAULT 'registered',
            valid_until {$s->str(10)} DEFAULT '', notes $txt, created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_vehicles_plate', 'vehicles', 'plate');
        $s->index('idx_vehicles_faction', 'vehicles', 'faction_id');
        $s->exec("CREATE TABLE IF NOT EXISTS custody (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, patient_id $fk NULL, citizen_id {$s->str(64)} DEFAULT '', name $str NOT NULL,
            arrested_at $dt NOT NULL, arrested_by $fk NULL, reason $txt, jail_units INTEGER NOT NULL DEFAULT 0, cell {$s->str(64)} DEFAULT '',
            belongings $txt, fit_check {$s->str(16)} NOT NULL DEFAULT '', lawyer INTEGER NOT NULL DEFAULT 0, phone_call INTEGER NOT NULL DEFAULT 0,
            status {$s->str(16)} NOT NULL DEFAULT 'in', released_at $dt NULL, released_by $fk NULL, release_note $str DEFAULT '', created_at $dt NOT NULL
        )$e");
        $s->index('idx_custody_faction', 'custody', 'faction_id, status');
        $s->exec("CREATE TABLE IF NOT EXISTS hearings (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, title $str NOT NULL, kind {$s->str(32)} NOT NULL DEFAULT '', case_ref {$s->str(64)} DEFAULT '',
            patient_id $fk NULL, starts_at $dt NOT NULL, room {$s->str(64)} DEFAULT '', judge_id $fk NULL, prosecutor_id $fk NULL, lawyer_id $fk NULL,
            participants $txt, status {$s->str(16)} NOT NULL DEFAULT 'planned', result $txt, reminded INTEGER NOT NULL DEFAULT 0,
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_hearings_faction', 'hearings', 'faction_id, starts_at');
        $s->exec("CREATE TABLE IF NOT EXISTS deadlines (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, title $str NOT NULL, kind {$s->str(32)} DEFAULT '', due_date {$s->str(10)} NOT NULL,
            case_ref {$s->str(64)} DEFAULT '', patient_id $fk NULL, assigned_to $fk NULL, note $txt, reminded INTEGER NOT NULL DEFAULT 0,
            done_at $dt NULL, done_by $fk NULL, created_by $fk NULL, created_at $dt NOT NULL
        )$e");
        $s->index('idx_deadlines_faction', 'deadlines', 'faction_id, due_date');
        $s->exec("CREATE TABLE IF NOT EXISTS licenses (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, patient_id $fk NULL, citizen_id {$s->str(64)} DEFAULT '', name $str NOT NULL,
            type {$s->str(64)} NOT NULL, classes {$s->str(64)} DEFAULT '', number {$s->str(32)} DEFAULT '', issued_on {$s->str(10)} DEFAULT '',
            expires_on {$s->str(10)} DEFAULT '', status {$s->str(16)} NOT NULL DEFAULT 'valid', suspended_until {$s->str(10)} DEFAULT '', note $str DEFAULT '',
            reminded INTEGER NOT NULL DEFAULT 0, created_by $fk NULL, created_at $dt NOT NULL, updated_by $fk NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_licenses_citizen', 'licenses', 'citizen_id');
        $s->index('idx_licenses_faction', 'licenses', 'faction_id, status');
        $s->exec("CREATE TABLE IF NOT EXISTS objects (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, name $str NOT NULL, address $str DEFAULT '', kind {$s->str(64)} DEFAULT '', risk INTEGER NOT NULL DEFAULT 0,
            contact $str DEFAULT '', keybox $str DEFAULT '', hazards $txt, access $txt, notes $txt, last_inspection {$s->str(10)} DEFAULT '',
            next_inspection {$s->str(10)} DEFAULT '', created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_objects_faction', 'objects', 'faction_id');
        $s->exec("CREATE TABLE IF NOT EXISTS object_files (
            id {$s->pk()}, object_id $fk NOT NULL, title $str NOT NULL, stored_name {$s->str(64)} NOT NULL, mime {$s->str(64)} NOT NULL, size INTEGER NOT NULL DEFAULT 0,
            has_thumb INTEGER NOT NULL DEFAULT 0, created_by $fk NULL, created_at $dt NOT NULL
        )$e");
        $s->index('idx_object_files', 'object_files', 'object_id');
        $s->exec("CREATE TABLE IF NOT EXISTS joint_reports (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, number {$s->str(32)} NOT NULL DEFAULT '', title $str NOT NULL, occurred_at $dt NOT NULL,
            location $str DEFAULT '', summary $txt, status {$s->str(16)} NOT NULL DEFAULT 'open', created_by $fk NULL, created_at $dt NOT NULL,
            closed_at $dt NULL, closed_by $fk NULL
        )$e");
        $s->exec("CREATE TABLE IF NOT EXISTS joint_participants (
            id {$s->pk()}, report_id $fk NOT NULL, faction_id $fk NOT NULL, added_at $dt NOT NULL
        )$e");
        $s->index('idx_joint_part', 'joint_participants', 'report_id, faction_id');
        $s->exec("CREATE TABLE IF NOT EXISTS joint_sections (
            id {$s->pk()}, report_id $fk NOT NULL, faction_id $fk NOT NULL, user_id $fk NULL, body $txt, created_at $dt NOT NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_joint_sections', 'joint_sections', 'report_id');

        // Neue Module für Fraktionen mit eigener Modulauswahl ergänzen (Vorgabe der Art)
        $new = ['medical' => ['joint'], 'police' => ['evidence', 'vehicles', 'custody', 'licenses', 'joint'], 'justice' => ['court'],
            'fire' => ['objects', 'joint'], 'government' => ['licenses', 'vehicles']];
        $byFaction = [];
        foreach ($s->db->query('SELECT id, kind, modules FROM factions')->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $kind = $f['kind'] ?: 'medical';
            $mods = json_decode((string) $f['modules'], true);
            if (is_array($mods)) {
                $mods = array_values(array_unique(array_merge($mods, $new[$kind] ?? [])));
                $s->db->prepare('UPDATE factions SET modules = ? WHERE id = ?')->execute([json_encode($mods), $f['id']]);
            }
            $byFaction[(int) $f['id']] = $new[$kind] ?? [];
        }
        // Rechte: ansehen wie „Dokumente ansehen“, bearbeiten wie „eigene Dokumente stornieren“ (Mitarbeitende ab Grundstufe)
        $perms = ['evidence' => ['evidence.view', 'evidence.manage'], 'vehicles' => ['vehicles.view', 'vehicles.manage'], 'custody' => ['custody.view', 'custody.manage'],
            'court' => ['court.view', 'court.manage'], 'licenses' => ['licenses.view', 'licenses.manage'], 'objects' => ['objects.view', 'objects.manage'],
            'joint' => ['joint.view', 'joint.write']];
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query('SELECT id, faction_id, short FROM ranks')->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $has = $s->db->query('SELECT permission FROM rank_permissions WHERE rank_id = ' . (int) $r['id'])->fetchAll(PDO::FETCH_COLUMN);
            foreach ($byFaction[(int) $r['faction_id']] ?? [] as $m) {
                [$view, $manage] = $perms[$m];
                $grant = [];
                if (in_array('documents.view', $has, true)) {
                    $grant[] = $view;
                }
                if (in_array('documents.revoke.own', $has, true)) {
                    $grant[] = $manage;
                }
                // Anwältinnen und Anwälte sehen im Gerichtskalender nur ihre eigenen Termine und Fristen
                if ($m === 'court' && $r['short'] === 'RA') {
                    $grant = ['court.view', 'court.own'];
                }
                foreach (array_diff($grant, $has) as $p) {
                    $add->execute([$r['id'], $p]);
                }
            }
        }
    },
];
