<?php
require_once(__DIR__ . '/models/UserModel.php');
$userModel = new UserModel();

// Nếu là request POST (AJAX login)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $user = $userModel->auth($username, $password);
        if ($user) {
            echo json_encode([
                "status" => "success",
                "user" => [
                    "id" => $user[0]['id'],
                    "name" => $user[0]['name'],
                    "email" => $user[0]['email']
                ]
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Login failed"
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid input"
        ]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Login</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
<?php include 'views/header.php'?>

<div class="container">
    <div id="loginbox" style="margin-top:50px;" 
         class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="panel-title">Login</div>
                <div style="float:right; font-size: 80%; position: relative; top:-10px">
                    <a href="#">Forgot password?</a>
                </div>
            </div>

            <div style="padding-top:30px" class="panel-body">
                <form id="loginForm" class="form-horizontal" role="form">
                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" 
                               name="username" placeholder="username or email">
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" 
                               name="password" placeholder="password">
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                            Don't have an account!
                            <a href="form_user.php">Sign Up Here</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelector("#loginForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    let formData = new FormData(this);

    let response = await fetch("login.php", {
        method: "POST",
        body: formData
    });

    let data = await response.json();

    if (data.status === "success") {
        // Lưu user vào localStorage
        localStorage.setItem("user", JSON.stringify(data.user));
        alert("Login thành công!");

        // Chuyển sang trang danh sách user
        window.location.href = "list_users.php";
    } else {
        alert(data.message);
    }
});
</script>

</body>
</html>
