<?php
/**
 * Handbuch für Mitarbeitende im Wiki (Kategorie „Handbuch“). Legt nur fehlende Seiten an.
 */
return [
    'description' => 'Handbuch im Wiki',
    'up' => function (Schema $s): void {
        $now = date('Y-m-d H:i:s');
        $exists = $s->db->prepare('SELECT COUNT(*) FROM wiki_pages WHERE slug = ?');
        $ins = $s->db->prepare("INSERT INTO wiki_pages (slug, title, category, content, pinned, created_at, updated_at) VALUES (?, ?, 'Handbuch', ?, ?, ?, ?)");
        $first = true;
        foreach (require dirname(__DIR__) . '/content/handbuch.php' as $title => $content) {
            $slug = wiki_slug($title);
            $exists->execute([$slug]);
            if (!$exists->fetchColumn()) {
                $ins->execute([$slug, $title, $content, $first ? 1 : 0, $now, $now]);
            }
            $first = false;
        }
    },
];
