<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị Hệ Thống - EDULINGO Admin</title>
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
        
        /* Thống kê cards */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; text-align: center; }
        .stat-number { font-size: 22px; font-weight: bold; color: var(--primary-color); margin-bottom: 5px; }
        .stat-label { font-size: 12px; color: var(--primary-color); opacity: 0.85; }

        /* Khung thống kê chi tiết */
        .admin-section { background: white; border: 1px solid var(--border-color); border-radius: 15px; padding: 20px 25px; margin-bottom: 25px; }
        .admin-section h3 { color: var(--primary-color); font-size: 15px; margin-bottom: 15px; }
        
        .progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 13px; }
        .progress-label { width: 120px; color: #333; }
        .progress-bar-container { flex: 1; background: #f5f5f5; height: 10px; border-radius: 5px; margin: 0 15px; overflow: hidden; border: 1px solid var(--border-color); }
        .progress-bar-fill { background: var(--primary-color); height: 100%; border-radius: 5px; }
        .progress-value { width: 30px; text-align: right; font-weight: bold; color: var(--primary-color); }

        .teacher-progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 13px; }
        .teacher-name-label { width: 160px; color: #333; }
        /* Tăng chiều rộng cột chữ và bật white-space: nowrap để không bị xuống dòng */
        .teacher-stat-text { width: 145px; text-align: right; font-size: 12px; color: var(--primary-color); white-space: nowrap; }

        /* Chân trang */
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
        <div class="nav-tabs">
            <a href="tk-admin.php" class="tab-btn main-active"><i class="fa-solid fa-chart-pie"></i> Thống kê</a>
            <a href="gv-admin.php" class="tab-btn"><i class="fa-regular fa-user"></i> Giảng viên</a>
            <a href="dg-admin.php" class="tab-btn"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>

        <!-- Thống kê tổng số lượng -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">18</div>
                <div class="stat-label">Tổng số cuộc hẹn</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">3</div>
                <div class="stat-label">Đang chờ duyệt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">7</div>
                <div class="stat-label">Đã duyệt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">2</div>
                <div class="stat-label">Danh sách chờ</div>
            </div>
        </div>

        <!-- Phản hồi theo trạng thái -->
        <div class="admin-section">
            <h3>Phản hồi theo trạng thái</h3>
            <div class="progress-row">
                <span class="progress-label">Chờ duyệt</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 30%;"></div></div>
                <span class="progress-value">3</span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Đã duyệt</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 70%;"></div></div>
                <span class="progress-value">7</span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Từ chối</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 10%;"></div></div>
                <span class="progress-value">1</span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Hoàn thành</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 100%;"></div></div>
                <span class="progress-value">10</span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Đã huỷ</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 0%;"></div></div>
                <span class="progress-value">0</span>
            </div>
        </div>

        <!-- Theo giảng viên (Đồng bộ danh sách 4 giảng viên giống phía sinh viên) -->
        <div class="admin-section">
            <h3>Theo giảng viên</h3>
            <div class="teacher-progress-row">
                <span class="teacher-name-label">Lý Gia Hân</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 0%;"></div></div>
                <span class="teacher-stat-text">0 hẹn - 0 xong - 0 chờ</span>
            </div>
            <div class="teacher-progress-row">
                <span class="teacher-name-label">Nguyễn Thảo Vy</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 80%;"></div></div>
                <span class="teacher-stat-text">3 hẹn - 2 xong - 1 chờ</span>
            </div>
            <div class="teacher-progress-row">
                <span class="teacher-name-label">Trần Minh Đức</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 50%;"></div></div>
                <span class="teacher-stat-text">2 hẹn - 2 xong - 1 chờ</span>
            </div>
            <div class="teacher-progress-row">
                <span class="teacher-name-label">Yamada Haruko</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: 40%;"></div></div>
                <span class="teacher-stat-text">1 hẹn - 1 xong - 0 chờ</span>
            </div>
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