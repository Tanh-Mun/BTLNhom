<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { 
    $pdo = $conn; 
}

// Bắt chính xác ID sinh viên từ tất cả các kiểu Session phổ biến
$student_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;

if (!$student_id) {
    header('Location: dangnhap.php');
    exit;
}

// Lấy thông tin tên sinh viên
$student_name = $_SESSION['user']['fullname'] ?? $_SESSION['user_name'] ?? $_SESSION['fullname'] ?? 'Học viên';
try {
    $stmtUser = $pdo->prepare("SELECT fullname, username FROM users WHERE id = ?");
    $stmtUser->execute([$student_id]);
    $currentUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if (!empty($currentUser['fullname'])) {
        $student_name = trim($currentUser['fullname']);
    } elseif (!empty($currentUser['username'])) {
        $student_name = trim($currentUser['username']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
}

$name_parts = explode(' ', $student_name);
$first_name = end($name_parts);
$avatar_letter = mb_strtoupper(mb_substr($first_name, 0, 1, 'UTF-8'), 'UTF-8');

// Lấy danh sách cuộc hẹn status = 'completed' và CHƯA CÓ trong bảng reviews
$appointments = [];
if (isset($pdo)) {
    try {
        $sql = "
            SELECT a.appointment_id, 
                   COALESCE(u.fullname, u.username, 'Giảng viên') AS teacher_name, 
                   ts.topic, 
                   ts.start_time
            FROM appointments a
            JOIN time_slots ts ON a.slot_id = ts.slot_id
            LEFT JOIN users u ON ts.lecturer_id = u.id
            LEFT JOIN reviews r ON a.appointment_id = r.appointment_id
            WHERE LOWER(a.status) = 'completed' 
              AND r.id IS NULL 
              AND a.student_id = ?
            ORDER BY ts.start_time DESC
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$student_id]);
        $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Lỗi truy vấn: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Đánh Giá - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #d81b60; --primary-light: #fdf2f5; --border-color: #f8bbd0; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--primary-light); color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        .header { background-color: var(--primary-color); color: white; padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .header-text h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .header-text p { font-size: 13px; opacity: 0.9; }
        .user-dropdown-container { position: relative; display: inline-block; }
        .user-profile-icon { display: flex; align-items: center; gap: 10px; color: white; background-color: rgba(255, 255, 255, 0.15); padding: 6px 14px 6px 8px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.3); text-decoration: none; font-size: 14px; font-weight: 600; cursor: pointer; }
        .avatar-circle { width: 34px; height: 34px; background-color: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); background-color: white; min-width: 170px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); padding: 8px 0; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: 600; }
        .dropdown-item:hover { background-color: #fce4ec; }
        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; width: 100%; flex: 1; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 20px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; color: var(--primary-color); font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background: var(--primary-color); color: white; }
        .content-box { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; }
        .section-title { color: var(--primary-color); font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        .card-item { background-color: white; border-radius: 12px; padding: 18px 25px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; border: 1px solid var(--border-color); }
        .teacher-title { font-size: 16px; font-weight: bold; color: var(--primary-color); display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
        .status-tag { background-color: #d4edda; color: #155724; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 4px; }
        .info-text { font-size: 13px; color: #555; display: flex; align-items: center; gap: 6px; margin-top: 2px; }
        .btn-review { background-color: var(--primary-color); color: white; border: none; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: bold; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-review:hover { background-color: #c2185b; }
        .footer { background-color: var(--primary-color); color: white; padding: 40px 60px; margin-top: 40px; }
        .footer-grid { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 30px; }
        .footer-col h5 { font-size: 12px; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; }
        .footer-col p { font-size: 12px; line-height: 1.6; opacity: 0.95; margin-bottom: 15px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: white; text-decoration: none; font-size: 12px; opacity: 0.95; }
        .social-icons { display: flex; gap: 10px; }
        .social-btn { width: 32px; height: 32px; background: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-brand">
            <div class="logo-box">ABC</div>
            <div class="header-text">
                <h2>EDULINGO</h2>
                <p>Hệ thống đặt lịch tư vấn giảng viên</p>
            </div>
        </div>
        <div class="user-dropdown-container">
            <div class="user-profile-icon" id="userBtn">
                <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
                <span class="user-name"><?= htmlspecialchars($student_name) ?></span>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <a href="Hososv.php" class="dropdown-item"><i class="fa-regular fa-id-card"></i> Xem hồ sơ</a>
                <a href="dangnhap.php" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="nav-tabs">
            <a href="timvadatlich.php" class="tab-btn"><i class="fa-regular fa-calendar-check"></i> Tìm & Đặt lịch</a>
            <a href="lichhen.php" class="tab-btn"><i class="fa-regular fa-user"></i> Lịch của tôi</a>
            <a href="danhgia.php" class="tab-btn active"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>

        <div class="content-box">
            <h3 class="section-title">Danh sách cần đánh giá</h3>
            <?php if (!empty($appointments)): ?>
                <?php foreach ($appointments as $item): ?>
                    <div class="card-item">
                        <div>
                            <div class="teacher-title">
                                Giảng viên: <?= htmlspecialchars($item['teacher_name']) ?>
                                <span class="status-tag">Đã hoàn thành</span>
                            </div>
                            <div class="info-text">Chủ đề: <?= htmlspecialchars($item['topic'] ?? 'Tư vấn học tập') ?></div>
                            <div class="info-text">
                                <i class="fa-regular fa-clock"></i> <?= !empty($item['start_time']) ? date('d/m/Y H:i', strtotime($item['start_time'])) : date('d/m/Y H:i') ?>
                            </div>
                        </div>
                        <div>
                            <a href="vietdanhgia.php?id=<?= $item['appointment_id'] ?>" class="btn-review"><i class="fa-regular fa-star"></i> Viết đánh giá</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666; font-size: 14px;">Chưa có buổi tư vấn nào đã hoàn thành cần đánh giá!</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="header-brand" style="margin-bottom: 12px;">
                    <div class="logo-box">ABC</div>
                    <span style="font-weight: bold; font-size: 16px;">EDULINGO</span>
                </div>
                <p>Hệ thống đặt lịch tư vấn giảng viên ngoại ngữ, giúp sinh viên tìm và đặt buổi gặp chỉ trong vài bước</p>
                <div class="social-icons">
                    <a href="#" class="social-btn">FB</a>
                    <a href="#" class="social-btn">ZL</a>
                    <a href="#" class="social-btn"><i class="fa-regular fa-envelope"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h5>KHÁM PHÁ</h5>
                <ul class="footer-links">
                    <li><a href="timvadatlich.php">Tìm giảng viên</a></li>
                    <li><a href="danhgia.php">Đánh giá</a></li>
                    <li><a href="#">Ngôn ngữ hỗ trợ</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>DÀNH CHO GIẢNG VIÊN</h5>
                <ul class="footer-links">
                    <li><a href="#">Đăng ký giảng dạy</a></li>
                    <li><a href="#">Quản lý khung giờ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>NHẬN THÔNG BÁO</h5>
                <p>Nhận tin khi có giảng viên mới hoặc khung giờ mới mở</p>
            </div>
        </div>
    </footer>

    <script>
        const userBtn = document.getElementById('userBtn');
        const userDropdown = document.getElementById('userDropdown');

        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
    </script>
</body>
</html>