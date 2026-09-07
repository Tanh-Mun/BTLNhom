<?php
namespace App\Models;

use App\Models\Database;
use PDO;
use Exception;

class Lecturer {
    private $pdo;

    public function __construct() {
        $this->pdo = (new Database())->getConnection();
    }

    public function getAll($searchKeyword = '') {
        $cols = [];
        try {
            $cols = $this->pdo->query("DESCRIBE lecturer_profiles")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Exception $e) {}

        $has_faculty = in_array('faculty', $cols) || in_array('department', $cols);
        $faculty_col = in_array('faculty', $cols) ? 'faculty' : (in_array('department', $cols) ? 'department' : '');
        $has_specialty = in_array('specialty', $cols);
        $has_bio = in_array('bio', $cols);

        $selectFaculty   = $has_faculty ? "lp.$faculty_col AS dept" : "NULL AS dept";
        $selectSpecialty = $has_specialty ? "lp.specialty AS desc_text" : "NULL AS desc_text";
        $selectBio       = $has_bio ? "lp.bio AS intro" : "NULL AS intro";

        $sql = "SELECT u.id, u.fullname AS name, u.username, u.email, u.phone, 
                       $selectFaculty, $selectSpecialty, $selectBio
                FROM users u
                LEFT JOIN lecturer_profiles lp ON u.id = lp.user_id
                WHERE u.role = 'lecturer' OR u.role = 'teacher' OR u.role = 'gv'";

        if ($searchKeyword !== '') {
            $whereSearch = ["u.fullname LIKE :kw", "u.username LIKE :kw"];
            if ($has_faculty) { $whereSearch[] = "lp.$faculty_col LIKE :kw"; }
            if ($has_specialty) { $whereSearch[] = "lp.specialty LIKE :kw"; }
            $sql .= " AND (" . implode(" OR ", $whereSearch) . ")";
        }

        $stmt = $this->pdo->prepare($sql);
        if ($searchKeyword !== '') {
            $stmt->bindValue(':kw', '%' . $searchKeyword . '%');
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function add($data) {
        try {
            $this->pdo->beginTransaction();

            $stmtCheck = $this->pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmtCheck->execute([$data['username'], $data['email']]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Tên đăng nhập hoặc Email này đã tồn tại!");
            }

            $stmtUser = $this->pdo->prepare("INSERT INTO users (fullname, username, email, phone, password, role) VALUES (?, ?, ?, ?, ?, 'lecturer')");
            $stmtUser->execute([$data['fullname'], $data['username'], $data['email'], $data['phone'], $data['password']]);
            $new_user_id = $this->pdo->lastInsertId();

            $stmtProfile = $this->pdo->prepare("INSERT INTO lecturer_profiles (user_id, faculty, specialty, bio) VALUES (?, ?, ?, ?)");
            $stmtProfile->execute([$new_user_id, $data['faculty'], $data['specialty'], $data['bio']]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        try {
            $this->pdo->beginTransaction();

            $stmtUser = $this->pdo->prepare("UPDATE users SET fullname = ?, username = ?, email = ?, phone = ? WHERE id = ?");
            $stmtUser->execute([$data['fullname'], $data['username'], $data['email'], $data['phone'], $id]);

            $stmtCheck = $this->pdo->prepare("SELECT id FROM lecturer_profiles WHERE user_id = ?");
            $stmtCheck->execute([$id]);

            if ($stmtCheck->fetch()) {
                $stmtProfile = $this->pdo->prepare("UPDATE lecturer_profiles SET faculty = ?, specialty = ?, bio = ? WHERE user_id = ?");
                $stmtProfile->execute([$data['faculty'], $data['specialty'], $data['bio'], $id]);
            } else {
                $stmtProfile = $this->pdo->prepare("INSERT INTO lecturer_profiles (user_id, faculty, specialty, bio) VALUES (?, ?, ?, ?)");
                $stmtProfile->execute([$id, $data['faculty'], $data['specialty'], $data['bio']]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        try {
            $this->pdo->beginTransaction();
            $stmtProfile = $this->pdo->prepare("DELETE FROM lecturer_profiles WHERE user_id = ?");
            $stmtProfile->execute([$id]);

            $stmtUser = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmtUser->execute([$id]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}