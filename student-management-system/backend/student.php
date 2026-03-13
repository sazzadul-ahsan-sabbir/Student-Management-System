<?php
// ============================================
// Student Management API
// ============================================

// Set headers for CORS and JSON response
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include database configuration
require_once 'config.php';

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// ============================================
// Handle GET requests
// ============================================
if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Get single student by ID
        $id = intval($_GET['id']);
        $sql = "SELECT * FROM students WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode($result->fetch_assoc());
        } else {
            echo json_encode(["error" => "Student not found"]);
        }
        $stmt->close();
    } else {
        // Get all students
        $sql = "SELECT * FROM students ORDER BY id DESC";
        $result = $conn->query($sql);
        
        $students = [];
        while($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        
        echo json_encode($students);
    }
}

// ============================================
// Handle POST requests (Add new student)
// ============================================
elseif ($method == 'POST') {
    // Get JSON data from request
    $data = json_decode(file_get_contents("php://input"), true);
    
    // If no JSON data, try form data
    if (!$data) {
        $data = $_POST;
    }
    
    // Check required fields
    if (empty($data['student_id']) || empty($data['name']) || empty($data['email'])) {
        echo json_encode([
            "success" => false,
            "message" => "Required fields missing: student_id, name, email"
        ]);
        exit;
    }
    
    // Prepare data
    $student_id = $conn->real_escape_string($data['student_id']);
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = isset($data['phone']) ? $conn->real_escape_string($data['phone']) : '';
    $department = isset($data['department']) ? $conn->real_escape_string($data['department']) : '';
    $semester = isset($data['semester']) ? intval($data['semester']) : 1;
    $address = isset($data['address']) ? $conn->real_escape_string($data['address']) : '';
    
    // Check if student_id already exists
    $check_sql = "SELECT id FROM students WHERE student_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Student ID already exists"
        ]);
        $check_stmt->close();
        exit;
    }
    $check_stmt->close();
    
    // Check if email already exists
    $check_email_sql = "SELECT id FROM students WHERE email = ?";
    $check_email_stmt = $conn->prepare($check_email_sql);
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    
    if ($check_email_result->num_rows > 0) {
        echo json_encode([
            "success" => false,
            "message" => "Email already exists"
        ]);
        $check_email_stmt->close();
        exit;
    }
    $check_email_stmt->close();
    
    // Insert new student
    $sql = "INSERT INTO students (student_id, name, email, phone, department, semester, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssis", $student_id, $name, $email, $phone, $department, $semester, $address);
    
    if ($stmt->execute()) {
        $new_id = $stmt->insert_id;
        echo json_encode([
            "success" => true,
            "message" => "Student added successfully",
            "student_id" => $new_id
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Failed to add student: " . $stmt->error
        ]);
    }
    
    $stmt->close();
}

// ============================================
// Handle PUT requests (Update student)
// ============================================
elseif ($method == 'PUT') {
    // Get data from request
    parse_str(file_get_contents("php://input"), $data);
    
    if (empty($data['id'])) {
        echo json_encode(["success" => false, "message" => "Student ID required"]);
        exit;
    }
    
    $id = intval($data['id']);
    $name = $conn->real_escape_string($data['name']);
    $email = $conn->real_escape_string($data['email']);
    $phone = $conn->real_escape_string($data['phone']);
    $department = $conn->real_escape_string($data['department']);
    $semester = intval($data['semester']);
    $address = $conn->real_escape_string($data['address']);
    
    $sql = "UPDATE students SET name=?, email=?, phone=?, department=?, semester=?, address=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssisi", $name, $email, $phone, $department, $semester, $address, $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Student updated successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to update student"]);
    }
    
    $stmt->close();
}

// ============================================
// Handle DELETE requests
// ============================================
elseif ($method == 'DELETE') {
    if (!isset($_GET['id'])) {
        echo json_encode(["success" => false, "message" => "Student ID required"]);
        exit;
    }
    
    $id = intval($_GET['id']);
    
    $sql = "DELETE FROM students WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Student deleted successfully"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to delete student"]);
    }
    
    $stmt->close();
}

// ============================================
// Handle invalid methods
// ============================================
else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

// Close database connection
$conn->close();
?>