<?php
session_start();
require_once 'db.php';

if (!isset($pdo) && isset($conn)) { $pdo = $conn; }

// 1. Kiểm tra session đăng nhập
if (!isset($_SESSION['user']['id']) && !isset($_SESSION['user_id'])) {
    header('Location: dangnhap.php');
    exit;
}

$student_id = $_SESSION['user']['id'] ?? $_SESSION['user_id'];

// 2. Lấy thông tin người dùng từ DB
$stmtUser = $pdo->prepare("SELECT fullname FROM users WHERE id = ?");
$stmtUser->execute([$student_id]);
$currentUser = $stmtUser->fetch();

$student_name = !empty($currentUser['fullname']) ? trim($currentUser['fullname']) : ($_SESSION['user_name'] ?? 'Học viên');

// Tách chữ cái đầu tiên của TÊN (từ cuối cùng trong chuỗi họ tên)
$name_parts = explode(' ', $student_name);
$first_name = end($name_parts);
$avatar_letter = mb_strtoupper(mb_substr($first_name, 0, 1, 'UTF-8'), 'UTF-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$teacher_name = "Giảng viên";
$teacher_email = "teacher@edu.vn";
$topic = "Tư vấn ngôn ngữ";
$time = date('d/m/Y H:i');

if ($id > 0 && isset($pdo)) {
    $stmt = $pdo->prepare("
        SELECT u.fullname AS teacher_name, u.email, ts.topic, ts.start_time
        FROM appointments a
        JOIN time_slots ts ON a.slot_id = ts.slot_id
        JOIN users u ON ts.lecturer_id = u.id
        WHERE a.appointment_id = ?
    ");
    $stmt->execute([$id]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($info) {
        $teacher_name = $info['teacher_name'];
        if (!empty($info['email'])) { $teacher_email = $info['email']; }
        if (!empty($info['topic'])) { $topic = $info['topic']; }
        if (!empty($info['start_time'])) { $time = date('d/m/Y H:i', strtotime($info['start_time'])); }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = (int)($_POST['rating'] ?? 5);
    $comment = trim($_POST['comment'] ?? '');

    if ($id > 0 && isset($pdo)) {
        $insert = $pdo->prepare("
            INSERT INTO reviews (appointment_id, teacher_name, rating, type, type_class, created_at, content) 
            VALUES (?, ?, ?, 'Xuất sắc', 'badge-excellent', NOW(), ?)
        ");
        $insert->execute([$id, $teacher_name, $rating, $comment]);
    }

    header('Location: danhgia.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viết Đánh Giá - EDULINGO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #f8bbd0;
            --text-color: #333;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--primary-light);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* HEADER UI */
        .header { 
            background-color: var(--primary-color); 
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
        .logo-box { 
            background: white; 
            color: var(--primary-color); 
            font-weight: bold; 
            padding: 8px 12px; 
            border-radius: 6px; 
            font-size: 14px; 
        }
        .header-text h2 { 
            font-size: 18px; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
        }
        .header-text p { 
            font-size: 13px; 
            opacity: 0.9; 
        }

        .user-profile-icon { 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            color: white; 
            background-color: rgba(255, 255, 255, 0.15); 
            padding: 6px 14px 6px 8px; 
            border-radius: 25px; 
            border: 1px solid rgba(255, 255, 255, 0.3); 
            font-size: 14px; 
            font-weight: 600; 
            user-select: none; 
        }
        .avatar-circle { 
            width: 34px; 
            height: 34px; 
            background-color: white; 
            color: var(--primary-color); 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
            font-size: 15px; 
        }

        /* MAIN CONTENT */
        .main-container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
            flex: 1;
            width: 100%;
        }

        .review-card {
            background-color: white;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(216, 27, 96, 0.05);
            border: 1px solid var(--border-color);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }

        .teacher-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .teacher-name-row {
            display: flex;
            align-items: baseline;
            gap: 10px;
        }

        .teacher-title {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .teacher-email {
            font-size: 13px;
            color: #888;
        }

        .info-sub {
            font-size: 13px;
            color: var(--primary-color);
            opacity: 0.8;
        }

        .star-rating {
            display: flex;
            gap: 6px;
            color: var(--primary-color);
            font-size: 22px;
        }

        .review-textarea {
            width: 100%;
            height: 180px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px;
            font-size: 14px;
            outline: none;
            resize: vertical;
            margin-bottom: 25px;
            background-color: #fafafa;
        }

        .review-textarea:focus {
            background-color: #fff;
            border-color: var(--primary-color);
        }

        .button-group {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-cancel {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
            padding: 10px 24px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <div class="header">
        <div class="header-brand">
            <div class="logo-box">ABC</div>
            <div class="header-text">
                <h2>EDULINGO</h2>
                <p>Hệ thống đặt lịch tư vấn giảng viên</p>
            </div>
        </div>
        <div class="user-profile-icon">
            <div class="avatar-circle"><?= htmlspecialchars($avatar_letter) ?></div>
            <span class="user-name"><?= htmlspecialchars($student_name) ?></span>
        </div>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-container">
        <form method="POST" class="review-card">
            <div class="review-header">
                <div class="teacher-info">
                    <div class="teacher-name-row">
                        <span class="teacher-title">Giảng viên: <?= htmlspecialchars($teacher_name) ?></span>
                        <span class="teacher-email"><?= htmlspecialchars($teacher_email) ?></span>
                    </div>
                    <span class="info-sub"><?= htmlspecialchars($topic) ?></span>
                    <span class="info-sub"><?= htmlspecialchars($time) ?></span>
                </div>
                <div class="star-rating">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>

            <textarea name="comment" class="review-textarea" placeholder="Nhập đánh giá của bạn..." required></textarea>

            <div class="button-group">
                <a href="danhgia.php" class="btn-cancel">Quay lại</a>
                <button type="submit" class="btn-submit">Gửi đánh giá</button>
            </div>
        </form>
    </div>

</body>
</html>