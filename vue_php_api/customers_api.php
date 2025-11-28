<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// เชื่อมต่อฐานข้อมูล
$host = 'localhost';
$dbname = 'profile_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// GET - ดึงข้อมูลพนักงานทั้งหมด
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
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// POST - สำหรับเพิ่มและลบพนักงาน
else if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // ADD - เพิ่มพนักงานใหม่
    if ($action === 'add') {
        try {
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            // Handle image upload
            $image = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image = uploadImage($_FILES['image']);
            }
            
            $sql = "INSERT INTO employees (first_name, last_name, address, phone, image) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$first_name, $last_name, $address, $phone, $image]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'เพิ่มพนักงานสำเร็จ'
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }
    
    // UPDATE - แก้ไขข้อมูลพนักงาน
    else if ($action === 'update') {
        try {
            $emp_id = $_POST['emp_id'] ?? '';
            $first_name = $_POST['first_name'] ?? '';
            $last_name = $_POST['last_name'] ?? '';
            $address = $_POST['address'] ?? '';
            $phone = $_POST['phone'] ?? '';
            
            // Handle image upload
            $image = $_POST['current_image'] ?? '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $image = uploadImage($_FILES['image']);
                // ลบรูปเก่าถ้ามี
                if (!empty($_POST['current_image'])) {
                    deleteImage($_POST['current_image']);
                }
            }
            
            $sql = "UPDATE employees SET first_name = ?, last_name = ?, address = ?, phone = ?, image = ? WHERE emp_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$first_name, $last_name, $address, $phone, $image, $emp_id]);
            
            echo json_encode([
                'success' => true, 
                'message' => 'อัปเดตข้อมูลพนักงานสำเร็จ'
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    }
    
    // DELETE - ลบพนักงาน
    else if ($action === 'delete') {
        try {
            $emp_id = $_POST['emp_id'] ?? '';
            
            // ดึงข้อมูลรูปภาพก่อนลบ
            $stmt = $conn->prepare("SELECT image FROM employees WHERE emp_id = ?");
            $stmt->execute([$emp_id]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // ลบข้อมูลพนักงาน
            $stmt = $conn->prepare("DELETE FROM employees WHERE emp_id = ?");
            $stmt->execute([$emp_id]);
            
            // ลบรูปภาพถ้ามี
            if ($employee && !empty($employee['image'])) {
                deleteImage($employee['image']);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'ลบพนักงานสำเร็จ'
            ]);
            
        } catch (PDOException $e) {
            echo json_encode([
                'success' => false, 
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid action'
        ]);
    }
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Invalid request method'
    ]);
}

// ฟังก์ชันอัพโหลดรูปภาพ
function uploadImage($file) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file['type'], $allowedTypes)) {
        return '';
    }
    
    if ($file['size'] > $maxSize) {
        return '';
    }
    
    $uploadDir = '../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return $filename;
    }
    
    return '';
}

// ฟังก์ชันลบรูปภาพ
function deleteImage($imageName) {
    $uploadDir = '../uploads/';
    $filePath = $uploadDir . $imageName;
    
    if (file_exists($filePath) && is_file($filePath)) {
        unlink($filePath);
    }
}
?>