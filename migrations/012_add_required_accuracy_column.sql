-- Add required_accuracy column to exercises table
ALTER TABLE exercises ADD COLUMN required_accuracy REAL DEFAULT NULL;

-- Set accuracy requirements for WPM exercises
UPDATE exercises SET required_accuracy = 90 WHERE id = '006';
UPDATE exercises SET required_accuracy = 90 WHERE id = '007';
UPDATE exercises SET required_accuracy = 90 WHERE id = '009';
UPDATE exercises SET required_accuracy = 97 WHERE id = '010';
UPDATE exercises SET required_accuracy = 97 WHERE id = '011';
UPDATE exercises SET required_accuracy = 97 WHERE id = '012';
