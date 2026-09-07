<?php
namespace App\Models;

use App\Models\Database;
use PDO;

class Stat {
    private $pdo;

    public function __construct() {
        $this->pdo = (new Database())->getConnection();
    }

    public function getOverviewStats() {
        $sql = "SELECT 
            COUNT(*) AS total_appointments,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS total_approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS total_rejected,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS total_completed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS total_cancelled
        FROM appointments";
        
        return $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
    }

    public function getTeacherStats() {
        $sql = "SELECT 
            u.id AS lecturer_id,
            u.fullname AS lecturer_name,
            COUNT(a.status) AS total_hen,
            SUM(CASE WHEN a.status = 'completed' THEN 1 ELSE 0 END) AS total_xong,
            SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) AS total_cho
        FROM users u
        LEFT JOIN time_slots ts ON u.id = ts.lecturer_id
        LEFT JOIN appointments a ON ts.slot_id = a.slot_id
        WHERE u.role = 'lecturer' OR u.role = 'teacher' OR u.role = 'gv'
        GROUP BY u.id, u.fullname
        ORDER BY total_hen DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}