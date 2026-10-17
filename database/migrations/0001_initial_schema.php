<?php
/** Ausgangsschema (Stand Version 1.0). */
return [
    'description' => 'Grundtabellen: Einstellungen, Benutzer, Patienten, Einträge, Dokumente, Protokoll',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $engine = $s->engine();

        $s->exec("CREATE TABLE IF NOT EXISTS settings (
                    name $str NOT NULL PRIMARY KEY,
                    value $text
                ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS users (
                    id $pk,
                    username $str NOT NULL UNIQUE,
                    password_hash $str NOT NULL,
                    name $str NOT NULL,
                    title $str DEFAULT '',
                    role $str NOT NULL DEFAULT 'medic',
                    rank_name $str DEFAULT '',
                    department $str DEFAULT '',
                    badge_number $str DEFAULT '',
                    doctor_number $str DEFAULT '',
                    phone $str DEFAULT '',
                    signature $text,
                    active INTEGER NOT NULL DEFAULT 1,
                    last_login $dt NULL,
                    created_at $dt NOT NULL
                ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS patients (
                    id $pk,
                    citizen_id $str DEFAULT '',
                    first_name $str NOT NULL,
                    last_name $str NOT NULL,
                    dob $str DEFAULT '',
                    gender $str DEFAULT '',
                    phone $str DEFAULT '',
                    address $text,
                    occupation $str DEFAULT '',
                    employer $str DEFAULT '',
                    insurance $str DEFAULT '',
                    insurance_number $str DEFAULT '',
                    blood_type $str DEFAULT '',
                    height $str DEFAULT '',
                    weight $str DEFAULT '',
                    allergies $text,
                    conditions $text,
                    medication $text,
                    emergency_contact $text,
                    organ_donor $str DEFAULT '',
                    living_will INTEGER NOT NULL DEFAULT 0,
                    flags $text,
                    notes $text,
                    deceased_at $str DEFAULT '',
                    created_by $fk NULL,
                    created_at $dt NOT NULL,
                    updated_at $dt NOT NULL
                ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS entries (
                    id $pk,
                    patient_id $fk NOT NULL,
                    type $str NOT NULL,
                    title $str NOT NULL,
                    content $text,
                    confidential INTEGER NOT NULL DEFAULT 0,
                    occurred_at $dt NOT NULL,
                    created_by $fk NULL,
                    created_at $dt NOT NULL,
                    updated_at $dt NOT NULL,
                    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE
                ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS documents (
                    id $pk,
                    number $str NOT NULL DEFAULT '',
                    type $str NOT NULL,
                    patient_id $fk NULL,
                    data $text,
                    patient_snapshot $text,
                    issuer_snapshot $text,
                    verify_code $str NOT NULL UNIQUE,
                    status $str NOT NULL DEFAULT 'valid',
                    revoked_reason $text,
                    revoked_by $fk NULL,
                    revoked_at $dt NULL,
                    issued_by $fk NULL,
                    issued_at $dt NOT NULL,
                    valid_until $str DEFAULT '',
                    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE SET NULL
                ){$engine}");
        $s->exec("CREATE TABLE IF NOT EXISTS audit_log (
                    id $pk,
                    user_id $fk NULL,
                    action $str NOT NULL,
                    object_type $str DEFAULT '',
                    object_id $fk NULL,
                    details $text,
                    ip $str DEFAULT '',
                    created_at $dt NOT NULL
                ){$engine}");

        $s->index('idx_patients_name', 'patients', 'last_name, first_name');
        $s->index('idx_entries_patient', 'entries', 'patient_id');
        $s->index('idx_documents_patient', 'documents', 'patient_id');
        $s->index('idx_documents_number', 'documents', 'number');
        $s->index('idx_audit_created', 'audit_log', 'created_at');
    },
];
