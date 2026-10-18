<?php
/**
 * Feuerwehr-Ausbau: Geräte & Prüffristen (auch Rettungsdienst), Atemschutz-Nachweise (FwDV 7),
 * Ausstellrechte für die neuen Feuerwehr-Dokumentarten und Fachinhalte (Wiki, Lehrgänge).
 */
return [
    'description' => 'Feuerwehr: Geräte & Prüffristen, Atemschutz, neue Dokumentarten, Fachinhalte',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $dt = $s->datetime();
        $str = $s->str();
        $e = $s->engine();
        $s->exec("CREATE TABLE IF NOT EXISTS equipment (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, name $str NOT NULL, category {$s->str(64)} DEFAULT '', inventory_no {$s->str(64)} DEFAULT '',
            location $str DEFAULT '', interval_days INTEGER NOT NULL DEFAULT 365, last_check {$s->str(10)} DEFAULT '', next_check {$s->str(10)} DEFAULT '',
            status {$s->str(16)} NOT NULL DEFAULT 'ok', notes {$s->text()}, reminded INTEGER NOT NULL DEFAULT 0, active INTEGER NOT NULL DEFAULT 1,
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NULL
        )$e");
        $s->index('idx_equipment_faction', 'equipment', 'faction_id, next_check');
        $s->exec("CREATE TABLE IF NOT EXISTS equipment_checks (
            id {$s->pk()}, equipment_id $fk NOT NULL, result {$s->str(16)} NOT NULL, note $str DEFAULT '', user_id $fk NULL, checked_on {$s->str(10)} NOT NULL, created_at $dt NOT NULL
        )$e");
        $s->index('idx_equipment_checks', 'equipment_checks', 'equipment_id');
        $s->exec("CREATE TABLE IF NOT EXISTS breathing_status (
            user_id $fk NOT NULL PRIMARY KEY, faction_id $fk NOT NULL DEFAULT 1, g26_until {$s->str(10)} DEFAULT '', last_exercise {$s->str(10)} DEFAULT '',
            last_instruction {$s->str(10)} DEFAULT '', reminded INTEGER NOT NULL DEFAULT 0, note $str DEFAULT '', updated_at $dt NULL
        )$e");
        $s->exec("CREATE TABLE IF NOT EXISTS breathing_log (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, user_id $fk NOT NULL, log_date {$s->str(10)} NOT NULL, kind {$s->str(32)} NOT NULL,
            minutes INTEGER NOT NULL DEFAULT 0, note $str DEFAULT '', recorded_by $fk NULL, created_at $dt NOT NULL
        )$e");
        $s->index('idx_breathing_log', 'breathing_log', 'user_id, log_date');

        // Module ergänzen (Fraktionen mit eigener Auswahl), Rechte für bestehende Ränge
        $new = ['fire' => ['equipment', 'breathing'], 'medical' => ['equipment']];
        $byFaction = [];
        foreach ($s->db->query('SELECT id, kind, modules FROM factions')->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $kind = $f['kind'] ?: 'medical';
            $mods = json_decode((string) $f['modules'], true);
            if (is_array($mods) && isset($new[$kind])) {
                $s->db->prepare('UPDATE factions SET modules = ? WHERE id = ?')->execute([json_encode(array_values(array_unique(array_merge($mods, $new[$kind])))), $f['id']]);
            }
            $byFaction[(int) $f['id']] = $kind;
        }
        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        foreach ($s->db->query('SELECT id, faction_id FROM ranks')->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $kind = $byFaction[(int) $r['faction_id']] ?? '';
            $has = $s->db->query('SELECT permission FROM rank_permissions WHERE rank_id = ' . (int) $r['id'])->fetchAll(PDO::FETCH_COLUMN);
            $grant = [];
            foreach ($new[$kind] ?? [] as $m) {
                if (in_array('documents.view', $has, true)) {
                    $grant[] = "$m.view";
                }
                if (in_array('documents.revoke.own', $has, true)) {
                    $grant[] = "$m.manage";
                }
            }
            if ($kind === 'fire') {
                // neue Feuerwehr-Dokumente: wie Einsatzbericht (Mannschaft) bzw. Brandschutzbescheinigung (Führung)
                if (in_array('documents.issue.fd_einsatz', $has, true)) {
                    array_push($grant, 'documents.issue.fd_atemschutz', 'documents.issue.fd_bsw', 'documents.issue.fd_uebung', 'documents.issue.fd_gefahrgut',
                        'documents.issue.fd_nachschau', 'documents.issue.fd_tueroeffnung', 'documents.issue.fd_schaden');
                }
                if (in_array('documents.issue.fd_bescheinigung', $has, true)) {
                    $grant[] = 'documents.issue.fd_brandursache';
                }
            }
            foreach (array_unique(array_diff($grant, $has)) as $p) {
                $add->execute([$r['id'], $p]);
            }
        }
        // Fachinhalte für bestehende Feuerwehren
        foreach (array_keys(array_filter($byFaction, fn($k) => $k === 'fire')) as $fid) {
            seed_kind_content((int) $fid, 'fire');
        }
    },
];
