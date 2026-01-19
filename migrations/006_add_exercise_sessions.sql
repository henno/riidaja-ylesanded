-- Track active exercise sessions for time tracking even when page is refreshed
CREATE TABLE IF NOT EXISTS exercise_sessions (
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

-- Index for quick lookups
CREATE INDEX IF NOT EXISTS idx_sessions_email ON exercise_sessions(email);
CREATE INDEX IF NOT EXISTS idx_sessions_status ON exercise_sessions(status);
CREATE INDEX IF NOT EXISTS idx_sessions_started ON exercise_sessions(started_at);
