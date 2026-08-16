<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giới Thiệu Nhóm & Đề Tài Dự Kiến</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background-color: #f4f6f9;
            color: #333;
            line-height: 1.6;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        h1 {
            color: #1a73e8;
            font-size: 26px;
            border-bottom: 2px solid #1a73e8;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        h2 {
            color: #202124;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .member-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .member-item {
            background: #f8f9fa;
            border-left: 4px solid #1a73e8;
            padding: 12px 15px;
            border-radius: 0 8px 8px 0;
        }
        .member-name {
            font-weight: bold;
            color: #202124;
        }
        .member-id {
            font-size: 14px;
            color: #5f6368;
        }
        .topic-box {
            background: #e8f0fe;
            border: 1px solid #d2e3fc;
            padding: 20px;
            border-radius: 8px;
        }
        .topic-title {
            font-size: 18px;
            font-weight: bold;
            color: #174ea6;
            margin-bottom: 10px;
        }
        .feature-list {
            margin-left: 20px;
            margin-top: 10px;
        }
        .feature-list li {
            margin-bottom: 6px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>THÔNG TIN NHÓM DỰ ÁN</h1>
    <ul class="member-list">
        <li class="member-item">
            <div class="member-name">Thành viên 1: Trần Tuấn Anh</div>
            <div class="member-id">MSSV: 224001771</div>
            <a href="https://github.com/Tanh-Mun/php-practice-labs.git" target="_blank">bài tập cá nhân</a>
        </li>
        <li class="member-item">
            <div class="member-name">Thành viên 2: Đoàn Quang Huy</div>
            <div class="member-id">MSSV: 224001796</div>
            <a href="https://github.com/qhuyd-bee/QuangHuy.git" target="_blank">bài tập cá nhân</a>
        </li>
        <li class="member-item">
            <div class="member-name">Thành viên 3: Nguyễn Thu Quỳnh</div>
            <div class="member-id">MSSV: 224001827</div>
            <a href="https://github.com/NguyenQuynh06/LaptrinhWEB.git" target="_blank">bài tập cá nhân</a>
        </li>
        <li class="member-item">
            <div class="member-name">Thành viên 4: Đặng Thi Ngọc Anh </div>
            <div class="member-id">MSSV: 224001795</div>
            <a href="https://github.com/nqocanhh/Laptrinhweb.git" target="_blank">bài tập cá nhân</a>
        </li>
        <li class="member-item">
            <div class="member-name">Thành viên 5: Trần Thuỳ Dung</div>
            <div class="member-id">MSSV: 224001779 </div>
            <a href="https://github.com/trthdung/Laptrinhweb/tree/main" target="_blank">bài tập cá nhân</a>
        </li>
    </ul>

    <h2>ĐỀ TÀI DỰ KIẾN</h2>
    <div class="topic-box">
<div class="topic-title">CHỦ ĐỀ 5: HỆ THỐNG ĐẶT LỊCH TƯ VẤN/HẸN GẶP GIẢNG VIÊN</div>
        <p><strong>Mô tả tổng quan:</strong> Hệ thống hỗ trợ sinh viên chủ động tra cứu lịch rảnh và đặt lịch hẹn tư vấn học tập, hướng dẫn đồ án với giảng viên. Giúp tối ưu hóa thời gian và quy trình quản lý lịch làm việc của giảng viên.</p>
        
        <p style="margin-top: 12px;"><strong>Các chức năng chính dự kiến:</strong></p>
        <ul class="feature-list">
            <li><strong>Dành cho Sinh viên:</strong> Tra cứu danh sách giảng viên, xem khung giờ rảnh, đặt lịch hẹn và nhận thông báo xác nhận.</li>
            <li><strong>Dành cho Giảng viên:</strong> Cập nhật thời gian rảnh, duyệt hoặc từ chối lịch hẹn từ sinh viên, quản lý danh sách cuộc hẹn.</li>
            <li><strong>Dành cho Quản trị viên (Admin):</strong> Quản lý tài khoản giảng viên/sinh viên, thống kê số lượng buổi tư vấn.</li>
        </ul>
    </div>
</div>

</body>
</html>
