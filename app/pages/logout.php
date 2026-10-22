<?php
defined('SAMS_ROOT') || exit;
if (is_post()) {
    csrf_check();
    $tabletId = (int) ($_SESSION['tablet'] ?? 0);
    audit('logout');
    if ($tabletId) {
        // Abmelden im Tablet beendet auch die Tablet-Anmeldung im Spiel
        q('UPDATE tablet_tokens SET revoked = 1 WHERE id = ?', [$tabletId]);
    }
    logout();
    if ($tabletId) {
        echo '<!doctype html><meta charset="utf-8"><script>window.parent.postMessage({sams: "logout"}, "*");</script>';
        exit;
    }
}
redirect(url('login'));
