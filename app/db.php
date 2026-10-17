<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $c = cfg('db');
    if (($c['driver'] ?? 'sqlite') === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $c['host'],
            (int) ($c['port'] ?? 3306),
            $c['database'],
            $c['charset'] ?? 'utf8mb4'
        );
        $pdo = new PDO($dsn, $c['username'], $c['password']);
    } else {
        $file = $c['sqlite'] ?? (SAMS_ROOT . '/data/sams.sqlite');
        $pdo = new PDO('sqlite:' . $file);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

function db_driver(): string
{
    return cfg('db.driver', 'sqlite') === 'mysql' ? 'mysql' : 'sqlite';
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function scalar(string $sql, array $params = [])
{
    $v = q($sql, $params)->fetchColumn();
    return $v === false ? null : $v;
}

function insert(string $table, array $data): int
{
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(', ', $cols),
        implode(', ', array_map(fn($c) => ':' . $c, $cols))
    );
    q($sql, $data);
    return (int) db()->lastInsertId();
}

function update(string $table, int $id, array $data): void
{
    $set = implode(', ', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $data['__id'] = $id;
    q("UPDATE $table SET $set WHERE id = :__id", $data);
}

/** Schreibweise für case-insensitive Suche in beiden Datenbanken. */
function like(string $term): string
{
    return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $term) . '%';
}

function setting(string $key, $default = '')
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (all('SELECT name, value FROM settings') as $row) {
                $cache[$row['name']] = $row['value'];
            }
        } catch (PDOException $e) {
            // Datenbank noch nicht installiert
        }
    }
    if ($key === '__reset') {
        $cache = null;
        return null;
    }
    return ($cache[$key] ?? '') !== '' ? $cache[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    if (one('SELECT name FROM settings WHERE name = ?', [$key])) {
        q('UPDATE settings SET value = ? WHERE name = ?', [$value, $key]);
    } else {
        q('INSERT INTO settings (name, value) VALUES (?, ?)', [$key, $value]);
    }
}

function audit(string $action, string $objectType = '', ?int $objectId = null, string $details = ''): void
{
    try {
        insert('audit_log', [
            'user_id'     => current_user()['id'] ?? null,
            'action'      => $action,
            'object_type' => $objectType,
            'object_id'   => $objectId,
            'details'     => mb_substr($details, 0, 1000),
            'ip'          => $_SERVER['REMOTE_ADDR'] ?? '',
            'created_at'  => now(),
        ]);
    } catch (PDOException $e) {
        // Protokollierung darf den Betrieb nicht verhindern
    }
}

function schema_sql(): array
{
    $mysql = db_driver() === 'mysql';
    $pk = $mysql ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $fk = $mysql ? 'INT UNSIGNED' : 'INTEGER';
    $str = $mysql ? 'VARCHAR(190)' : 'TEXT';
    $text = $mysql ? 'MEDIUMTEXT' : 'TEXT';
    $dt = $mysql ? 'DATETIME' : 'TEXT';
    $engine = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : '';

    return [
        "CREATE TABLE IF NOT EXISTS settings (
            name $str NOT NULL PRIMARY KEY,
            value $text
        )$engine",
        "CREATE TABLE IF NOT EXISTS users (
            id $pk,
            username $str NOT NULL UNIQUE,
            password_hash $str NOT NULL,
            name $str NOT NULL,
            title $str DEFAULT '',
            role $str NOT NULL DEFAULT 'medic',
            rank_name $str DEFAULT '',
            department $str DEFAULT '',
            badge_number $str DEFAULT '',
            doctor_number $str DEFAULT '',
            phone $str DEFAULT '',
            signature $text,
            active INTEGER NOT NULL DEFAULT 1,
            last_login $dt NULL,
            created_at $dt NOT NULL
        )$engine",
        "CREATE TABLE IF NOT EXISTS patients (
            id $pk,
            citizen_id $str DEFAULT '',
            first_name $str NOT NULL,
            last_name $str NOT NULL,
            dob $str DEFAULT '',
            gender $str DEFAULT '',
            phone $str DEFAULT '',
            address $text,
            occupation $str DEFAULT '',
            employer $str DEFAULT '',
            insurance $str DEFAULT '',
            insurance_number $str DEFAULT '',
            blood_type $str DEFAULT '',
            height $str DEFAULT '',
            weight $str DEFAULT '',
            allergies $text,
            conditions $text,
            medication $text,
            emergency_contact $text,
            organ_donor $str DEFAULT '',
            living_will INTEGER NOT NULL DEFAULT 0,
            flags $text,
            notes $text,
            deceased_at $str DEFAULT '',
            created_by $fk NULL,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL
        )$engine",
        "CREATE TABLE IF NOT EXISTS entries (
            id $pk,
            patient_id $fk NOT NULL,
            type $str NOT NULL,
            title $str NOT NULL,
            content $text,
            confidential INTEGER NOT NULL DEFAULT 0,
            occurred_at $dt NOT NULL,
            created_by $fk NULL,
            created_at $dt NOT NULL,
            updated_at $dt NOT NULL,
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
        )$engine",
        "CREATE TABLE IF NOT EXISTS documents (
            id $pk,
            number $str NOT NULL DEFAULT '',
            type $str NOT NULL,
            patient_id $fk NULL,
            data $text,
            patient_snapshot $text,
            issuer_snapshot $text,
            verify_code $str NOT NULL UNIQUE,
            status $str NOT NULL DEFAULT 'valid',
            revoked_reason $text,
            revoked_by $fk NULL,
            revoked_at $dt NULL,
            issued_by $fk NULL,
            issued_at $dt NOT NULL,
            valid_until $str DEFAULT '',
            FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL
        )$engine",
        "CREATE TABLE IF NOT EXISTS audit_log (
            id $pk,
            user_id $fk NULL,
            action $str NOT NULL,
            object_type $str DEFAULT '',
            object_id $fk NULL,
            details $text,
            ip $str DEFAULT '',
            created_at $dt NOT NULL
        )$engine",
        "CREATE INDEX IF NOT EXISTS idx_patients_name ON patients (last_name, first_name)",
        "CREATE INDEX IF NOT EXISTS idx_entries_patient ON entries (patient_id)",
        "CREATE INDEX IF NOT EXISTS idx_documents_patient ON documents (patient_id)",
        "CREATE INDEX IF NOT EXISTS idx_documents_number ON documents (number)",
        "CREATE INDEX IF NOT EXISTS idx_audit_created ON audit_log (created_at)",
    ];
}

function install_schema(): void
{
    foreach (schema_sql() as $sql) {
        if (db_driver() === 'mysql' && str_starts_with($sql, 'CREATE INDEX')) {
            // MySQL kennt kein "IF NOT EXISTS" bei Indizes
            $sql = str_replace('IF NOT EXISTS ', '', $sql);
            try {
                db()->exec($sql);
            } catch (PDOException $e) {
                // Index existiert bereits
            }
            continue;
        }
        db()->exec($sql);
    }
}
