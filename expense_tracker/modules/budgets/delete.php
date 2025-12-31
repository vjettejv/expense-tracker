<?php
session_start();
require_once '../../config/db.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    
    $id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Xóa hạn mức (Chỉ xóa cái của mình)
    $sql = "DELETE FROM budgets WHERE id = ? AND user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        header("Location: index.php");
    } else {
        echo "Lỗi khi xóa.";
    }

} else {
    header("Location: index.php");
}
?>