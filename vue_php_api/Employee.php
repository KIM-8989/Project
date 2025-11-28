<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'database.php';

if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// ✅ บังคับให้ใช้ UTF-8
$conn->exec("SET NAMES utf8mb4");
$conn->exec("SET CHARACTER SET utf8mb4");

$method = $_SERVER['REQUEST_METHOD'];

// ========== GET - ดึงข้อมูลพนักงาน (ข้อ 2.1) ==========
if ($method === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT emp_id, first_name, last_name, address, phone, image FROM employees ORDER BY emp_id ASC");
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $employees
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}

// ========== POST - เพิ่ม/แก้ไข/ลบ พนักงาน ==========
else if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ========== ADD - เพิ่มพนักงาน (ข้อ 2.2) ==========
    if ($action === 'add') {
        try {
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $image_name = '';

            // จัดการอัพโหลดรูปภาพ
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = 'uploads/' . uniqid() . '.' . $file_extension;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $image_name)) {
                    echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัพโหลดรูปภาพได้']);
                    exit();
                }
            }

            $stmt = $conn->prepare("INSERT INTO employees (first_name, last_name, address, phone, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $address, $phone, $image_name]);
            
            echo json_encode([
                'success' => true,
                'message' => 'เพิ่มพนักงานสำเร็จ',
                'emp_id' => $conn->lastInsertId()
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    // ========== UPDATE - แก้ไขพนักงาน (ข้อ 2.3) ==========
    else if ($action === 'update') {
        try {
            $emp_id = $_POST['emp_id'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $old_image = $_POST['old_image'] ?? '';
            $image_name = $old_image;

            // จัดการรูปภาพใหม่
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                // ลบรูปเก่า
                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }
                
                $upload_dir = 'uploads/';
                $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = 'uploads/' . uniqid() . '.' . $file_extension;
                move_uploaded_file($_FILES['image']['tmp_name'], $image_name);
            }

            $stmt = $conn->prepare("UPDATE employees SET first_name = ?, last_name = ?, address = ?, phone = ?, image = ? WHERE emp_id = ?");
            $stmt->execute([$first_name, $last_name, $address, $phone, $image_name, $emp_id]);
            
            echo json_encode([
                'success' => true,
                'message' => 'แก้ไขข้อมูลสำเร็จ'
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
    
    // ========== DELETE - ลบพนักงาน (ข้อ 2.4) ==========
    else if ($action === 'delete') {
        try {
            $emp_id = $_POST['emp_id'] ?? '';
            
            // ดึงข้อมูลรูปภาพก่อนลบ
            $stmt = $conn->prepare("SELECT image FROM employees WHERE emp_id = ?");
            $stmt->execute([$emp_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ลบข้อมูล
            $stmt = $conn->prepare("DELETE FROM employees WHERE emp_id = ?");
            $stmt->execute([$emp_id]);
            
            // ลบรูปภาพ
            if ($employee && $employee['image'] && file_exists($employee['image'])) {
                unlink($employee['image']);
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'ลบพนักงานสำเร็จ'
            ]);
            
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>