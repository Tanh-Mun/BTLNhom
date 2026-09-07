<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thống kê - EDULINGO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
            --text-color: #333;
            --text-muted: #777;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--primary-light); color: var(--text-color); min-height: 100vh; display: flex; flex-direction: column; }

        .header-bar { background-color: var(--primary-color); color: white; padding: 14px 60px; display: flex; justify-content: space-between; align-items: center; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-box { background-color: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        .brand-name { font-size: 16px; letter-spacing: 0.5px; }

        .user-profile { display: flex; align-items: center; gap: 8px; background-color: rgba(255, 255, 255, 0.18); padding: 8px 18px; border-radius: 20px; font-size: 14px; font-weight: bold; cursor: pointer; border: none; color: white; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); background-color: white; min-width: 160px; box-shadow: 0px 8px 20px rgba(0,0,0,0.12); border-radius: 12px; padding: 6px 0; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { color: #d81b60; padding: 10px 18px; text-decoration: none; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: bold; }
        .dropdown-item:hover { background-color: #fdf2f5; }

        .main-container { max-width: 1000px; margin: 25px auto; padding: 0 20px; width: 100%; flex: 1; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 20px; }
        .nav-tab { padding: 8px 22px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; }
        .nav-tab.active { background-color: var(--primary-color); color: white; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px; }
        .stat-card { background-color: white; border-radius: 16px; padding: 20px; display: flex; align-items: center; gap: 15px; border: 1px solid var(--border-color); }
        .stat-icon { width: 45px; height: 45px; border-radius: 50%; background-color: var(--primary-light); color: var(--primary-color); display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .stat-label { font-size: 12px; color: var(--text-muted); margin-bottom: 4px; }
        .stat-value { font-size: 20px; font-weight: bold; color: var(--primary-color); }

        .table-card { background-color: white; border-radius: 16px; padding: 25px; border: 1px solid var(--border-color); }
        .table-title { font-size: 16px; font-weight: bold; color: var(--primary-color); margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 10px; font-size: 13px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); font-weight: 600; }
        td { padding: 14px 10px; font-size: 13px; border-bottom: 1px solid #f9f9f9; }

        .progress-bar-bg { background-color: #eee; border-radius: 10px; height: 10px; width: 100%; overflow: hidden; }
        .progress-bar-fill { background-color: var(--primary-color); height: 100%; border-radius: 10px; }

        .footer { text-align: center; padding: 20px; color: var(--text-muted); font-size: 12px; margin-top: auto; }
    </style>
</head>
<body>

    <div class="header-bar">
        <div class="logo-section">
            <div class="logo-box">ABC</div>
            <div class="brand-name"><strong>EDULINGO</strong></div>
        </div>
        <div style="position: relative;">
            <button class="user-profile" id="userMenuBtn"><i class="fa-regular fa-user"></i> admin</button>
            <div class="dropdown-menu" id="userDropdown">
                <a href="index.php?controller=review&action=logout" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="main-container">
        <div class="nav-tabs">
            <a href="index.php?controller=stat&action=index" class="nav-tab active">Thống kê</a>
            <a href="index.php?controller=lecturer&action=index" class="nav-tab">Giảng viên</a>
            <a href="index.php?controller=student&action=index" class="nav-tab">Học viên</a>
            <a href="index.php?controller=review&action=index" class="nav-tab">Đánh giá</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-calendar-check"></i></div>
                <div>
                    <div class="stat-label">Tổng số lịch hẹn</div>
                    <div class="stat-value"><?= $total_appointments ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                <div>
                    <div class="stat-label">Đang chờ xử lý</div>
                    <div class="stat-value"><?= $total_pending ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="stat-label">Đã duyệt</div>
                    <div class="stat-value"><?= $total_approved ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-check-double"></i></div>
                <div>
                    <div class="stat-label">Đã hoàn thành</div>
                    <div class="stat-value"><?= $total_completed ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-ban"></i></div>
                <div>
                    <div class="stat-label">Đã từ chối</div>
                    <div class="stat-value"><?= $total_rejected ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-xmark"></i></div>
                <div>
                    <div class="stat-label">Đã hủy</div>
                    <div class="stat-value"><?= $total_cancelled ?></div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-title">Thống kê hoạt động theo Giảng viên</div>
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Giảng viên</th>
                        <th>Tổng lịch hẹn</th>
                        <th>Tỷ lệ hoàn thành</th>
                        <th>Đang chờ</th>
                        <th>Đã hoàn thành</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): ?>
                        <?php foreach ($teachers as $index => $t): ?>
                        <?php $pct = get_percentage($t['total_hen'], $max_hen); ?>
                        <tr>
                            <td><?= sprintf("%02d", $index + 1) ?></td>
                            <td><strong><?= htmlspecialchars($t['lecturer_name']) ?></strong></td>
                            <td><?= $t['total_hen'] ?></td>
                            <td style="width: 200px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div class="progress-bar-bg">
                                        <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                                    </div>
                                    <span style="font-size:11px; font-weight:bold;"><?= get_percentage($t['total_xong'], $t['total_hen']) ?>%</span>
                                </div>
                            </td>
                            <td><span style="color: #f39c12; font-weight: bold;"><?= $t['total_cho'] ?></span></td>
                            <td><span style="color: #27ae60; font-weight: bold;"><?= $t['total_xong'] ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding: 20px; color: var(--text-muted);">Chưa có dữ liệu thống kê.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">EDULINGO Administration System &copy; 2026</div>

    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('show'); });
        document.addEventListener('click', () => userDropdown.classList.remove('show'));
    </script>
</body>
</html>