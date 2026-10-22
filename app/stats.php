<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Statistik: Kennzahlen für einen Zeitraum (Aktivität, Fallzahlen,
 * Finanzen, Qualität). Grundlage für die Statistikseite und die
 * Wochen-/Monatsberichte (Druck/PDF und Discord).
 */

/** Zeitraum aus einer Kurzangabe: week, lastweek, month, lastmonth oder von/bis. */
function stats_period(string $preset, string $from = '', string $to = ''): array
{
    switch ($preset) {
        case 'lastweek':
            $f = date('Y-m-d 00:00:00', strtotime(week_start() . ' -7 days'));
            return [$f, week_start(), 'KW ' . date('W/Y', strtotime($f))];
        case 'month':
            return [date('Y-m-01 00:00:00'), date('Y-m-01 00:00:00', strtotime('first day of next month')), fmt_date(date('Y-m-01'), 'm/Y')];
        case 'lastmonth':
            $f = date('Y-m-01 00:00:00', strtotime('first day of last month'));
            return [$f, date('Y-m-01 00:00:00'), fmt_date($f, 'm/Y')];
        case 'custom':
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to) && $to >= $from) {
                return [$from . ' 00:00:00', date('Y-m-d 00:00:00', strtotime($to . ' +1 day')), fmt_date($from) . ' – ' . fmt_date($to)];
            }
            // no break
        default:
            return [week_start(), date('Y-m-d H:i:s', strtotime(week_start() . ' +7 days')), 'KW ' . date('W/Y')];
    }
}

function stats_collect(string $from, string $to): array
{
    $docs = all('SELECT type, status, issued_by, amount_cents, pay_status FROM documents WHERE ' . fw() . ' AND issued_at >= ? AND issued_at < ?', [$from, $to]);
    $byType = [];
    foreach ($docs as $d) {
        $byType[$d['type']] = ($byType[$d['type']] ?? 0) + 1;
    }
    arsort($byType);

    // Aktivität je Mitarbeiter/in
    $staff = [];
    $add = function (int $uid, string $k, $v) use (&$staff) {
        if ($uid) {
            $staff[$uid][$k] = ($staff[$uid][$k] ?? 0) + $v;
        }
    };
    foreach ($docs as $d) {
        $add((int) $d['issued_by'], 'docs', 1);
    }
    foreach (all('SELECT created_by FROM entries WHERE ' . fw() . ' AND created_at >= ? AND created_at < ?', [$from, $to]) as $e) {
        $add((int) $e['created_by'], 'entries', 1);
    }
    foreach (all('SELECT created_by FROM cases WHERE ' . fw() . ' AND created_at >= ? AND created_at < ?', [$from, $to]) as $c) {
        $add((int) $c['created_by'], 'cases', 1);
    }
    foreach (duty_totals($from, $to) as $uid => $sec) {
        $add((int) $uid, 'duty', $sec);
    }
    $names = array_column(all('SELECT id, name, title, rank_id FROM users WHERE ' . fw()), null, 'id');
    $rows = [];
    foreach ($staff as $uid => $s) {
        if (!isset($names[$uid])) {
            continue;
        }
        $rows[] = ['id' => $uid, 'name' => user_display($names[$uid]), 'rank' => user_rank_name($names[$uid]),
            'docs' => $s['docs'] ?? 0, 'entries' => $s['entries'] ?? 0, 'cases' => $s['cases'] ?? 0, 'duty' => $s['duty'] ?? 0];
    }
    usort($rows, fn($a, $b) => [$b['duty'], $b['docs']] <=> [$a['duty'], $a['docs']]);

    $invoices = array_filter($docs, fn($d) => $d['type'] === 'rechnung' && $d['status'] !== 'revoked');
    $sum = fn(array $list) => array_sum(array_column($list, 'amount_cents')) / 100;
    $revoked = count(array_filter($docs, fn($d) => $d['status'] === 'revoked'));

    return [
        'from' => $from, 'to' => $to,
        'documents' => count($docs), 'by_type' => $byType,
        'patients_new' => (int) scalar('SELECT COUNT(*) FROM patients WHERE ' . fw() . ' AND created_at >= ? AND created_at < ?', [$from, $to]),
        'cases_new' => (int) scalar('SELECT COUNT(*) FROM cases WHERE ' . fw() . ' AND created_at >= ? AND created_at < ?', [$from, $to]),
        'cases_closed' => (int) scalar('SELECT COUNT(*) FROM cases WHERE ' . fw() . ' AND closed_at >= ? AND closed_at < ?', [$from, $to]),
        'entries' => (int) scalar('SELECT COUNT(*) FROM entries WHERE ' . fw() . ' AND created_at >= ? AND created_at < ?', [$from, $to]),
        'duty_total' => array_sum(array_column($rows, 'duty')),
        'staff' => $rows,
        'finance' => [
            'invoiced' => $sum($invoices), 'count' => count($invoices),
            'paid' => $sum(array_filter($invoices, fn($d) => $d['pay_status'] === 'paid')),
            'open_total' => (int) scalar("SELECT COALESCE(SUM(amount_cents), 0) FROM documents WHERE " . fw() . " AND type = 'rechnung' AND status <> 'revoked' AND pay_status IN ('open', 'dunned')") / 100,
            'paid_in_period' => (int) scalar("SELECT COALESCE(SUM(amount_cents), 0) FROM documents WHERE " . fw() . " AND type = 'rechnung' AND status <> 'revoked' AND pay_status = 'paid' AND paid_at >= ? AND paid_at < ?", [$from, $to]) / 100,
        ],
        'quality' => [
            'revoked' => $revoked,
            'revoked_rate' => $docs ? round($revoked / count($docs) * 100, 1) : 0,
            'addenda' => (int) scalar('SELECT COUNT(*) FROM document_addenda a JOIN documents d ON d.id = a.document_id WHERE ' . fw('d') . ' AND a.created_at >= ? AND a.created_at < ?', [$from, $to]),
            'rejected' => (int) scalar("SELECT COUNT(*) FROM audit_log WHERE " . fw() . " AND action = 'draft.reject' AND created_at >= ? AND created_at < ?", [$from, $to]),
            'approved' => (int) scalar("SELECT COUNT(*) FROM audit_log WHERE " . fw() . " AND action = 'draft.approve' AND created_at >= ? AND created_at < ?", [$from, $to]),
            'exams' => (int) scalar('SELECT COUNT(*) FROM training_attempts t JOIN training_courses c ON c.id = t.course_id WHERE ' . fw('c') . ' AND finished_at >= ? AND finished_at < ?', [$from, $to]),
            'exams_passed' => (int) scalar('SELECT COUNT(*) FROM training_attempts t JOIN training_courses c ON c.id = t.course_id WHERE ' . fw('c') . ' AND passed = 1 AND finished_at >= ? AND finished_at < ?', [$from, $to]),
        ],
        // Verlauf pro Tag (Dokumente) für das Balkendiagramm
        'daily' => (function () use ($from, $to) {
            $days = [];
            for ($t = strtotime($from); $t < strtotime($to) && count($days) < 62; $t += 86400) {
                $days[date('Y-m-d', $t)] = 0;
            }
            foreach (all('SELECT issued_at FROM documents WHERE ' . fw() . ' AND issued_at >= ? AND issued_at < ?', [$from, $to]) as $d) {
                $k = substr($d['issued_at'], 0, 10);
                if (isset($days[$k])) {
                    $days[$k]++;
                }
            }
            return $days;
        })(),
    ];
}

/** Kurzfassung als Discord-Embed (Wochen-/Monatsbericht). */
function stats_discord_embed(array $s, string $label): array
{
    $top = array_slice($s['staff'], 0, 5);
    $types = [];
    foreach (array_slice($s['by_type'], 0, 6, true) as $k => $n) {
        $types[] = (doc_type($k)['short'] ?? $k) . ': ' . $n;
    }
    return [
        'title' => 'Bericht ' . $label . ' – ' . org('org_short'),
        'color' => 0x0b4f9c,
        'fields' => [
            ['name' => 'Dokumente', 'value' => (string) $s['documents'] . ($types ? "\n" . implode("\n", $types) : ''), 'inline' => true],
            ['name' => 'Fälle', 'value' => 'neu: ' . $s['cases_new'] . "\nabgeschlossen: " . $s['cases_closed'] . "\nneue Akten: " . $s['patients_new'], 'inline' => true],
            ['name' => 'Dienstzeit gesamt', 'value' => duty_format($s['duty_total']), 'inline' => true],
            ['name' => 'Finanzen', 'value' => 'abgerechnet: ' . money($s['finance']['invoiced']) . "\neingegangen: " . money($s['finance']['paid_in_period']) . "\noffen gesamt: " . money($s['finance']['open_total']), 'inline' => true],
            ['name' => 'Qualität', 'value' => 'Stornoquote: ' . $s['quality']['revoked_rate'] . " %\nNachträge: " . $s['quality']['addenda'] . "\nPrüfungen bestanden: " . $s['quality']['exams_passed'] . '/' . $s['quality']['exams'], 'inline' => true],
            ['name' => 'Aktivste Mitarbeitende', 'value' => $top ? implode("\n", array_map(fn($r) => $r['name'] . ' – ' . duty_format($r['duty']) . ', ' . $r['docs'] . ' Dok.', $top)) : '–'],
        ],
        'footer' => ['text' => fmt_date($s['from']) . ' – ' . fmt_date(date('Y-m-d', strtotime($s['to'] . ' -1 day')))],
    ];
}
