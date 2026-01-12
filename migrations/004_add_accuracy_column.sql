-- Add accuracy column to results table for WPM exercises
ALTER TABLE results ADD COLUMN accuracy REAL DEFAULT NULL;

-- Update exercise 006 to be WPM type
UPDATE exercises SET result_type = 'wpm', min_value = 0 WHERE id = '006';
