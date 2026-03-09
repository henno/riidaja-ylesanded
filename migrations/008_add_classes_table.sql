CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    sort_order INTEGER NOT NULL DEFAULT 0
);

INSERT OR IGNORE INTO classes (name, sort_order) VALUES ('5r', 1);
INSERT OR IGNORE INTO classes (name, sort_order) VALUES ('7r', 2);
INSERT OR IGNORE INTO classes (name, sort_order) VALUES ('8r', 3);
