<?php
require_once '../../config/db.php';

// Xử lý gửi mã OTP (Giả lập gửi email)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize($_POST['email']);

    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        // Tạo token ngẫu nhiên (OTP)
        $otp = rand(100000, 999999);
        $user = $res->fetch_assoc();
        
        // Lưu OTP vào DB với thời hạn 15 phút
        // Lưu ý: Cần chỉnh múi giờ DB cho khớp hoặc dùng hàm NOW()
        $conn->query("UPDATE users SET reset_token = '$otp', reset_exp = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE email = '$email'");

        // GỬI EMAIL (Ở đây ta giả lập bằng cách hiện thông báo)
        // Trong thực tế, dùng PHPMailer để gửi $otp vào $email
        // $subject = "Mã xác thực lấy lại mật khẩu";
        // $message = "Mã OTP của bạn là: " . $otp;
        // mail($email, $subject, $message);

        set_flash_message("Mã OTP đã được gửi đến email $email (Demo: $otp)", 'success');
        $_SESSION['reset_email'] = $email; // Lưu email để qua bước sau dùng
        redirect('reset_password.php');

    } else {
        set_flash_message('Email này chưa được đăng ký!', 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Quên mật khẩu</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div id="main">
        <div id="logo">
            <span class="logo-text">Khôi phục mật khẩu</span>
        </div>
        
        <div style="padding: 0 40px; text-align: center; color: #8e8e8e; font-size: 14px; margin-bottom: 20px;">
            Nhập email của bạn, chúng tôi sẽ gửi mã xác thực để đặt lại mật khẩu.
        </div>

        <form method="POST" style="display: flex; flex-direction: column; align-items: center;">
            <input type="email" name="email" class="form-control" placeholder="Nhập email đăng ký" required>
            <button type="submit" class="btn-submit">Gửi mã xác thực</button>
        </form>

        <div class="register-link">
            <a href="login.php">Quay lại Đăng nhập</a>
        </div>
    </div>
    
    <div id="toast-container"></div>
    <script src="../../assets/js/toast.js"></script>
    <?php display_flash_message(); ?>
</body>
</html>