<?php
defined('SAMS_ROOT') || exit;
/**
 * US-Gestaltung: Arbeitsvertrag als US-Arbeitsvereinbarung (Employment Agreement) – Eckdaten-Übersicht,
 * nummerierte Abschnitte mit „At-will“-Klausel, Unterschriften beider Parteien. Gleiche Felder wie app/templates/arbeitsvertrag.php.
 */
$emp = $d['_employee'] ?? [];
$empName = trim(($emp['title'] ?? '') . ' ' . ($emp['name'] ?? ''));
$fixed = !empty($d['fixed_until']);
$probation = (int) ($d['probation'] ?? 0);
$hours = (int) ($d['hours'] ?? 40) ?: 40;
$vacation = (int) ($d['vacation'] ?? 30) ?: 30;
$salary = ($d['salary'] ?? '') !== '' ? (string) $d['salary'] : dt('gemäß der geltenden Vergütungsordnung');
$notice = ($d['notice'] ?? '') !== '' ? dt((string) $d['notice']) : dt('4 Wochen zum Monatsende');
$rot = doc_rand($doc, 'evsig', -4, 3);
$sections = [
    'Position und Aufgaben (Position and Duties)' => dt('Der/die Arbeitnehmer/in wird als {position}{department} beschäftigt und berichtet an die jeweils zuständige Leitung. Der Arbeitgeber kann ihm/ihr andere zumutbare Aufgaben entsprechend der Qualifikation übertragen.', [
        'position' => $d['position'] ?? '', 'department' => ($d['department'] ?? '') !== '' ? dt(' in der Abteilung {department}', ['department' => $d['department']]) : '']),
    'Beginn und Laufzeit (Term of Employment)' => dt('Das Arbeitsverhältnis beginnt am {start}', ['start' => fmt_date($d['start'] ?? '')]) . ($fixed ? dt(' und ist befristet bis zum {until}.', ['until' => fmt_date($d['fixed_until'])]) : dt(' und wird auf unbestimmte Zeit geschlossen.'))
        . ($probation > 0 ? ' ' . dt('Die ersten {months} Monate gelten als Einarbeitungszeit (Introductory Period); in dieser Zeit kann das Arbeitsverhältnis von beiden Seiten mit einer Frist von zwei Wochen beendet werden.', ['months' => $probation]) : ''),
    'Arbeitszeit (Work Schedule)' => dt('Die regelmäßige Arbeitszeit beträgt {hours} Stunden wöchentlich. Der Dienst erfolgt nach Dienstplan im Schichtdienst einschließlich Nacht-, Wochenend- und Feiertagsdiensten sowie Rufbereitschaft.', ['hours' => $hours]),
    'Vergütung (Compensation)' => dt('Der/die Arbeitnehmer/in erhält eine Vergütung von {salary}. Die Zahlung erfolgt bargeldlos auf ein vom Arbeitnehmer zu benennendes Konto.', ['salary' => $salary]),
    'Urlaub (Paid Time Off)' => dt('Der Urlaubsanspruch beträgt {days} Arbeitstage im Kalenderjahr.', ['days' => $vacation]),
    'Vertraulichkeit (Confidentiality)' => dt('Der/die Arbeitnehmer/in ist verpflichtet, über alle ihm/ihr bei der Tätigkeit bekannt gewordenen Angelegenheiten, insbesondere über Patientendaten, Stillschweigen zu bewahren. Diese Pflicht besteht auch nach Beendigung des Arbeitsverhältnisses fort.'),
    'Dienstkleidung und Eigentum des Arbeitgebers (Company Property)' => dt('Dienstkleidung, Dienstausweis und Ausrüstung werden gestellt, bleiben Eigentum des Arbeitgebers und sind bei Beendigung des Arbeitsverhältnisses unaufgefordert zurückzugeben.'),
    'Beendigung (At-Will Employment)' => dt('Das Arbeitsverhältnis besteht „at will“: Beide Parteien können es jederzeit mit oder ohne Angabe von Gründen beenden. Die Parteien vereinbaren hierfür jedoch eine Kündigungsfrist von {notice}; die Kündigung bedarf der Schriftform. Das Recht zur fristlosen Beendigung aus wichtigem Grund bleibt unberührt. Mündliche Zusagen über eine bestimmte Beschäftigungsdauer sind unwirksam.', ['notice' => $notice]),
];
if (trim((string) ($d['extra'] ?? '')) !== '') {
    $sections['Besondere Vereinbarungen (Special Provisions)'] = (string) $d['extra'];
}
$sections['Schlussbestimmungen (Entire Agreement; Governing Law)'] = dt('Diese Vereinbarung enthält alle Absprachen der Parteien. Änderungen und Ergänzungen bedürfen der Schriftform. Sollte eine Bestimmung unwirksam sein, bleibt die Wirksamkeit der übrigen Bestimmungen unberührt. Es gilt das Recht des Staates San Andreas.');
$cfg = ['style' => 'hr', 'title' => 'Arbeitsvertrag (Employment Agreement)', 'subtitle' => '', 'banner' => ''] + us_layout($type);
if ($cfg['form'] === '') {
    $cfg['form'] = mb_strtoupper(org('org_short')) . ' HR 10';
}
echo us_paper_open($doc, $draft, 'us-hr us-gen-contract');
?>
<div class="us-body">
    <?= us_header($doc, $cfg, $type) ?>
    <p class="us-gen-contract-pre"><?= e(dt('Diese Arbeitsvereinbarung wird geschlossen zwischen der {org}, {street}, {city}, vertreten durch {name} – im Folgenden „Arbeitgeber“ –, und {employee}{badge} – im Folgenden „Arbeitnehmer/in“.', [
        'org' => org('org_name'), 'street' => org('org_street'), 'city' => org('org_city'), 'name' => user_display($u),
        'employee' => $empName !== '' ? $empName : '__________', 'badge' => !empty($emp['badge_number']) ? ', ' . dt('Personal-Nr. {number}', ['number' => $emp['badge_number']]) : '',
    ])) ?></p>
    <div class="us-sec"><?= e(mb_strtoupper(dt('Eckdaten (Key Terms)'))) ?></div>
    <div class="us-grid">
        <div class="us-cell w4"><span class="fl"><?= e(dt('Tätigkeit / Rang')) ?></span><span class="fv"><?= e($d['position'] ?? '') ?: '—' ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e(dt('Abteilung')) ?></span><span class="fv"><?= e($d['department'] ?? '') ?: '—' ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e(dt('Beschäftigungsart')) ?></span><span class="fv"><span class="us-opts"><span class="us-opt"><?= box(!$fixed) ?> <?= e(dt('unbefristet')) ?></span><span class="us-opt"><?= box($fixed) ?> <?= e(dt('befristet')) ?></span></span></span></div>
        <div class="us-cell w3"><span class="fl"><?= e(dt('Beginn')) ?></span><span class="fv"><?= e(fmt_date($d['start'] ?? '')) ?: '—' ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e(dt('Befristet bis')) ?></span><span class="fv"><?= $fixed ? e(fmt_date($d['fixed_until'])) : '—' ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e(dt('Einarbeitungszeit')) ?></span><span class="fv"><?= $probation > 0 ? e(dt('{months} Monate', ['months' => $probation])) : '—' ?></span></div>
        <div class="us-cell w3"><span class="fl"><?= e(dt('Wochenstunden')) ?></span><span class="fv"><?= $hours ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e(dt('Vergütung')) ?></span><span class="fv"><?= e($salary) ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e(dt('Urlaubstage / Jahr')) ?></span><span class="fv"><?= $vacation ?></span></div>
        <div class="us-cell w4"><span class="fl"><?= e(dt('Kündigungsfrist')) ?></span><span class="fv"><?= e($notice) ?></span></div>
    </div>
    <div class="us-gen-contract-body">
        <?php $i = 1; foreach ($sections as $head => $text): ?>
            <div class="us-gen-clause"><h3 class="us-gen-h"><?= $i++ ?>. <?= e(dt($head)) ?></h3><div class="us-gen-prose"><?= nl($text) ?></div></div>
        <?php endforeach; ?>
    </div>
    <p class="us-gen-witness"><?= e(dt('Zur Bestätigung haben die Parteien diese Vereinbarung am unten genannten Datum unterzeichnet.')) ?></p>
    <div class="us-gen-contract-signs">
        <div class="us-gen-party-sig">
            <span class="fl"><?= e(dt('Für den Arbeitgeber')) ?></span>
            <div class="us-sig-area"><div class="sig-stamp round"><?= seal_svg($doc) ?></div><?= signature_html($u, doc_rand($doc, 'ussig', -4, 3)) ?></div>
            <span class="us-sig-line"></span>
            <span class="us-sig-name"><b><?= e(user_display($u)) ?></b><?= !empty($u['rank_name']) ? ', ' . e($u['rank_name']) : '' ?></span>
            <span class="us-sig-name"><?= e(org('org_name')) ?> · <?= e(dt('Datum')) ?>: <?= e(fmt_date($doc['issued_at'] ?? now())) ?></span>
        </div>
        <div class="us-gen-party-sig">
            <span class="fl"><?= e(dt('Arbeitnehmer/in')) ?></span>
            <div class="us-sig-area"><?php if (!empty($d['employee_signature'])): ?><img class="sig-img" style="transform:rotate(<?= $rot ?>deg)" src="<?= e($d['employee_signature']) ?>" alt=""><?php endif; ?></div>
            <span class="us-sig-line"></span>
            <span class="us-sig-name"><b><?= e($empName) ?></b><?= !empty($emp['badge_number']) ? ' · ' . e(dt('Personal-Nr. {number}', ['number' => $emp['badge_number']])) : '' ?></span>
            <span class="us-sig-name"><?= e(dt('Datum')) ?>: ____________</span>
        </div>
    </div>
    <?php if (!empty($u['cosigner'])): $c = $u['cosigner']; ?>
    <div class="us-gen-cosign-line"><?= e(dt('Gegengezeichnet / freigegeben am {date}', ['date' => fmt_date($c['at'] ?? '')])) ?>: <?= signature_html($c, doc_rand($doc, 'uscosig', -3, 3)) ?> <?= e(user_display($c)) ?></div>
    <?php endif; ?>
</div>
<?= us_footer($doc, $cfg, $type) ?>
<?= a4_close() ?>
