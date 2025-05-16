-- Create exercises table
CREATE TABLE IF NOT EXISTS exercises (
  id TEXT PRIMARY KEY,
  title TEXT,
  target_time REAL,
  description TEXT
);

-- Insert default data for existing exercises
INSERT INTO exercises (id, title, target_time, description) VALUES 
('001', 'Sõnade kopeerimine', 85, 'Kopeeri sõnad õigesti'),
('002', 'Lausete järjestamine', 50, 'Taasta laused õiges järjekorras');