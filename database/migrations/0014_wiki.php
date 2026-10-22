<?php
/**
 * Etappe 5c: Wiki (SOPs, Medikamente, Abläufe) und pflegbare Vorschlagslisten.
 */
return [
    'description' => 'Wiki und Vorschlagslisten',
    'up' => function (Schema $s): void {
        $pk = $s->pk();
        $fk = $s->fk();
        $str = $s->str();
        $text = $s->text();
        $dt = $s->datetime();
        $e = $s->engine();
        $s->exec("CREATE TABLE IF NOT EXISTS wiki_pages (
            id $pk, faction_id $fk NOT NULL DEFAULT 1, slug {$s->str(120)} NOT NULL UNIQUE, title $str NOT NULL,
            category $str NOT NULL DEFAULT 'Allgemein', content $text, pinned INTEGER NOT NULL DEFAULT 0,
            created_by $fk NULL, created_at $dt NOT NULL, updated_by $fk NULL, updated_at $dt NOT NULL
        ){$e}");
        $s->exec("CREATE TABLE IF NOT EXISTS wiki_revisions (
            id $pk, page_id $fk NOT NULL, title $str NOT NULL, content $text, user_id $fk NULL, note $str DEFAULT '', created_at $dt NOT NULL
        ){$e}");
        $s->index('idx_wiki_rev_page', 'wiki_revisions', 'page_id');
        $s->exec("CREATE TABLE IF NOT EXISTS suggestion_lists (
            id $pk, list_key {$s->str(32)} NOT NULL UNIQUE, items $text, updated_by $fk NULL, updated_at $dt NOT NULL
        ){$e}");

        if (!(int) $s->db->query('SELECT COUNT(*) FROM wiki_pages')->fetchColumn()) {
            $now = date('Y-m-d H:i:s');
            $ins = $s->db->prepare('INSERT INTO wiki_pages (slug, title, category, content, pinned, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $ins->execute(['willkommen', 'Willkommen im SAMS-Wiki', 'Allgemein', "Hier sammeln wir **Standardvorgehen (SOPs)**, Medikamenteninformationen und Abläufe.\n\n## So funktioniert das Wiki\n- Jede Seite hat eine Versionsgeschichte – nichts geht verloren.\n- Links auf andere Seiten: `[[Seitentitel]]`, z. B. [[SOP Reanimation]].\n- Formatierung: # Überschrift, **fett**, *kursiv*, - Listen.\n\nDie **Vorschlagslisten** in den Formularen (ICD-10, Medikamente, Fachrichtungen, Impfstoffe) werden unter *Wiki → Vorschlagslisten* gepflegt.", 1, $now, $now]);
            $ins->execute(['sop_reanimation', 'SOP Reanimation', 'SOP', "# Ablauf\n1. Sicherheit prüfen, Bewusstsein und Atmung prüfen (max. 10 s)\n2. Notruf / Nachalarmierung, AED anfordern\n3. 30 Kompressionen : 2 Beatmungen\n4. AED anschließen, Anweisungen folgen\n5. Adrenalin 1 mg i.v./i.o. alle 3–5 min (nicht schockbar sofort, schockbar nach 3. Schock)\n6. Amiodaron 300 mg nach dem 3. Schock\n\n## Dokumentation\nEinsatzprotokoll mit Reanimationszeiten ausfüllen. Bei Todesfeststellung: Todesbescheinigung.", 0, $now, $now]);
        }

        $add = $s->db->prepare('INSERT INTO rank_permissions (rank_id, permission) VALUES (?, ?)');
        $has = fn(int $rank, string $perm) => (bool) $s->db->query("SELECT COUNT(*) FROM rank_permissions WHERE rank_id = $rank AND permission = '$perm'")->fetchColumn();
        foreach ($s->db->query("SELECT rank_id FROM rank_permissions WHERE permission = 'duty.clock'")->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            if (!$has((int) $rankId, 'wiki.view')) {
                $add->execute([$rankId, 'wiki.view']);
            }
        }
        foreach ($s->db->query('SELECT id FROM ranks WHERE level >= 50')->fetchAll(PDO::FETCH_COLUMN) as $rankId) {
            foreach (['wiki.edit', 'lists.manage'] as $perm) {
                if (!$has((int) $rankId, $perm)) {
                    $add->execute([$rankId, $perm]);
                }
            }
        }
    },
];
