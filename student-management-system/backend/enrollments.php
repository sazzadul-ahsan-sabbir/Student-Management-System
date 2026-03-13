<?php
// enrollments.php ফাইল
require_once 'config.php'; // কানেকশন ফাইল

header('Content-Type: application/json');

$response = [];

try {
    // SQL কোয়েরি
    $sql = "SELECT 
                e.id as enrollment_id,
                e.enrollment_date,
                e.status,
                s.id as student_id,
                s.name as student_name,
                s.email,
                s.phone,
                c.id as course_id,
                c.course_code,
                c.course_name,
                c.instructor,
                c.credits
            FROM enrollments e
            INNER JOIN students s ON e.student_id = s.id
            INNER JOIN courses c ON e.course_id = c.id
            ORDER BY e.enrollment_date DESC";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $enrollments = [];
        while($row = $result->fetch_assoc()) {
            $enrollments[] = $row;
        }
        $response = [
            'success' => true,
            'enrollments' => $enrollments,
            'count' => count($enrollments)
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'No enrollment records found'
        ];
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$conn->close();
?>