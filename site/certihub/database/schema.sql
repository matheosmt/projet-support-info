CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'eleve' CHECK(role IN ('eleve','support','admin')),
    two_factor_enabled INTEGER NOT NULL DEFAULT 0,
    two_factor_secret TEXT,
    active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS certifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    vendor TEXT NOT NULL,
    name TEXT NOT NULL,
    domain TEXT NOT NULL,
    summary TEXT NOT NULL,
    prerequisites TEXT NOT NULL,
    featured INTEGER NOT NULL DEFAULT 0,
    certified_count INTEGER NOT NULL DEFAULT 0,
    difficulty TEXT NOT NULL DEFAULT 'intermediaire',
    price_eur INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS tracks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    domain TEXT NOT NULL,
    description TEXT NOT NULL,
    sort_order INTEGER NOT NULL DEFAULT 0
);
CREATE TABLE IF NOT EXISTS track_certifications (
    track_id INTEGER NOT NULL,
    certification_id INTEGER NOT NULL,
    position INTEGER NOT NULL DEFAULT 0,
    PRIMARY KEY(track_id, certification_id),
    FOREIGN KEY(track_id) REFERENCES tracks(id) ON DELETE CASCADE,
    FOREIGN KEY(certification_id) REFERENCES certifications(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS trainings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    certification_id INTEGER,
    title TEXT NOT NULL,
    domain TEXT NOT NULL,
    description TEXT NOT NULL,
    level TEXT NOT NULL,
    duration TEXT NOT NULL,
    FOREIGN KEY(certification_id) REFERENCES certifications(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS news (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category TEXT NOT NULL,
    title TEXT NOT NULL,
    excerpt TEXT NOT NULL,
    published_at TEXT NOT NULL
);
CREATE TABLE IF NOT EXISTS user_certifications (
    user_id INTEGER NOT NULL,
    certification_id INTEGER NOT NULL,
    status TEXT NOT NULL CHECK(status IN ('a_planifier','en_cours','obtenue')),
    progress INTEGER NOT NULL DEFAULT 0,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY(user_id, certification_id),
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY(certification_id) REFERENCES certifications(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    certification_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    body TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(certification_id) REFERENCES certifications(id) ON DELETE CASCADE,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    subject TEXT NOT NULL,
    message TEXT NOT NULL,
    status TEXT NOT NULL DEFAULT 'ouvert',
    created_at TEXT NOT NULL,
    updated_at TEXT,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
