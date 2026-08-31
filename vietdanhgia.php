<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}

$teachers_db = [
    1 => ['name' => 'Lý Gia Hân', 'email' => 'han.ly@sv.edu', 'subject' => 'Tiếng Trung giao tiếp, luyện phản xạ', 'time' => 'T4, 26/08 - 8:30-9:30'],
    2 => ['name' => 'Nguyễn Thảo Vy', 'email' => 'vy.nguyen@sv.edu', 'subject' => 'IELTS Reading, kỹ năng đọc hiểu', 'time' => 'T6, 28/08 - 13:30-14:30'],
    3 => ['name' => 'Trần Minh Đức', 'email' => 'duc.tran@sv.edu', 'subject' => 'Ngữ pháp tiếng Đức', 'time' => 'T6, 28/08 - 15:30-16:30'],
    4 => ['name' => 'Yamada Haruko', 'email' => 'haruko.yamada@sv.edu', 'subject' => 'Phát âm tiếng Pháp, luyện âm', 'time' => 'T7, 29/08 - 9:00-10:00']
];

$teacher_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
$teacher = isset($teachers_db[$teacher_id]) ? $teachers_db[$teacher_id] : $teachers_db[1];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['success_message'] = "Đã gửi đánh giá thành công!";
    header('Location: danhgia.php');
    exit;
}

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
    <title>Viết Đánh Giá - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-color: #d81b60; --primary-light: #fdf2f5; --border-color: #fce4ec; --star-grey: #e0e0e0; --star-pink: #d81b60; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        html, body { height: 100vh; overflow: hidden; background-color: var(--primary-light); display: flex; flex-direction: column; }
        .header { background-color: var(--primary-color); color: white; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        
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
        .user-profile-icon:hover { background-color: rgba(255, 255, 255, 0.25); }
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
        .user-name { font-size: 14px; font-weight: 600; }

        .dropdown-menu { display: none; position: absolute; right: 0; top: 110%; background-color: white; min-width: 150px; box-shadow: 0px 4px 12px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { color: var(--primary-color); padding: 10px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .dropdown-item:hover { background-color: var(--primary-light); }
        .main-container { max-width: 1150px; margin: auto; padding: 20px; width: 100%; flex: 1; display: flex; flex-direction: column; justify-content: center; }
        .review-form-card { background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 30px 40px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); }
        .form-header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #fce4ec; padding-bottom: 15px; margin-bottom: 20px; }
        .form-header h4 { color: var(--primary-color); font-size: 18px; display: inline-block; }
        .form-header .email { color: #b0124a; font-size: 13px; margin-left: 8px; opacity: 0.6; }
        .star-rating { display: flex; gap: 6px; font-size: 26px; cursor: pointer; color: var(--star-grey); }
        .star-rating i.active { color: var(--star-pink) !important; }
        .review-textarea { width: 100%; height: 220px; border: none; outline: none; resize: none; font-size: 14px; color: #333; margin-bottom: 20px; }
        .form-actions { display: flex; justify-content: flex-end; gap: 12px; }
        .btn-back { background: white; color: var(--primary-color); border: 1px solid var(--primary-color); padding: 8px 24px; border-radius: 20px; font-size: 13px; font-weight: bold; text-decoration: none; display: inline-block; }
        .btn-submit { background-color: var(--primary-color); color: white; border: none; padding: 8px 24px; border-radius: 20px; font-size: 13px; font-weight: bold; cursor: pointer; }
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
                <a href="?action=logout" class="dropdown-item"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </header>
    <div class="main-container">
        <div class="review-form-card">
            <form action="vietdanhgia.php?id=<?= $teacher_id ?>" method="POST">
                <input type="hidden" name="teacher_id" value="<?= $teacher_id ?>">
                <div class="form-header">
                    <div>
                        <h4><?= htmlspecialchars($teacher['name']) ?></h4>
                        <span class="email"><?= htmlspecialchars($teacher['email']) ?></span>
                        <div style="color: var(--primary-color); font-size: 13px; margin-top: 4px;"><?= htmlspecialchars($teacher['subject']) ?></div>
                        <div style="color: var(--primary-color); font-size: 12px; margin-top: 2px; opacity: 0.65;"><?= htmlspecialchars($teacher['time']) ?></div>
                    </div>
                    <div class="star-rating" id="starRating">
                        <i class="fa-solid fa-star" data-value="1"></i>
                        <i class="fa-solid fa-star" data-value="2"></i>
                        <i class="fa-solid fa-star" data-value="3"></i>
                        <i class="fa-solid fa-star" data-value="4"></i>
                        <i class="fa-solid fa-star" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="ratingValue" value="0">
                </div>
                <textarea name="comment" class="review-textarea" placeholder="Nhập đánh giá của bạn ...."></textarea>
                <div class="form-actions">
                    <a href="danhgia.php" class="btn-back">Quay lại</a>
                    <button type="submit" class="btn-submit">Gửi đánh giá</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('show'); });
        document.addEventListener('click', () => { userDropdown.classList.remove('show'); });

        const stars = document.querySelectorAll('#starRating i');
        const ratingInput = document.getElementById('ratingValue');
        stars.forEach((star, index) => {
            star.addEventListener('click', () => {
                const val = index + 1;
                ratingInput.value = val;
                stars.forEach((s, i) => {
                    if (i < val) s.classList.add('active'); else s.classList.remove('active');
                });
            });
        });
    </script>
</body>
</html>