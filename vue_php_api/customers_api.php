<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$host = "localhost";
$username = "root";
$password = "";
$dbname = "db_shop";  //ชื่อฐานข้อมูล


try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


try {
 //ตรวจสอบคำขอที่ได้รับจาก Client  ตามประเภทของคำ ว่าเป็น GET หรือ POST
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method == 'GET') {
        // ดึงข้อมูลลูกค้าทั้งหมด
        $stmt = $conn->prepare("SELECT * FROM Customers");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $data]);
    }else {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
      }

}catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
