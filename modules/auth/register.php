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
    <style>
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #fafafa;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
        }

        .main-box {
            background: white;
            border: 1px solid #dbdbdb;
            width: 350px;
            padding: 40px;
            text-align: center;
        }

        .logo {
            width: 175px;
            margin-bottom: 20px;
        }

        input {
            width: 100%;
            padding: 9px 8px;
            margin-bottom: 6px;
            border: 1px solid #dbdbdb;
            border-radius: 3px;
            background: #fafafa;
            font-size: 12px;
        }

        button {
            width: 100%;
            background: #0095f6;
            color: white;
            border: none;
            padding: 5px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            height: 30px;
        }

        button:hover {
            background: #1877f2;
        }

        .alert {
            padding: 10px;
            margin-bottom: 10px;
            font-size: 13px;
            border-radius: 4px;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .link {
            margin-top: 20px;
            font-size: 14px;
        }

        .link a {
            color: #0095f6;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="main-box">
        <img class="logo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Instagram_logo.svg/1280px-Instagram_logo.svg.png" alt="">
        <h3 style="color: #8e8e8e; font-size: 17px; font-weight: 600; margin-bottom: 20px;">Đăng ký để quản lý chi tiêu của bạn.</h3>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger"><?= $error_msg ?></div>
        <?php endif; ?>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success"><?= $success_msg ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="full_name" placeholder="Tên đầy đủ" required>
            <input type="text" name="username" placeholder="Tên đăng nhập" required>
            <input type="password" name="password" placeholder="Mật khẩu" required>

            <button type="submit">Đăng ký</button>
        </form>
    </div>

    <div class="main-box" style="margin-top: 10px; padding: 20px;">
        <p class="link">Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>

</body>

</html>