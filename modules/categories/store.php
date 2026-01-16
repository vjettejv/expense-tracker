<?php
session_start();
require_once '../../config/db.php';

// Kiểm tra xem có phải gửi từ form không
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $color = $_POST['color'];

    if (!empty($name)) {
        // Chuẩn bị câu lệnh Insert
        $sql = "INSERT INTO categories (user_id, name, type, color) VALUES (?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $name, $type, $color);
        
        if ($stmt->execute()) {
            // Thành công -> Quay về trang danh sách
            header("Location: index.php?msg=success");
        } else {
            echo "Lỗi: " . $conn->error;
        }
    } else {
        echo "Tên không được để trống!";
    }
} else {
    header("Location: index.php");
}
?>