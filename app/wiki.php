<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Wiki: Seiten mit Versionsgeschichte und [[Wiki-Links]], dazu die
 * pflegbaren Vorschlagslisten für die Dokumentformulare.
 */

const SUGGESTION_LISTS = [
    'icd'         => 'ICD-10-Diagnosen',
    'meds'        => 'Medikamente (Rezept)',
    'departments' => 'Fachrichtungen (Überweisung, Arztbrief)',
    'vaccines'    => 'Impfstoffe',
];

function wiki_slug(string $title): string
{
    return substr(custom_slug($title), 0, 110) ?: 'seite';
}

/** Wiki-Inhalt rendern: [[Seite]] bzw. [[Seite|Text]] werden zu Links. */
function wiki_render(string $content): string
{
    $content = preg_replace_callback('/\[\[([^\]|]+)(?:\|([^\]]+))?\]\]/', fn($m) => '[' . trim($m[2] ?? $m[1]) . '](index.php?p=wiki_page&slug=' . wiki_slug($m[1]) . ')', $content);
    return light_markup($content);
}

/** Gespeicherte Vorschlagsliste (oder null, wenn noch nicht gepflegt). */
function suggestion_list(string $key): ?array
{
    static $cache = null;
    if ($cache === null) {
        try {
            $cache = array_column(all('SELECT list_key, items FROM suggestion_lists'), 'items', 'list_key');
        } catch (PDOException $e) {
            $cache = [];
        }
    }
    if (!isset($cache[$key])) {
        return null;
    }
    return array_values(array_filter(array_map('trim', preg_split('/\r?\n/', (string) $cache[$key])), 'strlen'));
}
