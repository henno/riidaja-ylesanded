-- Initial database schema
CREATE TABLE IF NOT EXISTS results (
  id INTEGER PRIMARY KEY, 
  email TEXT, 
  name TEXT, 
  exercise_id INTEGER, 
  elapsed REAL, 
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);
