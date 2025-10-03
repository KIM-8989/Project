<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// เชื่อมต่อฐานข้อมูล
include_once 'database.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET - ดึงข้อมูลลูกค้า
if ($method === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT customer_id, firstName, lastName, phone, username FROM customers ORDER BY customer_id ASC");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $customers
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
            $customer_id = $_POST['customer_id'];
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $phone = $_POST['phone'];
            $username = $_POST['username'];
            
            // ตรวจสอบว่ามีการเปลี่ยนรหัสผ่านหรือไม่
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $sql = "UPDATE customers SET firstName = ?, lastName = ?, phone = ?, username = ?, password = ? WHERE customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$firstName, $lastName, $phone, $username, $password, $customer_id]);
            } else {
                $sql = "UPDATE customers SET firstName = ?, lastName = ?, phone = ?, username = ? WHERE customer_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$firstName, $lastName, $phone, $username, $customer_id]);
            }
            
            echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลลูกค้าสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    
    // DELETE
    else if ($action === 'delete') {
        try {
            $customer_id = $_POST['customer_id'];
            
            // ลบข้อมูล
            $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
            $stmt->execute([$customer_id]);
            
            // ⭐ เรียง ID ใหม่หลังลบ
            $conn->exec("SET @count = 0");
            $conn->exec("UPDATE customers SET customer_id = @count:= @count + 1 ORDER BY customer_id");
            $conn->exec("ALTER TABLE customers AUTO_INCREMENT = 1");
            
            echo json_encode(['success' => true, 'message' => 'ลบข้อมูลลูกค้าสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>