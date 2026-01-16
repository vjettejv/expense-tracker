<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $wallet_id = intval($_POST['wallet_id']);
    $category_id = intval($_POST['category_id']);
    $amount = floatval($_POST['amount']);
    $transaction_date = $_POST['transaction_date'];
    $note = sanitize($_POST['note']);

    // 1. Validate
    if ($amount <= 0) {
        die("Số tiền phải lớn hơn 0");
    }
    if ($wallet_id == 0 || $category_id == 0) {
        die("Vui lòng chọn ví và danh mục");
    }

    // 2. Xác định loại giao dịch (Thu hay Chi) từ Category
    $sql_type = "SELECT type FROM categories WHERE id = ?";
    $stmt_type = $conn->prepare($sql_type);
    $stmt_type->bind_param("i", $category_id);
    $stmt_type->execute();
    $res_type = $stmt_type->get_result();
    
    if ($res_type->num_rows == 0) die("Danh mục không tồn tại");
    $type = $res_type->fetch_assoc()['type']; // 'income' hoặc 'expense'

    // 3. Bắt đầu Transaction (Database) để đảm bảo toàn vẹn dữ liệu
    $conn->begin_transaction();

    try {
        // A. Thêm vào bảng transactions
        $sql_trans = "INSERT INTO transactions (user_id, wallet_id, category_id, amount, transaction_date, note) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_trans = $conn->prepare($sql_trans);
        $stmt_trans->bind_param("iiidss", $user_id, $wallet_id, $category_id, $amount, $transaction_date, $note);
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
        set_flash_message("Thêm giao dịch thành công!", "success");
        header("Location: index.php");

    } catch (Exception $e) {
        $conn->rollback(); // Có lỗi thì hoàn tác hết
        echo "Giao dịch thất bại: " . $e->getMessage();
    }

} else {
    header("Location: index.php");
}
?>