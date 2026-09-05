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

$msg = '';
$msg_type = 'success';

// 1. XỬ LÝ THÊM, SỬA HOẶC XÓA HỌC VIÊN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- XÓA HỌC VIÊN ---
    if ($action === 'delete_student') {
        $student_id = intval($_POST['student_id']);
        try {
            $pdo->beginTransaction();

            // Xóa thông tin chi tiết ở bảng student_profiles trước
            $stmtProf = $pdo->prepare("DELETE FROM student_profiles WHERE user_id = ?");
            $stmtProf->execute([$student_id]);

            // Xóa tài khoản ở bảng users
            $stmtUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtUser->execute([$student_id]);

            $pdo->commit();
            $msg = "Đã xóa học viên thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Lỗi khi xóa học viên: " . $e->getMessage();
            $msg_type = 'danger';
        }
    }

    // --- THÊM HỌC VIÊN MỚI ---
    elseif ($action === 'add_student') {
        $fullname     = trim($_POST['fullname']);
        $username     = trim($_POST['username']);
        $email        = trim($_POST['email']);
        $phone        = trim($_POST['phone']);
        $student_code = trim($_POST['student_code']);
        $faculty      = trim($_POST['faculty']);
        $major        = trim($_POST['major']);
        $bio          = trim($_POST['bio']);
        $password     = !empty($_POST['password']) ? md5(trim($_POST['password'])) : md5('123456');

        try {
            $pdo->beginTransaction();

            // Kiểm tra username hoặc email trùng lặp
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmtCheck->execute([$username, $email]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Tên đăng nhập hoặc Email này đã tồn tại trong hệ thống!");
            }

            // 1. Thêm vào bảng users
            $stmtUser = $pdo->prepare("INSERT INTO users (fullname, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'student')");
            $stmtUser->execute([$fullname, $username, $email, $phone, $password]);
            $new_user_id = $pdo->lastInsertId();

            // 2. Thêm vào bảng student_profiles
            $stmtProf = $pdo->prepare("INSERT INTO student_profiles (user_id, student_code, faculty, major, bio) VALUES (?, ?, ?, ?, ?)");
            $stmtProf->execute([$new_user_id, $student_code, $faculty, $major, $bio]);

            $pdo->commit();
            $msg = "Thêm học viên mới thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Lỗi: " . $e->getMessage();
            $msg_type = 'danger';
        }
    }

    // --- CẬP NHẬT TÀI KHOẢN HỌC VIÊN ---
    elseif ($action === 'update_student') {
        $student_id   = intval($_POST['student_id']);
        $fullname     = trim($_POST['fullname']);
        $username     = trim($_POST['username']);
        $email        = trim($_POST['email']);
        $phone        = trim($_POST['phone']);
        $student_code = trim($_POST['student_code']);
        $faculty      = trim($_POST['faculty']);
        $major        = trim($_POST['major']);
        $bio          = trim($_POST['bio']);

        try {
            $pdo->beginTransaction();

            // 1. Cập nhật bảng users
            $stmtUser = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, phone = ? WHERE id = ?");
            $stmtUser->execute([$fullname, $username, $email, $phone, $student_id]);

            // 2. Cập nhật/Chèn mới bảng student_profiles
            $stmtCheckProf = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id = ?");
            $stmtCheckProf->execute([$student_id]);
            
            if ($stmtCheckProf->fetch()) {
                $stmtProf = $pdo->prepare("UPDATE student_profiles SET student_code = ?, faculty = ?, major = ?, bio = ? WHERE user_id = ?");
                $stmtProf->execute([$student_code, $faculty, $major, $bio, $student_id]);
            } else {
                $stmtProf = $pdo->prepare("INSERT INTO student_profiles (user_id, student_code, faculty, major, bio) VALUES (?, ?, ?, ?, ?)");
                $stmtProf->execute([$student_id, $student_code, $faculty, $major, $bio]);
            }

            $pdo->commit();
            $msg = "Cập nhật thông tin học viên thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Lỗi khi cập nhật: " . $e->getMessage();
            $msg_type = 'danger';
        }
    }
}

// 2. TRUY VẤN DANH SÁCH HỌC VIÊN KÈM THEO HỒ SƠ TỪ DATABASE
$searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$sql = "SELECT u.id, u.fullname AS name, u.username, u.email, u.phone,
               sp.student_code, sp.faculty, sp.major, sp.bio
        FROM users u
        LEFT JOIN student_profiles sp ON u.id = sp.user_id
        WHERE u.role = 'student' OR u.role = 'hocvien' OR u.role = 'user'";

if ($searchKeyword !== '') {
    $sql .= " AND (u.fullname LIKE :kw OR u.username LIKE :kw OR u.email LIKE :kw OR u.phone LIKE :kw OR sp.student_code LIKE :kw)";
}

$stmt = $pdo->prepare($sql);
if ($searchKeyword !== '') {
    $stmt->bindValue(':kw', '%' . $searchKeyword . '%');
}
$stmt->execute();
$students_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

$students = [];
foreach ($students_db as $s) {
    $firstChar = !empty($s['name']) ? mb_strtoupper(mb_substr($s['name'], 0, 1, 'UTF-8'), 'UTF-8') : 'H';
    $students[] = [
        'id'           => $s['id'],
        'name'         => $s['name'] ?? '',
        'username'     => $s['username'] ?? '',
        'avatar'       => $firstChar,
        'email'        => $s['email'] ?? '',
        'phone'        => $s['phone'] ?? '',
        'student_code' => $s['student_code'] ?? '',
        'faculty'      => $s['faculty'] ?? '',
        'major'        => $s['major'] ?? '',
        'bio'          => $s['bio'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Học Viên - EDULINGO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
            --text-color: #333;
            --danger-color: #dc3545;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); color: var(--text-color); display: flex; flex-direction: column; min-height: 100vh; }

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

        .nav-tabs-wrapper { display: flex; justify-content: flex-start; margin-bottom: 25px; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .nav-tab { padding: 8px 22px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; transition: all 0.2s ease; }
        .nav-tab.active { background-color: var(--primary-color); color: white; }

        .search-bar-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .search-input-form { position: relative; width: 350px; display: flex; align-items: center; }
        .search-input-form input { width: 100%; padding: 10px 15px 10px 35px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 13px; background: white; color: var(--primary-color); }
        .search-input-form input::placeholder { color: var(--primary-color); opacity: 0.7; }
        .search-input-form i.fa-magnifying-glass { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--primary-color); font-size: 13px; opacity: 0.7; }

        .btn-add-student {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 9px 20px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }
        .btn-add-student:hover { opacity: 0.9; }

        .student-list { display: flex; flex-direction: column; gap: 15px; }
        .student-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; padding: 18px 25px; display: flex; align-items: center; justify-content: space-between; }
        .student-left { display: flex; align-items: center; gap: 15px; }
        .student-avatar { width: 45px; height: 45px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; }
        .student-info h4 { color: var(--primary-color); font-size: 15px; margin-bottom: 3px; }
        .student-info .email-phone { font-size: 12px; color: #666; margin-bottom: 4px; }
        .student-tag { font-size: 11px; background: #fce4ec; color: var(--primary-color); padding: 2px 8px; border-radius: 10px; display: inline-block; font-weight: 600; }

        .student-actions { display: flex; align-items: center; gap: 8px; }
        .btn-view-profile { background: white; color: var(--primary-color); border: 1px solid var(--border-color); padding: 7px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; transition: all 0.2s; }
        .btn-view-profile:hover { background: var(--primary-light); }
        .btn-delete-student { background: #fff0f0; color: var(--danger-color); border: 1px solid #fbc4c4; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; transition: all 0.2s; }
        .btn-delete-student:hover { background: var(--danger-color); color: white; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; width: 680px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; max-height: 90vh; }
        .modal-header { padding: 18px 25px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: white; }
        .modal-header-left { display: flex; align-items: center; gap: 12px; }
        .modal-avatar { width: 40px; height: 40px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }
        .modal-title-info h4 { color: var(--primary-color); font-size: 14px; margin-bottom: 2px; }
        .modal-title-info p { font-size: 11px; color: #666; }
        .modal-close { background: transparent; border: 1px solid var(--border-color); width: 30px; height: 30px; border-radius: 50%; color: var(--primary-color); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }
        
        .modal-body { padding: 25px; overflow-y: auto; background: white; flex: 1; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 2; }
        .form-group label { font-size: 12px; color: var(--primary-color); font-weight: 600; }
        .form-control { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 12px; color: var(--primary-color); background: white; width: 100%; }
        textarea.form-control { border-radius: 12px; resize: vertical; }

        .modal-footer { padding: 15px 25px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px; background: white; }
        .btn-action-outline { background: white; color: var(--primary-color); border: 1px solid var(--border-color); padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; }
        .btn-action-primary { background: var(--primary-color); color: white; border: none; padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; }

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
        <?php if (!empty($msg)): ?>
            <div style="padding: 12px 20px; background: <?= $msg_type === 'danger' ? '#f8d7da' : '#d4edda' ?>; color: <?= $msg_type === 'danger' ? '#721c24' : '#155724' ?>; border-radius: 10px; margin-bottom: 20px; font-size: 13px;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="nav-tabs-wrapper">
            <div class="nav-tabs">
                <a href="tk-admin.php" class="nav-tab"><i class="fa-solid fa-chart-pie"></i> Thống kê</a>
                <a href="gv-admin.php" class="nav-tab"><i class="fa-solid fa-user-tie"></i> Giảng viên</a>
                <a href="hv-admin.php" class="nav-tab active"><i class="fa-solid fa-user-graduate"></i> Học viên</a>
                <a href="dg-admin.php" class="nav-tab"><i class="fa-regular fa-star"></i> Đánh giá</a>
            </div>
        </div>

        <div class="search-bar-container">
            <div class="search-input-form">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" value="<?= htmlspecialchars($searchKeyword) ?>" placeholder="Tìm tên, username, mã SV...">
            </div>
            
            <button class="btn-add-student" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Thêm học viên
            </button>
        </div>

        <div class="student-list" id="studentList">
            <?php if (count($students) > 0): ?>
                <?php foreach ($students as $s): ?>
                <div class="student-card">
                    <div class="student-left">
                        <div class="student-avatar"><?= $s['avatar'] ?></div>
                        <div class="student-info">
                            <h4><?= htmlspecialchars($s['name']) ?> <span style="font-size: 12px; color: #888; font-weight: normal;">(@<?= htmlspecialchars($s['username']) ?>)</span></h4>
                            <div class="email-phone">
                                <i class="fa-regular fa-envelope"></i> <?= htmlspecialchars($s['email']) ?> 
                                <?php if (!empty($s['phone'])): ?> | <i class="fa-solid fa-phone"></i> <?= htmlspecialchars($s['phone']) ?><?php endif; ?>
                            </div>
                            <?php if (!empty($s['student_code']) || !empty($s['faculty'])): ?>
                                <div>
                                    <?php if (!empty($s['student_code'])): ?><span class="student-tag">MSV: <?= htmlspecialchars($s['student_code']) ?></span><?php endif; ?>
                                    <?php if (!empty($s['faculty'])): ?><span class="student-tag" style="background: #e3f2fd; color: #1976d2;"><?= htmlspecialchars($s['faculty']) ?></span><?php endif; ?>
                                    <?php if (!empty($s['major'])): ?><span class="student-tag" style="background: #e8f5e9; color: #388e3c;"><?= htmlspecialchars($s['major']) ?></span><?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="student-actions">
                        <button class="btn-view-profile" onclick="openStudentModal(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)">Xem & Sửa hồ sơ</button>
                        <button class="btn-delete-student" onclick="confirmDelete(<?= $s['id'] ?>, '<?= htmlspecialchars($s['name'], ENT_QUOTES) ?>')"><i class="fa-solid fa-trash-can"></i> Xóa</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: var(--primary-color); background: white; border-radius: 15px; border: 1px solid var(--border-color); font-size: 13px;">
                    Không tìm thấy học viên phù hợp.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- FORM ẨN GỬI YÊU CẦU XÓA -->
    <form id="deleteForm" method="POST" action="hv-admin.php" style="display:none;">
        <input type="hidden" name="action" value="delete_student">
        <input type="hidden" name="student_id" id="deleteStudentId">
    </form>

    <!-- MODAL THÊM & SỬA HỌC VIÊN -->
    <div class="modal-overlay" id="studentModal">
        <form class="modal-box" method="POST" action="hv-admin.php">
            <input type="hidden" name="action" id="modalAction" value="update_student">
            <input type="hidden" name="student_id" id="modalStudentId">

            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-avatar" id="modalAvatar">H</div>
                    <div class="modal-title-info">
                        <small id="modalSubTitle" style="font-size: 10px; color: #888; text-transform: uppercase;">Chỉnh sửa học viên</small>
                        <h4 id="modalStudentName">Học viên</h4>
                        <p id="modalStudentSub">Tài khoản sinh viên</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeStudentModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="modal-body">
                <!-- THÔNG TIN TÀI KHOẢN -->
                <div class="form-grid">
                    <div class="form-group">
                        <label>Họ và tên <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" name="fullname" id="inputName" required placeholder="Nhập họ tên...">
                    </div>
                    <div class="form-group">
                        <label>Tên đăng nhập (Username) <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" name="username" id="inputUsername" required placeholder="Nhập username...">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Email <span style="color:red;">*</span></label>
                        <input type="email" class="form-control" name="email" id="inputEmail" required placeholder="Nhập email...">
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" class="form-control" name="phone" id="inputPhone" placeholder="Nhập SĐT...">
                    </div>
                </div>

                <!-- THÔNG TIN HỒ SƠ STUDENT_PROFILES -->
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mã sinh viên (student_code)</label>
                        <input type="text" class="form-control" name="student_code" id="inputStudentCode" placeholder="VD: SV001">
                    </div>
                    <div class="form-group">
                        <label>Khoa (faculty)</label>
                        <input type="text" class="form-control" name="faculty" id="inputFaculty" placeholder="VD: Khóa học Tiếng Anh">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group full">
                        <label>Chuyên ngành / Ngoại ngữ (major)</label>
                        <input type="text" class="form-control" name="major" id="inputMajor" placeholder="VD: Tiếng Anh Giao tiếp">
                    </div>
                </div>

                <div class="form-group full" style="margin-bottom: 15px;">
                    <label>Mô tả / Ghi chú (bio)</label>
                    <textarea class="form-control" name="bio" id="inputBio" rows="3" placeholder="Nhập mô tả hoặc ghi chú tư vấn..."></textarea>
                </div>

                <div class="form-group full" id="passwordGroup" style="display: none; margin-bottom: 15px;">
                    <label>Mật khẩu tài khoản (Mặc định: 123456)</label>
                    <input type="password" class="form-control" name="password" id="inputPassword" placeholder="Để trống nếu dùng mật khẩu mặc định 123456">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-outline" onclick="closeStudentModal()">Hủy</button>
                <button type="submit" class="btn-action-primary" id="btnSubmitModal">Lưu thông tin</button>
            </div>
        </form>
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
                <h5>DÀNH CHO GIẢNG VIÊN</h5>
                <ul class="footer-links">
                    <li><a href="#">Đăng ký giảng dạy</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>NHẬN THÔNG BÁO</h5>
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

        // 1. MỞ MODAL THÊM HỌC VIÊN
        function openAddModal() {
            document.getElementById('modalAction').value = 'add_student';
            document.getElementById('modalStudentId').value = '';
            
            document.getElementById('modalAvatar').innerText = '+';
            document.getElementById('modalSubTitle').innerText = 'TẠO TÀI KHOẢN MỚI';
            document.getElementById('modalStudentName').innerText = 'Thêm học viên mới';
            document.getElementById('modalStudentSub').innerText = 'Nhập thông tin tài khoản cho học viên';

            document.getElementById('inputName').value = '';
            document.getElementById('inputUsername').value = '';
            document.getElementById('inputEmail').value = '';
            document.getElementById('inputPhone').value = '';
            document.getElementById('inputStudentCode').value = '';
            document.getElementById('inputFaculty').value = '';
            document.getElementById('inputMajor').value = '';
            document.getElementById('inputBio').value = '';
            
            document.getElementById('passwordGroup').style.display = 'flex';
            document.getElementById('btnSubmitModal').innerText = 'Tạo mới học viên';

            document.getElementById('studentModal').classList.add('show');
        }

        // 2. MỞ MODAL SỬA HỌC VIÊN
        function openStudentModal(student) {
            document.getElementById('modalAction').value = 'update_student';
            document.getElementById('modalStudentId').value = student.id;
            
            document.getElementById('modalAvatar').innerText = student.avatar;
            document.getElementById('modalSubTitle').innerText = 'CHỈNH SỬA THÔNG TIN HỌC VIÊN';
            document.getElementById('modalStudentName').innerText = student.name;
            document.getElementById('modalStudentSub').innerText = 'Username: ' + student.username;
            
            document.getElementById('inputName').value = student.name;
            document.getElementById('inputUsername').value = student.username;
            document.getElementById('inputEmail').value = student.email;
            document.getElementById('inputPhone').value = student.phone || '';
            document.getElementById('inputStudentCode').value = student.student_code || '';
            document.getElementById('inputFaculty').value = student.faculty || '';
            document.getElementById('inputMajor').value = student.major || '';
            document.getElementById('inputBio').value = student.bio || '';

            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('btnSubmitModal').innerText = 'Lưu thông tin';

            document.getElementById('studentModal').classList.add('show');
        }

        function closeStudentModal() {
            document.getElementById('studentModal').classList.remove('show');
        }

        // 3. XÁC NHẬN XÓA
        function confirmDelete(id, name) {
            if (confirm(`Bạn có chắc chắn muốn xóa học viên "${name}" khỏi hệ thống không?`)) {
                document.getElementById('deleteStudentId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                window.location.href = 'hv-admin.php?keyword=' + encodeURIComponent(this.value);
            }
        });
    </script>
</body>
</html>