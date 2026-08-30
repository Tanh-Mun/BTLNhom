<?php
// Dữ liệu mảng tuần từ T2 (24/8) đến CN (30/8)
$week_days = [
    [
        'day_label' => 'T2, 24/8',
        'slots' => [
            ['time' => '8:00 - 9:00', 'booked' => '1/1'],
            ['time' => '10:00 - 11:00', 'booked' => '1/1']
        ]
    ],
    [
        'day_label' => 'T3, 25/8',
        'slots' => []
    ],
    [
        'day_label' => 'T4, 26/8',
        'slots' => [
            ['time' => '13:30 - 14:30', 'booked' => '1/2']
        ]
    ],
    [
        'day_label' => 'T5, 27/8',
        'slots' => [
            ['time' => '15:30 - 16:30', 'booked' => '0/1']
        ]
    ],
    [
        'day_label' => 'T6, 28/8',
        'slots' => []
    ],
    [
        'day_label' => 'T7, 29/8',
        'slots' => []
    ],
    [
        'day_label' => 'CN, 30/8',
        'slots' => []
    ]
];

$selected_lecturer = 'Nguyễn Thảo Vy';

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
    <title>Lịch tuần - Giảng viên</title>
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
        .container { max-width: 1100px; margin: 25px auto; padding: 0 20px; }

        /* Navigation Tabs */
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-btn { background: white; border: 1px solid #f48fb1; padding: 8px 18px; border-radius: 20px; color: #ad1457; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background-color: #f8bbd0; font-weight: bold; }

        /* Date Switcher Bar */
        .date-switcher { display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 30px; }
        .btn-arrow { background: white; border: 1px solid #f48fb1; color: #d81b60; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; text-decoration: none; font-size: 16px; }
        .date-range-btn { background: white; border: 1px solid #f48fb1; color: #d81b60; padding: 8px 20px; border-radius: 10px; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 8px; }

        /* Weekly Columns Container */
        .week-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 12px; margin-bottom: 40px; }
        
        /* Day Cards */
        .day-card { background: white; border: 1px solid #f8bbd0; border-radius: 12px; padding: 12px 10px; min-height: 280px; display: flex; flex-direction: column; gap: 10px; }
        .day-header { color: #d81b60; font-weight: bold; font-size: 13px; text-align: left; margin-bottom: 5px; padding-left: 2px; }

        /* Slot Boxes Inside Card */
        .slot-item { border: 1px solid #f8bbd0; border-radius: 8px; padding: 8px 6px; text-align: center; }
        .slot-item .time { color: #d81b60; font-weight: bold; font-size: 12px; margin-bottom: 3px; }
        .slot-item .booked { color: #888; font-size: 11px; }

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
            <a href="LichTuan.php" class="tab-btn active">📅 Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn">👥 Danh sách chờ</a>
            <a href="Hoso.php" class="tab-btn">👤 Hồ sơ</a>
        </div>

        <!-- Date Range Switcher -->
        <div class="date-switcher">
            <a href="#" class="btn-arrow">&lt;</a>
            <div class="date-range-btn">
                T2, 24/8 - CN, 30/8 📅
            </div>
            <a href="#" class="btn-arrow">&gt;</a>
        </div>

        <!-- Week Grid -->
        <div class="week-grid">
            <?php foreach ($week_days as $day): ?>
                <div class="day-card">
                    <div class="day-header"><?= htmlspecialchars($day['day_label']) ?></div>
                    <?php foreach ($day['slots'] as $slot): ?>
                        <div class="slot-item">
                            <div class="time"><?= htmlspecialchars($slot['time']) ?></div>
                            <div class="booked"><?= htmlspecialchars($slot['booked']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
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