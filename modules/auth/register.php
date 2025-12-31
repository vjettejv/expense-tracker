<?php
session_start();
require_once '../../config/db.php';

$error_msg = '';
$success_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        $error_msg = "Vui lòng điền đầy đủ thông tin!";
    } else {
        $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if ($check->num_rows > 0) {
            $error_msg = "Tên đăng nhập hoặc Email đã tồn tại!";
        } else {
            // Mã hóa mật khẩu MD5
            $pass_hash = md5($password);
            $sql = "INSERT INTO users (full_name, username, email, password, role, status) 
                    VALUES ('$full_name', '$username', '$email', '$pass_hash', 'user', 'active')";

            if ($conn->query($sql) === TRUE) {
                $success_msg = "Đăng ký thành công! Đang chuyển hướng...";
                header("refresh:2;url=login.php");
            } else {
                $error_msg = "Lỗi: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | Expense Tracker</title>
    <link rel="stylesheet" href="../../assets/css/register.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">
</head>
<body>

    <div id="main">
        <!-- LOGO (CHỮ) -->
        <div id="logo">
            <a href="../../index.php" class="logo-text">ExpenseTracker</a>
        </div>
        
        <h3>Đăng ký để quản lý chi tiêu của bạn.</h3>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <input type="text" name="full_name" class="form-control" placeholder="Tên đầy đủ" required>
            <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>

            <button type="submit" class="btn-submit">Đăng ký</button>
        </form>
    </div>

    <div class="login-link-box">
        <p class="login-link">Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>

</body>

</html>