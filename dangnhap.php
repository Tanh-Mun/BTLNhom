<?php
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!empty($username) && !empty($password)) {
        
        // 1. Kiểm tra tài khoản Admin
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['user'] = [
                'fullname' => 'Quản Trị Viên',
                'username' => 'admin',
                'email'    => 'admin@edulingo.edu.vn',
                'role'     => 'admin'
            ];
            header('Location: gv-admin.php');
            exit;
        } 
        // 2. Kiểm tra tài khoản Giảng viên (Demo)
        elseif ($username === 'giangvien' || stripos($username, 'lecturer') !== false) {
            $_SESSION['user'] = [
                'fullname' => 'Nguyễn Thảo Vy',
                'username' => $username,
                'email'    => 'vy.nguyen@lingua.edu',
                'role'     => 'lecturer'
            ];
            header('Location: Cuochen.php');
            exit;
        } 
        // 3. Mặc định là tài khoản Sinh viên
        else {
            $_SESSION['user'] = [
                'fullname' => $_SESSION['user']['fullname'] ?? $username,
                'username' => $username,
                'email'    => $_SESSION['user']['email'] ?? ($username . '@sv.edu.vn'),
                'phone'    => $_SESSION['user']['phone'] ?? '0987654321',
                'role'     => 'student'
            ];
            header('Location: trangchu.php');
            exit;
        }

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
        .content-box { width: 100%; max-width: 480px; }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--primary-color); text-decoration: none; font-weight: bold; font-size: 14px; margin-bottom: 12px; }
        .auth-card { background: white; border-radius: 16px; padding: 28px 35px; box-shadow: 0 10px 30px rgba(216, 27, 96, 0.08); }
        
        .form-header { text-align: center; margin-bottom: 20px; }
        .form-header h3 { color: var(--primary-color); font-size: 20px; margin-bottom: 5px; }
        .form-header p { color: var(--text-muted); font-size: 13px; }
        
        .error-msg { background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; margin-bottom: 15px; font-size: 13px; text-align: center; }
        
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; color: var(--primary-color); font-size: 13px; font-weight: bold; margin-bottom: 6px; }
        
        .input-box { position: relative; display: flex; align-items: center; }
        .input-icon { position: absolute; left: 14px; color: var(--primary-color); font-size: 14px; }
        .form-control { width: 100%; padding: 10px 40px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 14px; background: #fff; }
        .form-control:focus { border-color: var(--primary-color); }
        .toggle-pwd { position: absolute; right: 14px; color: var(--primary-color); cursor: pointer; font-size: 14px; }
        
        .form-options { display: flex; justify-content: space-between; align-items: center; font-size: 12px; margin-bottom: 20px; color: var(--primary-color); }
        .remember-me { display: flex; align-items: center; gap: 6px; cursor: pointer; }
        .forgot-link { color: var(--primary-color); text-decoration: none; font-weight: bold; }
        
        .btn-submit { width: 100%; background: var(--primary-color); color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 15px; cursor: pointer; transition: 0.2s; }
        .btn-submit:hover { opacity: 0.9; }

        .demo-accounts { margin-top: 20px; padding-top: 15px; border-top: 1px dashed var(--border-color); font-size: 12px; color: #666; }
        .demo-accounts strong { color: var(--primary-color); }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo-container">
            <div class="logo-box">ABC</div>
            <strong>EDULINGO</strong>
        </div>
    </div>

    <div class="main-wrapper">
        <div class="content-box">
            <a href="dangky.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Tạo tài khoản mới</a>
            
            <div class="auth-card">
                <div class="form-header">
                    <h3>Đăng nhập Hệ thống</h3>
                    <p>Nhập thông tin đăng nhập để tiếp tục</p>
                </div>

                <?php if ($error): ?>
                    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="dangnhap.php">
                    <div class="form-group">
                        <label class="form-label">Tên đăng nhập hoặc Email</label>
                        <div class="input-box">
                            <i class="fa-solid fa-user input-icon"></i>
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

                <div class="demo-accounts">
                    <p><strong>Thông tin tài khoản thử nghiệm:</strong></p>
                    <p>• Admin: <code>admin</code> / <code>admin123</code> (Vào quản trị)</p>
                    <p>• Giảng viên: <code>giangvien</code> / <code>123456</code> (Vào hồ sơ)</p>
                    <p>• Sinh viên: Nhập tên bất kỳ</p>
                </div>
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