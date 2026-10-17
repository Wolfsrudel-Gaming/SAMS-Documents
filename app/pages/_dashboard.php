<?php defined('SAMS_ROOT') || exit; ?>
<div class="page-head">
    <div>
        <h1>Guten <?= (int) date('G') < 11 ? 'Morgen' : ((int) date('G') < 18 ? 'Tag' : 'Abend') ?>, <?= e(user_display($me)) ?></h1>
        <p class="muted"><?= e(setting('org_name', 'San Andreas Medical Services')) ?> · <?= e(fmt_date(date('Y-m-d'), 'l, d.m.Y')) ?></p>
    </div>
    <div class="btn-row">
        <?php if (can('patients.edit')): ?><a class="btn" href="<?= e(url('patient_edit')) ?>"><?= icon('plus') ?>Neue Patientenakte</a><?php endif; ?>
        <?php if (can_issue_any()): ?><a class="btn primary" href="<?= e(url('document_new')) ?>"><?= icon('doc-plus') ?>Dokument ausstellen</a><?php endif; ?>
    </div>
</div>

<?php foreach ($notices as $a): ?>
<div class="card board-alert">
    <div class="card-head"><h2><?= e($a['title']) ?></h2><a href="<?= e(url('board')) ?>#a<?= (int) $a['id'] ?>">Schwarzes Brett</a></div>
    <div class="prose-ui"><?= light_markup(mb_strimwidth((string) $a['content'], 0, 600, '…')) ?></div>
    <form method="post" action="<?= e(url('board')) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $a['id'] ?>"><input type="hidden" name="return" value="dashboard">
        <button class="btn small primary" name="action" value="<?= $a['require_ack'] ? 'ack' : 'read' ?>"><?= $a['require_ack'] ? 'Gelesen und verstanden' : 'Gelesen' ?></button></form>
</div>
<?php endforeach; ?>

<?php if (empty($me['signature'])): ?>
<div class="flash warn">Sie haben noch keine Unterschrift hinterlegt. <a href="<?= e(url('profile')) ?>">Jetzt im Profil unterschreiben</a> – sie wird auf allen Dokumenten gedruckt.</div>
<?php endif; ?>

<?php if ($stats): ?><div class="stats">
    <?php foreach ($stats as $label => $n): ?>
        <div class="stat"><strong><?= $n ?></strong><span><?= e($label) ?></span></div>
    <?php endforeach; ?>
</div><?php endif; ?>

<?php if (can_issue_any()): ?>
<section class="card">
    <h2>Dokument ausstellen</h2>
    <div class="tiles">
        <?php foreach (document_types() as $key => $t): if (!empty($t['hidden']) || !can('documents.issue.' . $key)) continue; ?>
            <a class="tile" href="<?= e(url('document_new', ['type' => $key])) ?>">
                <span class="tile-code"><?= e($t['code']) ?></span>
                <strong><?= e($t['short']) ?></strong>
                <small><?= e($t['category']) ?></small>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<div class="grid-2">
    <?php if ($recentDocs !== null): ?>
    <section class="card">
        <div class="card-head"><h2>Zuletzt ausgestellt</h2><a href="<?= e(url('documents')) ?>">Alle</a></div>
        <table class="list">
            <tbody>
            <?php foreach ($recentDocs as $d): $t = doc_type($d['type']); ?>
                <tr onclick="location.href='<?= e(url('document', ['id' => $d['id']])) ?>'">
                    <td><strong><?= e($t['short'] ?? $d['type']) ?></strong><br><small class="mono"><?= e($d['number']) ?></small></td>
                    <td><?= e(patient_name($d, true) ?: '—') ?><br><small class="muted"><?= e($d['issuer_name']) ?></small></td>
                    <td class="right"><?= doc_status_badge($d) ?><br><small class="muted"><?= e(fmt_datetime($d['issued_at'])) ?></small></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recentDocs): ?><tr><td class="muted">Noch keine Dokumente.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>
    <?php if ($recentPatients !== null): ?>
    <section class="card">
        <div class="card-head"><h2>Zuletzt bearbeitete Akten</h2><a href="<?= e(url('patient_edit')) ?>">+ Neue Akte</a></div>
        <table class="list">
            <tbody>
            <?php foreach ($recentPatients as $p): ?>
                <tr onclick="location.href='<?= e(url('patient', ['id' => $p['id']])) ?>'">
                    <td><strong><?= e(patient_name($p, true)) ?></strong><?= $p['deceased_at'] ? ' <span class="badge black">†</span>' : '' ?><br>
                        <small class="muted">geb. <?= e(fmt_date($p['dob'])) ?><?= $p['citizen_id'] ? ' · ID ' . e($p['citizen_id']) : '' ?></small></td>
                    <td class="right"><?= $p['blood_type'] ? '<span class="badge">' . e($p['blood_type']) . '</span>' : '' ?><br><small class="muted"><?= e(fmt_datetime($p['updated_at'])) ?></small></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recentPatients): ?><tr><td class="muted">Noch keine Patientenakten.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </section>
    <?php endif; ?>
</div>
