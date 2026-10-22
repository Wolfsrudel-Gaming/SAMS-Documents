<?php
// Dienstplan: anlegen, eintragen, Höchstbesetzung, Tausch, Sperrfrist, Erinnerung, Anwesenheit
$chief = (new Client(TEST_BASE))->login('chief');
$nfs = (new Client(TEST_BASE))->login('nfs');
$azubi = (new Client(TEST_BASE))->login('azubi');
$nfsId = (int) tval("SELECT id FROM users WHERE username = 'nfs'");
$azubiId = (int) tval("SELECT id FROM users WHERE username = 'azubi'");
$day = date('Y-m-d', strtotime('+3 days'));
$chief->get('/index.php?p=roster&week=' . $day);
$chief->post('/index.php?p=roster', ['action' => 'create', 'title' => 'Spätdienst RD', 'date' => $day, 'from' => '18:00', 'to' => '02:00', 'min_staff' => '2', 'max_staff' => '1', 'department_id' => '1']);
$shift = trow("SELECT * FROM roster_shifts WHERE title = 'Spätdienst RD'");
check($shift && $shift['ends_at'] === date('Y-m-d', strtotime("$day +1 day")) . ' 02:00:00', 'Nachtschicht endet am Folgetag');
$nfs->get('/index.php?p=roster&week=' . $day);
check($nfs->has('Spätdienst RD') && $nfs->has('Eintragen'), 'Schicht im Plan sichtbar');
$nfs->post('/index.php?p=roster', ['action' => 'signup', 'shift_id' => $shift['id'], 'week' => $day]);
check((bool) tval('SELECT id FROM roster_slots WHERE shift_id = ? AND user_id = ?', [$shift['id'], $nfsId]), 'Selbst eingetragen');
$azubi->get('/index.php?p=roster&week=' . $day);
$azubi->post('/index.php?p=roster', ['action' => 'signup', 'shift_id' => $shift['id'], 'week' => $day]);
check(!tval('SELECT id FROM roster_slots WHERE user_id = ?', [$azubiId]), 'Höchstbesetzung wird eingehalten');
// Tausch
$nfs->get('/index.php?p=roster&week=' . $day);
$nfs->post('/index.php?p=roster', ['action' => 'offer', 'shift_id' => $shift['id'], 'week' => $day]);
check((bool) tval("SELECT id FROM notifications WHERE user_id = ? AND title LIKE 'Schicht zur Übernahme%'", [$azubiId]), 'Kollegen werden über das Tauschangebot informiert');
$slot = (int) tval('SELECT id FROM roster_slots WHERE shift_id = ?', [$shift['id']]);
$azubi->get('/index.php?p=roster&week=' . $day);
$azubi->post('/index.php?p=roster', ['action' => 'take', 'shift_id' => $shift['id'], 'slot_id' => $slot, 'week' => $day]);
check((int) tval('SELECT user_id FROM roster_slots WHERE id = ?', [$slot]) === $azubiId && (bool) tval("SELECT id FROM notifications WHERE user_id = ? AND title LIKE '%hat deine Schicht übernommen'", [$nfsId]), 'Schicht übernommen, Anbieter benachrichtigt');
// Sperrfrist: kurz vor Beginn nur Tausch
texec('UPDATE roster_shifts SET starts_at = ?, ends_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() + 1800), date('Y-m-d H:i:s', time() + 7200), $shift['id']]);
$azubi->get('/index.php?p=roster');
$azubi->post('/index.php?p=roster', ['action' => 'leave', 'shift_id' => $shift['id']]);
check((bool) tval('SELECT id FROM roster_slots WHERE id = ?', [$slot]) && $azubi->has('zum Tausch an'), 'Austragen kurz vor Beginn gesperrt');
// Erinnerung + Unterbesetzung über den Cron
exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/sams') . ' cron 2>&1', $o);
check((bool) tval("SELECT id FROM notifications WHERE user_id = ? AND title LIKE 'Deine Schicht beginnt%'", [$azubiId]), 'Erinnerung vor Schichtbeginn');
check((bool) tval("SELECT id FROM notifications WHERE title LIKE 'Unterbesetzt:%'"), 'Planung erhält Unterbesetzungs-Warnung');
// Anwesenheit aus der Dienstzeit
texec('UPDATE roster_shifts SET starts_at = ?, ends_at = ? WHERE id = ?', [date('Y-m-d H:i:s', time() - 3600), date('Y-m-d H:i:s', time() + 3600), $shift['id']]);
texec("INSERT INTO duty_sessions (user_id, started_at, ended_at, source, created_at) VALUES (?, ?, ?, 'web', ?)", [$azubiId, date('Y-m-d H:i:s', time() - 1800), date('Y-m-d H:i:s', time() - 600), date('Y-m-d H:i:s')]);
$chief->get('/index.php?p=roster');
check($chief->has('✓ 0:20 h') && $chief->has('läuft'), 'Gestempelte Zeit in der Schicht sichtbar');
// Vorwoche übernehmen
$next = date('Y-m-d', strtotime(week_start_test() . ' +7 days'));
$chief->get('/index.php?p=roster&week=' . $next);
$chief->post('/index.php?p=roster', ['action' => 'copy_week', 'week' => $next]);
check((int) tval('SELECT COUNT(*) FROM roster_shifts WHERE starts_at >= ?', [$next]) >= 1, 'Schichten der Vorwoche übernommen');
check($azubi->post('/index.php?p=roster', ['action' => 'create', 'date' => $day, 'from' => '10:00', 'to' => '12:00'])->status === 403, 'Planung nur mit Recht');

function week_start_test(): string
{
    $t = time();
    return date('Y-m-d', strtotime('-' . ((int) date('N', $t) - 1) . ' days', $t));
}
