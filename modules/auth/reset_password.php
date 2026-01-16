<?php
session_start();
require_once '../../config/db.php';

$token = $_GET["token"] ?? "";
$error_msg = "";

if (empty($token)) {
    die("Token không hợp lệ hoặc thiếu.");
}

// 1. Kiểm tra Token trong DB
$token_hash = hash("sha256", $token);
$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    die("Liên kết không hợp lệ hoặc đã được sử dụng.");
}
if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Liên kết đã hết hạn (chỉ có hiệu lực trong 30 phút).");
}

// 2. Xử lý Đổi Mật Khẩu
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if (strlen($password) < 6) {
        $error_msg = "Mật khẩu phải có ít nhất 6 ký tự.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Mật khẩu xác nhận không khớp.";
    } else {
        // --- QUAN TRỌNG: Dùng password_hash (Bcrypt) ---
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Update mật khẩu và Xóa token
        $sql = "UPDATE users SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $password_hash, $user["id"]);
        
        if ($stmt->execute()) {
            header("Location: login.php?msg=Đổi mật khẩu thành công! Hãy đăng nhập.&type=success");
            exit();
        } else {
            $error_msg = "Lỗi hệ thống: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="../../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@700;900&family=Barlow:wght@600&display=swap" rel="stylesheet">
    <style>
        .password-wrapper { position: relative; width: 268px; }
        .password-wrapper input { width: 100%; }
        .toggle-password {
            position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
            cursor: pointer; font-size: 12px; color: #262626; font-weight: 600;
            background: none; border: none; user-select: none;
        }
        /* CSS sửa lỗi căn giữa cho box phụ */
        .footer-box {
            width: 350px;
            background: white;
            border: 1px solid #dbdbdb;
            margin: 10px auto; /* Canh giữa màn hình */
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div id="main">
        <div id="logo">
            <span class="logo-text">Mật khẩu mới</span>
        </div>
        
        <div style="padding: 0 40px; text-align: center; margin-bottom: 20px;">
            <p style="color: #8e8e8e; font-size: 14px; line-height: 1.4;">
                Tài khoản: <b style="color: #262626;"><?php echo htmlspecialchars($user['email']); ?></b>
            </p>
        </div>

        <?php if (!empty($error_msg)): ?>
            <div style="color: #ed4956; font-size: 13px; margin-bottom: 15px; text-align: center;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="resetForm" style="display: flex; flex-direction: column; align-items: center; width: 100%;">
            <div class="form-group password-wrapper">
                <input type="password" name="password" id="password" class="form-control" placeholder="Mật khẩu mới" required>
                <button type="button" class="toggle-password" onclick="toggleVisibility('password', this)">Hiện</button>
            </div>
            <div class="form-group password-wrapper" style="margin-top: 6px;">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required>
            </div>
            <button type="submit" class="btn-submit">Lưu Mật Khẩu</button>
        </form>
    </div>

    <!-- Box quay lại đăng nhập (Đã sửa căn giữa) -->
    <div class="footer-box">
        <a href="login.php" style="text-decoration: none; color: #262626; font-weight: 600;">Quay lại Đăng nhập</a>
    </div>

    <script>
        function toggleVisibility(id, btn) {
            const el = document.getElementById(id);
            if(el.type === 'password') { el.type = 'text'; btn.textContent = 'Ẩn'; }
            else { el.type = 'password'; btn.textContent = 'Hiện'; }
        }
    </script>
</body>
</html>