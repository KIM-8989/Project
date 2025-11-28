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

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $first_name = $_POST['first_name'] ?? '';
        $last_name = $_POST['last_name'] ?? '';
        $address = $_POST['address'] ?? '';
        $phone = $_POST['phone'] ?? '';
        
        // ตรวจสอบข้อมูลที่จำเป็น
        if (empty($first_name) || empty($last_name)) {
            echo json_encode([
                'success' => false,
                'message' => 'ชื่อและนามสกุลต้องไม่ว่างเปล่า'
            ]);
            exit();
        }
        
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
        $sql = "INSERT INTO employees (first_name, last_name, address, phone, image) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$first_name, $last_name, $address, $phone, $image]);
        
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