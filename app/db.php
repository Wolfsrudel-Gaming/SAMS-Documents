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
        $file = $c['sqlite'] ?? (SAMS_ROOT . '/storage/sams.sqlite');
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
            'ip'          => function_exists('client_ip') ? client_ip() : ($_SERVER['REMOTE_ADDR'] ?? ''),
            'created_at'  => now(),
        ]);
    } catch (PDOException $e) {
        // Protokollierung darf den Betrieb nicht verhindern
    }
}
