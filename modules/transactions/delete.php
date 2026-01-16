<?php
session_start();
require_once '../../config/db.php';
require_login(); // Hàm check login từ config

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // 1. Lấy thông tin giao dịch chuẩn bị xóa (để biết số tiền và ví nào mà hoàn lại)
    $sql_check = "SELECT t.*, c.type as category_type 
                  FROM transactions t 
                  JOIN categories c ON t.category_id = c.id 
                  WHERE t.id = ? AND t.user_id = ?";
    
    $stmt = $conn->prepare($sql_check);
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $trans = $stmt->get_result()->fetch_assoc();

    if ($trans) {
        $wallet_id = $trans['wallet_id'];
        $amount = $trans['amount'];
        $type = $trans['category_type'];

        // 2. Logic Hoàn tiền (Rollback Balance)
        // - Nếu xóa khoản CHI TIÊU (Expense) -> Tiền phải quay về ví -> CỘNG (+)
        // - Nếu xóa khoản THU NHẬP (Income) -> Tiền phải trừ đi khỏi ví -> TRỪ (-)
        if ($type == 'expense') {
            $sql_update = "UPDATE wallets SET balance = balance + ? WHERE id = ?";
        } else {
            $sql_update = "UPDATE wallets SET balance = balance - ? WHERE id = ?";
        }
        
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("di", $amount, $wallet_id);
        
        // Chỉ khi cập nhật ví thành công mới xóa giao dịch
        if ($stmt_update->execute()) {
            
            // 3. Xóa giao dịch
            $stmt_del = $conn->prepare("DELETE FROM transactions WHERE id = ?");
            $stmt_del->bind_param("i", $id);
            
            if ($stmt_del->execute()) {
                // Thành công: Quay về index và báo tin vui
                header("Location: index.php?msg=deleted");
                exit();
            }
        }
    } else {
        // Không tìm thấy hoặc hack quyền
        header("Location: index.php?msg=error");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>