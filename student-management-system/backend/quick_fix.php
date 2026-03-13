<?php
echo "<h2>Quick Fix for Course Management System</h2>";

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "student_db";

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Step 1: Create database if not exists
echo "<h3>Step 1: Creating/Checking Database</h3>";
$conn->query("CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($database);
echo "<p style='color:green;'>✓ Database ready</p>";

// Step 2: Create courses table
echo "<h3>Step 2: Creating Courses Table</h3>";
$courses_table_sql = "
CREATE TABLE IF NOT EXISTS courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    credits INT DEFAULT 3,
    semester INT,
    description TEXT,
    instructor_name VARCHAR(100),
    max_students INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($courses_table_sql)) {
    echo "<p style='color:green;'>✓ Courses table created</p>";
} else {
    echo "<p style='color:red;'>✗ Error creating courses table: " . $conn->error . "</p>";
}

// Step 3: Create enrollments table
echo "<h3>Step 3: Creating Enrollments Table</h3>";
$enrollments_table_sql = "
CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL DEFAULT (CURRENT_DATE),
    status ENUM('Enrolled', 'Completed', 'Dropped', 'Withdrawn') DEFAULT 'Enrolled',
    grade VARCHAR(2) DEFAULT NULL,
    gpa DECIMAL(3,2) DEFAULT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// First, check if students table exists for foreign key
$check_students = $conn->query("SHOW TABLES LIKE 'students'");
if ($check_students->num_rows > 0) {
    $enrollments_table_sql = "
    CREATE TABLE IF NOT EXISTS enrollments (
        enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        course_id INT NOT NULL,
        enrollment_date DATE NOT NULL DEFAULT (CURRENT_DATE),
        status ENUM('Enrolled', 'Completed', 'Dropped', 'Withdrawn') DEFAULT 'Enrolled',
        grade VARCHAR(2) DEFAULT NULL,
        gpa DECIMAL(3,2) DEFAULT NULL,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
        UNIQUE KEY unique_enrollment (student_id, course_id)
    )";
}

if ($conn->query($enrollments_table_sql)) {
    echo "<p style='color:green;'>✓ Enrollments table created</p>";
} else {
    echo "<p style='color:red;'>✗ Error creating enrollments table: " . $conn->error . "</p>";
    // Try without foreign keys
    $enrollments_table_simple = "
    CREATE TABLE IF NOT EXISTS enrollments (
        enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT NOT NULL,
        course_id INT NOT NULL,
        enrollment_date DATE NOT NULL DEFAULT (CURRENT_DATE),
        status ENUM('Enrolled', 'Completed', 'Dropped', 'Withdrawn') DEFAULT 'Enrolled',
        grade VARCHAR(2) DEFAULT NULL,
        gpa DECIMAL(3,2) DEFAULT NULL,
        remarks TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_enrollment (student_id, course_id)
    )";
    
    if ($conn->query($enrollments_table_simple)) {
        echo "<p style='color:green;'>✓ Enrollments table created (without foreign keys)</p>";
    }
}

// Step 4: Insert sample courses
echo "<h3>Step 4: Inserting Sample Courses</h3>";
$sample_courses = [
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('CS101', 'Introduction to Programming', 'Computer Science', 3, 1, 'Dr. Smith')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('CS201', 'Data Structures', 'Computer Science', 4, 2, 'Prof. Johnson')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('CS301', 'Database Management', 'Computer Science', 3, 3, 'Dr. Williams')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('MATH101', 'Calculus I', 'Mathematics', 4, 1, 'Prof. Brown')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('PHY101', 'Physics I', 'Physics', 4, 1, 'Dr. Davis')"
];

foreach ($sample_courses as $sql) {
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>✓ Course added</p>";
    } else {
        echo "<p style='color:orange;'>⚠ " . $conn->error . "</p>";
    }
}

// Step 5: Test the course.php API
echo "<h3>Step 5: Testing Course API</h3>";
echo "<iframe src='course.php' width='100%' height='300' style='border:1px solid #ccc;'></iframe>";

// Step 6: Create a simple test endpoint
echo "<h3>Step 6: Simple Test Endpoint</h3>";
echo "<p><a href='test_courses.php' target='_blank'>Test Courses Directly</a></p>";

echo "<h3 style='color:green;'>✓ Quick Fix Completed!</h3>";
echo "<p><a href='../frontend/courses.html' style='padding: 15px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; font-size: 18px;'>Go to Course Management</a></p>";

$conn->close();
?>