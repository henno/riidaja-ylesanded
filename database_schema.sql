CREATE TABLE migrations (
        id INTEGER PRIMARY KEY,
        migration TEXT,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
CREATE TABLE IF NOT EXISTS "results" (
    id INTEGER PRIMARY KEY, 
    email TEXT, 
    name TEXT, 
    exercise_id TEXT,  -- Using TEXT to support both numeric and string formats
    elapsed REAL, 
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
, accuracy REAL DEFAULT NULL, duration REAL DEFAULT NULL);
CREATE TABLE exercises (
    id TEXT PRIMARY KEY,
    title TEXT,
    target_time REAL,
    description TEXT
, result_type TEXT DEFAULT 'time', min_value REAL DEFAULT 11);
CREATE TABLE students (
    email TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    grade TEXT
);
CREATE TABLE exercise_sessions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL,
    name TEXT NOT NULL,
    exercise_id TEXT NOT NULL,
    started_at DATETIME NOT NULL,
    last_heartbeat DATETIME NOT NULL,
    ended_at DATETIME DEFAULT NULL,
    duration_seconds REAL DEFAULT NULL,
    status TEXT DEFAULT 'active' CHECK(status IN ('active', 'completed', 'abandoned')),
    result_id INTEGER DEFAULT NULL,
    FOREIGN KEY (result_id) REFERENCES results(id)
);
CREATE TABLE sqlite_sequence(name,seq);
CREATE INDEX idx_sessions_email ON exercise_sessions(email);
CREATE INDEX idx_sessions_status ON exercise_sessions(status);
CREATE INDEX idx_sessions_started ON exercise_sessions(started_at);
