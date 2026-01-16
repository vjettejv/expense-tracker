<?php
require_once '../../config/db.php';

// Nếu đã đăng nhập thì không cho vào trang này
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/index.php');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = sanitize($_POST['full_name']);
    $username  = sanitize($_POST['username']);
    $email     = sanitize($_POST['email']);
    $password  = $_POST['password']; // Password không cần sanitize để giữ nguyên ký tự đặc biệt
    
    // Validate
    if (empty($full_name) || empty($username) || empty($email) || empty($password)) {
        set_flash_message('Vui lòng điền đầy đủ thông tin!', 'error');
    } elseif (strlen($password) < 6) {
        set_flash_message('Mật khẩu phải có ít nhất 6 ký tự!', 'error');
    } else {
        // Kiểm tra trùng lặp
        $check = $conn->query("SELECT id FROM users WHERE username = '$username' OR email = '$email'");
        if ($check->num_rows > 0) {
            set_flash_message('Tên đăng nhập hoặc Email đã tồn tại!', 'error');
        } else {
            // NÂNG CẤP: Dùng PASSWORD_DEFAULT (Bcrypt) thay vì MD5
            $pass_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $conn->prepare("INSERT INTO users (full_name, username, email, password, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
            $stmt->bind_param("ssss", $full_name, $username, $email, $pass_hash);

            if ($stmt->execute()) {
                set_flash_message('Đăng ký thành công! Vui lòng đăng nhập.', 'success');
                redirect('login.php');
            } else {
                set_flash_message('Lỗi hệ thống: ' . $conn->error, 'error');
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
    <link rel="stylesheet" href="../../assets/css/register.css">
    <!-- Thêm CSS chung cho Toast -->
    <link rel="stylesheet" href="../../assets/css/toast.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">
</head>
<body>
    <div id="main">
        <div id="logo">
            <a href="../../index.php" class="logo-text">ExpenseTracker</a>
        </div>
        
        <h3>Đăng ký để quản lý chi tiêu.</h3>

        <form method="POST">
            <input type="email" name="email" class="form-control" placeholder="Email" required value="<?php echo isset($email) ? $email : ''; ?>">
            <input type="text" name="full_name" class="form-control" placeholder="Tên đầy đủ" required value="<?php echo isset($full_name) ? $full_name : ''; ?>">
            <input type="text" name="username" class="form-control" placeholder="Tên đăng nhập" required value="<?php echo isset($username) ? $username : ''; ?>">
            <input type="password" name="password" class="form-control" placeholder="Mật khẩu (Tối thiểu 6 ký tự)" required>

            <button type="submit" class="btn-submit">Đăng ký</button>
        </form>
    </div>

    <div class="login-link-box">
        <p class="login-link">Bạn đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>

    <!-- Container cho Toast Notification -->
    <div id="toast-container"></div>
    <script src="../../assets/js/toast.js"></script>
    <?php display_flash_message(); ?>
</body>
</html>