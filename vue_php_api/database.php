<?php
$host = "localhost";
$username = "root";
$password = "";
$dbname = "profile_db";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // ✅ ไม่ใช้ die() เพราะจะทำลาย JSON format
    error_log("Database connection failed: " . $e->getMessage());
    $conn = null;
}