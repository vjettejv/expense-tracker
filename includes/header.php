<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = '/expense-tracker'; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chi tiêu</title>
    <link rel="stylesheet" href="/expense-tracker/assets/css/header.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <a href="<?php echo $base_url; ?>/index.php">Expense Tracker</a>
        </div>
        
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <!-- MENU CHỨC NĂNG -->
                <a href="<?php echo $base_url; ?>/modules/categories/index.php">Danh mục</a>
                <a href="<?php echo $base_url; ?>/modules/wallets/index.php">Ví tiền</a>
                <a href="<?php echo $base_url; ?>/modules/budgets/index.php">Ngân sách</a>
                <a href="<?php echo $base_url; ?>/modules/transactions/index.php">Giao dịch</a>
                
                <!-- MENU USER -->
                <span style="border-left: 1px solid #ccc; height: 20px; margin-left: 20px; margin-right: 10px;"></span>
                
                <a href="<?php echo $base_url; ?>/modules/users/profile.php">
                    Xin chào, <b><?php echo $_SESSION['full_name']; ?></b>
                </a>

                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="<?php echo $base_url; ?>/admin/dashboard.php" style="color: blue;">Admin</a>
                <?php endif; ?>

                <a href="<?php echo $base_url; ?>/modules/auth/logout.php" class="btn-logout" onclick="return confirm('Đăng xuất?');">Thoát</a>

            <?php else: ?>
                <a href="<?php echo $base_url; ?>/modules/auth/login.php">Đăng nhập</a>
                <a href="<?php echo $base_url; ?>/modules/auth/register.php">Đăng ký</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">