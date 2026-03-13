CREATE DATABASE IF NOT EXISTS student_db;
USE student_db;

CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    department VARCHAR(50),
    semester INT,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample data
INSERT INTO students (student_id, name, email, phone, department, semester, address) 
VALUES 
('STU001', 'রহিম আহমেদ', 'rahim@example.com', '01712345678', 'Computer Science', 5, 'ঢাকা, বাংলাদেশ'),
('STU002', 'সিমা আক্তার', 'simaa@example.com', '01812345679', 'Physics', 3, 'চট্টগ্রাম, বাংলাদেশ'),
('STU003', 'আরফিন ইসলাম', 'arfin@example.com', '01912345680', 'Mathematics', 7, 'সিলেট, বাংলাদেশ');