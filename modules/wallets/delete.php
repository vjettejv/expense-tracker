<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra có ID cần xóa và đã đăng nhập chưa
if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    
    $id_vi_can_xoa = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // QUAN TRỌNG: Thêm điều kiện AND user_id = ... để không xóa nhầm ví của người khác
    $sql = "DELETE FROM wallets WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_vi_can_xoa, $user_id);
    
    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Không thể xóa. Có thể ví này không phải của bạn.";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php");
}
?>