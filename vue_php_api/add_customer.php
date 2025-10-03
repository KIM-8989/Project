<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // อ่านข้อมูล JSON
        $input = json_decode(file_get_contents('php://input'), true);
        
        $firstName = $input['firstName'] ?? '';
        $lastName = $input['lastName'] ?? '';
        $phone = $input['phone'] ?? '';
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        
        // ตรวจสอบข้อมูล
        if (empty($firstName) || empty($lastName) || empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
            exit();
        }
        
        // ตรวจสอบว่า username ซ้ำหรือไม่
        $stmt = $conn->prepare("SELECT customer_id FROM customers WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'ชื่อผู้ใช้นี้มีอยู่แล้ว']);
            exit();
        }
        
        // เข้ารหัสรหัสผ่าน
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // เพิ่มข้อมูลลูกค้าใหม่
        $sql = "INSERT INTO customers (firstName, lastName, phone, username, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$firstName, $lastName, $phone, $username, $hashedPassword]);
        
        // ⭐ เรียง ID ใหม่หลังเพิ่มข้อมูล
        $conn->exec("SET @count = 0");
        $conn->exec("UPDATE customers SET customer_id = @count:= @count + 1 ORDER BY customer_id");
        $conn->exec("ALTER TABLE customers AUTO_INCREMENT = 1");
        
        echo json_encode(['success' => true, 'message' => 'ลงทะเบียนสำเร็จ']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>