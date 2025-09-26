<?php

// Basic CSP header (tune according to needs)
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'");

// Secure session cookie params BEFORE session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '', // set domain if needed
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start the session
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();


$message = '';
$username_value = '';

// Very basic login attempt throttling (improve for production)
$maxAttempts = 10;
$lockMinutes = 15;

if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_lock_until'])) $_SESSION['login_lock_until'] = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['submit'])) {
    if (time() < (int)$_SESSION['login_lock_until']) {
        $message = 'Quá nhiều lần thử. Vui lòng thử lại sau một thời gian.';
    } else {
        $username_value = isset($_POST['username']) ? trim($_POST['username']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';

        if ($username_value === '' || $password === '') {
            $message = 'Vui lòng nhập đầy đủ thông tin.';
        } else {
            // call auth (UserModel::auth must use prepared statements & password_verify)
            $user = $userModel->auth($username_value, $password);

            if ($user) {
                // reset attempts
                $_SESSION['login_attempts'] = 0;
                $_SESSION['login_lock_until'] = 0;

                // successful login
                session_regenerate_id(true); // prevent session fixation
                $_SESSION['id'] = $user[0]['id'];
                // optionally store user name/role in session (non-sensitive)
                header('Location: list_users.php');
                exit;
            } else {
                $_SESSION['login_attempts'] += 1;
                if ($_SESSION['login_attempts'] >= $maxAttempts) {
                    $_SESSION['login_lock_until'] = time() + ($lockMinutes * 60);
                    $message = 'Quá nhiều lần thử. Vui lòng thử lại sau ' . $lockMinutes . ' phút.';
                } else {
                    $message = 'Tên đăng nhập hoặc mật khẩu không đúng.';
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>

<body>
    <?php include 'views/header.php' ?>

    <div class="container">
        <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="panel-title">Login</div>
                    <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
                </div>

                <div style="padding-top:30px" class="panel-body">
                    <?php if ($message): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" class="form-horizontal" role="form" autocomplete="off" novalidate>
                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                            <input id="login-username" type="text" class="form-control" name="username"
                                value="<?php echo htmlspecialchars($username_value, ENT_QUOTES, 'UTF-8'); ?>"
                                placeholder="username or email" required>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                            <input id="login-password" type="password" class="form-control" name="password" placeholder="password" required>
                        </div>

                        <div class="margin-bottom-25">
                            <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                            <label for="remember"> Remember Me</label>
                        </div>

                        <div class="margin-bottom-25 input-group">
                            <div class="col-sm-12 controls">
                                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
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

</body>

</html>