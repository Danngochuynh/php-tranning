<?php
require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = NULL;
$id = NULL;

if (!empty($_GET['id'])) {
    $id = $_GET['id'];
    $user = $userModel->findUserById($id);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User profile</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>

<div class="container">
    <script>
    const loggedUser = JSON.parse(localStorage.getItem("user"));
    if (!loggedUser) {
        alert("Bạn chưa đăng nhập!");
        window.location.href = "login.php";
    }
    </script>

    <?php if ($user) { ?>
        <div class="alert alert-warning" role="alert">
            User profile
        </div>
        <div class="form-group">
            <label for="name">Name</label>
            <span><?php echo $user[0]['name'] ?></span>
        </div>
        <div class="form-group">
            <label for="fullname">Fullname</label>
            <span><?php echo $user[0]['fullname'] ?></span>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <span><?php echo $user[0]['email'] ?></span>
        </div>
    <?php } else { ?>
        <div class="alert alert-success" role="alert">
            User not found!
        </div>
    <?php } ?>
</div>
</body>
</html>
