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

// Kiểm tra xem các cột trong lecturer_profiles có tồn tại hay không
$has_faculty = false;
$has_specialty = false;
$has_bio = false;

try {
    $cols = $pdo->query("DESCRIBE lecturer_profiles")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('faculty', $cols) || in_array('department', $cols)) {
        $faculty_col = in_array('faculty', $cols) ? 'faculty' : 'department';
        $has_faculty = true;
    }
    if (in_array('specialty', $cols)) { $has_specialty = true; }
    if (in_array('bio', $cols)) { $has_bio = true; }
} catch (Exception $e) {
    // Bảng chưa tồn tại
}

$msg = '';
$msg_type = 'success';

// 1. XỬ LÝ THÊM, SỬA HOẶC XÓA GIẢNG VIÊN (DATABASE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // --- XÓA GIẢNG VIÊN ---
    if ($action === 'delete_lecturer') {
        $lecturer_id = intval($_POST['lecturer_id']);
        try {
            $pdo->beginTransaction();

            $stmtProfile = $pdo->prepare("DELETE FROM lecturer_profiles WHERE user_id = ?");
            $stmtProfile->execute([$lecturer_id]);

            $stmtUser = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtUser->execute([$lecturer_id]);

            $pdo->commit();
            $msg = "Đã xóa giảng viên thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Lỗi khi xóa giảng viên: " . $e->getMessage();
            $msg_type = 'danger';
        }
    }

    // --- THÊM GIẢNG VIÊN MỚI ---
    elseif ($action === 'add_lecturer') {
        $fullname  = trim($_POST['fullname']);
        $username  = trim($_POST['username']);
        $email     = trim($_POST['email']);
        $phone     = trim($_POST['phone']);
        $faculty   = trim($_POST['faculty']);
        $specialty = trim($_POST['specialty']);
        $bio       = trim($_POST['bio']);
        $password  = !empty($_POST['password']) ? md5(trim($_POST['password'])) : md5('123456');

        try {
            $pdo->beginTransaction();

            // Kiểm tra username hoặc email trùng lặp
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmtCheck->execute([$username, $email]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Tên đăng nhập hoặc Email này đã tồn tại trong hệ thống!");
            }

            // Thêm vào bảng users kèm username
            $stmtUser = $pdo->prepare("INSERT INTO users (fullname, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'lecturer')");
            $stmtUser->execute([$fullname, $username, $email, $phone, $password]);
            $new_user_id = $pdo->lastInsertId();

            $insertCols = ['user_id'];
            $insertVals = [$new_user_id];

            if ($has_faculty) { $insertCols[] = $faculty_col; $insertVals[] = $faculty; }
            if ($has_specialty) { $insertCols[] = 'specialty'; $insertVals[] = $specialty; }
            if ($has_bio) { $insertCols[] = 'bio'; $insertVals[] = $bio; }

            $placeholders = implode(',', array_fill(0, count($insertCols), '?'));
            $sqlProfile = "INSERT INTO lecturer_profiles (" . implode(',', $insertCols) . ") VALUES ($placeholders)";
            $stmtProfile = $pdo->prepare($sqlProfile);
            $stmtProfile->execute($insertVals);

            $pdo->commit();
            $msg = "Thêm giảng viên mới thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Lỗi: " . $e->getMessage();
            $msg_type = 'danger';
        }
    }

    // --- CẬP NHẬT HỒ SƠ GIẢNG VIÊN ---
    elseif ($action === 'update_lecturer') {
        $lecturer_id = intval($_POST['lecturer_id']);
        $fullname    = trim($_POST['fullname']);
        $username    = trim($_POST['username']);
        $email       = trim($_POST['email']);
        $phone       = trim($_POST['phone']);
        $faculty     = trim($_POST['faculty']);
        $specialty   = trim($_POST['specialty']);
        $bio         = trim($_POST['bio']);

        try {
            $pdo->beginTransaction();

            // Cập nhật thông tin tài khoản bao gồm username
            $stmtUser = $pdo->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, phone = ? WHERE id = ?");
            $stmtUser->execute([$fullname, $username, $email, $phone, $lecturer_id]);

            $stmtCheck = $pdo->prepare("SELECT id FROM lecturer_profiles WHERE user_id = ?");
            $stmtCheck->execute([$lecturer_id]);
            $exists = $stmtCheck->fetch();

            $updateFields = [];
            $updateParams = [];

            if ($has_faculty) { $updateFields[] = "$faculty_col = ?"; $updateParams[] = $faculty; }
            if ($has_specialty) { $updateFields[] = "specialty = ?"; $updateParams[] = $specialty; }
            if ($has_bio) { $updateFields[] = "bio = ?"; $updateParams[] = $bio; }

            if (!empty($updateFields)) {
                if ($exists) {
                    $sqlProfile = "UPDATE lecturer_profiles SET " . implode(', ', $updateFields) . " WHERE user_id = ?";
                    $updateParams[] = $lecturer_id;
                    $stmtProfile = $pdo->prepare($sqlProfile);
                    $stmtProfile->execute($updateParams);
                } else {
                    $colsInsert = array_merge(['user_id'], array_map(function($f) { return explode(' =', $f)[0]; }, $updateFields));
                    $placeholders = array_fill(0, count($colsInsert), '?');
                    $sqlProfile = "INSERT INTO lecturer_profiles (" . implode(', ', $colsInsert) . ") VALUES (" . implode(', ', $placeholders) . ")";
                    array_unshift($updateParams, $lecturer_id);
                    $stmtProfile = $pdo->prepare($sqlProfile);
                    $stmtProfile->execute($updateParams);
                }
            }

            $pdo->commit();
            $msg = "Cập nhật hồ sơ giảng viên thành công!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $msg = "Lỗi khi cập nhật: " . $e->getMessage();
            $msg_type = 'danger';
        }
    }
}

// 2. TRUY VẤN TOÀN BỘ DANH SÁCH GIẢNG VIÊN ĐỂ LỌC TRỰC TIẾP TRÊN GIAO DIỆN
$selectFaculty   = $has_faculty ? "lp.$faculty_col AS dept" : "NULL AS dept";
$selectSpecialty = $has_specialty ? "lp.specialty AS desc_text" : "NULL AS desc_text";
$selectBio       = $has_bio ? "lp.bio AS intro" : "NULL AS intro";

$sql = "SELECT 
            u.id, 
            u.fullname AS name, 
            u.username,
            u.email, 
            u.phone, 
            $selectFaculty, 
            $selectSpecialty, 
            $selectBio
        FROM users u
        LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id
        WHERE u.role = 'lecturer' OR u.role = 'teacher' OR u.role = 'gv'";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$teachers_db = $stmt->fetchAll(PDO::FETCH_ASSOC);

$teachers = [];
foreach ($teachers_db as $t) {
    $firstChar = !empty($t['name']) ? mb_strtoupper(mb_substr($t['name'], 0, 1, 'UTF-8'), 'UTF-8') : 'G';
    $teachers[] = [
        'id'       => $t['id'],
        'name'     => $t['name'] ?? '',
        'username' => $t['username'] ?? '',
        'avatar'   => $firstChar,
        'dept'     => $t['dept'] ?? 'Chưa cập nhật',
        'email'    => $t['email'] ?? '',
        'phone'    => $t['phone'] ?? '',
        'desc'     => $t['desc_text'] ?? 'Chưa cập nhật chuyên môn',
        'intro'    => $t['intro'] ?? ''
    ];
}

$filteredTeachers = $teachers;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giảng Viên - EDULINGO Admin</title>
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

        .btn-add-teacher {
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
        .btn-add-teacher:hover { opacity: 0.9; }

        .teacher-list { display: flex; flex-direction: column; gap: 15px; }
        .teacher-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; padding: 18px 25px; display: flex; align-items: center; justify-content: space-between; }
        .teacher-left { display: flex; align-items: center; gap: 15px; }
        .teacher-avatar { width: 45px; height: 45px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; }
        .teacher-info h4 { color: var(--primary-color); font-size: 15px; margin-bottom: 3px; }
        .teacher-info .dept-email { font-size: 12px; color: #666; margin-bottom: 3px; }
        .teacher-info .desc { font-size: 12px; color: var(--primary-color); opacity: 0.85; }

        .teacher-actions { display: flex; align-items: center; gap: 8px; }
        .btn-view-profile { background: white; color: var(--primary-color); border: 1px solid var(--border-color); padding: 7px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; transition: all 0.2s; }
        .btn-view-profile:hover { background: var(--primary-light); }
        .btn-delete-teacher { background: #fff0f0; color: var(--danger-color); border: 1px solid #fbc4c4; padding: 7px 14px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; transition: all 0.2s; }
        .btn-delete-teacher:hover { background: var(--danger-color); color: white; }

        /* MODAL */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; width: 720px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; max-height: 90vh; }
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
        .form-group label { font-size: 12px; color: var(--primary-color); font-weight: 500; }
        .form-control { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 12px; color: var(--primary-color); background: white; width: 100%; }
        textarea.form-control { border-radius: 12px; resize: none; height: 75px; }
        
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
                <a href="gv-admin.php" class="nav-tab active"><i class="fa-regular fa-user"></i> Giảng viên</a>
                <a href="hv-admin.php" class="nav-tab"><i class="fa-solid fa-graduation-cap"></i> Học viên</a>
                <a href="dg-admin.php" class="nav-tab"><i class="fa-regular fa-star"></i> Đánh giá</a>
            </div>
        </div>

        <div class="search-bar-container">
            <div class="search-input-form">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Tìm theo tên, username, khoa...">
            </div>
            
            <button class="btn-add-teacher" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Thêm giảng viên
            </button>
        </div>

        <div class="teacher-list" id="teacherList">
            <?php if (count($filteredTeachers) > 0): ?>
                <?php foreach ($filteredTeachers as $t): ?>
                <div class="teacher-card" data-search-text="<?= htmlspecialchars(mb_strtolower($t['name'] . ' ' . $t['username'] . ' ' . $t['dept'] . ' ' . $t['email'] . ' ' . $t['desc'], 'UTF-8')) ?>">
                    <div class="teacher-left">
                        <div class="teacher-avatar"><?= $t['avatar'] ?></div>
                        <div class="teacher-info">
                            <h4><?= htmlspecialchars($t['name']) ?> <span style="font-size: 12px; color: #888; font-weight: normal;">(@<?= htmlspecialchars($t['username']) ?>)</span></h4>
                            <div class="dept-email"><?= htmlspecialchars($t['dept']) ?> - <?= htmlspecialchars($t['email']) ?></div>
                            <div class="desc"><?= htmlspecialchars($t['desc']) ?></div>
                        </div>
                    </div>
                    <div class="teacher-actions">
                        <button class="btn-view-profile" onclick="openTeacherModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)">Xem & Sửa hồ sơ</button>
                        <button class="btn-delete-teacher" onclick="confirmDelete(<?= $t['id'] ?>, '<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>')"><i class="fa-solid fa-trash-can"></i> Xóa</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: var(--primary-color); background: white; border-radius: 15px; border: 1px solid var(--border-color); font-size: 13px;">
                    Không tìm thấy giảng viên trong hệ thống.
                </div>
            <?php endif; ?>
            <div id="noResultMsg" style="display: none; text-align: center; padding: 30px; color: var(--primary-color); background: white; border-radius: 15px; border: 1px solid var(--border-color); font-size: 13px;">
                Không tìm thấy giảng viên phù hợp.
            </div>
        </div>
    </div>

    <!-- FORM ẨN ĐỂ GỬI YÊU CẦU XÓA -->
    <form id="deleteForm" method="POST" action="gv-admin.php" style="display:none;">
        <input type="hidden" name="action" value="delete_lecturer">
        <input type="hidden" name="lecturer_id" id="deleteLecturerId">
    </form>

    <!-- MODAL (THÊM VÀ SỬA TÀI KHOẢN GIẢNG VIÊN) -->
    <div class="modal-overlay" id="teacherModal">
        <form class="modal-box" method="POST" action="gv-admin.php">
            <input type="hidden" name="action" id="modalAction" value="update_lecturer">
            <input type="hidden" name="lecturer_id" id="modalLecturerId">

            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-avatar" id="modalAvatar">G</div>
                    <div class="modal-title-info">
                        <small id="modalSubTitle" style="font-size: 10px; color: #888; text-transform: uppercase;">Chỉnh sửa hồ sơ giảng viên</small>
                        <h4 id="modalTeacherName">Giảng viên</h4>
                        <p id="modalTeacherSub">Khoa Ngoại Ngữ</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeTeacherModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Họ và tên <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" name="fullname" id="inputName" required placeholder="Nhập họ tên...">
                    </div>
                    <div class="form-group">
                        <label>Tên đăng nhập (Username) <span style="color:red;">*</span></label>
                        <input type="text" class="form-control" name="username" id="inputUsername" required placeholder="Nhập username đăng nhập...">
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

                <div class="form-group full" style="margin-bottom: 15px;">
                    <label>Khoa Ngoại Ngữ</label>
                    <select class="form-control" name="faculty" id="inputDept">
                        <option value="Khoa Tiếng Anh">Khoa Tiếng Anh</option>
                        <option value="Khoa Tiếng Pháp">Khoa Tiếng Pháp</option>
                        <option value="Khoa Tiếng Trung">Khoa Tiếng Trung</option>
                        <option value="Khoa Tiếng Nhật">Khoa Tiếng Nhật</option>
                    </select>
                </div>

                <div class="form-group full" id="passwordGroup" style="display: none; margin-bottom: 15px;">
                    <label>Mật khẩu tài khoản (Mặc định: 123456)</label>
                    <input type="password" class="form-control" name="password" id="inputPassword" placeholder="Để trống nếu dùng mật khẩu mặc định 123456">
                </div>

                <div class="form-group full" style="margin-bottom: 15px;">
                    <label>Chuyên môn / chủ đề tư vấn</label>
                    <input type="text" class="form-control" name="specialty" id="inputDesc" placeholder="Ví dụ: Luyện thi IELTS, Ngữ pháp nâng cao...">
                </div>
                <div class="form-group full">
                    <label>Giới thiệu</label>
                    <textarea class="form-control" name="bio" id="inputIntro" placeholder="Mô tả tóm tắt bản thân..."></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action-outline" onclick="closeTeacherModal()">Hủy</button>
                <button type="submit" class="btn-action-primary" id="btnSubmitModal">Lưu hồ sơ vào Database</button>
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

        // 1. MỞ MODAL THÊM GIẢNG VIÊN
        function openAddModal() {
            document.getElementById('modalAction').value = 'add_lecturer';
            document.getElementById('modalLecturerId').value = '';
            
            document.getElementById('modalAvatar').innerText = '+';
            document.getElementById('modalSubTitle').innerText = 'TẠO TÀI KHOẢN MỚI';
            document.getElementById('modalTeacherName').innerText = 'Thêm giảng viên mới';
            document.getElementById('modalTeacherSub').innerText = 'Nhập thông tin tài khoản để giảng viên đăng nhập';

            document.getElementById('inputName').value = '';
            document.getElementById('inputUsername').value = '';
            document.getElementById('inputEmail').value = '';
            document.getElementById('inputPhone').value = '';
            document.getElementById('inputDept').selectedIndex = 0;
            document.getElementById('inputDesc').value = '';
            document.getElementById('inputIntro').value = '';
            
            document.getElementById('passwordGroup').style.display = 'flex';
            document.getElementById('btnSubmitModal').innerText = 'Tạo mới giảng viên';

            document.getElementById('teacherModal').classList.add('show');
        }

        // 2. MỞ MODAL SỬA GIẢNG VIÊN
        function openTeacherModal(teacher) {
            document.getElementById('modalAction').value = 'update_lecturer';
            document.getElementById('modalLecturerId').value = teacher.id;
            
            document.getElementById('modalAvatar').innerText = teacher.avatar;
            document.getElementById('modalSubTitle').innerText = 'CHỈNH SỬA HỒ SƠ GIẢNG VIÊN';
            document.getElementById('modalTeacherName').innerText = teacher.name;
            document.getElementById('modalTeacherSub').innerText = teacher.dept + ' - Username: ' + teacher.username;
            
            document.getElementById('inputName').value = teacher.name;
            document.getElementById('inputUsername').value = teacher.username;
            document.getElementById('inputEmail').value = teacher.email;
            document.getElementById('inputPhone').value = teacher.phone || '';
            document.getElementById('inputDept').value = teacher.dept;
            document.getElementById('inputDesc').value = teacher.desc;
            document.getElementById('inputIntro').value = teacher.intro;

            document.getElementById('passwordGroup').style.display = 'none';
            document.getElementById('btnSubmitModal').innerText = 'Lưu hồ sơ vào Database';

            document.getElementById('teacherModal').classList.add('show');
        }

        function closeTeacherModal() {
            document.getElementById('teacherModal').classList.remove('show');
        }

        // 3. XÁC NHẬN XÓA
        function confirmDelete(id, name) {
            if (confirm(`Bạn có chắc chắn muốn xóa giảng viên "${name}" khỏi hệ thống không?`)) {
                document.getElementById('deleteLecturerId').value = id;
                document.getElementById('deleteForm').submit();
            }
        }

        // Tự động lọc trực tiếp ngay khi gõ từng ký tự không cần bấm Enter và không load lại trang
        document.getElementById('searchInput').addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.teacher-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const searchText = card.getAttribute('data-search-text') || '';
                if (searchText.includes(keyword)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const noResultMsg = document.getElementById('noResultMsg');
            if (noResultMsg) {
                noResultMsg.style.display = (visibleCount === 0) ? 'block' : 'none';
            }
        });
    </script>
</body>
</html>