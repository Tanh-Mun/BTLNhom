<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

$lecturer_id = $_SESSION['user_id'] ?? 1;

// Xử lý khi Giảng viên bấm "Tạo khung giờ mới"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_slot') {
    $topic = trim($_POST['topic']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $location = trim($_POST['location'] ?? 'Online - Zoom');

    if (!empty($topic) && !empty($start_time) && !empty($end_time)) {
        $stmt = $pdo->prepare("INSERT INTO time_slots (lecturer_id, topic, start_time, end_time, location, status) VALUES (?, ?, ?, ?, ?, 'available')");
        $stmt->execute([$lecturer_id, $topic, $start_time, $end_time, $location]);
        
        header('Location: Khunggio.php?status=success');
        exit;
    }
}

// Xử lý Xóa khung giờ
if (isset($_GET['delete_slot_id'])) {
    $slot_id = (int)$_GET['delete_slot_id'];
    $stmt = $pdo->prepare("DELETE FROM time_slots WHERE slot_id = ? AND lecturer_id = ?");
    $stmt->execute([$slot_id, $lecturer_id]);
    header('Location: Khunggio.php');
    exit;
}

// Lấy danh sách khung giờ
$stmt = $pdo->prepare("SELECT * FROM time_slots WHERE lecturer_id = ? ORDER BY start_time ASC");
$stmt->execute([$lecturer_id]);
$time_slots = $stmt->fetchAll();

$selected_lecturer = $_SESSION['user_name'] ?? 'Nguyễn Thảo Vy';
$name_parts = explode(' ', trim($selected_lecturer));
$avatar_letter = strtoupper(substr(end($name_parts), 0, 1));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Khung Giờ - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --primary-color: #d81b60; 
            --primary-light: #fdf2f5; 
            --border-color: #f8bbd0; 
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: var(--primary-light); color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        
        /* HEADER */
        .header { background-color: var(--primary-color); color: white; padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .header-text h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .header-text p { font-size: 13px; opacity: 0.9; }

        /* USER DROPDOWN MENU */
        .user-dropdown { position: relative; display: inline-block; }
        .user-profile-icon { display: flex; align-items: center; gap: 10px; color: white; background-color: rgba(255, 255, 255, 0.15); padding: 6px 16px 6px 6px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.3); cursor: pointer; text-decoration: none; user-select: none; }
        .avatar-circle { width: 34px; height: 34px; background-color: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }
        
        .dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); background-color: white; min-width: 170px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); padding: 8px 0; z-index: 1000; border: 1px solid var(--border-color); }
        .dropdown-menu.show { display: block; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.2s; }
        .dropdown-item:hover { background-color: #fce4ec; }
        .dropdown-item i { font-size: 16px; width: 18px; text-align: center; }

        /* CONTAINER & TABS */
        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; width: 100%; flex: 1; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 25px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .tab-btn.active { background: var(--primary-color); color: white; }

        /* FORM */
        .card-form { background: white; padding: 20px 24px; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 25px; }
        .card-form h3 { color: var(--primary-color); font-size: 16px; margin-bottom: 12px; }
        .form-grid { display: grid; grid-template-columns: 2fr 1.5fr 1.5fr 1fr; gap: 12px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: #555; }
        .form-group input { width: 100%; padding: 8px 12px; border: 1px solid #f48fb1; border-radius: 6px; outline: none; font-size: 13px; }
        .form-group input:focus { border-color: var(--primary-color); }
        .btn-submit { background: var(--primary-color); color: white; border: none; padding: 8px 15px; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px; transition: opacity 0.2s; }
        .btn-submit:hover { opacity: 0.9; }

        /* CARDS */
        .slot-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 18px 24px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .slot-card h4 { color: #c2185b; font-size: 16px; margin-bottom: 4px; }
        .badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-available { background: #e8f5e9; color: #2e7d32; }
        .badge-booked { background: #ffebee; color: #c62828; }
        .btn-delete { color: #d32f2f; text-decoration: none; font-size: 13px; font-weight: bold; display: flex; align-items: center; gap: 4px; }

        /* FOOTER */
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
        <div class="user-dropdown">
            <div class="user-profile-icon" id="userMenuBtn">
                <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
                <span style="font-size: 14px; font-weight: 600;"><?= htmlspecialchars($selected_lecturer) ?></span>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <a href="Hoso.php" class="dropdown-item"><i class="fa-regular fa-id-card"></i> Xem hồ sơ</a>
                <a href="dangnhap.php" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </div>

    <!-- MAIN CONTAINER -->
    <div class="container">
        <!-- TABS -->
        <div class="nav-tabs">
            <a href="Cuochen.php" class="tab-btn"><i class="fa-regular fa-calendar-check"></i> Cuộc hẹn</a>
            <a href="Khunggio.php" class="tab-btn active"><i class="fa-regular fa-clock"></i> Khung giờ</a>
            <a href="Lichtuan.php" class="tab-btn"><i class="fa-regular fa-calendar-days"></i> Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn"><i class="fa-solid fa-users"></i> Danh sách chờ</a>
        </div>

        <!-- FORM TẠO KHUNG GIỜ -->
        <div class="card-form">
            <h3>Mở khung giờ tư vấn mới</h3>
            <form method="POST">
                <input type="hidden" name="action" value="add_slot">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Chủ đề tư vấn</label>
                        <input type="text" name="topic" placeholder="Ví dụ: Sửa bài Writing Task 2" required>
                    </div>
                    <div class="form-group">
                        <label>Thời gian bắt đầu</label>
                        <input type="datetime-local" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label>Thời gian kết thúc</label>
                        <input type="datetime-local" name="end_time" required>
                    </div>
                    <div style="display: flex; align-items: flex-end;">
                        <button type="submit" class="btn-submit" style="width: 100%; height: 35px;"><i class="fa-solid fa-plus"></i> Thêm giờ</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- DANH SÁCH KHUNG GIỜ -->
        <h3 style="color: var(--primary-color); font-size: 16px; margin-bottom: 15px;">Khung giờ đã tạo</h3>
        <?php if (empty($time_slots)): ?>
            <div style="background: white; padding: 25px; border-radius: 12px; text-align: center; color: #777; border: 1px solid var(--border-color);">Chưa có khung giờ nào được mở.</div>
        <?php else: ?>
            <?php foreach ($time_slots as $slot): ?>
                <div class="slot-card">
                    <div>
                        <h4><?= htmlspecialchars($slot['topic']) ?></h4>
                        <div style="font-size: 13px; color: #888; margin-top: 4px;">
                            <i class="fa-regular fa-clock"></i> 
                            <?= date('d/m/Y H:i', strtotime($slot['start_time'])) ?> - <?= date('H:i', strtotime($slot['end_time'])) ?>
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <?php if ($slot['status'] === 'available'): ?>
                            <span class="badge badge-available">Đang mở</span>
                        <?php else: ?>
                            <span class="badge badge-booked">Đã có học viên đặt</span>
                        <?php endif; ?>
                        
                        <a href="Khunggio.php?delete_slot_id=<?= $slot['slot_id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc chắn muốn xóa khung giờ này?');"><i class="fa-regular fa-trash-can"></i> Xóa</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
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
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('show'); });
        document.addEventListener('click', () => { userDropdown.classList.remove('show'); });
    </script>
</body>
</html>