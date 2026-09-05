<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// 1. Kiểm tra session đăng nhập
if (!isset($_SESSION['user']['id'])) {
    header('Location: dangnhap.php');
    exit;
}

$student_id = $_SESSION['user']['id']; 

// 2. Lấy thông tin người dùng đang đăng nhập từ DB
$stmtUser = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
$stmtUser->execute([$student_id]);
$currentUser = $stmtUser->fetch();

$student_name = !empty($currentUser['fullname']) ? $currentUser['fullname'] : ($_SESSION['user_name'] ?? 'Học viên');

// Tách chữ cái đại diện Avatar
$avatar_letter = mb_strtoupper(mb_substr($student_name, 0, 1, 'UTF-8'), 'UTF-8');

// Truy vấn danh sách cuộc hẹn của học viên
$sql = "SELECT a.appointment_id, a.status, ts.topic, ts.start_time, ts.end_time, ts.location, u.fullname AS lecturer_name
        FROM appointments a
        JOIN time_slots ts ON a.slot_id = ts.slot_id
        JOIN users u ON ts.lecturer_id = u.id
        WHERE a.student_id = ?
        ORDER BY ts.start_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$my_appointments = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch Của Tôi - EDULINGO</title>
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
        
        .card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px 25px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        
        .badge { padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-completed { background: #e2e3e5; color: #383d41; }
        
        .btn-review-link { background: var(--primary-color); color: white; padding: 6px 14px; border-radius: 15px; font-size: 12px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 4px; }

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
            <a href="timvadatlich.php" class="tab-btn"><i class="fa-regular fa-calendar-check"></i> Tìm & Đặt lịch</a>
            <a href="lichhen.php" class="tab-btn active"><i class="fa-regular fa-user"></i> Lịch của tôi</a>
            <a href="danhgia.php" class="tab-btn"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>

        <!-- DANH SÁCH BUỔI HẸN -->
        <div class="content-box">
            <h3 class="section-title">Danh sách buổi hẹn</h3>
            <?php if (empty($my_appointments)): ?>
                <p style="color: #666; font-size: 14px;">Bạn chưa đặt buổi hẹn nào.</p>
            <?php else: ?>
                <?php foreach ($my_appointments as $item): ?>
                    <div class="card">
                        <div>
                            <h4 style="color: var(--primary-color); font-size: 16px; margin-bottom: 4px;"><?= htmlspecialchars($item['lecturer_name']) ?></h4>
                            <div style="font-size: 13px; font-weight: bold; color: #444;"><?= htmlspecialchars($item['topic']) ?></div>
                            <div style="font-size: 12px; color: #777; margin-top: 4px;">
                                <i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($item['start_time'])) ?> - <?= date('H:i', strtotime($item['end_time'])) ?>
                            </div>
                        </div>
                        <div>
                            <?php if ($item['status'] === 'pending'): ?>
                                <span class="badge status-pending">Chờ xác nhận</span>
                            <?php elseif ($item['status'] === 'approved'): ?>
                                <span class="badge status-approved">Đã xác nhận</span>
                            <?php elseif ($item['status'] === 'rejected' || $item['status'] === 'cancelled'): ?>
                                <span class="badge status-rejected">Đã từ chối</span>
                            <?php elseif ($item['status'] === 'completed'): ?>
                                <span class="badge status-completed">Đã hoàn thành</span>
                                <a href="danhgia.php" class="btn-review-link" style="margin-left: 8px;"><i class="fa-regular fa-star"></i> Viết đánh giá</a>
                            <?php endif; ?>
                        </div>
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