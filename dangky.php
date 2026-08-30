<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname         = trim($_POST['fullname'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree            = isset($_POST['agree']);

    if (empty($fullname) || empty($phone) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Vui lòng nhập đầy đủ tất cả các trường thông tin!';
    } elseif ($password !== $confirm_password) {
        $error = 'Mật khẩu xác nhận không khớp!';
    } elseif (!$agree) {
        $error = 'Bạn phải đồng ý với Điều khoản sử dụng và Chính sách bảo mật!';
    } else {
        // Lưu vào Session để các trang khác (như hồ sơ, trang chủ) có thể đọc được
        $_SESSION['user'] = [
            'fullname' => $fullname,
            'username' => $username,
            'email'    => $email,
            'phone'    => $phone,
            'role'     => 'student'
        ];

        // Đăng ký thành công, chuyển hướng sang trang hồ sơ hoặc trang đăng nhập
        header('Location: dangnhap.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fff5f8;
            --border-color: #f0cfd9;
            --text-muted: #777;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        html, body { height: 100vh; overflow: hidden; background-color: #fdf2f5; display: flex; flex-direction: column; }
        .header { background-color: var(--primary-color); color: white; padding: 12px 30px; display: flex; align-items: center; flex-shrink: 0; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        .main-wrapper { flex: 1; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 15px; }
        .content-box { width: 100%; max-width: 680px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: bold; font-size: 14px; margin-bottom: 12px; }
        .auth-card { background: white; border-radius: 16px; padding: 28px 45px; box-shadow: 0 10px 30px rgba(216, 27, 96, 0.08); }
        .tabs { display: flex; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; }
        .tab { flex: 1; text-align: center; padding-bottom: 10px; font-size: 17px; font-weight: bold; color: var(--text-muted); text-decoration: none; position: relative; }
        .tab.active { color: var(--primary-color); }
        .tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: var(--primary-color); }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px 20px; }
        .full-width { grid-column: span 2; }
        .form-group { display: flex; flex-direction: column; }
        .form-label { font-size: 13px; font-weight: bold; color: var(--primary-color); margin-bottom: 5px; }
        .input-box { position: relative; display: flex; align-items: center; }
        .input-box i.input-icon { position: absolute; left: 16px; color: var(--text-muted); font-size: 14px; }
        .input-box i.toggle-pwd { position: absolute; right: 16px; color: var(--text-muted); cursor: pointer; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 42px; border: 1px solid var(--border-color); border-radius: 25px; font-size: 13px; outline: none; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(216, 27, 96, 0.1); }
        .agree-option { font-size: 12px; color: var(--text-muted); margin: 16px 0 18px 0; display: flex; align-items: center; gap: 6px; }
        .agree-option a { color: var(--primary-color); text-decoration: underline; font-weight: bold; }
        .btn-submit { width: 100%; background-color: var(--primary-color); color: white; border: none; padding: 11px; border-radius: 25px; font-size: 15px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background-color: #b0124a; }
        .msg-box { font-size: 13px; margin-bottom: 12px; text-align: center; padding: 7px; border-radius: 6px; }
        .error-msg { color: #d32f2f; background-color: #ffebee; }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <div class="logo-box">ABC</div>
            <div>
                <strong style="font-size: 15px;">EDULINGO</strong><br>
                <small style="font-size: 10px; opacity: 0.8;">Hệ thống đặt lịch tư vấn giảng viên</small>
            </div>
        </div>
    </header>
    <div class="main-wrapper">
        <div class="content-box">
            <a href="trangchu.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ</a>
            <div class="auth-card">
                <div class="tabs">
                    <a href="dangnhap.php" class="tab">Đăng nhập</a>
                    <a href="dangky.php" class="tab active">Đăng ký</a>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="msg-box error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Họ và tên</label>
                            <div class="input-box">
                                <i class="fa-regular fa-user input-icon"></i>
                                <input type="text" name="fullname" class="form-control" placeholder="Họ và tên của bạn" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Số điện thoại</label>
                            <div class="input-box">
                                <i class="fa-solid fa-phone input-icon"></i>
                                <input type="tel" name="phone" class="form-control" placeholder="Nhập số điện thoại" required>
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Tên đăng nhập</label>
                            <div class="input-box">
                                <i class="fa-solid fa-at input-icon"></i>
                                <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập..." required>
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Email</label>
                            <div class="input-box">
                                <i class="fa-regular fa-envelope input-icon"></i>
                                <input type="email" name="email" class="form-control" placeholder="Nhập email của bạn..." required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mật khẩu</label>
                            <div class="input-box">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                                <i class="fa-regular fa-eye-slash toggle-pwd" onclick="togglePassword('password', this)"></i>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Xác nhận mật khẩu</label>
                            <div class="input-box">
                                <i class="fa-solid fa-lock input-icon"></i>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Xác nhận mật khẩu" required>
                                <i class="fa-regular fa-eye-slash toggle-pwd" onclick="togglePassword('confirm_password', this)"></i>
                            </div>
                        </div>
                    </div>
                    <div class="agree-option">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">Tôi đồng ý với <a href="#">Điều khoản sử dụng</a> và <a href="#">Chính sách bảo mật</a></label>
                    </div>
                    <button type="submit" class="btn-submit">Đăng ký tài khoản</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
            }
        }
    </script>
</body>
</html>