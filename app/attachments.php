<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Anhänge (Fotos, Röntgen-/CT-Bilder, Scans, PDF) an Akten und Fällen.
 * Dateien liegen außerhalb des Webroots und werden nur über
 * index.php?p=attachment mit Rechteprüfung ausgeliefert. Bilder werden beim
 * Hochladen neu kodiert (entfernt Metadaten wie GPS und präparierte Inhalte).
 */

const ATTACHMENT_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif', 'application/pdf' => 'pdf'];
const ATTACHMENT_THUMB = 320;

function attachment_max_bytes(): int
{
    $setting = (int) setting('attachment_max_mb', '10') * 1024 * 1024;
    $ini = function (string $k): int {
        $v = trim((string) ini_get($k));
        $n = (int) $v;
        return match (strtolower(substr($v, -1))) { 'g' => $n << 30, 'm' => $n << 20, 'k' => $n << 10, default => $n };
    };
    return max(1 << 20, min($setting, $ini('upload_max_filesize') ?: $setting, $ini('post_max_size') ?: $setting));
}

function attachment_dir(): string
{
    return storage_path('attachments', true);
}

function attachment_path(array $a, bool $thumb = false): string
{
    return attachment_dir() . '/' . ($thumb ? 'thumb_' : '') . $a['stored_name'];
}

/**
 * Hochgeladene Datei prüfen und speichern. Gibt [id, null] oder [null, Fehlermeldung] zurück.
 */
function store_attachment(array $file, int $patientId, ?int $caseId, string $title, bool $confidential, int $userId): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [null, in_array($file['error'] ?? 0, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
            ? 'Die Datei ist zu groß (höchstens ' . round(attachment_max_bytes() / 1048576) . ' MB).' : 'Bitte eine Datei auswählen.'];
    }
    if ($file['size'] > attachment_max_bytes()) {
        return [null, 'Die Datei ist zu groß (höchstens ' . round(attachment_max_bytes() / 1048576) . ' MB).'];
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']) ?: '';
    if (!isset(ATTACHMENT_TYPES[$mime])) {
        return [null, 'Erlaubt sind Bilder (JPG, PNG, WebP, GIF) und PDF-Dateien.'];
    }
    $stored = bin2hex(random_bytes(16)) . '.' . ATTACHMENT_TYPES[$mime];
    $target = attachment_dir() . '/' . $stored;
    $width = $height = 0;
    $thumb = false;
    if ($mime === 'application/pdf') {
        if (strncmp((string) file_get_contents($file['tmp_name'], false, null, 0, 5), '%PDF-', 5) !== 0 || !move_uploaded_file_safe($file['tmp_name'], $target)) {
            return [null, 'Die PDF-Datei ist beschädigt.'];
        }
    } else {
        [$ok, $width, $height, $thumb] = reencode_image($file['tmp_name'], $mime, $target);
        if (!$ok) {
            return [null, 'Das Bild konnte nicht gelesen werden.'];
        }
    }
    $id = insert('attachments', [
        'patient_id' => $patientId, 'case_id' => $caseId, 'title' => mb_substr($title !== '' ? $title : pathinfo((string) $file['name'], PATHINFO_FILENAME), 0, 190),
        'original_name' => mb_substr((string) $file['name'], 0, 190), 'stored_name' => $stored, 'mime' => $mime, 'size' => (int) filesize($target),
        'width' => $width, 'height' => $height, 'has_thumb' => $thumb ? 1 : 0, 'confidential' => $confidential ? 1 : 0,
        'created_by' => $userId, 'created_at' => now(),
    ]);
    return [$id, null];
}

/** move_uploaded_file, im Testbetrieb (CLI) auch für normale Dateien. */
function move_uploaded_file_safe(string $from, string $to): bool
{
    return is_uploaded_file($from) ? move_uploaded_file($from, $to) : (PHP_SAPI === 'cli' && rename($from, $to));
}

/**
 * Bild neu kodieren (ohne Metadaten), sehr große Bilder auf 2560 px begrenzen,
 * Vorschaubild erzeugen. Ohne GD wird das Original übernommen.
 */
function reencode_image(string $src, string $mime, string $target): array
{
    $info = @getimagesize($src);
    if (!$info) {
        return [false, 0, 0, false];
    }
    [$w, $h] = $info;
    if (!function_exists('imagecreatefromstring') || $w * $h > 40_000_000) {
        return $w * $h > 40_000_000 ? [false, 0, 0, false] : [move_uploaded_file_safe($src, $target), $w, $h, false];
    }
    $img = @imagecreatefromstring((string) file_get_contents($src));
    if (!$img) {
        return [false, 0, 0, false];
    }
    $save = function ($im, string $path) use ($mime): bool {
        return match ($mime) {
            'image/png' => imagepng($im, $path, 6),
            'image/webp' => function_exists('imagewebp') ? imagewebp($im, $path, 85) : imagejpeg($im, $path, 88),
            'image/gif' => imagegif($im, $path),
            default => imagejpeg($im, $path, 88),
        };
    };
    $scale = function ($im, int $max) {
        $w = imagesx($im);
        $h = imagesy($im);
        if ($w <= $max && $h <= $max) {
            return $im;
        }
        $f = $max / max($w, $h);
        $out = imagecreatetruecolor((int) round($w * $f), (int) round($h * $f));
        imagealphablending($out, false);
        imagesavealpha($out, true);
        imagecopyresampled($out, $im, 0, 0, 0, 0, imagesx($out), imagesy($out), $w, $h);
        return $out;
    };
    imagesavealpha($img, true);
    $main = $scale($img, 2560);
    $ok = $save($main, $target);
    $thumb = $ok && $save($scale($main, ATTACHMENT_THUMB), dirname($target) . '/thumb_' . basename($target));
    return [$ok, imagesx($main), imagesy($main), $thumb];
}

function attachment_visible(array $a): bool
{
    $me = current_user();
    return $me && can('patients.view') && (!$a['confidential'] || can('entries.confidential') || (int) $a['created_by'] === (int) $me['id']);
}

function can_delete_attachment(array $a): bool
{
    $me = current_user();
    return (int) $a['created_by'] === (int) $me['id'] || can('entries.edit_all');
}

function delete_attachment(array $a): void
{
    @unlink(attachment_path($a));
    @unlink(attachment_path($a, true));
    q('DELETE FROM attachments WHERE id = ?', [$a['id']]);
}

function format_bytes(int $b): string
{
    return $b >= 1048576 ? number_format($b / 1048576, 1, ',', '.') . ' MB' : max(1, (int) round($b / 1024)) . ' KB';
}

/**
 * Upload-Aktionen aus Akte/Fall verarbeiten (POST action=attach / attach_delete).
 * Gibt true zurück, wenn eine Anhang-Aktion behandelt wurde.
 */
function handle_attachment_post(int $patientId, ?int $caseId): bool
{
    $action = (string) post('action');
    if (!in_array($action, ['attach', 'attach_delete'], true)) {
        return false;
    }
    $me = current_user();
    if ($action === 'attach_delete') {
        $a = one('SELECT * FROM attachments WHERE id = ? AND patient_id = ?', [(int) post('attachment_id'), $patientId]);
        if ($a && can_delete_attachment($a)) {
            delete_attachment($a);
            audit('attachment.delete', 'patient', $patientId, $a['title']);
            flash('success', 'Anhang gelöscht.');
        }
        return true;
    }
    require_perm('attachments.upload');
    $case = (int) post('case_id') ?: $caseId;
    if ($case && !scalar('SELECT id FROM cases WHERE id = ? AND patient_id = ?', [$case, $patientId])) {
        $case = null;
    }
    [$id, $error] = store_attachment($_FILES['file'] ?? [], $patientId, $case ?: null, trim((string) post('title')), (bool) post('confidential'), (int) $me['id']);
    if ($error) {
        flash('error', $error);
    } else {
        audit('attachment.upload', 'patient', $patientId, (string) scalar('SELECT title FROM attachments WHERE id = ?', [$id]));
        flash('success', 'Anhang hochgeladen.');
    }
    return true;
}

/** Kachel-Ansicht der Anhänge mit Upload-Formular. */
function attachments_card(int $patientId, ?int $caseId, bool $readonly): string
{
    $rows = all('SELECT a.*, u.name AS author, c.number AS case_number FROM attachments a LEFT JOIN users u ON u.id = a.created_by LEFT JOIN cases c ON c.id = a.case_id
        WHERE a.patient_id = ?' . ($caseId ? ' AND a.case_id = ?' : '') . ' ORDER BY a.id DESC', $caseId ? [$patientId, $caseId] : [$patientId]);
    $rows = array_values(array_filter($rows, 'attachment_visible'));
    $cases = $caseId ? [] : all("SELECT id, number, title FROM cases WHERE patient_id = ? AND status = 'open' ORDER BY id DESC", [$patientId]);
    ob_start(); ?>
    <div class="card" id="attachments">
        <div class="card-head"><h2>Anhänge (<?= count($rows) ?>)</h2></div>
        <?php if ($rows): ?>
        <div class="att-grid">
            <?php foreach ($rows as $a): $src = url('attachment', ['id' => $a['id']]); ?>
                <figure class="att">
                    <a href="<?= e($src) ?>" target="_blank" rel="noopener" class="att-thumb">
                        <?php if (str_starts_with($a['mime'], 'image/')): ?><img src="<?= e(url('attachment', ['id' => $a['id'], 'thumb' => 1])) ?>" alt="" loading="lazy">
                        <?php else: ?><span class="att-pdf">PDF</span><?php endif; ?>
                    </a>
                    <figcaption><strong><?= e($a['title']) ?></strong><?= $a['confidential'] ? ' <span class="badge purple">vertraulich</span>' : '' ?>
                        <small><?= e(fmt_datetime($a['created_at'])) ?> · <?= e($a['author'] ?? '') ?> · <?= e(format_bytes((int) $a['size'])) ?><?= $a['case_number'] && !$caseId ? ' · ' . e($a['case_number']) : '' ?></small>
                        <?php if (!$readonly && can_delete_attachment($a)): ?><form method="post" enctype="multipart/form-data"><?= csrf_field() ?><input type="hidden" name="action" value="attach_delete"><input type="hidden" name="attachment_id" value="<?= (int) $a['id'] ?>"><button class="btn-icon" title="Löschen" onclick="return confirm('Anhang löschen?')">✕</button></form><?php endif; ?>
                    </figcaption>
                </figure>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if (!$readonly && can('attachments.upload')): ?>
            <form method="post" enctype="multipart/form-data" class="form-grid att-upload">
                <?= csrf_field() ?><input type="hidden" name="action" value="attach">
                <div class="field w-third"><label>Datei (Bild oder PDF, max. <?= round(attachment_max_bytes() / 1048576) ?> MB)</label><input type="file" name="file" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf" required></div>
                <div class="field w-third"><label>Bezeichnung</label><input name="title" maxlength="190" placeholder="z. B. Röntgen Unterarm li."></div>
                <?php if ($cases): ?><div class="field w-third"><label>Fall</label><select name="case_id"><option value="">–</option><?php foreach ($cases as $c): ?><option value="<?= (int) $c['id'] ?>"><?= e($c['number'] . ' · ' . $c['title']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
                <div class="field w-full"><label class="choice single"><input type="checkbox" name="confidential" value="1"> vertraulich (nur ärztlicher Dienst)</label></div>
                <div class="form-actions"><button class="btn small">Hochladen</button></div>
            </form>
        <?php endif; ?>
        <?php if (!$rows && ($readonly || !can('attachments.upload'))): ?><p class="muted">Keine Anhänge.</p><?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
