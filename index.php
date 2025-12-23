<?php
session_start();
require_once 'config/db.php';

// =========================================================================
// PHẦN 1: ĐÃ ĐĂNG NHẬP -> HIỆN DASHBOARD (Tiếng Việt + Flexbox)
// =========================================================================
if (isset($_SESSION['user_id'])) {

    include 'includes/header.php';

    $user_id = $_SESSION['user_id'];
    $sql_balance = "SELECT SUM(balance) as total FROM wallets WHERE user_id = $user_id";
    $result = $conn->query($sql_balance);
    $row = $result->fetch_assoc();
    $total_balance = $row['total'] ? $row['total'] : 0;
?>
    <style>
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #dbdbdb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);

            flex: 1;
            min-width: 250px;
        }

        .card h3 {
            font-size: 14px;
            color: #8e8e8e;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .card .money {
            font-size: 24px;
            font-weight: bold;
            color: #262626;
        }

        .welcome-text {
            margin-bottom: 20px;
        }
    </style>

    <div class="welcome-text">
        <h2>Xin chào, <?php echo $_SESSION['full_name']; ?>! 👋</h2>
        <p style="color: #8e8e8e;">Tình hình tài chính hiện tại của bạn:</p>
    </div>

    <div class="dashboard-container">
        <div class="card">
            <h3>Tổng tài sản hiện có</h3>
            <div class="money" style="color: #0095f6;"><?php echo number_format($total_balance); ?> đ</div>
            <div style="margin-top: 10px; font-size: 13px;">
                <a href="modules/wallets/index.php" style="text-decoration: none; color: #0095f6;">Quản lý ví tiền &rarr;</a>
            </div>
        </div>
        <div class="card">
            <h3>Thu nhập tháng này</h3>
            <div class="money" style="color: #2ecc71;">+ 0 đ</div>
            <small>Chưa có dữ liệu</small>
        </div>
        <div class="card">
            <h3>Đã chi tiêu tháng này</h3>
            <div class="money" style="color: #ed4956;">- 0 đ</div>
            <small>Chưa có dữ liệu</small>
        </div>
    </div>

    <div class="card">
        <h3>Thao tác nhanh</h3>
        <div style="display: flex; gap: 10px; margin-top: 15px; flex-wrap: wrap;">
            <a href="modules/transactions/create.php" style="background: #0095f6; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">+ Thêm Giao dịch</a>
            <a href="modules/wallets/create.php" style="background: #efefef; color: #262626; padding: 10px 20px; text-decoration: none; border-radius: 4px;">+ Tạo Ví mới</a>
        </div>
    </div>

<?php
    include 'includes/footer.php';
    exit(); // Dừng code tại đây nếu đã đăng nhập
}
?>

<!-- ========================================================================= -->
<!-- PHẦN 2: CHƯA ĐĂNG NHẬP -> HIỆN TRANG GIỚI THIỆU (Tiếng Việt) -->
<!-- ========================================================================= -->
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang giới thiệu Expense Tracker</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Barlow', sans-serif;
        }

        #header {
            background-color: #3ebfff;
            /* Màu xanh da trời */
            /* background: url(./assets/images/header-bg.jpg) top center / cover no-repeat; */
            padding: 30px 40px 100px 40px;
            text-align: center;
            position: relative;
        }

        .nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 80px;
        }

        .logo-text {
            font-family: 'Fraunces', serif;
            font-weight: 900;
            color: white;
            font-size: 24px;
            text-decoration: none;
        }

        .menu {
            list-style: none;
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .menu a {
            text-decoration: none;
            color: white;
            font-size: 16px;
        }

        .btn-login {
            background: white;
            color: #3ebfff;
            padding: 12px 25px;
            border-radius: 30px;
            font-family: 'Fraunces', serif;
            text-transform: uppercase;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
        }

        /* Intro Text */
        .intro-text h1 {
            font-family: 'Fraunces', serif;
            font-size: 48px;
            text-transform: uppercase;
            color: white;
            letter-spacing: 5px;
            margin-bottom: 50px;
        }

        /* Content Grid */
        #content {
            display: flex;
            flex-wrap: wrap;
        }

        .half-box {
            width: 50%;
            min-height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Text Box */
        .text-box {
            padding: 50px 80px;
            text-align: left;
        }

        .text-box h3 {
            font-family: 'Fraunces', serif;
            font-size: 32px;
            color: #23303e;
            margin-bottom: 20px;
        }

        .text-box p {
            color: #818498;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .learn-more {
            font-family: 'Fraunces', serif;
            text-transform: uppercase;
            text-decoration: none;
            color: #23303e;
            font-weight: 900;
            border-bottom: 5px solid #fad400;
        }

        .img-box {
            width: 50%;
            min-height: 400px;
        }

        .img-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .service-box {
            width: 50%;
            height: 400px;
            position: relative;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding-bottom: 50px;
            background-size: cover;
            background-position: center;
        }

        .service-green {
            background-color: #90d4c5;
            color: #25564b;
        }

        .service-orange {
            background-color: #ffbc66;
            color: #19536b;
        }

        .service-box h4 {
            font-family: 'Fraunces', serif;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .service-box p {
            font-size: 14px;
            max-width: 300px;
            margin: 0 auto;
            line-height: 1.5;
        }

        /* Testimonials */
        .testimonials {
            padding: 100px 20px;
            text-align: center;
            background: #fffaf0;
        }

        .testimonials h4 {
            font-family: 'Fraunces', serif;
            text-transform: uppercase;
            color: #a7aaad;
            letter-spacing: 4px;
            margin-bottom: 60px;
        }

        .testi-grid {
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        .testi-item {
            width: 300px;
        }

        .avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #ccc;
            margin: 0 auto 30px;
        }

        .testi-name {
            font-family: 'Fraunces', serif;
            font-weight: 900;
            margin-top: 20px;
            color: #23303e;
        }

        /* Footer */
        .footer {
            background: #90d4c5;
            padding: 50px;
            text-align: center;
            color: #2c7566;
            font-weight: 600;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {

            .half-box,
            .img-box,
            .service-box {
                width: 100%;
            }

            #header {
                padding: 20px;
            }

            .intro-text h1 {
                font-size: 36px;
            }

            .menu {
                display: none;
            }

            .testi-grid {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <div id="main">

        <div id="header">
            <div class="nav">
                <a href="#" class="logo-text">ExpenseTracker</a>
                <ul class="menu">
                    <li><a href="#">Giới thiệu</a></li>
                    <li><a href="#">Tính năng</a></li>
                    <li><a href="modules/auth/login.php" class="btn-login">Đăng nhập</a></li>
                </ul>
            </div>

            <div class="intro-text">
                <h1>Quản lý tài chính<br>Thông minh & Hiệu quả</h1>
            </div>
        </div>

        <div id="content">

            <div class="half-box text-box">
                <div>
                    <h3>Kiểm soát dòng tiền</h3>
                    <p>Chúng tôi cung cấp công cụ giúp bạn ghi chép thu chi hàng ngày một cách nhanh chóng.
                        Giúp bạn biết chính xác tiền của mình đi đâu về đâu.</p>
                    <a href="modules/auth/register.php" class="learn-more">Đăng ký ngay</a>
                </div>
            </div>
            <div class="img-box">
                <img src="https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?auto=format&fit=crop&q=80&w=800" alt="Finance">
            </div>

            <div class="img-box">
                <img src="https://images.unsplash.com/photo-1579621970563-ebec7560ff3e?auto=format&fit=crop&q=80&w=800" alt="Saving">
            </div>
            <div class="half-box text-box">
                <div>
                    <h3>Tiết kiệm cho tương lai</h3>
                    <p>Đặt hạn mức chi tiêu cho từng danh mục (Ăn uống, Mua sắm...).
                        Hệ thống sẽ cảnh báo khi bạn tiêu quá tay.</p>
                    <a href="#" class="learn-more">Xem chi tiết</a>
                </div>
            </div>

            <!-- Khối 3: Dịch vụ -->
            <div class="service-box service-green">
                <h4>Quản lý Ví</h4>
                <p>Theo dõi số dư Tiền mặt, Thẻ ngân hàng, Ví điện tử tại một nơi duy nhất.</p>
            </div>
            <div class="service-box service-orange">
                <h4>Báo cáo</h4>
                <p>Biểu đồ trực quan giúp bạn nhìn lại thói quen tiêu dùng trong tháng.</p>
            </div>

        </div>

        <!-- TESTIMONIALS -->
        <div class="testimonials">
            <h4>Người dùng nói gì?</h4>
            <div class="testi-grid">
                <div class="testi-item">
                    <img src="https://randomuser.me/api/portraits/women/44.jpg" class="avatar" alt="">
                    <p>Ứng dụng rất dễ sử dụng, phù hợp với sinh viên như mình để quản lý tiền sinh hoạt phí.</p>
                    <div class="testi-name">Nguyễn Thùy Linh</div>
                    <small>Sinh viên KTQD</small>
                </div>
                <div class="testi-item">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" class="avatar" alt="">
                    <p>Giao diện đẹp, đơn giản. Mình thích nhất tính năng báo cáo chi tiêu.</p>
                    <div class="testi-name">Trần Văn Nam</div>
                    <small>Nhân viên văn phòng</small>
                </div>
            </div>
        </div>

        <div class="footer">
            Expense Tracker Project - Nhóm 2
            <br><br>
            <a href="modules/auth/login.php" style="color: #25564b;">Đăng nhập</a> |
            <a href="modules/auth/register.php" style="color: #25564b;">Đăng ký</a>
        </div>

    </div>
</body>

</html>