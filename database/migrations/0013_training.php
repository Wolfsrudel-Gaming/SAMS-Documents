<?php
/**
 * Etappe 5b: Ausbildung – Lehrgänge mit Modulen, Online-Prüfungen und
 * automatischen Zertifikaten.
 */
return [
    'description' => 'Ausbildung und Prüfungen',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $e = $s->engine();
        $s->exec("CREATE TABLE IF NOT EXISTS training_courses (
            id $pk, faction_id $fk NOT NULL DEFAULT 1, title $str NOT NULL, description $text,
            qualification_id $fk NULL, pass_percent INTEGER NOT NULL DEFAULT 80, question_count INTEGER NOT NULL DEFAULT 0,
            time_limit INTEGER NOT NULL DEFAULT 0, attempts_per_day INTEGER NOT NULL DEFAULT 3, require_modules INTEGER NOT NULL DEFAULT 1,
            certificate INTEGER NOT NULL DEFAULT 1, valid_months INTEGER NOT NULL DEFAULT 0, hours INTEGER NOT NULL DEFAULT 0,
            signer_id $fk NULL, active INTEGER NOT NULL DEFAULT 1, sort INTEGER NOT NULL DEFAULT 0,
            created_by $fk NULL, created_at $dt NOT NULL, updated_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS training_modules (
            id $pk, course_id $fk NOT NULL, title $str NOT NULL, content $text, sort INTEGER NOT NULL DEFAULT 0
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS training_questions (
            id $pk, course_id $fk NOT NULL, question $text, options $text, correct $text, explanation $text, sort INTEGER NOT NULL DEFAULT 0
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS training_progress (
            id $pk, user_id $fk NOT NULL, module_id $fk NOT NULL, read_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS training_attempts (
            id $pk, course_id $fk NOT NULL, user_id $fk NOT NULL, started_at $dt NOT NULL, finished_at $dt NULL,
            questions $text, answers $text, score INTEGER NOT NULL DEFAULT 0, passed INTEGER NOT NULL DEFAULT 0,
            overtime INTEGER NOT NULL DEFAULT 0, document_id $fk NULL
        ){$e}");
        $s->index('idx_progress_user', 'training_progress', 'user_id, module_id');
        $s->index('idx_attempts_user', 'training_attempts', 'user_id, course_id');
        $s->index('idx_questions_course', 'training_questions', 'course_id');

        // Beispiel-Lehrgang
        if (!(int) $s->db->query('SELECT COUNT(*) FROM training_courses')->fetchColumn()) {
            $now = date('Y-m-d H:i:s');
            $s->db->prepare('INSERT INTO training_courses (title, description, pass_percent, question_count, time_limit, hours, created_at, updated_at) VALUES (?, ?, 80, 5, 10, 2, ?, ?)')
                ->execute(['Basic Life Support (BLS)', 'Grundlagen der Herz-Lungen-Wiederbelebung für alle Mitarbeitenden. Beispiel-Lehrgang – frei anpassbar.', $now, $now]);
            $cid = (int) $s->db->lastInsertId();
            $m = $s->db->prepare('INSERT INTO training_modules (course_id, title, content, sort) VALUES (?, ?, ?, ?)');
            $m->execute([$cid, 'Erkennen des Kreislaufstillstands', "Prüfen: Bewusstsein (ansprechen, anfassen) und Atmung (sehen, hören, fühlen – max. 10 Sekunden).\n\n**Keine normale Atmung = Kreislaufstillstand.** Schnappatmung gilt nicht als normale Atmung.\n\nSofort Hilfe rufen bzw. Notruf 911 absetzen und einen AED anfordern.", 10]);
            $m->execute([$cid, 'Herzdruckmassage und Beatmung', "Druckpunkt: Mitte des Brustkorbs (untere Hälfte des Brustbeins).\nDrucktiefe 5–6 cm, Frequenz 100–120 pro Minute, vollständige Entlastung.\n\nVerhältnis **30 Kompressionen : 2 Beatmungen**. Unterbrechungen so kurz wie möglich halten.", 20]);
            $m->execute([$cid, 'AED', "AED einschalten und den Anweisungen folgen. Elektroden auf die freie Brust kleben.\nWährend der Analyse und Schockabgabe niemand berührt den Patienten.\nNach dem Schock sofort mit der Herzdruckmassage fortfahren.", 30]);
            $q = $s->db->prepare('INSERT INTO training_questions (course_id, question, options, correct, explanation, sort) VALUES (?, ?, ?, ?, ?, ?)');
            foreach ([
                ['Wie lange prüfst du höchstens die Atmung?', ['5 Sekunden', '10 Sekunden', '30 Sekunden', '1 Minute'], [1], 'Maximal 10 Sekunden, um keine Zeit zu verlieren.'],
                ['Welche Kompressionsfrequenz ist richtig?', ['60–80 pro Minute', '80–100 pro Minute', '100–120 pro Minute', '140–160 pro Minute'], [2], 'Empfohlen sind 100–120 Kompressionen pro Minute.'],
                ['Wie ist das Verhältnis von Kompressionen zu Beatmungen beim Erwachsenen?', ['15:2', '30:2', '30:1', '5:1'], [1], '30 Kompressionen, dann 2 Beatmungen.'],
                ['Wie tief wird der Brustkorb eingedrückt?', ['2–3 cm', '5–6 cm', '8–10 cm'], [1], '5–6 cm beim Erwachsenen.'],
                ['Was gilt beim Einsatz eines AED? (Mehrere Antworten möglich)', ['Während der Analyse niemand berührt den Patienten', 'Nach dem Schock sofort weiter drücken', 'Vor dem Schock 2 Minuten warten', 'Elektroden auf die freie Brust kleben'], [0, 1, 3], 'Nur „2 Minuten warten“ ist falsch.'],
                ['Was bedeutet Schnappatmung?', ['Der Patient atmet normal', 'Sie ist ein Zeichen des Kreislaufstillstands', 'Der Patient schläft'], [1], 'Schnappatmung ist keine normale Atmung – sofort mit der Wiederbelebung beginnen.'],
            ] as $i => [$question, $opts, $correct, $expl]) {
                $q->execute([$cid, $question, json_encode($opts, JSON_UNESCAPED_UNICODE), json_encode($correct), $expl, ($i + 1) * 10]);
            }
        }

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach (['training.view' => 'duty.clock', 'training.manage' => 'users.edit'] as $perm => $basis) {
            foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = '$basis'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
