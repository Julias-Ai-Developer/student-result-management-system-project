-- Student Result Management System Database
-- Create database (uncomment if you need to create a new database)
-- CREATE DATABASE IF NOT EXISTS student_result_management;
-- USE student_result_management;

-- Drop tables if they exist to avoid conflicts
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher') NOT NULL DEFAULT 'teacher',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create students table
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    math INT NOT NULL,
    english INT NOT NULL,
    science INT NOT NULL,
    total INT NOT NULL,
    average DECIMAL(5,2) NOT NULL,
    grade VARCHAR(2) NOT NULL,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO users (username, password, role) VALUES 
('admin', '$2y$10$qQmm4JqwPL3/RFP.fT8oAOVVzPr0oz.BzOe.F9Y5JHh0CrQpyhtYG', 'admin');

-- Insert sample teacher user (username: teacher, password: teacher123)
INSERT INTO users (username, password, role) VALUES 
('teacher', '$2y$10$Nt7Xr9r.aCRJPvV5zVJVEOBGQIEBP0T.aKYYkYZ3XN5xLsnTvnKfO', 'teacher');

-- Insert sample student data
INSERT INTO students (student_id, name, math, english, science, total, average, grade, created_by) VALUES
('STU001', 'John Smith', 85, 78, 92, 255, 85.00, 'A', 1),
('STU002', 'Emma Johnson', 92, 88, 95, 275, 91.67, 'A+', 1),
('STU003', 'Michael Brown', 65, 72, 68, 205, 68.33, 'C', 1),
('STU004', 'Sophia Davis', 78, 85, 80, 243, 81.00, 'A', 1),
('STU005', 'William Wilson', 55, 60, 58, 173, 57.67, 'D', 1),
('STU006', 'Olivia Martinez', 98, 95, 97, 290, 96.67, 'A+', 1),
('STU007', 'James Taylor', 72, 68, 75, 215, 71.67, 'B', 1),
('STU008', 'Ava Anderson', 88, 92, 85, 265, 88.33, 'A', 1),
('STU009', 'Alexander Thomas', 62, 58, 65, 185, 61.67, 'C', 1),
('STU010', 'Isabella Jackson', 90, 87, 93, 270, 90.00, 'A+', 1);

-- Create indexes for better performance
CREATE INDEX idx_student_name ON students(name);
CREATE INDEX idx_student_grade ON students(grade);
CREATE INDEX idx_student_average ON students(average);