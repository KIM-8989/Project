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

// Database connection (แก้ไขตามการตั้งค่าของคุณ)
$host = 'localhost';
$dbname = 'db_shop';  // แก้เป็นชื่อฐานข้อมูลจริง
$username = 'root';              // แก้เป็น username ของฐานข้อมูล
$password = '';                  // แก้เป็น password ของฐานข้อมูล

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit();
}

// รับข้อมูลจาก POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput); // Debug log
    
    $input = json_decode($rawInput, true);
    error_log("Parsed input: " . print_r($input, true)); // Debug log
    
    // ตรวจสอบข้อมูลที่จำเป็น
    if (!isset($input['first_name']) || !isset($input['last_name']) || 
        !isset($input['email']) || !isset($input['phone'])) {
        error_log("Missing required fields"); // Debug log
        echo json_encode([
            'success' => false, 
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน - ข้อมูลที่ได้รับไม่ครบ'
        ]);
        exit();
    }
    
    $firstName = isset($input['first_name']) ? trim($input['first_name']) : '';
    $lastName = isset($input['last_name']) ? trim($input['last_name']) : '';
    $email = isset($input['email']) ? trim($input['email']) : '';
    $phone = isset($input['phone']) ? trim($input['phone']) : '';
    
    error_log("Processed data: firstName=$firstName, lastName=$lastName, email=$email, phone=$phone"); // Debug log
    
    // ตรวจสอบว่าข้อมูลไม่เป็นค่าว่าง
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone)) {
        error_log("Empty fields detected"); // Debug log
        echo json_encode([
            'success' => false, 
            'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน - มีข้อมูลที่เป็นค่าว่าง'
        ]);
        exit();
    }
    
    // ตรวจสอบรูปแบบอีเมล
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false, 
            'message' => 'รูปแบบอีเมลไม่ถูกต้อง'
        ]);
        exit();
    }
    
    try {
        // ตรวจสอบว่า email ซ้ำหรือไม่
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = :email");
        $checkStmt->bindParam(':email', $email);
        $checkStmt->execute();
        
        if ($checkStmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'อีเมลนี้มีอยู่แล้ว กรุณาใช้อีเมลอื่น'
            ]);
            exit();
        }
        
        // ตรวจสอบว่า phone ซ้ำหรือไม่
        $checkPhoneStmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE phone = :phone");
        $checkPhoneStmt->bindParam(':phone', $phone);
        $checkPhoneStmt->execute();
        
        if ($checkPhoneStmt->fetchColumn() > 0) {
            echo json_encode([
                'success' => false, 
                'message' => 'เบอร์โทรนี้มีอยู่แล้ว กรุณาใช้เบอร์อื่น'
            ]);
            exit();
        }
        
        // เพิ่มข้อมูลนักเรียนใหม่
        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone) 
                              VALUES (:first_name, :last_name, :email, :phone)");
        
        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'เพิ่มข้อมูลนักเรียนสำเร็จ',
                'student_id' => $pdo->lastInsertId()
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถเพิ่มข้อมูลได้'
            ]);
        }
        
    } catch(PDOException $e) {
        // ตรวจสอบ error code สำหรับ duplicate entry
        if ($e->getCode() == 23000) {
            if (strpos($e->getMessage(), 'email') !== false) {
                echo json_encode([
                    'success' => false,
                    'message' => 'อีเมลนี้มีอยู่แล้ว กรุณาใช้อีเมลอื่น'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ข้อมูลซ้ำ กรุณาตรวจสอบอีเมลและเบอร์โทร'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
    
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>