<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'config.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Get courses
if ($method == 'GET') {
    if(isset($_GET['course_id'])) {
        // Get single course
        $course_id = intval($_GET['course_id']);
        $sql = "SELECT * FROM courses WHERE course_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $course_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(["error" => "Course not found"]);
        }
        $stmt->close();
    } 
    elseif(isset($_GET['department'])) {
        // Get courses by department
        $department = $conn->real_escape_string($_GET['department']);
        $sql = "SELECT * FROM courses WHERE department = ? ORDER BY semester";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $department);
        $stmt->execute();
        $result = $stmt->get_result();
        $courses = [];
        while($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode($courses);
        $stmt->close();
    }
    else {
        // Get all courses
        $sql = "SELECT * FROM courses ORDER BY department, semester";
        $result = $conn->query($sql);
        $courses = [];
        while($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
        echo json_encode($courses);
    }
}

// POST: Add new course
elseif ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    if(!$data) {
        echo json_encode(["error" => "Invalid data"]);
        exit;
    }
    
    $course_code = $conn->real_escape_string($data['course_code'] ?? '');
    $course_name = $conn->real_escape_string($data['course_name'] ?? '');
    $department = $conn->real_escape_string($data['department'] ?? '');
    $credits = intval($data['credits'] ?? 3);
    $semester = intval($data['semester'] ?? 1);
    $instructor_name = $conn->real_escape_string($data['instructor_name'] ?? '');
    
    // Check if course already exists
    $check_sql = "SELECT * FROM courses WHERE course_code = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $course_code);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if($check_result->num_rows > 0) {
        echo json_encode(["error" => "Course with this code already exists"]);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
    
    $sql = "INSERT INTO courses (course_code, course_name, department, credits, semester, instructor_name) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiis", $course_code, $course_name, $department, $credits, $semester, $instructor_name);
    
    if($stmt->execute()) {
        echo json_encode([
            "success" => true, 
            "message" => "Course added successfully",
            "course_id" => $stmt->insert_id
        ]);
    } else {
        echo json_encode(["error" => "Failed to add course: " . $stmt->error]);
    }
    $stmt->close();
}

// PUT: Update course
elseif ($method == 'PUT') {
    parse_str(file_get_contents("php://input"), $data);
    
    $course_id = intval($data['course_id'] ?? 0);
    $course_code = $conn->real_escape_string($data['course_code'] ?? '');
    $course_name = $conn->real_escape_string($data['course_name'] ?? '');
    $department = $conn->real_escape_string($data['department'] ?? '');
    $credits = intval($data['credits'] ?? 3);
    $semester = intval($data['semester'] ?? 1);
    $instructor_name = $conn->real_escape_string($data['instructor_name'] ?? '');
    
    $sql = "UPDATE courses SET course_code=?, course_name=?, department=?, credits=?, semester=?, instructor_name=? 
            WHERE course_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiisi", $course_code, $course_name, $department, $credits, $semester, $instructor_name, $course_id);
    
    if($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Course updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update course"]);
    }
    $stmt->close();
}

// DELETE: Delete course
elseif ($method == 'DELETE') {
    $course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
    
    if($course_id == 0) {
        echo json_encode(["error" => "Course ID required"]);
        exit;
    }
    
    $sql = "DELETE FROM courses WHERE course_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $course_id);
    
    if($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Course deleted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to delete course"]);
    }
    $stmt->close();
}

else {
    echo json_encode(["error" => "Method not allowed"]);
}

$conn->close();
?>