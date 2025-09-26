<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
$host = 'localhost';
$dbname = 'db_shop';
$username = 'root';
$password = '';

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
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            deleteCustomer($pdo, $_GET['id']);
        } else {
            getCustomers($pdo);
        }
        break;
        
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'delete') {
            deleteCustomer($pdo, $input['customer_id']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid POST action']);
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            deleteCustomer($pdo, $_GET['id']);
        } else {
            $input = json_decode(file_get_contents('php://input'), true);
            if (isset($input['customer_id'])) {
                deleteCustomer($pdo, $input['customer_id']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Customer ID is required']);
            }
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
}

function getCustomers($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY customer_id");
        $stmt->execute();
        $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'data' => $customers
        ]);
    } catch(PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error fetching customers: ' . $e->getMessage()
        ]);
    }
}

function deleteCustomer($pdo, $customerId) {
    try {
        $stmt = $pdo->prepare("DELETE FROM customers WHERE customer_id = :id");
        $stmt->bindParam(':id', $customerId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'ลบข้อมูลลูกค้าสำเร็จ'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ไม่พบข้อมูลลูกค้าที่ต้องการลบ'
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
?>