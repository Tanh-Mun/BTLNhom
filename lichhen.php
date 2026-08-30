<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

// Khởi tạo mặc định nếu chưa có
if (!isset($_SESSION['appointments'])) {
    $_SESSION['appointments'] = [
        ['id' => 1, 'name' => 'Lý Gia Hân', 'subject' => 'Tiếng Trung giao tiếp, luyện phản xạ', 'time' => 'T4, 26/08 - 8:30-9:30']
    ];
}

// Xử lý hủy lịch
if (isset($_GET['cancel_id'])) {
    $cancel_id = (int)$_GET['cancel_id'];
    foreach ($_SESSION['appointments'] as $key => $app) {
        if ($app['id'] === $cancel_id) {
            unset($_SESSION['appointments'][$key]);
        }
    }
    $_SESSION['appointments'] = array_values($_SESSION['appointments']); // sắp xếp lại index mảng
    header('Location: lichhen.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch của tôi - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
            --btn-grey-bg: #e0e0e0; --btn-grey-txt: #757575;
            --btn-yellow-bg: #fff8e1; --btn-yellow-txt: #f57f17;
            --btn-red-bg: #fce4ec; --btn-red-txt: #d81b60;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); display: flex; flex-direction: column; min-height: 100vh; }
        .header { background-color: var(--primary-color); color: white; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        .user-dropdown { position: relative; display: inline-block; }
        .btn-profile { color: white; text-decoration: none; font-size: 13px; font-weight: bold; background: rgba(255, 255, 255, 0.18); padding: 7px 16px; border-radius: 20px; display: flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 110%; background-color: white; min-width: 150px; box-shadow: 0px 4px 12px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { color: var(--primary-color); padding: 10px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .dropdown-item:hover { background-color: var(--primary-light); }
        .main-container { max-width: 1000px; margin: 0 auto; padding: 25px 20px 50px 20px; width: 100%; flex: 1; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 25px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .tab-btn.main-active { background: var(--primary-color); color: white; }
        .schedule-list { display: flex; flex-direction: column; gap: 15px; }
        .schedule-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 25px; display: flex; justify-content: space-between; align-items: center; }
        .card-info h4 { color: var(--primary-color); font-size: 16px; margin-bottom: 5px; }
        .card-info .subject { color: var(--primary-color); font-size: 13px; margin-bottom: 4px; opacity: 0.85; }
        .card-info .time { color: var(--primary-color); font-size: 12px; opacity: 0.65; }
        .card-actions { display: flex; gap: 10px; }
        .btn-action { border: none; padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .btn-yellow { background-color: var(--btn-yellow-bg); color: var(--btn-yellow-txt); }
        .btn-red { background-color: var(--btn-red-bg); color: var(--btn-red-txt); }
        
        .footer { background-color: var(--primary-color); color: white; padding: 40px 60px; margin-top: auto; }
        .footer-grid { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 30px; }
        .footer-col h5 { font-size: 12px; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; }
        .footer-col p { font-size: 12px; line-height: 1.6; opacity: 0.95; margin-bottom: 15px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: white; text-decoration: none; font-size: 12px; opacity: 0.95; }
        .social-icons { display: flex; gap: 10px; }
        .social-btn { width: 32px; height: 32px; background: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; text-decoration: none; }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <div class="logo-box">ABC</div>
            <div>
                <strong style="font-size: 15px;">EDULINGO</strong><br>
                <small style="font-size: 10px; opacity: 0.85;">Hệ thống đặt lịch tư vấn giảng viên</small>
            </div>
        </div>
        <div class="user-dropdown">
            <button class="btn-profile" id="userMenuBtn">
                <i class="fa-regular fa-user"></i> Tôi <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
            </button>
            <div class="dropdown-menu" id="userDropdown">
                <a href="hoso.php" class="dropdown-item"><i class="fa-regular fa-id-card"></i> Xem hồ sơ</a>
                <a href="?action=logout" class="dropdown-item" style="color: #d81b60;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </header>
    <div class="main-container">
        <div class="nav-tabs">
            <a href="timvadatlich.php" class="tab-btn"><i class="fa-regular fa-calendar-check"></i> Tìm & Đặt lịch</a>
            <a href="lichhen.php" class="tab-btn main-active"><i class="fa-regular fa-user"></i> Lịch của tôi</a>
            <a href="danhgia.php" class="tab-btn"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>
        <div class="schedule-list">
            <?php if (empty($_SESSION['appointments'])): ?>
                <p style="text-align: center; color: #757575; padding: 20px;">Bạn chưa có lịch hẹn nào. Hãy qua trang <a href="timvadatlich.php" style="color: var(--primary-color);">Tìm & Đặt lịch</a> để đăng ký.</p>
            <?php else: ?>
                <?php foreach ($_SESSION['appointments'] as $item): ?>
                    <div class="schedule-card">
                        <div class="card-info">
                            <h4><?= htmlspecialchars($item['name']) ?></h4>
                            <div class="subject"><?= htmlspecialchars($item['subject']) ?></div>
                            <div class="time"><?= htmlspecialchars($item['time']) ?></div>
                        </div>
                        <div class="card-actions">
                            <button class="btn-action btn-yellow" onclick="alert('Đã đặt nhắc lịch thành công!')"><i class="fa-regular fa-clock"></i> Nhắc lịch</button>
                            <a href="lichhen.php?cancel_id=<?= $item['id'] ?>" class="btn-action btn-red" onclick="return confirm('Bạn có muốn hủy lịch này?')"><i class="fa-regular fa-circle-xmark"></i> Hủy lịch</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="logo-container" style="margin-bottom: 12px;">
                    <div class="logo-box">ABC</div>
                    <strong>EDULINGO</strong>
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
                    <li><a href="#">Tìm giảng viên</a></li>
                    <li><a href="#">Đánh giá</a></li>
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