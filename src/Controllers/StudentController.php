<?php
namespace App\Controllers;

use App\Models\Student;

class StudentController {
    public function index() {
        $searchKeyword = $_GET['keyword'] ?? '';
        $studentModel = new Student();
        $students_db = $studentModel->getAll($searchKeyword);

        $students = [];
        foreach ($students_db as $s) {
            $firstChar = !empty($s['name']) ? mb_strtoupper(mb_substr($s['name'], 0, 1, 'UTF-8'), 'UTF-8') : 'H';
            $students[] = [
                'id'           => $s['id'],
                'name'         => $s['name'] ?? '',
                'username'     => $s['username'] ?? '',
                'avatar'       => $firstChar,
                'email'        => $s['email'] ?? '',
                'phone'        => $s['phone'] ?? '',
                'student_code' => $s['student_code'] ?? '',
                'faculty'      => $s['faculty'] ?? '',
                'major'        => $s['major'] ?? '',
                'bio'          => $s['bio'] ?? ''
            ];
        }

        require_once __DIR__ . '/../../views/admin/students.php';
    }
}