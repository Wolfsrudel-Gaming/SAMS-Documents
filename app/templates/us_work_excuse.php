<?php
defined('SAMS_ROOT') || exit;
/** US-Vorlagenset: Arbeits- / Schulbefreiung (Work / School Excuse, statt AU-Bescheinigung). Die Diagnose steht nicht auf dem Schreiben (HIPAA). */
$rehab = (array) ($d['rehab'] ?? []);
$since = (string) ($d['au_since'] ?? '');
$until = (string) ($d['au_until'] ?? '');
$back = $until !== '' ? date('Y-m-d', strtotime($until . ' +1 day')) : '';
$name = $p ? patient_name($p) : '';
echo a4_open($doc, $draft);
echo doc_letterhead($doc, ['recipient' => (string) ($d['employer'] ?? ''), 'no_patient_address' => true]);
?>
<div class="doc-body">
    <h1 class="doc-title"><?= e(dt('Arbeits- / Schulbefreiung')) ?><small><?= e(($d['kind'] ?? '') === 'folge' ? dt('Fortsetzung einer vorherigen Befreiung') : dt('Erstbefreiung')) ?></small></h1>
    <?= doc_patient_box($doc, false) ?>
    <p class="prose"><?= e(dt('Zur Vorlage beim Arbeitgeber bzw. bei der Schule:')) ?></p>
    <p class="prose"><?= strtr(e(dt('Hiermit wird bescheinigt, dass {name} am {date} in unserer Einrichtung untersucht wurde.')), ['{name}' => '<strong>' . e($name) . '</strong>', '{date}' => '<strong>' . e(fmt_date($d['au_found'] ?? '')) . '</strong>']) ?>
        <?= strtr(e(dt('Aus medizinischen Gründen ist der Patient / die Patientin vom {since} bis einschließlich {until} arbeits- bzw. schulunfähig')), ['{since}' => '<strong>' . e(fmt_date($since)) . '</strong>', '{until}' => '<strong>' . e(fmt_date($until)) . '</strong>']) ?><?php if ($back !== ''): ?><?= strtr(e(dt(' und kann die Arbeit bzw. den Schulbesuch am {back} wieder aufnehmen, sofern nichts anderes angeordnet wird')), ['{back}' => '<strong>' . e(fmt_date($back)) . '</strong>']) ?><?php endif; ?>.</p>
    <table class="tbl two">
        <tr><th><?= e(dt('Arbeitsbedingte Verletzung oder Erkrankung')) ?></th><td><?= e(!empty($d['accident']) ? dt('Ja – Fall der Arbeitsunfallversicherung') : dt('Nein')) ?></td></tr>
        <?php if (!empty($d['d_doctor'])): ?><tr><th><?= e(dt('Arbeitsmedizin')) ?></th><td><?= e(dt('Überwiesen an Arbeitsmediziner/in')) ?></td></tr><?php endif; ?>
        <?php if (!empty($d['other_accident'])): ?><tr><th><?= e(dt('Unfall')) ?></th><td><?= e(dt('Unfall / Verletzung außerhalb der Arbeit')) ?></td></tr><?php endif; ?>
        <?php if (in_array('reha', $rehab, true)): ?><tr><th><?= e(dt('Empfehlung')) ?></th><td><?= e(dt('Rehabilitation / Physiotherapie empfohlen')) ?></td></tr><?php endif; ?>
        <?php if (in_array('wiedereingliederung', $rehab, true)): ?><tr><th><?= e(dt('Rückkehr an den Arbeitsplatz')) ?></th><td><?= e(dt('Stufenweise Wiedereingliederung (angepasste Tätigkeit) empfohlen')) ?></td></tr><?php endif; ?>
    </table>
    <p class="small muted-print"><?= e(dt('Die Diagnose ist vertraulich und wird Arbeitgebern oder Schulen nicht mitgeteilt (HIPAA). Rückfragen zu dieser Bescheinigung richten Sie bitte an die ausstellende Stelle. Die Echtheit lässt sich mit dem unten stehenden Code prüfen.')) ?></p>
    <?= doc_signature($doc, 'rect') ?>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
