<?php
session_start();

// --- KẾT NỐI SQL SERVER QUA PDO ---
$serverName = "localhost"; // hoặc "localhost\SQLEXPRESS"
$database   = "Thongkevadanhsachcuochen";
$username   = "sa"; 
$password   = "123456";

try {
    $pdo = new PDO("sqlsrv:Server=$serverName;Database=$database;TrustServerCertificate=true", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Lỗi kết nối SQL Server: " . $e->getMessage());
}

$lecturer_id = $_SESSION['lecturer_id'] ?? 1;
$message = "";
$message_type = "";

// Mảng lưu trữ lỗi từng trường & dữ liệu cũ để hiển thị lại
$errors = [];
$old_data = [
    'student_name' => '',
    'app_date'     => date('Y-m-d'),
    'app_time'     => '09:00'
];

// --- HÀM KHỞI TẠO BẢNG VÀ THÊM DỮ LIỆU MẪU TỰ ĐỘNG ---
function initDatabaseSchema($pdo) {
    $checkTable = $pdo->query("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'appointments'");
    if ($checkTable->fetch() === false) {
        $sqlCreate = "
            CREATE TABLE appointments (
                id INT IDENTITY(1,1) PRIMARY KEY,
                lecturer_id INT NOT NULL,
                student_name NVARCHAR(100) NOT NULL,
                appointment_date DATETIME NOT NULL,
                status NVARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'completed', 'rejected'))
            )
        ";
        $pdo->exec($sqlCreate);

        $sqlInsert = "
            INSERT INTO appointments (lecturer_id, student_name, appointment_date, status) VALUES
            (1, N'Nguyễn Văn An', '2026-08-16 09:00:00', 'pending'),
            (1, N'Trần Thị Bích', '2026-08-16 10:30:00', 'approved'),
            (1, N'Lê Hoàng Cường', '2026-08-15 14:00:00', 'completed'),
            (1, N'Phạm Minh Đức', '2026-08-14 15:30:00', 'rejected'),
            (1, N'Nguyễn Văn An', '2026-08-10 08:30:00', 'completed'),
            (1, N'Vũ Thị Mai', '2026-08-17 11:00:00', 'pending')
        ";
        $pdo->exec($sqlInsert);
    }
}

// Gọi hàm khởi tạo
initDatabaseSchema($pdo);

// --- 1. XỬ LÝ NHẬP LIỆU (THÊM CUỘC HẸN MỚI WITH SERVER-SIDE VALIDATION) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appointment'])) {
    
    // Chuẩn hóa dữ liệu đầu vào (Trim space)
    $student_name = trim($_POST['student_name'] ?? '');
    $app_date     = trim($_POST['app_date'] ?? '');
    $app_time     = trim($_POST['app_time'] ?? '');

    // Lưu lại dữ liệu cũ để re-fill vào form (tránh mất dữ liệu)
    $old_data['student_name'] = $student_name;
    $old_data['app_date']     = $app_date;
    $old_data['app_time']     = $app_time;

    // --- KIỂM TRA DỮ LIỆU (VALIDATION) ---
    // 1. Kiểm tra Tên Sinh Viên
    if (empty($student_name)) {
        $errors['student_name'] = "Vui lòng nhập tên sinh viên.";
    } elseif (mb_strlen($student_name) < 2 || mb_strlen($student_name) > 100) {
        $errors['student_name'] = "Tên sinh viên phải từ 2 đến 100 ký tự.";
    }

    // 2. Kiểm tra Ngày Hẹn (Định dạng YYYY-MM-DD)
    if (empty($app_date)) {
        $errors['app_date'] = "Vui lòng chọn ngày hẹn.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $app_date);
        if (!($d && $d->format('Y-m-d') === $app_date)) {
            $errors['app_date'] = "Định dạng ngày không hợp lệ.";
        }
    }

    // 3. Kiểm tra Giờ Hẹn (Định dạng HH:MM)
    if (empty($app_time)) {
        $errors['app_time'] = "Vui lòng chọn giờ hẹn.";
    } else {
        $t = DateTime::createFromFormat('H:i', $app_time);
        if (!($t && $t->format('H:i') === $app_time)) {
            $errors['app_time'] = "Định dạng giờ không hợp lệ.";
        }
    }

    // --- NẾU KHÔNG CÓ LỖI THÌ TIẾN HÀNH LƯU VÀO DB ---
    if (empty($errors)) {
        $formatted_date = $app_date . ' ' . $app_time . ':00';

        $stmtAdd = $pdo->prepare("INSERT INTO appointments (lecturer_id, student_name, appointment_date, status) VALUES (?, ?, ?, 'pending')");
        if ($stmtAdd->execute([$lecturer_id, $student_name, $formatted_date])) {
            $_SESSION['msg'] = "Tạo cuộc hẹn thành công!";
            $_SESSION['msg_type'] = "success";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $message = "Thêm thất bại, vui lòng thử lại!";
            $message_type = "error";
        }
    } else {
        $message = "Vui lòng kiểm tra lại thông tin nhập liệu!";
        $message_type = "error";
    }
}

// Hiển thị thông báo chuyển trang
if (isset($_SESSION['msg'])) {
    $message = $_SESSION['msg'];
    $message_type = $_SESSION['msg_type'];
    unset($_SESSION['msg'], $_SESSION['msg_type']);
}

// --- 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    $allowed_statuses = [
        'approve'  => 'approved',
        'reject'   => 'rejected',
        'complete' => 'completed'
    ];

    if (array_key_exists($action, $allowed_statuses)) {
        $new_status = $allowed_statuses[$action];
        $stmt = $pdo->prepare("UPDATE appointments SET status = ? WHERE id = ? AND lecturer_id = ?");
        $stmt->execute([$new_status, $id, $lecturer_id]);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    }
}

// --- 3. XEM THỐNG KÊ SỐ CUỘC HẸN ---
$stmtStats = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
    FROM appointments 
    WHERE lecturer_id = ?
");
$stmtStats->execute([$lecturer_id]);
$stats = $stmtStats->fetch(PDO::FETCH_ASSOC);

// --- 4. LẤY DANH SÁCH CUỘC HẸN ---
$stmtList = $pdo->prepare("SELECT id, student_name, CONVERT(VARCHAR(19), appointment_date, 120) AS appointment_date, status FROM appointments WHERE lecturer_id = ? ORDER BY appointment_date DESC");
$stmtList->execute([$lecturer_id]);
$appointments = $stmtList->fetchAll(PDO::FETCH_ASSOC);

// --- 5. THỐNG KÊ THEO SINH VIÊN ---
$stmtStudents = $pdo->prepare("
    SELECT 
        student_name,
        COUNT(*) as total_requests,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        MAX(CONVERT(VARCHAR(19), appointment_date, 120)) as last_appointment
    FROM appointments 
    WHERE lecturer_id = ?
    GROUP BY student_name
    ORDER BY total_requests DESC
");
$stmtStudents->execute([$lecturer_id]);
$student_summary = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Quản Lý Cuộc Hẹn</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-main: #fff5f7;
            --surface: #ffffff;
            --primary: #e83e8c;
            --primary-hover: #d63384;
            --text-main: #2d3748;
            --text-muted: #718096;
            --border-color: #f8d7da;
            
            --status-pending-bg: #fff3cd;
            --status-pending-text: #856404;
            --status-approved-bg: #e8f5e9;
            --status-approved-text: #2e7d32;
            --status-completed-bg: #e3f2fd;
            --status-completed-text: #1565c0;
            --status-rejected-bg: #ffebee;
            --status-rejected-text: #c62828;
            
            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-sm: 6px;
            --shadow: 0 10px 30px -5px rgba(232, 62, 140, 0.08);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-main); color: var(--text-main); padding: 40px 20px; }
        .container { max-width: 1100px; margin: 0 auto; }

        .page-header { margin-bottom: 30px; display: flex; align-items: center; justify-content: space-between; }
        .page-title { font-size: 26px; font-weight: 700; color: #4a154b; display: flex; align-items: center; gap: 12px; }
        .page-title i { color: var(--primary); background: var(--surface); padding: 10px; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); }

        .section-card { background: var(--surface); border-radius: var(--radius-lg); padding: 28px; box-shadow: var(--shadow); border: 1px solid rgba(232, 62, 140, 0.1); margin-bottom: 30px; }
        .section-title { font-size: 18px; font-weight: 600; margin-bottom: 20px; color: var(--text-main); display: flex; align-items: center; gap: 10px; }

        /* Style cho Form Nhập Liệu Mới */
        .form-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: start; }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
        }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 13px; font-weight: 600; color: var(--text-main); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); font-size: 14px; font-family: inherit; outline: none; transition: border-color 0.2s; background-color: #fff; }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(232, 62, 140, 0.15); }
        .form-control.is-invalid { border-color: #dc3545; background-color: #fff8f8; }
        
        /* Hiển thị thông báo lỗi bên dưới trường input */
        .error-feedback { color: #dc3545; font-size: 12px; font-weight: 500; margin-top: 2px; }

        .alert { padding: 12px 16px; border-radius: var(--radius-sm); font-size: 14px; font-weight: 500; margin-bottom: 20px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 18px; }
        .stat-card { background: #fff; padding: 20px; border-radius: var(--radius-md); border: 1px solid var(--border-color); transition: all 0.3s ease; position: relative; overflow: hidden; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(232, 62, 140, 0.12); border-color: var(--primary); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: var(--primary); }
        .stat-label { font-size: 13px; color: var(--text-muted); font-weight: 500; margin-bottom: 6px; text-transform: uppercase; }
        .stat-value { font-size: 28px; font-weight: 700; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: separate; border-spacing: 0; }
        th { background-color: var(--bg-main); color: var(--text-muted); font-weight: 600; font-size: 13px; text-transform: uppercase; padding: 14px 16px; text-align: left; border-bottom: 2px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid #f0f0f0; font-size: 14px; vertical-align: middle; }
        tr:hover td { background-color: #fff9fb; }

        .badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
        .badge-pending { background-color: var(--status-pending-bg); color: var(--status-pending-text); }
        .badge-approved { background-color: var(--status-approved-bg); color: var(--status-approved-text); }
        .badge-completed { background-color: var(--status-completed-bg); color: var(--status-completed-text); }
        .badge-rejected { background-color: var(--status-rejected-bg); color: var(--status-rejected-text); }

        .action-group { display: flex; gap: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 9px 16px; text-decoration: none; border-radius: var(--radius-sm); font-size: 13px; font-weight: 600; transition: all 0.2s ease; box-shadow: var(--shadow-sm); border: none; cursor: pointer; }
        .btn-submit { background-color: var(--primary); color: #fff; height: 42px; white-space: nowrap; margin-top: 21px; }
        .btn-submit:hover { background-color: var(--primary-hover); }
        .btn-approve { background-color: #28a745; color: #fff; }
        .btn-approve:hover { background-color: #218838; }
        .btn-reject { background-color: #dc3545; color: #fff; }
        .btn-reject:hover { background-color: #c82333; }
        .btn-complete { background-color: var(--primary); color: #fff; }
        .btn-complete:hover { background-color: var(--primary-hover); }
        .empty-action { color: var(--text-muted); padding-left: 10px; }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fa-solid fa-calendar-check"></i>
                Trang Quản Lý Cuộc Hẹn
            </h1>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <i class="fa-solid <?= $message_type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation' ?>"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Block 0: Form Nhập Liệu Tách Ngày & Giờ (Đã nâng cấp Validation & Giữ dữ liệu cũ) -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fa-solid fa-calendar-plus" style="color: var(--primary);"></i>
                Đặt cuộc hẹn mới
            </h2>
            <form action="" method="POST" novalidate>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="student_name">Tên Sinh Viên <span style="color:red;">*</span></label>
                        <input type="text" id="student_name" name="student_name" 
                               class="form-control <?= isset($errors['student_name']) ? 'is-invalid' : '' ?>" 
                               placeholder="Nhập tên sinh viên..." 
                               value="<?= htmlspecialchars($old_data['student_name']) ?>">
                        <?php if (isset($errors['student_name'])): ?>
                            <span class="error-feedback"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['student_name'] ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="app_date">Ngày Hẹn <span style="color:red;">*</span></label>
                        <input type="date" id="app_date" name="app_date" 
                               class="form-control <?= isset($errors['app_date']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($old_data['app_date']) ?>">
                        <?php if (isset($errors['app_date'])): ?>
                            <span class="error-feedback"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['app_date'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="app_time">Giờ Hẹn <span style="color:red;">*</span></label>
                        <input type="time" id="app_time" name="app_time" 
                               class="form-control <?= isset($errors['app_time']) ? 'is-invalid' : '' ?>" 
                               value="<?= htmlspecialchars($old_data['app_time']) ?>">
                        <?php if (isset($errors['app_time'])): ?>
                            <span class="error-feedback"><i class="fa-solid fa-circle-exclamation"></i> <?= $errors['app_time'] ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <button type="submit" name="add_appointment" class="btn btn-submit">
                            <i class="fa-solid fa-plus"></i> Tạo Lịch Hẹn
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Block 1: Thống kê -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fa-solid fa-chart-pie" style="color: var(--primary);"></i>
                Thống kê tổng quan
            </h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Tổng số</div>
                    <div class="stat-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Chờ duyệt</div>
                    <div class="stat-value" style="color: var(--status-pending-text);"><?= $stats['pending'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Đã duyệt</div>
                    <div class="stat-value" style="color: var(--status-approved-text);"><?= $stats['approved'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Hoàn thành</div>
                    <div class="stat-value" style="color: var(--status-completed-text);"><?= $stats['completed'] ?? 0 ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Từ chối</div>
                    <div class="stat-value" style="color: var(--status-rejected-text);"><?= $stats['rejected'] ?? 0 ?></div>
                </div>
            </div>
        </div>

        <!-- Block 2: Danh sách cuộc hẹn -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
                Danh sách cuộc hẹn
            </h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Sinh viên</th>
                            <th>Thời gian</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $app): ?>
                        <?php 
                            $status = strtolower($app['status'] ?? 'pending');
                            $badgeClass = 'badge-pending';
                            if ($status === 'approved') $badgeClass = 'badge-approved';
                            elseif ($status === 'completed') $badgeClass = 'badge-completed';
                            elseif ($status === 'rejected') $badgeClass = 'badge-rejected';
                        ?>
                        <tr>
                            <td><b>#<?= $app['id'] ?></b></td>
                            <td><strong><?= htmlspecialchars($app['student_name']) ?></strong></td>
                            <td><i class="fa-regular fa-clock" style="color: var(--primary); margin-right: 4px;"></i> <?= htmlspecialchars($app['appointment_date']) ?></td>
                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars(strtoupper($app['status'])) ?></span></td>
                            <td>
                                <div class="action-group">
                                    <?php if ($app['status'] === 'pending'): ?>
                                        <a href="?action=approve&id=<?= $app['id'] ?>" class="btn btn-approve"><i class="fa-solid fa-check"></i> Duyệt</a>
                                        <a href="?action=reject&id=<?= $app['id'] ?>" class="btn btn-reject"><i class="fa-solid fa-xmark"></i> Từ chối</a>
                                    <?php elseif ($app['status'] === 'approved'): ?>
                                        <a href="?action=complete&id=<?= $app['id'] ?>" class="btn btn-complete"><i class="fa-solid fa-check-double"></i> Đánh dấu xong</a>
                                    <?php else: ?>
                                        <span class="empty-action">&bull;&bull;&bull;</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Block 3: Thống kê sinh viên -->
        <div class="section-card">
            <h2 class="section-title">
                <i class="fa-solid fa-user-graduate" style="color: var(--primary);"></i>
                Tổng hợp sinh viên đặt hẹn
            </h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Tên Sinh Viên</th>
                            <th>Tổng Số Lần Đặt Hẹn</th>
                            <th>Số Lần Đã Hoàn Thành</th>
                            <th>Cuộc Hẹn Gần Nhất</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($student_summary)): ?>
                            <?php foreach ($student_summary as $student): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($student['student_name']) ?></strong></td>
                                <td><span class="badge badge-pending"><?= $student['total_requests'] ?> lần</span></td>
                                <td><span class="badge badge-completed"><?= $student['completed_count'] ?> lần</span></td>
                                <td><i class="fa-regular fa-calendar-days" style="color: var(--primary); margin-right: 4px;"></i> <?= htmlspecialchars($student['last_appointment']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--text-muted);">Chưa có dữ liệu sinh viên.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</body>
</html>