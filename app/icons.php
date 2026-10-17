<?php
declare(strict_types=1);
defined('SAMS_ROOT') || exit;

/** Kleine Strich-Icons (24×24) für Navigation und Werkzeugleisten. */
function icon(string $name, string $class = 'ic'): string
{
    static $paths = [
        'home'     => '<path d="M3 11 12 4l9 7"/><path d="M5 10v10h5v-6h4v6h5V10"/>',
        'patients' => '<circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14c2 .7 3 2.6 3 5"/>',
        'folder'   => '<path d="M3 6.5A1.5 1.5 0 0 1 4.5 5H9l2 2h8.5A1.5 1.5 0 0 1 21 8.5v9A1.5 1.5 0 0 1 19.5 19h-15A1.5 1.5 0 0 1 3 17.5z"/>',
        'docs'     => '<path d="M7 3h7l5 5v13H7z"/><path d="M14 3v5h5M10 13h6M10 17h6"/>',
        'doc-plus' => '<path d="M6 3h7l5 5v13H6z"/><path d="M13 3v5h5M12 11v6M9 14h6"/>',
        'users'    => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2.5v3M12 18.5v3M4.2 6.2l2.1 2.1M17.7 15.7l2.1 2.1M2.5 12h3M18.5 12h3M4.2 17.8l2.1-2.1M17.7 8.3l2.1-2.1"/>',
        'shield'   => '<path d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6z"/><path d="m9 12 2 2 4-4"/>',
        'log'      => '<path d="M5 4h14v16H5z"/><path d="M8 8h8M8 12h8M8 16h5"/>',
        'server'   => '<rect x="4" y="4" width="16" height="6" rx="1"/><rect x="4" y="14" width="16" height="6" rx="1"/><path d="M8 7h.01M8 17h.01"/>',
        'logout'   => '<path d="M14 4h5v16h-5"/><path d="M10 8l-4 4 4 4M6 12h10"/>',
        'search'   => '<circle cx="11" cy="11" r="6"/><path d="m20 20-4.5-4.5"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'print'    => '<path d="M7 9V3h10v6"/><rect x="4" y="9" width="16" height="8" rx="1"/><path d="M7 14h10v7H7z"/>',
        'image'    => '<rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="9" cy="10" r="1.8"/><path d="m21 16-5-5-8 8"/>',
        'link'     => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1"/><path d="M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
        'edit'     => '<path d="M4 20h4L19 9l-4-4L4 16z"/><path d="m13 7 4 4"/>',
        'copy'     => '<rect x="8" y="8" width="12" height="12" rx="1"/><path d="M16 8V4H4v12h4"/>',
        'user'     => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c0-3.9 3.1-7 7-7s7 3.1 7 7"/>',
        'check'    => '<path d="m5 12 5 5 9-10"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 14v6H4V6h6"/>',
        'clock'    => '<circle cx="12" cy="12" r="8"/><path d="M12 8v4l3 2"/>',
        'warn'     => '<path d="M12 3 2 20h20z"/><path d="M12 10v4M12 17h.01"/>',
        'menu'     => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'back'     => '<path d="M15 5l-7 7 7 7"/>',
        'heart'    => '<path d="M12 20s-7-4.5-7-10a4 4 0 0 1 7-2.5A4 4 0 0 1 19 10c0 5.5-7 10-7 10z"/>',
    ];
    $p = $paths[$name] ?? $paths['docs'];
    return '<svg class="' . htmlspecialchars($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}
