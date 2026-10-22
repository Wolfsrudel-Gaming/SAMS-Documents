<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Ärztliches Attest als US-Arztschreiben (Physician’s Statement / Medical Certificate) auf Briefbogen.
 * Felder und gespeicherte Auswahlwerte wie attest.php; Einstellungen aus app/us_layouts haben Vorrang vor den Vorgaben hier.
 */
$cfg = array_replace(us_layout($type), array_diff_key([
    'style' => 'medical', 'form' => 'PHMC-MD 6001', 'seal' => 'rect', 'blocks' => ['hipaa'],
    'sig_caption' => dt('Ausstellende Ärztin / ausstellender Arzt'),
], us_layout_registry()[$type['key'] ?? ''] ?? []));
$recipient = trim((string) ($d['recipient'] ?? ''));
$subject = trim((string) ($d['subject'] ?? ''));
$purpose = opt_label($type, 'purpose', $d['purpose'] ?? '');
echo us_paper_open($doc, $draft, 'us-' . $cfg['style'] . ' us-med-letter');
?>
<?php if ($cfg['color'] !== ''): ?><style>.doc-wrap .paper.us { --us-accent: <?= e(preg_match('/^#[0-9a-f]{6}$/i', $cfg['color']) ? $cfg['color'] : '#0b4f9c') ?>; }</style><?php endif; ?>
<div class="us-body">
    <?= us_letterhead($doc, ['recipient' => $recipient, 'no_patient_address' => true, 'info' => [dt('Zur Vorlage bei') => $purpose]]) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['confidential'])), $doc, $cfg) ?>
    <h1 class="us-med-ltitle"><?= e(mb_strtoupper(dt($subject !== '' ? $subject : 'Ärztliches Attest'))) ?><small><?= e(dt('Ärztliche Bescheinigung (Physician’s Statement)')) ?></small></h1>
    <table class="us-med-re">
        <tr><th><?= e(dt('Betreff')) ?>:</th><td><b><?= e($p ? patient_name($p, true) : '') ?></b><?php if ($p): ?> · <?= e(dt('geb. am')) ?> <?= e(fmt_date($p['dob'] ?? '')) ?><?php if (($p['citizen_id'] ?? '') !== ''): ?> · <?= e(dt('Bürger-ID')) ?> <span class="mono"><?= e($p['citizen_id']) ?></span><?php endif; ?><?php endif; ?></td></tr>
        <tr><th><?= e(dt('Untersuchung am')) ?>:</th><td><?= e(fmt_date($d['exam_date'] ?? '')) ?></td></tr>
        <?php if (!empty($d['valid_until'])): ?><tr><th><?= e(dt('Gültig bis einschließlich')) ?>:</th><td><b><?= e(fmt_date($d['valid_until'])) ?></b></td></tr><?php endif; ?>
    </table>
    <p class="us-med-text"><?= e(dt('Sehr geehrte Damen und Herren,')) ?></p>
    <div class="us-med-text"><?= nl($d['body'] ?? '') ?></div>
    <?php if (trim((string) ($d['icd'] ?? '')) !== ''): ?>
        <p class="us-med-text small"><b><?= e(dt('Diagnose (auf ausdrücklichen Wunsch des Patienten angegeben):')) ?></b> <?= e($d['icd']) ?></p>
    <?php endif; ?>
    <p class="us-med-text"><?= e(dt('Ich bestätige, dass ich die oben genannte Person persönlich untersucht habe und die vorstehenden Angaben nach bestem ärztlichem Wissen zutreffen.')) ?></p>
    <p class="us-med-text"><?= e(dt('Mit freundlichen Grüßen')) ?></p>
    <?= us_signatures($doc, $cfg) ?>
    <?= us_blocks(array_values(array_intersect((array) $cfg['blocks'], ['notice', 'hipaa'])), $doc, $cfg) ?>
    <p class="small muted-print"><?= e(dt('Dieses Attest wurde aufgrund einer persönlichen ärztlichen Untersuchung ausgestellt. Es ist nur mit Unterschrift und Stempel gültig.')) ?></p>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
