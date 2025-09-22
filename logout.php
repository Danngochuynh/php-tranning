<?php
session_start();


$_SESSION = [];

// Xoá cookie PHPSESSID trên trình duyệt
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Huỷ session trong Redis
session_destroy();

// Redirect về trang login
header('Location: login.php');
exit;
