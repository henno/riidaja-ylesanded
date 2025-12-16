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
);
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
