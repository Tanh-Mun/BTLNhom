<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

$student_id = $_SESSION['user_id'] ?? 6;

// Lấy thông tin người dùng đang đăng nhập để hiển thị trên Header
$stmtUser = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
$stmtUser->execute([$student_id]);
$currentUser = $stmtUser->fetch();

$student_name = !empty($currentUser['fullname']) ? $currentUser['fullname'] : 'Lê Hoàng Nam';

// Tách chữ cái đại diện Avatar (Lấy chữ 'N' trong 'Nam')
$name_parts = explode(' ', trim($student_name));
$last_word = end($name_parts);
$avatar_letter = !empty($last_word) ? strtoupper(substr($last_word, 0, 1)) : 'N';

// Xử lý khi học viên bấm Đặt lịch
if (isset($_GET['book_slot_id'])) {
    $slot_id = (int)$_GET['book_slot_id'];

    $stmt = $pdo->prepare("INSERT INTO appointments (slot_id, student_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$slot_id, $student_id]);

    header('Location: lichhen.php');
    exit;
}

// Lấy danh sách các khung giờ còn trống
$stmt = $pdo->query("SELECT ts.*, u.fullname AS lecturer_name 
                     FROM time_slots ts 
                     JOIN users u ON ts.lecturer_id = u.id 
                     WHERE ts.status = 'available' 
                     ORDER BY ts.start_time ASC");
$time_slots = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm Giảng Viên & Đặt Lịch - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #f8bbd0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--primary-light); color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        
        /* HEADER UI */
        .header { background-color: var(--primary-color); color: white; padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .header-text h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .header-text p { font-size: 13px; opacity: 0.9; }
        
        /* USER DROPDOWN MENU */
        .user-dropdown-container { position: relative; display: inline-block; }
        .user-profile-icon { display: flex; align-items: center; gap: 10px; color: white; background-color: rgba(255, 255, 255, 0.15); padding: 6px 14px 6px 8px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.3); text-decoration: none; font-size: 14px; font-weight: 600; cursor: pointer; user-select: none; }
        .avatar-circle { width: 34px; height: 34px; background-color: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }

        .dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); background-color: white; min-width: 170px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); padding: 8px 0; z-index: 1000; }
        
        /* Class show kích hoạt khi click */
        .dropdown-menu.show { display: block; }

        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.2s; }
        .dropdown-item:hover { background-color: #fce4ec; }
        .dropdown-item i { font-size: 16px; width: 18px; text-align: center; }

        /* MAIN CONTAINER */
        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; width: 100%; flex: 1; }
        
        /* NAV TABS UI */
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 20px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; color: var(--primary-color); font-size: 13px; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background: var(--primary-color); color: white; }

        /* CONTENT CONTAINER */
        .content-box { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 25px; }
        .section-title { color: var(--primary-color); font-size: 18px; font-weight: bold; margin-bottom: 20px; }
        
        .teacher-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px 25px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .teacher-info h4 { color: var(--primary-color); font-size: 16px; margin-bottom: 4px; font-weight: bold; }
        .teacher-topic { color: var(--primary-color); font-size: 13px; margin-bottom: 4px; }
        .teacher-time { color: #888; font-size: 12px; }
        
        .btn-book { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; padding: 8px 18px; border-radius: 20px; font-weight: bold; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; }
        .btn-book:hover { background-color: #c8e6c9; }

        /* FOOTER UI */
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

    <!-- HEADER -->
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

    <!-- MAIN CONTAINER -->
    <div class="container">
        <!-- NAV TABS -->
        <div class="nav-tabs">
            <a href="timvadatlich.php" class="tab-btn active"><i class="fa-regular fa-calendar-check"></i> Tìm & Đặt lịch</a>
            <a href="lichhen.php" class="tab-btn"><i class="fa-regular fa-user"></i> Lịch của tôi</a>
            <a href="danhgia.php" class="tab-btn"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>

        <!-- DANH SÁCH LỊCH -->
        <div class="content-box">
            <h3 class="section-title">Danh sách khung giờ tư vấn còn trống</h3>
            <?php if (empty($time_slots)): ?>
                <p style="color: #666; font-size: 14px;">Hiện chưa có khung giờ khả dụng nào.</p>
            <?php else: ?>
                <?php foreach ($time_slots as $row): ?>
                    <div class="teacher-card">
                        <div class="teacher-info">
                            <h4><?= htmlspecialchars($row['lecturer_name']) ?></h4>
                            <div class="teacher-topic"><?= htmlspecialchars($row['topic']) ?></div>
                            <div class="teacher-time">
                                <i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($row['start_time'])) ?> - <?= date('H:i', strtotime($row['end_time'])) ?>
                            </div>
                        </div>
                        <a href="timvadatlich.php?book_slot_id=<?= $row['slot_id'] ?>" class="btn-book"><i class="fa-regular fa-circle-check"></i> Đặt lịch</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- FOOTER -->
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

        // Bấm vào nút tài khoản thì ẩn/hiện menu
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        // Bấm ra bất kỳ đâu ngoài màn hình thì tự động đóng menu
        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
    </script>

</body>
</html>