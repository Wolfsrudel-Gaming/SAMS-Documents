<?php
// Dienstzeit, Tablet-API, Drucker
$nfs = (new Client(TEST_BASE))->login('nfs');
check(api('printers', [], 'falsch')[0] === 401, 'API ohne gültigen Schlüssel abgelehnt');
$nfs->get('/index.php?p=dashboard');
$nfs->post('/index.php?p=duty', ['action' => 'toggle', 'return' => 'dashboard']);
check($nfs->has('Im Dienst'), 'Dienst im Browser begonnen');
[$st, $r] = api('tablet_login', ['username' => 'nfs', 'password' => 'TestPasswort123', 'license' => 'license:test', 'player' => 'Nina']);
check(($r['status'] ?? '') === 'ok' && !empty($r['user']['on_duty']), 'Tablet-Login meldet „im Dienst“');
[$st, $r2] = api('duty_set', ['token' => $r['token'], 'on' => false]);
check(($r2['on_duty'] ?? true) === false, 'Ausstempeln aus dem Spiel');
texec("UPDATE duty_sessions SET started_at = ?, ended_at = NULL WHERE id = (SELECT MAX(id) FROM duty_sessions)", [date('Y-m-d H:i:s', time() - 20 * 3600)]);
[$st, $r3] = api('duty_status', ['token' => $r['token']]);
check(($r3['on_duty'] ?? true) === false, 'Vergessene Schicht nach 12 h automatisch beendet');
[$st, $r4] = api('tablet_launch', ['token' => $r['token']]);
check(str_contains($r4['url'] ?? '', 'tablet.php?c='), 'Einmal-Link für das Tablet');
api('tablet_logout', ['token' => $r['token']]);
check(api('tablet_session', ['token' => $r['token']])[0] === 401, 'Abgemeldetes Tablet ungültig');
