<?php

// Basic security headers (optional)
header("Content-Security-Policy: default-src 'self'; script-src 'self'; object-src 'none'");

// Secure session cookie params BEFORE session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);
// Start the session
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

$user = NULL; //Add new user
$_id = NULL;
$message = '';

// validate and cast id from GET
if (!empty($_GET['id'])) {
    $_id = (int) $_GET['id'];
    if ($_id > 0) {
        $user = $userModel->findUserById($_id); // should return [row] or null
    } else {
        $_id = null;
    }
}

if (!empty($_POST['submit'])) {
    // sanitize inputs server-side (basic)
    $name_in = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password_in = isset($_POST['password']) ? $_POST['password'] : '';

    // basic validation
    if ($name_in === '') {
        $message = 'Name is required.';
    } else {
        // Prepare data for model (do not echo raw $_POST back)
        $data = ['name' => $name_in];

        if ($password_in !== '') {
            // hash password before saving (not XSS but important)
            $data['password_hash'] = password_hash($password_in, PASSWORD_DEFAULT);
        }

        if (!empty($_id)) {
            $data['id'] = (int)$_id;
            $userModel->updateUser($data);
        } else {
            $userModel->insertUser($data);
        }
        header('location: list_users.php');
        exit;
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

        <?php if ($user || !isset($_id)) { ?>
            <div class="alert alert-warning" role="alert">
                User form
            </div>
            <?php if (!empty($message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo isset($_id) ? (int)$_id : ''; ?>">

                <div class="form-group">
                    <label for="name">Name</label>
                    <!-- escape value output to prevent XSS -->
                    <input class="form-control" name="name" placeholder="Name"
                        value="<?php echo isset($user[0]['name']) ? htmlspecialchars($user[0]['name'], ENT_QUOTES, 'UTF-8') : htmlspecialchars(isset($name_in) ? $name_in : '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Password">
                </div>

                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-success" role="alert">
                User not found!
            </div>
        <?php } ?>
    </div>
</body>

</html>