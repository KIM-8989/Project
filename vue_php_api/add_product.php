<?php
// ✅ CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Content-Type: application/json; charset=UTF-8');

// ✅ Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ ป้องกัน error output
error_reporting(0);
ob_start();

$response = ["success" => false, "message" => ""];

try {
    // ✅ เชื่อมต่อฐานข้อมูล (ใช้ database.php)
    include 'database.php';
    
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        // ✅ ตรวจสอบข้อมูลที่จำเป็น
        if (empty($_POST["product_name"]) || empty($_POST["description"]) || 
            empty($_POST["price"]) || empty($_POST["stock"])) {
            $response["message"] = "กรุณากรอกข้อมูลให้ครบถ้วน";
        }
        else if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
            $response["message"] = "กรุณาเลือกรูปภาพ";
        }
        else {
            $product_name = trim($_POST["product_name"]);
            $description  = trim($_POST["description"]);
            $price        = floatval($_POST["price"]);
            $stock        = intval($_POST["stock"]);

            // ✅ จัดการอัพโหลดรูปภาพ
            $upload_dir = "./uploads/";
            
            // สร้างโฟลเดอร์ถ้าไม่มี
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            // ตรวจสอบประเภทไฟล์
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            
            if (!in_array($file_extension, $allowed_types)) {
                $response["message"] = "ประเภทไฟล์ไม่ถูกต้อง (อนุญาตเฉพาะ jpg, png, gif)";
            }
            else {
                $filename = time() . "_" . uniqid() . "." . $file_extension;
                $target_file = $upload_dir . $filename;

                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // ✅ บันทึกข้อมูลลงฐานข้อมูล
                    $stmt = $conn->prepare("INSERT INTO Products (product_name, description, price, stock, image) VALUES (?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$product_name, $description, $price, $stock, $filename])) {
                        $response["success"] = true;
                        $response["message"] = "บันทึกสินค้าเรียบร้อยแล้ว";
                    } else {
                        $response["message"] = "เกิดข้อผิดพลาดในการบันทึกข้อมูล";
                    }
                } else {
                    $response["message"] = "อัปโหลดไฟล์ไม่สำเร็จ";
                }
            }
        }
    } else {
        $response["message"] = "Method ไม่ถูกต้อง - ต้องใช้ POST";
    }

} catch (Exception $e) {
    $response["message"] = "เกิดข้อผิดพลาด: " . $e->getMessage();
}

// ✅ เคลียร์ output buffer และส่ง JSON
ob_clean();
echo json_encode($response, JSON_UNESCAPED_UNICODE);
exit();
?>