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
    $sql = "SELECT * FROM products ORDER BY product_id DESC";
    $result = $conn->query($sql);
    
    $products = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
}

// POST - สำหรับ update และ delete
else if ($method === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // UPDATE
    if ($action === 'update') {
        $product_id = $_POST['product_id'];
        $product_name = $_POST['product_name'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        
        // จัดการรูปภาพ
        $image = $_POST['old_image'] ?? '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $target_dir = "uploads/";
            $image = basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $image;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }
        
        $sql = "UPDATE products SET 
                product_name = ?, 
                description = ?, 
                price = ?, 
                stock = ?" . 
                ($image ? ", image = ?" : "") . 
                " WHERE product_id = ?";
        
        $stmt = $conn->prepare($sql);
        if ($image) {
            $stmt->bind_param("ssdisi", $product_name, $description, $price, $stock, $image, $product_id);
        } else {
            $stmt->bind_param("ssdii", $product_name, $description, $price, $stock, $product_id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'อัปเดตสินค้าสำเร็จ']);
        } else {
            echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
        }
    }
    
    // DELETE
    else if ($action === 'delete') {
        $product_id = $_POST['product_id'];
        
        // ลบรูปภาพ (ถ้าต้องการ)
        $sql = "SELECT image FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $image_path = "uploads/" . $row['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // ลบข้อมูล
        $sql = "DELETE FROM products WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            echo json_encode(['message' => 'ลบสินค้าสำเร็จ']);
        } else {
            echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $conn->error]);
        }
    }
}

$conn->close();
?>