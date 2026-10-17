<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/**
 * Mehrsprachigkeit: Der deutsche Text ist zugleich der Schlüssel. Übersetzungen
 * liegen in lang/<sprache>.php (Deutsch → Zielsprache); fehlt ein Eintrag,
 * bleibt der deutsche Text stehen. Dokumente selbst bleiben deutsch.
 */

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
