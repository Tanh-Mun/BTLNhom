<?php
namespace App\Controllers;

use App\Models\Review;

class ReviewController {
    private $reviewModel;

    public function __construct() {
        $this->reviewModel = new Review();
    }

    public function index() {
        $reviews = $this->reviewModel->getAll();
        $total_reviews = count($reviews);
        $avg_rating = $this->reviewModel->getAverageRating();

        require_once __DIR__ . '/../../views/admin/reviews.php';
    }

    public function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
            $this->reviewModel->delete((int)$_POST['review_id']);
        }
        header('Location: index.php?controller=review&action=index');
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: ../dangnhap.php');
        exit;
    }
}