<?php
// Simple test file for courses
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$host = "localhost";
$username = "root";
$password = "";
$database = "student_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode([
        "error" => true,
        "message" => "Database connection failed: " . $conn->connect_error,
        "details" => [
            "host" => $host,
            "username" => $username,
            "database" => $database,
            "error" => $conn->connect_error
        ]
    ]));
}

// Check if courses table exists
$table_check = $conn->query("SHOW TABLES LIKE 'courses'");
if ($table_check->num_rows == 0) {
    // Create courses table
    $create_sql = "CREATE TABLE courses (
        course_id INT PRIMARY KEY AUTO_INCREMENT,
        course_code VARCHAR(20) UNIQUE NOT NULL,
        course_name VARCHAR(100) NOT NULL,
        department VARCHAR(50) NOT NULL,
        credits INT DEFAULT 3,
        semester INT,
        instructor_name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($create_sql)) {
        echo json_encode([
            "error" => true,
            "message" => "Failed to create courses table",
            "sql_error" => $conn->error
        ]);
        exit;
    }
    
    // Insert sample data
    $sample_data = [
        "INSERT INTO courses (course_code, course_name, department, credits, semester) 
         VALUES ('CS101', 'Introduction to Programming', 'Computer Science', 3, 1)",
        "INSERT INTO courses (course_code, course_name, department, credits, semester) 
         VALUES ('CS102', 'Web Development', 'Computer Science', 3, 2)",
        "INSERT INTO courses (course_code, course_name, department, credits, semester) 
         VALUES ('MATH101', 'Calculus I', 'Mathematics', 4, 1)"
    ];
    
    foreach ($sample_data as $sql) {
        $conn->query($sql);
    }
}

// Get all courses
$result = $conn->query("SELECT * FROM courses ORDER BY course_code");
$courses = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
    
    echo json_encode([
        "success" => true,
        "count" => count($courses),
        "courses" => $courses,
        "debug_info" => [
            "database" => $database,
            "table_exists" => true,
            "total_records" => count($courses)
        ]
    ]);
} else {
    echo json_encode([
        "error" => true,
        "message" => "Query failed: " . $conn->error,
        "sql_error" => $conn->error
    ]);
}

$conn->close();
?>