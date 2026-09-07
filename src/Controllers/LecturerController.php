<?php
namespace App\Controllers;

use App\Models\Lecturer;
use Exception;

class LecturerController {
    public function index() {
        $lecturerModel = new Lecturer();
        $msg = '';
        $msg_type = 'success';

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            try {
                if ($action === 'delete_lecturer') {
                    $lecturerModel->delete(intval($_POST['lecturer_id']));
                    $msg = "Đã xóa giảng viên thành công!";
                } elseif ($action === 'add_lecturer') {
                    $lecturerModel->add([
                        'fullname'  => trim($_POST['fullname']),
                        'username'  => trim($_POST['username']),
                        'email'     => trim($_POST['email']),
                        'phone'     => trim($_POST['phone']),
                        'faculty'   => trim($_POST['faculty']),
                        'specialty' => trim($_POST['specialty']),
                        'bio'       => trim($_POST['bio']),
                        'password'  => !empty($_POST['password']) ? md5(trim($_POST['password'])) : md5('123456')
                    ]);
                    $msg = "Thêm giảng viên mới thành công!";
                } elseif ($action === 'update_lecturer') {
                    $lecturerModel->update(intval($_POST['lecturer_id']), [
                        'fullname'  => trim($_POST['fullname']),
                        'username'  => trim($_POST['username']),
                        'email'     => trim($_POST['email']),
                        'phone'     => trim($_POST['phone']),
                        'faculty'   => trim($_POST['faculty']),
                        'specialty' => trim($_POST['specialty']),
                        'bio'       => trim($_POST['bio'])
                    ]);
                    $msg = "Cập nhật hồ sơ giảng viên thành công!";
                }
            } catch (Exception $e) {
                $msg = "Lỗi: " . $e->getMessage();
                $msg_type = 'danger';
            }
        }

        $searchKeyword = $_GET['keyword'] ?? '';
        $teachers_db = $lecturerModel->getAll($searchKeyword);

        $filteredTeachers = [];
        foreach ($teachers_db as $t) {
            $firstChar = !empty($t['name']) ? mb_strtoupper(mb_substr($t['name'], 0, 1, 'UTF-8'), 'UTF-8') : 'G';
            $filteredTeachers[] = [
                'id'       => $t['id'],
                'name'     => $t['name'] ?? '',
                'username' => $t['username'] ?? '',
                'avatar'   => $firstChar,
                'dept'     => $t['dept'] ?? 'Chưa cập nhật',
                'email'    => $t['email'] ?? '',
                'phone'    => $t['phone'] ?? '',
                'desc'     => $t['desc_text'] ?? 'Chưa cập nhật chuyên môn',
                'intro'    => $t['intro'] ?? ''
            ];
        }

        require_once __DIR__ . '/../../views/admin/lecturers.php';
    }
}