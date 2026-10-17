<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Datenbank-Migrationen.
 *
 * Jede Datei in database/migrations/ (z. B. 0003_ranks.php) gibt ein Array
 * ['description' => '…', 'up' => function (Schema $s): void { … }] zurück.
 * Migrationen laufen genau einmal, in Dateinamen-Reihenfolge, und werden in
 * der Tabelle "migrations" vermerkt. Bereits veröffentlichte Migrationen
 * werden nie geändert – Änderungen kommen immer als neue Datei.
 */

final class Schema
{
    public function __construct(public readonly PDO $db, public readonly string $driver)
    {
    }

    public function mysql(): bool
    {
        return $this->driver === 'mysql';
    }

    // Spaltentypen für beide Datenbanken
    public function pk(): string { return $this->mysql() ? 'INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT'; }
    public function fk(): string { return $this->mysql() ? 'INT UNSIGNED' : 'INTEGER'; }
    public function str(int $len = 190): string { return $this->mysql() ? "VARCHAR($len)" : 'TEXT'; }
    public function text(): string { return $this->mysql() ? 'MEDIUMTEXT' : 'TEXT'; }
    public function datetime(): string { return $this->mysql() ? 'DATETIME' : 'TEXT'; }
    public function engine(): string { return $this->mysql() ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' : ''; }

    public function exec(string $sql): void
    {
        $this->db->exec($sql);
    }

    public function hasTable(string $table): bool
    {
        $sql = $this->mysql()
            ? 'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?'
            : "SELECT COUNT(*) FROM sqlite_master WHERE type = 'table' AND name = ?";
        $st = $this->db->prepare($sql);
        $st->execute([$table]);
        return (int) $st->fetchColumn() > 0;
    }

    public function hasColumn(string $table, string $column): bool
    {
        if ($this->mysql()) {
            $st = $this->db->prepare('SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?');
            $st->execute([$table, $column]);
            return (int) $st->fetchColumn() > 0;
        }
        foreach ($this->db->query('PRAGMA table_info(' . $table . ')')->fetchAll() as $col) {
            if ($col['name'] === $column) {
                return true;
            }
        }
        return false;
    }

    /** Spalte ergänzen, falls sie noch nicht existiert. */
    public function addColumn(string $table, string $column, string $definition): void
    {
        if (!$this->hasColumn($table, $column)) {
            $this->exec("ALTER TABLE $table ADD COLUMN $column $definition");
        }
    }

    public function index(string $name, string $table, string $columns, bool $unique = false): void
    {
        $u = $unique ? 'UNIQUE ' : '';
        if ($this->mysql()) {
            $st = $this->db->prepare('SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?');
            $st->execute([$table, $name]);
            if ((int) $st->fetchColumn() === 0) {
                $this->exec("CREATE {$u}INDEX $name ON $table ($columns)");
            }
        } else {
            $this->exec("CREATE {$u}INDEX IF NOT EXISTS $name ON $table ($columns)");
        }
    }
}

function migration_files(): array
{
    $files = glob(SAMS_ROOT . '/database/migrations/*.php') ?: [];
    sort($files, SORT_STRING);
    $out = [];
    foreach ($files as $f) {
        $out[basename($f, '.php')] = $f;
    }
    return $out;
}

function ensure_migrations_table(): void
{
    $s = new Schema(db(), db_driver());
    $s->exec('CREATE TABLE IF NOT EXISTS migrations (
        name ' . $s->str() . ' NOT NULL PRIMARY KEY,
        batch INTEGER NOT NULL,
        ran_at ' . $s->datetime() . ' NOT NULL
    )' . $s->engine());
}

function ran_migrations(): array
{
    try {
        return array_column(all('SELECT name FROM migrations'), 'name');
    } catch (PDOException $e) {
        return [];
    }
}

function pending_migrations(): array
{
    return array_values(array_diff(array_keys(migration_files()), ran_migrations()));
}

/**
 * Offene Migrationen ausführen. Gibt die Namen der ausgeführten Migrationen zurück.
 * $log erhält Statusmeldungen (z. B. für die Konsole).
 */
function run_migrations(?callable $log = null): array
{
    $log ??= fn(string $m) => null;
    $lock = fopen(storage_path('migrate.lock'), 'c');
    if (!$lock || !flock($lock, LOCK_EX)) {
        throw new RuntimeException('Migrations-Sperre konnte nicht gesetzt werden.');
    }
    try {
        ensure_migrations_table();
        $pending = pending_migrations();
        if (!$pending) {
            $log('Datenbank ist aktuell.');
            return [];
        }
        $batch = (int) scalar('SELECT COALESCE(MAX(batch), 0) FROM migrations') + 1;
        $files = migration_files();
        $schema = new Schema(db(), db_driver());
        $done = [];
        foreach ($pending as $name) {
            $migration = require $files[$name];
            $log("→ $name" . (!empty($migration['description']) ? ' – ' . $migration['description'] : ''));
            // SQLite kann Schemaänderungen transaktional ausführen, MySQL nicht
            $tx = db_driver() === 'sqlite';
            if ($tx) {
                db()->beginTransaction();
            }
            try {
                ($migration['up'])($schema);
                insert('migrations', ['name' => $name, 'batch' => $batch, 'ran_at' => now()]);
                if ($tx) {
                    db()->commit();
                }
            } catch (Throwable $e) {
                if ($tx && db()->inTransaction()) {
                    db()->rollBack();
                }
                throw new RuntimeException("Migration $name fehlgeschlagen: " . $e->getMessage(), 0, $e);
            }
            $done[] = $name;
        }
        setting('__reset');
        $log(count($done) . ' Migration(en) ausgeführt.');
        return $done;
    } finally {
        flock($lock, LOCK_UN);
        fclose($lock);
    }
}
