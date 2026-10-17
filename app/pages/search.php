<?php
defined('SAMS_ROOT') || exit;
/**
 * Suche aus der Kopfleiste. Exakte Treffer (Dokument-Nr., Prüfcode, Bürger-ID)
 * öffnen direkt, sonst Volltextsuche über alle Bereiche, auf die der
 * Benutzer Zugriff hat (vertrauliche/abteilungsbezogene Einträge werden gefiltert).
 */
$q = trim((string) ($_GET['q'] ?? ''));
if ($q === '') {
    redirect(url(can('patients.view') ? 'patients' : 'dashboard'));
}
$code = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $q));
if (can('documents.view') && ($doc = one('SELECT id FROM documents WHERE number = ? OR verify_code = ?', [strtoupper($q), $code]))) {
    redirect(url('document', ['id' => $doc['id']]));
}
if (can('patients.view') && ($p = one("SELECT id FROM patients WHERE citizen_id = ? AND citizen_id <> '' AND merged_into IS NULL", [$q]))) {
    redirect(url('patient', ['id' => $p['id']]));
}

$like = like($q);
$limit = 15;
$sections = [];
// Textausschnitt um den Treffer herum
$snippet = function (string $text) use ($q): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    $pos = mb_stripos($text, $q);
    $start = $pos === false ? 0 : max(0, $pos - 60);
    $cut = mb_substr($text, $start, 180);
    $html = e(($start > 0 ? '… ' : '') . $cut . (mb_strlen($text) > $start + 180 ? ' …' : ''));
    return preg_replace('/(' . preg_quote(e($q), '/') . ')/iu', '<mark>$1</mark>', $html);
};
$nameExpr = db_driver() === 'mysql' ? "CONCAT(first_name, ' ', last_name)" : "first_name || ' ' || last_name";

if (can('patients.view')) {
    $rows = all("SELECT * FROM patients WHERE merged_into IS NULL AND ($nameExpr LIKE ? OR citizen_id LIKE ? OR phone LIKE ? OR insurance_number LIKE ?) ORDER BY archived_at IS NOT NULL, last_name LIMIT $limit", [$like, $like, $like, $like]);
    $sections['Patientenakten'] = array_map(fn($p) => ['url' => url('patient', ['id' => $p['id']]), 'title' => patient_name($p, true) . ($p['archived_at'] ? ' (archiviert)' : ''),
        'meta' => 'geb. ' . fmt_date($p['dob']) . ($p['citizen_id'] ? ' · ' . $p['citizen_id'] : ''), 'text' => ''], $rows);

    $rows = all('SELECT c.*, p.first_name, p.last_name FROM cases c JOIN patients p ON p.id = c.patient_id WHERE c.number LIKE ? OR c.title LIKE ? OR c.diagnosis LIKE ? OR c.incident LIKE ? OR c.location LIKE ? ORDER BY c.id DESC LIMIT ' . $limit, [$like, $like, $like, $like, $like]);
    $sections['Fälle'] = array_map(fn($c) => ['url' => url('case', ['id' => $c['id']]), 'title' => $c['title'], 'meta' => $c['number'] . ' · ' . patient_name($c, true),
        'text' => $snippet($c['diagnosis'] . ' ' . $c['incident'] . ' ' . $c['location'])], $rows);

    $rows = all('SELECT e.*, p.first_name, p.last_name FROM entries e JOIN patients p ON p.id = e.patient_id WHERE e.title LIKE ? OR e.content LIKE ? ORDER BY e.occurred_at DESC LIMIT 60', [$like, $like]);
    $rows = array_slice(array_values(array_filter($rows, 'entry_visible')), 0, $limit);
    $sections['Akteneinträge'] = array_map(fn($e) => ['url' => url('patient', ['id' => $e['patient_id']]), 'title' => $e['title'], 'meta' => fmt_datetime($e['occurred_at']) . ' · ' . patient_name($e, true) . ' · ' . $e['type'],
        'text' => $snippet($e['content'])], $rows);
}
if (can('documents.view')) {
    $rows = all('SELECT * FROM documents WHERE number LIKE ? OR patient_snapshot LIKE ? ORDER BY issued_at DESC LIMIT ' . $limit, [$like, $like]);
    $sections['Dokumente'] = array_map(function ($d) {
        $p = json_decode((string) $d['patient_snapshot'], true) ?: [];
        return ['url' => url('document', ['id' => $d['id']]), 'title' => (doc_type($d['type'])['title'] ?? $d['type']), 'meta' => $d['number'] . ' · ' . fmt_date($d['issued_at']) . ($p ? ' · ' . patient_name($p, true) : ''), 'text' => ''];
    }, $rows);
}
if (can('wiki.view|wiki.edit')) {
    $rows = all('SELECT * FROM wiki_pages WHERE title LIKE ? OR content LIKE ? ORDER BY title LIMIT ' . $limit, [$like, $like]);
    $sections['Wiki'] = array_map(fn($w) => ['url' => url('wiki_page', ['slug' => $w['slug']]), 'title' => $w['title'], 'meta' => $w['category'], 'text' => $snippet((string) $w['content'])], $rows);
}
if (can('training.view|training.manage')) {
    $rows = all('SELECT * FROM training_courses WHERE active = 1 AND (title LIKE ? OR description LIKE ?) ORDER BY title LIMIT ' . $limit, [$like, $like]);
    $sections['Lehrgänge'] = array_map(fn($c) => ['url' => url('course', ['id' => $c['id']]), 'title' => $c['title'], 'meta' => 'Lehrgang', 'text' => $snippet((string) $c['description'])], $rows);
}
if (can('users.view') || can('personnel.view')) {
    $rows = all('SELECT * FROM users WHERE name LIKE ? OR username LIKE ? OR badge_number LIKE ? ORDER BY name LIMIT ' . $limit, [$like, $like, $like]);
    $sections['Personal'] = array_map(fn($u) => ['url' => url(can('personnel.view') ? 'personnel' : 'user_edit', ['id' => $u['id']]), 'title' => user_display($u),
        'meta' => user_rank_name($u) . ($u['badge_number'] ? ' · ' . $u['badge_number'] : '') . ($u['active'] ? '' : ' · gesperrt'), 'text' => ''], $rows);
}
$sections = array_filter($sections);
$total = array_sum(array_map('count', $sections));

ob_start(); ?>
<div class="page-head">
    <div><h1>Suche: „<?= e($q) ?>“</h1><p class="muted"><?= $total ?> Treffer<?= $total ? ' in ' . count($sections) . ' Bereichen' : '' ?></p></div>
</div>
<?php if (!$sections): ?><div class="card muted">Nichts gefunden. Tipp: Teile eines Namens, eine Diagnose, Dokumentnummer oder Dienstnummer eingeben.</div><?php endif; ?>
<?php if (count($sections) > 1): ?><div class="tabs page-tabs"><?php foreach ($sections as $name => $list): ?><a href="#s-<?= e(custom_slug($name)) ?>"><?= e($name) ?> (<?= count($list) ?>)</a><?php endforeach; ?></div><?php endif; ?>
<?php foreach ($sections as $name => $list): ?>
    <section class="card" id="s-<?= e(custom_slug($name)) ?>">
        <h2><?= e($name) ?></h2>
        <ul class="search-results">
            <?php foreach ($list as $r): ?>
                <li><a href="<?= e($r['url']) ?>"><?= preg_replace('/(' . preg_quote(e($q), '/') . ')/iu', '<mark>$1</mark>', e($r['title'])) ?></a>
                    <small class="muted"><?= e($r['meta']) ?></small><?php if ($r['text'] !== ''): ?><span class="snippet"><?= $r['text'] ?></span><?php endif; ?></li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endforeach; ?>
<?php
$content = ob_get_clean();
$title = 'Suche';
require SAMS_ROOT . '/app/layout.php';
