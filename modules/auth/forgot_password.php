<?php
session_start();
require_once '../../config/db.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu | Expense Tracker</title>
    <!-- Tái sử dụng CSS Login để đồng bộ -->
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <style>
        /* CSS bổ sung để căn chỉnh form quên mật khẩu */
        .fp-container {
            width: 100%;
            padding: 0 40px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .fp-text {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .fp-text h3 {
            margin-bottom: 10px;
            font-size: 16px;
            color: #262626;
        }
        
        .fp-text p {
            color: #8e8e8e;
            font-size: 14px;
            line-height: 1.4;
        }

        .or-divider {
            display: flex;
            width: 100%;
            align-items: center;
            margin: 20px 0;
        }

        .or-divider span {
            height: 1px;
            background-color: #dbdbdb;
            flex: 1;
        }

        .or-divider b {
            color: #8e8e8e;
            font-size: 13px;
            font-weight: 600;
            padding: 0 10px;
            text-transform: uppercase;
        }
        
        /* Box đăng ký mới - nằm ngoài main */
        .register-box {
            width: 350px;
            background: white;
            border: 1px solid #dbdbdb;
            margin: 10px auto; /* Căn giữa */
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <div id="main">
        <div id="logo">
            <a href="../../index.php" class="logo-text">ExpenseTracker</a>
        </div>
        
        <div class="fp-container">
            <!-- Icon ổ khóa (Tùy chọn cho đẹp giống Instagram) -->
            <div style="margin-bottom: 15px;">
                <svg aria-label="Biểu tượng khóa" class="_8-yf5 " color="#262626" fill="#262626" height="60" role="img" viewBox="0 0 96 96" width="60"><circle cx="48" cy="48" fill="none" r="47" stroke="currentColor" stroke-miterlimit="10" stroke-width="2"></circle><path d="M48 60.1c-2.3 0-4.2-1.9-4.2-4.2s1.9-4.2 4.2-4.2 4.2 1.9 4.2 4.2-1.8 4.2-4.2 4.2zM58.3 35.8h-2.5v-5.2c0-4.3-3.5-7.8-7.8-7.8s-7.8 3.5-7.8 7.8v5.2h-2.5c-2 0-3.6 1.6-3.6 3.6v17.7c0 2 1.6 3.6 3.6 3.6h20.6c2 0 3.6-1.6 3.6-3.6V39.4c0-2-1.6-3.6-3.6-3.6zM42.5 30.6c0-3 2.5-5.5 5.5-5.5s5.5 2.5 5.5 5.5v5.2h-11v-5.2z" fill="none" stroke="currentColor" stroke-linejoin="round" stroke-width="2"></path></svg>
            </div>

            <div class="fp-text">
                <h3>Gặp sự cố đăng nhập?</h3>
                <p>Nhập email của bạn và chúng tôi sẽ gửi cho bạn một liên kết để truy cập lại vào tài khoản.</p>
            </div>

            <form action="send_reset.php" method="POST" style="width: 100%;">
                <div class="form-group" style="margin-bottom: 10px;">
                    <input type="email" name="email" class="form-control" placeholder="Email" required style="width: 100%;">
                </div>

                <button type="submit" class="btn-submit" style="width: 100%;">Gửi liên kết đăng nhập</button>
            </form>

            <div class="or-divider">
                <span></span>
                <b>Hoặc</b>
                <span></span>
            </div>

            <div style="margin-bottom: 30px;">
                <a href="register.php" style="text-decoration: none; color: #262626; font-weight: 600; font-size: 14px;">Tạo tài khoản mới</a>
            </div>
        </div>

        <!-- Footer quay lại đăng nhập -->
        <div style="width: 100%; background-color: #fafafa; border-top: 1px solid #dbdbdb; padding: 10px 0; text-align: center; margin-top: auto;">
            <a href="login.php" style="text-decoration: none; color: #262626; font-weight: 600; font-size: 14px;">Quay lại Đăng nhập</a>
        </div>
    </div>

    <div id="toast-container"></div>
    <script src="../../assets/js/toast.js"></script>

    <!-- Hiển thị thông báo nếu có -->
    <?php if(isset($_GET['msg'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = "<?php echo htmlspecialchars($_GET['msg']); ?>";
                const type = "<?php echo isset($_GET['type']) ? $_GET['type'] : 'info'; ?>";
                showToast(msg, type);
            });
        </script>
    <?php endif; ?>

</body>
</html>