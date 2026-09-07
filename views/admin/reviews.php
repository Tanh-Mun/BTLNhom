<?php
  // Giao diện giữ nguyên 100% từ dg-admin.php gốc của bạn
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh giá - EDULINGO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
            --text-color: #333;
            --text-muted: #777;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--primary-light);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navigation Bar */
        .header-bar {
            background-color: var(--primary-color);
            color: white;
            padding: 14px 60px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-box {
            background-color: white;
            color: var(--primary-color);
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 14px;
        }

        .brand-name {
            font-size: 16px;
            letter-spacing: 0.5px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: rgba(255, 255, 255, 0.18);
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            position: relative;
            border: none;
            color: white;
        }

        /* Dropdown Menu */
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
        }

        .dropdown-menu.show {
            display: block;
        }

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

        .dropdown-item:hover {
            background-color: #fdf2f5;
        }

        /* Main Container */
        .main-container {
            max-width: 1000px;
            margin: 25px auto;
            padding: 0 20px;
            width: 100%;
            flex: 1;
        }

        /* Navigation Tabs */
        .nav-tabs {
            display: inline-flex;
            background: white;
            padding: 4px;
            border-radius: 30px;
            border: 1px solid var(--border-color);
            margin-bottom: 20px;
        }

        .nav-tab {
            padding: 8px 22px;
            border-radius: 20px;
            border: none;
            background: transparent;
            color: var(--primary-color);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .nav-tab.active {
            background-color: var(--primary-color);
            color: white;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background-color: white;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border: 1px solid var(--border-color);
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: var(--primary-color);
        }

        /* Table Card */
        .table-card {
            background-color: white;
            border-radius: 16px;
            padding: 25px;
            border: 1px solid var(--border-color);
        }

        .table-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: 12px 10px;
            font-size: 13px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border-color);
            font-weight: 600;
        }

        td {
            padding: 14px 10px;
            font-size: 13px;
            border-bottom: 1px solid #f9f9f9;
        }

        .stars {
            color: var(--primary-color);
            letter-spacing: 2px;
        }

        .comment-text {
            color: #555;
            font-style: italic;
        }

        .btn-delete {
            background-color: #fff0f3;
            color: var(--primary-color);
            border: 1px solid var(--border-color);
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 12px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            background-color: var(--primary-color);
            color: white;
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 20px;
            color: var(--text-muted);
            font-size: 12px;
            margin-top: auto;
        }
    </style>
</head>
<body>

    <!-- Header Bar -->
    <div class="header-bar">
        <div class="logo-section">
            <div class="logo-box">ABC</div>
            <div class="brand-name"><strong>EDULINGO</strong></div>
        </div>

        <div style="position: relative;">
            <button class="user-profile" id="userMenuBtn">
                <i class="fa-regular fa-user"></i> admin
            </button>
            
            <div class="dropdown-menu" id="userDropdown">
                <!-- SỬA ĐƯỜNG DẪN 1: Đăng xuất qua Controller MVC -->
                <a href="index.php?controller=review&action=logout" class="dropdown-item">
                    <i class="fa-solid fa-right-from-bracket"></i> Đăng xuất
                </a>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <a href="index.php?controller=stat&action=index" class="nav-tab">Thống kê</a>
            <a href="index.php?controller=lecturer&action=index" class="nav-tab">Giảng viên</a>
            <a href="index.php?controller=student&action=index" class="nav-tab">Học viên</a>
            <a href="index.php?controller=review&action=index" class="nav-tab active">Đánh giá</a>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-database"></i></div>
                <div>
                    <div class="stat-label">Tổng dữ liệu</div>
                    <div class="stat-value"><?= $total_reviews ?> bản ghi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
                <div>
                    <div class="stat-label">Điểm trung bình</div>
                    <div class="stat-value"><?= number_format($avg_rating, 1) ?>/5.0</div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <div class="table-title">Danh sách đánh giá</div>
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Giảng viên</th>
                        <th>Đánh giá</th>
                        <th>Thời gian</th>
                        <th>Nội dung</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $index => $row): ?>
                        <tr>
                            <td><?= sprintf("%02d", $index + 1) ?></td>
                            <td><strong><?= htmlspecialchars($row['display_teacher_name']) ?></strong></td>
                            <td>
                                <div class="stars">
                                    <?php 
                                        $rating_val = (int)($row['rating'] ?? 5);
                                        for ($i = 0; $i < 5; $i++) {
                                            if ($i < $rating_val) {
                                                echo '<i class="fa-solid fa-star"></i>';
                                            } else {
                                                echo '<i class="fa-regular fa-star"></i>';
                                            }
                                        }
                                    ?>
                                </div>
                            </td>
                            <td><?= date('H:i, d/m/Y', strtotime($row['created_at'])) ?></td>
                            <td class="comment-text">"<?= htmlspecialchars($row['content']) ?>"</td>
                            <td>
                                <!-- SỬA ĐƯỜNG DẪN 2: Form Xóa chuyển hướng về Controller MVC -->
                                <form method="POST" action="index.php?controller=review&action=delete" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?');" style="display:inline;">
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
                            <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">
                                Chưa có đánh giá nào trong hệ thống.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        EDULINGO Administration System &copy; 2026
    </div>

    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');

        userMenuBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdown.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            userDropdown.classList.remove('show');
        });
    </script>
</body>
</html>