<?php
// Dữ liệu mẫu danh sách cuộc hẹn
$appointments = [
    [
        'student_name' => 'Đỗ Bảo Ngân',
        'email' => 'ngan.do@sv.edu',
        'topic' => 'Sửa bài Writing Task 2',
        'time' => 'T2, 24/08 · 08:00–09:00'
    ],
    [
        'student_name' => 'Phạm Minh Khoa',
        'email' => 'khoa.pham@sv.edu',
        'topic' => 'Chọn lộ trình IELTS',
        'time' => 'T2, 24/08 · 10:00–11:00'
    ],
    [
        'student_name' => 'Trần Thanh Trúc',
        'email' => 'truc.tran@sv.edu',
        'topic' => 'Kiểm tra kỹ năng Listening',
        'time' => 'T4, 26/08 · 13:30–14:30'
    ],
    [
        'student_name' => 'Nguyễn Gia Bảo',
        'email' => 'bao.nguyen@sv.edu',
        'topic' => 'Phát âm, khoá Speaking cơ bản',
        'time' => 'T5, 27/08 · 15:30–16:30'
    ]
];

$selected_lecturer = 'Nguyễn Thảo Vy';

// Lấy chữ cái đầu tiên của tên (dùng hàm thuần PHP tránh lỗi mbstring)
$name_parts = explode(' ', trim($selected_lecturer));
$last_word = end($name_parts);
$avatar_letter = strtoupper(substr($last_word, 0, 1));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuộc hẹn - Giảng viên</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #fce4ec; color: #333; }
        
        /* Header điều chỉnh flex giữa logo/tiêu đề và user icon */
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
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-btn { background: white; border: 1px solid #f48fb1; padding: 8px 18px; border-radius: 20px; color: #ad1457; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background-color: #f8bbd0; font-weight: bold; }

        /* Appointment Cards */
        .appointment-card { background: white; border: 1px solid #f8bbd0; border-radius: 12px; padding: 20px 24px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .info h3 { color: #c2185b; font-size: 18px; margin-bottom: 6px; }
        .info h3 span { color: #888; font-weight: normal; font-size: 14px; margin-left: 5px; }
        .info .topic { color: #f48fb1; font-size: 14px; margin-bottom: 6px; font-weight: 500; }
        .info .time { color: #888; font-size: 13px; }

        /* Actions */
        .actions { display: flex; gap: 12px; }
        .btn-complete { background-color: white; color: #d81b60; border: 1px solid #f48fb1; padding: 8px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: bold; }
        .btn-remind { background-color: #fce4ec; color: #c2185b; border: none; padding: 8px 18px; border-radius: 8px; font-size: 13px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 6px; }

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

        <!-- Biểu tượng Hồ sơ ở góc trên bên phải Header -->
        <a href="Hoso.php" class="user-profile-icon" title="Xem hồ sơ giảng viên">
            <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
            <span class="user-name"><?= htmlspecialchars($selected_lecturer) ?></span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="container">

        <!-- Navigation Tabs -->
        <div class="nav-tabs">
            <a href="Cuochen.php" class="tab-btn active">📋 Cuộc hẹn</a>
            <a href="Khunggio.php" class="tab-btn">🕒 Khung giờ</a>
            <a href="LichTuan.php" class="tab-btn">📅 Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn">👥 Danh sách chờ</a>
            <a href="Hoso.php" class="tab-btn">👤 Hồ sơ</a>
        </div>

        <!-- Appointment List -->
        <?php foreach ($appointments as $item): ?>
            <div class="appointment-card">
                <div class="info">
                    <h3><?= htmlspecialchars($item['student_name']) ?> <span>· <?= htmlspecialchars($item['email']) ?></span></h3>
                    <div class="topic"><?= htmlspecialchars($item['topic']) ?></div>
                    <div class="time"><?= htmlspecialchars($item['time']) ?></div>
                </div>
                <div class="actions">
                    <button class="btn-complete">Hoàn thành</button>
                    <button class="btn-remind">✉ Nhắc lịch</button>
                </div>
            </div>
        <?php endforeach; ?>
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