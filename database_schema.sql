CREATE TABLE migrations (
        id INTEGER PRIMARY KEY,
        migration TEXT,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    );
CREATE TABLE IF NOT EXISTS "results" (
  id INTEGER PRIMARY KEY, 
  email TEXT, 
  name TEXT, 
  exercise_id TEXT,  -- Changed from INTEGER to TEXT to support both formats
  elapsed REAL, 
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE exercises (
  id TEXT PRIMARY KEY,
  title TEXT,
  target_time REAL,
  description TEXT
);
