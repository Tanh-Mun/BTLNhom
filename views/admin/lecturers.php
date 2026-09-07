<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Giảng viên - EDULINGO Admin</title>
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

        .alert { padding: 12px 20px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .alert-danger { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }

        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 15px; }
        .search-box { display: flex; gap: 10px; flex: 1; max-width: 400px; }
        .search-input { width: 100%; padding: 10px 16px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 13px; }
        .btn-search { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 13px; }
        .btn-add { background-color: var(--primary-color); color: white; border: none; padding: 10px 20px; border-radius: 20px; cursor: pointer; font-size: 13px; font-weight: bold; }

        .table-card { background-color: white; border-radius: 16px; padding: 25px; border: 1px solid var(--border-color); }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 10px; font-size: 13px; color: var(--text-muted); border-bottom: 1px solid var(--border-color); font-weight: 600; }
        td { padding: 14px 10px; font-size: 13px; border-bottom: 1px solid #f9f9f9; }

        .avatar-circle { width: 35px; height: 35px; border-radius: 50%; background-color: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; }
        .btn-action { background-color: #fff0f3; color: var(--primary-color); border: 1px solid var(--border-color); padding: 6px 12px; border-radius: 12px; font-size: 12px; cursor: pointer; margin-right: 4px; }
        .btn-action:hover { background-color: var(--primary-color); color: white; }

        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); z-index: 2000; justify-content: center; align-items: center; }
        .modal.show { display: flex; }
        .modal-content { background: white; border-radius: 16px; padding: 25px; width: 100%; max-width: 500px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 12px; font-weight: bold; margin-bottom: 5px; color: var(--text-muted); }
        .form-control { width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13px; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }

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
            <a href="index.php?controller=lecturer&action=index" class="nav-tab active">Giảng viên</a>
            <a href="index.php?controller=student&action=index" class="nav-tab">Học viên</a>
            <a href="index.php?controller=review&action=index" class="nav-tab">Đánh giá</a>
        </div>

        <?php if (!empty($msg)): ?>
            <div class="alert alert-<?= $msg_type ?>"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="action-bar">
            <form method="GET" action="index.php" class="search-box">
                <input type="hidden" name="controller" value="lecturer">
                <input type="hidden" name="action" value="index">
                <input type="text" name="keyword" class="search-input" placeholder="Tìm kiếm giảng viên..." value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i></button>
            </form>
            <button class="btn-add" onclick="openAddModal()"><i class="fa-solid fa-plus"></i> Thêm giảng viên</button>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Avatar</th>
                        <th>Họ tên</th>
                        <th>Khoa/Bộ môn</th>
                        <th>Email / SĐT</th>
                        <th>Chuyên môn</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($filteredTeachers)): ?>
                        <?php foreach ($filteredTeachers as $index => $t): ?>
                        <tr>
                            <td><?= sprintf("%02d", $index + 1) ?></td>
                            <td><div class="avatar-circle"><?= htmlspecialchars($t['avatar']) ?></div></td>
                            <td><strong><?= htmlspecialchars($t['name']) ?></strong><br><small style="color:var(--text-muted)">@<?= htmlspecialchars($t['username']) ?></small></td>
                            <td><?= htmlspecialchars($t['dept']) ?></td>
                            <td><?= htmlspecialchars($t['email']) ?><br><small><?= htmlspecialchars($t['phone']) ?></small></td>
                            <td><?= htmlspecialchars($t['desc']) ?></td>
                            <td>
                                <button class="btn-action" onclick='openEditModal(<?= json_encode($t) ?>)'><i class="fa-regular fa-pen-to-square"></i></button>
                                <form method="POST" action="index.php?controller=lecturer&action=index" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa giảng viên này?');">
                                    <input type="hidden" name="action" value="delete_lecturer">
                                    <input type="hidden" name="lecturer_id" value="<?= $t['id'] ?>">
                                    <button type="submit" class="btn-action"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" style="text-align:center; padding: 20px; color: var(--text-muted);">Không tìm thấy giảng viên nào.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Thêm/Sửa Giảng Viên -->
    <div class="modal" id="lecturerModal">
        <div class="modal-content">
            <h3 id="modalTitle" style="color: var(--primary-color); margin-bottom: 15px;">Thêm giảng viên</h3>
            <form method="POST" action="index.php?controller=lecturer&action=index">
                <input type="hidden" name="action" id="formAction" value="add_lecturer">
                <input type="hidden" name="lecturer_id" id="lecturerId" value="">
                
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="fullname" id="fullname" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tên đăng nhập (Username)</label>
                    <input type="text" name="username" id="username" class="form-control" required>
                </div>
                <div class="form-group" id="passGroup">
                    <label>Mật khẩu (Mặc định: 123456)</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" id="phone" class="form-control">
                </div>
                <div class="form-group">
                    <label>Khoa / Phòng ban</label>
                    <input type="text" name="faculty" id="faculty" class="form-control">
                </div>
                <div class="form-group">
                    <label>Chuyên môn</label>
                    <input type="text" name="specialty" id="specialty" class="form-control">
                </div>
                <div class="form-group">
                    <label>Giới thiệu (Bio)</label>
                    <textarea name="bio" id="bio" class="form-control" rows="2"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-action" onclick="closeModal()">Hủy</button>
                    <button type="submit" class="btn-add">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">EDULINGO Administration System &copy; 2026</div>

    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('show'); });
        document.addEventListener('click', () => userDropdown.classList.remove('show'));

        const modal = document.getElementById('lecturerModal');
        function openAddModal() {
            document.getElementById('modalTitle').innerText = 'Thêm giảng viên';
            document.getElementById('formAction').value = 'add_lecturer';
            document.getElementById('lecturerId').value = '';
            document.getElementById('fullname').value = '';
            document.getElementById('username').value = '';
            document.getElementById('email').value = '';
            document.getElementById('phone').value = '';
            document.getElementById('faculty').value = '';
            document.getElementById('specialty').value = '';
            document.getElementById('bio').value = '';
            document.getElementById('passGroup').style.display = 'block';
            modal.classList.add('show');
        }

        function openEditModal(data) {
            document.getElementById('modalTitle').innerText = 'Cập nhật giảng viên';
            document.getElementById('formAction').value = 'update_lecturer';
            document.getElementById('lecturerId').value = data.id;
            document.getElementById('fullname').value = data.name;
            document.getElementById('username').value = data.username;
            document.getElementById('email').value = data.email;
            document.getElementById('phone').value = data.phone;
            document.getElementById('faculty').value = data.dept;
            document.getElementById('specialty').value = data.desc;
            document.getElementById('bio').value = data.intro;
            document.getElementById('passGroup').style.display = 'none';
            modal.classList.add('show');
        }

        function closeModal() { modal.classList.remove('show'); }
    </script>
</body>
</html>