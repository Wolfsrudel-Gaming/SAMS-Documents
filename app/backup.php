<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Datenbank-Backups ohne externe Programme (kein mysqldump nötig).
 * Dateien liegen in storage/backups/ und werden nach BACKUP_KEEP_DAYS gelöscht.
 */

function backup_dir(): string
{
    return storage_path('backups', true);
}

function create_backup(string $tag = ''): string
{
    $name = 'sams-' . date('Ymd-His') . ($tag !== '' ? '-' . preg_replace('/[^a-z0-9-]/i', '', $tag) : '');
    if (db_driver() === 'sqlite') {
        $tmp = backup_dir() . '/' . $name . '.sqlite';
        db()->exec('VACUUM INTO ' . db()->quote($tmp));
        $file = $tmp . '.gz';
        file_put_contents($file, gzencode((string) file_get_contents($tmp), 6));
        unlink($tmp);
    } else {
        $file = backup_dir() . '/' . $name . '.sql.gz';
        $gz = gzopen($file, 'wb6');
        gzwrite($gz, "-- SAMS Backup " . date('c') . "\nSET FOREIGN_KEY_CHECKS=0;\n");
        foreach (db()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $table) {
            $create = db()->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM)[1];
            gzwrite($gz, "DROP TABLE IF EXISTS `$table`;\n" . str_replace("\n", ' ', $create) . ";\n");
            $st = db()->query("SELECT * FROM `$table`", PDO::FETCH_NUM);
            $rows = [];
            while ($row = $st->fetch()) {
                $rows[] = '(' . implode(',', array_map(fn($v) => $v === null ? 'NULL' : db()->quote((string) $v), $row)) . ')';
                if (count($rows) >= 200) {
                    gzwrite($gz, "INSERT INTO `$table` VALUES " . implode(',', $rows) . ";\n");
                    $rows = [];
                }
            }
            if ($rows) {
                gzwrite($gz, "INSERT INTO `$table` VALUES " . implode(',', $rows) . ";\n");
            }
        }
        gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        gzclose($gz);
    }
    prune_backups();
    return $file;
}

function restore_backup(string $file): void
{
    if (!is_file($file)) {
        $file = backup_dir() . '/' . basename($file);
    }
    if (!is_file($file)) {
        throw new RuntimeException('Backup nicht gefunden: ' . $file);
    }
    if (str_ends_with($file, '.sqlite.gz')) {
        if (db_driver() !== 'sqlite') {
            throw new RuntimeException('SQLite-Backup kann nicht in MySQL eingespielt werden.');
        }
        $target = cfg('db.sqlite');
        file_put_contents($target . '.restore', gzdecode((string) file_get_contents($file)));
        rename($target . '.restore', $target);
        return;
    }
    if (db_driver() !== 'mysql') {
        throw new RuntimeException('SQL-Backup kann nur in MySQL eingespielt werden.');
    }
    $gz = gzopen($file, 'rb');
    $statement = '';
    while (($line = gzgets($gz)) !== false) {
        if ($statement === '' && str_starts_with($line, '--')) {
            continue;
        }
        $statement .= $line;
        if (str_ends_with(rtrim($line), ';')) {
            db()->exec($statement);
            $statement = '';
        }
    }
    gzclose($gz);
}

function list_backups(): array
{
    $files = glob(backup_dir() . '/sams-*.gz') ?: [];
    rsort($files);
    return array_map(fn($f) => ['file' => basename($f), 'size' => filesize($f), 'time' => filemtime($f)], $files);
}

function prune_backups(): int
{
    $keep = max(1, (int) cfg('backup_keep_days', 14));
    $limit = time() - $keep * 86400;
    $deleted = 0;
    foreach (glob(backup_dir() . '/sams-*.gz') ?: [] as $f) {
        if (filemtime($f) < $limit) {
            unlink($f);
            $deleted++;
        }
    }
    return $deleted;
}
