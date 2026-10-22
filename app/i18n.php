<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Mehrsprachigkeit: Der deutsche Text ist zugleich der Schlüssel. Übersetzungen
 * liegen in lang/<sprache>.php (Deutsch → Zielsprache); fehlt ein Eintrag,
 * bleibt der deutsche Text stehen.
 *
 * Dokumente (portalweit, Einstellungen → Portal), zwei getrennte Schalter:
 *  - Gestaltung (doc_region): „de“ = deutsche Vordrucke, „us“ = US-Gestaltung (US Letter, US-Formulare,
 *    MM/DD/YYYY, $1,234.56) – typisch für deutschsprachige Server in Los Santos
 *  - Sprache (doc_lang): „de“ = deutsche Beschriftungen, „en“ = englisch (lang/doc_en/*.php, Deutsch → Englisch)
 * Druckvorlagen und Dokumentarten schreiben feste Texte immer mit dt('Deutsch'). Beides wird beim Ausstellen am
 * Dokument gespeichert (_region, _lang), damit ältere Dokumente unverändert bleiben.
 */

const DOC_REGIONS = ['de' => 'Deutschland – deutsche Vordrucke', 'us' => 'USA – US-Gestaltung je Dokument'];
const DOC_LANGS = ['de' => 'Deutsch', 'en' => 'Englisch'];

function doc_region(): string
{
    $r = (string) ($GLOBALS['sams_doc_region'] ?? setting('doc_region', 'de'));
    return isset(DOC_REGIONS[$r]) ? $r : 'de';
}

function doc_us(): bool
{
    return doc_region() === 'us';
}

function doc_lang(): string
{
    $l = (string) ($GLOBALS['sams_doc_lang'] ?? setting('doc_lang', 'de'));
    return isset(DOC_LANGS[$l]) ? $l : 'de';
}

/** Englische Beschriftungen auf Dokumenten? */
function doc_en(): bool
{
    return doc_lang() === 'en';
}

/** Fester Text in Dokumenten und Dokumentarten: bei Dokumentsprache Englisch übersetzt. Platzhalter als {name}. */
function dt(string $text, array $vars = []): string
{
    static $map = null;
    if (doc_en() && $text !== '') {
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
