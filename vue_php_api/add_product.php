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
        $product_name = $_POST['product_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $price = $_POST['price'] ?? 0;
        $stock = $_POST['stock'] ?? 0;
        
        // ตรวจสอบข้อมูล
        if (empty($product_name)) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อสินค้า']);
            exit();
        }
        
        // จัดการรูปภาพ
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "uploads/";
            
            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // สร้างชื่อไฟล์ใหม่เพื่อป้องกันชื่อซ้ำ
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $image = uniqid() . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $image;
            
            // ตรวจสอบประเภทไฟล์
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (!in_array(strtolower($file_extension), $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'อนุญาตเฉพาะไฟล์ภาพ (jpg, jpeg, png, gif)']);
                exit();
            }
            
            // อัปโหลดไฟล์
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถอัปโหลดรูปภาพได้']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'กรุณาเลือกรูปภาพ']);
            exit();
        }
        
        // เพิ่มสินค้าใหม่
        $sql = "INSERT INTO products (product_name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$product_name, $description, $price, $stock, $image]);
        
        // ⭐ เรียง ID ใหม่ทุกครั้งหลังเพิ่มสินค้า
        $conn->exec("SET @count = 0");
        $conn->exec("UPDATE products SET product_id = @count:= @count + 1 ORDER BY product_id");
        $conn->exec("ALTER TABLE products AUTO_INCREMENT = 1");
        
        echo json_encode(['success' => true, 'message' => 'เพิ่มสินค้าสำเร็จ']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>