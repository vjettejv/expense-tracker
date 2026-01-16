<?php
session_start();
require_once '../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    
    $user_id = $_SESSION['user_id'];
    
    // 1. Sanitize
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $type = isset($_POST['type']) ? trim($_POST['type']) : '';
    $color = isset($_POST['color']) ? trim($_POST['color']) : '#000000';

    // 2. Validate
    if (empty($name)) {
        echo "<script>alert('Tên danh mục không được để trống!'); window.history.back();</script>";
        exit();
    }

    // Validate type chỉ được là 'income' hoặc 'expense'
    if ($type !== 'income' && $type !== 'expense') {
        echo "<script>alert('Loại danh mục không hợp lệ!'); window.history.back();</script>";
        exit();
    }

    // 3. Insert
    $sql = "INSERT INTO categories (user_id, name, type, color) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $user_id, $name, $type, $color);
    
    if ($stmt->execute()) {
        header("Location: index.php?msg=success");
    } else {
        // Kiểm tra lỗi duplicate entry (nếu có unique key cho name)
        echo "Lỗi: " . $conn->error;
    }

} else {
    header("Location: index.php");
}
?>