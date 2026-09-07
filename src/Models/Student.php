<?php
namespace App\Models;

use App\Models\Database;
use PDO;

class Student {
    private $pdo;

    public function __construct() {
        $this->pdo = (new Database())->getConnection();
    }

    public function getAll($searchKeyword = '') {
        $sql = "SELECT u.id, u.fullname AS name, u.username, u.email, u.phone,
                       sp.student_code, sp.faculty, sp.major, sp.bio
                FROM users u
                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                WHERE u.role = 'student' OR u.role = 'hocvien' OR u.role = 'user'";

        if ($searchKeyword !== '') {
            $sql .= " AND (u.fullname LIKE :kw OR u.username LIKE :kw OR u.email LIKE :kw OR u.phone LIKE :kw OR sp.student_code LIKE :kw)";
        }

        $stmt = $this->pdo->prepare($sql);
        if ($searchKeyword !== '') {
            $stmt->bindValue(':kw', '%' . $searchKeyword . '%');
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}