<?php
// db.php
$host = 'localhost';
$dbname = 'edulingo';
$username = 'root';
$password = ''; // Mặc định XAMPP để trống

try {
    // Khởi tạo PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Gán thêm biến $conn để tránh xung đột tên
    $conn = $pdo; 
} catch (PDOException $e) {
    die("Lỗi kết nối CSDL: " . $e->getMessage());
}
?>