<?php defined('SAMS_ROOT') || exit; ?>
<div class="card narrow">
    <h1><?= e($title ?? 'Fehler') ?></h1>
    <p><?= e($message ?? '') ?></p>
    <p><a class="btn" href="<?= e(url('dashboard')) ?>">Zur Übersicht</a></p>
</div>
