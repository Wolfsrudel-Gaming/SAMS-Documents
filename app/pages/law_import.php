<?php
defined('SAMS_ROOT') || exit;
/** Gesetzbuch importieren: PDF (im Browser gelesen), Text- oder Markdown-Datei oder eingefügter Text → Vorschau → speichern. */
$me = current_user();
$preview = $_SESSION['law_import'] ?? null;

/** Text aus einer hochgeladenen Datei (Text/Markdown direkt, PDF nur, wenn pdftotext auf dem Server vorhanden ist). */
function law_upload_text(): string
{
    $f = $_FILES['file'] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || $f['size'] > 10 * 1024 * 1024) {
        return '';
    }
    $data = (string) file_get_contents($f['tmp_name']);
    if (str_starts_with($data, '%PDF')) {
        if (!function_exists('shell_exec') || !trim((string) @shell_exec('command -v pdftotext 2>/dev/null'))) {
            return '';
        }
        return (string) @shell_exec('pdftotext -layout -enc UTF-8 ' . escapeshellarg($f['tmp_name']) . ' - 2>/dev/null');
    }
    return mb_check_encoding($data, 'UTF-8') ? $data : mb_convert_encoding($data, 'UTF-8', 'Windows-1252');
}

if (is_post()) {
    csrf_check();
    $action = post('action');
    if ($action === 'preview') {
        $text = trim((string) post('text')) ?: law_upload_text();
        if (trim($text) === '') {
            flash('error', 'Kein Text gefunden. Bitte die PDF auswählen (sie wird im Browser gelesen) oder den Text einfügen.');
            redirect(url('law_import'));
        }
        $parsed = law_parse($text);
        $server = (int) post('server_id');
        $_SESSION['law_import'] = $parsed + ['server_id' => $server && game_server($server) ? $server : null,
            'source' => mb_substr((string) ($_FILES['file']['name'] ?? '') ?: (string) post('source_name') ?: 'eingefügter Text', 0, 190)];
        redirect(url('law_import'));
    }
    if ($action === 'cancel') {
        unset($_SESSION['law_import']);
        redirect(url('law_import'));
    }
    if ($action === 'import' && $preview) {
        $preview['name'] = mb_substr(trim((string) post('name')) ?: $preview['name'] ?: 'Gesetzbuch', 0, 190);
        $preview['description'] = mb_substr(trim((string) post('description')), 0, 2000);
        $books = [];
        foreach ($preview['books'] as $i => $b) {
            if (!post("use_$i")) {
                continue;
            }
            $b['title'] = mb_substr(trim((string) post("title_$i")) ?: $b['title'], 0, 190);
            $b['short'] = mb_substr(trim((string) post("short_$i")), 0, 32);
            $books[] = $b;
        }
        $preview['books'] = $books;
        $server = (int) post('server_id');
        $codeId = law_import($preview, $server && game_server($server) ? $server : null, (string) $preview['source'], (int) $me['id']);
        $n = array_sum(array_map(fn($b) => count($b['sections']), $books));
        audit('laws.import', 'law_code', $codeId, $preview['name'] . ': ' . count($books) . ' Bücher, ' . $n . ' Paragraphen (' . $preview['source'] . ')');
        unset($_SESSION['law_import']);
        flash('success', 'Gesetzbuch „' . $preview['name'] . '“ importiert: ' . count($books) . ' Bücher, ' . $n . ' Paragraphen.');
        redirect(url('laws', ['code' => $codeId]));
    }
    redirect(url('law_import'));
}

$servers = game_servers();
$serverSelect = function (?int $sel) use ($servers): string {
    if (count($servers) < 2) {
        return '<input type="hidden" name="server_id" value="' . (int) $sel . '">';
    }
    $h = '<div class="field w-third"><label>Gilt in</label><select name="server_id"><option value="0">allen Staaten</option>';
    foreach ($servers as $s) {
        $h .= '<option value="' . (int) $s['id'] . '"' . selected($s['id'], $sel) . '>' . e($s['name']) . '</option>';
    }
    return $h . '</select></div>';
};

ob_start(); ?>
<div class="page-head"><div><h1>Gesetzbuch importieren</h1><p class="muted">PDF, Text- oder Markdown-Datei hochladen oder Text einfügen. Erkannt werden Bücher (z. B. „Strafgesetzbuch (StGB)“), Paragraphen („§1 Titel“), Absätze, Aufzählungen und Strafmaße („Strafmaß: bis 20 HE und 10.000 $“). Vor dem Speichern siehst du eine Vorschau.</p></div>
    <div><a class="btn" href="<?= e(url('laws')) ?>">Zu den Gesetzen</a></div></div>

<?php if (!$preview): ?>
<form method="post" enctype="multipart/form-data" class="card form-grid" id="law-form">
    <?= csrf_field() ?><input type="hidden" name="action" value="preview"><input type="hidden" name="source_name" id="law-source">
    <div class="field w-two-thirds"><label>Datei (PDF, TXT, MD)</label><input type="file" name="file" id="law-file" accept=".pdf,.txt,.md,application/pdf,text/plain,text/markdown">
        <div class="hint" id="law-status">Die PDF wird direkt im Browser gelesen – es wird nur der Text übertragen.</div></div>
    <?= $serverSelect(game_server_for_user((int) $me['id']) ?: null) ?>
    <div class="field w-full"><label>Text des Gesetzbuchs</label><textarea name="text" id="law-text" rows="16" placeholder="Strafgesetzbuch (StGB)&#10;&#10;§1 Körperverletzung&#10;(1) Körperverletzung begeht, wer …&#10;Strafmaß: bis 20 HE und 10.000 $"></textarea>
        <div class="hint">Tipp für eigene Texte: Buchüberschrift in eine eigene Zeile (Kürzel in Klammern), jeden Paragraphen mit „§“ beginnen, Strafmaß in eine eigene Zeile mit „Strafmaß:“.</div></div>
    <div class="form-actions"><button class="btn primary">Vorschau</button></div>
</form>
<script src="assets/vendor/pdf.min.js"></script>
<script>
(function () {
    var file = document.getElementById('law-file'), text = document.getElementById('law-text'), status = document.getElementById('law-status');
    if (window.pdfjsLib) { pdfjsLib.GlobalWorkerOptions.workerSrc = 'assets/vendor/pdf.worker.min.js'; }
    // Textzeilen einer PDF-Seite: nach Höhe gruppieren, Einrückung und Absätze erhalten
    function pageText(tc) {
        var items = tc.items.filter(function (i) { return i.str !== undefined && i.str !== ''; });
        if (!items.length) { return ''; }
        var minX = Math.min.apply(null, items.map(function (i) { return i.transform[4]; }));
        var rows = [];
        items.forEach(function (i) {
            var y = i.transform[5], row = rows.find(function (r) { return Math.abs(r.y - y) < 2.5; });
            if (!row) { row = {y: y, h: i.height || 10, items: []}; rows.push(row); }
            row.items.push(i);
        });
        rows.sort(function (a, b) { return b.y - a.y; });
        var out = [], lastY = null;
        rows.forEach(function (r) {
            r.items.sort(function (a, b) { return a.transform[4] - b.transform[4]; });
            var line = '', end = null;
            r.items.forEach(function (i) {
                var x = i.transform[4];
                if (end !== null && x - end > 1.5 && !/\s$/.test(line) && !/^\s/.test(i.str)) { line += ' '; }
                line += i.str; end = x + i.width;
            });
            if (lastY !== null && lastY - r.y > r.h * 1.9) { out.push(''); }
            out.push((r.items[0].transform[4] - minX > 12 ? '     ' : '') + line.replace(/\s+$/, ''));
            lastY = r.y;
        });
        return out.join('\n');
    }
    file && file.addEventListener('change', async function () {
        var f = file.files[0];
        if (!f) { return; }
        document.getElementById('law-source').value = f.name;
        if (/\.pdf$/i.test(f.name) || f.type === 'application/pdf') {
            if (!window.pdfjsLib) { status.textContent = 'PDF-Leser nicht geladen – die Datei wird beim Absenden auf dem Server gelesen (falls dort möglich).'; return; }
            status.textContent = 'PDF wird gelesen …';
            try {
                var pdf = await pdfjsLib.getDocument({data: await f.arrayBuffer()}).promise, pages = [];
                for (var p = 1; p <= pdf.numPages; p++) {
                    pages.push(pageText(await (await pdf.getPage(p)).getTextContent()));
                    status.textContent = 'PDF wird gelesen … Seite ' + p + ' von ' + pdf.numPages;
                }
                text.value = pages.join('\n\n');
                status.textContent = pdf.numPages + ' Seiten gelesen. Text prüfen und „Vorschau“ wählen.';
                file.value = '';
            } catch (e) { status.textContent = 'Die PDF konnte nicht gelesen werden: ' + e.message; }
        } else {
            text.value = await f.text();
            status.textContent = 'Datei geladen.';
            file.value = '';
        }
    });
})();
</script>
<?php else: $total = array_sum(array_map(fn($b) => count($b['sections']), $preview['books'])); $pen = array_sum(array_map(fn($b) => count(array_filter($b['sections'], fn($s) => $s['jail_max'] || $s['fine_max'])), $preview['books'])); ?>
<form method="post" class="card form-grid">
    <?= csrf_field() ?><input type="hidden" name="action" value="import">
    <h2 class="w-full">Vorschau: <?= count($preview['books']) ?> Bücher, <?= $total ?> Paragraphen, <?= $pen ?> mit Strafmaß</h2>
    <div class="field w-two-thirds"><label>Name des Gesetzbuchs</label><input name="name" value="<?= e($preview['name']) ?>" required maxlength="190"></div>
    <?= $serverSelect($preview['server_id']) ?>
    <div class="field w-full"><label>Beschreibung</label><textarea name="description" rows="2"><?= e($preview['description']) ?></textarea></div>
    <?php if ($preview['warnings']): ?><div class="flash warn w-full"><strong>Bitte prüfen:</strong><ul><?php foreach ($preview['warnings'] as $w): ?><li><?= e($w) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($ex = one('SELECT id FROM law_codes WHERE name = ?', [$preview['name']])): ?><div class="flash info w-full">Ein Gesetzbuch mit diesem Namen gibt es schon. Beim Import im selben Staat wird es <strong>ersetzt</strong>.</div><?php endif; ?>
    <table class="list w-full"><thead><tr><th></th><th>Buch</th><th>Kürzel</th><th>Paragraphen</th><th>mit Strafmaß</th></tr></thead><tbody>
    <?php foreach ($preview['books'] as $i => $b): ?>
        <tr><td><input type="checkbox" name="use_<?= $i ?>" value="1" checked></td>
            <td><input name="title_<?= $i ?>" value="<?= e($b['title']) ?>" maxlength="190"></td><td style="width:8rem"><input name="short_<?= $i ?>" value="<?= e($b['short']) ?>" maxlength="32"></td>
            <td><?= count($b['sections']) ?></td><td><?= count(array_filter($b['sections'], fn($s) => $s['jail_max'] || $s['fine_max'])) ?></td></tr>
        <tr><td></td><td colspan="4"><details><summary class="small">Paragraphen anzeigen</summary><table class="list compact">
            <?php foreach ($b['sections'] as $s): ?><tr><td class="nowrap"><strong>§ <?= e($s['number']) ?></strong></td><td><?= e($s['title']) ?><div class="small muted law-snippet"><?= e(mb_strimwidth(str_replace("\n", ' ', $s['body']), 0, 220, '…')) ?></div></td>
                <td class="small nowrap"><?= $s['jail_max'] || $s['fine_max'] ? '<span class="badge red">' . e(law_penalty_label($s)) . '</span>' : '' ?></td></tr><?php endforeach; ?>
        </table></details></td></tr>
    <?php endforeach; ?>
    </tbody></table>
    <div class="form-actions"><button class="btn primary"<?= $total ? '' : ' disabled' ?>>Importieren</button> <button class="btn" name="action" value="cancel" formnovalidate>Verwerfen</button></div>
</form>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Gesetzbuch importieren';
require SAMS_ROOT . '/app/layout.php';
