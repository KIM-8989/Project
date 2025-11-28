<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'database.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET - ดึงข้อมูลนักเรียน
if ($method === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT student_id, first_name, last_name, email, phone FROM students ORDER BY student_id ASC");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $students
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// POST - สำหรับ update และ delete
else if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // UPDATE
    if ($action === 'update') {
        try {
            $student_id = $_POST['student_id'];
            $first_name = $_POST['first_name'];
            $last_name = $_POST['last_name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            
            $sql = "UPDATE students SET first_name = ?, last_name = ?, email = ?, phone = ? WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$first_name, $last_name, $email, $phone, $student_id]);
            
            echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลนักเรียนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    
    // DELETE
    else if ($action === 'delete') {
        try {
            $student_id = $_POST['student_id'];
            
            // ลบข้อมูล
            $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
            $stmt->execute([$student_id]);
            
            // เรียง ID ใหม่หลังลบ
            $conn->exec("SET @count = 0");
            $conn->exec("UPDATE students SET student_id = @count:= @count + 1 ORDER BY student_id");
            $conn->exec("ALTER TABLE students AUTO_INCREMENT = 1");
            
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลนักเรียนสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>