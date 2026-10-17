<?php
/**
 * Etappe 1: frei konfigurierbare Ränge mit Rechten, Abteilungen,
 * Qualifikationen, Login-Schutz, 2FA und Sitzungsverwaltung.
 * Die bisherigen festen Rollen werden in Ränge überführt.
 */
return [
    'description' => 'Ränge, Rechte, Abteilungen, Qualifikationen, Login-Schutz und 2FA',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $engine = $s->engine();
        $now = date('Y-m-d H:i:s');

        $s->exec("CREATE TABLE IF NOT EXISTS ranks (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            name $str NOT NULL,
            short $str DEFAULT '',
            level INTEGER NOT NULL DEFAULT 10,
            color {$s->str(16)} DEFAULT '#64717f',
            description $text,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS rank_permissions (
            rank_id $fk NOT NULL,
            permission {$s->str(100)} NOT NULL,
            PRIMARY KEY (rank_id, permission)
        ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS departments (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            name $str NOT NULL,
            short $str DEFAULT '',
            color {$s->str(16)} DEFAULT '#0b4f9c',
            description $text,
            sort INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS qualifications (
            id $pk,
            faction_id $fk NOT NULL DEFAULT 1,
            name $str NOT NULL,
            short $str DEFAULT '',
            color {$s->str(16)} DEFAULT '#2e7d4f',
            description $text,
            sort INTEGER NOT NULL DEFAULT 0,
            active INTEGER NOT NULL DEFAULT 1,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS user_departments (
            user_id $fk NOT NULL,
            department_id $fk NOT NULL,
            PRIMARY KEY (user_id, department_id)
        ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS user_qualifications (
            user_id $fk NOT NULL,
            qualification_id $fk NOT NULL,
            PRIMARY KEY (user_id, qualification_id)
        ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id $pk,
            username $str NOT NULL,
            ip {$s->str(64)} NOT NULL,
            success INTEGER NOT NULL DEFAULT 0,
            created_at $dt NOT NULL
        ){$engine}");
        $s->index('idx_login_attempts_user', 'login_attempts', 'username, created_at');
        $s->index('idx_login_attempts_ip', 'login_attempts', 'ip, created_at');

        $s->addColumn('users', 'rank_id', "$fk NULL");
        $s->addColumn('users', 'is_superadmin', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('users', 'must_change_password', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('users', 'session_version', 'INTEGER NOT NULL DEFAULT 1');
        $s->addColumn('users', 'totp_secret', "$str DEFAULT ''");
        $s->addColumn('users', 'totp_enabled', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('users', 'totp_last_step', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('users', 'recovery_codes', "$text");
        $s->addColumn('users', 'discord_id', "$str DEFAULT ''");
        $s->addColumn('users', 'deactivated_reason', "$str DEFAULT ''");
        $s->addColumn('users', 'deactivated_at', "$dt NULL");
        $s->addColumn('users', 'password_changed_at', "$dt NULL");
        $s->addColumn('users', 'created_by', "$fk NULL");
        $s->index('idx_users_rank', 'users', 'rank_id');

        $db = $s->db;
        if ((int) $db->query('SELECT COUNT(*) FROM ranks')->fetchColumn() > 0) {
            return;
        }

        // Rechte-Vorlagen (Katalog siehe app/permissions.php)
        $medicDocs = ['einsatzprotokoll', 'revers', 'rechnung', 'schreiben'];
        $doctorDocs = ['au', 'attest', 'rezept', 'ueberweisung', 'arztbrief', 'verletzung', 'blutentnahme', 'tauglichkeit', 'todesbescheinigung'];
        $issue = fn(array $types) => array_map(fn($t) => 'documents.issue.' . $t, $types);
        $base = ['patients.view', 'patients.edit', 'entries.create', 'documents.view'];
        $medic = array_merge($base, $issue($medicDocs), ['documents.revoke.own']);
        $doctor = array_merge($medic, $issue($doctorDocs), ['entries.confidential', 'entries.edit_all']);
        $lead = array_merge($doctor, ['patients.delete', 'documents.revoke.all', 'users.view', 'users.create', 'users.edit',
            'users.password_reset', 'users.deactivate', 'users.sessions', 'users.2fa_reset', 'ranks.manage',
            'departments.manage', 'qualifications.manage', 'audit.view', 'security.2fa_required']);
        $admin = array_merge($lead, ['settings.manage', 'system.manage']);

        $template = [
            // Name, Kürzel, Stufe, Farbe, Rechte, alte Rolle
            ['Administrator', 'ADM', 100, '#14202c', $admin, 'admin'],
            ['Ärztliche Direktion / Chief', 'CHF', 90, '#7a1f2b', $lead, null],
            ['Oberarzt / Oberärztin', 'OA', 70, '#0b4f9c', $doctor, null],
            ['Arzt / Ärztin', 'A', 50, '#1f6fb2', $doctor, 'doctor'],
            ['Notfallsanitäter/in', 'NFS', 30, '#c8102e', $medic, 'medic'],
            ['Rettungssanitäter/in', 'RS', 20, '#d9822b', $medic, null],
            ['Auszubildende/r', 'AZB', 10, '#64717f', $base, 'trainee'],
        ];
        $insRank = $db->prepare('INSERT INTO ranks (name, short, level, color, description, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insPerm = $db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $roleMap = [];
        foreach ($template as [$name, $short, $level, $color, $perms, $role]) {
            $insRank->execute([$name, $short, $level, $color, '', $now, $now]);
            $id = (int) $db->lastInsertId();
            foreach (array_unique($perms) as $p) {
                $insPerm->execute([$id, $p]);
            }
            if ($role) {
                $roleMap[$role] = $id;
            }
        }

        // Bestehende Konten den neuen Rängen zuordnen
        $upd = $db->prepare('UPDATE users SET rank_id = ? WHERE role = ? AND rank_id IS NULL');
        foreach ($roleMap as $role => $rankId) {
            $upd->execute([$rankId, $role]);
        }
        $db->exec('UPDATE users SET rank_id = ' . (int) $roleMap['trainee'] . ' WHERE rank_id IS NULL');
        // Das älteste Admin-Konto wird Hauptadministrator (kann nicht gesperrt oder herabgestuft werden)
        $first = $db->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1")->fetchColumn();
        if ($first) {
            $db->exec('UPDATE users SET is_superadmin = 1 WHERE id = ' . (int) $first);
        }

        // Abteilungen aus der Fragerunde (frei änderbar)
        $insDep = $db->prepare('INSERT INTO departments (name, short, color, description, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ([
            ['Rettungsdienst / Notarzt', 'RD', '#c8102e', 'Bodengebundener Rettungsdienst und Notarztdienst'],
            ['Klinik / Chirurgie', 'KLI', '#0b4f9c', 'Notaufnahme, Station, OP'],
            ['Psychologie / Psychiatrie', 'PSY', '#6c3483', 'Psychologische Gespräche und Gutachten'],
            ['Luft- und Wasserrettung', 'LWR', '#1e8449', 'Helikopter-, Berg- und Wasserrettung'],
        ] as $i => [$name, $short, $color, $desc]) {
            $insDep->execute([$name, $short, $color, $desc, ($i + 1) * 10, $now, $now]);
        }

        $insQ = $db->prepare('INSERT INTO qualifications (name, short, color, description, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
        foreach ([
            ['Notarzt', 'NA', 'Darf als Notarzt eingesetzt werden'],
            ['Leitender Notarzt', 'LNA', 'Einsatzleitung bei Großschadenslagen'],
            ['Pilot/in Rettungshubschrauber', 'PIL', 'Fliegen des Rettungshubschraubers'],
            ['Ausbilder/in', 'AUS', 'Darf ausbilden und Prüfungen abnehmen'],
            ['Psychologische Gutachten', 'PSYG', 'Darf psychologische Gutachten erstellen'],
        ] as $i => [$name, $short, $desc]) {
            $insQ->execute([$name, $short, '#2e7d4f', $desc, ($i + 1) * 10, $now, $now]);
        }
    },
];
