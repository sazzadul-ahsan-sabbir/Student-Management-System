<?php
echo "<h2>Database Setup for Course Management</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .info { color: blue; }
    table { border-collapse: collapse; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f2f2f2; }
</style>";

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "student_db";

$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("<p class='error'>✗ MySQL Connection Failed: " . $conn->connect_error . "</p>");
}

echo "<p class='success'>✓ Connected to MySQL Server</p>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql)) {
    echo "<p class='success'>✓ Database '$database' created/selected</p>";
    $conn->select_db($database);
} else {
    echo "<p class='error'>✗ Database creation failed: " . $conn->error . "</p>";
    exit;
}

// SQL queries to execute
$queries = [];

// 1. Create students table
$queries[] = "CREATE TABLE IF NOT EXISTS students (
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

// 2. Create courses table
$queries[] = "CREATE TABLE IF NOT EXISTS courses (
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

// 3. Create enrollments table
$queries[] = "CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('Enrolled', 'Completed', 'Dropped', 'Withdrawn') DEFAULT 'Enrolled',
    grade VARCHAR(2) DEFAULT NULL,
    gpa DECIMAL(3,2) DEFAULT NULL,
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (student_id, course_id)
)";

// Execute table creation queries
foreach ($queries as $index => $sql) {
    $table_name = ['students', 'courses', 'enrollments'][$index];
    echo "<p>Creating table: <strong>$table_name</strong>... ";
    
    if ($conn->query($sql)) {
        echo "<span class='success'>✓ Success</span></p>";
    } else {
        echo "<span class='error'>✗ Error: " . $conn->error . "</span></p>";
    }
}

// Check if tables are empty and insert sample data
echo "<h3>Checking and Inserting Sample Data</h3>";

// Check students table
$result = $conn->query("SELECT COUNT(*) as count FROM students");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    echo "<p>Inserting sample students... ";
    $student_sql = "INSERT INTO students (student_id, name, email, phone, department, semester, address) VALUES 
    ('STU001', 'John Smith', 'john@example.com', '01711111111', 'Computer Science', 5, 'Dhaka'),
    ('STU002', 'Sarah Johnson', 'sarah@example.com', '01722222222', 'Physics', 3, 'Chittagong'),
    ('STU003', 'David Wilson', 'david@example.com', '01733333333', 'Mathematics', 7, 'Sylhet')";
    
    if ($conn->query($student_sql)) {
        echo "<span class='success'>✓ Added 3 students</span></p>";
    } else {
        echo "<span class='error'>✗ Failed</span></p>";
    }
} else {
    echo "<p><span class='info'>Students table already has {$row['count']} records</span></p>";
}

// Check courses table
$result = $conn->query("SELECT COUNT(*) as count FROM courses");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    echo "<p>Inserting sample courses... ";
    $course_sql = "INSERT INTO courses (course_code, course_name, department, credits, semester, instructor_name) VALUES 
    ('CS101', 'Introduction to Programming', 'Computer Science', 3, 1, 'Dr. Smith'),
    ('CS102', 'Web Development', 'Computer Science', 3, 2, 'Prof. Johnson'),
    ('MATH101', 'Calculus I', 'Mathematics', 4, 1, 'Dr. Brown'),
    ('PHY101', 'Physics I', 'Physics', 4, 1, 'Prof. Davis')";
    
    if ($conn->query($course_sql)) {
        echo "<span class='success'>✓ Added 4 courses</span></p>";
    } else {
        echo "<span class='error'>✗ Failed: " . $conn->error . "</span></p>";
    }
} else {
    echo "<p><span class='info'>Courses table already has {$row['count']} records</span></p>";
}

// Display current data
echo "<h3>Current Database Status</h3>";
echo "<table>";
echo "<tr><th>Table</th><th>Records</th></tr>";

$tables = ['students', 'courses', 'enrollments'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    $row = $result->fetch_assoc();
    $count = $row['count'];
    $color = $count > 0 ? 'success' : 'error';
    echo "<tr><td>$table</td><td><span class='$color'>$count</span></td></tr>";
}

echo "</table>";

// Test course.php API
echo "<h3>Testing Course API</h3>";
echo "<p><a href='course.php' target='_blank'>Test course.php API</a></p>";

// Direct test
echo "<p>Direct API Test: ";
$test_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/course.php";
$api_response = @file_get_contents($test_url);
if ($api_response) {
    $data = json_decode($api_response, true);
    if (is_array($data)) {
        echo "<span class='success'>✓ Working (" . count($data) . " courses)</span>";
    } else {
        echo "<span class='error'>✗ Invalid response</span>";
    }
} else {
    echo "<span class='error'>✗ API not responding</span>";
}
echo "</p>";

echo "<h3 class='success'>✓ Database Setup Complete!</h3>";
echo "<p><a href='../frontend/courses.html' style='padding: 15px 30px; background: #2ecc71; color: white; text-decoration: none; border-radius: 5px; font-size: 18px;'>Go to Course Management</a></p>";

$conn->close();
?>