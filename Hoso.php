<?php
session_start();
require_once 'db.php';

$user_id = $_SESSION['user']['id'] ?? null;

if (!$user_id) {
    $stmtUser = $pdo->query("SELECT id FROM users WHERE role = 'lecturer' LIMIT 1");
    $defaultUser = $stmtUser->fetch();
    $user_id = $defaultUser['id'] ?? 2;
}

// Giữ đường dẫn trang trước đó vào Session
if (!isset($_SESSION['back_url_lecturer']) || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer && !str_contains($referer, 'Hoso.php')) {
        $_SESSION['back_url_lecturer'] = $referer;
    }
}
$back_url = $_SESSION['back_url_lecturer'] ?? 'Cuochen.php';

$msg = '';

// Cập nhật Hồ sơ Giảng viên
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname  = trim($_POST['name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $faculty   = trim($_POST['faculty'] ?? '');
    $expertise = trim($_POST['expertise'] ?? '');
    $bio       = trim($_POST['bio'] ?? '');

    try {
        $updateUser = $pdo->prepare("UPDATE users SET fullname = :fullname, email = :email, phone = :phone WHERE id = :id");
        $updateUser->execute(['fullname' => $fullname, 'email' => $email, 'phone' => $phone, 'id' => $user_id]);

        $updateProfile = $pdo->prepare("UPDATE lecturer_profiles SET faculty = :faculty, expertise = :expertise, bio = :bio WHERE user_id = :user_id");
        $updateProfile->execute(['faculty' => $faculty, 'expertise' => $expertise, 'bio' => $bio, 'user_id' => $user_id]);

        if (isset($_SESSION['user'])) {
            $_SESSION['user']['fullname'] = $fullname;
        }

        $msg = "Cập nhật hồ sơ thành công!";
    } catch (PDOException $e) {
        $msg = "Lỗi cập nhật: " . $e->getMessage();
    }
}

// Lấy thông tin CSDL
$stmt = $pdo->prepare("
    SELECT u.fullname, u.email, u.phone, p.faculty, p.expertise, p.bio 
    FROM users u 
    LEFT JOIN lecturer_profiles p ON u.id = p.user_id 
    WHERE u.id = :id
");
$stmt->execute(['id' => $user_id]);
$lecturer_profile = $stmt->fetch();

$selected_lecturer = $lecturer_profile['fullname'] ?? 'Giảng viên';

$name_parts = explode(' ', trim($selected_lecturer));
$last_word = end($name_parts);
$avatar_letter = strtoupper(substr($last_word, 0, 1));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ Giảng viên - EDULINGO</title>
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
    </style>
</head>
<body>

    <div class="header">
        <div class="header-brand">
            <div class="logo-box">ABC</div>
            <div class="header-text">
                <h2>EDULINGO</h2>
                <p>Hệ thống đặt lịch tư vấn giảng viên</p>
            </div>
        </div>
        <div class="user-profile-icon">
            <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
            <span class="user-name"><?= htmlspecialchars($selected_lecturer) ?></span>
        </div>
    </div>

    <div class="container">
        <a href="<?= htmlspecialchars($back_url) ?>" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>

        <div class="profile-header">
            <div class="avatar-box"><?= htmlspecialchars($avatar_letter) ?></div>
            <div>
                <div class="profile-title">HỒ SƠ GIẢNG VIÊN</div>
                <div class="profile-name"><?= htmlspecialchars($lecturer_profile['fullname'] ?? '') ?></div>
                <div style="font-size: 12px; color: #888;"><?= htmlspecialchars($lecturer_profile['faculty'] ?? '') ?> · <?= htmlspecialchars($lecturer_profile['email'] ?? '') ?></div>
            </div>
        </div>

        <?php if ($msg): ?>
            <div class="alert-msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Họ và tên đầy đủ</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($lecturer_profile['fullname'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($lecturer_profile['email'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($lecturer_profile['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Khoa / Bộ môn</label>
                        <input type="text" name="faculty" value="<?= htmlspecialchars($lecturer_profile['faculty'] ?? '') ?>">
                    </div>
                    <div class="form-group full-width">
                        <label>Chuyên môn / Chủ đề tư vấn</label>
                        <textarea name="expertise" rows="2"><?= htmlspecialchars($lecturer_profile['expertise'] ?? '') ?></textarea>
                    </div>
                    <div class="form-group full-width">
                        <label>Giới thiệu</label>
                        <textarea name="bio" rows="2"><?= htmlspecialchars($lecturer_profile['bio'] ?? '') ?></textarea>
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