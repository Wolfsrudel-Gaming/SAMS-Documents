<?php
defined('SAMS_ROOT') || exit;
/** Arbeitsvertrag – A4 mit Paragraphen und Unterschriften beider Seiten */
$emp = $d['_employee'] ?? [];
$empName = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''));
$fixed = !empty($d['fixed_until']);
$rot = doc_rand($doc, 'evsig', -4, 3);
$paras = [
    dt('Beginn und Dauer') => dt('Das Arbeitsverhältnis beginnt am {start}', ['start' => fmt_date($d['start'] ?? '')]) . ($fixed ? dt(' und ist befristet bis zum {until}.', ['until' => fmt_date($d['fixed_until'])]) : dt(' und wird auf unbestimmte Zeit geschlossen.'))
        . ((int) ($d['probation'] ?? 0) > 0 ? dt(' Die ersten {months} Monate gelten als Probezeit, während der das Arbeitsverhältnis mit einer Frist von zwei Wochen gekündigt werden kann.', ['months' => (int) $d['probation']]) : ''),
    dt('Tätigkeit') => dt('Der/die Arbeitnehmer/in wird als {position}{department} eingestellt. Der Arbeitgeber behält sich vor, ihm/ihr andere zumutbare Tätigkeiten entsprechend der Qualifikation zu übertragen.', [
        'position' => $d['position'] ?? '', 'department' => ($d['department'] ?? '') !== '' ? dt(' in der Abteilung {department}', ['department' => $d['department']]) : '']),
    dt('Arbeitszeit') => dt('Die regelmäßige Arbeitszeit beträgt {hours} Stunden wöchentlich. Der Dienst erfolgt nach Dienstplan im Schichtdienst einschließlich Nacht-, Wochenend- und Feiertagsdiensten sowie Rufbereitschaft.', ['hours' => (int) ($d['hours'] ?? 40) ?: 40]),
    dt('Vergütung') => dt('Der/die Arbeitnehmer/in erhält eine Vergütung von {salary}. Die Zahlung erfolgt bargeldlos auf ein vom Arbeitnehmer zu benennendes Konto.', ['salary' => ($d['salary'] ?? '') !== '' ? $d['salary'] : dt('gemäß der geltenden Vergütungsordnung')]),
    dt('Urlaub') => dt('Der Urlaubsanspruch beträgt {days} Arbeitstage im Kalenderjahr.', ['days' => (int) ($d['vacation'] ?? 30) ?: 30]),
    dt('Schweigepflicht') => dt('Der/die Arbeitnehmer/in ist verpflichtet, über alle ihm/ihr bei der Tätigkeit bekannt gewordenen Angelegenheiten, insbesondere über Patientendaten, Stillschweigen zu bewahren. Diese Pflicht besteht auch nach Beendigung des Arbeitsverhältnisses fort.'),
    dt('Dienstkleidung und Ausrüstung') => dt('Dienstkleidung, Dienstausweis und Ausrüstung werden gestellt, bleiben Eigentum des Arbeitgebers und sind bei Beendigung des Arbeitsverhältnisses unaufgefordert zurückzugeben.'),
    dt('Kündigung') => dt('Nach Ablauf der Probezeit kann das Arbeitsverhältnis mit einer Frist von {notice} gekündigt werden. Das Recht zur außerordentlichen Kündigung bleibt unberührt. Kündigungen bedürfen der Schriftform.', ['notice' => ($d['notice'] ?? '') !== '' ? $d['notice'] : dt('4 Wochen zum Monatsende')]),
];
if (trim((string) ($d['extra'] ?? '')) !== '') {
    $paras[dt('Besondere Vereinbarungen')] = (string) $d['extra'];
}
$paras[dt('Schlussbestimmungen')] = dt('Änderungen und Ergänzungen dieses Vertrages bedürfen der Schriftform. Sollte eine Bestimmung unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt. Es gilt das Recht des State of San Andreas.');
echo a4_open($doc, $draft);
echo doc_letterhead($doc, ['recipient' => $empName . "\n" . dt('– im Hause –'), 'no_patient_address' => true, 'department' => dt('Personalabteilung'), 'info' => [dt('Personal-Nr.') => $emp['badge_number'] ?? '']]);
?>
<div class="doc-body contract">
    <h1 class="doc-title center"><?= e(dt('Arbeitsvertrag')) ?></h1>
    <p><?= e(dt('Zwischen der')) ?> <strong><?= e(org('org_name')) ?></strong>, <?= e(org('org_street')) ?>, <?= e(org('org_city')) ?>, <?= e(dt('vertreten durch {name} – im Folgenden „Arbeitgeber“ –', ['name' => user_display($u)])) ?></p>
    <p><?= e(dt('und')) ?> <strong><?= e($empName) ?></strong><?= !empty($emp['badge_number']) ? ', ' . e(dt('Personal-Nr. {number}', ['number' => $emp['badge_number']])) : '' ?> <?= e(dt('– im Folgenden „Arbeitnehmer/in“ –')) ?></p>
    <p><?= e(dt('wird folgender Arbeitsvertrag geschlossen:')) ?></p>
    <?php $i = 1; foreach ($paras as $head => $text): ?>
        <h3 class="contract-h"><?= e(dt('§ {n}', ['n' => $i++])) ?> <?= e($head) ?></h3>
        <div class="prose"><?= nl($text) ?></div>
    <?php endforeach; ?>
    <p class="contract-place"><span class="hand-filled"><?= e(org('city')) ?>, <?= e(fmt_date($doc['issued_at'] ?? now())) ?></span></p>
    <div class="contract-signs">
        <?= doc_signature($doc, 'round', dt('Arbeitgeber'), false) ?>
        <div class="sigblock">
            <div class="sig-sign"><div class="sig-area"><?php if (!empty($d['employee_signature'])): ?><img class="sig-img" style="transform:rotate(<?= $rot ?>deg)" src="<?= e($d['employee_signature']) ?>" alt=""><?php endif; ?></div>
                <span class="sig-cap"><?= e(dt('Arbeitnehmer/in')) ?></span><span class="sig-name"><?= e($empName) ?></span></div>
        </div>
    </div>
</div>
<?= doc_footer($doc) ?>
<?= a4_close() ?>
