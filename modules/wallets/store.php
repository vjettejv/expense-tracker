<?php
session_start();
require_once '../../config/db.php';

// Chỉ xử lý khi POST và đã đăng nhập
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $user_id = $_SESSION['user_id'];
    
    // 1. Sanitize & Validate
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    // Cho phép số dư là 0 hoặc âm (nếu là ví tín dụng), nhưng phải là số
    $balance = isset($_POST['balance']) ? floatval($_POST['balance']) : 0; 
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    $errors = [];

    // Kiểm tra tên ví
    if (empty($name)) {
        $errors[] = "Tên ví không được để trống!";
    } elseif (strlen($name) > 50) {
        $errors[] = "Tên ví quá dài (tối đa 50 ký tự).";
    }

    // Nếu có lỗi
    if (!empty($errors)) {
        // Có thể dùng session flash message hoặc echo script alert quay lại
        echo "<script>alert('" . implode("\\n", $errors) . "'); window.history.back();</script>";
        exit();
    }

    // 2. Insert Database
    $sql = "INSERT INTO wallets (user_id, name, balance, description) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    // clean description
    $clean_desc = htmlspecialchars($description);
    $stmt->bind_param("isds", $user_id, $name, $balance, $clean_desc);
    
    if ($stmt->execute()) {
        header("Location: index.php?msg=success_add");
    } else {
        echo "Có lỗi xảy ra: " . $conn->error;
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php");
}
?>