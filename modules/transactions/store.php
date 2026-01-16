<?php
session_start();
require_once '../../config/db.php';

// Chỉ xử lý POST và phải đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    
    // 1. Lấy và Làm sạch dữ liệu (Sanitize)
    // Dùng trim() để loại bỏ khoảng trắng thừa
    $wallet_id = isset($_POST['wallet_id']) ? intval($_POST['wallet_id']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $transaction_date = isset($_POST['transaction_date']) ? trim($_POST['transaction_date']) : '';
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';

    // 2. Validate dữ liệu
    $errors = [];

    if ($amount <= 0) {
        $errors[] = "Số tiền phải lớn hơn 0.";
    }

    if ($wallet_id <= 0) {
        $errors[] = "Vui lòng chọn ví thanh toán.";
    }

    if ($category_id <= 0) {
        $errors[] = "Vui lòng chọn danh mục.";
    }

    if (empty($transaction_date)) {
        $errors[] = "Ngày giao dịch không được để trống.";
    }

    // Nếu có lỗi, lưu vào session và quay lại trang create
    if (!empty($errors)) {
        // Gộp mảng lỗi thành 1 chuỗi để hiển thị Toast
        $_SESSION['flash_message'] = implode("<br>", $errors); 
        $_SESSION['flash_type'] = 'error'; // Loại thông báo lỗi
        header("Location: create.php");
        exit();
    }

    // 3. Logic Nghiệp vụ (Check DB)
    
    // Xác định loại giao dịch (Thu hay Chi) từ Category
    $sql_type = "SELECT type FROM categories WHERE id = ?";
    $stmt_type = $conn->prepare($sql_type);
    $stmt_type->bind_param("i", $category_id);
    $stmt_type->execute();
    $res_type = $stmt_type->get_result();
    
    if ($res_type->num_rows == 0) {
        $_SESSION['flash_message'] = "Danh mục không tồn tại!";
        $_SESSION['flash_type'] = 'error';
        header("Location: create.php");
        exit();
    }
    
    $type = $res_type->fetch_assoc()['type']; // 'income' hoặc 'expense'

    // 4. Bắt đầu Transaction (Database)
    $conn->begin_transaction();

    try {
        // A. Thêm vào bảng transactions
        $sql_trans = "INSERT INTO transactions (user_id, wallet_id, category_id, amount, transaction_date, note) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_trans = $conn->prepare($sql_trans);
        // note cần sanitize kỹ hơn để tránh XSS nếu hiển thị lại
        $clean_note = htmlspecialchars($note);
        $stmt_trans->bind_param("iiidss", $user_id, $wallet_id, $category_id, $amount, $transaction_date, $clean_note);
        
        if (!$stmt_trans->execute()) {
            throw new Exception("Lỗi thêm giao dịch: " . $stmt_trans->error);
        }

        // B. Cập nhật số dư Ví (Wallets)
        if ($type == 'expense') {
            // Chi tiêu -> Trừ tiền
            $sql_wallet = "UPDATE wallets SET balance = balance - ? WHERE id = ? AND user_id = ?";
        } else {
            // Thu nhập -> Cộng tiền
            $sql_wallet = "UPDATE wallets SET balance = balance + ? WHERE id = ? AND user_id = ?";
        }
        
        $stmt_wallet = $conn->prepare($sql_wallet);
        $stmt_wallet->bind_param("dii", $amount, $wallet_id, $user_id);
        
        if (!$stmt_wallet->execute()) {
            throw new Exception("Lỗi cập nhật ví: " . $stmt_wallet->error);
        }

        // C. Hoàn tất
        $conn->commit();
        
        // Dùng cơ chế Toast thông báo (giả sử header.php xử lý hiển thị session này)
        // Nếu chưa có, bạn có thể redirect kèm param msg=success
        header("Location: index.php?msg=success_add");

    } catch (Exception $e) {
        $conn->rollback(); // Có lỗi thì hoàn tác hết
        $_SESSION['flash_message'] = "Giao dịch thất bại: " . $e->getMessage();
        $_SESSION['flash_type'] = 'error';
        header("Location: create.php");
    }

} else {
    header("Location: index.php");
}
?>