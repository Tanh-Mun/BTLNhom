<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

// Khởi tạo danh sách lịch hẹn trong session nếu chưa có
if (!isset($_SESSION['appointments'])) {
    $_SESSION['appointments'] = [
        ['id' => 1, 'name' => 'Lý Gia Hân', 'subject' => 'Tiếng Trung giao tiếp, luyện phản xạ', 'time' => 'T4, 26/08 - 8:30-9:30']
    ];
}

// Xử lý hành động đặt lịch từ danh sách
if (isset($_GET['book_id'])) {
    $book_id = (int)$_GET['book_id'];
    $all_teachers = [
        1 => ['id' => 1, 'name' => 'Lý Gia Hân', 'subject' => 'Tiếng Trung giao tiếp, luyện phản xạ', 'time' => 'T4, 26/08 - 8:30-9:30'],
        2 => ['id' => 2, 'name' => 'Nguyễn Thảo Vy', 'subject' => 'IELTS Reading, kỹ năng đọc hiểu', 'time' => 'T6, 28/08 - 10:00-11:30'],
        3 => ['id' => 3, 'name' => 'Trần Minh Đức', 'subject' => 'Ngữ pháp tiếng Đức', 'time' => 'T6, 28/08 - 15:30-16:30'],
        4 => ['id' => 4, 'name' => 'Yamada Haruko', 'subject' => 'Phát âm tiếng Pháp, luyện âm', 'time' => 'T7, 29/08 - 9:00-10:00']
    ];
    
    if (isset($all_teachers[$book_id])) {
        // Kiểm tra xem đã tồn tại trong lịch hẹn chưa để tránh trùng lặp
        $exists = false;
        foreach ($_SESSION['appointments'] as $app) {
            if ($app['id'] === $book_id) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $_SESSION['appointments'][] = $all_teachers[$book_id];
        }
    }
    header('Location: lichhen.php');
    exit;
}

// Dữ liệu sinh viên mẫu và xử lý chữ cái đầu làm avatar giống các trang giảng viên
$selected_student = 'Lê Hoàng Nam';
$name_parts = explode(' ', trim($selected_student));
$last_word = end($name_parts);
$avatar_letter = strtoupper(substr($last_word, 0, 1));
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tìm Giảng Viên & Đặt Lịch - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
            --btn-green: #2e7d32;
            --btn-green-bg: #e8f5e9;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); display: flex; flex-direction: column; min-height: 100vh; }
        .header { background-color: var(--primary-color); color: white; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        
        /* Giao diện nút profile/avatar không chứa mũi tên */
        .user-dropdown { position: relative; display: inline-block; }
        .user-profile-icon {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            padding: 4px 14px 4px 6px;
            border-radius: 25px;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
        }
        .user-profile-icon:hover {
            background-color: rgba(255, 255, 255, 0.25);
        }
        .avatar-circle {
            width: 32px;
            height: 32px;
            background-color: white;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }
        .user-name {
            font-size: 14px;
            font-weight: 600;
        }

        .dropdown-menu { display: none; position: absolute; right: 0; top: 110%; background-color: white; min-width: 150px; box-shadow: 0px 4px 12px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { color: var(--primary-color); padding: 10px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .dropdown-item:hover { background-color: var(--primary-light); }
        .main-container { max-width: 1000px; margin: 0 auto; padding: 25px 20px 50px 20px; width: 100%; flex: 1; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 20px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .tab-btn.active { background: var(--primary-light); border: 1px solid var(--border-color); }
        .search-banner { background-color: var(--primary-color); border-radius: 20px; padding: 30px; color: white; margin-bottom: 25px; }
        .search-banner h2 { font-size: 22px; margin-bottom: 20px; text-transform: uppercase; color: white; }
        .search-form { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; }
        .search-control { width: 100%; padding: 10px 16px; border-radius: 20px; border: none; outline: none; font-size: 13px; background: white; color: var(--primary-color); }
        .search-control::placeholder { color: var(--primary-color); opacity: 0.8; }
        .teacher-section { background: #fff0f3; border: 1px solid var(--border-color); border-radius: 20px; padding: 20px; display: flex; flex-direction: column; gap: 12px; }
        .teacher-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; padding: 16px 25px; display: flex; justify-content: space-between; align-items: center; }
        .teacher-info h4 { color: var(--primary-color); font-size: 16px; margin-bottom: 4px; }
        .teacher-info .subject { color: #d81b60; font-size: 13px; margin-bottom: 4px; opacity: 0.85; }
        .teacher-info .time { color: #e91e63; font-size: 12px; opacity: 0.65; }
        .btn-book { background-color: var(--btn-green-bg); color: var(--btn-green); border: 1px solid #c8e6c9; padding: 8px 18px; border-radius: 20px; font-size: 13px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        
        .footer { background-color: var(--primary-color); color: white; padding: 40px 60px; margin-top: auto; }
        .footer-grid { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 30px; }
        .footer-col h5 { font-size: 12px; text-transform: uppercase; margin-bottom: 15px; letter-spacing: 0.5px; }
        .footer-col p { font-size: 12px; line-height: 1.6; opacity: 0.95; margin-bottom: 15px; }
        .footer-links { list-style: none; }
        .footer-links li { margin-bottom: 10px; }
        .footer-links a { color: white; text-decoration: none; font-size: 12px; opacity: 0.95; }
        .social-icons { display: flex; gap: 10px; }
        .social-btn { width: 32px; height: 32px; background: white; color: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; text-decoration: none; }
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
        <div class="user-dropdown">
            <div class="user-profile-icon" id="userMenuBtn">
                <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
                <span class="user-name"><?= htmlspecialchars($selected_student) ?></span>
            </div>
            <div class="dropdown-menu" id="userDropdown">
                <a href="Hososv.php" class="dropdown-item"><i class="fa-regular fa-id-card"></i> Xem hồ sơ</a>
                <a href="?action=logout" class="dropdown-item" style="color: #d81b60;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </header>
    <div class="main-container">
        <div class="nav-tabs">
            <a href="timvadatlich.php" class="tab-btn active"><i class="fa-regular fa-calendar-check"></i> Tìm & Đặt lịch</a>
            <a href="lichhen.php" class="tab-btn"><i class="fa-regular fa-user"></i> Lịch của tôi</a>
            <a href="danhgia.php" class="tab-btn"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>
        <div class="search-banner">
            <h2>TÌM GIẢNG VIÊN & ĐẶT LỊCH TƯ VẤN</h2>
            <form class="search-form" method="GET" action="">
                <select name="teacher" class="search-control">
                    <option value="">Tên giảng viên</option>
                    <option value="Nguyen Thao Vy">Nguyễn Thảo Vy</option>
                    <option value="Tran Minh Duc">Trần Minh Đức</option>
                    <option value="Yamada Haruko">Yamada Haruko</option>
                    <option value="Ly Gia Han">Lý Gia Hân</option>
                </select>
                <select name="subject" class="search-control">
                    <option value="">Môn học</option>
                    <option value="Tieng Anh">Tiếng Anh</option>
                    <option value="Tieng Duc">Tiếng Đức</option>
                    <option value="Tieng Phap">Tiếng Pháp</option>
                    <option value="Tieng Trung">Tiếng Trung</option>
                </select>
                <input type="text" name="keyword" class="search-control" placeholder="Chủ đề / Từ khóa">
                <input type="text" name="date" class="search-control" placeholder="dd/mm/yyyy" onfocus="(this.type='date')" onblur="(this.type='text')">
            </form>
        </div>
        <div class="teacher-section">
            <div class="teacher-card">
                <div class="teacher-info">
                    <h4>Lý Gia Hân</h4>
                    <div class="subject">Tiếng Trung giao tiếp, luyện phản xạ</div>
                    <div class="time">T4, 26/08 - 8:30-9:30</div>
                </div>
                <a href="timvadatlich.php?book_id=1" class="btn-book"><i class="fa-regular fa-circle-check"></i> Đặt lịch</a>
            </div>
            <div class="teacher-card">
                <div class="teacher-info">
                    <h4>Nguyễn Thảo Vy</h4>
                    <div class="subject">IELTS READING</div>
                    <div class="time">T6, 28/08 - 10:00-11:30</div>
                </div>
                <a href="timvadatlich.php?book_id=2" class="btn-book"><i class="fa-regular fa-circle-check"></i> Đặt lịch</a>
            </div>
            <div class="teacher-card">
                <div class="teacher-info">
                    <h4>Trần Minh Đức</h4>
                    <div class="subject">Ngữ pháp tiếng Đức</div>
                    <div class="time">T6, 28/08 - 15:30-16:30</div>
                </div>
                <a href="timvadatlich.php?book_id=3" class="btn-book"><i class="fa-regular fa-circle-check"></i> Đặt lịch</a>
            </div>
            <div class="teacher-card">
                <div class="teacher-info">
                    <h4>Yamada Haruko</h4>
                    <div class="subject">Phát âm tiếng Pháp, luyện âm</div>
                    <div class="time">T7, 29/08 - 9:00-10:00</div>
                </div>
                <a href="timvadatlich.php?book_id=4" class="btn-book"><i class="fa-regular fa-circle-check"></i> Đặt lịch</a>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-col">
                <div class="logo-container" style="margin-bottom: 12px;">
                    <div class="logo-box">ABC</div>
                    <strong>EDULINGO</strong>
                </div>
                <p>Hệ thống đặt lịch tư vấn giảng viên ngoại ngữ, giúp sinh viên tìm và đặt buổi gặp chỉ trong vài bước</p>
                <div class="social-icons">
                    <a href="#" class="social-btn">FB</a>
                    <a href="#" class="social-btn">ZL</a>
                    <a href="#" class="social-btn"><i class="fa-regular fa-envelope"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h5>KHÁM PHÁ</h5>
                <ul class="footer-links">
                    <li><a href="#">Tìm giảng viên</a></li>
                    <li><a href="#">Đánh giá</a></li>
                    <li><a href="#">Ngôn ngữ hỗ trợ</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>DÀNH CHO GIẢNG VIÊN</h5>
                <ul class="footer-links">
                    <li><a href="#">Đăng ký giảng dạy</a></li>
                    <li><a href="#">Quản lý khung giờ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h5>NHẬN THÔNG BÁO</h5>
                <p>Nhận tin khi có giảng viên mới hoặc khung giờ mới mở</p>
            </div>
        </div>
    </footer>
    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('show'); });
        document.addEventListener('click', () => { userDropdown.classList.remove('show'); });
    </script>
</body>
</html>