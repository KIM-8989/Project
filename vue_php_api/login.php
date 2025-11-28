<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$input = json_decode(file_get_contents('php://input'), true);

$username = $input['username'] ?? '';
$password = $input['password'] ?? '';
$role = $input['role'] ?? 'customer';

// Validation
if (empty($username) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
    exit();
}

$conn = new mysqli("localhost", "root", "", "db_shop");

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อฐานข้อมูลได้']);
    exit();
}

// เลือก table ตาม role
if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? AND status='active'");
} else {
    $stmt = $conn->prepare("SELECT * FROM customers WHERE username=?");
}

$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // ตรวจสอบรหัสผ่าน (รองรับทั้ง hashed และ plain text)
    if (password_verify($password, $user['password']) || $password === $user['password']) {
        // ส่งข้อมูลกลับไป (ไม่ส่ง password)
        unset($user['password']); // ลบ password ออกก่อนส่งกลับ
        
        // เพิ่ม role ให้ชัดเจน
        $user['role'] = $role;
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'เข้าสู่ระบบสำเร็จ',
            'user' => $user
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'รหัสผ่านไม่ถูกต้อง']);
    }
} else {
    if ($role === 'admin') {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบบัญชีผู้ดูแลระบบหรือบัญชีถูกระงับ']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ไม่พบบัญชีผู้ใช้']);
    }
}

$stmt->close();
$conn->close();
?>