-- Create students table for student grade management
CREATE TABLE IF NOT EXISTS students (
    email TEXT PRIMARY KEY,
    name TEXT NOT NULL,
    grade TEXT
);
