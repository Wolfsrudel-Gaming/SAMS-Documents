<?php
defined('SAMS_ROOT') || exit;
/** Anwaltsverzeichnis: alle Kanzleien mit ihren Anwältinnen und Anwälten – für Gericht, Polizei, Rettungsdienst und Verwaltung. */
$firms = law_firms();

ob_start(); ?>
<div class="page-head"><div><h1>Anwaltsverzeichnis</h1><p class="muted"><?= count($firms) ?> Kanzlei<?= count($firms) === 1 ? '' : 'en' ?> mit <?= count(lawyers_directory()) ?> zugelassenen Anwältinnen und Anwälten. Bei Festnahmen auf Wunsch eine Kanzlei verständigen; Pflichtverteidigung im Gerichtskalender zuordnen.</p></div></div>
<?php if (!$firms): ?><div class="card muted">Noch keine Kanzleien. Der Hauptadministrator legt sie unter „Fraktionen“ mit der Art „Anwaltskanzlei“ an.</div><?php endif; ?>
<div class="lawyer-grid">
<?php foreach ($firms as $f): ?>
    <section class="card">
        <div class="card-head"><h2><img src="<?= e($f['logo']) ?>" alt="" class="firm-logo"> <?= e($f['name']) ?></h2></div>
        <p class="small muted"><?= e(trim($f['street'] . ', ' . $f['city'], ', ')) ?><?= $f['phone'] !== '' ? ' · Tel. ' . e($f['phone']) : '' ?></p>
        <table class="list compact"><tbody>
        <?php foreach ($f['lawyers'] as $l): ?><tr><td><strong><?= e(user_display($l)) ?></strong><br><span class="small muted"><?= e($l['rank_name']) ?></span></td><td class="small right"><?= $l['phone'] ? 'Tel. ' . e($l['phone']) : '' ?></td></tr><?php endforeach; ?>
        </tbody></table>
        <?php if (fid() !== $f['id'] && can('requests.manage') && module_enabled('requests')): ?><a class="btn small" href="<?= e(url('requests', ['to' => $f['id']])) ?>">Anfrage an die Kanzlei</a><?php endif; ?>
    </section>
<?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Anwaltsverzeichnis';
require SAMS_ROOT . '/app/layout.php';
