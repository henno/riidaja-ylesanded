-- Add duration column to results table to track time spent on exercise (in seconds)
-- This is separate from 'elapsed' which stores the result (time for time-based, WPM for WPM-based)
ALTER TABLE results ADD COLUMN duration REAL DEFAULT NULL;
