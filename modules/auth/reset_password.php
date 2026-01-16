<?php
require_once '../../config/db.php';

if (!isset($_SESSION['reset_email'])) {
    redirect('forgot_password.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp = sanitize($_POST['otp']);
    $new_pass = $_POST['new_password'];
    $email = $_SESSION['reset_email'];

    // Kiểm tra OTP và thời hạn
    $sql = "SELECT id FROM users WHERE email = '$email' AND reset_token = '$otp' AND reset_exp > NOW()";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Đổi mật khẩu
        $pass_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        
        // Xóa token sau khi dùng xong
        $conn->query("UPDATE users SET password = '$pass_hash', reset_token = NULL, reset_exp = NULL WHERE email = '$email'");
        
        unset($_SESSION['reset_email']);
        set_flash_message('Đổi mật khẩu thành công! Hãy đăng nhập.', 'success');
        redirect('login.php');
    } else {
        set_flash_message('Mã OTP không đúng hoặc đã hết hạn!', 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&display=swap" rel="stylesheet">
</head>
<body>
    <div id="main">
        <div id="logo">
            <span class="logo-text">Đặt lại mật khẩu</span>
        </div>
        
        <form method="POST" style="display: flex; flex-direction: column; align-items: center;">
            <div style="font-size: 13px; margin-bottom: 10px; color: #666;">
                Email: <b><?php echo $_SESSION['reset_email']; ?></b>
            </div>

            <input type="text" name="otp" class="form-control" placeholder="Nhập mã OTP (6 số)" required>
            <input type="password" name="new_password" class="form-control" placeholder="Mật khẩu mới" required>
            
            <button type="submit" class="btn-submit">Xác nhận đổi</button>
        </form>
    </div>
    
    <div id="toast-container"></div>
    <script src="../../assets/js/toast.js"></script>
    <?php display_flash_message(); ?>
</body>
</html>