<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

// 1. Lấy thống kê tổng quan các cuộc hẹn
$stat_sql = "SELECT 
    COUNT(*) AS total_appointments,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS total_approved,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS total_rejected,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS total_completed,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS total_cancelled
FROM appointments";
$stat_stmt = $pdo->query($stat_sql);
$stats = $stat_stmt->fetch(PDO::FETCH_ASSOC);

$total_appointments = $stats['total_appointments'] ?? 0;
$total_pending      = $stats['total_pending'] ?? 0;
$total_approved     = $stats['total_approved'] ?? 0;
$total_rejected     = $stats['total_rejected'] ?? 0;
$total_completed    = $stats['total_completed'] ?? 0;
$total_cancelled    = $stats['total_cancelled'] ?? 0;

// Hàm tính phần trăm cho thanh tiến trình
function get_percentage($value, $total) {
    return $total > 0 ? round(($value / $total) * 100) : 0;
}

// 2. Lấy thống kê cuộc hẹn chi tiết theo từng Giảng viên
$teacher_sql = "SELECT 
    u.id AS lecturer_id,
    u.fullname AS lecturer_name,
    COUNT(a.status) AS total_hen,
    SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) AS total_xong,
    SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) AS total_cho
FROM users u
LEFT JOIN time_slots ts ON u.id = ts.lecturer_id
LEFT JOIN appointments a ON ts.slot_id = a.slot_id
WHERE u.role = 'lecturer' OR u.role = 'teacher' OR u.role = 'gv'
GROUP BY u.id, u.fullname
ORDER BY total_hen DESC";

$teacher_stmt = $pdo->query($teacher_sql);
$teachers = $teacher_stmt->fetchAll(PDO::FETCH_ASSOC);

$max_hen = 1;
foreach ($teachers as $t) {
    if ($t['total_hen'] > $max_hen) {
        $max_hen = $t['total_hen'];
    }
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
            --text-color: #333;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); color: var(--text-color); display: flex; flex-direction: column; min-height: 100vh; }

        /* HEADER FULL WIDTH */
        .header-bar {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-box { background-color: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        .brand-info { display: flex; flex-direction: column; }
        .brand-name { font-weight: bold; font-size: 15px; line-height: 1.2; }
        .brand-sub { font-size: 10px; opacity: 0.85; }

        /* DROPDOWN MENU CẢI TIẾN THEO MẪU */
        .user-dropdown { position: relative; display: inline-block; }
        .user-pill { 
            background-color: rgba(255, 255, 255, 0.18); 
            color: white; 
            padding: 8px 18px; 
            border-radius: 20px; 
            font-size: 13px; 
            font-weight: bold; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            border: none; 
            cursor: pointer; 
        }
        .dropdown-menu { 
            display: none; 
            position: absolute; 
            right: 0; 
            top: calc(100% + 8px); 
            background-color: white; 
            min-width: 160px; 
            box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.12); 
            border-radius: 12px; 
            padding: 6px 0;
            z-index: 1000; 
            border: 1px solid rgba(0, 0, 0, 0.05);
        }
        .dropdown-menu.show { display: block; }
        .dropdown-item { 
            color: #d81b60; 
            padding: 10px 18px; 
            text-decoration: none; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            font-size: 13px; 
            font-weight: bold;
            transition: background 0.2s;
        }
        .dropdown-item:hover { background-color: #fce4ec; }

        .main-container { max-width: 1000px; margin: 25px auto; padding: 0 20px 50px 20px; width: 100%; flex: 1; }

        /* NAV TABS CĂN TRÁI */
        .nav-tabs-wrapper { display: flex; justify-content: flex-start; margin-bottom: 25px; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .nav-tab { padding: 8px 22px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s ease; }
        .nav-tab.active { background-color: var(--primary-color); color: white; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; text-align: center; }
        .stat-number { font-size: 22px; font-weight: bold; color: var(--primary-color); margin-bottom: 5px; }
        .stat-label { font-size: 12px; color: var(--primary-color); opacity: 0.85; }

        .admin-section { background: white; border: 1px solid var(--border-color); border-radius: 15px; padding: 20px 25px; margin-bottom: 25px; }
        .admin-section h3 { color: var(--primary-color); font-size: 15px; margin-bottom: 15px; }
        
        .progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 13px; }
        .progress-label { width: 120px; color: #333; }
        .progress-bar-container { flex: 1; background: #f5f5f5; height: 10px; border-radius: 5px; margin: 0 15px; overflow: hidden; border: 1px solid var(--border-color); }
        .progress-bar-fill { background: var(--primary-color); height: 100%; border-radius: 5px; }
        .progress-value { width: 30px; text-align: right; font-weight: bold; color: var(--primary-color); }

        .teacher-progress-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; font-size: 13px; }
        .teacher-name-label { width: 160px; color: #333; }
        .teacher-stat-text { width: 145px; text-align: right; font-size: 12px; color: var(--primary-color); white-space: nowrap; }

        .footer { background-color: var(--primary-color); color: white; padding: 40px 60px; margin-top: auto; }
        .footer-grid { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 30px; }
        .footer-col h5 { font-size: 12px; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; }
        .footer-col p { font-size: 12px; line-height: 1.6; opacity: 0.95; margin-bottom: 15px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: white; text-decoration: none; font-size: 12px; opacity: 0.95; }
    </style>
</head>
<body>

    <div class="header-bar">
        <div class="logo-section">
            <div class="logo-box">ABC</div>
            <div class="brand-info">
                <span class="brand-name">EDULINGO</span>
                <span class="brand-sub">Hệ thống đặt lịch tư vấn giảng viên</span>
            </div>
        </div>
        <div class="user-dropdown">
            <button class="user-pill" id="userMenuBtn">
                <i class="fa-regular fa-user"></i> admin
            </button>
            <div class="dropdown-menu" id="userDropdown">
                <a href="?action=logout" class="dropdown-item">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">
                <a href="tk-admin.php" class="nav-tab active"><i class="fa-solid fa-chart-pie"></i> Thống kê</a>
                <a href="gv-admin.php" class="nav-tab"><i class="fa-regular fa-user"></i> Giảng viên</a>
                <a href="hv-admin.php" class="nav-tab"><i class="fa-solid fa-graduation-cap"></i> Học viên</a>
                <a href="dg-admin.php" class="nav-tab"><i class="fa-regular fa-star"></i> Đánh giá</a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_appointments ?></div>
                <div class="stat-label">Tổng số cuộc hẹn</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_pending ?></div>
                <div class="stat-label">Đang chờ duyệt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_approved ?></div>
                <div class="stat-label">Đã duyệt</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $total_completed ?></div>
                <div class="stat-label">Hoàn thành</div>
            </div>
        </div>

        <div class="admin-section">
            <h3>Phản hồi theo trạng thái</h3>
            <div class="progress-row">
                <span class="progress-label">Chờ duyệt</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: <?= get_percentage($total_pending, $total_appointments) ?>%;"></div></div>
                <span class="progress-value"><?= $total_pending ?></span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Đã duyệt</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: <?= get_percentage($total_approved, $total_appointments) ?>%;"></div></div>
                <span class="progress-value"><?= $total_approved ?></span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Từ chối</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: <?= get_percentage($total_rejected, $total_appointments) ?>%;"></div></div>
                <span class="progress-value"><?= $total_rejected ?></span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Hoàn thành</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: <?= get_percentage($total_completed, $total_appointments) ?>%;"></div></div>
                <span class="progress-value"><?= $total_completed ?></span>
            </div>
            <div class="progress-row">
                <span class="progress-label">Đã huỷ</span>
                <div class="progress-bar-container"><div class="progress-bar-fill" style="width: <?= get_percentage($total_cancelled, $total_appointments) ?>%;"></div></div>
                <span class="progress-value"><?= $total_cancelled ?></span>
            </div>
        </div>

        <div class="admin-section">
            <h3>Theo giảng viên</h3>
            <?php if (empty($teachers)): ?>
                <p style="font-size: 13px; color: #777;">Chưa có dữ liệu giảng viên.</p>
            <?php else: ?>
                <?php foreach ($teachers as $t): ?>
                    <?php $fill_percent = get_percentage($t['total_hen'], $max_hen); ?>
                    <div class="teacher-progress-row">
                        <span class="teacher-name-label"><?= htmlspecialchars($t['lecturer_name']) ?></span>
                        <div class="progress-bar-container">
                            <div class="progress-bar-fill" style="width: <?= $fill_percent ?>%;"></div>
                        </div>
                        <span class="teacher-stat-text"><?= $t['total_hen'] ?> hẹn - <?= $t['total_xong'] ?> xong - <?= $t['total_cho'] ?> chờ</span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="logo-section" style="margin-bottom: 12px;">
                    <div class="logo-box">ABC</div>
                    <strong style="font-size: 15px;">EDULINGO</strong>
                </div>
                <p>Hệ thống đặt lịch tư vấn giảng viên ngoại ngữ, giúp sinh viên tìm và đặt buổi gặp chỉ trong vài bước</p>
            </div>
            <div class="footer-col">
                <h5>KHÁM PHÁ</h5>
                <ul class="footer-links">
                    <li><a href="#">Tìm giảng viên</a></li>
                    <li><a href="#">Đánh giá</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>GIẢNG VIÊN</h5>
                <ul class="footer-links">
                    <li><a href="#">Đăng ký giảng dạy</a></li>
                    <li><a href="#">Quản lý khung giờ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>THÔNG BÁO</h5>
                <p>Nhận tin khi có giảng viên mới mở lịch</p>
            </div>
        </div>
    </footer>

    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            userDropdown.classList.toggle('show'); 
        });
        document.addEventListener('click', () => { 
            userDropdown.classList.remove('show'); 
        });
    </script>
</body>
</html>