-- Run this in your Supabase SQL Editor
-- Go to: https://supabase.com/dashboard/project/kfbolysjloxvauqrxtye/sql

-- Disable Row Level Security for public access
ALTER TABLE equipment DISABLE ROW LEVEL SECURITY;

-- Create the equipment table
CREATE TABLE IF NOT EXISTS equipment (
    equipment_id VARCHAR(20) PRIMARY KEY,
    equipment_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 0,
    condition VARCHAR(30) NOT NULL,
    laboratory VARCHAR(50) NOT NULL,
    date_acquired DATE NOT NULL
);

-- Insert sample data
INSERT INTO equipment (equipment_id, equipment_name, category, quantity, condition, laboratory, date_acquired) VALUES
('EQ-101', 'Digital Storage Oscilloscope 50MHz', 'Electronics', 15, 'Good', 'Electronics Lab 101', '2024-01-15'),
('EQ-102', 'Binocular Compound Microscope', 'Biology', 24, 'Good', 'Biology Lab 202', '2023-08-20'),
('EQ-103', 'Analytical Balance 0.1mg', 'Chemistry', 8, 'For Repair', 'Chemistry Lab 304', '2023-11-05'),
('EQ-104', 'High-Speed Centrifuge 4000 RPM', 'Biology', 5, 'Damaged', 'Biology Lab 202', '2022-04-12'),
('EQ-105', 'Function Generator 10MHz', 'Electronics', 12, 'Unserviceable', 'Electronics Lab 101', '2021-09-30'),
('EQ-106', 'UV-Vis Spectrophotometer', 'Chemistry', 4, 'Good', 'Chemistry Lab 304', '2024-03-10');
