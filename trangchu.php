<?php
session_start();

function renderStars($rating, $maxStars = 5) {
    echo '<div class="star-rating">';
    for ($i = 1; $i <= $maxStars; $i++) {
        if ($i <= $rating) {
            echo '<span class="star filled">★</span>';
        } else {
            echo '<span class="star empty">☆</span>';
        }
    }
    echo '</div>';
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDULINGO - Hệ thống đặt lịch tư vấn giảng viên</title>
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-hover: #b0124a;
            --bg-light: #fff5f8;
            --text-dark: #333;
            --text-muted: #666;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #fcfcfc;
            color: var(--text-dark);
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 15px;
        }

        /* Header */
        .header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-box {
            background: white;
            color: var(--primary-color);
            font-weight: bold;
            padding: 5px 8px;
            border-radius: 4px;
        }

        .header-auth {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-btn {
            background: white;
            color: var(--primary-color);
            padding: 8px 18px;
            text-decoration: none;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
        }

        .auth-btn.register {
            background: transparent;
            color: white;
            border: 1px solid white;
        }

        /* Hero */
        .hero {
            background-color: var(--primary-color);
            color: white;
            margin: 30px auto 20px;
            padding: 40px;
            border-radius: 12px;
        }

        .hero h1 {
            margin: 10px 0;
            font-size: 28px;
        }

        .hero p {
            font-size: 14px;
            opacity: 0.9;
            max-width: 600px;
            line-height: 1.5;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: white;
            color: var(--primary-color);
            padding: 12px 24px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        /* Thống kê */
        .stats-grid {
            display: grid !important;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 15px;
            margin: 25px 0 50px;
        }

        .stat-card {
            background: white;
            border: 1px solid #f0cfd9;
            border-radius: 10px;
            padding: 20px 15px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }

        .stat-number {
            font-size: 26px;
            font-weight: bold;
            color: var(--primary-color);
        }

        .stat-label {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 5px;
        }

        /* Titles */
        .section-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .section-subtitle {
            color: var(--primary-color);
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 22px;
            margin: 8px 0;
            text-transform: uppercase;
        }

        .section-desc {
            font-size: 14px;
            color: var(--text-muted);
        }

        /* Grids */
        .lecturers-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 60px;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 60px;
        }

        .card {
            background: white;
            border: 1px solid #f0cfd9;
            border-radius: 12px;
            padding: 20px 15px;
            text-align: center;
        }

        .avatar {
            width: 45px;
            height: 45px;
            background: #fde8ef;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            margin: 0 auto 12px;
        }

        .card h3 {
            font-size: 15px;
            margin-bottom: 4px;
        }

        .card .dept {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 12px;
        }

        .tag {
            background: var(--bg-light);
            color: var(--primary-color);
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
            margin-bottom: 15px;
        }

        .btn-link {
            color: var(--primary-color);
            font-size: 13px;
            text-decoration: none;
            font-weight: bold;
        }

        /* Ratings */
        .star-rating {
            display: inline-flex;
            gap: 3px;
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .star.filled {
            color: var(--primary-color);
        }
        
        .star.empty {
            color: #f8bbd0;
        }

        .review-text {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.4;
            margin-bottom: 15px;
            text-align: left;
        }

        /* Footer */
        .footer {
            background: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: 50px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 30px;
            padding-bottom: 30px;
        }

        .footer h4 {
            font-size: 13px;
            margin-bottom: 15px;
            text-transform: uppercase;
        }

        .footer ul {
            list-style: none;
        }

        .footer ul li {
            margin-bottom: 8px;
        }

        .footer ul li a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 13px;
        }

        .social-icons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .social-icon {
            width: 32px;
            height: 32px;
            background: white;
            color: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <header class="header">
        <div class="logo-container">
            <div class="logo-box">ABC</div>
            <div>
                <strong style="font-size: 18px;">EDULINGO</strong><br>
                <small style="font-size: 11px; opacity: 0.8;">Hệ thống đặt lịch tư vấn giảng viên</small>
            </div>
        </div>
        <div class="header-auth">
            <a href="dangnhap.php" class="auth-btn">Đăng nhập</a>
            <a href="dangky.php" class="auth-btn register">Đăng ký</a>
        </div>
    </header>

    <div class="container">
        <!-- Hero Section -->
        <section class="hero">
            <small style="text-transform: uppercase; letter-spacing: 1px;">Hệ thống đặt lịch tư vấn giảng viên</small>
            <h1>Kết nối với giảng viên ngoại ngữ<br>chỉ trong vài giây</h1>
            <p>Tìm giảng viên theo ngôn ngữ và chủ đề tư vấn, xem khung giờ còn trống theo thời gian thực và gửi yêu cầu đặt lịch — tất cả trong một nơi.</p>
            <a href="timvadatlich.php" class="btn-primary">Tìm giảng viên & đặt lịch</a>
        </section>

        <!-- Thống kê -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">10</div>
                <div class="stat-label">Giảng viên</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">5</div>
                <div class="stat-label">Ngôn ngữ</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">35</div>
                <div class="stat-label">Buổi đã hoàn thành</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">4.9</div>
                <div class="stat-label">Đánh giá trung bình</div>
            </div>
        </div>

        <!-- Giảng viên nổi bật -->
        <section>
            <div class="section-header">
                <div class="section-subtitle">Giảng viên nổi bật</div>
                <h2 class="section-title">Chọn giảng viên theo ngôn ngữ bạn cần</h2>
                <p class="section-desc">Mỗi giảng viên tự cập nhật khung giờ trống, nên lịch bạn thấy luôn là lịch mới nhất</p>
            </div>

            <div class="lecturers-grid">
                <div class="card">
                    <div class="avatar">N</div>
                    <h3>Nguyễn Thảo Vy</h3>
                    <div class="dept">Tiếng Anh</div>
                    <div class="tag">IELTS Speaking</div><br>
                    <a href="#" class="btn-link">Xem lịch</a>
                </div>
                <div class="card">
                    <div class="avatar">T</div>
                    <h3>Trần Minh Đức</h3>
                    <div class="dept">Tiếng Pháp</div>
                    <div class="tag">Phát âm cơ bản</div><br>
                    <a href="#" class="btn-link">Xem lịch</a>
                </div>
                <div class="card">
                    <div class="avatar">Y</div>
                    <h3>Yamada Haruko</h3>
                    <div class="dept">Tiếng Nhật</div>
                    <div class="tag">Giao tiếp N3</div><br>
                    <a href="#" class="btn-link">Xem lịch</a>
                </div>
                <div class="card">
                    <div class="avatar">L</div>
                    <h3>Lý Gia Hân</h3>
                    <div class="dept">Tiếng Trung</div>
                    <div class="tag">HSK 4</div><br>
                    <a href="#" class="btn-link">Xem lịch</a>
                </div>
            </div>
        </section>

        <!-- Sinh viên nói gì -->
        <section>
            <div class="section-header">
                <div class="section-subtitle">Sinh viên nói gì</div>
                <h2 class="section-title">Nhận xét từ sinh viên đã đặt lịch</h2>
                <p class="section-desc">Những buổi tư vấn gần đây được đánh giá bởi chính sinh viên đã tham gia</p>
            </div>

            <div class="reviews-grid">
                <div class="card">
                    <?php renderStars(5); ?>
                    <p class="review-text">Đặt lịch rất nhanh, thấy ngay khung giờ trống nên không phải nhắn tin lại. Buổi tư vấn phát âm giúp mình sửa được nhiều lỗi.</p>
                    <div class="avatar">T</div>
                    <h3>Trần Lan Anh</h3>
                    <div class="dept">Sinh viên Tiếng Nhật, năm 2</div>
                </div>
                <div class="card">
                    <?php renderStars(5); ?>
                    <p class="review-text">Mình cần tư vấn gấp trước buổi phỏng vấn học bổng, đặt được lịch trong ngày với thầy chuyên IELTS Speaking. Rất tiện.</p>
                    <div class="avatar">N</div>
                    <h3>Ngô Minh Khôi</h3>
                    <div class="dept">Sinh viên Tiếng Anh, năm 3</div>
                </div>
                <div class="card">
                    <?php renderStars(4); ?>
                    <p class="review-text">Giao diện dễ dùng, lọc theo ngôn ngữ và chủ đề rất nhanh. Chỉ mong có nhiều khung giờ vào tối muộn hơn để chọn.</p>
                    <div class="avatar">Đ</div>
                    <h3>Đỗ Hải Phương</h3>
                    <div class="dept">Sinh viên Tiếng Trung, năm 2</div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container footer-grid">
            <div>
                <div class="logo-container" style="margin-bottom: 10px;">
                    <div class="logo-box">ABC</div>
                    <strong>EDULINGO</strong>
                </div>
                <p style="font-size: 12px; opacity: 0.8; line-height: 1.5;">Hệ thống đặt lịch tư vấn giảng viên ngoại ngữ, giúp sinh viên tìm và đặt buổi gặp chỉ trong vài bước.</p>
                <div class="social-icons">
                    <div class="social-icon">FB</div>
                    <div class="social-icon">ZL</div>
                    <div class="social-icon">EM</div>
                </div>
            </div>
            <div>
                <h4>Khám phá</h4>
                <ul>
                    <li><a href="#">Tìm giảng viên</a></li>
                    <li><a href="#">Đánh giá</a></li>
                    <li><a href="#">Ngôn ngữ hỗ trợ</a></li>
                    <li><a href="#">Câu hỏi thường gặp</a></li>
                </ul>
            </div>
            <div>
                <h4>Dành cho giảng viên</h4>
                <ul>
                    <li><a href="#">Đăng ký giảng dạy</a></li>
                    <li><a href="#">Quản lý khung giờ</a></li>
                </ul>
            </div>
            <div>
                <h4>Nhận thông báo</h4>
                <p style="font-size: 12px; opacity: 0.8;">Nhận tin khi có giảng viên mới hoặc khung giờ mới mở.</p>
            </div>
        </div>
    </footer>

</body>
</html>