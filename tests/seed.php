<?php
/**
 * Testdaten: Konten für jede Rolle, Abteilungen, eine Patientenakte.
 * Wird vom Testlauf mit den Umgebungsvariablen der Testdatenbank ausgeführt.
 */
declare(strict_types=1);
define('SAMS_IGNORE_MAINTENANCE', true);
require dirname(__DIR__) . '/app/bootstrap.php';

$now = now();
$hash = password_hash('TestPasswort123', PASSWORD_DEFAULT);
$rank = fn(string $name) => (int) scalar('SELECT id FROM ranks WHERE name LIKE ?', [$name . '%']);
// Ohne 2FA-Pflicht testen (2FA wird separat geprüft)
q("DELETE FROM rank_permissions WHERE permission = 'security.2fa_required'");
$ext = insert('ranks', ['name' => 'LSPD (extern)', 'short' => 'LSPD', 'level' => 1, 'created_at' => $now, 'updated_at' => $now]);
insert('rank_permissions', ['rank_id' => $ext, 'permission' => 'documents.shared_view']);

$users = [
    'admin' => ['Ada Admin', $rank('Administrator'), 1, [1, 2]],
    'chief' => ['Chris Chief', $rank('Ärztliche Direktion'), 0, [1, 2]],
    'oa'    => ['Olga Oberarzt', $rank('Oberarzt'), 0, [2]],
    'arzt'  => ['Arne Arzt', $rank('Arzt'), 0, [2]],
    'nfs'   => ['Nina Notfall', $rank('Notfallsanit'), 0, [1]],
    'azubi' => ['Alex Azubi', $rank('Auszubild'), 0, [1]],
    'lspd'  => ['Det. Lee Police', $ext, 0, []],
];
foreach ($users as $username => [$name, $rankId, $super, $deps]) {
    $id = insert('users', ['username' => $username, 'name' => $name, 'password_hash' => $hash, 'rank_id' => $rankId ?: null,
        'is_superadmin' => $super, 'active' => 1, 'must_change_password' => 0, 'badge_number' => 'T-' . strtoupper($username), 'created_at' => $now]);
    foreach ($deps as $d) {
        insert('user_departments', ['user_id' => $id, 'department_id' => $d]);
    }
}
insert('patients', ['first_name' => 'Tom', 'last_name' => 'Tester', 'dob' => '1990-02-03', 'gender' => 'm', 'citizen_id' => 'TST001',
    'address' => "Grove Street 1\nLos Santos", 'created_at' => $now, 'updated_at' => $now]);
echo "Testdaten angelegt.\n";
