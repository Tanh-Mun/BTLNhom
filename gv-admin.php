<?php
session_start();
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: dangnhap.php');
    exit;
}
$teachers = [
    [
        'id' => 1,
        'name' => 'Nguyễn Thảo Vy',
        'avatar' => 'N',
        'dept' => 'Khoa Tiếng Anh',
        'email' => 'vy.nguyen@lingua.edu',
        'phone' => '0901234567',
        'desc' => 'Giao tiếp học thuật, IELTS Speaking, phát âm và thuyết trình',
        'intro' => 'Giảng viên tiếng Anh, phụ trách tư vấn học thuật và kỹ năng giao tiếp.'
    ],
    [
        'id' => 2,
        'name' => 'Trần Minh Đức',
        'avatar' => 'T',
        'dept' => 'Khoa Tiếng Pháp',
        'email' => 'duc.tran@lingua.edu',
        'phone' => '0902345678',
        'desc' => 'DELF A2-B2, giao tiếp tiếng Pháp, luyện phát âm',
        'intro' => 'Giảng viên tiếng Pháp, chuyên luyện thi DELF và giao tiếp.'
    ],
    [
        'id' => 3,
        'name' => 'Yamada Haruko',
        'avatar' => 'Y',
        'dept' => 'Khoa Tiếng Nhật',
        'email' => 'haruko.yamada@lingua.edu',
        'phone' => '0903456789',
        'desc' => 'JLPT N3-N1, hội thoại, văn hóa doanh nghiệp Nhật Bản',
        'intro' => 'Giảng viên người Nhật, chuyên tiếng Nhật thương mại và JLPT.'
    ],
    [
        'id' => 4,
        'name' => 'Lý Gia Hân',
        'avatar' => 'L',
        'dept' => 'Khoa Tiếng Trung',
        'email' => 'han.ly@lingua.edu',
        'phone' => '0904567890',
        'desc' => 'HSK 3-6, giao tiếp thương mại, khẩu ngữ và chữ Hàn',
        'intro' => 'Giảng viên tiếng Trung, luyện thi HSK và khẩu ngữ thực tế.'
    ]
];

$searchKeyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$filteredTeachers = $teachers;

if ($searchKeyword !== '') {
    $filteredTeachers = array_filter($teachers, function($t) use ($searchKeyword) {
        $keyword = mb_strtolower($searchKeyword, 'UTF-8');
        return mb_strpos(mb_strtolower($t['name'], 'UTF-8'), $keyword) !== false ||
               mb_strpos(mb_strtolower($t['dept'], 'UTF-8'), $keyword) !== false ||
               mb_strpos(mb_strtolower($t['desc'], 'UTF-8'), $keyword) !== false;
    });
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Giảng Viên - EDULINGO Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #d81b60;
            --primary-light: #fdf2f5;
            --border-color: #fce4ec;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
        body { background-color: var(--primary-light); display: flex; flex-direction: column; min-height: 100vh; }
        
        .header { background-color: var(--primary-color); color: white; padding: 12px 40px; display: flex; align-items: center; justify-content: space-between; }
        .logo-container { display: flex; align-items: center; gap: 10px; }
        .logo-box { background: white; color: var(--primary-color); font-weight: bold; padding: 4px 8px; border-radius: 4px; font-size: 14px; }
        .user-dropdown { position: relative; display: inline-block; }
        .btn-profile { color: white; text-decoration: none; font-size: 13px; font-weight: bold; background: rgba(255, 255, 255, 0.18); padding: 7px 16px; border-radius: 20px; display: flex; align-items: center; gap: 6px; border: none; cursor: pointer; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 110%; background-color: white; min-width: 150px; box-shadow: 0px 4px 12px rgba(0,0,0,0.15); border-radius: 8px; overflow: hidden; z-index: 1000; }
        .dropdown-menu.show { display: block; }
        .dropdown-item { color: var(--primary-color); padding: 10px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 13px; }
        .dropdown-item:hover { background-color: var(--primary-light); }

        .main-container { max-width: 1000px; margin: 0 auto; padding: 25px 20px 50px 20px; width: 100%; flex: 1; }
        .nav-tabs { display: inline-flex; background: white; padding: 4px; border-radius: 30px; border: 1px solid var(--border-color); margin-bottom: 25px; }
        .tab-btn { padding: 8px 18px; border-radius: 20px; border: none; background: transparent; color: var(--primary-color); font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; text-decoration: none; }
        .tab-btn.main-active { background: var(--primary-color); color: white; }

        .search-bar-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .search-input-form { position: relative; width: 350px; display: flex; align-items: center; }
        .search-input-form input { width: 100%; padding: 10px 15px 10px 35px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 13px; background: white; color: var(--primary-color); }
        .search-input-form input::placeholder { color: var(--primary-color); opacity: 0.7; }
        .search-input-form i.fa-magnifying-glass { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--primary-color); font-size: 13px; opacity: 0.7; }
        .teacher-count { font-size: 13px; color: var(--primary-color); font-weight: 500; }

        .teacher-list { display: flex; flex-direction: column; gap: 15px; }
        .teacher-card { background: white; border: 1px solid var(--border-color); border-radius: 15px; padding: 18px 25px; display: flex; align-items: center; justify-content: space-between; }
        
        .teacher-left { display: flex; align-items: center; gap: 15px; }
        .teacher-avatar { width: 45px; height: 45px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; }
        
        .teacher-info h4 { color: var(--primary-color); font-size: 15px; margin-bottom: 3px; }
        .teacher-info .dept-email { font-size: 12px; color: #666; margin-bottom: 3px; }
        .teacher-info .desc { font-size: 12px; color: var(--primary-color); opacity: 0.85; }

        .teacher-actions { display: flex; align-items: center; gap: 10px; }
        .btn-view-profile { background: white; color: var(--primary-color); border: 1px solid var(--border-color); padding: 7px 16px; border-radius: 20px; font-size: 12px; font-weight: bold; text-decoration: none; cursor: pointer; }
        .btn-view-profile:hover { background: var(--primary-light); }
        .btn-delete { background: white; color: var(--primary-color); border: 1px solid var(--border-color); width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 13px; cursor: pointer; text-decoration: none; }
        .btn-delete:hover { background: var(--primary-light); }

        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.4); z-index: 2000; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-box { background: white; width: 720px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.15); display: flex; flex-direction: column; overflow: hidden; max-height: 90vh; }
        
        .modal-header { padding: 18px 25px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; background: white; }
        .modal-header-left { display: flex; align-items: center; gap: 12px; }
        .modal-avatar { width: 40px; height: 40px; background: var(--primary-light); color: var(--primary-color); border: 1px solid var(--border-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 15px; }
        .modal-title-info h4 { color: var(--primary-color); font-size: 14px; margin-bottom: 2px; }
        .modal-title-info p { font-size: 11px; color: #666; }
        .modal-close { background: transparent; border: 1px solid var(--border-color); width: 30px; height: 30px; border-radius: 50%; color: var(--primary-color); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 13px; }
        .modal-close:hover { background: var(--primary-light); }

        .modal-tabs { display: flex; gap: 20px; padding: 0 25px; border-bottom: 1px solid var(--border-color); background: white; }
        .modal-tab-btn { padding: 12px 0; background: none; border: none; font-size: 13px; font-weight: bold; color: #888; cursor: pointer; position: relative; }
        .modal-tab-btn.active { color: var(--primary-color); }
        .modal-tab-btn.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 2px; background: var(--primary-color); }

        .modal-body { padding: 25px; overflow-y: auto; background: white; flex: 1; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: span 2; }
        .form-group label { font-size: 12px; color: var(--primary-color); font-weight: 500; }
        .form-control { padding: 10px 14px; border-radius: 20px; border: 1px solid var(--border-color); outline: none; font-size: 12px; color: var(--primary-color); background: white; width: 100%; }
        textarea.form-control { border-radius: 12px; resize: none; height: 75px; }
        
        .modal-footer { padding: 15px 25px; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end; gap: 10px; background: white; }
        .btn-action-outline { background: white; color: var(--primary-color); border: 1px solid var(--border-color); padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-action-outline:hover { background: var(--primary-light); }
        .btn-action-primary { background: var(--primary-color); color: white; border: none; padding: 8px 18px; border-radius: 20px; font-size: 12px; font-weight: bold; cursor: pointer; }

        .schedule-date-nav { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }
        .date-picker-box { display: flex; align-items: center; gap: 8px; border: 1px solid var(--border-color); padding: 6px 14px; border-radius: 20px; font-size: 12px; color: var(--primary-color); }
        .date-nav-btn { width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border-color); background: white; color: var(--primary-color); cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 11px; }
        
        .slot-creation-card { border: 1px solid var(--border-color); border-radius: 12px; padding: 15px; margin-bottom: 20px; background: var(--primary-light); display: none; }
        .slot-creation-card.show { display: block; }
        .slots-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .slot-card-item { border: 1px solid var(--border-color); border-radius: 10px; padding: 12px 15px; background: white; display: flex; justify-content: space-between; align-items: center; }
        .slot-time-info strong { font-size: 12px; color: var(--primary-color); display: block; margin-bottom: 3px; }
        .slot-time-info p { font-size: 11px; color: #555; }
        .slot-status-badge { padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-available { background: #e2f0d9; color: #385723; }
        .slot-actions { display: flex; gap: 6px; }
        .slot-action-btn { width: 26px; height: 26px; border-radius: 50%; border: 1px solid var(--border-color); background: white; color: var(--primary-color); cursor: pointer; font-size: 11px; display: flex; align-items: center; justify-content: center; }

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
            <button class="btn-profile" id="userMenuBtn">
                <i class="fa-regular fa-user"></i> admin
            </button>
            <div class="dropdown-menu" id="userDropdown">
                <a href="?action=logout" class="dropdown-item" style="color: #d81b60;"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a>
            </div>
        </div>
    </header>

    <div class="main-container">
        <div class="nav-tabs">
            <a href="tk-admin.php" class="tab-btn"><i class="fa-solid fa-chart-pie"></i> Thống kê</a>
            <a href="gv-admin.php" class="tab-btn main-active"><i class="fa-regular fa-user"></i> Giảng viên</a>
            <a href="dg-admin.php" class="tab-btn"><i class="fa-regular fa-star"></i> Đánh giá</a>
        </div>

        <div class="search-bar-container">
            <div class="search-input-form">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" value="<?= htmlspecialchars($searchKeyword) ?>" placeholder="Tìm theo tên, khoa hoặc chuyên môn...">
            </div>
            <div class="teacher-count" id="teacherCount"><?= count($filteredTeachers) ?> giảng viên</div>
        </div>

        <div class="teacher-list" id="teacherList">
            <?php if (count($filteredTeachers) > 0): ?>
                <?php foreach ($filteredTeachers as $t): ?>
                <div class="teacher-card">
                    <div class="teacher-left">
                        <div class="teacher-avatar"><?= $t['avatar'] ?></div>
                        <div class="teacher-info">
                            <h4><?= htmlspecialchars($t['name']) ?></h4>
                            <div class="dept-email"><?= htmlspecialchars($t['dept']) ?> - <?= htmlspecialchars($t['email']) ?></div>
                            <div class="desc"><?= htmlspecialchars($t['desc']) ?></div>
                        </div>
                    </div>
                    <div class="teacher-actions">
                        <button class="btn-view-profile" onclick="openTeacherModal(<?= htmlspecialchars(json_encode($t), ENT_QUOTES, 'UTF-8') ?>)">Xem hồ sơ & lịch</button>
                        <a href="#" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa giảng viên này?')"><i class="fa-regular fa-trash-can"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 30px; color: var(--primary-color); background: white; border-radius: 15px; border: 1px solid var(--border-color); font-size: 13px;">
                    Không tìm thấy giảng viên phù hợp.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal-overlay" id="teacherModal">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-header-left">
                    <div class="modal-avatar" id="modalAvatar">N</div>
                    <div class="modal-title-info">
                        <small style="font-size: 10px; color: #888; text-transform: uppercase;">Hồ sơ giảng viên</small>
                        <h4 id="modalTeacherName">Nguyễn Thảo Vy</h4>
                        <p id="modalTeacherSub">Khoa Tiếng Anh - vy.nguyen@lingua.edu</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeTeacherModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="modal-tabs">
                <button class="modal-tab-btn active" onclick="switchModalTab(event, 'tabProfile')">Hồ sơ cá nhân</button>
                <button class="modal-tab-btn" onclick="switchModalTab(event, 'tabSchedule')">Lịch làm việc</button>
            </div>

            <div class="modal-body">
                <div class="tab-content active" id="tabProfile">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Họ và tên</label>
                            <input type="text" class="form-control" id="inputName" value="Nguyễn Thảo Vy">
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" class="form-control" id="inputEmail" value="vy.nguyen@lingua.edu">
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Số điện thoại</label>
                            <input type="text" class="form-control" id="inputPhone" value="0901234567">
                        </div>
                        <div class="form-group">
                            <label>Khoa Ngoại Ngữ</label>
                            <select class="form-control" id="inputDept">
                                <option>Khoa Tiếng Anh</option>
                                <option>Khoa Tiếng Pháp</option>
                                <option>Khoa Tiếng Trung</option>
                                <option>Khoa Tiếng Nhật</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group full" style="margin-bottom: 15px;">
                        <label>Chuyên môn / chủ đề tư vấn</label>
                        <input type="text" class="form-control" id="inputDesc" value="Giao tiếp học thuật, IELTS Speaking, phát âm và thuyết trình.">
                    </div>
                    <div class="form-group full">
                        <label>Giới thiệu</label>
                        <textarea class="form-control" id="inputIntro">Giảng viên tiếng Anh, phụ trách tư vấn học thuật và kỹ năng giao tiếp.</textarea>
                    </div>
                </div>

                <div class="tab-content" id="tabSchedule">
                    <p style="font-size: 12px; color: #666; margin-bottom: 15px;">Chọn ngày để xem các khung giờ tư vấn.</p>
                    <div class="schedule-date-nav">
                        <div class="date-picker-box">
                            <button class="date-nav-btn"><i class="fa-solid fa-chevron-left"></i></button>
                            <span>24/8/2026</span>
                            <i class="fa-regular fa-calendar"></i>
                            <button class="date-nav-btn"><i class="fa-solid fa-chevron-right"></i></button>
                        </div>
                        <button class="btn-action-primary" onclick="toggleAddSlotCard()" style="font-size: 11px; padding: 7px 14px;">+ Thêm Khung giờ</button>
                    </div>

                    <div class="slot-creation-card" id="addSlotCard">
                        <p style="font-size: 12px; font-weight: bold; color: var(--primary-color); margin-bottom: 10px;">Thêm khung giờ mới</p>
                        <div class="form-grid" style="margin-bottom: 10px;">
                            <div class="form-group">
                                <label>Ngày</label>
                                <input type="text" class="form-control" value="24/8/2026">
                            </div>
                            <div class="form-group">
                                <label>Trạng thái</label>
                                <select class="form-control">
                                    <option>Còn trống</option>
                                    <option>Đã đặt</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-grid" style="margin-bottom: 10px;">
                            <div class="form-group">
                                <label>Giờ bắt đầu</label>
                                <input type="text" class="form-control" value="8:00 AM">
                            </div>
                            <div class="form-group">
                                <label>Giờ kết thúc</label>
                                <input type="text" class="form-control" value="9:00 AM">
                            </div>
                        </div>
                        <div class="form-group full" style="margin-bottom: 10px;">
                            <label>Ghi chú</label>
                            <input type="text" class="form-control" placeholder="Nhập ghi chú (nếu có)">
                        </div>
                        <div style="text-align: right;">
                            <button class="btn-action-primary" style="font-size: 11px; padding: 6px 14px;">Lưu khung giờ</button>
                        </div>
                    </div>

                    <p style="font-size: 12px; font-weight: bold; color: #333; margin-bottom: 10px;">Thứ Hai, 24/8/2026</p>
                    <div class="slots-grid">
                        <div class="slot-card-item">
                            <div class="slot-time-info">
                                <strong>8:00 - 9:00</strong>
                                <p>Sinh viên Nguyễn An đã đặt lịch</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <span class="slot-status-badge badge-pending">Đã duyệt</span>
                                <div class="slot-actions">
                                    <button class="slot-action-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="slot-action-btn"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="slot-card-item">
                            <div class="slot-time-info">
                                <strong>10:00 - 11:00</strong>
                                <p>Đã tư vấn xong</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <span class="slot-status-badge badge-completed">Đã xong</span>
                                <div class="slot-actions">
                                    <button class="slot-action-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="slot-action-btn"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="slot-card-item">
                            <div class="slot-time-info">
                                <strong>13:30 - 14:30</strong>
                                <p>Có thể đặt lịch</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <span class="slot-status-badge badge-available">Còn trống</span>
                                <div class="slot-actions">
                                    <button class="slot-action-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="slot-action-btn"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="slot-card-item">
                            <div class="slot-time-info">
                                <strong>15:30 - 16:30</strong>
                                <p>Có thể đặt lịch</p>
                            </div>
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                <span class="slot-status-badge badge-available">Còn trống</span>
                                <div class="slot-actions">
                                    <button class="slot-action-btn"><i class="fa-solid fa-pen"></i></button>
                                    <button class="slot-action-btn"><i class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn-action-outline"><i class="fa-solid fa-pen" style="font-size: 10px;"></i> Sửa</button>
                <button class="btn-action-primary">Lưu hồ sơ</button>
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
        const allTeachers = <?= json_encode($teachers, JSON_UNESCAPED_UNICODE) ?>;

        const userMenuBtn = document.getElementById('userMenuBtn');
        const userDropdown = document.getElementById('userDropdown');
        userMenuBtn.addEventListener('click', (e) => { e.stopPropagation(); userDropdown.classList.toggle('show'); });
        document.addEventListener('click', () => { userDropdown.classList.remove('show'); });

        const searchInput = document.getElementById('searchInput');
        const teacherList = document.getElementById('teacherList');
        const teacherCount = document.getElementById('teacherCount');

        searchInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            
            const filtered = allTeachers.filter(t => {
                return t.name.toLowerCase().includes(keyword) ||
                       t.dept.toLowerCase().includes(keyword) ||
                       t.desc.toLowerCase().includes(keyword);
            });

            teacherCount.innerText = filtered.length + ' giảng viên';

            if (filtered.length > 0) {
                let html = '';
                filtered.forEach(t => {
                    html += `
                    <div class="teacher-card">
                        <div class="teacher-left">
                            <div class="teacher-avatar">${t.avatar}</div>
                            <div class="teacher-info">
                                <h4>${escapeHtml(t.name)}</h4>
                                <div class="dept-email">${escapeHtml(t.dept)} - ${escapeHtml(t.email)}</div>
                                <div class="desc">${escapeHtml(t.desc)}</div>
                            </div>
                        </div>
                        <div class="teacher-actions">
                            <button class="btn-view-profile" onclick='openTeacherModal(${JSON.stringify(t)})'>Xem hồ sơ & lịch</button>
                            <a href="#" class="btn-delete" onclick="return confirm('Bạn có chắc muốn xóa giảng viên này?')"><i class="fa-regular fa-trash-can"></i></a>
                        </div>
                    </div>`;
                });
                teacherList.innerHTML = html;
            } else {
                teacherList.innerHTML = `
                <div style="text-align: center; padding: 30px; color: var(--primary-color); background: white; border-radius: 15px; border: 1px solid var(--border-color); font-size: 13px;">
                    Không tìm thấy giảng viên phù hợp.
                </div>`;
            }
        });

        function escapeHtml(text) {
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function openTeacherModal(teacher) {
            document.getElementById('modalAvatar').innerText = teacher.avatar;
            document.getElementById('modalTeacherName').innerText = teacher.name;
            document.getElementById('modalTeacherSub').innerText = teacher.dept + ' - ' + teacher.email;
            
            document.getElementById('inputName').value = teacher.name;
            document.getElementById('inputEmail').value = teacher.email;
            document.getElementById('inputPhone').value = teacher.phone || '0901234567';
            document.getElementById('inputDept').value = teacher.dept;
            document.getElementById('inputDesc').value = teacher.desc;
            document.getElementById('inputIntro').value = teacher.intro;

            document.getElementById('teacherModal').classList.add('show');
        }

        function closeTeacherModal() {
            document.getElementById('teacherModal').classList.remove('show');
        }

        function switchModalTab(event, tabId) {
            document.querySelectorAll('.modal-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            event.currentTarget.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        }

        function toggleAddSlotCard() {
            const card = document.getElementById('addSlotCard');
            card.classList.toggle('show');
        }

        document.getElementById('teacherModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTeacherModal();
            }
        });
    </script>
</body>
</html>