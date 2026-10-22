<?php
defined('SAMS_ROOT') || exit;
/**
 * Anmeldung mit Discord für Mitarbeitende. Ohne Anmeldung: Login über die
 * hinterlegte Discord-ID (2FA bleibt Pflicht, wenn aktiviert). Angemeldet mit
 * ?link=1: eigenes Discord-Konto verknüpfen.
 */
if (!discord_configured()) {
    flash('error', 'Die Discord-Anmeldung ist nicht eingerichtet.');
    redirect(url(current_user() ? 'profile' : 'login'));
}
if (!isset($_GET['code'])) {
    $_SESSION['discord_link'] = current_user() && isset($_GET['link']);
    redirect(discord_authorize_url(discord_staff_redirect()));
}
$profile = discord_callback(discord_staff_redirect());
$linkMode = !empty($_SESSION['discord_link']);
unset($_SESSION['discord_link']);
if (!$profile) {
    flash('error', 'Die Anmeldung mit Discord ist fehlgeschlagen.');
    redirect(url(current_user() ? 'profile' : 'login'));
}
$discordId = (string) $profile['id'];

if ($linkMode && ($me = current_user())) {
    $other = one("SELECT id FROM users WHERE discord_id = ? AND id <> ?", [$discordId, $me['id']]);
    if ($other) {
        flash('error', 'Dieses Discord-Konto ist bereits mit einem anderen Benutzer verknüpft.');
    } else {
        update('users', (int) $me['id'], ['discord_id' => $discordId]);
        audit('discord.link', 'user', (int) $me['id'], (string) ($profile['username'] ?? ''));
        $change = discord_sync_rank(one('SELECT * FROM users WHERE id = ?', [$me['id']]));
        flash('success', 'Discord-Konto verknüpft. Du kannst dich jetzt mit Discord anmelden.' . ($change !== '' ? ' Dein Rang wurde an deine Discord-Rollen angepasst.' : ''));
    }
    redirect(url('profile'));
}

$u = one("SELECT * FROM users WHERE discord_id = ? AND discord_id <> ''", [$discordId]);
if (!$u) {
    flash('error', 'Mit diesem Discord-Konto ist kein Zugang verknüpft. Melde dich mit Benutzername und Passwort an und verknüpfe Discord in deinem Profil.');
    redirect(url('login'));
}
if (!(int) $u['active']) {
    flash('error', 'Dieses Konto ist gesperrt.');
    redirect(url('login'));
}
discord_sync_rank($u);
if (!empty($u['totp_enabled'])) {
    $_SESSION['pending_2fa'] = ['uid' => (int) $u['id'], 'at' => time()];
    redirect(url('login'));
}
record_login_attempt($u['username'], true);
complete_login($u);
audit('login', 'user', (int) $u['id'], 'Discord');
redirect(url('dashboard'));
