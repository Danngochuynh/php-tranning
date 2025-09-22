<?php
require_once 'models/UserModel.php';
$userModel = new UserModel();

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $userModel->deleteUserById($id);
}
header('location: list_users.php');
exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Delete user</title>
</head>
<body>
<script>
const loggedUser = JSON.parse(localStorage.getItem("user"));
if (!loggedUser) {
    alert("Bạn chưa đăng nhập!");
    window.location.href = "login.php";
}
</script>
</body>
</html>
