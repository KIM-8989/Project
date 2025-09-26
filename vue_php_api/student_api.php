<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
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

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // ถ้ามี action=delete ใน query string
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            deleteStudent($pdo, $_GET['id']);
        } else {
            // ดึงข้อมูลนักเรียนทั้งหมด
            getStudents($pdo);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'delete') {
            deleteStudent($pdo, $input['student_id']);
        } else {
            // Handle other POST operations like adding new student
            addStudent($pdo, $input);
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteStudent($pdo, $_GET['id']);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['student_id'])) {
                deleteStudent($pdo, $input['student_id']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Student ID is required']);
            }
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

// ฟังก์ชันดึงข้อมูลนักเรียนทั้งหมด
function getStudents($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM students ORDER BY student_id");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $students
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching students: ' . $e->getMessage()
        ]);
    }
}

// ฟังก์ชันลบข้อมูลนักเรียน
function deleteStudent($pdo, $studentId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = :id");
        $stmt->bindParam(':id', $studentId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'ลบข้อมูลนักเรียนสำเร็จ'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลนักเรียนที่ต้องการลบ'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ไม่สามารถลบข้อมูลได้'
            ]);
        }
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// ฟังก์ชันเพิ่มนักเรียนใหม่ (สำหรับอนาคต)
function addStudent($pdo, $data) {
    try {
        $stmt = $pdo->prepare("INSERT INTO students (first_name, last_name, email, phone) VALUES (:first_name, :last_name, :email, :phone)");
        $stmt->bindParam(':first_name', $data['first_name']);
        $stmt->bindParam(':last_name', $data['last_name']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':phone', $data['phone']);
        
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
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>