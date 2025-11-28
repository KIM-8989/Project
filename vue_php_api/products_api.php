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

// GET - ดึงข้อมูลสินค้า
if ($method === 'GET') {
    try {
        $stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id ASC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $products
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
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            
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
                $sql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock = ?, image = ? WHERE product_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$product_name, $description, $price, $stock, $image, $product_id]);
            } else {
                $sql = "UPDATE products SET product_name = ?, description = ?, price = ?, stock = ? WHERE product_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$product_name, $description, $price, $stock, $product_id]);
            }
            
            echo json_encode(['message' => 'อัปเดตสินค้าสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
    
    // DELETE
    else if ($action === 'delete') {
        try {
            $product_id = $_POST['product_id'];
            
            // ลบรูปภาพ
            $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($row) {
                $image_path = "uploads/" . $row['image'];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // ลบข้อมูล
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            
            // ⭐ เรียง ID ใหม่หลังลบ
            $conn->exec("SET @count = 0");
            $conn->exec("UPDATE products SET product_id = @count:= @count + 1 ORDER BY product_id");
            $conn->exec("ALTER TABLE products AUTO_INCREMENT = 1");
            
            echo json_encode(['message' => 'ลบสินค้าสำเร็จ']);
        } catch (PDOException $e) {
            echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
        }
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>