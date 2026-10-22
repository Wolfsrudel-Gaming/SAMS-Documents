<?php
defined('SAMS_ROOT') || exit;
/** Angaben, die auf Dokumenten und im Arztstempel erscheinen. Erwartet $data, optional $readonly. */
$ro = !empty($readonly) ? ' disabled' : '';
?>
<div class="field w-sixth"><label>Titel</label><input name="title" value="<?= e($data['title'] ?? '') ?>" placeholder="Dr. med." list="dl-titles"<?= $ro ?>></div>
<div class="field w-third"><label>Name <span class="req">*</span></label><input name="name" value="<?= e($data['name'] ?? '') ?>" required placeholder="Vorname Nachname"<?= $ro ?>></div>
<?php if (faction_kind() === 'medical'): ?>
<div class="field w-half"><label>Fachrichtung (für Stempel)</label><input name="department" value="<?= e($data['department'] ?? '') ?>" placeholder="z. B. Facharzt für Allgemeinmedizin" list="dl-departments"<?= $ro ?>></div>
<div class="field w-third"><label>Arzt-Nr. (LANR)</label><input name="doctor_number" value="<?= e($data['doctor_number'] ?? '') ?>" placeholder="9-stellig, nur Ärzt/innen"<?= $ro ?>></div>
<?php else: ?>
<div class="field w-half"><label>Abteilung / Dienststelle (für Stempel)</label><input name="department" value="<?= e($data['department'] ?? '') ?>" placeholder="leer = erste zugeordnete Abteilung"<?= $ro ?>></div>
<input type="hidden" name="doctor_number" value="<?= e($data['doctor_number'] ?? '') ?>">
<?php endif; ?>
<div class="field w-third"><label>Telefon</label><input name="phone" value="<?= e($data['phone'] ?? '') ?>"<?= $ro ?>></div>
<datalist id="dl-titles"><option value="Dr. med."><option value="Prof. Dr. med."><option value="Dr. med. dent."><option value="Dipl.-Psych."><option value="Dr."></datalist>
