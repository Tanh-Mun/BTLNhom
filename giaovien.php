<?php
session_start();

// 1. KẾT NỐI VỚI DỮ LIỆU TỪ SESSION
if (!isset($_SESSION['feedbacks'])) {
    $_SESSION['feedbacks'] = [
        [
            'lecturer_name' => 'TS. Trần Tuấn Anh',
            'rating'        => 5,
            'comment'       => 'Thầy tư vấn đồ án rất nhiệt tình và chi tiết.',
            'created_at'    => '2026-08-10 14:30',
            'reply'         => 'Cảm ơn em, chúc em hoàn thành tốt đồ án!'
        ],
        [
            'lecturer_name' => 'ThS. Nguyễn Thu Quỳnh',
            'rating'        => 4,
            'comment'       => 'Buổi tư vấn tạm ổn, giải đáp đúng trọng tâm.',
            'created_at'    => '2026-08-12 09:15',
            'reply'         => ''
        ]
    ];
}

$errors = [];
$success_message = '';

// 2. HÀM PHÂN LOẠI MỨC ĐỘ HÀI LÒNG
function phanLoaiDanhGia($rating) {
    if ($rating == 5) {
        return '<span class="badge badge-excellent"><span class="dot"></span> Xuất sắc</span>';
    } elseif ($rating >= 4) {
        return '<span class="badge badge-good"><span class="dot"></span> Tốt</span>';
    } elseif ($rating == 3) {
        return '<span class="badge badge-normal"><span class="dot"></span> Bình thường</span>';
    } else {
        return '<span class="badge badge-poor"><span class="dot"></span> Cần cải thiện</span>';
    }
}

// 3. XỬ LÝ PHẢN HỒI CỦA GIẢNG VIÊN (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reply_feedback') {
    $fb_index = intval($_POST['fb_index'] ?? -1);
    $reply_text = trim($_POST['reply_text'] ?? '');

    if ($fb_index >= 0 && isset($_SESSION['feedbacks'][$fb_index])) {
        $_SESSION['feedbacks'][$fb_index]['reply'] = htmlspecialchars($reply_text);
        $_SESSION['success_message'] = "Đã lưu phản hồi của giảng viên thành công!";
        
        $current_page = strtok($_SERVER["REQUEST_URI"], '?');
        header("Location: " . $current_page);
        exit();
    } else {
        $errors['global'] = "Không tìm thấy phản hồi cần trả lời!";
    }
}

// Lấy thông báo thành công từ Session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// 4. XỬ LÝ BỘ LỌC DỮ LIỆU & VALIDATION
$filter_lecturer = $_GET['filter_lecturer'] ?? 'all';
$filter_rating   = $_GET['filter_rating'] ?? 'all';

// Kiểm tra xem người dùng đã thực hiện thao tác bấm nút "Lọc" chưa
$is_filter_submitted = isset($_GET['filter_lecturer']) || isset($_GET['filter_rating']);

if ($is_filter_submitted && $filter_lecturer === 'all' && $filter_rating === 'all') {
    $errors['filter'] = "Vui lòng chọn ít nhất một điều kiện để lọc!";
}

$filtered_feedbacks = [];
foreach ($_SESSION['feedbacks'] as $index => $fb) {
    // Lọc theo tên giảng viên
    if ($filter_lecturer !== 'all' && $fb['lecturer_name'] !== $filter_lecturer) {
        continue;
    }
    // Lọc theo số sao
    if ($filter_rating !== 'all' && intval($fb['rating']) !== intval($filter_rating)) {
        continue;
    }
    // Lưu lại index gốc để phục vụ việc lưu câu trả lời chính xác
    $fb['original_index'] = $index;
    $filtered_feedbacks[] = $fb;
}

// 5. TÍNH TOÁN THỐNG KÊ (DASHBOARD METRICS)
$total_feedbacks = count($_SESSION['feedbacks']);
$sum_stars = 0;
$positive_count = 0;

foreach ($_SESSION['feedbacks'] as $fb) {
    $sum_stars += $fb['rating'];
    if ($fb['rating'] >= 4) {
        $positive_count++;
    }
}

$avg_rating = $total_feedbacks > 0 ? round($sum_stars / $total_feedbacks, 1) : 0;
$positive_rate = $total_feedbacks > 0 ? round(($positive_count / $total_feedbacks) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cổng Thông Tin Giảng Viên - Quản Lý Đánh Giá</title>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PURE CSS THUẦN - THEME PINK/ROSE Đồng bộ trang đánh giá -->
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #fff1f2;
            color: #1e293b;
            padding: 0.75rem;
        }
        @media (min-width: 768px) { body { padding: 1.5rem; } }

        .container { max-width: 1152px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.5rem; }
        
        /* Cards */
        .card { background: #ffffff; padding: 1.25rem; border-radius: 1rem; border: 1px solid #fecdd3; box-shadow: 0 1px 3px 0 rgba(225, 29, 72, 0.05); }
        .header-card { display: flex; flex-direction: column; justify-content: space-between; gap: 1rem; background: #ffffff; padding: 1.5rem; border-radius: 1rem; border: 1px solid #fecdd3; box-shadow: 0 1px 3px 0 rgba(225, 29, 72, 0.05); }
        @media (min-width: 768px) { .header-card { flex-direction: row; align-items: center; } }

        .header-title { font-size: 1.5rem; font-weight: 900; color: #881337; margin-top: 0.5rem; }
        .header-subtitle { color: #9f1239; font-size: 0.875rem; margin-top: 0.25rem; opacity: 0.8; }

        /* Dashboard Stat Cards */
        .stats-grid { display: grid; grid-template-columns: repeat(1, 1fr); gap: 1rem; }
        @media (min-width: 768px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
        
        .stat-card { background: #ffffff; border: 1px solid #fecdd3; border-radius: 0.75rem; padding: 1rem; display: flex; align-items: center; gap: 1rem; }
        .stat-icon { width: 3rem; height: 3rem; border-radius: 0.5rem; background: #ffe4e6; color: #e11d48; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .stat-label { font-size: 0.75rem; color: #9f1239; font-weight: 600; }
        .stat-value { font-size: 1.5rem; font-weight: 800; color: #881337; }

        /* Alerts */
        .alert { padding: 1rem; border-radius: 0.75rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.75rem; }
        .alert-error { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

        .section-title { font-size: 1rem; font-weight: 700; color: #881337; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .icon-theme { color: #e11d48; }

        /* Filters Bar */
        .filter-wrapper { display: flex; flex-direction: column; align-items: flex-end; gap: 0.375rem; }
        .filter-form { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }
        
        .form-control { font-size: 0.75rem; padding: 0.5rem 0.75rem; border: 1px solid #fecdd3; border-radius: 0.5rem; background: #fff1f2; outline: none; }
        .form-control:focus { border-color: #e11d48; box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.2); }
        .form-control.is-invalid { border-color: #be123c; background-color: #fff5f5; }

        .btn-filter { background: #e11d48; color: #ffffff; font-weight: 600; font-size: 0.75rem; padding: 0.5rem 1rem; border-radius: 0.5rem; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.375rem; transition: background 0.2s; text-decoration: none; }
        .btn-filter:hover { background: #be123c; }

        .error-text { font-size: 0.6875rem; color: #be123c; font-weight: 600; display: flex; align-items: center; gap: 0.25rem; }

        /* Table Structure */
        .table-container { border-radius: 0.75rem; border: 1px solid #fecdd3; overflow: hidden; }
        .custom-table { width: 100%; text-align: left; font-size: 0.75rem; border-collapse: collapse; }
        .custom-table th { background: #ffe4e6; color: #881337; font-weight: 600; padding: 0.75rem 0.5rem; border-bottom: 1px solid #fecdd3; }
        .custom-table td { padding: 0.75rem 0.5rem; border-bottom: 1px solid #fff1f2; vertical-align: top; }
        .custom-table tr:hover { background: rgba(255, 241, 242, 0.6); }

        /* Stars Display */
        .stars-wrapper { color: #e11d48; font-size: 0.6875rem; display: inline-flex; gap: 0.125rem; }
        .star-empty { color: #fecdd3; }

        /* Badges */
        .badge { display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.6875rem; font-weight: 600; padding: 0.125rem 0.5rem; border-radius: 9999px; white-space: nowrap; }
        .badge-excellent { background: #d1fae5; color: #065f46; }
        .badge-excellent .dot { background: #10b981; }
        .badge-good { background: #ffe4e6; color: #9f1239; }
        .badge-good .dot { background: #f43f5e; }
        .badge-normal { background: #fef3c7; color: #92400e; }
        .badge-normal .dot { background: #f59e0b; }
        .badge-poor { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }
        .badge-poor .dot { background: #e11d48; }
        .dot { width: 0.375rem; height: 0.375rem; border-radius: 50%; display: inline-block; }

        /* Teacher Reply Box */
        .reply-box { margin-top: 0.5rem; padding: 0.5rem; background: #fff1f2; border-left: 3px solid #e11d48; border-radius: 0.25rem; font-size: 0.75rem; }
        .reply-title { font-weight: 700; color: #881337; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.25rem; }
        .reply-form { margin-top: 0.5rem; display: flex; gap: 0.5rem; }
        .reply-input { flex: 1; padding: 0.375rem 0.5rem; font-size: 0.75rem; border: 1px solid #fecdd3; border-radius: 0.375rem; background: #fff1f2; outline: none; }
        .reply-input:focus { border-color: #e11d48; }
        .btn-reply { background: #e11d48; color: white; border: none; padding: 0.375rem 0.75rem; border-radius: 0.375rem; cursor: pointer; font-size: 0.75rem; font-weight: 600; transition: background 0.2s; }
        .btn-reply:hover { background: #be123c; }
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Header Header -->
        <div class="header-card">
            <div>
                <h1 class="header-title"><i class="fa-solid fa-chalkboard-user icon-theme"></i> Cổng Giảng Viên: Tổng Quan Đánh Giá</h1>
                <p class="header-subtitle">Theo dõi, phân tích ý kiến phản hồi và tương tác với sinh viên</p>
            </div>
        </div>

        <!-- Dashboard Thống kê -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-comments"></i></div>
                <div>
                    <div class="stat-label">Tổng Số Phản Hồi</div>
                    <div class="stat-value"><?= $total_feedbacks ?></div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-star"></i></div>
                <div>
                    <div class="stat-label">Điểm Đánh Giá TB</div>
                    <div class="stat-value"><?= $avg_rating ?> / 5.0</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-face-smile"></i></div>
                <div>
                    <div class="stat-label">Tỷ Lệ Hài Lòng (>= 4★)</div>
                    <div class="stat-value"><?= $positive_rate ?>%</div>
                </div>
            </div>
        </div>

        <!-- Thông báo Alert -->
        <?php if (isset($errors['global'])): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= $errors['global'] ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $success_message ?></span>
            </div>
        <?php endif; ?>

        <!-- Bảng danh sách & Bộ lọc -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                <h2 class="section-title" style="margin-bottom: 0;">
                    <i class="fa-solid fa-filter icon-theme"></i> Lọc danh sách phản hồi
                </h2>

                <!-- Form Lọc -->
                <div class="filter-wrapper">
                    <form method="GET" action="" class="filter-form">
                        <select name="filter_lecturer" class="form-control <?= isset($errors['filter']) ? 'is-invalid' : '' ?>">
                            <option value="all">-- Tất cả Giảng viên --</option>
                            <option value="TS. Trần Tuấn Anh" <?= $filter_lecturer === 'TS. Trần Tuấn Anh' ? 'selected' : '' ?>>TS. Trần Tuấn Anh</option>
                            <option value="ThS. Nguyễn Thu Quỳnh" <?= $filter_lecturer === 'ThS. Nguyễn Thu Quỳnh' ? 'selected' : '' ?>>ThS. Nguyễn Thu Quỳnh</option>
                            <option value="Trịnh Quang Vinh" <?= $filter_lecturer === 'Trịnh Quang Vinh' ? 'selected' : '' ?>>Trịnh Quang Vinh</option>
                        </select>

                        <select name="filter_rating" class="form-control <?= isset($errors['filter']) ? 'is-invalid' : '' ?>">
                            <option value="all">-- Tất cả Mức sao --</option>
                            <option value="5" <?= $filter_rating === '5' ? 'selected' : '' ?>>5 Sao (Xuất sắc)</option>
                            <option value="4" <?= $filter_rating === '4' ? 'selected' : '' ?>>4 Sao (Tốt)</option>
                            <option value="3" <?= $filter_rating === '3' ? 'selected' : '' ?>>3 Sao (Bình thường)</option>
                            <option value="2" <?= $filter_rating === '2' ? 'selected' : '' ?>>2 Sao</option>
                            <option value="1" <?= $filter_rating === '1' ? 'selected' : '' ?>>1 Sao (Cần cải thiện)</option>
                        </select>

                        <button type="submit" class="btn-filter">
                            <i class="fa-solid fa-magnifying-glass"></i> Lọc
                        </button>
                        
                        <?php if ($filter_lecturer !== 'all' || $filter_rating !== 'all'): ?>
                            <a href="<?= strtok($_SERVER['REQUEST_URI'], '?') ?>" class="btn-filter" style="background-color: #64748b;" title="Bỏ lọc">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </form>

                    <!-- Hiển thị dòng thông báo lỗi bên dưới ô chọn -->
                    <?php if (isset($errors['filter'])): ?>
                        <div class="error-text">
                            <i class="fa-solid fa-circle-exclamation"></i> <?= $errors['filter'] ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Bảng Dữ Liệu -->
            <div class="table-container">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 3rem; text-align: center;">STT</th>
                            <th style="width: 11rem;">Giảng viên</th>
                            <th style="width: 6rem;">Đánh giá</th>
                            <th style="width: 8rem;">Phân loại</th>
                            <th style="width: 8rem;">Ngày gửi</th>
                            <th>Nội dung nhận xét & Trả lời từ Giảng viên</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($filtered_feedbacks)): ?>
                            <tr>
                                <td colspan="6" style="padding: 1.5rem; text-align: center; color: #fda4af; font-style: italic;">
                                    Không có phản hồi nào phù hợp với bộ lọc.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($filtered_feedbacks as $index => $fb): ?>
                                <tr>
                                    <td style="text-align: center; color: #fda4af; font-weight: 500;"><?= sprintf("%02d", $index + 1) ?></td>
                                    
                                    <!-- Giảng viên -->
                                    <td style="font-weight: 700; color: #4c0519;">
                                        <?= $fb['lecturer_name'] ?>
                                    </td>
                                    
                                    <!-- Số sao -->
                                    <td>
                                        <div class="stars-wrapper">
                                            <?= str_repeat('<i class="fa-solid fa-star"></i>', $fb['rating']) ?>
                                            <?= str_repeat('<i class="fa-regular fa-star star-empty"></i>', 5 - $fb['rating']) ?>
                                        </div>
                                    </td>
                                    
                                    <!-- Badge Phân loại -->
                                    <td><?= phanLoaiDanhGia($fb['rating']) ?></td>

                                    <!-- Ngày tạo -->
                                    <td style="color: #64748b; font-size: 0.6875rem;">
                                        <?= $fb['created_at'] ?? 'N/A' ?>
                                    </td>
                                    
                                    <!-- Nhận xét & Khung Trả Lời -->
                                    <td>
                                        <div style="color: #475569; font-style: italic; font-size: 0.8125rem;">
                                            "<?= !empty($fb['comment']) ? $fb['comment'] : 'Không có nhận xét' ?>"
                                        </div>

                                        <!-- Nếu đã có câu trả lời của Giáo viên -->
                                        <?php if (!empty($fb['reply'])): ?>
                                            <div class="reply-box">
                                                <div class="reply-title"><i class="fa-solid fa-reply"></i> Giảng viên phản hồi:</div>
                                                <div style="color: #1e293b;"><?= $fb['reply'] ?></div>
                                            </div>
                                        <?php else: ?>
                                            <!-- Form cho phép Giảng viên nhập câu trả lời -->
                                            <form method="POST" action="" class="reply-form">
                                                <input type="hidden" name="action" value="reply_feedback">
                                                <input type="hidden" name="fb_index" value="<?= $fb['original_index'] ?>">
                                                <input type="text" name="reply_text" class="reply-input" placeholder="Viết phản hồi tới sinh viên..." required>
                                                <button type="submit" class="btn-reply">Gửi</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>