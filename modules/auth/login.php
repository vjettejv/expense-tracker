<?php
require_once '../../config/db.php';

// Nếu đã đăng nhập
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        redirect(BASE_URL . '/admin/dashboard.php');
    } else {
        redirect(BASE_URL . '/index.php');
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        set_flash_message('Vui lòng nhập đầy đủ thông tin!', 'error');
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Kiểm tra trạng thái khóa
            if ($user['status'] === 'banned') {
                set_flash_message('Tài khoản của bạn đã bị KHÓA do vi phạm!', 'error');
            } 
            // NÂNG CẤP: Verify hash password
            elseif (password_verify($password, $user['password'])) {
                // Đăng nhập thành công
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                $_SESSION['avatar']    = $user['avatar'];

                set_flash_message('Chào mừng trở lại, ' . $user['full_name'] . '!', 'success');
                
                if ($user['role'] === 'admin') {
                    redirect(BASE_URL . '/admin/dashboard.php');
                } else {
                    redirect(BASE_URL . '/index.php');
                }
            } else {
                set_flash_message('Mật khẩu không chính xác!', 'error');
            }
        } else {
            set_flash_message('Tên đăng nhập không tồn tại!', 'error');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | Expense Tracker</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link rel="stylesheet" href="../../assets/css/toast.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">
</head>
<body>
    <div id="main">
        <div id="logo">
            <a href="../../index.php" class="logo-text">ExpenseTracker</a>
        </div>
        
        <div id="loginFrom">
            <form id="formmain" method="POST">
                <div class="form-col">
                    <div class="form-group">
                        <input type="text" id="username" name="username" class="form-control" placeholder="Tên đăng nhập" required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                    </div>
                    <div class="form-button">
                        <button type="submit" id="btn-login" class="btn-submit">Đăng nhập</button>
                    </div>
                    
                    <div style="margin-top: 15px; font-size: 12px;">
                        <a href="forgot_password.php" style="color: #0095f6; text-decoration: none;">Quên mật khẩu?</a>
                    </div>
                </div>
            </form>
        </div>
        <div class="register-link">
            Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a>
        </div>
    </div>
    
    <div id="toast-container"></div>
    <script src="../../assets/js/toast.js"></script>
    <?php display_flash_message(); ?>
</body>
</html>