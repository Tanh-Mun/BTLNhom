<?php
session_start();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        // Giữ lại thông tin cũ nếu đã có (từ lúc đăng ký), nếu chưa thì tạo mặc định
        $_SESSION['user'] = [
            'fullname' => $_SESSION['user']['fullname'] ?? $username,
            'username' => $username,
            'email'    => $_SESSION['user']['email'] ?? ($username . '@sv.edu.vn'),
            'phone'    => $_SESSION['user']['phone'] ?? '0987654321',
            'role'     => 'student'
        ];

        header('Location: timvadatlich.php');
        exit;
    } else {
        $error = 'Vui lòng nhập đầy đủ thông tin!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - EDULINGO</title>
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
        .content-box { width: 100%; max-width: 560px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: bold; font-size: 14px; margin-bottom: 12px; }
        .auth-card { background: white; border-radius: 16px; padding: 30px 45px 35px 45px; box-shadow: 0 10px 30px rgba(216, 27, 96, 0.08); }
        .tabs { display: flex; border-bottom: 2px solid #f0f0f0; margin-bottom: 20px; }
        .tab { flex: 1; text-align: center; padding-bottom: 10px; font-size: 17px; font-weight: bold; color: var(--text-muted); text-decoration: none; position: relative; }
        .tab.active { color: var(--primary-color); }
        .tab.active::after { content: ''; position: absolute; bottom: -2px; left: 0; width: 100%; height: 3px; background-color: var(--primary-color); }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13px; font-weight: bold; color: var(--primary-color); margin-bottom: 6px; }
        .input-box { position: relative; display: flex; align-items: center; }
        .input-box i.input-icon { position: absolute; left: 16px; color: var(--text-muted); font-size: 15px; }
        .input-box i.toggle-pwd { position: absolute; right: 16px; color: var(--text-muted); cursor: pointer; font-size: 15px; }
        .form-control { width: 100%; padding: 11px 45px; border: 1px solid var(--border-color); border-radius: 25px; font-size: 14px; outline: none; }
        .form-control:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(216, 27, 96, 0.1); }
        .form-options { display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 18px; color: var(--text-muted); }
        .remember-me { display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .forgot-link { color: var(--primary-color); text-decoration: none; font-weight: bold; }
        .btn-submit { width: 100%; background-color: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 25px; font-size: 15px; font-weight: bold; cursor: pointer; }
        .btn-submit:hover { background-color: #b0124a; }
        .error-msg { color: #d32f2f; font-size: 13px; margin-bottom: 12px; text-align: center; }
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
                    <a href="dangnhap.php" class="tab active">Đăng nhập</a>
                    <a href="dangky.php" class="tab">Đăng ký</a>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="error-msg"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Tên đăng nhập</label>
                        <div class="input-box">
                            <i class="fa-regular fa-user input-icon"></i>
                            <input type="text" name="username" class="form-control" placeholder="Nhập tên đăng nhập..." required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mật khẩu</label>
                        <div class="input-box">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input type="password" id="passwordInput" name="password" class="form-control" placeholder="Nhập mật khẩu..." required>
                            <i class="fa-regular fa-eye-slash toggle-pwd" onclick="togglePassword()"></i>
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="remember-me"><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>
                        <a href="#" class="forgot-link">Quên mật khẩu?</a>
                    </div>
                    <button type="submit" class="btn-submit">Đăng nhập</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.querySelector('.toggle-pwd');
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