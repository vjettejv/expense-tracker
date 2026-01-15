<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$base_url = BASE_URL; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Chi tiêu</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/header.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">
            <a href="<?php echo $base_url; ?>/admin/dashboard.php">Expense Tracker</a>
        </div>
        
        <div class="nav-links">
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <!-- MENU CHỨC NĂNG -->
                <a href="<?php echo $base_url; ?>/modules/categories/index.php">Danh mục</a>
                <a href="<?php echo $base_url; ?>/modules/wallets/index.php">Ví tiền</a>
                <a href="<?php echo $base_url; ?>/modules/budgets/index.php">Ngân sách</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="<?php echo $base_url; ?>/admin/admin_report.php">Giao dịch</a>
                <?php else: ?>
                    <div class="nav-dropdown">
                        <a href="<?php echo $base_url; ?>/modules/transactions/index.php" class="dropdown-toggle">Giao dịch &#9662;</a>
                        <div class="dropdown-menu">
                            <a href="<?php echo $base_url; ?>/modules/transactions/user_add.php">+ Thêm Giao dịch</a>
                            <a href="<?php echo $base_url; ?>/modules/transactions/user_history.php">📜 Lịch sử Giao dịch</a>
                        </div>
                    </div>
                <?php endif; ?>
                
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