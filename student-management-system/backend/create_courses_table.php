<?php
// Create courses and enrollments tables
require_once 'config.php';

echo "<h2>Creating Course Management Tables</h2>";
echo "<style>
    body { font-family: Arial; padding: 20px; }
    .success { color: green; }
    .error { color: red; }
    .info { color: blue; }
</style>";

// Step 1: Create courses table
echo "<h3>Step 1: Creating courses table...</h3>";
$courses_sql = "CREATE TABLE IF NOT EXISTS courses (
    course_id INT PRIMARY KEY AUTO_INCREMENT,
    course_code VARCHAR(20) UNIQUE NOT NULL,
    course_name VARCHAR(100) NOT NULL,
    department VARCHAR(50) NOT NULL,
    credits INT DEFAULT 3,
    semester INT,
    instructor_name VARCHAR(100),
    max_students INT DEFAULT 50,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($courses_sql)) {
    echo "<p class='success'>✓ Courses table created successfully</p>";
} else {
    echo "<p class='error'>✗ Error creating courses table: " . $conn->error . "</p>";
}

// Step 2: Create enrollments table
echo "<h3>Step 2: Creating enrollments table...</h3>";
$enrollments_sql = "CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('Enrolled', 'Completed', 'Dropped') DEFAULT 'Enrolled',
    grade VARCHAR(2),
    gpa DECIMAL(3,2),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_enrollment (student_id, course_id)
)";

if ($conn->query($enrollments_sql)) {
    echo "<p class='success'>✓ Enrollments table created successfully</p>";
} else {
    echo "<p class='error'>✗ Error creating enrollments table: " . $conn->error . "</p>";
}

// Step 3: Insert sample courses
echo "<h3>Step 3: Inserting sample courses...</h3>";
$sample_courses = [
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('CS101', 'Introduction to Programming', 'Computer Science', 3, 1, 'Dr. Smith')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('CS102', 'Web Development', 'Computer Science', 3, 2, 'Prof. Johnson')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('CS201', 'Data Structures', 'Computer Science', 4, 3, 'Dr. Williams')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('MATH101', 'Calculus I', 'Mathematics', 4, 1, 'Prof. Brown')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('PHY101', 'Physics I', 'Physics', 4, 1, 'Dr. Davis')",
    
    "INSERT IGNORE INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
     VALUES ('ENG101', 'English Composition', 'English', 3, 1, 'Prof. Miller')"
];

$courses_added = 0;
foreach ($sample_courses as $sql) {
    if ($conn->query($sql)) {
        $courses_added++;
    }
}
echo "<p class='success'>✓ $courses_added sample courses added</p>";

// Step 4: Test the APIs
echo "<h3>Step 4: Testing APIs...</h3>";
echo "<p><a href='course.php' target='_blank'>Test Course API</a></p>";
echo "<p><a href='enrollment.php' target='_blank'>Test Enrollment API</a></p>";

// Step 5: Display current courses
echo "<h3>Step 5: Current Courses in Database</h3>";
$result = $conn->query("SELECT * FROM courses ORDER BY department, semester");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse;'>
            <tr>
                <th>ID</th>
                <th>Code</th>
                <th>Name</th>
                <th>Department</th>
                <th>Semester</th>
                <th>Credits</th>
                <th>Instructor</th>
            </tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['course_id']}</td>
                <td>{$row['course_code']}</td>
                <td>{$row['course_name']}</td>
                <td>{$row['department']}</td>
                <td>{$row['semester']}</td>
                <td>{$row['credits']}</td>
                <td>{$row['instructor_name']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>No courses found in database</p>";
}

echo "<h3 class='success'>✓ Setup Completed!</h3>";
echo "<p><a href='../frontend/courses.html' style='padding: 10px 20px; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Go to Course Management</a></p>";

$conn->close();
?>