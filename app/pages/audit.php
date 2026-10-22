<?php
defined('SAMS_ROOT') || exit;
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 50;
$userFilter = (int) ($_GET['user'] ?? 0);
$where = 'WHERE ' . fw('a') . ($userFilter ? ' AND a.user_id = ' . $userFilter : '');
$total = (int) scalar("SELECT COUNT(*) FROM audit_log a $where");
$rows = all("SELECT a.*, u.name FROM audit_log a LEFT JOIN users u ON u.id = a.user_id $where ORDER BY a.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
$pages = max(1, (int) ceil($total / $per));
$labels = [
    'login' => 'Anmeldung', 'logout' => 'Abmeldung', 'patient.view' => 'Akte geöffnet', 'patient.create' => 'Akte angelegt',
    'patient.update' => 'Akte geändert', 'patient.delete' => 'Akte gelöscht', 'entry.create' => 'Eintrag erstellt', 'entry.update' => 'Eintrag geändert',
    'entry.delete' => 'Eintrag gelöscht', 'document.issue' => 'Dokument ausgestellt', 'document.view' => 'Dokument angesehen',
    'document.revoke' => 'Dokument storniert', 'user.create' => 'Konto angelegt', 'user.update' => 'Konto geändert',
    'user.signature' => 'Unterschrift geändert', 'user.password' => 'Passwort geändert', 'settings.update' => 'Einstellungen geändert',
    'patient.print' => 'Akte gedruckt', 'patient.archive' => 'Akte archiviert', 'patient.restore' => 'Akte reaktiviert',
    'draft.create' => 'Entwurf angelegt', 'draft.update' => 'Entwurf gespeichert', 'draft.delete' => 'Entwurf verworfen', 'draft.handover' => 'Entwurf übergeben',
    'draft.submit' => 'Zur Freigabe eingereicht', 'draft.approve' => 'Freigegeben', 'draft.reject' => 'Freigabe abgelehnt', 'draft.withdraw' => 'Einreichung zurückgezogen',
    'document.addendum' => 'Nachtrag geschrieben', 'invoice.status' => 'Rechnungsstatus geändert',
    'document.share' => 'Dokument freigegeben', 'document.share_revoke' => 'Freigabe widerrufen', 'document.share_view' => 'Freigabe abgerufen',
    'prices.create' => 'Leistung angelegt', 'prices.update' => 'Leistung geändert', 'prices.deactivate' => 'Leistung deaktiviert', 'settings.doc_types' => 'Dokumentarten geändert', 'templates.create' => 'Vorlage angelegt', 'duty.on' => 'Dienst begonnen', 'personnel.add' => 'Personalakte: Eintrag', 'training.start' => 'Prüfung begonnen', 'wiki.create' => 'Wiki-Seite angelegt', 'board.create' => 'Aushang veröffentlicht', 'portal.link' => 'Bürgerportal verknüpft', 'attachment.upload' => 'Anhang hochgeladen', 'patient.merge' => 'Akten zusammengeführt', 'attachment.view' => 'Anhang geöffnet', 'attachment.delete' => 'Anhang gelöscht', 'discord.link' => 'Discord verknüpft', 'discord.rank_sync' => 'Rang per Discord-Rolle angepasst', 'discord.settings' => 'Discord-Einstellungen geändert', 'portal.appointment' => 'Terminanfrage bearbeitet', 'portal.request' => 'Antrag bearbeitet', 'portal.application' => 'Bewerbung bearbeitet', 'portal.citizen' => 'Bürgerkonto geändert', 'board.update' => 'Aushang geändert', 'board.ack' => 'Aushang bestätigt', 'messages.send' => 'Nachricht gesendet', 'wiki.update' => 'Wiki-Seite geändert', 'wiki.delete' => 'Wiki-Seite gelöscht', 'lists.update' => 'Vorschlagsliste geändert', 'training.finish' => 'Prüfung abgegeben', 'training.create' => 'Lehrgang angelegt', 'training.update' => 'Lehrgang geändert', 'personnel.delete' => 'Personalakte: Eintrag entfernt', 'duty.off' => 'Dienst beendet', 'duty.correct' => 'Dienstzeit korrigiert', 'roster.signup' => 'Dienstplan: eingetragen', 'roster.leave' => 'Dienstplan: ausgetragen', 'roster.offer' => 'Dienstplan: Tausch angeboten', 'roster.take' => 'Dienstplan: Schicht übernommen', 'roster.create' => 'Dienstplan: Schicht angelegt', 'roster.edit' => 'Dienstplan: Schicht geändert', 'roster.delete' => 'Dienstplan: Schicht gelöscht', 'roster.assign' => 'Dienstplan: eingeteilt', 'roster.remove' => 'Dienstplan: ausgetragen (Leitung)', 'roster.copy' => 'Dienstplan: Vorwoche übernommen', 'stock.article' => 'Medikament: Artikel', 'stock.in' => 'Medikament: Zugang', 'stock.out' => 'Medikament: Entnahme', 'stock.loss' => 'Medikament: Verwurf', 'stock.correct' => 'Medikament: Korrektur', 'btm.check' => 'BtM-Bestand geprüft', 'rx.redeem' => 'Rezept eingelöst', 'location.create' => 'Standort angelegt', 'faction.create' => 'Fraktion angelegt', 'wanted.create' => 'Zur Fahndung ausgeschrieben', 'wanted.close' => 'Fahndung erledigt', 'request.create' => 'Behördenanfrage gestellt', 'request.answer' => 'Behördenanfrage beantwortet', 'request.decline' => 'Behördenanfrage abgelehnt', 'request.progress' => 'Behördenanfrage in Bearbeitung', 'faction.update' => 'Fraktion geändert', 'location.update' => 'Standort geändert', 'templates.update' => 'Vorlage geändert',
    'case.create' => 'Fall angelegt', 'case.update' => 'Fall geändert', 'case.view' => 'Fall geöffnet', 'case.close' => 'Fall abgeschlossen', 'case.reopen' => 'Fall wieder geöffnet', 'user.password_reset' => 'Passwort zurückgesetzt', 'user.sessions_ended' => 'Sitzungen beendet',
    'user.2fa_reset' => '2FA zurückgesetzt', 'user.2fa_enabled' => '2FA aktiviert', 'user.2fa_disabled' => '2FA abgeschaltet',
    'user.2fa_codes' => 'Neue 2FA-Codes', 'user.2fa_recovery_used' => '2FA-Wiederherstellungscode benutzt', 'user.deactivate' => 'Konto gesperrt',
    'user.reactivate' => 'Konto entsperrt', 'rank.create' => 'Rang angelegt', 'rank.update' => 'Rang geändert', 'rank.delete' => 'Rang gelöscht',
    'departments.create' => 'Abteilung angelegt', 'departments.update' => 'Abteilung geändert', 'departments.delete' => 'Abteilung gelöscht',
    'qualifications.create' => 'Qualifikation angelegt', 'qualifications.update' => 'Qualifikation geändert', 'qualifications.delete' => 'Qualifikation gelöscht',
    'system.update_request' => 'Update angefordert', 'system.migrate' => 'Migrationen ausgeführt', 'system.backup' => 'Backup erstellt',
    'system.backup_download' => 'Backup heruntergeladen',
    'document.print' => 'Druckauftrag gesendet', 'document.printed' => 'Am Drucker abgeholt', 'document.print_cancel' => 'Druckauftrag abgebrochen',
    'printers.create' => 'Drucker angelegt', 'printers.update' => 'Drucker geändert', 'printers.delete' => 'Drucker gelöscht', 'printers.pickup_rule' => 'Abholregel geändert',
];
$links = ['patient' => 'patient', 'document' => 'document', 'user' => 'user_edit', 'rank' => 'rank_edit', 'printer' => 'printers', 'case' => 'case', 'course' => 'course'];
ob_start(); ?>
<div class="page-head"><div><h1>Zugriffsprotokoll</h1><p class="muted">Alle Zugriffe auf Akten und Dokumente werden protokolliert (<?= $total ?> Einträge).</p></div>
    <form action="index.php"><input type="hidden" name="p" value="audit">
        <select name="user" onchange="this.form.submit()"><option value="">Alle Mitarbeitenden</option>
        <?php foreach (all('SELECT id, name FROM users WHERE ' . fw() . ' ORDER BY name') as $u): ?><option value="<?= (int) $u['id'] ?>"<?= selected($u['id'], $userFilter) ?>><?= e($u['name']) ?></option><?php endforeach; ?></select></form>
</div>
<div class="card">
<table class="list compact">
    <thead><tr><th>Zeitpunkt</th><th>Mitarbeiter/in</th><th>Aktion</th><th>Objekt</th><th>Details</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?= e(fmt_datetime($r['created_at'])) ?></td>
            <td><?= e($r['name'] ?? '–') ?></td>
            <td><?= e($labels[$r['action']] ?? $r['action']) ?></td>
            <td><?php if ($r['object_id'] && isset($links[$r['object_type']])): ?><a href="<?= e(url($links[$r['object_type']], ['id' => $r['object_id']])) ?>"><?= e($r['object_type']) ?> #<?= (int) $r['object_id'] ?></a><?php endif; ?></td>
            <td><?= e($r['details']) ?></td>
            <td class="mono small"><?= e($r['ip']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if ($pages > 1): ?><div class="pager">
    <?php for ($i = max(1, $page - 5); $i <= min($pages, $page + 5); $i++): ?><a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(url('audit', ['page' => $i, 'user' => $userFilter ?: null])) ?>"><?= $i ?></a><?php endfor; ?>
</div><?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Zugriffsprotokoll';
require SAMS_ROOT . '/app/layout.php';
