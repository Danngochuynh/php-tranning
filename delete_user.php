<?php
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }

    $id = $_POST['id'] ?? null;
    if ($id) {
        $deleted = $userModel->deleteUserById($id);
        if ($deleted) {
            header('Location: list_users.php?status=deleted');
            exit;
        } else {
            header('Location: list_users.php?status=error');
            exit;
        }
    }
}

header('Location: list_users.php?status=invalid');
exit;
