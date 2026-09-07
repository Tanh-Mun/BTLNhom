<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Học viên - EDULINGO Admin</title>
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

        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .search-box { display: flex; gap: 10px; flex: 1; max-width: 400px; }
        .search-input { width: 100%; padding: 10px 16px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 13px; }
        .btn-search { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 13px; }

        .table-card { background-color: white; border-radius: 16px; padding: 25px; border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 10px; font-size: 13px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); font-weight: 600; }
        td { padding: 14px 10px; font-size: 13px; border-bottom: 1px solid #f9f9f9; }

        .avatar-circle { width: 35px; height: 35px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
        .badge { background-color: var(--primary-light); color: var(--primary-color); padding: 4px 8px; border-radius: 10px; font-size: 11px; font-weight: bold; }

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
            <a href="index.php?controller=stat&action=index" class="nav-tab">Thống kê</a>
            <a href="index.php?controller=lecturer&action=index" class="nav-tab">Giảng viên</a>
            <a href="index.php?controller=student&action=index" class="nav-tab active">Học viên</a>
            <a href="index.php?controller=review&action=index" class="nav-tab">Đánh giá</a>
        </div>

        <div class="action-bar">
            <form method="GET" action="index.php" class="search-box">
                <input type="hidden" name="controller" value="student">
                <input type="hidden" name="action" value="index">
                <input type="text" name="keyword" class="search-input" placeholder="Tìm kiếm tên, mã SV, email..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Avatar</th>
                        <th>Mã SV</th>
                        <th>Họ và tên</th>
                        <th>Khoa / Ngành</th>
                        <th>Email / SĐT</th>
                        <th>Mô tả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $index => $s): ?>
                        <tr>
                            <td><?= sprintf("%02d", $index + 1) ?></td>
                            <td><div class="avatar-circle"><?= htmlspecialchars($s['avatar']) ?></div></td>
                            <td><span class="badge"><?= htmlspecialchars($s['student_code'] ?: 'N/A') ?></span></td>
                            <td><strong><?= htmlspecialchars($s['name']) ?></strong><br><small style="color:var(--text-muted)">@<?= htmlspecialchars($s['username']) ?></small></td>
                            <td><?= htmlspecialchars($s['faculty']) ?><br><small style="color:var(--text-muted)"><?= htmlspecialchars($s['major']) ?></small></td>
                            <td><?= htmlspecialchars($s['email']) ?><br><small><?= htmlspecialchars($s['phone']) ?></small></td>
                            <td><small style="color:var(--text-muted)"><?= htmlspecialchars($s['bio'] ?: 'Chưa cập nhật') ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding: 20px; color: var(--text-muted);">Không tìm thấy học viên nào.</td></tr>
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