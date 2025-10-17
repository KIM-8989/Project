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

// GET - ดึงข้อมูลพนักงาน
if ($method === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT * FROM employees ORDER BY employee_id ASC");
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $employees
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
            $employee_id = $_POST['employee_id'];
            $employee_name = $_POST['employee_name'];
            $department = $_POST['department'];
            $position = $_POST['position'];
            $salary = $_POST['salary'];
            
            // จัดการรูปภาพ
            $image = $_POST['old_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $target_dir = "uploads/";
                
                // ลบรูปเก่า (ถ้ามี)
                if (!empty($_POST['old_image'])) {
                    $old_image_path = $target_dir . $_POST['old_image'];
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                
                // อัปโหลดรูปใหม่
                $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $image = uniqid() . '_' . time() . '.' . $file_extension;
                $target_file = $target_dir . $image;
                move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
            }
            
            if ($image) {
                $sql = "UPDATE employees SET employee_name = ?, department = ?, position = ?, salary = ?, image = ? WHERE employee_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$employee_name, $department, $position, $salary, $image, $employee_id]);
            } else {
                $sql = "UPDATE employees SET employee_name = ?, department = ?, position = ?, salary = ? WHERE employee_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$employee_name, $department, $position, $salary, $employee_id]);
            }
            
            echo json_encode(['message' => 'อัปเดตพนักงานสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    
    // DELETE
    else if ($action === 'delete') {
        try {
            $employee_id = $_POST['employee_id'];
            
            // ลบรูปภาพ
            $stmt = $conn->prepare("SELECT image FROM employees WHERE employee_id = ?");
            $stmt->execute([$employee_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $image_path = "uploads/" . $row['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // ลบข้อมูล
            $stmt = $conn->prepare("DELETE FROM employees WHERE employee_id = ?");
            $stmt->execute([$employee_id]);
            
            // ⭐ เรียง ID ใหม่หลังลบ
            $conn->exec("SET @count = 0");
            $conn->exec("UPDATE employees SET employee_id = @count:= @count + 1 ORDER BY employee_id");
            $conn->exec("ALTER TABLE employees AUTO_INCREMENT = 1");
            
            echo json_encode(['message' => 'ลบพนักงานสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
