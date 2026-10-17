<?php
// Inaktivitäts-Abmeldung (opt-in)
$c = (new Client(TEST_BASE))->login('arzt');
check(!$c->get('/index.php?p=dashboard')->has('data-idle='), 'Inaktivitäts-Abmeldung standardmäßig aus');
texec("INSERT INTO settings (name, value) VALUES ('session_idle_minutes', '30')");
check($c->get('/index.php?p=dashboard')->has('data-idle="30"'), 'Eingeschaltet: Seite kennt die Frist');
check($c->get('/index.php?p=ping')->status === 204, 'Lebenszeichen');
// Sitzung altern lassen: letzte Aktivität in der Sitzungsdatei 31 Minuten zurücksetzen
$file = rtrim(ini_get('session.save_path') ?: sys_get_temp_dir(), '/') . '/sess_' . $c->sessionId();
if (is_file($file)) {
    file_put_contents($file, preg_replace('/last_activity\|i:\d+;/', 'last_activity|i:' . (time() - 31 * 60) . ';', (string) file_get_contents($file)));
    $c->get('/index.php?p=dashboard');
    check(str_contains($c->url, 'p=login') && $c->has('automatisch abgemeldet'), 'Nach 31 Minuten ohne Aktivität abgemeldet');
} else {
    check(false, 'Sitzungsdatei nicht gefunden: ' . $file);
}
texec("DELETE FROM settings WHERE name = 'session_idle_minutes'");

// Ratenbremse auf der Echtheitsprüfung (60 Abfragen pro Minute und IP)
texec('DELETE FROM rate_hits');
$v = new Client(TEST_BASE);
for ($i = 0; $i < 60; $i++) {
    $v->get('/verify.php?c=ABCD' . sprintf('%04d', $i));
}
check($v->status === 200, '60 Prüfungen pro Minute erlaubt');
check($v->get('/verify.php?c=ZZZZ9999')->status === 429, 'Die 61. Abfrage wird gebremst (429)');
check($v->get('/verify.php')->status === 200, 'Seite ohne Code bleibt aufrufbar');
texec('DELETE FROM rate_hits');
