<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Mehrsprachigkeit: Der deutsche Text ist zugleich der Schlüssel. Übersetzungen
 * liegen in lang/<sprache>.php (Deutsch → Zielsprache); fehlt ein Eintrag,
 * bleibt der deutsche Text stehen.
 *
 * Vorlagenset (portalweit, Einstellungen → Administration): „de“ = deutsche Vorlagen, „us“ = US-Vorlagen auf Englisch.
 * Druckvorlagen und Dokumentarten schreiben feste Texte mit dt('Deutsch'); im US-Set kommt die Übersetzung aus
 * lang/doc_en/*.php (Deutsch → Englisch), Datumsangaben als MM/DD/YYYY, Beträge als $1,234.56.
 */

const DOC_REGIONS = ['de' => 'Deutschland – deutsche Vorlagen', 'us' => 'USA – US-Vorlagen (englisch)'];

function doc_region(): string
{
    $r = (string) ($GLOBALS['sams_doc_region'] ?? setting('doc_region', 'de'));
    return isset(DOC_REGIONS[$r]) ? $r : 'de';
}

function doc_us(): bool
{
    return doc_region() === 'us';
}

/** Fester Text in Dokumenten und Dokumentarten: im US-Set englisch. Platzhalter als {name}. */
function dt(string $text, array $vars = []): string
{
    static $map = null;
    if (doc_us() && $text !== '') {
        if ($map === null) {
            $map = [];
            foreach (glob(SAMS_ROOT . '/lang/doc_en/*.php') ?: [] as $file) {
                $map += (array) require $file;
            }
        }
        $text = $map[$text] ?? $map[trim($text)] ?? $text;
    }
    if ($vars) {
        $text = strtr($text, array_combine(array_map(fn($k) => '{' . $k . '}', array_keys($vars)), array_map('strval', $vars)));
    }
    return $text;
}

const UI_LANGUAGES = ['de' => 'Deutsch', 'en' => 'English'];

function ui_lang(): string
{
    static $lang = null;
    if ($lang === null) {
        $l = (string) setting('language', 'de');
        $lang = isset(UI_LANGUAGES[$l]) ? $l : 'de';
    }
    return $lang;
}

/** Text übersetzen; Platzhalter als {name}. */
function t(string $text, array $vars = []): string
{
    static $map = null;
    if (ui_lang() !== 'de') {
        if ($map === null) {
            $file = SAMS_ROOT . '/lang/' . ui_lang() . '.php';
            $map = is_file($file) ? (array) require $file : [];
        }
        $text = $map[$text] ?? $text;
    }
    if ($vars) {
        $text = strtr($text, array_combine(array_map(fn($k) => '{' . $k . '}', array_keys($vars)), array_map('strval', $vars)));
    }
    return $text;
}
