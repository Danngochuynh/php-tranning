<?php
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Chỉ xử lý POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Kiểm tra CSRF token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $id = $_POST['id'] ?? null;
    if ($id) {
        $userModel->deleteUserById($id); // Delete user
    }
}

header('location: list_users.php');
exit;