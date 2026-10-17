<?php
defined('SAMS_ROOT') || exit;
/** Arbeitsvertrag – A4 mit Paragraphen und Unterschriften beider Seiten */
$emp = $d['_employee'] ?? [];
$empName = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''));
$fixed = !empty($d['fixed_until']);
$rot = doc_rand($doc, 'evsig', -4, 3);
$paras = [
    'Beginn und Dauer' => 'Das Arbeitsverhältnis beginnt am ' . fmt_date($d['start'] ?? '') . ($fixed ? ' und ist befristet bis zum ' . fmt_date($d['fixed_until']) . '.' : ' und wird auf unbestimmte Zeit geschlossen.')
        . ((int) ($d['probation'] ?? 0) > 0 ? ' Die ersten ' . (int) $d['probation'] . ' Monate gelten als Probezeit, während der das Arbeitsverhältnis mit einer Frist von zwei Wochen gekündigt werden kann.' : ''),
    'Tätigkeit' => 'Der/die Arbeitnehmer/in wird als ' . ($d['position'] ?? '') . (($d['department'] ?? '') !== '' ? ' in der Abteilung ' . $d['department'] : '') . ' eingestellt. Der Arbeitgeber behält sich vor, ihm/ihr andere zumutbare Tätigkeiten entsprechend der Qualifikation zu übertragen.',
    'Arbeitszeit' => 'Die regelmäßige Arbeitszeit beträgt ' . ((int) ($d['hours'] ?? 40) ?: 40) . ' Stunden wöchentlich. Der Dienst erfolgt nach Dienstplan im Schichtdienst einschließlich Nacht-, Wochenend- und Feiertagsdiensten sowie Rufbereitschaft.',
    'Vergütung' => 'Der/die Arbeitnehmer/in erhält eine Vergütung von ' . (($d['salary'] ?? '') !== '' ? $d['salary'] : 'gemäß der geltenden Vergütungsordnung') . '. Die Zahlung erfolgt bargeldlos auf ein vom Arbeitnehmer zu benennendes Konto.',
    'Urlaub' => 'Der Urlaubsanspruch beträgt ' . ((int) ($d['vacation'] ?? 30) ?: 30) . ' Arbeitstage im Kalenderjahr.',
    'Schweigepflicht' => 'Der/die Arbeitnehmer/in ist verpflichtet, über alle ihm/ihr bei der Tätigkeit bekannt gewordenen Angelegenheiten, insbesondere über Patientendaten, Stillschweigen zu bewahren. Diese Pflicht besteht auch nach Beendigung des Arbeitsverhältnisses fort.',
    'Dienstkleidung und Ausrüstung' => 'Dienstkleidung, Dienstausweis und Ausrüstung werden gestellt, bleiben Eigentum des Arbeitgebers und sind bei Beendigung des Arbeitsverhältnisses unaufgefordert zurückzugeben.',
    'Kündigung' => 'Nach Ablauf der Probezeit kann das Arbeitsverhältnis mit einer Frist von ' . (($d['notice'] ?? '') !== '' ? $d['notice'] : '4 Wochen zum Monatsende') . ' gekündigt werden. Das Recht zur außerordentlichen Kündigung bleibt unberührt. Kündigungen bedürfen der Schriftform.',
];
if (trim((string) ($d['extra'] ?? '')) !== '') {
    $paras['Besondere Vereinbarungen'] = (string) $d['extra'];
}
$paras['Schlussbestimmungen'] = 'Änderungen und Ergänzungen dieses Vertrages bedürfen der Schriftform. Sollte eine Bestimmung unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt. Es gilt das Recht des State of San Andreas.';
echo a4_open($doc, $draft);
echo doc_letterhead($doc, ['recipient' => $empName . "\n– im Hause –", 'no_patient_address' => true, 'department' => 'Personalabteilung', 'info' => ['Personal-Nr.' => $emp['badge_number'] ?? '']]);
?>
<div class="doc-body contract">
    <h1 class="doc-title center">Arbeitsvertrag</h1>
    <p>Zwischen der <strong><?= e(org('org_name')) ?></strong>, <?= e(org('org_street')) ?>, <?= e(org('org_city')) ?>, vertreten durch <?= e(user_display($u)) ?> – im Folgenden „Arbeitgeber“ –</p>
    <p>und <strong><?= e($empName) ?></strong><?= !empty($emp['badge_number']) ? ', Personal-Nr. ' . e($emp['badge_number']) : '' ?> – im Folgenden „Arbeitnehmer/in“ –</p>
    <p>wird folgender Arbeitsvertrag geschlossen:</p>
    <?php $i = 1; foreach ($paras as $head => $text): ?>
        <h3 class="contract-h">§ <?= $i++ ?> <?= e($head) ?></h3>
        <div class="prose"><?= nl($text) ?></div>
    <?php endforeach; ?>
    <p class="contract-place"><span class="hand-filled"><?= e(org('city')) ?>, <?= e(fmt_date($doc['issued_at'] ?? now())) ?></span></p>
    <div class="contract-signs">
        <?= doc_signature($doc, 'round', 'Arbeitgeber', false) ?>
        <div class="sigblock">
            <div class="sig-sign"><div class="sig-area"><?php if (!empty($d['employee_signature'])): ?><img class="sig-img" style="transform:rotate(<?= $rot ?>deg)" src="<?= e($d['employee_signature']) ?>" alt=""><?php endif; ?></div>
                <span class="sig-cap">Arbeitnehmer/in</span><span class="sig-name"><?= e($empName) ?></span></div>
        </div>
    </div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
