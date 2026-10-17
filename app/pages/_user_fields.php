<?php
defined('SAMS_ROOT') || exit;
/** Gemeinsame Profilfelder für Benutzerverwaltung und eigenes Profil. Erwartet $data */
?>
<div class="field w-sixth"><label>Titel</label><input name="title" value="<?= e($data['title']) ?>" placeholder="Dr. med." list="dl-titles"></div>
<div class="field w-third"><label>Name <span class="req">*</span></label><input name="name" value="<?= e($data['name']) ?>" required placeholder="Vorname Nachname"></div>
<div class="field w-half"><label>Fachrichtung / Abteilung</label><input name="department" value="<?= e($data['department']) ?>" placeholder="Facharzt für Allgemeinmedizin" list="dl-departments"></div>
<div class="field w-third"><label>Dienstgrad / Funktion</label><input name="rank_name" value="<?= e($data['rank_name']) ?>" placeholder="z.B. Oberarzt, Notfallsanitäter" list="dl-ranks"></div>
<div class="field w-sixth"><label>Dienstnummer</label><input name="badge_number" value="<?= e($data['badge_number']) ?>"></div>
<div class="field w-sixth"><label>Arzt-Nr. (LANR)</label><input name="doctor_number" value="<?= e($data['doctor_number']) ?>" placeholder="9-stellig"></div>
<div class="field w-third"><label>Telefon</label><input name="phone" value="<?= e($data['phone']) ?>"></div>
<datalist id="dl-titles"><option value="Dr. med."><option value="Prof. Dr. med."><option value="Dr. med. dent."><option value="Dipl.-Psych."><option value="Dr."></datalist>
<datalist id="dl-ranks"><option value="Ärztliche/r Direktor/in"><option value="Chefarzt / Chefärztin"><option value="Oberarzt / Oberärztin"><option value="Facharzt / Fachärztin"><option value="Assistenzarzt / Assistenzärztin"><option value="Notarzt / Notärztin"><option value="Leitende/r Notfallsanitäter/in"><option value="Notfallsanitäter/in"><option value="Rettungssanitäter/in"><option value="Rettungshelfer/in"><option value="Auszubildende/r"><option value="Psychologe / Psychologin"><option value="Verwaltung"></datalist>
