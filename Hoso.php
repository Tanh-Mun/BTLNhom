<?php
// Dữ liệu mẫu thông tin giảng viên
$lecturer_profile = [
    'name' => 'Nguyễn Thảo Vy',
    'email' => 'vy.nguyen@lingua.edu',
    'phone' => '0901234567',
    'faculty' => 'Khoa Tiếng Anh',
    'expertise' => 'Giao tiếp học thuật, IELTS Speaking, phát âm và thuyết trình.',
    'bio' => 'Giảng viên tiếng Anh, phụ trách tư vấn học thuật và kỹ năng giao tiếp.'
];

$selected_lecturer = $lecturer_profile['name'];

// Lấy chữ cái đầu tiên của tên (dùng hàm thuần PHP)
$name_parts = explode(' ', trim($selected_lecturer));
$last_word = end($name_parts);
$avatar_letter = strtoupper(substr($last_word, 0, 1));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - Giảng viên</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #fce4ec; color: #333; }
        
        /* Header */
        .header { 
            background-color: #d81b60; 
            color: white; 
            padding: 15px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }
        .header-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logo-box { background: white; color: #d81b60; font-weight: bold; padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .header-text h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .header-text p { font-size: 13px; opacity: 0.9; }

        /* Biểu tượng hồ sơ cá nhân trên Header */
        .user-profile-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            padding: 6px 14px 6px 8px;
            border-radius: 25px;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .user-profile-icon:hover {
            background-color: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        .avatar-circle {
            width: 34px;
            height: 34px;
            background-color: white;
            color: #d81b60;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 15px;
        }
        .user-name {
            font-size: 14px;
            font-weight: 600;
        }

        /* Main Container */
        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; }

        /* Navigation Tabs */
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 30px; }
        .tab-btn { background: white; border: 1px solid #f48fb1; padding: 8px 18px; border-radius: 20px; color: #ad1457; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background-color: #f8bbd0; font-weight: bold; }

        /* Profile Header Info */
        .profile-header { display: flex; align-items: center; gap: 15px; margin-bottom: 25px; }
        .avatar-box { width: 50px; height: 50px; background-color: white; border: 1px solid #f48fb1; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #d81b60; font-weight: bold; font-size: 22px; }
        .profile-title { font-size: 11px; text-transform: uppercase; color: #888; letter-spacing: 0.5px; margin-bottom: 2px; }
        .profile-name { font-size: 18px; color: #d81b60; font-weight: bold; }
        .profile-sub { font-size: 12px; color: #888; }

        /* Form Styling */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full-width { grid-column: span 2; }
        
        label { font-size: 13px; color: #d81b60; font-weight: bold; }
        input, textarea { width: 100%; border: 1px solid #f48fb1; border-radius: 10px; padding: 12px 16px; font-size: 14px; color: #d81b60; outline: none; background: white; }
        textarea { resize: vertical; min-height: 50px; }

        /* Form Actions */
        .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px; }
        .btn-edit { background: white; border: 1px solid #f48fb1; color: #d81b60; padding: 8px 20px; border-radius: 8px; font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 5px; }
        .btn-save { background-color: #d81b60; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-size: 13px; font-weight: bold; cursor: pointer; }

        /* Footer */
        .footer { background-color: #d81b60; color: white; padding: 40px; margin-top: 50px; display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 30px; }
        .footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        .footer-logo .logo-box { color: #d81b60; }
        .footer p { font-size: 13px; line-height: 1.5; opacity: 0.9; }
        .footer h4 { font-size: 13px; font-style: italic; margin-bottom: 15px; text-transform: uppercase; }
        .footer ul { list-style: none; }
        .footer ul li { margin-bottom: 10px; font-size: 13px; opacity: 0.9; cursor: pointer; }
        .social-icons { display: flex; gap: 10px; margin-top: 15px; }
        .social-icon { width: 30px; height: 30px; background: white; color: #d81b60; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 12px; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="header-brand">
            <div class="logo-box">ABC</div>
            <div class="header-text">
                <h2>EDULINGO</h2>
                <p>Hệ thống đặt lịch tư vấn giảng viên</p>
            </div>
        </div>

        <a href="Hoso.php" class="user-profile-icon" title="Xem hồ sơ giảng viên">
            <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
            <span class="user-name"><?= htmlspecialchars($selected_lecturer) ?></span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="container">
        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <a href="Cuochen.php" class="tab-btn">📋 Cuộc hẹn</a>
            <a href="Khunggio.php" class="tab-btn">🕒 Khung giờ</a>
            <a href="LichTuan.php" class="tab-btn">📅 Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn">👥 Danh sách chờ</a>
            <a href="Hoso.php" class="tab-btn active">👤 Hồ sơ</a>
        </div>

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar-box"><?= htmlspecialchars($avatar_letter) ?></div>
            <div>
                <div class="profile-title">HỒ SƠ GIẢNG VIÊN</div>
                <div class="profile-name"><?= htmlspecialchars($lecturer_profile['name']) ?></div>
                <div class="profile-sub"><?= htmlspecialchars($lecturer_profile['faculty']) ?> · <?= htmlspecialchars($lecturer_profile['email']) ?></div>
            </div>
        </div>

        <!-- Profile Form -->
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($lecturer_profile['name']) ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($lecturer_profile['email']) ?>">
                </div>
                <div class="form-group">
                    <label>Số điện thoại</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($lecturer_profile['phone']) ?>">
                </div>
                <div class="form-group">
                    <label>Khoa Ngoại Ngữ</label>
                    <input type="text" name="faculty" value="<?= htmlspecialchars($lecturer_profile['faculty']) ?>">
                </div>
                <div class="form-group full-width">
                    <label>Chuyên môn / chủ đề tư vấn</label>
                    <textarea name="expertise" rows="2"><?= htmlspecialchars($lecturer_profile['expertise']) ?></textarea>
                </div>
                <div class="form-group full-width">
                    <label>Giới thiệu</label>
                    <textarea name="bio" rows="2"><?= htmlspecialchars($lecturer_profile['bio']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-edit">✏ Sửa</button>
                <button type="submit" class="btn-save">Lưu hồ sơ</button>
            </div>
        </form>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>
            <div class="footer-logo">
                <div class="logo-box">ABC</div>
                <strong>EDULINGO</strong>
            </div>
            <p>Hệ thống đặt lịch tư vấn ngoại ngữ, giúp sinh viên tìm và đặt buổi gặp chỉ trong vài bước</p>
            <div class="social-icons">
                <div class="social-icon">FB</div>
                <div class="social-icon">ZL</div>
                <div class="social-icon">✉</div>
            </div>
        </div>
        <div>
            <h4>KHÁM PHÁ</h4>
            <ul>
                <li>Tìm giảng viên</li>
                <li>Đánh giá</li>
                <li>Ngôn ngữ hỗ trợ</li>
                <li>Câu hỏi thường gặp</li>
            </ul>
        </div>
        <div>
            <h4>DÀNH CHO GIẢNG VIÊN</h4>
            <ul>
                <li>Đăng ký giảng dạy</li>
                <li>Quản lý khung giờ</li>
            </ul>
        </div>
        <div>
            <h4>NHẬN THÔNG BÁO</h4>
            <p>Nhận tin khi có giảng viên mới hoặc khung giờ mới mở</p>
        </div>
    </div>

</body>
</html>