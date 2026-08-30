<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

// Dữ liệu mẫu cho các đánh giá hệ thống
$reviews = [
    [
        'id' => 1,
        'teacher' => 'Trần Lan Anh',
        'rating' => 5,
        'type' => 'Xuất sắc',
        'type_class' => 'badge-excellent',
        'time' => '16:20, 29/08/2026',
        'content' => 'Đặt lịch rất có sát nhanh, thầy dạy đúng giờ đúng...'
    ],
    [
        'id' => 2,
        'teacher' => 'Ngô Minh Khôi',
        'rating' => 5,
        'type' => 'Xuất sắc',
        'type_class' => 'badge-excellent',
        'time' => '10:15, 30/01/2026',
        'content' => 'Giáo viên tư vấn góp thước buổi, phong cách học lương...'
    ],
    [
        'id' => 3,
        'teacher' => 'Đỗ Hải Phương',
        'rating' => 5,
        'type' => 'Tốt',
        'type_class' => 'badge-good',
        'time' => '09:45, 28/08/2026',
        'content' => 'Giao diện rõ dùng, lọc theo ngôn ngữ và chủ đề rất nhanh.'
    ]
];

// Xử lý xóa đánh giá nếu có yêu cầu
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $reviews = array_filter($reviews, function($r) use ($deleteId) {
        return $r['id'] !== $deleteId;
    });
    // Reset lại mảng
    $reviews = array_values($reviews);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá - EDULINGO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); display: flex; flex-direction: column; min-height: 100vh; }
        
        .header { background-color: var(--primary-color); color: white; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        
        /* User Dropdown */
        .user-dropdown { position: relative; display: inline-block; }
        .btn-profile { color: white; text-decoration: none; font-size: 13px; font-weight: bold; background: rgba(255, 255, 255, 0.18); padding: 7px 16px; border-radius: 20px; display: flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 110%; background-color: white; min-width: 150px; box-shadow: 0px 4px 12px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { color: var(--primary-color); padding: 10px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .dropdown-item:hover { background-color: var(--primary-light); }

        .main-container { max-width: 1000px; margin: 0 auto; padding: 25px 20px 50px 20px; width: 100%; flex: 1; }
        
        /* Navigation Tabs */
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 25px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .tab-btn.main-active { background: var(--primary-color); color: white; }

        /* Admin Control Banner Box */
        .admin-banner { background: white; border: 1px solid var(--border-color); border-radius: 16px; padding: 20px 25px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 25px; }
        .admin-banner-left { display: flex; align-items: center; gap: 15px; }
        .admin-icon-box { width: 40px; height: 40px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; }
        .admin-title h4 { color: var(--primary-color); font-size: 15px; margin-bottom: 3px; }
        .admin-title p { font-size: 12px; color: var(--primary-color); opacity: 0.85; }
        .btn-reset { background: var(--primary-color); color: white; border: none; padding: 9px 20px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; text-decoration: none; }
        .btn-reset:hover { opacity: 0.9; }

        /* Stats Row */
        .stats-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid var(--border-color); border-radius: 16px; padding: 20px 25px; display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 45px; height: 45px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .stat-info span { font-size: 12px; color: var(--primary-color); opacity: 0.85; display: block; margin-bottom: 3px; }
        .stat-info h3 { font-size: 20px; color: var(--primary-color); font-weight: bold; }

        /* Table Card Container */
        .table-card { background: white; border: 1px solid var(--border-color); border-radius: 16px; padding: 20px 25px; }
        .table-card-header { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: bold; color: var(--primary-color); margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { font-size: 12px; color: var(--primary-color); padding: 10px 12px; border-bottom: 1px solid var(--border-color); font-weight: bold; }
        td { font-size: 12px; color: #333; padding: 14px 12px; border-bottom: 1px solid var(--border-color); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }

        .teacher-name-cell { color: var(--primary-color); font-weight: 500; }
        .stars-cell { color: var(--primary-color); font-size: 11px; letter-spacing: 2px; }
        
        .badge-type { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 11px; font-weight: bold; text-align: center; }
        .badge-excellent { background: #e2f0d9; color: #385723; border: 1px solid #c8e1b7; }
        .badge-good { background: #fff2cc; color: #806000; border: 1px solid #ffe599; }
        
        .time-cell { color: var(--primary-color); opacity: 0.85; font-size: 11px; }
        .content-cell { color: var(--primary-color); opacity: 0.9; font-style: italic; max-width: 280px; }

        .btn-delete { background: white; color: var(--primary-color); border: 1px solid var(--border-color); padding: 5px 12px; border-radius: 15px; font-size: 11px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .btn-delete:hover { background: var(--primary-light); }

        /* Footer */
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
                <i class="fa-regular fa-user"></i> Quản trị viên <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
            </button>
            <div class="dropdown-menu" id="userDropdown">
                <a href="hoso.php" class="dropdown-item"><i class="fa-regular fa-id-card"></i> Xem hồ sơ</a>
                <a href="?action=logout" class="dropdown-item" style="color: #d81b60;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <a href="tk-admin.php" class="tab-btn"><i class="fa-solid fa-chart-pie"></i> Thống kê</a>
            <a href="gv-admin.php" class="tab-btn"><i class="fa-regular fa-user"></i> Giảng viên</a>
            <a href="dg-admin.php" class="tab-btn main-active"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>

        <!-- Admin Control Banner -->
        <div class="admin-banner">
            <div class="admin-banner-left">
                <div class="admin-icon-box"><i class="fa-regular fa-user"></i></div>
                <div class="admin-title">
                    <h4>Trang quản trị hệ thống Admin</h4>
                    <p>Quản lý toàn bộ danh sách phản hồi, theo dõi tiến độ phản hồi và điều phối dữ liệu</p>
                </div>
            </div>
            <a href="#" class="btn-reset" onclick="alert('Đã reset dữ liệu hệ thống!'); return false;">Reset dữ liệu</a>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-regular fa-clipboard"></i></div>
                <div class="stat-info">
                    <span>Tổng dữ liệu</span>
                    <h3><?= count($reviews) ?> bản ghi</h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-regular fa-star"></i></div>
                <div class="stat-info">
                    <span>Điểm trung bình</span>
                    <h3>4.9/5.0</h3>
                </div>
            </div>
        </div>

        <!-- Review Table Card -->
        <div class="table-card">
            <div class="table-card-header">
                <i class="fa-solid fa-list"></i> Danh sách tất cả đánh giá
            </div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 60px;">STT</th>
                        <th style="width: 160px;">Giảng viên</th>
                        <th style="width: 110px;">Đánh giá</th>
                        <th style="width: 100px;">Phân loại</th>
                        <th style="width: 150px;">Thời gian</th>
                        <th>Nội dung đánh giá</th>
                        <th style="width: 90px; text-align: center;">Hoạt động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($reviews) > 0): ?>
                        <?php foreach ($reviews as $index => $r): ?>
                        <tr>
                            <td style="color: var(--primary-color); font-weight: bold;"><?= sprintf('%02d', $index + 1) ?></td>
                            <td class="teacher-name-cell"><?= htmlspecialchars($r['teacher']) ?></td>
                            <td class="stars-cell">
                                <?php for($i = 0; $i < $r['rating']; $i++): ?><i class="fa-solid fa-star"></i><?php endfor; ?>
                            </td>
                            <td><span class="badge-type <?= $r['type_class'] ?>"><?= $r['type'] ?></span></td>
                            <td class="time-cell"><?= htmlspecialchars($r['time']) ?></td>
                            <td class="content-cell">“<?= htmlspecialchars($r['content']) ?>”</td>
                            <td style="text-align: center;">
                                <a href="?delete_id=<?= $r['id'] ?>" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                    <i class="fa-regular fa-trash-can" style="font-size: 10px;"></i> Xóa
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: var(--primary-color);">Chưa có đánh giá nào trong hệ thống.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
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