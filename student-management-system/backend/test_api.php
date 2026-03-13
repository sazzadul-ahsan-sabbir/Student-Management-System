<?php
echo "<h2>API Connection Test</h2>";

// Database test
$host = "localhost";
$username = "root";
$password = "";
$database = "student_db";

echo "<h3>1. Database Connection Test</h3>";
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    echo "<p style='color:red;'>✗ MySQL Connection Failed: " . $conn->connect_error . "</p>";
    echo "<p>Solution: Check XAMPP/WAMP MySQL is running</p>";
} else {
    echo "<p style='color:green;'>✓ MySQL Server Connected</p>";
    
    // Check database
    if ($conn->select_db($database)) {
        echo "<p style='color:green;'>✓ Database '$database' Selected</p>";
        
        // Check courses table
        $result = $conn->query("SHOW TABLES LIKE 'courses'");
        if ($result->num_rows > 0) {
            echo "<p style='color:green;'>✓ Courses table exists</p>";
            
            // Check data in courses table
            $courses_result = $conn->query("SELECT COUNT(*) as count FROM courses");
            $courses_data = $courses_result->fetch_assoc();
            echo "<p>Courses count: " . $courses_data['count'] . "</p>";
            
            // Show sample courses
            $sample = $conn->query("SELECT course_code, course_name FROM courses LIMIT 5");
            echo "<p>Sample courses:</p>";
            echo "<ul>";
            while($row = $sample->fetch_assoc()) {
                echo "<li>" . $row['course_code'] . " - " . $row['course_name'] . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:orange;'>⚠ Courses table doesn't exist</p>";
            echo "<p>Run: <a href='create_tables.php'>create_tables.php</a> to create tables</p>";
        }
    } else {
        echo "<p style='color:orange;'>⚠ Database '$database' not found</p>";
        echo "<p>Creating database...</p>";
        
        $create_sql = "CREATE DATABASE IF NOT EXISTS $database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($create_sql)) {
            echo "<p style='color:green;'>✓ Database created</p>";
            $conn->select_db($database);
        } else {
            echo "<p style='color:red;'>✗ Failed to create database: " . $conn->error . "</p>";
        }
    }
}

echo "<h3>2. Course API Direct Test</h3>";
echo "<pre>";

// Test course.php directly
$course_api_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/course.php';
echo "Course API URL: " . $course_api_url . "\n\n";

// Create a test request to course.php
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "Content-Type: application/json\r\n"
    ]
]);

try {
    $response = file_get_contents($course_api_url, false, $context);
    echo "Response: " . $response;
} catch (Exception $e) {
    echo "Error accessing course.php: " . $e->getMessage();
    
    // Try to include and run course.php directly
    echo "\n\nTrying to include course.php directly:\n";
    ob_start();
    try {
        require_once 'course.php';
    } catch (Exception $e2) {
        echo "Error in course.php: " . $e2->getMessage();
    }
    $output = ob_get_clean();
    echo $output;
}

echo "</pre>";

echo "<h3>3. Quick Fix</h3>";
echo "<p><a href='quick_fix.php' style='padding: 10px 20px; background: #2ecc71; color: white; text-decoration: none; border-radius: 5px;'>Run Quick Fix</a></p>";

$conn->close();
?>