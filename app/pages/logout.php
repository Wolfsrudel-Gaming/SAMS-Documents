<?php
defined('SAMS_ROOT') || exit;
if (is_post()) {
    csrf_check();
    audit('logout');
    logout();
}
redirect(url('login'));
