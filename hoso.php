<?php
session_start();

// Kiểm tra nếu chưa đăng nhập thì chuyển hướng về trang đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: dangnhap.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

// --- XỬ LÝ LƯU TRANG TRƯỚC ĐÓ ---
// Chỉ lưu nếu có HTTP_REFERER gửi lên và trang đó KHÁC hoso.php (tránh bị kẹt khi submit form)
if (isset($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    if (strpos($referer, 'hoso.php') === false) {
        $_SESSION['previous_page'] = $referer;
    }
}
// Nếu chưa có lịch sử nào thì mặc định về trangchu.php
$back_url = $_SESSION['previous_page'] ?? 'trangchu.php';
// ---------------------------------

// Lấy thông tin người dùng từ Session
$user_fullname = $_SESSION['user']['fullname'] ?? 'Nguyễn Văn A';
$user_username = $_SESSION['user']['username'] ?? 'nguyenvana';
$user_email    = $_SESSION['user']['email'] ?? 'nguyenvana@sv.edu.vn';
$user_phone    = $_SESSION['user']['phone'] ?? '0987654321';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $user_fullname = trim($_POST['fullname'] ?? $user_fullname);
        $user_phone    = trim($_POST['phone'] ?? $user_phone);
        
        $_SESSION['user']['fullname'] = $user_fullname;
        $_SESSION['user']['phone']    = $user_phone;
        
        $message = 'Cập nhật thông tin cá nhân thành công!';
    } elseif (isset($_POST['change_password'])) {
        $message = 'Đổi mật khẩu thành công!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ Sơ Cá Nhân - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #d81b60; --primary-light: #fdf2f5; --border-color: #fce4ec; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        html, body { height: 100vh; overflow: hidden; background-color: var(--primary-light); display: flex; flex-direction: column; }
        .header { background-color: var(--primary-color); color: white; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        .main-container { max-width: 750px; width: 100%; margin: auto; padding: 15px; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; padding: 8px 14px; border-radius: 8px; margin-bottom: 12px; font-size: 13px; border: 1px solid #c8e6c9; }
        .profile-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px 28px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .section-title { color: var(--primary-color); font-size: 15px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 8px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: block; font-size: 12px; color: #555; margin-bottom: 3px; font-weight: bold; }
        .form-control { width: 100%; padding: 7px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; outline: none; }
        .form-control:focus { border-color: var(--primary-color); }
        .form-control[readonly] { background-color: #f9f9f9; color: #777; cursor: not-allowed; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-submit { background-color: var(--primary-color); color: white; border: none; padding: 8px 20px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; margin-top: 4px; }
        .back-home { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 15px; color: var(--primary-color); text-decoration: none; font-size: 13px; font-weight: bold; }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <div class="logo-box">ABC</div>
            <div>
                <strong style="font-size: 15px;">EDULINGO</strong><br>
                <small style="font-size: 10px; opacity: 0.85;">Hệ thống đặt lịch tư vấn giảng viên</small>
            </div>
        </div>
    </header>
    <div class="main-container">
        <a href="<?= htmlspecialchars($back_url) ?>" class="back-home">
            <i class="fa-solid fa-arrow-left"></i> Quay lại trang trước
        </a>
        <?php if (!empty($message)): ?>
            <div class="alert-success"><i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <div class="profile-card">
            <form action="hoso.php" method="POST" style="margin-bottom: 15px;">
                <div class="section-title"><i class="fa-solid fa-user-pen"></i> Thông tin cá nhân</div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Họ và tên</label>
                        <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user_fullname) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user_phone) ?>" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Tên đăng nhập</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($user_username) ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Email đăng ký</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($user_email) ?>" readonly>
                    </div>
                </div>
                <button type="submit" name="update_profile" class="btn-submit">Lưu thay đổi</button>
            </form>
            <form action="hoso.php" method="POST">
                <div class="section-title"><i class="fa-solid fa-key"></i> Đổi mật khẩu</div>
                <div class="form-group">
                    <label>Mật khẩu hiện tại</label>
                    <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" class="form-control" placeholder="••••••••" required>
                    </div>
                    <div class="form-group">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" name="change_password" class="btn-submit">Đổi mật khẩu</button>
            </form>
        </div>
    </div>
</body>
</html>