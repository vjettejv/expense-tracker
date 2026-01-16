<?php
session_start();
require_once '../../config/db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // QUAN TRỌNG: Chỉ xóa nếu category đó thuộc về user đang đăng nhập
    // Thêm điều kiện AND user_id = $user_id để tránh xóa nhầm của người khác hoặc xóa danh mục hệ thống
    $sql = "DELETE FROM categories WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: index.php?msg=deleted");
    } else {
        echo "Lỗi hoặc bạn không có quyền xóa mục này.";
    }
} else {
    header("Location: index.php");
}
?>