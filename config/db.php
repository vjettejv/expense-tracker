<?php
// config/db.php - File cấu hình duy nhất

$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "expense_tracker";

// 1. Kết nối Database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    exit("Lỗi charset: " . $conn->error);
}

// 2. Khởi tạo Session an toàn
if (session_status() === PHP_SESSION_NONE) {
    // Cấu hình session an toàn hơn (HttpOnly)
    ini_set('session.cookie_httponly', 1);
    session_start();
}

define('BASE_URL', '/expense-tracker'); // Đổi đường dẫn nếu bạn đặt thư mục khác

// =========================================================================
// PHẦN 3: CÁC HÀM DÙNG CHUNG (HELPER FUNCTIONS) - GIẢI QUYẾT VẤN ĐỀ DRY
// =========================================================================

// 3.1. Hàm làm sạch dữ liệu đầu vào (Chống XSS)
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// 3.2. Middleware kiểm tra đăng nhập (DRY - Chỉ cần gọi hàm này ở đầu mỗi file)
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect(BASE_URL . '/modules/auth/login.php');
    }
}

// 3.3. Middleware kiểm tra Admin (Chặn User thường vào trang Admin)
function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        // Nếu cố tình vào, đá về trang chủ và báo lỗi
        set_flash_message('Bạn không có quyền truy cập khu vực này!', 'error');
        redirect(BASE_URL . '/index.php');
    }
}

// 3.4. Hàm chuyển hướng nhanh
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// 3.5. Hệ thống thông báo (Flash Message - Toast)
function set_flash_message($message, $type = 'success') {
    $_SESSION['flash_message'] = [
        'text' => $message,
        'type' => $type // 'success', 'error', 'info'
    ];
}

function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        
        // Render đoạn JS để gọi Toast (Sử dụng lại hàm showToast trong login.php của bạn)
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof showToast === 'function') {
                    showToast('" . addslashes($msg['text']) . "', '" . $msg['type'] . "');
                } else {
                    // Fallback nếu chưa có hàm showToast
                    alert('" . addslashes($msg['text']) . "');
                }
            });
        </script>";
    }
}

// 3.6. Bảo mật CSRF (Chống tấn công giả mạo request)
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf_token() {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF Token không hợp lệ! Yêu cầu bị từ chối.");
    }
}
?>