<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    while (ob_get_level()) { ob_end_clean(); }
    header('Content-Type: application/json; charset=utf-8');
    
    $errors = [];
//loại bỏ khoảng trống đầu cuối 
    $fullname = trim($_POST['fullname'] ?? '');  
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $avatar = $_FILES['avatar'] ?? null;


    if (empty($fullname)) {
        $errors[] = "Họ tên không được để trống.";
    }


    $contentLength = strlen($content);
    if (empty($content)) {
        $errors[] = "Nội dung không được để trống.";
    } elseif ($contentLength < 10 || $contentLength > 500) {
        $errors[] = "Nội dung phải từ 10 đến 500 ký tự.";
    }

    if (empty($email)) {
        $errors[] = "Email không được để trống.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email không đúng định dạng.";
    }


    if (!$avatar || $avatar['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = "Vui lòng chọn ảnh đại diện.";
    } elseif ($avatar['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Lỗi khi tải ảnh lên (Mã lỗi: " . $avatar['error'] . ").";
    } else {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExt = strtolower(pathinfo($avatar['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $allowedExtensions)) {
            $errors[] = "Ảnh đại diện phải thuộc định dạng: JPG, JPEG, PNG, GIF.";
        }
    }

    // Trả về kết quả JSON
    if (!empty($errors)) {
        echo json_encode(['status' => 'error', 'errors' => $errors], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['status' => 'success', 'message' => 'Gửi thông tin liên hệ thành công!'], JSON_UNESCAPED_UNICODE);
    }
    
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực hành: Form liên hệ</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f9; display: flex; justify-content: center; padding: 20px; }
        .contact-card { background: #fff; padding: 25px; border-radius: 8px; width: 420px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 8px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px; font-size: 14px; }
        .btn-submit { width: 100%; padding: 10px; background: #2b6cb0; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 15px; }
        .btn-submit:hover { background: #2c5282; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        small { color: #666; font-size: 12px; }
    </style>
</head>
<body>

<div class="contact-card">
    <h2 style="margin-top:0;">Liên hệ</h2>
    <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Vui lòng nhập đầy đủ thông tin bên dưới.</p>
    
    <div id="message"></div>

    <form id="contactForm">
        <div class="form-group">
            <label for="fullname">Họ tên</label>
            <input type="text" id="fullname" name="fullname" placeholder="">
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="">
        </div>

        <div class="form-group">
            <label for="subject">Chủ đề</label>
            <select id="subject" name="subject">
                <option value="Hỗ trợ kỹ thuật">Hỗ trợ kỹ thuật</option>
                <option value="Góp ý">Góp ý</option>
                <option value="Hợp tác">Hợp tác</option>
            </select>
        </div>

        <div class="form-group">
            <label for="content">Nội dung</label>
            <textarea id="content" name="content" rows="4" placeholder="Nhập nội dung liên hệ..."></textarea>
            <small>Nội dung phải từ 10 đến 500 ký tự</small>
        </div>

        <div class="form-group">
            <label for="avatar">Ảnh đại diện</label>
            <input type="file" id="avatar" name="avatar" accept="image/*">
        </div>

        <button type="submit" class="btn-submit">Gửi liên hệ</button>
    </form>
</div>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const messageDiv = document.getElementById('message');
    messageDiv.innerHTML = '<small>Đang xử lý...</small>';

    const formData = new FormData(this);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(async response => {
        const text = await response.text();
        try {
            return JSON.parse(text);
        } catch (err) {
            throw new Error(text || "Response từ server bị rỗng hoặc có lỗi PHP.");
        }
    })
    .then(result => {
        if (result.status === 'error') {
            messageDiv.innerHTML = `<div class="alert alert-error">${result.errors.join('<br>')}</div>`;
        } else if (result.status === 'success') {
            messageDiv.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
            document.getElementById('contactForm').reset();
        }
    })
    .catch(error => {
        messageDiv.innerHTML = `<div class="alert alert-error">Lỗi Server: ${error.message}</div>`;
    });
});
</script>

</body>
</html>