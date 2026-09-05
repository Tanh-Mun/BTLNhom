<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

$lecturer_id = $_SESSION['user_id'] ?? 1; // ID giảng viên đăng nhập
$selected_lecturer = $_SESSION['user_name'] ?? 'Nguyễn Thảo Vy';

// 1. Xác định tuần hiện tại (Mặc định lấy tuần này, có thể điều chỉnh qua tham số $_GET['week_offset'])
$monday_timestamp = strtotime('monday this week');
if (isset($_GET['week_offset'])) {
    $offset = (int)$_GET['week_offset'];
    $monday_timestamp = strtotime("$offset week", $monday_timestamp);
} else {
    $offset = 0;
}

$sunday_timestamp = strtotime('sunday', $monday_timestamp);

$start_date_str = date('Y-m-d 00:00:00', $monday_timestamp);
$end_date_str = date('Y-m-d 23:59:59', $sunday_timestamp);

// 2. Lấy toàn bộ khung giờ của Giảng viên trong tuần kèm theo số lượng slot đã duyệt
$sql = "SELECT ts.slot_id, ts.start_time, ts.end_time, 1 AS max_students,
               COUNT(CASE WHEN a.status = 'approved' THEN 1 END) AS approved_count
        FROM time_slots ts
        LEFT JOIN appointments a ON ts.slot_id = a.slot_id
        WHERE ts.lecturer_id = ? AND ts.start_time BETWEEN ? AND ?
        GROUP BY ts.slot_id, ts.start_time, ts.end_time
        ORDER BY ts.start_time ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$lecturer_id, $start_date_str, $end_date_str]);
$raw_slots = $stmt->fetchAll();

// 3. Gom nhóm các slot theo 7 ngày trong tuần (Từ T2 đến CN)
$week_days = [];
$day_names = ['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'];

for ($i = 0; $i < 7; $i++) {
    $current_day_timestamp = strtotime("+$i days", $monday_timestamp);
    $date_key = date('Y-m-d', $current_day_timestamp);
    $day_label = $day_names[$i] . ', ' . date('d/m', $current_day_timestamp);

    $slots_for_day = [];
    foreach ($raw_slots as $slot) {
        if (date('Y-m-d', strtotime($slot['start_time'])) === $date_key) {
            $slots_for_day[] = [
                'time' => date('H:i', strtotime($slot['start_time'])) . ' - ' . date('H:i', strtotime($slot['end_time'])),
                'booked' => $slot['approved_count'] . '/' . $slot['max_students']
            ];
        }
    }

    $week_days[] = [
        'day_label' => $day_label,
        'slots' => $slots_for_day
    ];
}

// Lấy chữ cái avatar
$name_parts = explode(' ', trim($selected_lecturer));
$avatar_letter = strtoupper(substr(end($name_parts), 0, 1));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch tuần - Giảng viên</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: var(--primary-light); color: #333; }
        
        /* Header */
        .header { 
            background-color: var(--primary-color); 
            color: white; 
            padding: 15px 40px; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
        }
        .header-brand { display: flex; align-items: center; gap: 15px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 8px 12px; border-radius: 6px; font-size: 14px; }
        .header-text h2 { font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }
        .header-text p { font-size: 13px; opacity: 0.9; }

        /* USER DROPDOWN MENU */
        .user-dropdown { position: relative; display: inline-block; }
        .user-profile-icon { display: flex; align-items: center; gap: 10px; color: white; background-color: rgba(255, 255, 255, 0.15); padding: 6px 16px 6px 6px; border-radius: 25px; border: 1px solid rgba(255, 255, 255, 0.3); cursor: pointer; text-decoration: none; user-select: none; }
        .avatar-circle { width: 34px; height: 34px; background-color: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }
        
        .dropdown-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); background-color: white; min-width: 170px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15); padding: 8px 0; z-index: 1000; border: 1px solid var(--border-color); }
        .dropdown-menu.show { display: block; }
        .dropdown-item { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: var(--primary-color); text-decoration: none; font-size: 14px; font-weight: 600; transition: background 0.2s; }
        .dropdown-item:hover { background-color: #fce4ec; }
        .dropdown-item i { font-size: 16px; width: 18px; text-align: center; }

        /* Container & Navigation */
        .container { max-width: 1000px; margin: 25px auto; padding: 0 20px; }
        .nav-tabs { 
            display: inline-flex; background: white; padding: 4px; border-radius: 30px; 
            border: 1px solid var(--border-color); margin-bottom: 25px; 
        }
        .tab-btn { 
            padding: 8px 18px; border-radius: 20px; border: none; background: transparent; 
            color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; 
            display: flex; align-items: center; gap: 6px; text-decoration: none; 
        }
        .tab-btn.active { background: var(--primary-color); color: white; }

        /* Switcher Bar */
        .date-switcher { display: flex; justify-content: center; align-items: center; gap: 12px; margin-bottom: 30px; }
        .btn-arrow { 
            background: white; border: 1px solid #f48fb1; color: var(--primary-color); 
            width: 36px; height: 36px; border-radius: 10px; display: flex; 
            align-items: center; justify-content: center; text-decoration: none; font-size: 16px; font-weight: bold;
        }
        .date-range-btn { 
            background: white; border: 1px solid #f48fb1; color: var(--primary-color); 
            padding: 8px 20px; border-radius: 10px; font-weight: bold; font-size: 14px; 
        }

        /* Week Grid */
        .week-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 40px; }
        .day-card { 
            background: white; border: 1px solid #f8bbd0; border-radius: 12px; 
            padding: 12px 8px; min-height: 280px; display: flex; flex-direction: column; gap: 10px; 
        }
        .day-header { color: var(--primary-color); font-weight: bold; font-size: 13px; text-align: left; margin-bottom: 5px; }
        .slot-item { border: 1px solid #f8bbd0; border-radius: 8px; padding: 8px 4px; text-align: center; background: #fff0f3; }
        .slot-item .time { color: var(--primary-color); font-weight: bold; font-size: 11px; margin-bottom: 3px; }
        .slot-item .booked { color: #666; font-size: 11px; font-weight: 600; }

        /* Footer */
        .footer { 
            background-color: var(--primary-color); color: white; padding: 40px; 
            margin-top: 50px; display: grid; grid-template-columns: 2fr 1fr 1fr 1.5fr; gap: 30px; 
        }
        .footer-logo { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        .footer-logo .logo-box { color: var(--primary-color); }
        .footer p { font-size: 13px; line-height: 1.5; opacity: 0.9; }
        .footer h4 { font-size: 13px; font-style: italic; margin-bottom: 15px; text-transform: uppercase; }
        .footer ul { list-style: none; }
        .footer ul li { margin-bottom: 10px; font-size: 13px; opacity: 0.9; }
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

        <div class="user-dropdown">
            <div class="user-profile-icon" id="userMenuBtn">
                <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
                <span class="user-name"><?= htmlspecialchars($selected_lecturer) ?></span>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <a href="Hoso.php" class="dropdown-item"><i class="fa-regular fa-id-card"></i> Xem hồ sơ</a>
                <a href="dangnhap.php" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="nav-tabs">
            <a href="Cuochen.php" class="tab-btn"><i class="fa-regular fa-calendar-check"></i> Cuộc hẹn</a>
            <a href="Khunggio.php" class="tab-btn"><i class="fa-regular fa-clock"></i> Khung giờ</a>
            <a href="Lichtuan.php" class="tab-btn active"><i class="fa-solid fa-calendar-days"></i> Lịch tuần</a>
            <a href="DanhSachCho.php" class="tab-btn"><i class="fa-solid fa-users"></i> Danh sách chờ</a>
        </div>

        <div class="date-switcher">
            <a href="Lichtuan.php?week_offset=<?= $offset - 1 ?>" class="btn-arrow">&lt;</a>
            <div class="date-range-btn">
                T2, <?= date('d/m', $monday_timestamp) ?> - CN, <?= date('d/m', $sunday_timestamp) ?> 📅
            </div>
            <a href="Lichtuan.php?week_offset=<?= $offset + 1 ?>" class="btn-arrow">&gt;</a>
        </div>

        <div class="week-grid">
            <?php foreach ($week_days as $day): ?>
                <div class="day-card">
                    <div class="day-header"><?= htmlspecialchars($day['day_label']) ?></div>
                    <?php if (empty($day['slots'])): ?>
                        <div style="font-size: 11px; color: #aaa; text-align: center; margin-top: 15px;">Trống</div>
                    <?php else: ?>
                        <?php foreach ($day['slots'] as $slot): ?>
                            <div class="slot-item">
                                <div class="time"><?= htmlspecialchars($slot['time']) ?></div>
                                <div class="booked"><?= htmlspecialchars($slot['booked']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="footer">
        <div>
            <div class="footer-logo">
                <div class="logo-box">ABC</div>
                <strong>EDULINGO</strong>
            </div>
            <p>Hệ thống đặt lịch tư vấn ngoại ngữ, giúp sinh viên tìm và đặt buổi gặp chỉ trong vài bước</p>
        </div>
        <div>
            <h4>KHÁM PHÁ</h4>
            <ul>
                <li>Tìm giảng viên</li>
                <li>Đánh giá</li>
            </ul>
        </div>
        <div>
            <h4>GIẢNG VIÊN</h4>
            <ul>
                <li>Đăng ký giảng dạy</li>
                <li>Quản lý khung giờ</li>
            </ul>
        </div>
        <div>
            <h4>THÔNG BÁO</h4>
            <p>Nhận tin khi có khung giờ mới mở</p>
        </div>
    </div>

    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        
        userMenuBtn.addEventListener('click', (e) => { 
            e.stopPropagation(); 
            userDropdown.classList.toggle('show'); 
        });
        
        document.addEventListener('click', () => { 
            userDropdown.classList.remove('show'); 
        });
    </script>
</body>
</html>