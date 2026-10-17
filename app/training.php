<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Ausbildung: Lehrgänge mit Lernmodulen und Online-Prüfung. Fragen und
 * Antwortreihenfolge werden pro Versuch zufällig gezogen und auf dem Server
 * bewertet. Bestanden → Qualifikation, Laufbahn-Eintrag und Zertifikat.
 */

function course_modules(int $courseId): array
{
    return all('SELECT * FROM training_modules WHERE course_id = ? ORDER BY sort, id', [$courseId]);
}

function course_questions(int $courseId): array
{
    return array_map(function ($q) {
        $q['options'] = json_decode((string) $q['options'], true) ?: [];
        $q['correct'] = array_map('intval', json_decode((string) $q['correct'], true) ?: []);
        return $q;
    }, all('SELECT * FROM training_questions WHERE course_id = ? ORDER BY sort, id', [$courseId]));
}

/** IDs der gelesenen Module eines Lehrgangs. */
function modules_read(int $userId, int $courseId): array
{
    return array_map('intval', array_column(all('SELECT p.module_id FROM training_progress p JOIN training_modules m ON m.id = p.module_id
        WHERE p.user_id = ? AND m.course_id = ?', [$userId, $courseId]), 'module_id'));
}

function attempts_today(int $userId, int $courseId): int
{
    return (int) scalar('SELECT COUNT(*) FROM training_attempts WHERE user_id = ? AND course_id = ? AND started_at >= ?', [$userId, $courseId, date('Y-m-d 00:00:00')]);
}

/** Bestandener, noch gültiger Versuch (für die Statusanzeige). */
function course_passed(int $userId, array $course): ?array
{
    $a = one('SELECT * FROM training_attempts WHERE user_id = ? AND course_id = ? AND passed = 1 ORDER BY finished_at DESC LIMIT 1', [$userId, $course['id']]);
    if ($a && (int) $course['valid_months'] > 0 && strtotime($a['finished_at'] . ' +' . (int) $course['valid_months'] . ' months') < time()) {
        return null; // abgelaufen → Rezertifizierung nötig
    }
    return $a;
}

/** Kann die Prüfung jetzt gestartet werden? Gibt einen Hinderungsgrund oder '' zurück. */
function exam_blocker(array $course, int $userId): string
{
    $modules = course_modules((int) $course['id']);
    if (!course_questions((int) $course['id'])) {
        return 'Für diesen Lehrgang sind noch keine Prüfungsfragen hinterlegt.';
    }
    if ((int) $course['require_modules'] && count(array_diff(array_map('intval', array_column($modules, 'id')), modules_read($userId, (int) $course['id'])))) {
        return 'Bitte zuerst alle Lernmodule durcharbeiten.';
    }
    if ((int) $course['attempts_per_day'] > 0 && attempts_today($userId, (int) $course['id']) >= (int) $course['attempts_per_day']) {
        return 'Du hast heute schon ' . (int) $course['attempts_per_day'] . ' Versuche gebraucht. Morgen geht es weiter.';
    }
    return '';
}

/** Neuen Versuch anlegen: zufällige Fragen, zufällige Antwortreihenfolge. */
function start_attempt(array $course, int $userId): int
{
    $questions = course_questions((int) $course['id']);
    shuffle($questions);
    if ((int) $course['question_count'] > 0) {
        $questions = array_slice($questions, 0, (int) $course['question_count']);
    }
    $plan = [];
    foreach ($questions as $q) {
        $order = array_keys($q['options']);
        shuffle($order);
        $plan[] = ['id' => (int) $q['id'], 'order' => $order];
    }
    return insert('training_attempts', ['course_id' => $course['id'], 'user_id' => $userId, 'started_at' => now(), 'questions' => json_encode($plan)]);
}

/**
 * Versuch bewerten. $answers: [question_id => [Original-Indizes]].
 * Eine Frage zählt nur, wenn genau die richtigen Antworten gewählt wurden.
 */
function grade_attempt(array $attempt, array $course, array $answers): array
{
    $all = array_column(course_questions((int) $course['id']), null, 'id');
    $plan = json_decode((string) $attempt['questions'], true) ?: [];
    $right = 0;
    $clean = [];
    foreach ($plan as $p) {
        $q = $all[$p['id']] ?? null;
        if (!$q) {
            continue;
        }
        $chosen = array_values(array_unique(array_filter(array_map('intval', (array) ($answers[$p['id']] ?? [])), fn($i) => isset($q['options'][$i]))));
        sort($chosen);
        $correct = $q['correct'];
        sort($correct);
        $clean[$p['id']] = $chosen;
        $right += $chosen === $correct ? 1 : 0;
    }
    $score = $plan ? (int) floor($right / count($plan) * 100) : 0;
    $overtime = (int) $course['time_limit'] > 0 && time() > strtotime($attempt['started_at']) + (int) $course['time_limit'] * 60 + 60;
    return ['score' => $score, 'passed' => !$overtime && $score >= (int) $course['pass_percent'], 'answers' => $clean, 'overtime' => $overtime];
}

/** Nach bestandener Prüfung: Qualifikation, Laufbahn, Zertifikat. Gibt die Dokument-ID zurück. */
function complete_course(array $course, array $user, int $score): ?int
{
    $uid = (int) $user['id'];
    if ($course['qualification_id'] && !scalar('SELECT COUNT(*) FROM user_qualifications WHERE user_id = ? AND qualification_id = ?', [$uid, $course['qualification_id']])) {
        insert('user_qualifications', ['user_id' => $uid, 'qualification_id' => $course['qualification_id']]);
    }
    $docId = null;
    $type = doc_type('ausbildungsnachweis');
    $signer = $course['signer_id'] ? one('SELECT * FROM users WHERE id = ? AND active = 1', [$course['signer_id']]) : null;
    $signer = $signer ?: ($course['created_by'] ? one('SELECT * FROM users WHERE id = ? AND active = 1', [$course['created_by']]) : null);
    if ((int) $course['certificate'] && $type && $signer) {
        $data = [
            'course' => $course['title'], 'date_from' => '', 'date_to' => date('Y-m-d'), 'hours' => (int) $course['hours'] ?: '',
            'valid_until' => (int) $course['valid_months'] > 0 ? date('Y-m-d', strtotime('+' . (int) $course['valid_months'] . ' months')) : '',
            'contents' => implode("\n", array_column(course_modules((int) $course['id']), 'title')),
            'result' => $score >= 95 ? 'mit_auszeichnung' : 'bestanden', 'grade' => $score . ' %', 'instructor' => 'Online-Prüfung',
            '_employee' => employee_snapshot($user),
        ];
        $docId = issue_document($type, null, null, $data, $signer);
    } else {
        personnel_event($uid, 'training', 'Lehrgang bestanden: ' . $course['title'] . ' (' . $score . ' %)');
    }
    return $docId;
}
