<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// --- XỬ LÝ ĐĂNG XUẤT ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

// --- XỬ LÝ CHỨC NĂNG XÓA ĐÁNH GIÁ ---
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['review_id'])) {
    $review_id = (int)$_POST['review_id'];
    if ($review_id > 0 && isset($pdo)) {
        try {
            $stmt_del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $stmt_del->execute([$review_id]);
        } catch (PDOException $e) {
            // Xử lý lỗi nếu có
        }
    }
    header('Location: dg-admin.php');
    exit;
}

// --- TRUY VẤN DANH SÁCH ĐÁNH GIÁ ---
$reviews = [];
if (isset($pdo)) {
    $stmt = $pdo->query("
        SELECT r.*, COALESCE(u.fullname, r.teacher_name, 'Giảng viên') AS display_teacher_name
        FROM reviews r
        LEFT JOIN appointments a ON r.appointment_id = a.appointment_id
        LEFT JOIN time_slots ts ON a.slot_id = ts.slot_id
        LEFT JOIN users u ON ts.lecturer_id = u.id
        ORDER BY r.created_at DESC
    ");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$total_reviews = count($reviews);

// --- TÍNH ĐIỂM TRUNG BÌNH THỰC TẾ TỪ DATABASE ---
$avg_rating = 0;
if (isset($pdo)) {
    try {
        $stmt_avg = $pdo->query("SELECT AVG(rating) as avg_score FROM reviews");
        $row_avg = $stmt_avg->fetch(PDO::FETCH_ASSOC);
        if ($row_avg && $row_avg['avg_score'] !== null) {
            $avg_rating = round($row_avg['avg_score'], 1);
        }
    } catch (PDOException $e) {
        $avg_rating = 5.0; 
    }
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
            --text-color: #333;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); color: var(--text-color); min-height: 100vh; display: flex; flex-direction: column; }

        /* --- HEADER BAR --- */
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

        /* DROPDOWN MENU CHUẨN */
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

        /* --- NAV TABS --- */
        .nav-tabs-wrapper { display: flex; justify-content: flex-start; margin-bottom: 25px; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .nav-tab { padding: 8px 22px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s ease; }
        .nav-tab.active { background-color: var(--primary-color); color: white; }

        .admin-banner { background-color: white; border-radius: 16px; padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border: 1px solid var(--border-color); }
        .admin-info { display: flex; align-items: center; gap: 15px; }
        .admin-icon { background-color: #fdf2f5; color: var(--primary-color); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .admin-text h3 { font-size: 15px; color: var(--primary-color); margin-bottom: 3px; }
        .admin-text p { font-size: 12px; color: #666; }

        .btn-reset { background-color: var(--primary-color); color: white; border: none; padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; }

        .stats-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .stat-card { background-color: white; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px; border: 1px solid var(--border-color); }
        .stat-icon { background-color: #fdf2f5; color: var(--primary-color); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .stat-title { font-size: 12px; color: var(--primary-color); margin-bottom: 2px; }
        .stat-value { font-size: 20px; font-weight: bold; color: var(--primary-color); }

        .table-card { background-color: white; border-radius: 16px; padding: 25px; border: 1px solid var(--border-color); }
        .table-title { font-size: 15px; font-weight: bold; color: var(--primary-color); margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }

        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 12px; color: var(--primary-color); padding: 10px; border-bottom: 1px solid var(--border-color); }
        td { padding: 12px 10px; font-size: 13px; border-bottom: 1px solid #f9f9f9; }

        .stt-col { font-weight: bold; color: var(--primary-color); }
        .teacher-col { color: var(--primary-color); font-weight: 500; }
        .stars-col { color: var(--primary-color); }
        .badge-excellent { background-color: #e8f5e9; color: #2e7d32; padding: 3px 8px; border-radius: 10px; font-size: 11px; }

        .btn-delete { background-color: #fff0f3; color: var(--primary-color); border: 1px solid var(--border-color); padding: 4px 10px; border-radius: 12px; font-size: 11px; cursor: pointer; transition: all 0.2s; }
        .btn-delete:hover { background-color: var(--primary-color); color: white; }
    </style>
</head>
<body>

    <!-- HEADER BAR -->
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
        <!-- NAV TABS -->
        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">
                <a href="tk-admin.php" class="nav-tab"><i class="fa-solid fa-chart-pie"></i> Thống kê</a>
                <a href="gv-admin.php" class="nav-tab"><i class="fa-regular fa-user"></i> Giảng viên</a>
                <a href="hv-admin.php" class="nav-tab"><i class="fa-solid fa-graduation-cap"></i> Học viên</a>
                <a href="dg-admin.php" class="nav-tab active"><i class="fa-regular fa-star"></i> Đánh giá</a>
            </div>
        </div>

        <div class="admin-banner">
            <div class="admin-info">
                <div class="admin-icon"><i class="fa-regular fa-user"></i></div>
                <div class="admin-text">
                    <h3>Trang quản trị hệ thống Admin</h3>
                    <p>Quản lý toàn bộ danh sách phản hồi, theo dõi tiến độ phản hồi và điều phối dữ liệu</p>
                </div>
            </div>
            <button class="btn-reset">Reset dữ liệu</button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-regular fa-clipboard"></i></div>
                <div>
                    <div class="stat-title">Tổng dữ liệu</div>
                    <div class="stat-value"><?= $total_reviews ?> bản ghi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-regular fa-star"></i></div>
                <div>
                    <div class="stat-title">Điểm trung bình</div>
                    <div class="stat-value"><?= $avg_rating > 0 ? number_format($avg_rating, 1) . '/5.0' : '5.0/5.0' ?></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-title">
                <i class="fa-solid fa-list"></i> Danh sách tất cả đánh giá
            </div>

            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Giảng viên</th>
                        <th>Đánh giá</th>
                        <th>Phân loại</th>
                        <th>Thời gian</th>
                        <th>Nội dung đánh giá</th>
                        <th>Hoạt động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $index => $row): ?>
                        <tr>
                            <td class="stt-col"><?= sprintf("%02d", $index + 1) ?></td>
                            <td class="teacher-col"><?= htmlspecialchars($row['display_teacher_name']) ?></td>
                            <td class="stars-col">
                                <?php 
                                    $rating_val = (int)($row['rating'] ?? 5);
                                    for ($i = 0; $i < 5; $i++) {
                                        echo $i < $rating_val ? '<i class="fa-solid fa-star"></i>' : '<i class="fa-regular fa-star"></i>';
                                    }
                                ?>
                            </td>
                            <td><span class="badge-excellent"><?= htmlspecialchars($row['type'] ?? 'Xuất sắc') ?></span></td>
                            <td><?= date('H:i, d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td style="font-style: italic;">"<?= htmlspecialchars($row['content']) ?>"</td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này không?');" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="review_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn-delete">
                                        <i class="fa-regular fa-trash-can"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; color: #888; padding: 20px;">
                                Chưa có bản ghi đánh giá nào trong hệ thống.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

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