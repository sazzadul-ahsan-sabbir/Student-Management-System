<?php
// ============================================
// Database Configuration for Student Management System
// ============================================

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database credentials - XAMPP Default
$host = "localhost";
$username = "root";
$password = "";  // XAMPP default: no password
$database = "student_db";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check MySQL connection
if ($conn->connect_error) {
    die(json_encode([
        "error" => true,
        "message" => "MySQL Connection Failed!",
        "details" => $conn->connect_error,
        "solution" => "Please check: 1. XAMPP MySQL is running, 2. Username/password"
    ]));
}

// Create database if not exists
$create_db = "CREATE DATABASE IF NOT EXISTS $database 
              CHARACTER SET utf8mb4 
              COLLATE utf8mb4_unicode_ci";
              
if (!$conn->query($create_db)) {
    die(json_encode([
        "error" => true,
        "message" => "Database creation failed",
        "details" => $conn->error
    ]));
}

// Select the database
$conn->select_db($database);

// Create students table if not exists
$create_students_table = "CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(15),
    department VARCHAR(50),
    semester INT,
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($create_students_table)) {
    die(json_encode([
        "error" => true,
        "message" => "Students table creation failed",
        "details" => $conn->error
    ]));
}

// Insert sample data if table is empty
$check_data = $conn->query("SELECT COUNT(*) as count FROM students");
$row = $check_data->fetch_assoc();
if ($row['count'] == 0) {
    $sample_data = "INSERT INTO students (student_id, name, email, phone, department, semester, address) 
                    VALUES 
                    ('STU001', 'John Smith', 'john@example.com', '01711111111', 'Computer Science', 5, 'Dhaka'),
                    ('STU002', 'Sarah Johnson', 'sarah@example.com', '01722222222', 'Physics', 3, 'Chittagong'),
                    ('STU003', 'David Wilson', 'david@example.com', '01733333333', 'Mathematics', 7, 'Sylhet')";
    $conn->query($sample_data);
}

// Set character set to UTF-8
$conn->set_charset("utf8");

// Connection successful message (for debugging)
// echo json_encode(["success" => true, "message" => "Database connected successfully!"]);
?>