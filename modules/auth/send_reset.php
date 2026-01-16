<?php
// === CẤU HÌNH GỬI MAIL (THAY ĐỔI CÁI NÀY) ===
define('SMTP_EMAIL', 'chipxinhgaiii@gmail.com'); 
define('SMTP_PASSWORD', 'jrmv jlir ecyh frdm');
define('SMTP_NAME', 'Expense Tracker Support');

session_start();
require_once '../../config/db.php';

// Nạp thủ công PHPMailer (Đảm bảo bạn đã copy file vào đúng chỗ)
require '../../assets/vendor/PHPMailer/Exception.php';
require '../../assets/vendor/PHPMailer/PHPMailer.php';
require '../../assets/vendor/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = trim($_POST["email"]);

    // 1. Kiểm tra email có tồn tại không
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        // 2. Tạo Token ngẫu nhiên
        $token = bin2hex(random_bytes(16)); // Token gửi đi (32 ký tự)
        $token_hash = hash("sha256", $token); // Token lưu vào DB (đã băm)
        
        // Thời gian hết hạn: 30 phút từ bây giờ
        $expiry = date("Y-m-d H:i:s", time() + 60 * 30);

        // 3. Lưu Token vào DB
        $sql = "UPDATE users SET reset_token_hash = ?, reset_token_expires_at = ? WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $token_hash, $expiry, $email);
        $stmt->execute();

        // 4. Gửi Email bằng PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Cấu hình Server
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_EMAIL;
            $mail->Password   = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Hoặc ENCRYPTION_SMTPS
            $mail->Port       = 587; // Hoặc 465
            $mail->CharSet    = 'UTF-8';

            // Người gửi - Người nhận
            $mail->setFrom(SMTP_EMAIL, SMTP_NAME);
            $mail->addAddress($email);

            // Nội dung Email
            $mail->isHTML(true);
            $mail->Subject = 'Đặt lại mật khẩu - Expense Tracker';
            
            // Link reset (Sửa localhost thành tên miền thật nếu up host)
            $reset_link = BASE_URL . "/modules/auth/reset_password.php?token=" . $token;

            $mail->Body    = "
                <h3>Xin chào,</h3>
                <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                <p>Vui lòng nhấn vào liên kết dưới đây để đặt lại mật khẩu (Liên kết hết hạn sau 30 phút):</p>
                <p><a href='$reset_link' style='background:#0095f6; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Đặt lại mật khẩu</a></p>
                <p>Nếu bạn không yêu cầu, vui lòng bỏ qua email này.</p>
            ";

            $mail->send();
            
            header("Location: forgot_password.php?msg=Đã gửi email hướng dẫn! Vui lòng kiểm tra hộp thư (cả mục Spam).&type=success");

        } catch (Exception $e) {
            header("Location: forgot_password.php?msg=Không thể gửi mail. Lỗi: {$mail->ErrorInfo}&type=error");
        }

    } else {
        // Email không tồn tại -> Vẫn báo thành công để tránh lộ thông tin (Security)
        // Hoặc báo lỗi tùy bạn. Ở đây mình báo lỗi cho dễ test.
        header("Location: forgot_password.php?msg=Email này chưa được đăng ký!&type=error");
    }
}
?>