<?php
/**
 * Anbindung Sky Ambulance (sky_ambulancejob): Vitalwerte aus dem Spiel (Vitalverlauf der Akte),
 * Rechnungen über Sky Billing (Rechnungs-ID als Text).
 */
return [
    'description' => 'Sky Ambulance: Vitalwerte, Sky Billing',
    'up' => function (Schema $s): void {
        $fk = $s->fk();
        $s->exec("CREATE TABLE IF NOT EXISTS patient_vitals (
            id {$s->pk()}, faction_id $fk NOT NULL DEFAULT 1, patient_id $fk NOT NULL, heart_rate INTEGER NULL, systolic INTEGER NULL, diastolic INTEGER NULL,
            spo2 INTEGER NULL, blood_sugar INTEGER NULL, cardiac_arrest INTEGER NOT NULL DEFAULT 0, activity {$s->str(16)} DEFAULT '',
            source {$s->str(16)} NOT NULL DEFAULT 'sky', recorded_by $fk NULL, recorded_at {$s->datetime()} NOT NULL
        ){$s->engine()}");
        $s->index('idx_patient_vitals', 'patient_vitals', 'patient_id, recorded_at');
        $s->addColumn('game_bills', 'ext_ref', $s->str(64) . " DEFAULT ''");
    },
];
