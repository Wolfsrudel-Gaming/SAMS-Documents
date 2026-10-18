<?php
/** Anwaltskanzleien (Fraktionsart „law“ steckt im Code) und Höchstgrenzen der Gesamtstrafe je Gesetzbuch für den Strafrechner. */
return [
    'description' => 'Anwaltskanzleien, Strafrechner',
    'up' => function (Schema $s): void {
        $s->addColumn('law_codes', 'max_jail', 'INTEGER NOT NULL DEFAULT 0');
        $s->addColumn('law_codes', 'max_fine', 'INTEGER NOT NULL DEFAULT 0');
        foreach ($s->db->query('SELECT id FROM law_codes')->fetchAll(PDO::FETCH_COLUMN) as $id) {
            $text = implode("\n", $s->db->query('SELECT s.body FROM law_sections s JOIN law_books b ON b.id = s.book_id WHERE b.code_id = ' . (int) $id)->fetchAll(PDO::FETCH_COLUMN));
            [$jail, $fine] = law_detect_limits($text);
            $s->db->prepare('UPDATE law_codes SET max_jail = ?, max_fine = ? WHERE id = ?')->execute([$jail, $fine, $id]);
        }
    },
];
