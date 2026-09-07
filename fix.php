<?php
require_once 'db.php';

if (!isset($pdo) && isset($conn)) {
    $pdo = $conn;
}

try {
    $stmt = $pdo->prepare("UPDATE users SET password = '123456'");
    $stmt->execute();
    
    echo "<h2>✅ Cập nhật mật khẩu thành công!</h2>";
    echo "<p>Tất cả tài khoản trong CSDL đã được đổi mật khẩu thành: <strong>123456</strong></p>";
} catch (PDOException $e) {
    echo "<h2>❌ Lỗi: " . $e->getMessage() . "</h2>";
}
?>