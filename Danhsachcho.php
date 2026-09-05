<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

$lecturer_id = $_SESSION['user_id'] ?? 1;

if (isset($_GET['action']) && isset($_GET['appointment_id'])) {
    $appointment_id = (int)$_GET['appointment_id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'approved' WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);

        $stmt_slot = $pdo->prepare("UPDATE time_slots SET status = 'full' WHERE slot_id = (SELECT slot_id FROM appointments WHERE appointment_id = ?)");
        $stmt_slot->execute([$appointment_id]);

    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'rejected' WHERE appointment_id = ?");
        $stmt->execute([$appointment_id]);
    }

    header('Location: DanhSachCho.php');
    exit;
}

$stmt = $pdo->prepare("SELECT a.appointment_id, u.fullname AS student_name, u.email, ts.topic, ts.start_time, ts.end_time 
                       FROM appointments a 
                       JOIN time_slots ts ON a.slot_id = ts.slot_id 
                       JOIN users u ON a.student_id = u.id 
                       WHERE ts.lecturer_id = ? AND LOWER(a.status) = 'pending'
                       ORDER BY a.created_at ASC");
$stmt->execute([$lecturer_id]);
$waiting_list = $stmt->fetchAll();

$selected_lecturer = $_SESSION['user_name'] ?? 'Nguyễn Thảo Vy';
$name_parts = explode(' ', trim($selected_lecturer));
$avatar_letter = strtoupper(substr(end($name_parts), 0, 1));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách chờ Duyệt - EDULINGO</title>
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

        /* LIST CARD */
        .waiting-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px 24px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .waiting-card h4 { color: #c2185b; font-size: 18px; font-weight: bold; }
        .waiting-card h4 span { color: #888; font-weight: normal; font-size: 14px; }
        .topic { color: #f48fb1; font-weight: 500; font-size: 14px; margin: 4px 0; }
        .time { color: #888; font-size: 13px; }
        
        .btn-approve { background-color: #d7f5dd; color: #1b5e20; padding: 8px 18px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 13px; transition: opacity 0.2s; }
        .btn-approve:hover { opacity: 0.85; }
        .btn-reject { background-color: #fce4ec; color: #c2185b; padding: 8px 18px; border-radius: 8px; font-weight: bold; text-decoration: none; font-size: 13px; transition: opacity 0.2s; }
        .btn-reject:hover { opacity: 0.85; }

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
        <div class="nav-tabs">
            <a href="Cuochen.php" class="tab-btn"><i class="fa-regular fa-calendar-check"></i> Cuộc hẹn</a>
            <a href="Khunggio.php" class="tab-btn"><i class="fa-regular fa-clock"></i> Khung giờ</a>
            <a href="Lichtuan.php" class="tab-btn"><i class="fa-regular fa-calendar-days"></i> Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn active"><i class="fa-solid fa-users"></i> Danh sách chờ</a>
        </div>

        <?php if (empty($waiting_list)): ?>
            <div style="background: white; padding: 30px; border-radius: 12px; text-align: center; color: #777; border: 1px solid var(--border-color);">
                Không có yêu cầu nào đang chờ duyệt.
            </div>
        <?php else: ?>
            <?php foreach ($waiting_list as $item): ?>
                <div class="waiting-card">
                    <div>
                        <h4><?= htmlspecialchars($item['student_name']) ?> <span>(<?= htmlspecialchars($item['email']) ?>)</span></h4>
                        <div class="topic"><?= htmlspecialchars($item['topic']) ?></div>
                        <div class="time"><i class="fa-regular fa-clock"></i> <?= date('d/m/Y H:i', strtotime($item['start_time'])) ?> - <?= date('H:i', strtotime($item['end_time'])) ?></div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="DanhSachCho.php?action=approve&appointment_id=<?= $item['appointment_id'] ?>" class="btn-approve">✓ Duyệt</a>
                        <a href="DanhSachCho.php?action=reject&appointment_id=<?= $item['appointment_id'] ?>" class="btn-reject">✕ Từ chối</a>
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