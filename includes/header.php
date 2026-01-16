<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Định nghĩa Base URL để tránh lỗi link khi include từ thư mục con
$base_url = '/expense-tracker'; 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker Pro</title>
    
    <!-- 1. Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- 2. CSS Chính (Sử dụng main.css thay vì header.css cũ) -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/toast.css">
    
    <!-- 3. QUAN TRỌNG: Thư viện Chart.js để vẽ biểu đồ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php if (isset($_SESSION['user_id'])): ?>
    
    <!-- === GIAO DIỆN MOBILE (Nút Hamburger) === -->
    <div class="mobile-header">
        <a href="#" class="brand" style="font-size: 20px;">ExpenseTracker.</a>
        <button class="hamburger-btn js-mobile-menu">☰</button>
    </div>

    <!-- Lớp phủ đen khi mở menu mobile -->
    <div class="mobile-overlay js-overlay"></div>

    <div class="app-layout">
        <!-- === SIDEBAR (MENU TRÁI) === -->
        <aside class="sidebar js-sidebar">
            <div class="brand-box">
                <a href="<?php echo $base_url; ?>/index.php" class="brand">ExpenseTracker.</a>
                <button class="close-sidebar-btn js-close-sidebar">✕</button>
            </div>
            
            <nav style="display: flex; flex-direction: column; gap: 5px; flex: 1;">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <!-- MENU ADMIN -->
                    <div style="font-size: 11px; text-transform: uppercase; color: #9ca3af; margin: 15px 0 5px 10px; font-weight: bold;">Quản trị</div>
                    <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="menu-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                        <span>📊</span> Tổng quan
                    </a>
                    <a href="<?php echo $base_url; ?>/admin/users.php" class="menu-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                        <span>👥</span> Quản lý Users
                    </a>
                    <a href="<?php echo $base_url; ?>/admin/admin_report.php" class="menu-item <?php echo $current_page == 'admin_report.php' ? 'active' : ''; ?>">
                        <span>📑</span> Báo cáo Giao dịch
                    </a>
                    <a href="<?php echo $base_url; ?>/modules/categories/index.php" class="menu-item">
                        <span>📂</span> Danh mục Hệ thống
                    </a>
                <?php else: ?>
                    <!-- MENU USER -->
                    <div style="font-size: 11px; text-transform: uppercase; color: #9ca3af; margin: 15px 0 5px 10px; font-weight: bold;">Cá nhân</div>
                    <a href="<?php echo $base_url; ?>/index.php" class="menu-item <?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                        <span>🏠</span> Dashboard
                    </a>
                    <a href="<?php echo $base_url; ?>/modules/transactions/index.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'transactions') !== false) ? 'active' : ''; ?>">
                        <span>💸</span> Thu chi
                    </a>
                    <a href="<?php echo $base_url; ?>/modules/wallets/index.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'wallets') !== false) ? 'active' : ''; ?>">
                        <span>💳</span> Ví tiền
                    </a>
                    <a href="<?php echo $base_url; ?>/modules/budgets/index.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'budgets') !== false) ? 'active' : ''; ?>">
                        <span>📉</span> Hạn mức
                    </a>
                    <a href="<?php echo $base_url; ?>/modules/categories/index.php" class="menu-item <?php echo (strpos($_SERVER['REQUEST_URI'], 'categories') !== false) ? 'active' : ''; ?>">
                        <span>📂</span> Danh mục
                    </a>
                <?php endif; ?>
            </nav>

            <!-- Footer của Sidebar (Avatar User) -->
            <div class="sidebar-footer">
                <div class="user-mini">
                    <!-- Avatar mặc định theo tên -->
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=random" alt="Avatar">
                    <div class="user-info">
                        <a href="<?php echo $base_url; ?>/modules/users/profile.php" style="text-decoration: none;">
                            <h4><?php echo $_SESSION['full_name']; ?></h4>
                        </a>
                        <a href="<?php echo $base_url; ?>/modules/auth/logout.php" style="font-size: 12px; color: #ef4444; text-decoration: none;" onclick="return confirm('Đăng xuất?');">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Container Chính (Nơi chứa nội dung trang) -->
        <main class="main-content">
            <div id="toast-container"></div>
            <script src="<?php echo $base_url; ?>/assets/js/toast.js"></script>

            <!-- Script xử lý đóng/mở Sidebar trên Mobile -->
            <script>
                const mobileBtn = document.querySelector('.js-mobile-menu');
                const sidebar = document.querySelector('.js-sidebar');
                const closeBtn = document.querySelector('.js-close-sidebar');
                const overlay = document.querySelector('.js-overlay');

                function showSidebar() {
                    sidebar.classList.add('open');
                    overlay.classList.add('open');
                }

                function hideSidebar() {
                    sidebar.classList.remove('open');
                    overlay.classList.remove('open');
                }

                if(mobileBtn) mobileBtn.addEventListener('click', showSidebar);
                if(closeBtn) closeBtn.addEventListener('click', hideSidebar);
                if(overlay) overlay.addEventListener('click', hideSidebar);
            </script>

<?php else: ?>
    <!-- Giao diện khi CHƯA ĐĂNG NHẬP -->
    <div id="toast-container"></div>
    <script src="<?php echo $base_url; ?>/assets/js/toast.js"></script>
    <!-- Mở container để giữ layout cho các trang auth/landing -->
    <div class="container">
<?php endif; ?>