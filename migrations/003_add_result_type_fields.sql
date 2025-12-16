-- Add result_type and min_value columns to exercises table
-- result_type: 'time' for time-based exercises (seconds), 'wpm' for typing speed exercises
-- min_value: minimum acceptable value (e.g., 11 for time-based, 0 for WPM)

ALTER TABLE exercises ADD COLUMN result_type TEXT DEFAULT 'time';
ALTER TABLE exercises ADD COLUMN min_value REAL DEFAULT 11;

-- Set result_type and min_value for exercise 006 (typing exercise)
-- Exercise 006 uses WPM (words per minute) and allows any non-zero value
UPDATE exercises SET result_type = 'wpm', min_value = 0 WHERE id = '006';