<?php
session_start();
require_once 'db.php';

// Kiểm tra phiên đăng nhập
if (!isset($_SESSION['user']['id'])) {
    header('Location: dangnhap.php');
    exit;
}

$user_id = $_SESSION['user']['id'];

// Giữ đường dẫn trang trước đó vào Session
if (!isset($_SESSION['back_url_student']) || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer && !str_contains($referer, 'Hososv.php')) {
        $_SESSION['back_url_student'] = $referer;
    }
}
$back_url = $_SESSION['back_url_student'] ?? 'trangchu1.php';

$msg = '';
$error = '';

// Cập nhật Hồ sơ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $faculty  = trim($_POST['faculty'] ?? '');
    $major    = trim($_POST['major'] ?? '');
    $bio      = trim($_POST['bio'] ?? '');

    try {
        $pdo->beginTransaction();

        // 1. Cập nhật bảng users
        $updateUser = $pdo->prepare("UPDATE users SET fullname = :fullname, email = :email, phone = :phone WHERE id = :id");
        $updateUser->execute([
            'fullname' => $fullname,
            'email'    => $email,
            'phone'    => $phone,
            'id'       => $user_id
        ]);

        // 2. Kiểm tra xem user_id đã có trong student_profiles chưa
        $checkProfile = $pdo->prepare("SELECT id FROM student_profiles WHERE user_id = :user_id");
        $checkProfile->execute(['user_id' => $user_id]);

        if ($checkProfile->fetch()) {
            // Đã có -> UPDATE
            $updateProfile = $pdo->prepare("UPDATE student_profiles SET faculty = :faculty, major = :major, bio = :bio WHERE user_id = :user_id");
            $updateProfile->execute([
                'faculty' => $faculty,
                'major'   => $major,
                'bio'     => $bio,
                'user_id' => $user_id
            ]);
        } else {
            // Chưa có -> INSERT mới
            $getUserInfo = $pdo->prepare("SELECT username FROM users WHERE id = :id");
            $getUserInfo->execute(['id' => $user_id]);
            $uData = $getUserInfo->fetch();
            $stCode = $uData['username'] ?? ('SV' . str_pad($user_id, 3, '0', STR_PAD_LEFT));

            $insertProfile = $pdo->prepare("INSERT INTO student_profiles (user_id, student_code, faculty, major, bio) VALUES (:user_id, :student_code, :faculty, :major, :bio)");
            $insertProfile->execute([
                'user_id'      => $user_id,
                'student_code' => $stCode,
                'faculty'      => $faculty,
                'major'        => $major,
                'bio'          => $bio
            ]);
        }

        $pdo->commit();

        // Cập nhật lại Session
        $_SESSION['user']['fullname'] = $fullname;
        $_SESSION['user_name'] = $fullname;

        $msg = "Cập nhật hồ sơ thành công!";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// Truy vấn thông tin CSDL
$stmt = $pdo->prepare("
    SELECT u.username, u.fullname, u.email, u.phone, p.student_code, p.faculty, p.major, p.bio 
    FROM users u 
    LEFT JOIN student_profiles p ON u.id = p.user_id 
    WHERE u.id = :id
");
$stmt->execute(['id' => $user_id]);
$student_profile = $stmt->fetch();

$display_code = !empty($student_profile['student_code']) ? $student_profile['student_code'] : ($student_profile['username'] ?? '');
$selected_student = !empty($student_profile['fullname']) ? trim($student_profile['fullname']) : 'Học viên';

// Tách chữ cái đại diện Avatar (Lấy chữ cái đầu tiên của TÊN - từ cuối cùng)
$name_parts = explode(' ', $selected_student);
$first_name = end($name_parts);
$avatar_letter = mb_strtoupper(mb_substr($first_name, 0, 1, 'UTF-8'), 'UTF-8');
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ Học viên - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #d81b60; --primary-light: #fdf2f5; --border-color: #fce4ec; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--primary-light); color: #333; }
        .header { background-color: var(--primary-color); color: white; padding: 15px 40px; display: flex; align-items: center; justify-content: space-between; }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .header-text h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .header-text p { font-size: 13px; opacity: 0.9; }
        .user-profile-icon { display: flex; align-items: center; gap: 10px; color: white; background-color: rgba(255, 255, 255, 0.15); padding: 6px 14px 6px 8px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.3); }
        .avatar-circle { width: 34px; height: 34px; background-color: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }
        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; background: white; border: 1px solid #f48fb1; color: var(--primary-color); padding: 8px 16px; border-radius: 20px; font-size: 13px; font-weight: 600; text-decoration: none; }
        .profile-header { display: flex; align-items: center; gap: 15px; margin: 20px 0; background: white; padding: 20px; border-radius: 12px; border: 1px solid #f8bbd0; }
        .avatar-box { width: 50px; height: 50px; background-color: var(--primary-light); border: 1px solid #f48fb1; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--primary-color); font-weight: bold; font-size: 22px; }
        .profile-title { font-size: 11px; text-transform: uppercase; color: #888; }
        .profile-name { font-size: 18px; color: var(--primary-color); font-weight: bold; }
        .form-container { background: white; padding: 25px; border-radius: 12px; border: 1px solid #f8bbd0; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full-width { grid-column: span 2; }
        label { font-size: 13px; color: var(--primary-color); font-weight: bold; }
        input, textarea { width: 100%; border: 1px solid #f48fb1; border-radius: 10px; padding: 12px 16px; font-size: 14px; outline: none; }
        .btn-save { background-color: var(--primary-color); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; }
        .alert-msg { background: #e8f5e9; color: #2e7d32; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
        .error-msg { background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 14px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-brand">
            <div class="logo-box">ABC</div>
            <div class="header-text">
                <h2>EDULINGO</h2>
                <p>Hệ thống đặt lịch tư vấn học viên</p>
            </div>
        </div>
        <div class="user-profile-icon">
            <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
            <span class="user-name"><?= htmlspecialchars($selected_student) ?></span>
        </div>
    </div>

    <div class="container">
        <a href="<?= htmlspecialchars($back_url) ?>" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>

        <div class="profile-header">
            <div class="avatar-box"><?= htmlspecialchars($avatar_letter) ?></div>
            <div>
                <div class="profile-title">HỒ SƠ HỌC VIÊN</div>
                <div class="profile-name"><?= htmlspecialchars($student_profile['fullname'] ?? '') ?> (MSHV: <?= htmlspecialchars($display_code) ?>)</div>
                <div style="font-size: 12px; color: #888;"><?= htmlspecialchars($student_profile['email'] ?? '') ?> · <?= htmlspecialchars($student_profile['phone'] ?? '') ?></div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="alert-msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error-msg"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Mã số học viên (Cố định)</label>
                        <input type="text" value="<?= htmlspecialchars($display_code) ?>" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
                    </div>
                    <div class="form-group">
                        <label>Họ và tên đầy đủ</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($student_profile['fullname'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($student_profile['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($student_profile['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Khóa học / Khoa</label>
                        <input type="text" name="faculty" value="<?= htmlspecialchars($student_profile['faculty'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Chuyên ngành / Khóa quan tâm</label>
                        <input type="text" name="major" value="<?= htmlspecialchars($student_profile['major'] ?? '') ?>">
                    </div>
                    <div class="form-group full-width">
                        <label>Mục tiêu học tập / Ghi chú</label>
                        <textarea name="bio" rows="2"><?= htmlspecialchars($student_profile['bio'] ?? '') ?></textarea>
                    </div>
                </div>
                <div style="text-align: right;">
                    <button type="submit" class="btn-save">Lưu hồ sơ</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>