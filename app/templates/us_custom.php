<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung für Dokumentarten der Vorlagen-Engine. Gestaltung je Art aus app/us_layouts/*.php (siehe app/render_us.php).
 * Beim Ausstellen eingefrorene Felder (_tpl) haben Vorrang, damit ältere Dokumente unverändert bleiben.
 */
$tpl = is_array($d['_tpl'] ?? null) ? $d['_tpl'] : ($type['custom'] ?? []);
$fields = array_values(array_filter($tpl['fields'] ?? $type['fields'], fn($f) => ($f['print'] ?? true) !== false));
$cfg = us_layout($type);
if (!empty($tpl['title']) && empty(us_layout_registry()[$type['key'] ?? '']['title'])) {
    $cfg['title'] = $tpl['title'];
}
$body = (string) ($tpl['body'] ?? '');
$used = custom_used_fields($body);
if ($cfg['caption'] === true) {
    $used[] = 'aktenzeichen'; // steht im Rubrum
}
$sigFields = array_values(array_filter($fields, fn($f) => ($f['type'] ?? '') === 'signature'));
$n = 0;
$pleading = in_array($cfg['style'], ['court', 'pleading'], true);
$classes = 'us-' . $cfg['style'] . ($pleading ? ' us-pleading-paper' : '') . ($cfg['style'] === 'certificate' ? ' us-cert' : '');
echo us_paper_open($doc, $draft, $classes);
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#1d2f5e') ?>; }</style><?php endif; ?>
<?php if ($pleading): ?><div class="us-lines" aria-hidden="true"><?php for ($i = 1; $i <= 28; $i++): ?><span><?= $i ?></span><?php endfor; ?></div><?php endif; ?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>
    <?= us_person_block($doc, $cfg, $n) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['validbox', 'times'])), $doc, $cfg) ?>
    <?php if (!empty($cfg['order_before'])): ?><?= us_blocks(['order'], $doc, $cfg) ?><?php endif; ?>
    <?php if ($body !== '' && empty($cfg['body_after'])): ?><div class="us-prose prose"><?= custom_render_body($body, $doc, $fields) ?></div><?php endif; ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], empty($cfg['order_before']) ? ['miranda', 'order', 'notice', 'hipaa'] : ['miranda', 'notice', 'hipaa'])), $doc, $cfg) ?>
    <?= us_fields($fields, $d, $cfg, $n, $used) ?>
    <?php if ($body !== '' && !empty($cfg['body_after'])): ?><div class="us-prose prose"><?= custom_render_body($body, $doc, $fields) ?></div><?php endif; ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['oath', 'notice2'])), $doc, $cfg) ?>
    <?php if (array_intersect((array) $cfg['blocks'], ['thumbprint', 'photo'])): ?><div class="us-idrow"><?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['photo', 'thumbprint'])), $doc, $cfg) ?></div><?php endif; ?>
    <?= us_signatures($doc, $cfg, $sigFields) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['review', 'receipt', 'tearoff'])), $doc, $cfg) ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
