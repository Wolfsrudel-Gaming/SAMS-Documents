<?php
defined('SAMS_ROOT') || exit;
$q = trim((string) ($_GET['q'] ?? ''));
$params = [];
$where = '1=1';
if ($q !== '') {
    $where = "(LOWER(first_name || ' ' || last_name) LIKE LOWER(:q) ESCAPE '\\' OR LOWER(last_name || ' ' || first_name) LIKE LOWER(:q) ESCAPE '\\'
        OR citizen_id LIKE :q ESCAPE '\\' OR phone LIKE :q ESCAPE '\\' OR insurance_number LIKE :q ESCAPE '\\')";
    if (db_driver() === 'mysql') {
        // MySQL nutzt "\" bereits als Standard-Escape-Zeichen für LIKE
        $where = str_replace(" ESCAPE '\\'", '', $where);
        $where = str_replace(["first_name || ' ' || last_name", "last_name || ' ' || first_name"],
            ["CONCAT(first_name, ' ', last_name)", "CONCAT(last_name, ' ', first_name)"], $where);
    }
    $params['q'] = like($q);
}
$page = max(1, (int) ($_GET['page'] ?? 1));
$per = 25;
$total = (int) scalar("SELECT COUNT(*) FROM patients WHERE $where", $params);
$patients = all("SELECT p.*, (SELECT COUNT(*) FROM documents d WHERE d.patient_id = p.id) AS doc_count
    FROM patients p WHERE $where ORDER BY last_name, first_name LIMIT $per OFFSET " . (($page - 1) * $per), $params);
$pages = max(1, (int) ceil($total / $per));

ob_start(); ?>
<div class="page-head">
    <div><h1>Patientenakten</h1><p class="muted"><?= $total ?> Akte(n)</p></div>
    <a class="btn primary" href="<?= e(url('patient_edit')) ?>">+ Neue Patientenakte</a>
</div>
<form class="card filter" action="index.php">
    <input type="hidden" name="p" value="patients">
    <input name="q" value="<?= e($q) ?>" placeholder="Name, Bürger-ID, Telefon oder Versichertennummer …" autofocus>
    <button class="btn">Suchen</button>
</form>
<div class="card">
<table class="list">
    <thead><tr><th>Name</th><th>Geburtsdatum</th><th>Bürger-ID</th><th>Telefon</th><th>Blutgr.</th><th>Hinweise</th><th class="right">Dok.</th></tr></thead>
    <tbody>
    <?php foreach ($patients as $p): ?>
        <tr onclick="location.href='<?= e(url('patient', ['id' => $p['id']])) ?>'">
            <td><strong><?= e(patient_name($p, true)) ?></strong><?= $p['deceased_at'] ? ' <span class="badge black">† verstorben</span>' : '' ?></td>
            <td><?= e(fmt_date($p['dob'])) ?> <?= $p['dob'] ? '<small class="muted">(' . e(age($p['dob'])) . ' J.)</small>' : '' ?></td>
            <td class="mono"><?= e($p['citizen_id']) ?></td>
            <td><?= e($p['phone']) ?></td>
            <td><?= e($p['blood_type']) ?></td>
            <td><?php if (trim((string) $p['allergies']) !== ''): ?><span class="badge red" title="<?= e($p['allergies']) ?>">Allergien</span><?php endif; ?>
                <?php foreach (array_filter(array_map('trim', explode(',', (string) $p['flags']))) as $flag): ?><span class="badge orange"><?= e($flag) ?></span> <?php endforeach; ?></td>
            <td class="right"><?= (int) $p['doc_count'] ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (!$patients): ?><tr><td colspan="7" class="muted">Keine Akten gefunden.</td></tr><?php endif; ?>
    </tbody>
</table>
<?php if ($pages > 1): ?>
<div class="pager">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a class="<?= $i === $page ? 'active' : '' ?>" href="<?= e(url('patients', ['q' => $q, 'page' => $i])) ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Patientenakten';
require SAMS_ROOT . '/app/layout.php';
