<?php
//date_default_timezone_set('Asia/Ho_Chi_Minh');
// Bắt đầu Session để lưu trữ danh sách phản hồi tạm thời
session_start();
//session_destroy();

// 1. TỔ CHỨC DỮ LIỆU BẰNG MẢNG
if (!isset($_SESSION['feedbacks'])) {
    $_SESSION['feedbacks'] = [
        [
            'lecturer_name' => 'TS. Trần Tuấn Anh',
            'rating'        => 5,
            'comment'       => 'Thầy tư vấn đồ án rất nhiệt tình và chi tiết.',
            'created_at'    => '2026-08-10 14:30'
        ],
        [
            'lecturer_name' => 'ThS. Nguyễn Thu Quỳnh',
            'rating'        => 4,
            'comment'       => 'Buổi tư vấn tạm ổn, giải đáp đúng trọng tâm.',
            'created_at'    => '2026-08-12 09:15'
        ]
    ];
}

$error_message = '';
$success_message = '';

// 2. HÀM TỰ ĐỊNH NGHĨA (Phân loại mức độ hài lòng)
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

// 3. CHỨC NĂNG XÓA PHẢN HỒI (Đã tích hợp làm sạch URL)
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_to_delete = intval($_GET['id']);
    
    if (isset($_SESSION['feedbacks'][$id_to_delete])) {
        unset($_SESSION['feedbacks'][$id_to_delete]);
        $_SESSION['feedbacks'] = array_values($_SESSION['feedbacks']);
        $_SESSION['success_message'] = "Đã xóa phản hồi thành công!";
    }

    $current_page = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: " . $current_page);
    exit();
}

// 4. TIẾP NHẬN VÀ XỬ LÝ DỮ LIỆU POST (Thêm mới & làm sạch URL)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lecturer_name = trim($_POST['lecturer_name'] ?? '');
    $rating        = intval($_POST['rating'] ?? 0);
    $comment       = trim($_POST['comment'] ?? '');

    if (empty($lecturer_name) || $rating < 1 || $rating > 5) {
        $error_message = "Vui lòng chọn tên Giảng viên và chọn đánh giá từ 1 đến 5 sao!";
    } else {
        $new_feedback = [
            'lecturer_name' => htmlspecialchars($lecturer_name),
            'rating'        => $rating,
            'comment'       => htmlspecialchars($comment),
            'created_at'    => date('Y-m-d H:i')
        ];

        $_SESSION['feedbacks'][] = $new_feedback;
        $_SESSION['success_message'] = "Gửi phản hồi thành công!";

        $current_page = strtok($_SERVER["REQUEST_URI"], '?');
        header("Location: " . $current_page);
        exit();
    }
}

// Lấy thông báo thành công từ Session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Đánh Giá Tư Vấn</title>
    <!-- FontAwesome Icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PURE CSS THUẦN - THEME PINK/ROSE -->
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
        .counter-badge { display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; font-weight: 500; color: #9f1239; background: #ffe4e6; padding: 0.5rem 1rem; border-radius: 0.75rem; width: fit-content; }
        
        /* Alerts */
        .alert { padding: 1rem; border-radius: 0.75rem; font-size: 0.875rem; display: flex; align-items: center; gap: 0.75rem; }
        .alert-error { background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; }

        /* Main Grid Layout */
        .main-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
        @media (min-width: 1024px) { .main-grid { grid-template-columns: 1fr 2fr; } }

        .section-title { font-size: 1rem; font-weight: 700; color: #881337; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .icon-theme { color: #e11d48; }

        /* Forms */
        .space-y-4 > * + * { margin-top: 1rem; }
        .form-group { display: flex; flex-direction: column; gap: 0.375rem; }
        .form-label { font-size: 0.75rem; font-weight: 700; color: #4c0519; }
        .text-required { color: #e11d48; }
        .form-control { width: 100%; font-size: 0.75rem; padding: 0.625rem; border: 1px solid #fecdd3; border-radius: 0.75rem; background: #fff1f2; outline: none; }
        .form-control:focus { border-color: #e11d48; box-shadow: 0 0 0 2px rgba(225, 29, 72, 0.2); }

        /* Star Rating */
        .star-rating { background: #fff1f2; padding: 0.5rem; border: 1px solid #fecdd3; border-radius: 0.75rem; display: flex; flex-direction: row-reverse; justify-content: center; gap: 0.25rem; }
        .star-rating input { display: none; }
        .star-rating label { font-size: 1.75rem; color: #fca5a5; cursor: pointer; transition: color 0.2s; }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color: #e11d48; }

        /* Buttons */
        .btn-submit { width: 100%; background: #e11d48; color: #ffffff; font-weight: 600; font-size: 0.75rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem; box-shadow: 0 4px 6px -1px rgba(225, 29, 72, 0.25); transition: background 0.2s; }
        .btn-submit:hover { background: #be123c; }

        /* Table Structure */
        .table-container { border-radius: 0.75rem; border: 1px solid #fecdd3; overflow: hidden; }
        .custom-table { width: 100%; text-align: left; font-size: 0.75rem; border-collapse: collapse; table-layout: fixed; }
        .custom-table th { background: #ffe4e6; color: #881337; font-weight: 600; padding: 0.75rem 0.5rem; border-bottom: 1px solid #fecdd3; }
        .custom-table td { padding: 0.75rem 0.5rem; border-bottom: 1px solid #fff1f2; vertical-align: top; }
        .custom-table tr:hover { background: rgba(255, 241, 242, 0.6); }

        /* Table Column Sizing */
        .col-stt { width: 2.5rem; text-align: center; }
        .col-lecturer { width: 8rem; }
        .col-rating { width: 6rem; }
        .col-badge { width: 7rem; }
        .col-action { width: 5rem; text-align: center; }

        /* Cells */
        .td-stt { color: #fda4af; font-weight: 500; text-align: center; }
        .td-lecturer { font-weight: 700; color: #4c0519; overflow-wrap: break-word; }
        .td-comment { color: #475569; font-style: italic; overflow-wrap: break-word; word-break: break-word; line-height: 1.5; }

        /* Stars Display */
        .stars-wrapper { color: #e11d48; font-size: 0.6875rem; display: inline-flex; gap: 0.125rem; }
        .star-empty { color: #fecdd3; }

        /* Action Buttons */
        .btn-delete { display: inline-flex; align-items: center; gap: 0.25rem; color: #e11d48; background: #ffe4e6; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 0.375rem; font-size: 0.6875rem; text-decoration: none; transition: background 0.2s; }
        .btn-delete:hover { background: #fecdd3; color: #9f1239; }

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
    </style>
</head>
<body>

    <div class="container">
        
        <!-- Header -->
        <div class="header-card">
            <div>
                <h1 class="header-title">Đánh Giá & Phản Hồi Tư Vấn</h1>
                <p class="header-subtitle">Hệ thống tiếp nhận và quản lý ý kiến phản hồi của sinh viên</p>
            </div>
            <div class="counter-badge">
                <i class="fa-solid fa-database icon-theme"></i>
                Tổng số phản hồi: <strong><?= count($_SESSION['feedbacks']) ?></strong>
            </div>
        </div>

        <!-- Thông báo Alert -->
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span><?= $error_message ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span><?= $success_message ?></span>
            </div>
        <?php endif; ?>

        <!-- Bố cục 2 Cột chính -->
        <div class="main-grid">

            <!-- CỘT 1: FORM NHẬP DỮ LIỆU -->
            <div class="card" style="height: fit-content;">
                <h2 class="section-title">
                    <i class="fa-solid fa-pen-to-square icon-theme"></i> Gửi đánh giá mới
                </h2>

                <form action="" method="POST" class="space-y-4">
                    <div class="form-group">
                        <label for="lecturer_name" class="form-label">1. Tên Giảng viên <span class="text-required">*</span></label>
                        <select name="lecturer_name" id="lecturer_name" required class="form-control">
                            <option value="">-- Chọn Giảng viên --</option>
                            <option value="TS. Trần Tuấn Anh">TS. Trần Tuấn Anh</option>
                            <option value="ThS. Nguyễn Thu Quỳnh">ThS. Nguyễn Thu Quỳnh</option>
                            <option value="Trịnh Quang Vinh">Trịnh Quang Vinh</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">2. Chất lượng buổi tư vấn <span class="text-required">*</span></label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" required /><label for="star5" title="5 sao">★</label>
                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="4 sao">★</label>
                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="3 sao">★</label>
                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="2 sao">★</label>
                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="1 sao">★</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="comment" class="form-label">3. Nhận xét & Góp ý</label>
                        <textarea name="comment" id="comment" rows="3" class="form-control" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> Gửi Phản Hồi
                    </button>
                </form>
            </div>

            <!-- CỘT 2: BẢNG HIỂN THỊ DỮ LIỆU -->
            <div class="card space-y-4">
                <h2 class="section-title">
                    <i class="fa-solid fa-list-check icon-theme"></i> Danh sách phản hồi đã ghi nhận
                </h2>

                <div class="table-container">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th class="col-stt">STT</th>
                                <th class="col-lecturer">Giảng viên</th>
                                <th class="col-rating">Đánh giá</th>
                                <th class="col-badge">Phân loại</th>
                                <th>Nhận xét</th>
                                <!-- <th style="width: 7rem;">Ngày gửi</th> -->
                                <th class="col-action">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($_SESSION['feedbacks'])): ?>
                                <tr>
                                    <td colspan="6" style="padding: 1.5rem; text-align: center; color: #fda4af; font-style: italic;">
                                        Chưa có phản hồi nào trong danh sách.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($_SESSION['feedbacks'] as $index => $fb): ?>
                                    <tr>
                                        <td class="td-stt"><?= sprintf("%02d", $index + 1) ?></td>
                                        
                                        <!-- Giảng viên -->
                                        <td class="td-lecturer">
                                            <?= $fb['lecturer_name'] ?>
                                        </td>
                                        
                                        <!-- Đánh giá -->
                                        <td>
                                            <div class="stars-wrapper">
                                                <?= str_repeat('<i class="fa-solid fa-star"></i>', $fb['rating']) ?>
                                                <?= str_repeat('<i class="fa-regular fa-star star-empty"></i>', 5 - $fb['rating']) ?>
                                            </div>
                                        </td>
                                        
                                        <!-- Phân loại -->
                                        <td><?= phanLoaiDanhGia($fb['rating']) ?></td>
                                        
                                        <!-- Nhận xét -->
                                        <td class="td-comment">
                                            "<?= !empty($fb['comment']) ? $fb['comment'] : 'Không có' ?>"
                                        </td>

                                        <!-- Ngày gửi -->
                                        <!-- <td style="color: #64748b; font-size: 0.6875rem;">
                                            <?= isset($fb['created_at']) ? $fb['created_at'] : date('Y-m-d H:i') ?>
                                        </td> -->
                                        
                                        <!-- Hành động -->
                                        <td style="text-align: center;">
                                            <a href="?action=delete&id=<?= $index ?>" 
                                               onclick="return confirm('Bạn có chắc chắn muốn xóa phản hồi này không?');"
                                               class="btn-delete">
                                                <i class="fa-solid fa-trash-can"></i> Xóa
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>