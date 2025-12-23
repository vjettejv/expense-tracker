<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chi tiêu</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f0f2f5;
        }

        /* Thanh Menu trên cùng */
        .navbar {
            background: white;
            height: 60px;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-size: 20px;
            text-decoration: none;
            font-weight: bold;
            color: #333;
        }

        .nav-links {
            display: flex;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
            font-weight: 600;
        }


        .user-name {
            margin-right: 10px;
            font-size: 14px;
            color: #555;
        }

        .btn-logout {
            color: #dc3545 !important;
        }

        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            min-height: 400px;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div>
            <a class="logo" href="/expense-tracker/index.php">Expense Tracker</a>
        </div>
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-name">
                    Xin chào, <b><?php echo $_SESSION['full_name']; ?></b>
                </span>



                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="/expense-tracker/admin/dashboard.php" style="color: blue;">Quản trị</a>
                <?php endif; ?>

                <a href="/expense-tracker/modules/auth/logout.php" class="btn-logout" onclick="return confirm('Bạn muốn đăng xuất?');">Đăng xuất</a>

            <?php else: ?>

                <a href="/expense-tracker/modules/auth/login.php">Đăng nhập</a>
                <a href="/expense-tracker/modules/auth/register.php">Đăng ký</a>

            <?php endif; ?>
        </div>
    </nav>

    <div class="container">