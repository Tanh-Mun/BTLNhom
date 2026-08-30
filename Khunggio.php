<?php
// Dữ liệu mẫu danh sách khung giờ
$time_slots = [
    [
        'time' => 'T2, 24/08 · 08:00–09:00',
        'type' => 'online',
        'location' => 'Trực tuyến',
        'booked' => 1,
        'total' => 1,
        'status_color' => '#d81b60'
    ],
    [
        'time' => 'T2, 24/08 · 10:00–11:00',
        'type' => 'online',
        'location' => 'Trực tuyến',
        'booked' => 1,
        'total' => 1,
        'status_color' => '#d81b60'
    ],
    [
        'time' => 'T4, 26/08 · 13:30–14:30',
        'type' => 'offline',
        'location' => 'Phòng A204',
        'booked' => 1,
        'total' => 2,
        'status_color' => '#2e7d32'
    ],
    [
        'time' => 'T5, 27/08 · 15:30–16:30',
        'type' => 'offline',
        'location' => 'Phòng B301',
        'booked' => 0,
        'total' => 1,
        'status_color' => '#2e7d32'
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
    <title>Khung giờ - Giảng viên</title>
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
        .nav-tabs { display: flex; gap: 10px; margin-bottom: 25px; }
        .tab-btn { background: white; border: 1px solid #f48fb1; padding: 8px 18px; border-radius: 20px; color: #ad1457; text-decoration: none; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        .tab-btn.active { background-color: #f8bbd0; font-weight: bold; }

        /* Add Button */
        .action-bar { display: flex; justify-content: flex-end; margin-bottom: 15px; }
        .btn-add { background-color: #d81b60; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 14px; }

        /* Time Slot Cards */
        .slot-card { background: white; border: 1px solid #f8bbd0; border-radius: 12px; padding: 18px 24px; margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; }
        .slot-info { display: flex; align-items: center; gap: 30px; }
        .slot-time { color: #d81b60; font-weight: bold; font-size: 15px; width: 200px; }
        .slot-location { color: #f48fb1; font-size: 14px; display: flex; align-items: center; gap: 6px; width: 140px; }
        .slot-location.offline { color: #ab47bc; }
        .slot-booked { font-weight: bold; font-size: 14px; }

        /* Action Buttons */
        .slot-actions { display: flex; gap: 10px; }
        .btn-edit { border: 1px solid #f48fb1; background: white; color: #d81b60; padding: 6px 16px; border-radius: 6px; font-size: 13px; cursor: pointer; }
        .btn-delete { border: 1px solid #e0e0e0; background: white; color: #888; padding: 6px 12px; border-radius: 6px; font-size: 13px; cursor: pointer; }

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
            <a href="Khunggio.php" class="tab-btn active">🕒 Khung giờ</a>
            <a href="LichTuan.php" class="tab-btn">📅 Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn">👥 Danh sách chờ</a>
            <a href="Hoso.php" class="tab-btn">👤 Hồ sơ</a>
        </div>

        <div class="action-bar">
            <button class="btn-add">+ Thêm khung giờ</button>
        </div>

        <?php foreach ($time_slots as $slot): ?>
            <div class="slot-card">
                <div class="slot-info">
                    <div class="slot-time"><?= htmlspecialchars($slot['time']) ?></div>
                    <div class="slot-location <?= $slot['type'] === 'offline' ? 'offline' : '' ?>">
                        <?= $slot['type'] === 'online' ? '📹' : '📍' ?> <?= htmlspecialchars($slot['location']) ?>
                    </div>
                    <div class="slot-booked" style="color: <?= $slot['status_color'] ?>;">
                        <?= $slot['booked'] ?>/<?= $slot['total'] ?> đã đặt
                    </div>
                </div>
                <div class="slot-actions">
                    <button class="btn-edit">Sửa</button>
                    <button class="btn-delete">🗑</button>
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