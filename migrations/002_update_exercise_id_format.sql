-- Update exercise_id to handle both numeric and string formats
-- SQLite doesn't support ALTER TABLE to change column types directly
-- We need to create a new table, copy data, drop old table, and rename new table

-- Create a temporary table with the updated schema
CREATE TABLE results_new (
  id INTEGER PRIMARY KEY, 
  email TEXT, 
  name TEXT, 
  exercise_id TEXT,  -- Changed from INTEGER to TEXT to support both formats
  elapsed REAL, 
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Copy data from old table to new table, formatting exercise_id as needed
INSERT INTO results_new (id, email, name, exercise_id, elapsed, timestamp)
SELECT 
  id, 
  email, 
  name, 
  CASE 
    WHEN LENGTH(exercise_id) = 1 THEN '0' || exercise_id 
    ELSE exercise_id 
  END, 
  elapsed, 
  timestamp 
FROM results;

-- Drop old table
DROP TABLE results;

-- Rename new table to original name
ALTER TABLE results_new RENAME TO results;
