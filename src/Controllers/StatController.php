<?php
namespace App\Controllers;

use App\Models\Stat;

class StatController {
    public function index() {
        $statModel = new Stat();
        $stats = $statModel->getOverviewStats();
        $teachers = $statModel->getTeacherStats();

        $total_appointments = $stats['total_appointments'] ?? 0;
        $total_pending      = $stats['total_pending'] ?? 0;
        $total_approved     = $stats['total_approved'] ?? 0;
        $total_rejected     = $stats['total_rejected'] ?? 0;
        $total_completed    = $stats['total_completed'] ?? 0;
        $total_cancelled    = $stats['total_cancelled'] ?? 0;

        $max_hen = 1;
        foreach ($teachers as $t) {
            if ($t['total_hen'] > $max_hen) {
                $max_hen = $t['total_hen'];
            }
        }

        require_once __DIR__ . '/../../views/admin/stats.php';
    }
}

function get_percentage($value, $total) {
    return $total > 0 ? round(($value / $total) * 100) : 0;
}