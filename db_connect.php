<?php
$host = "localhost"; // Hoặc IP máy chủ SQL Server
$dbname = "WebTA";
$username = "sa"; // Tên đăng nhập SQL Server (VD: sa)
$password = "123456"; // Mật khẩu SQL Server

try {
    // Kết nối CSDL SQL Server bằng PDO Driver
    $conn = new PDO("sqlsrv:Server=$host;Database=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Thông báo khi kết nối thành công
    echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin-bottom: 15px; border-radius: 5px;'>Kết nối cơ sở dữ liệu EDULINGO thành công!</div>";

} catch (PDOException $e) {
    die("<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin-bottom: 15px; border-radius: 5px;'>Lỗi kết nối cơ sở dữ liệu EDULINGO: " . $e->getMessage() . "</div>");
}
?>