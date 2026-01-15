<?php
session_start();
// 1. Kết nối Database
require_once '../../config/db.php';

// 2. Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../auth/login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$message = '';

// 3. Xử lý khi người dùng xác nhận đã chuyển khoản
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_deposit'])) {
    $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $transaction_date = date('Y-m-d H:i:s'); // Lưu cả giờ phút cho chính xác

    if ($user_id > 0 && $wallet_id > 0 && $category_id > 0 && $amount > 0) {
        // Bắt đầu transaction để đảm bảo toàn vẹn dữ liệu
        $conn->begin_transaction();
        try {
            // Lưu vào bảng transactions
            $sql = "INSERT INTO transactions (user_id, wallet_id, category_id, amount, transaction_date) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiids", $user_id, $wallet_id, $category_id, $amount, $transaction_date);
            $stmt->execute();

            // Cập nhật số dư trong ví (đây là giao dịch 'thu' nên luôn là +)
            $update_sql = "UPDATE wallets SET balance = balance + ? WHERE id = ? AND user_id = ?";
            $stmt_update = $conn->prepare($update_sql);
            $stmt_update->bind_param("dii", $amount, $wallet_id, $user_id);
            $stmt_update->execute();
            
            // Commit transaction
            $conn->commit();
            $message = '<p class="msg-success">✅ Giao dịch nạp tiền đã được ghi nhận thành công!</p>';

        } catch (Exception $e) {
            $conn->rollback();
            $message = '<p class="msg-error">Lỗi hệ thống: ' . $e->getMessage() . '</p>';
        }
    } else {
        $message = '<p class="msg-error">Thông tin không hợp lệ, không thể ghi nhận giao dịch.</p>';
    }
}

// 4. Lấy tham số từ URL để hiển thị
$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$wallet_id = isset($_GET['wallet_id']) ? intval($_GET['wallet_id']) : 0;
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Lấy thông tin ví để hiển thị tên
$wallet_name = 'Không rõ';
if ($wallet_id > 0) {
    $stmt_wallet = $conn->prepare("SELECT name FROM wallets WHERE id = ? AND user_id = ?");
    $stmt_wallet->bind_param("ii", $wallet_id, $user_id);
    $stmt_wallet->execute();
    $result_wallet = $stmt_wallet->get_result();
    if($row = $result_wallet->fetch_assoc()) {
        $wallet_name = $row['name'];
    }
}

// Nếu thiếu thông tin quan trọng và không phải là POST request, chuyển về trang thêm giao dịch
if (($_SERVER['REQUEST_METHOD'] !== 'POST') && ($amount <= 0 || $wallet_id <= 0 || $category_id <= 0)) {
    header("Location: user_add.php");
    exit();
}

// 5. Gọi Header
include '../../includes/header.php';

// --- THÔNG TIN NGÂN HÀNG NHẬN TIỀN (Vẫn hardcode) ---
$bankId = '970436'; // BIN của Vietcombank
$accountNumber = '1024775440'; // Số tài khoản của bạn
$accountName = 'DAM DINH LONG'; // Tên chủ tài khoản của bạn
$description = 'Nap tien ' . time(); // Nội dung chuyển khoản
$qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNumber}-compact2.png?amount={$amount}&addInfo=" . urlencode($description) . "&accountName=" . urlencode($accountName);

?>

<!-- 6. CSS và Nội dung chính -->
<!-- Tái sử dụng một số class từ user_add.css để hiển thị thông báo -->
<link rel="stylesheet" href="../../assets/css/user_add.css"> 
<style>
    .qr-page-wrapper {
        display: flex;
        justify-content: center;
        align-items: flex-start; /* Align top */
        padding: 40px 20px;
        text-align: center;
    }
    .qr-card {
        background: #fff;
        padding: 30px 40px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        border: 1px solid #eee;
        max-width: 420px; /* Tăng độ rộng một chút */
        width: 100%;
    }
    .qr-card h2 { margin-top: 0; margin-bottom: 10px; color: #333; font-size: 22px; }
    .qr-card p { color: #666; margin-bottom: 20px; }
    .qr-image-container img { max-width: 100%; border: 1px solid #ddd; border-radius: 8px; }
    .qr-info { margin-top: 20px; text-align: left; font-size: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px; }
    .qr-info div { margin-bottom: 10px; }
    .qr-info div:last-child { margin-bottom: 0; }
    .qr-info span { font-weight: bold; color: #000; }
    
    .btn-confirm {
        background-color: #28a745;
        color: white;
        padding: 15px;
        width: 100%;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        margin-top: 25px;
        transition: background-color 0.2s;
        display: block;
    }
    .btn-confirm:hover {
        background-color: #218838;
    }
    .btn-cancel { 
        display: inline-block; 
        margin-top: 15px; 
        padding: 10px 30px; 
        background: #f8f9fa;
        border: 1px solid #dbdbdb;
        color: #262626;
        text-decoration: none; 
        border-radius: 5px; 
        font-weight: bold; 
    }
    .post-submission-links a {
        margin: 5px;
    }
</style>

<div class="qr-page-wrapper">
    <div class="qr-card">
        <?php if (!empty($message)) { echo $message; } ?>

        <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST'): // Chỉ hiển thị form QR nếu chưa submit ?>
            <h2>Quét mã QR để nạp tiền</h2>
            <p>Sử dụng ứng dụng ngân hàng của bạn để hoàn tất giao dịch.</p>

            <div class="qr-image-container">
                <img src="<?php echo $qrUrl; ?>" alt="Mã QR thanh toán">
            </div>

            <div class="qr-info">
                 <div>Số tiền: <span><?php echo number_format($amount, 0, ',', '.'); ?> VNĐ</span></div>
                 <div>Nạp vào ví: <span><?php echo htmlspecialchars($wallet_name); ?></span></div>
                 <div>Nội dung: <span><?php echo htmlspecialchars($description); ?></span></div>
                 <div>Ngân hàng: <span>Vietcombank</span></div>
                 <div>Chủ tài khoản: <span>DAM DINH LONG</span></div>
            </div>

            <form method="POST" onsubmit="return confirm('Xác nhận bạn ĐÃ CHUYỂN KHOẢN THÀNH CÔNG và muốn ghi nhận giao dịch này?');">
                <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                <input type="hidden" name="wallet_id" value="<?php echo $wallet_id; ?>">
                <input type="hidden" name="category_id" value="<?php echo $category_id; ?>">
                <button type="submit" name="confirm_deposit" class="btn-confirm">✅ Đã chuyển khoản & Lưu giao dịch</button>
            </form>

            <a href="user_add.php" class="btn-cancel">Hủy bỏ</a>
        <?php else: ?>
            <!-- Đã submit, chỉ hiển thị thông báo và các nút hành động tiếp theo -->
            <div class="post-submission-links">
                <a href="user_history.php" class="btn-confirm" style="background-color: #007bff;">📜 Xem lịch sử giao dịch</a>
                <a href="user_add.php" class="btn-cancel" style="margin-top: 10px;">+ Thêm giao dịch khác</a>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php
// 7. Gọi Footer
include '../../includes/footer.php';
?>