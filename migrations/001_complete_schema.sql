-- Create migrations table to track applied migrations
CREATE TABLE IF NOT EXISTS migrations (
    id INTEGER PRIMARY KEY,
    migration TEXT,
    applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create results table for storing exercise completion data
CREATE TABLE IF NOT EXISTS "results" (
    id INTEGER PRIMARY KEY, 
    email TEXT, 
    name TEXT, 
    exercise_id TEXT,  -- Using TEXT to support both numeric and string formats
    elapsed REAL, 
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Create exercises table for storing exercise metadata
CREATE TABLE IF NOT EXISTS exercises (
    id TEXT PRIMARY KEY,
    title TEXT,
    target_time REAL,
    description TEXT
);