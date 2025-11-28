<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// เชื่อมต่อฐานข้อมูล
include_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $employee_name = $_POST['employee_name'] ?? '';
        $department = $_POST['department'] ?? '';
        $position = $_POST['position'] ?? '';
        $salary = $_POST['salary'] ?? 0;
        
        // จัดการอัปโหลดรูปภาพ
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "uploads/";
            
            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $image = uniqid() . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $image;
            
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                throw new Exception("ไม่สามารถอัปโหลดรูปภาพได้");
            }
        }
        
        // เพิ่มข้อมูลลงฐานข้อมูล
        $sql = "INSERT INTO employees (employee_name, department, position, salary, image) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$employee_name, $department, $position, $salary, $image]);
        
        echo json_encode([
            'success' => true,
            'message' => 'เพิ่มพนักงานสำเร็จ'
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?>