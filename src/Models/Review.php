<?php
namespace App\Models;

use PDO;

class Review {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT r.*, COALESCE(u.fullname, r.teacher_name, 'Giảng viên') AS display_teacher_name
            FROM reviews r
            LEFT JOIN appointments a ON r.appointment_id = a.appointment_id
            LEFT JOIN time_slots ts ON a.slot_id = ts.slot_id
            LEFT JOIN users u ON ts.lecturer_id = u.id
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getAverageRating(): float {
        $stmt = $this->db->query("SELECT AVG(rating) as avg_score FROM reviews");
        $row = $stmt->fetch();
        return ($row && $row['avg_score'] !== null) ? round((float)$row['avg_score'], 1) : 5.0;
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        return $stmt->execute([$id]);
    }
}